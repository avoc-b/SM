<?php
/**
 * Elly Framework 
 *
 * @author webdevelop@bk.ru
 * @date 27/7/2016
 */
if (!defined('CS-SOFT'))
    die('access denited!');

class User extends Controller
{

    private $userArr = array();
    private $num = 0;

    function index()
    {
        $this->loadTheme();

        $tb = $this->core->table('view_user_struct')
            ->where('status > -1')
            ->sort('code_parent')
            ->range();
        foreach ($tb as $v) {
            $this->userArr[$v['code_parent']][] = $v;
        }
        $this->theme->struct = $this->_struct(0);

        $tb = $this->core->table('sp_user')
            ->where("(status > -1 and (code_struct < 1 or code_struct IS NULL)) or code_struct=1")
            ->sort('fio')
            ->range('codeid, fio, is_photo AS avatar');
        foreach ($tb as $k => $v) {
            $tb[$k]['avatar'] = $this->_avatar($v['avatar'], $v['codeid']);
        }
        $this->theme->table('users', $tb);
    }

    private function _avatar($isset, $id)
    {
        return $isset ? 'public/upd/avatar/' . $id . '.jpg' : 'public/img/no_avatar.jpg';
    }

    private function _struct($parentID)
    {
        $html = '';
        if (!count($this->userArr[$parentID]))
            return $html;

        foreach ($this->userArr[$parentID] as $v) {
            //if($this->num++ > 5) $this->num = 1;
            if(empty($v['code_user']))
                $v['fio'] = 'Вакансия';

            $img = $this->_avatar($v['is_photo'], $v['code_user']);

            $html.= '<li>
            <a href="#" class="clr_'. $v['code_color'] .'" data-code="' . $v['codeid'] . '">
                <img src="' . $img . '" /> 
                <b>' . $v['fio'] . '</b>
                <span>' . $v['name'] . '</span>
            </a>';

            if (count($this->userArr[$v['codeid']]))
                $html.= PHP_EOL . '<ul>' . $this->_struct($v['codeid']) . '</ul>';

            $html.= PHP_EOL . '</li>';
        }
        return $html;
    }

    function struct_del()
    {
        $exec = $this->core->exec('do_user_struct', array('action' => 2, 'codeid' => intval($_POST['code'])), array('code' => 'BIGINT'));
        $this->json($exec['code']);
    }

    function struct_vacans()
    {
        $this->core->exec('do_user_struct', array('action' => 4, 'codeid' => intval($_POST['code'])));
        $this->json(0);
    }

    function parent_upd()
    {
        $this->core->exec('do_user_struct', array(
            'action' => 5,
            'codeid' => intval($_POST['code']),
            'code_parent' => intval($_POST['parent']),
        ));
        $this->json(0);
    }

    function edit()
    {
        //обрабатывается как редактирование, так и отдельный запрос на свободных пользователей
        $id = intval($_POST['code']);

        //чистка не сохраненных аватарок
        $oldAvatar = $this->_avatar(true, 'undefine_' . $_SESSION[USER_ID]);
        if (is_file($oldAvatar))
            unlink($oldAvatar);


        if ($id > 0)
            $row = $this->core->table('view_user_struct')
                ->where('codeid=' . $id)
                ->row('codeid,name,code_user AS fio,lgn,psw,phone,mail,address,is_photo AS avatar');
        $row['avatar'] = $this->_avatar($row['avatar'], $row['fio']);
        $row['fio_data'] = $this->core->table('sp_user')
            ->where('status > -1 and (code_struct < 1 or code_struct IS NULL or code_struct=' . $id . ')')
            ->sort('fio')
            ->range('codeid AS id, fio AS text, is_photo AS avatar, lgn,psw,phone,mail,address');
        foreach ($row['fio_data'] as $k => $v) {
            $row['fio_data'][$k]['avatar'] = $this->_avatar($v['avatar'], $v['id']);
        }
        $this->json($row);
    }
    /*
      function search()
      {
      $search = $this->clear($_POST['search']);
      $tb = $this->core->table('view_user_struct')
      ->where("status > -1 and code_user > 0 and fio LIKE '%$search%'")
      ->sort('fio')
      ->limit(0,10)
      ->range('codeid AS id, fio AS text');
      $this->json($tb);
      }
     */

    function upload()
    {
        /* if(empty($_POST['code'])) //не вписано даже фио
          {
          $this->json(-2);
          return;
          } */
        $ext = array('image/jpeg', 'image/png', 'image/gif', 'image/bmp');
        if (!in_array($_FILES['file']['type'], $ext)) {
            $this->json(-1);
            return;
        }
        debug($_FILES);
        include_once 'system/mod/images.php';

        $fileName = is_numeric($_POST['code']) ? intval($_POST['code']) : 'undefine_' . $_SESSION[USER_ID];
        $fileName = $this->_avatar(true, $fileName);
        $result   = img_resize($_FILES['file']['tmp_name'], $fileName, 120, 120);

        if(!$result) debug('ERROR resize image');

        //move_uploaded_file($_FILES['file']['tmp_name'], $fileName);

        $this->json($fileName);
    }

    function save()
    {
        $post = $this->core->validationPOST();

        $post['action'] = 1;
        $post['is_photo'] = 0;

        if (is_numeric($post['fio']) && is_file($this->_avatar(true, $post['fio'])))
            $post['is_photo'] = 1;
        else {
            $avatar = $this->_avatar(true, 'undefine_' . $_SESSION[USER_ID]);
            if (!is_file($avatar))
                $avatar = false;
            else
                $post['is_photo'] = 1;
        }

        $exec = $this->core->exec('do_user_struct', $post, array('code' => 'BIGINT', 'code_user' => 'BIGINT'));

        // переименовываю несохраненную фотографию профиля
        if ($avatar)
            rename($avatar, $this->_avatar(true, $exec['code_user']));

        $this->json($exec);
    }
}

?>