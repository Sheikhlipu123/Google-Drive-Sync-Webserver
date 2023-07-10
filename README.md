# Google Drive Sync

This PHP script allows you to download files from Google Drive using the Google Drive API.

## Prerequisites

- PHP 5.4+
- Google API credentials

## Set up Google API credentials:

- Go to the Google Cloud Console.
- Create a new project or select an existing project.
- Enable the Google Drive API for your project.
- Create OAuth 2.0 credentials and download the client configuration JSON file.
## Configure the script:

- Rename GoogleDriveApi.class.example.php to GoogleDriveApi.class.php.
- Open GoogleDriveApi.class.php and update the following constants:
- GOOGLE_CLIENT_ID: Your Google API client ID
- GOOGLE_CLIENT_SECRET: Your Google API client secret
- GOOGLE_OAUTH_SCOPE: The desired Google Drive API scope
- REDIRECT_URI: The URI where the user will be redirected after authentication


License
This project is licensed under the MIT License. See the LICENSE file for details.
