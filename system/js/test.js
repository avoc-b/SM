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
            elly.ajaxHTML('{url=task+tasks}', {id: task_item.attr('data-id')}, task_children, function () {
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
        elly.ajax('{url=task+active}', {code: task_item.attr('data-id')}, function (data) {
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
            elly.ajax('{url=task+delete}', {code: task_item.attr('data-id')}, function (data) {
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
    if (edit) {
        elly.ajax('{url=task+load}', {code: task_item.attr('data-id')}, function (data) {
            console.log(data);
            $('#code_category').prop('disabled', true);
            window.ke.html(data.info);
            var files = $('#task_form ul.task_files').html('');
            if (data.files && data.files.length > 0) {
                $.each(data.files, function (k, v) {
                    files.append(task_file_html(v));
                });
            }
            elly.form($form, data);
            window.performers = data.performers.map(function () {
                console.log(this);
            });
            $form.find('.tree_elly li').each(function () {
                var self = $(this);
                if (self.attr('code'))
                    var check = self.children('span').children('input').prop('checked');
                if (check)
                    return self.attr('code');
            }).get();
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
        return {code: $('#task_form').find('[name="codeid"]').val()};
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
    elly.ajax('{url=task+file_delete}', {code: $file.attr('data-code')}, function (data) {
        $file.remove();
    });
};
var task_search = function ($category) {
    elly.ajaxHTML('{url=task+task_group}', {code: $category.attr('data-code')}, '#task_list_div', function (data, html) {
        if (!html)
            elly.msg('Корневых заданий не найдено');
        $('#code_category').val($category.attr('data-code'));
    });
    $category.addClass('active');
    window.task_category = parseInt($category.attr('data-code'));
};