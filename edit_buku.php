<?php
$root_path = dirname(__DIR__);
include $root_path . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id_buku = (int)$_POST['id_buku'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $pengarang = mysqli_real_escape_string($conn, $_POST['pengarang']);
    $penerbit = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $tahun_terbit = !empty($_POST['tahun_terbit']) ? (int)$_POST['tahun_terbit'] : null;
    $stok = (int)$_POST['stok'];
    
    // Validasi
    if(empty($judul) || empty($pengarang)) {
        header("Location: ../index.php?page=buku&status=error&msg=Judul dan Pengarang harus diisi");
        exit();
    }
    
    // Buat query update
    if($tahun_terbit) {
        $query = "UPDATE buku SET 
                  judul='$judul', 
                  pengarang='$pengarang', 
                  penerbit='$penerbit', 
                  tahun_terbit='$tahun_terbit', 
                  stok='$stok' 
                  WHERE id_buku='$id_buku'";
    } else {
        $query = "UPDATE buku SET 
                  judul='$judul', 
                  pengarang='$pengarang', 
                  penerbit='$penerbit', 
                  stok='$stok' 
                  WHERE id_buku='$id_buku'";
    }
    
    if(mysqli_query($conn, $query)) {
        header("Location: ../index.php?page=buku&status=success");
    } else {
        $error = mysqli_error($conn);
        header("Location: ../index.php?page=buku&status=error&msg=" . urlencode($error));
    }
    
} else {
    header("Location: ../index.php?page=buku");
}
?>