<?php

require_once "SecureStrategy.php";
require_once "../src/DBController.php";

class CreateNewAccountStrategy implements SecureStrategy {
    public function execute($params) {
        // Get the required parameters
        extract($params);

        // Database connection
        $db = new DBController();

        // Check for a valid UserID
        $rows = $db->query("SELECT COUNT(*) as count FROM User");
        $row = $rows->fetchArray();
        $newUserID = $row['count'] + 927000000;

        // Check if user already exists
        $query = "SELECT Email FROM User WHERE Email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':email', $email);
        $results = $stmt->execute();

        if (!$results) {
            // Update the database with the new info
            $query = "INSERT INTO User VALUES (:newUserID, :email, :acctype, :password, :fname, :lname, :dob, :studentyear, :facultyrank, :squestion, :sanswer)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':newUserID', $newUserID, SQLITE3_INTEGER);
            $stmt->bindParam(':email', $email, SQLITE3_TEXT);
            $stmt->bindParam(':acctype', $acctype, SQLITE3_INTEGER);
            $stmt->bindParam(':password', $password, SQLITE3_TEXT);
            $stmt->bindParam(':fname', $fname, SQLITE3_TEXT);
            $stmt->bindParam(':lname', $lname, SQLITE3_TEXT);
            $stmt->bindParam(':dob', $dob, SQLITE3_TEXT);
            $stmt->bindParam(':studentyear', $studentyear, SQLITE3_INTEGER);
            $stmt->bindParam(':facultyrank', $facultyrank, SQLITE3_TEXT);
            $stmt->bindParam(':squestion', $squestion, SQLITE3_TEXT);
            $stmt->bindParam(':sanswer', $sanswer, SQLITE3_TEXT);

            $results = $stmt->execute();

            if ($results) {
                // Backup the database
                $db->backup($db, "temp", $GLOBALS['dbPath']);
                return true;
            }
        }

        return false;
    }
}
