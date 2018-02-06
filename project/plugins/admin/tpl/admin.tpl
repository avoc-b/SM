<html>
<head>    
    <script type="text/javascript" src="system/js/jquery-1.9.0.min.js"></script>
    <script type="text/javascript" src="system/js/elly.js"></script>
    <script type="text/javascript" src="system/js/elly.ui.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="/public/js/ace.js" type="text/javascript" charset="utf-8"></script>
    
    <link type="text/css" rel="stylesheet" href="system/css/elly.css" />
    <link type="text/css" rel="stylesheet" href="project/plugins/admin/tpl/style.css" />
    
    {header}
    
    <link type="image/x-icon" rel="shortcut icon" href="/public/favicon.png" />
</head>
<body>

    
    <h2 class="title"><span>Elly ::</span> MS Admin</h2>
    <div class="panel">
        <input class="btn btn_run" type="button" value="Выполнить" title="Ctrl + Enter" />
        <div class="panel_r">
            <input type="button" class="btn btn_check active" value="Структура" />
            <input type="button" class="btn btn_check" value="Данные" />
        </div>
    </div>
    
    <div id="sidebar">
        <ul class="sidebar">
            <li><p id="modal_open">SQL ЗАПРОС</p></li>
            <li><p>ТАБЛИЦЫ<span>{count_tb}</span></p>
                <ul class="sidebar-tb">
                [for=tb]
                    <li><a href="javascript://">{tb.name}</a></li>
                [/for]
                </ul>
            </li>
            <li><p>ВИДЫ<span>{count_vw}</span></p>
                <ul class="sidebar-vw">
                [for=vw]
                    <li><a href="javascript://">{vw.name}</a></li>
                [/for]
                </ul>
            </li>
            <li><p>ПРОЦЕДУРЫ<span>{count_pr}</span></p>
                <ul class="sidebar-pr">
                [for=pr]
                    <li><a href="javascript://">{pr.name}</a></li>
                [/for]
                </ul>
            </li>
            <li><p>ФУНКЦИИ<span>{count_fn}</span></p>
                <ul class="sidebar-fn">
                [for=fn]
                    <li><a href="javascript://">{fn.name}</a></li>
                [/for]
                </ul>
            </li>                
         </ul>
    </div>
    <div id="content"></div>
    <div class="modal_w">
        <div class="modal_h">Поле sql-запроса (Ctrl + Enter)</div>
        <pre id="modal">{query_last}</pre>
    </div>
    
    
    <script>
    
    var editor;
    var idTimer;
    
    (function ($) {
                
        $('#content').on('dblclick', '.tb_col th, .tb_row td:first-child', function()
        {
            editor2.insert($(this).text());
            editor2.focus();
        });
        
        function loadContent()
        {
            $('#content').find('.editor').each(function(i,v)
            {
                editor = ace.edit(this);
                editor.container.style.opacity = "";
                editor.session.setMode("ace/mode/sql");
                //editor.setTheme("ace/theme/monokai");    
            });
        }
        
        
        $('#sidebar .sidebar')
        .on('click', 'a', function()
        {
            var obj  = $(this);
            var type = obj.parents('ul').attr('class');
            var data = $('.btn_check.active').val() == 'Данные' ? 1 : 0;
            
            if (!idTimer)
            idTimer = setTimeout(function () 
            {
                clearTimeout(idTimer);
                idTimer = false;
                
                //$('iframe#content').attr('src', '?show='+type+'&name='+obj.text()+'&data='+data);
                elly.ajaxHTML('{url=adm+show}', {type: type, name: obj.text(), data: data}, '#content', loadContent);
                
                obj.parents('ul.sidebar').find('a').removeClass('active');
                obj.addClass('active');
            }, 200);
            
            return false;
        })
        .on('dblclick', 'a', function()
        {
            clearTimeout(idTimer);
            idTimer = false;
            
            editor2.insert($(this).text());
            editor2.focus();
        })
        .on('click','p',function(){
            $(this).next('ul').slideToggle(400);
        }); 
        
        $('#modal_open').on('click',function()
        {
            $('body').toggleClass('modal');
            if(editor) editor.resize();
        });
        
        $('.btn_check').on('click', function()
        {       
            if($(this).hasClass('active')) return;
            
            var obj  = $('#sidebar .sidebar a.active');
            var type = obj.parents('ul').attr('class');
            var data = $(this).val() == 'Данные' ? 1 : 0;
            
            //$('iframe#content').attr('src', '?show='+type+'&name='+obj.text()+'&data='+data);
            elly.ajaxHTML('{url=adm+show}', {type: type, name: obj.text(), data: data}, '#content', loadContent);
            
            $(this).addClass('active').siblings().removeClass('active');
        });
        
        $('.btn_run').on('click', function()
        {   
            if(!$('body').hasClass('modal')) 
            {
                $('body').addClass('modal');
                if(editor) editor.resize();
            }
            
            var query = editor2.session.getTextRange(editor2.getSelectionRange());
            if(!query) query = editor2.getValue();
            
            if(query == '') 
            {
                alert('Пустой запрос!');
                return;
            }
                    
            elly.ajaxHTML('{url=adm+query}', {query: query}, '#content'); 
        });
        
        editor2 = ace.edit('modal');
        editor2.container.style.opacity = "";
        editor2.session.setMode("ace/mode/sql");
        editor2.commands.addCommand({
            name: 'myCommand',
            bindKey: {win: 'Ctrl-RETURN',  mac: 'Command-RETURN'},
            exec: function(editor) {
                $('.btn_run').trigger('click');
            },
            readOnly: true
        });
        
    }(jQuery));
    </script>

</body>
</html>