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
    
	//4. Set the default level of the user
    $level=0;

    
    //5. Check if the user is in the db
    $level_arguments = array('phonenumber'=>$phoneNumber);// saving the number as an array so we can check.
    if(returnExists('session_levels', $level_arguments) == 0){
        //6. Register the user
        $level = $level;        
        if($userResponse==""){
            switch ($level) {
                case 0:
                    //6a increment the level
                    $level++;
                    //6b. Insert level to the session_level table in the DB, so you dont serve them the same menu
                    $sql6b = "INSERT INTO `session_levels`(`sessionId`, `phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."', 1)";
                    $conn->query($sql6b);
                    //6c. Insert the phoneNumber to the users, since it comes with the first POST
                    $sql6c = "INSERT INTO `users`(`phonenumber`) VALUES ('".$phoneNumber."')";
                    $conn->query($sql6c);
                    //6d. Serve the menu request for name
                    $response = "CON Please enter your name";
                    // Print the response onto the page so that our gateway can read it
                    header('Content-type: text/plain');
                    echo $response;
                    break;
                default:
			    	//6e. You could use this to set a default
					$response = "END Opps1, something went wrong... \n";
			  		// Print the response onto the page so that our gateway can read it
			  		header('Content-type: text/plain');
 			  		echo $response;	
			        break;
            }            
        }        
    }
    elseif (returnExists('session_levels', $level_arguments) != 0 && strlen(getByValue('users','name',$level_arguments))==0){
        //7 post the user input
        $sql7 = "UPDATE `users` SET `name` = '$userResponse' WHERE `phonenumber` = '$phoneNumber'";
        $conn->query($sql7);
        $name=getByValue('users','name',$level_arguments);
        $response = "END Welcome 1 $name";
        echo $response;
    }
    else{
        //7. Check the level of the user from the DB
        $level=getByValue('session_levels', 'level', $level_arguments);
        $name=getByValue('users','name',$level_arguments);
        switch ($level) {
            case 1:
                //7a. Request for name again if name is not valid
                if(strlen($name)!="") {
                    $response = "END Welcome 2 $name";
                    echo $response;                    
                }                
                else{
                    //7b. increment level to avoid serving the same menu
                    $level++;
                    //7c. Update the level in the DB
                    $sql7c = "UPDATE `session_levels` SET `level` = '$level' WHERE `phonenumber` = '$phoneNumber'";
                    $conn->query($sql7c);
                    $response = "CON Name not supposed to be empty. Please enter your name \n";
                    // Print the response onto the page so that our gateway can read it
                    header('Content-type: text/plain');
                    echo $response;                    
                }
                break;
            case 2:
                //7d. increment level to avoid serving the same menu
                $level++;
                //7e. Update level in the DB
                $sql7e = "UPDATE `session_levels` SET `level` = '$level' WHERE `phonenumber` = '$phoneNumber'";
                $conn->query($sql7e);
                //7f. Update the name in the DB
                $sql7f = "UPDATE `users` SET `name` = '$userResponse' WHERE `phonenumber` = '$phoneNumber'";
                $conn->query($sql7f);
                //7g. fetch the name from the DB for use.               
                $name=getByValue('users','name',$level_arguments);
                $response = "END Welcome 3 $name";
                echo $response;
                break;
            case 3:
                //7h. Returning already registered user                
                $response = "END Welcome 4 $name";
                echo $response;                    
            default:
                //7i. Use this to set a default
                $response = "END Opps2, something went wrong... \n";
                // Print the response onto the page so that our gateway can read it
                header('Content-type: text/plain');
                echo $response;	
                break;
        }
    }

}
?>