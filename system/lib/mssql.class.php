<?php
/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2013
 */


class CDatabase
{
    static public $execTime = 0;    
    
    private $sqlArr = array();
    private $sql;
    private $connect;
    
    private $tables;
    
    function _init($settings = array())
    {
        if(count($settings) < 1) {
            $settings = array('dbhost' => dbHOST, 'dbuser' => dbUSER, 'dbpass' => dbPASS, 'dbname' => dbNAME);
        }  
        $this->connect = @mssql_connect($settings['dbhost'], $settings['dbuser'], $settings['dbpass']);
        if(!$this->connect) die(sprintf(elly::$lang['connect.error'], dbTYPE));   
        
        mssql_select_db($settings['dbname'], $this->connect);
        $this->_structure($settings['dbname']);
    }
    
    private function _structure($table)
    {        
        $this->tables = array();
        $tb = mssql_query("select TABLE_NAME, COLUMN_NAME, DATA_TYPE from INFORMATION_SCHEMA.columns where TABLE_CATALOG='". $table ."'");  
        while ($tmp = mssql_fetch_assoc($tb))
            $this->tables[$tmp['TABLE_NAME']][$tmp['COLUMN_NAME']] = strtoupper($tmp['DATA_TYPE']); 
    } 
    
    /**
     * Обработка всех SQL-запросов 
     * @return resource
     */ 
    function query($sql) 
    {
        $pDebug = CDebug::getInstance();
        
        if(!$this->connect) $this->_init();
        
        if($pDebug->Enable) {
            list($usec, $sec) = explode(' ', microtime());
    		$timeStart = (float) $sec + (float) $usec;        
            }
            
        $result = mssql_query(iconv("utf-8","cp1251",$sql));
          
        if($pDebug->Enable) {
            list($usec, $sec) = explode(' ', microtime());
    		$timeStop = (float) $sec + (float) $usec;
    		self::$execTime += ($timeStop - $timeStart);
            }
            
        if ($result) {
            if($pDebug->Enable && is_object($pDebug)) $pDebug->group('SQL',$sql, true,true); 	
            return $result;
        }
        elseif(is_object($pDebug)) $pDebug->errorSQL(elly::$lang['sql.error'], $sql, dbTYPE);  
    }
        
    private function _clear()
    {
        $this->sqlArr = array();
    }
        
    function table($value)  { $this->_clear(); $this->sqlArr[__function__] = $value; return $this; }    
    function where($value)  { $this->sqlArr[__function__] = $value; return $this; } 
    function sort($value)   { $this->sqlArr[__function__] = $value; return $this; }     
    function group($value)  { $this->sqlArr[__function__] = $value; return $this; }
    function sql($sql)      { $this->sql = $sql; return $this; }
    //function limit($value)  { $this->sqlArr[__function__] = $value; return $this; } 
    function limit($start,$count)  
    { 
        $this->sqlArr[__function__ .'_start'] = (int)$start; 
        $this->sqlArr[__function__ .'_count'] = (int)$count;
        return $this; 
    }
    
    private function _select($fields)
    {
        if(!empty($this->sql)) return $this->query($this->sql);
        
        $sql = "SELECT $fields FROM ". $this->sqlArr['table'];
        if(!empty($this->sqlArr['where'])) $sql .= ' WHERE '. $this->sqlArr['where'];
        if(!empty($this->sqlArr['group'])) $sql .= ' GROUP BY '. $this->sqlArr['group'];
        if(!empty($this->sqlArr['sort']))  $sql .= ' ORDER BY '. $this->sqlArr['sort'];         
        //if(!empty($this->sqlArr['limit_start']) && !empty($this->sqlArr['limit_count'])) $sql .= ' LIMIT '. $this->sqlArr['limit_start'] .','. $this->sqlArr['limit_count'];        
        
        if(isset($this->sqlArr['limit_start']) && !empty($this->sqlArr['limit_count']))
        {
            $where = empty($this->sqlArr['where']) ? '' : ' WHERE '. $this->sqlArr['where'];
            $and   = empty($where) ? ' WHERE ' : ' AND ';
            $atr   = '';
            if(!empty($this->sqlArr['group'])) $atr .= ' GROUP BY '. $this->sqlArr['group'];
            if(!empty($this->sqlArr['sort']))  $atr .= ' ORDER BY '. $this->sqlArr['sort'];             
            
            $sql = "SELECT TOP ". $this->sqlArr['limit_count'] ." $fields FROM ". $this->sqlArr['table'] .
                   $where . $and ."codeid NOT IN( ".
                        "SELECT top ". $this->sqlArr['limit_start'] ." codeid FROM ". $this->sqlArr['table'] . $where . $atr .
                   ")". $atr;
        }      
        return $this->query($sql);
    }
    
    function range($fields = '')
    {        
        $fields = ($fields) ? $fields : '*';
        
        $result = array();
        if($table = $this->_select($fields))
        {
            while ($tmp = mssql_fetch_assoc($table)) {
                foreach($tmp as $key=>$val) $value[$key] = trim(iconv("cp1251","utf-8",$val)); 
                $result[] = $value;
            }          
            mssql_free_result($table);    
        }
        return $result;
    } 
    
    function row($fields = '')
    {
        $fields = ($fields) ? $fields : '*';
        
        $result = array();
        if($table = $this->_select($fields))
        {
            if($tmp = mssql_fetch_assoc($table))
            foreach($tmp as $key=>$val) $result[$key] = trim(iconv("cp1251","utf-8",$val)); 
            mssql_free_result($table);
        }        
        return $result;
    }
    
    function col($field, $key = NULL)
    {
        $result = array();
        if(is_null($key)) {
            if($table = $this->_select($field))
                while ($tmp = mssql_fetch_assoc($table)) $result[] = trim(iconv("cp1251","utf-8",$tmp[$field]));
            }
        else {
            if($table = $this->_select($field .','. $key))
                while ($tmp = mssql_fetch_assoc($table)) $result[$tmp{$key}] = trim(iconv("cp1251","utf-8",$tmp[$field]));
            }                  
        if($table) mssql_free_result($table);
        return $result;
    }
    
    function cell($field)
    {
        $result = '';
        if($table = $this->_select($field))
        {
            if (mssql_num_rows($table)>0) $result = mssql_result($table,0,0);
            mssql_free_result($table);    
        }        
        return trim(iconv("cp1251","utf-8",$result));  
    }
    
    function add($data)
    {           
        $fields = '';
        $values = '';
        $data   = $this->_security($this->sqlArr['table'], $data);
        foreach ($data as $f => $v)
        {
            //if(is_string($v)) $v = iconv("utf-8", "cp1251", $v);
            $fields .= "[$f],";
            $values .= "'$v',";
        }
        $fields = substr($fields, 0, -1);
        $values = substr($values, 0, -1);
        $insert = "INSERT INTO ". $this->sqlArr['table'] ." ({$fields}) VALUES({$values})";
        
        if(self::query($insert) > 0) return $this->mssql_insert_id();
        else return 0;
    }
    
    function mssql_insert_id() { 
        $id = 0; 
        $res = mssql_query("SELECT @@identity AS id"); 
        if ($row = mssql_fetch_array($res, MSSQL_ASSOC)) { 
            $id = $row["id"]; 
        } 
        return $id; 
    } 
    
    function delete()
    {
        $sql = "DELETE FROM ". $this->sqlArr['table'];
        $sql = empty($this->sqlArr['where']) ? $sql : $sql .' WHERE '.    $this->sqlArr['where'];
        $sql = empty($this->sqlArr['group']) ? $sql : $sql .' GROUP BY '. $this->sqlArr['group'];
        $sql = empty($this->sqlArr['sort'])  ? $sql : $sql .' ORDER BY '. $this->sqlArr['sort'];        
        $sql = empty($this->sqlArr['limit']) ? $sql : $sql .' LIMIT '.    $this->sqlArr['limit'];
        
        return $this->query($sql);
    }
    
    function update($data)
    {
        $sql  = "UPDATE ". $this->sqlArr['table'] ." SET ";
        $data = $this->_security($this->sqlArr['table'], $data);
        foreach( $data as $field => $value ) $sql .= "[".$field."]='$value',";        
        $sql = substr($sql, 0, -1);
        $sql = empty($this->sqlArr['where']) ? $sql : $sql .' WHERE '. $this->sqlArr['where'];
        
        return $this->query($sql);
    }
    
    /**
     * Валидация переменной
     * @return String 
     */
    function validation($data)
    {           
        if(get_magic_quotes_gpc()) $data = stripslashes($data);
        return str_replace("'", "''", $data);      
    }
        
    function _security($table, $array)
    {        
        if(!empty($this->tables[$table]))
        {
            foreach($array as $k => $v)
            {
                $typeField = $this->tables[$table][$k];
                switch($typeField)
                {
                    case 'INT':
                    case 'TINYINT':
                    case 'BIGINT':
                        $array[$k] = (int)$v;
                        break;
                    case 'FLOAT':
                    case 'DOUBLE':
                        $array[$k] = (double)$v;
                        break;
                    case 'CHAR':
                    case 'VARCHAR':
                        $quotes = array ("\x27", "\x22", "\x60", "\t", "\n", "\r", "*", "%", "<", ">", "?", "!" );
                        $v = trim( strip_tags($v) );
                        $v = str_replace( $quotes, '', $v );                        
                        $array[$k] = $v ;//mysql_real_escape_string($v);
                        break;
                    case 'TEXT':
                        $search = array(
                                            '@<script[^>]*?>.*?</script>@si',   // javascript
                                            '@<[\/\!]*?[^<>]*?>@si',            // HTML теги
                                            '@<style[^>]*?>.*?</style>@siU'     // теги style
                                        );                        
                        $array[$k] = mysql_real_escape_string(preg_replace($search, '', $v));
                        break;
                    case 'DATE':
                        if(!empty($v)) $array[$k] = date('Y-m-d', strtotime($v));
                        break;
                    case 'TIME':
                        if(!empty($v)) $array[$k] = date('H:i:s', strtotime($v));
                        break;
                    case 'DATETIME':
                        if(!empty($v)) $array[$k] = date('Y-m-d H:i:s', strtotime($v));
                        break;
                }
            }
        }
        return $array;
    }   
    
    
    /**
     * Вызов хранимой процедуры MSSQL
     * @name   String название процедуры
     * @data   array значения в виде field => value
     * @return mixed
     */
    function procedure( $name, $data, $res='')
    {
        $pDebug = CDebug::getInstance();
                
        $proc = mssql_init($name, $this->db);   //инициализирует хранимую процедуру
        foreach( $data as $field => $value )
        {
            $data[$field] = self::validation($value);  //конвентирование в win1251 тамже
            $arr          = explode('_',$field);
            if(count($arr) > 1){
                $type  = array_pop($arr);
                $field2 = implode('_',$arr);
                switch($type){
                    //ИНТЕРЕСНЫЙ МОМЕНТ!
                    //обязательно передавать надо в виде массивной ячейки $data[$field], т.к. преобразуется на люнексе в ссылку
                    case 'f':                    
                    case 'flt':
                    case 'num':     mssql_bind($proc, '@'.$field2, $data[$field], SQLFLT8); break;      
                    case 'v':
                    case 'var':
                    case 'varchar': mssql_bind($proc, '@'.$field2, $data[$field], SQLVARCHAR, FALSE, FALSE, 500);  break;
                    case 't':
                    case 'text':    mssql_bind($proc, '@'.$field2, $data[$field], SQLTEXT);  break;
                }
            }
            else mssql_bind($proc, '@'.$field, $data[$field], SQLVARCHAR, FALSE, FALSE, 500);                        
        }
        if(!empty($res)) mssql_bind($proc, '@'.$res, $result, SQLVARCHAR, TRUE, FALSE, 500);
                  
        if(mssql_execute($proc)){               //выполняет хранимую процедуру
            mssql_free_statement($proc);        //освобождает память инструкций
            if($pDebug->Enable && is_object($pDebug)){ 
                $pDebug->groupAdd( $pDebug->groupCreate('SQL',true,true), elly::$lang['procedure.end'].' '.$name );
                } 
            return $result;
            }
        else {
            $pDebug->groupAdd( $pDebug->groupCreate(elly::$lang['sql.error'],true), elly::$lang['procedure.end'].' '.$name );
            return false;
            }
    }
    
    /**
     * Вызов хранимой процедуры MSSQL через EXEC
     * @name   String название процедуры
     * @data   array значения в виде field => value
     * @out    array выходные параметры (при необходимости), название переменной => тип данных
     * @return mixed
     */
    function exec( $name, $data, $out=false )
    {
        $sql = array();
        foreach($data as $field => $value)
        {
            $value = $this->validation($value);
            $value = (substr($value, 0, 2) == '0x' /*|| is_numeric($value)*/ || $value == 'NULL')? $value : "'$value'";
            $sql[] = '@'. $this->validation($field) ."=". $value;
        } 
        $exec = "EXEC $name ". implode(',', $sql);
        //$exec = iconv("utf-8","cp1251",$exec);

        if(is_array($out))
        {
            foreach($out as $name=>$type) {
                $exec_declare[] = '@'. $name .' '. $type;
                $exec_select[]  = '@'. $name .' AS '. $name;
                $exec .= ", @$name=@$name OUTPUT";
            }
            $exec = "DECLARE ". implode(',', $exec_declare) ."; ". $exec ."; SELECT ". implode(',', $exec_select) .";";  
        } 
        //die($exec);
        $table = $this->query($exec);
        if(is_array($out))
        {
            $result = mssql_fetch_assoc($table);
            foreach($result as $key=>$val) $result[$key] = iconv("cp1251","utf-8",$val); 
            mssql_free_result($table);
        }
        return $result;   
    }
    
    function _securityField($v, $typeField, $hard = false)
    {
        switch($typeField)
        {
            case 'INT':
            case 'TINYINT':
            case 'BIGINT':
                $result = (int)$v;
                break;
            case 'FLOAT':
            case 'DOABLE':
                $result = (double)$v;
                break;
            case 'CHAR':
            case 'VARCHAR':
                if($hard) {                    
                    $v = trim(strip_tags($v));
                    $v = str_replace( array("\x27","\x22","\x60","\t","\n","\r","*","%","<",">","?","!" ), '', $v );
                }                        
                $result = $v;//mysql_real_escape_string($v);
                break;
            case 'TEXT':
                if($hard) $v = preg_replace(array(
                                                    '@<script[^>]*?>.*?</script>@si',   // javascript
                                                    /*'@<[\/\!]*?[^<>]*?>@si',*/            // HTML теги
                                                    '@<style[^>]*?>.*?</style>@siU'     // теги style
                                                ), '', $v);
                                   
                $result = $v; //mysql_real_escape_string($v);
                break;
            case 'DATE':
                if(!empty($v)) $result = date('Y-m-d', strtotime($v));
                break;
            case 'TIME':
                if(!empty($v)) $result = date('H:i:s', strtotime($v));
                break;
            case 'DATETIME':
                if(!empty($v)) $result = date('Y-m-d H:i:s', strtotime($v));
                break;
        }
        
        return $result;
    }
    
    /**
     * Эксперементальная функция подготавливающая данные для отдачи в JSON 
     */
    function toJson($data, $tableName)
    {       
        /*
        $text   = array('CHAR', 'VARCHAR', 'NVARCHAR', 'TEXT');
        $date   = array('DATETIME', 'DATE', 'TIME');
        */
        if(is_array($data))  
        foreach($data as $k => $v)
        {
            if(is_array($v)) $data[$k] = $this->toJson($v, $tableName);
            else
            {
                $type = $this->tables[$tableName][$k];
                //debug($k);
                //debug($v);
                //debug($type);
                if(empty($type)) if(is_numeric($v)) $data[$k] = floatval($v);                
                if(in_array($type, array('INT', 'TINYINT', 'BIGINT'))) $data[$k] = intval($v);
                elseif(in_array($type, array('FLOAT', 'DOUBLE', 'MONEY'))) $data[$k] = floatval($v);
            }
                       
        }
        return $data;
    }
}

?>