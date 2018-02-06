<?php

/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2015
 */
class Plugin
{

    public $name;
    public $action;
    public $model;
    public $core;
    public $theme;
    public $isVidget = false;
    private $themeName;
    private $themeUse = false;
    public static $_instance = array();

    public static function getInstance($plugin, $action, $isVidget)
    {
        //$class = get_called_class(); //появилась только в PHP 5.3
        if (empty(self::$_instance[$plugin]))
            self::$_instance[$plugin] = new $plugin($plugin, $action, $isVidget);
        return self::$_instance[$plugin];
    }

    function __construct($plugin, $action, $isVidget)
    {        
        $this->name = $plugin;
        $this->action = ($action == 'index') ? $plugin : $action;
        $this->isVidget = $isVidget;
        $this->core = CCore::getInstance();
        $this->theme = CTheme::getInstance();
    }

    /**
     * Создает объект для работы с таблицей
     * Если имеется аналогичная модель - используется она, если нет - то по умолчанию
     * @class   String название таблицы
     * @return  Object
     */
    function loadModel($class)
    {
        if (Run::_autoload($class, 'models'))
            $this->model = new Model($class, true);
        else {
            $this->model = new $class($class);
        }
        return $this->model;
    }

    /**
     * Сообщает контроллеру о работе с шаблоном
     * ф-ия должна вызываться до создания переменных шаблона
     * @mainTheme String главный шаблон
     * @subTheme  mixed шаблон модуля, если FALSE - не использовать, если пусто - по умолчанию 
     */
    function loadTheme($mainTheme = 'main', $subTheme = '')//, $subTheme = true)
    {
        //Важный момент!
        //в Run::$action, при обращении из виджета, находится глобальная переменная
        //поэтому нужно использовать $this->action 
        if (!defined('AJAX')) {
            if ($subTheme == 'index')
                $subTheme = $this->action;
            if ($mainTheme === false)
                $this->theme->create((empty($subTheme) ? $this->action : $subTheme), true, 'project/plugins/' . $this->name . '/tpl', true);
            elseif (!$this->isVidget)
                $this->theme->create((empty($mainTheme) ? 'main' : $mainTheme), true); //если не был задан др. главный шаблон, - задается по умолчанию
        }
        if ($subTheme !== false && $mainTheme !== false) {
            $this->themeName = empty($subTheme) ? $this->action : $subTheme;
            $this->theme->create($this->themeName, false, 'project/plugins/' . $this->name . '/tpl', true);
            //$this->theme->create($this->themeName, false, $this->name); 
            $this->themeUse = true; //!$this->isVidget;    
        }
    }

    function json($data)
    {
        Run::ajaxAdd($data, 'json');
    }

    function script($data)
    {
        Run::ajaxAdd($data, 'script');
    }

    function html($data)
    {
        Run::ajaxAdd($data, 'html');
    }

    /**
     * Функция выполняется по завершению любого экшена
     */
    function end()
    {
        if ($this->themeUse) {
            if ($this->isVidget)
                return $this->theme->result();

            if ($this->themeName)
                $this->theme->select($this->themeName);
            $this->theme->resultContent();
        }
    }

    /**
     * Генерация ссылки
     * В зависимости от конфига шифрованная, либо чпу 
     */
    function link($module, $action, $param = array())
    {
        return $this->core->link($module, $action, $param);
    }

    /**
     * Очистка потенциально опасных входных данных
     * @value String значение 
     * @type  String тип данных, как в таблице
     */
    function clear($value, $type = 'varchar')
    {
        $type = strtoupper($type);
        return $this->core->_securityField($value, $type, true);
    }

    function prepare_args($args = null)
    {
        if (elly::is_iterable($args)) {
            $params = array();

            foreach ($args as $arg) {
                $par = explode('=', $arg);
                $key = array_shift($par);
                $value = implode('=', $par);

                $params[$key] = $value;
            }

            $this->theme->post($params);
        }
    }
}

?>