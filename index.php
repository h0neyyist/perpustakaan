<?php include 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: white;
            border-radius: 10px;
            margin: 5px 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.3);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="text-center py-4">
                    <h4 class="text-white">Perpustakaan</h4>
                    <small class="text-white-50">Digital Library</small>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link <?= (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : '' ?>" href="?page=dashboard">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'buku') ? 'active' : '' ?>" href="?page=buku">
                        <i class="bi bi-book"></i> Manajemen Buku
                    </a>
                    <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'anggota') ? 'active' : '' ?>" href="?page=anggota">
                        <i class="bi bi-people"></i> Manajemen Anggota
                    </a>
                    <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'peminjaman') ? 'active' : '' ?>" href="?page=peminjaman">
                        <i class="bi bi-arrow-right-circle"></i> Transaksi Peminjaman
                    </a>
                    <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'pengembalian') ? 'active' : '' ?>" href="?page=pengembalian">
                        <i class="bi bi-arrow-left-circle"></i> Transaksi Pengembalian
                    </a>
                    <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'laporan') ? 'active' : '' ?>" href="?page=laporan">
                        <i class="bi bi-file-text"></i> Laporan
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
             <!-- Di dalam main-content, sebelum include page -->
<?php if(isset($_GET['status'])): ?>
    <?php if($_GET['status'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> Operasi berhasil dilakukan!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif($_GET['status'] == 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> Terjadi kesalahan: <?= $_GET['msg'] ?? 'Unknown error' ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif($_GET['status'] == 'deleted'): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-trash"></i> Data berhasil dihapus!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>
            <div class="col-md-10 main-content p-4">
                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
                $file = "pages/" . $page . ".php";
                if (file_exists($file)) {
                    include $file;
                } else {
                    include "pages/dashboard.php";
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Alert auto hide -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function() {
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 3000);
    </script>
</body>
</html>
