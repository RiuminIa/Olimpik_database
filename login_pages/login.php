<?php 
   
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    session_start();
    require_once('../config.php');
    require_once ('../PHPGangsta/GoogleAuthenticator.php');
    
    require_once '../google/vendor/autoload.php';

    if (isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] == true) {
        // Neprihlaseny pouzivatel, zobraz odkaz na Login alebo Register stranku.
    header("location: ../web_pages/admin.php");
}   
$client = new Google_Client();


    $client->setAuthConfig('../google/googled.json');

// // Nastavenie URI, na ktoru Google server presmeruje poziadavku po uspesnej autentifikacii.
 $redirect_uri = "https://site203.webte.fei.stuba.sk/zadanie1/google/redirect.php";
 $client->setRedirectUri($redirect_uri);
// // Definovanie Scopes - rozsah dat, ktore pozadujeme od pouzivatela z jeho Google uctu.
$client->addScope("email");
$client->addScope("profile");

// // Vytvorenie URL pre autentifikaciu na Google server - odkaz na Google prihlasenie.
$auth_url = $client->createAuthUrl();


    try{
        $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo 'Connected to database';
    }
    catch(PDOException $e){
        echo 'eaaarror';
    }

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // TODO: Skontrolovat ci login a password su zadane (podobne ako v register.php).

    $sql = "SELECT fullname, email, login, password, created_at, 2fa_code FROM users WHERE login = :login";

    $stmt = $db->prepare($sql);

    // TODO: Upravit SQL tak, aby mohol pouzivatel pri logine zadat login aj email.
    $stmt->bindParam(":login", $_POST["login"], PDO::PARAM_STR);
    if ($stmt->execute()) {
        if ($stmt->rowCount() == 1) {
            // Uzivatel existuje, skontroluj heslo.
            $row = $stmt->fetch();
            $hashed_password = $row["password"];
            if (password_verify($_POST['password'], $hashed_password)) {
                // Heslo je spravne.
                $g2fa = new PHPGangsta_GoogleAuthenticator();
                if ($g2fa->verifyCode($row["2fa_code"], $_POST['2fa'])) {
                
                    // Heslo aj kod su spravne, pouzivatel autentifikovany.
                    // Uloz data pouzivatela do session.
                    $_SESSION["loggedin"] = true;
                    $_SESSION["login"] = $row['login'];
                    $_SESSION["fullname"] = $row['fullname'];
                    $_SESSION["email"] = $row['email'];
                    $_SESSION["created_at"] = $row['created_at'];
                    $_SESSION["way"] ="registered";
                    
                    $sql2 = "INSERT INTO session (email, way, login) VALUES (?,?,?)";
                    $stmt2 = $db->prepare($sql2);
                    $success = $stmt2->execute([$_SESSION["email"],"registered",$_SESSION["login"]]);
                    $sql = "SELECT MAX(id) as 'id' from session";
                    $stmt = $db->query($sql); 
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);    
                    $_SESSION['session_id']=$result['id'];
                   //echo '<script>alert("Welcome: '.$_SESSION["fullname"].'")</script>';
                    echo '<script>alert("Welcome: '.$_SESSION["fullname"].'"); 
                    window.location.href ="../web_pages/admin.php";</script>';
                    //header("location: ../web_pages/admin.php");
                }
                else {
                  $errmsg= "Neplatny kod 2FA.";
                }
            } else {
              $errmsg= "Nespravne meno alebo heslo.";
            }
        } else {
          $errmsg= "Nespravne meno alebo heslo.";
        }
    } else {
      $errmsg= "Ups. Nieco sa pokazilo!";
    }

    unset($stmt);
    unset($db);
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>admin</title>
       
        <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js" ></script>
        <script src="https://code.jquery.com/jquery-3.5.1.js" ></script>
        <link type="text/css" href="https://cdn.datatables.net/1.13.3/css/jquery.dataTables.min.css" rel="stylesheet">
        <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap.min.js"></script>
        <link type="text/css" href="https://cdn.datatables.net/1.13.3/css/dataTables.bootstrap.min.css" rel="stylesheet"> 
        <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" type="text/css" href="../style.css">
        <script src="../script/login.js"></script>

    </head>
<body>
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
    <div class="container-fluid">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link " href="../index.php">Home</a>
      </li>
     <li class="nav-item">
        <a class="nav-link" href="../web_pages/top.php">Top 10</a>
      </li>
      <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            echo "<li class='nav-item dropdown'>
            <a class='nav-link active dropdown-toggle' href='#' role='button' data-bs-toggle='dropdown'>Log in</a>
            <ul class='dropdown-menu'>
              <li><a class='dropdown-item' href='./login.php'>Log in</a></li>
              <li><a class='dropdown-item' href='./register.php'>Sign up</a></li>
            </ul>
          </li>";}
            else{
            echo "<li class='nav-item'><a class='nav-link ' id='logOut'> Log out </a></li>";}
        ?>
      <li class="nav-item">
        <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            echo "<a class='nav-link disabled'>Guest</a>";}
            else{
            echo "<a class='nav-link' href='../web_pages/session_history.php'>".$_SESSION["email"]."</a>";}
        ?>
      </li>
    </nav>

        <div class="container m-5">
          <div class="row d-flex justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
              <div class="card bg-white">
                <div class="card-body pb-4 px-5">
                  <form class="mb-3 md-4" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <h2 class="fw-bold mb-4 text-uppercase ">Log in</h2>
                    <i class="fab fa-google"></i>
                    <a class="btn btn-primary btn-lg btn-block loginBtn"  href=<?php echo"'". filter_var($auth_url, FILTER_SANITIZE_URL)."'" ?> role="button"><img id="googleImg" src="../images/google.png">
                        <i class="fab fa-twitter me-2"></i>Log in with Google</a>
                    <p class="text-center mt-3 mb-3">or</p>
                    <div class="mb-3">
                      <label for="login" class="form-label ">Login:*</label>
                      <input type="text" name="login" class="form-control" id="login" placeholder="exm:alfaPro228" required>
                    </div>
                    <div class="mb-3">
                      <label for="password" class="form-label ">Password:*</label>
                      <input type="password" name="password" class="form-control" id="password" placeholder="*******" required>
                    </div>
                    <div class="mb-3">
                        <label for="2fa" class="form-label ">2FA kod:*</label>
                        <input type="text" name="2fa" class="form-control" id="2fa" placeholder="12345" required>
                    </div>
                    <button type="submit" class="btn btn-primary loginoutbtm">Log in</button>
                  </form>
                  <div>
                    <p class="mb-0  text-center">Don't have an account? <a href="./register.php" class="text-primary fw-bold">Sign
                        Up</a></p>
                  </div>
                  <?php
                      if (!empty($errmsg)) {
                        // Tu vypis chybne vyplnene polia formulara.
                        echo '<p style="color:red" class="text-center">Error! '.$errmsg.'</p>';
                    }
                  ?>             
                </div>
              </div>
            </div>
          </div>
        </div>
        <script>
            $('#logOut').on('click',function(){
    $con=confirm("Are you sure you want to get out?"); 
    if($con){
      window.location.href ='./logout.php';
    }
});

        </script>
</body>
</html>