<!DOCTYPE html>
<html lang="en">
<?php
// Initialize session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.html');
    exit();
}

// Get admin info
require_once("include/connection.php");
$id = mysqli_real_escape_string($conn, $_SESSION['admin_user']);
$r = mysqli_query($conn, "SELECT * FROM admin_login where id = '$id'") or die(mysqli_error($conn));
$row = mysqli_fetch_array($r);
$admin_email = $row['admin_user'];
$admin_status = $row['admin_status'];
$name = $row['name'];
?>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Upload Document - BitKeep Management</title>
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap core CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Material Design Bootstrap -->
  <link href="css/mdb.min.css" rel="stylesheet">
  
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
      text-decoration: none;
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
      text-decoration: none;
    }

    .sign-out-btn:hover {
      background-color: rgba(239, 35, 60, 0.1);
      color: var(--danger-color);
      transform: translateY(-2px);
    }

    /* Page Header */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
    }

    .page-header h4 {
      margin: 0;
      font-weight: 600;
      color: var(--dark-color);
      display: flex;
      align-items: center;
    }

    .page-header h4 i {
      margin-right: 10px;
      color: var(--primary-color);
    }

    .page-header .btn {
      padding: 8px 16px;
      border-radius: 8px;
      font-weight: 500;
      display: flex;
      align-items: center;
      transition: all 0.3s;
    }

    .page-header .btn i {
      margin-right: 8px;
    }

    .page-header .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Upload Card */
    .upload-card {
      background: white;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      overflow: hidden;
      transition: all 0.3s;
      max-width: 800px;
      margin: 0 auto 30px;
    }

    .upload-card:hover {
      box-shadow: var(--hover-shadow);
      transform: translateY(-3px);
    }

    .upload-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 20px;
      position: relative;
    }

    .upload-header h4 {
      margin: 0;
      font-weight: 600;
      display: flex;
      align-items: center;
    }

    .upload-header i {
      margin-right: 10px;
      font-size: 1.5rem;
    }

    .upload-body {
      padding: 30px;
    }

    /* File Upload Zone */
    .file-upload-zone {
      border: 2px dashed var(--border-color);
      border-radius: 12px;
      padding: 40px 20px;
      text-align: center;
      transition: all 0.3s;
      cursor: pointer;
      margin-bottom: 20px;
      position: relative;
    }

    .file-upload-zone:hover {
      border-color: var(--primary-color);
      background-color: var(--primary-light);
    }

    .file-upload-zone i {
      font-size: 3rem;
      color: var(--primary-color);
      margin-bottom: 15px;
    }

    .file-upload-zone h5 {
      font-weight: 600;
      margin-bottom: 10px;
    }

    .file-upload-zone p {
      color: var(--gray-color);
      margin-bottom: 0;
    }

    .file-upload-zone input[type="file"] {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      cursor: pointer;
    }

    /* File Info */
    .file-info {
      background-color: var(--light-color);
      border-radius: 8px;
      padding: 15px;
      margin-top: 20px;
      display: none;
    }

    .file-info.active {
      display: block;
      animation: fadeIn 0.5s;
    }

    .file-info-header {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
    }

    .file-info-header i {
      font-size: 1.5rem;
      margin-right: 10px;
      color: var(--primary-color);
    }

    .file-info-header h6 {
      margin: 0;
      font-weight: 600;
    }

    .file-info-body {
      font-size: 0.9rem;
      color: var(--gray-color);
    }

    /* Upload Button */
    .upload-button {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      border: none;
      border-radius: 8px;
      padding: 12px 25px;
      font-weight: 600;
      transition: all 0.3s;
      margin-top: 20px;
      display: inline-flex;
      align-items: center;
    }

    .upload-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }

    .upload-button i {
      margin-right: 10px;
    }

    /* File Type Icons */
    .file-type-icon {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      font-size: 1.2rem;
      color: white;
    }

    .file-type-pdf {
      background-color: #f40f02;
    }

    .file-type-doc, .file-type-docx {
      background-color: #2b579a;
    }

    .file-type-xls, .file-type-xlsx {
      background-color: #217346;
    }

    .file-type-ppt, .file-type-pptx {
      background-color: #d24726;
    }

    .file-type-zip {
      background-color: #ffc107;
    }

    .file-type-default {
      background-color: #6c757d;
    }

    /* Allowed File Types */
    .allowed-file-types {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 20px;
    }

    .file-type-badge {
      background-color: var(--light-color);
      border-radius: 6px;
      padding: 5px 10px;
      font-size: 0.8rem;
      font-weight: 500;
      display: flex;
      align-items: center;
    }

    .file-type-badge i {
      margin-right: 5px;
      font-size: 0.9rem;
    }

    /* User Info Card */
    .user-info-card {
      background-color: var(--light-color);
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }

    .user-info-card i {
      font-size: 1.5rem;
      margin-right: 15px;
      color: var(--primary-color);
    }

    .user-info-card .user-details h6 {
      margin: 0;
      font-weight: 600;
    }

    .user-info-card .user-details p {
      margin: 0;
      font-size: 0.9rem;
      color: var(--gray-color);
    }

    /* Footer */
    .footer {
      background: white;
      padding: 20px;
      text-align: center;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-top: 30px;
    }

    .footer p {
      margin: 0;
      color: var(--gray-color);
      font-size: 0.9rem;
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

    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .animate-fade-in {
      animation: fadeIn 0.5s ease;
    }

    @keyframes slideUp {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .animate-slide-up {
      animation: slideUp 0.5s ease;
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
      .page-header {
        flex-direction: column;
        align-items: flex-start;
      }
      .page-header .btn {
        margin-top: 15px;
      }
      .allowed-file-types {
        justify-content: center;
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
      <a href="folder_management.php" class="nav-link">
        <i class="fas fa-folder"></i> Folders
      </a>
       <a href="manage_requests.php" class="nav-link">
        <i class="fas fa-key"></i> Requests
      </a>
      <a href="add_document.php" class="nav-link active">
        <i class="fas fa-file-medical"></i> Documents
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
      <a href="logout.php" class="sign-out-btn">
        <i class="fas fa-sign-out-alt me-2"></i>Sign Out
      </a>
    </div>

    <!-- Page Header -->
    <div class="page-header animate-fade-in">
      <h4><i class="fas fa-file-upload"></i>Upload Document</h4>
      <a href="add_document.php" class="btn btn-primary">
        <i class="fas fa-file-alt"></i>View All Documents
      </a>
    </div>

    <!-- Upload Card -->
    <div class="upload-card animate-slide-up">
      <div class="upload-header">
        <h4><i class="fas fa-cloud-upload-alt"></i>Upload New Document</h4>
      </div>
      <div class="upload-body">
        <!-- User Info Card -->
        <div class="user-info-card">
          <i class="fas fa-user-circle"></i>
          <div class="user-details">
            <h6><?php echo htmlspecialchars($name); ?></h6>
            <p>Status: <?php echo htmlspecialchars($admin_status); ?></p>
          </div>
        </div>

        <form action="fileprocess.php" method="post" enctype="multipart/form-data" id="uploadForm">
          <!-- Hidden Fields -->
          <input type="hidden" name="email" value="<?php echo htmlspecialchars($name); ?>">
          
          <div class="file-upload-zone" id="dropZone">
            <i class="fas fa-file-upload"></i>
            <h5>Drag & Drop Files Here</h5>
            <p>or click to browse your files</p>
            <input type="file" name="myfile" id="fileInput" required>
          </div>

          <div class="file-info" id="fileInfo">
            <div class="file-info-header">
              <div id="fileTypeIcon" class="file-type-icon file-type-default">
                <i class="fas fa-file"></i>
              </div>
              <h6 id="fileName">No file selected</h6>
            </div>
            <div class="file-info-body">
              <p id="fileSize">0 KB</p>
            </div>
          </div>

          <div class="allowed-file-types">
            <div class="file-type-badge"><i class="fas fa-file-pdf"></i> PDF</div>
            <div class="file-type-badge"><i class="fas fa-file-word"></i> DOCX</div>
            <div class="file-type-badge"><i class="fas fa-file-word"></i> DOC</div>
            <div class="file-type-badge"><i class="fas fa-file-powerpoint"></i> PPTX</div>
            <div class="file-type-badge"><i class="fas fa-file-powerpoint"></i> PPT</div>
            <div class="file-type-badge"><i class="fas fa-file-excel"></i> XLSX</div>
            <div class="file-type-badge"><i class="fas fa-file-excel"></i> XLS</div>
            <div class="file-type-badge"><i class="fas fa-file-archive"></i> ZIP</div>
            <div class="file-type-badge"><i class="fas fa-file-alt"></i> ODT</div>
          </div>

          <div class="d-flex justify-content-between mt-4">
            <a href="add_document.php" class="btn btn-light">
              <i class="fas fa-arrow-left me-2"></i>Back to Documents
            </a>
            <button type="submit" name="save" class="upload-button" id="uploadBtn" disabled>
              <i class="fas fa-cloud-upload-alt"></i>Upload Document
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Footer -->
    <div class="footer">
      <p>All rights Reserved &copy; <?php echo date('Y'); ?> Created By: BitKeep Management</p>
    </div>
  </div>

  <!-- Add Admin Modal -->
  <div class="modal fade" id="modalRegisterForm" tabindex="-1" aria-labelledby="modalRegisterFormLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalRegisterFormLabel"><i class="fas fa-user-plus me-2 text-primary"></i>Add Admin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
  <div class="modal fade" id="modalRegisterForm2" tabindex="-1" aria-labelledby="modalRegisterForm2Label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalRegisterForm2Label"><i class="fas fa-user-plus me-2 text-primary"></i>Add User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
  <script>
    // Loader
    window.addEventListener('load', function() {
      setTimeout(function() {
        document.getElementById('loader').style.display = 'none';
      }, 500);
    });

    // File Upload Handling
    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('dropZone');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileTypeIcon = document.getElementById('fileTypeIcon');
    const uploadBtn = document.getElementById('uploadBtn');

    // Handle file selection
    fileInput.addEventListener('change', function() {
      handleFileSelection(this.files[0]);
    });

    // Handle drag and drop
    dropZone.addEventListener('dragover', function(e) {
      e.preventDefault();
      dropZone.style.borderColor = '#4361ee';
      dropZone.style.backgroundColor = '#eaefff';
    });

    dropZone.addEventListener('dragleave', function(e) {
      e.preventDefault();
      dropZone.style.borderColor = '#e9ecef';
      dropZone.style.backgroundColor = '';
    });

    dropZone.addEventListener('drop', function(e) {
      e.preventDefault();
      dropZone.style.borderColor = '#e9ecef';
      dropZone.style.backgroundColor = '';
      
      if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        handleFileSelection(e.dataTransfer.files[0]);
      }
    });

    function handleFileSelection(file) {
      if (file) {
        // Show file info
        fileInfo.classList.add('active');
        fileName.textContent = file.name;
        
        // Format file size
        const fileSizeKB = file.size / 1024;
        const fileSizeMB = fileSizeKB / 1024;
        
        if (fileSizeMB >= 1) {
          fileSize.textContent = fileSizeMB.toFixed(2) + ' MB';
        } else {
          fileSize.textContent = fileSizeKB.toFixed(2) + ' KB';
        }
        
        // Set file type icon
        const fileExtension = file.name.split('.').pop().toLowerCase();
        fileTypeIcon.className = 'file-type-icon';
        
        switch(fileExtension) {
          case 'pdf':
            fileTypeIcon.classList.add('file-type-pdf');
            fileTypeIcon.innerHTML = '<i class="fas fa-file-pdf"></i>';
            break;
          case 'doc':
          case 'docx':
            fileTypeIcon.classList.add('file-type-doc');
            fileTypeIcon.innerHTML = '<i class="fas fa-file-word"></i>';
            break;
          case 'xls':
          case 'xlsx':
            fileTypeIcon.classList.add('file-type-xls');
            fileTypeIcon.innerHTML = '<i class="fas fa-file-excel"></i>';
            break;
          case 'ppt':
          case 'pptx':
            fileTypeIcon.classList.add('file-type-ppt');
            fileTypeIcon.innerHTML = '<i class="fas fa-file-powerpoint"></i>';
            break;
          case 'zip':
            fileTypeIcon.classList.add('file-type-zip');
            fileTypeIcon.innerHTML = '<i class="fas fa-file-archive"></i>';
            break;
          default:
            fileTypeIcon.classList.add('file-type-default');
            fileTypeIcon.innerHTML = '<i class="fas fa-file"></i>';
        }
        
        // Check if file type is allowed
        const allowedExtensions = ['pdf', 'docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls', 'zip', 'odt'];
        if (allowedExtensions.includes(fileExtension)) {
          // Enable upload button
          uploadBtn.disabled = false;
        } else {
          // Show error and disable upload button
          alert('Invalid file type! Allowed types are: ' + allowedExtensions.join(', '));
          fileInput.value = '';
          fileInfo.classList.remove('active');
          uploadBtn.disabled = true;
        }
      } else {
        // Hide file info
        fileInfo.classList.remove('active');
        uploadBtn.disabled = true;
      }
    }

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