
            <form method="post" onsubmit="createFile(this); return false">
                <p>Заголовок:<input type="text" name="title" class="wiki_i_create" /></p>
                <p>Ссылка:<input type="text" name="file" class="wiki_i_create" /></p>
                <input type="submit" value="Создать" class="wiki_b" />
            </form>

            <script>
                function createFile(obj)
                {
                    var file = $('.modal_body [name="file"]').val();
                    elly.ajax('{url=wiki+create}', $(obj), function(){
                        modal.close();
                        location.href = elly.home +'wiki?'+ file;
                    });                    
                }
            </script>