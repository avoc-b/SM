<?php
/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */
@include_once('config.php');

function show($arr)
{
    echo '<br><pr', 'e>', print_r($arr, true), '</p', 're>';
}

class elly
{

    public static $LangSystem;
    public static $lang;

    /**
     * Подгрузка языкового пакета
     * (предыдущая загрузка не очищается)
     */
    static function LoadLang($what, $where = '', $area = '')
    {
        global $lang;

        self::$LangSystem = (self::$LangSystem) ? self::$LangSystem : LANG;
        $where = ($where) ? '/' . $where : '';

        if (!file_exists($toinc = './system/lang/' . self::$LangSystem . $where . '/' . $what . '.ini')) {
            $toinc = './system/lang/russian/' . $where . '/' . $what . '.ini';
        }
        if (file_exists($toinc)) {
            $content = parse_ini_file($toinc, true);
            if (!is_array(self::$lang))
                self::$lang = array();
            if ($area)
                self::$lang[$area] = $content;
            else
                self::$lang = array_merge(self::$lang, $content);
        }
        $lang = self::$lang;
        return self::$lang;
    }

    /**
     * Возвращает ip-адрес
     */
    static function checkIP()
    {
        if (getenv("REMOTE_ADDR"))
            return getenv("REMOTE_ADDR");
        elseif ($_SERVER["REMOTE_ADDR"])
            return $_SERVER['REMOTE_ADDR'];

        return "unknown";
    }

    /**
     * Определяет предпочитаемый язык пользователя 
     * @return String
     */
    static function userLang()
    {
        preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]), $matches);
        $langs = array_combine($matches[1], $matches[2]);       // Создаём массив с ключами $matches[1] и значениями $matches[2]
        foreach ($langs as $n => $v)
            $langs[$n] = $v ? $v : 1;  // Если нет q, то ставим значение 1
        arsort($langs);                                         // Сортируем по убыванию q
        return key($langs);                                     // Берём 1-й элемент (он же максимальный по q), Выводим язык по умолчанию
    }

    /**
     * Генерация пароля заданной длины
     * @lenght  Integer длина пароля
     * @allow   String набор символов
     */
    static function GeneratorPass($length, $allow = "abcdefghijklmnopqrstuvwxyz0123456789")
    {
        $i = 1;
        while ($i <= $length) {
            $max = strlen($allow) - 1;
            $num = rand(0, $max);
            $temp = substr($allow, $num, 1);
            $ret = $ret . $temp;
            $i++;
        }
        return $ret;
    }

    /**
     * Вывод заголовков HTML
     * @title    String приставка к заголовоку страницы
     * @keywords String ключевые поля
     * @img      String ссылка к главной картинке страницы
     */
    static function HeaderHTML($title = '', $keywords = '', $img = '')
    {
        if (!empty($title)) {
            $result = '<title>' . $title . "</title>\r\n";
            $result .= '<meta property="og:title" content="' . htmlspecialchars($title) . '" />';
        } else
            $result = '<title>' . TITLE . '</title>';
        //$result .= '<script type="text/javascript" src="'.HOME.'/system/res/js/core.js"></script>';

        if (!empty($img))
            $result .= '
    	<meta property="og:description" content="" />
    	<meta property="og:image" content="' . HOME . '/' . $img . '" />
    			   ';

        if (!empty($keywords))
            $result .= '<meta name="keywords" content="' . htmlspecialchars($keywords) . '">
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
        $count = ceil($count);

        if ($count > 1) {
            $paginator = '';
            if ($page > 3)
                $paginator .= ' <a class="page" href="' . self::_link($link, 1, $crypt) . '">&laquo;</a> ';
            for ($i = ($page - 2); $i < ($page + 5); $i++) {
                if ($i > 0 AND $i < $count + 1) {
                    if ($i == ($page + 1))
                        $alink = ' <b>' . $i . '</b> ';
                    else
                        $alink = ' <a class="page" href="' . self::_link($link, $i, $crypt) . '">' . $i . '</a> ';
                    $paginator .= $alink;
                }
            }
            if ($page < $count - 4)
                $paginator .= '<a class="page" href="' . self::_link($link, $count, $crypt) . '">&raquo;</a> ';
            return "Страницы: " . $paginator;
        }
    }

    static private function _link($link, $id, $crypt)
    {
        if ($crypt) {
            $pCore = CCore::getInstance();
            return 'index.php?go=' . $pCore->encrypt($link . $id);
        } else
            return $link . $id;
    }

    /**
     * Склонение числительных
     * @forms - например: день, дня, дней
     */
    static function skolko($n, $form1, $form2, $form5)
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20)
            return $n . ' ' . $form5;
        if ($n1 > 1 && $n1 < 5)
            return $n . ' ' . $form2;
        if ($n1 == 1)
            return $n . ' ' . $form1;
        return $n . ' ' . $form5;
    }

    /**
     * Склонение городов и стран: где? - в Бишкеке, в Кыргызстане
     * функция не охватывает некоторые правила исключений
     */
    static function gde($str)
    {
        $end = mb_substr($str, -1, 1);
        //array('а','е','ё','и','й','о','у','ы','э','ю','я'); // гласные
        //array('е','и','о','у','ы');                         // несклоняющиеся

        if ($end == 'я')
            return substr_replace($str, 'и', -1, 1);
        if (in_array($end, array('е', 'и', 'о', 'у', 'ы')))
            return $str;
        if (in_array($end, array('а', 'ё', 'й', 'э', 'ю')))
            return substr_replace($str, 'е', -1, 1);
        else
            return $str . 'е';
    }

    static function is_iterable($var)
    {
        return (is_array($var) || $var instanceof Traversable);
    }

    static function avatar($isset, $id)
    {
        return $isset ? 'public/upd/avatar/' . $id . '.jpg' : 'public/img/no_avatar.jpg';
    }
}

?>