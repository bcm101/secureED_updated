<?php
try {
    
    /*Get DB connection*/
    require_once "../src/DBController.php";

    /*Get information from the post request*/
    $myusername = $_POST['username'];
    $mypassword = $_POST['password'];

    $patternUN = '/^[a-z0-9]+((\.|_|-)[a-z0-9]+)*@[a-z]+\.[a-z]{2,4}$/i';
    $UNmatches = preg_match($patternUN, $myusername);
    
    $patternPW1 = "/^[a-zA-Z0-9]{8,16}$/";
    $patternPW2 = "/[A-Z]+/";
    $patternPW3 = "/[0-9]+/";

    if( $UNmatches !== 1){
        throw new Exception("username must be a valid email !");
    }
    if (preg_match($patternPW3, $mypassword) == 0 or preg_match($patternPW2, $mypassword) == 0 or preg_match($patternPW1, $mypassword) == 0){
        throw new Exception("password must be 8 to 16 characters and contain at least 1 uppercase letter and digit !");
    }

    //convert password to 80 byte hash using ripemd256 before comparing
    $hashpassword = hash('ripemd256', $mypassword);

    if($myusername==null)
    {throw new Exception("input did not exist");}


    $myusername = strtolower($myusername); //makes username noncase-sensitive
    global $acctype;


    //query for count
    $statement = "SELECT COUNT(*) as count FROM User WHERE Email=:username AND (Password=:password OR Password=:hashpassword)";
    // $count = $db->querySingle($statement);
    
    
    $stmt1 = $db_r->prepare($statement);
    $stmt1->bindParam(':username', $myusername);
    $stmt1->bindParam(':password', $mypassword);
    $stmt1->bindParam(':hashpassword', $hashpassword);

    $count = $stmt1->execute();




    //query for the row(s)
    $statement = "SELECT * FROM User WHERE Email=:username AND (Password=:password OR Password=:hashpassword)";

    $stmt2 = $db_r->prepare($statement);
    $stmt2->bindParam(':username', $myusername);
    $stmt2->bindParam(':password', $mypassword);
    $stmt2->bindParam(':hashpassword', $hashpassword);

    $results = $stmt2->execute();

    if ($results !== false) //query failed check
    {
        if (($userinfo = $results->fetchArray()) !== (null || false)) //checks if rows exist
        {
            // users or user found
            $error = false;

            $acctype = $userinfo[2];
        } else {
            // user was not found
            $error = true;

        }
    } else {
        //query failed
        $error = true;

    }

    //determine if an account that met the credentials was found
    if ($count >= 1 && !$error) {
        //login success

        if (isset($_SESSION)) {
            //a session already existed
            session_destroy();
            session_start();
            $_SESSION['email'] = $myusername;
            $_SESSION['acctype'] = $acctype;
        } else {
            //a session did not exist
            session_start();
            $_SESSION['email'] = $myusername;
            $_SESSION['acctype'] = $acctype;
        }
        //redirect
        header("Location: ../public/dashboard.php");
    } else {
        //login fail
        header("Location: ../public/index.php?login=fail");
    }

//note: since the database is not changed, it is not backed up
}
catch(Exception $e)
{
    //prepare page for content
    include_once "ErrorHeader.php";

    //Display error information
    echo 'Username or password is incorrect';
    // var_dump($e->getTraceAsString());
    // echo 'in '.'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']."<br>";

    // $allVars = get_defined_vars();
    // debug_zval_dump($allVars);
}




?>