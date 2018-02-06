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

class Login extends Controller
{

    function index()
    {
        $this->theme->select()->content = 'login page';
    }

    function auth()
    {
        $post = $this->core->validationPOST();
        /*
          $this->loadModel('users');
          if($this->model->autorisation($post)) $this->script('location.reload();');
          else $this->script('elly.msg("'. $this->model->error() .'");');
         */
        //if($post['login'] == 'admin' && $post['pass'] == '123') {
        $row = $this->core->table('view_user_struct')->where("lgn='{$post['login']}' and psw='{$post['pass']}'")->row();

//        $user = $this->loadModel('view_user_struct')->find('lgn=\'' . $post['login'] . '\' AND psw=\'' . $post['pass'] . '\'')->table;

//        var_dump($this->core->table('view_user_struct')->where("lgn='{$post['login']}' and psw='{$post['pass']}'")->table());
//        die;
//        var_dump($user);
//        die;
        if (!empty($row['codeid'])) {
            unset($row['psw']);

            $_SESSION[USER_ID] = $row['codeid'];
            $_SESSION['profile'] = $row;
            $this->script('location.reload();');
        } else {
            $this->script('elly.msg("В доступе отказано!");');
        }
    }

    function logout()
    {
        $this->loadModel('users');
        $this->model->logout();
        $this->script('location.reload();');
    }
}

?>