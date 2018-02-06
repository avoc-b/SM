<link rel="stylesheet" href="public/bootstrap/css/bootstrap-datetimepicker.css" />
<script type="text/javascript" src="public/js/moment-with-locales.min.js"></script>
<script type="text/javascript" src="public/bootstrap/js/bootstrap-datetimepicker.min.js"></script>


<div class="sidebar" style="overflow: auto; background-color: inherit; margin-top: 4px;">
    <div class="panel-group tree" id="accordion">


      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse1">Отчёт по выполненным и поставленным задачам</a>
          </h4>
        </div>
        <div id="collapse1" class="panel-collapse collapse">
          <form class="panel-body" code="1">

            <input type="hidden" name="form" value="1" />

            <small>Период от ... до</small>
            <div class="form-group">
                <input type="text" class="col-sm-6 datetime form-control" name="date_11" value="{date_1}" />
                <input type="text" class="col-sm-6 datetime form-control" name="date_22" value="{date_2}" />
                <div style="clear: both;"></div>
            </div>

            <input type="submit" class="btn btn-primary btn-sm btn-block" value="Построить отчёт" />
            <input type="button" class="btn btn-default btn-sm btn-block" value="Экспорт в Excel" />

          </form>
        </div>
      </div>

    </div>
</div>
    
        

<div class="content list">

</div>

<!--
<link href="/public/css/vendor/jquery.jqplot.min.css" rel="stylesheet" />
<script type="text/javascript" src="/public/js/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="/public/js/plugins/jqplot.barRenderer.js"></script>
<script type="text/javascript" src="/public/js/plugins/jqplot.pieRenderer.js"></script>
<script type="text/javascript" src="/public/js/plugins/jqplot.categoryAxisRenderer.js"></script>
<script type="text/javascript" src="/public/js/plugins/jqplot.pointLabels.js"></script>
-->

<script>
    $(function(){

        $('#accordion .datetime').datetimepicker({
                                                pickTime: false,
                                                useMinutes: false,
                                                language: 'ru',
                                            });

        $(document)
        .on('submit', '#accordion form', function(e)
        {
            e.preventDefault();
            $('.panel-title span').remove();
            $(this).parents('.panel').find('.panel-title').append('<span class="glyphicon glyphicon-transfer right"></span>');
            elly.ajaxHTML('{url=report+show}', $(this), '.list', function(data)
            {
                //console.log(data);
            });
        })
        .on('click', '#accordion [type="button"]', function(e)
        {
            var form = $(this).parents('form');
            //console.log(form.serialize());
            window.open('report/excel/?' + form.serialize(), '_blank');
        });

        /*
        function trActive(obj)
        {
            var tr = $(obj).parents('tr');
            tr.addClass('info').siblings().removeClass('info');
            return {obj: tr, id: tr.children('td:first').text()};
        }*/
    });
</script>