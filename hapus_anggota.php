<?php
include '../config/database.php';
$id = $_GET['id'];
$query = "DELETE FROM anggota WHERE id_anggota='$id'";
if(mysqli_query($conn, $query)) {
    header("Location: ../index.php?page=anggota&status=deleted");
} else {
    header("Location: ../index.php?page=anggota&status=error");
}
?>