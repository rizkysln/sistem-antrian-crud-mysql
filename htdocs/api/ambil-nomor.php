<?php
// 1. Koneksi Database dan Konfigurasi Awal
require_once 'config.php';

// Set zona waktu ke Indonesia
date_default_timezone_set('Asia/Jakarta');

// Set header response sebagai JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

function jsonResponse($success, $message, $data = []) {
    $response = [
        'sukses' => $success,
        'pesan' => $message,
        'data' => $data
    ];
    echo json_encode($response);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Method tidak diizinkan. Gunakan POST.');
    }

    // Ambil jenis layanan dari input POST
    $jenis_layanan = $_POST['jenis_layanan'] ?? 'A';
    
    // Validasi jenis layanan
    if (!in_array($jenis_layanan, ['A', 'B', 'C'])) {
        jsonResponse(false, 'Jenis layanan tidak valid. Pilih A (Umum), B (Pensiun), atau C (CS).');
    }

    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // FIX 1: Set timezone database
    $pdo->exec("SET time_zone = '+7:00'");
    
    $pdo->beginTransaction();

    // Generate nomor antrian
    $tanggal_hari_ini = date('Y-m-d');
    $tanggal_start = $tanggal_hari_ini . ' 00:00:00';
    $tanggal_end = $tanggal_hari_ini . ' 23:59:59';
    
    // FIX 2: Modifikasi query untuk handle timezone
    $query_nomor_terakhir = $pdo->prepare("
        SELECT MAX(CAST(SUBSTRING(nomor, 2) AS UNSIGNED)) 
        FROM antrian 
        WHERE jenis_layanan = ? 
        AND waktu_ambil BETWEEN ? AND ?
    ");
    
    $query_nomor_terakhir->execute([$jenis_layanan, $tanggal_start, $tanggal_end]);
    $nomor_terakhir = (int)$query_nomor_terakhir->fetchColumn();
    
    // FIX 3: Handle case ketika tidak ada data
    if ($nomor_terakhir === 0) {
        // Cek apakah benar-benar tidak ada data
        $query_cek_data = $pdo->prepare("SELECT COUNT(*) FROM antrian WHERE jenis_layanan = ? AND DATE(waktu_ambil) = ?");
        $query_cek_data->execute([$jenis_layanan, $tanggal_hari_ini]);
        $total_data = (int)$query_cek_data->fetchColumn();
        
        error_log("Debug: Jenis=$jenis_layanan, Tanggal=$tanggal_hari_ini, TotalData=$total_data, NomorTerakhir=$nomor_terakhir");
    }
    
    $nomor_berikutnya = $nomor_terakhir + 1;
    $nomor_antrian = $jenis_layanan . str_pad($nomor_berikutnya, 3, '0', STR_PAD_LEFT);

    // Simpan ke database
    $query_simpan = $pdo->prepare("
        INSERT INTO antrian 
        (nomor, jenis_layanan, status, loket_id, waktu_ambil) 
        VALUES (?, ?, 'menunggu', NULL, NOW())
    ");
    $query_simpan->execute([$nomor_antrian, $jenis_layanan]);

    // Hitung estimasi tunggu (dengan query yang sama)
    $query_antrian_aktif = $pdo->prepare("
        SELECT COUNT(*) 
        FROM antrian 
        WHERE status = 'menunggu' 
        AND jenis_layanan = ?
        AND waktu_ambil BETWEEN ? AND ?
    ");
    $query_antrian_aktif->execute([$jenis_layanan, $tanggal_start, $tanggal_end]);
    $jumlah_antrian = (int)$query_antrian_aktif->fetchColumn();
    
    $estimasi_per_antrian = [
        'A' => 3,  // Umum
        'B' => 5,  // Pensiun
        'C' => 2   // CS
    ];
    
    $estimasi_tunggu = $jumlah_antrian * $estimasi_per_antrian[$jenis_layanan];

    $pdo->commit();

    $nama_layanan = match($jenis_layanan) {
        'A' => 'Umum',
        'B' => 'Pensiun',
        'C' => 'CS',
        default => 'Tidak Dikenal'
    };

    jsonResponse(true, 'Nomor antrian berhasil dibuat', [
        'nomor_antrian' => $nomor_antrian,
        'nomor_urut' => str_pad($nomor_berikutnya, 3, '0', STR_PAD_LEFT),
        'jenis_layanan' => $jenis_layanan,
        'nama_layanan' => $nama_layanan,
        'estimasi_tunggu' => $estimasi_tunggu,
        'waktu_ambil' => date('d/m/Y H:i:s'),
        'jumlah_antrian_depan' => $jumlah_antrian,
        'loket_tujuan' => $nama_layanan
    ]);

} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("PDO Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    jsonResponse(false, 'Terjadi kesalahan database. Silakan coba lagi.');
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    jsonResponse(false, $e->getMessage());
}