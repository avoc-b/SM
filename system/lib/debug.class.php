<?php
/**
 * Elly Framework
 * http://333.kg
 *
 * Отлов ошибок и вывод информации в консоль браузера
 *
 * работает в Firefox+Firebug, Opera, Chrome, Safari
 * частично в IE8 и выше
 * в не поддерживающих консоль браузерах выводится заглушка функций
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 * @version 2.2 (04.03.2014)
 */


////////////////////////////////////////////////////////////////////////

/**
 * Вывод в консоль браузера информации
 * @dump mixed любая строка или массив
 */
function debug($dump)
{
    $pDebug = CDebug::getInstance(); //синглтон
    $pDebug->log($dump);
}

/**
 * Вывод в консоль браузера информации (расширенный вариант)
 * @name    String название
 * @dump    mixed любая строка или массив
 * @close   bool свернутая или нет
 * @count   bool вывод количества *
 */
function debug_group($name, $dump, $close=false, $count=false)
{
    $pDebug = CDebug::getInstance(); //синглтон
    $pDebug->group($name, $dump, $close, $count);
}
////////////////////////////////////////////////////////////////////////

class CDebug
{
    static private $_instance;
    public $Enable;                 // включить\выключить вывод в консоль

    private $html;
    private $error  = array();
    private $group  = array();
    private $timers = array();
    private $rn = "\r\n";

    private function __construct()
    {
        $this->Enable = true;
        $this->time('Timer');
        $this->console('Elly DEBUGER v.'.VERSION_CORE, 'info');

        //ini_set('display_errors', TRUE);                    // определяем режим вывода ошибок
        //error_reporting(E_ERROR | E_WARNING | E_PARSE);     // определеяем уровень протоколирования ошибок E_ALL | E_STRICT
        set_error_handler(array($this, 'errorPHP'), E_ALL); // устанавливаем пользовательский обработчик ошибок
    }

    static function getInstance()
    {
        if (null === self::$_instance) self::$_instance = new self();
        return self::$_instance;
    }

    private function __clone() {}
    private function __wakeup() {}


    /**
     * Вывод в консоль информации
     * @text String
     * @type List('log','info','warn','debug')
     */
    function console( $text, $type='log' )
    {
        $this->html .= $this->getCommand($text, $type);

        //echo '<hr>'.$this->html;
    }

    function log( $text )   { $this->html .= $this->getCommand($text, 'log'  , false); return $this; }
    function info( $text )  { $this->html .= $this->getCommand($text, 'info' ); return $this; }
    function warn( $text )  { $this->html .= $this->getCommand($text, 'warn' ); return $this; }
    function error( $text ) { $this->html .= $this->getCommand($text, 'error'); return $this; }
    function debug( $text ) { $this->html .= $this->getCommand($text, 'debug'); return $this; }

    /**
     * Формирование команды консоли
     * @text String
     * @type List('log','info','warn','debug')
     * @system Bool выводить или нет номер строки и адрес откуда был вызов функции
     */
    private function getCommand( $text, $type='log', $system=true )
    {
        $trace = '';
        if(!$system) {
            $trace = debug_backtrace();
            //получаю относительный путь
            $trace[2]['file'] = substr($trace[2]['file'], strlen($_SERVER['DOCUMENT_ROOT']) - strlen($trace[2]['file']) +1);
            $trace = elly::$lang['debug.dump'] .' | '. elly::$lang['debug.line'] .' '. $trace[2]['line'] .' | '. str_replace("\\","/",$trace[2]['file']);
            $trace.= '\n';
            }

        if(is_array($text))      $text = $this->arrayToStr( $text );
        elseif(is_object($text)) $text = 'object::'. get_class($text) .'\n'. $this->arrayToStr( $text, '  ' );
        else $text = $this->validation($text);

        return 'console.'.$type.'("'. $trace . $text .'");'.$this->rn;
    }

    public function validation($text)
    {
        //$text = preg_replace(array('/[\t]/','/[\r]/','/[\n]/','/[\v]/'), ' ', $text); //заменяем символы табуляции, переноса строки на пробел
        $text = preg_replace(array('/[\t]/','/[\r]/','/[\n]/'), ' ', $text); //заменяем символы табуляции, переноса строки на пробел
        $text = preg_replace('/[ ]{2,}/', ' ', $text); //удаляем повторные пробелы
        /*$text = str_replace("\\",'/',$text);
        $text = str_replace("\n",'\n',$text);
        $text = str_replace('"','\"',$text);*/
        $text = addslashes($text); //вместо предыдущих 3х фильтров
        $text = str_replace('\\\\n','\n',$text);

        //$text = strip_tags($text);
        return $text;
    }

    /**
     * Подготовка вывода Php-массива в консоль
     */
    private function arrayToStr( $arr, $step='')
    {
        $result = '';
        foreach($arr as $key => $val)
        {
            if(is_array($val)) {
                $result .= $step."[$key] ...".'\n';
                $result .= $this->arrayToStr( $val, $step.'  ');
                }
            else $result .= $step."[$key] = ".$this->validation($val).'\n';
        }
        return $result;
    }

    /**
     * Создать группу записей в консоле,
     * если есть уже, возвращает указатель и переназначает $close
     * @name    String название
     * @close   bool свернутая или нет
     * @count   bool вывод количества
     * @return  int идентификатор группы
     */
    function groupCreate($name, $close=false, $count=false)
    {
        foreach($this->group as $numder => $group)
            if($group['name'] == $name) {
                $this->group[$numder]['close'] = $close;
                $this->group[$numder]['count'] = $count;
                return $numder;
            }
        $this->group[]['name'] = $name;
        $numder = count($this->group) -1;
        $this->group[$numder]['close'] = $close;
        $this->group[$numder]['count'] = $count;
        return $numder;
    }

    /**
    * Добавление записей в группу логов
    * @key  int идентификатор груааы
    * @text String текст сообщения
    * @type List('log','info','warn','debug')
    */
    function groupAdd($key, $text, $type='log')
    {
        $this->group[$key]['log'][] = $this->getCommand($text, $type);
    }

    /**
     * Создать группу записей в консоле,
     * (быстрое создание)
     * @name    String название
     * @arr     Array данные
     * @close   bool свернутая или нет
     * @count   bool вывод количества
     */
    function group($name, $arr, $close=false, $count=false)
    {
        $this->groupAdd($this->groupCreate($name, $close, $count), $arr);
    }

    /**
     * Вывод всех скриптов на страницу
     * (располагать в самом конце index.php)
     */
    function debugEND()
    {
        if(!$this->Enable) return;

        $pTheme = CTheme::getInstance();
        //$this->show($this->group);

        /*
        // список подгруженных файлов
        $includes = get_included_files();
        if(count($includes) > 0) $this->groupAdd($this->groupCreate('include',true), $includes);
        */
        if(!empty($_POST)) $this->groupAdd($this->groupCreate('POST'), $_POST);
        //if(!empty($map))   $this->groupAdd($this->groupCreate('$map'), $map);

        // вывод всех созданных групп
        foreach($this->group as $group )
        if(count($group['log']) > 0)
        {
            $count = ($group['count'] === false) ? '' : ' ['.count($group['log']).']';
            if($group['close'] === false)
                 $this->html .= 'console.group("'.$group['name'].$count.'");'.$this->rn;
            else $this->html .= 'console.groupCollapsed("'.$group['name'].$count.'");'.$this->rn;

            foreach($group['log'] as $val) $this->html .= $val;

            $this->html .= 'console.groupEnd();'.$this->rn;
        }

        // дополнительная информация
        if(defined('AJAX')) $this->console('A J A X', 'info');

        if(is_object($pTheme)/* && is_object($pCore)*/)
             $this->info(elly::$lang['debug.timer'].' '.round($this->timeEnd('Timer'),4).elly::$lang['sec'].' (TPL: '.round($pTheme->statTimer,4).elly::$lang['sec'].'; SQL: '.round(CCore::$execTime,4).elly::$lang['sec'].')');
        else $this->info(elly::$lang['debug.timer'].' '.$this->timeEnd('Timer').elly::$lang['sec']);
        $this->info('Использовано памяти: '.round(memory_get_peak_usage()/1024, 2).'Kb, Выделено: '.round(memory_get_peak_usage(true)/1024, 2).'Kb');

        // отображение метки в случае ошибки
        if(count($this->error))
        {
            $img = empty($this->error['sql']) ? 'elly_debuger_php.png' : 'elly_debuger_sql.png';
            $home = HOME;
            $this->html .=
<<<LABEL
if($('#elly_debug_error').length == 0)
$('body').append('<div id="elly_debug_error"><div style="background: url($home/system/img/$img); width:192px; height:69px; position:fixed; bottom:10px; left:10px; z-index:9999"></div></div>');
else $('#elly_debug_error div').css('background','url($home/system/img/$img)');
LABEL;
        }

        //if(!defined('AJAX') || AJAX == 'html') echo $this->rn.'<script type="text/javascript" id="debug">';
        $result = $this->rn.
<<<DEBUG
if (!window.console || typeof(console) == 'undefined') console = {};
console.log  = console.log || function(){};
console.info = console.info || function(){};
console.warn = console.warn || function(){};
console.error = console.reeor || function(){};
console.debug = console.debug || function(){};
console.group = console.group || function(){};
console.groupCollapsed = console.groupCollapsed || function(){};
console.groupEnd = console.groupEnd || function(){};
console.clear = console.clear || function(){};
if(document.getElementById('elly_debug_error')) document.getElementById('elly_debug_error').remove();
if(typeof sessionStorage.debugClear == 'undefined' || sessionStorage.debugClear != '0') console.clear();
{$this->html}
DEBUG;
        //if(!defined('AJAX') || AJAX == 'html') echo $this->rn.'<script type="text/javascript" id="debug">'. $result .'</script>';
        if(!defined('AJAX')) echo $this->rn.'<script type="text/javascript" id="elly_debug_js">'. $result .'</script>';
        else return $result;
    }


    /*
     * Начало запуска таймера
     */
    function time($name)
    {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $this->timers[$name] = $mtime;
        return $this;
    }

    /*
     * Остановка таймера
     */
    function timeEnd($name)
    {
        $timeStart = $this->timers[$name];
        if ($timeStart)
        {
            $mtime = microtime();
            $mtime = explode(" ", $mtime);
            $mtime = $mtime[1] + $mtime[0];
            $endtime = $mtime;
            $totaltime = $endtime - $timeStart;
            $this->timers[$name] = null;
        }
        return $totaltime;
    }


    function errorPHP($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_USER_ERROR:
            case E_WARNING:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            case E_PARSE:
                $errstr  = strip_tags($errstr); //при добавлении в консоль валидируется
                $errstr  = $this->translate($errstr);  // онлайн перевод от Яндекса
                $this->error['php'] = true;
                break;
            default: return true; // не запускаем внутренний обработчик ошибок PHP
            }
        $group = $this->groupCreate('Error Php!', true);
        $this->groupAdd($group, $errstr, 'warn');
        $this->groupAdd($group, elly::$lang['debug.file'].' '.$errfile);
        $this->groupAdd($group, elly::$lang['debug.line'].' '.$errline);

		if ($errno == E_USER_ERROR)
        {
            echo elly::$lang['debug.error'];
            exit(1);
        }
        return true; // не запускаем внутренний обработчик ошибок PHP
    }

    function errorSQL($title, $sql='-', $dbtype)
    {
        if(!$this->Enable) return;

        $trace = debug_backtrace();
        foreach($trace as $key => $t)
            //if(in_array($t['function'], array('sqlSelect','sqlRecord','sqlDelete','sqlInsert','Query')))
            if($t['class'] == 'CDatabase' || $t['class'] == 'Model')
            $level = $key;
        if(empty($level))
            $level = count($trace)-1;

        $group = $this->groupCreate($title);

        if($dbtype == 'mysql')      $error = mysql_error(); // убрал str_replace("'","`",mysql_error())
        elseif($dbtype == 'mssql')  $error = mssql_get_last_message();

        $error = $this->translate($error);  // онлайн перевод от Яндекса

        $this->groupAdd($group, elly::$lang['error'].' '.$error,'warn');

        $this->groupAdd($group, elly::$lang['debug.request'].' '.$sql);
        $this->groupAdd($group, elly::$lang['debug.func'].' '.$trace[$level]['class'].$trace[$level]['type'].$trace[$level]['function']);
        $this->groupAdd($group, elly::$lang['debug.file'].' '.str_replace("\\","/",$trace[$level]['file']));
        $this->groupAdd($group, elly::$lang['debug.line'].' '.$trace[$level]['line']);

        $this->error['sql'] = true;
    }

    function show($arr) { echo '<br><pr', 'e>', print_r($arr, true), '</p', 're>'; }

    /**
     * Онлайн переводчик, если TRANSLATE 1, то от Яндекса, если 2 - от Google
     * @text    String текст для перевода
     * @in      String язык оригинала
     * @out     String язык перевода
     */
    function translate($text, $in='en', $out='ru')
    {
        switch( TRANSLATE )
        {
            case 1: $text = $this->translateY($text, $in.'-'.$out); break;
            case 2: $text = $this->translateG($text, $in, $out);    break;
        }
        return $text;
    }

    /**
     * Онлайн переводчик от Яндекса
     * @text    String текст для перевода
     * @lang    String направление перевода
     */
    function translateY($text, $lang='en-ru')
    {
        // Онлайн перевод от Яндекса
        // Не более 2000 символов зараз
        // Генерация ключа: http://api.yandex.ru/key/form.xml?service=trnsl

        if( !TRANSLATE ) return $text;

        $json = json_decode(file_get_contents("https://translate.yandex.net/api/v1.5/tr.json/translate?lang=$lang&text=". urlencode($text) ."&key=trnsl.1.1.20130925T072404Z.0508b8667ab64db2.d6be9e6d4256ed6d6e06edceaf164c58a94104df"));
        if(!empty($json->text[0])) $text = $json->text[0];
        return $text;
    }

    /**
     * Онлайн переводчик от Google
     * @text    String текст для перевода
     * @in      String язык оригинала
     * @out     String язык перевода
     */
    function translateG($text, $in='en', $out='ru')
    {
        if( !TRANSLATE ) return $text;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, "http://translate.google.com/translate_a/t?client=x&text=". urlencode($text) ."&sl=$in&tl=$out");
        curl_setopt($curl, CURLOPT_USERAGENT, " Mozilla/5.0 (Windows NT 6.1; rv:24.0) Gecko/20100101 Firefox/24.0");
        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        $trans = '';
        if(count($response->sentences))
            foreach($response->sentences as $i) $trans .= $i->trans;

        return $trans;
    }
}

/* Example: */
/*
$bg = new CDebug;
trigger_error("Не могу поделить на ноль", E_USER_NOTICE);

$arr = array('module'=>'uuu', 'code'=>'fds', 'link'=>'tttt','massiv'=>array('11','22'));

$hh;
$ff = 15 / 0;

$bg->console('Warning','warn');
$bg->console('Information','info');
$bg->console('Debuger','debug');
$bg->console($arr);

$gr_sql = $bg->groupCreate('SQL',true);
$bg->groupAdd($gr_sql,'sada "=" sds');
$bg->groupAdd($gr_sql,"sdsdfsdfsdf\nxv'_'df");
$bg->groupAdd($gr_sql,'33333 3');
$bg->groupAdd($gr_sql,'Oh, my got!');

$gr_rand = $bg->groupCreate('Случайный набор слов',true);
$bg->groupAdd($gr_rand,'круто!');
$bg->groupAdd($gr_rand,'вот это да!');
$bg->groupAdd($gr_rand,'еще бы!');
$bg->groupAdd($gr_rand,'Oh, my got!');

$bg->groupAdd($bg->groupCreate('M A P',true), $_SERVER);

echo '1='.$gr_rand.' & 2='.$bg->groupCreate('Случайный набор слов',true);

$bg->DebugEND();
*/
?>