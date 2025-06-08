<?php
// Konfigurasi database
require_once 'config.php';

// Set header JSON
header('Content-Type: application/json');

try {
    // Koneksi ke database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Data untuk tampilan display
    $loket_data = [];
    $stmt = $pdo->query("SELECT id, nama, antrian_saat_ini FROM loket WHERE status = 'aktif'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $loket_data[] = $row;
    }

    // Antrian yang sedang menunggu
    $stmt = $pdo->query("SELECT nomor FROM antrian WHERE status = 'menunggu' ORDER BY id ASC LIMIT 10");
    $waiting = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Panggilan terakhir
    $stmt = $pdo->query("SELECT a.nomor, l.nama as loket_nama
                         FROM antrian a
                         JOIN loket l ON a.loket_id = l.id
                         WHERE a.status = 'dipanggil'
                         ORDER BY a.waktu_panggil DESC
                         LIMIT 5");
    $recent_calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistik hari ini
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT 
                            COUNT(*) as total,
                            COALESCE(SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END), 0) as menunggu,
                            COALESCE(SUM(CASE WHEN status = 'dipanggil' THEN 1 ELSE 0 END), 0) as dipanggil,
                            COALESCE(SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END), 0) as selesai,
                            COALESCE(SUM(CASE WHEN status = 'dilewati' THEN 1 ELSE 0 END), 0) as dilewati
                           FROM antrian
                           WHERE DATE(waktu_ambil) = ?");
    $stmt->execute([$today]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Pastikan statistik tidak null
    if (!$stats) {
        $stats = [
            'total' => 0,
            'menunggu' => 0,
            'dipanggil' => 0,
            'selesai' => 0,
            'dilewati' => 0
        ];
    }

    // Return data
    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'loket' => $loket_data,
        'antrian_menunggu' => $waiting,
        'panggilan_terakhir' => $recent_calls,
        'statistik' => $stats  // The statistics data is here
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'statistik' => [
            'total' => 0,
            'menunggu' => 0,
            'dipanggil' => 0,
            'selesai' => 0,
            'dilewati' => 0
        ]
    ]);
}