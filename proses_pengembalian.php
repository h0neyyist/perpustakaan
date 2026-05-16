<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_peminjaman = $_POST['id_peminjaman'];
    $tanggal_kembali = date('Y-m-d');
    
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // Ambil id_buku dari peminjaman
        $query_get_buku = "SELECT id_buku FROM peminjaman WHERE id_peminjaman = '$id_peminjaman' AND status = 'dipinjam'";
        $result = mysqli_query($conn, $query_get_buku);
        
        if (mysqli_num_rows($result) == 0) {
            throw new Exception("Data peminjaman tidak ditemukan atau sudah dikembalikan!");
        }
        
        $peminjaman = mysqli_fetch_assoc($result);
        $id_buku = $peminjaman['id_buku'];
        
        // Update status peminjaman
        $query_update = "UPDATE peminjaman 
                         SET tanggal_kembali = '$tanggal_kembali', 
                             status = 'dikembalikan' 
                         WHERE id_peminjaman = '$id_peminjaman'";
        
        if (!mysqli_query($conn, $query_update)) {
            throw new Exception("Gagal mengupdate status peminjaman");
        }
        
        // Tambah stok buku kembali
        $query_stok = "UPDATE buku SET stok = stok + 1 WHERE id_buku = '$id_buku'";
        if (!mysqli_query($conn, $query_stok)) {
            throw new Exception("Gagal mengupdate stok buku");
        }
        
        // Commit transaksi
        mysqli_commit($conn);
        
        // Hitung denda jika terlambat (opsional)
        $query_tgl = "SELECT tanggal_jatuh_tempo FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'";
        $result_tgl = mysqli_query($conn, $query_tgl);
        $data = mysqli_fetch_assoc($result_tgl);
        $jatuh_tempo = $data['tanggal_jatuh_tempo'];
        
        if (strtotime($tanggal_kembali) > strtotime($jatuh_tempo)) {
            $selisih_hari = floor((strtotime($tanggal_kembali) - strtotime($jatuh_tempo)) / (60 * 60 * 24));
            $denda = $selisih_hari * 2000; // Denda Rp 2000/hari
            header("Location: ../index.php?page=pengembalian&status=success&denda=$denda");
        } else {
            header("Location: ../index.php?page=pengembalian&status=success");
        }
        
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        header("Location: ../index.php?page=pengembalian&status=error&msg=" . $e->getMessage());
    }
} else {
    header("Location: ../index.php?page=pengembalian");
}
?>