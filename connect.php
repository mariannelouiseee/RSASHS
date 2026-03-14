<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "rsashs_eportal"
);

if (!$conn) {
    die("Connection Error: " . mysqli_connect_error());
}
