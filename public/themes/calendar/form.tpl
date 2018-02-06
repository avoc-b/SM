<form class="form row" role="form">
    <input type="hidden" name="codeid" value="{val=codeid}" />
    <div class="col-sm-12">
        <label class="control-label" for="body">Описание:</label>
        <div>
            {val=body}
        </div>
        <br />
    </div>
    <div class="col-sm-12">
        <div class="well row user_list">
            <div class="col-sm-8">
                <label  class="control-label">Участники:</label>
                <div class="user_list_1">
                    [for=struct]
                    <span class="user_block">
                        <img src="{struct.avatar}" />
                        <b>{struct.fio}</b>
                        <u>{struct.struct}</u>
                    </span>
                    [/for]                                    
                </div>
            </div>
            <div class="col-sm-4">
                <label  class="control-label">Автор:</label>
                <div class="user_list_2">
                    <span class="user_block">
                        <img src="{val=avatar}" />
                        <b>{val=fio}</b>
                        <u>{val=struct}</u>
                    </span>                                    
                </div>
                <label class="control-label text-right">Баллы: <b class="text-info">{val=price}</b></label>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <label for="" class="control-label">Комментарии:</label>
        <div class="comment">
            [for=comment]
            <span data-code="{comment.codeid}">
                <img src="{comment.avatar}" />
                <div>{comment.text}</div>
                <u>{comment.date_system} <a>{comment.fio} ({comment.struct})</a></u>
                <i class="glyphicon glyphicon-remove"></i>
            </span>
            [/for]
        </div>
        <div class="form-group has-feedback">
            <div class="relative">
                <textarea class="form-control input-sm editor2" name="" style="width: 91.5%;"></textarea>
                <button type="button" class="btn btn-default b_add_comment">
                    <span class="glyphicon glyphicon-share-alt"></span>
                </button>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <label class="control-label" for="body">Подзадачи:</label>
        <ul class="item">
            [for=item]
            <li data-code="{item.codeid}">
                <input type="checkbox" name="status[]" /> 
                <span>{item.text}</span>
                <i class="glyphicon glyphicon-remove"></i>
            </li>
            [/for]
        </ul>
        <div class="form-group has-feedback">                                
            <label for="" class="control-label">Добавить подзадачу:</label>
            <div class="relative">
                <textarea class="form-control input-sm" name="tx_item" style="width: 100%;"></textarea>
                <button class="btn btn-default form-control-feedback b_add_item" type="button">
                    <span class="glyphicon glyphicon-share-alt"></span>
                </button>
            </div>
        </div>

        <!--<div class="">
            <label class="control-label" for="timer">Прикреплённые файлы:</label>
            <p>...</p>
            <div class="well text-center">Кликните по полю или перетащите сюда файлы</div>
        </div>-->
    </div>

    <div class="col-sm-12">
        <label for="timer" class="control-label">Прикреплённые файлы:</label>
        <div class="upload_files">
            [for=file]
            <span data-code="{file.codeid}">
                <i class="glyphicon glyphicon-file"></i> 
                <a href="{file.url}" target="_blank">{file.real_name}</a>
                <i class="glyphicon glyphicon-remove"></i>
            </span>
            [/for]
        </div>
    </div>
</form>

<script>
    var ke2 = KindEditor.create('textarea.editor2', {
        resizeType: 1,
        allowPreviewEmoticons: false,
        allowImageUpload: false,
        items: [
            'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
            'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
            'insertunorderedlist', '|', 'emoticons', 'image', 'link']
    });

    $('.b_add_comment').click(function () {
        var self = $(this).parents('form'),
            data = {};
        data.code = self.find('[name="codeid"]').val();
        data.text = ke2.html();
        if (data.text && data.code)
            elly.ajax('{url=calendar+comment_add}', data, function (json) {
                if (!json) {
                    elly.msg('Произошла ошибка при сохранении!');
                    return;
                }
                var block = self.find('.comment');
                block.append('<span data-code="' + json.code + '">\
                                <img src="' + json.avatar + '">\
                                <div>' + data.text + '</div>\
                                <u>' + moment().format('DD.MM.YYYY HH:mm') + ' <a>' + json.fio + ' (' + json.struct + ')</a></u>\
                                <i class="glyphicon glyphicon-remove"></i>\
                            </span>');
                block.animate({"scrollTop": block.offset().top}, 600); // толкаю вниз
                ke2.html('');
            });
    });
    $('.comment')
        .on('click', '.glyphicon-remove', function () {
            var span = $(this).parent();
            elly.confirm('Удалить комментарий?', function (ok) {
                if (ok)
                    elly.ajax('{url=calendar+comment_del}', {code: span.data('code')}, function () {
                        span.remove();
                    });
            });
        });
    $('.b_add_item').click(function () {
        var self = $(this).parents('form'),
            data = {};
        data.code = self.find('[name="codeid"]').val();
        data.text = self.find('[name="tx_item"]').val();
        if (data.text && data.code)
            elly.ajax('{url=calendar+item_add}', data, function (code) {
                if (!code) {
                    elly.msg('Произошла ошибка при сохранении!');
                    return;
                }
                self.find('.item').append('<li data-code="' + code + '"><input type="checkbox" name="status[]"><span>' + data.text + '</span><i class="glyphicon glyphicon-remove"></i></li>');
                self.find('[name="tx_item"]').val('')
            });
    });
    $('.item')
        .on('click', '.glyphicon-remove', function () {
            var li = $(this).parent();
            elly.confirm('Удалить пункт?', function (ok) {
                if (ok)
                    elly.ajax('{url=calendar+item_del}', {code: li.data('code')}, function () {
                        li.remove();
                    });
            });
        })
        .on('click', ':checkbox', function () {
            elly.ajax('{url=calendar+item_check}', {code: $(this).parent().data('code'), checked: Number($(this).prop('checked'))});
        });

    $('.upload_files')
        .on('click', '.glyphicon-remove', function () {
            var span = $(this).parent();
            elly.confirm('Удалить файл?', function (ok) {
                if (ok)
                    elly.ajax('{url=calendar+file_del}', {code: span.data('code')}, function () {
                        span.remove();
                    });
            });
        })
</script>