<!DOCTYPE html>
<!--
 * DESKRIPSI: Aplikasi SISTEM ANTRIAN berbasis PHP, JS, HTML dan CSS
 * AUTHOR: [rizkysln_]
 * DATE: [19 Mei 2025]
 * UPDATED: Disesuaikan dengan tema display antrian Kantor Pos Rantauprapat
 -->
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Nomor Antrian</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #182B5A;     /* Warna biru tua utama */
            --secondary-color: #FFFFFF;    /* Warna putih */
            --accent-color: #FF5733;       /* Warna oranye untuk aksen */
            --bg-color: #F5F5F5;           /* Warna latar belakang abu-abu terang */
            --highlight-color: rgba(255, 87, 51, 0.3); /* Warna highlight untuk animasi */
            
            /* Warna layanan */
            --service-a-color: #182B5A;    /* Warna Layanan A (Prioritas) */
            --service-b-color: #0A5F38;    /* Warna Layanan B (Reguler) */
            --service-c-color: #FF5733;    /* Warna Layanan C (Informasi) */
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
        }

        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .ticket-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
        }

        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.8);
        }

        .content {
            padding: 25px;
        }

        /* Service options styling */
        .service-options .btn {
            text-align: left;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-width: 2px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .service-options .btn i {
            margin-right: 10px;
            font-size: 18px;
        }

        /* Service colors */
        .service-options .btn.active[data-service="A"] {
            background-color: var(--service-a-color);
            border-color: var(--service-a-color);
            color: white;
        }

        .service-options .btn.active[data-service="B"] {
            background-color: var(--service-b-color);
            border-color: var(--service-b-color);
            color: white;
        }

        .service-options .btn.active[data-service="C"] {
            background-color: var(--service-c-color);
            border-color: var(--service-c-color);
            color: white;
        }

        /* Take number button */
        .take-number-btn {
            background-color: var(--service-a-color);
            border: none;
            width: 100%;
            padding: 15px;
            font-size: 18px;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(24, 43, 90, 0.3);
            transition: all 0.3s ease;
        }

        .take-number-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(24, 43, 90, 0.4);
        }

        /* Ticket styling */
        .ticket {
            display: none;
            margin-top: 20px;
        }

        .ticket-inner {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }

        .ticket-inner::before,
        .ticket-inner::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: var(--bg-color);
            border-radius: 50%;
        }

        .ticket-inner::before {
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .ticket-inner::after {
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .ticket-header {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .ticket-number {
            font-size: 48px;
            font-weight: bold;
            padding: 15px 0;
        }

        .ticket-date {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 15px;
        }

        /* Service-specific ticket colors */
        .ticket-inner.service-a {
            background-color: var(--service-a-color);
        }

        .ticket-inner.service-b {
            background-color: var(--service-b-color);
        }

        .ticket-inner.service-c {
            background-color: var(--service-c-color);
        }

        /* Waiting info styling */
        .waiting-info {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.2);
            padding: 10px;
            border-radius: 5px;
        }

        .waiting-info i {
            margin-right: 10px;
        }

        /* Action buttons */
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }

        .action-buttons .btn {
            padding: 10px;
        }

        .action-buttons .btn i {
            margin-right: 5px;
        }

        /* Pulse animation */
        .pulse {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            background-color: white;
            color: #333;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            width: 300px;
            animation: slideIn 0.3s ease-out;
        }

        .toast i {
            margin-right: 10px;
            font-size: 20px;
        }

        .toast.success {
            border-left: 5px solid #28a745;
        }

        .toast.success i {
            color: #28a745;
        }

        .toast.error {
            border-left: 5px solid #dc3545;
        }

        .toast.error i {
            color: #dc3545;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Spinner animation */
        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        /* Footer styling */
        footer {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 15px 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .title {
                font-size: 24px;
            }

            .ticket-number {
                font-size: 40px;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }

        /* Logo styling */
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .logo {
            height: 50px;
            margin-right: 15px;
        }

        /* Location info */
        .location-info {
            font-size: 14px;
            color: rgb(252, 126, 0);
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="ticket-container">
            <div class="header">
                <div class="logo-container">
                    <img src="logo.jpg" alt="Logo Pos Indonesia" class="logo">
                    <div class="title">SISTEM ANTRIAN</div>
                </div>
                <div class="location-info">Kantor Pos Rantauprapat</div>
                <div class="subtitle">Silakan pilih layanan dan ambil nomor antrian</div>
            </div>

            <div class="content">
                <div class="service-options mb-4">
                    <h5 class="mb-3">Pilih Jenis Layanan:</h5>
                    <div class="btn-group-vertical w-100" role="group">
                        <button type="button" class="btn btn-outline-primary service-btn active" data-service="A">
                            <i class="fas fa-user-tie"></i> Layanan Umum
                        </button>
                        <button type="button" class="btn btn-outline-primary service-btn" data-service="B">
                            <i class="fas fa-file-alt"></i> Layanan Pensiun
                        </button>
                        <button type="button" class="btn btn-outline-primary service-btn" data-service="C">
                            <i class="fas fa-question-circle"></i> Layanan CS
                        </button>
                    </div>
                </div>

                <button class="btn btn-primary btn-lg take-number-btn" id="take-number-btn">
                    <i class="fas fa-ticket-alt"></i> AMBIL NOMOR
                </button>

                <div class="ticket" id="ticket">
                    <div class="ticket-inner service-a" id="ticket-inner">
                        <div class="ticket-header">Nomor Antrian Anda</div>
                        <div class="ticket-number pulse" id="ticket-number">A001</div>
                        <div class="ticket-date" id="ticket-date"></div>
                        <div class="waiting-info" id="waiting-info">
                            <i class="fas fa-clock"></i>
                            <span>Estimasi tunggu: ± 10 menit</span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-outline-secondary" id="print-btn">
                            <i class="fas fa-print"></i> Cetak Tiket
                        </button>
                        <button class="btn btn-outline-primary" id="new-number-btn">
                            <i class="fas fa-plus-circle"></i> Ambil Nomor Baru
                        </button>
                        <button class="btn btn-outline-secondary" id="share-btn">
                            <i class="fas fa-share-alt"></i> Bagikan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toast-container">
        <!-- Toast notifications will appear here -->
    </div>

    <footer>
        <div class="container">
            <p class="mb-0">Sistem Antrian &copy; 2025 | <i class="fas fa-heart" style="color: #ff4757;"></i> Customer
                Service</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            let selectedService = 'A'; // Default service

            // Handle service selection
            $('.service-btn').click(function () {
                $('.service-btn').removeClass('active');
                $(this).addClass('active');
                selectedService = $(this).data('service');

                // Update button color based on service
                if (selectedService === 'A') {
                    $('#take-number-btn').css('background-color', 'var(--service-a-color)')
                        .css('box-shadow', '0 10px 20px rgba(24, 43, 90, 0.3)');
                } else if (selectedService === 'B') {
                    $('#take-number-btn').css('background-color', 'var(--service-b-color)')
                        .css('box-shadow', '0 10px 20px rgba(10, 95, 56, 0.3)');
                } else {
                    $('#take-number-btn').css('background-color', 'var(--service-c-color)')
                        .css('box-shadow', '0 10px 20px rgba(255, 87, 51, 0.3)');
                }
            });

            // Fungsi untuk mengambil nomor antrian baru
            function fetchNewNumber() {
                // Tampilkan loading
                $('#take-number-btn').prop('disabled', true)
                    .html('<i class="fas fa-spinner spinner"></i> Memproses...');

                // Kirim request ke ambil-antrian.php
                $.ajax({
                    url: 'api/ambil-nomor.php',
                    type: 'POST',
                    data: {
                        jenis_layanan: selectedService // Gunakan selectedService yang sudah ada
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.sukses) {
                            // Update tampilan dengan response dari server
                            $('#ticket-number').text(response.data.nomor_antrian);
                            $('#ticket-inner').removeClass('service-a service-b service-c')
                                .addClass('service-' + selectedService.toLowerCase());

                            // Format tanggal dari response
                            $('#ticket-date').text(response.data.waktu_ambil);

                            // Update estimasi tunggu
                            $('#waiting-info').html(`
                    <i class="fas fa-clock"></i>
                    <span>Estimasi tunggu: ± ${response.data.estimasi_tunggu} menit</span>
                `);

                            // Tampilkan tiket
                            $('#ticket').hide().fadeIn(500);

                            // Notifikasi sukses
                            showToast('Nomor antrian ' + response.data.nomor_antrian + ' berhasil diambil', 'success');

                            // Vibrate jika didukung
                            if ('vibrate' in navigator) {
                                navigator.vibrate(200);
                            }
                        } else {
                            showToast(response.pesan, 'error');
                        }
                    },
                    error: function () {
                        showToast('Gagal terhubung ke server', 'error');
                    },
                    complete: function () {
                        // Reset tombol
                        $('#take-number-btn').prop('disabled', false)
                            .html('<i class="fas fa-ticket-alt"></i> AMBIL NOMOR');
                    }
                });
            }

            // Toast notification function
            function showToast(message, type) {
                let icon = 'fa-info-circle';
                let toastClass = '';

                if (type === 'success') {
                    icon = 'fa-check-circle';
                    toastClass = 'success';
                } else if (type === 'error') {
                    icon = 'fa-exclamation-circle';
                    toastClass = 'error';
                }

                const toast = `
                    <div class="toast ${toastClass}">
                        <i class="fas ${icon}"></i>
                        <div>${message}</div>
                    </div>
                `;

                const $toast = $(toast);
                $('#toast-container').append($toast);

                // Auto remove after 3 seconds
                setTimeout(function () {
                    $toast.animate({ opacity: 0, marginRight: '-100%' }, 300, function () {
                        $(this).remove();
                    });
                }, 3000);
            }

            // Create a function to format the ticket for printing
            function formatTicketForPrinting() {
                const ticketNumber = $('#ticket-number').text();
                const ticketDate = $('#ticket-date').text();
                const waitingInfo = $('#waiting-info span').text();

                // Ambil nama layanan dari class
                let serviceName = 'Layanan Reguler';
                if (ticketNumber.startsWith('A')) serviceName = 'Layanan Prioritas';
                if (ticketNumber.startsWith('C')) serviceName = 'Layanan Informasi';

                return `
        <div style="width: 300px; margin: 0 auto; text-align: center; font-family: Arial, sans-serif;">
            <h2 style="margin-bottom: 5px;">NOMOR ANTRIAN</h2>
            <div style="font-size: 16px; margin-bottom: 10px; color: #555;">Kantor Pos Rantauprapat</div>
            <div style="font-size: 16px; margin-bottom: 10px; color: #555;">${serviceName}</div>
            <div style="border: 2px dashed #ccc; padding: 15px; border-radius: 10px; margin: 10px 0;">
                <div style="font-size: 18px; margin-bottom: 10px;">Nomor Antrian Anda</div>
                <div style="font-size: 60px; font-weight: bold; margin: 15px 0;">${ticketNumber}</div>
                <div style="font-size: 14px; color: #666; margin-bottom: 10px;">${ticketDate}</div>
                <div style="font-size: 16px; margin-top: 10px;">${waitingInfo}</div>
            </div>
            <div style="margin-top: 15px; font-size: 12px;">
                <p>Terima kasih telah menggunakan layanan kami.</p>
                <p>Mohon tunggu hingga nomor anda dipanggil.</p>
            </div>
        </div>
    `;
            }

            // Share tiket function - define before using it
            function shareTicket() {
                const ticketNumber = $('#ticket-number').text();
                const ticketDate = $('#ticket-date').text();
                const waitingInfo = $('#waiting-info span').text();

                // Ambil nama layanan dari class
                let serviceName = 'Reguler';
                if (ticketNumber.startsWith('A')) serviceName = 'Prioritas';
                if (ticketNumber.startsWith('C')) serviceName = 'Informasi';

                const shareText = `Nomor Antrian ${serviceName}: ${ticketNumber}\nTanggal: ${ticketDate}\n${waitingInfo}`;
                // Check if Web Share API is available
                if (navigator.share) {
                    navigator.share({
                        title: 'Nomor Antrian',
                        text: shareText,
                    })
                        .then(() => showToast('Berhasil dibagikan', 'success'))
                        .catch(() => showToast('Pembagian dibatalkan', 'error'));
                } else {
                    // Fallback for browsers that don't support Web Share API
                    try {
                        const textArea = document.createElement('textarea');
                        textArea.value = shareText;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
                        showToast('Nomor antrian disalin ke clipboard', 'success');
                    } catch (err) {
                        showToast('Tidak dapat menyalin teks', 'error');
                    }
                }
            }

            // Ambil nomor pertama kali
            $('#take-number-btn').click(function () {
                fetchNewNumber();
            });

            // Ambil nomor baru
            $('#new-number-btn').click(function () {
                fetchNewNumber();
            });

            // Cetak tiket
            $('#print-btn').click(function () {
                const printContents = formatTicketForPrinting();
                const originalContents = document.body.innerHTML;

                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;

                // Reinitialize event handlers after printing
                $(document).ready(function () {
                    $('#take-number-btn').click(fetchNewNumber);
                    $('#new-number-btn').click(fetchNewNumber);
                    $('#print-btn').click(function() {
                        const printContents = formatTicketForPrinting();
                        const originalContents = document.body.innerHTML;
                        document.body.innerHTML = printContents;
                        window.print();
                        document.body.innerHTML = originalContents;
                    });
                    $('#share-btn').click(shareTicket);
                });
            });

            // Share tiket
            $('#share-btn').click(shareTicket);

            // Check for online/offline status
            function updateOnlineStatus() {
                if (navigator.onLine) {
                    if ($('#offline-message').length) {
                        $('#offline-message').slideUp(300, function () {
                            $(this).remove();
                        });
                    }
                } else {
                    if ($('#offline-message').length === 0) {
                        $('body').append(`
                            <div id="offline-message" style="position: fixed; bottom: 0; left: 0; right: 0; background-color: #d90429; color: white; text-align: center; padding: 10px; z-index: 9999;">
                                <i class="fas fa-wifi" style="margin-right: 8px;"></i>
                                Anda sedang offline. Silakan periksa koneksi internet Anda.
                            </div>
                        `);
                        $('#offline-message').hide().slideDown(300);
                    }
                }
            }

            window.addEventListener('online', updateOnlineStatus);
            window.addEventListener('offline', updateOnlineStatus);
            updateOnlineStatus();

            // Check if the page is displayed in a standalone PWA mode
            if (window.matchMedia('(display-mode: standalone)').matches) {
                $('.subtitle').text('Aplikasi Sistem Antrian');
            }
        });
    </script>
</body>

</html>