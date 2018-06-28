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
    // manage via levels
    /*
    level 0     = not registered
    level 1     = home menu
    level 2     = request name
    level 3     = request age
    */
    switch ($level) {
        case 0:
        //unregistered user     
        if($userResponse==""){
            $response = UnregisteredWelcomeScreen();
        }
        elseif($userResponse=="1"){
            //always update level to avoid serving the same menu
            $sqlLev1 = "INSERT INTO `session_levels`(`sessionId`, `phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."', 1)";
            $conn->query($sqlLev1);
            $response = RegisterUser();
        }
        elseif($userResponse=="2"){
            $response = "END Good-Bye";
        }
        else{
            header('Content-type: text/plain');
            $response = "CON Oops, Invalid Entry... \n";
        }      
        break;
        
        case 1:
        $sqlLev2 = "UPDATE `session_levels` SET `level` = '2' WHERE `phonenumber` = '$phoneNumber'";
        $conn->query($sqlLev2);
        if ($userResponse==""){
            $name     =getByValue('users','name',$levelFetch);
            if (strlen($name)==0){
                $sqlLev0 = "UPDATE `session_levels` SET `level` = '0' WHERE `phonenumber` = '$phoneNumber'";
                $conn->query($sqlLev0);
                $response = "END Something went wrong please try again";
            }
            else{
                $response = RegisterdUserWelcomeScreen($name);
            }
        }
        else{
            $sqlReg2     = "INSERT INTO `users`(`name`,`phonenumber`) VALUES ('".$userResponse."','".$phoneNumber."')";
            $conn      ->query($sqlReg2);
            $name      =getByValue('users','name',$levelFetch);
            $response  =RegisterdUserWelcomeScreen($name);
        }
        break;

        case 2:
        $sqlLev3 = "UPDATE `session_levels` SET `level` = '3' WHERE `phonenumber` = '$phoneNumber'";
        $conn->query($sqlLev3);
        if ($userResponse==""){
            $name     =getByValue('users','name',$levelFetch);
            if (strlen($name)==0){
                $sqlLev0 = "UPDATE `session_levels` SET `level` = '0' WHERE `phonenumber` = '$phoneNumber'";
                $conn->query($sqlLev0);
                $response = "END Something went wrong please try again";
            }
            else{
                $response = RegisterdUserWelcomeScreen($name);
            }
        }
        elseif($userResponse=="1"){
            $response = accountInformation();
        }
        elseif($userResponse=="2"){
            $response=phoneNumber($phoneNumber);
        }

        elseif($userResponse=="3"){
            $response=editName();
        }
        else{
            $response ="CON Ooops!!! Something went wrong";
        }
        break;

        case 3:
        $sqlLev4 = "UPDATE `session_levels` SET `level` = '4' WHERE `phonenumber` = '$phoneNumber'";
        $conn->query($sqlLev4);
        if ($userResponse==""){
            $name      =getByValue('users','name',$levelFetch);
            $response  =RegisterdUserWelcomeScreen($name);
        }
        elseif($userResponse=="1"){
            $accountNumber  = "AC521254";
            $response = "END Your account number is $accountNumber";
        }
        elseif($userResponse=="2"){
            $balance  = "KES 10,000";
            $response = "END Your balance is $balance";
        }
        elseif(strlen($userResponse)>2){
            $sqlLev3 = "UPDATE `session_levels` SET `level` = '3' WHERE `phonenumber` = '$phoneNumber'";
            $conn->query($sqlLev3);
            $sqlname = "UPDATE `users` SET `name` = '$userResponse' WHERE `phonenumber` = '$phoneNumber'";
            $conn->query($sqlname);
            $name      =getByValue('users','name',$levelFetch);
            $response  =RegisterdUserWelcomeScreen($name);
        }
        else{
            $response ="CON Ooops!!! Something went wrong";
        }
        break;

        case 4:
        $response = "CON Welcome back press\n";
        $response .= "1. To proceed \n";
        $response .= "2. To exit \n";
        if($userResponse=="1"){
            $sqlLev4 = "UPDATE `session_levels` SET `level` = '2' WHERE `phonenumber` = '$phoneNumber'";
            $conn->query($sqlLev4);
            $name      =getByValue('users','name',$levelFetch);
            $response  =RegisterdUserWelcomeScreen($name);
        }
        elseif($userResponse=="2"){
            $name      =getByValue('users','name',$levelFetch);
            $response="END Good-bye $name\n";
        }
        break;

        default:
        $response = "END Oops, something isn't right... \n";
    }
    header('Content-type: text/plain');
    echo $response;
}
?>