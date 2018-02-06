            
            <form method="post" onsubmit="deleteFile(this); return false">
                <select name="file" size="10" style="width: 100%">
                    {list}
                </select>
                <input type="submit" value="Удалить выбраную" style="width: 100%" />
            </form>
            
            <script>
                function deleteFile(obj)
                {
                    obj = $(obj).find('[name="file"]');
                    var file  = obj.val();
                    var error = ['', 'Нельзя удалить файл настроек!', 'Можно удалить только пустую страницу']; 
                    
                    elly.ajax('{url=wiki+delete}', {file: file}, function(json)
                    {                        
                        if(json == '0')
                        {
                            obj.find(':selected').remove();
                            
                            if(location.search == '?'+file)
                            {
                                //мертвая страница
                                var link = obj.find('option').not('.wiki_list').first().val();
                                location.href = elly.home +'wiki?'+ link;
                            }
                        }
                        else alert(error[json]);
                    });
                }
            </script>