<?php
//open file for reading and converting to an array for processing
function readFileToArray($csvFile)
{

    $fileToRead = fopen($csvFile, 'r');
    $data[] = array();
    //clean data and combine into an array of objects
    while (!feof($fileToRead)) {
        $header = fgetcsv($fileToRead);
        $header = array_map('trim', $header);
        while ($row = fgetcsv($fileToRead)) {
            $row = array_map('trim', $row);
            $data[] = array_combine($header, $row);
        }
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
    } else {
        echo "Connected successfully\n";
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
    return $conn;
}

//create table
function createTable($conn)
{
    $createTableSql = "CREATE TABLE IF NOT EXISTS users (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(255) NOT NULL,
surname VARCHAR(255) NOT NULL,
email VARCHAR(255) UNIQUE NOT NULL)";
    if (!$conn->query($createTableSql)) {
        echo "Error creating table: " . $conn->error . "\n";
    }
}

//read filename convert to array
$filename = 'users.csv';
$fileData = readFileToArray($filename);
//db server connection Detail
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "catalystCodeTestDb";
$conn = connectToDatabase($servername, $username, $password, $dbname);
createTable($conn);
insertData($conn, $fileData);

//process data and insert into database
function insertData($conn, $fileData)
{
    foreach ($fileData as $rowData) {
        $name = "";
        $surname = "";
        $email = "";
        $query = "";

        //handle escape ' characters of data value
        if (isset($rowData['name']) && isset($rowData['surname']) && isset($rowData['email'])) {
            $name = $rowData['name'];
            $surname = $rowData['surname'];
            $email = $rowData['email'];
            $name = str_replace("'", "\'", $name);
            $surname = str_replace("'", "\'", $surname);
            $email = str_replace("'", "\'", $email);
        }

        if ($name != "" && $surname != "" && $email != "") {
            $query = "INSERT INTO users (name, surname, email) VALUES ('" . $name . "','" . $surname . "','" . $email . "')";
        }
        
        if ($conn->query($query)) {
            echo "New record created successfully for " . $name . " " . $surname . ".\n";
        } else {
            echo "Error: " . $query . " " . $conn->error . '\n';
        }
    }

}

$conn->close();
?>