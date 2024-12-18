<?php
$client = new SoapClient("http://localhost/wsguru/service.wsdl");

// Parameter untuk metode SOAP
$params = [
    'guruID' => 123,
    'tanggalMulai' => '2024-01-01T00:00:00',
    'tanggalAkhir' => '2024-12-31T23:59:59'
];

try {
    // Memanggil metode SOAP
    $response = $client->__soapCall("getEvaluasiKinerja", [$params]);
    echo "<pre>";
    print_r($response);
    echo "</pre>";
} catch (SoapFault $e) {
    echo "SOAP Error: {$e->getMessage()}";
}
