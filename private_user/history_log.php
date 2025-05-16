<!DOCTYPE html>
<html lang="en">
<?php
session_start();
if(!isset($_SESSION["email_address"])){
    header("location:../login.html");
} 
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>BitKeep Management - User Logs</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css">
  <!-- Bootstrap core CSS -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <!-- Material Design Bootstrap -->
  <link href="css/mdb.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Your custom styles (optional) -->
  <link href="css/style.css" rel="stylesheet">

  <script src="js/jquery-1.8.3.min.js"></script>
  <link rel="stylesheet" type="text/css" href="media/css/dataTable.css" />
  <script src="media/js/jquery.dataTables.js" type="text/javascript"></script>
  
  <script type="text/javascript" charset="utf-8">
    $(document).ready(function(){
      $('#dtable').dataTable({
        "aLengthMenu": [[5, 10, 15, 25, 50, 100 , -1], [5, 10, 15, 25, 50, 100, "All"]],
        "iDisplayLength": 10
      });
    })
  </script>
  
  <style type="text/css">
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }
    
    .navbar {
      box-shadow: 0 4px 12px 0 rgba(0,0,0,.05);
    }
    
    .navbar-brand {
      font-weight: 600;
      letter-spacing: 0.5px;
    }
    
    .card {
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      border: none;
      margin-bottom: 30px;
    }
    
    .card-header {
      border-radius: 10px 10px 0 0 !important;
      padding: 15px 20px;
      background: linear-gradient(45deg, #4a6bff, #2196F3);
      color: white;
    }
    
    .btn-info {
      background: linear-gradient(45deg, #4a6bff, #2196F3) !important;
      box-shadow: 0 4px 7px rgba(33, 150, 243, 0.28);
      transition: all 0.3s ease;
    }
    
    .btn-info:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 14px rgba(33, 150, 243, 0.3);
    }
    
    select[multiple], select[size] {
      height: auto;
      width: 20px;
    }
    
    .pull-right {
      float: right;
      margin: 2px !important;
    }
    
    #loader {
      position: fixed;
      left: 0px;
      top: 0px;
      width: 100%;
      height: 100%;
      z-index: 9999;
      background: url('img/lg.flip-book-loader.gif') 50% 50% no-repeat rgb(249,249,249);
      opacity: 1;
    }
    
    .table {
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
      border-collapse: collapse;
    }
    
    .table thead th {
      background-color: #4a6bff;
      color: white;
      font-weight: 500;
      border: none;
      padding: 12px 15px;
      font-size: 14px;
    }
    
    .table tbody tr {
      border-bottom: 1px solid #f2f2f2;
      transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
      background-color: #f8f9ff;
    }
    
    .table tbody td {
      padding: 12px 15px;
      font-size: 14px;
      color: #555;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
      background-color: rgba(0, 0, 0, 0.02);
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
      background: linear-gradient(45deg, #4a6bff, #2196F3) !important;
      color: white !important;
      border: none;
      border-radius: 4px;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      background: #f0f4ff !important;
      color: #4a6bff !important;
      border: 1px solid #4a6bff;
    }
    
    .dataTables_wrapper .dataTables_filter input {
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 5px 10px;
      margin-left: 10px;
    }
    
    .dataTables_wrapper .dataTables_length select {
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 5px;
      margin: 0 5px;
    }
    
    .page-title {
      font-weight: 600;
      color: #333;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }
    
    .page-title i {
      margin-right: 10px;
      color: #4a6bff;
    }
    
    .action-buttons {
      margin-bottom: 20px;
    }
    
    .footer-copyright {
      background-color: #f8f9fa;
      color: #666;
      text-align: center;
      padding: 15px 0;
      margin-top: 30px;
      border-top: 1px solid #eee;
    }
  </style>

  <script src="jquery.min.js"></script>
  <script type="text/javascript">
    $(window).on('load', function(){
      setTimeout(function(){
        $('#loader').fadeOut('slow');  
      });
    });
    
    // Disable right-click
    document.addEventListener('contextmenu', function (e) {
      e.preventDefault();
      showNotification("Right-click is disabled.");
    });
    
    // Disable common keyboard shortcuts
    document.addEventListener('keydown', function (e) {
      if (e.keyCode == 123 || // F12
          (e.ctrlKey && e.shiftKey && e.keyCode == 73) || // Ctrl+Shift+I
          (e.ctrlKey && e.shiftKey && e.keyCode == 74) || // Ctrl+Shift+J
          (e.ctrlKey && e.keyCode == 83) || // Ctrl+S
          (e.ctrlKey && e.keyCode == 85) || // Ctrl+U
          (e.keyCode == 44)) { // Print Screen
        e.preventDefault();
        showNotification("This action is disabled.");
      }
    });
    
    // Disable Print Screen functionality
    document.addEventListener('keyup', function (e) {
      if (e.keyCode == 44) { // Print Screen
        navigator.clipboard.writeText('');
        showNotification("Print Screen is disabled.");
      }
    });
    
    // Function to show notifications
    function showNotification(message) {
      // Save the original content
      var originalContent = document.body.innerHTML;
    
      // Replace the content with a notification message
      document.body.innerHTML = `
        <div style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: white; font-size: 24px;">
          ${message}
        </div>
      `;
    
      // Restore the original content after 3 seconds
      setTimeout(function () {
        document.body.innerHTML = originalContent;
      }, 3000);
    }
  </script>
</head>

<body>
  <?php 
    require_once("include/connection.php");
    $id = mysqli_real_escape_string($conn,$_SESSION['email_address']);
    $r = mysqli_query($conn,"SELECT * FROM login_user where id = '$id'") or die (mysqli_error($con));
    $row = mysqli_fetch_array($r);
    $id=$row['email_address'];
  ?>
  
  <div id="loader"></div>
  
  <!-- Navbar -->
  <nav class="mb-1 navbar navbar-expand-lg navbar-dark default-color fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">
        <img src="js/img/Files_Download.png" width="33px" height="33px" class="mr-2"> 
        <font color="#F0B56F">Bit</font>Keep <font color="#F0B56F">M</font>anagement <font color="#F0B56F">S</font>ystem
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent-4"
        aria-controls="navbarSupportedContent-4" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink-4" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
              <font color="black">Welcome!,</font> <?php echo ucwords(htmlentities($id)); ?> 
              <i class="fas fa-user-circle ml-1"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right dropdown-info" aria-labelledby="navbarDropdownMenuLink-4">
              <a class="dropdown-item" href="home.php"><i class="fas fa-chevron-circle-left mr-2"></i>Home</a>
              <a class="dropdown-item" href="add_file.php"><i class="fas fa-file-medical mr-2"></i>Add file</a>
              <a class="dropdown-item" href="Logout.php"><i class="fas fa-sign-in-alt mr-2"></i>LogOut</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- /.Navbar -->
  
  <!-- Main content -->
  <main class="pt-5 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <!-- Page header -->
          <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
              <h4 class="page-title mb-0">
                <i class="fas fa-history"></i> User Login History
              </h4>
              <div class="action-buttons">
                <a href="home.php" class="btn btn-info">
                  <i class="fas fa-chevron-circle-left mr-1"></i> Back to Home
                </a>
              </div>
            </div>
          </div>
          
          <!-- Data table card -->
          <div class="card">
            <div class="card-body">
              <div class="table-responsive">
                <table id="dtable" class="table table-striped">
                  <thead>
                    <tr>
                      <th><i class="fas fa-user mr-1"></i> USER</th>    
                      <th><i class="fas fa-network-wired mr-1"></i> IP ADDRESS</th>
                      <th><i class="fas fa-server mr-1"></i> HOST</th>
                      <th><i class="fas fa-sign-in-alt mr-1"></i> ACTION</th> 
                      <th><i class="fas fa-clock mr-1"></i> LOGIN TIME</th>
                      <th><i class="fas fa-sign-out-alt mr-1"></i> ACTION</th> 
                      <th><i class="fas fa-clock mr-1"></i> LOGOUT TIME</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                      require_once("include/connection.php");
                      $query = mysqli_query($conn,"SELECT * from history_log") or die (mysqli_error($conn));
                      while($file=mysqli_fetch_array($query)){
                        $name = $file['email_address'];
                        $ip = $file['ip'];
                        $host = $file['host'];
                        $action = $file['action'];
                        $logintime = $file['login_time'];
                        $actions = $file['actions'];
                        $logouttime = $file['logout_time'];
                    ?>
                    <tr>
                      <td><?php echo $name; ?></td>
                      <td><?php echo $ip; ?></td>
                      <td><?php echo $host; ?></td>
                      <td><?php echo $action; ?></td>
                      <td><?php echo $logintime; ?></td>
                      <td><?php echo $actions; ?></td>
                      <td><?php echo $logouttime; ?></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  
  <!-- Footer -->
  <footer class="page-footer font-small blue mt-5">
    <div class="footer-copyright py-3">
      <p class="text-center mb-0">All rights Reserved &copy; <?php echo date('Y');?> Created By: BitKeep Management</p>
    </div>
  </footer>
  
  <!-- SCRIPTS -->
  <script type="text/javascript" src="js/jquery-3.4.0.min.js"></script>
  <script type="text/javascript" src="js/popper.min.js"></script>
  <script type="text/javascript" src="js/bootstrap.min.js"></script>
  <script type="text/javascript" src="js/mdb.min.js"></script>
  
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css"/>   
  <script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css">
  <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/responsive/1.0.3/js/dataTables.responsive.js"></script>
</body>
</html>