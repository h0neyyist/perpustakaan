<?php
include '../config/database.php';
$id_buku = $_POST['id_buku'];
$id_anggota = $_POST['id_anggota'];
$tanggal_pinjam = $_POST['tanggal_pinjam'];
$tanggal_jatuh_tempo = $_POST['tanggal_jatuh_tempo'];

// Cek stok buku
$query_stok = "SELECT stok FROM buku WHERE id_buku='$id_buku'";
$result = mysqli_query($conn, $query_stok);
$stok = mysqli_fetch_assoc($result)['stok'];

if($stok > 0) {
    // Kurangi stok buku
    mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku='$id_buku'");
    
    // Simpan peminjaman
    $query = "INSERT INTO peminjaman (id_buku, id_anggota, tanggal_pinjam, tanggal_jatuh_tempo, status) VALUES ('$id_buku', '$id_anggota', '$tanggal_pinjam', '$tanggal_jatuh_tempo', 'dipinjam')";
    if(mysqli_query($conn, $query)) {
        header("Location: ../index.php?page=peminjaman&status=success");
    } else {
        header("Location: ../index.php?page=peminjaman&status=error");
    }
} else {
    header("Location: ../index.php?page=peminjaman&status=outofstock");
}
?>