<?php
/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */
        
     /* Пример использования
    
    $pHtml = new Html;    
    for($i=0; $i<10; ++$i) {
        $link = array( 'Изменить' => "elly.Ajax('sdf80kjsd0f978')", 'Удалить' => "$('#tr_%d').remove()" );
        $mass = array( true, $i+1, 'myoption', 15, rand(10,99), $link );          
        $pHtml->table($mass);
    }
    $pHtml->table();
    
    
    $arr = array( 1 => 'aaaa', 2 => 'bbbb' );
    $pHtml->select('sel_1', $arr, 2);    
    
    
    echo $pHtml->paginator(2,250,'dfdf',true);
    */
    
class Html
{
    private $table = '';
    private $count = 1;
    
    /**
     * Вывод таблицы
     * @row mixed при передачи массива добовляется строка,
     *      при пустом аргументе возвращается html 
     */
    function table($row = NULL)
    {
        if(is_null($row)) 
        {
            return "<table border='1'>\r\n". $this->table ."</table>\r\n";
        }
        foreach($row as $item) 
        {
            if(is_array($item)) 
            {
                $btn = '';
                foreach($item as $k => $v) {
                    $v = sprintf($v, $this->count);
                    $btn .= "\t\t". '<input type="button" value="'. $k .'" onclick="'. $v .'"/>' ."\r\n";
                } 
                $html .= "\t". '<td>'. "\r\n". $btn ."\t" .'</td>' ."\r\n";  
            }
            elseif(is_bool($item)) 
            {
                $activ = ($item) ? ' checked="checked"' : ''; 
                $html .= "\t". '<td><input type="checkbox"'. $activ .' /></td>' ."\r\n";
            }
            else $html .= "\t". '<td>'. $item . '</td>' ."\r\n";            
        }
        $this->table .= '<tr id="tr_'. $this->count .'">'. "\r\n". $html .'</tr>' ."\r\n";
        $this->count ++;
    }
    
    /**
     * Вывод выпадающего списка
     * @name  String название
     * @arr   array строки в виде field => value
     * @id    int активная строка
     * @class String стиль html
     */
    function select($name, $arr, $id = '', $class)
    {
        foreach($arr as $k => $v)
        {
            $activ = ($k == $id) ? ' selected="selected"' : '';
            $html .= "\r\n\t". '<option value="'. $k .'"'. $activ .'>'. $v .'</option>';
        }
        return '<select name="'. $name .'" class="'. $class .'">'. $html ."\r\n" .'</select>' ."\r\n";
    }
    
    
    /**
     * Вывод заголовков HTML
     * @title    String приставка к заголовоку страницы
     * @keywords String ключевые поля
     * @img      String ссылка к главной картинке страницы
     */
    function header($title='', $keywords='', $img='')
    {
    	if(!empty($title)) { 
    		$result  = '<title>'.TITLE.' '.$title."</title>\r\n";		
    		$result .= '<meta property="og:title" content="'.htmlspecialchars($title).'" />';
    		}
    	else
    	$result  = '<title>'.TITLE.'</title>';
        //$result .= '<script type="text/javascript" src="'.HOME.'/system/res/js/core.js"></script>';
    	
    	if(!empty($img))
    	$result .= '
    	<meta property="og:description" content="" />
    	<meta property="og:image" content="'.HOME.'/'.$img.'" />
    			   ';
    								  
    	if(!empty($keywords))
    	$result .= '<meta name="keywords" content="'.htmlspecialchars($keywords).'">
    			   ';        
    	
    	$result .= '        
    	<meta name="description" content="">
    	<meta name="generator" content="Elly Framework">       
    	
    	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    			   ';
    	return $result;
    }    
    
    /**
     * Возвращает html-строку с Пагинатором
     * @page  String текущая страница
     * @count int количество
     * @link  String адрес
     * @crypt bool шифрованная ссылка или нет
     */
    function paginator($page, $count, $link, $crypt = false) 
    {
        if($count > 1) {
            $paginator ='';
            if ($page > 3) $paginator .= ' <a class="page" href="'.$this->_link($link,1,$crypt).'">&laquo;</a> ';
            for ($i = ($page - 2); $i < ($page + 5); $i++) {
                if ($i > 0 AND $i < $count+1) {
                    if ($i == ($page + 1)) $alink = ' <b>'.$i.'</b> ';
                    else $alink = ' <a class="page" href="'.$this->_link($link,$i,$crypt).'">'.$i.'</a> ';
                    $paginator .= $alink;
                }
            }
            if ($page < $count-4) $paginator .= '<a class="page" href="'.$this->_link($link,$count,$crypt).'">&raquo;</a> ';
            return "Страницы: ".$paginator;
        }
    }
    
    private function _link($str, $id, $crypt)
    {
        if($crypt) {
            $pCore = CCore::getInstance();
            return 'index.php?go='.$pCore->encrypt($link.$id);
            }
        else return $link . $id;
    }
}

?>