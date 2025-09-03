<?php
// Simple index file
http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    'message' => 'Kato App Backend API',
    'status' => 'running',
    'timestamp' => date('c'),
    'version' => '1.0.0'
]);
exit;