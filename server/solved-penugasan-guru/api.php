<?php

class AssignmentAPI
{
    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method == 'POST') {
            $this->assignTeacher();
        } elseif ($method == 'GET') {
            $this->getAssignedTeachers();
        } else {
            header("HTTP/1.1 405 Method Not Allowed");
            echo json_encode(["message" => "Method Not Allowed"]);
        }
    }
    private function assignTeacher()
    {
        $inputData = json_decode(file_get_contents('php://input'), true);

        if (isset($inputData['name']) && isset($inputData['expertise'])) {
            $_SESSION['teachers'][] = [
                'name' => $inputData['name'],
                'expertise' => $inputData['expertise']
            ];
            header("HTTP/1.1 200 OK");
            echo json_encode(["message" => "Mata pelajaran guru telah disimpan."]);
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(["message" => "Invalid input"]);
        }
    }

    private function getAssignedTeachers()
    {
        if (isset($_SESSION['teachers']) && !empty($_SESSION['teachers'])) {
            echo json_encode($_SESSION['teachers']);
        } else {
            echo json_encode(["message" => "Tidak ada guru yang idtemukan untuk mata pelajaran ini."]);
        }
    }
}
