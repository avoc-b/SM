<?php
/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2016
 */

if(!defined('CS-SOFT')) die('access denited!');

class Install extends Plugin
{       
    private $create   = 0;
    private $error    = '';    
    private $backup   = 'project/backup/';
    private $fileName = 'config.php';
    
    function index()
    {        
        $f = new ConfigPHP($this->fileName);
        
        //if(is_file('.htaccess')) die('install: access denited');
        if($f->get('SERVER_ADDR') == $_SERVER['SERVER_ADDR']) die('install: access denited');
        
        if($_GET['action'] == 3)     // завершение работы с установщиком
        {
            //if(chmod($this->fileName, 0777) == false) die(print_r(stat($this->fileName),1));//die('install: chmod error');
            $f->add('SERVER_ADDR', $_SERVER['SERVER_ADDR']);
            $f->write();
            //chmod($this->fileName, 0644); // Доступ на запись и чтение для владельца, доступ на чтение для других
            //file_put_contents('.htaccess', $text); //'deny from all');
            //header('Location: '. HOME);
            die('install: finished');
        }
        
        if(count($_POST))
        {
            // Сохранение конфигурации
            //chmod($this->fileName, 0777);        
            $_POST['LOGIN'] = ($_POST['LOGIN'])? 1:0;
            foreach($_POST as $k => $v) 
            {        
                $f->set($k, $v);
            }
            $f->write();
            
            // Проверки БД
            $this->_issetDB();
        }
                
        if(!empty($_GET['action'])) switch($_GET['action']) 
        {
            case 1:     // создание пустой базы данных                    
                    $this->_createTable();                
                    $this->error  = 'База данных создана, но в ней отсутсвуют какие-либо таблицы!';
                    $this->create = 2;                                                    
                    break;
            
            case 2:     // восстановление базы данных
                    if($this->create == 1) 
                    {
                        $this->_createTable();
                        if($_POST['dbTYPE'] == 'mysql')     mysql_select_db($_POST['dbNAME']);
                        elseif($_POST['dbTYPE'] == 'mssql') mssql_select_db($_POST['dbNAME']);
                    }
                    $this->_loadData();                                                 
                    break;
        }        
        if(!$this->error && $this->create == -1) 
        {
            $this->error  = 'Все готово к разработке...';
        }
        
                
        $this->loadTheme(false, 'index');                
        $this->theme->php(array(
                                    'create'=> $this->create,
                                    'error' => $this->error, 
                                    'f'     => $f,
                                ));
        $this->theme->header  = elly::HeaderHTML('ELLY :: Установка');
    }
    
        
    function listdb()
    {
        foreach (glob($this->backup ."*.zip") as $file) {
            $filename = basename($file);
            $options .= '<option value="'.$filename.'">'. $filename .' ('. filesize($file) .")</option>\n";
        }
        
        $this->loadTheme();
        $this->theme->list = $options;
    }
    
    
    
    private function _issetDB()
    {           
        if(empty($_POST['dbNAME']))
        {
            $this->error  = 'Неуказана база данных!';
            //$this->create = 1;
        } 
        elseif($_POST['dbTYPE'] == 'mysql') 
        {
            if(!@mysql_connect($_POST['dbHOST'], $_POST['dbUSER'], $_POST['dbPASS']))
                $this->error  = 'Отсутсвует подключение к серверу '. $_POST['dbTYPE'] .'!'; 
            elseif(!mysql_select_db($_POST['dbNAME'])) {
                $this->error  = 'Данная база данных отсутсвует на сервере!';
                $this->create = 1;
                }
            else {
                $tb = mysql_fetch_row(mysql_query("SHOW TABLES"));
                if(empty($tb[0]))
                {
                    $this->error  = 'В базе данных отсутсвуют какие-либо таблицы!';
                    $this->create = 2;
                }
                else $this->create = -1;
            }        
        }
        elseif($_POST['dbTYPE'] == 'mssql')
        {
            if(!@mssql_connect($_POST['dbHOST'], $_POST['dbUSER'], $_POST['dbPASS']))
                $this->error  = 'Отсутсвует подключение к серверу '. $_POST['dbTYPE'] .'!'; 
            elseif(!mssql_select_db($_POST['dbNAME'])) {
                $this->error  = 'Данная база данных отсутсвует на сервере!';
                $this->create = 1;
                }
            else {
                $tb = mssql_fetch_row(mssql_query("select * from INFORMATION_SCHEMA.columns"));
                if(empty($tb[0]))
                {
                    $this->error  = 'В базе данных отсутсвуют какие-либо таблицы!';
                    $this->create = 2;
                }
                else $this->create = -1;
            }
        }
    }
    
    private function _createTable()
    {
        if($_POST['dbTYPE'] == 'mysql')     mysql_query("CREATE DATABASE ". $_POST['dbNAME']. " CHARACTER SET utf8 COLLATE utf8_general_ci;");
        elseif($_POST['dbTYPE'] == 'mssql') mssql_query("CREATE DATABASE ". $_POST['dbNAME']);    
    }
    
    private function _loadData()
    {
        $file = $this->backup.$_POST['file'];
        
        if (!file_exists($file))
        {
            $this->error  = 'Файл недоступен!';
            $this->create = 1;
        }
        
        //создаем новый объект ZipArchive
        $zip = new ZipArchive;
        //используем метод open(), но теперь используем ключ ZipArchive::CREATE
        //который говорит, что архив нужно создать
        //а первым параметром передаем название архива] 
        $res = $zip->open($this->backup.$_POST['file'], ZipArchive::CHECKCONS);
        if ($res === TRUE) {
        	//вот это интересная функция, которая, использует содержимое файла
            //для добавления его в архив
            //$zip->addFromString($back_name, $return);
            //$dump = $zip->getFromName('ecobank_testing_20140226_23.sql');
            $dump = $zip->getFromName($zip->getNameIndex(0));
            
            //$zip->extractTo('backup');
            //$dump = file_get_contents('backup/ecobank_testing_20140226_23.sql');
            
            $dump = explode(";", $dump);
            
            if($_POST['dbTYPE'] == 'mysql') mysql_query("SET NAMES utf8");
            foreach($dump as $sql)
            {
                if($_POST['dbTYPE'] == 'mysql')     mysql_query($sql);
                elseif($_POST['dbTYPE'] == 'mssql') mssql_query($sql); 
            }                    
            
            //закрываем работу с архивом
            $zip->close();
        }   
        $this->error  = 'База развернута на сервере...';
        $this->create = -1;
    }
    
    function vidget()
    {
        return '<font color="#f55">--ViDJeT--</font>';
    }
    function vidget2()
    {
        return '--ViDJeT--<b>№2</b>--';
    }
}




//////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////

class ConfigPHP
{
    static private $_instance;
    
    private $arrIN  = array();
    private $arrOUT = null;
    private $arrAdd = array();
    private $fileName;
    private $fileDump;        
    
    function __construct($filename)
    {
        $this->fileName = $filename;
    }
    
    static function getInstance($filename)
    {
        if (null === self::$_instance) self::$_instance = new self($filename);
        return self::$_instance;
    }
    
    /**
     * Установление значения конфигурационной переменной,
     * после выставления всех переменных необходимо вызвать метод write()
     */
    function set($key, $value)
    {
        $this->arrIN[$key] = $value;
    }
    
    /**
     * Добавляет значения конфигурационной переменной если её нет, если есть - обновляет 
     * после выставления всех переменных необходимо вызвать метод write()
     */
    function add($key, $value)
    {
        if($this->get($key))
             $this->arrIN[$key]  = $value;
        else $this->arrAdd[$key] = $value;
    }
    
    /**
     * Получение переменной, прочитанной из конфиг-файла
     */
    function get($key)
    {
        $this->_read();
        return $this->arrOUT[$key];
    }
    
    function getArr()
    {
        $this->_read();
        return $this->arrOUT;
    }
        
    private function _read()
    {
        if(is_null($this->arrOUT))
        {
            $this->fileDump = file_get_contents($this->fileName);
            preg_match_all("~define\('(\w+)',\s*'?([^']*)'?\);~is", $this->fileDump, $tmp); //, PREG_OFFSET_CAPTURE); 
            
            $this->arrOUT = array_combine(array_values($tmp[1]),array_values($tmp[2]));   
        }
    }
    
    /**
     * Сохранение всех настроек в конфиг-файле
     */
    function write()
    {
        $this->_read();        
        foreach($this->arrOUT as $key => $val)
        {
            if(isset($this->arrIN[$key]))
            {
                $gg = is_numeric($this->arrIN[$key]) ? '' : "'";
                $val = preg_quote($val);
                $this->fileDump = preg_replace("~define\('$key',\s*'?$val'?\);~is", "define('$key',$gg{$this->arrIN[$key]}$gg);", $this->fileDump);
            }  
        }
        if(count($this->arrAdd))
        {
            $isCorect = (strpos($this->fileDump, '?>') !== false);
        
            foreach($this->arrAdd as $key => $val)
            {
                if($isCorect) 
                     $this->fileDump = str_replace('?>', "define('$key','$val');\r\n?>", $this->fileDump) ;
                else $this->fileDump.= "\r\ndefine('$key','$val');";            
            }
        }
        file_put_contents($this->fileName, $this->fileDump);
        $this->arrOUT = null;
    }    
}
//////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////
?>