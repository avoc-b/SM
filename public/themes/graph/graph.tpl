<link href="/public/css/select2.css" rel="stylesheet" />
<link href="/public/css/clockpicker.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/public/css/timeline.css">


<script src="/public/js/select2.full.min.js"></script>
<script src="/public/js/select2.ru.js"></script>
<script src="/public/js/clockpicker.js"></script>

<script type="text/javascript" src="/public/js/timeline-locales.js"></script>
<script type="text/javascript" src="/public/js/timeline.js"></script>


<style>
    span.timeline.user img {
        height: 30px;
        float: left;
        margin-right: 10px;
    }
    .timeline-groups-text {
        width: 100%;
    }
    .popover, .timeline-title .timeline-event-content span.title{
        color: black;
    }
    .popover{
        min-width: 200px;
    }
</style>

<div class="sidebar">        
    <div id="myDiagramDiv"></div>

    {plugin=tree+vidget+id=tree_elly_main}

</div>
<div class="content" style="padding-top: 10px;">
    <div id="mytimeline"></div>
</div>

<div class="footer">
    <div class="footer_h"><i class="glyphicon glyphicon-chevron-up"></i> История файлов / Сообщения</div>
    <div class="container-fluid">
        <p class="text-muted">Footer...</p>
    </div>
</div>

<script>
    window.gant = {
        loading: false
    };


    // Called when the Visualization API is loaded.
    var Gant = {
        timeline: {},
        options: {
            width: "100%",
            height: "auto",
            minHeight: 400,
            axisOnTop: true,
            style: "box", // optional
            box: {
                align: 'left'
            },
            snapEvents: true,
            cluster: false,
            locale: 'ru',
            layout: "box",
            editable: false,
            eventMargin: 5, // minimal margin between events
            groupsWidth: "250px",
            zoomMin: 1000 * 60 * 60, // one hour in milliseconds
            zoomMax: 1000 * 60 * 60 * 24 * 31 * 5, // about three months in milliseconds
            timechanged: function () {
                console.log(this);
                Gant.clearPopOvers();
            }
        },
        data: [],
        init: function () {
            this.timeline = new links.Timeline(document.getElementById('mytimeline'));
            this.timeline.setOptions(links.locales['ru']);
            this.timeline.setOptions(this.options);
            this.loadData();
        },
        loadData: function () {
            var users = $('#tree_elly_main.tree_elly li').map(function () {
                var self = $(this);
                var check = self.children('span').children('input').prop('checked');
                if (check)
                    return self.attr('code');
            }).get();
            elly.ajax('{url=graph+data}', {
                users: users
            }, function (data) {
                window.gant.loading = false;
                console.log(data);
//                console.log(html);
                Gant.clearData();
                $.each(data, function (index, item) {
                    Gant.addData(item);
                });
                setTimeout(function () {
                    Gant.timeline.draw(Gant.data);
                    $('.timeline-title').each(function () {
                        var self = $(this);
                        if (self.css('width') < 10) {
                        }
                    });
                    Gant.timeline.setVisibleChartRangeNow();
                }, 10);
            });
        },
        addData: function (item) {
            item.start = new Date(item.start);
            item.end = new Date(item.end);
            item.className = 'timeline-title';
            this.data.push(item);
        },
        clearData: function () {
            this.data = [];
            this.clearPopOvers();
        },
        clearPopOvers: function () {
            $('.timeline-event-content, .timeline-event').popover('hide');
        }
    };

    $(document)
        .ready(function () {
            Gant.init();
            links.events.addListener(Gant.timeline, 'rangechanged', function () {
                Gant.clearPopOvers();
            });
            tree_elly('#tree_elly_main', {
                change: function (e) {
                    if (window.gant && window.gant.loading === true) {
                        return;
                    }
                    window.gant.loading = true;
                    setTimeout(function () {
                        Gant.loadData();
                    }, 10);
                }
            });
            $('body').tooltip({
                selector: '.timeline-event-content',
                title: function () {
                    return $(this).find('span.title').data('original-title')//.attr('title')
                },
                placement: 'bottom'
            }).popover({
                selector: '.timeline-event-content, .timeline-event',
                placement: 'bottom',
                content: function () {
                    return $(this).find('span.title').attr('body');
                },
                title: function () {
                    return $(this).find('span.title').data('original-title')//.attr('title');
                },
                delay: {
                    show: 100,
                    hide: 500
                },
                viewport: 'body',
                trigger: 'click',
                html: true

            });
        });
    function dt(moment_dt) {
        return moment_dt ? moment_dt.format('YYYY-MM-DD HH:mm') : '';
    }
</script>