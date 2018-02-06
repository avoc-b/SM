<?php
/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2016
 */

if(!defined('CS-SOFT')) die('access denited!');

class Wiki extends Plugin
{ 
    private $path = 'project/cache/';
    private $conf = 'config';
    private $confArr = null;
    
    function index()
    {
        if(!PLUGIN_WIKI && !DEBUG && elly::checkIP() != '127.0.0.1') return false;                       
        
        $file = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        $file = $this->path . str_replace(array('.','/','\\'), '', $file) .'.txt';
        
        if(!is_file($file) && !empty($_SERVER['QUERY_STRING'])) return false;
        
        
        //$ini = $this->_getConfig();
        $this->_getConfig();
            
        $pages = array();       
        foreach (glob($this->path ."*.txt") as $f)
        {
            $filename = basename($f, '.txt');
            if($filename == $this->conf) continue;
            
            $pages = $this->_menuAdd($filename, $pages);
            /*                
            if(strpos($filename, ':') === false)
            {
                $pages[$filename] = (empty($ini[$filename]) || empty($ini[$filename]['title'])) ? array('title'=>'без имени') : $ini[$filename];
                $pages[$filename]['link'] = $filename;
            }
            else 
            {                    
                $path    = explode(':', $filename);
                $out     = '{';
                $ini_key = '';
                foreach($path as $v)
                {
                    $ini_key = trim($ini_key .':'. $v, ':');
                    
                    $ini_arr = (empty($ini[$ini_key]) || empty($ini[$ini_key]['title'])) ? array('title'=>'') : $ini[$ini_key]; // {"title":"TEXT","flag":"TEXT"}
                    if(is_file($this->path . $ini_key .'.txt')) $ini_arr['link'] = $ini_key;
                    $ini_arr = trim(json_encode($ini_arr), '{}');
                    $out .= '"'. $v .'":{'. $ini_arr .', "items":{';    
                }
                $out   = substr($out, 0, -11) . str_repeat('}', count($path)*2);
                $out   = json_decode($out, true);                    
                $pages = $this->_arrayAdd($pages, $out);
            }*/
        }
                        
        $this->loadTheme(false, 'index');
        $this->theme->header = elly::HeaderHTML(TITLE .' :: WiKi');
        $this->theme->site   = TITLE;
        $this->theme->menu   = '<ul class="wiki_menu">'. $this->_menuHtml($pages) .'</ul>';
                
        if(is_file($file))
        {
            $this->theme->title  = $this->_getConfig($file);
            $this->theme->text   = file_get_contents($file);
        }
        elseif(empty($_SERVER['QUERY_STRING']))
        {
            $this->theme->title  = 'WiKi';
            $this->theme->text   = $this->theme->menu;
        }
        else return false;
    }
    
    private function _menuAdd($file, $out = array(), $link = '')
    {
        list($v, $file)  = explode(':', $file, 2);
        $link = trim($link.':'.$v, ':'); 
        $out2 = empty($out[$v]['items']) ? array() :  $out[$v]['items'];
        
        if(!empty($this->confArr[$link])) $out[$v] = $this->confArr[$link];        
        if(is_file($this->path . $link .'.txt'))
        {
            $out[$v]['link'] = $link;
            $out[$v]['size'] = filesize($this->path . $link .'.txt');
        }
                
        if(!empty($file)) $out[$v]['items'] = $this->_menuAdd($file, $out2, $link);
                     
        return $out;
    }
    
    // не используется
    private function _arrayAdd($arr1, $arr2)
    {
        foreach($arr2 as $k => $v)
        {
            if(empty($arr1[$k]) || !is_array($v)) $arr1[$k] = $v;
            else $arr1[$k] = $this->_arrayAdd($arr1[$k], $v);
        }
        return $arr1;
    }
    
    private function _menuHtml($arr)
    {        
        foreach($arr as $k => $v)
        {
            $title = $v['title'] ? $v['title'] : 'Раздел "'. $k .'"';
            $class = ($v['link'] && !$v['size']) ? ' class="wiki_menu_empty"' : '';
            if($v['link']) $title = '<a href="/wiki?'. $v['link'] .'">'. $title .'</a>';
            
            if(!empty($v['items'])) 
            {                
                $html .= '<li'. $class .'><span>'. $title .PHP_EOL .'</span><ul>'. PHP_EOL. $this->_menuHtml($v['items']) .'</ul></li>'. PHP_EOL;
            }
            else $html .= '<li'. $class .'>'. $title .'</li>'. PHP_EOL;            
        }
        return $html;
    }
    
    function write()
    {
        if(!PLUGIN_WIKI && !DEBUG && elly::checkIP() != '127.0.0.1') return false;
        
        $file = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
        $file = $this->path . str_replace(array('.','/','\\'), '', $file) .'.txt';
        
        if(is_file($file)) file_put_contents($file, $_POST['text']);
    }
    
    function create()
    {
        if(!PLUGIN_WIKI && !DEBUG && elly::checkIP() != '127.0.0.1') return false;
        
        $file = $this->path . str_replace(array('.','/','\\'), '', trim($_POST['file'])) .'.txt';
        if(!is_file($file)) 
        {
            file_put_contents($file, '');
            
            // прописываю названия заголовка в конфиге
            $ini = $this->_getConfig();
            $ini[$_POST['file']] = array('title' => trim($_POST['title']));
            $this->_setConfig($ini);
        }
    }
    
    function delete()
    {
        $file = $this->path . $_POST['file'] .'.txt';
        
        if($_POST['file'] == $this->conf)   $this->json(1);
        elseif(filesize($file) != 0)        $this->json(2);
        else
        {
            if(is_file($file)) @unlink($file);
            
            // убираю записи из конфига
            $ini = $this->_getConfig();
            unset($ini[$_POST['file']]);
            $this->_setConfig($ini);
            $this->json(0);
        }
    }
    
    function form_create()
    {
        $this->loadTheme();
    }
    
    function form_list()
    {
        $ini = $this->_getConfig();
        
        foreach (glob($this->path ."*.txt") as $file)
        {   
            $filename = basename($file, '.txt');
            $class    = '';                          
            if($filename == $this->conf) {
                $title = '<настройки wiki>';
                $class = ' class="wiki_list"';
                }          
            elseif(empty($ini[$filename]) || empty($ini[$filename]['title']))
                 $title = '<без имени>';
            else $title = $ini[$filename]['title'];
            $options .= '<option value="'.$filename.'"'.$class.'>'. $title .' ('. $this->_formatSize(filesize($file)) .")</option>\n";
        }
        
        $this->loadTheme();
        $this->theme->list = $options;
    }
    
    private function _getConfig($item = null)
    {
        if($this->confArr === null)
        {
            $file = $this->path . $this->conf .'.txt';
            if(!is_file($file)) return false;
            
            $this->confArr  = parse_ini_file($file, true);
        }
        if(is_null($item)) return $this->confArr;
        else
        {
            $filename = basename($item, '.txt');
            if($filename == $this->conf) return '<настройки wiki>';
            if(empty($this->confArr[$filename]) || empty($this->confArr[$filename]['title'])) return '<без имени>';
            return $this->confArr[$filename]['title'];
        }
        /*
        $file = $this->path . $this->conf .'.txt';
        if(!is_file($file)) return false;
        
        $ini  = parse_ini_file($file, true);
        if(is_null($item)) return $ini;
        else
        {
            $filename = basename($item, '.txt');
            if($filename == $this->conf) return '<настройки wiki>';
            if(empty($ini[$filename]) || empty($ini[$filename]['title'])) return '<без имени>';
            return $ini[$filename]['title'];
        }*/
    }
    
    private function _setConfig($data)
    {        
        if(!is_array($data)) return false;
        $out = '';
        foreach($data as $header => $body)
        {
            if(!is_array($body)) $out .= $header .' = "'. $body .'"'. PHP_EOL;
            else  
            {
                $out .= '['. $header .']'. PHP_EOL;
                foreach($body as $k => $v)
                {
                    $out .= $k .' = "'. $v .'"'. PHP_EOL;
                }
            }
        }
        file_put_contents($this->path . $this->conf .'.txt', $out);
    }
    
    private function _formatSize($size)
    {
        $mod = 1024;
        $units = explode(' ', 'B KB MB GB TB PB');
        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }
        return round($size, 2, PHP_ROUND_HALF_UP) . ' ' . $units[$i];
    }
}
?>