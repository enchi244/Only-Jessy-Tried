<?php
// trainings_attended_action.php

include('../core/rms.php');  // Assuming this is your database handler class

$object = new rms();

if (isset($_POST["action_training"])) {

    // Fetch Trainings Attended data for the Researcher
    if ($_POST["action_training"] == 'fetch') {
        $order_column = array(
            'title',  // Title of the training
            'type',  // Type of training
            'venue',  // Venue of training
            'date_train',  // Date of training
            'lvl',  // Level of training
            'type_learning_dev',  // Type of learning development
            'sponsor_org',  // Sponsor organization
            'totnh'  // Total number of hours
        );

        $output = array();

        $main_query = "SELECT * FROM tbl_trainingsattended";
        $search_query = " WHERE researcherID = '" . $_POST["rid"] . "' ";

        // Search functionality
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' ";
            $search_query .= "OR type LIKE '%" . $search_value . "%' ";
            $search_query .= "OR venue LIKE '%" . $search_value . "%' ";
            $search_query .= "OR sponsor_org LIKE '%" . $search_value . "%' ";
            $search_query .= "OR type_learning_dev LIKE '%" . $search_value . "%') ";
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
            $sub_array[] = $row["type"];
            $sub_array[] = $row["venue"];
            $sub_array[] = $row["date_train"];
            $sub_array[] = $row["lvl"];
            $sub_array[] = $row["type_learning_dev"];
            $sub_array[] = $row["sponsor_org"];
            $sub_array[] = $row["totnh"];
            $sub_array[] = '
            <div align="center">
                <button type="button" name="edit_button_training" title="Edit Training" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" data-toggle="tooltip" class="btn btn-primary btn-sm edit_button_training" name="edit_button_training" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button>
                <button type="button" name="delete_button_training" title="Delete Training" style="margin-left: 5px;" data-toggle="tooltip" class="btn btn-danger btn-sm delete_button_training" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button>
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

    // Add new Training Attended
    if ($_POST["action_training"] == 'Add') {
        $error = '';
        $success = '';

        // Check if the training title already exists
        // $data = array(
        //     ':title' => $_POST["title_training"]
        // );

        // $object->query = "
        // SELECT * FROM tbl_trainingsattended
        // WHERE title = :title
        // ";
        // $object->execute($data);

        $timestamp = strtotime($_POST['date_training']);
        $date_applied = date("m-d-Y", $timestamp);

        // if ($object->row_count() > 0) {
        //     $error = '<div class="alert alert-danger">Training Already Exists</div>';
        // } else {
            $data = array(
                ':researcherID' => $_POST['hidden_researcherID_training'],
                ':title' => $_POST['title_training'],
                ':type' => $_POST['type_training'],
                ':venue' => $_POST['venue_training'],
                ':date_train' => $date_applied,
                ':lvl' => $_POST['level_training'],
                ':type_learning_dev' => $_POST['type_learning_dev'],
                ':sponsor_org' => $_POST['sponsor_org'],
                ':totnh' => $_POST['total_hours_training']
            );

            $object->query = "
            INSERT INTO tbl_trainingsattended
            (researcherID, title, type, venue, date_train, lvl, type_learning_dev, sponsor_org, totnh) 
            VALUES 
            (:researcherID, :title, :type, :venue, :date_train, :lvl, :type_learning_dev, :sponsor_org, :totnh)
            ";
            $object->execute($data);

            $success = '<div class="alert alert-success">Training Added</div>';
        // }

        $output = array(
            'error' => $error,
            'success' => $success
        );

        echo json_encode($output);
    }

    // Fetch single Training data for editing
    if ($_POST["action_training"] == 'fetch_single') {
        $object->query = "
        SELECT * FROM tbl_trainingsattended
        WHERE id = '" . $_POST["trainingID"] . "'
        ";

        $result = $object->get_result();
        $data = array();

        foreach ($result as $row) {
            $data['title'] = $row["title"];
            $data['type'] = $row["type"];
            $data['venue'] = $row["venue"];
            $data['date_train'] = $row["date_train"];
            $data['lvl'] = $row["lvl"];
            $data['type_learning_dev'] = $row["type_learning_dev"];
            $data['sponsor_org'] = $row["sponsor_org"];
            $data['totnh'] = $row["totnh"];
        }

        echo json_encode($data);
    }

    // Edit Training Attended
    if ($_POST["action_training"] == 'Edit') {
        $error = '';
        $success = '';

        // Check if the training title already exists (excluding the current ID)
        // $data = array(
        //     ':title' => $_POST['title_training'],
        //     ':hidden_trainingID' => $_POST['hidden_trainingID']
        // );

        // $object->query = "
        // SELECT * FROM tbl_trainingsattended
        // WHERE title = :title 
        // AND id != :hidden_trainingID
        // ";

        // $object->execute($data);

        $timestampu = strtotime($_POST['date_training']);
        $date_trainu = date("m-d-Y", $timestampu);

        // if ($object->row_count() > 0) {
        //     $error = '<div class="alert alert-danger">Training Already Exists</div>';
        // } else {
            $data = array(
                ':title' => $_POST['title_training'],
                ':type' => $_POST['type_training'],
                ':venue' => $_POST['venue_training'],
                ':date_train' => $date_trainu,
                ':lvl' => $_POST['level_training'],
                ':type_learning_dev' => $_POST['type_learning_dev'],
                ':sponsor_org' => $_POST['sponsor_org'],
                ':totnh' => $_POST['total_hours_training'],
                ':hidden_trainingID' => $_POST['hidden_trainingID']
            );

            $object->query = "
            UPDATE tbl_trainingsattended
            SET title = :title, 
                type = :type, 
                venue = :venue, 
                date_train = :date_train, 
                lvl = :lvl, 
                type_learning_dev = :type_learning_dev, 
                sponsor_org = :sponsor_org, 
                totnh = :totnh
            WHERE id = :hidden_trainingID
            ";

            $object->execute($data);

            $success = '<div class="alert alert-success">Training Updated</div>';
        // }

        $output = array(
            'error' => $error,
            'success' => $success
        );

        echo json_encode($output);
    }

    // Delete Training Attended
    if ($_POST["action_training"] == 'delete') {
        $object->query = "
        DELETE FROM tbl_trainingsattended
        WHERE id = '" . $_POST["trainingID"] . "'
        ";
        $object->execute();

        echo '<div class="alert alert-success">Training Deleted</div>';
    }
}
?>
