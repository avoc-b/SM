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
    
    function _init($settings = array())
    {
        if(count($settings) < 1) {
            $settings = array('dbhost' => dbHOST, 'dbuser' => dbUSER, 'dbpass' => dbPASS, 'dbname' => dbNAME);
        }  
        $this->connect = @mysql_connect($settings['dbhost'], $settings['dbuser'], $settings['dbpass']);
        if(!$this->connect) die(sprintf(elly::$lang['connect.error'], dbTYPE));
        if(!mysql_query("SET NAMES utf8")) die(elly::$lang['connect.utf']);     
        
        $this->_structure($settings['dbname']);               
        mysql_select_db($settings['dbname'], $this->connect);  
    }
    
    private function _structure($table)
    {                
        global $_TABLES;
         
        mysql_select_db('information_schema', $this->connect);
        
        $_TABLES = array();
        $tb = mysql_query("select TABLE_NAME, COLUMN_NAME, DATA_TYPE from COLUMNS where TABLE_SCHEMA='". $table ."'");  
        while ($tmp = mysql_fetch_assoc($tb))
            $_TABLES[$tmp['TABLE_NAME']][$tmp['COLUMN_NAME']] = strtoupper($tmp['DATA_TYPE']);
        
        //mysql_select_db($table, $this->connect);
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
            
        $result = mysql_query($sql);
          
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
        if(isset($this->sqlArr['limit_start']) && !empty($this->sqlArr['limit_count'])) $sql .= ' LIMIT '. $this->sqlArr['limit_start'] .','. $this->sqlArr['limit_count'];        
          
        return $this->query($sql);
    }
        
    function range($fields = '')
    {        
        $fields = ($fields) ? $fields : '*';
        
        $result = array();
        if($table = $this->_select($fields))
        {
            while ($tmp = mysql_fetch_assoc($table)) $result[] = $tmp;
            mysql_free_result($table);    
        }
        return $result;
    } 
    
    function row()
    {
        if($table = $this->limit(0,1)->_select('*'))
        {
            $result = mysql_fetch_assoc($table);
            mysql_free_result($table);
        }        
        return $result ? $result : array();
    }
    
    function col($field, $key = NULL)
    {
        $result = array();
        if(is_null($key)) {
            if($table = $this->_select($field))
                while ($tmp = mysql_fetch_assoc($table)) $result[] = $tmp[$field];
            }
        else {
            if($table = $this->_select($field .','. $key))
                while ($tmp = mysql_fetch_assoc($table)) $result[$tmp{$key}] = $tmp[$field];
            }                  
        if($table) mysql_free_result($table);
        return $result;
    }
    
    function cell($field)
    {
        $result = '';
        if($table = $this->limit(0,1)->_select($field))
        {
            if (mysql_num_rows($table)>0) $result = mysql_result($table,0,0);
            mysql_free_result($table);    
        }        
        return $result;  
    }
    
    function add($data)
    {           
        $fields = '';
        $values = '';
        $data   = $this->_security($this->sqlArr['table'], $data);
        foreach ($data as $f => $v)
        {
            $fields .= "`$f`,";
            $values .= "'$v',";
        }
        $fields = substr($fields, 0, -1);
        $values = substr($values, 0, -1);
        $insert = "INSERT INTO ". $this->sqlArr['table'] ." ({$fields}) VALUES({$values})";
        
        if(self::query($insert) > 0) return mysql_insert_id();
        else return 0;
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
        foreach( $data as $field => $value ) $sql .= "`".$field."`='$value',";        
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
        return mysql_real_escape_string($data);      
    }
        
    function _security($table, $array)
    {
        global $_TABLES;
        
        if(!empty($_TABLES[$table]))
        {
            foreach($array as $k => $v)
            {
                $array[$k] = $this->_securityField($v, $_TABLES[$table][$k]);                
            }
        }
        return $array;
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
                $result = mysql_real_escape_string($v);
                break;
            case 'TEXT':
                if($hard) $v = preg_replace(array(
                                                    '@<script[^>]*?>.*?</script>@si',   // javascript
                                                    '@<[\/\!]*?[^<>]*?>@si',            // HTML теги
                                                    '@<style[^>]*?>.*?</style>@siU'     // теги style
                                                ), '', $v);
                                   
                $result = mysql_real_escape_string($v);
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
}
?>