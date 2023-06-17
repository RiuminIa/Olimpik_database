<?php

// Konfiguracia PDO
require_once ('../config.php');
// Kniznica pre 2FA
require_once '../PHPGangsta/GoogleAuthenticator.php';

if (isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] == true) {
    // Neprihlaseny pouzivatel, zobraz odkaz na Login alebo Register stranku.
header("location: ../web_pages/admin.php");
} 

try{
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo 'Connected to database';
}
catch(PDOException $e){
    echo 'eaaarror';
}
// ------- Pomocne funkcie -------
function checkEmpty($field) {
    // Funkcia pre kontrolu, ci je premenna po orezani bielych znakov prazdna.
    // Metoda trim() oreze a odstrani medzery, tabulatory a ine "whitespaces".
    if (empty(trim($field))) {
        return true;
    }
    return false;
}

function checkLength($field, $min, $max) {
    // Funkcia, ktora skontroluje, ci je dlzka retazca v ramci "min" a "max".
    // Pouzitie napr. pre "login" alebo "password" aby mali pozadovany pocet znakov.
    $string = trim($field);     // Odstranenie whitespaces.
    $length = strlen($string);      // Zistenie dlzky retazca.
    if ($length < $min || $length > $max) {
        return false;
    }
    return true;
}

function checkUsername($username) {
    // Funkcia pre kontrolu, ci username obsahuje iba velke, male pismena, cisla a podtrznik.
    if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($username))) {
        return false;
    }
    return true;
}

function checkGmail($email) {
    // Funkcia pre kontrolu, ci zadany email je gmail.
    if (!preg_match('/^[\w.+\-]+@gmail\.com$/', trim($email))) {
        return false;
    }
    return true;
}

function userExist($db,$login, $email) {
    // Funkcia pre kontrolu, ci pouzivatel s "login" alebo "email" existuje.
    $exist = false;

    $param_login = trim($login);
    $param_email = trim($email);
    $sql = "SELECT id FROM users WHERE login = :login OR email = :email";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":login", $param_login, PDO::PARAM_STR);
    $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);

    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $exist = true;
    }

    unset($stmt);

    return $exist;
}

// ------- ------- ------- -------



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errmsg = "";
    // Validacia username
    if (checkEmpty($_POST['login']) === true) {
        $errmsg .= "Enter login.";
    } elseif (checkLength($_POST['login'], 6,32) === false) {
        $errmsg .= "The login must have a minimum of 6 and a maximum of 32 characters.";
    } elseif (checkUsername($_POST['login']) === false) {
        $errmsg .= "Login can only contain uppercase, lowercase letters, numbers and underscore.";
    }

    // Kontrola pouzivatela
    if (userExist ($db,$_POST['login'], $_POST['email']) === true) {
        $errmsg .= "A user with this email/login already exists.";
    }

    // Validacia mailu
    if (checkGmail($_POST['email'])) {
     //   $errmsg .= "Prihlaste sa pomocou Google prihlasenia";
        // Ak pouziva google mail, presmerujem ho na prihlasenie cez Google.
        // header("Location: google_login.php");
    }

    // TODO: Validacia hesla
    // TODO: Validacia mena, priezviska

    if (empty($errmsg)) {

        $sql = "INSERT INTO users (fullname, login,  password, 2fa_code, email) VALUES (:fullname, :login, :password, :2fa_code, :email)";

        $fullname = $_POST['firstname'] . ' ' . $_POST['lastname'];
        $email = $_POST['email'];
        $login = $_POST['login'];
        $hashed_password = password_hash($_POST['password'], PASSWORD_ARGON2ID);

        // 2FA pomocou PHPGangsta kniznice: https://github.com/PHPGangsta/GoogleAuthenticator
        
        $g2fa = new PHPGangsta_GoogleAuthenticator();
        $user_secret = $g2fa->createSecret();
        
        // $qrCodeUrl = $ga->getQRCodeGoogleUrl($websiteTitle, $secret);
        // echo 'Google Charts URL QR-Code:<br /><img src="'.$qrCodeUrl.'" />';
        $codeURL = $g2fa->getQRCodeGoogleUrl('Olympic Games', $user_secret);
        // Bind parametrov do SQL
        $stmt = $db->prepare($sql);

        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);
        $stmt->bindParam(":login", $login, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(":2fa_code", $user_secret, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // qrcode je premenna, ktora sa vykresli vo formulari v HTML.
            $qrcode = $codeURL;
        } else {
            echo "Something went wrong";
        }

        unset($stmt);
    }

    unset($pdo);
}

?>

<!doctype html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>register</title>
       
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
            <a class='nav-link dropdown-toggle active' href='#' role='button' data-bs-toggle='dropdown'>Log in</a>
            <ul class='dropdown-menu'>
              <li><a class='dropdown-item' href='./login.php'>Log in</a></li>
              <li><a class='dropdown-item' href='./register.php'>Sign up</a></li>
            </ul>
          </li>";}
            else{
            echo "<li class='nav-item'><a class='nav-link' id='logOut''> Log out </a></li>";}
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
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <h2 class="fw-bold mb-4 text-uppercase ">Sign up</h2>
                    <div class="mb-3">
                        <label class="container" for="firstname">
                            Name:*
                            <input type="text" class="form-control" name="firstname" value="" id="firstname" placeholder="napr. Ivan" required>
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="container" for="lastname">
                            Surname:*
                            <input type="text" class="form-control" name="lastname" value="" id="lastname" placeholder="napr. Petrzlen" required>
                        </label>
                    </div> 
                    <div class="mb-3">
                        <label class="container" for="email">
                            E-mail:
                            <input type="email" class="form-control" name="email" value="" id="email" placeholder="napr. jpetrzlen@example.com" required>
                        </label>
                    </div>
                        <label class="container" for="login">
                            Login:
                            <input type="text" class="form-control" name="login" value="" id="login" placeholder="napr. jperasin" required">
                        </label>
                        <div class="mb-3">
                        <label class="container" for="password">
                            Heslo:
                            <input type="password" class="form-control" name="password" value="" id="password" required>
                        </label>
                        </div>
                        <button class="btn btn-primary loginoutbtm" type="submit">Sign up</button>
                    </form>
                 </div>
                 <?php
                    if (!empty($errmsg)) {
                        // Tu vypis chybne vyplnene polia formulara.
                        echo '<p style="color:red" class="text-center">Error! '.$errmsg.'</p>';
                    }
                    if (isset($qrcode)) {
                        // Pokial bol vygenerovany QR kod po uspesnej registracii, zobraz ho.
                        $message = '<p class="text-center" >Registration was successful!<br>Scan the QR code into the Authenticator app for 2FA: <br><img src="'.$qrcode.'" alt="qr kod for authenticator application"></p>';

                        echo $message;
                        echo '<p class="text-center" > Now you can log in: <a class="text-center" href="./login.php" role="button">Login</a></p>';
                    }
                    ?>
                    <p class="mb-0  text-center"> <a href="./login.php" class="text-primary fw-bold">Back</a></p>
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