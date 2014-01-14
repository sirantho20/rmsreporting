<?php
  $action = filter_input(INPUT_GET, 'action');
  
  if(isset($action) && $action == 'logout')
  {
      session_start();
      session_destroy();
  }
  
  $username = filter_input(INPUT_POST, 'username');
  $password = filter_input(INPUT_POST, 'password');
  
  if(isset($username) && isset( $password ))
  {
      include 'lib/adAuth.php';
      $obj = new adAuth();
      if( $obj->authenticate($username, $password))
      {
          session_start();
          $_SESSION['user'] = $username;
          header('Location: index.php');
      }
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <title>HTG Power Reporting</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>
  <body style="background-color: lightgray">
      <div class="row col-lg-3 col-lg-offset-4" style="margin-top: 100px;">

        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><span class="glyphicon glyphicon-user"></span> Please Login</h3>
            </div>
          <div class="panel-body">
            
              <form method="POST" role="form">
                 
                  
                    <div class="form-group">
                      
                      <input type="datetime" class="form-control input-sm" required id="username" name="username" placeholder="User name">
                      <small class="help-block">Use your HTG login details</small>
                    </div>
                    
                    <div class="form-group">
                      
                        <input type="password" class="form-control input-sm" required id="password" name="password" placeholder="Password">
                      
                    </div>

                  <button type="submit" class="btn btn-primary btn-block">
                      <span class="glyphicon glyphicon-download"></span> Sign In</button>
                  
            </form>
              
          </div>
        </div>
  </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script>
    $( document ).ready(function() {
         $("#username").focus();
    
    });
    </script>
  </body>
</html>
