<?php
// actions/log_view.php
include('../../../core/rms.php');
$object = new rms();

// Ensure it's a POST request and values are provided
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id']) && isset($_POST['item_type'])) {
    
    $item_id = (int)$_POST['item_id'];
    // Clean string just in case to prevent injection
    $item_type = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['item_type']); 

    if ($item_id > 0 && !empty($item_type)) {
        // Insert a new row indicating this specific card was viewed right now
        $object->query = "INSERT INTO tbl_rde_views (item_id, item_type) VALUES (:item_id, :item_type)";
        $object->execute([
            ':item_id' => $item_id,
            ':item_type' => $item_type
        ]);

        echo json_encode(['status' => 'success', 'message' => 'View recorded.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bad request.']);
}
?>