<?php


include("../apanel/connect.php");
include("./include/functions.php");
include("../include/notification.class.php");
include("../sql-injection/sql-injection.php");
include('jwt_helper.php');


/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', '1'); */

$method         = $_SERVER['REQUEST_METHOD'];
$requestUrl     = $_SERVER['REQUEST_URI'];


$action = basename($requestUrl) ?? null; 

if (!empty($action) && ($action == 'login' || $action == 'logout')) {
    if ($method === 'POST') {
        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput);
        if ($data === null) {
            sendResponse(400, "Invalid JSON");
        }

        switch ($action) {
            case 'login':
                handleLogin($data);
                break;
            case 'logout':
                handleLogout();
                break;
            default:
                sendResponse(400, "Invalid action");
                break;
        }
    } else {
        sendResponse(405, "Method Not Allowed");
    }
} else {
    sendResponse(400, "Action is required");
}

function handleLogin($data) {

    if (!isset($data->email) || !isset($data->password)) {
        sendResponse(400, "Email and password are required");
    }

    $email = $data->email;
    $password = $data->password;
    $db = new Admin();
    $userInfo = $db->rpGetSingleRecord('admin', '*', "isDelete=0 and active_account=1 and email='".$email."'");

    if ($userInfo) {
        $userEmail = $userInfo["email"];
        $hashedPassword = $userInfo["password"];
        
        if (md5($password) == $hashedPassword) {
            $token = generateJWT($userInfo['id']);
            $_SESSION[SESS_PRE.'_JWT_TOKEN'] = $token;
            $data = [
                "name" => $userInfo["first_name"] . ' ' . $userInfo["last_name"],
                "email" => $userEmail,
                "token" => $token
            ];
            sendResponse(200, 'User authenticated successfully', $data);
        } else {
            sendResponse(401, 'Incorrect password');
        }
    } else {
        sendResponse(404, 'User not found');
    }
}

function handleLogout() {
    sendResponse(200, ["message" => "Logout successful"]);
}

function sendResponse($statusCode, $message, $data = null) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $statusCode < 400,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}