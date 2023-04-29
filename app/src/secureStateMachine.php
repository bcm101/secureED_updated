<?php

class SecurityContext { // Defines the interface that the client (faculty) is going to use. All client operations are handled by an instance of this
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

class FacultyFunctionContext { // Defines the same function requests as security context. This is where requests get forwarded from the user state machine.
    
    public $state;

    function __construct($isFaculty) {
        $this->state = new ConcreteFacultyFunctionState($isFaculty);
    }

    function request(){
        $this->state->handle();
    }

}

class SecureState { //An interface for the possible operations handled by the security state machine
    function handle() {

    }
}

class FacultyFunctionState { // Has the same interface as SecureState. Defines the user level state machine.
    
    function handle() {
        
    }
}

class ConcreteFacultyFunctionState extends FacultyFunctionState{ // Implements the user level state dependent behavior for the enter grades function.
    
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
                    while (($line = fgetcsv($handle, 20)) !== FALSE) { //iterate through csv

                        $str = implode("", $line);
                        $pattern = '/^[0-9]{9},[A-DF]{1}(\+|\-){0,1}$/';
                        
                        if(preg_match($pattern, $str) == 1){
                            $crn = $db->escapeString($crn); //sanitize the crn
                            $stmt->bindParam(':crn', $crn);
                            $stmt->bindParam(':studentID', $data[0]);
                            $stmt->bindParam(':grade', $data[1]);
                            $stmt->execute();
                        }
                        
                    }
            
                    $db->backup($db, "temp", $GLOBALS['dbPath']);
                    fclose($handle);
                }
            
                
                header("Location: ../public/dashboard.php");
                
            
            }else{
                echo("no submission found");
                return false;
            }


            return true;
        }else{

            echo("error with authenticating user");

            return false;
        }
    }
}

class ConcreteState extends SecureState{ //  implements the security state dependent behavior for the operation of entering the grades
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
