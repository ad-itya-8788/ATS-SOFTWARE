<?php
$host = 'localhost';
$dbname = 'ats_software';
$port = '5432';
$user = 'postgres';
$password = '1234';
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn)
 {
    echo "Failed to connect to the database.";
} 

else
{

}
?>
