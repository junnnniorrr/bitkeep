<!DOCTYPE html>
<html lang="en">
<?php
// Initialize session
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
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
  
  <style>
    :root {
      --primary-color: #3498db;
      --secondary-color: #2980b9;
      --success-color: #2ecc71;
      --danger-color: #e74c3c;
      --warning-color: #f39c12;
      --light-color: #f8f9fa;
      --dark-color: #343a40;
      --sidebar-width: 280px;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 0;
    }
    
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: var(--sidebar-width);
      height: 100vh;
      background-color: white;
      color: #333;
      overflow-y: auto;
      z-index: 1000;
      transition: all 0.3s;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    .sidebar-header {
      padding: 20px;
      text-align: center;
      background-color: #f8f9fa;
      border-bottom: 1px solid #eaeaea;
    }
    
    .sidebar-logo {
      max-width: 150px;
      margin-bottom: 20px;
    }
    
    .sidebar .nav-link {
      padding: 12px 20px;
      color: #555;
      border-radius: 5px;
      margin: 2px 10px;
      transition: all 0.3s;
      display: flex;
      align-items: center;
    }
    
    .sidebar .nav-link i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
      color: #777;
    }
    
    .sidebar .nav-link:hover {
      background-color: #f1f5f9;
      color: var(--primary-color);
    }
    
    .sidebar .nav-link.active {
      background-color: var(--primary-color);
      color: white;
    }
    
    .sidebar .nav-link.active i {
      color: white;
    }
    
    .main-content {
      margin-left: var(--sidebar-width);
      padding: 20px;
      transition: all 0.3s;
    }
    
    .top-navbar {
      background-color: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      padding: 15px 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .content-card {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      overflow: hidden;
    }
    
    .card-header {
      background-color: var(--primary-color);
      color: white;
      padding: 15px 20px;
      font-weight: 500;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .card-body {
      padding: 20px;
    }
    
    table.dataTable {
      border-collapse: collapse !important;
      width: 100% !important;
    }
    
    .table th {
      background-color: #f8f9fa;
      color: #495057;
      font-weight: 600;
      border-bottom: 2px solid #dee2e6;
    }
    
    .table td, .table th {
      padding: 12px !important;
      vertical-align: middle !important;
    }
    
    .btn-action {
      padding: 5px 10px;
      border-radius: 4px;
      margin: 0 2px;
    }
    
    .btn-edit {
      color: var(--primary-color);
    }
    
    .btn-delete {
      color: var(--danger-color);
    }
    
    .modal-header {
      background-color: var(--primary-color);
      color: white;
      border-radius: 0;
    }
    
    .modal-footer {
      background-color: #f8f9fa;
    }
    
    .form-floating label {
      color: #6c757d;
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    
    .btn-primary:hover {
      background-color: var(--secondary-color);
      border-color: var(--secondary-color);
    }
    
    .footer {
      background-color: white;
      padding: 15px 20px;
      text-align: center;
      font-size: 14px;
      color: #6c757d;
      border-radius: 8px;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
      margin-top: 20px;
    }
    
    .user-welcome {
      display: flex;
      align-items: center;
    }
    
    .user-avatar {
      width: 40px;
      height: 40px;
      background-color: var(--primary-color);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 10px;
      font-weight: bold;
    }
    
    /* Loader */
    #loader {
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      z-index: 9999;
      background: rgba(255, 255, 255, 0.9) url('img/lg.flip-book-loader.gif') 50% 50% no-repeat;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    @media (max-width: 992px) {
      .sidebar {
        width: 70px;
        overflow: visible;
      }
      
      .sidebar .nav-link {
        padding: 12px;
        text-align: center;
        margin: 5px;
      }
      
      .sidebar .nav-link i {
        margin-right: 0;
        font-size: 1.25rem;
      }
      
      .sidebar .nav-link span {
        display: none;
      }
      
      .sidebar-header img {
        max-width: 40px;
      }
      
      .main-content {
        margin-left: 70px;
      }
    }
  </style>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script type="text/javascript">
    $(window).on('load', function(){
      setTimeout(function(){
        $('#loader').fadeOut('slow');  
      }, 500);
    });
    
    // Security measures
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
      var originalContent = document.body.innerHTML;
      document.body.innerHTML = `
        <div style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: white; font-size: 24px;">
            ${message}
        </div>
      `;
      setTimeout(function () {
        document.body.innerHTML = originalContent;
      }, 3000);
    }
  </script>
</head>

<body>
  <!-- Loader -->
  <div id="loader">
    <div class="loader-spinner"></div>
  </div>
  
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <img src="img/image1.svg" alt="BitKeep Logo" class="sidebar-logo">
      <h5 class="text-primary">BitKeep Management</h5>
    </div>
    <ul class="nav flex-column mt-2">
      <li class="nav-item">
        <a class="nav-link" href="dashboard.php">
          <i class="fas fa-chart-pie"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalRegisterForm">
          <i class="fas fa-user-plus"></i>
          <span>Add Admin</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="view_admin.php">
          <i class="fas fa-users-cog"></i>
          <span>View Admin</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalRegisterForm2">
          <i class="fas fa-user-plus"></i>
          <span>Add User</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="view_user.php">
          <i class="fas fa-users"></i>
          <span>View User</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="folder_management.php">
          <i class="fas fa-folder"></i>
          <span>Folders</span>
        </a>
        </li>
      <li class="nav-item">
        <a class="nav-link" href="manage_requests.php">
          <i class="fas fa-key"></i>
          <span>Requests</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="add_document.php">
          <i class="fas fa-file-medical"></i>
          <span>Files</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="view_userfile.php">
          <i class="fas fa-folder-open"></i>
          <span>View User File</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="admin_log.php">
          <i class="fas fa-shield-alt"></i>
          <span>Admin Logs</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="user_log.php">
          <i class="fas fa-clipboard-list"></i>
          <span>User Logs</span>
        </a>
         <li class="nav-item">
        <a class="nav-link" href="file_log.php">
          <i class="fas fa-file-alt"></i>
          <span>File Logs</span>
        </a>
         <li class="nav-item">
        <a class="nav-link" href="security_logs.php">
          <i class="fas fa-lock"></i>
          <span>Security Logs</span>
        </a>
      </li>
    </ul>
  </div>

  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <div class="top-navbar">
      <div class="d-flex align-items-center">
        <h4 class="mb-0">Admin Management</h4>
      </div>
      
      <?php 
        require_once("include/connection.php");
        $id = mysqli_real_escape_string($conn,$_SESSION['admin_user']);
        $r = mysqli_query($conn,"SELECT * FROM admin_login where id = '$id'") or die (mysqli_error($conn));
        $row = mysqli_fetch_array($r);
        $id=$row['admin_user'];
      ?>
      
      <div class="d-flex align-items-center">
        <div class="user-welcome me-3">
          <div class="user-avatar">
            <?php echo strtoupper(substr($id, 0, 1)); ?>
          </div>
          <div>
            <small class="text-muted">Welcome,</small>
            <div><?php echo ucwords(htmlentities($id)); ?></div>
          </div>
        </div>
        <a href="logout.php" class="btn btn-outline-primary btn-sm">
          <i class="fas fa-sign-out-alt me-1"></i> Sign Out
        </a>
      </div>
    </div>
    
    <!-- Content -->
    <div class="content-card">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users-cog me-2"></i>Admin List</h5>
        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalRegisterForm">
          <i class="fas fa-plus me-1"></i> Add New Admin
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="dtable" class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Department</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                require_once("include/connection.php");
                $query = "SELECT * FROM admin_login";
                $result = mysqli_query($conn, $query);
                while ($rs = mysqli_fetch_array($result)) {
                  $id = $rs['id'];
                  $fname = $rs['name'];
                  $admin = $rs['admin_user'];
                  $status = $rs['admin_status'];
                  $department = $rs['department'];
              ?>       
              <tr>
                <td><?php echo $fname; ?></td>
                <td><?php echo $admin; ?></td>
                <td>
                  <span class="badge bg-primary"><?php echo $status; ?></span>
                </td>
                <td><?php echo $department; ?></td>
                <td>
                  <button class="btn btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#modalRegisterFormsss">
                    <i class="fas fa-edit"></i>
                  </button>
                  <a href="delete_admin.php?id=<?php echo htmlentities($rs['id']); ?>" class="btn btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this admin?');">
                    <i class="fas fa-trash-alt"></i>
                  </a>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
      <p class="mb-0">All rights reserved &copy; <?php echo date('Y');?> | BitKeep Management</p>
    </div>
  </div>

  <!-- Add Admin Modal -->
  <div class="modal fade" id="modalRegisterForm" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="create_Admin.php" method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="addAdminModalLabel"><i class="fas fa-user-plus me-2"></i>Add Admin</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="floatingName" name="name" placeholder="Your name" required>
              <label for="floatingName">Your name</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="floatingDepartment" name="department" placeholder="Your Department" required>
              <label for="floatingDepartment">Your Department</label>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="floatingEmail" name="admin_user" placeholder="Your email" required>
              <label for="floatingEmail">Your email</label>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="floatingPassword" name="admin_password" placeholder="Your password" required>
              <label for="floatingPassword">Your password</label>
            </div>
            <div class="form-floating">
              <input type="text" class="form-control" id="floatingStatus" name="admin_status" value="Admin" readonly>
              <label for="floatingStatus">User Status</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" name="reg">Register</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add User Modal -->
  <div class="modal fade" id="modalRegisterForm2" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="create_user.php" method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="addUserModalLabel"><i class="fas fa-user-plus me-2"></i>Add User Employee</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="floatingUserName" name="name" placeholder="Your name" required>
              <label for="floatingUserName">Your name</label>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="floatingUserEmail" name="email_address" placeholder="Your email" required>
              <label for="floatingUserEmail">Your email</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="floatingUserDepartment" name="department" placeholder="Your Department" required>
              <label for="floatingUserDepartment">Your Department</label>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="floatingUserPassword" name="user_password" placeholder="Your password" required>
              <label for="floatingUserPassword">Your password</label>
            </div>
            <div class="form-floating">
              <input type="text" class="form-control" id="floatingUserStatus" name="user_status" value="Employee" readonly>
              <label for="floatingUserStatus">User Status</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" name="reguser">Register</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Admin Modal -->
  <div class="modal fade" id="modalRegisterFormsss" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <?php 
          require_once("include/connection.php");
          $q = mysqli_query($conn,"select * from admin_login where id = '$id'") or die (mysqli_error($conn));
          $rs1 = mysqli_fetch_array($q);
          $id1 = $rs1['id'];
          $fname1 = $rs1['name'];
          $admin1 = $rs1['admin_user'];
          $pass1 = $rs1['admin_password'];
          $status = $rs1['admin_status'];
          $department = $rs1['department'];
        ?>
        <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="editAdminModalLabel"><i class="fas fa-user-edit me-2"></i>Edit Admin</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" value="<?php echo $id1; ?>">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="floatingEditName" name="name" value="<?php echo $fname1; ?>" required>
              <label for="floatingEditName">Your name</label>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="floatingEditEmail" name="admin_user" value="<?php echo $admin1; ?>" required>
              <label for="floatingEditEmail">Your email</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="floatingEditDepartment" name="department" value="<?php echo $department; ?>" required>
              <label for="floatingEditDepartment">Your Department</label>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="floatingEditPassword" name="admin_password" value="<?php echo $pass1; ?>" required>
              <label for="floatingEditPassword">Your password</label>
            </div>
            <div class="form-floating">
              <input type="text" class="form-control" id="floatingEditStatus" name="status" value="Admin" readonly>
              <label for="floatingEditStatus">User Status</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" name="edit2">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  
  <script>
    $(document).ready(function() {
      $('#dtable').DataTable({
        responsive: true,
        language: {
          search: "<i class='fas fa-search'></i>",
          searchPlaceholder: "Search records"
        },
        dom: '<"top"fl>rt<"bottom"ip>',
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        pageLength: 10
      });
      
      $('.dataTables_filter input').addClass('form-control');
      $('.dataTables_length select').addClass('form-select');
    });
    
    <?php 
    if(isset($_POST['edit2'])){
      $user_name = mysqli_real_escape_string($conn, $_POST['name']);
      $admin_user = mysqli_real_escape_string($conn, $_POST['admin_user']);
      $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT, array('cost' => 12));  
      $department = mysqli_real_escape_string($conn, $_POST['department']);

      mysqli_query($conn, "UPDATE `admin_login` SET `name` = '$user_name', `admin_user` = '$admin_user', `admin_password` = '$admin_password', `department` = '$department' WHERE `id` = '$id'") or die (mysqli_error($conn));

      echo "alert('Successfully updated admin!'); window.location.href='view_admin.php';";
    }
    ?>
  </script>
</body>
</html>