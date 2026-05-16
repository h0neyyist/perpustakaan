<?php
include '../config/database.php';
$nomor_anggota = $_POST['nomor_anggota'];
$nama = $_POST['nama'];
$alamat = $_POST['alamat'];
$no_telepon = $_POST['no_telepon'];

$query = "INSERT INTO anggota (nomor_anggota, nama, alamat, no_telepon) VALUES ('$nomor_anggota', '$nama', '$alamat', '$no_telepon')";
if(mysqli_query($conn, $query)) {
    header("Location: ../index.php?page=anggota&status=success");
} else {
    header("Location: ../index.php?page=anggota&status=error");
}
?>