<?php

// Inialize session
session_start();

// Include database connection
require_once("include/connection.php");

// Check, if username session is NOT set then this page will jump to login page
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.html');
}

// Get the folder ID from the URL
if (isset($_GET['folder_id'])) {
    $folder_id = $_GET['folder_id'];
} else {
    echo '<script>alert("Invalid folder ID!"); window.location = "folder_management.php";</script>';
    exit();
}

// Fetch folder details from the database
$query_folder = "SELECT * FROM folders WHERE folder_id = '$folder_id'";
$result_folder = mysqli_query($conn, $query_folder);
if (!$result_folder || mysqli_num_rows($result_folder) == 0) {
    echo '<script>alert("Folder not found!"); window.location = "folder_management.php";</script>';
    exit();
}

$folder = mysqli_fetch_assoc($result_folder);

// Get parent folder details if exists
$parent_folder = null;
if (!empty($folder['PARENT_ID'])) {
    $query_parent = "SELECT * FROM folders WHERE folder_id = '" . $folder['PARENT_ID'] . "'";
    $result_parent = mysqli_query($conn, $query_parent);
    if ($result_parent && mysqli_num_rows($result_parent) > 0) {
        $parent_folder = mysqli_fetch_assoc($result_parent);
    }
}

// Get breadcrumb trail
function getBreadcrumbTrail($conn, $folder_id) {
    $breadcrumbs = array();
    $current_id = $folder_id;
    
    while ($current_id) {
        $query = "SELECT folder_id, FOLDER_NAME, PARENT_ID FROM folders WHERE folder_id = '$current_id'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $folder = mysqli_fetch_assoc($result);
            array_unshift($breadcrumbs, array(
                'id' => $folder['folder_id'],
                'name' => $folder['FOLDER_NAME']
            ));
            $current_id = $folder['PARENT_ID'];
        } else {
            break;
        }
    }
    
    // Add home/root as the first item
    array_unshift($breadcrumbs, array(
        'id' => 0,
        'name' => 'Root'
    ));
    
    return $breadcrumbs;
}

$breadcrumbs = getBreadcrumbTrail($conn, $folder_id);

// Check if search was submitted
$searchTerm = '';
$searchType = 'all'; // Default to search all
$isSearching = false;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($conn, $_GET['search']);
    $isSearching = true;
    
    if (isset($_GET['search_type'])) {
        $searchType = $_GET['search_type'];
    }
}

// Fetch files based on search or default
if ($isSearching && ($searchType == 'files' || $searchType == 'all')) {
    $query_files = "SELECT * FROM folder_files WHERE folder_id = '$folder_id' AND name LIKE '%$searchTerm%' ORDER BY timers DESC";
} else {
    $query_files = "SELECT * FROM folder_files WHERE folder_id = '$folder_id' ORDER BY timers DESC";
}

// Execute the query to fetch files
$result_files = mysqli_query($conn, $query_files);

// Fetch subfolders based on search or default
if ($isSearching && ($searchType == 'folders' || $searchType == 'all')) {
    $query_subfolders = "SELECT * FROM folders WHERE PARENT_ID = '$folder_id' AND FOLDER_NAME LIKE '%$searchTerm%' ORDER BY TIMERS DESC";
} else {
    $query_subfolders = "SELECT * FROM folders WHERE PARENT_ID = '$folder_id' ORDER BY TIMERS DESC";
}

$result_subfolders = mysqli_query($conn, $query_subfolders);

// Count files and subfolders
$file_count = mysqli_num_rows($result_files);
$subfolder_count = mysqli_num_rows($result_subfolders);

// Get admin info
$admin_id = $_SESSION['admin_user'];
$query_admin = mysqli_query($conn, "SELECT admin_user FROM admin_login WHERE id = '$admin_id'") or die(mysqli_error($conn));
$row_admin = mysqli_fetch_array($query_admin);
$admin_email = $row_admin['admin_user'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Folder - <?php echo htmlspecialchars($folder['FOLDER_NAME']); ?></title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- Animate.css -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #4361ee;
      --primary-light: #eaefff;
      --secondary-color: #3a0ca3;
      --success-color: #4cc9f0;
      --warning-color: #f72585;
      --danger-color: #ef233c;
      --dark-color: #2b2d42;
      --light-color: #f8f9fa;
      --gray-color: #8d99ae;
      --border-color: #e9ecef;
      --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      --hover-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    body {
      background-color: #f5f7fa;
      font-family: 'Inter', sans-serif;
      color: var(--dark-color);
      overflow-x: hidden;
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
    }

    /* Sidebar Styles */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 280px;
      background: white;
      box-shadow: var(--card-shadow);
      z-index: 1000;
      transition: all 0.3s ease;
      padding-top: 20px;
      overflow-y: auto;
    }

    .sidebar-header {
      padding: 0 20px 20px;
      text-align: center;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 15px;
    }

    .sidebar-header img {
      max-width: 180px;
      transition: transform 0.3s ease;
    }
    
    .sidebar-header img:hover {
      transform: scale(1.05);
    }

    .nav-link {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      color: var(--dark-color);
      transition: all 0.3s;
      border-radius: 8px;
      margin: 5px 15px;
      font-weight: 500;
    }

    .nav-link i {
      margin-right: 12px;
      width: 20px;
      text-align: center;
      font-size: 1.1rem;
    }

    .nav-link:hover {
      background-color: var(--primary-light);
      color: var(--primary-color);
      transform: translateX(3px);
    }

    .nav-link.active {
      background-color: var(--primary-color);
      color: white;
      box-shadow: 0 4px 8px rgba(67, 97, 238, 0.2);
    }

    /* Main Content Styles */
    .main-content {
      margin-left: 280px;
      padding: 20px;
      transition: all 0.3s ease;
    }

    /* Top Navbar */
    .top-navbar {
      background: white;
      padding: 15px 20px;
      box-shadow: var(--card-shadow);
      margin-bottom: 25px;
      border-radius: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .user-welcome {
      font-weight: 600;
      color: var(--dark-color);
      display: flex;
      align-items: center;
    }
    
    .user-welcome .user-avatar {
      width: 40px;
      height: 40px;
      background-color: var(--primary-color);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      margin-right: 12px;
      font-size: 1.2rem;
    }

    .sign-out-btn {
      color: var(--danger-color);
      font-weight: 500;
      padding: 8px 16px;
      border-radius: 8px;
      transition: all 0.3s;
      border: 1px solid var(--border-color);
      background-color: white;
    }

    .sign-out-btn:hover {
      background-color: rgba(239, 35, 60, 0.1);
      color: var(--danger-color);
      transform: translateY(-2px);
    }

    /* Breadcrumb */
    .breadcrumb-container {
      background: white;
      padding: 15px 20px;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 25px;
    }

    .breadcrumb {
      margin-bottom: 0;
    }

    .breadcrumb-item a {
      color: var(--primary-color);
      text-decoration: none;
      transition: all 0.2s;
    }

    .breadcrumb-item a:hover {
      color: var(--secondary-color);
    }

    .breadcrumb-item.active {
      color: var(--dark-color);
      font-weight: 600;
    }

    /* Search Bar */
    .search-container {
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 25px;
    }

    .search-form {
      display: flex;
      gap: 10px;
    }

    .search-input {
      flex-grow: 1;
      position: relative;
    }

    .search-input i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gray-color);
    }

    .search-input input {
      padding-left: 40px;
      border-radius: 8px;
      border: 1px solid var(--border-color);
      height: 45px;
      width: 100%;
      transition: all 0.3s;
    }

    .search-input input:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }

    .search-type {
      width: 150px;
    }

    .search-button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 0 20px;
      height: 45px;
      font-weight: 500;
      transition: all 0.3s;
    }

    .search-button:hover {
      background-color: var(--secondary-color);
      transform: translateY(-2px);
    }

    /* Folder Info Card */
    .folder-info-card {
      background: white;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 25px;
      overflow: hidden;
      transition: all 0.3s;
    }

    .folder-info-card:hover {
      box-shadow: var(--hover-shadow);
      transform: translateY(-3px);
    }

    .folder-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 20px;
      position: relative;
    }

    .folder-header h4 {
      margin: 0;
      font-weight: 600;
      display: flex;
      align-items: center;
    }

    .folder-header i {
      margin-right: 10px;
      font-size: 1.5rem;
    }

    .folder-stats {
      display: flex;
      margin-top: 15px;
    }

    .stat-item {
      margin-right: 20px;
      display: flex;
      align-items: center;
    }

    .stat-item i {
      margin-right: 8px;
      font-size: 1rem;
    }

    .folder-actions {
      padding: 15px 20px;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .folder-body {
      padding: 20px;
    }

    /* Table Container */
    .table-container {
      background: white;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      padding: 25px;
      margin-bottom: 25px;
      transition: all 0.3s;
    }
    
    .table-container:hover {
      box-shadow: var(--hover-shadow);
    }
    
    .table {
      border-collapse: separate;
      border-spacing: 0;
    }
    
    .table thead th {
      background-color: #f8fafc;
      border-bottom: 2px solid #edf2f7;
      color: var(--dark-color);
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
      background-color: var(--primary-light);
    }
    
    .table td {
      padding: 14px 16px;
      vertical-align: middle;
      border-bottom: 1px solid #edf2f7;
    }

    /* Action Buttons */
    .btn-action {
      padding: 8px 16px;
      border-radius: 8px;
      transition: all 0.3s ease;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .btn-action i {
      margin-right: 8px;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), #6c8cff);
      border: none;
    }
    
    .btn-info {
      background-color: var(--success-color);
      border: none;
      color: white;
    }
    
    .btn-success {
      background-color: var(--success-color);
      border: none;
    }
    
    .btn-danger {
      background-color: var(--danger-color);
      border: none;
    }
    
    .btn-sm {
      padding: 0.4rem 0.8rem;
      font-size: 0.875rem;
    }

    /* Subfolder Cards */
    .subfolder-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }

    .subfolder-card {
      background: white;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      transition: all 0.3s;
      overflow: hidden;
      height: 100%;
      border: 1px solid var(--border-color);
    }

    .subfolder-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--hover-shadow);
    }

    .subfolder-header {
      background: linear-gradient(135deg, #f9c74f, #f8961e);
      padding: 15px;
      color: white;
    }

    .subfolder-header h5 {
      margin: 0;
      font-weight: 600;
      display: flex;
      align-items: center;
    }

    .subfolder-header i {
      margin-right: 10px;
    }

    .subfolder-body {
      padding: 15px;
    }

    .subfolder-footer {
      padding: 15px;
      border-top: 1px solid var(--border-color);
      display: flex;
      justify-content: space-between;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 40px 20px;
    }

    .empty-state i {
      font-size: 4rem;
      color: var(--gray-color);
      margin-bottom: 20px;
    }

    .empty-state h5 {
      font-weight: 600;
      margin-bottom: 10px;
    }

    .empty-state p {
      color: var(--gray-color);
      max-width: 400px;
      margin: 0 auto;
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

    /* DataTables Customization */
    .dataTables_wrapper .dataTables_filter input {
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 8px 12px;
      margin-left: 10px;
      transition: all 0.2s;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
      outline: none;
    }
    
    .dataTables_wrapper .dataTables_length select {
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 6px 12px;
    }
    
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
      background: rgba(67, 97, 238, 0.1) !important;
      border-color: rgba(67, 97, 238, 0.1) !important;
      color: var(--primary-color) !important;
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

    /* Responsive Design */
    @media (max-width: 992px) {
      .sidebar {
        width: 250px;
      }
      .main-content {
        margin-left: 250px;
      }
    }
    
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding-bottom: 20px;
      }
      .main-content {
        margin-left: 0;
      }
      .top-navbar {
        flex-direction: column;
        align-items: flex-start;
      }
      .sign-out-btn {
        margin-top: 10px;
      }
      .search-form {
        flex-direction: column;
      }
      .search-type {
        width: 100%;
      }
      .subfolder-grid {
        grid-template-columns: 1fr;
      }
    }

    /* Animations */
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

    .animate-fade-in {
      animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .animate-slide-up {
      animation: slideUp 0.5s ease;
    }

    @keyframes slideUp {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
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

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <img src="img/image1.svg" alt="BitKeep Logo">
    </div>
    <div class="list-group list-group-flush">
      <a href="dashboard.php" class="nav-link">
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
      <a href="folder_management.php" class="nav-link active">
        <i class="fas fa-folder"></i> Folders
      </a>
      <a href="manage_requests.php" class="nav-link">
        <i class="fas fa-key"></i> Requests
      </a>
      <a href="add_document.php" class="nav-link">
        <i class="fas fa-file-medical"></i> Documents
      </a>
      <a href="view_userfile.php" class="nav-link">
        <i class="fas fa-folder-open"></i> View User File
      </a>
      <a href="admin_log.php" class="nav-link">
        <i class="fas fa-history"></i> Admin Log
      </a>
      <a href="file_log.php" class="nav-link">
        <i class="fas fa-file-alt"></i> File Log
      </a>
      <a href="security_logs.php" class="nav-link">
        <i class="fas fa-lock"></i> Security Log
      </a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
      <div class="user-welcome">
        <div class="user-avatar">
          <?php echo substr($admin_email, 0, 1); ?>
        </div>
        Welcome, <?php echo htmlspecialchars($admin_email); ?>!
      </div>
      <a href="logout.php" class="sign-out-btn text-decoration-none">
        <i class="fas fa-sign-out-alt me-2"></i>Sign Out
      </a>
    </div>

    <!-- Breadcrumb -->
    <div class="breadcrumb-container animate-fade-in">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="folder_management.php"><i class="fas fa-home me-1"></i>Home</a></li>
          <?php 
          foreach ($breadcrumbs as $index => $crumb) {
              // Skip the first item (Root) as we already have Home
              if ($index == 0) continue;
              
              // If it's the last item, make it active
              if ($index == count($breadcrumbs) - 1) {
                  echo '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($crumb['name']) . '</li>';
              } else {
                  echo '<li class="breadcrumb-item"><a href="manage_folder.php?folder_id=' . $crumb['id'] . '">' . htmlspecialchars($crumb['name']) . '</a></li>';
              }
          }
          ?>
        </ol>
      </nav>
    </div>

    <!-- Search Container -->
    <div class="search-container animate-fade-in">
      <form action="manage_folder.php" method="GET" class="search-form">
        <input type="hidden" name="folder_id" value="<?php echo $folder_id; ?>">
        <div class="search-input">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search files and folders..." value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control">
        </div>
        <select name="search_type" class="form-select search-type">
          <option value="all" <?php echo ($searchType == 'all') ? 'selected' : ''; ?>>All</option>
          <option value="files" <?php echo ($searchType == 'files') ? 'selected' : ''; ?>>Files Only</option>
          <option value="folders" <?php echo ($searchType == 'folders') ? 'selected' : ''; ?>>Folders Only</option>
        </select>
        <button type="submit" class="search-button">
          <i class="fas fa-search me-2"></i>Search
        </button>
      </form>
    </div>

    <!-- Folder Management Content -->
    <div class="container-fluid px-0">
      <!-- Folder Info Card -->
      <div class="folder-info-card animate-slide-up">
        <div class="folder-header">
          <h4><i class="fas fa-folder-open"></i><?php echo htmlspecialchars($folder['FOLDER_NAME']); ?></h4>
          <div class="folder-stats">
            <div class="stat-item">
              <i class="fas fa-file"></i>
              <span><?php echo $file_count; ?> Files</span>
            </div>
            <div class="stat-item">
              <i class="fas fa-folder"></i>
              <span><?php echo $subfolder_count; ?> Subfolders</span>
            </div>
            <div class="stat-item">
              <i class="fas fa-calendar-alt"></i>
              <span>Created: <?php echo date('M d, Y', strtotime($folder['TIMERS'])); ?></span>
            </div>
          </div>
          <div class="folder-actions">
            <a href="folder_management.php" class="btn btn-sm btn-light">
              <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            <a href="addfilesinfolders.php?folder_id=<?php echo $folder_id; ?>" class="btn btn-sm btn-light">
              <i class="fas fa-upload me-1"></i>Upload File
            </a>
            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#createSubfolderModal">
              <i class="fas fa-folder-plus me-1"></i>New Subfolder
            </button>
          </div>
        </div>
      </div>

      <?php if ($isSearching): ?>
      <div class="alert alert-info animate-fade-in">
        <i class="fas fa-search me-2"></i>
        Search results for "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>" 
        (<?php 
          if ($searchType == 'all') echo "Files and Folders";
          else if ($searchType == 'files') echo "Files Only";
          else echo "Folders Only";
        ?>)
        <a href="manage_folder.php?folder_id=<?php echo $folder_id; ?>" class="float-end text-decoration-none">
          <i class="fas fa-times me-1"></i>Clear Search
        </a>
      </div>
      <?php endif; ?>

      <!-- Subfolders Section -->
      <div class="table-container animate-slide-up">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="mb-0"><i class="fas fa-folder me-2 text-warning"></i>Subfolders</h5>
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createSubfolderModal">
            <i class="fas fa-folder-plus me-2"></i>Create Subfolder
          </button>
        </div>
        
        <?php if ($result_subfolders && mysqli_num_rows($result_subfolders) > 0): ?>
          <div class="subfolder-grid">
            <?php while ($subfolder = mysqli_fetch_assoc($result_subfolders)): ?>
              <div class="subfolder-card">
                <div class="subfolder-header">
                  <h5><i class="fas fa-folder"></i><?php echo htmlspecialchars($subfolder['FOLDER_NAME']); ?></h5>
                </div>
                <div class="subfolder-body">
                  <p class="text-muted mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>Created: <?php echo date('M d, Y', strtotime($subfolder['TIMERS'])); ?>
                  </p>
                  
                  <?php
                  // Count files in this subfolder
                  $subfolder_id = $subfolder['folder_id'];
                  $query_count = "SELECT COUNT(*) as file_count FROM folder_files WHERE folder_id = '$subfolder_id'";
                  $result_count = mysqli_query($conn, $query_count);
                  $file_count_data = mysqli_fetch_assoc($result_count);
                  $subfolder_file_count = $file_count_data['file_count'];
                  
                  // Count subfolders in this subfolder
                  $query_subfolder_count = "SELECT COUNT(*) as subfolder_count FROM folders WHERE PARENT_ID = '$subfolder_id'";
                  $result_subfolder_count = mysqli_query($conn, $query_subfolder_count);
                  $subfolder_count_data = mysqli_fetch_assoc($result_subfolder_count);
                  $subfolder_subfolder_count = $subfolder_count_data['subfolder_count'];
                  ?>
                  
                  <div class="d-flex mt-3">
                    <div class="me-3">
                      <span class="badge bg-primary rounded-pill">
                        <i class="fas fa-file me-1"></i><?php echo $subfolder_file_count; ?> Files
                      </span>
                    </div>
                    <div>
                      <span class="badge bg-secondary rounded-pill">
                        <i class="fas fa-folder me-1"></i><?php echo $subfolder_subfolder_count; ?> Subfolders
                      </span>
                    </div>
                  </div>
                </div>
                <div class="subfolder-footer">
                  <a href="manage_folder.php?folder_id=<?php echo $subfolder['folder_id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-folder-open me-1"></i>Open
                  </a>
                  <a href="delete_folder.php?folder_id=<?php echo $subfolder['folder_id']; ?>" class="btn btn-sm btn-danger" 
                     onclick="return confirm('Are you sure you want to delete this subfolder? All files and subfolders inside will also be deleted.');">
                    <i class="fas fa-trash me-1"></i>Delete
                  </a>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h5>No Subfolders Found</h5>
            <p>
              <?php if ($isSearching): ?>
                No subfolders match your search criteria. Try a different search term or create a new subfolder.
              <?php else: ?>
                This folder doesn't have any subfolders yet. Create your first subfolder to organize your files better.
              <?php endif; ?>
            </p>
            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#createSubfolderModal">
              <i class="fas fa-folder-plus me-2"></i>Create Subfolder
            </button>
          </div>
        <?php endif; ?>
      </div>

      <!-- Files Table -->
      <div class="table-container animate-slide-up">
        <h5 class="mb-4"><i class="fas fa-file me-2 text-primary"></i>Files in <?php echo htmlspecialchars($folder['FOLDER_NAME']); ?></h5>
        
        <?php if ($result_files && mysqli_num_rows($result_files) > 0): ?>
          <table id="filesTable" class="table table-hover">
            <thead>
              <tr>
                <th>File Name</th>
                <th>Size (KB)</th>
                <th>Upload Time</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($file = mysqli_fetch_assoc($result_files)): ?>
                <tr>
                  <td>
                    <?php 
                    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $icon_class = 'fa-file';
                    
                    // Determine icon based on file extension
                    switch(strtolower($file_extension)) {
                      case 'pdf':
                        $icon_class = 'fa-file-pdf';
                        break;
                      case 'doc':
                      case 'docx':
                        $icon_class = 'fa-file-word';
                        break;
                      case 'xls':
                      case 'xlsx':
                        $icon_class = 'fa-file-excel';
                        break;
                      case 'ppt':
                      case 'pptx':
                        $icon_class = 'fa-file-powerpoint';
                        break;
                      case 'jpg':
                      case 'jpeg':
                      case 'png':
                      case 'gif':
                        $icon_class = 'fa-file-image';
                        break;
                      case 'zip':
                      case 'rar':
                        $icon_class = 'fa-file-archive';
                        break;
                      case 'mp3':
                      case 'wav':
                        $icon_class = 'fa-file-audio';
                        break;
                      case 'mp4':
                      case 'avi':
                        $icon_class = 'fa-file-video';
                        break;
                      case 'txt':
                        $icon_class = 'fa-file-alt';
                        break;
                    }
                    ?>
                    <i class="fas <?php echo $icon_class; ?> me-2 text-primary"></i>
                    <?php echo htmlspecialchars($file['name']); ?>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark">
                      <?php echo number_format($file['size'], 2); ?> KB
                    </span>
                  </td>
                  <td><?php echo date('M d, Y H:i', strtotime($file['timers'])); ?></td>
                  <td>
                    <div class="btn-group">
                      <a href="view_files.php?file_id=<?php echo $file['id']; ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="download_file.php?file_id=<?php echo $file['id']; ?>" class="btn btn-sm btn-info">
                        <i class="fas fa-download"></i>
                      </a>
                      <a href="delete_file.php?file_id=<?php echo $file['id']; ?>&folder_id=<?php echo $folder_id; ?>" 
                         class="btn btn-sm btn-danger" 
                         onclick="return confirm('Are you sure you want to delete this file?');">
                        <i class="fas fa-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-file-alt"></i>
            <h5>No Files Found</h5>
            <p>
              <?php if ($isSearching): ?>
                No files match your search criteria. Try a different search term or upload a new file.
              <?php else: ?>
                This folder doesn't have any files yet. Upload your first file to get started.
              <?php endif; ?>
            </p>
            <a href="addfilesinfolders.php?folder_id=<?php echo $folder_id; ?>" class="btn btn-primary mt-3">
              <i class="fas fa-upload me-2"></i>Upload File
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Create Subfolder Modal -->
  <div class="modal fade" id="createSubfolderModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-folder-plus me-2 text-primary"></i>Create Subfolder</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="create_subfolder.php">
          <div class="modal-body">
            <div class="mb-3">
              <label for="subfolder_name" class="form-label">Subfolder Name</label>
              <input type="text" name="subfolder_name" id="subfolder_name" class="form-control" required>
            </div>
            <input type="hidden" name="parent_id" value="<?php echo $folder_id; ?>">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-folder-plus me-2"></i>Create Subfolder
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Admin Modal -->
  <div class="modal fade" id="modalRegisterForm" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-primary"></i>Add Admin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="create_Admin.php" method="POST">
          <div class="modal-body">
            <div class="mb-3">
              <label for="adminName" class="form-label">Name</label>
              <input type="text" class="form-control" id="adminName" name="name" required>
            </div>
            <div class="mb-3">
              <label for="adminDepartment" class="form-label">Department</label>
              <input type="text" class="form-control" id="adminDepartment" name="department" required>
            </div>
            <div class="mb-3">
              <label for="adminEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="adminEmail" name="admin_user" required>
            </div>
            <div class="mb-3">
              <label for="adminPassword" class="form-label">Password</label>
              <input type="password" class="form-control" id="adminPassword" name="admin_password" required>
            </div>
            <div class="mb-3">
              <label for="adminStatus" class="form-label">Status</label>
              <input type="text" class="form-control" id="adminStatus" name="admin_status" value="Admin" readonly>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" name="reg">
              <i class="fas fa-user-plus me-2"></i>Add Admin
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add User Modal -->
  <div class="modal fade" id="modalRegisterForm2" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-primary"></i>Add User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="create_user.php" method="POST">
          <div class="modal-body">
            <div class="mb-3">
              <label for="userName" class="form-label">Name</label>
              <input type="text" class="form-control" id="userName" name="name" required>
            </div>
            <div class="mb-3">
              <label for="userEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="userEmail" name="email_address" required>
            </div>
            <div class="mb-3">
              <label for="userDepartment" class="form-label">Department</label>
              <input type="text" class="form-control" id="userDepartment" name="department" required>
            </div>
            <div class="mb-3">
              <label for="userPassword" class="form-label">Password</label>
              <input type="password" class="form-control" id="userPassword" name="user_password" required>
            </div>
            <div class="mb-3">
              <label for="userStatus" class="form-label">Status</label>
              <input type="text" class="form-control" id="userStatus" name="user_status" value="Employee" readonly>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" name="reguser">
              <i class="fas fa-user-plus me-2"></i>Add User
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script>
    // Initialize DataTable
    $(document).ready(function() {
      $('#filesTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "order": [[2, "desc"]],
        "language": {
          "emptyTable": "No files found in this folder.",
          "zeroRecords": "No matching files found - try a different search term."
        }
      });
    });

    // Loader
    window.addEventListener('load', function() {
      setTimeout(function() {
        document.getElementById('loader').style.display = 'none';
      }, 500);
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