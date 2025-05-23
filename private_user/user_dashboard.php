<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION["email_address"])) {
    header("location:../login.html");
    exit();
}

// Include database connection
require_once("include/connection.php");

// Get user ID from session
$user_id = $_SESSION["email_address"]; // This might be misnamed in your system 

// Query database to get user details
$query = "SELECT * FROM login_user WHERE id = ?"; // Adjust table name and column as needed
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id); // "i" for integer, change if needed
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_email = $row['email_address']; // Get actual email from database
} else {
    // User not found in database
    session_destroy();
    header("location:../login.html");
    exit();
}

// Close statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard - BitKeep Management System</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    :root {
      --primary-color: #5469d4;
      --primary-light: #e6ebff;
      --primary-dark: #3a4aa3;
      --secondary-color: #F0B56F;
      --dark-color: #2d3748;
      --light-color: #f8f9fa;
      --success-color: #38a169;
      --warning-color: #e9b949;
      --danger-color: #e53e3e;
      --pending-color: #f59e0b;
      --approved-color: #10b981;
      --rejected-color: #ef4444;
      --expired-color: #9ca3af;
      --expiring-color: #f97316;
      --sidebar-width: 250px;
      --navbar-height: 70px;
      --border-radius: 10px;
      --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      --transition: all 0.3s ease;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f7fafc;
      overflow-x: hidden;
      padding-top: var(--navbar-height);
      color: #4a5568;
    }
    
    /* Sidebar Styles */
    .sidebar {
      position: fixed;
      top: var(--navbar-height);
      left: 0;
      height: calc(100vh - var(--navbar-height));
      width: var(--sidebar-width);
      background-color: white;
      box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
      z-index: 999;
      transition: var(--transition);
      overflow-y: auto;
    }
    
    .sidebar-header {
      padding: 20px;
      border-bottom: 1px solid #edf2f7;
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
      color: var(--dark-color);
      text-decoration: none;
      transition: var(--transition);
      border-left: 4px solid transparent;
    }
    
    .sidebar-item:hover, .sidebar-item.active {
      background-color: #f8fafc;
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
      background-color: #edf2f7;
      margin: 10px 20px;
    }
    
    .sidebar-footer {
      padding: 15px 20px;
      border-top: 1px solid #edf2f7;
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
      background-color: #f8fafc;
      border-radius: 8px;
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
    }
    
    .user-role {
      font-size: 0.75rem;
      color: #718096;
    }
    
    /* Main Content Styles */
    .main-content {
      margin-left: var(--sidebar-width);
      padding: 30px;
      transition: var(--transition);
      min-height: calc(100vh - var(--navbar-height));
    }
    
    /* Navbar Styles */
    .navbar {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      background-color: white !important;
      padding: 0 30px;
      height: var(--navbar-height);
      margin-left: var(--sidebar-width);
      transition: var(--transition);
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
      color: var(--dark-color);
      font-size: 1.25rem;
      cursor: pointer;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 6px;
      transition: var(--transition);
    }
    
    .sidebar-toggle:hover {
      background-color: #f8fafc;
    }
    
    /* Dashboard Styles */
    .dashboard-header {
      background-color: white;
      border-radius: var(--border-radius);
      padding: 25px;
      margin-bottom: 25px;
      box-shadow: var(--box-shadow);
      position: relative;
      overflow: hidden;
    }
    
    .dashboard-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
    }
    
    .dashboard-title {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--dark-color);
      margin-bottom: 5px;
    }
    
    .dashboard-subtitle {
      font-size: 1.1rem;
      color: #718096;
      margin-bottom: 15px;
    }
    
    .dashboard-stats {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 20px;
    }
    
    .stat-card {
      background-color: white;
      border-radius: var(--border-radius);
      padding: 20px;
      flex: 1;
      min-width: 200px;
      box-shadow: var(--box-shadow);
      display: flex;
      align-items: center;
      transition: var(--transition);
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      font-size: 1.5rem;
    }
    
    .stat-pending {
      background-color: #fff7ed;
      color: var(--pending-color);
    }
    
    .stat-approved {
      background-color: #ecfdf5;
      color: var(--approved-color);
    }
    
    .stat-rejected {
      background-color: #fef2f2;
      color: var(--rejected-color);
    }
    
    .stat-expired {
      background-color: #f3f4f6;
      color: var(--expired-color);
    }
    
    .stat-info {
      flex: 1;
    }
    
    .stat-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--dark-color);
      margin-bottom: 5px;
    }
    
    .stat-label {
      font-size: 0.85rem;
      color: #718096;
    }
    
    .content-card {
      background-color: white;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      margin-bottom: 25px;
      overflow: hidden;
      transition: var(--transition);
    }
    
    .content-card:hover {
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
      padding: 20px 25px;
      border-bottom: 1px solid #edf2f7;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .card-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: var(--dark-color);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .card-title i {
      color: var(--primary-color);
    }
    
    .card-body {
      padding: 0;
    }
    
    .table-responsive {
      overflow-x: auto;
    }
    
    .dashboard-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .dashboard-table th {
      background-color: #f8fafc;
      color: #64748b;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.5px;
      padding: 15px 20px;
      text-align: left;
      border-bottom: 1px solid #edf2f7;
    }
    
    .dashboard-table td {
      padding: 15px 20px;
      border-bottom: 1px solid #edf2f7;
      vertical-align: middle;
    }
    
    .dashboard-table tr:last-child td {
      border-bottom: none;
    }
    
    .dashboard-table tr:hover {
      background-color: #f8fafc;
    }
    
    .folder-name {
      font-weight: 500;
      color: var(--dark-color);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .folder-icon {
      color: var(--primary-color);
    }
    
    .reason-text {
      max-width: 300px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .reason-text:hover {
      white-space: normal;
      overflow: visible;
    }
    
    .status-badge {
      display: inline-flex;
      align-items: center;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      gap: 5px;
    }
    
    .status-pending {
      background-color: #fff7ed;
      color: var(--pending-color);
    }
    
    .status-approved {
      background-color: #ecfdf5;
      color: var(--approved-color);
    }
    
    .status-rejected {
      background-color: #fef2f2;
      color: var(--rejected-color);
    }
    
    .status-expired {
      background-color: #f3f4f6;
      color: var(--expired-color);
    }
    
    .status-expiring {
      background-color: #fff7ed;
      color: var(--expiring-color);
    }
    
    .expiry-date {
      font-size: 0.85rem;
      line-height: 1.3;
    }
    
    .expiry-time {
      color: #718096;
      font-size: 0.75rem;
      display: block;
      margin-top: 2px;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 6px;
      font-weight: 500;
      transition: var(--transition);
      text-decoration: none;
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      color: white;
      border: none;
    }
    
    .btn-primary:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(84, 105, 212, 0.25);
    }
    
    .btn-outline {
      background-color: transparent;
      border: 1px solid #e2e8f0;
      color: #64748b;
    }
    
    .btn-outline:hover {
      border-color: var(--primary-color);
      color: var(--primary-color);
      transform: translateY(-2px);
    }
    
    .empty-state {
      padding: 50px 20px;
      text-align: center;
    }
    
    .empty-icon {
      font-size: 3rem;
      color: #cbd5e0;
      margin-bottom: 15px;
    }
    
    .empty-text {
      font-size: 1.1rem;
      color: #718096;
      margin-bottom: 20px;
    }
    
    /* Loader */
    #loader {
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      z-index: 9999;
      background: rgba(249,249,249,0.95);
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
    
    /* Responsive */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .main-content, .navbar {
        margin-left: 0;
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .dashboard-stats {
        flex-direction: column;
      }
    }
    
    @media (max-width: 768px) {
      .dashboard-table thead {
        display: none;
      }
      
      .dashboard-table, .dashboard-table tbody, .dashboard-table tr, .dashboard-table td {
        display: block;
        width: 100%;
      }
      
      .dashboard-table tr {
        margin-bottom: 15px;
        border: 1px solid #edf2f7;
        border-radius: var(--border-radius);
      }
      
      .dashboard-table td {
        text-align: right;
        padding-left: 50%;
        position: relative;
      }
      
      .dashboard-table td:before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        text-align: left;
        font-weight: 600;
      }
      
      .folder-name, .reason-text {
        text-align: right;
      }
    }
    
    @media (max-width: 576px) {
      .navbar {
        padding: 0 15px;
      }
      
      .main-content {
        padding: 20px 15px;
      }
      
      .dashboard-header {
        padding: 20px;
      }
      
      .dashboard-title {
        font-size: 1.5rem;
      }
    }
  </style>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script type="text/javascript">
    $(window).on('load', function(){
      setTimeout(function(){
            $('#loader').fadeOut('slow');  
        }, 500);
    });
  </script>
</head>

<body>
  <!-- Loading screen -->
  <div id="loader">
    <div class="loader-content">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
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
  <div class="sidebar">
    <div class="user-info">
      <div class="user-avatar">
        <i class="fas fa-user"></i>
      </div>
      <div class="user-details">
        <div class="user-name"><?php echo ucwords(htmlentities($user_email)); ?></div>
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
      <a href="history_log.php" class="sidebar-item">
        <i class="fas fa-history"></i> User Logs
      </a>
      <a href="request_folder.php" class="sidebar-item">
        <i class="fas fa-folder-plus"></i> Request Folder
      </a>
      <a href="user_dashboard.php" class="sidebar-item active">
        <i class="fas fa-folder"></i> My Folders
      </a>
      
      <div class="sidebar-divider"></div>
      
      <a href="Logout.php" class="sidebar-item">
        <i class="fas fa-sign-out-alt"></i> Log Out
      </a>
    </div>
    
    <div class="sidebar-footer">
      <small class="text-muted">© 2025 BitKeep Management System</small>
    </div>
  </div>

  <!-- Main content -->
  <div class="main-content">
    <!-- Dashboard header -->
    <div class="dashboard-header">
      <h1 class="dashboard-title">My Folders</h1>
      <p class="dashboard-subtitle">Manage your folder requests and access approved folders</p>
      
      <?php
      // Get folder request statistics
      require_once("include/connection.php");
      
      $stmt = $conn->prepare("SELECT 
          COUNT(*) as total,
          SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
          SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
          SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
          SUM(CASE WHEN is_expired = 1 THEN 1 ELSE 0 END) as expired
          FROM folder_requests 
          WHERE user_email = ?");
      $stmt->bind_param("s", $user_email);
      $stmt->execute();
      $stats = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      ?>
      
      <div class="dashboard-stats">
        <div class="stat-card">
          <div class="stat-icon stat-pending">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-info">
            <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
            <div class="stat-label">Pending Requests</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon stat-approved">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-info">
            <div class="stat-value"><?php echo $stats['approved'] ?? 0; ?></div>
            <div class="stat-label">Approved Folders</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon stat-rejected">
            <i class="fas fa-times-circle"></i>
          </div>
          <div class="stat-info">
            <div class="stat-value"><?php echo $stats['rejected'] ?? 0; ?></div>
            <div class="stat-label">Rejected Requests</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon stat-expired">
            <i class="fas fa-calendar-times"></i>
          </div>
          <div class="stat-info">
            <div class="stat-value"><?php echo $stats['expired'] ?? 0; ?></div>
            <div class="stat-label">Expired Folders</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Folder requests table -->
    <div class="content-card">
      <div class="card-header">
        <h2 class="card-title">
          <i class="fas fa-folder"></i> Your Folder Requests
        </h2>
        <a href="request_folder.php" class="btn btn-primary">
          <i class="fas fa-plus"></i> New Request
        </a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Requested Folder</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Assigned Folder</th>
                <th>Request Date</th>
                <th>Expiry Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Get current date for expiry calculations
              $current_date = date('Y-m-d H:i:s');
              
              // Use prepared statement to prevent SQL injection
              $stmt = $conn->prepare("SELECT fr.*, f.FOLDER_NAME 
                  FROM folder_requests fr
                  LEFT JOIN folders f ON fr.assigned_folder_id = f.folder_id
                  WHERE fr.user_email = ? 
                  ORDER BY fr.request_date DESC");
              $stmt->bind_param("s", $user_email);
              $stmt->execute();
              $result = $stmt->get_result();
              
              if ($result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      // Calculate days until expiry for approved folders
                      $expiry_status = '';
                      $days_remaining = 0;
                      $is_expired = $row['is_expired'] == 1;
                      
                      if (!empty($row['expiry_date'])) {
                          $expiry_date = new DateTime($row['expiry_date']);
                          $current = new DateTime($current_date);
                          $interval = $current->diff($expiry_date);
                          $days_remaining = $interval->invert ? 0 : $interval->days;
                          
                          if ($is_expired || $interval->invert) {
                              $expiry_status = 'expired';
                          } elseif ($days_remaining <= 1) {
                              $expiry_status = 'expiring';
                          }
                      }
                      ?>
                      <tr>
                          <td data-label="Requested Folder">
                              <div class="folder-name">
                                  <i class="fas fa-folder folder-icon"></i>
                                  <?php echo htmlspecialchars($row['requested_folder_name']); ?>
                              </div>
                          </td>
                          <td data-label="Reason">
                              <div class="reason-text" title="<?php echo htmlspecialchars($row['reason']); ?>">
                                  <?php echo htmlspecialchars($row['reason']); ?>
                              </div>
                          </td>
                          <td data-label="Status">
                              <?php 
                              $status = strtolower($row['status']);
                              $statusIcon = '';
                              $statusClass = '';
                              $statusText = ucfirst($status);
                              
                              if ($status == 'approved' && $expiry_status == 'expired') {
                                  $statusIcon = 'calendar-times';
                                  $statusClass = 'status-expired';
                                  $statusText = 'Expired';
                              } elseif ($status == 'approved' && $expiry_status == 'expiring') {
                                  $statusIcon = 'exclamation-circle';
                                  $statusClass = 'status-expiring';
                                  $statusText = 'Expiring Soon';
                              } elseif ($status == 'approved') {
                                  $statusIcon = 'check-circle';
                                  $statusClass = 'status-approved';
                              } elseif ($status == 'rejected') {
                                  $statusIcon = 'times-circle';
                                  $statusClass = 'status-rejected';
                              } elseif ($status == 'pending') {
                                  $statusIcon = 'clock';
                                  $statusClass = 'status-pending';
                              } else {
                                  $statusIcon = 'question-circle';
                                  $statusClass = 'status-pending';
                              }
                              ?>
                              <span class="status-badge <?php echo $statusClass; ?>">
                                  <i class="fas fa-<?php echo $statusIcon; ?>"></i>
                                  <?php echo $statusText; ?>
                              </span>
                          </td>
                          <td data-label="Assigned Folder">
                              <?php 
                              if (isset($row['assigned_folder_id']) && !empty($row['assigned_folder_id']) && isset($row['FOLDER_NAME'])) {
                                  echo '<div class="folder-name">';
                                  echo '<i class="fas fa-folder-open folder-icon"></i>';
                                  echo htmlspecialchars($row['FOLDER_NAME']);
                                  echo '</div>';
                              } else {
                                  if ($status == 'approved') {
                                      echo '<span class="status-badge status-pending">';
                                      echo '<i class="fas fa-hourglass-half"></i> Pending Assignment';
                                      echo '</span>';
                                  } else {
                                      echo '<span class="text-muted">Not assigned yet</span>';
                                  }
                              }
                              ?>
                          </td>
                          <td data-label="Request Date">
                              <?php echo date('M d, Y g:i A', strtotime($row['request_date'])); ?>
                          </td>
                          <td data-label="Expiry Date">
                              <?php 
                              if (!empty($row['expiry_date']) && $status != 'rejected' && $status != 'pending') {
                                  if ($expiry_status == 'expired') {
                                      echo '<span class="status-badge status-expired">';
                                      echo '<i class="fas fa-calendar-times"></i> Expired';
                                      echo '</span>';
                                  } elseif ($expiry_status == 'expiring') {
                                      echo '<span class="status-badge status-expiring">';
                                      echo '<i class="fas fa-exclamation-circle"></i> ';
                                      echo '<div class="expiry-date">';
                                      echo date('M d, Y', strtotime($row['expiry_date']));
                                      echo '<span class="expiry-time">' . date('g:i A', strtotime($row['expiry_date'])) . '</span>';
                                      echo '</div>';
                                      echo '</span>';
                                  } else {
                                      echo '<div class="expiry-date">';
                                      echo date('M d, Y', strtotime($row['expiry_date']));
                                      echo '<span class="expiry-time">' . date('g:i A', strtotime($row['expiry_date'])) . '</span>';
                                      echo '</div>';
                                  }
                              } else {
                                  echo '<span class="text-muted">N/A</span>';
                              }
                              ?>
                          </td>
                          <td data-label="Action">
                              <?php 
                              // Fix for the action column
                              if ($status == 'approved' && !$is_expired && isset($row['assigned_folder_id']) && !empty($row['assigned_folder_id'])) {
                                  echo '<a href="manage_folder.php?folder_id=' . urlencode($row['assigned_folder_id']) . '" class="btn btn-primary">';
                                  echo '<i class="fas fa-folder-open"></i> Open';
                                  echo '</a>';
                              } elseif ($expiry_status == 'expired') {
                                  echo '<a href="request_folder.php" class="btn btn-outline">';
                                  echo '<i class="fas fa-sync"></i> Request again';
                                  echo '</a>';
                              } elseif ($status == 'pending') {
                                  echo '<span class="status-badge status-pending">';
                                  echo '<i class="fas fa-clock"></i> Awaiting Approval';
                                  echo '</span>';
                              } elseif ($status == 'rejected') {
                                  echo '<span class="status-badge status-rejected">';
                                  echo '<i class="fas fa-ban"></i> Rejected';
                                  echo '</span>';
                              } else {
                                  echo '<span class="status-badge status-pending">';
                                  echo '<i class="fas fa-hourglass-half"></i> Processing';
                                  echo '</span>';
                              }
                              ?>
                          </td>
                      </tr>
                      <?php
                  }
              } else {
                  ?>
                  <tr>
                      <td colspan="7">
                          <div class="empty-state">
                              <div class="empty-icon">
                                  <i class="fas fa-folder-open"></i>
                              </div>
                              <div class="empty-text">No folder requests found</div>
                              <a href="request_folder.php" class="btn btn-primary">
                                  <i class="fas fa-plus"></i> Request a New Folder
                              </a>
                          </div>
                      </td>
                  </tr>
                  <?php
              }
              
              // Close statement
              $stmt->close();
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <footer class="text-center text-muted mt-4">
      <p class="small mb-0">BitKeep Management System &copy; 2025. All rights reserved.</p>
    </footer>
  </div>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  
  <script>
    $(document).ready(function() {
      // Sidebar Toggle
      $('#sidebarToggle').click(function() {
        $('.sidebar').toggleClass('active');
      });
      
      // Close sidebar on small screens when clicking outside
      $(document).on('click touchstart', function(e) {
        var sidebar = $('.sidebar');
        var sidebarToggle = $('#sidebarToggle');
        
        if (!sidebar.is(e.target) && sidebar.has(e.target).length === 0 &&
            !sidebarToggle.is(e.target) && sidebarToggle.has(e.target).length === 0 &&
            sidebar.hasClass('active') && window.innerWidth < 992) {
          sidebar.removeClass('active');
        }
      });
      
      // Responsive behavior
      function checkSize() {
        if (window.innerWidth >= 992) {
          $('.sidebar').removeClass('active');
        }
      }
      
      // Check on resize
      $(window).resize(function() {
        checkSize();
      });
      
      // Check on load
      checkSize();
      
      // Initialize tooltips
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      });
      
      // Expand reason text on hover
      $('.reason-text').hover(function() {
        $(this).css('white-space', 'normal');
      }, function() {
        $(this).css('white-space', 'nowrap');
      });
    });
  </script>
</body>
</html>