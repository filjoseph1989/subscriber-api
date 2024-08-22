<?php



// Set load environment variables from .env file
$rootDir = dirname(__DIR__);
$dotenv = Dotenv\Dotenv::createImmutable($rootDir);
$dotenv->safeLoad();

// Get the request method such get, post, etc
$method = $_SERVER['REQUEST_METHOD'];

// Get the requested URI and remove the query string if it exists
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');
$uriSegments = explode('/', $uri);

// Route the request based on the URI and method
if ($uriSegments[0] === 'ims' && $uriSegments[1] === 'subscriber' && isset($uriSegments[2])) {
    $phoneNumber = $uriSegments[2];

    switch ($method) {
        case 'GET':
            $contact = null;

            $resources = json_decode($resources, true);

            foreach ($resources as $resource) {
                if ($resource['phoneNumber'] == $phoneNumber) {
                    $contact = $resource;
                    break;
                }
            }

            if ($contact) {
                echo json_encode($contact);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Contact not found']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'message' => 'Method not allowed'
            ]);
            break;
    }
} else {
    http_response_code(404);
    echo json_encode(['message' => 'Route not found']);
}