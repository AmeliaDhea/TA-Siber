async function getEvaluasiKinerja(event) {
    event.preventDefault(); // Cegah pengiriman form default

    const guruID = document.getElementById("guruID").value;
    const tanggalMulai = document.getElementById("tanggalMulai").value;
    const tanggalAkhir = document.getElementById("tanggalAkhir").value;

    // URL API
    const url = `http://localhost/wsguru/server.php?guruID=${guruID}&tanggalMulai=${tanggalMulai}&tanggalAkhir=${tanggalAkhir}`;

    try {
        // Mengirim permintaan API
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }
        const json = await response.json();

        // Menampilkan hasil evaluasi
        const resultDiv = document.getElementById("result");
        resultDiv.innerHTML = `
            <p>Guru ID: ${json.data.guruID}</p>
            <p>Total Durasi: ${Math.floor(json.data.totalDurasi / 3600)} jam ${Math.floor((json.data.totalDurasi % 3600) / 60)} menit</p>
            <p>Status Kinerja: <span class="status">${json.data.kinerja.status}</span></p>
            <p>Rekomendasi: <span class="recommendation">${json.data.kinerja.recommendation}</span></p>
        `;
    } catch (error) {
        console.error("Error:", error.message);
        alert("Terjadi kesalahan saat mengambil data: " + error.message);
    }
}

// Menambahkan event listener untuk menangani pengiriman form
document.getElementById("evaluasiForm").addEventListener("submit", getEvaluasiKinerja);
