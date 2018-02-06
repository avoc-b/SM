<?php

/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */
class CTheme
{

    private $data = array();      // хранилище шаблонов 
    private $script = '';           // набор js-скриптов для вывода в конце потока
    private $plugin = false;        // метка о плагине
    private $plugin_arr = array();      // какой шаблон соответствует плагину 
    private $lastFile = '';           // последний загруженный шаблон
    public $statTimer = 0;            // время затрачиваемое на шаблонизатор
    public $statCount = 0;            // количество обращений к шаблонизатору
    private $timer = 0;
    private $varsType = '';
    private $varsArr = array();
    private $mainTheme;
    private $tableName;                 // хранилище названий таблиц для циклов
    static private $_instance;

    static function getInstance()
    {
        if (null === self::$_instance)
            self::$_instance = new self();
        return self::$_instance;
    }

    private function __clone()
    {
        
    }

    private function __wakeup()
    {
        
    }

    /** Загрузка каркаса шаблона
     * @name   String имя шаблона  (по умолчанию как и имя php-файла)
     * @main   bool если конечный шаблон 
     * @dir    String директория с шаблоном 
     * @return Object
     */
    function create($name = '', $main = false, $dir = '', $plugin = false)
    {
        $file = ''; //String файл, если не указан @name 


        if ($main)
            $this->mainTheme = $name;

        if (empty($name)) {
            $trace = debug_backtrace();
            $name = basename($trace[0]['file'], '.php');         // аналогичное имя файла шаблона
        }

        if (!$plugin)
            $dir = ($dir) ? THEME . '/' . $dir : THEME;

        $this->_timer(true); // Засекание времени



        $nn = $name;
        $fname = $dir . ($file ? $file : ((substr($dir, -1) != '/' ? '/' : '') . $name . '.tpl'));

        if (!isset($this->data[$nn]['in'])) {
            $pDebug = CDebug::getInstance();

            if ($pDebug->Enable && is_object($pDebug)) {
                if (empty($dir))
                    $pDebug->group(elly::$lang['debug.tpl'], $name, true, true);
                else
                    $pDebug->group(elly::$lang['debug.tpl'], $dir . '/' . $name, true, true);
            }

            if (!is_dir($dir)) {
                header('Content-Type: text/html; charset=utf-8');
                die(sprintf(elly::$lang['msge_no_tpldir'], $dir));
            }

            if (!is_file($fname)) {
                header('Content-Type: text/html; charset=utf-8');
                die(sprintf(str_replace('{fname}', $fname, elly::$lang['fatal.tpl.lost'], $fname)));
            }

            $fp = fopen($fname, 'r');
            $data = filesize($fname) ? fread($fp, filesize($fname)) : '';
            fclose($fp);

            $this->data[$nn]['in'] = $data;
        }

        $this->lastFile = $name;
        $this->varsType = '';
        unset($this->varsArr[$name]);

        $this->_timer();      // Суммирование времени затрачиваемого шаблонизатором  

        return $this;
    }

    /** Вывод данных
     * @params  array массив ключей
     * @php     bool флаг для выполнения php кода в tpl
     * @return  String
     */
    function result($php = false)
    {
        //debug(array_keys($this->varsArr[$this->lastFile]['tag']));
        $name = $this->lastFile;
        $this->_parsing($name, $this->varsArr[$name], $php);

        return $this->_show($name);
    }

    /**
     * Вывод в {content} и через Ajax
     * @tag     String название тега в основном шаблоне
     */
    function resultContent($php = false, $tag = 'content')
    {   
        if (!$this->plugin) {
            //if(defined('AJAX')) echo $this->result($php);                   // вывод результата напрямую
            if (defined('AJAX')) {                                               // вывод результата напрямую
                $result = $this->result($php);
                //$result = CDebug::getInstance()->validation($result);
                Run::ajaxAdd($result, 'html');
            } else
                $this->varsArr[$this->mainTheme]['tag'][$tag] = $this->result($php); // отправка в index.php            
        }
        else {
            $this->_parsing($this->lastFile, $this->varsArr[$this->lastFile]);
            $this->plugin = $this->lastFile;
        }
    }

    /**
     * Вывод главного шаблона
     */
    function resultMain()
    {
        if (empty($this->mainTheme))
            return;
        $this->lastFile = $this->mainTheme;
        //show($this->varsArr[$this->mainTheme]);
        return $this->result();
    }

    /**
     * Выведет в конце потока скрипт
     * !для ajax
     */
    function addScript($text)
    {
        $this->script .= $text . "\n";
    }

    private function _show($name)
    {
        $ret = $this->data[$name]['out'];
        if (!empty($this->script))
            $ret .= '<script type="text/javascript">' . $this->script . '</script>';
        $this->data[$name]['out'] = $this->data[$name]['in'];
        $this->script = '';
        return $ret;
    }

    /** Парсинг шаблона по заданным ключам
     * @nn   String имя шаблона
     * @vars array массив ключей
     * @php  bool флаг для выполнения php кода в tpl
     */
    private function _parsing($nn, $vars = array(), $php = false)
    {
        $this->_timer(true); // Засекание времени

        //if (!isset($this->data[$nn]['out'])) {
            $data = $this->data[$nn]['in'];


            if (isset($vars['php'])) {
                extract($vars['php'], EXTR_SKIP);
                ob_start();
                $data = (eval(' ?>' . $this->data[$nn]['in'] . '<?php '));
                $data = ob_get_clean();
            }

            // Замена своих переменных
            if (isset($vars['tag']) && is_array($vars['tag'])) {               
                foreach ($vars['tag'] as $id => $var) {
                    if (substr($id, 0, 1) == '[') {
                        $data = str_replace($id, $var, $data);
                    } else {
                        $data = str_replace('{' . $id . '}', $var, $data);
                    }
                }
            }



            // Построение таблиц
            if (count($vars['for']) > 0)
                if (preg_match_all('/\[for=(.+?)\](.+?)\[\/for\]/is', $data, $parr)) {


                    foreach ($parr[1] as $k => $v) { // переменные, содержащие названия Таблиц
                        $html = '';
                        if (isset($vars['for'][$v])) {
                            $types = $vars['for'][$v]['types'];
                            $params = $vars['for'][$v]['params'];

                            //$for = CCore::validation($vars['for'][$v]);                    
                            if (is_array($vars['for'][$v]['data']))
                                foreach ($vars['for'][$v]['data'] as $i => $for) {
                                    $block = $parr[2][$k];
                                    foreach ($for as $x => $r) {
                                        if ($types[$x] == 'date') {
                                            if (!is_numeric($r)) $r = strtotime($r);
                                            $r = date($params[$x], $r);
                                        }
                                        elseif ($types[$x] == 'check' && $r)
                                            $block = str_replace('name="' . $x . '[]"', 'name="' . $x . '[]" checked', $block);
                                        $block = str_replace('{' . $v . '.' . $x . '}', $r, $block);
                                    }
                                    $block = str_replace('{' . $v . '._NUMBER}', ($i +1), $block);
                                    $html .= $block;
                                }
                        }
                        $data = str_replace($parr[0][$k], $html, $data);
                    }
                }



            // Авто подстановка в инпуты значений из переданного массива $vars['post']
            // {val=nameid}
            if (count($vars['post']) > 0)
                if (preg_match_all('/\{val=(.*?)\}/i', $data, $parr)) {
                    foreach ($parr[1] as $k => $v) {
                        if (isset($vars['post'][$v]))
                            $data = str_replace($parr[0][$k], $vars['post'][$v], $data); //CCore::validation()
                        else
                            $data = str_replace($parr[0][$k], '', $data);
                    }
                }

            // Замена конструкций вида {url=par1+par2} на шифрованные ссылки
            if (preg_match_all('/\{url=(.*?)\}/i', $data, $parr)) {
                foreach ($parr[0] as $k => $v) {
                    $arr = explode('+', $parr[1][$k]);
                    if (isset($arr[0]))
                        $link = 'module=' . $arr[0];
                    else
                        continue;
                    if (isset($arr[1]))
                        $link .= '&action=' . $arr[1];
                    if (isset($arr[2])) {
                        for ($i = 2; $i < count($arr); $i++)
                            if (strpos($arr[$i], '='))
                                $link .= '&' . $arr[$i];
                    }
                    $data = str_replace($v, CCore::encrypt($link), $data);
                }
            }

            // Вывод плагинов {plugin=name}
            if (preg_match_all('/\{plugin=(.*?)\}/i', $data, $parr)) {

                global $map;

                $run = Run::getInstance();

                foreach ($parr[0] as $k => $v)
                {     
                    $arr = explode('+', $parr[1][$k]);
                    if (empty($arr[0]))
                        continue;
                    if (isset($arr[1])) { //$map['action'] = $arr[1];
                        $par = array();
                        while (isset($arr[2])) {
                            array_unshift($par, array_pop($arr));
                        }

                        $plugin = $run->router($arr[0], $arr[1], $par, 2);
                        
//                        var_dump($plugin);
//                        die;
                        
                        $data = str_replace($v, $plugin, $data);
                    }
                    /*
                      if($this->plugin_arr[$parr[1][$k]]) {
                      $data = str_replace($v, $this->_show($this->plugin_arr[$parr[1][$k]]), $data);
                      }
                      else {
                      $this->plugin = true;
                      $_PLUGIN      = '';
                      if(file_exists('view/'.$arr[0].'.php')) include('view/'.$arr[0].'.php');

                      if(is_string($this->plugin))
                      $data = str_replace($v, $this->_show($this->plugin), $data);
                      else $data = str_replace($v, $_PLUGIN, $data);

                      $this->plugin_arr[$parr[1][$k]] = $this->plugin;
                      $this->plugin = false;
                      } */
                }
            }

            if (count($vars['post']) > 0) {
                // Авто выделение выбранного радио-баттона       
                if (preg_match_all('/\[radio=(.+?)\](.+?)\[\/radio\]/is', $data, $parr)) {
                    foreach ($parr[1] as $k => $v) { // переменные, содержащие названия POST'ов
                        if (isset($vars['post'][$v])) {
                            $post = CCore::validation($vars['post'][$v]);
                            //$parr[2][$k] - html код, выхваченный регуляркой
                            if (preg_match_all('/value="(.*?)"/is', $parr[2][$k], $larr))
                                foreach ($larr[1] as $x => $r)
                                    if ($r == $post)
                                        $parr[2][$k] = str_replace($larr[0][$x], $larr[0][$x] . ' checked', $parr[2][$k]);
                        }
                        $data = str_replace($parr[0][$k], $parr[2][$k], $data);
                    }
                }

                // Авто выделение активных чек-боксов
                if (preg_match_all('/\[check\](.+?)\[\/check\]/is', $data, $parr)) {
                    foreach ($parr[1] as $k => $v) {
                        if (preg_match_all('/name="(.*?)"/is', $v, $larr))
                            foreach ($larr[1] as $x => $r)
                                if (isset($vars['post'][$r]) && $vars['post'][$r] == 1)
                                    $parr[1][$k] = str_replace($larr[0][$x], $larr[0][$x] . ' checked', $parr[1][$k]);
                        $data = str_replace($parr[0][$k], $parr[1][$k], $data);
                    }
                }
            }

            // Обработка блоков условного вывода
            if (isset($vars['regx']) && is_array($vars['regx'])) {
                foreach ($vars['regx'] as $id => $var) {
                    $data = preg_replace($id, $var, $data);
                }
            }


            // Обработка ссылок (из относительных в абсолютные)
            if (preg_match_all('~ ((?:href|src)=["\'])([^"\']*)(["\'])~i', $data, $parr)) {
                //die('<pre>.'.print_r($parr,1));
                foreach ($parr[2] as $k => $link) {
                    if ($link[0] == '#')
                        continue;
                    if (preg_match('~^javascript:.*~i', $link))
                        continue;
                    if (preg_match('~^http.*~i', $link))
                        continue;

                    $linkFinish = ($link[0] == '/') ? substr($link, 1) : $link;
                    $link = preg_quote($link);  //экранирует спец-символы
                    $data = preg_replace("~ {$parr[1][$k]}{$link}{$parr[3][$k]}~i", ' ' . $parr[1][$k] . HOME . '/' . $linkFinish . $parr[3][$k], $data);
                }
            }

            $this->data[$nn]['out'] = $data;
        //}

        $this->varsType = '';
        unset($this->varsArr[$nn]);

        $this->_timer(true); // Суммирование времени затрачиваемого шаблонизатором		
        $this->statCount++;
    }

    /**
     * Таймер выполнения кода
     * @start bool если TRUE - старт, если FALSE - остановка и подсчет
     */
    private function _timer($start = false)
    {
        list($usec, $sec) = explode(' ', microtime());
        $time = (float) $sec + (float) $usec;

        if ($start)
            $this->timer = $time;
        else
            $this->statTimer += ($time - $this->timer);
    }

    function block($clear = false)
    {
        $this->varsType = (bool) $clear;
        return $this;
    }

    function tag()
    {
        $this->varsType = 'tag';
        return $this;
    }

    function post($arr = NULL)
    {
        if (is_null($arr))
            $this->varsType = 'post';
        else
            $this->varsArr[$this->lastFile]['post'] = $arr;
        return $this;
    }

    function php($arr = NULL)
    {
        if (is_null($arr))
            $this->varsType = 'php';
        else {
            if (is_object($arr))
                $this->varsArr[$this->lastFile]['php'] = $arr->table;
            else
                $this->varsArr[$this->lastFile]['php'] = $arr;
        }
        return $this;
    }

    function table($name, $arr = array(), $params = NULL)
    {
        if (!is_null($params))
            $this->varsArr[$this->lastFile]['for'][$name] = $params;
        //$this->varsArr[$this->lastFile]['for'][$name] = array_merge($params, $arr);
        //else 
        $this->varsArr[$this->lastFile]['for'][$name]['data'] = $arr;
        $this->tableName = $name;
        return $this;
    }

    function tableSetDate($field, $format = 'd.m.Y')
    {
        $this->varsArr[$this->lastFile]['for'][$this->tableName]['types'][$field] = 'date';
        $this->varsArr[$this->lastFile]['for'][$this->tableName]['params'][$field] = $format;
        return $this;
    }

    function tableSetCheck($field)
    {
        $this->varsArr[$this->lastFile]['for'][$this->tableName]['types'][$field] = 'check';
        return $this;
    }

    /**
     * Проверка наличия главного шаблона
     */
    function isMain()
    {
        return empty($this->mainTheme);
    }

    /**
     * Выбор шаблона для работы с переменными
     * @name String название шаблона, если не задано - используется главный шаблон
     */
    function select($name = NULL)
    {
        if (is_null($name))
            $this->lastFile = $this->mainTheme;
        else
            $this->lastFile = $name;
        return $this;
    }

    function __set($name, $value)
    {
        if (is_bool($this->varsType)) { //условные блоки
            if ($this->varsType) {
                $this->varsArr[$this->lastFile]['tag']['[' . $name . ']'] = '';
                $this->varsArr[$this->lastFile]['tag']['[/' . $name . ']'] = '';
            } else
                $this->varsArr[$this->lastFile]['regx']["'\\[$name\\].*?\\[/$name\\]'si"] = "";
            $this->varsType = 'tag';
        } else
            switch ($this->varsType) {
                case '':
                case 'tag' : $this->varsArr[$this->lastFile]['tag'][$name] = $value;
                    break;
                case 'post': $this->varsArr[$this->lastFile]['post'][$name] = $value;
                    break;
                case 'php' : $this->varsArr[$this->lastFile]['php'][$name] = $value;
                    break;
            }
    }

    function __get($name)
    {
        switch ($this->varsType) {
            case '':
            case 'tag' : return $this->varsArr[$this->lastFile]['tag'][$name];
            case 'post': return $this->varsArr[$this->lastFile]['post'][$name];
            case 'php' : return $this->varsArr[$this->lastFile]['php'][$name];
        }
        return false;
    }
}

//////////////////////////////////////////////////////////////////////////////////// 

/*
  $pTheme = new CTheme();

  $pTheme->load('main')->result($main);
  $pTheme->resultAuto($main);


  $pTheme->load('tovar');

  $pTheme->arrPost['fdf'] = 'fffff';
  $pTheme->block(true)->hide = '';
  $pTheme->tag()->memo = 15;
  $pTheme->post($_POST);
  $pTheme->php($arr);



  $pTheme->selectTheme('tovar');
  $pTheme->setPost    = $tb[0];
  $pTheme->setBlock   = $tb[0];
  $pTheme->setTag     = $tb[0];
  $pTheme->setLive    = $tb[0];


  // Блоки условного вывода
  if(!empty($_SESSION['user_id']))
  {
  $pTheme->setTag('[hide]','');
  $pTheme->setTag('[/hide]','');
  }
  else $pTheme->setBlock("'\\[hide\\].*?\\[/hide\\]'si", "");
 */

?>