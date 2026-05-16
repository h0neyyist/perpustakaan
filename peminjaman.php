<?php
// Perbaiki path ke config
$root_path = dirname(__DIR__);
include $root_path . '/config/database.php';

// Cek struktur tabel anggota
$check_anggota = mysqli_query($conn, "SHOW COLUMNS FROM anggota");
$anggota_columns = [];
while($col = mysqli_fetch_assoc($check_anggota)) {
    $anggota_columns[] = $col['Field'];
}

// Cek struktur tabel buku
$check_buku = mysqli_query($conn, "SHOW COLUMNS FROM buku");
$buku_columns = [];
while($col = mysqli_fetch_assoc($check_buku)) {
    $buku_columns[] = $col['Field'];
}

// Ambil daftar buku yang tersedia (stok > 0)
$query_buku = "SELECT * FROM buku WHERE stok > 0 ORDER BY judul";
$buku_list = mysqli_query($conn, $query_buku);

// Ambil daftar anggota
$query_anggota = "SELECT * FROM anggota ORDER BY nama";
$anggota_list = mysqli_query($conn, $query_anggota);

// Bangun query untuk peminjaman aktif berdasarkan kolom yang ada
$select_fields = "p.*, b.judul";
if(in_array('pengarang', $buku_columns)) {
    $select_fields .= ", b.pengarang";
}
$select_fields .= ", a.nama";
if(in_array('nomor_anggota', $anggota_columns)) {
    $select_fields .= ", a.nomor_anggota";
}

$query_pinjam = "SELECT $select_fields 
                 FROM peminjaman p 
                 JOIN buku b ON p.id_buku = b.id_buku 
                 JOIN anggota a ON p.id_anggota = a.id_anggota 
                 WHERE p.status = 'dipinjam' 
                 ORDER BY p.tanggal_pinjam DESC";
$pinjam_list = mysqli_query($conn, $query_pinjam);

if(!$pinjam_list) {
    die("Error query: " . mysqli_error($conn));
}
?>

<div class="container">
    <h2 class="mb-4"><i class="bi bi-cart-plus"></i> Transaksi Peminjaman Buku</h2>
    
    <?php if(isset($_GET['status'])): ?>
        <?php if($_GET['status'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> Peminjaman buku berhasil!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['status'] == 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> Gagal meminjam: <?= $_GET['msg'] ?? 'Stok tidak mencukupi' ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['status'] == 'outofstock'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-archive"></i> Maaf, stok buku sedang habis!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Form Peminjaman -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Form Peminjaman Buku</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="../proses/proses_peminjaman.php">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Pilih Buku</label>
                        <select name="id_buku" class="form-select" required>
                            <option value="">-- Pilih Buku --</option>
                            <?php while($buku = mysqli_fetch_assoc($buku_list)): ?>
                                <option value="<?= $buku['id_buku'] ?>">
                                    <?= htmlspecialchars($buku['judul']) ?> 
                                    (Stok: <?= $buku['stok'] ?>)
                                    <?php if(in_array('pengarang', $buku_columns) && $buku['pengarang']): ?>
                                        - <?= htmlspecialchars($buku['pengarang']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Pilih Anggota</label>
                        <select name="id_anggota" class="form-select" required>
                            <option value="">-- Pilih Anggota --</option>
                            <?php while($anggota = mysqli_fetch_assoc($anggota_list)): ?>
                                <option value="<?= $anggota['id_anggota'] ?>">
                                    <?php if(in_array('nomor_anggota', $anggota_columns) && $anggota['nomor_anggota']): ?>
                                        <?= htmlspecialchars($anggota['nomor_anggota']) ?> - 
                                    <?php endif; ?>
                                    <?= htmlspecialchars($anggota['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Pinjam</label>
                        <input type="date" name="tanggal_pinjam" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Jatuh Tempo (7 hari)</label>
                        <input type="date" name="tanggal_jatuh_tempo" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Peminjaman
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="bi bi-arrow-repeat"></i> Reset
                </button>
            </form>
        </div>
    </div>
    
    <!-- Daftar Peminjaman Aktif -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-list-check"></i> Daftar Peminjaman Aktif</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Buku</th>
                            <?php if(in_array('pengarang', $buku_columns)): ?>
                                <th>Pengarang</th>
                            <?php endif; ?>
                            <th>Anggota</th>
                            <?php if(in_array('nomor_anggota', $anggota_columns)): ?>
                                <th>No. Anggota</th>
                            <?php endif; ?>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($pinjam_list) > 0): ?>
                            <?php $no = 1; while($row = mysqli_fetch_assoc($pinjam_list)): 
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
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= 5 + (in_array('pengarang', $buku_columns) ? 1 : 0) + (in_array('nomor_anggota', $anggota_columns) ? 1 : 0) ?>" 
                                    class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i><br>
                                    Tidak ada peminjaman aktif
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>