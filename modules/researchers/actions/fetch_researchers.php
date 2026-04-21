<?php
// Include the core database class
include('../../../core/rms.php');

// Initialize the RMS object
$object = new rms();

// Capture the search term from the Select2 AJAX request
$search = "";
if (isset($_POST['search'])) {
    $search = $object->clean_input($_POST['search']);
}

// Prepare the query to search by first name or family name
// We limit to 10 for performance and only fetch active researchers (status = 1)
$object->query = "
    SELECT id, firstName, familyName 
    FROM tbl_researchdata 
    WHERE (firstName LIKE '%".$search."%' OR familyName LIKE '%".$search."%') 
    AND status = 1 
    ORDER BY familyName ASC 
    LIMIT 10
";

$result = $object->get_result();
$data = [];

foreach($result as $row) {
    // Combine names for display
    $fullName = htmlspecialchars($row['firstName'] . ' ' . $row['familyName']);
    
    // Select2 requires an 'id' and 'text' field in the JSON response
    $data[] = [
        'id'   => $fullName, // Using name as ID to match legacy comma-separated string format
        'text' => $fullName
    ];
}

// Return the properly formatted JSON
header('Content-Type: application/json');
echo json_encode($data);
?>