<?php
// Start the session - this line is crucial!
session_start();

// Simple authentication
if (!isset($_SESSION['loket_id'])) {
    header('Location: login.php');
    exit;
}
$loket_id = $_SESSION['loket_id'];
$loket_nama = $_SESSION['loket_nama'];

// Icons for different counters
$loket_icons = [
    'Loket Umum' => 'fa-users',
    'Loket Pensiun' => 'fa-hand-holding-heart',
    'Loket CS' => 'fa-headset'
];

// Get the appropriate icon based on loket name
$loket_icon = isset($loket_icons[$loket_nama]) ? $loket_icons[$loket_nama] : 'fa-desktop';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard <?= htmlspecialchars($loket_nama) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #182B5A;
            /* Warna biru tua utama */
            --secondary-color: #FFFFFF;
            /* Warna putih */
            --accent-color: #FF5733;
            /* Warna oranye untuk aksen */
            --bg-color: #F5F5F5;
            /* Warna latar belakang abu-abu terang */
            --highlight-color: rgba(255, 87, 51, 0.3);
            /* Warna highlight untuk animasi */
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
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
            color: #FF5733;
            /* Warna oranye untuk lokasi */
        }

        .clock {
            font-size: 28px;
            font-weight: bold;
            color: #FF5733;
            /* Warna oranye untuk jam */
            background-color: rgba(0, 0, 0, 0.2);
            padding: 10px 20px;
            border-radius: 10px;
            margin-right: 15px;
        }

        .logout-btn {
            background-color: #FF5733;
            color: white;
            border: none;
        }

        .logout-btn:hover {
            background-color: #E04526;
            color: white;
        }

        .panel {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .panel-title {
            color: var(--primary-color);
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .panel-icon {
            margin-right: 10px;
            color: var(--accent-color);
        }

        .current-number {
            font-size: 80px;
            font-weight: bold;
            text-align: center;
            color: var(--primary-color);
            margin: 20px 0;
        }

        .btn-control {
            margin: 0 5px;
            padding: 10px 20px;
            font-weight: bold;
        }

        .btn-panggil {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-panggil:hover {
            background-color: #0D1A3A;
            color: white;
        }

        .btn-lewati {
            background-color: #FF5733;
            color: white;
            border: none;
        }

        .btn-lewati:hover {
            background-color: #E04526;
            color: white;
        }

        .btn-selesai {
            background-color: #28a745;
            color: white;
            border: none;
        }

        .btn-selesai:hover {
            background-color: #218838;
            color: white;
        }

        .waiting-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .waiting-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .waiting-item:last-child {
            border-bottom: none;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
        }

        .stat-card {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }

        .stat-label {
            font-size: 14px;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .stats-container {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .header h1 {
                font-size: 18px;
            }

            .clock {
                font-size: 20px;
                padding: 8px 12px;
            }

            .current-number {
                font-size: 60px;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .btn-control {
                width: 100%;
                margin: 5px 0;
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
                <div class="d-flex align-items-center">
                    <div class="clock" id="clock">00:00</div>
                    <a href="logout.php" class="btn logout-btn">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="panel">
                    <h3 class="panel-title">
                        <i class="fas <?= $loket_icon ?> panel-icon"></i> <?= htmlspecialchars($loket_nama) ?>
                    </h3>
                    <div class="current-number" id="current-number">-</div>
                    <div class="d-flex flex-wrap justify-content-center">
                        <button class="btn btn-control btn-panggil" id="btn-panggil">
                            <i class="fas fa-bullhorn me-2"></i>Panggil
                        </button>
                        <button class="btn btn-control btn-lewati" id="btn-lewati">
                            <i class="fas fa-forward me-2"></i>Lewati
                        </button>
                        <button class="btn btn-control btn-selesai" id="btn-selesai">
                            <i class="fas fa-check me-2"></i>Selesai
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel">
                    <h3 class="panel-title">
                        <i class="fas fa-clock panel-icon"></i> Antrian Menunggu
                    </h3>
                    <div class="waiting-list" id="waiting-list">
                        <!-- Akan diisi oleh JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="panel">
                    <h3 class="panel-title">
                        <i class="fas fa-chart-line panel-icon"></i> Statistik Hari Ini
                    </h3>
                    <div class="stats-container" id="statistics">
                        <!-- Akan diisi oleh JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        const loketId = <?= $loket_id ?>;
        let currentNumber = null;

        // Fungsi untuk update jam
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            document.getElementById('clock').textContent = timeString;
        }

        // Update jam setiap detik
        setInterval(updateClock, 1000);
        updateClock(); // Update pertama kali

        // Polling untuk update data
        function pollData() {
            $.ajax({
                url: '../api/status.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        updateLoketDisplay(data);
                        updateWaitingList(data.antrian_menunggu);
                        updateStatistics(data.statistik); // Pass the statistics data directly
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching data:", error);
                },
                complete: function () {
                    // Poll every 3 seconds
                    setTimeout(pollData, 3000);
                }
            });
        }

        // Update tampilan loket
        function updateLoketDisplay(data) {
            const loketInfo = data.loket.find(l => l.id == loketId);
            if (loketInfo && loketInfo.antrian_saat_ini) {
                $('#current-number').text(loketInfo.antrian_saat_ini);
                currentNumber = loketInfo.antrian_saat_ini;
                $('#btn-panggil').prop('disabled', true);
                $('#btn-lewati, #btn-selesai').prop('disabled', false);
            } else {
                $('#current-number').text('-');
                currentNumber = null;
                $('#btn-panggil').prop('disabled', false);
                $('#btn-lewati, #btn-selesai').prop('disabled', true);
            }
        }

        // Update daftar yang menunggu
        function updateWaitingList(waitingList) {
            const $list = $('#waiting-list');
            $list.empty();

            if (waitingList.length === 0) {
                $list.append('<div class="waiting-item text-center">Tidak ada antrian</div>');
                return;
            }

            waitingList.forEach((nomor, index) => {
                $list.append(`
                    <div class="waiting-item">
                        <span><i class="fas fa-bookmark me-2" style="color: var(--accent-color);"></i>${nomor}</span>
                        <span class="badge bg-primary rounded-pill">${index + 1}</span>
                    </div>
                `);
            });
        }

        // Update statistik
        function updateStatistics(data) {
            const $stats = $('#statistics');
            $stats.empty();

            // Pastikan data statistik ada dan beri nilai default jika tidak
            const stats = data || {
                total: 0,
                menunggu: 0,
                dipanggil: 0,
                selesai: 0,
                dilewati: 0
            };

            const items = [
                { label: 'Total', value: stats.total, color: 'primary', icon: 'fa-list-ol' },
                { label: 'Menunggu', value: stats.menunggu, color: 'warning', icon: 'fa-clock' },
                { label: 'Dipanggil', value: stats.dipanggil, color: 'info', icon: 'fa-bullhorn' },
                { label: 'Selesai', value: stats.selesai, color: 'success', icon: 'fa-check-circle' },
                { label: 'Dilewati', value: stats.dilewati, color: 'secondary', icon: 'fa-forward' }
            ];

            items.forEach(item => {
                $stats.append(`
            <div class="stat-card bg-${item.color} text-white">
                <i class="fas ${item.icon} mb-2" style="font-size: 24px;"></i>
                <div class="stat-value">${item.value}</div>
                <div class="stat-label">${item.label}</div>
            </div>
        `);
            });
        }

        // Panggil nomor berikutnya
        $('#btn-panggil').click(function () {
            $.ajax({
                url: '../api/panggil.php',
                type: 'POST',
                data: { loket_id: loketId },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // Akan diupdate oleh polling
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function (xhr, status, error) {
                    alert('Terjadi kesalahan saat memanggil nomor antrian.');
                    console.error("Error calling next number:", error);
                }
            });
        });

        // Perbaikan untuk tombol "Lewati"
        $('#btn-lewati').click(function () {
            if (!currentNumber) return;

            if (confirm("Apakah Anda yakin ingin melewati nomor antrian ini?")) {
                $.ajax({
                    url: '../api/lewati.php',
                    type: 'POST',
                    data: {
                        nomor: currentNumber,
                        loket_id: loketId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            // Akan diupdate oleh polling
                            console.log("Berhasil melewati antrian: " + currentNumber);
                        } else {
                            alert('Error: ' + response.message);
                            console.error("Error response:", response);
                        }
                    },
                    error: function (xhr, status, error) {
                        alert('Terjadi kesalahan saat melewati nomor antrian.');
                        console.error("Error details:", xhr.responseText);
                        console.error("Status:", status);
                        console.error("Error:", error);
                    }
                });
            }
        });

        // Perbaikan untuk tombol "Selesai"
        $("#btn-selesai").click(function () {
            if (!currentNumber) return;

            if (confirm("Apakah Anda yakin ingin menyelesaikan nomor antrian ini?")) {
                $.ajax({
                    url: '../api/selesai.php',
                    type: 'POST',
                    data: {
                        nomor: currentNumber,
                        loket_id: loketId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            // Akan diupdate oleh polling
                            console.log("Berhasil menyelesaikan antrian: " + currentNumber);
                        } else {
                            alert('Error: ' + response.message);
                            console.error("Error response:", response);
                        }
                    },
                    error: function (xhr, status, error) {
                        alert('Terjadi kesalahan saat menyelesaikan nomor antrian.');
                        console.error("Error details:", xhr.responseText);
                        console.error("Status:", status);
                        console.error("Error:", error);
                    }
                });
            }
        });

        // Start polling when page loads
        $(document).ready(function () {
            pollData();
        });
    </script>
</body>

</html>