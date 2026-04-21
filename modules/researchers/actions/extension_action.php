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
        
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' OR proj_lead LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY id ASC ";
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

    // Add New Extension Activity
    if ($_POST["action_ext"] == 'Add') {
        $attachment_name = '';
        if(isset($_FILES['attachments_ext']) && $_FILES['attachments_ext']['name'] != '') {
            $attachment_name = 'ext_att_' . time() . '_' . rand(100, 999) . '.' . pathinfo($_FILES['attachments_ext']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['attachments_ext']['tmp_name'], '../../../uploads/documents/' . $attachment_name);
        }

        $researcherID = !empty($_POST['hidden_researcherID_ext']) ? $_POST['hidden_researcherID_ext'] : null;
        $parent_project_id = !empty($_POST['linked_extension_project']) ? $_POST['linked_extension_project'] : null;

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
            ':title'                => $object->clean_input($_POST['title_ext']),
            ':description'          => $object->clean_input($_POST['description_ext']),
            ':proj_lead'            => $object->clean_input($_POST['proj_lead']),
            ':assist_coordinators'  => $assist_coordinators,
            ':period_implement'     => $object->clean_input($_POST['period_implement']),
            ':budget'               => $object->clean_input($_POST['budget']),
            ':fund_source'          => $object->clean_input($_POST['fund_source']),
            ':target_beneficiaries' => $object->clean_input($_POST['target_beneficiaries']),
            ':partners'             => $object->clean_input($_POST['partners_ext']), // Updated to unique ID
            ':stat'                 => $object->clean_input($_POST['stat_ext']),
            ':attachments'          => $attachment_name,
            ':a_link'               => $object->clean_input($_POST['a_link_ext'] ?? '')
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
            $data['extension_project_id'] = $row["extension_project_id"];
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

    // Update Extension Activity
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
            ':extension_project_id' => $_POST['linked_extension_project'],
            ':title'                => $object->clean_input($_POST['title_ext']),
            ':description'          => $object->clean_input($_POST['description_ext']),
            ':proj_lead'            => $object->clean_input($_POST['proj_lead']),
            ':assist_coordinators'  => $assist_coordinators,
            ':period_implement'     => $object->clean_input($_POST['period_implement']),
            ':budget'               => $object->clean_input($_POST['budget']),
            ':fund_source'          => $object->clean_input($_POST['fund_source']),
            ':target_beneficiaries' => $object->clean_input($_POST['target_beneficiaries']),
            ':partners'             => $object->clean_input($_POST['partners_ext']), // Updated to unique ID
            ':stat'                 => $object->clean_input($_POST['stat_ext']),
            ':attachments'          => $attachment_name,
            ':a_link'               => $object->clean_input($_POST['a_link_ext'] ?? ''),
            ':hidden_extID'         => $ext_id
        );

        $object->query = "
            UPDATE tbl_ext SET 
                extension_project_id = :extension_project_id,
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
}