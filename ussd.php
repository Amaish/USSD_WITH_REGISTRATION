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
                    //6a increment the level and insert into the DB to avoid serving the same menu
                    $level++;
                    $sql6a = "INSERT INTO `session_levels`(`sessionId`, `phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."', 1)";
                    $conn->query($sql6a);
                    //6b. Insert the phoneNumber to the users, since it comes with the first POST
                    $sql6b = "INSERT INTO `users`(`phonenumber`) VALUES ('".$phoneNumber."')";
                    $conn->query($sql6b);
                    //6c. Serve the menu request for name
                    $response = "CON Please enter your name";
                    // Print the response onto the page so that our gateway can read it
                    header('Content-type: text/plain');
                    echo $response;
                    break;
                default:
			    	//6d. You could use this to set a default
					$response = "END Oops0, something went wrong... \n";
			  		// Print the response onto the page so that our gateway can read it
			  		header('Content-type: text/plain');
 			  		echo $response;	
			        break;
            }            
        }
        else{
            $response = "END Oops1, something went wrong... try dialing *384*1404# again \n";
            // Print the response onto the page so that our gateway can read it
            header('Content-type: text/plain');
            echo $response;
        }     
    }
    elseif (returnExists('session_levels', $level_arguments) != 0 && strlen(getByValue('users','name',$level_arguments))==0 && $userResponse!=""){
        //7 update the user input into the db
        $sql7 = "UPDATE `users` SET `name` = '$userResponse' WHERE `phonenumber` = '$phoneNumber'";
        $conn->query($sql7);
        $name=getByValue('users','name',$level_arguments);
        $response = "END Welcome 1 $name";
        echo $response;
    }
    else{
        //8. Check the level of the user from the DB
        $level=getByValue('session_levels', 'level', $level_arguments);
        $name=getByValue('users','name',$level_arguments);
        switch ($level) {
            case 1:
                //8a. Use this to serve menus to registered users
                if(strlen($name)!="") {
                    $response = "END Welcome 2 $name";
                    echo $response;                    
                }                
                else{
                    //7b. increment level to avoid serving the same menu
                    $level++;
                    //7c. Request for name again if earlier name is not valid
                    $response = "CON Name should not be empty. Please enter your name \n";
                    // Print the response onto the page so that our gateway can read it
                    header('Content-type: text/plain');
                    echo $response;                    
                }
                break;       
            default:
                //7d. Use this to set a default
                $response = "END Oops2, something went wrong... \n";
                // Print the response onto the page so that our gateway can read it
                header('Content-type: text/plain');
                echo $response;	
                break;
        }
    }
}
?>