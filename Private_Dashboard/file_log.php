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
  <title>BitKeep Management System - File Access Logs</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap core CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css"/>
  <!-- Toastify -->
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

  <style>
    :root {
      --primary-color: #3498db;
      --primary-dark: #2980b9;
      --primary-light: #e1f0fa;
      --secondary-color: #2c3e50;
      --success-color: #2ecc71;
      --danger-color: #e74c3c;
      --warning-color: #f39c12;
      --light-color: #f8f9fa;
      --dark-color: #343a40;
      --sidebar-width: 280px;
      --sidebar-collapsed-width: 70px;
      --border-radius: 10px;
      --box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      --transition: all 0.3s ease;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 0;
      color: #333;
      overflow-x: hidden;
    }
    
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: var(--sidebar-width);
      height: 100vh;
      background: linear-gradient(to bottom, #fff, #f8f9fa);
      color: var(--secondary-color);
      overflow-y: auto;
      z-index: 1000;
      transition: var(--transition);
      box-shadow: var(--box-shadow);
    }
    
    .sidebar-header {
      padding: 20px;
      text-align: center;
      background-color: white;
      border-bottom: 1px solid #eaeaea;
    }
    
    .sidebar-logo {
      max-width: 150px;
      margin-bottom: 15px;
      transition: var(--transition);
    }
    
    .sidebar .nav-link {
      padding: 12px 20px;
      color: var(--secondary-color);
      border-radius: var(--border-radius);
      margin: 5px 10px;
      transition: var(--transition);
      display: flex;
      align-items: center;
      font-weight: 500;
    }
    
    .sidebar .nav-link i {
      margin-right: 12px;
      width: 20px;
      text-align: center;
      font-size: 1.1rem;
      color: #777;
      transition: var(--transition);
    }
    
    .sidebar .nav-link:hover {
      background-color: var(--primary-light);
      color: var(--primary-color);
      transform: translateX(5px);
    }
    
    .sidebar .nav-link:hover i {
      color: var(--primary-color);
    }
    
    .sidebar .nav-link.active {
      background-color: var(--primary-color);
      color: white;
      box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
    }
    
    .sidebar .nav-link.active i {
      color: white;
    }
    
    .main-content {
      margin-left: var(--sidebar-width);
      padding: 20px 30px;
      transition: var(--transition);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .top-navbar {
      background-color: white;
      box-shadow: var(--box-shadow);
      padding: 15px 25px;
      margin-bottom: 25px;
      border-radius: var(--border-radius);
      display: flex;
      justify-content: space-between;
      align-items: center;
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
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      transform: translateY(-3px);
    }
    
    .card-header {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      color: white;
      padding: 18px 25px;
      font-weight: 600;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: none;
    }
    
    .card-body {
      padding: 25px;
    }
    
    .table-responsive {
      overflow-x: auto;
      border-radius: var(--border-radius);
    }
    
    .table {
      width: 100%;
      margin-bottom: 0;
      color: #212529;
      vertical-align: top;
      border-color: #dee2e6;
    }
    
    .table th {
      background-color: #f8f9fa;
      color: var(--secondary-color);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
      padding: 15px;
      vertical-align: middle;
      border-bottom: 2px solid #dee2e6;
    }
    
    .table td {
      padding: 15px;
      vertical-align: middle;
      border-bottom: 1px solid #eee;
    }
    
    .table tbody tr {
      transition: var(--transition);
    }
    
    .table tbody tr:hover {
      background-color: rgba(52, 152, 219, 0.05);
    }
    
    .table-striped > tbody > tr:nth-of-type(odd) > * {
      background-color: rgba(0, 0, 0, 0.02);
    }
    
    .btn {
      border-radius: 6px;
      font-weight: 500;
      padding: 8px 16px;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }
    
    .btn-sm {
      padding: 5px 12px;
      font-size: 0.875rem;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      border: none;
      box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(52, 152, 219, 0.4);
    }
    
    .user-welcome {
      display: flex;
      align-items: center;
    }
    
    .user-avatar {
      width: 42px;
      height: 42px;
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 12px;
      font-weight: bold;
      font-size: 1.2rem;
      box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
    }
    
    .footer {
      background-color: white;
      padding: 15px 20px;
      text-align: center;
      font-size: 14px;
      color: #6c757d;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      margin-top: auto;
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
      flex-direction: column;
    }
    
    .loader-spinner {
      width: 50px;
      height: 50px;
      border: 5px solid var(--primary-light);
      border-top: 5px solid var(--primary-color);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-bottom: 15px;
    }
    
    .loader-text {
      color: var(--primary-color);
      font-weight: 500;
      margin-top: 10px;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    /* Status badges */
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }
    
    .status-preview {
      background-color: #d1ecf1;
      color: #0c5460;
      border: 1px solid #bee5eb;
    }
    
    .status-download {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    /* Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
      animation: fadeIn 0.5s ease forwards;
    }
    
    /* Modal styles */
    .modal-content {
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      border: none;
    }
    
    .modal-header {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      color: white;
      border-bottom: none;
      border-radius: var(--border-radius) var(--border-radius) 0 0;
    }
    
    .modal-title {
      font-weight: 600;
    }
    
    .modal-footer {
      border-top: 1px solid #eaeaea;
    }
    
    /* DataTables customization */
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter {
      margin-bottom: 15px;
    }
    
    .dataTables_wrapper .dataTables_length select {
      padding: 5px 10px;
      border-radius: 6px;
      border: 1px solid #ddd;
    }
    
    .dataTables_wrapper .dataTables_filter input {
      padding: 6px 12px;
      border-radius: 6px;
      border: 1px solid #ddd;
      margin-left: 5px;
    }
    
    .dataTables_wrapper .dataTables_info {
      padding-top: 15px;
      color: #6c757d;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
      padding: 5px 12px;
      border-radius: 6px;
      margin: 0 3px;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: var(--primary-color);
      color: white !important;
      border: 1px solid var(--primary-color);
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      background: var(--primary-light);
      color: var(--primary-color) !important;
      border: 1px solid var(--primary-light);
    }
    
    /* File type icons */
    .file-icon {
      width: 32px;
      height: 32px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 10px;
    }
    
    .file-icon.pdf {
      background-color: rgba(231, 76, 60, 0.1);
      color: #e74c3c;
    }
    
    .file-icon.doc {
      background-color: rgba(52, 152, 219, 0.1);
      color: #3498db;
    }
    
    .file-icon.xls {
      background-color: rgba(46, 204, 113, 0.1);
      color: #2ecc71;
    }
    
    .file-icon.img {
      background-color: rgba(155, 89, 182, 0.1);
      color: #9b59b6;
    }
    
    .file-icon.txt {
      background-color: rgba(149, 165, 166, 0.1);
      color: #95a5a6;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
      :root {
        --sidebar-width: var(--sidebar-collapsed-width);
      }
      
      .sidebar .nav-link {
        padding: 12px;
        justify-content: center;
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
      
      .sidebar-header h5 {
        display: none;
      }
      
      .main-content {
        margin-left: var(--sidebar-collapsed-width);
        padding: 15px;
      }
    }
    
    @media (max-width: 768px) {
      .top-navbar {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
      }
      
      .user-welcome {
        margin-top: 10px;
      }
    }
    
    @media (max-width: 576px) {
      .main-content {
        margin-left: 0;
        padding: 10px;
      }
      
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.show {
        transform: translateX(0);
      }
      
      .mobile-toggle {
        display: block;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1001;
        background-color: var(--primary-color);
        color: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--box-shadow);
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
      Toastify({
        text: message,
        duration: 3000,
        close: true,
        gravity: "top",
        position: "center",
        backgroundColor: "#e74c3c",
        stopOnFocus: true,
      }).showToast();
    }
  </script>
</head>

<body>
  <!-- Loader -->
  <div id="loader">
    <div class="loader-spinner"></div>
    <div class="loader-text">Loading BitKeep Management...</div>
  </div>

  <!-- Mobile menu toggle -->
  <button class="mobile-toggle d-md-none" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <img src="img/image1.svg" alt="BitKeep Logo" class="sidebar-logo">
      <h5 class="text-primary">BitKeep Management</h5>
    </div>
    <ul class="nav flex-column mt-3">
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
        <a class="nav-link" href="view_admin.php">
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
          <span>Document</span>
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
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="file_log.php">
          <i class="fas fa-file-alt"></i>
          <span>File Access Logs</span>
        </a>
      </li>
    </ul>
  </div>

  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <div class="top-navbar">
      <div class="d-flex align-items-center">
        <h4 class="mb-0 fw-bold">
          <i class="fas fa-file-alt me-2 text-primary"></i>
          File Access Logs
        </h4>
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
            <div class="fw-bold"><?php echo ucwords(htmlentities($id)); ?></div>
          </div>
        </div>
        <a href="logout.php" class="btn btn-outline-primary btn-sm">
          <i class="fas fa-sign-out-alt me-1"></i> Sign Out
        </a>
      </div>
    </div>

    <!-- Action buttons -->
    <div class="mb-4">
      <a href="dashboard.php" class="btn btn-primary">
        <i class="fas fa-home me-2"></i> Back to Dashboard
      </a>
      <a href="#" class="btn btn-outline-secondary ms-2" id="refreshBtn">
        <i class="fas fa-sync-alt me-2"></i> Refresh Data
      </a>
      <a href="#" class="btn btn-outline-success ms-2" id="exportBtn">
        <i class="fas fa-file-export me-2"></i> Export to CSV
      </a>
    </div>

    <!-- Content Card -->
    <div class="content-card fade-in">
      <div class="card-header">
        <div>
          <i class="fas fa-file-alt me-2"></i>
          File Access Log History
        </div>
        <?php
        require_once("include/connection.php");
        $query = mysqli_query($conn,"SELECT COUNT(*) as total FROM file_access_logs") or die (mysqli_error($conn));
        $row = mysqli_fetch_array($query);
        $total = $row['total'];
        ?>
        <div>
          <span class="badge bg-light text-dark"><?php echo $total; ?> Total Records</span>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="fileAccessTable" class="table table-striped table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>File</th>
                <th>User</th>
                <th>Access Type</th>
                <th>Access Time</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              require_once("include/connection.php");
              $query = mysqli_query($conn,"SELECT l.id, l.file_id, l.user_email, l.access_type, l.access_time, 
                                           f.name, f.file_path, f.file_type 
                                           FROM file_access_logs l
                                           LEFT JOIN folder_files f ON l.file_id = f.id
                                           ORDER BY l.access_time DESC") or die (mysqli_error($conn));
              while($file=mysqli_fetch_array($query)){
                $id = $file['id'];
                $file_id = $file['file_id'];
                $file_name = $file['name'];
                $file_path = $file['file_path'];
                $file_type = $file['file_type'] ?? '';
                $user_email = $file['user_email'];
                $access_type = $file['access_type'];
                $access_time = $file['access_time'];
                
                // Determine file icon class based on file type
                $icon_class = 'fa-file';
                $type_class = '';
                
                if ($file_type) {
                    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    switch ($file_extension) {
                        case 'pdf':
                            $icon_class = 'fa-file-pdf';
                            $type_class = 'pdf';
                            break;
                        case 'doc':
                        case 'docx':
                            $icon_class = 'fa-file-word';
                            $type_class = 'doc';
                            break;
                        case 'xls':
                        case 'xlsx':
                            $icon_class = 'fa-file-excel';
                            $type_class = 'xls';
                            break;
                        case 'jpg':
                        case 'jpeg':
                        case 'png':
                        case 'gif':
                            $icon_class = 'fa-file-image';
                            $type_class = 'img';
                            break;
                        case 'txt':
                        case 'csv':
                            $icon_class = 'fa-file-alt';
                            $type_class = 'txt';
                            break;
                    }
                }
              ?>
              <tr>
                <td><?php echo htmlspecialchars($id); ?></td>
                <td>
                  <?php if($file_name): ?>
                  <div class="d-flex align-items-center">
                    <div class="file-icon <?php echo $type_class; ?>">
                      <i class="fas <?php echo $icon_class; ?>"></i>
                    </div>
                    <div>
                      <div class="fw-medium"><?php echo htmlspecialchars($file_name); ?></div>
                      <small class="text-muted">ID: <?php echo htmlspecialchars($file_id); ?></small>
                    </div>
                  </div>
                  <?php else: ?>
                  <div class="d-flex align-items-center">
                    <div class="file-icon">
                      <i class="fas fa-file-circle-question"></i>
                    </div>
                    <div>
                      <div class="fw-medium text-muted">File not found</div>
                      <small class="text-muted">ID: <?php echo htmlspecialchars($file_id); ?></small>
                    </div>
                  </div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div style="width: 30px; height: 30px; background-color: #e1f0fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                      <i class="fas fa-user text-primary"></i>
                    </div>
                    <?php echo htmlspecialchars($user_email); ?>
                  </div>
                </td>
                <td>
                  <?php if($access_type == 'preview'): ?>
                  <span class="status-badge status-preview">
                    <i class="fas fa-eye"></i>
                    Preview
                  </span>
                  <?php elseif($access_type == 'download'): ?>
                  <span class="status-badge status-download">
                    <i class="fas fa-download"></i>
                    Download
                  </span>
                  <?php else: ?>
                  <span class="badge bg-secondary">
                    <?php echo htmlspecialchars($access_type); ?>
                  </span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <i class="far fa-calendar-alt me-2 text-muted"></i>
                    <?php echo date('M d, Y g:i A', strtotime($access_time)); ?>
                  </div>
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
      <p class="mb-0">&copy; <?php echo date('Y');?> BitKeep Management System. All rights reserved.</p>
    </div>
  </div>

  <!-- Add Admin Modal -->
  <div class="modal fade" id="modalRegisterForm" tabindex="-1" aria-labelledby="modalRegisterFormLabel" aria-hidden="true">
    <form action="create_Admin.php" method="POST">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalRegisterFormLabel">
              <i class="fas fa-user-plus me-2"></i> Add Admin
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="status" value="Admin">
            
            <div class="mb-3">
              <label for="name" class="form-label">Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" id="name" name="name" class="form-control" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="admin_user" class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" id="admin_user" name="admin_user" class="form-control" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="department" class="form-label">Department</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-building"></i></span>
                <input type="text" id="department" name="department" class="form-control" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="admin_password" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" id="admin_password" name="admin_password" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" name="reg">
              <i class="fas fa-user-plus me-2"></i> Add Admin
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Add User Modal -->
  <div class="modal fade" id="modalRegisterForm2" tabindex="-1" aria-labelledby="modalRegisterForm2Label" aria-hidden="true">
    <form action="create_user.php" method="POST">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalRegisterForm2Label">
              <i class="fas fa-user-plus me-2"></i> Add User Employee
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="status" value="Employee">
            
            <div class="mb-3">
              <label for="name" class="form-label">Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" id="name" name="name" class="form-control" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="email_address" class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" id="email_address" name="email_address" class="form-control" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="department" class="form-label">Department</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-building"></i></span>
                <input type="text" id="department" name="department" class="form-control" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="user_password" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" id="user_password" name="user_password" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" name="reguser">
              <i class="fas fa-user-plus me-2"></i> Add User
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
  <!-- Toastify JS -->
  <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

  <script>
    $(document).ready(function() {
      // Initialize DataTable
      var table = $('#fileAccessTable').DataTable({
        responsive: true,
        language: {
          search: "_INPUT_",
          searchPlaceholder: "Search logs...",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          infoEmpty: "Showing 0 to 0 of 0 entries",
          infoFiltered: "(filtered from _MAX_ total entries)"
        },
        dom: '<"top"lf>rt<"bottom"ip>',
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        order: [[4, 'desc']] // Order by access time, newest first
      });
      
      // Mobile menu toggle
      function toggleSidebar() {
        $('.sidebar').toggleClass('show');
      }
      
      // Make the function available globally
      window.toggleSidebar = toggleSidebar;
      
      // Refresh button functionality
      $('#refreshBtn').click(function(e) {
        e.preventDefault();
        location.reload();
      });
      
      // Export to CSV functionality
      $('#exportBtn').click(function(e) {
        e.preventDefault();
        
        // Get table data
        var data = [];
        var headers = [];
        
        // Get headers
        $('#fileAccessTable thead th').each(function() {
          headers.push($(this).text().trim());
        });
        
        // Get data rows
        $('#fileAccessTable tbody tr').each(function() {
          var row = [];
          $(this).find('td').each(function(index) {
            // Get text content without HTML
            var cellText = $(this).clone().children().remove().end().text().trim();
            
            // For cells with complex content, extract the main text
            if (index === 1) { // File column
              cellText = $(this).find('.fw-medium').first().text().trim();
            } else if (index === 2) { // User column
              cellText = $(this).text().trim();
            } else if (index === 3) { // Access Type column
              cellText = $(this).text().trim();
            }
            
            row.push(cellText);
          });
          data.push(row);
        });
        
        // Create CSV content
        var csvContent = "data:text/csv;charset=utf-8,";
        
        // Add headers
        csvContent += headers.join(",") + "\r\n";
        
        // Add rows
        data.forEach(function(rowArray) {
          var row = rowArray.join(",");
          csvContent += row + "\r\n";
        });
        
        // Create download link
        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "file_access_logs_" + new Date().toISOString().slice(0,10) + ".csv");
        document.body.appendChild(link);
        
        // Download the CSV file
        link.click();
        
        // Show success notification
        Toastify({
          text: "File access logs exported successfully",
          duration: 3000,
          close: true,
          gravity: "top",
          position: "right",
          backgroundColor: "#2ecc71",
          stopOnFocus: true,
        }).showToast();
      });
      
      // Initialize tooltips
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      });
    });
  </script>
</body>
</html>