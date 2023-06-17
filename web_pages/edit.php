<?php 
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
    session_start();
    require_once('../config.php');
    //echo  $_GET['id'];
    //$i= intval($_GET['id']);
   // echo  $i;
   if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Neprihlaseny pouzivatel, zobraz odkaz na Login alebo Register stranku.
        header("location: ../login_pages/login.php");;
    }    
    if (!isset($_GET['id'])) {
        exit("id not exist");
    }
    
    try {
        $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT person.id as 'id' from person where person.id = ".$_GET['id'];
        $stmt = $db->query($sql); 
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        //var_dump($result);

        if (!$result){
            exit("id not exist");
        }

        if (!empty($_POST) && !empty($_POST['name'])) {
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
            $sql = "UPDATE person SET name=?, surname=?, birth_day=?, birth_place=?, birth_country=?, death_day=?, death_place=?, death_country=? where id=".$_GET['id'];
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([$_POST['name'], $_POST['surname'], $_POST['birth_day'], $_POST['birth_place'], $_POST['birth_country'], $_POST['death_day'], $_POST['death_place'], $_POST['death_country']]);
            
            $sql = "INSERT INTO activity (activiti, which_table, table_id, session_id) VALUES (?,?,?,?)";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute(["update", 'person', $_GET['id'], $_SESSION['session_id']]);
            echo '<script>alert("Success!\n The record change was successful.")</script>'; 
        }
    
        $query = "SELECT * FROM person where id=?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_GET['id']]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if(isset($_POST['del_placement_id'])){
            $sql = "DELETE FROM placement WHERE id=?";
            $stmt = $db->prepare($sql);
            $stmt->execute([intval($_POST['del_placement_id'])]);
        
            $sql = "INSERT INTO activity (activiti, which_table, table_id, session_id) VALUES (?,?,?,?)";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute(["del", 'placement', $_POST['del_placement_id'], $_SESSION['session_id']]);
            echo '<script>alert("Success!\n The deletion was successful.")</script>'; 


        }
        $query="Select year from oh order by year asc";
        $stmt = $db->query($query); 
        $allYear = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(!empty($_POST) && !empty($_POST['placing'])){
            // var_dump($_POST);
            $sql = "SELECT placement.id as 'id' from placement join oh on placement.game_id=oh.id join person on placement.person_id=person.id where placement.person_id = ".$_GET['id']." and oh.year = ".$_POST['year']." and placement.discipline= '".$_POST['discipline']."'";
            $stmt = $db->query($sql); 
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            // var_dump($result);
            if (!$result){
                $query="Select id from oh where year=".$_POST['year'];
                $stmt = $db->query($query); 
                $game_id = $stmt->fetch(PDO::FETCH_ASSOC);
                // var_dump($game_id);

                $sql = "INSERT INTO placement (person_id, game_id, placing, discipline) VALUES (?,?,?,?)";
                $stmt = $db->prepare($sql);
                $success = $stmt->execute([$_GET['id'],$game_id['id'], $_POST['placing'], $_POST['discipline']]);                
           
                $sql = "SELECT MAX(id) as 'id' from placement";
                $stmt = $db->query($sql); 
                $maxId = $stmt->fetch(PDO::FETCH_ASSOC);      

                $sql = "INSERT INTO activity (activiti, which_table, table_id, session_id) VALUES (?,?,?,?)";
                $stmt = $db->prepare($sql);
                $success = $stmt->execute(["add", 'placement', $maxId['id'], $_SESSION['session_id']]);
                echo '<script>alert("Success!\n This entry was successfully added.")</script>'; 

            }
            else{
                echo '<script>alert("Error!\n Such a sportsman already exists.")</script>';
           }
        }
        $query = "Select placement.id as id, placement.person_id, placement.game_id, placement.placing, placement.discipline, oh.id as OlimpId, oh.type, oh.year, oh.city, oh.country from placement join oh on placement.game_id = oh.id where placement.person_id=?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_GET['id']]);
        $placements = $stmt->fetchAll(PDO::FETCH_ASSOC);
       // var_dump($placements);
    } catch (PDOException $e) {
        echo $e->getMessage();
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
        <a class="nav-link" href="./top.php">Top 10</a>
      </li>
      <li class="nav-item" <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){echo "style='display:none;'";}
      else{echo "style='display:block;'";}
      ?>>
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
        <h1>Olympic champions</h1>
    <div class="container-md">
        <h2>Add sporsmen</h2>
            <form action="#" method="post" onsubmit="return confirm('Do you really want to change the record?');">
                <div class="mb-3">
                    <label for="InputName" class="form-label">Name:*</label>
                    <input type="text" name="name" class="form-control" id="InputName" value="<?php echo $person['name']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="InputSurname" class="form-label">Surname:*</label>
                    <input type="text" name="surname" class="form-control" id="InputSurname"  value="<?php echo $person['surname']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="InputDate" class="form-label">birth day:*</label>
                    <input type="date" name="birth_day" class="form-control" id="InputDate" value="<?php echo $person['birth_day']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="InputbrPlace" class="form-label">birth place:*</label>
                    <input type="text" name="birth_place" class="form-control" id="InputBrPlace" value="<?php echo $person['birth_place']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="InputBrCountry" class="form-label">birth country:*</label>
                    <input type="text" name="birth_country" class="form-control" id="InputBrCountry" value="<?php echo $person['birth_country']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="InputDeathDay" class="form-label">death day:</label>
                    <input type="date" name="death_day" class="form-control" id="InputDeathDay" value="<?php echo $person['death_day']; ?>">
                </div>
                <div class="mb-3">
                    <label for="InputDeathPlace" class="form-label">death place:</label>
                    <input type="text" name="death_place" class="form-control" id="InputDeathPlace" value="<?php echo $person['death_place']; ?>">
                </div>
                <div class="mb-3">
                    <label for="InputDeathCountry" class="form-label">death country:</label>
                    <input type="text" name="death_country" class="form-control" id="InputDeathCountry" value="<?php echo $person['death_country']; ?>">
                </div>
                <button type="submit" class="btn btn-primary">change</button>

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
        <h2 class="text-center">The success of an athlete</h2>
        <div class="championTable">
        <table id="olimpicChampions" class="table" style="width:100%">
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
        <form action="#" method="post" onsubmit="return confirm('Do you really want to delete the record?');"><input id="delForm" type="hidden" name="del_placement_id">
        <button type="submit" disabled="true" class="btn btn-danger">delete</button>
        <button type="button" id="addBtn" class="btn btn-primary">add new</button>
        </form>


        <form action="#" method="post" id="addPlace" style="display:none" onsubmit="return confirm('Do you really want add the record?');">
                <div class="mb-3">
                    <label for="placing" class="form-label">Placing:*</label>
                    <input type="number" name="placing" class="form-control" id="placing" required>
                </div>
                <div class="mb-3">
                    <label for="discipline" class="form-label">Discipline:*</label>
                    <input type="text" name="discipline" class="form-control" id="discipline" required>
                </div>
                <div class="mb-3">
                <label for="year" class="form-label">year:*</label>    
                <select name="year" id="year" required>
                    <?php
                    foreach($allYear as $a){
                       echo "<option value=". $a["year"].">".$a["year"]."</option>";
                }       
                ?>    
                </select>
                </div>
                <button type="submit" class="btn btn-primary">add</button>
        </form>   
    </div>
</body>

</html>
    
        <!-- <button type="button" disabled="true" class="btn btn-success">Edit</button>
        <button type="button" disabled="true" class="btn btn-danger">Delete</button> -->
        </div>
        <script>
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
                //window.location.href = './edit.php';
                elemEdit=this.firstChild.textContent;
                 $("#delForm").val(elemEdit);
                try{
                after.setAttribute("style","");
                // after.setAttribute("style","color:black");
                }
                catch(e){
                }
                // var elem = document.querySelectorAll('.btn');
                $('.btn-danger').prop('disabled', false);
                this.setAttribute("style","background-color:black; color:white");
                // this.setAttribute("style","color:white");
                after=this;
          })    
        });
        $("#addBtn").on('click',function(){
            if (document.getElementById('addPlace').style.display=='none'){
                $("#addPlace").attr("style","display:block");
            }
            else{
                $("#addPlace").attr("style","display:none");
            }
          })
        </script>
    </body>
</html>