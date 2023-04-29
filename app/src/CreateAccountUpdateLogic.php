<?php
// Import necessary classes
require_once "../src/AbstractSecureStrategyFactory.php";
require_once "../src/ConcreteStrategyFactory.php";
require_once "../src/SecureContext.php";
require_once "../src/SecureStrategy.php";
require_once "../src/CreateNewAccountStrategy.php";
require_once "../src/DBController.php";

// Access Control
session_start();

if (!isset($_SESSION['email']) || empty($_SESSION['email']) || $_SESSION['acctype'] != 1) {
    http_response_code(403);
    die('Forbidden');
}

try {
    // Get information from the search (post) request
    $email = strtolower($_POST['email']);
    $acctype = $_POST['acctype'];
    $password = hash('ripemd256', $_POST['password']);
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $dob = $_POST['dob'];
    $studentyear = $_POST['studentyear'];
    $facultyrank = $_POST['facultyrank'];
    $squestion = $_POST['squestion'];
    $sanswer = $_POST['sanswer'];

    if ($acctype === "3") {
        $facultyrank = null;
    } else if ($acctype === "2") {
        $studentyear = null;
    }

    // Create a ConcreteStrategyFactory instance and set it in SecureContext
    $strategyFactory = new ConcreteStrategyFactory();
    $secureContext = new SecureContext($strategyFactory);

    // Set the strategy and execute it
    $secureContext->setStrategy(new CreateNewAccountStrategy());
    $result = $secureContext->executeStrategy([
        'email' => $email,
        'acctype' => $acctype,
        'password' => $password,
        'fname' => $fname,
        'lname' => $lname,
        'dob' => $dob,
        'studentyear' => $studentyear,
        'facultyrank' => $facultyrank,
        'squestion' => $squestion,
        'sanswer' => $sanswer
    ]);

    if ($result) {
        header("Location: ../public/dashboard.php");
    } else {
        throw new Exception("Create account failed");
    }
} catch (Exception $e) {
    include_once "ErrorHeader.php";

    echo 'Caught exception: ',  $e->getMessage(), "<br>";
    var_dump($e->getTraceAsString());
    echo 'in '.'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']."<br>";

    $allVars = get_defined_vars();
    debug_zval_dump($allVars);
}
