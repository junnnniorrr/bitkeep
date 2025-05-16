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
    $user_name = isset($row['name']) ? $row['name'] : $user_email;
    $user_avatar = strtoupper(substr($user_email, 0, 1));
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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Request Folder - BitKeep Management</title>
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
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

    /* Content Card */
    .content-card {
      background: white;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 25px;
      overflow: hidden;
      transition: all 0.3s;
    }

    .content-card:hover {
      box-shadow: var(--hover-shadow);
      transform: translateY(-3px);
    }

    .card-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 20px;
      position: relative;
    }

    .card-header h4 {
      margin: 0;
      font-weight: 600;
      display: flex;
      align-items: center;
    }

    .card-header i {
      margin-right: 10px;
      font-size: 1.5rem;
    }

    .card-body {
      padding: 30px;
    }

    /* Form Styles */
    .form-label {
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 8px;
    }

    .form-control {
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 0.95rem;
      transition: all 0.3s;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
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

    /* Button Styles */
    .btn {
      font-weight: 500;
      padding: 10px 20px;
      border-radius: 8px;
      transition: all 0.3s;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border: none;
      color: white;
      box-shadow: 0 4px 10px rgba(67, 97, 238, 0.2);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(67, 97, 238, 0.3);
    }

    .btn-light {
      background-color: #f8f9fa;
      border-color: #e9ecef;
      color: var(--dark-color);
    }

    .btn-light:hover {
      background-color: #e9ecef;
      transform: translateY(-2px);
    }

    /* Guidelines Card */
    .guidelines-card {
      background: white;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 25px;
      overflow: hidden;
      transition: all 0.3s;
    }

    .guidelines-card:hover {
      box-shadow: var(--hover-shadow);
      transform: translateY(-3px);
    }

    .guidelines-header {
      background: linear-gradient(135deg, #4cc9f0, #4361ee);
      color: white;
      padding: 20px;
      position: relative;
    }

    .guidelines-header h4 {
      margin: 0;
      font-weight: 600;
      display: flex;
      align-items: center;
    }

    .guidelines-header i {
      margin-right: 10px;
      font-size: 1.5rem;
    }

    .guidelines-body {
      padding: 20px;
    }

    .guideline-item {
      display: flex;
      align-items: flex-start;
      padding: 15px;
      border-bottom: 1px solid var(--border-color);
    }

    .guideline-item:last-child {
      border-bottom: none;
    }

    .guideline-icon {
      width: 36px;
      height: 36px;
      background-color: rgba(76, 201, 240, 0.1);
      color: var(--success-color);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      flex-shrink: 0;
    }

    .guideline-content {
      flex: 1;
    }

    .guideline-title {
      font-weight: 600;
      margin-bottom: 5px;
      color: var(--dark-color);
    }

    .guideline-text {
      color: var(--gray-color);
      font-size: 0.9rem;
      margin: 0;
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
      
      .sign-out-btn {
        margin-top: 10px;
      }
      
      .card-body, .guidelines-body {
        padding: 20px 15px;
      }
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
      <img src="img/image1.svg" alt="BitKeep Logo">
    </div>
    <div class="list-group list-group-flush">
      <a href="home.php" class="nav-link">
        <i class="fas fa-tachometer-alt"></i> Dashboard
      </a>
      <a href="add_file.php" class="nav-link">
        <i class="fas fa-file-upload"></i> Upload Files
      </a>
      <a href="history_log.php" class="nav-link">
        <i class="fas fa-history"></i> User Logs
      </a>
      <a href="request_folder.php" class="nav-link active">
        <i class="fas fa-folder-plus"></i> Request Folder
      </a>
      <a href="user_dashboard.php" class="nav-link">
        <i class="fas fa-folder-open"></i> My Folders
      </a>
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
      <div class="user-welcome">
        <div class="user-avatar">
          <?php echo $user_avatar; ?>
        </div>
        Welcome, <?php echo htmlspecialchars($user_name); ?>!
      </div>
      <a href="Logout.php" class="sign-out-btn">
        <i class="fas fa-sign-out-alt me-2"></i>Sign Out
      </a>
    </div>

    <!-- Breadcrumb -->
    <div class="breadcrumb-container animate-fade-in">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="home.php"><i class="fas fa-home me-1"></i>Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Request Folder</li>
        </ol>
      </nav>
    </div>

    <!-- Request Form Card -->
    <div class="content-card animate-slide-up">
      <div class="card-header">
        <h4><i class="fas fa-folder-plus"></i>Request New Folder</h4>
      </div>
      <div class="card-body">
        <p class="text-muted mb-4">
          Fill in the details below to request a new folder. Your request will be reviewed by an administrator.
        </p>
        
        <form action="submit_folder_request.php" method="POST">
          <div class="mb-4">
            <label for="folder_name" class="form-label">Folder Name</label>
            <input type="text" id="folder_name" name="requested_folder_name" class="form-control" required>
            <div class="form-text">Please provide a meaningful name for your folder.</div>
          </div>
          
          <div class="mb-4">
            <label for="reason" class="form-label">Reason for Request</label>
            <textarea id="reason" name="reason" class="form-control" required></textarea>
            <div class="form-text">Please explain why you need this folder and what it will be used for.</div>
          </div>
          
          <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($user_email); ?>">
          
          <div class="d-flex justify-content-end">
            <a href="home.php" class="btn btn-light me-2">
              <i class="fas fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-paper-plane me-1"></i> Submit Request
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Guidelines Card -->
    <div class="guidelines-card animate-slide-up">
      <div class="guidelines-header">
        <h4><i class="fas fa-info-circle"></i>Folder Request Guidelines</h4>
      </div>
      <div class="guidelines-body">
        <div class="guideline-item">
          <div class="guideline-icon">
            <i class="fas fa-check"></i>
          </div>
          <div class="guideline-content">
            <div class="guideline-title">Naming Convention</div>
            <p class="guideline-text">Use clear, descriptive names that reflect the folder's purpose. Avoid special characters and keep names concise.</p>
          </div>
        </div>
        
        <div class="guideline-item">
          <div class="guideline-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="guideline-content">
            <div class="guideline-title">Request Approval</div>
            <p class="guideline-text">Folder requests are typically reviewed within 24-48 hours. You'll receive an email notification once your request is processed.</p>
          </div>
        </div>
        
        <div class="guideline-item">
          <div class="guideline-icon">
            <i class="fas fa-database"></i>
          </div>
          <div class="guideline-content">
            <div class="guideline-title">Size Limits</div>
            <p class="guideline-text">Each folder has a default storage limit of 500MB. If you need additional space, please specify in your request.</p>
          </div>
        </div>
        
        <div class="guideline-item">
          <div class="guideline-icon">
            <i class="fas fa-archive"></i>
          </div>
          <div class="guideline-content">
            <div class="guideline-title">Retention Policy</div>
            <p class="guideline-text">Folders unused for 90 days may be archived or deleted. Regular activity ensures your folder remains active.</p>
          </div>
        </div>
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

    // Form validation enhancement
    const folderNameInput = document.getElementById('folder_name');
    const reasonTextarea = document.getElementById('reason');
    
    folderNameInput.addEventListener('input', function() {
      // Remove special characters as they're typed
      this.value = this.value.replace(/[^\w\s-]/gi, '');
    });
    
    // Character counter for reason textarea
    reasonTextarea.addEventListener('input', function() {
      const maxLength = 500;
      const currentLength = this.value.length;
      
      if (currentLength > maxLength) {
        this.value = this.value.substring(0, maxLength);
      }
      
      // If you want to show a character counter, uncomment this
      // const counter = document.getElementById('reason-counter');
      // counter.textContent = `${this.value.length}/${maxLength}`;
    });
  </script>
</body>
</html>