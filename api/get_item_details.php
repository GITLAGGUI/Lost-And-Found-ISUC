<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get parameters
$itemId = $_GET['id'] ?? null;
$type = $_GET['type'] ?? null;

if (!$itemId || !is_numeric($itemId) || !in_array($type, ['lost', 'found'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

try {
    if ($type === 'lost') {
        $item = $db->getLostItemById($itemId);
        $dateField = 'date_lost';
        $dateLabel = 'Lost';
    } else {
        $item = $db->getFoundItemById($itemId);
        $dateField = 'date_found';
        $dateLabel = 'Found';
    }

    if (!$item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Item not found']);
        exit;
    }

    // Format the response
    $response = [
        'success' => true,
        'item' => [
            'id' => $item['id'],
            'item_name' => $item['item_name'],
            'description' => $item['description'],
            'location' => $item['location'],
            'category' => $item['category'],
            'image_path' => $item['image_path'],
            'contact_email' => $item['contact_email'],
            'contact_phone' => $item['contact_phone'],
            'username' => $item['username'],
            'status' => status_chip($item['status']),
            'date_formatted' => format_date($item[$dateField])
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log('Error fetching item details: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>