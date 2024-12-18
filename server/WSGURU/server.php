<?php
header('Content-Type: application/json');

/**
 * @param int $guruID 
 * @param string $tanggalMulai 
 * @param string $tanggalAkhir 
 */
$guruID = isset($_GET['guruID']) ? (int)$_GET['guruID'] : 0;
$tanggalMulai = isset($_GET['tanggalMulai']) ? $_GET['tanggalMulai'] : '';
$tanggalAkhir = isset($_GET['tanggalAkhir']) ? $_GET['tanggalAkhir'] : '';

/**
 * @param string $tanggalMulai 
 * @param string $tanggalAkhir 
 * @return array 
 */
function getRekomendasiByTanggal($tanggalMulai, $tanggalAkhir) {
    $tanggalMulaiTimestamp = strtotime($tanggalMulai);
    $tanggalAkhirTimestamp = strtotime($tanggalAkhir);
    
    $selisihDurasi = $tanggalAkhirTimestamp - $tanggalMulaiTimestamp;
    $durasiHari = $selisihDurasi / (60 * 60 * 24); 

    if ($durasiHari > 90) {
        return [
            'status' => 'Baik',
            'recommendation' => 'Dipertimbangkan untuk promosi'
        ];
    } elseif ($durasiHari >= 60 && $durasiHari <= 90) {
        return [
            'status' => 'Cukup',
            'recommendation' => 'Tidak direkomendasikan untuk promosi'
        ];
    } else {
        return [
            'status' => 'Tidak Baik',
            'recommendation' => 'Harus diperbaiki'
        ];
    }
}

if ($guruID && $tanggalMulai && $tanggalAkhir) {
    $tanggalMulaiTimestamp = strtotime($tanggalMulai);
    $tanggalAkhirTimestamp = strtotime($tanggalAkhir);
    $totalDurasiDetik = $tanggalAkhirTimestamp - $tanggalMulaiTimestamp; 

    $kinerja = getRekomendasiByTanggal($tanggalMulai, $tanggalAkhir);

    $response = [
        'status' => 'success',
        'data' => [
            'guruID' => $guruID,
            'totalDurasi' => $totalDurasiDetik, 
            'kinerja' => $kinerja
        ]
    ];
} else {
    $response = ['status' => 'error', 'message' => 'Invalid input'];
}
echo json_encode($response);
?>