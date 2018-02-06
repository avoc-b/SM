<?php
/**
 * @author Vasiliy (avoc-b@yandex.ru)
 * @copyright 2013
 */



if (!defined('CS-SOFT')) die("Hacking attempt!");

//if(dbTYPE != 'mysql') return;
if ($pCore->tb === false) {
    $pDebug->warn('Не настроено подключение к бд или отсутствует бд на сервере...');
    return;
    }

// если в этом часе уже сохранялся файл, то пока не нужно сохранять
$back_name  = dbNAME.'_'.date('Ymd_H').'.sql';
$back_dir   = 'project/backup/';
$files = scandir($back_dir);
if(in_array($back_name.'.zip', $files)) {
    $pDebug->debug('Бекап базы данных пока не требуется');
    return;
    }

// чистка бекапов за сегодня
$lenght = (int)strlen($back_name) -6;
foreach($files as $file) {
    if(substr($file,0,$lenght) == substr($back_name,0,$lenght)) unlink($back_dir . $file);
    }


if(dbTYPE == 'mysql')
     $return = backupMySQL(dbHOST,dbUSER,dbPASS,dbNAME);
else $return = backupMsSQL(dbHOST,dbUSER,dbPASS,dbNAME);



//создаем новый объект ZipArchive
$zip = new ZipArchive;
//используем метод open(), но теперь используем ключ ZipArchive::CREATE
//который говорит, что архив нужно создать
//а первым параметром передаем название архива]
$res = $zip->open($back_dir.$back_name.'.zip', ZipArchive::CREATE);
if ($res === TRUE) {
	//вот это интересная функция, которая, использует содержимое файла
    //для добавления его в архив
    $zip->addFromString($back_name, $return);
    //тут все просто: говорим, какой файл добавить в архив
    //$zip->addFile('file.fl');
    //закрываем работу с архивом
    $zip->close();
    $pDebug->debug('Создан бекап базы данных...');
} else {
    $pDebug->debug('Ошибка создания бекапа №'.$res);
}
/*
require_once('pclzip.lib.php');
$archive = new PclZip('archive.zip');
$archive->add('cafe_20131017_213326.sql');
*/



/**
 * Бекап БД из MySQL
 */
function backupMySQL($host,$user,$pass,$name)
{
    $db = mysql_connect($host,$user,$pass);
        //mysql_query('SET NAMES cp1251');
        mysql_set_charset('utf8',$db);

    $result = array();
    mysql_select_db('information_schema', $db);
    $tb = mysql_query("select TABLE_NAME,TABLE_TYPE from TABLES where TABLE_SCHEMA='". $name ."'");
    while ($tmp = mysql_fetch_assoc($tb)) $result[] = $tmp;


    foreach($result as $v)
    {
        switch($v['TABLE_TYPE'])
        {
            case 'BASE TABLE':

                    mysql_select_db($name, $db);
                    $return.= 'DROP TABLE  IF EXISTS `'. $v['TABLE_NAME'] .'`;';
                    $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '. $v['TABLE_NAME']));
                    $return.= "\n\n".$row2[1].";\n\n";


                    $result = mysql_query('SELECT * FROM '. $v['TABLE_NAME']);
                    $num_fields = mysql_num_fields($result);

                    for ($i = 0; $i < $num_fields; $i++)
                    {
                      while($row = mysql_fetch_row($result))
                      {
                        $return.= 'INSERT INTO `'. $v['TABLE_NAME'] .'` VALUES(';
                        for($j=0; $j<$num_fields; $j++)
                        {
                          //$row[$j] = addslashes($row[$j]);
                          $row[$j] = str_replace("'","\'",$row[$j]);
                          $row[$j] = str_replace("\n","\\n",$row[$j]);
                          $row[$j] = str_replace("\r","\\r",$row[$j]);
                          if (isset($row[$j])) { $return.= "'".$row[$j]."'" ; } else { $return.= "''"; }
                          if ($j<($num_fields-1)) { $return.= ','; }
                        }
                        $return.= ");\n";
                      }
                    }
                    $return.="\n\n";

                break;

            case 'VIEW':
                    if($view) break;

                    mysql_select_db('information_schema', $db);
                    $tb = mysql_query("select TABLE_NAME,VIEW_DEFINITION from VIEWS where TABLE_SCHEMA='". $name ."'");
                    while ($tmp = mysql_fetch_assoc($tb))
                        $return.= "CREATE VIEW `". $tmp['TABLE_NAME'] ."` AS ". $tmp['VIEW_DEFINITION'] .";\n\n";

                    $view = true;

                break;
        }
    }

    /*
    $handle = fopen($name.'_'.date('Ymd_His').'.sql','w+');
    fwrite($handle,$return);
    fclose($handle);
    */

    return $return;
}


/**
 * Бекап БД из MS SQL
 */
function backupMsSQL($host,$user,$pass,$name)
{
    $db = mssql_connect($host,$user,$pass);
    mssql_select_db($name, $db);



    $return .= "\r\n-- ТАБЛИЦЫ \r\n\r\n";

    $tb = mssql_query(
        "SELECT
         	--a.TABLE_NAME,
            '['+a.TABLE_SCHEMA+'].['+a.TABLE_NAME+']' as [table],
            '['+COLUMN_NAME+'] '+DATA_TYPE+
            (case when IS_NULLABLE = 'No' then ' NOT ' else ' ' end) +'NULL'+
            (case when COLUMN_DEFAULT IS NOT NULL THEN ' DEFAULT '+ COLUMN_DEFAULT else '' end)+
         	CASE when exists (
        		select id from syscolumns
                where object_name(id)=a.TABLE_NAME
                and name=COLUMN_NAME
                and columnproperty(id,name,'IsIdentity') = 1
        	) then
                ' IDENTITY(' +
                cast(ident_seed(a.TABLE_NAME) as varchar) + ',' +
                cast(ident_incr(a.TABLE_NAME) as varchar) + ')'
            else ''
            end as field
         from
         	INFORMATION_SCHEMA.columns a
         	LEFT OUTER JOIN INFORMATION_SCHEMA.tables b ON (a.TABLE_NAME = b.TABLE_NAME)
         where a.TABLE_CATALOG='". $name ."' and b.TABLE_TYPE='BASE TABLE'"
    );
    $result = array();
    while($tmp = mssql_fetch_assoc($tb))
    {
        $result[$tmp['table']][] = $tmp['field'];
    }
    foreach($result as $k => $v)
    {
        $return .= "CREATE TABLE ".$k." (\r\n\t". implode(",\r\n\t", $v) ."\r\n);\r\n";
    }



    $return .= "\r\n\r\n-- КЛЮЧИ \r\n\r\n";

    $result = array();
    $tb = mssql_query(
       "select
            '['+a.TABLE_SCHEMA+'].['+a.TABLE_NAME+']' as [table],
            a.CONSTRAINT_NAME,
            '['+b.COLUMN_NAME+']' as COLUMN_NAME
        from
        	INFORMATION_SCHEMA.table_constraints a
            LEFT OUTER JOIN INFORMATION_SCHEMA.key_column_usage b ON (a.CONSTRAINT_NAME = b.CONSTRAINT_NAME)
        where a.TABLE_CATALOG='". $name ."' AND a.CONSTRAINT_TYPE='PRIMARY KEY'
        ORDER BY b.ORDINAL_POSITION"
    );
    while ($tmp = mssql_fetch_assoc($tb))
    {
        $result[$tmp['table']]['CONSTRAINT_NAME'] = $tmp['CONSTRAINT_NAME'];
        $result[$tmp['table']]['COLUMN_NAME'][]   = $tmp['COLUMN_NAME'];
    }
    foreach($result as $k => $v)
    {
        $return .= "ALTER TABLE ".$k." ADD CONSTRAINT ".$v['CONSTRAINT_NAME']." PRIMARY KEY (". implode(",", $v['COLUMN_NAME']) .");\r\n";
    }



    $return .= "\r\n\r\n-- ПРЕДСТАВЛЕНИЯ \r\n\r\n";

    $tb = mssql_query("select convert(TEXT, VIEW_DEFINITION) as code from INFORMATION_SCHEMA.views where TABLE_CATALOG='". $name ."'");
    while ($tmp = mssql_fetch_assoc($tb))
    {
        $return .= iconv("cp1251","utf-8",$tmp['code']) ."\r\n----------".strlen($tmp['code'])."--------\r\n\r\n";
    }



    $return .= "\r\n\r\n-- ПРОЦЕДУРЫ и ФУНКЦИИ \r\n\r\n";

    $tb = mssql_query("select
                            convert(TEXT, ROUTINE_DEFINITION) as code,
                            LAST_ALTERED
                       from INFORMATION_SCHEMA.routines
                       where ROUTINE_CATALOG='". $name ."'
                       order by ROUTINE_TYPE");
    while ($tmp = mssql_fetch_assoc($tb))
    {
        $return.= "\r\n----------------------------------"
                 ."\r\n-- изменения от: ".date('Y-m-d H:i', strtotime($tmp['LAST_ALTERED']))
                 ."\r\n-- размер: ".strlen($tmp['code'])
                 ."\r\n----------------"
                 ."\r\n\r\n". iconv("cp1251","utf-8",$tmp['code']) ."\r\n";
    }

    return $return;
}

?>