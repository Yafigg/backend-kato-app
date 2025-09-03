<?php
// Simple health check
http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'timestamp' => date('c'),
    'version' => '1.0.0'
]);
exit;