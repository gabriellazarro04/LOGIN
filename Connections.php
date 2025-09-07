<?php
$Connections = mysqli_connect ("localhost:3306", "root", "", "log2");
if(mysqli_connect_errno()){
    echo"failed to connect to My SQL:" .mysqli_connect_error();
}
?>