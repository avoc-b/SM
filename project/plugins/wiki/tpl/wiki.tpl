<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
  
    <script type="text/javascript" src="system/js/jquery-1.9.0.min.js"></script>
    <script type="text/javascript" src="system/js/elly.js"></script>
    <script type="text/javascript" src="system/js/elly.ui.js"></script>
    <script type="text/javascript" src="system/mod/kindeditor/kindeditor.js"></script>
    <script type="text/javascript" src="system/mod/kindeditor/ru_Ru.js"></script>
    <script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js"></script>
    
    <link type="text/css" rel="stylesheet" href="system/css/elly.css" />    
    <link type="text/css" rel="stylesheet" href="system/mod/kindeditor/default.css" />
    <link type="text/css" rel="stylesheet" href="project/plugins/wiki/tpl/style.css" />
    
    {header}
    
    <link href="/public/favicon.png?h" type="image/x-icon" rel="shortcut icon"/>        
</head>
<body>

    <div class="wiki_wrap">
    
        <h2 class="wiki_title"><span><a href="/" title="Вернуться на главную">{site}</a> ::</span> {title}</h2>
        
        <div class="wiki_content" id="wiki_content">{text}</div>
        <div class="wiki_panel">
            <input type="button" value="Список страниц" id="wiki_list" />
            <input type="button" value="Новая страница" id="wiki_new" />
            <input type="button" value="Редакция" id="wiki_edit" />
        </div>
        
        
        <div class="wiki_menu_pos">
            <div class="wiki_menu_b"> &raquo; </div>
            {menu}
        </div>
    
    </div>
    
    
    <script>
        var ke = null;
        var modal;
        
        if(location.search == '') $('#wiki_edit').hide();
        if(location.search == '?config') $('#wiki_content').css({whiteSpace: 'pre'});
        
        $('#wiki_edit').on('click', function(){
            if(location.search == '') return;            
            if(ke) {                
                elly.ajax("{url=wiki+write}", {text: ke.html()});
                KindEditor.remove("#wiki_content"); 
                ke = null;
                this.value = 'Редакция';
            }
            else {
                if(location.search == '?config') 
                {
                    ke = KindEditor.create("#wiki_content", {items : ['source']});
                    ke.clickToolbar('source');
                    ke.createDialog({
                            name : 'about',
                            width : 300,
                            title : ke.lang('about'),
                            body : '<div style="margin:20px;">Данный файл является СИСТЕМНЫМ и содержит настройки для других файлов. <br>В файле недопустимы html теги.</div>'
                    });
                }
                else ke = KindEditor.create("#wiki_content");
                this.value = 'Сохранить';
            }                            
        });
        
        $('#wiki_new').on('click', function(){
            modal = Modal('{url=wiki+form_create}','Новая страница');
        });
        
        $('#wiki_list').on('click', function(){
            modal = Modal('{url=wiki+form_list}','Список страниц');
        });
        
        $(document).on('dblclick', '.modal_body option', function(){
            var file = $(this).val();
            modal.close();
            location.href = elly.home +'wiki?'+ file;
        });
    </script>

</body>
</html>