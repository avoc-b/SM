<?php
/**
 * Elly Framework
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */
if (!defined('CS-SOFT')) die('access denited!');

class Calendar extends Controller
{

    private $dirUpload = 'public/upd/files/';

    function index()
    {
        $this->loadTheme();

//        var_dump($this->_get_setting('calendar', 'minTime'));
//        die;

//        $this->theme->table('group', $this->core->table('sp_group')->sort('name')->range());
        $this->theme->minTime = $this->_get_setting('calendar', 'minTime')? : '06:00';
        $this->theme->maxTime = $this->_get_setting('calendar', 'maxTime')? : '24:00';

        $path = HOME . '/' . $this->dirUpload;
        $tb   = $this->core->table('view_event_file')->sort('codeid DESC')->limit(0, 20)->range("real_name, '$path'+url AS url, fio, struct, date_system, title");
        $this->theme->table('file', $tb)
                    ->tableSetDate('date_system', 'H:i d.m.Y');

        $tb   = $this->core->table('view_history')->sort('codeid DESC')->limit(0, 20)->range();
        $this->theme->table('history', $tb)
                    ->tableSetDate('date_system', 'H:i d.m.Y');

        $tb   = $this->core->table('sp_group')->where('status > -1')->sort('name')->range();
        $this->theme->table('group', $tb);
    }

    function data()
    {
        $dt_1 = $_POST['start'];
        $dt_2 = $_POST['end'];

        /*
        $users = (!empty($_POST['users']) && is_array($_POST['users'])) ?
            'code_author IN (' . implode(',', array_map(function($v) {
                    return (int) $v;
                }, array_filter($_POST['users'], function($v) {
                        return $v > 0;
                    }))) . ') AND ' :
            '';
        $this->json($users);
        */
        $where = "(date_start BETWEEN '$dt_1' and '$dt_2' OR date_end BETWEEN '$dt_1' and '$dt_2') and status > -1";
        if(!empty($_POST['users']) && preg_match('~^((\d+);)*(\d+)$~is', $_POST['users'])) // пустит только 1;2;3 или просто 3
        {
            $list  = str_replace(';', ',', $_POST['users']);
            $where.= ' AND (code_author IN('. $list .') OR codeid IN( select code_event from event_struct where code_struct IN('. $list .') ))';
        }
        if(!empty($_POST['groups']) && preg_match('~^((\d+);)*(\d+)$~is', $_POST['groups'])) // пустит только 1;2;3 или просто 3
        {
            $list  = str_replace(';', ',', $_POST['groups']);
            $where.= ' AND code_group IN('. $list .')';
        }
        if(isset($_POST['status']))
        {
            $where.= empty($_POST['status']) ? ' AND status IN(0,1)' : ' AND status = '. intval($_POST['status']);
        }
        if(!empty($_POST['search']))
        {
            $find  = $this->clear($_POST['search']);
            if(strlen($find) > 3 && strlen($find) < 20) $where.= " AND title LIKE '%". $find ."%'";
        }

        //ограничение по доступам
        if($_SESSION['profile']['code_parent'] != -1)
        {
            $code  = intval($_SESSION[USER_ID]);
            $where.= ' AND (code_author = '.$code.' OR codeid IN( select code_event from event_struct where code_struct = '. $code .'))';
        }

        $tb = $this->core->table('view_event')
            ->where($where)
            ->sort('date_start')
            ->range('codeid as id, title, [group], status, color, is_primary, is_expired, allday as allDay, convert(varchar, date_start, 120) as [start], convert(varchar, date_end, 120) as [end]');
        foreach ($tb as $k => $v) {
            if (empty($v['end'])) { // задания на весь день
                //unset($tb[$k]['end']);
                $tb[$k]['start'] = $this->core->formatDate($v['start'], 'Y-m-d');
            }
        }
        $tb = $this->core->toJson($tb, 'view_event');
        $this->json($tb);
        /*
          $this->json(array(
          array(
          'title' => 'Задание на весь день',
          'start' => '2016-09-14',
          'color' => '#ff5555',
          'status'=> 3,
          'codeid'=> 41,
          ),
          array(
          'title' => 'Простое задение',
          'start' => '2016-09-17 07:20',
          'end'   => '2016-09-17 10:10',
          'color' => '#6FC66F',
          'status'=> 1,
          'codeid'=> 42,
          ),
          )); */
    }

    function form()
    {
        $id = intval($_POST['code']);

        $this->loadTheme();

        $row                = $this->core->table('view_event')->where('status > -1 and codeid=' . $id)->row();
        //debug($row);
        $row['avatar']      = $this->_avatar($row['is_photo'], $row['code_user']);
        $row['date_start']  = $this->core->formatDate($row['date_start'], 'd.m.Y H:i');
        $row['date_end']    = $this->core->formatDate($row['date_end'], 'd.m.Y H:i');
        $this->theme->post($row);


        $tb = $this->core->table('view_event_comment')->where('status > -1 and code_event=' . $id)->sort('date_system')->range();
        //debug($tb);
        foreach ($tb as $k => $v) {
            $tb[$k]['avatar']       = $this->_avatar($v['is_photo'], $v['code_user']);
            $tb[$k]['date_system']  = $this->core->formatDate($v['date_system'], 'd.m.Y H:i');
        }
        $this->theme->table('comment', $tb);

        $list = array();
        $tb = $this->core->table('view_event_struct')->where('code_event=' . $id)->range();
        foreach ($tb as $k => $v) {
            $tb[$k]['avatar'] = $this->_avatar($v['is_photo'], $v['code_user']);
            $list[] = $v['code_struct'];
        }
        $this->theme->table('struct', $tb);
        $row['struct_list'] = $list;

        $tb = $this->core->table('event_item')->where('status > -1 and code_event=' . $id)->sort('date_system')->range();
        $this->theme->table('item', $tb)
            ->tableSetCheck('status');

        $path = HOME . '/' . $this->dirUpload;
        $tb = $this->core->table('event_file')->where('code_event=' . $id)->sort('real_name')
            ->range("codeid, real_name, '$path'+url AS url");
        $this->theme->table('file', $tb);

        $row['access'] = ($_SESSION['profile']['code_parent'] == -1 || $row['code_author'] == $_SESSION[USER_ID]) ? 1 : 0;

        $row = $this->core->toJson($row, 'view_event');
        $this->json($row);
    }

    function save()
    {
        /*
          [codeid] =
          [users] = 5;46;55;56;4;12;13;14;15
          [code_group] = 1
          [title] = rretertert erterrrr 7777
          [body] =
          [date_start] = 2016-09-16 07:15
          [date_end] = 2016-09-16 08:00
          [allday] = on
          [is_primary] = on
          [price] = 40000
          [timer] = 45
         */

        $post = CCore::getPOST();

        if(empty($post->code_group) || empty($post->title) || empty($post->date_start) || empty($post->date_end) || empty($post->users))
        {
            $this->json(-1);
            return;
        }

        $post->action       = 1;
        $post->codeid       = intval($post->codeid);
        $post->code_group   = intval($post->code_group);
        $post->code_author  = intval($_SESSION[USER_ID]);
        $post->date_start   = $this->core->formatDate($post->date_start, 'Y-m-d H:i');
        $post->date_end     = $this->core->formatDate($post->date_end, 'Y-m-d H:i');
        $post->title        = $this->clear($post->title);
        $post->body         = $this->clear($post->body, 'TEXT');
        $post->is_primary   = $post->is_primary ? 1 : 0;
        $post->allday       = $post->allday ? 1 : 0;
        $post->price        = floatval($post->price);
        $post->timer        = intval($post->timer);

        //привожу к XML виду список участников
        $arr = explode(';', $post->users);
        $post->users_xml = '';
        foreach ($arr as $v)
            if(!empty($v)) $post->users_xml .= '<i><code>' . $v . '</code></i>';
        $post->users_xml = '<xml>' . $post->users_xml . '</xml>';

        unset($post->users); //, $post->allday);

        $exec = $this->core->exec('do_event', $post, array('code' => 'BIGINT', 'color' => 'VARCHAR(10)'));
        $this->json($exec);
    }

    function status()
    {
        $id = intval($_POST['code']);
        $st = intval($_POST['status']);

        $this->core->exec('do_event', array('action' => 2, 'codeid' => $id, 'status' => $st, 'code_author' => $_SESSION[USER_ID]));
        $this->json(0);
    }

    function change()
    {
        $this->core->exec('do_event', array(
                                                'action'        => 3,
                                                'codeid'        => intval($_POST['code']),
                                                'allday'        => intval($_POST['allday']),
                                                'date_start'    => (empty($_POST['start']) ? '' : $this->core->formatDate($_POST['start'], 'Y-m-d H:i')),
                                                'date_end'      => (empty($_POST['end']) ? '' : $this->core->formatDate($_POST['end'], 'Y-m-d H:i')),
                                                'code_author'   => $_SESSION[USER_ID],
                                        ));
        $this->json(0);
    }

    function upload()
    {
        $id = intval($_POST['code']);
        $ext = array(
            'image/jpeg', 'image/png', 'image/gif', 'image/bmp',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/wps-office.docx', 'application/wps-office.doc',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/wps-office.xlsx', 'application/wps-office.xls',
            'application/pdf',
            'application/octet-stream', //rar
            'application/zip',
            'text/plain', //txt
        );
        if (!in_array($_FILES['file']['type'], $ext)) {
            $this->json(-1);
            return;
        }

        $pathInfo = pathinfo($_FILES['file']['name']);
        do {
            $fileName = uniqid() . '.' . $pathInfo['extension'];
            $filePath = $this->dirUpload . $fileName;
        } while (file_exists($filePath));

        move_uploaded_file($_FILES['file']['tmp_name'], $filePath);

        $exec = $this->core->exec('do_event', array(
            'action'    => 9,
            'codeid'    => $id, //code_event
            'title'     => $fileName, //url
            'body'      => $_FILES['file']['name'], //real_name,
            'ext'       => $pathInfo['extension']
            ), array('code' => 'BIGINT'));
        $this->json(array('code' => $exec['code'], 'url' => $filePath));
    }

    function file_del()
    {
        $id     = intval($_POST['code']);
        $exec   = $this->core->exec('do_event', array('action' => 10, 'codeid' => $id));
        $this->json(0);
    }

    function item_add()
    {
        $id = intval($_POST['code']);
        $tx = $this->clear(str_replace("\r", '', $_POST['text']));

        if (empty($id) || empty($tx)) {
            $this->json(-1);
            return;
        }
        $exec = $this->core->exec('do_event', array('action' => 4, 'codeid' => $id, 'title' => $tx), array('code' => 'BIGINT'));
        $this->json($exec['code']);
    }

    function item_del()
    {
        $id     = intval($_POST['code']);
        $exec   = $this->core->exec('do_event', array('action' => 5, 'codeid' => $id));
        $this->json(0);
    }

    function item_check()
    {
        $id     = intval($_POST['code']);
        $check  = $_POST['checked'] ? 1 : 0;
        $exec   = $this->core->exec('do_event', array('action' => 6, 'codeid' => $id, 'status' => $check));
        $this->json(0);
    }

    function comment_add()
    {
        $id = intval($_POST['code']);
        $tx = $this->clear(str_replace("\r", '', $_POST['text']));

        if (empty($id) || empty($tx)) {
            $this->json(-1);
            return;
        }
        //$_SESSION[USER_ID]
        $exec = $this->core->exec('do_event', array('action' => 7, 'codeid' => $id, 'title' => $tx, 'code_author' => $_SESSION['profile']['code_user']), array('code' => 'BIGINT'));
        $exec['fio']    = $_SESSION['profile']['fio'];
        $exec['struct'] = $_SESSION['profile']['name'];
        $exec['avatar'] = $this->_avatar($_SESSION['profile']['is_photo'], $_SESSION['profile']['code_user']);
        $this->json($exec);
    }

    function comment_del()
    {
        $id     = intval($_POST['code']);
        $exec   = $this->core->exec('do_event', array('action' => 8, 'codeid' => $id));
        $this->json(0);
    }

    private function _avatar($isset, $id)
    {
        return $isset ? 'public/upd/avatar/' . $id . '.jpg' : 'public/img/no_avatar.jpg';
    }


    function detal()
    {
        $this->loadTheme('', false);

        //$id = Run::$arg[0] ? Run::$arg[0] : $_SESSION['profile']['code_user'];
        $id = $_SESSION['profile']['code_user'];
        $tb = $this->core->sql(
        "SELECT
          title +' <br>'+ cast(body as varchar(MAX)), event.price, convert(varchar,date_end, 104), convert(varchar,date_system, 104), status,
          view_event_struct.fio,
          view_event_struct.struct
        FROM
          dbo.event
          INNER JOIN view_event_struct ON (event.codeid = view_event_struct.code_event)
        WHERE
          status > -1 and code_user = ".intval($id)."
        ORDER BY date_system")->range();

        //and price > 0

        $html  = '';
        $total = 0;
        foreach($tb as $num => $row) {
            $class = $row['status'] >  1 ? ' class="active"'  : '';
            $class = $row['status'] == 1 ? ' class="warning"' : $class;
            if($row['status'] >  1) $total += $row['price'];
            $html .= '<tr'.$class.'><td>'.++$num.'</td>';
            foreach($row as $k => $v) {
                if($k == 'status') $v = str_repeat('<i class="glyphicon glyphicon-check"></i>', $v) . str_repeat('<i class="glyphicon glyphicon-unchecked"></i>', 3 -$v);
                $html .= '<td>'.$v.'</td>';
            }
            $html .= '</tr>';
        }
        $this->theme->content = '<div class="content" style="padding-top: 10px;"><table class="table">'. $html .'<tfoot><tr><th colspan="2" class="text-right">Итого:</th><th>'. $total .'</th></tr></tfoot></table></div>';
    }
}

?>