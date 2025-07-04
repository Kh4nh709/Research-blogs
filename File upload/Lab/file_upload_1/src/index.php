<?php
// error_reporting(0);

session_start();
if (!isset($_SESSION['dir'])) {
    $_SESSION['dir'] = 'upload/' . session_id();
}

// Create folder for each user
$dir = $_SESSION['dir'];
if (!file_exists($dir))
    mkdir($dir);
// show source code
if (isset($_GET["debug"])) die(highlight_file(__FILE__));

if (isset($_FILES["file"])) {
    $error = '';
    $success = '';
    try {
        $file = $dir . "/" . $_FILES["file"]["name"];
        move_uploaded_file($_FILES["file"]["tmp_name"], $file);
        $success = 'Successfully uploaded file at: <a href="/' . $file . '">/' . $file . ' </a><br>';
        $success .= 'View all uploaded file at: <a href="/' . $dir . '/">/' . $dir . ' </a>';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 2rem;
        }
        .upload-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 600px;
            margin-top: 2rem;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .upload-area:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .upload-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .btn-upload {
            width: 100%;
            padding: 0.75rem;
            font-size: 1.1rem;
        }
        .status-message {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 5px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }
        .debug-link {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
    </style>
</head>

<body>
    <a href="/?debug" class="debug-link btn btn-outline-secondary">View Source</a>
    
    <div class="upload-container">
        <h1 class="text-center mb-4">File Upload</h1>
        
        <form method="post" enctype="multipart/form-data" class="upload-form">
            <div class="upload-area" onclick="document.getElementById('file').click()">
                <div class="upload-icon">
                    <i class="bi bi-cloud-upload"></i>
                </div>
                <h4>Click to select or drag and drop files</h4>
                <p class="text-muted">Maximum file size: 10MB</p>
            </div>
            
            <input type="file" name="file" id="file" class="d-none" onchange="updateFileName()">
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-upload">
                    Upload File
                </button>
            </div>
        </form>

        <?php if (isset($error) && $error): ?>
            <div class="status-message error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success) && $success): ?>
            <div class="status-message success-message">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateFileName() {
            const fileInput = document.getElementById('file');
            const uploadArea = document.querySelector('.upload-area');
            if (fileInput.files.length > 0) {
                uploadArea.innerHTML = `
                    <div class="upload-icon">
                        <i class="bi bi-file-earmark"></i>
                    </div>
                    <h4>${fileInput.files[0].name}</h4>
                    <p class="text-muted">Click to change file</p>
                `;
            }
        }

        // Handle drag and drop
        const uploadArea = document.querySelector('.upload-area');
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            uploadArea.classList.add('border-primary');
        }

        function unhighlight(e) {
            uploadArea.classList.remove('border-primary');
        }

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const fileInput = document.getElementById('file');
            fileInput.files = dt.files;
            updateFileName();
        }
    </script>
</body>
</html>