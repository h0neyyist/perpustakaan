<?php
include 'config/database.php';
// Hitung total buku
$query_buku = "SELECT COUNT(*) as total FROM buku";
$result_buku = mysqli_query($conn, $query_buku);
$total_buku = mysqli_fetch_assoc($result_buku)['total'];
// Hitung total anggota
$query_anggota = "SELECT COUNT(*) as total FROM anggota";
$result_anggota = mysqli_query($conn, $query_anggota);
$total_anggota = mysqli_fetch_assoc($result_anggota)['total'];
// Hitung peminjaman aktif
$query_pinjam = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'";
$result_pinjam = mysqli_query($conn, $query_pinjam);
$total_pinjam = mysqli_fetch_assoc($result_pinjam)['total'];
// Hitung peminjaman yang sudah dikembalikan
$query_kembali = "SELECT COUNT(*) as total FROM peminjaman WHERE status =
'dikembalikan'";
$result_kembali = mysqli_query($conn, $query_kembali);
$total_kembali = mysqli_fetch_assoc($result_kembali)['total'];
?>
<div class="container">
 <h2 class="mb-4">📊 Dashboard Perpustakaan</h2>

 <div class="row mb-4">
 <div class="col-md-3 mb-3">
 <div class="card text-white bg-primary">
 <div class="card-body">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <h5 class="card-title">Total Buku</h5>
 <h2><?= $total_buku ?></h2>
 </div>
<i class="bi bi-book" style="font-size: 3rem;"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-md-3 mb-3">
 <div class="card text-white bg-success">
    <div class="card-body">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <h5 class="card-title">Total Anggota</h5>
 <h2><?= $total_anggota ?></h2>
 </div>
<i class="bi bi-people" style="font-size: 3rem;"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-md-3 mb-3">
 <div class="card text-white bg-warning">
 <div class="card-body">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <h5 class="card-title">Sedang Dipinjam</h5>
 <h2><?= $total_pinjam ?></h2>
 </div>
<i class="bi bi-arrow-right-circle" style="font-size:
3rem;"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-md-3 mb-3">
 <div class="card text-white bg-info">
 <div class="card-body">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <h5 class="card-title">Sudah Dikembalikan</h5>
 <h2><?= $total_kembali ?></h2>
 </div>
<i class="bi bi-check-circle" style="font-size: 3rem;"></i>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Peminjaman Terbaru -->
 <div class="card">
 <div class="card-header">
 <h5 class="mb-0">📖 Peminjaman Terbaru</h5>
 </div>
 <div class="card-body">
 <div class="table-responsive">
 <table class="table table-hover">
 <thead>
 <tr>
 <th>No</th>
 <th>Judul Buku</th>
 <th>Nama Anggota</th>
 <th>Tanggal Pinjam</th>
 <th>Jatuh Tempo</th>
 <th>Status</th>
 </tr>

 </thead>
<tbody>
 <?php
$query = "SELECT p.*, b.judul, a.nama
 FROM peminjaman p
JOIN buku b ON p.id_buku = b.id_buku
JOIN anggota a ON p.id_anggota = a.id_anggota
ORDER BY p.tanggal_pinjam DESC LIMIT 5";
 $result = mysqli_query($conn, $query);
 $no = 1;
 while($row = mysqli_fetch_assoc($result)):
 ?>
<tr>
 <td><?= $no++ ?></td>
 <td><?= $row['judul'] ?></td>
 <td><?= $row['nama'] ?></td>
 <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam']))
?></td>
 <td><?= date('d/m/Y',
strtotime($row['tanggal_jatuh_tempo'])) ?></td>
 <td>
 <?php if($row['status'] == 'dipinjam'): ?>
 <span class="badge bg-warning">Dipinjam</span>
 <?php else: ?>
 <span class="badge bg-success">Dikembalikan</span>
 <?php endif; ?>
</td>
 </tr>
<?php endwhile; ?>
 </tbody>
 </table>
 </div>
 </div>
 </div>
</div>