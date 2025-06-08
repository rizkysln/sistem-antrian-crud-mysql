<?php
// Konfigurasi database
require_once 'config.php';

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

// Ambil data dari request
$loket_id = isset($_POST['loket_id']) ? intval($_POST['loket_id']) : 0;
$service_type = isset($_POST['service_type']) ? strtoupper($_POST['service_type']) : null;

// Validasi input
if ($loket_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid loket ID'
    ]);
    exit;
}

try {
    // Koneksi ke database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mulai transaction untuk menjaga konsistensi data
    $pdo->beginTransaction();
    
    // 1. Cek status loket
    $stmt = $pdo->prepare("SELECT id, nama, antrian_saat_ini, jenis_layanan FROM loket WHERE id = ?");
    $stmt->execute([$loket_id]);
    $loket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$loket) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Loket tidak ditemukan'
        ]);
        exit;
    }
    
    // 2. Cek jika loket sudah memiliki antrian aktif
    if ($loket['antrian_saat_ini']) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Loket sudah memiliki antrian aktif. Selesaikan dulu antrian sebelumnya.'
        ]);
        exit;
    }
    
    // 3. Tentukan jenis layanan yang bisa dipanggil oleh loket ini
    $jenis_layanan_loket = $loket['jenis_layanan'];
    $service_condition = '';
    $params = [];
    
    if ($jenis_layanan_loket !== 'campur') {
        // Loket khusus hanya memanggil antrian dengan jenis layanan tertentu
        $service_condition = 'AND jenis_layanan = ?';
        $params[] = $jenis_layanan_loket;
    } elseif ($service_type && in_array($service_type, ['A', 'B', 'C'])) {
        // Loket campur bisa memilih jenis layanan tertentu
        $service_condition = 'AND jenis_layanan = ?';
        $params[] = $service_type;
    }
    
    // 4. Ambil antrian berikutnya yang sesuai
    $query = "SELECT id, nomor, jenis_layanan FROM antrian 
              WHERE status = 'menunggu' 
              $service_condition
              ORDER BY 
                CASE jenis_layanan 
                  WHEN 'A' THEN 1  -- Prioritas pertama
                  WHEN 'B' THEN 2  -- Reguler berikutnya
                  WHEN 'C' THEN 3  -- Informasi terakhir
                END,
                waktu_ambil ASC 
              LIMIT 1";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $antrian = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$antrian) {
        $pdo->rollBack();
        $message = $jenis_layanan_loket !== 'campur' 
            ? "Tidak ada antrian menunggu untuk layanan ini" 
            : "Tidak ada antrian yang menunggu";
        
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
    
    // 5. Update status antrian
    $stmt = $pdo->prepare("UPDATE antrian SET 
                          status = 'dipanggil', 
                          loket_id = ?, 
                          waktu_panggil = CURRENT_TIMESTAMP 
                          WHERE id = ?");
    $stmt->execute([$loket_id, $antrian['id']]);
    
    // 6. Update loket dengan antrian saat ini
    $stmt = $pdo->prepare("UPDATE loket SET antrian_saat_ini = ? WHERE id = ?");
    $stmt->execute([$antrian['nomor'], $loket_id]);
    
    // 7. Commit transaction
    $pdo->commit();
    
    // 8. Response sukses
    echo json_encode([
        'success' => true,
        'message' => 'Nomor antrian berhasil dipanggil',
        'data' => [
            'nomor' => $antrian['nomor'],
            'jenis_layanan' => $antrian['jenis_layanan'],
            'loket_id' => $loket_id,
            'nama_loket' => $loket['nama'],
            'waktu_panggil' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction jika error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}