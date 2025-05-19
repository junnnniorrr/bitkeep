<!DOCTYPE html>
<html lang="en">
<?php

session_start();
if(!isset($_SESSION["admin_user"])){
    header("location:index.html");
} else {
    $uname = $_SESSION['admin_user'];
    
    // Fetch the admin email from the database
    require_once("include/connection.php");
    $id = mysqli_real_escape_string($conn, $uname);
    $query = mysqli_query($conn, "SELECT admin_user FROM admin_login WHERE id = '$id'") or die(mysqli_error($conn));
    $row = mysqli_fetch_array($query);
    $admin_email = $row['admin_user'];
    
}
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bitkeep Management</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --primary-color: #6366f1;
      --primary-hover: #4f46e5;
      --secondary-color: #10b981;
      --accent-color: #f59e0b;
      --danger-color: #ef4444;
      --dark-color: #1e293b;
      --light-color: #f8fafc;
      --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
      background-color: #f1f5f9;
      font-family: 'Poppins', sans-serif;
      color: #334155;
    }

    /* Sidebar Styles */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 280px;
      background: #fff;
      box-shadow: 4px 0 10px rgba(0,0,0,0.05);
      z-index: 1000;
      transition: var(--transition);
      overflow-y: auto;
    }

    .sidebar-header {
      padding: 1.75rem 1.5rem;
      text-align: center;
      border-bottom: 1px solid rgba(0,0,0,0.05);
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
    }

    .sidebar-header img {
      max-width: 180px;
      height: auto;
      filter: brightness(0) invert(1);
    }

    .nav-link {
      padding: 0.875rem 1.5rem;
      color: var(--dark-color);
      transition: var(--transition);
      border-radius: 0.5rem;
      margin: 0.3rem 0.75rem;
      font-weight: 500;
      display: flex;
      align-items: center;
    }

    .nav-link:hover {
      background-color: rgba(99, 102, 241, 0.08);
      color: var(--primary-color);
      transform: translateX(5px);
    }

    .nav-link.active {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
      color: white;
      box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.2);
    }

    .nav-link i {
      width: 24px;
      text-align: center;
      margin-right: 0.75rem;
      font-size: 1.1rem;
    }

    /* Main Content Styles */
    .main-content {
      margin-left: 280px;
      padding: 2rem;
      min-height: 100vh;
    }

    /* Navbar Styles */
    .top-navbar {
      background: #fff;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      padding: 1rem 2rem;
      margin-bottom: 2rem;
      border-radius: 1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .user-welcome {
      font-size: 1.1rem;
      color: var(--dark-color);
      font-weight: 600;
      display: flex;
      align-items: center;
    }
    
    .user-welcome::before {
      content: '';
      display: inline-block;
      width: 10px;
      height: 10px;
      background-color: var(--secondary-color);
      border-radius: 50%;
      margin-right: 10px;
    }

    .sign-out-btn {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
      color: white;
      padding: 0.6rem 1.5rem;
      border-radius: 0.5rem;
      transition: var(--transition);
      font-weight: 500;
      border: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.4);
    }

    .sign-out-btn:hover {
      background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary-color) 100%);
      transform: translateY(-2px);
      color: white;
      box-shadow: 0 6px 8px -1px rgba(99, 102, 241, 0.5);
    }

    /* Card Styles */
    .dashboard-card {
      background: #fff;
      border-radius: 1rem;
      box-shadow: var(--card-shadow);
      transition: var(--transition);
      margin-bottom: 1.5rem;
      border: none;
      overflow: hidden;
      height: 100%;
    }

    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .card-icon {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.75rem;
      margin-bottom: 1.25rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .dashboard-card h3 {
      font-size: 1.25rem;
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
    }

    .dashboard-card .display-4 {
      font-weight: 700;
      color: var(--primary-color);
      font-size: 2.5rem;
    }

    .dashboard-card a {
      display: inline-flex;
      align-items: center;
      margin-top: 1rem;
      font-weight: 500;
      color: var(--primary-color);
      transition: var(--transition);
    }

    .dashboard-card a:hover {
      color: var(--primary-hover);
    }

    .dashboard-card a i {
      transition: var(--transition);
    }

    .dashboard-card a:hover i {
      transform: translateX(5px);
    }

    /* Modal Styles */
    .modal-content {
      border-radius: 1rem;
      border: none;
      box-shadow: var(--card-shadow);
      overflow: hidden;
    }

    .modal-header {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
      color: white;
      border-bottom: none;
      padding: 1.5rem;
    }

    .modal-title {
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .modal-body {
      padding: 1.5rem;
    }

    .form-label {
      font-weight: 500;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
    }

    .form-control {
      border-radius: 0.5rem;
      padding: 0.75rem;
      border: 1px solid #e2e8f0;
      font-size: 1rem;
      transition: var(--transition);
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
    }

    .input-group-text {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 0.5rem 0 0 0.5rem;
      color: #64748b;
    }

    .btn-close {
      color: white;
      opacity: 0.8;
      filter: brightness(0) invert(1);
    }

    .btn-close:hover {
      opacity: 1;
    }

    .modal-footer {
      border-top: none;
      padding: 1rem 1.5rem 1.5rem;
    }

    .modal-footer .btn {
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      border-radius: 0.5rem;
      transition: var(--transition);
    }

    .modal-footer .btn-secondary {
      background-color: #e2e8f0;
      color: #475569;
      border: none;
    }

    .modal-footer .btn-secondary:hover {
      background-color: #cbd5e1;
    }

    .modal-footer .btn-primary {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
      border: none;
      box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.4);
    }

    .modal-footer .btn-primary:hover {
      background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary-color) 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 8px -1px rgba(99, 102, 241, 0.5);
    }

    /* Chart Styles */
    .chart-container {
      background: #fff;
      border-radius: 1rem;
      box-shadow: var(--card-shadow);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      transition: var(--transition);
      height: 100%;
      position: relative;
    }

    .chart-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .chart-container h4 {
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid #e2e8f0;
      display: flex;
      align-items: center;
    }

    .chart-container h4::before {
      content: '';
      display: inline-block;
      width: 12px;
      height: 12px;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
      border-radius: 50%;
      margin-right: 10px;
    }

    /* Chart wrapper to control height */
    .chart-wrapper {
      position: relative;
      height: 220px; /* Fixed height for all charts */
      width: 100%;
    }

    /* Loader */
    #loader {
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      z-index: 9999;
      background: rgba(255, 255, 255, 0.95);
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .loader-spinner {
      width: 50px;
      height: 50px;
      border: 5px solid #f3f3f3;
      border-top: 5px solid var(--primary-color);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Notification */
    .custom-notification {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 1.5rem;
      border-radius: 1rem;
      box-shadow: var(--card-shadow);
      z-index: 9999;
      text-align: center;
      animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translate(-50%, -60%); }
      to { opacity: 1; transform: translate(-50%, -50%); }
    }

    .notification-icon {
      font-size: 2.5rem;
      color: var(--accent-color);
      margin-bottom: 1rem;
    }

    .notification-message {
      font-weight: 500;
      color: var(--dark-color);
    }

    /* Card hover effects */
    .dashboard-card {
      position: relative;
      overflow: hidden;
    }

    .dashboard-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--primary-color), var(--primary-hover));
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.3s ease;
    }

    .dashboard-card:hover::before {
      transform: scaleX(1);
    }

    /* Chart Legend Custom Styling */
    .custom-legend {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 0.5rem;
      gap: 1rem;
    }

    .legend-item {
      display: flex;
      align-items: center;
      font-size: 0.875rem;
      font-weight: 500;
    }

    .legend-color {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-right: 6px;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
        width: 250px;
      }
      
      .sidebar.show {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
        padding: 1rem;
      }
      
      .toggle-sidebar {
        display: block !important;
      }

      .top-navbar {
        padding: 0.75rem 1rem;
      }

      .dashboard-card {
        padding: 1rem !important;
      }

      .card-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
      }

      .dashboard-card h3 {
        font-size: 1rem;
      }

      .dashboard-card .display-4 {
        font-size: 1.75rem;
      }
      
      .chart-wrapper {
        height: 180px;
      }
    }

    @media (min-width: 993px) {
      .toggle-sidebar {
        display: none !important;
      }
    }

    .toggle-sidebar {
      background: none;
      border: none;
      color: var(--dark-color);
      font-size: 1.5rem;
      cursor: pointer;
      display: none;
    }
  </style>
</head>

<body>
  <!-- Loader -->
  <div id="loader">
    <div class="loader-spinner"></div>
  </div>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <img src="img/image1.svg" alt="Bitkeep Logo">
    </div>
    <div class="list-group list-group-flush mt-3">
      <a href="dashboard.php" class="nav-link active">
        <i class="fas fa-tachometer-alt"></i> Dashboard
      </a>
      <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalRegisterForm">
        <i class="fas fa-user-plus"></i> Add Admin
      </a>
      <a href="view_admin.php" class="nav-link">
        <i class="fas fa-users"></i> View Admin
      </a>
      <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalRegisterForm2">
        <i class="fas fa-user-plus"></i> Add User
      </a>
      <a href="view_user.php" class="nav-link">
        <i class="fas fa-users"></i> View User
      </a>
      <a href="folder_management.php" class="nav-link">
        <i class="fas fa-folder"></i> Folders
      </a>
      <a href="manage_requests.php" class="nav-link">
        <i class="fas fa-key"></i> Requests
      </a>
      <a href="add_document.php" class="nav-link">
        <i class="fas fa-file-medical"></i> Document
      </a>
      <a href="view_userfile.php" class="nav-link">
        <i class="fas fa-folder-open"></i> View User File
      </a>
      <a href="admin_log.php" class="nav-link">
        <i class="fas fa-history"></i> Admin Log
      </a>
      <a href="user_log.php" class="nav-link">
        <i class="fas fa-history"></i> User Log
      </a>
      <a href="file_log.php" class="nav-link">
        <i class="fas fa-file-alt"></i> File Log
      </a>
            <a href="security_logs.php" class="nav-link">
        <i class="fas fa-lock"></i> Security Log
      </a>
      <a href="admin_files_log.php" class="nav-link">
        <i class="fas fa-file-alt"></i> Admin file Access Log
      </a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Top Navbar -->
    <nav class="top-navbar">
      <button class="toggle-sidebar" id="toggleSidebar">
        <i class="fas fa-bars"></i>
      </button>
      <div class="user-welcome">
        Welcome, <?php echo htmlspecialchars($admin_email); ?>!
      </div>
      <a href="logout.php" class="sign-out-btn text-decoration-none">
        <i class="fas fa-sign-out-alt"></i>Sign Out
      </a>
    </nav>
<!-- Dashboard Content -->
<div class="container-fluid px-0">
  <!-- Stats Cards -->
  <div class="row g-4">
    <?php
    require_once("include/connection.php");
    
    // Fetch counts for files, users (employees), admins, folders, admin logged activities, and user logged activities
    $file_count_query = "SELECT COUNT(*) AS total_files FROM upload_files";
    $user_count_query = "SELECT COUNT(*) AS total_users FROM login_user";
    $admin_count_query = "SELECT COUNT(*) AS total_admins FROM admin_login";
    $folder_count_query = "SELECT COUNT(*) AS total_folders FROM folders";
    
    // Add queries for security logs and file access logs
    $security_logs_query = "SELECT COUNT(*) AS total_security_logs FROM security_logs";
    $file_access_logs_query = "SELECT COUNT(*) AS total_file_access FROM file_access_logs";

    // Admin logged activities and user logged activities from history logs
    $admin_logged_query = "SELECT COUNT(*) AS admin_logged FROM history_log1 WHERE action LIKE '%LoggedIn%'";
    $user_logged_query = "SELECT COUNT(*) AS user_logged FROM history_log WHERE action LIKE '%LoggedIn%'";
    
    // Execute queries
    $file_count_result = mysqli_query($conn, $file_count_query);
    $user_count_result = mysqli_query($conn, $user_count_query);
    $admin_count_result = mysqli_query($conn, $admin_count_query);
    $folder_count_result = mysqli_query($conn, $folder_count_query);
    $admin_logged_result = mysqli_query($conn, $admin_logged_query);
    $user_logged_result = mysqli_query($conn, $user_logged_query);
    $security_logs_result = mysqli_query($conn, $security_logs_query);
    $file_access_logs_result = mysqli_query($conn, $file_access_logs_query);
    
    // Fetch results
    $file_count = mysqli_fetch_assoc($file_count_result)['total_files'];
    $user_count = mysqli_fetch_assoc($user_count_result)['total_users'];
    $admin_count = mysqli_fetch_assoc($admin_count_result)['total_admins'];
    $folder_count = mysqli_fetch_assoc($folder_count_result)['total_folders'];
    $admin_logged = mysqli_fetch_assoc($admin_logged_result)['admin_logged'];
    $user_logged = mysqli_fetch_assoc($user_logged_result)['user_logged'];
    $security_logs_count = mysqli_fetch_assoc($security_logs_result)['total_security_logs'];
    $file_access_logs_count = mysqli_fetch_assoc($file_access_logs_result)['total_file_access'];
    ?>
    
    <!-- Total Files -->
    <div class="col-md-3">
      <div class="dashboard-card p-4">
        <div class="card-icon" style="background-color: rgba(99, 102, 241, 0.1); color: var(--primary-color);">
          <i class="fas fa-file-alt"></i>
        </div>
        <h3 class="mb-2">Total Documents</h3>
        <p class="display-4 mb-0"><?php echo $file_count; ?></p>
        <a href="add_document.php" class="text-decoration-none">Manage Documents <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
    
    <!-- Total Users -->
    <div class="col-md-3">
      <div class="dashboard-card p-4">
        <div class="card-icon" style="background-color: rgba(16, 185, 129, 0.1); color: var(--secondary-color);">
          <i class="fas fa-users"></i>
        </div>
        <h3 class="mb-2">Total Users</h3>
        <p class="display-4 mb-0"><?php echo $user_count; ?></p>
        <a href="view_user.php" class="text-decoration-none">Manage users <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
    
    <!-- Total Admins -->
    <div class="col-md-3">
      <div class="dashboard-card p-4">
        <div class="card-icon" style="background-color: rgba(245, 158, 11, 0.1); color: var(--accent-color);">
          <i class="fas fa-user-shield"></i>
        </div>
        <h3 class="mb-2">Total Admins</h3>
        <p class="display-4 mb-0"><?php echo $admin_count; ?></p>
        <a href="view_admin.php" class="text-decoration-none">Manage admins <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
    
    <!-- Total Folders -->
    <div class="col-md-3">
      <div class="dashboard-card p-4">
        <div class="card-icon" style="background-color: rgba(239, 68, 68, 0.1); color: var(--danger-color);">
          <i class="fas fa-folder"></i>
        </div>
        <h3 class="mb-2">Total Folders</h3>
        <p class="display-4 mb-0"><?php echo $folder_count; ?></p>
        <a href="folder_management.php" class="text-decoration-none">Manage folders <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  
  <!-- Activity Cards -->
  <div class="row g-4 mt-2">
    <!-- Admin Logged Activities -->
    <div class="col-md-3">
      <div class="dashboard-card p-4">
        <div class="card-icon" style="background-color: rgba(99, 102, 241, 0.1); color: var(--primary-color);">
          <i class="fas fa-user-check"></i>
        </div>
        <h3 class="mb-2">Admin Logged Activities</h3>
        <p class="display-4 mb-0"><?php echo $admin_logged; ?></p>
        <a href="admin_log.php" class="text-decoration-none">View admin logs <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
    
    <!-- Users Logged Activities -->
    <div class="col-md-3">
      <div class="dashboard-card p-4">
        <div class="card-icon" style="background-color: rgba(16, 185, 129, 0.1); color: var(--secondary-color);">
          <i class="fas fa-user-clock"></i>
        </div>
        <h3 class="mb-2">Users Logged Activities</h3>
        <p class="display-4 mb-0"><?php echo $user_logged; ?></p>
        <a href="user_log.php" class="text-decoration-none">View user logs <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
    
    <!-- Security Logs -->
    <div class="col-md-3">
      <div class="dashboard-card p-4">
        <div class="card-icon" style="background-color: rgba(220, 38, 38, 0.1); color: #dc2626;">
          <i class="fas fa-shield-virus"></i>
        </div>
        <h3 class="mb-2">Security Events</h3>
        <p class="display-4 mb-0"><?php echo $security_logs_count; ?></p>
        <a href="security_logs.php" class="text-decoration-none">View security logs <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
    
    <!-- File Access Logs -->
    <div class="col-md-3">
      <div class="dashboard-card p-4">
        <div class="card-icon" style="background-color: rgba(79, 70, 229, 0.1); color: #4f46e5;">
          <i class="fas fa-file-medical"></i>
        </div>
        <h3 class="mb-2">File Access Events</h3>
        <p class="display-4 mb-0"><?php echo $file_access_logs_count; ?></p>
        <a href="file_log.php" class="text-decoration-none">View file access logs <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  
  <!-- Charts -->
  <div class="row g-4 mt-2">
    <div class="col-md-6">
      <div class="chart-container">
        <h4>System Overview</h4>
        <div class="chart-wrapper">
          <canvas id="fileChart"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="chart-container">
        <h4>User Distribution</h4>
        <div class="chart-wrapper">
          <canvas id="userChart"></canvas>
        </div>
        <div id="userChartLegend" class="custom-legend mt-2"></div>
      </div>
    </div>
  </div>
</div>

      <!-- Activity Timeline Chart -->
      <div class="row g-4 mt-2">
        <div class="col-12">
          <div class="chart-container">
            <h4>Activity Timeline</h4>
            <div class="chart-wrapper">
              <canvas id="timelineChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
                    
  <!-- Add Admin Modal -->
  <div class="modal fade" id="modalRegisterForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Admin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="create_Admin.php" method="POST">
          <div class="modal-body">
            <div class="mb-3">
              <label for="name" class="form-label">Your Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="department" class="form-label">Your Department</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-building"></i></span>
                <input type="text" class="form-control" id="department" name="department" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="admin_user" class="form-label">Your Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" id="admin_user" name="admin_user" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="admin_password" class="form-label">Your Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="admin_password" name="admin_password" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="admin_status" class="form-label">User Status</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                <input type="text" class="form-control" id="admin_status" name="admin_status" value="Admin" readonly>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" name="reg">Sign Up</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add User Modal -->
  <div class="modal fade" id="modalRegisterForm2" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add User Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="create_user.php" method="POST">
          <div class="modal-body">
            <div class="mb-3">
              <label for="user_name" class="form-label">Your Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="user_name" name="name" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="email_address" class="form-label">Your Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" id="email_address" name="email_address" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="user_department" class="form-label">Your Department</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-building"></i></span>
                <input type="text" class="form-control" id="user_department" name="department" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="user_password" class="form-label">Your Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="user_password" name="user_password" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="user_status" class="form-label">User Status</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                <input type="text" class="form-control" id="user_status" name="user_status" value="Employee" readonly>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" name="reguser">Sign Up</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Loader
    window.addEventListener('load', function() {
      setTimeout(function() {
        document.getElementById('loader').style.display = 'none';
      }, 500);
    });

    // Sidebar Toggle for Mobile
    document.getElementById('toggleSidebar').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('show');
    });

    // Security Features
    document.addEventListener('contextmenu', function(e) {
      e.preventDefault();
      showNotification("Right-click is disabled.");
    });

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

    function showNotification(message) {
      // Remove any existing notifications
      const existingNotification = document.querySelector('.custom-notification');
      if (existingNotification) {
        existingNotification.remove();
      }
      
      // Create new notification
      const notification = document.createElement('div');
      notification.className = 'custom-notification';
      notification.innerHTML = `
        <div class="notification-icon">
          <i class="fas fa-exclamation-circle"></i>
        </div>
        <p class="notification-message">${message}</p>
      `;
      document.body.appendChild(notification);
      
      // Remove notification after delay
      setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translate(-50%, -60%)';
        setTimeout(() => notification.remove(), 300);
      }, 3000);
    }

    // Chart Configuration
    const chartColors = {
      primary: 'rgba(99, 102, 241, 1)',
      primaryLight: 'rgba(99, 102, 241, 0.7)',
      secondary: 'rgba(16, 185, 129, 1)',
      secondaryLight: 'rgba(16, 185, 129, 0.7)',
      accent: 'rgba(245, 158, 11, 1)',
      accentLight: 'rgba(245, 158, 11, 0.7)',
      danger: 'rgba(239, 68, 68, 1)',
      dangerLight: 'rgba(239, 68, 68, 0.7)',
      gray: 'rgba(100, 116, 139, 0.8)',
      grayLight: 'rgba(100, 116, 139, 0.2)'
    };

    // Format numbers with commas
    function formatNumber(num) {
      return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    }

    // Calculate percentages
    function calculatePercentage(value, total) {
      return ((value / total) * 100).toFixed(1) + '%';
    }

    // Charts
    document.addEventListener('DOMContentLoaded', function() {
      // System Overview Chart (Horizontal Bar)
      const fileCtx = document.getElementById('fileChart').getContext('2d');
      
      // Data for the chart
      const fileData = {
        labels: ['Documents', 'Folders', 'Users', 'Admins'],
        datasets: [{
          label: 'Total Count',
          data: [
            <?php echo $file_count; ?>, 
            <?php echo $folder_count; ?>, 
            <?php echo $user_count; ?>, 
            <?php echo $admin_count; ?>
          ],
          backgroundColor: [
            chartColors.primaryLight,
            chartColors.dangerLight,
            chartColors.secondaryLight,
            chartColors.accentLight
          ],
          borderColor: [
            chartColors.primary,
            chartColors.danger,
            chartColors.secondary,
            chartColors.accent
          ],
          borderWidth: 2,
          borderRadius: 8,
          barThickness: 25
        }]
      };
      
      // Chart options
      const fileOptions = {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.9)',
            titleColor: '#1e293b',
            bodyColor: '#1e293b',
            bodyFont: {
              family: "'Poppins', sans-serif",
              size: 13
            },
            titleFont: {
              family: "'Poppins', sans-serif",
              size: 15,
              weight: 'bold'
            },
            padding: 12,
            boxPadding: 8,
            usePointStyle: true,
            borderColor: 'rgba(0, 0, 0, 0.1)',
            borderWidth: 1,
            callbacks: {
              label: function(context) {
                return `Total: ${formatNumber(context.raw)}`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              display: false
            },
            ticks: {
              font: {
                family: "'Poppins', sans-serif",
                size: 12,
                weight: '500'
              }
            }
          },
          x: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              font: {
                family: "'Poppins', sans-serif",
                size: 12
              }
            }
          }
        },
        animation: {
          delay: function(context) {
            return context.dataIndex * 100;
          },
          easing: 'easeOutQuart',
          duration: 1500
        }
      };
      
      // Create the chart
      const fileChart = new Chart(fileCtx, {
        type: 'bar',
        data: fileData,
        options: fileOptions
      });

      // User Distribution Chart (Doughnut)
      const userCtx = document.getElementById('userChart').getContext('2d');
      
      // Calculate total users for percentage
      const totalUsers = <?php echo $admin_count + $user_count; ?>;
      
      // Data for the chart
      const userData = {
        labels: ['Admins', 'Users'],
        datasets: [{
          data: [<?php echo $admin_count; ?>, <?php echo $user_count; ?>],
          backgroundColor: [
            chartColors.accentLight,
            chartColors.secondaryLight
          ],
          borderColor: [
            chartColors.accent,
            chartColors.secondary
          ],
          borderWidth: 2,
          hoverOffset: 15
        }]
      };
      
      // Chart options
      const userOptions = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.9)',
            titleColor: '#1e293b',
            bodyColor: '#1e293b',
            bodyFont: {
              family: "'Poppins', sans-serif",
              size: 13
            },
            titleFont: {
              family: "'Poppins', sans-serif",
              size: 15,
              weight: 'bold'
            },
            padding: 12,
            boxPadding: 8,
            usePointStyle: true,
            borderColor: 'rgba(0, 0, 0, 0.1)',
            borderWidth: 1,
            callbacks: {
              label: function(context) {
                const value = context.raw;
                const percentage = calculatePercentage(value, totalUsers);
                return `${context.label}: ${value} (${percentage})`;
              }
            }
          }
        },
        animation: {
          animateRotate: true,
          animateScale: true,
          duration: 2000,
          easing: 'easeOutQuart'
        }
      };
      
      // Create the chart
      const userChart = new Chart(userCtx, {
        type: 'doughnut',
        data: userData,
        options: userOptions
      });

      // Custom Legend for User Chart
      const userChartLegend = document.getElementById('userChartLegend');
      const legendHTML = userData.labels.map((label, index) => {
        const color = userData.datasets[0].backgroundColor[index];
        const value = userData.datasets[0].data[index];
        const percentage = calculatePercentage(value, totalUsers);
        return `
          <div class="legend-item">
            <span class="legend-color" style="background-color: ${color}"></span>
            <span>${label}: ${value} (${percentage})</span>
          </div>
        `;
      }).join('');
      userChartLegend.innerHTML = legendHTML;

      // Activity Timeline Chart (Line)
      const timelineCtx = document.getElementById('timelineChart').getContext('2d');
      
      // Generate some sample data for the timeline
      // In a real application, you would fetch this from your database
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
      
      // Generate random data for admin and user logins
      function generateRandomData(min, max, count) {
        const data = [];
        for (let i = 0; i < count; i++) {
          data.push(Math.floor(Math.random() * (max - min + 1)) + min);
        }
        return data;
      }
      
      const adminLoginData = generateRandomData(5, 20, 6);
      const userLoginData = generateRandomData(10, 30, 6);
      
      // Data for the chart
      const timelineData = {
        labels: months,
        datasets: [
          {
            label: 'Admin Logins',
            data: adminLoginData,
            borderColor: chartColors.primary,
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            borderWidth: 3,
            pointBackgroundColor: chartColors.primary,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
            tension: 0.3,
            fill: true
          },
          {
            label: 'User Logins',
            data: userLoginData,
            borderColor: chartColors.secondary,
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 3,
            pointBackgroundColor: chartColors.secondary,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
            tension: 0.3,
            fill: true
          }
        ]
      };
      
      // Chart options
      const timelineOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            align: 'end',
            labels: {
              boxWidth: 15,
              usePointStyle: true,
              pointStyle: 'circle',
              padding: 20,
              font: {
                family: "'Poppins', sans-serif",
                size: 12,
                weight: '500'
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.9)',
            titleColor: '#1e293b',
            bodyColor: '#1e293b',
            bodyFont: {
              family: "'Poppins', sans-serif",
              size: 13
            },
            titleFont: {
              family: "'Poppins', sans-serif",
              size: 15,
              weight: 'bold'
            },
            padding: 12,
            boxPadding: 8,
            usePointStyle: true,
            borderColor: 'rgba(0, 0, 0, 0.1)',
            borderWidth: 1
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              font: {
                family: "'Poppins', sans-serif",
                size: 12
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                family: "'Poppins', sans-serif",
                size: 12,
                weight: '500'
              }
            }
          }
        },
        animation: {
          delay: function(context) {
            return context.dataIndex * 100 + context.datasetIndex * 300;
          },
          easing: 'easeOutQuart',
          duration: 1500
        }
      };
      
      // Create the chart
      const timelineChart = new Chart(timelineCtx, {
        type: 'line',
        data: timelineData,
        options: timelineOptions
      });
    });
  </script>
</body>
</html>