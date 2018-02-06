<link href="/public/css/select2.css" rel="stylesheet" />
<link href="/public/css/clockpicker.css" rel="stylesheet">
<link rel="stylesheet" href="system/mod/kindeditor/default.css" />

<script src="/public/js/select2.full.min.js"></script>
<script src="/public/js/select2.ru.js"></script>
<script src="/public/js/clockpicker.js"></script>
<script type="text/javascript" src="system/mod/kindeditor/kindeditor.js"></script>
<script type="text/javascript" src="system/mod/kindeditor/ru_Ru.js"></script>

<style>
    td, th {
        padding: 10px;
    }
    td.codeid, th.codeid, th.user, td.user  {
        text-align: center;
    }
    #task_list {
        vertical-align: baseline;
    }
    tr.task_item {
        border-top: none; /* Линия сверху текста */
        border-bottom: 1px solid #d7be99; /* Линия снизу текста */
    }
    table.task_list {
        width: 99%;
        float: right;
        border-collapse: collapse;
    }
    .task_action {
        padding-top: 15px;
    }
    .task_tougle {
        padding-top: 15px;
    }
    ul.task_files li{
        list-style-type: none; /* Убираем маркеры у списка */
    }
    #task_categories{
        padding: 0px;

    }
    .element::-webkit-scrollbar { width: 1px; }
    #task_categories li {
        list-style-type: none;
        text-align: left;
        padding-top: 35px;
        padding-bottom: 35px;
    }
    table tr.task_item {
        vertical-align: baseline;
    }
    table tr.task_item td.time, table tr.task_item td.process, table tr.task_item td.codeid{
        vertical-align: baseline;
    }
    table tr.task_item td.price {
        width: 50px;
        padding: 8px 0;
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        color: #c00;
    }
    span.task_title {
        font-weight: bold;
        font-size: small;
        color: #F16246;
    }
    .task_item > td.process > p {
        margin: 0px;
    }
</style>

<div class="sidebar" style="overflow-y: hidden;">        
    <ul id="task_categories" class="list-group">
        [for=taskCategories]
        <li class="list-group-item rgba-{taskCategories.color}-light" data-code="{taskCategories.codeid}">{taskCategories.name}<span class="badge">{taskCategories.count}</span></li>
        [/for]
    </ul>
</div>


<div class="content">
    <div class="panel-body">
        <div id="task_create_new" class="title">
            <button type="button" class="btn btn-default stylish-color">Создать новый циклический процесс</button>
        </div>
        <div id="task_list_all" class="title" style="display: none;">
            <button type="button" class="btn btn-default">Отобразить все процессы</button>
        </div>
        <div id="task_list_all" class="title" style="display: none;">
            <button type="button" class="btn btn-default">Отобразить все процессы в категории "<span></span>"</button>
        </div>
    </div>
    <div id="task_list_div">
        {struct}
    </div>
</div>



<div class="footer">
    <div class="footer_h"><i class="glyphicon glyphicon-chevron-up"></i> История файлов / Сообщения</div>
    <div class="container-fluid">
        <p class="text-muted">Footer...</p>
    </div>
</div>

<!-- шаблон модального окна -->
<div class="modal fade" id="task_form" role="dialog">
    <div class="modal-dialog" style="width: 800px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Циклический процесс</h4>
            </div>
            <div class="modal-body">

                <form class="form-horizontal row" role="form">
                    <input type="hidden" name="codeid" />
                    <input type="hidden" name="code_parent" />
                    <input type="hidden" name="users" />

                    <div class="col-sm-12">

                        <div class="form-group">
                            <label class="control-label col-sm-4" for="title">Название задачи:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="title" placeholder="Введите название задачи" />
                            </div>                    
                        </div>   
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="code_category">Категория:</label>
                            <div class="col-sm-8">
                                <select class="form-control select2" id="code_category" name="code_category" style="width: 100%;">
                                    <option value="0" disabled="" selected="">Выберите категорию</option>
                                </select>
                            </div>                    
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="cycle_type">Периодичность:</label>
                            <div class="col-sm-8">
                                <select class="form-control select2" id="cycle_type" name="cycle_type" style="width: 100%;">
                                    <option value="0" disabled="" selected="">Выберите периодичность</option>
                                    <option value="1">Каждый год</option>
                                    <option value="2">Каждый месяц</option>
                                    <option value="3">Каждую неделю</option>
                                    <option value="4">Каждый день</option>
                                </select>
                            </div>                    
                        </div>
                        <div class="form-group" id="yearly" style="display: none;">
                            <label class="control-label col-sm-4" for="cycle_value">Дата создания события:</label>
                            <div class="col-sm-4">
                                <select class="form-control select2 cycle_value" id="cycle_value_1_1" name="cycle_value_1_1" style="width: 100%;">
                                    <option value="0" disabled="" selected="">Выберите месяц</option>
                                    <option value="1">Январь</option>
                                    <option value="2">Февраль</option>
                                    <option value="3">Март</option>
                                    <option value="4">Апрель</option>
                                    <option value="5">Май</option>
                                    <option value="6">Июнь</option>
                                    <option value="7">Июль</option>
                                    <option value="8">Август</option>
                                    <option value="9">Сентябрь</option>
                                    <option value="10">Октябрь</option>
                                    <option value="11">Ноябрь</option>
                                    <option value="12">Декабрь</option>
                                </select>
                            </div>                    
                            <div class="col-sm-4">
                                <select class="form-control select2 cycle_value" id="cycle_value_1_2" name="cycle_value_1_2" style="width: 100%;">
                                    <option value="0" disabled="" selected="">Выберите день</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                    <option value="13">13</option>
                                    <option value="14">14</option>
                                    <option value="15">15</option>
                                    <option value="16">16</option>
                                    <option value="17">17</option>
                                    <option value="18">18</option>
                                    <option value="19">19</option>
                                    <option value="20">20</option>
                                    <option value="21">21</option>
                                    <option value="22">22</option>
                                    <option value="23">23</option>
                                    <option value="24">24</option>
                                    <option value="25">25</option>
                                    <option value="26">26</option>
                                    <option value="27">27</option>
                                    <option value="28">28</option>
                                    <option value="29">29</option>
                                    <option value="30">30</option>
                                    <option value="31">31</option>
                                </select>
                            </div>                    
                        </div>
                        <div class="form-group" id="montly" style="display: none;">
                            <label class="control-label col-sm-4" for="cycle_value_2">Число создания события:</label>
                            <div class="col-sm-8">
                                <select class="form-control select2 cycle_value" id="cycle_value_2" name="cycle_value_2" style="width: 100%;">
                                    <option value="0" disabled="" selected="">Выберите день</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                    <option value="13">13</option>
                                    <option value="14">14</option>
                                    <option value="15">15</option>
                                    <option value="16">16</option>
                                    <option value="17">17</option>
                                    <option value="18">18</option>
                                    <option value="19">19</option>
                                    <option value="20">20</option>
                                    <option value="21">21</option>
                                    <option value="22">22</option>
                                    <option value="23">23</option>
                                    <option value="24">24</option>
                                    <option value="25">25</option>
                                    <option value="26">26</option>
                                    <option value="27">27</option>
                                    <option value="28">28</option>
                                    <option value="29">29</option>
                                    <option value="30">30</option>
                                    <option value="31">31</option>
                                </select>
                            </div>                    
                        </div>
                        <div class="form-group" id="weekly" style="display: none;">
                            <label class="control-label col-sm-4" for="cycle_value_3">День недели:</label>
                            <div class="col-sm-8">
                                <select class="form-control select2 cycle_value" id="cycle_value_3" name="cycle_value_3" style="width: 100%;">
                                    <option value="0" disabled="" selected="">Выберите день недели</option>
                                    <option value="1">Понедельник</option>
                                    <option value="2">Вторник</option>
                                    <option value="3">Среда</option>
                                    <option value="4">Четверг</option>
                                    <option value="5">Пятница</option>
                                    <option value="6">Суббота</option>
                                    <option value="7">Воскресенье</option>
                                </select>
                            </div>                    
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="date_begin">Время:</label>
                            <div class="col-sm-4 clockpicker" data-autoclose="true">
                                <input type="text" class="form-control" name="date_begin" placeholder="Начало" />
                            </div>                    
                            <div class="col-sm-4 clockpicker" data-autoclose="true">
                                <input type="text" class="form-control" name="date_end" placeholder="Окончание" />
                            </div>                    
                        </div>                    
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="options"></label>
                            <div class="checkbox col-sm-4">
                                <label><input type="checkbox" value="1" name="all_day">Весь день</label>
                            </div>
                            <div class="checkbox col-sm-4">
                                <label><input type="checkbox" value="1" name="priority">Важность</label>
                            </div>
                            <label class="control-label col-sm-4" for="options"></label>
                            <div class="checkbox col-sm-4">
                                <label><input type="checkbox" value="1" name="active">Активная</label>
                            </div>
                            <div class="checkbox col-sm-4" style="display: none;">
                                <label><input type="checkbox" name="notify" value="1" id="notify">Уведомление</label>
                            </div>
                        </div>
                        <div class="form-group notify" style="display: none;">
                            <label class="control-label col-sm-4" for="notify">Уведомление</label>
                            <div class="checkbox col-sm-4">
                                <label><input type="checkbox" value="1" name="notify_sms">По SMS</label>
                            </div>
                            <div class="checkbox col-sm-4">
                                <label><input type="checkbox" value="1" name="notify_email">По E-mail</label>
                            </div>
                        </div>
                        <div class="form-group notify" style="display: none;">
                            <label class="control-label col-sm-4" for="notify_text">Текст уведомления:</label>
                            <div class="col-sm-8">
                                <textarea class="form-control" name="notify_text"></textarea>
                            </div>                    
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="phone">Балл:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="price" placeholder="Балл" />
                            </div>                    
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="info">Описание:</label>
                            <div class="col-sm-8">
                                <textarea class="form-control input-sm editor" name="info" style="width: 100%;"></textarea>
                            </div>                    
                        </div>
                        <div class="form-group" id="performers" style="display: none;">
                            <label class="control-label col-sm-4" for="info"></label>
                            <div class="dropup col-sm-6">
                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Участники
                                    <span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    {plugin=tree+vidget}
                                </ul>
                            </div>
                        </div>
                        <div class="form-group" id="task_files">
                            <label class="control-label col-sm-4" for="info">Файлы:</label>
                            <div class="task_files col-sm-6">
                                <ul class = "task_files">

                                </ul>
                            </div>
                        </div>
                    </div>                    
                </form>
                <div id="drop_progress"></div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-default b_upload" type="button" data-toggle="tooltip" title="Кликните по кнопке и выберите файлы или перетащите и бросьте файлы сюда">
                    <i class="glyphicon glyphicon-circle-arrow-up"></i> 
                    Загрузить файлы
                </button>
                <div class="upload_progress"></div>
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary b_save">Сохранить</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document)
        .on('click', '#task_categories li', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('#task_categories li').removeClass('active');
            task_search($(e.target));
        })
        .on('click', '.task_file_remove', function (e) {
            e.preventDefault();
            e.stopPropagation();
            task_file_delete($(e.target).closest('li.task_file'));
        })
        .on('click', '.b_save', function (e) {
            e.preventDefault();
            e.stopPropagation();
            task_save();
        })
        .on('change', '#notify', function (e) {
            if ($(e.target).prop('checked')) {
                $('.notify').show();
            } else {
                $('.notify').hide();
                $('[name=notify_sms]').prop('checked', false);
                $('[name=notify_email]').prop('checked', false);
            }
        })
        .on('change', '#cycle_type', function (e) {
            var val = $(e.target).val();
            if (val === '1')
                $('#yearly').show();
            else
                $('#yearly').hide();
            if (val === '2')
                $('#montly').show();
            else
                $('#montly').hide();
            if (val === '3')
                $('#weekly').show();
            else
                $('#weekly').hide();
        })
        .on('click', 'span.task_tougle', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var task_item = $(e.target).closest('tr.task_item');
            var task_children = $(task_item).next('tr').children('td.task_list_children');
            if (task_item.hasClass('active')) {
                task_item.removeClass('active');
                $(e.target).removeClass('glyphicon-folder-open');
                $(e.target).addClass('glyphicon-folder-close');
                task_children.html('');
            } else {
                elly.ajaxHTML('{url=task+tasks}',{id: task_item.attr('data-id')}, task_children, function () {
                    task_item.addClass('active');
                    $(e.target).removeClass('glyphicon-folder-close');
                    $(e.target).addClass('glyphicon-folder-open');
                });
            }
        })
        .on('click', 'div.task_active', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var task_item = $(e.target).closest('tr.task_item');
            window.task = task_item;
            elly.ajax('{url=task+active}',{code: task_item.attr('data-id')}, function (data) {
                if (data.code === 1 && data.active === 1) {
                    task_item.find('input[name="active"]').prop('checked', true);
                } else if (data.code === 1 && data.active === 0) {
                    task_item.find('input[name="active"]').prop('checked', false);
                }
            });
        })
        .on('click', 'span.task_edit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            task_form($(e.target).closest('tr.task_item'), true);
        })
        .on('click', 'span.task_new', function (e) {
            e.preventDefault();
            e.stopPropagation();
            task_form($(e.target).closest('tr.task_item'), false);
        })
        .on('click', '#task_create_new', function (e) {
            e.preventDefault();
            e.stopPropagation();
            task_form(null, false);
        })
        .on('click', 'span.task_delete', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var task_item = (e.target.nodeName === 'TR' && $(e.target).hasClass('task_item')) ? $(e.target) : $(e.target).closest('tr.task_item');
            elly.confirm('Хотите удалить?', function (ok) {
                if (ok !== true) {
                    return;
                }
                elly.ajax('{url=task+delete}',{code: task_item.attr('data-id')}, function (data) {
                    if (data.code === 1) {
                        elly.msg('Процесс удален');
                        task_item.next().remove().end().remove();
                    } else {
                        elly.msg('Не удалось удалить процесс');
                    }
                });
            });
        })
        .ready(function () {
            $('.clockpicker').clockpicker();
            window.ke = KindEditor.create('textarea.editor', {
                resizeType: 1,
                allowPreviewEmoticons: false,
                allowImageUpload: false,
                items: [
                    'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
                    'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
                    'insertunorderedlist', '|', 'emoticons', 'image', 'link']
            });
        });
    function dt(moment_dt) {
        return moment_dt ? moment_dt.format('YYYY-MM-DD HH:mm') : '';
    }
    var task_form = function (task_item, edit) {
        var $form = $('#task_form').find('form');
        $form[0].reset();
        $form.find('[name=codeid]').val('');
        $form.find('[name=code_parent]').val(!task_item ? 0 : task_item.attr('data-id'));
        $form.find('.tree_elly li input').prop('checked', false);

        $('#code_category').prop('disabled', false);
        if (edit) {
            elly.ajax('{url=task+load}',{code: task_item.attr('data-id')}, function (data) {
                window.ke.html(data.info);
                var files = $('#task_form ul.task_files').html('');
                if (data.files && data.files.length > 0) {
                    $.each(data.files, function (k, v) {
                        files.append(task_file_html(v));
                    });
                }
                elly.form($form, data);
                window.performers = $.map(data.performers, function (v, k) {
                    return v.code_struct;
                });
                $form.find('.tree_elly li').each(function () {
                    var self = $(this);
                    if (window.performers.indexOf(parseInt(self.attr('code'))) > -1)
                        self.children('span').children('input').prop('checked', true);
                });
            });
        } else {
            if (!!window.task_category) {
                $('#code_category').val(window.task_category).trigger('change');
            }
            if (!task_item) {
                $('#code_category').prop('disabled', false);
            } else {
                $('#code_category').val(task_item.attr('data-category')).trigger('change').prop('disabled', true);
            }
        }
        $('#code_category').trigger('change');
        $('#performers').show();
        var obj = elly.modalForm('#task_form');
    };
    var task_save = function () {

        window.ke.sync();
        var $form = $('#task_form form');
        var users = $form.find('.tree_elly li').map(function () {
            var self = $(this);
            var check = self.children('span').children('input').prop('checked');
            if (check)
                return self.attr('code');
        }).get();
        $form.find('[name="users"]').val(users.join(';'));
        elly.ajax('{url=task+save}', '#task_form form', function (data) {
            if (!data.code) {
                elly.msg('Возникили ошибки. Обратитесь к администратору.');
            } else {
                elly.msg('Задача успешно сохранена');
                elly.close();
            }
        });
    };
    var load_task_categories = function () {
        var $cat_select = $('#code_category').html('');
        $('#task_categories li').each(function () {
            $cat_select.append('<option value="' + $(this).attr('data-code') + '">' + $(this).html().replace(/<span.*span>/, '') + '</option>');
        });
    }();
    elly.fileUpload({
        drop: '.b_upload',
        progress: '.upload_progress',
        php: '{url=task+upload}',
        onstart: function () {
            $('.b_upload').tooltip("hide");
            return {
                code: $('#task_form').find('[name="codeid"]').val()
            };
        },
        callback: function (file, json, obj) {
            if (json == -1)
                elly.msg('Данный тип файла запрещен к загрузке на сервер.');
            else {
                $('ul.task_files').append(task_file_html(json));
            }
            setTimeout(function () {
                obj.remove();
            }, 1500);
        }
    });
    var task_file_html = function (json) {
        return '<li class="task_file" data-code="' + json.code + '"><i class="glyphicon glyphicon-file"></i>&nbsp;\
                    <a href="' + json.url + '" target="_blank">' + json.name + '</a>' +
            (json.delete === 1 ? '&nbsp;<i class="glyphicon glyphicon-remove task_file_remove"></i>' : '') + '\
                </li>';
    };
    var task_file_delete = function ($file) {
        elly.ajax('{url=task+file_delete}', {
            code: $file.attr('data-code')
        }, function (data) {
            $file.remove();
        });
    };
    var task_search = function ($category) {
        console.log($category.attr('data-code'));
        elly.ajaxHTML('{url=task+task_group}', {
            code: $category.attr('data-code')
        }, '#task_list_div', function (data, html) {
            console.log(data);
            console.log(html);
            return;
            if (!html)
                elly.msg('Корневых заданий не найдено');
            $('#code_category').val($category.attr('data-code'));
        });
        $category.addClass('active');
        window.task_category = parseInt($category.attr('data-code'));
    };
</script>