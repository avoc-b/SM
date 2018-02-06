<link href="public/css/select2.css" rel="stylesheet" />
<script src="public/js/select2.full.min.js"></script>
<script src="public/js/select2.ru.js"></script>


<div class="sidebar">
    <div class="container-fluid"><h5>Без должности:</h5></div>
    
    <ul class="list-group">
    [for=users]
        <li class="list-group-item" data-code="{users.codeid}">
            <img src="{users.avatar}" width="24" />
            <b>{users.fio}</b>
        </li>
    [/for]
    </ul>
            
</div>


<div class="tree_org content">
	<ul>
    
        {struct}
    
    <!--
		<li>
			<a href="#" class="clr_2">
                <i class="glyphicon glyphicon-user"></i> 
                <b>Parent</b>
                <span>Председатель 8</span>
            </a>
			<ul>
				<li>
					<a href="#">
                        <i class="glyphicon glyphicon-user"></i> 
                        <b>Child</b>
                        <span>Начальник отдела 7</span>
                    </a>
					<ul>
						<li>
							<a href="#">Grand Child 5</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#" class="clr_6">
                        <i class="glyphicon glyphicon-user"></i> 
                        <b>Child</b>
                        <span>Начальник отдела 6</span>
                    </a>					
				</li>
                <li>
					<a href="#" class="clr_5">
                        <i class="glyphicon glyphicon-user"></i> 
                        <b>Child</b>
                        <span>Начальник отдела</span>
                    </a>
				</li>
			</ul>
		</li>
    -->
	</ul>
</div>


<!-- шаблон всплывающего меню -->
<div class="tree_menu" style="display: none;">
    <ul>
        <li><b>Редактировать</b></li>
        <li>Добавить подчинённого</li>
        <li>Освободить от должности</li>
        <li>Сократить должность</li>
    </ul>
</div>


<!-- шаблон модального окна -->
<div class="modal fade" id="struct_edit" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Должность</h4>
			</div>
			<div class="modal-body">
                
                <form class="form-horizontal row" role="form">
                    
                    <input type="hidden" name="codeid" />
                    <input type="hidden" name="code_parent" />
                                        
                    <div class="col-sm-3 avatar_w">
                        
                        <img class="avatar" src="public/img/no_avatar.jpg" title="Для загрузки фотографии &#010;кликните по изображению &#010;или перетащите его сюда" />
                                                
                    </div>
                    <div class="col-sm-9">
                    
                        <div class="form-group">
                            <label class="control-label col-sm-3" for="name">Должность:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" placeholder="Введите наименование Должности" />
                            </div>                    
                        </div>                        
                        <div class="form_user">
                            <div class="form-group">
                                <label class="control-label col-sm-3" for="fio">ФИО:</label>
                                <div class="col-sm-9">
                                    <select class="form-control select2" id="fio" name="fio" style="width: 100%;"></select>
                                </div>                    
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-3" for="lgn">Логин:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="lgn" placeholder="Введите Логин" />
                                </div>                    
                            </div>
                            <div class="form-group has-feedback">
                                <label class="control-label col-sm-3" for="psw">Пароль:</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control" name="psw" placeholder="Введите Пароль" />
                                    <span class="glyphicon glyphicon-eye-open form-control-feedback b_psw"></span>
                                </div>                    
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-3" for="phone">Телефон:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="phone" placeholder="Введите Телефон" />
                                </div>                    
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-3" for="mail">Почта:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="mail" placeholder="Введите Почту" />
                                </div>                    
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-3" for="address">Адрес:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="address" placeholder="Введите Адрес" />
                                </div>                    
                            </div>
                        </div>
                        
                    </div>                    
                    
                </form>
                <div id="drop_progress"></div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary b_save">Сохранить</button>
            </div>
		</div>
	</div>
</div>


<script>

    
    var noAvatar = 'public/img/no_avatar.jpg';
    var selOptions = {
        language: 'ru',
        tags: true,
        //allowClear: true, 
        //placeholder: 'Выбрать...',
        placeholder: {
            id: 0,
            text: 'Должность вакантна'
        },
        templateResult: function(state) {
            if(!$.isNumeric(state.id)) { return $('<div><img src="' +noAvatar+ '" /><h5>' +state.text+ '</h5><p>Почта: <b>...</b></p><p>Логин: <b>...</b></p></div>'); }
            //state.element.value.toLowerCase()
            return $('<div><img src="'+state.avatar+'" /><h5>' +state.text+ '</h5><p>Почта: <b>'+state.mail+'</b></p><p>Логин: <b>'+state.lgn+'</b></p></div>');            
        },
        templateSelection: function(state){
            if(!state.id) return state.text;
            if(!$.isNumeric(state.id)) state.avatar = noAvatar;
            return $('<span><img src="'+state.avatar+'" width="20" /> ' +state.text+ '</span>'); 
        },
        /*ajax: {
            url: '?go={url=user+search}',
            dataType: 'json',
            method: 'post',
            data: function (params) {
                return {
                    ajax: 1,
                    search: params.term,
                    page: params.page,
                };
            },
            delay: 500,
            processResults: function (data) {
                if(data.script) window.execScript ? execScript(data.script) : window.eval(data.script);
                return {
                    results: data.json
                };
            }
        }*/
    };
    
(function($){  
    
    $('#fio').select2(selOptions)
             .on('select2:select', function(e){
                if(!$.isNumeric(e.params.data.id)) e.params.data.avatar = noAvatar;
                e.params.data.fio = e.params.data.id; //иначе обнуляется
                elly.form('.form_user', e.params.data);
                $('.avatar').attr('src', e.params.data.avatar); 
             })/*
             .on('change', function(e){ // при включенном allowClear отлов удаления
                if(!e.target.value) 
                {
                    elly.form('.form_user', {});
                    $('.avatar').attr('src', noAvatar);
                }                
             })*/;
    
    $('#struct_edit').on('click', '.b_save', function(){
        elly.ajax('{url=user+save}', '#struct_edit', function(json){
            
            //создание блока ирархии
            //нет проверки
            var form   = $('#struct_edit');
            var parent = form.find('[name="code_parent"]').val();
            var fio    = form.find('[name="fio"] :selected').text();
            var code   = json.code; //form.find('[name="codeid"]').val();
            
            if(!fio) fio = 'Вакансия';
            
            if(parent)
            {
                var obj = $('.tree_org li a[data-code="'+ parent +'"]');
                var ul = obj.next('ul');
                
                if(!ul.length) ul = obj.after('<ul></ul>').next();
                ul.append(
                    '<li>\
                    <a href="#" class="clr_1" data-code="'+ code +'">\
                        <img src="'+ $('.avatar').attr('src') +'?'+ Math.random() +'" />\
                        <b>'+ fio +'</b>\
                        <span>'+ form.find('[name="name"]').val() +'</span>\
                    </a>\
                    </li>'
                );    
            }
            else
            {
                //обновление данных в структуре                
                var obj = $('.tree_org li a[data-code="'+ code +'"]');
                                
                obj.find('img').attr('src', $('.avatar').attr('src') +'?'+ Math.random())
                   .end().find('b').text(fio)
                   .end().find('span').text(form.find('[name="name"]').val());
            }
            treeRebuild();
            elly.close();
        });
    });
    $('#struct_edit').on('click', '.b_psw', function(){        
        var obj = $(this);
        obj.toggleClass('glyphicon-eye-open glyphicon-eye-close');
        if(obj.hasClass('glyphicon-eye-open'))
             obj.prev('input').attr('type', 'password');
        else obj.prev('input').attr('type', 'text');
    });
    
        
    elly.fileUpload({
        drop: '.avatar',
        progress: '#drop_progress',
        php: '{url=user+upload}',
        onstart: function(){
            return {code: $('#fio').val()};
        },
        callback: function(file, newName, obj){            
            if(newName == -1) elly.msg('Некорректный формат файла. Используйте один из следующих: JPG, PNG, GIF, BMP');
            else
            {
                var avatar = newName +'?'+ Math.random();
                var code = $('#struct_edit').find('[name="codeid"]').val();
                var user = $('#struct_edit').find('[name="fio"]').val();
                
                $('.avatar').attr('src', avatar);                
                $('.tree_org li a[data-code="'+ code +'"]').find('img').attr('src', avatar);
                //обновляю аватарки в select2
                selOptions.data.map(function(v,i){
                    if(v.id == user) v.avatar = avatar;
                    return v;
                });
                $('#fio').empty().select2(selOptions);
            }
            setTimeout(function(){
                obj.remove();
            }, 1500);
        },
    });
    
    treeRebuild();
    
    $('.tree_org li')
    .on('click', 'a', function(e){ e.preventDefault(); })
    .on('mousedown', 'a', function(e) { // при удерживании мыши
                var d = $(this); // получаем текущий элемент
                //var dd = d.clone().addClass('tree_drop').css({position: 'fixed'}).appendTo('.tree_org').wrap('<li></li>'); //.insertAfter('.tree_org').wrap('<li></li>');
                
                if(!d.is('a')) return;
                if(e.which != 1) return; // только левый клик
                                
                var dd = d.parent().clone().css({position: 'fixed', display: 'none'}).appendTo('.tree_org');
                $(document).unbind('mouseup'); // очищаем событие при отпускании мыши
                //o.start(d); // выполнение пользовательской функции
                
                
                var f = d.offset(), // находим позицию курсора относительно элемента                            
                    //x = e.pageX - f.left,// слева                            
                    //y = e.pageY - f.top; // и сверху
                    x = $(document).scrollLeft() + e.pageX - f.left,  // слева
                    y = $(document).scrollTop() + e.pageY - f.top;  // и сверху
                $(document).mousemove(function(a) { // при перемещении мыши
                        if(!dd.is(':visible')) dd.show();
                        dd.css({'top': a.pageY - y + 'px', 'left': a.pageX - x + 'px'}); // двигаем блок
                });
                $(document).mouseup(function(e) { // когда мышь отпущена
                        $(document).unbind('mousemove'); // убираем событие при перемещении мыши
                        $(document).unbind('mouseup');
                        //o.stop(d); // выполнение пользовательской функции
                        
                        var dropElem = findDroppable(e, dd);
                        
                        //console.log(dropElem);
                        //console.log(dd);
                        
                        var isSelf = false;
                        $(dropElem).parents('li').each(function(){
                            if($(this).children('a').get(0) == d.get(0)) {
                                isSelf = true;
                                return false;
                            }
                        });
                        
                        if(dropElem == d.get(0)) 
                        {
                            //elly.msg('Вызов меню');
                            d.addClass('active');
                            $('.tree_menu').css({top: e.pageY, left: e.pageX -160, display: 'block'})
                                           .bind('mouseleave', {obj: d}, function(eObj){
                                                //elly.msg('Скрытие меню');
                                                eObj.data.obj.removeClass('active');
                                                $(this).unbind('mouseleave').hide()
                                                       .find('li').unbind('click');
                                           })
                                           .find('li')
                                           .bind('click', {obj: d}, function(eObj){
                                                //elly.msg('Клик по "'+ $(this).text() +'"');
                                                var d = eObj.data.obj;
                                                d.removeClass('active');
                                                $(this).parents('.tree_menu').unbind('mouseleave').hide()
                                                       .find('li').unbind('click');
                                                switch($(this).index())
                                                {
                                                    case 0: //редактировать
                                                        elly.ajax('{url=user+edit}', {code: d.data('code')}, function(json){
                                                            $('#struct_edit .modal-title').text('Редактирование должности');
                                                            selOptions.data = json.fio_data;
                                                            $('#fio').empty().select2(selOptions);                                                            
                                                            elly.modalForm('#struct_edit', json);
                                                            $('#fio').trigger('change');
                                                            $('.avatar').attr('src', json.avatar);
                                                        });
                                                        break;
                                                    case 1: //новый подчинённый
                                                        elly.ajax('{url=user+edit}', {code: 0}, function(json){
                                                            $('#struct_edit .modal-title').text('Новый подчинённый');
                                                            selOptions.data = json.fio_data;
                                                            $('#fio').empty().select2(selOptions);
                                                            elly.modalForm('#struct_edit', {});
                                                            $('#fio').trigger('change');
                                                            $('.avatar').attr('src', noAvatar);
                                                            $('#struct_edit [name="code_parent"]').val(d.data('code'));
                                                        });
                                                        break;
                                                    case 2: //удалить сотрудника
                                                        elly.confirm('Сотрудник будет освобождён от занимаемой должности. Продолжить?', function(ok){
                                                            if(ok) elly.ajax('{url=user+struct_vacans}', {code: d.data('code')}, function(){
                                                                elly.msg('Должность стала вакантной.');
                                                                d.find('b').text('Вакансия')
                                                                 .end()
                                                                 .find('img').attr('src', noAvatar);
                                                            });
                                                        });
                                                        break;
                                                    case 3: //удалить должность
                                                        elly.confirm('Должность будет удалена. Продолжить?', function(ok){
                                                            if(ok) elly.ajax('{url=user+struct_del}', {code: d.data('code')}, function(json){
                                                                if(json == '-1') elly.msg('Должность не может быть удалена пока есть хоть один подчинённый.');
                                                                else {
                                                                    elly.msg('Должность сокращена.');
                                                                    var li = d.parent('li');
                                                                    if(!li.siblings().length) li.parent('ul').remove();
                                                                    li.remove();
                                                                    treeRebuild();
                                                                }
                                                            });
                                                        });
                                                        break;
                                                }
                                           });
                        }
                        else if(isSelf) elly.msg('Нельзя переносить вниз в свою же ветку, т.к. это приведёт к коллизии.');
                        else if(!dropElem) elly.msg('<b>Удаление?</b><br />Для удаления воспользуйтесь всплывающим меню.');
                                     
                        if(dropElem /*&& dropElem != d.get(0)*/ && !isSelf) // если не null и не тот же самый элемент
                        {                            
                            var ul = $(dropElem).next('ul');
                            if(!ul.length) ul = $(dropElem).after('<ul></ul>').next();
                            //ul.append(dd.attr('style', '').parent());
                            ul.append(dd.attr('style', ''));
                            
                            var li = d.parent();
                            if(!li.siblings().length) li.parent().remove();
                            else li.remove();
                            
                            treeRebuild();
                            elly.ajax('{url=user+parent_upd}', {code: d.data('code'), parent: $(dropElem).data('code')}, function(){
                                elly.msg('Сотрудник успешно переподчинён.');                                
                            });                            
                        }
                        else dd.remove();
                        
                        //dd = null;  // иначе при следующем клике удаляется перенесённый элемент
                });
                
                function findDroppable(event, dd) {
                    // спрячем переносимый элемент
                    dd.hide(); //dd.hidden = true;        
                    // получить самый вложенный элемент под курсором мыши
                    var elem = document.elementFromPoint(event.clientX, event.clientY);        
                    // показать переносимый элемент обратно
                    dd.show(); //dd.hidden = false;        
                    if(elem == null) return null; // такое возможно, если курсор мыши "вылетел" за границу окна
                    return elem.closest('.tree_org li > a');
                }
                return false;
    });  
    
    function treeRebuild()
    {
        $('.tree_org ul').addClass('vertical');
        $('.tree_org li').has('ul li').each(function(){
            $(this).parent('ul').removeClass('vertical');
        });
    }
    
})(jQuery);

</script>