<?php

ini_set('memory_limit', '1024M');
include_once('config.php');
include_once('database.php');
include_once('functions.php');

// Constants
define('API_URL', 'https://api-test.oup.com/uat-swift/v1/Manuscripts');
define('SUBSCRIPTION_KEY', 'd2eaec69ba8c468ea97d0a8fecda4f66');

// Logging function
function logMessage($message) {
    $logFile = __DIR__ . '/application_logger/logs_dir/api.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// 1. Set Headers
header("Access-Control-Allow-Origin: *"); // Allow requests from any domain
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 2. Get the HTTP Method
$method = $_SERVER['REQUEST_METHOD'];

$pdo = getDbConnection();


// 3. Handle the Request
switch ($method) {
    
    case 'POST':
        handlePost($pdo);
        break;
    
    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['message' => 'Method not allowed']);
        break;
}
    
// --- FUNCTIONS ---


function getDbConnection() {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}



function handlePost($pdo) {
    // Authenticate the request
    authenticate($pdo);

    global $generatedXml, $apiResponse;
    // Get raw JSON input
    $jsonInput = file_get_contents("php://input");
    $data = json_decode($jsonInput);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'from' => 'SwiftXMLGenerator API',
            'message' => 'Invalid JSON payload'
        ]);
        return;
    }

    // Validate inputs
    if (!isset($data->doi) || !is_string($data->doi) || empty(trim($data->doi))) {
        http_response_code(400);
        echo json_encode([
            'from' => 'SwiftXMLGenerator API',
            'message' => 'Invalid or missing DOI'
        ]);
        return;
    }

    if (!isset($data->infotype) || !in_array($data->infotype, ['EditorChoice', 'SupplementaryData'])) {
        http_response_code(400);
        echo json_encode([
            'from' => 'SwiftXMLGenerator API',
            'message' => 'Invalid or missing infotype. Allowed: EditorChoice, SupplementaryData'
        ]);
        return;
    }

    if (!isset($data->option) || !in_array($data->option, ['true', 'false'])) {
        http_response_code(400);
        echo json_encode([
            'from' => 'SwiftXMLGenerator API',
            'message' => 'Invalid or missing option. Allowed: true, false'
        ]);
        return;
    }

    $workflow = trim($data->infotype);
    $option = trim($data->option);
    $doi = trim($data->doi);
        
        $data = [];
            if ($workflow === 'EditorChoice') {
                $data['instructions'] = [
                    'EditorChoice' => $option === 'true' ? true : false
                ];
            } elseif ($workflow === 'SupplementaryData') {
                $data['SupplementaryData'] = $option === 'true' ? 'true' : 'false';
            }

            
            if (!empty($doi) && !empty($data)) {
                $generatedXml = createDynamicXml($doi, $data);

                // Send XML to API
                $apiResponse = sendXmlToApi($generatedXml, null, $doi, $workflow);

                logMessage("Request processed successfully for DOI: $doi, Workflow: $workflow, API Response Code: " . ($apiResponse['http_code'] ?? 'N/A'));
            }

            http_response_code(201); // Created
            echo json_encode([
                'from' => 'SwiftXMLGenerator API',
                'message' => $apiResponse]);
}




function createDynamicXml($doi, $data) {
    // Create a new DOMDocument
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;

    // Create the root element with namespace
    $root = $dom->createElementNS('http://oxfordjournals.org/Swift', 'Manuscripts');
    $dom->appendChild($root);

    // Create Manuscript element
    $manuscript = $dom->createElement('Manuscript');
    $manuscript->setAttribute('DigitalObjectID', $doi);
    $root->appendChild($manuscript);

    // Dynamically add elements based on data
    foreach ($data as $key => $value) {
        if ($key === 'instructions' && is_array($value)) {
            // Handle nested instructions
            $instructions = $dom->createElement('Instructions');
            $manuscript->appendChild($instructions);
            foreach ($value as $instrKey => $instrValue) {
                $element = $dom->createElement($instrKey, is_bool($instrValue) ? ($instrValue ? 'true' : 'false') : $instrValue);
                $instructions->appendChild($element);
            }
        } else {
            // Direct elements under Manuscript
            $element = $dom->createElement($key, is_bool($value) ? ($value ? 'true' : 'false') : $value);
            $manuscript->appendChild($element);
        }
    }

    return $dom->saveXML();
}


function sendXmlToApi($xmlString, $apiUrl, $doi, $workflow) {
    $url = $apiUrl ?: API_URL;
    $headers = [
        'Content-Type: application/xml',
        'Ocp-Apim-Subscription-Key: ' . SUBSCRIPTION_KEY
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlString);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing, disable SSL verification

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['error' => 'cURL Error: ' . $error];
    }

    curl_close($ch);

    //store the information for log
    $pdo = getDbConnection();
    $workflowTypes = insertXmlToDatabase($pdo, $doi, $xmlString, $workflow, $response);

    return [
        'http_code' => $httpCode,
        'response' => $response
    ];
}

function insertXmlToDatabase($pdo, $doi, $xmlString, $infoType, $response) {
    try {
        $stmt = $pdo->prepare("INSERT INTO meta_info (infotype, metatype, response, doi) VALUES (:infotype, :metatype, :response, :doi)
                                ON DUPLICATE KEY UPDATE metatype = :metatype");
        $stmt->bindParam(':infotype', $infoType, PDO::PARAM_STR);
        $stmt->bindParam(':metatype', $xmlString, PDO::PARAM_STR);
        $stmt->bindParam(':response', $response, PDO::PARAM_STR);
        $stmt->bindParam(':doi', $doi, PDO::PARAM_STR);
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        return false;
    }
}


function authenticate($pdo) {
    // 1. Get the Authorization Header
    $headers = null;
    
    // Check for Apache or Nginx headers
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { 
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }

    // 2. Extract the Token (Format: "Bearer your_token_here")
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            $token = $matches[1];
        } else {
            // No Bearer format found
            returnUnauthorized("Invalid Token Format");
        }
    } else {
        // No header found
        returnUnauthorized("Authorization Header Missing");
    }

    // 3. Verify against Database
    $stmt = $pdo->prepare("SELECT id FROM api_tokens WHERE token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        returnUnauthorized("Invalid API Key");
    }

    // If we are here, the user is valid. 
    return true; 
}

function returnUnauthorized($msg) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'message' => $msg]);
    exit; // Stop script execution immediately
}




?>