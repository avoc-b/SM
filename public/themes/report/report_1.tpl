
		<table class="table table-striped_">
			<thead>
                <tr>
                    <th>#</th>
                    <th colspan="2">Сотрудник</th>
                    <th>Всего</th>
                    <th>Прогресс</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

                [for=report]
                <tr data-code="{report.code_struct}">
                    <td>{report._NUMBER}</td>
                    <td width="60"><img src="{report.avatar}" /></td>
                    <td>
                        <div>{report.fio}</div>
                        <div class="text-muted">{report.struct}</div>
                    </td>
                    <td class="text-right">{report.price} ({report.count})</td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar"
                            aria-valuenow="{report.proc_3}" aria-valuemin="0" aria-valuemax="100" style="width:{report.proc_3}%">
                                {report.proc_3}% Проверено
                            </div>
                            <div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" style="width:{report.proc_2}%">
                                {report.proc_2}% Выполнено
                            </div>
                        </div>
                        Баллов (к-во):
                        <span class="label label-success">{report.price_3} <kbd>{report.count_3}</kbd></span> (проверенно) \
                        <span class="label label-warning">{report.price_2} <kbd>{report.count_2}</kbd></span> (выполнено) \
                        <span class="label label-default">{report.price} <kbd>{report.count}</kbd></span> (всего)
                    </td>
                    <td>
                        <button class="btn btn-default btn-sm b_edit" data-toggle="collapse" data-target="#detal_{report._NUMBER}"><i class="glyphicon glyphicon-th-list"></i> Детально</button>
                    </td>
                </tr>
                <tr id="detal_{report._NUMBER}" class="active collapse">
                    <td></td>
                    <td colspan="5">
                        <table class="table table-bordered">
                            <tbody></tbody>
                        </table>
                    </td>
                </tr>
                [/for]

            </tbody>
		</table>

<script>

    elly.tpl = {
        detal: '<tr class="{{ getStatus status }}">\
                    <td>{{ dateSQL date_start }}</td>\
                    <td class="text-center"><i class="icon-circle icon-{{ getStatus status }}"></i></td>\
                    <td>{{ title }}</td>\
                    <td>{{ price }}</td>\
                </tr>\
        ',
    };
    elly.tplRegist('dateSQL', function(val){
        return elly.date(val, 'd.m.Y H:i');
    });
    elly.tplRegist('getStatus', function(val){
        var tbClass = ['', '', 'warning', 'success'];
        return tbClass[val];
    });


    $(".collapse").bind("show.bs.collapse", function(e)
    {
        var tbody = $(this).find('tbody');
        var code  = $(document.activeElement).parents('tr').data('code');
        var pnl   = $('#collapse1');
        tbody.html('');

        elly.ajax('{url=report+detal}', {
                code: code,
                date_11: pnl.find('[name="date_11"]').val(),
                date_22: pnl.find('[name="date_22"]').val(),
            }, function(data){
                tbody.html(
                    elly.tplParce('detal', data)
                );
            });
    });

</script>
