<?php
// check-table.php - File untuk mengecek struktur tabel database
require_once 'api/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Cek Struktur Tabel Database</h2>";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>1. Struktur Tabel 'antrian':</h3>";
    
    // Cek struktur tabel antrian
    $stmt = $pdo->query("DESCRIBE antrian");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        echo "<p style='color: red;'>❌ Tabel 'antrian' tidak ditemukan!</p>";
        echo "<p>Silakan buat tabel dengan query berikut:</p>";
        echo "<pre>";
        echo "CREATE TABLE antrian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_antrian VARCHAR(10) NOT NULL,
    jenis_layanan VARCHAR(1) NOT NULL,
    status ENUM('menunggu', 'dipanggil', 'selesai') DEFAULT 'menunggu',
    loket_id INT NULL,
    waktu_ambil TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
        echo "</pre>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>2. Data dalam Tabel 'antrian':</h3>";
    
    // Cek data yang ada
    $stmt = $pdo->query("SELECT * FROM antrian ORDER BY id DESC LIMIT 10");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($data)) {
        echo "<p>Belum ada data dalam tabel antrian.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach (array_keys($data[0]) as $header) {
            echo "<th>$header</th>";
        }
        echo "</tr>";
        
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>$cell</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>3. Test Query Nomor Terakhir:</h3>";
    
    // Test query untuk setiap jenis layanan
    $layanan = ['A', 'B', 'C'];
    $tanggal = date('Y-m-d');
    
    foreach ($layanan as $jenis) {
        $stmt = $pdo->prepare("
            SELECT nomor_antrian
            FROM antrian 
            WHERE jenis_layanan = ? 
            AND DATE(waktu_ambil) = ?
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute([$jenis, $tanggal]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $nomor_terakhir = substr($result['nomor_antrian'], 1);
            echo "<p>Layanan $jenis: Nomor terakhir = {$result['nomor_antrian']} (angka: $nomor_terakhir)</p>";
        } else {
            echo "<p>Layanan $jenis: Belum ada data hari ini</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>