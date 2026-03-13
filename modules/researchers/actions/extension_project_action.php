<?php
// extension_project_action.php

include('../../../core/rms.php');  // Assuming this is your database handler class

$object = new rms();

if (isset($_POST["action_extension"])) {

    // Fetch Extension Project data for the Researcher
    if ($_POST["action_extension"] == 'fetch') {
        $order_column = array(
            'title',  // Title of the extension project
            'start_date',  // Start Date
            'completed_date',  // Completed Date
            'funding_source',  // Funding Source
            'approved_budget',  // Approved Budget
            'target_beneficiaries_communities',  // Target Beneficiaries/Communities
            'partners',  // Partners
            'status_exct_exct',  // status_exct of the project
            'terminal_report'  // Terminal Report
        );




















        $output = array();

        $main_query = "SELECT * FROM tbl_extension_project_conducted";
        $search_query = " WHERE researcherID = '" . $_POST["rid"] . "' ";

        // Search functionality
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' ";
            $search_query .= "OR funding_source LIKE '%" . $search_value . "%' ";
            $search_query .= "OR target_beneficiaries_communities LIKE '%" . $search_value . "%' ";
            $search_query .= "OR partners LIKE '%" . $search_value . "%' ";
            $search_query .= "OR status_exct LIKE '%" . $search_value . "%') ";
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
            $sub_array[] = $row["start_date"];
            $sub_array[] = $row["completed_date"];
            $sub_array[] = $row["funding_source"];
            $sub_array[] = $row["approved_budget"];
            $sub_array[] = $row["target_beneficiaries_communities"];
            $sub_array[] = $row["partners"];
            $sub_array[] = $row["status_exct"];
            $sub_array[] = $row["terminal_report"];
            $sub_array[] = '
            <div align="center">
                <button type="button" name="edit_button_extension" title="Edit Extension Project" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" data-toggle="tooltip" class="btn btn-primary btn-sm edit_button_extension" name="edit_button_extension" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button>
                <button type="button" name="delete_button_extension" title="Delete Extension Project" style="margin-left: 5px;" data-toggle="tooltip" class="btn btn-danger btn-sm delete_button_extension" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button>
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

    // Add new Extension Project
    if ($_POST["action_extension"] == 'Add') {
        $error = '';
        $success = '';

        // Check if the extension project title already exists
        // $data = array(
        //     ':title' => $_POST["title_extp"]
        // );

        // $object->query = "
        // SELECT * FROM tbl_extension_project_conducted
        // WHERE title = :title
        // ";
        // $object->execute($data);






        // $timestamp = strtotime($_POST['date_training']);
        // $date_applied = date("m-d-Y", $timestamp);









        $timestamp = strtotime($_POST['start_date_extc']);
        $start_date = date("m-d-Y", $timestamp);
        $timestamp2 = strtotime($_POST['completion_date_extc']);
        $completed_date = date("m-d-Y", $timestamp2);

        // if ($object->row_count() > 0) {
        //     $error = '<div class="alert alert-danger">Extension Project Already Exists</div>';
        // } else {
            $data = array(
                ':researcherID' => $_POST['hidden_researcherID_extension'],
                ':title' => $_POST['title_extp'],
                ':start_date' => $start_date,
                ':completed_date' => $completed_date,
                ':funding_source' => $_POST['funding_source_exct'],
                ':approved_budget' => $_POST['approved_budget_exct'],
                ':target_beneficiaries_communities' => $_POST['target_beneficiaries_communities'],
                ':partners' => $_POST['partners'],
                ':status_exct' => $_POST['status_exct'],
                ':terminal_report' => $_POST['terminal_report_extc']




             










            );

            $object->query = "
            INSERT INTO tbl_extension_project_conducted
            (researcherID, title, start_date, completed_date, funding_source, approved_budget, target_beneficiaries_communities, partners, status_exct, terminal_report) 
            VALUES 
            (:researcherID, :title, :start_date, :completed_date, :funding_source, :approved_budget, :target_beneficiaries_communities, :partners, :status_exct, :terminal_report)
            ";
            $object->execute($data);

            $success = '<div class="alert alert-success">Extension Project Added</div>';
        // }

        $output = array(
            'error' => $error,
            'success' => $success
        );

        echo json_encode($output);
    }

    // Fetch single Extension Project data for editing
    if ($_POST["action_extension"] == 'fetch_single') {
        $object->query = "
        SELECT * FROM tbl_extension_project_conducted
        WHERE id = '" . $_POST["extensionID"] . "'
        ";

        $result = $object->get_result();
        $data = array();

        foreach ($result as $row) {
            $data['title'] = $row["title"];
            $data['start_date'] = $row["start_date"];
            $data['completed_date'] = $row["completed_date"];
            $data['funding_source'] = $row["funding_source"];
            $data['approved_budget'] = $row["approved_budget"];
            $data['target_beneficiaries_communities'] = $row["target_beneficiaries_communities"];
            $data['partners'] = $row["partners"];
            $data['status_exct'] = $row["status_exct"];
            $data['terminal_report'] = $row["terminal_report"];
        }

        echo json_encode($data);
    }

    // Edit Extension Project
    if ($_POST["action_extension"] == 'Edit') {
        $error = '';
        $success = '';

        // Check if the extension project title already exists (excluding the current ID)
        // $data = array(
        //     ':title' => $_POST['title_extp'],
        //     ':hidden_extensionID' => $_POST['hidden_extensionID']
        // );

        // $object->query = "
        // SELECT * FROM tbl_extension_project_conducted
        // WHERE title = :title 
        // AND id != :hidden_extensionID
        // ";

        // $object->execute($data);

        $timestampu = strtotime($_POST['start_date_extc']);
        $start_dateu = date("m-d-Y", $timestampu);
        $timestamp2u = strtotime($_POST['completion_date_extc']);
        $completed_dateu = date("m-d-Y", $timestamp2u);

        // if ($object->row_count() > 0) {
        //     $error = '<div class="alert alert-danger">Extension Project Already Exists</div>';
        // } else {
            $data = array(
               
               
               
               
 
               
               
               
               
               
               
               
               
               
               
               
               
               
               
               
                ':title' => $_POST['title_extp'],
                ':start_date' => $start_dateu,
                ':completed_date' => $completed_dateu,
                ':funding_source' => $_POST['funding_source_exct'],
                ':approved_budget' => $_POST['approved_budget_exct'],
                ':target_beneficiaries_communities' => $_POST['target_beneficiaries_communities'],
                ':partners' => $_POST['partners'],
                ':status_exct' => $_POST['status_exct'],
                ':terminal_report' => $_POST['terminal_report_extc'],
                ':hidden_extensionID' => $_POST['hidden_extensionID']
            );

            $object->query = "
            UPDATE tbl_extension_project_conducted
            SET title = :title, 
                start_date = :start_date, 
                completed_date = :completed_date, 
                funding_source = :funding_source, 
                approved_budget = :approved_budget, 
                target_beneficiaries_communities = :target_beneficiaries_communities, 
                partners = :partners, 
                status_exct = :status_exct, 
                terminal_report = :terminal_report
            WHERE id = :hidden_extensionID
            ";

            $object->execute($data);

            $success = '<div class="alert alert-success">Extension Project Updated</div>';
        // }

        $output = array(
            'error' => $error,
            'success' => $success
        );

        echo json_encode($output);
    }

    // Delete Extension Project
    if ($_POST["action_extension"] == 'delete') {
        $object->query = "
        DELETE FROM tbl_extension_project_conducted
        WHERE id = '" . $_POST["extensionID"] . "'
        ";
        $object->execute();

        echo '<div class="alert alert-success">Extension Project Deleted</div>';
    }
}
?>
