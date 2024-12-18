<?php
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Gaji Guru</title>
</head>
<body>
    <div>
        <h3>Masukkan NIP untuk Data Gaji</h3>
        <input type="text" id="nipInput" placeholder="Masukkan NIP">
        <button onclick="getGaji(document.getElementById('nipInput').value)">Ambil Data Gaji</button>
    </div>

    <div>
        <h3>Data Gaji</h3>
        <table border="1" id="gajiTable">
            <thead>
                <tr>
                    <th>NIP</th>
                    <th>Tanggal</th>
                    <th>Durasi Kerja (menit)</th>
                    <th>Gaji</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <p id="totalGaji"></p>
    </div>

    <script>
        async function getGaji(nip) {
            if (nip.trim() === "") {
                alert("NIP tidak boleh kosong.");
                return;
            }

            const nipPattern = /^[0-9]{8}$/;
            if (!nipPattern.test(nip)) {
                alert("Format NIP tidak valid. Harap masukkan NIP 8 digit.");
                return;
            }

            const url = `?nip=${nip}`;
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`Error: ${response.status} - ${response.statusText}`);
                }
                const data = await response.json();

                if (data.status === 'success') {
                    displayGaji(data);
                } else {
                    alert(data.message);  // Tampilkan pesan jika gagal
                }
            } catch (error) {
                console.error("Fetch Error:", error.message);
                alert("Terjadi kesalahan saat mengambil data.");
            }
        }

        function displayGaji(data) {
            const tableBody = document.querySelector("#gajiTable tbody");
            tableBody.innerHTML = ""; // Menghapus tabel sebelumnya

            // Menampilkan data gaji
            data.gaji_data.forEach(item => {
                const row = `
                    <tr>
                        <td>${item.nip}</td>
                        <td>${item.tanggal}</td>
                        <td>${item.durasi_menit} menit</td>
                        <td>Rp ${item.gaji}</td>
                    </tr>
                `;
                tableBody.innerHTML += row;
            });

            // Menampilkan total gaji
            const totalGajiElement = document.getElementById("totalGaji");
            totalGajiElement.innerText = `Total Gaji: Rp ${data.total_gaji}`;
        }
    </script>
</body>
</html>
