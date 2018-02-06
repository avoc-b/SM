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

class Task extends Controller
{

    private $taskArr = array();
    private $taskCatArr = array();
    private $taskSructArr = array();
    private $dirUpload = 'public/upd/files/';

    function index()
    {

        $this->loadTheme();

        $this->_load_task_categories();
        $this->_load_tasks();
        $this->theme->struct = $this->_html(0);
    }

    function task_group()
    {
        $this->_load_tasks(0, 'code_category=' . (int) $_POST['code']);
//        $this->json($this->_html(0));
        $this->html($this->_html(0));
    }

    function load()
    {
        $task = $this->_int_fields($this->loadModel('view_task')->find(intval($_POST['code']))[0]->table, [
            'codeid', 'code_parent', 'code_category', 'code_color', 'all_day', 'priority', 'active', 'notify_sms', 'notify_email', 'cycle_type', 'is_photo'
        ]);

        if (empty($task)) {
            $this->json(['code' => 0]);
            return;
        }

        $task['date_begin'] = date('H:i', strtotime($task['date_begin']));
        $task['date_end'] = date('H:i', strtotime($task['date_end']));
        $task['notify'] = $task['notify_sms'] || $task['notify_email'];

        $task['cycle_value_1_1'] = $task['cycle_value_1_2'] = $task['cycle_value_2'] = $task['cycle_value_3'] = 0;
        if ($task['cycle_type'] == 1) {
            list($task['cycle_value_1_1'], $task['cycle_value_1_2']) = explode('.', $task['cycle_value']);
        } elseif ($task['cycle_type'] < 4) {
            $task['cycle_value_' . $task['cycle_type']] = (int) $task['cycle_value'];
        }

        $task['files'] = array_map(function($v) {
            $file = $this->_int_fields($v->table, ['codeid', 'code_task', 'code_user']);
            return [
                'code' => $file['codeid'],
                'url' => $this->dirUpload . $file['codeid'] . '.' . $file['file_ext'],
                'name' => $this->_get_file_name($file),
                'delete' => $_SESSION[USER_ID] == $file['code_user'] ? 1 : 0
            ];
        }, $this->loadModel('task_file')->find('code_task = ' . $task['codeid'] . ' AND status > -1'));

        $task['performers'] = array_map(function($v) {
            return $this->_int_fields($v->table, ['codeid', 'code_task', 'code_color', 'is_photo', 'code_struct']);
        }, $this->loadModel('view_task_performer')->find('code_task=' . $task['codeid']));

        $this->json($task);
    }

    function tasks()
    {
        $id = (int) $_POST['id'];
        $this->_load_tasks($id);
        $this->html($this->_html($id));
    }

    function delete()
    {
        $exec = $this->core->exec('do_task', array('action' => 2, 'codeid' => intval($_POST['code']), 'status' => -1), array('code' => 'BIGINT'));
        $this->json(['code' => (int) $exec['code']]);
    }

    function active()
    {
        $task = $this->loadModel('task')->find(intval($_POST['code']))[0]->table;
        if (empty($task)) {
            $this->json(['code' => 0]);
            return;
        }
        $exec = $this->core->exec('do_task', array('action' => 3, 'codeid' => intval($_POST['code']), 'active' => (int) $task['active'] == 1 ? 0 : 1), array('code' => 'BIGINT'));
        $this->json(array('code' => (int) $exec['code'], 'active' => ((int) $task['active']) == 1 ? 0 : 1));
    }

    function create()
    {
        
    }

    function save()
    {
        file_put_contents('post_data.json', json_encode($_POST, JSON_PRETTY_PRINT));

        $post = $this->_int_fields($this->core->validationPOST(), ['code_category', 'codeid', 'code_parent', 'price', 'cycle_type']);
        $post['action'] = 1;
        $post['code_struct'] = $_SESSION[USER_ID];

//привожу к XML виду список участников
        $post['users_xml'] = '<xml>';
        $post['users_xml'] .= join('', array_map(function($v) {
                return '<i><code>' . $v . '</code></i>';
            }, explode(';', $post['users'])));
        $post['users_xml'] .= '</xml>';


        $post['all_day'] = empty($post['all_day']) ? 0 : 1;
        $post['priority'] = empty($post['priority']) ? 0 : 1;
        $post['active'] = empty($post['active']) ? 0 : 1;

        $post['cycle_value'] = 0;
        if ($post['cycle_type'] == 1) {
            $post['cycle_value'] = $post['cycle_value_1_1'] . '.' . $post['cycle_value_1_2'];
        } elseif ($post['cycle_type'] < 4) {
            $post['cycle_value'] = (int) $post['cycle_value_' . $post['cycle_type']];
        }

        $post['date_begin'] = empty($post['date_begin']) ? '00:00:00.000' : date('H:i:00.000', strtotime($post['date_begin']));
        $post['date_end'] = empty($post['date_end']) ? '00:00:00.000' : date('H:i:00.000', strtotime($post['date_end']));

        $post['notify_sms'] = (empty($post['notify_sms']) || empty($post['notify'])) ? 0 : 1;
        $post['notify_email'] = (empty($post['notify_sms']) || empty($post['notify'])) ? 0 : 1;
        $post['notify_text'] = ($post['notify_sms'] && $post['notify_email']) ? '' : $post['notif_text'];

        unset($post['users'], $post['notify'], $post['cycle_value_1_1'], $post['cycle_value_1_2'], $post['cycle_value_2'], $post['cycle_value_3']);
        $post['code_struct'] = $_SESSION[USER_ID];

        $exec = $this->core->exec('do_task', $post, array('code' => 'BIGINT'));
        $this->json($exec);
        $this->script('location.reload();');
//        $this->json($post);
    }

    function upload()
    {
        $id = intval($_POST['code']);
        $ext = array(
            'image/jpeg', 'image/png', 'image/gif', 'image/bmp',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/pdf',
            'application/octet-stream', //rar
            'application/zip',
            'text/plain', //txt
        );
        if (!in_array($_FILES['file']['type'], $ext)) {
            $this->json(-1);
            return;
        }

//        pathinfo ( string $path [, int $options = PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME ] );

        $pathInfo = pathinfo($_FILES['file']['name']);

        $exec = $this->core->exec('do_task', array(
            'action' => 4,
            'code_task' => $id, //code_task,
            'code_struct' => $_SESSION[USER_ID],
            'file_name' => $pathInfo['filename'],
            'file_ext' => $pathInfo['extension']
            ), array('code' => 'BIGINT'));

        $filePath = $this->dirUpload . $exec['code'] . '.' . $pathInfo['extension'];
        move_uploaded_file($_FILES['file']['tmp_name'], $filePath);
        $this->json(array('code' => $exec['code'], 'url' => $filePath, 'name' => $pathInfo['basename'], 'delete' => 1));
    }

    function file_delete()
    {
        $file = $this->_int_fields($this->loadModel('task_file')->find((int) $_POST['code'])[0]->table, ['codeid', 'code_task', 'code_user', 'status']);
        if ($file['code_user'] == $_SESSION[USER_ID]) {
            $exec = $this->core->exec('do_task', array('action' => 5, 'status' => -1, 'codeid' => $file['codeid']), array('code' => 'BIGINT'));
            if (is_readable($this->dirUpload . $file['codeid'] . '.' . $file['file_ext'])) {
                unlink($this->dirUpload . $file['codeid'] . '.' . $file['file_ext']);
            }
            $this->json(['code' => $file['codeid']]);
        } else {
            $this->json(-1);
        }
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

    private function _load_tasks($code_parent = 0, $where = '')
    {
        $if = [];
        if ($code_parent !== null && is_numeric($code_parent)) {
            $if[] = 'code_parent = ' . $code_parent;
        }
        if ($where !== '' && !empty($where)) {
            $if[] = $where;
        }

        foreach ($this->loadModel('view_task')->find(implode(' AND ', $if) . ' ORDER BY date_system DESC') as $v) {
            $task = $this->_int_fields($v->table, ['codeid', 'code_parent', 'code_category', 'code_struct', 'cycle_type', 'all_day', 'priority', 'active', 'notify_sms', 'notify_email', 'is_photo', 'children']);
            $this->taskArr[$task['codeid']] = $task;
        }

        $this->_load_task_performers();
        foreach ($this->taskArr as $task) {
            $this->taskSructArr[$task['code_parent']][] = $task;
        }
    }

    private function _load_task_performers()
    {
        if (empty($this->taskArr)) {
            return false;
        }

        foreach ($this->loadModel('view_task_performer')->find(
            'code_task IN (' . join(', ', array_keys($this->taskArr)) . ')'
        ) as $v) {
            $performer = $this->_int_fields($v->table, ['code_task', 'code_color', 'is_photo']);
            $this->taskArr[$performer['code_task']]['performers'][] = $performer;
        }
        return true;
    }

    private function _load_task_categories()
    {
        foreach ($this->loadModel('view_task_category')->find() as $v) {
            $taskCategory = $this->_int_fields($v->table, ['codeid', 'status', 'count']);
            $this->taskCatArr[$taskCategory['codeid']] = ['codeid' => $taskCategory['codeid'], 'name' => $taskCategory['name'], 'count' => $taskCategory['count'], 'color' => $this->_get_color()];
        }
        $this->theme->table('taskCategories', $this->taskCatArr);
        debug($this->taskCatArr);
    }

    private function _yearly($value)
    {
        list($month, $day) = explode('.', $value);
        return $day . ' ' . $this->_months(true)[$month - 1];
    }

    private function _monthly($value)
    {
        return $value . ' числа';
    }

    private function _weekly($value)
    {
        return $this->_weeks(3)[$value - 1];
    }

    private function _periodicity($task)
    {
        switch ($task['cycle_type']) {
            case 1: return 'Каждый год<br>' . $this->_yearly($task['cycle_value']);
            case 2: return 'Каждый месяц<br>' . $this->_monthly($task['cycle_value']);
            case 3: return 'Каждую неделю<br>' . $this->_weekly($task['cycle_value']);
            case 4: return 'Каждый день';
            default: return '';
        }
        return $task['cycle_type'];
    }

    private function _months(
    $form = false)
    {
        if ($form == true) {
            return [
                'Января',
                'Февраля',
                'Марта',
                'Апреля',
                'Мая',
                'Июня',
                'Июля',
                'Августа',
                'Сентября',
                'Октября',
                'Ноября',
                'Декабря',
            ];
        }
        return [
            'Январь',
            'Февраль',
            'Март',
            'Апрель',
            'Май',
            'Июнь',
            'Июль',
            'Август',
            'Сентябрь',
            'Октябрь',
            'Ноябрь',
            'Декабрь',
        ];
    }

    private function _weeks($form = false)
    {
        if ($form == 3) {
            return [
                'в Понедельник',
                'во Вторник',
                'в Среду',
                'в Четверг',
                'в Пятницу',
                'в Субботу',
                'в Воскресенье'
            ];
        }
        return [
            'Понедельник',
            'Вторник',
            'Среда',
            'Четверг',
            'Пятница',
            'Суббота',
            'Воскресенье'
        ];
    }

    private function _checked($field, $value)
    {
        return ($field == $value) ? 'checked="checked"' : '';
    }

    private function _html($parentID)
    {
        if (!count($this->taskSructArr[$parentID]))
            return '';

        $html = '<table class="task_list" id="task_list_' . $parentID . '"><tbody>';

        foreach ($this->taskSructArr[$parentID] as $v) {
            if (empty($v['title']))
                $v['title'] = 'Пустой процесс';

            $img = '';
            $html .= $this->_html_element($v);
            $html.= PHP_EOL . '<tr class="task_children" data-id="' . $v['codeid'] . '"><td></td><td colspan="6" class="task_list_children"></td></tr>' . PHP_EOL;
        }
        $html .= '</tr></tbody></table>';
        return $html;
    }

    private function _html_element($v)
    {
        return '<tr id="task_' . $v['codeid'] . '" data-id="' . $v['codeid'] . '" class="task_item" data-category="' . $v['code_category'] . '">'
            . '<td class="codeid task_tougle">' . $v['codeid'] . '</td>
		<td class="time" style="font-size:12px; line-height:1.5;  width:110px;">' . $this->_periodicity($v) .
            '<div style="font-weight:normal; margin-top:6px;" class="task_active">
                <label><input type="checkbox" value="1" ' . $this->_checked($v['active'], 1) . ' name="active"> Активное</label>
            </div>
            ' . $this->_html_children($v) . '
		</td>
		<td class="process">
			<h5>
				' . $this->_html_priority($v) . '
                <img src="/public/img/cycleprocess_icon.png" alt="Процесс">&nbsp;<span class="task_title ' . $this->_html_edit($v) . '>' . $v['title'] . '</span> 
            </h5>
            <p>' . $this->_html_task_content($v) . '</p>
            ' . $this->_html_user_perfomers($v) . '
            ' . $this->_html_files($v) . '
        </td>
        <td class = "price">' . $v['price'] . '</td>
        <td class = "user">' . $this->_html_user_author($v) . '</td>
        <td class = "delete_cycle" style = "width:20px;">
        <span class="glyphicon glyphicon-trash task_delete" title="Удалить процесс"></span>
        <span class="glyphicon glyphicon-plus task_new task_action" title="Создать новый дочерний процесс"></span>
        </td>
        </tr>';
    }

    private function _html_edit($task)
    {
        return $_SESSION[USER_ID] == $task['code_struct'] ? ' task_edit" title="Редактровать процесс"' : '"';
    }

    private function _html_priority($task)
    {
        return ($task['priority']) ? '<span class="glyphicon glyphicon-star" style="color: gold;"></span>' : '';
    }

    private function _html_children($task)
    {
        if ($task['children'] > 0) {
            return '<span class="glyphicon glyphicon-folder-close task_tougle task_action" title="Показать дочерние процессы"></span>';
        } else {
            return '';
        }
    }

    private function _html_color($task)
    {
        return 'rgba-' . $this->taskCatArr[$task['code_category']]['color'] . '-light';
    }

    private function _html_files($v)
    {
        $files = $this->_get_files($v);
        if (empty($files) || !is_array($files)) {
            return;
        }

        $html = '<p><strong>Файлы:</strong></p><ul class = "task_files">';
        foreach ($files as $file) {
            $html .= '<li class = "task_file" data-code="' . $file['codeid'] . '" id = "task_file_' . $file['codeid'] . '" title = "' . $this->_get_file_name($file) . '"><a href="/task/file/' . $file['codeid'] . '"><span class = "glyphicon glyphicon-paperclip"></span>&nbsp;
            ' . $this->_get_file_name($file) . ' </a>' . ($_SESSION[USER_ID] == $file['code_user'] ? '<i class="glyphicon glyphicon-remove task_file_remove"></i>' : '') . '</li>';
        }
        $html .= PHP_EOL . '</ul>';
        return $html;
    }

    private function _html_user_author($task)
    {
        return '<span class = "user-thumb" style = "background-color:#f2b311">'
            . '<img src="' . $this->_get_user_avatar($task['is_photo'], $task['author_id']) . '" width="50" height="50" alt="' . $this->_html_user_author_name($task) . '" title="' . $this->_html_user_author_name($task) . '">
            </span> <br>
        <span class = "user-displayname">' . $this->_html_user_author_name($task) . '</span>';
    }

    private function _html_user_author_name($task)
    {
        return $task['author_position'] . PHP_EOL . $task['author_fio'];
    }

    private function _html_task_content($task)
    {
        return $task['info'];
        $n = 30000;
        $str = strip_tags($task['info']);
        return substr($str, 0, $n) . (strlen($str) > $n ? '...' : '');
    }

    private function _html_user_perfomers($task)
    {
        if (empty($task['performers'])) {
            return '';
        }
        $html = '<p><strong>Исполнители:</strong></p><p>';
        foreach ($task['performers'] as $user) {
            $html .= '<span class = "user-thumb" style = "background-color:#f2b311"><img src = "' . $this->_get_user_avatar($user['is_photo'], $user['codeid']) . '" width = "25" height = "25   " alt = "' . $user['fio'] . '" title = "' . $user['fio'] . '"></span>';
        }
        return $html . '</p>';
    }

    private function _get_files($v)
    {
        return array_map(function($file) {
            return $file->table;
        }, $this->loadModel('task_file')->find('code_task = ' . $v['codeid'] . ' AND status > -1'));
    }

    private function _get_file_name($file)
    {
        return $file['file_name'] . '.' . $file['file_ext'];
    }

    private function _get_file_icon($file)
    {
        switch ($file['file_ext']) {
            case 'doc':
            case 'docx': return 'file-word-o';
            case 'xls':
            case 'xlsx': return 'file-excel-o';
            default : return 'file-o';
        }
    }

    private function _get_user($id)
    {
        $user_id = (int) $id;
        if (empty($this->users[$user_id])) {
            $this->users[$user_id] = $this->loadModel('sp_user')->find($user_id)[0]->table;
        }

        return $this->users[$user_id];
    }

    private function _get_user_avatar($isset, $id)
    {
        return $isset ? '/public/upd/avatar/' . $id . '.jpg' : '/public/img/no_avatar.jpg';
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