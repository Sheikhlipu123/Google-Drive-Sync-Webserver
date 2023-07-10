<?php

// Include and initialize Google Drive API handler class
include_once 'GoogleDriveApi.class.php';
$GoogleDriveApi = new GoogleDriveApi();

// Set the target folder to store the downloaded file
$target_folder = 'drive_files';

// Start the session
if (!session_id()) {
    session_start();
}

$statusMsg = '';
$status = 'danger';

// Google API configuration
define('GOOGLE_CLIENT_ID', 'your.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'your-CUVAKuL');
define('GOOGLE_OAUTH_SCOPE', 'https://www.googleapis.com/auth/drive');
define('REDIRECT_URI', 'your/google_drive_sync.php');

$googleOauthURL = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode(GOOGLE_OAUTH_SCOPE) . '&redirect_uri=' . REDIRECT_URI . '&response_type=code&client_id=' . GOOGLE_CLIENT_ID . '&access_type=offline';

$redirectURL = 'google_drive_sync.php';

// If the form is submitted
if (isset($_POST['submit'])) {
    // Validate the file ID
    if (empty($_POST["file_id"])) {
        $statusMsg = 'Please enter the ID of the Google Drive file.';
    } else {
        $drive_file_id = $_POST["file_id"];

        // Get the access token
        if (!empty($_SESSION['google_access_token'])) {
            $access_token = $_SESSION['google_access_token'];
        } else {
            // Redirect to the Google authentication site
            header("Location: $googleOauthURL");
            exit();
        }

        try {
            // Fetch file metadata from Google Drive
            $drive_file_data = $GoogleDriveApi->GetFileMeta($access_token, $drive_file_id, $googleOauthURL);

            // File information
            $drive_file_id = $drive_file_data['id'];
            $drive_file_name = $drive_file_data['name'];
            $drive_file_mime_type = $drive_file_data['mimeType'];

            // File save path
            $target_file = $target_folder . '/' . $drive_file_name;

            // Fetch file content from Google Drive
            $drive_file_content = $GoogleDriveApi->GetFileMediaContent($access_token, $drive_file_id, $googleOauthURL);

            // Save file on the server
            file_put_contents($target_file, $drive_file_content);

            $status = 'success';
            $statusMsg = 'The file has been downloaded from Google Drive and saved on the server successfully!';
            $redirectURL = 'google_drive_sync.php';
        } catch (Exception $e) {
            $statusMsg = 'Failed to download the file: ' . $e->getMessage();
        }
    }
} elseif (isset($_GET['code'])) {
    try {
        // Get the access token
        $data = $GoogleDriveApi->GetAccessToken(GOOGLE_CLIENT_ID, REDIRECT_URI, GOOGLE_CLIENT_SECRET, $_GET['code']);
        $access_token = $data['access_token'];
        $_SESSION['google_access_token'] = $access_token;

        // Redirect back to the sync page
        header("Location: $redirectURL");
        exit();
    } catch (Exception $e) {
        $statusMsg = 'Failed to fetch access token: ' . $e->getMessage();
    }
} else {
    if (empty($_SESSION['google_access_token'])) {
        // Redirect to the Google authentication site
        header("Location: $googleOauthURL");
        exit();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Google Drive Sync</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Google Drive Sync</h1>
        <?php if (!empty($statusMsg)) { ?>
            <div class="alert alert-<?php echo $status; ?>"><?php echo $statusMsg; ?></div>
        <?php } ?>
        <div class="row">
            <div class="col-md-6">
                <form method="post" action="google_drive_sync.php">
                    <div class="mb-3">
                        <label for="file_id" class="form-label">Drive File ID:</label>
                        <input type="text" name="file_id" id="file_id" class="form-control" placeholder="Enter ID of Google Drive file" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Download</button>
                </form>
               
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
