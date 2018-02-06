/**
 * @author avoc-b
 * @copyright CS-SOFT, 2012
 *  
 */
 
 /**
  * Пример использования:
  * 
  * Calendar.setup({            
  *         trigger    : "calendar-trigger",
  *         inputField : "calendar-inputField",
  *         dateFormat : "%d.%m.%Y %H:%M",
  *         showTime   : true,
  *         onSelect   : function() { this.hide() }
  *     });
  */
 
function selectDate(classField, classButton, time)
{
    time = time || false;
    var format = (time) ? "%d.%m.%Y %H:%M" : "%d.%m.%Y";
    
    Calendar.LANG("ru", "русский", {
    	fdow: 1,           // first day of week for this locale; 0 = Sunday, 1 = Monday, etc.
    	goToday: "Сегодня",
    	today: "Сегодня",  // appears in bottom bar
    	wk: "нед",
    	weekend: "0,6",    // 0 = Sunday, 1 = Monday, etc.
    	AM: "am",
    	PM: "pm",
    	mn: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
    	smn: ["янв", "фев", "мар", "апр", "май", "июн", "июл", "авг", "сен", "окт", "ноя", "дек"],
    	dn: ["воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота", "воскресенье"],
    	sdn: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб']
    });
    
    var count = $('.'+classField).length;
    for(i=0; i<count; ++i)
    {                           
        Calendar.setup({          
            trigger    : classButton,
            inputField : classField,
            dateFormat : format,
            showTime   : true,
            onSelect   : function() { this.hide() }
        });                
        var e = $('.'+classField).eq(i).attr('cln',1);
        var b = $('.'+classButton).eq(i).attr('cln',1);
    }
    $('.'+classField).removeAttr('cln');
    $('.'+classButton).removeAttr('cln');    
}
        
/**
 * Пример использования:
 * 
 * + вызов модального окна:
 * <input type="button" value="Modal PHP" onclick="ShowModal('getfile.php')" />
 * <input type="button" value="Modal IMG" onclick="ShowModal('1285056872_beauty_2.jpg')" />
 * 
 */
 
var dialog = false;
 
function Dialog(data, text, width)
{
    dialog = true;
    var base = Modal(data, text, width);
    dialog = false;
    return base;
} 

  
function Modal(url, title, width)
{
    width   = width || 400;
    var arr = url.split('.');
    var l   = arr.length-1;
    var base= this;    
            
    base.create = function()
                    {
                        if(!$('#modal_bg').length) $('body').append('<div id="modal_bg"></div>')
                        $('#modal_bg').fadeIn(300) //show('slow');
                        $('#modal_bg').after('<div class="modal_img"></div>');
                        //$('html').css({overflow: 'hidden'});
                    }    
    base.close = function()
                    {
                        //$('.modal_vraper').animate({top: -200}, 500, 'swing', function(){ base.clear(); });
                        $('.modal_vraper').fadeOut(300, function(){ base.clear(); });                        
                    }
    base.clear = function()
                    {
                        $('#modal_bg').fadeOut(300, function(){
                            $('.modal_vraper').remove(); 
                            $('.modal_img').remove(); 
                            $('#modal_bg').remove();
                            //$('html').css({overflow: ''});
                        });                        
                    }
    base.build = function(data, title, width)
                    {
                        var w   = $(window).width() - width;    
                        $('.modal_img').remove();        
                        $('#modal_bg').after('<div class="modal_vraper"></div>');   
                        $('.modal_vraper').css({'width' : width+'px', 'left' : w/2+'px'})
                                          .html('<div class="modal_head">'+title+'</div><div class="modal_x _mdclose"></div><div class="modal_body"><div>'+data+'</div></div>');
                        $('._mdclose').bind('click', function(){ base.close(); });
                        
                        //var hw = $(window).height(); 
                        var hw = window.innerHeight||document.documentElement.clientHeight||document.body.clientHeight;           
                        var h  = hw - $('.modal_body').outerHeight();//.height();
                                
                        if (h < 1) {
                            h = 100; 
                            $('.modal_body > div').css({'height' : (hw-200)+'px', 'overflow-y' : 'scroll'}); 
                            } 
                        //$('.modal_vraper').animate( {top: h/2}, 500 );
                        $('.modal_vraper').css('top',h/2).hide().fadeIn(800);
                        
                        $('.modal_head').drag({
                            obj: $('.modal_vraper')
                        });
                        
                        $('#modal_bg').click( function (){ base.close(); });                        
                    }
    
    base.create();
        
    if(dialog)
    {
        base.build(url, title, width);
    }
    else if(arr[l]=='jpg'||arr[l]=='png'||arr[l]=='gif') 
    {   
        var img = new Image();
        img.src = url;
        img.onload = function(){
            base.build('<img width="'+(width -20)+'" src="'+url+'">', title, width);
            }
        img.onerror = function(){
            base.clear();
            alert('Файл изображения не существует');
            }
    }
    else
    {
        var jqxhr = $.post('?go=' + url, {ajax: 1}, function(data)
        { 
            var json = JSON.parse(data);
            base.build(json.html, title, width); 
            if(json.script) window.execScript ? execScript(json.script) : window.eval(json.script);
        });  
        jqxhr.error(function() {             
            base.clear(); 
            alert("Ошибка выполнения модального окна"); 
        }); /*
        elly.Ajax(url, '', '', function(data){
            base.build(data.responseText, title, width);
        });*/
    }
    return base;
}


(function($){ 
    
    $.fn.drag = function(o) {
        var o = $.extend({
            start: function() {},// при начале перетаскивания
            stop: function() {},// при завершении перетаскивания
            obj: $(this)
        }, o);
        return $(this).each(function() {
            var d = $(this); // получаем текущий элемент
            c = o.obj;
            d.mousedown(function(e) { // при удерживании мыши
                c.css('position', 'fixed');
                $(document).unbind('mouseup'); // очищаем событие при отпускании мыши
                o.start(c); // выполнение пользовательской функции
                var f = c.offset(), // находим позицию курсора относительно элемента                            
                    //x = e.pageX - f.left,// слева                            
                    //y = e.pageY - f.top; // и сверху
                    x = $(document).scrollLeft() + e.pageX - f.left,  // слева
                    y = $(document).scrollTop() + e.pageY - f.top;  // и сверху
                $(document).mousemove(function(a) { // при перемещении мыши
                        c.css({'top': a.pageY - y + 'px', 'left': a.pageX - x + 'px'}); // двигаем блок
                });
                $(document).mouseup(function() { // когда мышь отпущена
                        $(document).unbind('mousemove'); // убираем событие при перемещении мыши
                        o.stop(c); // выполнение пользовательской функции
                });
                return false;
            });
        });
    }
    
})(jQuery);



function fileUpload(drop, progress, back, maxMBite, sendData)
{        
    var base = this;
    var self;
    
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
    /*base.back     = back || '#_upload_';
    base.maxMBite = maxMBite || 2;
    base.sendData = sendData || '';*/
    
    $('body').append('<div id="_upload_"></div>');            
    //$('#_upload_').html('<input type="file" multiple="multiple" accept="image/*,image/jpeg" />').css('display', 'none');
    $('#_upload_').html('<input type="file" multiple="multiple" />').css('display', 'none');
    
    $('#_upload_ input').on('change', function() { base.upload(this.files) });
    
    
    $(document).on('dragover', base.drop, function(){return false})
           .on('dragenter', base.drop, function(){return false})
           .on('drop', base.drop, function(e){
                    
                    // Отмена реакции браузера по-умолчанию
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if(e.originalEvent.dataTransfer.files.length) {
                        self = this;
                        base.upload(e.originalEvent.dataTransfer.files);
                    }
                    
              })
           .on('click', base.drop, function(){  //эмуляция клика выбора файлов
                    self = this;
                    $('#_upload_ input:file').trigger('click');
              });
                      
    
    base.upload = function(files)
                    {
                        var sendDataTemp = $.extend({ajax:1}, base.sendData);
                        var result = null;
                        if(base.onstart) result = base.onstart(self);
                        if(result != null) {if(typeof(sendDataTemp) == 'object') $.extend(sendDataTemp, result); else sendDataTemp = result;}
                                                
                        var maxBite = base.maxMBite *1024 *1024;  //2Mb
                        $(base.progress).html('');      
                        
                        $.each(files, function(i, file)
                        {    
                            var formData = new FormData;                                
                            formData.append('files[]', file);
                            if(typeof(sendDataTemp) == 'object') 
                            {
                                for(var key in sendDataTemp) formData.append(key, sendDataTemp[key]);
                            }
                            
                            var obj = base.create(i, file, obj).find('.fu_line'); 
                            
                            if(file.size > maxBite)
                            {    
                                obj.css({width:'100%', background:'#f55', textAlign:'center'})
                                   .html('Файл больше ' + base.maxMBite + ' Mb');
                                if(base.onfinish) result = base.onfinish(obj, self); 
                                return;
                            } 
                           /* console.log(formData);
                           */
                            $.ajax({
                                url: '?go=' + base.php,
                                type: 'post',
                                data: formData,
                                dataType: 'json',
                                contentType: false,
                                processData: false,
                                success: function (data){ 
                                    base.success(file, data.json[0], obj); 
                                    if(data.script) window.execScript ? execScript(data.script) : window.eval(data.script);                                    
                                },
                                xhr: function()
                                {                                        
                                    var xhr = new $.ajaxSettings.xhr();
                                    if(xhr.upload)  // проверка что осуществляется upload
                                        xhr.upload.addEventListener("progress",                                             
                                        function(e)
                                        {
                                            if (e.lengthComputable) {
                                                var procent = Math.round(100 * e.loaded / e.total) + '%';
                                                obj.text(procent).css('width', procent);                                                    
                                            }
                                        }
                                        , false);
                                    return xhr;
                                },
                                error: function( data, status, textMsg ) {            
                                    console.group('Ошибка AJAX-запроса в функции elly.Ajax()!');
                                        console.warn(textMsg);
                                        console.log(data.responseText);
                                        console.log(status); 
                                    console.groupEnd();           
                                },
                                complete: function() {if(base.onfinish) result = base.onfinish(obj, self);}
                            }); 
                        });   
                    }                   
    base.create = function(i, file) 
                    {
                        $(base.progress).append('<div id="info' + i + '" class="fu_progres"><div class="fu_name"></div><div class="fu_line"></div></div>');
                        var obj = $(base.progress + ' #info' +i);
                        obj.find('.fu_name').html( file.name + ' (' + (file.size/1048576).toFixed(2) + ' Mb)' );
                        return obj;
                    }                    
    base.success = function(file, newName, obj)
                    {
                        if(base.callback) base.callback(file, newName, obj);
                        else {
                            obj.parent().remove();
                            $(base.back).append('<input type="hidden" name="files[]" value="' + newName + '" />');
                        }
                    }              
}