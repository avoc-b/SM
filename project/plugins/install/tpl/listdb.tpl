            
            <form method="post" onsubmit="selectDB(this); return false">
                <select name="file" size="15" style="width: 100%">
                    {list}
                </select>
                <input type="submit" value="Распаковать на сервере" style="width: 100%" />
            </form>
            
            <script>
                function selectDB(obj)
                {
                    $('#file').val( $(obj).find('[name="file"]').val() );
                    go(2);
                }
            </script>