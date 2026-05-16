<?php
$root_path = dirname(__DIR__);
include $root_path . '/config/database.php';

// Cek struktur tabel buku
$check_buku = mysqli_query($conn, "SHOW COLUMNS FROM buku");
$buku_columns = [];
while($col = mysqli_fetch_assoc($check_buku)) {
    $buku_columns[] = $col['Field'];
}

// Handle search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

if(in_array('judul', $buku_columns) && in_array('pengarang', $buku_columns)) {
    $query = "SELECT * FROM buku WHERE judul LIKE '%$search%' OR pengarang LIKE '%$search%' ORDER BY id_buku DESC";
} else {
    $query = "SELECT * FROM buku ORDER BY id_buku DESC";
}

$result = mysqli_query($conn, $query);

if(!$result) {
    die("Error query: " . mysqli_error($conn));
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-book"></i> Manajemen Buku</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahBuku">
            <i class="bi bi-plus-circle"></i> Tambah Buku
        </button>
    </div>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="buku">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Cari judul atau pengarang..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Buku -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Penerbit</th>
                            <th>Tahun</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $row['id_buku'] ?></td>
                                    <td><?= htmlspecialchars($row['judul']) ?></td>
                                    <td><?= htmlspecialchars($row['pengarang']) ?></td>
                                    <td><?= htmlspecialchars($row['penerbit'] ?? '-') ?></td>
                                    <td><?= $row['tahun_terbit'] ?? '-' ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= $row['stok'] ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" 
                                            onclick="editBuku(<?= $row['id_buku'] ?>, 
                                                '<?= htmlspecialchars($row['judul']) ?>', 
                                                '<?= htmlspecialchars($row['pengarang']) ?>', 
                                                '<?= htmlspecialchars($row['penerbit'] ?? '') ?>', 
                                                '<?= $row['tahun_terbit'] ?? '' ?>', 
                                                <?= $row['stok'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="../proses/hapus_buku.php?id=<?= $row['id_buku'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin hapus buku ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted">Tidak ada data buku</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Buku -->
<div class="modal fade" id="modalTambahBuku" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../proses/tambah_buku.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah Buku Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Buku <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pengarang <span class="text-danger">*</span></label>
                        <input type="text" name="pengarang" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penerbit</label>
                        <input type="text" name="penerbit" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" class="form-control" min="1900" max="2026">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok <span class="text-danger">*</span></label>
                        <input type="number" name="stok" class="form-control" value="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Buku -->
<div class="modal fade" id="modalEditBuku" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../proses/edit_buku.php" method="POST">
                <input type="hidden" name="id_buku" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Buku</label>
                        <input type="text" name="judul" id="edit_judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pengarang</label>
                        <input type="text" name="pengarang" id="edit_pengarang" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penerbit</label>
                        <input type="text" name="penerbit" id="edit_penerbit" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" id="edit_tahun" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" name="stok" id="edit_stok" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editBuku(id, judul, pengarang, penerbit, tahun, stok) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_judul').value = judul;
    document.getElementById('edit_pengarang').value = pengarang;
    document.getElementById('edit_penerbit').value = penerbit;
    document.getElementById('edit_tahun').value = tahun;
    document.getElementById('edit_stok').value = stok;
    new bootstrap.Modal(document.getElementById('modalEditBuku')).show();
}

// Auto close alert after 3 seconds
setTimeout(function() {
    let alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        let bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 3000);
</script>