<?php

/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */
class Model
{

    public $name;
    public $default;
    public $table = array();

    function __construct($model, $default = false)
    {
        $this->default = $default;
        $this->name = $model;
    }

    function __set($key, $value)
    {
        $this->table[$key] = $value;
    }

    function __get($key)
    {
        return $this->table[$key];
    }

    /**
     * Получение значения поля тамлицы
     * @value   Mixed искомое значение, по умолчанию ищется в codeid
     * @field   String поле таблицы, по которому осущ-ся поиск
     */
    function get($value, $field = '')
    {
        $pCore = CCore::getInstance();

        if ($this->default)
            $model = new Model($this->name);         //если используется модель по умолчанию  
        else
            $model = new $this->name($this->name);   //модель по своему образу и подобию
        $where = ($field) ? $field . "='$value'" : "codeid='$value'";
        $pCore->table($this->name)->where($where);

        $model->table = $pCore->row();  //результат помещается во внутрь класса                    
        return $model;
    }

    /**
     * Осуществление выборки из таблицы с возвращением массива объектов
     * @where   Mixed условие выборки, если указать только число - ищется по codeid
     * @return  array массив объектов
     */
    function find($where = '')
    {
        $pCore = CCore::getInstance();

        if (is_integer($where))
            $where = 'codeid=' . $where;

        $pCore->table($this->name)->where($where);
        $table = $pCore->range($fields);
        $result = array();

        foreach ($table as $row) {
            if ($this->default)
                $model = new Model($this->name);
            else
                $model = new $this->name($this->name);
            $model->table = $row;
            $result[] = $model;
        }
        return $result;
    }

    function add($data)
    {
        $pCore = CCore::getInstance();
        $pCore->table($this->name);
        return $pCore->add($data);
    }

    function update()
    {
        $pCore = CCore::getInstance();
        return $pCore->update($this->table);
    }

    function delete($where = '')
    {
        $pCore = CCore::getInstance();
        $pCore->table($this->name)->where($where);
        $pCore->delete();
    }
}

?>