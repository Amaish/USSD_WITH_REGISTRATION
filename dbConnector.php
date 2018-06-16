<?php
//theUssdDb.php
//Connection Credentials
$servername = 'localhost';
$username = 'root';
$password = "";
$database = "ussd";
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $database);
    // Check connection
    if ($conn->connect_error) {
        header('Content-type: text/plain');
        //log error to file/db $e-getMessage()
        die("END An error was encountered. Please try again later");
    } 
    //echo " END Connected successfully (".$conn->host_info.")";
    ?>