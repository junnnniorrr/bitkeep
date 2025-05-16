<?php
session_start();
if(!isset($_SESSION["email_address"])){
    header("location:../login.html");
} 

require_once("include/connection.php");
$id = mysqli_real_escape_string($conn,$_SESSION['email_address']);
$r = mysqli_query($conn,"SELECT * FROM login_user where id = '$id'") or die (mysqli_error($conn));
$row = mysqli_fetch_array($r);
$id=$row['email_address'];
$user_avatar = strtoupper(substr($id, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>BitKeep Management - User Logs</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
  
  <style>
    :root {
        --primary-color: #4361ee;
        --primary-light: rgba(67, 97, 238, 0.1);
        --primary-dark: #3a56d4;
        --secondary-color: #f72585;
        --secondary-light: rgba(247, 37, 133, 0.1);
        --dark-color: #1e293b;
        --light-color: #f8fafc;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-300: #cbd5e1;
        --gray-400: #94a3b8;
        --gray-500: #64748b;
        --gray-600: #475569;
        --gray-700: #334155;
        --gray-800: #1e293b;
        --gray-900: #0f172a;
        --border-radius-sm: 0.375rem;
        --border-radius: 0.5rem;
        --border-radius-lg: 0.75rem;
        --box-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --box-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --box-shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --transition: all 0.2s ease-in-out;
        --sidebar-width: 250px;
        --navbar-height: 70px;
    }
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        color: var(--gray-800);
        line-height: 1.6;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        padding-top: var(--navbar-height);
        overflow-x: hidden;
    }
    
    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        top: var(--navbar-height);
        left: 0;
        height: calc(100vh - var(--navbar-height));
        width: var(--sidebar-width);
        background-color: white;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        z-index: 999;
        transition: all 0.3s;
        overflow-y: auto;
    }
    
    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid var(--gray-200);
        text-align: center;
    }
    
    .sidebar-brand {
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--dark-color);
        text-decoration: none;
    }
    
    .highlight {
        color: var(--secondary-color);
    }
    
    .sidebar-menu {
        padding: 15px 0;
        margin-bottom: 70px;
    }
    
    .sidebar-item {
        padding: 12px 20px;
        display: flex;
        align-items: center;
        color: var(--gray-700);
        text-decoration: none;
        transition: all 0.2s;
        border-left: 4px solid transparent;
        font-weight: 500;
    }
    
    .sidebar-item:hover, .sidebar-item.active {
        background-color: var(--gray-100);
        color: var(--primary-color);
        border-left: 4px solid var(--primary-color);
    }
    
    .sidebar-item i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    .sidebar-divider {
        height: 1px;
        background-color: var(--gray-200);
        margin: 10px 20px;
    }
    
    .sidebar-footer {
        padding: 15px 20px;
        border-top: 1px solid var(--gray-200);
        position: absolute;
        bottom: 0;
        width: 100%;
        background-color: white;
    }
    
    .user-info {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding: 15px 20px;
        background-color: var(--gray-100);
        border-radius: var(--border-radius);
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-weight: bold;
    }
    
    .user-details {
        flex: 1;
        overflow: hidden;
    }
    
    .user-name {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--gray-800);
    }
    
    .user-role {
        font-size: 0.75rem;
        color: var(--gray-500);
    }
    
    /* Main Content Styles */
    .main-content {
        margin-left: var(--sidebar-width);
        padding: 30px;
        transition: all 0.3s;
        min-height: calc(100vh - var(--navbar-height));
        width: calc(100% - var(--sidebar-width));
    }
    
    /* Navbar Styles */
    .navbar {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        background-color: white !important;
        padding: 0 30px;
        height: var(--navbar-height);
        margin-left: var(--sidebar-width);
        transition: all 0.3s;
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        z-index: 1030;
    }
    
    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
    }
    
    /* Toggle Button */
    .sidebar-toggle {
        background: none;
        border: none;
        color: var(--gray-700);
        font-size: 1.25rem;
        cursor: pointer;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: var(--border-radius);
        transition: all 0.2s;
    }
    
    .sidebar-toggle:hover {
        background-color: var(--gray-100);
    }
    
    /* Card Styles */
    .card {
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        border: none;
        margin-bottom: 30px;
        background-color: white;
        overflow: hidden;
    }
    
    .card-header {
        background-color: white;
        border-bottom: 1px solid var(--gray-200);
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .card-title {
        font-weight: 600;
        font-size: 1.25rem;
        color: var(--gray-800);
        margin-bottom: 0;
        display: flex;
        align-items: center;
    }
    
    .card-title i {
        margin-right: 10px;
        color: var(--primary-color);
    }
    
    .card-body {
        padding: 20px;
    }
    
    /* Button Styles */
    .btn {
        font-weight: 500;
        padding: 0.5rem 1.25rem;
        border-radius: var(--border-radius);
        transition: all 0.2s;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--box-shadow);
    }
    
    /* Table Styles */
    .table {
        width: 100%;
        margin-bottom: 0;
    }
    
    .table th {
        font-weight: 600;
        color: var(--gray-700);
        background-color: var(--gray-100);
        border-bottom: 2px solid var(--gray-200);
        padding: 12px 15px;
        font-size: 0.875rem;
    }
    
    .table td {
        padding: 12px 15px;
        vertical-align: middle;
        border-top: 1px solid var(--gray-200);
        color: var(--gray-700);
        font-size: 0.875rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: var(--gray-50);
    }
    
    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        color: var(--gray-700);
        font-size: 0.875rem;
        padding: 15px 0;
    }
    
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--gray-300);
        border-radius: var(--border-radius-sm);
        padding: 0.25rem 0.5rem;
        margin: 0 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid var(--gray-300);
        border-radius: var(--border-radius-sm);
        padding: 0.375rem 0.75rem;
        margin-left: 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin-left: 2px;
        border: 1px solid var(--gray-300);
        border-radius: var(--border-radius-sm);
        background-color: white;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: var(--primary-color) !important;
        color: white !important;
        border: 1px solid var(--primary-color);
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--gray-100) !important;
        color: var(--primary-color) !important;
        border: 1px solid var(--primary-color);
    }
    
    /* Footer Styles */
    .footer {
        text-align: center;
        padding: 1.5rem;
        color: var(--gray-500);
        font-size: 0.875rem;
        background-color: white;
        border-top: 1px solid var(--gray-200);
        margin-top: auto;
        margin-left: var(--sidebar-width);
        transition: all 0.3s;
    }
    
    /* Loader */
    #loader {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        background: rgba(255, 255, 255, 0.97);
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .loader-content {
        text-align: center;
    }
    
    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid rgba(67, 97, 238, 0.1);
        border-radius: 50%;
        border-top-color: var(--primary-color);
        animation: spin 1s ease-in-out infinite;
        margin: 0 auto 20px;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Responsive Styles */
    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-100%);
        }
        
        .main-content, .navbar, .footer {
            margin-left: 0;
            width: 100%;
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
    }
    
    @media (max-width: 768px) {
        .main-content {
            padding: 20px;
        }
        
        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .card-header .btn {
            margin-top: 15px;
            align-self: flex-start;
        }
    }
    
    /* Security Notification */
    .security-notification {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 9999;
        text-align: center;
        max-width: 400px;
        animation: fadeInUp 0.3s ease;
    }
    
    .security-notification i {
        font-size: 2.5rem;
        color: var(--warning-color);
        margin-bottom: 15px;
    }
    
    .security-notification p {
        font-size: 1.1rem;
        margin-bottom: 0;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate(-50%, 20px);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%);
        }
    }
  </style>
</head>

<body>
  <!-- Loader -->
  <div id="loader">
    <div class="loader-content">
      <div class="spinner"></div>
      <h5 class="mt-3 text-muted">Loading...</h5>
    </div>
  </div>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
      <button class="sidebar-toggle me-3" id="sidebarToggle">
        <i class="fas fa-bars"></i>
      </button>
      <span class="navbar-brand">
        <span class="highlight">Bit</span>Keep <span class="highlight">M</span>anagement <span class="highlight">S</span>ystem
      </span>
      <div class="d-flex ms-auto">
        <span class="navbar-text">
          <i class="fas fa-calendar-day me-2"></i> <?php echo date('F d, Y'); ?>
        </span>
      </div>
    </div>
  </nav>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="user-info">
      <div class="user-avatar">
        <?php echo $user_avatar; ?>
      </div>
      <div class="user-details">
        <div class="user-name"><?php echo ucwords(htmlentities($id)); ?></div>
        <div class="user-role">User</div>
      </div>
    </div>
    
    <div class="sidebar-menu">
      <a href="home.php" class="sidebar-item">
        <i class="fas fa-home"></i> Dashboard
      </a>
      <a href="add_file.php" class="sidebar-item">
        <i class="fas fa-file-medical"></i> Add File
      </a>
      <a href="history_log.php" class="sidebar-item active">
        <i class="fas fa-history"></i> User Logs
      </a>
      <a href="request_folder.php" class="sidebar-item">
        <i class="fas fa-folder-plus"></i> Request Folder
      </a>
      <a href="user_dashboard.php" class="sidebar-item">
        <i class="fas fa-folder"></i> My Folders
      </a>
      
      <div class="sidebar-divider"></div>
      
      <a href="Logout.php" class="sidebar-item">
        <i class="fas fa-sign-out-alt"></i> Log Out
      </a>
    </div>
    
    <div class="sidebar-footer">
      <small class="text-muted">© <?php echo date('Y'); ?> BitKeep Management System</small>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Page Header -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title">
          <i class="fas fa-history"></i> User Login History
        </h5>
        <a href="home.php" class="btn btn-primary">
          <i class="fas fa-chevron-left me-2"></i> Back to Home
        </a>
      </div>
    </div>
    
    <!-- Data Table Card -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="dtable" class="table table-striped table-hover">
            <thead>
              <tr>
                <th><i class="fas fa-user me-2"></i>USER</th>    
                <th><i class="fas fa-network-wired me-2"></i>IP ADDRESS</th>
                <th><i class="fas fa-server me-2"></i>HOST</th>
                <th><i class="fas fa-sign-in-alt me-2"></i>ACTION</th> 
                <th><i class="fas fa-clock me-2"></i>LOGIN TIME</th>
                <th><i class="fas fa-sign-out-alt me-2"></i>ACTION</th> 
                <th><i class="fas fa-clock me-2"></i>LOGOUT TIME</th>
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

  <!-- Footer -->
  <footer class="footer">
    <p class="mb-0">All rights Reserved &copy; <?php echo date('Y'); ?> Created By: BitKeep Management</p>
  </footer>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
  
  <script>
    // Initialize DataTable
    $(document).ready(function() {
      $('#dtable').DataTable({
        responsive: true,
        "aLengthMenu": [[5, 10, 15, 25, 50, 100, -1], [5, 10, 15, 25, 50, 100, "All"]],
        "iDisplayLength": 10
      });
    });
    
    // Loader
    window.addEventListener('load', function() {
      setTimeout(function() {
        document.getElementById('loader').style.display = 'none';
      }, 500);
    });
    
    // Sidebar Toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('active');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
      if (window.innerWidth < 992 && 
          !document.getElementById('sidebar').contains(e.target) && 
          e.target !== document.getElementById('sidebarToggle') && 
          !document.getElementById('sidebarToggle').contains(e.target) && 
          document.getElementById('sidebar').classList.contains('active')) {
        document.getElementById('sidebar').classList.remove('active');
      }
    });
    
    // Disable right-click
    document.addEventListener('contextmenu', function(e) {
      e.preventDefault();
      showNotification("Right-click is disabled.");
    });
    
    // Disable common keyboard shortcuts
    document.addEventListener('keydown', function(e) {
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
    
    // Function to show notifications
    function showNotification(message) {
      const notification = document.createElement('div');
      notification.className = 'security-notification';
      notification.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <p>${message}</p>
      `;
      document.body.appendChild(notification);
      setTimeout(() => notification.remove(), 3000);
    }
  </script>
</body>
</html>