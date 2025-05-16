<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Subfolder</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #4285f4;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem;
            border: 1px solid #ced4da;
        }
        .btn-primary {
            background-color: #4285f4;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #357abd;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-folder-plus me-2"></i>Create New Subfolder</h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="subfolder_name" class="form-label">Subfolder Name</label>
                                <input type="text" class="form-control" id="subfolder_name" name="subfolder_name" required 
                                       placeholder="Enter subfolder name">
                            </div>
                            <input type="hidden" name="parent_id" value="<?php echo htmlspecialchars($_GET['parent_id'] ?? ''); ?>">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create Subfolder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
require_once("include/connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subfolder_name = mysqli_real_escape_string($conn, $_POST['subfolder_name']);
    $parent_id = mysqli_real_escape_string($conn, $_POST['parent_id']);

    $query = "INSERT INTO folders (FOLDER_NAME, PARENT_ID, TIMERS) VALUES ('$subfolder_name', '$parent_id', NOW())";
    if (mysqli_query($conn, $query)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                alert('Subfolder created successfully!');
                window.location.href='manage_folder.php?folder_id=" . $parent_id . "';
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                alert('Error creating subfolder: " . mysqli_error($conn) . "');
                history.back();
            });
        </script>";
    }
}
?>
