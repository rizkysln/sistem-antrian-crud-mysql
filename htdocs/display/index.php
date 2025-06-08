<?php
// File: index.php
// Sistem Antrian Kantor Pos Rantauprapat
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Antrian Kantor Pos Rantauprapat</title>
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
            color:rgb(252, 126, 0);
        }

        .clock {
            font-size: 28px;
            font-weight: bold;
            color: #FF5733; /* Warna oranye untuk jam */
            background-color: rgba(0, 0, 0, 0.2);
            padding: 10px 20px;
            border-radius: 10px;
        }

        .main-content {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .loket-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .loket-display {
            background-color: var(--primary-color);
            border: 1px solid #182B5A;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            color: white;
            height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .loket-display.highlight {
            background-color: var(--highlight-color);
            border-color: var(--accent-color);
            box-shadow: 0 0 15px rgba(255, 87, 51, 0.5);
        }

        .loket-title {
            font-size: 18px;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loket-icon {
            margin-right: 8px;
            color: var(--accent-color);
        }

        .loket-number {
            font-size: 56px;
            font-weight: bold;
            color: var(--secondary-color);
            padding: 10px 0;
        }

        .bottom-panel {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .recent-calls {
            background-color: var(--primary-color);
            border: 1px solid #182B5A;
            border-radius: 10px;
            padding: 15px;
            color: white;
        }

        .recent-calls-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: var(--secondary-color);
        }

        .recent-calls-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .recent-call-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 10px;
        }

        .call-number {
            display: flex;
            align-items: center;
        }

        .call-icon {
            color: #FF5733;
            margin-right: 8px;
            font-size: 16px;
        }

        .call-badge {
            font-size: 12px;
            background-color: #FF5733;
            color: white;
            padding: 2px 10px;
            border-radius: 20px;
        }

        .settings-panel {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .calendar-section {
            text-align: center;
            margin-bottom: 15px;
        }

        .calendar-icon {
            font-size: 32px;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .calendar-day {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: var(--primary-color);
        }

        .calendar-date {
            font-size: 16px;
            margin: 0;
        }

        /* Toggle switch styling */
        .form-switch {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin: 15px 0;
        }

        .form-check-input {
            height: 20px;
            width: 40px;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-test {
            background-color: var(--primary-color);
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn-test:hover {
            background-color: #0D1A3A;
            color: white;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .loket-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .recent-calls-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .bottom-panel {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .header h1 {
                font-size: 18px;
            }

            .clock {
                font-size: 22px;
            }

            .loket-row {
                grid-template-columns: 1fr;
            }

            .recent-calls-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo-container">
                    <img src="../assets/sounds/logo.jpg" alt="Logo Pos Indonesia" class="logo" />
                    <div class="header-info">
                        <h1 class="m-0">DAFTAR ANTRIAN LOKET</h1>
                        <div class="location-info">Kantor Pos Rantauprapat</div>
                    </div>
                </div>
                <div class="clock" id="clock"></div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="main-content">
            <!-- Loket Row -->
            <div class="loket-row" id="loket-container">
                <!-- Loket akan ditambahkan di sini oleh JavaScript -->
            </div>

            <!-- Bottom Panel -->
            <div class="bottom-panel">
                <!-- Recent Calls -->
                <div class="recent-calls">
                    <div class="recent-calls-title"><i class="fas fa-history me-2"></i>PANGGILAN TERAKHIR</div>
                    <div class="recent-calls-list" id="recent-calls-list">
                        <!-- Panggilan terakhir akan ditambahkan di sini oleh JavaScript -->
                    </div>
                </div>
                <!-- Calendar Panel -->
                <div class="settings-panel">
                    <div class="calendar-section">
                        <div class="calendar-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <p class="calendar-day" id="calendar-day">SENIN</p>
                        <p class="calendar-date" id="calendar-date">19/05/2025</p>
                    </div>
                    <div class="form-check form-switch mb-3 mt-3">
                        <label class="form-check-label" for="enableSound">Aktifkan Suara</label>
                        <input class="form-check-input" type="checkbox" id="enableSound" checked>
                    </div>
                    <button class="btn btn-test" id="testSound">
                        <i class="fas fa-volume-up me-2"></i>Tes Suara
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio for bell sound -->
    <audio id="bell-sound" src="../assets/sounds/bell.mp3" preload="auto"></audio>

    <!-- QR Code library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Variabel untuk menyimpan data sebelumnya
        let previousData = {
            loket: [],
            panggilan: []
        };

        // Definisi ID loket yang sesuai dengan nama loket yang diinginkan
        const loketConfig = {
            'Umum': 1,
            'Pensiun': 2,
            'CS': 3
        };

        // Fungsi untuk memperbarui jam
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            $('#clock').text(timeString);
            setTimeout(updateClock, 1000);
        }

        // Fungsi untuk memperbarui tanggal dan hari
        function updateCalendar() {
            const now = new Date();
            const options = { weekday: 'long' };
            const dayName = now.toLocaleDateString('id-ID', options).toUpperCase();

            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const dateString = `${day}/${month}/${year}`;

            $('#calendar-day').text(dayName);
            $('#calendar-date').text(dateString);
        }

        // Fungsi untuk mengambil data antrian
        function fetchQueueData() {
            // Gunakan AJAX untuk mengambil data real-time dari API
            $.ajax({
                url: '../api/status.php?t=' + new Date().getTime(),
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        updateDisplay(data);
                        previousData = {
                            loket: [...data.loket],
                            panggilan: [...data.panggilan_terakhir]
                        };
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching queue data:", error);
                    if ($('#loket-container').children().length === 0) {
                        // Display dummy data for visualization
                        displayDummyData();
                    }
                },
                complete: function () {
                    // Polling lebih cepat untuk responsif yang lebih baik
                    setTimeout(fetchQueueData, 1000);
                }
            });
        }

        // Fungsi untuk menampilkan data dummy jika API tidak tersedia
        function displayDummyData() {
            const loketContainer = $('#loket-container');
            loketContainer.empty();
            
            // Data dummy untuk loket
            const dummyLoket = [
                { id: 1, name: "UMUM", number: "A015" },
                { id: 2, name: "PENSIUN", number: "B007" },
                { id: 3, name: "CS", number: "-" }
            ];
            
            // Tambahkan 3 loket dengan nomor dummy
            dummyLoket.forEach(loket => {
                const icon = getLoketIcon(loket.name);
                loketContainer.append(`
                    <div class="loket-display" id="loket-${loket.id}">
                        <div class="loket-title"><i class="fas ${icon} loket-icon"></i>${loket.name}</div>
                        <div class="loket-number">${loket.number}</div>
                    </div>
                `);
            });
            
            // Tambahkan panggilan terakhir dummy
            const recentCallsList = $('#recent-calls-list');
            recentCallsList.empty();
            
            // Data dummy untuk panggilan terakhir
            const dummyCalls = [
                { number: "A015", loket: "Umum" },
                { number: "B007", loket: "Pensiun" },
                { number: "A014", loket: "Umum" }
            ];
            
            dummyCalls.forEach(call => {
                recentCallsList.append(`
                    <div class="recent-call-item">
                        <div class="call-number">
                            <i class="fas fa-bookmark call-icon"></i>${call.number}
                        </div>
                        <span class="call-badge">${call.loket}</span>
                    </div>
                `);
            });
        }

        // Fungsi untuk mendapatkan icon yang sesuai dengan jenis loket
        function getLoketIcon(loketName) {
            switch(loketName.toUpperCase()) {
                case 'UMUM':
                    return 'fa-users';
                case 'PENSIUN':
                    return 'fa-user-clock';
                case 'CS':
                    return 'fa-headset';
                default:
                    return 'fa-user-alt';
            }
        }

        // Fungsi untuk memperbarui tampilan
        function updateDisplay(data) {
            // Deteksi database kosong
            const isEmpty = (data.loket.length === 0 && data.panggilan_terakhir.length === 0);

            if (isEmpty) {
                $('#loket-container').html('<div class="loket-display"><div class="loket-title text-center">Tidak ada loket aktif</div><div class="text-center mt-3">Database masih kosong atau belum terkonfigurasi</div></div>');
                $('#recent-calls-list').html('<div class="recent-call-item text-center text-muted"><i class="fas fa-coffee me-2"></i>Belum ada panggilan</div>');
                return;
            }

            updateLokets(data.loket);
            updateRecentCalls(data.panggilan_terakhir);
        }

        // Fungsi untuk memperbarui tampilan loket
        function updateLokets(lokets) {
            const container = $('#loket-container');

            // Hanya buat ulang loket jika belum ada
            if (container.children().length === 0) {
                container.empty();

                // Tambahkan 3 loket dengan urutan yang ditentukan
                const loketTypes = [
                    { id: loketConfig['Umum'], nama: 'UMUM', icon: 'fa-users' },
                    { id: loketConfig['Pensiun'], nama: 'PENSIUN', icon: 'fa-user-clock' },
                    { id: loketConfig['CS'], nama: 'CS', icon: 'fa-headset' }
                ];

                loketTypes.forEach(loketType => {
                    container.append(`
                        <div class="loket-display" id="loket-${loketType.id}">
                            <div class="loket-title"><i class="fas ${loketType.icon} loket-icon"></i>${loketType.nama}</div>
                            <div class="loket-number">-</div>
                        </div>
                    `);
                });
            }

            // Update nomor antrian di setiap loket
            lokets.forEach((loket) => {
                const loketElement = $(`#loket-${loket.id}`);
                if (loketElement.length > 0) {
                    // Update nomor antrian
                    loketElement.find('.loket-number').text(loket.antrian_saat_ini || '-');

                    // Tambahkan highlight dan suara jika ada perubahan
                    if (previousData.loket.length > 0) {
                        const prevLoket = previousData.loket.find(l => l.id === loket.id);
                        if (prevLoket && prevLoket.antrian_saat_ini !== loket.antrian_saat_ini && loket.antrian_saat_ini) {
                            loketElement.addClass('highlight');
                            setTimeout(() => loketElement.removeClass('highlight'), 2000);

                            // Mainkan suara jika diaktifkan
                            if ($('#enableSound').is(':checked')) {
                                playAnnouncement(loket.antrian_saat_ini, loket.nama);
                            }
                        }
                    }
                }
            });
        }

        // Fungsi untuk memperbarui daftar panggilan terakhir
        function updateRecentCalls(calls) {
            const list = $('#recent-calls-list');
            list.empty();

            if (calls.length === 0) {
                list.append('<div class="recent-call-item text-center"><i class="fas fa-coffee me-2"></i>Belum ada panggilan</div>');
                return;
            }

            calls.forEach(call => {
                list.append(`
                    <div class="recent-call-item">
                        <div class="call-number">
                            <i class="fas fa-bookmark call-icon"></i>${call.nomor}
                        </div>
                        <span class="call-badge">${call.loket_nama}</span>
                    </div>
                `);
            });
        }

        // Fungsi untuk memainkan pengumuman (bell + TTS) dengan delay yang disesuaikan
        function playAnnouncement(number, loket) {
            // Mainkan bel terlebih dahulu
            const bell = document.getElementById('bell-sound');
            bell.currentTime = 0;

            // Mainkan bel dan tunggu sampai selesai + delay tambahan
            bell.play().then(() => {
                // Delay setelah bel selesai (dalam milidetik)
                const BELL_DURATION = 2000; // Durasi bel (ms)
                const EXTRA_DELAY = 1000; // Delay tambahan (ms)
                const TOTAL_DELAY = BELL_DURATION + EXTRA_DELAY;

                console.log(`Menunggu ${TOTAL_DELAY}ms sebelum TTS`);

                setTimeout(() => {
                    if ('speechSynthesis' in window && $('#enableSound').is(':checked')) {
                        // Mapping nama loket untuk pengumuman suara
                        const namaLoket = {
                            'UMUM': "Umum",
                            'PENSIUN': "Pensiun", 
                            'CS': "CS"
                        };

                        // Cari suara bahasa Indonesia jika ada
                        const voices = window.speechSynthesis.getVoices();
                        let indonesianVoice = voices.find(voice => voice.lang.includes('id-ID'));

                        const msg = new SpeechSynthesisUtterance();
                        msg.text = `Nomor ${number}, silakan ke loket ${namaLoket[loket] || loket}`;
                        msg.lang = 'id-ID';
                        msg.volume = 1;

                        if (indonesianVoice) {
                            msg.voice = indonesianVoice;
                        }

                        console.log("Memulai TTS:", msg.text);

                        // Hentikan pengumuman sebelumnya jika ada
                        window.speechSynthesis.cancel();
                        window.speechSynthesis.speak(msg);
                    }
                }, TOTAL_DELAY);

            }).catch(e => console.error("Gagal memainkan bel:", e));
        }

        // Fungsi untuk memastikan suara TTS tersedia
        function ensureSpeechSynthesis() {
            if ('speechSynthesis' in window) {
                // Paksa browser memuat daftar suara
                window.speechSynthesis.getVoices();

                // Beberapa browser memerlukan event ini
                window.speechSynthesis.onvoiceschanged = function () {
                    console.log("Suara TTS tersedia:", window.speechSynthesis.getVoices());
                };
            }
        }

        // Fungsi untuk menguji suara
        function testSound() {
            if ($('#enableSound').is(':checked')) {
                playAnnouncement("A001", "UMUM");
            } else {
                alert("Aktifkan suara terlebih dahulu!");
            }
        }

        // Inisialisasi saat halaman dimuat
        $(document).ready(function () {
            updateClock();
            updateCalendar();
            
            // Coba ambil data dari API, jika gagal tampilkan data dummy
            fetchQueueData();
            
            // Jika API gagal diakses, tampilkan data dummy
            setTimeout(function() {
                if ($('#loket-container').children().length === 0) {
                    displayDummyData();
                }
            }, 2000);
            
            ensureSpeechSynthesis();

            // Event listener untuk tombol tes suara
            $('#testSound').click(testSound);

            // Pastikan interaksi pengguna untuk mengaktifkan audio
            document.addEventListener('click', function () {
                // Preload bell sound
                const bell = document.getElementById('bell-sound');
                bell.volume = 1;
                bell.play().then(() => {
                    bell.pause();
                    bell.currentTime = 0;
                    bell.volume = 1;
                }).catch(e => console.log("Preload bell sound:", e));
            }, { once: true });
        });
    </script>
</body>

</html>