<?php

class SecurityContext {
    public $state;
    public $userMachine;

    function __construct($session) {
        $this->state = new ConcreteState($session);
        $this->userMachine = new FacultyFunctionContext($this->state->isFaculty());
    }

    function request() {
        $this->userMachine->request();
    }

}

class FacultyFunctionContext {
    public $state;

    function __construct($isFaculty) {
        $this->state = new ConcreteFacultyFunctionState($isFaculty);
    }

    function request(){
        $this->state->handle();
    }

}

class SecureState {
    function handle() {

    }
}

class FacultyFunctionState {
    function handle() {
        
    }
}

class ConcreteFacultyFunctionState extends FacultyFunctionState{
    public $isFaculty;

    function __construct($isFaculty) {
        $this->isFaculty = $isFaculty;
    }

    function handle() {
        if($this->isFaculty){

            if (isset($_POST['submit'])) { //checks if submit var is set

                require_once "../src/DBController.php";

                $currentDirectory = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');//get root directory
                $uploadDirectory = "\uploads\\";
            
                //get info about the file
                $filename = $_FILES['file']['name'];
                $filetmp  = $_FILES['file']['tmp_name'];
            
                //create the upload path with the original filename
                $uploadPath = $currentDirectory . $uploadDirectory . basename($filename);
            
                //copy file to uploads folder
                copy($filetmp, $uploadPath);
            
                //prepare vars to insert data into database
                $handle = fopen(($_FILES['file']['tmp_name']), "r"); //sets a read-only pointer at beginning of file
                $crn = $_POST['crn']; //grabs CRN from form
                $path = pathinfo($_FILES['file']['name']); //path info for file
            
            
                $statement = "INSERT INTO Grade VALUES (:crn, :studentID, :grade)";
                $stmt = $db->prepare($statement);
            
                //insert data into the database if csv
                if($path['extension'] == 'csv') { //check if file is .csv
                    while (($data = fgetcsv($handle, 9001, ",")) !== FALSE) { //iterate through csv
                        $crn = $db->escapeString($crn); //sanitize the crn
                        $stmt->bindParam(':crn', $crn);
                        $stmt->bindParam(':studentID', $data[0]);
                        $stmt->bindParam(':grade', $data[1]);
                        $stmt->execute();
                    }
            
                    $db->backup($db, "temp", $GLOBALS['dbPath']);
                    fclose($handle);
                }
            
                
                header("Location: ../public/dashboard.php");
                
            
            }else{
                echo("no submission found");
            }


            return true;
        }else{

            echo("error with authenticating user");

            return false;
        }
    }
}

class ConcreteState extends SecureState{
    public $session;

    function isFaculty() {

        session_start();

        if($_SESSION['acctype']===2){
            return true;
        }else{
            return false;
        }
    }

    function __construct($session) {
        $this->session = $session;
    }

}

?>