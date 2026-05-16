<?php
include '../config/database.php';
$id_anggota = $_POST['id_anggota'];
$nomor_anggota = $_POST['nomor_anggota'];
$nama = $_POST['nama'];
$alamat = $_POST['alamat'];
$no_telepon = $_POST['no_telepon'];

$query = "UPDATE anggota SET nomor_anggota='$nomor_anggota', nama='$nama', alamat='$alamat', no_telepon='$no_telepon' WHERE id_anggota='$id_anggota'";
if(mysqli_query($conn, $query)) {
    header("Location: ../index.php?page=anggota&status=success");
} else {
    header("Location: ../index.php?page=anggota&status=error");
}
?>