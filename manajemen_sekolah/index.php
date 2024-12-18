<?php
session_start();

// Koneksi Database
$host = "localhost:3308";
$user = "root";
$pass = "root";
$db = "manajemen_sekolah";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Proses Login
if (isset($_POST['login'])) {
    $nip = mysqli_real_escape_string($conn, $_POST['nip']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE nip = '$nip' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $_SESSION['nip'] = $user['nip'];
        $_SESSION['nama'] = $user['nama'];
    } else {
        $error = "NIP atau Password salah.";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Cek Login
if (!isset($_SESSION['nip'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f9; }
            .container { width: 300px; margin: 0 auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
            input[type="text"], input[type="password"] { width: 100%; padding: 8px; margin: 8px 0; }
            input[type="submit"] { width: 100%; padding: 10px; background-color: #f4f4f9; color: white; border: none; cursor: pointer; }
            input[type="submit"]:hover { background-color: #218838; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Login</h2>
            <form method="post" action="">
                <label>NIP:</label><br>
                <input type="text" name="nip" required><br><br>
                <label>Password:</label><br>
                <input type="password" name="password" required><br><br>
                <input type="submit" name="login" value="Login">
            </form>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Menu Aktif
$menu = isset($_GET['menu']) ? $_GET['menu'] : 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        header { background-color: #007bff; color: white; text-align: center; padding: 20px 0; }
        nav { text-align: center; background-color: #f4f4f9; padding: 10px; }
        nav a { color: white; text-decoration: none; font-weight: bold; margin: 0 15px; padding: 10px 20px; background-color: #007bff; border-radius: 5px; }
        nav a:hover { background-color: #0056b3; }
        .container { width: 80%; margin: 20px auto; }
        form input[type="text"], form input[type="submit"], form textarea { width: 100%; padding: 8px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: center; }
        th { background-color: #007bff; color: white; }
        td { background-color: #f9f9f9; }
    </style>
</head>
<body>

<header>
    <h1>Selamat Datang, <?php echo $_SESSION['nama']; ?></h1>
</header>

<nav>
    <a href="?menu=jadwal">Input Jadwal Guru</a>
    <a href="?menu=gaji">Informasi Gaji Guru</a>
    <a href="?menu=evaluasi">Evaluasi Performa Guru</a>
    <a href="?logout=true" style="color: red;">Logout</a>
</nav>

<div class="container">
    <?php
    if ($menu == 'jadwal') {
        echo "<h2>Input Jadwal Guru</h2>";
        if (isset($_POST['simpan_jadwal'])) {
            $nip = $_SESSION['nip'];
            $mata_pelajaran = mysqli_real_escape_string($conn, $_POST['mata_pelajaran']);
            $hari = mysqli_real_escape_string($conn, $_POST['hari']);
            mysqli_query($conn, "INSERT INTO jadwal (nip, mata_pelajaran, hari) VALUES ('$nip', '$mata_pelajaran', '$hari')");
            echo "<p>Jadwal berhasil ditambahkan!</p>";
        }
        ?>
        <form method="post">
            <label>Mata Pelajaran:</label><br>
            <input type="text" name="mata_pelajaran" required><br><br>
            <label>Hari:</label><br>
            <input type="text" name="hari" required><br><br>
            <input type="submit" name="simpan_jadwal" value="Simpan Jadwal">
        </form>
    <?php
    } elseif ($menu == 'gaji') {
        echo "<h2>Informasi Gaji Guru</h2>";
        if (!isset($_POST['cari_nip'])) {
            echo "<form method='post'>
                    <label>Cari NIP:</label><br>
                    <input type='text' name='nip' required><br><br>
                    <input type='submit' name='cari_nip' value='Cari'>
                  </form>";
        }

        if (isset($_POST['cari_nip'])) {
            $nipCari = mysqli_real_escape_string($conn, $_POST['nip']);
            $bulan = date('Y-m');
            
            // URL API untuk mengambil data gaji berdasarkan NIP
            $api_url = "http://localhost/TATST/server/server.php?nip=$nipCari&bulan=$bulan"; // Sesuaikan dengan URL API Anda

            // Menggunakan cURL untuk memanggil API
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $api_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($curl);
            curl_close($curl);

            // Decode hasil API
            $data = json_decode($response, true);

            if ($data && $data['status'] === 'success') {
                echo "<table>
                        <tr>
                            <th>NIP</th>
                            <th>Tanggal</th>
                            <th>Gaji</th>
                        </tr>";
                        $totalGaji = 0; // Inisialisasi variabel total gaji

                        foreach ($data['gaji_data'] as $row) {
                            // Menghapus 'Rp.' dan titik ribuan (jika ada), serta mengganti koma dengan titik sebagai pemisah desimal
                            $gaji = str_replace(['Rp.', '.', ','], ['', '', '.'], $row['gaji']); // Menghapus Rp., titik ribuan, dan koma (untuk desimal)
                            
                            // Pastikan gaji diubah menjadi float dan sesuai format angka
                            $gaji = floatval($gaji); // Mengonversi menjadi angka desimal
                            
                            // Tampilkan gaji dalam tabel dengan format yang benar
                            echo "<tr>
                                    <td>{$row['nip']}</td>
                                    <td>{$row['tanggal']}</td>
                                    <td>Rp. " . number_format($gaji, 0, ',', '.') . "</td>
                                  </tr>";
                            
                            // Jumlahkan gaji ke total
                            $totalGaji += $gaji;
                        }
                        
                        // Tampilkan total gaji dengan format yang benar
                        echo "<h3>Total Gaji Bulanan: Rp. " . number_format($totalGaji, 0, ',', '.') . "</h3>";
                        
            } else {
                echo "<p>Data gaji tidak ditemukan untuk NIP $nipCari pada bulan ini.</p>";
            }
        }
    } elseif ($menu == 'evaluasi') {
        echo "<h2>Evaluasi Performa Guru</h2>";
        if (isset($_POST['simpan_evaluasi'])) {
            $nip = $_SESSION['nip'];
            $evaluasi = mysqli_real_escape_string($conn, $_POST['evaluasi']);
            mysqli_query($conn, "INSERT INTO evaluasi (nip, evaluasi) VALUES ('$nip', '$evaluasi')");
            echo "<p>Evaluasi berhasil disimpan!</p>";
        }
        ?>
        <form method="post">
            <label>Catatan Evaluasi:</label><br>
            <textarea name="evaluasi" rows="4" cols="50" required></textarea><br><br>
            <input type="submit" name="simpan_evaluasi" value="Simpan Evaluasi">
        </form>
    <?php
    } else {
        echo "<h2>Selamat Datang di Dashboard</h2>";
        echo "<p>Silakan pilih menu di atas.</p>";
    }
    ?>
</div>

</body>
</html>
