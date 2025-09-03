<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'message' => 'Kato App Backend is running',
    'timestamp' => date('c'),
    'version' => '1.0.0'
]);
