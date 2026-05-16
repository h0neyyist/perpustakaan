<?php 
include 'config/database.php';

// Cek struktur tabel terlebih dahulu
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM anggota");
$columns = [];
while($col = mysqli_fetch_assoc($check_column)) {
    $columns[] = $col['Field'];
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Buat query berdasarkan kolom yang tersedia
if(in_array('nomor_anggota', $columns)) {
    $query = "SELECT * FROM anggota WHERE nama LIKE '%$search%' OR nomor_anggota LIKE '%$search%' ORDER BY id_anggota DESC";
} else {
    // Jika tidak ada kolom nomor_anggota, coba kolom lain
    $query = "SELECT * FROM anggota WHERE nama LIKE '%$search%' ORDER BY id_anggota DESC";
}

$result = mysqli_query($conn, $query);

// Cek error
if(!$result) {
    die("Error query: " . mysqli_error($conn));
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Anggota</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota">
            <i class="bi bi-plus-circle"></i> Tambah Anggota
        </button>
    </div>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="anggota">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama anggota..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Anggota -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <?php if(in_array('nomor_anggota', $columns)): ?>
                                <th>No. Anggota</th>
                            <?php endif; ?>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>No. Telepon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $row['id_anggota'] ?></td>
                                    <?php if(in_array('nomor_anggota', $columns)): ?>
                                        <td><?= htmlspecialchars($row['nomor_anggota']) ?></td>
                                    <?php endif; ?>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['alamat'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['no_telepon'] ?? '-') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" 
                                            onclick="editAnggota(
                                                <?= $row['id_anggota'] ?>, 
                                                '<?= htmlspecialchars($row['nomor_anggota'] ?? '') ?>', 
                                                '<?= htmlspecialchars($row['nama']) ?>', 
                                                '<?= htmlspecialchars($row['alamat'] ?? '') ?>', 
                                                '<?= htmlspecialchars($row['no_telepon'] ?? '') ?>'
                                            )">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="proses/hapus_anggota.php?id=<?= $row['id_anggota'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin hapus anggota ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="<?= in_array('nomor_anggota', $columns) ? '6' : '5' ?>" class="text-center">
                                Tidak ada data anggota
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Anggota -->
<div class="modal fade" id="modalTambahAnggota" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="proses/tambah_anggota.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Anggota Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if(in_array('nomor_anggota', $columns)): ?>
                    <div class="mb-3">
                        <label>Nomor Anggota</label>
                        <input type="text" name="nomor_anggota" class="form-control" required>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>No. Telepon</label>
                        <input type="text" name="no_telepon" class="form-control">
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

<!-- Modal Edit Anggota -->
<div class="modal fade" id="modalEditAnggota" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="proses/edit_anggota.php" method="POST">
                <input type="hidden" name="id_anggota" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Anggota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if(in_array('nomor_anggota', $columns)): ?>
                    <div class="mb-3">
                        <label>Nomor Anggota</label>
                        <input type="text" name="nomor_anggota" id="edit_nomor" class="form-control" required>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" id="edit_alamat" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>No. Telepon</label>
                        <input type="text" name="no_telepon" id="edit_telepon" class="form-control">
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
function editAnggota(id, nomor, nama, alamat, telepon) {
    document.getElementById('edit_id').value = id;
    <?php if(in_array('nomor_anggota', $columns)): ?>
    document.getElementById('edit_nomor').value = nomor;
    <?php endif; ?>
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_alamat').value = alamat;
    document.getElementById('edit_telepon').value = telepon;
    new bootstrap.Modal(document.getElementById('modalEditAnggota')).show();
}
</script>