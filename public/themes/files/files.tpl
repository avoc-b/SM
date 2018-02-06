<link href="/public/css/select2.css" rel="stylesheet" />
<link href="/public/css/clockpicker.css" rel="stylesheet">
<link rel="stylesheet" href="system/mod/kindeditor/default.css" />

<script src="/public/js/select2.full.min.js"></script>
<script src="/public/js/select2.ru.js"></script>
<script src="/public/js/clockpicker.js"></script>
<script type="text/javascript" src="system/mod/kindeditor/kindeditor.js"></script>
<script type="text/javascript" src="system/mod/kindeditor/ru_Ru.js"></script>

<style>
    td, th {
        padding: 10px;
    }

    #process_list {
        margin-right: 10px;
        font-size: small;
        width: 100%;
        vertical-align: top !important;
    }
    #process_list tr {
        vertical-align: top !important;
    }

    .list-group{
        padding: 0px;

    }
    .element::-webkit-scrollbar { width: 1px; }
    .list-group li {
        list-style-type: none;
        text-align: left;
        padding-top: 35px;
        padding-bottom: 35px;
    }

    tr.entity, .bottom-line {
        border-bottom: 1px solid #d7be99;
    }

    tr.entity {
        padding: 10px;
    }

    .user-thumb > img {
        width: 40px;
        height: 40px;
        border-radius: 5px;
        border: 2px solid #259ff7;
    }
    .user-thumb-sm > img {
        width: 14px;
        height: 14px;
        border-radius: 2px;
        border: 1px solid #259ff7;
    }

    .users > li, .no-style {
        list-style-type: none;
    }

    .users {
        padding: 0px;
    }

    span.title {
        width: 100%;
        text-align: center;
        font-family: fantasy;
    }

    td.event-left {
        text-align: center;
        max-width: 200px;
    }

    .comment_date {
        font-style: italic;
        text-align: right;
    }

    .comment_body{
        margin-left: 17px;
    }

    img.author {
        border-color: #f2b311;
    }
    .event-title {
        font-weight: bold;
    }
    .event-container {
        border-left:1px solid #d7be99;
        font-size: 11px;
    }
    .event-container ul {
        padding: 0px;
    }
    .event-container-head {
        font-weight: bold;
    }
    button.event-page {
        margin-left: 5px;
        margin-right: 5px;
    }

    li.chosen {
        border-left: 4px solid #ba49b5;
    }

</style>

<div class="sidebar" style="overflow-y: hidden; text-align: center;">   
    <span class="title">Типы файлов</span>
    <ul id="file_exts" class="list-group">
        [for=fileTypes]
        <li class="list-group-item rgba-{fileTypes.color}-light" data-name="{fileTypes.name}">{fileTypes.name}</li>
        [/for]
    </ul>
    <span class="title">Группы проектов</span>
    <ul id="event_groups" class="list-group">
        [for=eventGroups]
        <li class="list-group-item rgba-{eventGroups.color}-light" data-code="{eventGroups.codeid}">{eventGroups.name}</li>
        [/for]
    </ul>
    <span class="title">Дополнительно</span>
    <ul id="event_ids" class="list-group">
        <li class="list-group-item rgba-blue-light" data-event="0">Без прикрепленного события</li>
    </ul>
</div>


<div class="content" style="padding-right: 10px;">
    <div class="panel-body">
        <div class="col-lg-2">
            <div class="" style="padding-top: 7px;">Найдено файлов: <span id="events-total">0</span></div>
        </div>
        <div class="col-lg-2">
            <div id="event_size" style="padding-top: 7px;">Кол-во эл-ов на странице: <span>0</span></div>
        </div>
        <div class="col-lg-8 text-right" id="event-pagination">

        </div>
    </div>
    <div id="events_div">
        <h4>Выберите группу слева</h4>
    </div>
</div>

<div class="footer">
    <div class="footer_h"><i class="glyphicon glyphicon-chevron-up"></i> История файлов / Сообщения</div>
    <div class="container-fluid">
        <p class="text-muted">Footer...</p>
    </div>
</div>

<!-- шаблон модального окна -->

<script>
    $(document)
        .on('click', '#events-size', function (e) {
            console.log(this);
        })
        .on('click', '.event-page', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $this = $(this);
            if ($this.hasClass('active')) {
                $this.blur();
                return false;
            }
            window.event_data.page = $(this).attr('data-page');
            console.log(window.event_data.page);
            event_load();
        })
        .on('click', '.list-group li', function (e) {
            $this = $(this);
            if ($this.hasClass('chosen')) {
                $this.removeClass('chosen');
            } else {
                $this.addClass('chosen');
            }
            window.event_data.page = 0;
            event_load();
        })
        .on('click', '.event-files ul li', function (e) {
            e.preventDefault();
            e.stopPropagation();
            window.location = '/files/file/' + $(this).attr('data-id');
        })
        .ready(function () {

        });
    function dt(moment_dt) {
        return moment_dt ? moment_dt.format('YYYY-MM-DD HH:mm') : '';
    }

    var event_load = function () {
        window.event_data.exts = [];
        window.event_data.groups = [];
        window.event_data.events = [];
        $('.list-group li.chosen').each(function () {
            if ($(this).attr('data-code'))
                window.event_data.groups.push($(this).attr('data-code'));
            if ($(this).attr('data-name'))
                window.event_data.exts.push($(this).attr('data-name'));
            if ($(this).attr('data-event'))
                window.event_data.events.push($(this).attr('data-event'));
        });

        elly.ajaxHTML('{url=files+data_load}', {
            obj: window.event_data
        }, '#events_div', function (data) {
            console.log(data);
            if (data && data.pages) {
                event_pagination(data.pages);
            }
            if (data && data.total) {
                $('#events-total').html(data.total);
            }
            if (data && data.size) {
                window.event_data.size = data.size;
            }
            if (data && data.page) {
                window.event_data.page = data.page;
            }
        });
    };

    var event_pagination = function (pages) {
        console.log(pages);
        $pages = $('#event-pagination').html('');
        if (!pages) {
            return;
        }
        $.each(pages, function (k, v) {
            $pages.append(event_page(v));
        });
    };
    var event_page = function (page) {
        if (page.title == 'break') {
            return '&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;';
        }
        return '<button type="button" class="btn btn-default event-page' + page.active + '" data-page="' + page.page + '">' + page.title + '</button>';
    };

    window.event_data = {
        page: 0,
        total: 0,
        size: 4,
        groups: [],
        exts: [],
        events: []
    };
    $('#event_size > span').html(window.event_data.size);
    $('#event_size').hide();
</script>

