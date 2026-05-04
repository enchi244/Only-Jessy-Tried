<?php
// actions/settings_action.php
include('../core/rms.php');
$object = new rms();

if (isset($_POST["action"])) {
    if ($_POST["action"] == 'delete_discipline') {
        $id = intval($_POST["id"]);

        // Basic validation
        if ($id > 0) {
            // Check if the discipline is currently in use by any researchers
            // It's good practice to prevent deleting data that is referenced elsewhere
            $object->query = "SELECT COUNT(*) as use_count FROM tbl_researchdata WHERE program = (SELECT major FROM tbl_majordiscipline WHERE majorID = :id)";
            $object->execute([':id' => $id]);
            $use_check = $object->statement->fetch(PDO::FETCH_ASSOC);

            if ($use_check['use_count'] > 0) {
                // Discipline is in use, refuse deletion
                echo json_encode(['status' => 'error', 'message' => 'Cannot delete this discipline because it is currently assigned to one or more researchers.']);
            } else {
                // Safe to delete
                $object->query = "DELETE FROM tbl_majordiscipline WHERE majorID = :id";
                try {
                    $object->execute([':id' => $id]);
                    echo json_encode(['status' => 'success', 'message' => 'Discipline deleted successfully.']);
                } catch (PDOException $e) {
                     echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
                }
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Discipline ID.']);
        }
    }
}
?>