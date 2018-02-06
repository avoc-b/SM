<?php
/**
 * @author webdevelop@bk.ru
 * @date 26/7/2016
 */
if (!defined('CS-SOFT'))
    die('access denited!');

class Tree extends Plugin
{

    public $data = null;

    function vidget()
    {
        $this->loadTheme();
        $args = func_get_args();
        $params = $this->prepare_args($args);
        $this->_getData();
        $this->theme->tree = $this->_getTree(-1) . PHP_EOL . $this->_getTree(0);
        //debug($this->theme->tree);
    }

    private function _getTree($id)
    {
        if (!is_array($this->data[$id]))
            return '';

        $html = '';
        foreach ($this->data[$id] as $k => $v) {
            if (!$v['fio'])
                $v['fio'] = 'Вакансия';
            $box = '
                    <input type="checkbox" />
                    <i class="tree_img"><img src="'. $this->_avatar($v['is_photo'], $v['code_user']) .'" /></i>
                    <div class="tree_clr" style="background-color: '. $v['color'] .'"></div>
                    <b>'. $v['fio'] .'</b>
                    <u>'. $v['name'] .'</u>';

            if (empty($this->data[$v['codeid']]))
                $html .= '<li code="' . $v['codeid'] . '"><span>' . $box . '</span></li>';
            else
                $html .= '<li code="' . $v['codeid'] . '"><span class="tree_group"><i class="glyphicon glyphicon-minus"></i>' . $box . '</span><ul>' . $this->_getTree($v['codeid']) . '</ul></li>';
        }
        return $html;
    }

    private function _getData()
    {
        if (is_null($this->data)) {
            $tb = $this->core->table('view_user_struct')
                ->where('status != -1')
                ->sort('name')
                ->range();
            foreach ($tb as $v)
                $this->data[$v['code_parent']][] = $v;
        }
        /*
          $this->data = array(
          0  => array(
          array('codeid'=>12, 'name'=>'perviy'),
          array('codeid'=>13, 'name'=>'vtoroy'),
          ),
          12 => array(
          array('codeid'=>14, 'name'=>'vnutri pervogo'),
          array('codeid'=>15, 'name'=>'vnutri pervogo'),
          array('codeid'=>16, 'name'=>'vnutri pervogo'),
          ),
          13 => array(
          array('codeid'=>17, 'name'=>'vnutri vtorogo'),
          array('codeid'=>18, 'name'=>'vnutri vtorogo'),
          ),
          );
         */
    }

    private function _avatar($isset, $id)
    {
        return $isset ? 'public/upd/avatar/' . $id . '.jpg' : 'public/img/no_avatar.jpg';
    }
}

?>