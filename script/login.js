$('#logOut').on('click',function(){
    $con=confirm("Are you sure you want to get out?"); 
    if($con){
      window.location.href ='../login_pages/logout.php';
    }
});

// $('#logOut').on('click',function(){
//     $con=confirm("Are you sure you want to get out?"); 
//     if($con){
//       window.location.href ="./login_pages/logout.php";
//     }