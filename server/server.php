<?php
session_start();

// Rate Limiting Configuration
$request_limit = 5; // Maksimal permintaan
$time_window = 60;  // Waktu jendela dalam detik

// Inisialisasi sesi permintaan jika belum ada
if (!isset($_SESSION['request_times'])) {
    $_SESSION['request_times'] = [];
}

// Tambahkan timestamp saat ini ke daftar permintaan
$_SESSION['request_times'][] = time();

// Hapus timestamp lama yang sudah berada di luar jendela waktu
$_SESSION['request_times'] = array_filter(
    $_SESSION['request_times'],
    function ($timestamp) use ($time_window) {
        return $timestamp >= time() - $time_window;
    }
);

// Jika jumlah permintaan melebihi batas, kirim respons error
if (count($_SESSION['request_times']) > $request_limit) {
    header('Content-Type: application/json'); // Tentukan tipe konten sebagai JSON
    http_response_code(429); // Tetapkan kode status HTTP untuk Too Many Requests
    echo json_encode([
        'status' => 'error',
        'message' => 'Terlalu banyak permintaan. Coba lagi nanti.',
        'retry_after' => $time_window - (time() - reset($_SESSION['request_times'])), // Waktu tunggu hingga permintaan berikutnya diizinkan
    ]);
    exit;
}

// Proses normal di sini
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Permintaan berhasil.']);
exit;

if (isset($_GET['nip'])) {
    header('Content-Type: application/json');

    // Koneksi ke database manajemen sekolah dan manajemen guru
    $host = "localhost:3308";
    $user = "root";
    $pass = "root";
    $db_sekolah = "manajemen_sekolah";
    $db_guru = "manajemen_guru";

    // Koneksi ke database manajemen sekolah
    $conn_sekolah = mysqli_connect($host, $user, $pass, $db_sekolah);
    if (!$conn_sekolah) {
        die(json_encode(['status' => 'error', 'message' => 'Koneksi ke manajemen_sekolah gagal: ' . mysqli_connect_error()])); 
    }

    // Koneksi ke database manajemen guru
    $conn_guru = mysqli_connect($host, $user, $pass, $db_guru);
    if (!$conn_guru) {
        die(json_encode(['status' => 'error', 'message' => 'Koneksi ke manajemen_guru gagal: ' . mysqli_connect_error()])); 
    }

    $nip = mysqli_real_escape_string($conn_guru, $_GET['nip']); // Menghindari SQL Injection

    // Query untuk mengambil semua data tanggal, jam_masuk, jam_keluar dari manajemen_guru
    $query = "SELECT tanggal, jam_masuk, jam_keluar FROM presensi WHERE nip = ? ORDER BY tanggal DESC";
    $stmt = mysqli_prepare($conn_guru, $query);
    mysqli_stmt_bind_param($stmt, "s", $nip); // Mengikat parameter NIP
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $total_gaji = 0;
        $gaji_data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $tanggal = $row['tanggal'];
            $jam_masuk = $row['jam_masuk'];
            $jam_keluar = $row['jam_keluar'];

            $masuk = new DateTime($jam_masuk);
            $keluar = new DateTime($jam_keluar);
            $interval = $masuk->diff($keluar);
            $durasi_menit = $interval->h * 60 + $interval->i;

            $gaji_per_menit = 166;
            $gaji = $durasi_menit * $gaji_per_menit;
            $total_gaji += $gaji;

            $gaji_data[] = [
                'nip' => $nip,
                'tanggal' => $tanggal,
                'gaji' => number_format($gaji, 2, ',', '.'),
                'durasi_menit' => $durasi_menit
            ];

            // Insert gaji ke dalam database
            $query_insert = "INSERT INTO gaji (nip, tanggal, gaji) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn_sekolah, $query_insert);
            mysqli_stmt_bind_param($stmt_insert, "ssd", $nip, $tanggal, $gaji); // Mengikat parameter
            if (!mysqli_stmt_execute($stmt_insert)) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data gaji ke manajemen_sekolah']);
                exit;
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Data gaji berhasil disimpan',
            'nip' => $nip,
            'total_gaji' => number_format($total_gaji, 2, ',', '.'),
            'gaji_data' => $gaji_data
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data absensi tidak ditemukan untuk NIP tersebut']);
    }

    mysqli_close($conn_sekolah);
    mysqli_close($conn_guru);
    exit;
}
?>
