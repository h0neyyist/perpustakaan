<?php
$root_path = dirname(__DIR__);
include $root_path . '/config/database.php';

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Cek apakah buku sedang dipinjam
    $check_query = "SELECT COUNT(*) as total FROM peminjaman WHERE id_buku = '$id' AND status = 'dipinjam'";
    $check_result = mysqli_query($conn, $check_query);
    $check_data = mysqli_fetch_assoc($check_result);
    
    if($check_data['total'] > 0) {
        header("Location: ../index.php?page=buku&status=error&msg=Buku sedang dipinjam, tidak bisa dihapus");
    } else {
        $query = "DELETE FROM buku WHERE id_buku='$id'";
        if(mysqli_query($conn, $query)) {
            header("Location: ../index.php?page=buku&status=deleted");
        } else {
            header("Location: ../index.php?page=buku&status=error&msg=" . urlencode(mysqli_error($conn)));
        }
    }
} else {
    header("Location: ../index.php?page=buku");
}
?>