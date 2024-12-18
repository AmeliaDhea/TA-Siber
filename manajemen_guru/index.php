<?php
session_start();

// Koneksi Database
$host = "localhost:3308";
$user = "root";
$pass = "root";
$db = "manajemen_guru";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Proses Login
if (isset($_POST['login'])) {
    $nip = $_POST['nip'];
    $password = $_POST['password'];

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
    </head>
    <body>
        <h2>Login</h2>
        <form method="post" action="">
            <label>NIP:</label><br>
            <input type="text" name="nip" required><br><br>
            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>
            <input type="submit" name="login" value="Login">
        </form>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
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
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        h1 { text-align: center; }
        nav { background-color: #007BFF; padding: 10px; text-align: center; }
        nav a { color: #fff; text-decoration: none; margin: 10px; font-weight: bold; }
        section { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid black; text-align: center; }
        th, td { padding: 10px; }
    </style>
</head>
<body>
    <h1>Selamat Datang, <?php echo $_SESSION['nama']; ?></h1>
    <nav>
        <a href="?menu=jadwal">Jadwal Guru</a>
        <a href="?menu=presensi">Presensi Guru</a>
        <a href="?menu=keahlian">Tambah Keahlian</a>
        <a href="?logout=true" style="color: red;">Logout</a>
    </nav>

    <section>
        <?php
        if ($menu == 'jadwal') {
            echo "<h2>Jadwal Guru</h2>";
            $nip = $_SESSION['nip'];
            $result = mysqli_query($conn, "SELECT * FROM jadwal WHERE nip = '$nip'");
            if (mysqli_num_rows($result) > 0) {
                echo "<table><tr><th>Mata Pelajaran</th><th>Hari</th></tr>";
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr><td>{$row['mata_pelajaran']}</td><td>{$row['hari']}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "Jadwal tidak tersedia.";
            }
        } elseif ($menu == 'presensi') {
            echo "<h2>Presensi Guru</h2>";
        
            // Proses Simpan Presensi
            if (isset($_POST['presensi'])) {
                $nip = $_SESSION['nip']; // Ambil NIP dari session login
                $tanggal = $_POST['tanggal']; // Menggunakan tanggal yang dipilih dari input
                $jam_masuk = $_POST['jam_masuk'];
                $jam_keluar = $_POST['jam_keluar'];
        
                // Masukkan data ke database
                $query = "INSERT INTO presensi (nip, tanggal, jam_masuk, jam_keluar) 
                          VALUES ('$nip', '$tanggal', '$jam_masuk', '$jam_keluar')";
                mysqli_query($conn, $query);
        
                echo "<p style='color:green;'>Presensi berhasil ditambahkan!</p>";
            }
        
            // Form Input Presensi
            ?>
            <form method="post" action="">
                <label>Tanggal:</label><br>
                <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>" ><br><br>

                <label>Jam Masuk:</label><br>
                <input type="time" name="jam_masuk" required><br><br>
        
                <label>Jam Keluar:</label><br>
                <input type="time" name="jam_keluar" required><br><br>
        
                <input type="submit" name="presensi" value="Simpan Presensi">
            </form>
            <?php
            // Menampilkan Data Presensi
            $nip = $_SESSION['nip'];
            $result = mysqli_query($conn, "SELECT * FROM presensi WHERE nip = '$nip' ORDER BY tanggal DESC");
        
            echo "<h3>Riwayat Presensi</h3>";
            if (mysqli_num_rows($result) > 0) {
                echo "<table>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                        </tr>";
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['tanggal']}</td>
                            <td>{$row['jam_masuk']}</td>
                            <td>{$row['jam_keluar']}</td>
                        </tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Belum ada data presensi.</p>";
            }
        
        } elseif ($menu == 'keahlian') {
            echo "<h2>Tambah Keahlian</h2>";
            if (isset($_POST['tambah_keahlian'])) {
                $nip = $_SESSION['nip'];
                $keahlian = $_POST['keahlian'];
                mysqli_query($conn, "INSERT INTO keahlian (nip, keahlian) VALUES ('$nip', '$keahlian')");
                echo "Keahlian berhasil ditambahkan!";
            }
            ?>
            <form method="post" action="">
                <label>Keahlian:</label>
                <input type="text" name="keahlian" required><br><br>
                <input type="submit" name="tambah_keahlian" value="Tambah Keahlian">
            </form>
            <?php
        } else {
            echo "<h2>Selamat Datang di Dashboard</h2>";
            echo "<p>Silakan pilih menu di atas.</p>";
        }
        ?>
    </section>
</body>
</html>
