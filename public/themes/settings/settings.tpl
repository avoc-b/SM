<link href="/public/css/select2.css" rel="stylesheet" />
<link href="/public/css/clockpicker.css" rel="stylesheet">

<script src="/public/js/select2.full.min.js"></script>
<script src="/public/js/select2.ru.js"></script>
<script src="/public/js/clockpicker.js"></script>

<style>
    td, th {
        padding: 10px;
    }

    .category_action {
        text-align: center;
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
        vertical-align: middle;
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
</style>
<div class="sidebar"></div>
<div class="content" style="padding-top: 10px;">
    <div class="col-sm-6">
        <div class="panel panel-default tasks">
            <div class="panel-heading">
                <h3 class="panel-title">Категории циклических задач</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="task_category_list" style="width: 100%;">
                            <tbody>
                                {task_category_list}
                            </tbody>
                        </table>
                    </div>
                    <hr>
                </div>
                <div class="row">
                    <hr>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <table style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" id="task_category" name="task_category" placeholder="Новая категория" value="" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class=" panel-footer">
                <button type="button" class="btn btn-default b_category_reset">Сбросить</button>
                <button type="button" class="btn btn-primary b_category_save pull-right">Сохранить</button>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default" id="google_account">
            <div class="panel-heading">
                <h3 class="panel-title">Google Аккаунт для рассылки почты</h3>
            </div>
            <div class="panel-body">
                <form class="form-horizontal row col-sm-12" role="form">
                    {google_account}
                </form>
            </div>
            <div class=" panel-footer">
                <button type="button" class="btn btn-default b_reset">Сбросить</button>
                <button type="button" class="btn btn-primary b_save pull-right">Сохранить</button>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default" id="calender_time_range">
            <div class="panel-heading">
                <h3 class="panel-title">Диапазон времени в календаре</h3>
            </div>
            <div class="panel-body">
                <form class="form-horizontal row col-sm-12" role="form">
                    {calendar}
                </form>
                <small>Значение времени округляется до 15 минут</small>
            </div>
            <div class=" panel-footer">
                <button type="button" class="btn btn-default b_reset">Сбросить</button>
                <button type="button" class="btn btn-primary b_save pull-right">Сохранить</button>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default tasks">
            <div class="panel-heading">
                <h3 class="panel-title">Группы задач</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12" style="max-height: 500px; overflow: scroll;">
                        <table id="group_category_list" style="width: 100%;">
                            <tbody>
                                {group_category_list}
                            </tbody>
                        </table>
                    </div>
                    <hr>
                </div>
                <div class="row">
                    <hr>
                </div>
                <div class="row">
                    <div class="col-sm-8">
                        <table style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" id="group_category" name="group_category" placeholder="Новая группа" value="" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-4">
                        <table style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" id="group_price" name="group_price" placeholder="Стоимость" value="" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class=" panel-footer">
                <button type="button" class="btn btn-default b_group_reset">Сбросить</button>
                <button type="button" class="btn btn-primary b_group_save pull-right">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="footer">
    <div class="footer_h"><i class="glyphicon glyphicon-chevron-up"></i> История файлов / Сообщения</div>
    <div class="container-fluid">
        <p class="text-muted">Footer...</p>
    </div>
</div>

<script>
    $(document)
            .on('click', '#google_account .b_reset', function (e) {
                e.preventDefault();
                google_account_reset();
            })
            .on('click', '#google_account .b_save', function (e) {
                e.preventDefault();
                setting_save('#google_account form');
            })
            .on('click', '#test .b_save', function (e) {
                e.preventDefault();
                setting_save('#test form');
            })
            .on('click', '#calender_time_range .b_reset', function (e) {
                e.preventDefault();
                google_account_reset();
            })
            .on('click', '#calender_time_range .b_save', function (e) {
                e.preventDefault();
                setting_save('#calender_time_range form');
            })
            .on('click', '.b_category_reset', function (e) {
                e.preventDefault();
                e.stopPropagation();
                task_category_reset();
            })
            .on('click', '.b_group_reset', function (e) {
                e.preventDefault();
                e.stopPropagation();
                group_category_reset();
            })
            .on('change', '#group_category_list tr.group_category_item input', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $tr = $(e.target).closest('tr.group_category_item');
                console.log('changed');
                if (!$tr.hasClass('changed')) {
                    $tr.addClass('changed');
                }
            })
            .on('change', '#task_category_list tr.task_category_item input', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $tr = $(e.target).closest('tr.task_category_item');
                if (!$tr.hasClass('changed')) {
                    $tr.addClass('changed');
                }
            })
            .on('click', '.task_category_delete', function (e) {
                task_category_delete($(e.target).closest('tr.task_category_item'));
            })
            .on('click', '.group_category_delete', function (e) {
                group_category_delete($(e.target).closest('tr.group_category_item'));
            })
            .on('click', '.b_category_save', function (e) {
                e.preventDefault();
                e.stopPropagation();
                task_category_save();
            })
            .on('click', '.b_group_save', function (e) {
                e.preventDefault();
                e.stopPropagation();
                group_category_save();
            })
            .ready(function () {
                $('.clockpicker')
                        .clockpicker()
                        .find('input')
                        .change(function () {
                            var time = this.value.split(":");
                            var time_range = $(this).closest('.clockpicker').attr('data-minutes');
                            time[1] = Math.floor(time[1] / time_range) * time_range;
                            time[1] = (time[1] === 0) ? '00' : time[1];
                            this.value = time.join(':');
                        });
            });
    function dt(moment_dt) {
        return moment_dt ? moment_dt.format('YYYY-MM-DD HH:mm') : '';
    }
    var task_category_save = function () {
        var categories = [];
        var new_category = $('#task_category').val();
        $('#task_category_list > tbody').children('tr.changed').each(function (k, v) {
            $v = $(v);
            categories.push({
                'code': $v.attr('data-id'),
                'name': $v.find('input').val()
            });
        });
        if (new_category !== '') {
            categories.push({
                'code': null,
                'name': new_category
            });
        }
        elly.ajax('{url=settings+task_category_save}', {
            'categories': categories
        }, function (data, html) {
            task_category_reset(html);
        });
    };
    var group_category_save = function () {
        var categories = [];
        var new_category = $('#group_category').val();
        var new_price = $('#group_price').val();
        $('#group_category_list > tbody').children('tr.changed').each(function (k, v) {
            $v = $(v);
            categories.push({
                'code': $v.attr('data-id'),
                'name': $v.find('input[name=title]').val(),
                'price': $v.find('input[name=price]').val()
            });
        });
        if (new_category !== '') {
            categories.push({
                'code': null,
                'name': new_category,
                'price': new_price
            });
        }

//        console.log(categories);
//        return;

        elly.ajax('{url=settings+group_category_save}', {
            'categories': categories
        }, function (data, html) {
            group_category_reset(html);
        });
    };
    var task_category_delete = function ($category) {
        elly.ajax('{url=settings+task_category_delete}', {
            'code': $category.attr('data-id')
        }, function (data, html) {
            task_category_reset(html);
        });
    };
    var group_category_delete = function ($category) {
        elly.ajax('{url=settings+group_category_delete}', {
            'code': $category.attr('data-id')
        }, function (data, html) {
            group_category_reset(html);
        });
    };
    var task_category_reset = function (html) {
        if (!!html) {
            $('#task_category_list tbody').html(html);
        } else {
            elly.ajaxHTML('{url=settings+task_category_load}', {}, '#task_category_list tbody');
        }
        task_category_clear();
    };
    var group_category_reset = function (html) {
        if (!!html) {
            $('#group_category_list tbody').html(html);
        } else {
            elly.ajaxHTML('{url=settings+group_category_load}', {}, '#group_category_list tbody');
        }
        group_category_clear();
    };
    var task_category_clear = function () {
        $('#task_category').val('');
    };
    var group_category_clear = function () {
        $('#group_category').val('');
        $('#group_price').val('');
    };

    var google_account_reset = function () {
        elly.ajaxHTML('{url=settings+google_account_load}', {}, '#google_account form', function (data, html) {
            console.log(data);
            console.log(html);
        });
    }

    var setting_save = function (form) {
        var $form = $(form);

        elly.ajax('{url=settings+setting_save}', form, function (data, html) {
            console.log(data);
            console.log(html);
            $form.html(html);
        });
    };
</script>