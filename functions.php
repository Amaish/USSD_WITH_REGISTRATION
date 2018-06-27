<?php
function formSearchString($arguments)
{
    $string = "";
    foreach ($arguments as $key => $value) {
        $string .= "`" . $key . "`=" . "'" . $value . "' && ";
    }
    
    $conditions = substr($string, 0, -3);
    return $conditions;
}
function returnExists($table, $arguments)
{
    global $conn;
    $appendSearch = formSearchString($arguments);
    $formedQuery  = "SELECT * FROM $table WHERE  $appendSearch";
    $getValues    = mysqli_num_rows(mysqli_query($conn, $formedQuery));
    return $getValues;
}

function getByValue($table, $column, $arguments)
{
    global $conn;
    $appendSearch = formSearchString($arguments);
    $formedQuery  = "SELECT * FROM $table WHERE $appendSearch";
    $executeQuery = mysqli_query($conn, $formedQuery);
    if (mysqli_num_rows($executeQuery) > 0) 
    {
        $getValues = mysqli_fetch_array($executeQuery);
        return $getValues[$column];
    } 
    else {
        return false;
    }
}

function UnregisteredWelcomeScreen()
{
    $response  = "CON Welcome to test USSD\n";
    $response .= "Do you wish to register?\n";
    $response .= "1. Yes\n";
    $response .= "2. No";
    return $response;
}
function RegisterUser()
{
    $response = "CON Please enter your name";
    return $response;
}

function RegisterdUserWelcomeScreen($name)
{
    $response  = "CON Welcome $name what would you like to check \n";
    $response .= "1. My Account \n";
    $response .= "2. My phone number \n";
    $response .= "3. Edit name \n";
    return $response;
}
function NameChangeSuccess()
{
    $response  = "CON Proceed?\n";
    $response .= "1. Yes \n";
    $response .= "2. No";
    return $response;
}
function ExitRegisteredUser($name)
{
    $response = "END Good-Bye $name";
    return $response;
}

?>