<html>
<head>
    <script type="text/javascript" src="system/js/jquery-1.9.0.min.js"></script>
    <script type="text/javascript" src="system/js/elly.js"></script>
    <script type="text/javascript" src="system/js/elly.ui.js"></script>
    
    <link type="text/css" rel="stylesheet" href="system/css/elly.css" />
    <link type="text/css" rel="stylesheet" href="project/plugins/install/tpl/style.css" />
    
    {header}
    
    <link href="/public/favicon.png?h" type="image/x-icon" rel="shortcut icon"/>
    
</head>
<body>

    
    <h2><span>Elly Framework ::</span> Установка</h2>
    
    
    <p style="line-height: 2em; color:#fff; text-align:center; background:<?php if($create == -1) echo '#0ce'; elseif($create == 2) echo '#ec0'; else echo '#e00'; ?>;"><?php echo $error; ?></p>
    
    <form method="post" action="<?php echo HOME ?>/install">
        
        <input type="hidden" name="file" id="file" value="" />
        
        <fieldset>
            <legend> Подключение к Базе Данных </legend>
            
            <p>Хост: <input type="text" name="dbHOST" value="<?php echo $f->get('dbHOST'); ?>" /></p>
            <p>Логин: <input type="text" name="dbUSER" value="<?php echo $f->get('dbUSER'); ?>" /></p>
            <p>Пароль: <input type="text" name="dbPASS" value="<?php echo $f->get('dbPASS'); ?>" /></p>
            <p>БД: <input type="text" name="dbNAME" value="<?php echo $f->get('dbNAME'); ?>" /></p>    
            <p>Тип БД: 
                <select name="dbTYPE">
                    <option value="mysql"<?php if($f->get('dbTYPE') == 'mysql') echo ' selected'; ?>>MySql</option>
                    <option value="mssql"<?php if($f->get('dbTYPE') == 'mssql') echo ' selected'; ?>>MS SQL</option>
                </select>
            </p> 
        </fieldset>
        
        <fieldset>
            <legend> Обязательные настройки </legend>
            
            <p>Заголовок сайта: <input type="text" name="TITLE" value="<?php echo $f->get('TITLE'); ?>" /></p>
            <div>Ключ шифрования: 
                <button id="key_button" onclick="">Сгенирировать</button>
                <div id="key_desktop"></div>
                <input id="key_input" type="hidden" name="KEY" value="<?php echo $f->get('KEY'); ?>" />
            </div>
            <p>Название сессионной переменной: <input type="text" name="USER_ID" value="<?php echo $f->get('USER_ID'); ?>" /></p>
            <p>Обязательная или нет авторизация: <input type="checkbox" name="LOGIN" <?php if($f->get('LOGIN')) echo 'checked'; ?>/></p>
            
            <p>Режим отладки:
                <select name="DEBUG">
                    <option value="0"<?php if($f->get('DEBUG') == 0) echo ' selected'; ?>>Только на локальном сервере</option>
                    <option value="1"<?php if($f->get('DEBUG') == 1) echo ' selected'; ?>>Включен всегда (не рекомендуется)</option>
                </select>
            </p>
                         
        </fieldset>
        
        <fieldset>
            <legend> Отправка почты по SMTP </legend>
                      
            <p>Адрес smpt-сервера: <input type="text" name="MAIL_SMTP" value="<?php echo $f->get('MAIL_SMTP'); ?>" /></p>
            <p>Порт: <input type="text" name="MAIL_PORT" value="<?php echo $f->get('MAIL_PORT'); ?>" /></p>
            <p>Учетная запись почты: <input type="text" name="MAIL_LGN" value="<?php echo $f->get('MAIL_LGN'); ?>" /></p>
            <p>Пароль от почты: <input type="text" name="MAIL_PSS" value="<?php echo $f->get('MAIL_PSS'); ?>" /></p>
            <p>Отображаемое имя отправителя: <input type="text" name="MAIL_NAME" value="<?php echo $f->get('MAIL_NAME'); ?>" /></p>
        </fieldset>
        
        <fieldset>
            <legend> Дополнительные настройки </legend>
                      
            <p>Папка с шаблонами: <input type="text" name="THEME" value="<?php echo $f->get('THEME'); ?>" /></p>
            <p>Используемые языковые файлы:
                <select name="LANG">
                    <option value="russian"<?php if($f->get('LANG') == 'russian') echo ' selected'; ?>>russian</option>
                    <option value="english"<?php if($f->get('LANG') == 'english') echo ' selected'; ?>>english</option>
                </select>
            </p>
            <p>Записей на страницу: <input type="text" name="PAGE_STEP" value="<?php echo $f->get('PAGE_STEP'); ?>" /></p>
            <p>Онлайн перевод ошибок:
                <select name="TRANSLATE">
                    <option value="0"<?php if($f->get('TRANSLATE') == 0) echo ' selected'; ?>>Выключен</option>
                    <option value="1"<?php if($f->get('TRANSLATE') == 1) echo ' selected'; ?>>Яндекс.Переводчик</option>
                    <option value="2"<?php if($f->get('TRANSLATE') == 2) echo ' selected'; ?>>Google Translate</option>
                </select>
            </p>
            <p>* место еще под один параметр</p>
        </fieldset>
        
        <div class="panel">
            <input type="submit" value="Сохранить" />
            
            <?php if($create == 1): ?>
            <input type="button" value="Создать пустую БД" onclick="go(1)" /> или 
            <?php endif; ?>
            <?php if($create > 0): ?>
            <input type="button" value="Восстановить БД" onclick="listDB()" />
            <?php endif; ?>
            <?php if($create == 2 || $create == -1 ): ?>
            <input type="button" value="Закончить настройку" onclick="go(3)" />
            <?php endif; ?>
        </div>
        
    </form>
    
    <script type="text/javascript">
        
        function go(operation)
        {
            if(operation == 1) document.forms.item(0).action = elly.home +'install?action=1'; else
            if(operation == 2) document.forms.item(0).action = elly.home +'install?action=2'; else
            if(operation == 3) document.forms.item(0).action = elly.home +'install?action=3'; 
            
            document.forms.item(0).submit();
        }
        function listDB()
        {
            Modal('{url=install+listdb}','БД проекта');
        }
        /*
        function generatePass()
        {
            var length = 15;
            var result = '';
            var symbols = [
                              'q','w','e','r','t','y','u','i','o','p',
                              'a','s','d','f','g','h','j','k','l',
                              'z','x','c','v','b','n','m',
                              'Q','W','E','R','T','Y','U','I','O','P',
                              'A','S','D','F','G','H','J','K','L',
                              'Z','X','C','V','B','N','M',
                              1,2,3,4,5,6,7,8,9,0
                          ];
            for (i = 0; i < length; i++) {
                result += symbols[Math.floor(Math.random() * symbols.length)];
            }
            $('[name=KEY]').val(result);
        }*/
        /*
  		var password_length = 10,
			symbols = new Array(
			'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
			'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
			1,2,3,4,5,6,7,8,9,0
        );
        
		$(document).ready(function(){
			for(var j = 0; j < password_length; j++) {
				var html = '';
				for(var i = 0; i < symbols.length; i++) {
					html += '<div class="symbol">'+symbols[i]+'</div>';
				}
				html = '<div class="symbols">'+html+'</div>';
				$('#key_desktop').append(html);
			}
            startSymbol();
			
			$('#key_button').click(function(e){
				e.preventDefault();
                $('#key_input').val('');
				makeSymbol(0);
			});
			
			function makeSymbol(num_symbol){
				if (num_symbol >= password_length) return false;
				
				var idx = Math.floor(Math.random() * symbols.length);
				$('#key_input').val($('#key_input').val()+symbols[idx]);
				var new_pos = - idx * 25;
				$('#key_desktop .symbols').eq(num_symbol).animate(
                    { top: new_pos + 'px' }, 
                    function(){ makeSymbol(++num_symbol); }
                );
			}
            
            function startSymbol()
            {
                var idx, key = $('#key_input').val();
                $('#key_desktop .symbols').each(function(i,v){
                    idx = $(v).find(':contains("'+key[i]+'")').index();
                    $(v).css('top', - idx * 25);
                });
            }
		});*/
        
        $(document).ready(function(){
            generator.init('#key_desktop', '#key_input', '#key_button');
        });
        
        var generator = 
        {
            objDesktop: null,
            objInput: null,
            objButton: null,
            length: 10,
            symbols: new Array(
    			'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
    			'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
    			1,2,3,4,5,6,7,8,9,0
            ),
            init: function(objDesktop, objInput, objButton)
            {
                this.objDesktop = $(objDesktop);
                this.objInput = $(objInput);
                this.objButton = $(objButton);
                
                for(var j = 0; j < this.length; j++) 
                {
    				var html = '';
    				for(var i = 0; i < this.symbols.length; i++) {
    					html += '<div class="symbol">'+this.symbols[i]+'</div>';
    				}
    				html = '<div class="symbols">'+html+'</div>';
    				this.objDesktop.append(html);
                }                
                var idx, key = this.objInput.val();
                this.objDesktop.find('.symbols').each(function(i,v){
                    idx = $(v).find(':contains("'+key[i]+'")').index();
                    $(v).css('top', - idx * 25);
                });                
                var self = this;
                this.objButton.bind('click', function(e){
    				e.preventDefault();
                    self.objInput.val('');
    				self.makeSymbol(0);
    			});
			
            },
            makeSymbol: function(num_symbol)
            {
				if (num_symbol >= this.length) return false;
				
                var self = this;
				var idx  = Math.floor(Math.random() * this.symbols.length);
				this.objInput.val(this.objInput.val()+this.symbols[idx]);
				var new_pos = - idx * 25;
				this.objDesktop.find('.symbols').eq(num_symbol).animate(
                    { top: new_pos + 'px' }, 
                    function(){ self.makeSymbol(++num_symbol); }
                );
			}
        };
        
    </script>

</body>
</html>