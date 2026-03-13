<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "rsashs_eportal",
    3307
);

if (!$conn) {
    die("Connection Error: " . mysqli_connect_error());
}
