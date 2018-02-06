/**
 * Elly Framework 
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2016
 * @version 3.2 (20.07.2016)
 */


var elly = {
    home: '', // регистрация домашней страницы (кореня сайта)
    wait: false, // защита от задвоенных вызовов
    modalObj: null, // указатель на модальное окно

    tpl: [],
    event: {},
    tplParce: function (tpl, data)
    {
        var html = '',
            tpl = this.tpl[tpl],
            self = this;
        if (typeof tpl == 'undefined')
            return '';

        data.forEach(function (item) {
            html += tpl.replace(/{{ ([\w ]+) }}/gi, function (str, key) {

                var arg = key.split(' ');

                if (arg.length == 1)
                    return item[key] ? item[key] : '';
                else
                {
                    var fun = self.event[arg[0]];
                    return typeof fun == 'undefined' ? '' : fun(item[arg[1]], item);
                }
            }) + '\r\n';
        });

        return html;
    },
    tplRegist: function (name, callback)
    {
        this.event[name] = callback;
    },
    homeUrl: function ()
    {
        var path = '';
        $('script').each(function ()
        {
            if (this.src && /elly.js/i.test(this.src))
            {
                var a = this.src.split(/\/{1}/);
                path = a.slice(0, a.length - 3).join('/') + '/';
                return false; // прерывание цикла
            }
        });
        return path;
    },
    // загрузчик в духе youtube
    progressbar: function (isFinish)
    {
        var obj = $('#elly_progressbar').children();
        if (isFinish)
        {
            obj.animate({width: '100%'}, 400, function () {
                obj.parent().remove();
            });
        } else
        {
            if (obj.length)
                obj.width(0);
            else
            {
                obj = $('<div id="elly_progressbar"><i></i></div>')
                    .appendTo('body')
                    .children()
                    .css({
                        position: 'fixed',
                        top: 0,
                        left: 0,
                        width: 0,
                        height: '4px',
                        backgroundColor: '#E53F42',
                        boxShadow: '-4px 0 4px #777',
                        zIndex: 19999
                    });
            }
            obj.animate({
                width: (50 + Math.random() * 30) + "%"
            }, 200);
        }
    },
    // обычный загрузчик
    loader: function (isShow, className)
    {
        className = className || '';
        var obj = $('#elly_loader');

        if (isShow == null)
            isShow = true;
        if (isShow)
        {
            if (obj.length)
                obj.hide().remove();
            if (className != '')
                className = ' class="' + className + '"';
            $('<div id="elly_loader"' + className + '><i>Загрузка <img src="' + elly.home + 'system/img/loading_h.png" /></i></div>')
                .appendTo('body')
                .children()
                .css({
                    position: 'fixed',
                    right: 0,
                    bottom: 0,
                    padding: '3px 10px 3px 30px',
                    color: '#fff',
                    fontStyle: 'normal',
                    backgroundColor: '#333',
                    zIndex: 19999
                });
        } else
            obj.hide().remove();
    },
    // обработка ajax-запроса, результат возвращается в виде HTML в заданный элемент
    ajaxHTML: function (url, sendData, back, callback)
    {
        elly.ajax(url, sendData, function (json, html) {
            if (back != null)
                $(back).fadeTo(0, 0).html(html).stop().fadeTo(500, 1);
            if (callback)
                callback(json, html);
        });
    },
    // обработка ajax-запроса, универсальная ф-ция
    ajax: function (url, sendData, callback)
    {
        if (elly.wait) {
            alert('Пожалуйста, подождите до завершения предыдущей операции.');
            return;
        }

        sendData = sendData || '';
        //back        = back || '';

        var sDataTemp = {ajax: 1};
        var formData = new FormData(); // в ajax нужна настройка processData и contentType
        var aDefault = true;

        if (typeof sendData == 'string' || !!sendData.select)
        {
            $(sendData).find('input, select, textarea').filter('[name]').serializeArray().forEach(function (obj, i)
            {
                sDataTemp[obj.name] = obj.value;
            });

            var files = $(sendData).find('input:file');
            if (files.length)
            {
                aDefault = false;
                files.each(function (i, file) {
                    if (file.files.length)
                        formData.append('file[]', file.files[0]);
                });
                for (var key in sDataTemp)
                    formData.append(key, sDataTemp[key]);
            } else
                formData = sDataTemp;
        } else
            formData = $.extend(sDataTemp, sendData);


        elly.wait = true;

        $.ajax({
            dataType: 'json',
            type: 'POST',
            url: 'index.php?go=' + url,
            data: formData,
            processData: aDefault,
            contentType: aDefault ? 'application/x-www-form-urlencoded' : false,
            beforeSend: function (xhr) {
                elly.progressbar();
            },
            error: function (jqXHR, status, textMsg) {
                console.group('Ошибка AJAX-запроса в функции elly.ajax()!');
                console.warn(textMsg);
                console.log(status);
                console.groupEnd();

                elly.dialog(jqXHR.responseText, 'Ошибка AJAX-запроса');
                elly.progressbar(1);
            },
            success: function (data) {
                elly.progressbar(1);
                if (typeof data != "undefined" && data != null)
                {
                    //if(back.length > 0) $(back).fadeTo(0,0).html(data.html).stop().fadeTo(500, 1);                    
                    if (data.script)
                        window.execScript ? execScript(data.script) : window.eval(data.script);
                    if (typeof data.json.error != "undefined" && data.json.error != 0)
                    {
                        location.reload();
                        exit();
                    }
                }
                ;
            },
            complete: function (jqXHR) {
                var data = JSON.parse(jqXHR.responseText);
                if (typeof data == "undefined")
                    data = {html: null, json: null};
                if (callback)
                    callback(data.json, data.html);
            }
        });
        elly.wait = false;
        return false;
    },
    // окно сообщений, которое через некоторое время само закрывается
    msg: function (text)
    {
        var msg = $('.elly_msg');

        var obj = $('<div class="elly_msg"><i>' + text + '</i></div>')
            .appendTo('body')
            .children()
            .css({
                position: 'fixed',
                bottom: 3,
                right: '-250px',
                width: '250px',
                padding: '10px 15px',
                backgroundColor: '#333',
                boxShadow: '0 0 5px #777',
                color: '#fff',
                fontStyle: 'normal',
                fontSize: '12px',
                lineHeight: 1.2,
                cursor: 'pointer',
                opacity: 0.9,
                zIndex: 1999
            })
            .stop(false, true)
            .animate({right: 0}, 400);

        var height = obj.outerHeight();
        if (msg.length)
        {
            msg.each(function () {
                var item = $(this).children();
                var bottom = parseInt(item.css('bottom').slice(0, -2)) + height + 3;
                item.stop(false, true).animate({bottom: bottom}, 200);
            });
        }
        setTimeout(function () {
            obj.animate({right: '-250px'}, 400, function () {
                obj.parent().remove();
            })
        }, 5000);
    },
    dialog: function (body, title, obj)
    {
        obj = obj || '#elly_modal';

        var self = $(obj);
        self.find('.modal-title').html(title);
        self.find('.modal-body').html(body);
        elly.modalObj = self.modal();

        return elly.modalObj;
    },
    close: function ()
    {
        if(elly.modalObj) elly.modalObj.modal('hide');
        return this;
    },
    // вызов модального окна с догрузкой контента
    modal: function (url, title, obj, sendData, callback)
    {
        title = title || '';

        elly.ajax(url, sendData, function (json, html) {
            elly.dialog(html, title, obj);
            if(callback) callback(json, html);
        });
        return false;
    },
    modalForm: function (obj, data)
    {
        elly.modalObj = $(obj).modal();
        if(data) elly.form(obj, data);

        return elly.modalObj;
    },
    confirm: function (text, callback)
    {
        var obj = $('#elly_confirm');
        obj.find('.modal-body').html(text);
        return obj.modal().one('hide.bs.modal', function () {
            if(callback) callback($(document.activeElement).hasClass('btn-ok'));
        });
    },
    // мультизагрузка файлов в несколько потоков
    //fileUpload: function(drop, progress, back, maxMBite, sendData)
    fileUpload: function (base)
    {
        //var base = this;
        var self;

        base = $.extend({
            drop: '', // элемент выступающий в роли кнопки загрузки
            progress: '', // элемент, где отображать процесс загрузки
            back: '#_upload_', // элемент, где будут создаваться скрытые поля с именами файлов
            maxMBite: 2, // мах размер файла
            sendData: '', // данные отправляемые на сервер
            callback: null, // событие происходящее после загрузоки всех файлов
            onstart: null, // событие происходящее перед каждой загрузкой файла
            onfinish: null, // событие происходящее после каждой загрузкой файла
            php: '/system/mod/upload.php'  // отрабатывающий скрипт на сервере
        }, base);
        /*
         if(typeof(drop) == 'object') {        
         base = {
         drop    : drop.drop || '',
         progress: drop.progress || '',
         back    : drop.back || '#_upload_',
         maxMBite: drop.maxMBite || 2,
         sendData: drop.sendData || '',
         callback: drop.callback,
         onstart : drop.onstart,
         onfinish: drop.onfinish,
         php     : drop.php || '/system/mod/upload.php'
         };
         }
         else base = {
         drop    : drop,
         progress: progress,
         back    : back || '#_upload_',
         maxMBite: maxMBite || 2,
         sendData: sendData || '',
         php     : '/system/mod/upload.php'
         };
         */

        $('body').append('<div id="_upload_"></div>');
        $('#_upload_').html('<input type="file" multiple="multiple" />').css('display', 'none');

        $('#_upload_ input').bind('change', function () {
            base.upload(this.files)
        });


        $(base.drop).bind('dragover', function () {
            return false
        })
            .bind('dragenter', function () {
                return false
            })
            .bind('drop', function (e) {

                // Отмена реакции браузера по-умолчанию
                e.preventDefault();
                e.stopPropagation();

                if (e.originalEvent.dataTransfer.files.length) {
                    self = this;
                    base.upload(e.originalEvent.dataTransfer.files);
                }

            })
            .bind('click', function () {  //эмуляция клика выбора файлов
                self = this;
                $('#_upload_ input:file').trigger('click');
            });


        base.upload = function (files)
        {
            var sendDataTemp = $.extend({ajax: 1}, base.sendData);
            var result = null;
            if (base.onstart)
                result = base.onstart(self);
            if (result != null) {
                if (typeof (sendDataTemp) == 'object')
                    $.extend(sendDataTemp, result);
                else
                    sendDataTemp = result;
            }

            var maxBite = base.maxMBite * 1024 * 1024;  //2Mb
            $(base.progress).html('');

            elly.wait = true;

            $.each(files, function (i, file)
            {
                var formData = new FormData;
                formData.append('file', file); //formData.append('files[]', file);
                if (typeof (sendDataTemp) == 'object')
                {
                    for (var key in sendDataTemp)
                        formData.append(key, sendDataTemp[key]);
                }

                var obj = base.create(i, file, obj).find('.fu_line');

                if (file.size > maxBite)
                {
                    obj.css({width: '100%', background: '#f55', textAlign: 'center'})
                        .html('Файл больше ' + base.maxMBite + ' Mb');
                    if (base.onfinish)
                        result = base.onfinish(obj, self);
                    return;
                }
                /* console.log(formData);*/

                $.ajax({
                    url: '?go=' + base.php,
                    type: 'post',
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        base.success(file, data.json, obj.parents('.fu_progres'));
                        if (data.script)
                            window.execScript ? execScript(data.script) : window.eval(data.script);
                    },
                    xhr: function ()
                    {
                        var xhr = new $.ajaxSettings.xhr();
                        if (xhr.upload)  // проверка что осуществляется upload
                            xhr.upload.addEventListener("progress",
                                function (e)
                                {
                                    if (e.lengthComputable) {
                                        var procent = Math.round(100 * e.loaded / e.total) + '%';
                                        obj.text(procent).css('width', procent);
                                    }
                                }
                            , false);
                        return xhr;
                    },
                    error: function (data, status, textMsg) {
                        console.group('Ошибка AJAX-запроса в функции elly.ajax()!');
                        console.warn(textMsg);
                        //console.log(data.responseText);
                        console.log(status);
                        console.groupEnd();

                        elly.dialog(data.responseText, 'Ошибка AJAX-загрузки файла');
                    },
                    complete: function () {
                        if (base.onfinish)
                            result = base.onfinish(obj, self);
                    }
                });
            });
            elly.wait = false;
        }
        base.create = function (i, file)
        {
            $(base.progress).append('<div id="info' + i + '" class="fu_progres"><div class="fu_name"></div><div class="progress"><div class="fu_line progress-bar"></div></div></div>');
            var obj = $(base.progress + ' #info' + i);
            obj.find('.fu_name').html(file.name + ' (' + (file.size / 1048576).toFixed(2) + ' Mb)');
            return obj;
        }
        base.success = function (file, newName, obj)
        {
            if (base.callback)
                base.callback(file, newName, obj);
            else {
                //obj.parent().remove();                                
                setTimeout(function () {
                    obj.remove();
                    $(base.back).append('<input type="hidden" name="files[]" value="' + newName + '" />');
                }, 1500);
            }
        }
    },
    // автозаполнение полей
    form: function (form, data)
    {
        var last_name = '';
        var last_index = {};

        if (typeof (data) != 'object')
        {
            console.log('Ошибка: в функцию нужно передавать вторым параметром Объект!');
            return false;
        }

        $(form).find('input, select, textarea').filter('[name]').each(function (i, v) {

            var obj = $(v);
            var type = obj.attr('type');
            var name = obj.attr('name');
            var val = null;
            var name2 = '';

            if (type == 'submit' || type == 'button')
                return;

            // обработка массивных переменных
            if (name && name.substr(-2) == '[]')
            {
                name2 = name.slice(0, -2);
                last_index[name2] = typeof (last_index[name2]) == 'undefined' ? 0 : last_index[name2] + 1;
                val = $.isArray(data[name2]) ? data[name2][last_index[name2]] : null;
            } else
                val = typeof (data[name]) == 'undefined' ? null : data[name];

            if (type == 'checkbox')
                obj.prop('checked', val);
            else if (type == 'radio')
            {
                if (name == last_name)
                    return;
                if (val == null)
                    $(form).find('[name="' + name + '"]').prop('checked', false);
                else
                    $(form).find('[name="' + name + '"][value="' + val + '"]').prop('checked', true);
                last_name = name;
            } else
            {
                // чтобы можно было отделить input:text, select и textarea друг от друга
                if (!type)
                    type = obj.get(0).tagName.toLowerCase();
                obj.val(val).trigger('change');
            }
        });
        return this;
    },
    // конвертирует дату полученную как из MSSQL-запроса, так и из MySql-запроса в нужный формат
    date: function(str, format)
    {
        if(typeof str != 'string') return '';

        str    = $.trim(str.replace(/\s+/, ' '));
        format = format || 'd.m.Y H:i:s';
        var PrefInt = function(number, len) {
            return (Array(len).join('0') + number).slice(-len);
        };

        if(/^(\d{4}|\d{2})-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(str))
        {
            var arr = str.split(/[\W]/);
            var dt = {Y: arr[0], m: PrefInt(arr[1],2), d: PrefInt(arr[2],2), H: arr[3], i: arr[4], s:arr[5]};
        }

        else if(/^\d{2}:\d{2}:\d{2}(\.\d{7})?$/.test(str))
        {
            var arr = str.split(/[\W]/);
            var dt = {Y: 0, m: 0, d: 0, H: arr[0], i: arr[1], s:arr[2]};
        }

        else if(/^\w{3} \d{1,2} \d{4} \d{2}:\d{2}:\d{2}:\d{3}(PM|AM)$/.test(str))
        {

            var month = {Jan:1,Feb:2,Mar:3,Apr:4,May:5,Jun:6,Jul:7,Aug:8,Sep:9,Oct:10,Nov:11,Dec:12};
            var arr = str.split(/[\W]/);
            var dt = {d: PrefInt(arr[1],2), Y: arr[2], m: PrefInt(month[arr[0]],2), H: arr[3], i: arr[4], s:arr[5]};
            if(arr[6].substr(-2) == 'PM' && dt.H != 12) dt.H = parseInt(dt.H) +12;
        }
        else return '';

        arr = format.split(/[\W]/);
        $.each(arr, function(i,v) {
            format = format.replace(v, dt[v]);
        });
        return format;
    },
    clear: function (clear)
    {
        clear = clear || 0;
        sessionStorage.debugClear = clear;
        location.reload();
    },
    table: function (arg)
    {

        var settings = {};

        var summaVal = {},
            tb = null,
            method = {
                init: function (options)
                {
                    settings = $.extend({
                        obj: null, // класс или id таблицы
                        sortOff: [], // номера столбцов, которым ненужна сортировка
                        columnSum: [], // номера столбцов, которые нужно просуммировать
                        columnCount: [], // номера столбцов под которыми вывести к-во записей
                        tpl: null, // шаблон в виде HTML или функция, возвращающая готовый HTML
                        data: null, // данные отображаемые при старте
                        ajax: '', // AJAX-ссылка
                        ajaxData: {}, // передаваемые данные в AJAX
                        itemCount: 0, // общее к-во записей
                        itemPos: 0, // с какой записи отображаются данные
                        pageSize: 20, // к-во строк в таблице
                    }, options);


                    if (settings.obj === null)
                        return;

                    tb = $(settings.obj);
                    var th = tb.find('thead th');

                    tb.addClass('ellyTable');
                    th.each(function (i, v) {
                        if ($.inArray(i, settings.sortOff) == -1)
                            $(v).addClass('sorting');
                    });

                    settings.ajaxData.summa_col = settings.columnSum;
                    settings.ajaxData.page_size = settings.pageSize;
                    settings.ajaxData.item_pos = settings.itemPos;
                    settings.ajaxData.item_count = 'вернуть item_count и item_pos';
                    settings.ajaxData.summa_val = 'вернуть суммы, где ключи это номера столбцов';
                    settings.ajaxData.tb = 'вернуть массив данных';

                    if (settings.data === null)
                        method.ajax();
                    else if (settings.data !== false)
                        method.build(settings.data);

                    th.bind('click', function () // изменение сортировки
                    {
                        var self = $(this);
                        if (self.hasClass('sorting'))
                            self.removeClass('sorting').addClass('sorting_asc');
                        else
                            self.toggleClass('sorting_asc sorting_desc');
                        self.siblings('.sorting_asc, .sorting_desc').removeClass('sorting_asc sorting_desc').addClass('sorting');

                        settings.ajaxData.order_col = self.index();
                        settings.ajaxData.order_asc = Number(self.hasClass('sorting_asc'));

                        //вызываю AJAX, передаю номер поля и тип сортировки
                        method.ajax();
                    });

                    tb.on('click', 'tbody tr', function () {
                        $(this).addClass('info').siblings().removeClass('info');
                    });

                    return method;
                },
                ajax: function (data)
                {
                    data = data || {};
                    if (typeof data == 'string') {
                        var dataTmp = {};
                        $(data).find('input, select, textarea').filter('[name]').serializeArray().forEach(function (obj)
                        {
                            if (!dataTmp.search)
                                dataTmp.search = {};
                            dataTmp.search[obj.name] = obj.value;
                        });
                        data = dataTmp;
                        data.item_pos = 0;
                    } else if (!$.isEmptyObject(data)) { // если пришёл запрос из вне ,а не пустой объект 
                        data.item_pos = 0;
                    }
                    settings.ajaxData = $.extend(settings.ajaxData, data);

                    elly.ajax(settings.ajax, settings.ajaxData, function (data) {
                        settings.itemCount = (typeof data.item_count == 'undefined') ? 0 : data.item_count;
                        settings.itemPos = (typeof data.item_pos == 'undefined') ? 0 : data.item_pos;
                        summaVal = (typeof data.summa_val == 'undefined') ? 0 : data.summa_val;
                        method.build(data.tb);

                        //console.log(data);
                    });
                },
                build: function (data)
                {
                    var html = '';
                    if (settings.tpl === null)
                    {
                        data.forEach(function (v) {
                            var html_tr = '';
                            for (var key in v) {
                                html_tr += '<td>' + v[key] + '</td>';
                            }
                            html += '<tr>' + html_tr + '</tr>';
                        });
                    } else if (typeof settings.tpl == 'function')
                    {
                        html = settings.tpl(data);
                    } else
                    {
                        elly.tpl['tb_tr'] = settings.tpl;
                        html = elly.tplParce('tb_tr', data);
                    }
                    tb.find('tbody').html(html);

                    method.updPage();
                    method.updTotal();
                },
                footer: function ()
                {
                    if (!tb.find('tfoot tr *').length)
                    {
                        var count = tb.find('thead tr *').length;
                        var html = '';
                        for (i = 0; i < count; i++) {
                            html += '<th></th>';
                        }
                        tb.find('tfoot').remove();
                        $('<tfoot>').html('<tr>' + html + '</tr>').appendTo(tb);
                    }
                    return tb.find('tfoot th');
                },
                updPage: function ()
                {
                    var pg = tb.next('.pagination');
                    if (!pg.length)
                        pg = $('<ul>').addClass('pagination').insertAfter(tb);

                    var html = '',
                        pagePos = Math.floor(settings.itemPos / settings.pageSize) + 1,
                        pageCount = Math.ceil(settings.itemCount / settings.pageSize),
                        posOt = pagePos - 5,
                        posDo = pagePos + 5;
                    if (posOt > 1)
                        html += '<li><a href="#">1</a></li><li class="disable"><a href="#">…</a></li>';
                    else
                        posOt = 1;
                    if (posDo > pageCount)
                        posDo = pageCount;
                    for (var i = posOt; i <= posDo; i++) {
                        html += '<li' + (i == pagePos ? ' class="active"' : '') + '><a href="#">' + i + '</a></li>';
                    }
                    if (posDo != pageCount)
                        html += '<li class="disable"><a href="#">…</a></li><li><a href="#">' + pageCount + '</a></li>';
                    pg.html(html);

                    pg.find('li').not('.active, .disable').children('a').bind('click', function (e) {
                        e.preventDefault();
                        settings.ajaxData.item_pos = (Number($(this).text()) - 1) * settings.pageSize;
                        method.ajax();
                    });
                },
                updTotal: function ()
                {
                    if (settings.columnSum.length)
                    {
                        var th = method.footer();
                        var total = {};
                        tb.find('tbody tr').each(function (i, v) {
                            var td = $(v).find('td');
                            settings.columnSum.forEach(function (col)
                            {
                                if (typeof total[col] == 'undefined')
                                    total[col] = 0;
                                var sum = Number(td.eq(col).text());
                                total[col] += sum;
                            });
                        });
                        var th = tb.find('tfoot th');
                        settings.columnSum.forEach(function (col, i) {
                            var html = total[col];
                            if (typeof summaVal[i] != 'undefined')
                                html += ' <span class="badge">' + summaVal[i] + '</span>';
                            th.eq(col).html(html);
                        });
                    }
                    if (settings.columnCount.length)
                    {
                        var th = method.footer(),
                            count = tb.find('tbody tr').length;
                        settings.columnCount.forEach(function (col) {
                            var html = count;
                            if (settings.itemCount)
                                html += ' <span class="badge">' + settings.itemCount + '</span>';
                            th.eq(col).html(html);
                        });
                    }
                },
            };

        // логика вызова метода
        if (method[arg])
            return method[arg].apply(this, Array.prototype.slice.call(arguments, 1));
        else if (typeof arg === 'object' || !arg)
            return method.init.apply(this, arguments);
        else
            $.error('Метод с именем ' + arg + ' не существует для jQuery.tooltip');

    },
    model: function (list) {
        return {
            list: list,
            index: function (id) {
                var out = null;
                $.each(this.list, function (i, v) {
                    if (v.codeid == id) {
                        out = i;
                        return false;
                    }
                });
                return out;
            },
            get: function (id) {
                //return this.list.filter(function(v){ return v.codeid == id})[0];
                return this.list[this.index(id)];
            },
            find: function (filter) {
                return this.list.filter(function (v) {
                    var isset = true;
                    for (var i in filter) {
                        if (filter[i] != v[i])
                            isset = false;
                    }
                    return isset;
                });
            },
            add: function (obj) {
                this.list.push(obj);
                return this.list.length;
            },
            upd: function (obj) {
                var i = this.index(obj.codeid);
                if (i != null)
                    this.list[i] = obj;
                else
                    return this.add(obj);
            },
            del: function (id) {
                var i = this.index(id);
                delete this.list[i];
            },
        };
    },
};


$(function () {

    elly.home = elly.homeUrl();

    // sessionStorage.debugClear = 1;

    /*$(document).bind({
     ajaxStart: function() { elly.progressbar(); },
     ajaxStop: function() { elly.progressbar(1); }
     });*/

    if ($('#elly_debug_js').length)
        $('<div id="elly_debug"><i>в разработке</i></div>')
            .appendTo('body')
            .children()
            .css({
                display: 'block',
                boxSizing: 'unset',
                position: 'fixed',
                backgroundColor: '#a236bc',
                border: '3px solid #e574ff',
                boxShadow: '0 0 4px #777',
                cursor: 'help',
                height: '24px',
                opacity: 0.8,
                right: '-43px',
                top: '43px',
                transform: 'rotate(45deg)',
                width: '200px',
                zIndex: 19998,
                textAlign: 'center',
                color: '#fff',
                font: '12px/22px Comic Sans MS'
            })
            .end()
            .attr('title', 'Включён режим отладки! \nОбратитесь к разработчикам. \nДля временного скрытия \nкликните по элементу.')
            .bind('click', function () {
                $(this).remove();
            });

    //Вывод только цифр
    //44 - запятая, 45 - минус, 46 - точка, 8 - backspace
    $('body').on('keypress', '.integer', function (e)
    {
        if (e.which != 8 && e.which != 46 && e.which != 45 && e.which != 0 && (e.which < 48 || e.which > 57))
            return false;
    });

    /*
     if(typeof($.datepicker) != 'undefined') {
     $.datepicker.setDefaults($.datepicker.regional['ru']);
     //$('.datepicker').datepicker();
     elly.Calendar('.datepicker',0); 
     if(typeof($.datetimepicker) != 'undefined')
     elly.Calendar('.timepicker',1);
     }*/
});