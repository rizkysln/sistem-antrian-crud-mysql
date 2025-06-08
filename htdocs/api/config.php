<?php
/*
 * FILE: config.php
 * FUNGSI: Konfigurasi koneksi database dan pengaturan global
 */

// Konfigurasi database
$db_host = 'sql110.infinityfree.com';     // Hostname database
$db_name = 'if0_37111530_db_antrian';    // Nama database
$db_user = 'if0_37111530';          // Username database (biasanya 'root' untuk XAMPP)
$db_pass = 'Gunsel2024';              // Password database (biasanya kosong untuk XAMPP)

// Pengaturan sistem
$nama_aplikasi = 'Sistem Antrian';
$versi_aplikasi = '1.0';

// Pengaturan kapasitas dan batas antrian
$max_antrian_per_hari = 900;  // Jumlah maksimum antrian per hari
$jam_operasional_mulai = '08:00';
$jam_operasional_selesai = '16:00';

// Pengaturan debug (set ke false untuk production)
$debug_mode = true;

// Fungsi helper untuk debugging
function debug($data) {
    global $debug_mode;
    if ($debug_mode) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}
// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Database connection test
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}