<?php
//1. Ensure ths code runs only after a POST from AT
if(!empty($_POST) && !empty($_POST['phoneNumber'])){
    require_once('functions.php');
    require_once('dbConnector.php');
    
    //2. receive the POST from AT
    $sessionId     =$_POST['sessionId'];
    $serviceCode   =$_POST['serviceCode'];
    $phoneNumber   =$_POST['phoneNumber'];
    $text          =$_POST['text'];
    
    //3. Explode the text this will store the text as an array.
    $textArray=explode('*', $text);
    // trim will get the last input
    $userResponse=trim(end($textArray));

    $levelFetch = array('phonenumber'=>$phoneNumber);

    //fetch level from DB
    if(returnExists('session_levels', $levelFetch) > 0){
        $level      = getByValue('session_levels','level',$levelFetch);
    }
    else{
        $level      = 0;
    }
    switch ($level) {
        case 0:       
        if($userResponse==""){
            $response = UnregisteredWelcomeScreen();
        }
        elseif($userResponse=="1"){
            $sqlLev1 = "INSERT INTO `session_levels`(`sessionId`, `phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."', 1)";
            $conn->query($sqlLev1);
            $response = RegisterUser();
        }
        elseif($userResponse=="2"){
            $sqlLev2 = "INSERT INTO `session_levels`(`sessionId`, `phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."', 2)";
            $conn->query($sqlLev2);
        }
        else{
            header('Content-type: text/plain');
            $response = "CON Oops, Invalid Entry... \n";
        }      
        break;
        
        case 1:
        $sqlLev3 = "UPDATE `session_levels` SET `level` = '3' WHERE `phonenumber` = '$phoneNumber'";
        $conn->query($sqlLev3);
        if (returnExists('users', $levelFetch) == 0 && getByValue('users','name',$levelFetch)==""){
            if ($userResponse!=""){
                $sqlReg2     = "INSERT INTO `users`(`name`,`phonenumber`) VALUES ('".$userResponse."','".$phoneNumber."')";
                $conn      ->query($sqlReg2);
                $name      =getByValue('users','name',$levelFetch);
                $response  =RegisterdUserWelcomeScreen($name);
            }
            else{
                $response = RegisterUser();
            }           
        }
        else{
            $name      =getByValue('users','name',$levelFetch);
            $response  =RegisterdUserWelcomeScreen($name);
        }
        break;

        case 2:
        $name      =getByValue('users','name',$levelFetch);
        $response = ExitRegisteredUser($name);
        break;
        
        case 3:
        if (returnExists('users', $levelFetch) == 0){
            if ($userResponse!=0){
                $sql1     = "INSERT INTO `users`(`name`,`phonenumber`) VALUES ('".$userResponse."','".$phoneNumber."')";
                $conn      ->query($sql1);
                $response  =RegisterdUserWelcomeScreen($name);
            }
            else{
                $response  =RegisterUser();
            }
        }
        elseif (returnExists('users', $levelFetch) > 0 && getByValue('users','name',$levelFetch)=="") {
            $sqlreg = "UPDATE `users` SET `name` = '$userResponse' WHERE `phonenumber` = '$phoneNumber'";
            $conn->query($sqlreg);
            $name      =getByValue('users','name',$levelFetch);
            $response  =RegisterdUserWelcomeScreen($name);
        }
        else{
            $name      =getByValue('users','name',$levelFetch);
            $response  =RegisterdUserWelcomeScreen($name);
        }
        break;        
        
        default:
        $response = "END Oops, something isn't right... \n";
        break;


    }

    header('Content-type: text/plain');
    echo $response;	

}
?>