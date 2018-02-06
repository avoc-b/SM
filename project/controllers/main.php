<?php
/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */

if(!defined('CS-SOFT')) die('access denited!');

class Main extends Controller
{       
    function index()
    {
        //$this->loadTheme();
        $this->theme->select()->home  = HOME;
        $this->theme->theme = HOME .'/'. THEME;
        $this->theme->profile_fio   = $_SESSION['profile']['fio'];
        $this->theme->profile_name  = $_SESSION['profile']['name'];
        $this->theme->profile_img   = elly::avatar($_SESSION['profile']['is_photo'], $_SESSION['profile']['code_user']);

        if($this->theme->header == '')  $this->theme->header  = elly::HeaderHTML();
        if($this->theme->content == '') $this->theme->content = ''; //file_get_contents('static/define.html');
        if($this->theme->title == '')   $this->theme->title   = 'Главная';

        $this->theme->block(PLUGIN_WIKI)->wiki = '';
    }

    function error404()
    {        
        $this->loadTheme('main', false);
        $this->theme->content = '<h3 class="title404">'.elly::$lang['404.title'].'</h3><br><p>'.elly::$lang['404.description'].'</p>';
    }

    /*private function _avatar($isset, $id)
    {
        return $isset ? 'public/upd/avatar/' . $id . '.jpg' : 'public/img/no_avatar.jpg';
    }*/
}

/*
$pTheme->select()->home = HOME;
$pTheme->theme          = HOME .'/'. THEME;
if($pTheme->header == '')  $pTheme->header  = elly::HeaderHTML();
if($pTheme->content == '') $pTheme->content = ''; //file_get_contents('static/define.html');
if($pTheme->title == '')   $pTheme->title   = 'Главная';

// Блоки условного вывода
//$pTheme->block(!empty($_SESSION[USER_ID]))->hide = '';  
*/
?>