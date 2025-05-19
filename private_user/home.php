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
  <title>BitKeep Management</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
  
  <style>
    :root {
      --primary-color: #4a6bff;
      --secondary-color: #F0B56F;
      --dark-color: #2d3748;
      --light-color: #f8f9fa;
      --success-color: #38a169;
      --warning-color: #e9b949;
      --danger-color: #e53e3e;
      --sidebar-width: 260px;
      --navbar-height: 70px;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f7fafc;
      overflow-x: hidden;
      padding-top: var(--navbar-height);
    }
    
    /* Sidebar Styles */
    .sidebar {
      position: fixed;
      top: var(--navbar-height);
      left: 0;
      height: calc(100vh - var(--navbar-height));
      width: var(--sidebar-width);
      background: linear-gradient(180deg, #ffffff 0%, #f8f9ff 100%);
      box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
      z-index: 999;
      transition: all 0.3s;
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
      transition: all 0.2s;
      border-left: 4px solid transparent;
      margin-bottom: 5px;
    }
    
    .sidebar-item:hover, .sidebar-item.active {
      background-color: rgba(74, 107, 255, 0.08);
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
      margin: 15px 20px;
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
      border-radius: 10px;
    }
    
    .user-avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-color), #6c8cff);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 12px;
      font-weight: bold;
      box-shadow: 0 3px 6px rgba(74, 107, 255, 0.2);
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
      transition: all 0.3s;
      min-height: calc(100vh - var(--navbar-height));
    }
    
    /* Navbar Styles */
    .navbar {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      background: linear-gradient(90deg, #ffffff 0%, #f8f9ff 100%) !important;
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
      color: var(--dark-color);
      font-size: 1.25rem;
      cursor: pointer;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 8px;
      transition: all 0.2s;
    }
    
    .sidebar-toggle:hover {
      background-color: rgba(74, 107, 255, 0.08);
      color: var(--primary-color);
    }
    
    /* Content Card */
    .content-card {
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
      padding: 25px;
      margin-bottom: 30px;
      transition: all 0.3s ease;
      border: 1px solid rgba(0, 0, 0, 0.03);
    }
    
    .content-card:hover {
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    }
    
    .card-header {
      border-bottom: 1px solid #edf2f7;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }
    
    .card-title {
      font-weight: 700;
      color: var(--dark-color);
      margin-bottom: 0;
    }
    
    /* Table Styles */
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
    
    .action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background-color: #edf2f7;
      color: var(--primary-color);
      transition: all 0.2s ease-in-out;
      border: none;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    
    .action-btn:hover {
      background-color: var(--primary-color);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(74, 107, 255, 0.25);
    }
    
    .badge {
      font-weight: 600;
      font-size: 0.75rem;
      padding: 0.35em 0.65em;
      border-radius: 6px;
    }
    
    .badge-success {
      background-color: #def7ec;
      color: #0c533a;
    }
    
    .badge-warning {
      background-color: #feecdc;
      color: #723b13;
    }
    
    .badge-primary {
      background-color: #e9eeff;
      color: #3547a0;
    }
    
    /* Loader */
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
    
    /* Additional Enhancements */
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), #6c8cff);
      border: none;
      border-radius: 8px;
      padding: 8px 16px;
      font-weight: 600;
      transition: all 0.2s;
      box-shadow: 0 4px 6px rgba(74, 107, 255, 0.2);
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, #4559c0, #5b7bff);
      transform: translateY(-1px);
      box-shadow: 0 6px 8px rgba(74, 107, 255, 0.25);
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
    
    .file-icon {
      font-size: 1.2rem;
      margin-right: 10px;
    }
    
    /* Toast notification */
    .toast {
      border: none;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
    }
    
    .toast-header {
      border-bottom: 1px solid #edf2f7;
      padding: 10px 15px;
    }
    
    .toast-body {
      padding: 12px 15px;
    }
    
    /* Breadcrumb */
    .breadcrumb {
      background-color: transparent;
      padding: 0;
      margin-bottom: 1.5rem;
    }
    
    .breadcrumb-item a {
      color: var(--primary-color);
      text-decoration: none;
    }
    
    .breadcrumb-item.active {
      color: #718096;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
        top: var(--navbar-height);
      }
      
      .main-content, .navbar {
        margin-left: 0;
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
    }
    
    @media (max-width: 576px) {
      .navbar {
        padding: 0 15px;
      }
      
      .main-content {
        padding: 20px 15px;
      }
      
      .content-card {
        padding: 20px 15px;
      }
    }
    
    /* Download button animation */
    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(74, 107, 255, 0.4);
      }
      70% {
        box-shadow: 0 0 0 10px rgba(74, 107, 255, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(74, 107, 255, 0);
      }
    }
    
    .download-btn {
      animation: pulse 2s infinite;
    }
  </style>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        
        toast.innerHTML = `
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Security Alert</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(function() {
            toast.remove();
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
     $id = $row['email_address'] ?? null;
  ?>
  
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
        <div class="user-name"><?php echo ucwords(htmlentities($id)); ?></div>
        <div class="user-role">User</div>
      </div>
    </div>
    
    <div class="sidebar-menu">
      <a href="index.php" class="sidebar-item active">
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
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
          </ol>
        </nav>
      
        <div class="content-card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">
              <i class="fas fa-file-alt me-2 text-primary"></i>
              File Management
            </h5>
            <a href="add_file.php" class="btn btn-primary btn-sm">
              <i class="fas fa-plus me-1"></i> Add New File
            </a>
          </div>
          
          <div class="table-responsive">
            <table id="fileTable" class="table table-hover">
              <thead>
                <tr>
                  <th>Filename</th>
                  <th>Size</th>
                  <th>Uploader</th>  
                  <th>Status</th> 
                  <th>Upload Date</th>
                  <th>Downloads</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  require_once("include/connection.php");
                  $query = mysqli_query($conn,"SELECT ID,NAME,SIZE,EMAIL,ADMIN_STATUS,TIMERS,DOWNLOAD FROM upload_files group by NAME DESC") or die (mysqli_error($conn));
                  while($file=mysqli_fetch_array($query)){
                    $id = $file['ID'];
                    $name = $file['NAME'];
                    $size = $file['SIZE'];
                    $uploads = $file['EMAIL'];
                    $status = $file['ADMIN_STATUS'];
                    $time = $file['TIMERS'];
                    $download = $file['DOWNLOAD'];
                    
                    // Define badge class based on status
                    $statusBadgeClass = '';
                    if(strtolower($status) == 'active') {
                      $statusBadgeClass = 'badge-success';
                    } else if(strtolower($status) == 'pending') {
                      $statusBadgeClass = 'badge-warning';
                    } else {
                      $statusBadgeClass = 'badge-primary';
                    }
                    
                    // Determine file icon based on extension
                    $fileExtension = pathinfo($name, PATHINFO_EXTENSION);
                    $fileIcon = 'fa-file';
                    
                    switch(strtolower($fileExtension)) {
                      case 'pdf':
                        $fileIcon = 'fa-file-pdf';
                        break;
                      case 'doc':
                      case 'docx':
                        $fileIcon = 'fa-file-word';
                        break;
                      case 'xls':
                      case 'xlsx':
                        $fileIcon = 'fa-file-excel';
                        break;
                      case 'ppt':
                      case 'pptx':
                        $fileIcon = 'fa-file-powerpoint';
                        break;
                      case 'jpg':
                      case 'jpeg':
                      case 'png':
                      case 'gif':
                        $fileIcon = 'fa-file-image';
                        break;
                      case 'zip':
                      case 'rar':
                        $fileIcon = 'fa-file-archive';
                        break;
                      case 'mp3':
                      case 'wav':
                        $fileIcon = 'fa-file-audio';
                        break;
                      case 'mp4':
                      case 'mov':
                        $fileIcon = 'fa-file-video';
                        break;
                      case 'txt':
                        $fileIcon = 'fa-file-alt';
                        break;
                      case 'html':
                      case 'css':
                      case 'js':
                        $fileIcon = 'fa-file-code';
                        break;
                    }
                ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <i class="far <?php echo $fileIcon; ?> file-icon text-primary"></i>
                      <span class="text-truncate" style="max-width: 200px;" title="<?php echo $name; ?>"><?php echo $name; ?></span>
                    </div>
                  </td>
                  <td><?php echo floor($size / 1000) . ' KB'; ?></td>
                  <td>
                    <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo $uploads; ?>">
                      <?php echo $uploads; ?>
                    </span>
                  </td>
                  <td><span class="badge <?php echo $statusBadgeClass; ?>"><?php echo $status; ?></span></td>
                  <td><?php echo $time; ?></td>
                  <td><span class="badge bg-light text-dark"><?php echo $download; ?></span></td>
                  <td>
                    <a href='downloads.php?file_id=<?php echo $id; ?>' class="action-btn download-btn" title="Download File">
                      <i class="fas fa-download"></i>
                    </a>
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <footer class="mt-4 text-center text-muted">
      <p class="small mb-0">BitKeep Management System &copy; <?php echo date('Y'); ?>. All rights reserved.</p>
    </footer>
  </div>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
  
  <script>
    $(document).ready(function() {
      // Initialize DataTable
      $('#fileTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        language: {
          search: "<i class='fas fa-search'></i>",
          searchPlaceholder: "Search files..."
        },
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
      });
      
      // Sidebar Toggle
      $('#sidebarToggle').click(function() {
        $('.sidebar').toggleClass('active');
      });
      
      // Set active sidebar item
      $('.sidebar-item').each(function() {
        var href = $(this).attr('href');
        var currentPage = window.location.pathname.split('/').pop();
        
        if (href === currentPage) {
          $(this).addClass('active');
        }
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
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      });
    });
  </script>
</body>
</html>