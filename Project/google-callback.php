require_once 'vendor/autoload.php';
$client = new Google_Client();
$client->setClientId('YOUR_CLIENT_ID');
$client->setClientSecret('YOUR_CLIENT_SECRET');
$client->setRedirectUri('https://yourdomain.com/google-callback.php');

$client->authenticate($_GET['code']);
$token = $client->getAccessToken();
$client->setAccessToken($token);

$google_oauth = new Google_Service_Oauth2($client);
$google_account_info = $google_oauth->userinfo->get();

$email = $google_account_info->email;
$name = $google_account_info->name;

// Check DB and create session or insert user
// (Reuse your existing logic from the registration file)
