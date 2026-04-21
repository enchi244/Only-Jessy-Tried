<?php
// extension_action.php
include('../../../core/rms.php');

$object = new rms();

if (isset($_POST["action_ext"])) {

    // Logic for auto-filling from a Base Extension Project
    if ($_POST["action_ext"] == 'fetch_project_info') {
        $proj_id = intval($_POST['project_id']);
        $data = array('proj_lead' => '', 'assist_coordinators' => '');

        // 1. Fetch the main leader from the Extension Project
        $object->query = "
            SELECT r.firstName, r.familyName, ep.researcherID 
            FROM tbl_extension_project_conducted ep
            LEFT JOIN tbl_researchdata r ON ep.researcherID = r.id
            WHERE ep.id = '".$proj_id."'
        ";
        $result = $object->get_result();
        $lead_id = 0;
        foreach($result as $row) {
            $data['proj_lead'] = trim($row['firstName'] . ' ' . $row['familyName']);
            $lead_id = $row['researcherID'];
        }

        // 2. Fetch Assistant Coordinators from linked Research Projects
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

    // Master Table Fetch
    if ($_POST["action_ext"] == 'fetch_all') {
        $order_column = array('tbl_researchdata.familyName', 'tbl_ext.title', 'tbl_ext.proj_lead', 'tbl_ext.period_implement', 'tbl_ext.budget');
        $main_query = "SELECT tbl_ext.*, tbl_researchdata.id AS author_db_id, tbl_researchdata.firstName, tbl_researchdata.familyName, tbl_researchdata.middleName, tbl_researchdata.Suffix, tbl_researchdata.academic_rank, tbl_researchdata.program AS primary_discipline FROM tbl_ext LEFT JOIN tbl_researchdata ON tbl_ext.researcherID = tbl_researchdata.id";
        $search_query = " WHERE tbl_ext.status = 1 "; 
        
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (tbl_ext.title LIKE '%" . $search_value . "%' OR tbl_researchdata.familyName LIKE '%" . $search_value . "%') ";
        }
        
        if (isset($_POST["order"]) && isset($order_column[$_POST["order"]["0"]["column"]])) {
            $order_query = "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " ";
        } else {
            $order_query = "ORDER BY tbl_ext.id DESC ";
        }
        
        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'] : "";

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
            $author_name = $row["familyName"] ? htmlspecialchars($row["familyName"].", ".$row["firstName"]." ".trim($row["middleName"]." ".$row["Suffix"])) : "Unknown Author";
            $rank_badge = !empty($row["academic_rank"]) ? '<span class="badge badge-success px-2 py-1 ml-1 align-text-top" style="font-size:0.65rem;"><i class="fas fa-award"></i> ' . htmlspecialchars($row["academic_rank"]) . '</span>' : '';
            $discipline_badge = !empty($row["primary_discipline"]) ? '<div class="small text-muted mt-1"><i class="fas fa-book-reader mr-1"></i> ' . htmlspecialchars($row["primary_discipline"]) . '</div>' : '';
            
            $sub_array[] = '<div class="mb-1"><span class="font-weight-bold text-gray-800">'.$author_name.'</span>'.$rank_badge.'</div>'.$discipline_badge;
            $sub_array[] = $row["title"];
            $sub_array[] = $row["proj_lead"];
            $sub_array[] = $row["period_implement"];
            $sub_array[] = "₱" . number_format((float)$row["budget"], 2);
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-danger btn-sm delete_master_ext" data-id="'.$row["id"].'" title="Delete"><i class="far fa-trash-alt"></i></button></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    // Associated Table Fetch
    if ($_POST["action_ext"] == 'fetch_associated') {
        $order_column = array('tbl_ext.title', 'tbl_ext.proj_lead', 'tbl_ext.assist_coordinators', 'tbl_ext.period_implement', 'tbl_ext.budget', 'tbl_ext.fund_source', 'tbl_ext.target_beneficiaries', 'tbl_ext.partners', 'tbl_ext.stat');
        $main_query = "SELECT tbl_ext.* FROM tbl_ext INNER JOIN tbl_extension_activity_links ON tbl_ext.id = tbl_extension_activity_links.extension_activity_id ";
        $search_query = " WHERE tbl_extension_activity_links.extension_project_id = '" . intval($_POST["project_id"]) . "' AND (tbl_ext.status = 1 OR tbl_ext.status IS NULL) ";
        
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (tbl_ext.title LIKE '%" . $search_value . "%' OR tbl_ext.proj_lead LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY tbl_ext.id ASC ";
        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'] : "";

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
            $sub_array[] = $row["title"];
            $sub_array[] = $row["proj_lead"];
            $sub_array[] = $row["assist_coordinators"];
            $sub_array[] = $row["period_implement"];
            $sub_array[] = "₱" . number_format((float)$row["budget"], 2);
            $sub_array[] = $row["fund_source"];
            $sub_array[] = $row["target_beneficiaries"];
            $sub_array[] = $row["partners"];
            $sub_array[] = $row["stat"];
            $sub_array[] = '<div align="center">
                <button type="button" class="btn btn-primary btn-sm edit_button_ext" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button> 
                <button type="button" class="btn btn-danger btn-sm delete_button_ext" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button>
            </div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    // Add New Extension
    if ($_POST["action_ext"] == 'Add') {
        $error = ''; $success = '';
        
        $attachment_name = '';
        if(isset($_FILES['attachments_ext']) && $_FILES['attachments_ext']['name'] != '') {
            $attachment_name = 'ext_att_' . time() . '_' . rand(100, 999) . '.' . pathinfo($_FILES['attachments_ext']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['attachments_ext']['tmp_name'], '../../../uploads/documents/' . $attachment_name);
        }

        $researcherID = !empty($_POST['hidden_researcherID_ext']) ? $_POST['hidden_researcherID_ext'] : null;

        if (!$researcherID) {
            echo json_encode(array('error' => '<div class="alert alert-danger">Error: Researcher ID is missing.</div>'));
            exit;
        }

        // Handle Select2 Multi-Select for Assistant Coordinators
        $assist_coordinators = "";
        if (isset($_POST["assist_coordinators"]) && is_array($_POST["assist_coordinators"])) {
            $assist_coordinators = implode(", ", $_POST["assist_coordinators"]);
        }

        $data = array(
            ':researcherID'         => $researcherID,
            ':title'                => $object->clean_input($_POST['title_ext']),
            ':description'          => $object->clean_input($_POST['description_ext']),
            ':proj_lead'            => $object->clean_input($_POST['proj_lead']),
            ':assist_coordinators'  => $assist_coordinators,
            ':period_implement'     => $object->clean_input($_POST['period_implement']),
            ':budget'               => $object->clean_input($_POST['budget']),
            ':fund_source'          => $object->clean_input($_POST['fund_source']),
            ':target_beneficiaries' => $object->clean_input($_POST['target_beneficiaries']),
            ':partners'             => $object->clean_input($_POST['partners']),
            ':stat'                 => $object->clean_input($_POST['stat_ext']),
            ':attachments'          => $attachment_name,
            ':a_link'               => $object->clean_input($_POST['a_link_ext'] ?? '')
        );

        $object->query = "
            INSERT INTO tbl_ext (
                researcherID, title, description, proj_lead, assist_coordinators, 
                period_implement, budget, fund_source, target_beneficiaries, 
                partners, stat, attachments, a_link, status
            ) VALUES (
                :researcherID, :title, :description, :proj_lead, :assist_coordinators, 
                :period_implement, :budget, :fund_source, :target_beneficiaries, 
                :partners, :stat, :attachments, :a_link, 1
            )
        ";
        $object->execute($data);
        $new_ext_id = $object->connect->lastInsertId();

        $link_project_id = !empty($_POST['hidden_parent_project_id']) ? $_POST['hidden_parent_project_id'] : (!empty($_POST['linked_extension_project']) ? $_POST['linked_extension_project'] : null);
        if(!empty($link_project_id)) {
            $object->query = "INSERT INTO tbl_extension_activity_links (extension_activity_id, extension_project_id) VALUES ('".$new_ext_id."', '".intval($link_project_id)."')";
            $object->execute();
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
            $data['title'] = $row["title"];
            $data['description'] = $row["description"];
            $data['proj_lead'] = $row["proj_lead"];
            $data['assist_coordinators'] = $row["assist_coordinators"];
            $data['period_implement'] = $row["period_implement"];
            $data['budget'] = $row["budget"];
            $data['fund_source'] = $row["fund_source"];
            $data['target_beneficiaries'] = $row["target_beneficiaries"];
            $data['partners'] = $row["partners"];
            $data['stat'] = $row["stat"];
            $data['a_link'] = $row["a_link"];
            $data['attachments'] = $row["attachments"];
        }
        echo json_encode($data);
        exit;
    }

    // Update Extension
    if ($_POST["action_ext"] == 'Edit') {
        $ext_id = intval($_POST['hidden_extID']);
        $attachment_name = $_POST['hidden_existing_attachment'] ?? '';

        if(isset($_FILES['attachments_ext']) && $_FILES['attachments_ext']['name'] != '') {
            if(!empty($attachment_name) && file_exists('../../../uploads/documents/' . $attachment_name)) {
                unlink('../../../uploads/documents/' . $attachment_name);
            }
            $attachment_name = 'ext_att_' . time() . '_' . rand(100, 999) . '.' . pathinfo($_FILES['attachments_ext']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['attachments_ext']['tmp_name'], '../../../uploads/documents/' . $attachment_name);
        }

        $assist_coordinators = "";
        if (isset($_POST["assist_coordinators"]) && is_array($_POST["assist_coordinators"])) {
            $assist_coordinators = implode(", ", $_POST["assist_coordinators"]);
        }

        $data = array(
            ':title'                => $object->clean_input($_POST['title_ext']),
            ':description'          => $object->clean_input($_POST['description_ext']),
            ':proj_lead'            => $object->clean_input($_POST['proj_lead']),
            ':assist_coordinators'  => $assist_coordinators,
            ':period_implement'     => $object->clean_input($_POST['period_implement']),
            ':budget'               => $object->clean_input($_POST['budget']),
            ':fund_source'          => $object->clean_input($_POST['fund_source']),
            ':target_beneficiaries' => $object->clean_input($_POST['target_beneficiaries']),
            ':partners'             => $object->clean_input($_POST['partners']),
            ':stat'                 => $object->clean_input($_POST['stat_ext']),
            ':attachments'          => $attachment_name,
            ':a_link'               => $object->clean_input($_POST['a_link_ext'] ?? ''),
            ':hidden_extID'         => $ext_id
        );

        $object->query = "
            UPDATE tbl_ext SET 
                title = :title, description = :description, proj_lead = :proj_lead, 
                assist_coordinators = :assist_coordinators, period_implement = :period_implement, 
                budget = :budget, fund_source = :fund_source, target_beneficiaries = :target_beneficiaries, 
                partners = :partners, stat = :stat, attachments = :attachments, a_link = :a_link 
            WHERE id = :hidden_extID
        ";
        $object->execute($data);

        echo json_encode(array('error' => '', 'success' => '<div class="alert alert-success">Extension Activity Updated</div>'));
        exit;
    }

    // Delete (Move to Bin)
    if ($_POST["action_ext"] == 'delete') {
        $object->query = "UPDATE tbl_ext SET status = 0 WHERE id = '" . intval($_POST["extID"]) . "'";
        $object->execute();
        echo '<div class="alert alert-success">Extension Activity moved to Recycle Bin</div>';
        exit;
    }
}