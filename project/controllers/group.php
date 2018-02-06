<?php
/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */
ini_set('display_errors', true);
if (!defined('CS-SOFT'))
    die('access denited!');

class Group extends Controller
{

    public $eventHtml = '';
    public $eventArr = [];
    public $eventUserArr = [];
    public $eventGroupArr = [];
    public $size = 10;
    public $page = 0;
    public $total = 0;
    public $last = 0;

    function index()
    {
        $this->loadTheme();
        $this->_load_groups();
    }

    function event_load()
    {
        $group = (int) $_POST['code'];
        $this->_load_events($group);
        $this->json([
            'total' => $this->total,
            'size' => $this->size,
            'page' => $this->page,
            'pages' => $this->_pages()
        ]);
        $this->html($this->_html());
    }

    function file($fid = '')
    {
        $id = (int) $fid;
        $file = $this->loadModel('task_file')->find($id)[0]->table;
        $file_path = (!file) ? 'sdfghjk' : $this->dirUpload . $file['codeid'] . '.' . $file['file_ext'];

        if (!$file || $file['status'] < 0 || !file_exists($file_path)) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
            header('Status: 404 Not Found');
            exit();
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . str_replace(' ', '-', $this->_get_file_name($file)));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));

        readfile($file_path);
        exit;
    }

    function error404()
    {
        $this->loadTheme('main', false);
        $this->theme->content = '<h3 class="title404">' . elly::$lang['404.title'] . '</h3><br><p>' . elly::$lang['404.description'] . '</p>';
    }

    private function _load_groups()
    {
        foreach ($this->loadModel('sp_group')->find('status > -1') as $v) {
            $eventGroup = $this->_int_fields($v->table, ['codeid']);
            $this->eventGroupArr[$eventGroup['codeid']] = ['codeid' => $eventGroup['codeid'], 'name' => $eventGroup['name'], 'color' => $this->_get_color()];
        }
        $this->theme->table('eventGroups', $this->eventGroupArr);
//        debug($this->eventGroupArr);
    }

    private function _load_events($groupId)
    {
        foreach ($this->loadModel('view_event')->find('code_group = ' . (int) $groupId . ' ORDER BY date_system DESC') as $v) {
            $event = $this->_int_fields($v->table, ['codeid', 'code_group', 'code_color', 'price', 'is_primary', 'is_expired', 'status', 'is_photo', 'code_user']);
            $this->eventArr[$event['codeid']] = $event;
        }

        debug(count($this->eventArr));
        $this->_filter();
        debug(count($this->eventArr));

        $this->_load_users();
        $this->_load_comments();
        $this->_load_files();
        $this->_load_items();
    }

    private function _load_users()
    {
        $keys = array_keys($this->eventArr);
        foreach ($this->loadModel('view_event_struct')->find('code_event IN (' . implode(', ', array_unique($keys)) . ')') as $v) {
            $user = $this->_int_fields($v->table, ['code_event', 'code_struct', 'code_user', 'is_photo']);
            $this->eventUserArr[$user['code_struct']] = $user;
            $this->eventArr[$user['code_event']]['users'][] = $user;
        }
    }

    private function _load_files()
    {
        $keys = array_keys($this->eventArr);
        foreach ($this->loadModel('event_file')->find('code_event IN (' . implode(', ', array_unique($keys)) . ')') as $v) {
            $file = $this->_int_fields($v->table, ['code_event', 'codeid']);
            $this->eventUserArr[$file['code_struct']] = $file;
            $this->eventArr[$file['code_event']]['files'][] = $file;
        }
    }

    private function _load_items()
    {
        $keys = array_keys($this->eventArr);
        foreach ($this->loadModel('event_item')->find('code_event IN (' . implode(', ', array_unique($keys)) . ')') as $v) {
            $item = $this->_int_fields($v->table, ['code_event', 'codeid', 'status']);
            $this->eventUserArr[$item['code_struct']] = $item;
            $this->eventArr[$item['code_event']]['items'][] = $item;
        }
    }

    private function _load_comments()
    {
        $keys = array_keys($this->eventArr);
        foreach ($this->loadModel('view_event_comment')->find('code_event IN (' . implode(', ', array_unique($keys)) . ')') as $v) {
            $comment = $this->_int_fields($v->table, ['codeid', 'code_event', 'code_user', 'struct', 'is_photo', 'status']);
            $this->eventUserArr[$comment['code_struct']] = $comment;
            $this->eventArr[$comment['code_event']]['comments'][] = $comment;
        }
    }

    public function test()
    {
        $this->_load_events(1);
        foreach ($this->eventArr as $event) {
            if (empty($event['comments'])) {
                continue;
            }
            $this->_html_comments($event);
            var_export($event);
            die;
        }
    }

    private function _get_color()
    {
        if (empty($this->colorArr)) {
            $this->colorArr = array_map(function($v) {
                return $v->table['color'];
            }, $this->loadModel('sp_color')->find('codeid > 0'));
        }
        if (empty($this->colorArr[$this->colorIndex++])) {
            $this->colorIndex = 0;
        }
        return $this->colorArr[$this->colorIndex++];
    }

    private function _filter()
    {
        $this->total = count($this->eventArr);

        $this->size = empty((int) $_POST['size']) ? 10 : (int) $_POST['size'];
        $this->page = empty((int) $_POST['page']) ? 0 : (int) $_POST['page'];
        $this->last = ceil($this->total / $this->size) - 1;
        if ($this->page > $this->last) {
            $this->page = 0;
        }

        $skip = $this->size * $this->page;
        for ($index = 0; $index < $skip; $index++) {
            array_shift($this->eventArr);
        }

        if (count($this->eventArr) < $this->size) {
            return;
        }

        $events = [];
        for ($index = 0; $index < $this->size; $index++) {
            $event = array_shift($this->eventArr);
            $events[$event['codeid']] = $event;
        }
        $this->eventArr = $events;
    }

    private function _pages()
    {
        $pages = [];
        $pages[] = $this->_page('1', 0, $this->page == 0);
        if ($this->page > 3) {
            $pages[] = $this->_page('break', null, false);
        }
        if ($this->page > 2) {
            $pages[] = $this->_page($this->page - 1, $this->page - 2, false);
        }
        if ($this->page > 1) {
            $pages[] = $this->_page($this->page, $this->page - 1, false);
        }
        if ($this->page > 0 && $this->page < $this->last) {
            $pages[] = $this->_page($this->page + 1, $this->page, true);
        }
        if ($this->last - $this->page > 1) {
            $pages[] = $this->_page($this->page + 2, $this->page + 1, false);
        }
        if ($this->last - $this->page > 2) {
            $pages[] = $this->_page($this->page + 3, $this->page + 2, false);
        }
        if ($this->last - $this->page > 3) {
            $pages[] = $this->_page('break', null, false);
        }
        if ($this->page < $this->last) {
            $pages[] = $this->_page($this->last + 1, $this->last, false);
        }
        if ($this->page == $this->last && $this->last > 0) {
            $pages[] = $this->_page($this->last + 1, $this->last, true);
        }

        return $pages;
    }

    private function _page($title, $page, $active)
    {
        $active = $active ? ' active' : '';
        return compact('title', 'page', 'active');
    }

    private function _html()
    {
        setlocale(LC_ALL, 'ru_RU.UTF-8');
        $this->eventHtml = '<table id="process_list" class="entity_list">';
        foreach ($this->eventArr as $event) {
            $this->eventHtml .= $this->_html_event($event);
        }
        $this->eventHtml .= '</table>';
        return $this->eventHtml;
    }

    private function _html_event($event)
    {
//        debug($event);
        return '<tr id="process_' . $event['codeid'] . '" class="entity" style="padding:0px 0px 0px 10px">'
            . '<td class="event-left">' . $this->_html_event_title($event) . '</td>'
            . '<td>' . $this->_html_event_content($event) . '</td>'
            . '</tr>';
    }

    private function _html_event_content($event)
    {
        return '<div class="menu canEdit">
                    <div class="process_change_status">
                        <i class="color-icons status_blue_co"></i><i class="color-icons status_yellow_co"></i>																												<i class="color-icons status_gray_co"></i>
                    </div>
                    <div class="event-title">' . $event['title'] . '</div>
                    <div class="event-body">' . $event['body'] . '</div>
                    ' . $this->_html_comments($event) . '
                    ' . $this->_html_files($event) . '
                    ' . $this->_html_items($event) . '
                </div>';
    }

    private function _html_event_title($event)
    {
        return '<div class="bottom-line">
                    <span class="title">Дата создания:</span><br>' . $this->_html_system_date($event) . '
                </div>
                <div class="bottom-line">' . $this->_html_dates($event) . '</div>
                <div class="bottom-line">
                    <span class="title">Участники:</span>
                    <ul class="users">
                        ' . $this->_html_users($event) . '
                    </ul>
                </div>
                <div><span class="title">Автор:</span>
                <ul class="users">
                        ' . $this->_html_user($event) . '
                    </ul>
                </div>';
    }

    private function _html_comments($event)
    {
        if (empty($event['comments']) || !is_array($event['comments'])) {
            return '';
        }

        $html = '<div class="event-container event-comments col-sm-6">
                    <div class="event-container-head">Комментарии ( всего: <span id="process_total_comments_' . $event['codeid'] . '">' . count($event['comments']) . '</span> ):</div>
                    <ul id="process_comments_' . $event['codeid'] . '" class="message_comments">';
        foreach ($event['comments'] as $comment) {
            $html .= $this->_html_comment($comment);
        }
        return $html . '</ul></div>';
    }

    private function _html_comment($comment)
    {
        return '<li id="comment_' . $comment['codeid'] . '" style="font-size:11px" class="no-style">
                    <span class="user-thumb-sm pull-left"><img src="' . $this->_get_user_avatar($comment['is_photo'], $comment['code_user']) . '" alt="' . $comment['fio'] . '" style="' . $this->_html_user_color($comment) . '"></span>
                    <div class="comment_info">
                        <div class="comment_title">&nbsp;&nbsp;' . $comment['fio'] . ':<span class="comment_date pull-right" style="font-size:11px">' . rdate('j M, в H:i', strtotime($comment['date_system'])) . '</span></div>
                        <div class="comment_body">' . $comment['text'] . '</div>                        
                    </div>
                </li>';
    }

    private function _html_files($event)
    {
        if (empty($event['files']) || !is_array($event['files'])) {
            return '';
        }

        $html = '<div class="event-container event-files col-sm-6" style="border-left:1px solid #d7be99">
                    <div class="event-container-head">Файлы ( всего: <span id="process_total_files_">' . count($event['files']) . '</span> )</div>
                    <ul id="process_files_' . $event['codeid'] . '" class="message_files">';
        foreach ($event['files'] as $file) {
            $html .= $this->_html_file($file);
        }
        return $html . '</ul></div>';
    }

    private function _html_file($file)
    {
        return '<li id="file_' . $file['codeid'] . '" class="no-style" data-id="' . $file['codeid'] . '">
                    <span class="glyphicon glyphicon-paperclip pull-left"></span>
                    <div class="comment_info">
                        <div class="comment_title">&nbsp;&nbsp;' . $file['real_name'] . '<span class="comment_date pull-right">' . rdate('j M, в H:i', strtotime($file['date_system'])) . '</span></div>                        
                    </div>
                </li>';
    }

    private function _html_items($event)
    {
        if (empty($event['items']) || !is_array($event['items'])) {
            return '';
        }

        $html = '<div class="event-container event-items col-sm-6" style="border-left:1px solid #d7be99">
                    <div class="event-container-head">Подзадачи ( всего: <span id="process_total_items_">' . count($event['items']) . '</span> )</div>
                    <ul id="process_items_' . $event['codeid'] . '" class="message_items">';
        foreach ($event['items'] as $item) {
            $html .= $this->_html_item($item);
        }
        return $html . '</ul></div>';
    }

    private function _html_item($item)
    {
        return '<li id="item_' . $item['codeid'] . '" class="no-style" data-id="' . $item['codeid'] . '">
                    <span class="glyphicon glyphicon-flag pull-left"></span>
                    <div class="comment_info">
                        <div class="comment_title">&nbsp;&nbsp;' . $item['text'] . '<span class="comment_date pull-right">' . rdate('j M, в H:i', strtotime($item['date_system'])) . '</span></div>                        
                    </div>
                </li>';
    }

    private function _html_users($event)
    {
        if (empty($event['users'])) {
            return '';
        }
        $html = '';
        foreach ($event['users'] as $user) {
            $html .= $this->_html_user($user);
        }
        return $html;
    }

    private function _html_user($user)
    {
        return '<li>
                    <span class="user-thumb" title="' . $user['fio'] . '"><img src="' . $this->_get_user_avatar($user['is_photo'], $user['code_user']) . '" alt="' . $user['fio'] . '" style="' . $this->_html_user_color($user) . '"></span><br>
                    <span class="user-displayname"><strong>' . $user['fio'] . '</strong></span>
                </li>';
    }

    private function _html_user_color($user)
    {
        $color = isset($user['author_color']) ? $user['author_color'] : false;
        if (empty($color) && isset($user['color'])) {
            $color = $user['color'];
        }
        if (empty($color)) {
            $color = '#259ff7';
        }
        return 'border-color: ' . $color . ';';
    }

    private function _html_system_date($event)
    {
        return date('d.m.Y', strtotime($event['date_system']));
    }

    private function _html_dates($event)
    {
        $start = !empty($event['date_start']) ? strtotime($event['date_start']) : null;
        $end = !empty($event['date_end']) ? strtotime($event['date_end']) : null;
        if (!$end) {
            return rdate('d M, H:m', $start);
        }
        if (date('M', $start) != date('M', $end)) {
            return rdate('H:m, d M', $start) . ' - ' . rdate('H:m, d M', $end);
        }
        if (date('d', $start) != date('d', $end)) {
            return rdate('H:m, d', $start) . ' - ' . rdate('H:m, d M', $end);
        }
        return rdate('d M <b\r> H:m', $start) . ' - ' . rdate('H:m', $end);
    }

    private function _get_user_avatar($isset, $id)
    {
        return $isset ? '/public/upd/avatar/' . $id . '.jpg' : '/public/img/no_avatar.jpg';
    }
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

function rdate($param, $time = 0)
{
    if (intval($time) == 0)
        $time = time();
    $MonthNames = array("Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря");
    if (strpos($param, 'M') === false)
        return date($param, $time);
    else
        return date(str_replace('M', $MonthNames[date('n', $time) - 1], $param), $time);
}

?>