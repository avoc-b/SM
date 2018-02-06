<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />    
  <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.min.css" />
  <link rel="stylesheet" href="/public/css/style.css" />
    
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script> 
  <script type="text/javascript" src="/public/bootstrap/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="/system/js/elly.js"></script>
  
  {header}
  
  <link href="/public/favicon.png?h" type="image/x-icon" rel="shortcut icon"/>
  
  <style>
  </style>
</head>
<body class="body_login">    
            
    <div id="main_frame" class="main_login">
    <form id="form_login" onsubmit="return elly.ajax('{url=login+auth}','#form_login')">
    
        <h3>Авторизация пользователя</h3>
        
        <div class="form-group has-feedback">
            <input type="text" name="login" class="form-control" placeholder="Логин" />
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <input type="password" name="pass" class="form-control" placeholder="Пароль" />
            <span class="glyphicon glyphicon-asterisk form-control-feedback"></span>
        </div>
        <input type="submit" value="Войти" class="btn btn-default" />
        
    </form>
    </div>

</body>
</html>