<div class="sidebar well" style="overflow: auto;">

    <button class="btn btn-default btn-block b_add">
        <i class="glyphicon glyphicon-plus"></i> Добавить новую группу
    </button>

</div>

<div class="content" style="padding-top: 10px;">

    <!--
    <button class="btn btn-default pull-right">
        <i class="glyphicon glyphicon-plus"></i> Добавить новую группу
    </button>
    -->
    <h4>Управление группами</h4>


    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Наименование</th>
                <th>Бюджет</th>
                <th>Прогресс</th>
                <th>Инструменты</th>
            </tr>
        </thead>
        <tbody>

            [for=group]
            <tr data-code="{group.codeid}">
                <td>{group.codeid}</td>
                <td>{group.name}</td>
                <td class="text-right" style="padding-right: 20px;">
                    <div class="text-primary">{group.price}</div>
                    <div class="text-danger">- {group.price_all}</div>
                    <hr style="margin: 0" />
                    <div class="">{group.ostatok}</div>
                </td>
                <td>
                    <div class="progress">
                        <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar"
                        aria-valuenow="{group.proc_3}" aria-valuemin="0" aria-valuemax="100" style="width:{group.proc_3}%">
                            {group.proc_3}% Проверено
                        </div>
                        <div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" style="width:{group.proc_2}%">
                            {group.proc_2}% Выполнено
                        </div>
                    </div>
                    Баллов (к-во):
                    <span class="label label-success">{group.price_3} <kbd>{group.count_3}</kbd></span> (проверенно) \
                    <span class="label label-warning">{group.price_2} <kbd>{group.count_2}</kbd></span> (выполнено) \
                    <span class="label label-default">{group.price_all} <kbd>{group.count}</kbd></span> (всего)
                </td>
                <td>
                    <button class="btn btn-default btn-xs b_edit"><i class="glyphicon glyphicon-pencil"></i> Изменить</button>
                    <button class="btn btn-default btn-xs b_del"><i class="glyphicon glyphicon-remove"></i></button>
                </td>
            </tr>
            [/for]

        </tbody>
    </table>

</div>



<!-- шаблон модального окна -->
<div class="modal fade" id="group_modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <small class="label label-default"></small>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">

                <form class="form-horizontal">
                    <input type="hidden" name="codeid" />
                    <div class="form-group_">
                        <label class="control-label" for="title">Наименование:</label>
                        <input type="text" class="form-control input-sm" name="name" placeholder="" />
                    </div>
                    <div class="form-group_">
                        <label class="control-label" for="title">Бюджет:</label>
                        <input type="text" class="form-control input-sm integer" name="price" placeholder="" />
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary b_save">Сохранить</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(function(){

        $(document)
            .on('click', '.b_edit', function(){
                var code = $(this).parents('tr').data('code');
                elly.ajax('{url=settings+group_form}', {code: code}, function(data){
                    elly.modalForm('#group_modal', data).find('.modal-title').html('Редактирование записи');
                });
            })
            .on('click', '.b_del', function(){
                var tr = $(this).parents('tr');
                elly.confirm('Удалить группу?', function(ok){
                    if(!ok) return;
                    elly.ajax('{url=settings+group_del}', {code: tr.data('code')}, function(){
                        tr.remove();
                    });
                });
            })
            .on('click', '.b_add', function(){
                elly.modalForm('#group_modal', {}).find('.modal-title').html('Добавление записи');
            })
            .on('click', '.b_save', function(){
                elly.ajax('{url=settings+group_save}', '#group_modal', function(json){
                    if(json == '-1') {
                        elly.msg('Произошла ошибка!');
                        return;
                    }
                    elly.close();
                    location.reload();
                });
            });

    });
</script>