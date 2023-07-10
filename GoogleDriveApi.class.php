<?php
session_start();

// Check if the user is not authenticated
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    // Store the current page in the session for redirection after login
    $_SESSION['requested_page'] = $_SERVER['REQUEST_URI'];

    // Redirect to the login page
    header('Location: login.php');
    exit();
}

// Authenticated user can access the index.php page



class GoogleDriveApi {
    const OAUTH2_TOKEN_URI = 'https://oauth2.googleapis.com/token';
    const DRIVE_FILE_META_URI = 'https://www.googleapis.com/drive/v3/files/';

    function __construct($params = array()) {
        if (count($params) > 0){
            $this->initialize($params);
        }
    }

    function initialize($params = array()) {
        if (count($params) > 0){
            foreach ($params as $key => $val){
                if (isset($this->$key)){
                    $this->$key = $val;
                }
            }
        }
    }

    public function GetAccessToken($client_id, $redirect_uri, $client_secret, $code) {
        $curlPost = 'client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $client_secret . '&code='. $code . '&grant_type=authorization_code';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::OAUTH2_TOKEN_URI);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);

        if ($http_code != 200) {
            $error_msg = 'Failed to receive access token';
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            } else {
                $error_msg = !empty($data['error']['message']) ? $data['error']['message'] : '';
            }
            throw new Exception('Error '.$http_code.': '.$error_msg);
        }

        return $data;
    }

    public function GetFileMeta($access_token, $file_id, $googleOauthURL = '') {
        $apiURL = self::DRIVE_FILE_META_URI . $file_id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '. $access_token));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $data = json_decode(curl_exec($ch), true);

        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);

        if ($http_code != 200) {
            $error_msg = 'Failed to retrieve file metadata';
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            } else {
                $error_msg = !empty($data['error']['message']) ? $data['error']['message'] : '';
            }

            if($http_code == 401 && !empty($googleOauthURL)){
                unset($_SESSION['google_access_token']);
                $error_msg .= '<br/>Click to <a href="'.$googleOauthURL.'">authenticate with Google Drive</a>';
            }

            throw new Exception('Error '.$http_code.': '.$error_msg);
        }

        return $data;
    }

    public function GetFileMediaContent($access_token, $file_id, $googleOauthURL = '') {
        $apiURL = self::DRIVE_FILE_META_URI . $file_id . '?alt=media';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '. $access_token));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $data = curl_exec($ch);

        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);

        if ($http_code != 200) {
            $error_msg = 'Failed to retrieve file data';
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            } else {
                $error_msg = !empty($data['error']['message']) ? $data['error']['message'] : '';
            }

            if($http_code == 401 && !empty($googleOauthURL)){
                unset($_SESSION['google_access_token']);
                $error_msg .= '<br/>Click to <a href="'.$googleOauthURL.'">authenticate with Google Drive</a>';
            }

            throw new Exception('Error '.$http_code.': '.$error_msg);
        }

        return $data;
    }
}

?>
