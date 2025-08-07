<?php

$conn = mysqli_connect("localhost", "root", "", "rsashs_portal");

if (!$conn) {
    die("Connection Error" . mysqli_connect_error());
}

?>