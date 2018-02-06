
<link rel="stylesheet" href="public/css/fullcalendar.css" />
<link rel="stylesheet" href="public/css/fullcalendar.print.css" media="print" />
<link rel="stylesheet" href="public/bootstrap/css/bootstrap-datetimepicker.css" />
<link rel="stylesheet" href="system/mod/kindeditor/default.css" />
<script type="text/javascript" src="public/js/moment.min.js"></script>
<script type="text/javascript" src="public/js/fullcalendar.js"></script>
<script type="text/javascript" src="public/js/fullcalendar.ru.js"></script>
<script type="text/javascript" src="public/bootstrap/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="system/mod/kindeditor/kindeditor.js"></script>
<script type="text/javascript" src="system/mod/kindeditor/ru_Ru.js"></script>


<div class="sidebar">        
    <div id="myDiagramDiv"></div>
    
    <div class="sidebar_panel">
        <a data-toggle="tab" href="#tab_tree" class="btn btn-default btn-xs"><i class="glyphicon glyphicon-user" data-toggle="tooltip" data-placement="right" title="Штатная структура"></i></a>
        <a data-toggle="tab" href="#tab_group" class="btn btn-default btn-xs"><i class="glyphicon glyphicon-th-list" data-toggle="tooltip" data-placement="right" title="Группы"></i></a>
    </div>
    
    <div class="tab-content">
        <div id="tab_tree" class="tab-pane fade in active">

            {plugin=tree+vidget+id=tree_elly_main}
            
        </div>
        <div id="tab_group" class="tab-pane fade">
            <label><input type="checkbox" id="group_all" /> Все группы</label>
            <ul class="list-group">
                [for=group]
                <label class="list-group-item" code="{group.codeid}"><input type="checkbox" name="group" /> {group.name}</label>
                [/for]
            </ul>
        </div>
    </div>

</div>


<div class="content" id="calendar">

</div>


<div class="footer">
    <div class="footer_h"><i class="glyphicon glyphicon-chevron-up"></i> История файлов | Сообщения системы</div>
    <div class="container-fluid row">
        <!--<p class="text-muted">Footer...</p>-->
        <div class="col-sm-6 last_files">

            <h4>Последние загруженные файлы:</h4>
            
            [for=file]
            <span>
                <i class="glyphicon glyphicon-file"></i> 
                <a href="{file.url}" target="_blank">{file.real_name}</a>
                <p>{file.date_system} | {file.fio} ({file.struct}) | {file.title}</p>
            </span>
            [/for]
            
        </div>
        <div class="col-sm-6 last_history">
            
            <h4>Сообщения системы:</h4>
            
            [for=history]
            <span data-code="{history.code_event}">
                <i class="glyphicon glyphicon-chevron-right"></i>                 
                <a href="javascript://">{history.status} | {history.title}</a>
                <p>{history.date_system} | {history.fio} ({history.struct})</p>
            </span>
            [/for]
            
        </div>
    </div>
</div>


<!-- шаблон модального окна -->
<div class="modal fade" id="calendar_modal" role="dialog">
    <div class="modal-dialog" style="width: 800px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <small class="label label-default"></small>
                <h4 class="modal-title"></h4>
                <div class="checkbox c_status">

                    <input type="hidden" name="status" />

                    <label><input type="checkbox" value="1" />Принята <i class="glyphicon glyphicon-share-alt"></i></label>
                    <label style="opacity: 0.3;"><input type="checkbox" value="2" disabled="disabled" />Выполнена <i class="glyphicon glyphicon-share-alt"></i></label>
                    <label style="opacity: 0.3;"><input type="checkbox" value="3" disabled="disabled" />Проверена</label>
                </div>

            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">                
                <button class="btn btn-default b_upload" type="button" data-toggle="tooltip" title="Кликните по кнопке и выберите файлы или перетащите и бросьте файлы сюда">
                    <i class="glyphicon glyphicon-circle-arrow-up"></i> 
                    Загрузить файлы
                </button>
                <div class="upload_progress"></div>

                <!--<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>-->
                <button type="button" class="btn btn-default b_delete hide">Удалить</button>
                <button type="button" class="btn btn-primary b_edit">
                    <i class="glyphicon glyphicon-pencil"></i>
                    Редактировать
                </button>
            </div>
        </div>
    </div>
</div>


<!-- шаблон модального окна -->
<div class="modal fade" id="calendar_modal_edit" role="dialog">
    <div class="modal-dialog" style="width: 800px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Редактирование задачи</h4>
            </div>
            <div class="modal-body">

                <form class="form row" role="form">

                    <input type="hidden" name="codeid" />
                    <input type="hidden" name="users" />
                    <div class="col-sm-8">
                        <div class="form-group_">
                            <label class="control-label" for="group">Группа:</label>
                            <select class="form-control input-sm" name="code_group">
                                <option value="0">Выберите группу</option>
                                [for=group]
                                <option value="{group.codeid}">{group.name}</option>
                                [/for]
                            </select>
                        </div>
                        <div class="form-group_">
                            <label class="control-label" for="title">Заголовок:</label>
                            <input type="text" class="form-control input-sm" name="title" placeholder="Введите текст Заголовка" />
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="body">Описание:</label>
                            <textarea class="form-control input-sm editor" name="body" style="width: 100%;"></textarea>
                        </div>

                        <div class="dropup">
                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Участники
                                <span class="caret"></span></button>
                            <ul class="dropdown-menu">

                                {plugin=tree+vidget+form}

                            </ul>
                        </div>

                    </div>
                    <div class="col-sm-4">

                        <div class="form-group_">
                            <label class="control-label" for="">Дата:</label>
                            <input type="text" class="datetime form-control input-sm" name="date_start" />
                            <input type="text" class="datetime form-control input-sm" name="date_end" />
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="allday" />Весь день</label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="is_primary" />Важность</label>
                        </div>
                        <div class="form-group_">
                            <label class="control-label" for="">Баллы:</label>
                            <input type="text" class="form-control input-sm integer" name="price" />
                        </div>
                        <div class="form-group_">
                            <label class="control-label" for="timer">Оповестить за (мин):</label>
                            <input type="text" class="form-control input-sm integer" name="timer" />
                        </div>

                    </div>


                </form>
                <div id="drop_progress"></div>

            </div>
            <div class="modal-footer">                
                <!--<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>-->
                <button type="button" class="btn btn-primary b_save">Сохранить</button>
            </div>
        </div>
    </div>
</div>



<script>

    var calendar = $('#calendar');

    $(function () {

        //$('[data-toggle="tooltip"]').tooltip();

        var ke = KindEditor.create('textarea.editor', {
            resizeType: 1,
            allowPreviewEmoticons: false,
            allowImageUpload: false,
            items: [
                'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
                'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
                'insertunorderedlist', '|', 'emoticons', 'image', 'link']
        });

        $('.datetime').datetimepicker({
            pickTime: true,
            useMinutes: true,
            language: 'ru'
        });

        $('.footer_h').on('click', function () {
            $('body').toggleClass('body_f');
            $('i', this).toggleClass('glyphicon-chevron-up glyphicon-chevron-down');
        });


        elly.fileUpload({
            drop: '.b_upload',
            progress: '.upload_progress',
            php: '{url=calendar+upload}',
            onstart: function () {
                $('.b_upload').tooltip("hide");
                return {code: $('#calendar_modal').find('[name="codeid"]').val()};
            },
            callback: function (file, json, obj) {
                if (json == -1)
                    elly.msg('Данный тип файла запрещен к загрузке на сервер.');
                else {
                    $('.upload_files').append('<span data-code="' + json.code + '">\
                                            <i class="glyphicon glyphicon-file"></i> \
                                            <a href="' + json.url + '" target="_blank">' + file.name + '</a>\
                                            <i class="glyphicon glyphicon-remove"></i>\
                                       </span>');
                }
                setTimeout(function () {
                    obj.remove();
                }, 1500);
            }
        });
        
        tree_elly('#tree_elly_main', {
            change: function (e) {
                if (window.calendar.loading === true) {
                    return;
                }
                window.calendar.loading = true;
                setTimeout(function () {
                    calendar.fullCalendar('refetchEvents');
                }, 10);
            }
        });
                
        
        function clickStatus()
        {
            $(this).addClass('fc-state-active').siblings().removeClass('fc-state-active');
            calendar.fullCalendar('refetchEvents');
        }
        function getStatus()
        {
            var btn = $('.fc-right .fc-state-active').find('.icon-circle');
            var status = 0;

            if(btn.hasClass('icon-warning')) status = 2;
            else if(btn.hasClass('icon-success')) status = 3;

            return status;
        }
        
        calendar.fullCalendar({
            header: {
                left: 'prev,title,next month,agendaWeek,agendaDay',
                //center: 'title',
                right: 'status1,status2,status3',//false,
            },
            customButtons: {
                status1: {  //text: 'В работе',
                    icon: ' icon-circle icon-default',
                    click: clickStatus,
                },
                status2: {  //text: 'Выполненные',
                    icon: ' icon-circle icon-warning',
                    click: clickStatus,
                },
                status3: {  //text: 'Проверенные',
                    icon: ' icon-circle icon-success',
                    click: clickStatus,
                },
            },
            views: {
                month: {
                    titleFormat: 'MMMM, YYYY',
                    columnFormat: 'dddd',
                },
                agendaWeek: {
                    titleFormat: 'MMMM, DD',
                    columnFormat: 'dddd, DD',
                },
                agendaDay: {
                    titleFormat: 'dddd, MMMM DD',
                    columnFormat: 'dddd M/D',
                },
            },
            height: 'auto',
            defaultView: 'agendaWeek',
            minTime: '{minTime}:00',
            maxTime: '{maxTime}:00',
            slotDuration: '00:15:00',
            slotLabelInterval: '01:00',
            slotLabelFormat: [
                'MMMM YYYY', // top level of text
                'H:mm'        // lower level of text
            ],
            lang: 'ru',
            buttonIcons: {// show the prev/next text
                    prev: 'chevron-left',
                    next: 'chevron-right',
                    prevYear: 'left-double-arrow',
                    nextYear: 'right-double-arrow',
                    month: 'th', //'calendar',
                    agendaWeek: 'th-large',
                    agendaDay: 'align-justify',
            },
            droppable: true, // this allows things to be dropped onto the calendar
            editable: true,
            //eventLimit: true, // allow "more" link when too many events
            weekNumbers: false,
            selectable: true,
            selectHelper: true,
            select: function (start, end, jsEvent, view, res)
            {
                var hour = (end - start) / 3600000;
                console.log(hour, start.hour(), start.minute(), (hour%24), (hour%24 == 0 && start.hour() == 0 && start.minute() == 0 ? 1 : 0));
                //var title = prompt('Заголовок события:');
                var obj = elly.modalForm('#calendar_modal_edit', {
                                                                    date_start: start.format('DD.MM.YYYY HH:mm'),
                                                                    date_end: end.format('DD.MM.YYYY HH:mm'),
                                                                    price: 0,
                                                                    timer: 30,
                                                                    allday: (hour%24 == 0 && start.hour() == 0 && start.minute() == 0 ? 1 : 0)
                                                                });
                ke.html('');
                obj.find('.modal-title').html('Новое задание');
                obj.find('.tree_elly input').prop('checked', false);
                //obj.find('.b_delete').addClass('hide');
                //obj.on('click', '.b_save', function(){
                saveEvent(obj, start, end, false);
            },            
            events: function (start, end, timezone, callback)
            {
                elly.ajax('{url=calendar+data}', {
                                                    start:  start.format('YYYY-MM-DD')/*.unix()*/,
                                                    end:    end.format('YYYY-MM-DD'),
                                                    users:  treeListUser($('#tree_elly_main li')),
                                                    groups: treeListUser($('#tab_group label')),
                                                    status: getStatus(),
                                                    search: $('#f_search input').val(),
                                                }, function (events) {
                                                    calendar.fullCalendar('removeEvents');
                                                    callback(events);
                                                    window.calendar.loading = false;
                                                });
            },
            eventClick: openEvent,
            eventResize: calendarChange,
            eventDrop: calendarChange,
        });
        
        $('.fc-right .fc-button:first').addClass('fc-state-active');

        function calendarChange(event, delta, revertFunc)
        {
            //console.log(event, delta);
            var data = {code: event.id, allday: Number(event.allDay), start: event.start.format('YYYY-MM-DD HH:mm')};
            if(event.end) data.end = event.end.format('YYYY-MM-DD HH:mm');

            elly.ajax('{url=calendar+change}', data, function(json){
                if(json != 0) revertFunc();
            });
        }

        function openEvent(calEvent)
        {
            //$('#calendar_modal .modal-header small').html(calEvent.group);
            //$('[name="status"]').val(calEvent.status).trigger('change'); //обязательно тригер, чтобы сработал отлов события
            elly.modal('{url=calendar+form}', calEvent.title, '#calendar_modal', {
                code: calEvent.id
            }, function (data) {

                if(data.is_primary) data.group += '<i class="glyphicon glyphicon-star"></i>';

                $('[name="status"]').val(data.status).trigger('change'); //обязательно тригер, чтобы сработал отлов события
                var modal = $('#calendar_modal');
                modal.find('.modal-header small').html(data.group);

                if(data.access) modal.find('.b_delete').removeClass('hide');
                else            modal.find('.b_delete').addClass('hide');

                elly.modalObj.one('click', '.b_edit', function () {
                    elly.close();
                    ke.html(data.body);
                    setTimeout(function () { //без задержки путаница с окнами происходит у скрипта
                        var obj = elly.modalForm('#calendar_modal_edit', data);
                        obj.find('.modal-title').html('Редактировать задание').end()
                            //.find('.tree_elly input').prop('checked', false)
                            .find('.tree_elly li').each(function () {
                            var self = $(this);
                            var code = Number(self.attr('code'));
                            self.children('span').children('input').prop('checked', (data.struct_list.indexOf(code) > -1));
                        });
                        //saveEvent(obj, dt(calEvent.start), dt(calEvent.end), true);
                        saveEvent(obj, dt(data.start), dt(data.end), true);
                    }, 300);
                });

                elly.modalObj.find('.b_delete')
                    .unbind('click')
                    .bind('click', function () {
                        //if(!isEdit) return;
                        var code = elly.modalObj.find('[name="codeid"]').val();
                        elly.confirm('Удалить задачу?', function(ok){
                            if(!ok) return;
                            elly.ajax('{url=calendar+status}', {code: code, status: -1}, function(json) {
                                if (json != 0) {
                                    elly.msg('Произошла ошибка');
                                    return;
                                }
                                //обновляю информацию в календаре
                                calendar.fullCalendar('removeEvents', code);
                                elly.close();
                            });
                        });
                    });
            });
        }
        
        function treeListUser(obj)
        {
            // собираю список участников
            var users = obj.map(function () {
            //var users = obj.find('.tree_elly li').map(function () {
                var self  = $(this);
                var check = self.find('input:first').prop('checked');
                //var check = self.children('span').children('input').prop('checked');
                if(check) return self.attr('code');
            }).get();
                        
            return users.join(';');
        }

        function saveEvent(obj, start, end, isEdit)
        {
            obj.find('.b_save')
                .unbind('click')
                .bind('click', function () {
            //obj.one('click', '.b_save', function () {
                var data = {
                    title: obj.find('input:text').val(),
                    start: start,
                    end: end
                };
                if (data.title)
                {
                    ke.sync();

                    // собираю список участников
                    obj.find('[name="users"]').val(treeListUser(obj.find('.tree_elly li')));

                    elly.ajax('{url=calendar+save}', obj, function (json)
                    {
                        //console.log(json);
                        if(json == '-1') {
                            elly.msg('<h4>Не все поля заполнены!</h4> `Группа`, `Заголовок`, `Дата` и `Участники` являются обязательными полями.');
                            return;
                        }

                        data.id = Number(json.code);
                        data.color = json.color;

                        if (isEdit) {
                            // ПЕРЕДАЛАТЬ: Нужно получить из пхп все данные о событии !!!
                            var events = calendar.fullCalendar('clientEvents', json.code);
                            //events[0] = $.extend(events[0], data);
                            events[0].title = data.title;
                            calendar.fullCalendar('updateEvent', events[0]);
                        } else
                            calendar.fullCalendar('renderEvent', data, true); // stick? = true


                        calendar.fullCalendar('unselect');
                        elly.close();
                    });
                }
                else elly.msg('Сохранять нечего!');
            });
        }
    
        $(document)
            .on('change', '.c_status input:checkbox', function()
            {
                var input = $(this),
                    label = input.parent(),
                    virt = $('.c_status [name="status"]'),
                    statusOld = virt.val();
    
                if (input.prop('checked'))
                {
                    label
                        .next().css('opacity', 1)
                        .children().prop('disabled', false)
                        .end().end()
                        .prev().css('opacity', 0.7)
                        .children().prop('disabled', true);
                    virt.val(label.index());
                } else
                {
                    label
                        .next().css('opacity', 0.7)
                        .children().prop('disabled', true)
                        .end().end()
                        .prev().css('opacity', 1)
                        .children().prop('disabled', false);
                    virt.val(label.index() - 1);
                }
                var data = {
                    status: virt.val(),
                    code: label.parents('.modal-content').find('[name="codeid"]').val()
                };

                // скрываю событие, если изменился статус
                if(data.status > 1 || (data.status == 1 && statusOld == 2))
                    calendar.fullCalendar('removeEvents', data.code);

                elly.ajax('{url=calendar+status}', data, function (json) {
                    if (json != 0) {
                        elly.msg('Произошла ошибка');
                        return;
                    }
                    //обновляю информацию в календаре
                    var events = calendar.fullCalendar('clientEvents', data.code);
                    events[0].status = Number(data.status);
                    calendar.fullCalendar('updateEvent', events[0]);
                });
            })
            .on('change', '.c_status [name="status"]', function(e)
            {
                //e.preventDefault();
                var status = $(this).val();
    
                // при смене значения в виртуальном элементе, соответсвующе отображаю это
                $('.c_status input:checkbox').each(function (i, v) {
                    var input = $(this),
                        label = input.parent(),
                        index = label.index() - 1;
    
                    if (status > index) {
                        input.prop({disabled: false, checked: true});
                        label.css('opacity', (status - 1 == index ? 1 : 0.7));
                    } else if (status == index) {
                        input.prop({disabled: false, checked: false});
                        label.css('opacity', 1);
                    } else {
                        input.prop({disabled: true, checked: false});
                        label.css('opacity', 0.7);
                    }
                });
            })
            .on('click', '.last_history a', function(e){
                e.preventDefault();

                var code  = $(this).parent().data('code');
                //var event = calendar.fullCalendar('clientEvents', code);
                //openEvent(event[0]);
                openEvent({id: code});
            })
            .on('change', '#group_all', function(){
                var self = $(this);
                self.parent().next().find('input').prop('checked', self.prop('checked'));
                calendar.fullCalendar('refetchEvents');
            })
            .on('change', '#tab_group [name="group"]', function(){
                calendar.fullCalendar('refetchEvents');
            })
            .on('submit', '#f_search', function(e){
                e.preventDefault();
                //elly.msg('search');
                calendar.fullCalendar('refetchEvents');
            });
            /*.ready(function () {
                tree_elly('#tree_elly_main', {
                    change: function (e) {
                        if (window.calendar.loading === true) {
                            return;
                        }
                        window.calendar.loading = true;
                        setTimeout(function () {
                            calendar.fullCalendar('refetchEvents');
                        }, 10);
                    }
                });
            });*/
    
    });

    

    function dt(moment_dt) {
        return moment_dt ? moment_dt.format('YYYY-MM-DD HH:mm') : '';
    }

</script>