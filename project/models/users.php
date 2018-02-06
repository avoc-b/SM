<?php
/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */

class Users
{
    private $table;
    private $error;
    
    function __construct($table)
    {
        $this->table = 'sp_user';
    }
    
    /**
     * Приведение данных к стандартным названиям
     */
    function standartKey($post)
    {
        $pCore = CCore::getInstance();
        
        $result = array();        
        foreach($post as $key=>$value)
        {            
            //валидация
            $value = $pCore->validation($value);
            //$value = addslashes(HTMLSPECIALCHARS($value));
            
            switch($key)
            {
                case 'login':
                case 'lgn':
                case 'user':    
                    $result['login']['val'] = $value; 
                    $result['login']['key'] = $key; 
                    break;                
                case 'mail':
                case 'email':   
                    $result['mail']['val']  = $value; 
                    $result['mail']['key']  = $key; 
                    break;                
                case 'password':
                case 'pass':
                case 'psw':     
                    $result['pass']['val']  = $value; 
                    $result['pass']['key']  = $key; 
                    break; 
                case 'password2':
                case 'pass2':
                case 'psw2':     
                    $result['pass2']['val'] = $value; 
                    $result['pass2']['key'] = $key; 
                    break;               
                default:        
                    $key = $pCore->validation($key);
                    $result[$key]['val']    = $value; 
                    $result[$key]['key']    = $key; 
                    break;
            } 
        }  
        return $result; 
    }
    
    /**
     * Регистрация нового пользователя
     */
    function registration($post)
    {
        $pCore = CCore::getInstance();
        
        $sk = $this->standartKey($post);
        if(empty($sk['login']['val'])) $this->error = 'Отсутсвует логин';
        else
        {
            if( strlen($sk['login']['val']) > 16 ) $this->error= "Логин пользователя не должно превышать 16 символов";
            if( strlen($sk['login']['val']) < 3 )  $this->error= "Логин пользователя не может быть короче 3 символов";                    
            if(!preg_match("|^([a-z0-9_\.\-]{3,16})|is", $sk['login']['val'])) 
                $this->error = 'Некорректный логин (возможно содержит недопустимые символы)';
            
            //проверка логина на существование
            if($pCore->sqlRecord($this->table, 'codeid', $sk['login']['key'] ."='". $sk['login']['val'] ."'"))
                $this->error = 'Такой логин уже существует';                
        }
        
        if(!empty($sk['mail']['val']))
        {
            $value = filter_var($sk['mail']['val'], FILTER_SANITIZE_EMAIL);
            //if(filter_var($value, FILTER_VALIDATE_EMAIL))
            if(!preg_match("|^([a-z0-9_\.\-]{1,20})@([a-z0-9\.\-]{1,20})\.([a-z]{2,4})|is", $value))
                $this->error = 'Некорректно указан почтовый адрес.';
            
            //проверка мэйла на существование
            if($pCore->sqlRecord($this->table, 'codeid', $sk['mail']['key'] ."='". $sk['mail']['val'] ."'"))
                $this->error = 'Такой почтовый адрес уже существует'; 
        }
        
        if(empty($sk['pass']['val'])) $this->error = 'Отсутсвует пароль';
        else
        {
            $post[$sk['pass']['key']] = $this->setPassword($sk['pass']['val']); 
        }
        
        if($this->error) return $this->error;
        
        //запись в базу
        $pCore->sqlInsert($this->table, $post);                
        return false;
    }
    
    /**
     * Авторизация
     */
    function autorisation($post)
    {
        $pCore = CCore::getInstance();
                
        $sk = $this->standartKey($post);
        
        if(empty($sk['login']['val']) || empty($sk['pass']['val'])) $this->error = 'Ошибка авторизации';
                                        
        //$tb   = $pCore->sqlSelect($this->table, 'codeid,'. $sk['pass']['key'], $sk['login']['key'] ."='". $sk['login']['val'] ."'");
        $row  = $pCore->table($this->table)
                      ->where($sk['login']['key'] ."='". $sk['login']['val']."'")
                      ->row('codeid,'. $sk['pass']['key']);
        $pass = $this->getPassword($row[$sk['pass']['key']]); 
        
        if($sk['pass']['val'] == $pass) 
        {
            $_SESSION[USER_ID]    = $row['codeid'];            
            //$_SESSION['user_field'] = $sk['login']['key'];
            return false;
        }
        else $this->error = 'Пароль или логин некорректны';            
        
        return $this->error;
    }
    
    /**
     * Выход пользователя
     * @except  array список ключей, которые не нужно очищать
     */
    function logout($except = array())
    {        
        if(count($except))
        {
            foreach($_SESSION as $k => $v) 
                if(!in_array($k, $except)) unset($_SESSION[$k]);
        }
        else $_SESSION = array();
    }
    
    /**
     * Смена пароля
     * @post    array массив содержащий старый и новы массив
     * @oldpass String при ошибке вернет правильный пароль(на всякий случай)
     * @check   bool если FALSE, то проверки старого пароля не будет 
     */
    function changePass($post, &$oldpass, $check = true)
    {
        $pCore = CCore::getInstance();
        
        $sk = $this->standartKey($post);
                
        if($check)
        {
            $pass = $pCore->sqlRecord($this->table, $sk['pass']['key'], "codeid='". $_SESSION['user_id'] ."'");
            $pass = $this->getPassword($pass);
        } 
        if($check && $pass != $sk['pass']['val']) 
        {
            $this->error = 'Старый пароль неверный';
            $oldpass = $pass;
            return $this->error;
        }
        else 
        {
            $sk['pass2']['val'] = $this->setPassword($sk['pass2']['val']);
            $pCore->sqlUpdate($this->table, 
                              array($sk['pass']['key'] => $sk['pass2']['val']),
                              "codeid='". $_SESSION['user_id'] ."'" 
                              );
            return 0;
        }
    }
    
    /**
     * Отображение пароля
     * @login    String логин пользователя
     * @keylogin String имя поля логина
     * @keypass  String имя поля пароля
     */
    function openPass($login, $keylogin, $keypass)
    {
        $pCore = CCore::getInstance(); 
        
        $pass = $pCore->sqlRecord($this->table, $keypass, $keylogin ."='". $login ."'");
        return $this->getPassword($pass);
    }
    
    /**
     * Запись пароля
     */
    function setPassword($password)
    {
        $key = elly::GeneratorPass(13); 
        $psw = $this->xorCoding($password, $key);
        return $psw .'::'. $key;
    } 
    
    /**
     * Чтение пароля
     */
    function getPassword($password)
    {
        $arr = explode('::', $password);
        if($arr[1]) return $this->xorCoding($arr[0], $arr[1]);
        else return '';
    }
    
    /**
     * XOR-шифрование
     */
    function xorCoding($string, $key = KEY)
    {      
        $result = '';
        for($i=0; $i<strlen($string); ++$i)
        {
            $char    = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char    = chr(ord($char) ^ ord($keychar));
            $result .= $char;
        }
        return $result; 
    }
    
    /**
     * Вывод последней ошибки
     */
    function error()
    {
        return $this->error;
    }
}

?>