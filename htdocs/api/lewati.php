<?php
// Konfigurasi database
require_once 'config.php';

// Function untuk sanitasi input
if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

// Set header JSON
header('Content-Type: application/json');

// Verifikasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Ambil parameter dari request
$nomor = isset($_POST['nomor']) ? sanitize($_POST['nomor']) : '';
$loket_id = isset($_POST['loket_id']) ? intval($_POST['loket_id']) : 0;

if (empty($nomor) || $loket_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid parameters'
    ]);
    exit;
}

try {
    // Koneksi ke database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mulai transaction
    $pdo->beginTransaction();
    
    // Update status antrian
    $stmt = $pdo->prepare("UPDATE antrian SET 
                          status = 'dilewati', 
                          waktu_selesai = CURRENT_TIMESTAMP 
                          WHERE nomor = ? AND loket_id = ? AND status = 'dipanggil'");
    $stmt->execute([$nomor, $loket_id]);
    
    if ($stmt->rowCount() == 0) {
        // Tidak ada antrian yang diupdate
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Nomor antrian tidak ditemukan atau bukan di loket ini'
        ]);
        exit;
    }
    
    // Reset nomor antrian di loket
    $stmt = $pdo->prepare("UPDATE loket SET antrian_saat_ini = NULL WHERE id = ?");
    $stmt->execute([$loket_id]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Nomor antrian berhasil dilewati'
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction jika error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}