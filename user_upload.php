<?php
//open file for reading and converting to an array for processing
function readFileToArray($csvFile)
{
    $fileToRead = fopen($csvFile, 'r') or die('Could not open file');
    $ext = pathinfo($csvFile, PATHINFO_EXTENSION);
    $data[] = array();

    if($ext==="csv"){
        //clean data and combine into an array of objects
        while (!feof($fileToRead)) {
            $header = fgetcsv($fileToRead);
            $header = array_map('trim', $header);
            //check header are correct format
            if($header[0]!=="name"&&$header[1]!=="surname"&&$header[2]!=="email"){
                die("csv file header should be name, surname and email");
            }
            $rowCounter = 0;
            while ($row = fgetcsv($fileToRead)) {
                //check if first row of data exclude header have content of user details
                if($row[0]==""&&!isset($row[1])&&$rowCounter<1){
                    die("please supplement enough data in csv file");
                }
                $row = array_map('trim', $row);
                $data[] = array_combine($header, $row);
                $rowCounter++;
            }
        }

    }
    else{
        die("File format is not csv.");
    }

    fclose($fileToRead);
    return $data;
}

function connectToDatabase($servername, $username, $password, $dbname)
{
// Create connection
    $conn = mysqli_connect($servername, $username, $password);

// Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error() . "\n");
    }


//create database
    $connectDbSql = "CREATE DATABASE IF NOT EXISTS " . $dbname;
    if (!$conn->query($connectDbSql)) {
        echo "Error connecting database: " . $conn->error . "\n";
    }
    $conn = new mysqli($servername, $username, $password, $dbname);
// Check connection to new database
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    else{
        echo "Database connected. \n";
    }
    return $conn;
}

//create table
function createTable($conn)
{
    $createTableSql = "
CREATE TABLE IF NOT EXISTS users (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(255) NOT NULL,
surname VARCHAR(255) NOT NULL,
email VARCHAR(255) NOT NULL,
    UNIQUE (email))";
    if (!$conn->query($createTableSql)) {
        die("Error creating table: " . $conn->error . "\n");
    }
    else{
        echo "Table user created. \n";
    }
}

//iterate, process data and insert into database
function insertData($conn, $fileData)
{
    $conn->query('TRUNCATE TABLE users'); /*to be removed*/
    foreach ($fileData as $rowData) {
        $name = "";
        $surname = "";
        $email = "";
        $query = "";
        $insertName = "";
        $insertSurname = "";
        $insertEmail = "";

        if (isset($rowData['name']) && isset($rowData['surname']) && isset($rowData['email'])) {
            $name = $rowData['name'];
            $surname = $rowData['surname'];
            $email = $rowData['email'];
            //escape ' characters of data value for data insertion
            $insertName = str_replace("'", "\' ", $name);
            $insertSurname = str_replace("'", "\' ", $surname);
            $insertEmail = str_replace("'", "\' ", $email);
            //captalise names and surnames
            $insertName = str_replace(" ", "", ucwords(strtolower($insertName)));
            $insertSurname = str_replace(" ", "", ucwords(strtolower($insertSurname)));
            //lowercase email addresses
            $insertEmail = str_replace(" ", "", strtolower($insertEmail));
        }

        if ($name != "" && $surname != "" && $email != "") {
            $query = "INSERT INTO users (name, surname, email) VALUES ('" . $insertName . "','" . $insertSurname . "','" . $insertEmail . "')";
        }
        if ($query !== "") {
            if (validEmail($email)) {
                if ($conn->query($query)) {
                    //used original data name here
                    echo "New record inserted successfully for " . $name . " " . $surname . ".\n";
                } else {
                    echo "Error: " . $query . " " . $conn->error . "\n";
                }
            } else {
                $message = "Invalid email format of " . $email . "\n";
                fwrite(STDOUT, $message);
            }
        }
    }

}

function validEmail($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    } else {
        return true;
    }
}

function main()
{

    //db server connection Detail
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "catalystCodeTestDb";

    //handling cli commands
    $shortOptions = "u";
    $shortOptions .= "p";
    $shortOptions .= "h";
    $longOptions = ["file:", "dry_run", "help", "create_table"];
    $arguments = getopt($shortOptions, $longOptions);
    $keys = array_keys($arguments);
    $isDryRun = false;
    foreach ($keys as $key) {
        switch ($key) {
            case "u":
                echo "username: ". $username."\n";
                break;
            case "p":
                echo "password: ". $password."\n";
                break;
            case "h":
                echo "hostname: ". $servername."\n";
                break;
            case "create_table":
                $conn = connectToDatabase($servername, $username, $password, $dbname);
                createTable($conn);
                $conn->close();
                break;
            case "file":
                echo "filename: ". $arguments["file"]."\n";
                break;
            case "dry_run":
                $filename = 'users.csv';
                $isDryRun = true;
                if (isset($arguments["file"])&&$arguments["file"]!==false) {
                    $filename = $arguments["file"];
                }
                $fileData = readFileToArray($filename);
                $conn = connectToDatabase($servername, $username, $password, $dbname);
                createTable($conn);
                $conn->close();
                break;
            case "help":
                echo "--file [csv file name] – this is the name of the CSV to be parsed \n";
                echo "--create_table – this will cause the MySQL users table to be built (and no further action will be taken \n)";
                echo "--dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered \n";
                echo "-u – MySQL username \n";
                echo "-p – MySQL password \n";
                echo "-h – MySQL host \n";
                echo "\n";
        }
    }

    if(!$isDryRun){
        $filename = 'users.csv';
        if (isset($arguments["file"])&&$arguments["file"]!==false) {
            $filename = $arguments["file"];
        }
        $fileData = readFileToArray($filename);
        $conn = connectToDatabase($servername, $username, $password, $dbname);
        createTable($conn);
        insertData($conn, $fileData);
        $conn->close();
    }
}

main();

?>