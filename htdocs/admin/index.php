<?php
session_start();
require_once '../api/config.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Simple authentication
if (!isset($_SESSION['admin'])) {
    // Admin login sederhana - Dalam sistem nyata, gunakan autentikasi yang lebih aman
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
            $_SESSION['admin'] = true;
        } else {
            $error = 'Username atau password salah';
        }
    }
    
    // Tampilkan form login jika belum login
    if (!isset($_SESSION['admin'])) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
            <link rel="stylesheet" href="style.css">
        </head>
        <body class="login-body">
            <div class="login-container">
                <div class="login-title">Admin Login</div>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login">Login</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Helper function untuk sanitasi input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Handle actions
$message = '';
if (isset($_POST['action'])) {
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        switch ($_POST['action']) {
            case 'reset_antrian':
                // Reset antrian
                $pdo->exec("TRUNCATE TABLE antrian");
                $pdo->exec("UPDATE loket SET antrian_saat_ini = NULL WHERE antrian_saat_ini IS NOT NULL");
                $message = 'Antrian berhasil direset';
                break;
                
            case 'update_loket':
                $loket_id = isset($_POST['loket_id']) ? intval($_POST['loket_id']) : 0;
                $status = isset($_POST['status']) ? $_POST['status'] : '';
                
                if ($loket_id > 0 && ($status === 'aktif' || $status === 'nonaktif')) {
                    $stmt = $pdo->prepare("UPDATE loket SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $loket_id]);
                    $message = 'Status loket berhasil diupdate';
                }
                break;
                
            case 'update_config':
                $awalan = isset($_POST['awalan_antrian']) ? sanitize($_POST['awalan_antrian']) : 'A';
                $digit = isset($_POST['digit_antrian']) ? intval($_POST['digit_antrian']) : 3;
                $reset = isset($_POST['reset_harian']) ? 'true' : 'false';
                
                // Check if konfigurasi table exists and has data
                $checkConfig = $pdo->query("SHOW TABLES LIKE 'konfigurasi'")->rowCount();
                if ($checkConfig > 0) {
                    $stmt = $pdo->prepare("INSERT INTO konfigurasi (nama, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
                    $stmt->execute(['awalan_antrian', $awalan]);
                    $stmt->execute(['digit_antrian', $digit]);
                    $stmt->execute(['reset_harian', $reset]);
                } else {
                    // Create konfigurasi table if it doesn't exist
                    $pdo->exec("CREATE TABLE IF NOT EXISTS konfigurasi (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        nama VARCHAR(50) UNIQUE,
                        nilai TEXT
                    )");
                    $stmt = $pdo->prepare("INSERT INTO konfigurasi (nama, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
                    $stmt->execute(['awalan_antrian', $awalan]);
                    $stmt->execute(['digit_antrian', $digit]);
                    $stmt->execute(['reset_harian', $reset]);
                }
                
                $message = 'Konfigurasi berhasil diupdate';
                break;
        }
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
    }
}

// Ambil data loket
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if loket table exists, if not create default lokets
    $checkLoket = $pdo->query("SHOW TABLES LIKE 'loket'")->rowCount();
    if ($checkLoket == 0) {
        // Create loket table
        $pdo->exec("CREATE TABLE IF NOT EXISTS loket (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(50) NOT NULL,
            jenis_layanan CHAR(1) NOT NULL,
            status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
            antrian_saat_ini VARCHAR(10) NULL
        )");
        
        // Insert default lokets
        $pdo->exec("INSERT INTO loket (nama, jenis_layanan, status) VALUES 
                   ('Loket A1', 'A', 'aktif'),
                   ('Loket A2', 'A', 'aktif'),
                   ('Loket B1', 'B', 'aktif'),
                   ('Loket C1', 'C', 'aktif')");
    }
    
    $loket_data = $pdo->query("SELECT * FROM loket ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil konfigurasi
    $config = [
        'awalan_antrian' => 'A',
        'digit_antrian' => '3',
        'reset_harian' => 'true'
    ];
    
    $checkConfig = $pdo->query("SHOW TABLES LIKE 'konfigurasi'")->rowCount();
    if ($checkConfig > 0) {
        $stmt = $pdo->query("SELECT nama, nilai FROM konfigurasi");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $config[$row['nama']] = $row['nilai'];
        }
    }
    
    // Ambil statistik - sesuaikan dengan struktur yang ada
    $today = date('Y-m-d');
    
    // Check if antrian table exists
    $checkAntrian = $pdo->query("SHOW TABLES LIKE 'antrian'")->rowCount();
    if ($checkAntrian > 0) {
        $stmt = $pdo->prepare("SELECT 
                                COUNT(*) as total,
                                SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
                                SUM(CASE WHEN status = 'dipanggil' THEN 1 ELSE 0 END) as dipanggil,
                                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                                SUM(CASE WHEN status = 'terlewati' THEN 1 ELSE 0 END) as terlewati
                              FROM antrian
                              WHERE DATE(waktu_ambil) = ?");
        $stmt->execute([$today]);
        $statistik = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Jika masih null, set default values
        if (!$statistik) {
            $statistik = [
                'total' => 0,
                'menunggu' => 0,
                'dipanggil' => 0,
                'selesai' => 0,
                'terlewati' => 0
            ];
        }
        
        // Ambil data antrian aktif
        $stmt = $pdo->prepare("SELECT a.*, l.nama as nama_loket 
                              FROM antrian a 
                              LEFT JOIN loket l ON a.loket_id = l.id 
                              WHERE DATE(a.waktu_ambil) = ? 
                              AND a.status IN ('menunggu', 'dipanggil') 
                              ORDER BY a.waktu_ambil ASC");
        $stmt->execute([$today]);
        $antrian_aktif = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Create antrian table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS antrian (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nomor VARCHAR(10) NOT NULL,
            jenis_layanan CHAR(1) NOT NULL,
            status ENUM('menunggu', 'dipanggil', 'selesai', 'terlewati') DEFAULT 'menunggu',
            loket_id INT NULL,
            waktu_ambil DATETIME NOT NULL,
            waktu_panggil DATETIME NULL,
            waktu_selesai DATETIME NULL,
            FOREIGN KEY (loket_id) REFERENCES loket(id)
        )");
        
        $statistik = [
            'total' => 0,
            'menunggu' => 0,
            'dipanggil' => 0,
            'selesai' => 0,
            'terlewati' => 0
        ];
        $antrian_aktif = [];
    }
    
} catch (PDOException $e) {
    $message = 'Error: ' . $e->getMessage();
    $loket_data = [];
    $config = [
        'awalan_antrian' => 'A',
        'digit_antrian' => '3',
        'reset_harian' => 'true'
    ];
    $statistik = [
        'total' => 0,
        'menunggu' => 0,
        'dipanggil' => 0,
        'selesai' => 0,
        'terlewati' => 0
    ];
    $antrian_aktif = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sistem Antrian</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            color: #333;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            font-weight: bold;
        }
        .stat-card {
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            background: #f8f9fa;
            margin-bottom: 1rem;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
        }
        .stat-title {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        .logout-link {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .logout-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .card {
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        footer {
            margin-top: 3rem;
            padding: 1rem 0;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-clipboard-list me-2"></i>
                Sistem Antrian - Admin Panel
            </a>
            <div class="ms-auto">
                <a href="?logout=1" class="logout-link" onclick="return confirm('Yakin ingin logout?');">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Statistik -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar me-2"></i> Statistik Hari Ini (<?= date('d/m/Y') ?>)
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="stat-card">
                                    <div class="stat-number"><?= htmlspecialchars($statistik['total'] ?? 0) ?></div>
                                    <div class="stat-title">Total Antrian</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="stat-card">
                                    <div class="stat-number"><?= htmlspecialchars($statistik['menunggu'] ?? 0) ?></div>
                                    <div class="stat-title">Menunggu</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="stat-card">
                                    <div class="stat-number"><?= htmlspecialchars($statistik['dipanggil'] ?? 0) ?></div>
                                    <div class="stat-title">Dipanggil</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="stat-card">
                                    <div class="stat-number"><?= htmlspecialchars($statistik['selesai'] ?? 0) ?></div>
                                    <div class="stat-title">Selesai</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="stat-card">
                                    <div class="stat-number"><?= htmlspecialchars($statistik['terlewati'] ?? 0) ?></div>
                                    <div class="stat-title">Terlewati</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manajemen Loket -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-desktop me-2"></i> Manajemen Loket
                    </div>
                    <div class="card-body">
                        <?php if (empty($loket_data)): ?>
                            <div class="text-center py-3">
                                <p class="text-muted">Belum ada data loket. Sistem akan membuat loket default.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Loket</th>
                                            <th>Layanan</th>
                                            <th>Status</th>
                                            <th>Antrian Aktif</th>
                                            <th>Tindakan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($loket_data as $loket): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($loket['nama']) ?></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php 
                                                    $layanan = match($loket['jenis_layanan']) {
                                                        'A' => 'Umum',
                                                        'B' => 'Pensiun', 
                                                        'C' => 'CS',
                                                        default => 'Unknown'
                                                    };
                                                    echo $layanan;
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $loket['status'] === 'aktif' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst(htmlspecialchars($loket['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= $loket['antrian_saat_ini'] ? htmlspecialchars($loket['antrian_saat_ini']) : '-' ?></td>
                                            <td>
                                                <form method="post" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_loket">
                                                    <input type="hidden" name="loket_id" value="<?= htmlspecialchars($loket['id']) ?>">
                                                    <input type="hidden" name="status" value="<?= $loket['status'] === 'aktif' ? 'nonaktif' : 'aktif' ?>">
                                                    <button type="submit" class="btn btn-sm btn-<?= $loket['status'] === 'aktif' ? 'warning' : 'success' ?>">
                                                        <?= $loket['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Konfigurasi Sistem -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-cogs me-2"></i> Konfigurasi Sistem
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="update_config">
                            
                            <div class="mb-3">
                                <label for="awalan_antrian" class="form-label">Awalan Antrian</label>
                                <input type="text" class="form-control" id="awalan_antrian" name="awalan_antrian" value="<?= htmlspecialchars($config['awalan_antrian'] ?? 'A') ?>" maxlength="3" required>
                                <div class="form-text">Huruf yang akan digunakan sebagai awalan nomor antrian (mis. A001)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="digit_antrian" class="form-label">Digit Nomor Antrian</label>
                                <select class="form-select" id="digit_antrian" name="digit_antrian">
                                    <option value="2" <?= ($config['digit_antrian'] ?? 3) == 2 ? 'selected' : '' ?>>2 digit (01-99)</option>
                                    <option value="3" <?= ($config['digit_antrian'] ?? 3) == 3 ? 'selected' : '' ?>>3 digit (001-999)</option>
                                    <option value="4" <?= ($config['digit_antrian'] ?? 3) == 4 ? 'selected' : '' ?>>4 digit (0001-9999)</option>
                                </select>
                                <div class="form-text">Jumlah digit untuk nomor antrian (mis. A001 = 3 digit)</div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="reset_harian" name="reset_harian" <?= ($config['reset_harian'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="reset_harian">Reset antrian setiap hari</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Konfigurasi
                            </button>
                        </form>
                        
                        <hr>
                        
                        <form method="post" action="" onsubmit="return confirm('Yakin ingin mereset semua antrian? Tindakan ini tidak dapat dibatalkan!');">
                            <input type="hidden" name="action" value="reset_antrian">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Reset Semua Antrian
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Antrian Aktif -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list-ol me-2"></i> Antrian Aktif
                    </div>
                    <div class="card-body">
                        <?php if (empty($antrian_aktif)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada antrian aktif saat ini.</p>
                                <small class="text-muted">Antrian yang berstatus 'menunggu' atau 'dipanggil' akan tampil di sini.</small>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nomor Antrian</th>
                                            <th>Layanan</th>
                                            <th>Status</th>
                                            <th>Waktu Ambil</th>
                                            <th>Waktu Panggil</th>
                                            <th>Loket</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($antrian_aktif as $antrian): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($antrian['nomor']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php 
                                                    $layanan = match($antrian['jenis_layanan']) {
                                                        'A' => 'Umum',
                                                        'B' => 'Pensiun', 
                                                        'C' => 'CS',
                                                        default => 'Unknown'
                                                    };
                                                    echo $layanan;
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $antrian['status'] === 'menunggu' ? 'warning' : 'info' ?>">
                                                    <?= ucfirst(htmlspecialchars($antrian['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(date('H:i:s', strtotime($antrian['waktu_ambil']))) ?></td>
                                            <td><?= $antrian['waktu_panggil'] ? htmlspecialchars(date('H:i:s', strtotime($antrian['waktu_panggil']))) : '-' ?></td>
                                            <td><?= $antrian['nama_loket'] ? htmlspecialchars($antrian['nama_loket']) : '-' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center text-muted">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> Sistem Manajemen Antrian - Dibuat dengan ❤️</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto hide alert after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            
            // Auto refresh page setiap 30 detik untuk update data real-time
            setTimeout(function() {
                location.reload();
            }, 30000);
        });
    </script>
</body>
</html>