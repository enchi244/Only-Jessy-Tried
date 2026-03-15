<?php
// external_training_action.php

include('../../../core/rms.php');  // Assuming this is your database handler class

$object = new rms();

if (isset($_POST["action_ext"])) {

    // Fetch External Project data for the Researcher
    if ($_POST["action_ext"] == 'fetch') {
        $order_column = array(
            'title',  // Title of the project
            'description',  // Description of the project
            'proj_lead',  // Project leader
            'assist_coordinators',  // Assistant coordinators
            'period_implement',  // Implementation period
            'budget',  // Budget of the project
            'fund_source',  // Source of funds
            'target_beneficiaries',  // Target beneficiaries
            'partners',  // Project partners
            'stat'  // Project status
        );

        $output = array();

        $main_query = "SELECT * FROM tbl_ext";  // Changed table name here
        $search_query = " WHERE researcherID = '" . $_POST["rid"] . "' ";

        // Search functionality
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' ";
            $search_query .= "OR description LIKE '%" . $search_value . "%' ";
            $search_query .= "OR proj_lead LIKE '%" . $search_value . "%' ";
            $search_query .= "OR assist_coordinators LIKE '%" . $search_value . "%' ";
            $search_query .= "OR period_implement LIKE '%" . $search_value . "%' ";
            $search_query .= "OR budget LIKE '%" . $search_value . "%' ";
            $search_query .= "OR fund_source LIKE '%" . $search_value . "%' ";
            $search_query .= "OR target_beneficiaries LIKE '%" . $search_value . "%' ";
            $search_query .= "OR partners LIKE '%" . $search_value . "%' ";
            $search_query .= "OR stat LIKE '%" . $search_value . "%') ";
        }

        // Sorting based on user input
        if (isset($_POST["order"])) {
            $order_query = "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " ";
        } else {
            $order_query = "ORDER BY id ASC ";  // Default order
        }

        // Pagination logic
        $limit_query = "";
        if ($_POST["length"] != -1) {
            $limit_query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        $object->query = $main_query . $search_query . $order_query;
        $object->execute();

        $filtered_rows = $object->row_count();

        $object->query .= $limit_query;
        $result = $object->get_result();

        $object->query = $main_query;
        $object->execute();

        $total_rows = $object->row_count();

        $data = array();

        foreach ($result as $row) {
            $sub_array = array();
            $sub_array[] = $row["title"];
            $sub_array[] = $row["description"];
            $sub_array[] = $row["proj_lead"];
            $sub_array[] = $row["assist_coordinators"];
            $sub_array[] = $row["period_implement"];
            $sub_array[] = $row["budget"];
            $sub_array[] = $row["fund_source"];
            $sub_array[] = $row["target_beneficiaries"];
            $sub_array[] = $row["partners"];
            $sub_array[] = $row["stat"];
            $sub_array[] = '
            <div align="center">
                <button type="button" name="edit_button_ext" title="Edit External Project" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" data-toggle="tooltip" class="btn btn-primary btn-sm edit_button_ext" name="edit_button_ext" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button>
                <button type="button" name="delete_button_ext" title="Delete External Project" style="margin-left: 5px;" data-toggle="tooltip" class="btn btn-danger btn-sm delete_button_ext" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button>
            </div>
            ';
            $data[] = $sub_array;
        }

        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => $total_rows,
            "recordsFiltered" => $filtered_rows,
            "data" => $data
        );

        echo json_encode($output);
    }

    // Add new External Project
    if ($_POST["action_ext"] == 'Add') {
        $error = '';
        $success = '';

        // Check if the external project title already exists
        $data = array(
            ':title' => $_POST["title_ext"]
        );

        $object->query = "
        SELECT * FROM tbl_ext 
        WHERE title = :title
        ";
        $object->execute($data);

        if ($object->row_count() > 0) {
            $error = '<div class="alert alert-danger">External Project Already Exists</div>';
        } else {
            $data = array(
                ':researcherID' => $_POST['hidden_researcherID_ext'],
                ':title' => $_POST['title_ext'],
                ':description' => $_POST['description_ext'],
                ':proj_lead' => $_POST['proj_lead_ext'],
                ':assist_coordinators' => $_POST['assist_coordinators_ext'],
                ':period_implement' => $_POST['period_implement_ext'],
                ':budget' => $_POST['budget_ext'],
                ':fund_source' => $_POST['fund_source_ext'],
                ':target_beneficiaries' => $_POST['target_beneficiaries_ext'],
                ':partners' => $_POST['partners_ext'],
                ':stat' => $_POST['stat_ext']
            );

            $object->query = "
            INSERT INTO tbl_ext 
            (researcherID, title, description, proj_lead, assist_coordinators, period_implement, budget, fund_source, target_beneficiaries, partners, stat) 
            VALUES 
            (:researcherID, :title, :description, :proj_lead, :assist_coordinators, :period_implement, :budget, :fund_source, :target_beneficiaries, :partners, :stat)
            ";
            $object->execute($data);

            $success = '<div class="alert alert-success">External Project Added</div>';
        }

        $output = array(
            'error' => $error,
            'success' => $success
        );

        echo json_encode($output);
    }

    // Fetch single External Project data for editing
    if ($_POST["action_ext"] == 'fetch_single') {
        $object->query = "
        SELECT * FROM tbl_ext 
        WHERE id = '" . $_POST["extID"] . "'
        ";

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
        }

        echo json_encode($data);
    }

    // Edit External Project
    if ($_POST["action_ext"] == 'Edit') {
        $error = '';
        $success = '';

        // Check if the external project title already exists (excluding the current ID)
        $data = array(
            ':title' => $_POST['title_ext'],
            ':hidden_extID' => $_POST['hidden_extID']
        );

        $object->query = "
        SELECT * FROM tbl_ext  
        WHERE title = :title 
        AND id != :hidden_extID
        ";

        $object->execute($data);

        if ($object->row_count() > 0) {
            $error = '<div class="alert alert-danger">External Project Already Exists</div>';
        } else {
            $data = array(
                ':title' => $_POST['title_ext'],
                ':description' => $_POST['description_ext'],
                ':proj_lead' => $_POST['proj_lead_ext'],
                ':assist_coordinators' => $_POST['assist_coordinators_ext'],
                ':period_implement' => $_POST['period_implement_ext'],
                ':budget' => $_POST['budget_ext'],
                ':fund_source' => $_POST['fund_source_ext'],
                ':target_beneficiaries' => $_POST['target_beneficiaries_ext'],
                ':partners' => $_POST['partners_ext'],
                ':stat' => $_POST['stat_ext'],
                ':hidden_extID' => $_POST['hidden_extID']
            );

            $object->query = "
            UPDATE tbl_ext
            SET title = :title, 
                description = :description, 
                proj_lead = :proj_lead, 
                assist_coordinators = :assist_coordinators, 
                period_implement = :period_implement, 
                budget = :budget, 
                fund_source = :fund_source, 
                target_beneficiaries = :target_beneficiaries, 
                partners = :partners, 
                stat = :stat
            WHERE id = :hidden_extID
            ";

            $object->execute($data);

            $success = '<div class="alert alert-success">External Project Updated</div>';
        }

        $output = array(
            'error' => $error,
            'success' => $success
        );

        echo json_encode($output);
    }

    // Delete External Project
    if ($_POST["action_ext"] == 'delete') {
        $object->query = "
        DELETE FROM tbl_ext  
        WHERE id = '" . $_POST["extID"] . "'
        ";
        $object->execute();

        echo '<div class="alert alert-success">External Project Deleted</div>';
    }
}
?>
