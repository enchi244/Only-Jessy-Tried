<?php
// modules/researchers/actions/extension_action.php
include('../../../core/rms.php');

$object = new rms();

if (isset($_POST["action_ext"])) {

    // Logic for auto-filling from a Base Extension Project
    if ($_POST["action_ext"] == 'fetch_project_info') {
        $proj_id = intval($_POST['project_id']);
        $data = array(
            'proj_lead' => '', 
            'assist_coordinators' => '', 
            'partners' => '', 
            'fund_source' => '', 
            'budget' => '', 
            'target_beneficiaries' => ''
        );

        $object->query = "
            SELECT r.firstName, r.familyName, ep.researcherID, ep.partners, ep.funding_source, ep.approved_budget, ep.target_beneficiaries_communities
            FROM tbl_extension_project_conducted ep
            LEFT JOIN tbl_researchdata r ON ep.researcherID = r.id
            WHERE ep.id = '".$proj_id."'
        ";
        $result = $object->get_result();
        $lead_id = 0;
        foreach($result as $row) {
            $data['proj_lead'] = trim($row['firstName'] . ' ' . $row['familyName']);
            $data['partners'] = $row['partners'];
            $data['fund_source'] = $row['funding_source'];
            $data['budget'] = $row['approved_budget'];
            $data['target_beneficiaries'] = $row['target_beneficiaries_communities'];
            $lead_id = $row['researcherID'];
        }

        $object->query = "SELECT research_id FROM tbl_extension_research_links WHERE extension_id = '".$proj_id."'";
        $links = $object->get_result();
        $coordinators = array();
        
        $research_ids = [];
        foreach($links as $l) { $research_ids[] = $l['research_id']; }

        if (!empty($research_ids)) {
            $in_clause = implode(',', $research_ids);
            $object->query = "
                SELECT DISTINCT r.firstName, r.familyName 
                FROM tbl_research_collaborators rc
                JOIN tbl_researchdata r ON rc.researcher_id = r.id
                WHERE rc.research_id IN (".$in_clause.") AND rc.researcher_id != '".$lead_id."'
            ";
            $collabs = $object->get_result();
            foreach($collabs as $c) {
                $coordinators[] = trim($c['firstName'] . ' ' . $c['familyName']);
            }
        }
        
        $data['assist_coordinators'] = implode(', ', $coordinators);
        echo json_encode($data);
        exit;
    }

    // Associated Table Fetch
    if ($_POST["action_ext"] == 'fetch_associated') {
        $order_column = array('title', 'proj_lead', 'assist_coordinators', 'period_implement', 'budget', 'fund_source', 'target_beneficiaries', 'partners', 'stat');
        $main_query = "SELECT * FROM tbl_ext ";
        $search_query = " WHERE extension_project_id = '" . intval($_POST["project_id"]) . "' AND (status = 1 OR status IS NULL) ";
        
        if (isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"])) {
            $search_value = addslashes($_POST["search"]["value"]);
            $search_query .= "AND (title LIKE '%" . $search_value . "%' OR proj_lead LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY id ASC ";
        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . intval($_POST['start']) . ', ' . intval($_POST['length']) : "";

        $object->query = $main_query . $search_query . $order_query;
        $object->execute();
        $filtered_rows = $object->row_count();
        $object->query .= $limit_query;
        $result = $object->get_result();
        
        $object->query = $main_query . $search_query;
        $object->execute();
        $total_rows = $object->row_count();

        $data = array();
        foreach ($result as $row) {
            $sub_array = array();
            $sub_array[] = htmlspecialchars($row["title"]);
            $sub_array[] = htmlspecialchars($row["proj_lead"]);
            $sub_array[] = htmlspecialchars($row["assist_coordinators"]);
            $sub_array[] = htmlspecialchars($row["period_implement"]);
            $sub_array[] = "₱" . number_format((float)$row["budget"], 2);
            $sub_array[] = htmlspecialchars($row["fund_source"]);
            $sub_array[] = htmlspecialchars($row["target_beneficiaries"]);
            $sub_array[] = htmlspecialchars($row["partners"]);
            $sub_array[] = htmlspecialchars($row["stat"]);
            $sub_array[] = '<div align="center">
                <button type="button" class="btn btn-primary btn-sm edit_button_ext" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button> 
                <button type="button" class="btn btn-danger btn-sm delete_button_ext" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button>
            </div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    // Add New Extension Activity
    if ($_POST["action_ext"] == 'Add') {
        $researcherID = !empty($_POST['hidden_researcherID_ext']) ? intval($_POST['hidden_researcherID_ext']) : null;
        $parent_project_id = !empty($_POST['linked_extension_project']) ? intval($_POST['linked_extension_project']) : null;

        if (!$researcherID || !$parent_project_id) {
            echo json_encode(array('error' => '<div class="alert alert-danger">Error: Researcher ID or Parent Project is missing.</div>'));
            exit;
        }

        $assist_coordinators = "";
        if (isset($_POST["assist_coordinators"]) && is_array($_POST["assist_coordinators"])) {
            $assist_coordinators = implode(", ", $_POST["assist_coordinators"]);
        }

        $data = array(
            ':researcherID'         => $researcherID,
            ':extension_project_id' => $parent_project_id,
            ':title'                => $_POST['title_ext'],
            ':description'          => $_POST['description_ext'],
            ':proj_lead'            => $_POST['proj_lead'],
            ':assist_coordinators'  => $assist_coordinators,
            ':period_implement'     => $_POST['period_implement'],
            ':budget'               => $_POST['budget'],
            ':fund_source'          => $_POST['fund_source'],
            ':target_beneficiaries' => $_POST['target_beneficiaries'],
            ':partners'             => $_POST['partners_ext'],
            ':stat'                 => $_POST['stat_ext'],
            ':attachments'          => 'Legacy Replaced', 
            ':a_link'               => $_POST['a_link_ext'] ?? ''
        );

        $object->query = "
            INSERT INTO tbl_ext (
                researcherID, extension_project_id, title, description, proj_lead, assist_coordinators, 
                period_implement, budget, fund_source, target_beneficiaries, 
                partners, stat, attachments, a_link, status
            ) VALUES (
                :researcherID, :extension_project_id, :title, :description, :proj_lead, :assist_coordinators, 
                :period_implement, :budget, :fund_source, :target_beneficiaries, 
                :partners, :stat, :attachments, :a_link, 1
            )
        ";
        $object->execute($data);
        $new_ext_id = intval($object->connect->lastInsertId());

        // PHASE 3: Universal Engine
        if(isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            $object->handle_generic_files($_FILES['research_files'], $categories, $new_ext_id, '../../../uploads/research_files/', 'uploads/research_files/', 'tbl_ext_files', 'ext_id');
        }

        echo json_encode(array('error' => '', 'success' => '<div class="alert alert-success">Extension Activity Added</div>'));
        exit;
    }

    // Fetch Single for Edit
    if ($_POST["action_ext"] == 'fetch_single') {
        $object->query = "SELECT * FROM tbl_ext WHERE id = '" . intval($_POST["extID"]) . "'";
        $result = $object->get_result();
        $data = array();
        foreach ($result as $row) {
            $data['title'] = htmlspecialchars_decode($row["title"]);
            $data['extension_project_id'] = $row["extension_project_id"];
            $data['description'] = htmlspecialchars_decode($row["description"]);
            $data['proj_lead'] = htmlspecialchars_decode($row["proj_lead"]);
            $data['assist_coordinators'] = htmlspecialchars_decode($row["assist_coordinators"]);
            $data['period_implement'] = htmlspecialchars_decode($row["period_implement"]);
            $data['budget'] = $row["budget"];
            $data['fund_source'] = htmlspecialchars_decode($row["fund_source"]);
            $data['target_beneficiaries'] = htmlspecialchars_decode($row["target_beneficiaries"]);
            $data['partners'] = htmlspecialchars_decode($row["partners"]);
            $data['stat'] = htmlspecialchars_decode($row["stat"]);
            $data['a_link'] = $row["a_link"];
        }

        $object->query = "SELECT id, file_category, file_name, file_path FROM tbl_ext_files WHERE ext_id = '".$_POST["extID"]."'";
        $file_result = $object->get_result();
        $files_array = [];
        foreach($file_result as $f) {
            $files_array[] = array('id' => $f['id'], 'category' => htmlspecialchars_decode($f['file_category']), 'name' => htmlspecialchars_decode($f['file_name']), 'path' => '../../' . $f['file_path']);
        }
        $data['existing_files'] = $files_array;

        echo json_encode($data);
        exit;
    }

    // Update Extension Activity
    if ($_POST["action_ext"] == 'Edit') {
        $ext_id = intval($_POST['hidden_extID']);
        $assist_coordinators = "";
        
        if (isset($_POST["assist_coordinators"]) && is_array($_POST["assist_coordinators"])) {
            $assist_coordinators = implode(", ", $_POST["assist_coordinators"]);
        }

        $data = array(
            ':extension_project_id' => $_POST['linked_extension_project'],
            ':title'                => $_POST['title_ext'],
            ':description'          => $_POST['description_ext'],
            ':proj_lead'            => $_POST['proj_lead'],
            ':assist_coordinators'  => $assist_coordinators,
            ':period_implement'     => $_POST['period_implement'],
            ':budget'               => $_POST['budget'],
            ':fund_source'          => $_POST['fund_source'],
            ':target_beneficiaries' => $_POST['target_beneficiaries'],
            ':partners'             => $_POST['partners_ext'], 
            ':stat'                 => $_POST['stat_ext'],
            ':a_link'               => $_POST['a_link_ext'] ?? '',
            ':hidden_extID'         => $ext_id
        );

        $object->query = "
            UPDATE tbl_ext SET 
                extension_project_id = :extension_project_id,
                title = :title, description = :description, proj_lead = :proj_lead, 
                assist_coordinators = :assist_coordinators, period_implement = :period_implement, 
                budget = :budget, fund_source = :fund_source, target_beneficiaries = :target_beneficiaries, 
                partners = :partners, stat = :stat, a_link = :a_link 
            WHERE id = :hidden_extID
        ";
        $object->execute($data);

        // PHASE 3: Universal Engine
        if(isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            $object->handle_generic_files($_FILES['research_files'], $categories, $ext_id, '../../../uploads/research_files/', 'uploads/research_files/', 'tbl_ext_files', 'ext_id');
        }

        echo json_encode(array('error' => '', 'success' => '<div class="alert alert-success">Extension Activity Updated</div>'));
        exit;
    }

    if($_POST["action_ext"] == 'delete_file') {
        // Because tbl_ext does not use the standard `has_files` column, we handle its deletion safely here
        // to bypass the automatic status sync which would trigger a SQL error.
        $file_id = intval($_POST['file_id']);
        $object->query = "SELECT file_path FROM tbl_ext_files WHERE id = '".$file_id."'";
        $file_data = $object->get_result();
        $file_deleted = false;
        
        foreach($file_data as $row) {
            $file_deleted = true;
            $physical_path = '../../../' . $row['file_path'];
            if(file_exists($physical_path)) { unlink($physical_path); }
        }
        
        if($file_deleted) {
            $object->query = "DELETE FROM tbl_ext_files WHERE id = '".$file_id."'";
            $object->execute();
            echo json_encode(['status' => 'success', 'message' => 'File deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found.']);
        }
        exit;
    }

    if ($_POST["action_ext"] == 'delete') {
        $ext_id = intval($_POST["extID"]);
        $object->query = "UPDATE tbl_ext SET status = 0 WHERE id = '" . $ext_id . "'";
        $object->execute();
        echo json_encode(['status' => 'success', 'message' => 'Extension Activity moved to Recycle Bin']);
        exit;
    }
}
?>