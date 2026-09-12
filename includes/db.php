<?php
$host     = "sql206.infinityfree.com";
$username = "if0_41927172";
$password = "Sneha8011";
$database = "if0_41927172_hersafe";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>