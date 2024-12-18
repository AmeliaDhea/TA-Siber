
<?php

class Middleware {
    /**
     * @param array $requestData
     * @return array
     */
    public function handleRequest($requestData) {
        if (empty($requestData['guruID']) || empty($requestData['tanggalMulai']) || empty($requestData['tanggalAkhir'])) {
            return ['status' => 'error', 'message' => 'Input tidak valid'];
        }

        $xmlRequest = $this->jsonToXml($requestData);

        $response = $this->sendToWebService($xmlRequest);

        $jsonResponse = $this->xmlToJson($response);

        return $jsonResponse;
    }

    /**
     * @param array $data
     * @return string
     */
    private function jsonToXml($data) {
        $xml = new SimpleXMLElement('<Request/>');
        foreach ($data as $key => $value) {
            $xml->addChild($key, $value);
        }
        return $xml->asXML();
    }

    /**
     * @param string $xmlRequest
     * @return string
     */
    private function sendToWebService($xmlRequest) {
        $url = "https://example.com/webservice"; 
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * @param string $xml
     * @return array
     */
    private function xmlToJson($xml) {
        $xmlObject = simplexml_load_string($xml);
        $json = json_encode($xmlObject);
        return json_decode($json, true);
    }
}
