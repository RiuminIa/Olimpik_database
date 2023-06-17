<?php 

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();

    require_once('../config.php');
    if (!isset($_GET['id'])) {
        exit("id does not exist");
    }       
    try{
        $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } 
    catch(PDOException $e){
        echo 'eaaarroqr';
    }
    $query = "SELECT * FROM `person` where id =".$_GET['id'];
    $stmt = $db->query($query); 
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result){
        exit("id does not exist");
    }
    $query = "Select placement.id as id, placement.person_id, placement.game_id, placement.placing, placement.discipline, oh.id as OlimpId, oh.type, oh.year, oh.city, oh.country from placement join oh on placement.game_id = oh.id where placement.person_id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $placements = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <a class="nav-link active" href="../index.php">Home</a>
      </li>
     <li class="nav-item">
        <a class="nav-link" href="./top.php">Top 10</a>
      </li>
      <li class="nav-item" <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){echo "style='display:none;'";}
      else{echo "style='display:block;'";}
      ?>>
        <a class="nav-link" href="./admin.php">Admin</a>
      </li>
      <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            echo "<li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' role='button' data-bs-toggle='dropdown'>Log in</a>
            <ul class='dropdown-menu'>
              <li><a class='dropdown-item' href='../login_pages/login.php'>Log in</a></li>
              <li><a class='dropdown-item' href='../login_pages/register.php'>Sign up</a></li>
            </ul>
          </li>";}
            else{
            echo "<li class='nav-item'><a class='nav-link' id='logOut'> Log out </a></li>";}
        ?>
      <li class="nav-item">
        <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            echo "<a class='nav-link disabled'>Guest</a>";}
            else{
            echo "<a class='nav-link' href='./session_history.php'>".$_SESSION["email"]."</a>";}
        ?>
      </li>
    </nav>

                <h2 class="text-center">Sportsman information</h2>
    <table id="olimpicChampions" class="table" style="width:100%">
            <thead>
                <tr>
                <td>name</td><td>surname</td><td>birth day</td><td>birth place</td><td>birth country</td><td>death day</td><td>death place</td><td>death country</td>
                </tr>
            </thead>
            <tbody>
        <?php
            $i=0;
                echo "<tr class='olimpicRow'><td>".$result["name"]."</td><td>".$result["surname"]."</td><td>".$result["birth_day"]."</td><td>".$result["birth_place"]."</td><td>".$result["birth_country"]."</td><td>".$result["death_day"]."</td><td>".$result["death_place"]."</td><td>".$result["death_country"]."</td></tr>";
        ?>
        </tbody>
        </table>
        <h2 class="text-center">Participation in the Olympic Games.</h2>
        <div class="championTable">
        <table id="olimpicChampions2" class="table" style="width:100%">
            <thead>
                <tr>
                <td class='superId'>id</td><td>placing</td><td>discipline</td><td>year</td><td>type</td><td>city</td><td>country</td></td>
                </tr>
            </thead>
            <tbody>
        <?php
            $i=0;
            foreach($placements as $a){
                echo "<tr class='olimpicRow'><td class='superId'>".$a["id"]."</td><td>".$a["placing"]."</td><td>".$a["discipline"]."</td><td>".$a["year"]."</td><td>".$a["type"]."</td><td>".$a["city"]."</td><td>".$a["country"]."</td></tr>";
            $i++;
            }
        ?>
        </tbody>
        </table>
        </div>
        <script>
            $('#logOut').on('click',function(){
    $con=confirm("Are you sure you want to get out?"); 
    if($con){
      window.location.href ='../login_pages/logout.php';
    }
});

        </script>



    </body>
</html>