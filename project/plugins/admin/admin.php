<?php
/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2016
 */

if(!defined('CS-SOFT')) die('access denited!');

class Admin extends Plugin
{    
    function index()
    {
        if(!DEBUG && elly::checkIP() != '127.0.0.1') return false;
                
        $this->loadTheme(false, 'index');
        $this->theme->header = elly::HeaderHTML('ELLY :: SQL Админ');
        
        $arr = $this->_sidebar();
        
        $this->theme->table('tb', $arr['BASE TABLE']);
        $this->theme->table('vw', $arr['VIEW']);
        $this->theme->table('pr', $arr['PROCEDURE']);
        $this->theme->table('fn', $arr['FUNCTION']);
                
        $this->theme->count_tb = count($arr['BASE TABLE']);
        $this->theme->count_vw = count($arr['VIEW']);
        $this->theme->count_pr = count($arr['PROCEDURE']);
        $this->theme->count_fn = count($arr['FUNCTION']);
        
        $this->theme->query_last = $_COOKIE['query_last'];
    }
    
    function show()
    {
        if(!DEBUG && elly::checkIP() != '127.0.0.1') return false;
        
        switch($_POST['type']) 
        {
            case 'sidebar-tb': if($_POST['data']) $html = $this->_show_data($_POST['name']); 
                               else               $html = $this->_show_table($_POST['name']); break;
            case 'sidebar-vw': if($_POST['data']) $html = $this->_show_data($_POST['name']); 
                               else               $html = $this->_show_view($_POST['name']); break;
            case 'sidebar-fn': $html = $this->_show_func($_POST['name']); break;
            case 'sidebar-pr': $html = $this->_show_func($_POST['name']); break;
        }
        $this->html($html);
    }
    
    function _err(){ return true; } //функция заглушка
    function query()
    {
        if(!DEBUG && elly::checkIP() != '127.0.0.1') return false;
        
        setcookie('query_last', $_POST['query'], time()+60*60*24*30);
        set_error_handler(array($this, '_err'), E_ALL); // глушу вывод ошибок из класса Debug
        $this->core->_init();                           // конект происходит теперь при первом обращении к бд
        if(!mssql_query(iconv("utf-8","cp1251", $_POST['query']))) {
        //if(!$this->core->query($_POST['query'])) {    // можно и так, но тогда срабатывают два обработчика ошибок
            $this->html('<h3 style="margin:0; color:#f55;">Ошибка запроса</h3>'. mssql_get_last_message());
            return;
        }
        
        restore_error_handler(); //восстанавливает предыдущий обработчик ошибок
        $tb = $this->core->sql(iconv("utf-8","cp1251", $_POST['query']))->range();        
        if(isset($tb[0])) 
        {
            $title = array_keys($tb[0]);
            $title[0] = $title[0] .' <span>Строк: '. count($tb) .'</span>';
            
            html::table($title, true);            
            foreach($tb as $v) html::table($v); 
            $this->html(html::table());
        }
        else $this->html('Пустая таблица'); 
    }
    
    
    private function _show_data($table)
    {
        $tb = $this->core->sql("SELECT TOP 20 * FROM $table")->range();               
        
        if(isset($tb[0])) 
        {
            html::table(array_keys($tb[0]), true);
            foreach($tb as $v) html::table($v); 
            return html::table('tb_col');
        }
        else return 'Пустая таблица';        
    }
    
    private function _show_table($table)
    {
        $tb = $this->core->sql(
            "SELECT 
             	COLUMN_NAME,DATA_TYPE, 
                case when IS_NULLABLE = 'No' then 1 else 0 end as nul,
                COLUMN_DEFAULT as def,
             	CASE when exists (
            		select id from syscolumns
                    where object_name(id)=TABLE_NAME
                    and name=COLUMN_NAME
                    and columnproperty(id,name,'IsIdentity') = 1
            	) then
                    ' IDENTITY(' + 
                    cast(ident_seed(TABLE_NAME) as varchar) + ',' + 
                    cast(ident_incr(TABLE_NAME) as varchar) + ')'
                else ''
                end as ident
             from 
             	INFORMATION_SCHEMA.columns 
             where TABLE_CATALOG='". dbNAME ."' and TABLE_NAME='$table'"
            )->range();
        foreach($tb as $v) html::table($v);
        $html = html::table('tb_row');
        
        $tb = $this->core->sql(
            "select 
                b.COLUMN_NAME,
                a.CONSTRAINT_TYPE, 
                a.CONSTRAINT_NAME                
            from 
            	INFORMATION_SCHEMA.table_constraints a
                LEFT OUTER JOIN INFORMATION_SCHEMA.key_column_usage b ON (a.CONSTRAINT_NAME = b.CONSTRAINT_NAME)
            where a.TABLE_CATALOG='". dbNAME ."' and a.TABLE_NAME='$table'
            ORDER BY b.ORDINAL_POSITION"
            )->range(); // AND a.CONSTRAINT_TYPE='PRIMARY KEY'
        foreach($tb as $v) html::table($v);
        $html .= '<h4>Ключи</h4>'. html::table();
        
        return $html;
        //$this->html($html);
    }
    
    private function _show_view($name)
    {
        ini_set('mssql.textlimit', 1048576);
        ini_set('mssql.textsize', 1048576);
        
        $txt = $this->core->sql("select convert(TEXT, object_definition(object_id(TABLE_NAME))) from INFORMATION_SCHEMA.views 
                                where TABLE_CATALOG='". dbNAME ."' and TABLE_NAME='$name'"
                                )->cell('computed');
        
        return '<pre class="editor">'. iconv('utf-8', 'cp1251', $txt) .'</pre>';
    }
    
    private function _show_func($name)
    {
        ini_set('mssql.textlimit', 1048576);
        ini_set('mssql.textsize', 1048576);
        
        $txt = $this->core->sql("select convert(TEXT, object_definition(object_id(ROUTINE_NAME))) from INFORMATION_SCHEMA.routines 
                                where ROUTINE_CATALOG='". dbNAME ."' and ROUTINE_NAME='$name'"
                                )->cell('computed');        
        //mb_strlen($txt)
        return '<pre class="editor">'. $txt .'</pre>';
    }
    
    private function _sidebar()
    {
        $tb = $this->core->sql("select TABLE_NAME,TABLE_TYPE from INFORMATION_SCHEMA.tables 
                              where TABLE_CATALOG='". dbNAME ."' order by TABLE_TYPE, TABLE_NAME"
                              )->range();
        $arr = array();
        foreach($tb as $v) 
        {
            $arr[$v['TABLE_TYPE']][]['name'] = $v['TABLE_NAME'];
        }
        $tb = $this->core->sql("select ROUTINE_NAME, ROUTINE_TYPE from INFORMATION_SCHEMA.routines 
                              where ROUTINE_CATALOG='". dbNAME ."' order by ROUTINE_NAME"
                              )->range();
        foreach($tb as $v) 
        {
            $arr[$v['ROUTINE_TYPE']][]['name'] = $v['ROUTINE_NAME'];
        }
        return $arr;
    }
}


class html
{
    static private $table = '';
    static private $count = 1;
    
    static function table($row = NULL, $isTitle = false)
    {
        if(!is_array($row)) 
        {
            $class  = is_null($row) ? '' : ' class="'.$row.'"';
            $result = "<table border='1'$class>\r\n". self::$table ."</table>\r\n";
            self::$table = '';
            return $result;
        }
        $td = $isTitle ? 'th' : 'td';
        foreach($row as $item) 
        {
            if(is_array($item)) 
            {
                $btn = '';
                foreach($item as $k => $v) {
                    $v = sprintf($v, self::$count);
                    $btn .= "\t\t". '<input type="button" value="'. $k .'" onclick="'. $v .'">' ."\r\n";
                } 
                $html .= "\t<$td>\r\n". $btn ."\t</$td>\r\n";  
            }
            else $html .= "\t<$td>". $item . "</$td>\r\n";            
        }
        self::$table .= '<tr id="tr_'. self::$count .'">'. "\r\n". $html .'</tr>' ."\r\n";
        self::$count ++;
    }
    
    static function select($name, $arr, $id = '')
    {
        foreach($arr as $k => $v)
        {
            $activ = ($k == $id) ? ' selected="selected"' : '';
            $html .= "\r\n\t". '<option value="'. $k .'"'. $activ .'>'. $v .'</option>';
        }
        echo '<select name="'. $name .'" class="dddd">'. $html ."\r\n" .'</select>' ."\r\n";
    } 
}
?>