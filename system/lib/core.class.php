<?php
/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2013
 * @version 3.3.0 (19.08.2016)
 */
 
define('VERSION_CORE','3.3 (19.08.2016)');


include_once('config.php');
if(defined('dbTYPE') && dbTYPE != '') include_once(dbTYPE .'.class.php');

function autoload($class) { include_once 'system/lib/'. $class .'.class.php'; }
spl_autoload_register('autoload');/**/

//регистрация домашней страницы (корня сайта)
$base_url  = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
$base_url .= "://" . $_SERVER['HTTP_HOST'];
$base_url .= str_replace('/'.basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);

define('HOME', $base_url);



 
class CCore extends CDatabase
{       
    static private $_instance;
    
    static function getInstance()
    {
        if (null === self::$_instance) self::$_instance = new self();
        return self::$_instance;
    }
       
    
    /**
     * Валидация массива
     * @return array
     */
    static function validationArr($arr)
    {
        foreach ($arr as $f => $v) {    
            if(is_array($v)) $mas[$f] = self::validationArr($v);
            else $mas[$f] = ( is_numeric( $v ) && ( intval( $v ) == $v ) ) ? $v : self::validation($v);		
        }
        return $mas;
    }
    
    /**
     * Валидация глобального $_POST
     * @return array
     */
    static function validationPOST()
    {		
        return self::validationArr($_POST);
	}
    
    /**
     * Валидация глобального $_POST с возвращением объекта
     * @return object(stdClass)
     */
    static function getPOST()
    {
        $obiect = new StdClass();        
        foreach ($_POST as $k => $v) $obiect->$k = self::validation($v);
        
        return $obiect;
    }
    
    /**
     * Преобразование даты к виду дд.мм.гггг
     * @date    String дата
     * @type    String для обратного преобразования к виду гггг-мм-дд
     * @return  String
     */
    static function getFormatDate($date,$type="")
    {
        switch($type)
        {
            case "base":
                {
                    $d    = explode('.',$date);
                    $date = $d[2]."-".$d[1]."-".$d[0];
                    return $date;
                }break;
            default:
                {
                    $date = explode(" ",$date);
                    $d    = explode('-',$date[0]);
                    $date = $d[2].".".$d[1].".".$d[0]." ".$date[1];
                    return $date;
                }break;
        }
    }
    
    /**
     * Форматирование даты
     * @return  String
     */
    static function formatDate($date,$type='d.m.Y')
    {
        return date($type,strtotime($date));
    }    
   
	/**
	 * Шифрование
     * @return  String
	 */
    static function encrypt($string, $key = KEY)
    {
        $result = '';
        for($i=0; $i<strlen($string); ++$i)
        {
            $char    = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char    = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return strtr( base64_encode( $result ), '+/=', '-.,' ); //Кодирование в base64 с заменой url-несовместимых символов
    }

	/**
	 * Дешифровывание
     * @return  String
	 */
    static function decrypt($string, $key = KEY)
    {
        $result = '';
        
        $string = base64_decode( strtr( $string, '-.,', '+/=' ) ); //Декодирование из base64 с заменой url-несовместимых символов 
        for($i=0; $i<strlen($string); ++$i)
        {
            $char    = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char    = chr(ord($char) - ord($keychar));
            $result .= $char;
        }                
        return $result;
    }
    
    /**
	 * Дешифровывание URL в массив
     * @return  array
	 *
    function decryptMap($string)
    {
        $string     = self::decrypt($string);
        $mas_source = explode("&",$string);        
        for($x=0; $x<count($mas_source); ++$x)
        {
            $mas_temp = explode("=",$mas_source[$x]);
            if(!empty($mas_temp[0]))
            $map[addslashes(HTMLSPECIALCHARS($mas_temp[0]))] = addslashes(HTMLSPECIALCHARS($mas_temp[1]));
        }
        return $map;
    }*/
        
    /**
     * Разбивка ЧПУ-ссылки на массив, если ЧПУ присутсвует,
     * либо дешифровывание URL с приведением к массиву
     * @return array
     */
    function urlMap()
    {    
        if(isset($_GET['go']))
        {
            $str = self::decrypt( self::validation($_GET['go']) );
            if(preg_match_all('/([^&]+)=([^&]+)/is', $str, $arr)) 
                foreach($arr[1] as $k => $v) $result[$v] = $arr[2][$k];       
            return $result;    
        }        
        elseif ($_SERVER['REQUEST_URI'] != '/') // если отличный от корня сайта.
        {
            $url = urldecode($_SERVER["REQUEST_URI"]);
            $url = parse_url($url, PHP_URL_PATH);   // чистка от QUERY_STRING
            $url = explode('/', trim($url, ' /'));   
            
            $arr = explode('/', str_replace('http://', '', HOME));
            if(isset($arr[1])) unset($url[0]);      // чистка от подкаталога            
            
            $result['module'] = array_shift($url);  //извлечение первого элемента массива 
            if(count($url) > 0)
            $result['action'] = array_shift($url);
            $result = array_merge($result, $url); 
            
            $result = self::validationArr($result);
        }
        return $result;
    }
        
    function sendEmail($email,$msg)
    {
        include_once "mail.class.php";
        $m= new Mail('UTF-8');  // можно сразу указать кодировку, можно ничего не указывать ($m= new Mail;)
        $m->From( MAIL_NAME.";".MAIL_LGN ); // от кого Можно использовать имя, отделяется точкой с запятой
        $m->To($email);   // кому, в этом поле так же разрешено указывать имя            
        $m->Subject(MAIL_NAME);
        $m->Body($msg, 'html');
        $m->Priority(4) ;	// установка приоритета
        $m->smtp_on(MAIL_SMTP, MAIL_LGN, MAIL_PSS, MAIL_PORT); // используя эу команду отправка пойдет через smtp                        
        $m->Send();
    }
    
    static function link($module,$action,$param=array())
    {
        if(CRIPT_LINK)
        {
            $str = "module=$module&action=$action";
            if(count($param)) foreach($param as $v=>$k) $str .= "&$v=$k";
            return 'index.php?go='.self::encrypt($str);
        }
        else {
            if(count($param)) foreach($param as $k) $str .= "/$k";
            return HOME ."/$module/$action". $str;
        }
    }
    
    
    static function ajaxRequest($module,$action='',$back='#main_frame')
    {
        return htmlspecialchars("elly.Ajax('".self::encrypt("module=$module&action=$action")."','', '$back')");
    }
    
    static function ajaxSubmit($module,$action='',$data, $back='#main_frame')
    {
        $data = ($data[0]=='{') ? $data : "'".$data."'";
        return htmlspecialchars("elly.Ajax('".self::encrypt("module=$module&action=$action")."', $data, '$back')");
    }

}
?>