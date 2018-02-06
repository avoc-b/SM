<?php
/**
 * Elly Framework
 * http://333.kg
 *
 * Класс автозагрузчика
 * Для работы необходим класс CCore
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */

class Run
{
    public $error = false;
    private $map = array();
    static public $action;
    static public $arg;
    static private $_instance;

    static private $ajax = array();

    function __construct()
    {
        self::$_instance = $this;


        spl_autoload_register(array($this, '_autoload'));



        // перенесено из index.php
        $pDebug = CDebug::getInstance();
        if(elly::checkIP() == '127.0.0.1') {
            $pDebug->Enable = true;
            }
        else $pDebug->Enable = DEBUG;

        if($pDebug->Enable) include("system/mod/backup.php");



        if(!empty($_POST['ajax']))
        {
            define('AJAX', $_POST['ajax']);
            unset($_POST['ajax']);
        }
        //else CTheme::getInstance()->create('main', true);

        $this->parseURL();

        $module = $this->getModule();
        $action = $this->getAction();
        $arg    = $this->getArguments();
        self::$action = $action;
        self::$arg    = $arg;

        $this->router($module, $action, $arg, $this->isPlugin());

        // На случай, если устарела сессия, но пошел ажакс-запрос
        /*if(empty($_SESSION[USER_ID]) && defined('AJAX'))
        switch(AJAX)
        {
            case 'html':    echo "<script>location.reload();</script>"; break;
            case 'script':  echo "location.reload();";  break;
        }*/

        //CDebug::getInstance()->group('$map',$this->map);
        $pDebug->group('$map',$this->map);

        if(!defined('AJAX')) $this->mainHtml();


        // перенесено из index.php
        $debug = $pDebug->debugEND();
        if(defined('AJAX'))
        {
            self::$ajax['script'] .= $debug;

            header('Content-Type: application/json');
            echo json_encode(self::$ajax);
        }
        else echo $debug;
    }

    static function getInstance()
    {
        //if (null === self::$_instance) self::$_instance = $this;
        return self::$_instance;
    }

    function _autoload($class, $path='')
    {
        $class = str_replace("/","",$class);
        if($path == 'plugins') $class = $class .'/'. $class;
        $path  = $path ? 'project/'. $path : 'system/lib';
        $file  = $path .'/'. $class .'.php';
        /**
         * Иногда файл может не иметь нужных прав на чтение,
         * поэтому лучше вначале использовать is_readable() вместо
         * file_exists(), что проверит оба условия.
         */
        if (is_readable($file)) include_once $file;
        else return true; //throw new Exception("В проекте не найден класс: $class... $file");
        return false;
    }

    static function ajaxAdd($data, $type) { self::$ajax[$type] = $data; }

    function router($module, $action, $arg = array(), $isPlugin = false)
    {
        $pDebug = CDebug::getInstance();

        try {

            if($isPlugin) {
                if($this->_autoload($module, 'plugins'))throw new Exception("Не найден плагин: $module...");
                $type = 'Plugin';
            }
        	else {
                if($this->_autoload($module, 'controllers'))throw new Exception("Не найден контроллер: $module...");
                $type = 'Controller';
            }
            $isVidget   = ($isPlugin === 2) ? true : false;

        	$reflClass  = new ReflectionClass($module) ;   //выполняем экшн с параметрами
            //$controller = new $module($module, $action, $isVidget); //создаем контроллер (вызов конструктора контролера|плагина)
            if($isPlugin) $controller = call_user_func_array(array($module, 'getInstance'), array($module, $action, $isVidget)); //$module::getInstance($module, $action, $isVidget); // Баг с PHP 5.2
            else          $controller = $reflClass->newInstance($module, $action, $isVidget); //создаём экземпляр класса

        	// проверки
        	if(!$reflClass->isSubclassOf($type))        throw new Exception('Класс '. $module .' должен быть унаследован от класса '. $type);
        	if(!$reflClass->hasMethod($action))         throw new Exception('Класс '. $module .' не содержит метода: '. $action);

            $method = $reflClass->getMethod($action);
        	if(!$method->isPublic())                    throw new Exception('Метод '. $action .' контроллера '. $module .' должен быть public');

            //проверка на количество обязательных параметров метода
        	$argCount = $method->getNumberOfRequiredParameters();
        	if( count($arg) < $argCount)                throw new Exception('Метод '. $action .' контроллера '. $module .' запрашивает больше обязательных параметров, чем ему передаётся');

            try {
               $result = $method->invokeArgs( $controller, $arg );
            } catch(Exception $e) { $pDebug->warn($e->getMessage()); }

            if($result === false)                       throw new Exception('Метод '. $action .' контроллера '. $module .' запросил страницу с ошибкой');
            $result_tpl = $controller->end();

        } catch(Exception $e) {
            $pDebug->warn($e->getMessage());
            $this->error = true;
            $module      = 'main';
            if($this->_autoload($module, 'controllers'))
            {
                echo str_replace('{msg}', "Не найден контроллер: $module...", elly::$lang['fatal.error']);
                return;
            }
            else
            {
                $controller  = new $module($module, 'error404');
                $controller->error404();
            }
        }
        return empty($result_tpl) ? $result : $result_tpl;
    }

    /**
     * Переадресация на необходимый контроллер по шаблонам из project/map.conf.php
     */
    function rewriteMap($url)
    {
        $this->ajaxAdd(array('error' => 0),'json');

        if(!defined('AJAX'))
        {
            if(!defined('SERVER_ADDR') || SERVER_ADDR != $_SERVER['SERVER_ADDR'])
            {
                $this->map['module'] = 'install';
                $this->map['plugin'] = 1;
                return;
            }
        }

        if(LOGIN && empty($_SESSION[USER_ID]))
        {
            if(defined('AJAX') && $this->map['module'] != 'login')
            {
                $this->ajaxAdd(array(
                                        'error'     => -1,
                                        'error_msg' => 'Закончилась ссесия',
                                     ),'json');
                $this->map['module'] = 'error';
                $this->map['action'] = 'index';
                return;
            }
            if($this->map['module'] != 'login') $this->map['action'] = 'index';
            $this->map['module'] = 'login';
            //if(empty($this->map['action'])) $this->map['action'] = 'index';
            CTheme::getInstance()->create('login', true);
            return;
        }

        if(empty($url)) return;

        include 'project/map.conf.php';
        foreach($_MAP as $rout)
        {
            $flag = false;
            foreach($rout['rule'] as $patern)
            {
                $arg = array();
                // Модификатор i - Шаблон становится регистронезависимым
                if(preg_match('~^'.$patern.'~u', $url, $arg)) { $flag = true; break; }
            }
            if($flag)
            {
                foreach($rout['params'] as $key => $param)
                {
                    if(substr($param, -1,1) == '$')
                    //if(preg_match('~([0-9]+)\$~i', $param, $index))
                         //$result[$key] = $arg[substr($param, 0,-1)];
                         $this->map[$key] = $arg[substr($param, 0,-1)];
                    //else $result[$key] = $param;
                    else $this->map[$key] = $param;
                }
                break;
            }
        }
        //$this->map = $result;
    }

    /**
     * Разбивка ЧПУ-ссылки на массив, если ЧПУ присутсвует,
     * либо дешифровывание URL с приведением к массиву
     */
    function parseURL()
    {
        if(isset($_GET['go']))
        {
            $str = CCore::decrypt(CCore::validation($_GET['go']));
            if(preg_match_all('/([^&]+)=([^&]+)/is', $str, $arr))
                foreach($arr[1] as $k => $v) $this->map[$v] = $arr[2][$k];

            $this->rewriteMap(implode('/', $this->map));//, true);
        }
        elseif($_SERVER['REQUEST_URI'] != '/')          // если отличный от корня сайта.
        {
            /*$url = urldecode($_SERVER["REQUEST_URI"]);
            $url = parse_url($url, PHP_URL_PATH);       // чистка от QUERY_STRING
            $url = CCore::validation($url);
            $url = explode('/', trim($url, ' /'));

            if(preg_match('~^http://(.+)/(.+)~is', HOME)) unset($url[0]);       // чистка от подкаталога
            */
            $url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
            $url .= "://" . $_SERVER['HTTP_HOST'];
            $url .= parse_url(urldecode($_SERVER["REQUEST_URI"]), PHP_URL_PATH);
            $url = CCore::validation(str_replace(HOME, '', $url));         // чистка от подкаталогов
            $url = explode('/', trim($url, ' /'));

            //$this->rewriteMap(implode('/', $url));
            //$url_str = implode('/', $url);
            //if(count($this->map) < 1)
            //{
                if(count($url) > 0) $this->map['module'] = array_shift($url);   // извлечение первого элемента массива
                if(count($url) > 0) $this->map['action'] = array_shift($url);
                if(count($url) > 0) $this->map = array_merge($this->map, $url);
            //}
            $this->rewriteMap(implode('/', $this->map));
        }
        else $this->rewriteMap('');               // если корень сайта
    }
    function isPlugin()
    {
        return !empty($this->map['plugin']);
    }
    function getModule()
    {
        if(empty($this->map['module'])) $this->map['module'] = 'calendar';
        return $this->map['module'];
    }
    function getAction()
    {
        if(empty($this->map['action'])) $this->map['action'] = 'index';
        return $this->map['action'];
    }
    function getArguments()
    {
        $arr = $this->map;
        unset($arr['module']);
        unset($arr['action']);
        return $arr;
    }

    function mainHtml()
    {
        $module = 'main';
        if($this->_autoload($module, 'controllers'))
        {
            echo str_replace('{msg}', "Не найден контроллер: $module...", elly::$lang['fatal.error']);
        }
        else
        {
            $controller  = new $module($module, $module);
            $controller->index();

            echo $controller->theme->resultMain();
        }
    }
}

?>