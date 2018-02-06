<style>
    .tree_elly { padding: 0 10px; list-style: none; }
    .tree_elly ul { 
        list-style: none; 
        padding-left: 15px;
        margin: 0;
    }
    .tree_elly li {
        background-image: url("data:image/gif;base64,R0lGODdhAQABAIABAAAAAP///ywAAAAAAQABAAACAkQBADs=");
        background-repeat: repeat-y;
        background-position: left top;
        clear: both;
        padding-top: 3px;
    }
    .tree_elly li:last-child {
        background-repeat: no-repeat;
        background-size: 1px 11px;
    }
    .tree_elly span {
        background: url("data:image/gif;base64,R0lGODdhAQABAIABAAAAAP///ywAAAAAAQABAAACAkQBADs=") no-repeat left 8px / 10px 1px;
        padding-left: 15px;
        cursor: pointer;
        display: block;
        position: relative;
        font-size: 12px;
        height: 28px;
    }
    .tree_elly span .glyphicon {
        background-color: #fef0c0;
        border: 1px solid #333;
        color: #333;
        font-size: 5px;
        left: -4px;
        margin-top: -6px;
        padding: 1px;
        position: absolute;
        top: 10px;
    }
    /*.tree_elly input {
        margin: 0 4px 0 0;
        vertical-align: middle;
    }*/

    .tree_elly input {
        left: 10px;
        margin: 0 4px 0 0;
        position: absolute;
        top: 3px;
        vertical-align: middle;
    }
    .tree_elly i.tree_img {
        float: left;
        height: 28px;
        padding-left: 10px;
        padding-right: 4px;
    }
    .tree_group > i.tree_img {
        background: url("data:image/gif;base64,R0lGODdhAQABAIABAAAAAP///ywAAAAAAQABAAACAkQBADs=") no-repeat scroll left 8px / 1px 100%;
    }
    .tree_elly img {
        height: 28px;
        max-width: 28px;
        border-radius: 20px;
    }
    .tree_elly b {
        display: block;
        width: 100%;
        white-space: nowrap;    
        font-weight: normal;
    }
    .tree_elly u {
        color: #999;
        display: block;
        font-size: 10px;    
        line-height: 1;
        text-decoration: none;
        white-space: nowrap;
        /*overflow-x: hidden;
        height: 1em;    
        text-overflow: ellipsis;*/
    }
    .tree_clr {
        background-color: #fff;
        border-left: 1px solid #fff;
        bottom: 0;
        height: 100%;
        position: absolute;
        right: -10px;
        width: 4px;
    }
</style>

<ul class="tree_elly" id="{val=id}">

    {tree}

</ul>

<script>
    var tree_elly = function (id, callback) 
    {
        if (!id) {
            id = '';
        }
        $(id + '.tree_elly')
            .off('click', 'span i')
            .off('change', 'input')
            .off('click', 'b, u, img')
            .on('click', 'span i', function () {
                $(this)
                    .toggleClass('glyphicon-plus glyphicon-minus')
                    .parents('span').toggleClass('tree_group')
                    .next('ul').slideToggle(0);
                if (!!callback && !!callback.click) {
                    callback.click(this);
                }
            })
            .on('change', 'input', function (e) {
                var obj = $(this);
                obj.parent().next('ul').find('input').prop('checked', obj.prop('checked')).change();
                if (!!callback && !!callback.change) {
                    callback.change(this);
                }
            })
            .on('click', 'b, u, img', function (e) {
//                e.preventDefault();
//                e.stopPropagation();
                
//                var code = $(this).parent().parent('li').attr('code');
                var obj = $(this).parent().find('input');
                obj.prop('checked', !obj.prop('checked'));
                if (!!callback && !!callback.change) {
                    callback.change(this);
                }
            });
    };
    tree_elly();
</script>