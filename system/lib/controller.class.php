<?php

/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */
class Controller
{

    public $name;
    public $action;
    public $model;
    public $core;
    public $theme;
    private $themeName;
    private $themeUse = false;
    public $colorArr = ['blue', 'red', 'pink', 'purple', 'indigo', 'cyan', 'teal', 'green', 'lime', 'yellow', 'orange', 'brown', 'grey', 'bluegrey', 'black', 'stylish'];
    public $colorIndex = 0;
    public $settings = array();

    function __construct($controller, $action/* , $isPlugin */)
    {
        $this->name = $controller;
        $this->action = ($action == 'index') ? $controller : $action;
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
        if (empty($mainTheme))
            $mainTheme = 'main';

        if (!defined('AJAX'))
            $this->theme->create($mainTheme, true); //если не был задан др. главный шаблон, - задается по умолчанию
        if ($subTheme !== false) {
            $this->themeName = empty($subTheme) ? $this->action : $subTheme;
            $this->theme->create($this->themeName, false, $this->name);
            $this->themeUse = true;
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
            if ($this->themeName)
                $this->theme->select($this->themeName);
            $this->theme->resultContent();
        }
        elseif (!defined('AJAX') && $this->theme->isMain()) {
            CDebug::getInstance()->warn(elly::$lang['content.empty']);
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

    /**
     * Возврат цвета из цветовой гаммы
     * * @value String значение 
     */
    function get_color($reset = false)
    {
        if (empty($this->colorArr)) {
            $this->colorArr = array_map(function($v) {
                return $v->table['color'];
            }, $this->loadModel('sp_color')->find('codeid > 0'));
        }
        if (empty($this->colorArr[$this->colorIndex++]) || $reset == true) {
            $this->colorIndex = 0;
        }
        return $this->colorArr[$this->colorIndex++];
    }

    /**
     * Подгружает настройки в случае необходимости из view_setting
     * и записывает их в $this->settings
     */
    function _load_settings()
    {
        foreach ($this->loadModel('view_setting')->find(
            'code_struct = ' . (int) $_SESSION[USER_ID]
        ) as $entry) {
            $setting = $this->_int_fields($entry->table, ['codeid', 'code_struct', 'status']);
            $setting['value'] = (json_decode($setting['value'], true))? : $setting['value'];
            $this->settings[$setting['option']] = empty($setting) ? ['codeid' => null, 'option' => $option, 'value' => ''] : $setting;
        }
    }

    function _get_setting($option, $field = null)
    {
        if (empty($this->settings)) {
            $this->_load_settings();
        }

        if (empty($field)) {
            return $this->settings[$option];
        }
        if (isset($this->settings[$option][$field])) {
            return $this->settings[$option][$field];
        }
        if (isset($this->settings[$option]['value'][$field])) {
            return $this->settings[$option]['value'][$field];
        }
        return null;
    }

    function _int_fields($obj, $arr = [])
    {
        if (is_array($obj)) {
            foreach ($arr as $k) {
                if (isset($obj[$k])) {
                    $obj[$k] = (int) $obj[$k];
                }
            }
        } elseif (is_object($obj)) {
            foreach ($arr as $k) {
                if (isset($obj->$k)) {
                    $obj->$k = (int) $obj->$k;
                }
            }
        } else {
            return false;
        }

        return $obj;
    }
}

?>