
<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

require_once 'vendor/autoload.php';
require_once '../config.php';
try{
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo 'Connected to database';
}
catch(PDOException $e){
    echo 'eaaarror';
}
if (isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] == true) {
    // Neprihlaseny pouzivatel, zobraz odkaz na Login alebo Register stranku.
header("location: ../web_pages/admin.php");
}

$redirect_uri = 'https://site203.webte.fei.stuba.sk/zadanie1/google/redirect.php';

$client = new Google_Client();
$client->setAuthConfig('./googled.json');
$client->setRedirectUri($redirect_uri);
$client->addScope("email");
$client->addScope("profile");
      
$service = new Google_Service_Oauth2($client);
			
if(isset($_GET['code'])){
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  $client->setAccessToken($token);
  $_SESSION['upload_token'] = $token;

  // redirect back to the example
//   header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

// set the access token as part of the client
if (!empty($_SESSION['upload_token'])) {
  $client->setAccessToken($_SESSION['upload_token']);
  if ($client->isAccessTokenExpired()) {
    unset($_SESSION['upload_token']);
  }
} else {
  $authUrl = $client->createAuthUrl();
}

if ($client->getAccessToken()) {
    //Get user profile data from google
    $UserProfile = $service->userinfo->get();
    //var_dump($client->getAccessToken());
    if(!empty($UserProfile)){

        // $g_fullname = $UserProfile->name;
        $g_id = $UserProfile->id;
        $e_mail = $UserProfile->email;
        $g_name = $UserProfile->givenName;
        $g_surname = $UserProfile->familyName;
        $fullname = $UserProfile->givenName. ' ' .$UserProfile->familyName;
        // Na tomto mieste je vhodne vytvorit poziadavku na vlastnu DB, ktora urobi:
        // 1. Ak existuje prihlasenie Google uctom -> ziskaj mi minule prihlasenia tohoto pouzivatela.
        // 2. Ak neexistuje prihlasenie pod tymto Google uctom -> vytvor novy zaznam v tabulke prihlaseni.
    
        // Ulozime potrebne data do session.
        $_SESSION["loggedin"] = true;
        $_SESSION['access_token'] = $token['access_token'];
        $_SESSION['email'] = $e_mail;
        $_SESSION['id'] = $g_id;
        $_SESSION['fullname'] = $fullname;
       // $_SESSION['name'] = $g_name;
       // $_SESSION['surname'] = $g_surname;
        $sql = "SELECT * from users where users.email = '".$e_mail."'";
        $stmt = $db->query($sql); 
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if($result){
            $login = $result['login'];
            $way = 'registered';
        }
        else{
            $sql = "SELECT * from google_users where google_users.email = '".$e_mail."'";
            $stmt = $db->query($sql); 
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $login = $e_mail;
            $way = 'google';
            if(!$result){
                $sql = "INSERT INTO google_users (email, fullname) VALUES (?,?)";
                $stmt = $db->prepare($sql);
                $success = $stmt->execute([$e_mail,$fullname]);
            }
        }
        $sql2 = "INSERT INTO session (email, way, login) VALUES (?,?,?)";
        $stmt2 = $db->prepare($sql2);
        $success = $stmt2->execute([$e_mail,$way,$login]);
        $_SESSION['way'] = $way;

        $sql = "SELECT MAX(id) as 'id' from session";
        $stmt = $db->query($sql); 
        $result = $stmt->fetch(PDO::FETCH_ASSOC);    

        $_SESSION['session_id']=$result['id'];
        echo '<script>alert("Welcome: '.$_SESSION["fullname"].'"); 
        window.location.href ="../web_pages/admin.php";</script>';
       // header('Location: ../web_pages/admin.php');
    }else{
        $output = '<h3 style="color:red">Some problem occurred, please try again.</h3>';
    }   
  } else {
      $authUrl = $client->createAuthUrl();
      $output = '<a href="'.filter_var($authUrl, FILTER_SANITIZE_URL).'"><img src="images/glogin.png" alt=""/></a>';
  }
?>

