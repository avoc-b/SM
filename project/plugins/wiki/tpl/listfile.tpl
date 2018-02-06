            
            <form method="post" onsubmit="deleteFile(this); return false">
                <select name="file" size="15" style="width: 100%">
                    {list}
                </select>
                <input type="submit" value="Удалить выбраную" style="width: 100%" />
            </form>
            
            <script>
                function deleteFile(obj)
                {
                    $(obj).find('[name="file"]').val();                    
                }
            </script>