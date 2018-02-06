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

class Files extends Controller
{

    private $dirUpload = 'public/upd/files/';
    private $fileTypeArr = [];
    public $eventGroupArr = [];
    private $extensions = [
        'Изображения' => ['jpg', 'jpeg', 'png', 'bmp', 'gif'],
        'Документы' => ['doc', 'docx', 'pdf', 'txt'],
        'Таблицы' => ['xls', 'xlsx'],
        'Архивы' => ['rar', 'zip']
    ];
    private $fileArr = [];
    public $size = 10;
    public $page = 0;
    public $total = 0;
    public $last = 0;

    function index()
    {
        $this->loadTheme();
        $this->_load_extensions();
        $this->_load_groups();
    }

    function data_load()
    {
        $post = $this->core->validationPOST()['obj'];

        $exts = [];
        $events = [];

        if (!empty($post['exts'] && is_array($post['exts']))) {
            foreach ($post['exts'] as $ext) {
                if (isset($this->extensions[$ext])) {
                    $exts = array_merge($exts, $this->extensions[$ext]);
                }
            }
        }

        if (!empty($post['groups'] && is_array($post['groups']))) {
            $events = $this->_load_events($post['groups']);
        }

        if (!empty($post['events'] && is_array($post['events']))) {
            foreach ($post['events'] as $id) {
                if (isset($events[$id])) {
                    continue;
                }
                $events[$id] = $id;
            }
        }

        $this->_load_files($events, $exts, $post);
        $this->json(['data' => $this->fileArr, 'pages' => $this->_pages(), 'total' => $this->total]);
        $this->html($this->_html());
    }

    function file($fid = '')
    {
        $id = (int) $fid;
        $file = $this->loadModel('event_file')->find($id)[0]->table;
        $file_path = (!$file) ? 'sdfghjk' : $this->dirUpload . $file['url'];

        if (!$file || $file['status'] < 0 || !file_exists($file_path)) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
            header('Status: 404 Not Found');
            header('Location: /file-ne-naiden');
            exit();
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . str_replace(' ', '-', $file['real_name']));
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
        foreach ($this->loadModel('sp_group')->find() as $v) {
            $eventGroup = $this->_int_fields($v->table, ['codeid']);
            $this->eventGroupArr[$eventGroup['codeid']] = ['codeid' => $eventGroup['codeid'], 'name' => $eventGroup['name'], 'color' => $this->_get_color()];
        }
        $this->theme->table('eventGroups', $this->eventGroupArr);
//        debug($this->eventGroupArr);
    }

    private function _load_extensions()
    {
        foreach ($this->core->table('event_file')
            ->group('ext')
            ->range('ext, count(*) [count]') as $v) {
            $Extension = $this->_int_fields($v, ['count']);
            $k = $this->_get_ext_type($Extension['ext']);
            $this->fileTypeArr[$k]['count'] += $Extension['count'];
            $this->fileTypeArr[$k]['ext'][] = $Extension['ext'];
            if (empty($this->fileTypeArr[$k]['name'])) {
                $this->fileTypeArr[$k]['name'] = $k;
            }
            if (empty($this->fileTypeArr[$k]['color'])) {
                $this->fileTypeArr[$k]['color'] = $this->_get_color();
            }
        }
        $this->theme->table('fileTypes', $this->fileTypeArr);
    }

    private function _get_ext_type($ext)
    {
        foreach ($this->extensions as $k => $v) {
            if (in_array($ext, $v)) {
                return $k;
            }
        }
    }

    private function _load_events($groupIds)
    {
        if (empty($groupIds)) {
            $where = '';
        } elseif (is_array($groupIds)) {
            $where = 'code_group IN (' . implode(', ', array_unique(array_map(function($v) {
                            return (int) $v;
                        }, $groupIds))) . ')';
        } else {
            $where = 'code_group = ' . (int) $groupIds;
        }
        $events = [];
        foreach ($this->loadModel('view_event')->find($where . ' ORDER BY date_system DESC') as $v) {
            $event = $this->_int_fields($v->table, ['codeid', 'code_group', 'code_color', 'price', 'is_primary', 'is_expired', 'status', 'is_photo', 'code_user']);
            $events[$event['codeid']] = $event;
        }
        return $events;
    }

    private function _load_files($events = null, $ext = null, $post = null)
    {
        $find = (!empty($events) && is_array($events)) ? 'code_event IN (' . implode(', ', array_unique(array_keys($events))) . ')' : '';
        $find .= ($find === '' || empty($ext) || !is_array($ext)) ? '' : ' AND ';
        $find .= (!empty($ext) && is_array($ext)) ? 'ext IN (' . implode(', ', array_map(function($v) {
                    return "'$v'";
                }, array_unique($ext))) . ')' : '';
        if (empty($find) || $find == '') {
            $find = 'codeid > 0';
        }


        foreach ($this->loadModel('event_file')->find($find . ' ORDER BY date_system DESC') as $v) {
            $file = $this->_int_fields($v->table, ['code_event', 'codeid']);
            $this->fileArr[$file['codeid']] = $file;
        }
        $this->_filter($post);
    }

    public function test()
    {
        $events = $this->_load_events(2);
        $this->_load_files($events, ['png', 'docx', 'jpg']);
        var_dump($this->fileArr);
        die;
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

    private function _filter($post = null)
    {
        $this->total = count($this->fileArr);

        $this->size = empty((int) $post['size']) ? 10 : (int) $post['size'];
        $this->page = empty((int) $post['page']) ? 0 : (int) $post['page'];
        $this->last = ceil($this->total / $this->size) - 1;
        if ($this->page > $this->last) {
            $this->page = 0;
        }

        $skip = $this->size * $this->page;
        for ($index = 0; $index < $skip; $index++) {
            array_shift($this->fileArr);
        }

        if (count($this->fileArr) < $this->size) {
            return;
        }

        $files = [];
        for ($index = 0; $index < $this->size; $index++) {
            $file = array_shift($this->fileArr);
            $files[$file['codeid']] = $file;
        }
        $this->fileArr = $files;
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
        $html = '<div class="event-container event-files col-sm-6" style="border-left:1px solid #d7be99">
                    <div class="event-container-head">Файлы ( всего: <span id="process_total_files_">' . $this->total . '</span> )</div>
                    <ul id="process_files" class="message_files">';
        foreach ($this->fileArr as $file) {
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

    private function _html_system_date($event)
    {
        return date('d.m.Y', strtotime($event['date_system']));
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