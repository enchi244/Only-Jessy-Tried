<?php
// actions/researchpolicy_action.php
include('../../../core/rms.php');
$object = new rms();

if(isset($_POST["action_policy"])) {
    
    // --- FETCH DROPDOWN FOR RESEARCHES ---
    if($_POST["action_policy"] == 'fetch_researches') {
        $rid = $_POST['researcher_id'];
        $object->query = "SELECT id, title FROM tbl_researchconducted WHERE researcherID = '$rid' AND status = 1";
        $result = $object->get_result();
        
        $html = '<option value="">Select a Research (Optional)</option>';
        foreach($result as $row) {
            $html .= '<option value="'.$row["id"].'">'.strtoupper($row["title"]).'</option>';
        }
        echo $html;
        exit;
    }

    // --- ADD ---
    if($_POST["action_policy"] == 'Add') {
        $rc_id = (!empty($_POST["research_conducted_id"])) ? (int)$_POST["research_conducted_id"] : NULL;
        $data = array(
            ':researcherID'          => $_POST["researcher_id"],
            ':title'                 => strtoupper($_POST["title"]),
            ':abstract'              => $_POST["abstract"],
            ':description'           => $_POST["description"],
            ':research_conducted_id' => $rc_id,
            ':date_implemented'      => $_POST["date_implemented"]
        );

        $object->query = "
        INSERT INTO tbl_research_policy 
        (researcherID, title, abstract, description, research_conducted_id, date_implemented, status) 
        VALUES (:researcherID, :title, :abstract, :description, :research_conducted_id, :date_implemented, 1)
        ";
        $object->execute($data);
        
        $policy_id = $object->connect->lastInsertId();

        // PHASE 2 DRY IMPLEMENTATION: Use the generic file handler
        if (isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            $object->handle_generic_files($_FILES['research_files'], $categories, $policy_id, '../../../uploads/documents/', 'uploads/documents/', 'policy');
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // --- FETCH SINGLE (Populates Edit Modal & Existing Files) ---
    if($_POST["action_policy"] == 'fetch_single') {
        $policy_id = intval($_POST["id"]);
        
        $object->query = "SELECT * FROM tbl_research_policy WHERE id = '$policy_id'";
        $result = $object->get_result();
        $data = array();
        foreach($result as $row) {
            $data['title'] = $row['title'];
            $data['abstract'] = $row['abstract'];
            $data['description'] = $row['description'];
            $data['research_conducted_id'] = $row['research_conducted_id'];
            $data['date_implemented'] = $row['date_implemented'];
        }

        // PHASE 2 DRY IMPLEMENTATION: Fetch from the unified file table
        $object->query = "SELECT id, file_category, file_name, file_path FROM tbl_rde_files WHERE entity_id = '$policy_id' AND entity_type = 'policy'";
        $files_result = $object->get_result();
        
        $data['existing_files'] = [];
        foreach($files_result as $f) {
            $data['existing_files'][] = [
                'id' => $f['id'],
                'category' => htmlspecialchars_decode($f['file_category']),
                'name' => htmlspecialchars_decode($f['file_name']),
                'path' => '../../' . $f['file_path']
            ];
        }
        echo json_encode($data);
        exit;
    }

    // --- EDIT ---
    if($_POST["action_policy"] == 'Edit') {
        $policy_id = intval($_POST['hidden_id_policy']);
        $rc_id = (!empty($_POST["research_conducted_id"])) ? (int)$_POST["research_conducted_id"] : NULL;
        
        $data = array(
            ':title'                 => strtoupper($_POST["title"]),
            ':abstract'              => $_POST["abstract"],
            ':description'           => $_POST["description"],
            ':research_conducted_id' => $rc_id,
            ':date_implemented'      => $_POST["date_implemented"],
            ':id'                    => $policy_id
        );

        $object->query = "
        UPDATE tbl_research_policy 
        SET title = :title, 
            abstract = :abstract, 
            description = :description, 
            research_conducted_id = :research_conducted_id, 
            date_implemented = :date_implemented 
        WHERE id = :id
        ";
        $object->execute($data);

        // PHASE 2 DRY IMPLEMENTATION: Use the generic file handler
        if (isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            $object->handle_generic_files($_FILES['research_files'], $categories, $policy_id, '../../../uploads/documents/', 'uploads/documents/', 'policy');
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // --- DELETE INDIVIDUAL FILE ---
    if($_POST["action_policy"] == 'delete_file') {
        // PHASE 2 DRY IMPLEMENTATION: Use the dynamic one-liner delete
        $success = $object->delete_generic_file($_POST['file_id'], '../../../');
        if($success) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        exit;
    }

    // --- DELETE MAIN RECORD ---
    if($_POST["action_policy"] == 'delete') {
        $object->query = "UPDATE tbl_research_policy SET status = 0 WHERE id = '".intval($_POST["id"])."'";
        $object->execute();
        echo json_encode(['success' => true]);
        exit;
    }
}
?>