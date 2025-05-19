<!DOCTYPE html>
<html lang="en">
<?php
// Initialize session
session_start();

// Check, if username session is NOT set then this page will jump to login page
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.html');
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap core CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom styles -->
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --info: #4895ef;
      --warning: #f72585;
      --danger: #e63946;
      --light: #f8f9fa;
      --dark: #212529;
      --sidebar-width: 280px;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 0;
    }
    
    /* Loader */
    #loader {
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      z-index: 9999;
      background: rgba(249,249,249,0.9) url('img/lg.flip-book-loader.gif') 50% 50% no-repeat;
    }
    
    /* Navbar */
    .topbar {
      height: 70px;
      background: white;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      position: fixed;
      top: 0;
      right: 0;
      left: var(--sidebar-width);
      z-index: 10;
      transition: all 0.3s ease;
    }
    
    .navbar-brand img {
      height: 40px;
    }
    
    .topbar .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .topbar .user-info .avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--primary);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
    }
    
    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: var(--sidebar-width);
      height: 100%;
      background: white;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      z-index: 20;
      transition: all 0.3s ease;
      overflow-y: auto;
    }
    
    .sidebar-header {
      padding: 20px;
      text-align: center;
      border-bottom: 1px solid #eaeaea;
    }
    
    .sidebar-logo {
      max-width: 150px;
      height: auto;
    }
    
    .sidebar-menu {
      padding: 20px 0;
    }
    
    .menu-item {
      padding: 12px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      color: #6c757d;
      text-decoration: none;
      transition: all 0.2s ease;
      border-left: 4px solid transparent;
    }
    
    .menu-item:hover {
      background: rgba(67, 97, 238, 0.05);
      color: var(--primary);
    }
    
    .menu-item.active {
      color: var(--primary);
      background: rgba(67, 97, 238, 0.1);
      border-left: 4px solid var(--primary);
      font-weight: 500;
    }
    
    .menu-item i {
      width: 20px;
      text-align: center;
    }
    
    /* Main Content */
    .main-content {
      margin-left: var(--sidebar-width);
      padding: 90px 30px 30px;
      min-height: 100vh;
      transition: all 0.3s ease;
    }
    
    .card {
      border: none;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      border-radius: 10px;
      margin-bottom: 24px;
    }
    
    .card-header {
      background-color: white;
      border-bottom: 1px solid #eaeaea;
      padding: 16px 24px;
      font-weight: 600;
    }
    
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }
    
    .page-title {
      font-size: 24px;
      font-weight: 600;
      color: #333;
    }
    
    /* DataTable Styling */
    .table-container {
      background: white;
      border-radius: 10px;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      padding: 20px;
      overflow: hidden;
    }
    
    table.dataTable {
      border-collapse: collapse !important;
      width: 100% !important;
    }
    
    table.dataTable thead th {
      background-color: #f8f9fa;
      color: #6c757d;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 12px;
      padding: 12px;
      border-bottom: 1px solid #eaeaea !important;
    }
    
    table.dataTable tbody td {
      padding: 12px;
      vertical-align: middle;
      border-bottom: 1px solid #eaeaea !important;
    }
    
    table.dataTable.stripe tbody tr.odd, 
    table.dataTable.display tbody tr.odd {
      background-color: #f8f9fa;
    }
    
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter {
      margin-bottom: 20px;
    }
    
    .dataTables_wrapper .dataTables_length select {
      border: 1px solid #dee2e6;
      border-radius: 4px;
      padding: 6px;
    }
    
    .dataTables_wrapper .dataTables_filter input {
      border: 1px solid #dee2e6;
      border-radius: 4px;
      padding: 6px 12px;
    }
    
    /* Buttons */
    .btn-primary {
      background-color: var(--primary);
      border-color: var(--primary);
    }
    
    .btn-primary:hover, .btn-primary:focus {
      background-color: var(--secondary);
      border-color: var(--secondary);
    }
    
    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 6px;
      background-color: var(--info);
      color: white;
      border: none;
      transition: all 0.2s ease;
    }
    
    .btn-back:hover {
      background-color: var(--primary);
    }
    
    /* Modal */
    .modal-content {
      border: none;
      border-radius: 10px;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .modal-header {
      border-bottom: 1px solid #eaeaea;
      padding: 16px 24px;
    }
    
    .modal-title {
      font-weight: 600;
    }
    
    .modal-body {
      padding: 24px;
    }
    
    .form-label {
      font-weight: 500;
      color: #495057;
    }
    
    .form-control {
      border-radius: 6px;
      padding: 10px 16px;
      border: 1px solid #dee2e6;
    }
    
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
    }
    
    /* Footer */
    .footer {
      background: white;
      padding: 20px;
      text-align: center;
      margin-top: 40px;
      border-radius: 10px;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    /* Responsive */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .topbar, .main-content {
        left: 0;
        margin-left: 0;
      }
      
      .menu-toggle {
        display: block;
      }
    }
  </style>

  <!-- DataTables -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
  
  <script type="text/javascript">
    $(document).ready(function() {
      $('#fileTable').DataTable({
        responsive: true,
        "aLengthMenu": [[5, 10, 15, 25, 50, 100, -1], [5, 10, 15, 25, 50, 100, "All"]],
        "iDisplayLength": 10
      });
      
      // Toggle sidebar on mobile
      $('.menu-toggle').on('click', function() {
        $('.sidebar').toggleClass('active');
      });
    });
    
    // Loader
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

    document.addEventListener('keyup', function (e) {
      if (e.keyCode == 44) { // Print Screen
          navigator.clipboard.writeText('');
          showNotification("Print Screen is disabled.");
      }
    });

    function showNotification(message) {
      // Create notification element
      var notification = document.createElement('div');
      notification.className = 'notification';
      notification.style.position = 'fixed';
      notification.style.top = '20px';
      notification.style.right = '20px';
      notification.style.padding = '15px 20px';
      notification.style.backgroundColor = '#333';
      notification.style.color = 'white';
      notification.style.borderRadius = '5px';
      notification.style.boxShadow = '0 3px 10px rgba(0,0,0,0.2)';
      notification.style.zIndex = '9999';
      notification.textContent = message;
      
      // Add to DOM
      document.body.appendChild(notification);
      
      // Remove after 3 seconds
      setTimeout(function() {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.5s ease';
        setTimeout(function() {
          document.body.removeChild(notification);
        }, 500);
      }, 3000);
    }
  </script>
</head>

<body>
  <!-- Loader -->
  <div id="loader"></div>
  
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <img src="img/image1.svg" class="sidebar-logo" alt="BitKeep Logo">
    </div>
    
    <div class="sidebar-menu">
      <a href="dashboard.php" class="menu-item">
        <i class="fas fa-chart-pie"></i>
        <span>Dashboard</span>
      </a>
      
      <a href="#" class="menu-item" data-bs-toggle="modal" data-bs-target="#addAdminModal">
        <i class="fas fa-user-plus"></i>
        <span>Add Admin</span>
      </a>
      
      <a href="view_admin.php" class="menu-item">
        <i class="fas fa-users-cog"></i>
        <span>View Admins</span>
      </a>
      
      <a href="#" class="menu-item" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-plus"></i>
        <span>Add User</span>
      </a>
      
      <a href="view_user.php" class="menu-item">
        <i class="fas fa-users"></i>
        <span>View Users</span>
      </a>
      
      <a href="folder_management.php" class="menu-item">
        <i class="fas fa-folder"></i>
        <span>Folders</span>
      </a>
      
      <a href="manage_requests.php" class="menu-item">
        <i class="fas fa-key"></i>
        <span>Requests</span>
      </a>

      <a href="add_document.php" class="menu-item">
        <i class="fas fa-file-medical"></i>
        <span>Documents</span>
      </a>
      
      <a href="view_userfile.php" class="menu-item active">
        <i class="fas fa-folder-open"></i>
        <span>View User Files</span>
      </a>
      
      <a href="admin_log.php" class="menu-item">
        <i class="fas fa-history"></i>
        <span>Admin Logs</span>
      </a>
      
      <a href="user_log.php" class="menu-item">
        <i class="fas fa-history"></i>
        <span>User Logs</span>
      </a>
      <a href="file_log.php" class="menu-item">
        <i class="fas fa-file-alt"></i>
        <span>file Logs</span>
      </a>
      <a href="security_logs.php" class="menu-item">
        <i class="fas fa-lock"></i>
        <span>security Logs</span>
      </a>
    </div>
  </div>
  
  <!-- Topbar -->
  <div class="topbar">
    <div class="container-fluid d-flex justify-content-between align-items-center h-100">
      <div class="d-flex align-items-center">
        <button class="menu-toggle btn d-lg-none me-3">
          <i class="fas fa-bars"></i>
        </button>
      </div>
      
      <?php 
        require_once("include/connection.php");
        $id = mysqli_real_escape_string($conn,$_SESSION['admin_user']);
        $r = mysqli_query($conn,"SELECT * FROM admin_login where id = '$id'") or die (mysqli_error($conn));
        $row = mysqli_fetch_array($r);
        $id = $row['admin_user'];
      ?>
      
      <div class="user-info">
        <div class="d-none d-md-block">
          Welcome, <strong><?php echo ucwords(htmlentities($id)); ?></strong>
        </div>
        <div class="avatar">
          <?php echo substr($id, 0, 1); ?>
        </div>
        <a href="logout.php" class="btn btn-outline-danger btn-sm ms-2">
          <i class="fas fa-sign-out-alt"></i>
          <span class="d-none d-md-inline-block ms-1">Sign Out</span>
        </a>
      </div>
    </div>
  </div>
  
  <!-- Main Content -->
  <div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">User Uploaded Files</h1>
      
      <a href="add_document.php" class="btn-back">
        <i class="fas fa-chevron-left"></i>
        Back to Files
      </a>
    </div>
    
    <!-- Files Table -->
    <div class="table-container">
      <table id="fileTable" class="table table-hover">
        <thead>
          <tr>
            <th>Filename</th>
            <th>File Size</th>
            <th>Uploader</th>
            <th>Status</th>
            <th>Upload Date/Time</th>
          </tr>
        </thead>
        <tbody>
          <?php 
            require_once("include/connection.php");
            $query = mysqli_query($conn,"SELECT DISTINCT ID,NAME,SIZE,EMAIL,ADMIN_STATUS,TIMERS,DOWNLOAD FROM upload_files WHERE ADMIN_STATUS = 'Employee' group by NAME DESC") or die (mysqli_error($conn));
            while($file=mysqli_fetch_array($query)){
              $id = $file['ID'];
              $name = $file['NAME'];
              $size = $file['SIZE'];
              $email = $file['EMAIL'];
              $uploads = $file['ADMIN_STATUS'];
              $time = $file['TIMERS'];
          ?>
          <tr>
            <td><?php echo $name; ?></td>
            <td><?php echo floor($size / 1000) . ' KB'; ?></td>
            <td><?php echo $email; ?></td>
            <td><span class="badge bg-info"><?php echo $uploads; ?></span></td>
            <td><?php echo $time; ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
      <p class="mb-0">All rights reserved &copy; <?php echo date('Y'); ?> Created by BitKeep Management</p>
    </div>
  </div>
  
  <!-- Add Admin Modal -->
  <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addAdminModalLabel"><i class="fas fa-user-plus me-2"></i>Add Admin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="create_Admin.php" method="POST">
          <div class="modal-body">
            <input type="hidden" name="status" value="Admin">
            
            <div class="mb-3">
              <label for="admin-name" class="form-label">Full Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="admin-name" name="name" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="admin-email" class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" id="admin-email" name="admin_user" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="admin-department" class="form-label">Department</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-building"></i></span>
                <input type="text" class="form-control" id="admin-department" name="department" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="admin-password" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="admin-password" name="admin_password" required>
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('admin-password')">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" name="reg">Create Admin</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!-- Add User Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addUserModalLabel"><i class="fas fa-user-plus me-2"></i>Add User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="create_user.php" method="POST">
          <div class="modal-body">
            <input type="hidden" name="status" value="Employee">
            
            <div class="mb-3">
              <label for="user-name" class="form-label">Full Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="user-name" name="name" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="user-email" class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" id="user-email" name="email_address" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="user-department" class="form-label">Department</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-building"></i></span>
                <input type="text" class="form-control" id="user-department" name="department" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="user-password" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="user-password" name="user_password" required>
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('user-password')">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" name="reguser">Create User</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    function togglePassword(id) {
      const passwordInput = document.getElementById(id);
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      
      // Toggle eye icon
      const icon = event.currentTarget.querySelector('i');
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    }
  </script>
</body>
</html>