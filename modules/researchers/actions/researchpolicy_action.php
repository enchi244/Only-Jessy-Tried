<?php
include('../../../core/rms.php');
$object = new rms();

if(isset($_POST["action_policy"]))
{
    // --- FETCH DROPDOWN FOR RESEARCHES ---
    if($_POST["action_policy"] == 'fetch_researches')
    {
        $rid = $_POST['researcher_id'];
        $object->query = "SELECT id, title FROM tbl_researchconducted WHERE researcherID = '$rid' AND status = 1";
        $result = $object->get_result();
        
        $html = '<option value="">Select a Research (Optional)</option>';
        foreach($result as $row) {
            $html .= '<option value="'.$row["id"].'">'.strtoupper($row["title"]).'</option>';
        }
        echo $html;
    }

    // --- ADD ---
    if($_POST["action_policy"] == 'Add')
    {
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

        // Safely Handle Dynamic Multi-Files Array (ADDED EXTRA ../ TO PATH)
        if (isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && count($_FILES['research_files']['name']) > 0) {
            for ($i = 0; $i < count($_FILES['research_files']['name']); $i++) {
                if (!empty($_FILES['research_files']['name'][$i])) {
                    $category = $_POST['file_categories'][$i] ?? 'Other';
                    $extension = pathinfo($_FILES['research_files']['name'][$i], PATHINFO_EXTENSION);
                    $new_file_name = 'policy_' . time() . '_' . rand(100,999) . '.' . $extension;
                    
                    // FIX: Go up 3 levels from actions/ to reach root uploads/
                    move_uploaded_file($_FILES['research_files']['tmp_name'][$i], '../../../uploads/documents/' . $new_file_name);
                    
                    $file_data = [
                        ':policy_id' => $policy_id,
                        ':file_name' => $new_file_name,
                        ':category'  => $category
                    ];
                    $object->query = "INSERT INTO tbl_policy_files (policy_id, file_name, category) VALUES (:policy_id, :file_name, :category)";
                    $object->execute($file_data);
                }
            }
        }

        echo json_encode(['success' => true]);
    }

    // --- FETCH SINGLE (Populates Edit Modal & Existing Files) ---
    if($_POST["action_policy"] == 'fetch_single')
    {
        $policy_id = $_POST["id"];
        
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

        $object->query = "SELECT * FROM tbl_policy_files WHERE policy_id = '$policy_id'";
        $files_result = $object->get_result();
        $data['existing_files'] = [];
        foreach($files_result as $f) {
            $data['existing_files'][] = [
                'id' => $f['id'],
                'category' => $f['category'],
                'name' => $f['file_name']
            ];
        }

        echo json_encode($data);
    }

    // --- EDIT ---
    if($_POST["action_policy"] == 'Edit')
    {
        $policy_id = $_POST['hidden_id_policy'];
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

        // Handle newly appended Multi-Files Array (ADDED EXTRA ../ TO PATH)
        if (isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && count($_FILES['research_files']['name']) > 0) {
            for ($i = 0; $i < count($_FILES['research_files']['name']); $i++) {
                if (!empty($_FILES['research_files']['name'][$i])) {
                    $category = $_POST['file_categories'][$i] ?? 'Other';
                    $extension = pathinfo($_FILES['research_files']['name'][$i], PATHINFO_EXTENSION);
                    $new_file_name = 'policy_' . time() . '_' . rand(100,999) . '.' . $extension;
                    
                    // FIX: Go up 3 levels from actions/ to reach root uploads/
                    move_uploaded_file($_FILES['research_files']['tmp_name'][$i], '../../../uploads/documents/' . $new_file_name);
                    
                    $file_data = [
                        ':policy_id' => $policy_id,
                        ':file_name' => $new_file_name,
                        ':category'  => $category
                    ];
                    $object->query = "INSERT INTO tbl_policy_files (policy_id, file_name, category) VALUES (:policy_id, :file_name, :category)";
                    $object->execute($file_data);
                }
            }
        }

        echo json_encode(['success' => true]);
    }

    // --- DELETE INDIVIDUAL FILE ---
    if($_POST["action_policy"] == 'delete_file')
    {
        $file_id = $_POST['file_id'];
        
        $object->query = "SELECT file_name FROM tbl_policy_files WHERE id = :id";
        $object->execute([':id' => $file_id]);
        $res = $object->statement->fetch(PDO::FETCH_ASSOC);
        
        // FIX: Go up 3 levels for the delete action too
        if($res && file_exists('../../../uploads/documents/'.$res['file_name'])) {
            unlink('../../../uploads/documents/'.$res['file_name']);
        }
        
        $object->query = "DELETE FROM tbl_policy_files WHERE id = :id";
        $object->execute([':id' => $file_id]);
        
        echo json_encode(['status' => 'success']);
    }

    // --- DELETE MAIN RECORD ---
    if($_POST["action_policy"] == 'delete')
    {
        $object->query = "UPDATE tbl_research_policy SET status = 0 WHERE id = '".$_POST["id"]."'";
        $object->execute();
        echo json_encode(['success' => true]);
    }
}
?>