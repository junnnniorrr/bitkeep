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
  <title>Manage Files - BitKeep Management System</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    :root {
      --primary-color: #5469d4;
      --secondary-color: #F0B56F;
      --dark-color: #2d3748;
      --light-color: #f8f9fa;
      --success-color: #38a169;
      --warning-color: #e9b949;
      --danger-color: #e53e3e;
      --sidebar-width: 250px;
      --navbar-height: 70px;
      --folder-color: #4361ee;
      --folder-hover: #3a56d4;
      --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      --transition: all 0.3s ease;
    }
    
    body {
      font-family: 'Nunito', sans-serif;
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
      background-color: white;
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
      transition: all 0.3s;
      min-height: calc(100vh - var(--navbar-height));
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
      transition: all 0.2s;
    }
    
    .sidebar-toggle:hover {
      background-color: #f8fafc;
    }
    
    /* Content Card */
    .content-card {
      background-color: #fff;
      border-radius: 10px;
      box-shadow: var(--card-shadow);
      padding: 25px;
      margin-bottom: 30px;
      transition: var(--transition);
    }
    
    .content-card:hover {
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
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
    
    /* Form Styles */
    .form-label {
      font-weight: 600;
      color: #4a5568;
      margin-bottom: 8px;
    }
    
    .form-control {
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 10px 15px;
      transition: all 0.2s;
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(84, 105, 212, 0.25);
    }
    
    textarea.form-control {
      min-height: 120px;
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      font-weight: 600;
      padding: 10px 20px;
      border-radius: 6px;
    }
    
    .btn-primary:hover {
      background-color: #4559c0;
      border-color: #4559c0;
      transform: translateY(-1px);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
    
    /* Enhanced File Manager Styles */
    .file-manager-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .folder-path {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }
    
    .folder-path-item {
      display: flex;
      align-items: center;
      color: #718096;
      text-decoration: none;
      font-size: 0.9rem;
    }
    
    .folder-path-item:hover {
      color: var(--folder-color);
    }
    
    .folder-path-separator {
      color: #cbd5e0;
      margin: 0 0.25rem;
    }
    
    .folder-info-card {
      background-color: #fff;
      border-radius: 10px;
      box-shadow: var(--card-shadow);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      border-left: 4px solid var(--folder-color);
    }
    
    .folder-title {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
    }
    
    .folder-icon {
      font-size: 1.75rem;
      color: var(--folder-color);
    }
    
    .folder-name {
      font-size: 1.5rem;
      font-weight: 700;
      color: #2d3748;
      margin: 0;
    }
    
    .folder-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .folder-meta-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.85rem;
      color: #718096;
      background-color: #f7fafc;
      padding: 0.4rem 0.75rem;
      border-radius: 20px;
    }
    
    .search-filter-container {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }
    
    .search-container {
      position: relative;
      flex-grow: 1;
      max-width: 500px;
    }
    
    .search-input {
      width: 100%;
      padding: 0.75rem 1rem 0.75rem 2.75rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 0.95rem;
      transition: var(--transition);
    }
    
    .search-input:focus {
      border-color: var(--folder-color);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }
    
    .search-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #a0aec0;
    }
    
    .filter-dropdown {
      min-width: 150px;
    }
    
    .upload-card {
      background-color: #fff;
      border-radius: 10px;
      box-shadow: var(--card-shadow);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }
    
    .upload-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.25rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #edf2f7;
    }
    
    .upload-title {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin: 0;
      font-size: 1.25rem;
      font-weight: 600;
      color: #2d3748;
    }
    
    .upload-icon {
      color: var(--folder-color);
    }
    
    .dropzone {
      border: 2px dashed #e2e8f0;
      border-radius: 8px;
      padding: 2rem 1.5rem;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      background-color: #f8fafc;
    }
    
    .dropzone:hover, .dropzone.dragover {
      border-color: var(--folder-color);
      background-color: rgba(67, 97, 238, 0.05);
    }
    
    .dropzone-icon {
      font-size: 2.5rem;
      color: #a0aec0;
      margin-bottom: 1rem;
    }
    
    .dropzone-text {
      color: #718096;
      margin-bottom: 0.5rem;
    }
    
    .dropzone-hint {
      font-size: 0.85rem;
      color: #a0aec0;
    }
    
    .file-input {
      position: absolute;
      width: 0;
      height: 0;
      opacity: 0;
    }
    
    .selected-file {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-top: 1rem;
      padding: 0.75rem 1rem;
      background-color: #f8fafc;
      border-radius: 8px;
      border-left: 3px solid var(--folder-color);
    }
    
    .selected-file-icon {
      color: var(--folder-color);
      font-size: 1.25rem;
    }
    
    .selected-file-name {
      font-weight: 500;
      color: #4a5568;
      flex-grow: 1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .selected-file-size {
      font-size: 0.85rem;
      color: #718096;
    }
    
    .selected-file-remove {
      color: #a0aec0;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .selected-file-remove:hover {
      color: var(--danger-color);
    }
    
    .upload-actions {
      display: flex;
      justify-content: flex-end;
      margin-top: 1.5rem;
    }
    
    .files-card {
      background-color: #fff;
      border-radius: 10px;
      box-shadow: var(--card-shadow);
      margin-bottom: 1.5rem;
      overflow: hidden;
    }
    
    .files-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #edf2f7;
    }
    
    .files-title {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin: 0;
      font-size: 1.25rem;
      font-weight: 600;
      color: #2d3748;
    }
    
    .files-icon {
      color: var(--folder-color);
    }
    
    .files-count {
      font-size: 0.85rem;
      color: #718096;
      background-color: #f7fafc;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
    }
    
    .files-table-container {
      overflow-x: auto;
    }
    
    .files-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .files-table th {
      background-color: #f8fafc;
      color: #718096;
      font-weight: 600;
      font-size: 0.85rem;
      text-align: left;
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #edf2f7;
    }
    
    .files-table td {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #edf2f7;
      vertical-align: middle;
    }
    
    .files-table tr:last-child td {
      border-bottom: none;
    }
    
    .files-table tr:hover {
      background-color: rgba(67, 97, 238, 0.03);
    }
    
    .file-cell {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .file-cell-icon {
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      background-color: rgba(67, 97, 238, 0.1);
      color: var(--folder-color);
    }
    
    .file-cell-icon.folder {
      background-color: rgba(246, 173, 85, 0.1);
      color: var(--secondary-color);
    }
    
    .file-cell-icon.pdf {
      background-color: rgba(239, 68, 68, 0.1);
      color: #ef4444;
    }
    
    .file-cell-icon.doc {
      background-color: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
    }
    
    .file-cell-icon.img {
      background-color: rgba(16, 185, 129, 0.1);
      color: #10b981;
    }
    
    .file-cell-icon.xls {
      background-color: rgba(16, 185, 129, 0.1);
      color: #10b981;
    }
    
    .file-cell-icon.ppt {
      background-color: rgba(249, 115, 22, 0.1);
      color: #f97316;
    }
    
    .file-cell-icon.txt {
      background-color: rgba(107, 114, 128, 0.1);
      color: #6b7280;
    }
    
    .file-cell-name {
      font-weight: 500;
      color: #2d3748;
    }
    
    .file-cell-info {
      font-size: 0.8rem;
      color: #718096;
    }
    
    .file-type-badge {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 500;
      text-transform: uppercase;
      background-color: #f7fafc;
      color: #718096;
    }
    
    .file-actions {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .file-action-btn {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      background-color: #f7fafc;
      color: #718096;
      border: none;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .file-action-btn:hover {
      background-color: #edf2f7;
      color: #4a5568;
    }
    
    .file-action-btn.preview:hover {
      color: var(--folder-color);
    }
    
    .file-action-btn.download:hover {
      color: var(--success-color);
    }
    
    .file-action-btn.delete:hover {
      color: var(--danger-color);
    }
    
    .empty-state {
      padding: 3rem 1.5rem;
      text-align: center;
    }
    
    .empty-state-icon {
      font-size: 3rem;
      color: #e2e8f0;
      margin-bottom: 1rem;
    }
    
    .empty-state-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #4a5568;
      margin-bottom: 0.5rem;
    }
    
    .empty-state-text {
      color: #718096;
      margin-bottom: 1.5rem;
    }
    
    .breadcrumb-container {
      margin-bottom: 1.5rem;
    }
    
    .breadcrumb {
      display: flex;
      flex-wrap: wrap;
      padding: 0;
      margin: 0;
      list-style: none;
      background-color: transparent;
    }
    
    .breadcrumb-item {
      display: flex;
      align-items: center;
    }
    
    .breadcrumb-item a {
      color: #718096;
      text-decoration: none;
      transition: var(--transition);
    }
    
    .breadcrumb-item a:hover {
      color: var(--folder-color);
    }
    
    .breadcrumb-item.active {
      color: var(--folder-color);
      font-weight: 500;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
      content: "/";
      padding: 0 0.5rem;
      color: #cbd5e0;
    }
    
    .subfolder-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .subfolder-item {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: var(--card-shadow);
      padding: 1rem;
      text-decoration: none;
      transition: var(--transition);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
    
    .subfolder-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
    }
    
    .subfolder-icon {
      font-size: 2rem;
      color: var(--secondary-color);
      margin-bottom: 0.75rem;
    }
    
    .subfolder-name {
      font-weight: 500;
      color: #2d3748;
      margin-bottom: 0.25rem;
      word-break: break-word;
    }
    
    .subfolder-info {
      font-size: 0.8rem;
      color: #718096;
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
      
      .subfolder-container {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      }
    }
    
    @media (max-width: 768px) {
      .file-manager-header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .search-filter-container {
        width: 100%;
      }
      
      .search-container {
        width: 100%;
        max-width: none;
      }
      
      .files-table th:nth-child(3),
      .files-table td:nth-child(3) {
        display: none;
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
      
      .files-table th:nth-child(4),
      .files-table td:nth-child(4) {
        display: none;
      }
      
      .subfolder-container {
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
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

  <!-- Main Content -->
  <div class="main-content">
    <?php
    require_once("../include/connection.php");

    // Get user email from database based on session ID
    $user_id = $_SESSION["email_address"];
    $stmt = $conn->prepare("SELECT email_address FROM login_user WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_row = $result->fetch_assoc();
        $user_email = $user_row['email_address'];
    } else {
        // User not found, redirect to login
        session_destroy();
        header("location:../login.html");
        exit();
    }
    $stmt->close();

    // Check if folder_id is provided
    if (!isset($_GET['folder_id']) || empty($_GET['folder_id'])) {
        $_SESSION['error'] = "No folder specified.";
        header("location:user_dashboard.php");
        exit();
    }

    $folder_id = $_GET['folder_id'];

    // Verify that this folder is actually assigned to this user
    $stmt = $conn->prepare("SELECT * FROM folder_requests WHERE assigned_folder_id = ? AND user_email = ? AND status = 'Approved'");
    $stmt->bind_param("ss", $folder_id, $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Either folder doesn't exist or doesn't belong to this user
        $_SESSION['error'] = "You don't have access to this folder.";
        header("location:user_dashboard.php");
        exit();
    }

    $folder_info = $result->fetch_assoc();
    $stmt->close();

    // Get folder details
    $stmt = $conn->prepare("SELECT * FROM folders WHERE folder_id = ?");
    $stmt->bind_param("s", $folder_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $folder_details = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = "Folder not found in the system.";
        header("location:user_dashboard.php");
        exit();
    }
    $stmt->close();

    // Handle file upload
    $message = "";
    $error = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["upload"])) {
        // Check if file was uploaded without errors
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
            $target_dir = "../uploads/" . $folder_id . "/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_name = basename($_FILES["file"]["name"]);
            $target_file = $target_dir . $file_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $file_size = $_FILES["file"]["size"];
            
            // Check file size (limit to 10MB)
            if ($file_size > 10000000) {
                $error = "File is too large. Maximum size is 10MB.";
            } 
            // Allow certain file formats
            else if (!in_array($file_type, ["pdf", "doc", "docx", "txt", "jpg", "jpeg", "png", "xlsx", "xls", "ppt", "pptx"])) {
                $error = "Only PDF, DOC, DOCX, TXT, JPG, JPEG, PNG, XLS, XLSX, PPT, and PPTX files are allowed.";
            } 
            // Check if file already exists
            else if (file_exists($target_file)) {
                $error = "File already exists. Please rename your file or upload a different one.";
            } 
            // Try to upload file
            else if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                $upload_date = date("Y-m-d H:i:s");
                
                // Save file info to database - using the correct column names
                $stmt = $conn->prepare("INSERT INTO folder_files (folder_id, name, file_path, size, file_type, timers) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssiss", $folder_id, $file_name, $target_file, $file_size, $file_type, $upload_date);
                
                if ($stmt->execute()) {
                    $message = "File uploaded successfully.";
                } else {
                    $error = "Error recording file in database: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error = "Error: " . $_FILES["file"]["error"];
        }
    }

    // Handle file deletion
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"]) && isset($_POST["file_id"])) {
        $file_id = $_POST["file_id"];
        
        // First get file info to verify ownership and get path for deletion
        $stmt = $conn->prepare("SELECT * FROM folder_files WHERE id = ? AND folder_id = ?");
        $stmt->bind_param("is", $file_id, $folder_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $file_info = $result->fetch_assoc();
            $file_path = $file_info["file_path"];
            
            // Delete from database
            $delete_stmt = $conn->prepare("DELETE FROM folder_files WHERE id = ?");
            $delete_stmt->bind_param("i", $file_id);
            
            if ($delete_stmt->execute()) {
                // Delete the actual file
                if (file_exists($file_path) && unlink($file_path)) {
                    $message = "File deleted successfully.";
                } else {
                    $message = "File removed from database but could not delete from storage.";
                }
            } else {
                $error = "Error deleting file: " . $delete_stmt->error;
            }
            $delete_stmt->close();
        } else {
            $error = "You don't have permission to delete this file or it doesn't exist.";
        }
        $stmt->close();
    }

    // Function to get file icon based on type
    function getFileIcon($fileType) {
        switch(strtolower($fileType)) {
            case 'pdf':
                return 'fa-file-pdf';
            case 'doc':
            case 'docx':
                return 'fa-file-word';
            case 'xls':
            case 'xlsx':
                return 'fa-file-excel';
            case 'ppt':
            case 'pptx':
                return 'fa-file-powerpoint';
            case 'jpg':
            case 'jpeg':
            case 'png':
                return 'fa-file-image';
            case 'txt':
                return 'fa-file-alt';
            default:
                return 'fa-file';
        }
    }

    // Function to get file icon class based on type
    function getFileIconClass($fileType) {
        switch(strtolower($fileType)) {
            case 'pdf':
                return 'pdf';
            case 'doc':
            case 'docx':
                return 'doc';
            case 'xls':
            case 'xlsx':
                return 'xls';
            case 'ppt':
            case 'pptx':
                return 'ppt';
            case 'jpg':
            case 'jpeg':
            case 'png':
                return 'img';
            case 'txt':
                return 'txt';
            default:
                return '';
        }
    }

    // Function to format file size
    function formatFileSize($bytes) {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb-container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="user_dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a></li>
          <li class="breadcrumb-item"><a href="user_dashboard.php">My Folders</a></li>
          <li class="breadcrumb-item active"><?php echo htmlspecialchars($folder_details['FOLDER_NAME']); ?></li>
        </ol>
      </nav>
    </div>

    <!-- Alerts -->
    <?php if (!empty($message)): ?>
    <div class="alert alert-success d-flex align-items-center" role="alert">
      <i class="fas fa-check-circle me-2"></i>
      <div><?php echo $message; ?></div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i>
      <div><?php echo $error; ?></div>
    </div>
    <?php endif; ?>

    <!-- Folder Info Card -->
    <div class="folder-info-card">
      <div class="folder-title">
        <div class="folder-icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <h1 class="folder-name"><?php echo htmlspecialchars($folder_details['FOLDER_NAME']); ?></h1>
      </div>
      
      <p class="mb-0"><?php echo htmlspecialchars($folder_details['DESCRIPTION']); ?></p>
      
      <div class="folder-meta">
        <div class="folder-meta-item">
          <i class="fas fa-fingerprint"></i>
          <span>ID: <?php echo htmlspecialchars($folder_id); ?></span>
        </div>
        <div class="folder-meta-item">
          <i class="fas fa-check-circle"></i>
          <span>Status: Approved</span>
        </div>
        <div class="folder-meta-item">
          <i class="fas fa-tag"></i>
          <span>Request: <?php echo htmlspecialchars($folder_info['requested_folder_name']); ?></span>
        </div>
      </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-filter-container">
      <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="fileSearch" class="search-input" placeholder="Search files by name, type, or size...">
      </div>
      
      <select class="form-select filter-dropdown" id="fileTypeFilter">
        <option value="">All File Types</option>
        <option value="pdf">PDF</option>
        <option value="doc">Word Documents</option>
        <option value="xls">Excel Spreadsheets</option>
        <option value="ppt">PowerPoint</option>
        <option value="img">Images</option>
        <option value="txt">Text Files</option>
      </select>
    </div>

    <!-- Check for subfolders -->
    <?php
    // This is a placeholder for subfolder functionality
    // In a real implementation, you would query the database for subfolders
    // For now, we'll simulate some subfolders for demonstration
    
    // Simulated subfolders - in a real implementation, replace this with actual database query
    $has_subfolders = false; // Set to true if subfolders exist
    $subfolders = []; // Array to hold subfolder data
    
    // Uncomment and modify this code when you have actual subfolder data
    /*
    $subfolder_query = $conn->prepare("SELECT * FROM folders WHERE parent_folder_id = ?");
    $subfolder_query->bind_param("s", $folder_id);
    $subfolder_query->execute();
    $subfolder_result = $subfolder_query->get_result();
    
    if ($subfolder_result->num_rows > 0) {
        $has_subfolders = true;
        while ($subfolder = $subfolder_result->fetch_assoc()) {
            $subfolders[] = $subfolder;
        }
    }
    $subfolder_query->close();
    */
    
    // For demonstration, let's simulate some subfolders
    if ($folder_id === "example_folder_with_subfolders") {
        $has_subfolders = true;
        $subfolders = [
            [
                'folder_id' => 'subfolder1',
                'FOLDER_NAME' => 'Documents',
                'file_count' => 5
            ],
            [
                'folder_id' => 'subfolder2',
                'FOLDER_NAME' => 'Images',
                'file_count' => 12
            ],
            [
                'folder_id' => 'subfolder3',
                'FOLDER_NAME' => 'Presentations',
                'file_count' => 3
            ]
        ];
    }
    ?>

    <!-- Subfolders Section (if any) -->
    <?php if ($has_subfolders): ?>
    <div class="subfolder-container">
      <?php foreach ($subfolders as $subfolder): ?>
      <a href="manage_file.php?folder_id=<?php echo $subfolder['folder_id']; ?>" class="subfolder-item">
        <div class="subfolder-icon">
          <i class="fas fa-folder"></i>
        </div>
        <div class="subfolder-name"><?php echo htmlspecialchars($subfolder['FOLDER_NAME']); ?></div>
        <div class="subfolder-info"><?php echo $subfolder['file_count']; ?> files</div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Upload Card -->
    <div class="upload-card">
      <div class="upload-header">
        <h2 class="upload-title">
          <i class="fas fa-cloud-upload-alt upload-icon"></i>
          Upload New File
        </h2>
      </div>
      
      <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
        <div class="dropzone" id="dropzone">
          <input type="file" name="file" id="fileInput" class="file-input" required>
          <div class="dropzone-icon">
            <i class="fas fa-file-upload"></i>
          </div>
          <h4 class="dropzone-text">Drag & drop files here or click to browse</h4>
          <p class="dropzone-hint">Supported formats: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG, XLS, XLSX, PPT, PPTX (Max: 10MB)</p>
        </div>
        
        <div id="selectedFile" style="display: none;" class="selected-file">
          <i class="fas fa-file selected-file-icon" id="selectedFileIcon"></i>
          <span class="selected-file-name" id="selectedFileName">filename.pdf</span>
          <span class="selected-file-size" id="selectedFileSize">2.5 MB</span>
          <i class="fas fa-times selected-file-remove" id="removeFile"></i>
        </div>
        
        <div class="upload-actions">
          <button type="submit" name="upload" class="btn btn-primary">
            <i class="fas fa-upload me-2"></i> Upload File
          </button>
        </div>
      </form>
    </div>

    <!-- Files Card -->
    <div class="files-card">
      <div class="files-header">
        <h2 class="files-title">
          <i class="fas fa-file-alt files-icon"></i>
          Files in Folder
        </h2>
        
        <?php
        // Get all files in this folder
        $stmt = $conn->prepare("SELECT * FROM folder_files WHERE folder_id = ? ORDER BY timers DESC");
        $stmt->bind_param("s", $folder_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $file_count = $result->num_rows;
        ?>
        
        <span class="files-count"><?php echo $file_count; ?> files</span>
      </div>
      
      <div class="files-table-container">
        <?php if ($file_count > 0): ?>
        <table class="files-table" id="filesTable">
          <thead>
            <tr>
              <th>File Name</th>
              <th>Type</th>
              <th>Size</th>
              <th>Upload Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="file-row">
              <td>
                <div class="file-cell">
                  <div class="file-cell-icon <?php echo getFileIconClass($row['file_type']); ?>">
                    <i class="fas <?php echo getFileIcon($row['file_type']); ?>"></i>
                  </div>
                  <div>
                    <div class="file-cell-name"><?php echo htmlspecialchars($row['name']); ?></div>
                    <div class="file-cell-info">Uploaded by you</div>
                  </div>
                </div>
              </td>
              <td>
                <span class="file-type-badge"><?php echo strtoupper(htmlspecialchars($row['file_type'])); ?></span>
              </td>
              <td>
                <?php echo formatFileSize($row['size']); ?>
              </td>
              <td>
                <?php echo date('M d, Y g:i A', strtotime($row['timers'])); ?>
              </td>
              <td>
                <div class="file-actions">
                  <a href="preview_files.php?file_id=<?php echo $row['id']; ?>&folder_id=<?php echo $folder_id; ?>" class="file-action-btn preview" title="Preview">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="<?php echo htmlspecialchars($row['file_path']); ?>" class="file-action-btn download" download title="Download">
                    <i class="fas fa-download"></i>
                  </a>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this file?')">
                    <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete" class="file-action-btn delete" title="Delete">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-folder-open"></i>
          </div>
          <h3 class="empty-state-title">No files in this folder yet</h3>
          <p class="empty-state-text">Upload a file to get started</p>
          <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
            <i class="fas fa-upload me-2"></i> Upload Your First File
          </button>
        </div>
        <?php endif;
        $stmt->close();
        ?>
      </div>
    </div>
    
    <!-- Footer -->
    <footer class="mt-4 text-center text-muted">
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
      
      // File Upload Handling
      const dropzone = document.getElementById('dropzone');
      const fileInput = document.getElementById('fileInput');
      const selectedFile = document.getElementById('selectedFile');
      const selectedFileName = document.getElementById('selectedFileName');
      const selectedFileSize = document.getElementById('selectedFileSize');
      const selectedFileIcon = document.getElementById('selectedFileIcon');
      const removeFile = document.getElementById('removeFile');
      
      // Prevent default behavior for drag events
      ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
      });
      
      function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
      }
      
      // Highlight dropzone when dragging over it
      ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, highlight, false);
      });
      
      ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, unhighlight, false);
      });
      
      function highlight() {
        dropzone.classList.add('dragover');
      }
      
      function unhighlight() {
        dropzone.classList.remove('dragover');
      }
      
      // Handle dropped files
      dropzone.addEventListener('drop', handleDrop, false);
      
      function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
          fileInput.files = files;
          updateFileInfo(files[0]);
        }
      }
      
      // Handle file selection via input
      fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
          updateFileInfo(this.files[0]);
        } else {
          resetFileSelection();
        }
      });
      
      // Update file info display
      function updateFileInfo(file) {
        selectedFileName.textContent = file.name;
        selectedFileSize.textContent = formatSize(file.size);
        
        // Update icon based on file type
        const fileExtension = file.name.split('.').pop().toLowerCase();
        let iconClass = 'fa-file';
        
        switch(fileExtension) {
          case 'pdf':
            iconClass = 'fa-file-pdf';
            selectedFileIcon.style.color = '#e53e3e';
            break;
          case 'doc':
          case 'docx':
            iconClass = 'fa-file-word';
            selectedFileIcon.style.color = '#3182ce';
            break;
          case 'xls':
          case 'xlsx':
            iconClass = 'fa-file-excel';
            selectedFileIcon.style.color = '#38a169';
            break;
          case 'ppt':
          case 'pptx':
            iconClass = 'fa-file-powerpoint';
            selectedFileIcon.style.color = '#dd6b20';
            break;
          case 'jpg':
          case 'jpeg':
          case 'png':
            iconClass = 'fa-file-image';
            selectedFileIcon.style.color = '#38a169';
            break;
          case 'txt':
            iconClass = 'fa-file-alt';
            selectedFileIcon.style.color = '#718096';
            break;
        }
        
        selectedFileIcon.className = 'fas ' + iconClass + ' selected-file-icon';
        selectedFile.style.display = 'flex';
        dropzone.style.display = 'none';
      }
      
      // Format file size
      function formatSize(bytes) {
        if (bytes >= 1048576) {
          return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
          return (bytes / 1024).toFixed(2) + ' KB';
        } else {
          return bytes + ' bytes';
        }
      }
      
      // Remove selected file
      removeFile.addEventListener('click', resetFileSelection);
      
      function resetFileSelection() {
        fileInput.value = '';
        selectedFile.style.display = 'none';
        dropzone.style.display = 'block';
      }
      
      // Click on dropzone to trigger file input
      dropzone.addEventListener('click', function() {
        fileInput.click();
      });
      
      // Search functionality
      $("#fileSearch").on("keyup", function() {
        const value = $(this).val().toLowerCase();
        $("#filesTable tbody tr").filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
      });
      
      // File type filter
      $("#fileTypeFilter").on("change", function() {
        const value = $(this).val().toLowerCase();
        
        if (value === "") {
          // Show all rows if no filter is selected
          $("#filesTable tbody tr").show();
        } else {
          // Hide all rows first
          $("#filesTable tbody tr").hide();
          
          // Show rows that match the selected file type
          $("#filesTable tbody tr").filter(function() {
            // Check if the row contains the selected file type
            if (value === "img") {
              // Special case for images (jpg, jpeg, png)
              return $(this).find(".file-type-badge").text().toLowerCase().match(/jpg|jpeg|png/);
            } else if (value === "doc") {
              // Special case for Word documents (doc, docx)
              return $(this).find(".file-type-badge").text().toLowerCase().match(/doc|docx/);
            } else if (value === "xls") {
              // Special case for Excel files (xls, xlsx)
              return $(this).find(".file-type-badge").text().toLowerCase().match(/xls|xlsx/);
            } else if (value === "ppt") {
              // Special case for PowerPoint files (ppt, pptx)
              return $(this).find(".file-type-badge").text().toLowerCase().match(/ppt|pptx/);
            } else {
              // For other file types
              return $(this).find(".file-type-badge").text().toLowerCase().indexOf(value) > -1;
            }
          }).show();
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