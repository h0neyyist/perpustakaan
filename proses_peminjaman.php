<?php
// Perbaiki path
$root_path = dirname(__DIR__);
include $root_path . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_buku = $_POST['id_buku'];
    $id_anggota = $_POST['id_anggota'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $tanggal_jatuh_tempo = $_POST['tanggal_jatuh_tempo'];
    
    mysqli_begin_transaction($conn);
    
    try {
        $query_stok = "SELECT stok FROM buku WHERE id_buku = '$id_buku' FOR UPDATE";
        $result = mysqli_query($conn, $query_stok);
        $buku = mysqli_fetch_assoc($result);
        
        if ($buku['stok'] <= 0) {
            throw new Exception("Stok tidak mencukupi");
        }
        
        mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku = '$id_buku'");
        
        $query_pinjam = "INSERT INTO peminjaman (id_buku, id_anggota, tanggal_pinjam, tanggal_jatuh_tempo, status) 
                         VALUES ('$id_buku', '$id_anggota', '$tanggal_pinjam', '$tanggal_jatuh_tempo', 'dipinjam')";
        mysqli_query($conn, $query_pinjam);
        
        mysqli_commit($conn);
        header("Location: ../index.php?page=peminjaman&status=success");
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: ../index.php?page=peminjaman&status=error&msg=" . $e->getMessage());
    }
} else {
    header("Location: ../index.php?page=peminjaman");
}
?>