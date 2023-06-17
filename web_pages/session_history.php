<?php 

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    require_once('../config.php');
    
    session_start();

    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        // Neprihlaseny pouzivatel, zobraz odkaz na Login alebo Register stranku.
        echo '<script>alert("Welcome to Geeks for Geeks")</script>';
        header("location: ../login_pages/login.php");
    }
    try{
        $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo 'Connected to database';
    }
    catch(PDOException $e){
        echo 'eaaarror';
    } 
    $query = "SELECT * FROM session where session.email='".$_SESSION['email']."'";
    $stmt = $db->query($query); 
    $sessionHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT activity.* FROM session  INNER JOIN activity on activity.session_id=session.id where session.email='".$_SESSION['email']."'";
    $stmt = $db->query($query); 
    $aktivityHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // var_dump($aktivityHistory);
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
            <a class="nav-link" href="./top.php">Top 10</a>
          </li>
          <li class="nav-item admin">
            <a class="nav-link" href="./admin.php">Admin</a>
          </li>
          <li>
            <a class="nav-link" id='logOut'>Log out</a>
            
          </li>
          <li class="nav-item">
            <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
                echo "<a class='nav-link disabled'>Guest</a>";}
                else{
                    echo "<a class='nav-link active' href='./session_history.php'>".$_SESSION["email"]."</a>";}
            ?>
          </li>
        </nav>
        <h2 class="text-center">Session history</h2>
        <div class="mx-auto" style="width: max-content"> 
        <table id="olimpicChampions" class="table" style="width:100%">
            <thead>
                <tr style="font-weight: bold">
                <td>id</td><td>time</td></td>
                </tr>
            </thead>
            <tbody>
        <?php
            $i=0;
            foreach($sessionHistory as $a){
                echo "<tr class='olimpicRow'><td>".$a["id"]."</td><td>".$a["time"]."</td></tr>";
            $i++;
            }
        ?>
        </tbody>
        </table>
        </div>
        <h2 class="text-center">Session aktivity</h2>
        <table id="olimpicChampions2" class="table container" style="width:100%">
            <thead>
                <tr style="font-weight: bold">
                <td>activity</td><td>table name</td><td>row id</td>
                </tr>
            </thead>
            <tbody>
        <?php
            $i=0;
            foreach($aktivityHistory as $ak){
                echo "<tr class='olimpicRow'><td>".$ak["activiti"]."</td><td>".$ak["which_table"]."</td><td>".$ak["table_id"]."</td></tr>";
            }
        ?>
        </tbody>
        </table>
        <script>
              $(document).ready(function () {
            var tabler=$('#olimpicChampions').dataTable({
            // columnDefs: [
            // { orderable: false,targets:[0,3,5], className: 'reorder'},
            // { orderable: true, targets: [1,2,4] }],
            // { orderable: true, targets: 2 },
            // { orderable: true, targets: 4 },
            // { orderable: false, targets: '_all' }],
            lengthMenu: [
            [5, 10, 20, -1],
             [5, 10, 20, 'All']
        ]
        // select: true,
            
    })
    $('#logOut').on('click',function(){
    $con=confirm("Are you sure you want to get out?"); 
    if($con){
      window.location.href ='../login_pages/logout.php';
    }
}); var tabler=$('#olimpicChampions2').dataTable({
            // columnDefs: [
            // { orderable: false,targets:[0,3,5], className: 'reorder'},
            // { orderable: true, targets: [1,2,4] }],
            // { orderable: true, targets: 2 },
            // { orderable: true, targets: 4 },
            // { orderable: false, targets: '_all' }],
            lengthMenu: [
            [5, 10, 20, -1],
             [5, 10, 20, 'All']
        ]
        // select: true,
            
    })
              })

        </script>
        </body>
    </html>         