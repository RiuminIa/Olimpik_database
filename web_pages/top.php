<?php 

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
    session_start();
    require_once('../config.php');
    try{
        $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo 'Connected to database';
    }
    
    catch(PDOException $e){
        echo 'eaaarror';
    }
    $query = "SELECT  person.name as 'name',person.id, person.surname as surname, count(placement.placing) as awards FROM (SELECT DISTINCT(p.person_id) as y, Count(p.placing) FROM placement as p where p.placing = 1 GROUP BY p.person_id  Order by Count(p.placing) DESC  LIMIT 10) as p INNER JOIN placement ON placement.person_id = p.y INNER JOIN person ON person.id = placement.person_id where placement.placing=1 GROUP BY person.id";
    $stmt = $db->query($query); 
    //$row = $stmt->fetch(PDO::FETCH_ASSOC);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // var_dump($results);

?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>php</title>
       
        <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js" ></script>
        <script src="https://code.jquery.com/jquery-3.5.1.js" ></script>
        <!-- <script src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap.min.js"></script> -->
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
        <a class="nav-link active" href="#">Top 10</a>
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
        <h1>Top 10 champions</h1>
        <div class="championTable">
        <table id="olimpicChampions" class="table table-bordered" style="width:100%">
            <thead>
                <tr>
                    <td class='superId'>id</td><td>name</td><td>surname</td><td>awards</td>
                </tr>
            </thead>
            <tbody>
        <?php
            foreach($results as $a){
                echo "<tr class='olimpicRow'><td class='superId'>".$a["id"]."</td><td>".$a["name"]."</td><td>".$a["surname"]."</td><td>".$a["awards"]."</td></tr>";
            }
        ?>
        </tbody>
        </table>
        </div>
        <script>
            $(document).ready(function () {
                $('#logOut').on('click',function(){
    $con=confirm("Are you sure you want to get out?"); 
    if($con){
      window.location.href ='../login_pages/logout.php';
    }
});
            var tabler=$('#olimpicChampions').dataTable({
            // columnDefs: [
            // { orderable: false,targets:[0,3,5], className: 'reorder'},
            // { orderable: true, targets: [1,2,4] }],
            // { orderable: true, targets: 2 },
            // { orderable: true, targets: 4 },
            // { orderable: false, targets: '_all' }],
            paging: false,
        ordering: false,
        info: false,
        filter:false
    })

            $('#olimpicChampions tbody').on('click', 'tr', function () {
            window.location.href = './person_info.php?id='+this.firstChild.textContent;
        });
        });

        </script>
    </body>
</html>