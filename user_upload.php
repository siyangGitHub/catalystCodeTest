<?php
//open file for reading and converting to an array for processing
function readFileToArray($csvFile){

    $fileToRead = fopen($csvFile, 'r');

    while (!feof($fileToRead) ) {
        $data[] = fgetcsv($fileToRead, 1000, ',');
    }

    fclose($fileToRead);
    return $data;
}

function connectToDatabase(){


// Create connection
    $conn = mysqli_connect($servername, $username, $password);

// Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error()."\n");
    }
    else{
        echo "Connected successfully\n";
    }

//create database
    $connectDbSql = "CREATE DATABASE IF NOT EXISTS ". $dbname;
    if (!$conn->query($connectDbSql)){
        echo "Error connecting database: " . $conn->error. "\n";
    }
    $conn = new mysqli($servername, $username, $password, $dbname);
// Check connection to new database
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}


//create table
function createTable(){
    $createTableSql = "CREATE TABLE IF NOT EXISTS users (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(255) NOT NULL,
surname VARCHAR(255) NOT NULL,
email VARCHAR(255) UNIQUE NOT NULL)";
    if (!$conn->query($createTableSql)){
        echo "Error creating table: " . $conn->error. "\n";
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
connectToDatabase();
createTable();



//process data
foreach($fileData as $rowData){
    //print_r($rowData);
}



?>