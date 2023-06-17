<?php 

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    require_once('../config.php');
    
    session_start();

    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        // Neprihlaseny pouzivatel, zobraz odkaz na Login alebo Register stranku.
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
    if(!empty($_POST) && !empty($_POST['name'])){
        // var_dump($_POST);
        if($_POST['death_day']==""){
            $_POST['death_day']=null;
        }
        if($_POST['death_place']==""){
            $_POST['death_place']=null;
        }
        if($_POST['death_country']==""){
            $_POST['death_country']=null;
        }
        $sql = "SELECT person.id as 'id' from person where person.name = '".$_POST['name']."' and person.surname = '".$_POST['surname']."' and person.birth_day = '".$_POST['birth_day']."'";
        $stmt = $db->query($sql); 
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        //var_dump($result);
        if (!$result){
        $sql = "INSERT INTO person (name, surname, birth_day, birth_place, birth_country, death_day, death_place, death_country) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $db->prepare($sql);
        $success = $stmt->execute([$_POST['name'], $_POST['surname'], $_POST['birth_day'], $_POST['birth_place'], $_POST['birth_country'], $_POST['death_day'], $_POST['death_place'], $_POST['death_country']]);
        
        $sql = "SELECT MAX(person.id) as 'id' from person";
        $stmt = $db->query($sql); 
        $maxId = $stmt->fetch(PDO::FETCH_ASSOC);    

        $sql = "INSERT INTO activity (activiti, which_table, table_id, session_id) VALUES (?,?,?,?)";
        $stmt = $db->prepare($sql);
        $success = $stmt->execute(["add", 'person', $maxId['id'], $_SESSION['session_id']]);
        echo '<script>alert("Success!\n The athlete has been added.")</script>'; 
        }
        else{
            echo '<script>alert("Error!\n Such a sportsman already exists.")</script>'; 
        }
        }

        if(isset($_POST['del_placement_id'])){
            
            $sql = "SELECT id FROM placement  WHERE placement.person_id=".$_POST['del_placement_id'];
            $stmt = $db->query($sql); 
            $placeIds = $stmt->fetchAll(PDO::FETCH_ASSOC);   
            
            $sql = "DELETE FROM placement  WHERE placement.person_id=?";
            $stmt = $db->prepare($sql);
            $stmt->execute([intval($_POST['del_placement_id'])]);

            // $sql = "SELECT MAX(person.id) as 'id' from person";
            // $stmt = $db->query($sql); 
            // $maxId = $stmt->fetch(PDO::FETCH_ASSOC);    
            foreach($placeIds as $ida){
                $sql = "INSERT INTO activity (activiti, which_table, table_id, session_id) VALUES (?,?,?,?)";
                $stmt = $db->prepare($sql);
                $success = $stmt->execute(["del", 'placement', $ida['id'], $_SESSION['session_id']]);
            }

            $sql = "DELETE FROM person WHERE id=?";
            $stmt = $db->prepare($sql);
            $stmt->execute([intval($_POST['del_placement_id'])]);

            $sql = "INSERT INTO activity (activiti, which_table, table_id, session_id) VALUES (?,?,?,?)";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute(["del", 'person', $_POST['del_placement_id'], $_SESSION['session_id']]);

            echo '<script>alert("Success!\n The deletion was successful.")</script>'; 
        }

         $query = "SELECT * FROM `person`";
          $stmt = $db->query($query); 
          $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <a class="nav-link active" href="./admin.php">Admin</a>
      </li>
      <li>
        <a class="nav-link" id='logOut'>Log out</a>
      </li>
      <li class="nav-item">
        <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            echo "<a class='nav-link disabled'>Guest</a>";}
            else{
            echo "<a class='nav-link' href='./session_history.php'>".$_SESSION["email"]."</a>";}
        ?>
      </li>
    </nav>
    <div class="container-md">
        <h2 class="text-center">Add athletes</h2>
            <form onsubmit="return confirm('Do you really want to add an athlete?');" action="#" method="post">
                <div class="mb-3">
                    <label for="InputName" class="form-label">Name:*</label>
                    <input type="text" name="name" class="form-control" id="InputName" required>
                </div>
                <div class="mb-3">
                    <label for="InputSurname" class="form-label">Surname:*</label>
                    <input type="text" name="surname" class="form-control" id="InputSurname" required>
                </div>
                <div class="mb-3">
                    <label for="InputDate" class="form-label">birth day:*</label>
                    <input type="date" name="birth_day" class="form-control" id="InputDate" required>
                </div>
                <div class="mb-3">
                    <label for="InputbrPlace" class="form-label">birth place:*</label>
                    <input type="text" name="birth_place" class="form-control" id="InputBrPlace" required>
                </div>
                <div class="mb-3">
                    <label for="InputBrCountry" class="form-label">birth country:*</label>
                    <input type="text" name="birth_country" class="form-control" id="InputBrCountry" required>
                </div>
                <div class="mb-3">
                    <label for="InputDeathDay" class="form-label">death day:</label>
                    <input type="date" name="death_day" class="form-control" id="InputDeathDay">
                </div>
                <div class="mb-3">
                    <label for="InputDeathPlace" class="form-label">death place:</label>
                    <input type="text" name="death_place" class="form-control" id="InputDeathPlace">
                </div>
                <div class="mb-3">
                    <label for="InputDeathCountry" class="form-label">death country:</label>
                    <input type="text" name="death_country" class="form-control" id="InputDeathCountry">
                </div>
                <button type="submit" class="btn btn-primary">add</button>

            </form>
    </div>
            <!-- <form action="#" method="post">
                <select name="person_id">
                    //
                    //foreach($persons as $person){
                    //    echo '<option value="' . $person['id'] . '">' . $person['name'] . ' ' . $person['surname'] . '</option>';
                    //}       
                    /
                </select>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form> -->
        <h2 class="text-center">List of athletes</h2>
        <div class="championTable">
        <table id="olimpicChampions" class="table" style="width:100%">
            <thead>
                <tr>
                <td class='superId'>id</td><td>name</td><td>surname</td><td>birth day</td><td>birth place</td><td>birth country</td><td>death day</td><td>death place</td><td>death country</td>
                </tr>
            </thead>
            <tbody>
        <?php
            $i=0;
            foreach($results as $a){
                echo "<tr class='olimpicRow'><td class='superId'>".$a["id"]."</td><td>".$a["name"]."</td><td>".$a["surname"]."</td><td>".$a["birth_day"]."</td><td>".$a["birth_place"]."</td><td>".$a["birth_country"]."</td><td>".$a["death_day"]."</td><td>".$a["death_place"]."</td><td>".$a["death_country"]."</td></tr>";
            $i++;
            }
        ?>
        </tbody>
        </table>
        <form onsubmit="return confirm('Do you really want to delete the record?');" action="#" method="post"><input id="delForm" type="hidden" name="del_placement_id">
        <button type="button" id="editBtn" disabled="true" class="btn btn-success">Edit</button>
        <button type="submit" disabled="true" class="btn btn-danger">delete</button></form>
        </div>
        
        <script>
            var elemEdit;
            var after;
            $(document).ready(function () {
            var tabler=$('#olimpicChampions').dataTable({
            lengthMenu: [
            [5, 10, 20, -1],
             [5, 10, 20, 'All']
        ],
        // select: true,
            // aoColumnDefs: [
            // { aDataSort: [ 5, 3 ], aTargets: [ 5 ] },
            // { aDataSort: [1], aTargets: [ 1 ] },
            // { aDataSort: [2], aTargets: [ 2 ] },
            // { aDataSort: [3], aTargets: [ 3 ] },
            // {orderable:false,aTargets:[0,4,6]},
            // ],
    })
            $('#logOut').on('click',function(){
    $con=confirm("Are you sure you want to get out?"); 
    if($con){
      window.location.href ='../login_pages/logout.php';
    }
});


            $('#olimpicChampions tbody').on('click', 'tr', function () { 
                // window.location.href = './edit.php?id='+this.firstChild.textContent;
                elemEdit=this.firstChild.textContent;
                $("#delForm").val(elemEdit);
                console.log(elemEdit);
                try{
                after.setAttribute("style","");
                // after.setAttribute("style","color:black");
                }
                catch(e){
                }
                // var elem = document.querySelectorAll('.btn');
                $('.btn').prop('disabled', false);
                this.setAttribute("style","background-color:black; color:white");
                // this.setAttribute("style","color:white");
                after=this;
          })    
        });
          $("#editBtn").on('click',function(){
            window.location.href = './edit.php?id='+elemEdit;
          })
        </script>
    </body>
</html>
