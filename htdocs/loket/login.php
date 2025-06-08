<?php
session_start();
require_once '../api/config.php';

// Function untuk sanitasi input jika belum ada di config.php
if (!function_exists('sanitize')) {
    function sanitize($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

// Cek jika sudah login
if (isset($_SESSION['loket_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $loket_id = isset($_POST['loket_id']) ? intval($_POST['loket_id']) : 0;

    if (empty($username) || empty($password) || $loket_id <= 0) {
        $error = 'Semua field harus diisi';
    } else {
        try {
            // Koneksi ke database
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Cek username & password (untuk transisi, kami mendukung cara hardcoded dan database)
            // Cara 1: Cek database jika user sudah ada di database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'aktif'");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $authenticated = false;

            // Cek apakah user ditemukan di database
            if ($user && ($password === $user['password'] || (function_exists('password_verify') && password_verify($password, $user['password'])))) {
                $authenticated = true;
                $user_id = $user['id'];
                $nama_lengkap = $user['nama_lengkap'];
            }
            // Cara 2: Fallback ke hardcoded admin (hanya untuk development/testing)
            elseif ($username === 'admin' && $password === 'admin123') {
                $authenticated = true;
                $user_id = 0;
                $nama_lengkap = 'Administrator';
            }

            if ($authenticated) {
                // Cek apakah loket valid
                $stmt = $pdo->prepare("SELECT id, nama FROM loket WHERE id = ? AND status = 'aktif'");
                $stmt->execute([$loket_id]);
                $loket = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($loket) {
                    // Set session
                    $_SESSION['loket_id'] = $loket['id'];
                    $_SESSION['loket_nama'] = $loket['nama'];
                    $_SESSION['username'] = $username;
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['nama_lengkap'] = $nama_lengkap;

                    // Redirect ke dashboard
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Loket tidak valid atau tidak aktif';
                }
            } else {
                $error = 'Username atau password salah';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Loket - Sistem Antrian Kantor Pos Rantauprapat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #182B5A;     /* Warna biru tua utama */
            --secondary-color: #FFFFFF;    /* Warna putih */
            --accent-color: #FF5733;       /* Warna oranye untuk aksen */
            --bg-color: #F5F5F5;           /* Warna latar belakang abu-abu terang */
            --highlight-color: rgba(255, 87, 51, 0.3); /* Warna highlight untuk animasi */
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
        }

        .logo {
            height: 60px;
            margin-right: 15px;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .header-info {
            display: flex;
            flex-direction: column;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .location-info {
            font-size: 14px;
            color: rgb(252, 126, 0);
        }

        .clock {
            font-size: 28px;
            font-weight: bold;
            color: #FF5733; /* Warna oranye untuk jam */
            background-color: rgba(0, 0, 0, 0.2);
            padding: 10px 20px;
            border-radius: 10px;
        }

        /* Login Form Styles */
        .content-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 450px;
        }

        .login-title {
            color: var(--primary-color);
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .login-icon {
            margin-right: 10px;
            color: var(--accent-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control,
        .form-select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ced4da;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(24, 43, 90, 0.25);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-login:hover {
            background-color: #0D1A3A;
            border-color: #0D1A3A;
        }

        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
            padding: 8px;
            background-color: #fff5f5;
            border-radius: 5px;
            border: 1px solid #dc3545;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 18px;
            }

            .clock {
                font-size: 20px;
                padding: 8px 12px;
            }

            .login-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo-container">
                    <img src="../assets/sounds/logo.jpg" alt="Logo Pos Indonesia" class="logo">
                    <div class="header-info">
                        <h1 class="m-0">SISTEM ANTRIAN LOKET</h1>
                        <div class="location-info">Kantor Pos Rantauprapat</div>
                    </div>
                </div>
                <div class="clock" id="clock">00:00</div>
            </div>
        </div>
    </div>

    <!-- Login Content -->
    <div class="content-container">
        <div class="login-container">
            <div class="login-title">
                <i class="fas fa-user-lock login-icon"></i> Login Petugas Loket
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="Masukkan username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Masukkan password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="loket_id" class="form-label">Pilih Loket</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-desktop"></i></span>
                        <select class="form-select" id="loket_id" name="loket_id" required>
                            <option value="">-- Pilih Loket --</option>
                            <?php
                            try {
                                $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
                                
                                // Versi 1: Menggunakan data dari database jika tersedia
                                $stmt = $pdo->query("SELECT id, nama FROM loket WHERE status = 'aktif'");
                                $lokets = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                // Definisi icon untuk setiap jenis loket
                                $loket_icons = [
                                    'Loket Umum' => 'fa-users',
                                    'Loket Pensiun' => 'fa-hand-holding-heart',
                                    'Loket CS' => 'fa-headset'
                                ];
                                
                                // Jika data loket dari database tersedia
                                if (count($lokets) > 0) {
                                    foreach ($lokets as $row) {
                                        $icon = isset($loket_icons[$row['nama']]) ? $loket_icons[$row['nama']] : 'fa-desktop';
                                        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nama']) . '</option>';
                                    }
                                } else {
                                    // Versi 2: Fallback - Tampilkan opsi loket hardcoded jika tidak ada data dari database
                                    // Anda mungkin perlu menyesuaikan ID loket sesuai dengan database Anda
                                    echo '<option value="1">Loket Umum</option>';
                                    echo '<option value="2">Loket Pensiun</option>';
                                    echo '<option value="3">Loket CS</option>';
                                }
                            } catch (PDOException $e) {
                                // Jika terjadi error pada database, tampilkan opsi hardcoded
                                echo '<option value="1">Loket Umum</option>';
                                echo '<option value="2">Loket Pensiun</option>';
                                echo '<option value="3">Loket CS</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Fungsi untuk update jam
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            $('#clock').text(timeString);
            setTimeout(updateClock, 1000);
        }

        // Update jam saat halaman dimuat
        $(document).ready(function () {
            updateClock();
            
            // Tambahkan ikon yang sesuai untuk setiap opsi loket
            $('#loket_id').change(function() {
                const selectedText = $(this).find('option:selected').text();
                const iconClass = getLoketIcon(selectedText);
                
                if (iconClass) {
                    $(this).parent().find('.input-group-text i').removeClass().addClass('fas ' + iconClass);
                } else {
                    $(this).parent().find('.input-group-text i').removeClass().addClass('fas fa-desktop');
                }
            });
            
            // Fungsi untuk mendapatkan icon berdasarkan nama loket
            function getLoketIcon(loketName) {
                const iconMapping = {
                    'Loket Umum': 'fa-users',
                    'Loket Pensiun': 'fa-hand-holding-heart',
                    'Loket CS': 'fa-headset'
                };
                
                return iconMapping[loketName] || 'fa-desktop';
            }
        });
    </script>
</body>

</html>