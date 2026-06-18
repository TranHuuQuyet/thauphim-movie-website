<?php
    $username_db = "Admin";
    $password_db = password_hash("abc123",PASSWORD_DEFAULT);
    // echo $password_db;

    $error=[];
    if(isset($_POST['username']) && trim($_POST['username']) !="")
        {
        if($_POST['username'] != $username_db)
            array_push($error,"Username khong dung");

        }
    else{
        array_push($error,"Username khong duoc trong");
        
    }
    if(isset($_POST['password']) && trim($_POST['password']) !=""){
        if(!password_verify($_POST['password'],$password_db)){

            array_push($error,"mat khau khong dung");
        }

    }
    else{
        array_push($error,"mat khau khong duoc trong");
        
    }

    if(count($error) >0){
        echo '<p style ="color: red">' .implode("<br>",$error).'</p>';
    }
    else {
        echo 'dang nhap thanh cong';
        header("location: index.php");
        
    }

?>