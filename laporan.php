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

// Bangun query berdasarkan kolom yang ada
$select_fields = "p.*, b.judul";
if(in_array('pengarang', $buku_columns)) {
    $select_fields .= ", b.pengarang";
}
if(in_array('penerbit', $buku_columns)) {
    $select_fields .= ", b.penerbit";
}
if(in_array('tahun_terbit', $buku_columns)) {
    $select_fields .= ", b.tahun_terbit";
}

$select_fields .= ", a.nama";
if(in_array('nomor_anggota', $anggota_columns)) {
    $select_fields .= ", a.nomor_anggota";
}
if(in_array('alamat', $anggota_columns)) {
    $select_fields .= ", a.alamat";
}
if(in_array('no_telepon', $anggota_columns)) {
    $select_fields .= ", a.no_telepon";
}

// Query laporan semua peminjaman
$query = "SELECT $select_fields 
          FROM peminjaman p 
          JOIN buku b ON p.id_buku = b.id_buku 
          JOIN anggota a ON p.id_anggota = a.id_anggota 
          ORDER BY p.tanggal_pinjam DESC";
$result = mysqli_query($conn, $query);

if(!$result) {
    die("Error query: " . mysqli_error($conn));
}

// Hitung statistik untuk laporan
$total_peminjaman = mysqli_num_rows($result);
$total_dipinjam = 0;
$total_dikembalikan = 0;

// Reset result untuk hitung ulang
$temp_result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($temp_result)) {
    if($row['status'] == 'dipinjam') {
        $total_dipinjam++;
    } else {
        $total_dikembalikan++;
    }
}

// Hitung peminjaman terlambat
$query_terlambat = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam' AND tanggal_jatuh_tempo < CURDATE()";
$result_terlambat = mysqli_query($conn, $query_terlambat);
$terlambat_data = mysqli_fetch_assoc($result_terlambat);
$total_terlambat = $terlambat_data['total'];
?>

<div class="container">
    <h2 class="mb-4"><i class="bi bi-file-text"></i> Laporan Peminjaman Buku</h2>
    
    <!-- Statistik Laporan -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary-gradient stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Transaksi</h5>
                            <h2><?= $total_peminjaman ?></h2>
                        </div>
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning-gradient stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Sedang Dipinjam</h5>
                            <h2><?= $total_dipinjam ?></h2>
                        </div>
                        <i class="bi bi-book-half"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success-gradient stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Dikembalikan</h5>
                            <h2><?= $total_dikembalikan ?></h2>
                        </div>
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-danger stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Terlambat</h5>
                            <h2><?= $total_terlambat ?></h2>
                        </div>
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabel Laporan -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-table"></i> Seluruh Transaksi Peminjaman</h5>
            <div>
                <button onclick="window.print()" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-printer"></i> Cetak Laporan
                </button>
                <button onclick="exportToExcel()" class="btn btn-success btn-sm">
                    <i class="bi bi-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="laporanTable">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>ID Pinjam</th>
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
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php 
                            $no = 1; 
                            mysqli_data_seek($result, 0);
                            while($row = mysqli_fetch_assoc($result)): 
                                $terlambat = ($row['status'] == 'dipinjam' && strtotime($row['tanggal_jatuh_tempo']) < time());
                                $telat_kembali = ($row['status'] == 'dikembalikan' && strtotime($row['tanggal_kembali']) > strtotime($row['tanggal_jatuh_tempo']));
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $row['id_peminjaman'] ?></td>
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
                                            <span class="badge bg-danger">Lewat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['tanggal_kembali']): ?>
                                            <?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?>
                                            <?php if($telat_kembali): ?>
                                                <span class="badge bg-warning">Telat</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['status'] == 'dipinjam'): ?>
                                            <span class="badge bg-warning">Dipinjam</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Dikembalikan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['status'] == 'dipinjam' && $terlambat): ?>
                                            <span class="text-danger">
                                                <i class="bi bi-clock-history"></i> Terlambat 
                                                <?php 
                                                    $hari = floor((time() - strtotime($row['tanggal_jatuh_tempo'])) / (60 * 60 * 24));
                                                    echo $hari . " hari";
                                                ?>
                                            </span>
                                        <?php elseif($row['status'] == 'dikembalikan' && $telat_kembali): ?>
                                            <span class="text-warning">
                                                <i class="bi bi-exclamation-circle"></i> Terlambat
                                            </span>
                                        <?php else: ?>
                                            <span class="text-success">
                                                <i class="bi bi-check-circle"></i> Tepat waktu
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= 8 + (in_array('pengarang', $buku_columns) ? 1 : 0) + (in_array('nomor_anggota', $anggota_columns) ? 1 : 0) ?>" 
                                    class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i><br>
                                    Belum ada data peminjaman
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .sidebar, .card-header button, .btn, .no-print {
        display: none !important;
    }
    .col-md-10 {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .main-content {
        padding: 0 !important;
        margin: 0 !important;
    }
    .container {
        max-width: 100% !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .table {
        border: 1px solid #ddd !important;
    }
    body {
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .stat-card {
        border: 1px solid #ddd !important;
        break-inside: avoid;
    }
</style>

<script>
function exportToExcel() {
    var table = document.getElementById('laporanTable');
    var html = table.outerHTML;
    var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    var link = document.createElement('a');
    link.download = 'laporan_perpustakaan.xls';
    link.href = url;
    link.click();
}

// Filter laporan (tambahan)
document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan filter tanggal jika diperlukan
    var filterRow = document.createElement('tr');
    filterRow.classList.add('filter-row');
    var headers = document.querySelectorAll('#laporanTable thead th');
    // Skip implementasi filter jika terlalu kompleks
});
</script>