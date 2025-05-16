<?php
session_start();
if(!isset($_SESSION["email_address"])){
    header("location:../login.html");
    exit();
} 

// Get user info
require_once("include/connection.php");
$id = mysqli_real_escape_string($conn,$_SESSION['email_address']);
$r = mysqli_query($conn,"SELECT * FROM login_user where id = '$id'") or die (mysqli_error($conn));
$row = mysqli_fetch_array($r);
$id = $row['email_address'];
$user_status = $row['user_status'];
$name = $row['name'];
$user_avatar = strtoupper(substr($id, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Upload File - BitKeep Management</title>
  
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

    .sidebar-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 15px;
    }

    .sidebar-logo img {
      width: 40px;
      height: 40px;
      margin-right: 10px;
    }

    .sidebar-logo h4 {
      margin: 0;
      font-weight: 600;
      font-size: 1.2rem;
    }

    .sidebar-logo span {
      color: #F0B56F;
    }

    .user-profile {
      display: flex;
      align-items: center;
      padding: 15px 20px;
      border-radius: 10px;
      background-color: var(--primary-light);
      margin-bottom: 15px;
    }

    .user-avatar {
      width: 45px;
      height: 45px;
      background-color: var(--primary-color);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 1.2rem;
      margin-right: 12px;
    }

    .user-info {
      flex: 1;
    }

    .user-info h6 {
      margin: 0;
      font-weight: 600;
      font-size: 0.9rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 150px;
    }

    .user-info p {
      margin: 0;
      font-size: 0.8rem;
      color: var(--gray-color);
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

    .sidebar-divider {
      height: 1px;
      background-color: var(--border-color);
      margin: 15px;
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

    .page-title {
      font-weight: 600;
      color: var(--dark-color);
      margin: 0;
      display: flex;
      align-items: center;
    }

    .page-title i {
      margin-right: 10px;
      color: var(--primary-color);
      font-size: 1.2rem;
    }

    .navbar-actions {
      display: flex;
      align-items: center;
    }

    .navbar-actions .btn {
      margin-left: 10px;
    }

    /* Upload Card */
    .upload-card {
      background: white;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 25px;
      overflow: hidden;
      transition: all 0.3s;
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

    /* Form Controls */
    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      font-weight: 600;
      margin-bottom: 8px;
      display: block;
    }

    .form-control {
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 12px 15px;
      width: 100%;
      transition: all 0.3s;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
      outline: none;
    }

    .form-text {
      color: var(--gray-color);
      font-size: 0.85rem;
      margin-top: 5px;
    }

    textarea.form-control {
      min-height: 120px;
      resize: vertical;
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
      cursor: pointer;
    }

    .upload-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }

    .upload-button i {
      margin-right: 10px;
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

    /* Mobile Sidebar Toggle */
    .sidebar-toggle {
      display: none;
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 50%;
      width: 45px;
      height: 45px;
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 999;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
      }
      
      .sidebar-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
      }
    }
    
    @media (max-width: 768px) {
      .top-navbar {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .navbar-actions {
        margin-top: 15px;
        width: 100%;
        justify-content: flex-end;
      }
      
      .upload-body {
        padding: 20px 15px;
      }
      
      .allowed-file-types {
        justify-content: center;
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

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <img src="js/img/Files_Download.png" alt="BitKeep Logo">
        <h4><span>Bit</span>Keep</h4>
      </div>
      
      <div class="user-profile">
        <div class="user-avatar">
          <?php echo $user_avatar; ?>
        </div>
        <div class="user-info">
          <h6><?php echo htmlspecialchars($name); ?></h6>
          <p><?php echo htmlspecialchars($user_status); ?></p>
        </div>
      </div>
    </div>
    
    <div class="sidebar-menu">
      <a href="home.php" class="nav-link">
        <i class="fas fa-home"></i> Dashboard
      </a>
      <a href="add_file.php" class="nav-link active">
        <i class="fas fa-file-upload"></i> Upload Files
      </a>
      <a href="history_log.php" class="nav-link">
        <i class="fas fa-history"></i> User Logs
      </a>
      <a href="request_folder.php" class="nav-link">
        <i class="fas fa-folder-plus"></i> Request Folder
      </a>
      
      <div class="sidebar-divider"></div>
      
      <a href="Logout.php" class="nav-link">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>

  <!-- Mobile Sidebar Toggle -->
  <button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
      <h4 class="page-title">
        <i class="fas fa-file-upload"></i> Upload File
     
    </div>

    <!-- Upload Card -->
    <div class="upload-card animate-slide-up">
      <div class="upload-header">
        <h4><i class="fas fa-cloud-upload-alt"></i>Upload New File</h4>
      </div>
      <div class="upload-body">
        <!-- User Info Card -->
        <div class="user-info-card">
          <i class="fas fa-user-circle"></i>
          <div class="user-details">
            <h6><?php echo htmlspecialchars($name); ?></h6>
            <p>Status: <?php echo htmlspecialchars($user_status); ?></p>
          </div>
        </div>

        <form action="fileprocess.php" method="post" enctype="multipart/form-data" id="uploadForm">
          <!-- Hidden Fields -->
          <input type="hidden" name="email" value="<?php echo htmlspecialchars($name); ?>">
          
          <div class="form-group">
            <label for="comment" class="form-label">File Description</label>
            <textarea id="comment" name="comment" class="form-control" placeholder="Add a description about this file (optional)"></textarea>
            <div class="form-text">Adding a description helps others understand what this file contains.</div>
          </div>
          
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

          <div class="d-flex justify-content-end mt-4">
            <a href="home.php" class="btn btn-light me-2">
              <i class="fas fa-times me-1"></i> Cancel
            </a>
            <button type="submit" name="save" class="upload-button" id="uploadBtn" disabled>
              <i class="fas fa-cloud-upload-alt"></i> Upload File
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

    // Mobile Sidebar Toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
      const sidebar = document.getElementById('sidebar');
      const sidebarToggle = document.getElementById('sidebarToggle');
      
      if (window.innerWidth <= 992) {
        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target) && sidebar.classList.contains('active')) {
          sidebar.classList.remove('active');
        }
      }
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