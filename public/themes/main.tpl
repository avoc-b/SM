<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />    
    <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/public/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/public/css/style.css" />

    <script type="text/javascript" src="/public/js/jquery.min.js"></script>  
    <script type="text/javascript" src="/public/bootstrap/js/bootstrap.min.js"></script>  
    <script type="text/javascript" src="/system/js/elly.js"></script>

    {header}

    <link href="/public/favicon.png?h" type="image/x-icon" rel="shortcut icon"/>
</head>
<body>
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container-fluid row">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/"><img src="public/img/logo.png" width="100%" /></a>
            </div>
            <div class="navbar-collapse collapse">
                <form class="navbar-form navbar-left" id="f_search">
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" placeholder="Найти..." />
                        <span class="glyphicon glyphicon-search form-control-feedback"></span>
                    </div>
                </form>
                <ul class="nav navbar-nav navbar-right container-fluid">
                    <li><a href="/" data-toggle="tooltip" data-placement="left" title="Задачи"><span class="glyphicon glyphicon-tasks"></span></a></li>
                    <li><a href="/group" data-toggle="tooltip" data-placement="left" title="Задачи по группам"><span class="glyphicon glyphicon-list"></span></a></li>
                    <li><a href="/files" data-toggle="tooltip" data-placement="left" title="Файлы"><span class="glyphicon glyphicon-cloud-download"></span></a></li>
                    <li><a href="/task" data-toggle="tooltip" data-placement="left" title="Циклические задачи"><span class="glyphicon glyphicon-refresh"></span></a></li>
                    <li><a href="/graph" data-toggle="tooltip" data-placement="left" title="Диаграмма Ганта"><span class="glyphicon glyphicon-object-align-left"></span></a></li>
                    <li><a href="/report" data-toggle="tooltip" data-placement="left" title="Отчёты"><span class="glyphicon glyphicon-stats"></span></a></li>
                    <li><a href="/user" data-toggle="tooltip" data-placement="left" title="Штатная структура"><span class="glyphicon glyphicon-user"></span></a></li>
                    <li><a href="/settings/group" data-toggle="tooltip" data-placement="left" title="Управление группами"><span class="glyphicon glyphicon-briefcase"></span></a></li>
                    <li><a href="/settings" data-toggle="tooltip" data-placement="left" title="Настройки"><span class="glyphicon glyphicon-cog"></span></a></li>
                    [wiki]
                    <li><a href="/wiki?info"><span class="glyphicon glyphicon-info-sign"></span></a></li>
                    <li><a href="/wiki?history"><span class="glyphicon glyphicon-pushpin"></span></a></li>
                    [/wiki]
                    <li class="navbar_pofile navbar-text_"><a href="/calendar/detal">
                        <img src="{profile_img}" />
                        <b>{profile_fio}</b>
                        <span>{profile_name}</span>
                    </a></li>
                    <li><a href="javascript://" onclick="elly.ajax('{url=login+logout}')" data-toggle="tooltip" data-placement="left" title="Выйти"><span class="glyphicon glyphicon glyphicon-off"></span></a></li>
                </ul>
            </div>
        </div>
    </div>

    {content}

    <!-- шаблон для модального окна -->
    <div class="modal fade" id="elly_modal" role="dialog" aria-labelledby="elly-modal-title" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="elly-modal-title"></h4>
                </div>
                <div class="modal-body" id="elly-modal-body"></div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="elly_confirm" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <b class="modal-title">Системное сообщение</b>
                </div>
                <div class="modal-body">
                    <p>This is a small modal.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary btn-ok" data-dismiss="modal" id="confirm">Подтвердить</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function(){
            $('[data-toggle="tooltip"]').tooltip({container: 'body'});
        });
    </script>
</body>
</html>