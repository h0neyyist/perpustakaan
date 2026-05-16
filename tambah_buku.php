<?php
// Gunakan path absolut
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/Perpustakaan';
include $root_path . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $pengarang = mysqli_real_escape_string($conn, $_POST['pengarang']);
    $penerbit = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $tahun_terbit = !empty($_POST['tahun_terbit']) ? (int)$_POST['tahun_terbit'] : null;
    $stok = (int)$_POST['stok'];
    
    if(empty($judul) || empty($pengarang)) {
        header("Location: /Perpustakaan/index.php?page=buku&status=error&msg=Judul dan Pengarang harus diisi");
        exit();
    }
    
    if($tahun_terbit) {
        $query = "INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, stok) 
                  VALUES ('$judul', '$pengarang', '$penerbit', '$tahun_terbit', '$stok')";
    } else {
        $query = "INSERT INTO buku (judul, pengarang, penerbit, stok) 
                  VALUES ('$judul', '$pengarang', '$penerbit', '$stok')";
    }
    
    if(mysqli_query($conn, $query)) {
        header("Location: /Perpustakaan/index.php?page=buku&status=success");
    } else {
        $error = mysqli_error($conn);
        header("Location: /Perpustakaan/index.php?page=buku&status=error&msg=" . urlencode($error));
    }
} else {
    header("Location: /Perpustakaan/index.php?page=buku");
}
?>