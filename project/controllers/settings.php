<?php
/**
 * Elly Framework
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */
if (!defined('CS-SOFT')) die('access denited!');

class Settings extends Controller
{
    private $taskCatArr = array();

    function index()
    {
        $this->loadTheme();
        $this->theme->task_category_list  = $this->_html_task_category_list();
        $this->theme->group_category_list = $this->_html_group_category_list();
        $this->theme->google_account      = $this->_html_google_account();
        $this->theme->calendar            = $this->_html_calendar();
    }

    // ---------- AJAX ------------- //

    function task_category_load()
    {
        $this->html($this->_html_task_category_list());
    }

    function group_category_load()
    {
        $this->html($this->_html_group_category_list());
    }

    function google_account_save()
    {
        $post = $this->core->validationPOST();

        $data = array(
            'action'      => 1,
            'codeid'      => (int) $this->_get_setting('google_account', 'codeid'),
            'code_struct' => (int) $_SESSION[USER_ID],
            'option'      => 'google_account',
            'value'       => json_encode(array(
                'email'    => $post['email'], 'password' => $post['password']
            ))
        );

        $exec = $this->core->exec('do_setting', $data, array('code' => 'BIGINT'));
        $this->json(['data' => $data, 'exec' => $exec]);
//        $this->json($exec['code']);
    }

    function setting_save()
    {
        $post   = $this->core->validationPOST();
        $option = $post['option'];
        unset($post['option']);

        $data = array(
            'action'      => 1,
            'codeid'      => (int) $this->_get_setting($option, 'codeid'),
            'code_struct' => (int) $_SESSION[USER_ID],
            'option'      => $option,
            'value'       => json_encode($post)
        );

        $exec = $this->core->exec('do_setting', $data, array('code' => 'BIGINT'));
        $this->json($exec['code']);
        $this->script('elly.msg("Опция успешно сохранена")');
    }

    function google_account_load()
    {
        $this->html($this->_html_google_account());
    }

    function task_category_save()
    {
        if (empty($_POST['categories'])) {
            $this->script('elly.msg("Изменений нет")');
        } else {
            foreach ($_POST['categories'] as $category) {
                $exec = $this->core->exec('do_task', array('action' => 8, 'codeid' => (int) $category['code'], 'name' => $this->core->validation($category['name'])), array(
                    'code' => 'BIGINT'));
            }
            $this->script('elly.msg("Категории успешно обновлены")');
        }
        $this->html($this->_html_task_category_list());
    }

    function task_category_delete()
    {
        $exec = $this->core->exec('do_task', array('action' => 7, 'codeid' => (int) $_POST['code'], 'status' => -1), array(
            'code' => 'BIGINT'));
        if ($exec['code'] > 0) {
            $this->script('elly.msg("Категория успешно удалена")');
        }
        $this->html($this->_html_task_category_list());
    }

    function group_category_save()
    {
        if (empty($_POST['categories'])) {
            $this->script('elly.msg("Изменений нет")');
        } else {
            foreach ($_POST['categories'] as $category) {
                $exec = $this->core->exec('do_setting', array(
                    'action' => 3,
                    'codeid' => (int) $category['code'],
                    'value'   => $this->core->validation($category['name']),
                    'price'  => (float) $this->core->validation($category['price'])
                    ), array(
                    'code' => 'BIGINT'
                    )
                );
//                debug($exec);
                $this->json((float) $this->core->validation($category['price']));
            }
            $this->script('elly.msg("Группы успешно обновлены")');
        }
        $this->html($this->_html_group_category_list());
    }

    function group_category_delete()
    {
        $exec = $this->core->exec('do_setting', array('action' => 4, 'codeid' => (int) $_POST['code'], 'status' => -1), array(
            'code' => 'BIGINT'));
        if ($exec['code'] > 0) {
            $this->script('elly.msg("Группа успешно удалена")');
        }
        $this->html($this->_html_group_category_list());
    }

    // ------------ PRIVATE ------------- //

    private function _load_task_category_list()
    {
        foreach ($this->loadModel('view_task_category')->find('status != -1') as $k => $v) {
            $taskCategory                              = $this->_int_fields($v->table, ['codeid', 'status', 'count']);
            $this->taskCatArr[$taskCategory['codeid']] = ['codeid' => $taskCategory['codeid'], 'name'   => $taskCategory['name'],
                'count'  => $taskCategory['count'], 'color'  => $this->get_color($k === 0)];
        }
    }

    private function _load_group_category_list()
    {
        foreach ($this->loadModel('sp_group')->find('status != -1') as $k => $v) {
            $groupCategory                               = $this->_int_fields($v->table, ['codeid', 'status', 'price']);
            $this->groupCatArr[$groupCategory['codeid']] = ['codeid' => $groupCategory['codeid'], 'name'   => $groupCategory['name'],
                'price'  => $groupCategory['price'], 'color'  => $this->get_color($k === 0)];
        }
    }

    // ------------- HTML ------------------ //

    private function _html_google_account()
    {
        return '<input type="hidden" name="option" value="google_account">
                <div class="form-group">
                    <label class="control-label col-sm-4" for="email">Google Логин:</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="email" placeholder="Введите логин" value="' . $this->_get_setting('google_account', 'email') . '" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4" for="password">Google Пароль:</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="password" placeholder="Введите пароль" value="' . $this->_get_setting('google_account', 'password') . '" />
                    </div>
                </div>';
    }

    private function _html_calendar()
    {
        return '<input type="hidden" name="option" value="calendar">
                <div class="form-group">
                    <label class="control-label col-sm-4" for="minTime">Мин:</label>
                    <div class="col-sm-8 clockpicker" data-autoclose="true" data-minutes="15">
                        <input type="text" class="form-control" name="minTime" value="' . ($this->_get_setting('calendar', 'minTime')
                ?: '06:00') . '" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4" for="maxTime">Max:</label>
                    <div class="col-sm-8 clockpicker" data-autoclose="true" data-minutes="15">
                        <input type="text" class="form-control" name="maxTime" value="' . ($this->_get_setting('calendar', 'maxTime')
                ?: '24:00') . '" />
                    </div>
                </div>';
    }

    private function _html_task_category_list()
    {
        if (empty($this->taskCatArr)) {
            $this->_load_task_category_list();
        }

        foreach ($this->taskCatArr as $category) {
            $_html .= $this->_html_task_category_element($category);
        }
        return $_html;
    }

    private function _html_group_category_list()
    {
        if (empty($this->groupCatArr)) {
            $this->_load_group_category_list();
        }

        foreach ($this->groupCatArr as $category) {
            $_html .= $this->_html_group_category_element($category);
        }
        return $_html;
    }

    function _html_task_category_element($category)
    {
        return '<tr data-id="' . $category['codeid'] . '" class="task_category_item">'
            . '<td><input type="text" class="form-control" name="title" placeholder="Введите название категории" value="' . $category['name'] . '" /></td>'
            . '<td class="category_action"><span class="glyphicon glyphicon-trash group_category_delete" title="Удалить категорию"></span>'
            . '</td>'
            . '</tr>';
    }

    function _html_group_category_element($category)
    {
        return '<tr data-id="' . $category['codeid'] . '" class="group_category_item">'
            . '<td><input type="text" class="form-control" name="title" placeholder="Введите название группы" value="' . $category['name'] . '" /></td>'
            . '<td><input type="text" class="form-control" name="price" placeholder="Введите стоимость" value="' . $category['price'] . '" /></td>'
            . '<td class="category_action"><span class="glyphicon glyphicon-trash group_category_delete" title="Удалить группу"></span>'
            . '</td>'
            . '</tr>';
    }

    function error404()
    {
        $this->loadTheme('main', false);
        $this->theme->content = '<h3 class="title404">' . elly::$lang['404.title'] . '</h3><br><p>' . elly::$lang['404.description'] . '</p>';
    }

    // ------------- SPECIAL -------------- //
// ----------------------------------- DEV ----------------------------


    function group()
    {
        $this->loadTheme();

        $tb = $this->core->table('view_group')->where('status > -1')->sort('name')->range();
        $this->theme->table('group', $tb);
    }

    function group_form()
    {
        $id = intval($_POST['code']);
        $this->json($this->core->table('sp_group')->where('codeid=' . $id)->row());
    }

    function group_save()
    {
        $post = $this->core->getPOST();

        $this->core->exec('do_setting', array(
            'action' => 3,
            'codeid' => $post->codeid,
            'value'  => $post->name,
            'price'  => $post->price,
        ));
        $this->json(0);
    }

    function group_del()
    {
        $id = intval($_POST['code']);
        $this->core->exec('do_setting', array('action' => 4, 'codeid' => $id,));
        $this->json(0);
    }
}
?>