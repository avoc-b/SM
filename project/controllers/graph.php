<?php
/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */
if (!defined('CS-SOFT'))
    die('access denited!');

class Graph extends Controller
{

    function index()
    {
        $this->loadTheme();
    }

    // ---------- AJAX ------------- //

    function data()
    {
        $users = (!empty($_POST['users']) && is_array($_POST['users'])) ?
            'code_author IN (' . implode(',', array_map(function($v) {
                    return (int) $v;
                }, array_filter($_POST['users'], function($v) {
                        return $v > 0;
                    }))) . ') AND ' :
            '';
        $data = [];

        foreach ($this->loadModel('view_event')->find($users . 'status > -1') as $k => $v) {
            $event = $this->_int_fields($v->table, ['codeid', 'code_group', 'code_author', 'code_color', 'status', 'is_expired', 'is_photo', 'is_primary']);
            $data[$event['codeid']] = [
                'codeid' => $event['codeid'],
                'start' => empty($event['date_end']) ? $this->core->formatDate($event['date_start'], 'Y-m-d 00:00:00') : $this->core->formatDate($event['date_start'], 'Y-m-d H:i:s'),
                'end' => empty($event['date_end']) ? null : $this->core->formatDate($event['date_end'], 'Y-m-d H:i:s'),
                'content' => '<span class="title" data-original-title="' . $event['title'] . '" body="' . $event['body'] . '">' . $event['title'] . '</span>',
                'group' => $this->_html_user($event),
                'status' => $event['status'],
            ];
        }
        $this->json($data);
    }

    private function _html_user($event)
    {
        return '<span class="timeline user"><img src="' . $this->_html_user_avatar($event) . '"><b>' . $event['fio'] . '</b><br /><u>' . $event['struct'] . '</u></span>';
    }

    private function _html_user_avatar($event)
    {
        return $event['is_photo'] ? '/public/upd/avatar/' . $event['code_user'] . '.jpg' : '/public/img/no_avatar.jpg';
    }

    function error404()
    {
        $this->loadTheme('main', false);
        $this->theme->content = '<h3 class="title404">' . elly::$lang['404.title'] . '</h3><br><p>' . elly::$lang['404.description'] . '</p>';
    }
    // ------------- SPECIAL -------------- //
// ----------------------------------- DEV ----------------------------
}

/*
  $pTheme->select()->home = HOME;
  $pTheme->theme          = HOME .'/'. THEME;
  if($pTheme->header == '')  $pTheme->header  = elly::HeaderHTML();
  if($pTheme->content == '') $pTheme->content = ''; //file_get_contents('static/define.html');
  if($pTheme->title == '')   $pTheme->title   = 'Главная';

  // Блоки условного вывода
  //$pTheme->block(!empty($_SESSION[USER_ID]))->hide = '





  ';
 */

?>