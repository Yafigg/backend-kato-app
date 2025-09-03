<?php
// Simple health check that always returns 200
http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    'status' => 'healthy',
    'timestamp' => date('c'),
    'version' => '1.0.0'
]);
exit;
