<?php 

session_start();
if(!isset($_SESSION['user']))
{
    header('Location:login.php');
}

    include 'lib/reportCore.php'; 
    $obj = new reportCore(); 
    

?>
<!DOCTYPE html>
<html>
  <head>
    <title>HTG Power Reporting</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/daterangepicker-bs3.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <div class="row col-lg-6 col-lg-offset-3">
      <div class="row">  
          <div class="col-lg-4">
              <img src="images/htg-logo.png" style="margin-top: 20px;" />
          </div>
          <div class="col-lg-8">
            <div class="page-header">
                <h3>Helios Towers Ghana <br /><small>Tenant Power Reporting</small></h3>
              </div>
          </div>
      </div>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><span class="glyphicon glyphicon-stats"></span> Complete Report Fields Below<?php if(isset($_SESSION['user'])): ?><span class="pull-right"><a href="login.php?action=logout" style="color: white;"><span class="glyphicon glyphicon-off"></span> exit</a><?php endif; ?>
</span></h3>
                
            </div>
          <div class="panel-body">
            
              <form method="POST" action="entry.php" role="form">
                  <div class="row">
                  <div class="col-lg-12">
                    <div class="form-group">
                      <label for="date-from">Report Date</label>
                      <input type="datetime" class="form-control input-sm" required id="report-date" name="report-date" placeholder="report start date">
                      
                    </div>
                  </div>
                  
                  </div>
                  <div class="form-group">
                        <label for="tenant">Tenant</label>
                        <select id="tenant" name="tenant" required class="form-control input-sm">
                           <option value="" selected="seleted">--select one--</option> 
                           <option value="Tigo">Tigo</option> 
                            <option value="Vodafone">Vodafone</option>
                            <option value="Airtel">Airtel</option>
                            <option value="MTN">MTN</option>
                    </select>
                  </div>
                  <button type="submit" class="btn btn-primary">
                      <span class="glyphicon glyphicon-download"></span> Download Report</button>
                  <button type="reset" class="btn btn-warning">
                      <span class="glyphicon glyphicon-refresh"></span> Clear</button>
                  
            </form>
              
          </div>
        </div>
  </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/moment.min.js"></script>
    <script src="js/daterangepicker.js"></script>
    <script>
    $( document ).ready(function() {
         $('#report-date').daterangepicker(
                {
                    ranges: {
                       'Today': [moment(), moment()],
                       'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                       'Last 7 Days': [moment().subtract('days', 6), moment()],
                       'Last 30 Days': [moment().subtract('days', 29), moment()],
                       'This Month': [moment().startOf('month'), moment().endOf('month')],
                       'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                    },
                    startDate: moment().subtract('days', 29),
                    endDate: moment(),
                    applyClass: 'btn-primary glyphicon glyphicon-check',
                    separator: ' to ',
                    format: 'YYYY-MM-DD',
                    cancelClass: 'btn-warning glyphicon glyphicon-ban-circle',
                    showDropdowns: true,
                    showWeekNumbers: true,
                    minDate: moment().subtract('years', 2),
                    maxDate: moment(),
              }
                );
    
    });
    </script>
  </body>
</html>
