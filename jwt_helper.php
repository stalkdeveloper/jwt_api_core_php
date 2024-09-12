<?php
require '../vendor/autoload.php';

use \Firebase\JWT\JWT;

// $secretKey = getenv('JWT_SECRET_KEY') ? : 'fallback-secret-key';
$secretKey = '';
function generateJWT($userId) {
    global $secretKey;

    $payload = [
        'iss' => 'sukuma.biz',
        'aud' => 'sukuma',
        'iat' => time(),
        'exp' => time() + 60 * 60,
        'sub' => $userId,
    ];

    return JWT::encode($payload, $secretKey, 'HS256');
}

function verifyToken($token) {
    global $secretKey;

    try {
        $decoded = JWT::decode($token, $secretKey, ['HS256']);
        return $decoded;
    } catch (\Firebase\JWT\ExpiredException $e) {
        http_response_code(401);
        echo json_encode(['message' => 'Token has expired']);
        exit;
    } catch (Exception $e) {
        http_response_code(403); 
        echo json_encode(['message' => 'Invalid token']);
        exit;
    }
}
