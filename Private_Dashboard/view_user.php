<!DOCTYPE html>
<html lang="en">
<?php
// Inialize session
session_start();
error_reporting(0);
require_once("include/connection.php");
$id = mysqli_real_escape_string($conn,$_GET['id']);

// Check, if username session is NOT set then this page will jump to login page
if (!isset($_SESSION['admin_user'])) {
header('Location: index.html');
}
else{
    $uname=$_SESSION['admin_user'];
}
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>BitKeep Management</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap core CSS -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <!-- Material Design Bootstrap -->
  <link href="css/mdb.min.css" rel="stylesheet">
  <!-- Your custom styles (optional) -->
  <link href="css/style.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">

  <script src="js/jquery-1.8.3.min.js"></script>
  <link rel="stylesheet" type="text/css" href="medias/css/dataTable.css" />
  <script src="medias/js/jquery.dataTables.js" type="text/javascript"></script>
  
  <script type="text/javascript" charset="utf-8">
    $(document).ready(function(){
      $('#dtable').dataTable({
        "aLengthMenu": [[5, 10, 15, 25, 50, 100 , -1], [5, 10, 15, 25, 50, 100, "All"]],
        "iDisplayLength": 10
      });
    })
  </script>

  <style>
    :root {
      --primary-color: #4a6bff;
      --secondary-color: #F0B56F;
      --dark-color: #2d3748;
      --light-color: #f8f9fa;
      --success-color: #38a169;
      --warning-color: #e9b949;
      --danger-color: #e53e3e;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f7fafc;
    }
    
    .navbar {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      background: linear-gradient(90deg, #ffffff 0%, #f8f9ff 100%) !important;
      height: 70px;
    }
    
    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
    }
    
    .sidebar-fixed {
      height: 100vh;
      width: 280px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      z-index: 1040;
      background: linear-gradient(180deg, #ffffff 0%, #f8f9ff 100%);
      padding-top: 0;
      overflow-y: auto;
    }
    
    .sidebar-fixed .logo-wrapper {
      padding: 2rem 1.5rem;
      text-align: center;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      margin-bottom: 1rem;
    }
    
    .sidebar-fixed .logo-wrapper img {
      width: 200px;
      height: auto;
      max-width: 100%;
      transition: transform 0.3s ease;
    }
    
    .sidebar-fixed .logo-wrapper img:hover {
      transform: scale(1.05);
    }
    
    .sidebar-fixed .list-group-item {
      border: none;
      border-radius: 0;
      color: #4a5568;
      font-weight: 500;
      padding: 0.9rem 1.5rem;
      display: flex;
      align-items: center;
      transition: all 0.2s;
      border-left: 4px solid transparent;
      margin-bottom: 3px;
    }
    
    .sidebar-fixed .list-group-item i {
      margin-right: 12px;
      font-size: 1.1rem;
      width: 24px;
      text-align: center;
    }
    
    .sidebar-fixed .list-group-item:hover {
      background-color: rgba(74, 107, 255, 0.08);
      color: var(--primary-color);
      border-left: 4px solid var(--primary-color);
    }
    
    .sidebar-fixed .list-group-item.active {
      background-color: rgba(74, 107, 255, 0.12);
      color: var(--primary-color);
      border-left: 4px solid var(--primary-color);
      font-weight: 600;
    }
    
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
      border: 1px solid rgba(0, 0, 0, 0.03);
      overflow: hidden;
    }
    
    .card-header {
      background-color: #f8fafc;
      border-bottom: 1px solid #edf2f7;
      padding: 1.25rem 1.5rem;
    }
    
    .card-title {
      font-weight: 700;
      color: var(--dark-color);
      margin-bottom: 0;
    }
    
    .table {
      border-collapse: separate;
      border-spacing: 0;
    }
    
    .table thead th {
      background-color: #f8fafc;
      border-bottom: 2px solid #edf2f7;
      color: #4a5568;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.05em;
      padding: 14px 16px;
    }
    
    .table tbody tr {
      transition: all 0.2s ease-in-out;
    }
    
    .table tbody tr:hover {
      background-color: #f8fafc;
    }
    
    .table td {
      padding: 14px 16px;
      vertical-align: middle;
      border-bottom: 1px solid #edf2f7;
    }
    
    .btn-outline-primary {
      color: var(--primary-color);
      border-color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
      background-color: var(--primary-color);
      color: white;
    }
    
    .btn-outline-danger {
      color: var(--danger-color);
      border-color: var(--danger-color);
    }
    
    .btn-outline-danger:hover {
      background-color: var(--danger-color);
      color: white;
    }
    
    .btn-info {
      background: linear-gradient(135deg, var(--primary-color), #6c8cff);
      border: none;
      box-shadow: 0 4px 6px rgba(74, 107, 255, 0.2);
      transition: all 0.2s;
      padding: 0.6rem 1.2rem;
      font-weight: 500;
    }
    
    .btn-info:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 8px rgba(74, 107, 255, 0.25);
    }
    
    .modal-content {
      border-radius: 12px;
      border: none;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .modal-header {
      border-bottom: 1px solid #edf2f7;
      padding: 1.25rem 1.5rem;
    }
    
    .modal-footer {
      border-top: 1px solid #edf2f7;
      padding: 1.25rem 1.5rem;
    }
    
    .md-form label {
      color: #718096;
    }
    
    .md-form input[type=text], 
    .md-form input[type=email], 
    .md-form input[type=password] {
      border-bottom: 1px solid #cbd5e0;
    }
    
    .md-form input[type=text]:focus:not([readonly]), 
    .md-form input[type=email]:focus:not([readonly]), 
    .md-form input[type=password]:focus:not([readonly]) {
      border-bottom: 1px solid var(--primary-color);
      box-shadow: 0 1px 0 0 var(--primary-color);
    }
    
    .md-form input[type=text]:focus:not([readonly])+label, 
    .md-form input[type=email]:focus:not([readonly])+label, 
    .md-form input[type=password]:focus:not([readonly])+label {
      color: var(--primary-color);
    }
    
    .dataTables_wrapper .dataTables_filter input {
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 8px 12px;
      margin-left: 10px;
      transition: all 0.2s;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(74, 107, 255, 0.15);
      outline: none;
    }
    
    .dataTables_wrapper .dataTables_length select {
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 6px 12px;
    }
    
    .footer-copyright {
      background-color: #f8f9fa;
      color: #718096;
      text-align: center;
      padding: 15px 0;
      margin-top: 30px;
      border-top: 1px solid #edf2f7;
    }
    
    #loader {
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      z-index: 9999;
      background: rgba(249,249,249,0.97);
      opacity: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    .loader-content {
      text-align: center;
    }
    
    .spinner-border {
      width: 3rem;
      height: 3rem;
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
      color: var(--primary-color);
    }
    
    /* Header with gradient background */
    .header-gradient {
      background: linear-gradient(135deg, #4a6bff, #6c8cff);
      color: white;
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 12px rgba(74, 107, 255, 0.2);
    }
    
    .header-gradient h4 {
      margin: 0;
      font-weight: 600;
    }
    
    /* Improved table styling */
    .table-container {
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    
    /* User status badge */
    .user-status {
      display: inline-block;
      padding: 0.35em 0.65em;
      font-size: 0.75em;
      font-weight: 600;
      line-height: 1;
      text-align: center;
      white-space: nowrap;
      vertical-align: baseline;
      border-radius: 6px;
      background-color: #e9eeff;
      color: #3547a0;
    }
    
    /* Action buttons */
    .action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      transition: all 0.2s ease-in-out;
      margin: 0 3px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
    }
    
    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
    }
    
    .action-btn-edit {
      background-color: rgba(74, 107, 255, 0.1);
      color: var(--primary-color);
    }
    
    .action-btn-edit:hover {
      background-color: var(--primary-color);
      color: white;
    }
    
    .action-btn-delete {
      background-color: rgba(229, 62, 62, 0.1);
      color: var(--danger-color);
    }
    
    .action-btn-delete:hover {
      background-color: var(--danger-color);
      color: white;
    }
    
    /* Improved pagination */
    .dataTables_paginate .paginate_button {
      border-radius: 6px !important;
      margin: 0 2px;
    }
    
    .dataTables_paginate .paginate_button.current {
      background: var(--primary-color) !important;
      border-color: var(--primary-color) !important;
      color: white !important;
    }
    
    .dataTables_paginate .paginate_button:hover {
      background: rgba(74, 107, 255, 0.1) !important;
      border-color: rgba(74, 107, 255, 0.1) !important;
      color: var(--primary-color) !important;
    }
    
    @media (max-width: 1199.98px) {
      .sidebar-fixed {
        display: none;
      }
      .navbar-nav .nav-item {
        margin-right: 0 !important;
      }
    }
    
    @media (min-width: 1200px) {
      .navbar .container-fluid {
        padding-left: 280px;
      }
      main {
        margin-left: 280px;
      }
    }
  </style>

  <script src="jquery.min.js"></script>
  <script type="text/javascript">
    $(window).on('load', function(){
      setTimeout(function(){
        $('#loader').fadeOut('slow');  
      }, 500);
    });
    
    // Security scripts
    document.addEventListener('contextmenu', function (e) {
      e.preventDefault();
      showNotification("Right-click is disabled.");
    });

    document.addEventListener('keydown', function (e) {
      if (e.keyCode == 123 || 
          (e.ctrlKey && e.shiftKey && e.keyCode == 73) || 
          (e.ctrlKey && e.shiftKey && e.keyCode == 74) || 
          (e.ctrlKey && e.keyCode == 83) || 
          (e.ctrlKey && e.keyCode == 85) || 
          (e.keyCode == 44)) {
          e.preventDefault();
          showNotification("This action is disabled.");
      }
    });

    document.addEventListener('keyup', function (e) {
      if (e.keyCode == 44) {
        navigator.clipboard.writeText('');
        showNotification("Print Screen is disabled.");
      }
    });

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

<body class="grey lighten-3">
  <!-- Loading screen -->
  <div id="loader">
    <div class="loader-content">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <h5 class="mt-3 text-muted">Loading...</h5>
    </div>
  </div>

  <!--Main Navigation-->
  <header>
    <!-- Navbar -->
    <nav class="navbar fixed-top navbar-expand-lg navbar-light white scrolling-navbar">
      <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand waves-effect" href="dashboard.php">
          <strong class="blue-text">BitKeep Management</strong>
        </a>

        <!-- Collapse -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Links -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <!-- Left -->
          <ul class="navbar-nav mr-auto">
            <!-- Empty left side -->
          </ul>

          <?php 
            require_once("include/connection.php");
            $id = mysqli_real_escape_string($conn,$_SESSION['admin_user']);
            $r = mysqli_query($conn,"SELECT * FROM admin_login where id = '$id'") or die (mysqli_error($con));
            $row = mysqli_fetch_array($r);
            $id=$row['admin_user'];
          ?>

          <!-- Right -->
          <ul class="navbar-nav nav-flex-icons">
            <li class="nav-item mr-3 d-flex align-items-center">
              <span class="font-weight-bold">Welcome, <?php echo ucwords(htmlentities($id)); ?></span>
            </li>
            <li class="nav-item">
              <a href="logout.php" class="nav-link border border-light rounded waves-effect">
                <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- Navbar -->

    <!-- Sidebar -->
    <div class="sidebar-fixed position-fixed">
      <a class="logo-wrapper waves-effect">
        <img src="img/image1.svg" class="img-fluid" alt="BitKeep Logo">
      </a>

      <div class="list-group list-group-flush">
        <a href="dashboard.php" class="list-group-item waves-effect">
          <i class="fas fa-chart-pie mr-3"></i>Dashboard
        </a>
        <a href="#" class="list-group-item list-group-item-action waves-effect" data-toggle="modal" data-target="#modalRegisterForm">
          <i class="fas fa-user-plus mr-3"></i>Add Admin
        </a>
        <a href="view_admin.php" class="list-group-item list-group-item-action waves-effect">
          <i class="fas fa-users mr-3"></i>View Admin
        </a>
        <a href="#" class="list-group-item list-group-item-action waves-effect" data-toggle="modal" data-target="#modalRegisterForm2">
          <i class="fas fa-user-plus mr-3"></i>Add User
        </a>
        <a href="view_user.php" class="list-group-item list-group-item-action active waves-effect">
          <i class="fas fa-users mr-3"></i>View User
        </a>
        <a href="folder_management.php" class="list-group-item list-group-item-action waves-effect">
          <i class="fas fa-folder mr-3"></i>Folders
        </a>
        <a href="manage_requests.php" class="list-group-item list-group-item-action waves-effect">
          <i class="fas fa-key mr-3"></i>Requests
        </a>
        <a href="add_document.php" class="list-group-item list-group-item-action waves-effect">
          <i class="fas fa-file-medical mr-3"></i>Document
        </a>
        <a href="view_userfile.php" class="list-group-item list-group-item-action waves-effect">
          <i class="fas fa-folder-open mr-3"></i>View User File
        </a>
        <a href="admin_log.php" class="list-group-item list-group-item-action waves-effect">
          <i class="fas fa-history mr-3"></i>Admin Logged
        </a>
        <a href="user_log.php" class="list-group-item list-group-item-action waves-effect">
          <i class="fas fa-history mr-3"></i>User Logged
           <a href="file_log.php" class="list-group-item list-group-item-action waves-effect">
          <i class="fas fa-file-alt mr-3"></i>File Logged
          <a href="security_logs.php" class="list-group-item list-group-item-action waves-effect">
          <i class="fas fa-lock mr-3"></i>Security Logged
        </a>
      </div>
    </div>
    <!-- Sidebar -->
  </header>
  <!--Main Navigation-->

  <!--Add admin modal-->
  <div class="modal fade" id="modalRegisterForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="create_Admin.php" method="POST">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header text-center">
            <h4 class="modal-title w-100 font-weight-bold"><i class="fas fa-user-plus"></i> Add Admin</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body mx-3">
            <div class="md-form mb-5">
              <input type="hidden" id="orangeForm-name" name="status" value="Admin" class="form-control validate">
            </div>
            <div class="md-form mb-5">
              <i class="fas fa-user prefix grey-text"></i>
              <input type="text" id="orangeForm-name" name="name" class="form-control validate" required="">
              <label data-error="wrong" data-success="right" for="orangeForm-name">Your name</label>
            </div>
            <div class="md-form mb-5">
              <i class="fas fa-envelope prefix grey-text"></i>
              <input type="email" id="orangeForm-email" name="admin_user" class="form-control validate" required="">
              <label data-error="wrong" data-success="right" for="orangeForm-email">Your email</label>
            </div>
            <div class="md-form mb-4">
              <i class="fas fa-lock prefix grey-text"></i>
              <input type="password" id="orangeForm-pass" name="admin_password" class="form-control validate" required="">
              <label data-error="wrong" data-success="right" for="orangeForm-pass">Your password</label>
            </div>
          </div>
          <div class="modal-footer d-flex justify-content-center">
            <button class="btn btn-info" name="reg">Sign up</button>
          </div>
        </div>
      </div>
    </form>
  </div>
  <!--end modal admin-->

  <!--Add user modal-->
  <div class="modal fade" id="modalRegisterForm2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="create_user.php" method="POST">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header text-center">
            <h4 class="modal-title w-100 font-weight-bold"><i class="fas fa-user-plus"></i> Add User Employee</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body mx-3">
            <div class="md-form mb-5">
              <input type="hidden" id="orangeForm-name" name="status" value="Employee" class="form-control validate" required="">
            </div>
            <div class="md-form mb-5">
              <i class="fas fa-user prefix grey-text"></i>
              <input type="text" id="orangeForm-name" name="name" class="form-control validate">
              <label data-error="wrong" data-success="right" for="orangeForm-name">Your name</label>
            </div>
            <div class="md-form mb-5">
              <i class="fas fa-envelope prefix grey-text"></i>
              <input type="email" id="orangeForm-email" name="email_address" class="form-control validate" required="">
              <label data-error="wrong" data-success="right" for="orangeForm-email">Your email</label>
            </div>
            <div class="md-form mb-4">
              <i class="fas fa-lock prefix grey-text"></i>
              <input type="password" id="orangeForm-pass" name="user_password" class="form-control validate" required="">
              <label data-error="wrong" data-success="right" for="orangeForm-pass">Your password</label>
            </div>
          </div>
          <div class="modal-footer d-flex justify-content-center">
            <button class="btn btn-info" name="reguser">Sign up</button>
          </div>
        </div>
      </div>
    </form>
  </div>
  <!--end modal user-->

  <!--Main layout-->
  <main class="pt-5 mx-lg-5">
    <div class="container-fluid mt-5">
      <!-- Heading -->
      <div class="header-gradient d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
          <i class="fas fa-users mr-2"></i>User Management
        </h4>
        <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#modalRegisterForm2">
          <i class="fas fa-user-plus mr-2"></i>Add New User
        </button>
      </div>
      <!-- Heading -->
      
      <!-- Users Table -->
      <div class="table-container">
        <div class="table-responsive">
          <table id="dtable" class="table table-hover">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Department</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                require_once("include/connection.php");

                $query = "SELECT * FROM login_user";
                $result = mysqli_query($conn, $query);
                while ($rs = mysqli_fetch_array($result)) {
                  $id = $rs['id'];
                  $fname = $rs['name'];
                  $admin = $rs['email_address'];
                  $status = $rs['user_status'];
                  $department = $rs['department']; // Get department
              ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-circle mr-3" style="width: 40px; height: 40px; background-color: #4a6bff; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                      <?php echo substr($fname, 0, 1); ?>
                    </div>
                    <div>
                      <span class="font-weight-medium"><?php echo $fname; ?></span>
                    </div>
                  </div>
                </td>
                <td><?php echo $admin; ?></td>
                <td><span class="user-status"><?php echo $status; ?></span></td>
                <td><?php echo $department; ?></td>
                <td>
                  <div class="d-flex">
                    <a href="#" class="action-btn action-btn-edit" data-toggle="modal" data-target="#modalRegisterFormss" title="Edit User">
                      <i class="fas fa-user-edit"></i>
                    </a>
                    <a href="delete_user.php?id=<?php echo htmlentities($rs['id']); ?>" class="action-btn action-btn-delete" title="Delete User" onclick="return confirm('Are you sure you want to delete this user?');">
                      <i class="fas fa-trash-alt"></i>
                    </a>
                  </div>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!--Copyright-->
    <div class="footer-copyright py-3 text-center">
      <p class="mb-0">All rights Reserved &copy; <?php echo date('Y');?> Created By: BitKeep Management</p>
    </div>
    <!--/.Copyright-->
  </main>
  <!--/.Main layout-->

  <!-- Edit User Modal -->
  <div class="modal fade" id="modalRegisterFormss" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <?php 
      require_once("include/connection.php");

      $q = mysqli_query($conn,"select * from login_user where id = '$id'") or die (mysqli_error($conn));
      $rs1 = mysqli_fetch_array($q);

      $id1 = $rs1['id'];
      $fname1 = $rs1['name'];
      $admin1 = $rs1['email_address'];
      $pass1 = $rs1['user_password'];
      $status = $rs1['user_status'];
      $department = $rs1['department']; // Get department
    ?>

    <div class="modal-dialog" role="document">
      <form method="POST">
        <div class="modal-content">
          <div class="modal-header text-center">
            <h4 class="modal-title w-100 font-weight-bold"><i class="fas fa-user-edit"></i> Edit User</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body mx-3">
            <div class="md-form mb-5">
              <input type="hidden" class="form-control" name="id" value="<?php echo $id1; ?>"><br>
            </div>
            <div class="md-form mb-5">
              <i class="fas fa-user prefix grey-text"></i>
              <input type="text" id="orangeForm-name" name="name" value="<?php echo $fname1; ?>" class="form-control validate">
              <label data-error="wrong" data-success="right" for="orangeForm-name">Your name</label>
            </div>
            <div class="md-form mb-5">
              <i class="fas fa-envelope prefix grey-text"></i>
              <input type="email" id="orangeForm-email" name="email_address" value="<?php echo $admin1; ?>" class="form-control validate">
              <label data-error="wrong" data-success="right" for="orangeForm-email">Your email</label>
            </div>
            <div class="md-form mb-4">
              <i class="fas fa-building prefix grey-text"></i>
              <input type="text" id="orangeForm-department" name="department" value="<?php echo $department; ?>" class="form-control validate">
              <label data-error="wrong" data-success="right" for="orangeForm-department">Your Department</label>
            </div>
            <div class="md-form mb-4">
              <i class="fas fa-lock prefix grey-text"></i>
              <input type="password" id="orangeForm-pass" name="user_password" value="<?php echo $pass1; ?>" class="form-control validate">
              <label data-error="wrong" data-success="right" for="orangeForm-pass">Your password</label>
            </div>
            <div class="md-form mb-4">
              <i class="fas fa-user prefix grey-text"></i>
              <input type="text" id="orangeForm-status" name="status" value="Employee" class="form-control validate" readonly="">
              <label data-error="wrong" data-success="right" for="orangeForm-status">User Status</label>
            </div>
          </div>
          <div class="modal-footer d-flex justify-content-center">
            <button class="btn btn-primary" name="edit">UPDATE</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <!-- Edit User Modal -->

  <!-- SCRIPTS -->
  <!-- JQuery -->
  <script type="text/javascript" src="js/jquery-3.4.0.min.js"></script>
  <!-- Bootstrap tooltips -->
  <script type="text/javascript" src="js/popper.min.js"></script>
  <!-- Bootstrap core JavaScript -->
  <script type="text/javascript" src="js/bootstrap.min.js"></script>
  <!-- MDB core JavaScript -->
  <script type="text/javascript" src="js/mdb.min.js"></script>
  <!-- DataTables JavaScript -->
  <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
  
  <!-- Initialize tooltips -->
  <script>
    $(document).ready(function() {
      // Initialize tooltips
      $('[title]').tooltip();
    });
  </script>
</body>

<?php 
require_once("include/connection.php");

if(isset($_POST['edit'])){
  $user_name = mysqli_real_escape_string($conn, $_POST['name']);
  $email_address = mysqli_real_escape_string($conn, $_POST['email_address']);
  $user_password = password_hash($_POST['user_password'], PASSWORD_DEFAULT, array('cost' => 12));  
  $department = mysqli_real_escape_string($conn, $_POST['department']); // Get department value

  mysqli_query($conn, "UPDATE `login_user` SET `name` = '$user_name', `email_address` = '$email_address', `user_password` = '$user_password', `department` = '$department' WHERE `id` = '$id'") or die (mysqli_error($conn));

  echo "<script type='text/javascript'>alert('Success Edit User/Employee!!!');document.location='view_user.php'</script>";
}
?>
</html>