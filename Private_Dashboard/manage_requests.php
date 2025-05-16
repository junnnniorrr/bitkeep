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
    
    .btn-success {
      background-color: var(--success-color);
      border: none;
      box-shadow: 0 4px 8px rgba(46, 204, 113, 0.3);
    }
    
    .btn-success:hover {
      background-color: #27ae60;
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(46, 204, 113, 0.4);
    }
    
    .btn-danger {
      background-color: var(--danger-color);
      border: none;
      box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
    }
    
    .btn-danger:hover {
      background-color: #c0392b;
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(231, 76, 60, 0.4);
    }
    
    .btn-outline-primary {
      color: var(--primary-color);
      border-color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
      background-color: var(--primary-color);
      color: white;
      transform: translateY(-2px);
    }
    
    .form-select, .form-control {
      padding: 10px 15px;
      border-radius: 6px;
      border: 1px solid #ddd;
      transition: var(--transition);
      font-size: 0.95rem;
    }
    
    .form-select:focus, .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
    }
    
    .badge {
      padding: 6px 10px;
      font-weight: 500;
      border-radius: 6px;
    }
    
    .badge-pending {
      background-color: #f39c12;
      color: white;
    }
    
    .badge-approved {
      background-color: #2ecc71;
      color: white;
    }
    
    .badge-rejected {
      background-color: #e74c3c;
      color: white;
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
    
    /* Custom tooltip */
    .custom-tooltip {
      position: relative;
      display: inline-block;
    }
    
    .custom-tooltip .tooltip-text {
      visibility: hidden;
      width: 120px;
      background-color: #333;
      color: #fff;
      text-align: center;
      border-radius: 6px;
      padding: 5px;
      position: absolute;
      z-index: 1;
      bottom: 125%;
      left: 50%;
      margin-left: -60px;
      opacity: 0;
      transition: opacity 0.3s;
    }
    
    .custom-tooltip:hover .tooltip-text {
      visibility: visible;
      opacity: 1;
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
    
    .status-pending {
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeeba;
    }
    
    .status-approved {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .status-rejected {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    /* Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
      animation: fadeIn 0.5s ease forwards;
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
    
    // Mobile menu toggle
    function toggleSidebar() {
      $('.sidebar').toggleClass('show');
    }
    
    // DataTable initialization
    $(document).ready(function() {
      $('#requestsTable').DataTable({
        responsive: true,
        language: {
          search: "_INPUT_",
          searchPlaceholder: "Search requests...",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          infoEmpty: "Showing 0 to 0 of 0 entries",
          infoFiltered: "(filtered from _MAX_ total entries)"
        },
        dom: '<"top"lf>rt<"bottom"ip>',
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        order: [[6, 'desc']] // Order by request date, newest first
      });
      
      // Confirm before approve/reject
      $('.btn-approve').click(function(e) {
        if(!confirm('Are you sure you want to approve this request?')) {
          e.preventDefault();
        }
      });
      
      $('.btn-reject').click(function(e) {
        if(!confirm('Are you sure you want to reject this request?')) {
          e.preventDefault();
        }
      });
    });
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
        <a class="nav-link" href="view_admin.php">
          <i class="fas fa-users-cog"></i>
          <span>View Admin</span>
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
        <a class="nav-link active" href="manage_requests.php">
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
           <li class="nav-item">
        <a class="nav-link" href="file_log.php">
          <i class="fas fa-file-alt"></i>
          <span>File Logs</span>
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
          <i class="fas fa-key me-2 text-primary"></i>
          Folder Requests Management
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

    <!-- Content Card -->
    <div class="content-card fade-in">
      <div class="card-header">
        <div>
          <i class="fas fa-folder-plus me-2"></i>
          Manage Folder Requests
        </div>
        <div>
          <span class="badge bg-primary"><?php echo $result->num_rows; ?> Total Requests</span>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="requestsTable" class="table table-striped table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>User Email</th>
                <th>Requested Folder</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Assigned Folder</th>
                <th>Request Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              require_once("include/connection.php");
              
              // Fetch all requests
              $sql = "SELECT fr.*, f.FOLDER_NAME 
                      FROM folder_requests fr
                      LEFT JOIN folders f ON fr.assigned_folder_id = f.folder_id
                      ORDER BY fr.request_date DESC";
              $result = $conn->query($sql);
              
              while($row = $result->fetch_assoc()): 
                // Determine status badge class
                $statusClass = '';
                switch($row['status']) {
                  case 'pending':
                    $statusClass = 'status-pending';
                    $statusIcon = '<i class="fas fa-clock"></i>';
                    break;
                  case 'approved':
                    $statusClass = 'status-approved';
                    $statusIcon = '<i class="fas fa-check-circle"></i>';
                    break;
                  case 'rejected':
                    $statusClass = 'status-rejected';
                    $statusIcon = '<i class="fas fa-times-circle"></i>';
                    break;
                  default:
                    $statusClass = 'status-pending';
                    $statusIcon = '<i class="fas fa-clock"></i>';
                }
              ?>
              <tr>
                <form action="process_request.php" method="POST" class="request-form">
                  <td><?= $row['id'] ?></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="me-2" style="width: 30px; height: 30px; background-color: #e1f0fa; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user text-primary"></i>
                      </div>
                      <?= htmlspecialchars($row['user_email']) ?>
                    </div>
                  </td>
                  <td>
                    <span class="fw-medium"><?= htmlspecialchars($row['requested_folder_name']) ?></span>
                  </td>
                  <td>
                    <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['reason']) ?>">
                      <?= htmlspecialchars($row['reason']) ?>
                    </div>
                  </td>
                  <td>
                    <span class="status-badge <?= $statusClass ?>">
                      <?= $statusIcon ?> <?= ucfirst($row['status']) ?>
                    </span>
                  </td>
                  <td>
                    <select name="assigned_folder_id" class="form-select form-select-sm" <?= $row['status'] !== 'pending' ? 'disabled' : '' ?>>
                      <option value="">-- Select Folder --</option>
                      <?php
                      $folders = $conn->query("SELECT folder_id, FOLDER_NAME FROM folders");
                      while ($f = $folders->fetch_assoc()):
                      ?>
                        <option value="<?= $f['folder_id'] ?>" <?= $row['assigned_folder_id'] == $f['folder_id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($f['FOLDER_NAME']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <i class="far fa-calendar-alt me-2 text-muted"></i>
                      <?= date('M d, Y g:i A', strtotime($row['request_date'])) ?>
                    </div>
                  </td>
                  <td>
                    <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="admin_email" value="<?= $id ?>">
                    
                    <?php if($row['status'] === 'pending'): ?>
                      <div class="d-flex gap-2">
                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm btn-approve">
                          <i class="fas fa-check"></i> Approve
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm btn-reject">
                          <i class="fas fa-times"></i> Reject
                        </button>
                      </div>
                    <?php else: ?>
                      <span class="text-muted">
                        <i class="fas fa-check-double"></i> Processed
                      </span>
                    <?php endif; ?>
                  </td>
                </form>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
      <p class="mb-0">&copy; <?= date('Y') ?> BitKeep Management System. All rights reserved.</p>
    </div>
  </div>

  <!--  <?= date('Y') ?> BitKeep Management System. All rights reserved.</p>
    </div>
  </div>

  <!-- Bootstrap and other scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
  
  <script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
    
    // Highlight active menu item
    document.addEventListener('DOMContentLoaded', function() {
      const currentPath = window.location.pathname;
      const navLinks = document.querySelectorAll('.sidebar .nav-link');
      
      navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath.split('/').pop()) {
          link.classList.add('active');
        }
      });
    });
  </script>
</body>
</html>
