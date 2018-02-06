<?php
/* Обязательные настройки */

define('dbHOST', '10.51.21.60');
define('dbNAME', 'SM_ELLY');
define('dbUSER', 'sa');
define('dbPASS', 'Afina954120');
define('dbTYPE', 'mssql');                       //тип БД (mysql или mssql)


define('dbHOSTOLD', '10.51.21.203');
define('dbNAMEOLD', 'SM2016');
define('dbUSEROLD', 'sa');
define('dbPASSOLD', 'Afina954120');
define('dbTYPEOLD', 'mssql');                       //тип БД (mysql или mssql)


define('TITLE', 'SM v4.0');               //название сайта
define('KEY', 'uZPjfIqE6X');                     //ключ для шифрования
define('USER_ID', 'user_id');                    //название сессионной переменной
define('LOGIN', 1);                              //обязательная авторизация
define('CRIPT_LINK', 0);                        //шифрованные ссылки или ЧПУ


/* Настройки для работы с классом /system/lib/mail.class.php */

define('MAIL_SMTP', 'mail.333.kg');       //адрес smpt-сервера
define('MAIL_PORT', 25);                         //порт
define('MAIL_NAME', '');                //Отображаемое имя отправителя
define('MAIL_LGN', '');                   //учетная запись почты
define('MAIL_PSS', '');                //пароль от почты


/* Дополнительные настройки */

define('DEBUG', 1);                              //1 -ведение логов всегда, 0 -только на локальной машине
define('PLUGIN_WIKI', 1);                        //1 -отображать wiki всегда, 0 -только на локальной машине или при отладке
define('TRANSLATE', 0);                          //активация онлайн переводчика от Яндекса
date_default_timezone_set('Asia/Dhaka');        //установка временной зоны
define('LANG', 'russian');                       //используемый языковой файл /system/lang/
define('THEME', 'public/themes');                //директория используемого шаблона
define('PAGE_STEP', 20);

define('CS-SOFT', true);                        //защита от прямого открытия файлов php
//каждый php-файл начинаться должен с записи:
//if (!defined('CS-SOFT')) die("Hacking attempt!");

mb_internal_encoding('UTF-8');                  //настройка PHP для работы с данными в Юникоде
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');


define('SERVER_ADDR', '217.29.21.66');

?>