<!DOCTYPE html>
<html lang="en">
<?php

// Inialize session
session_start();

if (!isset($_SESSION['admin_user'])) {
     header('Location: index.html');
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
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Folder Management - BitKeep</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #4a6bff;
      --secondary-color: #34a853;
      --accent-color: #fbbc05;
      --danger-color: #ea4335;
      --dark-color: #2d3748;
      --light-color: #f8f9fa;
      --border-color: #e2e8f0;
      --success-color: #38a169;
      --warning-color: #e9b949;
    }

    body {
      background-color: #f7fafc;
      font-family: 'Poppins', sans-serif;
      color: #4a5568;
    }

    /* Sidebar Styles */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 280px;
      background: white;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
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
      background-color: rgba(74, 107, 255, 0.08);
      color: var(--primary-color);
      transform: translateX(3px);
    }

    .nav-link.active {
      background-color: var(--primary-color);
      color: white;
      box-shadow: 0 4px 8px rgba(74, 107, 255, 0.2);
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
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
      background-color: rgba(234, 67, 53, 0.1);
      color: var(--danger-color);
      transform: translateY(-2px);
    }

    /* Folder Card */
    .folder-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      margin-bottom: 25px;
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
    }
    
    .folder-card:hover {
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transform: translateY(-2px);
    }

    .folder-card .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px;
      border-bottom: 1px solid var(--border-color);
    }
    
    .folder-card .card-header h4 {
      margin: 0;
      font-weight: 600;
      color: var(--dark-color);
      display: flex;
      align-items: center;
    }
    
    .folder-card .card-header h4 i {
      color: var(--primary-color);
      margin-right: 10px;
    }

    /* Table Container */
    .table-container {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      padding: 25px;
      margin-bottom: 25px;
      border: 1px solid var(--border-color);
    }
    
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
      background-color: #4299e1;
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

    /* Folder Icon */
    .folder-icon {
      color: var(--accent-color);
      font-size: 1.2rem;
      margin-right: 10px;
    }
    
    /* Parent Folder Badge */
    .parent-badge {
      display: inline-block;
      padding: 0.35em 0.65em;
      font-size: 0.75em;
      font-weight: 600;
      line-height: 1;
      text-align: center;
      white-space: nowrap;
      vertical-align: baseline;
      border-radius: 6px;
      background-color: #e9eeff;
      color: var(--primary-color);
    }
    
    .parent-badge.root {
      background-color: #def7ec;
      color: var(--success-color);
    }

    /* Modal Styles */
    .modal-content {
      border-radius: 12px;
      border: none;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .modal-header {
      border-bottom: 1px solid var(--border-color);
      padding: 1.25rem 1.5rem;
    }
    
    .modal-header .modal-title {
      font-weight: 600;
      display: flex;
      align-items: center;
    }
    
    .modal-header .modal-title i {
      color: var(--primary-color);
      margin-right: 10px;
    }
    
    .modal-footer {
      border-top: 1px solid var(--border-color);
      padding: 1.25rem 1.5rem;
    }
    
    .form-label {
      font-weight: 500;
      color: var(--dark-color);
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(74, 107, 255, 0.25);
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
      color: var(--primary-color);
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
      box-shadow: 0 0 0 3px rgba(74, 107, 255, 0.15);
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
      background: rgba(74, 107, 255, 0.1) !important;
      border-color: rgba(74, 107, 255, 0.1) !important;
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
      .folder-card .card-header {
        flex-direction: column;
        align-items: flex-start;
      }
      .folder-card .card-header button {
        margin-top: 15px;
        width: 100%;
      }
    }
  </style>
</head>

<body>
  <!-- Loader -->
  <div id="loader">
    <div class="loader-content">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
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
      <a href="user_log.php" class="nav-link">
        <i class="fas fa-history"></i> User Log
        <a href="file_log.php" class="nav-link">
        <i class="fas fa-file-alt"></i> File Log
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

    <!-- Folder Management Content -->
    <div class="container-fluid px-0">
      <!-- Quick Actions -->
      <div class="folder-card">
        <div class="card-header">
          <h4><i class="fas fa-folder"></i>Root Folder Management</h4>
          <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#addFolderModal">
            <i class="fas fa-plus"></i>Add New Folder
          </button>
        </div>
        <div class="card-body p-4">
          <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle me-3 fs-4"></i>
            <div>
              Manage your root folders here. Only top-level folders are displayed. You can create, edit, and delete folders as needed.
            </div>
          </div>
        </div>
      </div>

      <!-- Folders Table -->
      <div class="table-container">
        <table id="foldersTable" class="table table-hover">
          <thead>
            <tr>
              <th>Folder Name</th>
              <th>Created Date</th>
              <th>Parent Folder</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            require_once("include/connection.php");
            // Modified query to only show root folders (where PARENT_ID is NULL or 0)
            $query = "SELECT f.*, p.FOLDER_NAME as PARENT_NAME 
                     FROM folders f 
                     LEFT JOIN folders p ON f.PARENT_ID = p.FOLDER_ID 
                     WHERE f.PARENT_ID IS NULL OR f.PARENT_ID = 0
                     ORDER BY f.TIMERS DESC";
            $result = mysqli_query($conn, $query);
            
            // Debug: Print the first row to see the actual column names
            $first_row = mysqli_fetch_assoc($result);
            if ($first_row) {
                // Reset the result pointer
                mysqli_data_seek($result, 0);
                
                // Get the column names
                $columns = array_keys($first_row);
                
                // Check if 'FOLDER_ID' exists, if not, try to find the ID column
                $id_column = 'FOLDER_ID';
                if (!in_array('FOLDER_ID', $columns)) {
                    // Try common ID column names
                    $possible_id_columns = ['id', 'ID', 'folder_id', 'folderid', 'FOLDERID'];
                    foreach ($possible_id_columns as $col) {
                        if (in_array($col, $columns)) {
                            $id_column = $col;
                            break;
                        }
                    }
                }
                
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td><i class='fas fa-folder folder-icon'></i>" . htmlspecialchars($row['FOLDER_NAME']) . "</td>";
                    echo "<td>" . date('M d, Y H:i', strtotime($row['TIMERS'])) . "</td>";
                    echo "<td><span class='parent-badge root'>Root</span></td>";
                    echo "<td>
                            <a href='manage_folder.php?folder_id=" . $row[$id_column] . "' class='btn btn-sm btn-info btn-action me-2' title='Manage Folder'>
                              <i class='fas fa-cog'></i> Manage
                            </a>
                            <a href='create_subfolder.php?parent_id=" . $row[$id_column] . "' class='btn btn-sm btn-success btn-action me-2' title='Create Subfolder'>
                              <i class='fas fa-folder-plus'></i> Subfolder
                            </a>
                            <a href='delete_folder.php?folder_id=" . $row[$id_column] . "' class='btn btn-sm btn-danger btn-action' 
                               onclick='return confirm(\"Are you sure you want to delete this folder?\")' title='Delete Folder'>
                              <i class='fas fa-trash'></i> Delete
                            </a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center py-4'>
                        <div class='d-flex flex-column align-items-center'>
                          <i class='fas fa-folder-open text-muted mb-3' style='font-size: 3rem;'></i>
                          <p class='mb-0'>No root folders found. Create your first folder to get started.</p>
                        </div>
                      </td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add Folder Modal -->
  <div class="modal fade" id="addFolderModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-folder-plus"></i>Add New Folder</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="add_folder_process.php" method="POST">
          <div class="modal-body">
            <div class="mb-3">
              <label for="folderName" class="form-label">Folder Name</label>
              <input type="text" class="form-control" id="folderName" name="folder_name" required>
            </div>
            <div class="mb-3">
              <label for="parentFolder" class="form-label">Parent Folder</label>
              <select class="form-select" id="parentFolder" name="parent_id">
                <option value="0" selected>Root</option>
                <?php
                $folders = mysqli_query($conn, "SELECT FOLDER_ID, FOLDER_NAME FROM folders ORDER BY FOLDER_NAME");
                while($folder = mysqli_fetch_assoc($folders)) {
                  echo "<option value='" . $folder['FOLDER_ID'] . "'>" . htmlspecialchars($folder['FOLDER_NAME']) . "</option>";
                }
                ?>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" name="save">Create Folder</button>
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
          <h5 class="modal-title"><i class="fas fa-user-plus"></i>Add Admin</h5>
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
            <button type="submit" class="btn btn-primary" name="reg">Add Admin</button>
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
          <h5 class="modal-title"><i class="fas fa-user-plus"></i>Add User</h5>
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
            <button type="submit" class="btn btn-primary" name="reguser">Add User</button>
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
      $('#foldersTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "order": [[1, "desc"]],
        "language": {
          "emptyTable": "No root folders found. Create your first folder to get started.",
          "zeroRecords": "No matching root folders found - try a different search term."
        }
      });
      
      // Initialize tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
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