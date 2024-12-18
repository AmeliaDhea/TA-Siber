<?php

function validateSoapRequest($guruID, $tanggalMulai, $tanggalAkhir) {
    // Validasi guruID (pastikan berupa integer)
    if (empty($guruID) || !is_numeric($guruID)) {
        throw new Exception('Guru ID tidak valid atau tidak ditemukan.');
    }

    // Validasi tanggalMulai dan tanggalAkhir (pastikan tidak kosong)
    if (empty($tanggalMulai) || empty($tanggalAkhir)) {
        throw new Exception('Tanggal mulai atau tanggal akhir tidak boleh kosong.');
    }

    // Validasi apakah tanggalMulai lebih kecil dari tanggalAkhir
    if (strtotime($tanggalMulai) > strtotime($tanggalAkhir)) {
        throw new Exception('Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
    }
}
