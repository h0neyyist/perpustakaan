<?php
// Perbaiki path ke config
$root_path = dirname(__DIR__);
include $root_path . '/config/database.php';

// Cek struktur tabel
$check_anggota = mysqli_query($conn, "SHOW COLUMNS FROM anggota");
$anggota_columns = [];
while($col = mysqli_fetch_assoc($check_anggota)) {
    $anggota_columns[] = $col['Field'];
}

$check_buku = mysqli_query($conn, "SHOW COLUMNS FROM buku");
$buku_columns = [];
while($col = mysqli_fetch_assoc($check_buku)) {
    $buku_columns[] = $col['Field'];
}

// Proses pengembalian
if(isset($_POST['kembalikan'])) {
    $id_peminjaman = $_POST['id_peminjaman'];
    $tanggal_kembali = date('Y-m-d');
    
    mysqli_begin_transaction($conn);
    
    try {
        $query_get_buku = "SELECT id_buku FROM peminjaman WHERE id_peminjaman = '$id_peminjaman' AND status = 'dipinjam'";
        $result = mysqli_query($conn, $query_get_buku);
        
        if(mysqli_num_rows($result) == 0) {
            throw new Exception("Data peminjaman tidak ditemukan!");
        }
        
        $peminjaman = mysqli_fetch_assoc($result);
        $id_buku = $peminjaman['id_buku'];
        
        $query_update = "UPDATE peminjaman SET tanggal_kembali = '$tanggal_kembali', status = 'dikembalikan' WHERE id_peminjaman = '$id_peminjaman'";
        mysqli_query($conn, $query_update);
        
        mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku = '$id_buku'");
        
        mysqli_commit($conn);
        header("Location: ../index.php?page=pengembalian&status=success");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: ../index.php?page=pengembalian&status=error");
        exit();
    }
}

// Ambil daftar peminjaman aktif
$select_fields = "p.*, b.judul";
if(in_array('pengarang', $buku_columns)) {
    $select_fields .= ", b.pengarang";
}
$select_fields .= ", a.nama";
if(in_array('nomor_anggota', $anggota_columns)) {
    $select_fields .= ", a.nomor_anggota";
}
if(in_array('no_telepon', $anggota_columns)) {
    $select_fields .= ", a.no_telepon";
}

$query = "SELECT $select_fields 
          FROM peminjaman p 
          JOIN buku b ON p.id_buku = b.id_buku 
          JOIN anggota a ON p.id_anggota = a.id_anggota 
          WHERE p.status = 'dipinjam' 
          ORDER BY p.tanggal_jatuh_tempo ASC";
$result = mysqli_query($conn, $query);
?>

<div class="container">
    <h2 class="mb-4"><i class="bi bi-arrow-return-left"></i> Transaksi Pengembalian Buku</h2>
    
    <?php if(isset($_GET['status'])): ?>
        <?php if($_GET['status'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> Buku berhasil dikembalikan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['status'] == 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> Gagal mengembalikan buku!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-journal-bookmark-fill"></i> Daftar Buku yang Sedang Dipinjam</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Judul Buku</th>
                            <?php if(in_array('pengarang', $buku_columns)): ?>
                                <th>Pengarang</th>
                            <?php endif; ?>
                            <th>Nama Anggota</th>
                            <?php if(in_array('nomor_anggota', $anggota_columns)): ?>
                                <th>No. Anggota</th>
                            <?php endif; ?>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while($row = mysqli_fetch_assoc($result)): 
                                $terlambat = (strtotime($row['tanggal_jatuh_tempo']) < time());
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['judul']) ?></strong></td>
                                    <?php if(in_array('pengarang', $buku_columns)): ?>
                                        <td><?= htmlspecialchars($row['pengarang'] ?? '-') ?></td>
                                    <?php endif; ?>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <?php if(in_array('nomor_anggota', $anggota_columns)): ?>
                                        <td><?= htmlspecialchars($row['nomor_anggota'] ?? '-') ?></td>
                                    <?php endif; ?>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])) ?>
                                        <?php if($terlambat): ?>
                                            <span class="badge bg-danger ms-2">Terlambat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $terlambat ? 'bg-danger' : 'bg-warning' ?>">
                                            <?= $terlambat ? 'Terlambat' : 'Dipinjam' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:inline" 
                                              onsubmit="return confirm('Yakin ingin mengembalikan buku ini?')">
                                            <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman'] ?>">
                                            <button type="submit" name="kembalikan" class="btn btn-success btn-sm">
                                                <i class="bi bi-arrow-return-left"></i> Kembalikan
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= 6 + (in_array('pengarang', $buku_columns) ? 1 : 0) + (in_array('nomor_anggota', $anggota_columns) ? 1 : 0) ?>" 
                                    class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i><br>
                                    Tidak ada buku yang sedang dipinjam
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>