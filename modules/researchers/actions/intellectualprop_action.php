<?php
// intellectualprop_action.php

include('../../../core/rms.php');  // Assuming this is your database handler class

$object = new rms();

if (isset($_POST["action_intellectualprop"])) {

    // Fetch Intellectual Property data for the Researcher
    if ($_POST["action_intellectualprop"] == 'fetch') {
        $order_column = array(
            'title',  // Title of IP
            'coauth',  // Co-authors
            'type',  // Type of IP (e.g., patent, trademark)
            'date_applied',  // Application Date
            'date_granted',  // Grant Date
        );

        $output = array();

        $main_query = "SELECT * FROM tbl_itelectualprop";
        $search_query = " WHERE researcherID = '" . $_POST["rid"] . "' ";

        // Search functionality
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' ";
            $search_query .= "OR coauth LIKE '%" . $search_value . "%' ";
            $search_query .= "OR type LIKE '%" . $search_value . "%') ";
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
            $sub_array[] = $row["coauth"];
            $sub_array[] = $row["type"];
            $sub_array[] = $row["date_applied"];
            $sub_array[] = $row["date_granted"];
            $sub_array[] = '
            <div align="center">
                <button type="button" name="edit_button_intellectualprop" title="Edit Intellectual Property" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" data-toggle="tooltip" class="btn btn-primary btn-sm edit_button_intellectualprop" name="edit_button_intellectualprop" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button>
            
                <button type="button" name="delete_button_intellectualprop" title="Delete Intellectual Property" style="margin-left: 5px;" data-toggle="tooltip" class="btn btn-danger btn-sm delete_button_intellectualprop" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button>
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

    // Add new Intellectual Property
    if ($_POST["action_intellectualprop"] == 'Add') {
        $error = '';
        $success = '';

        // Check if the title already exists
        // $data = array(
        //     ':title' => $_POST["title_ip"]
        // );

        // $object->query = "
        // SELECT * FROM tbl_itelectualprop
        // WHERE title = :title
        // ";
        // $object->execute($data);




        $timestamp = strtotime($_POST['date_applied']);
 
// Creating new date format from that timestamp
$date_applied = date("m-d-Y", $timestamp);




$timestamp1 = strtotime($_POST['date_granted']);
 
// Creating new date format from that timestamp
$date_granted = date("m-d-Y", $timestamp1);












        // if ($object->row_count() > 0) {
        //     $error = '<div class="alert alert-danger">Intellectual Property Already Exists</div>';
        // } else {
            $data = array(
                ':researcherID' => $_POST['hidden_researcherID_ip'],
                ':title' => $_POST['title_ip'],
                ':coauth' => $_POST['coauth'],
                ':type' => $_POST['type_ip'],
                ':date_applied' => $date_applied,
                ':date_granted' => $date_granted
            );

            $object->query = "
            INSERT INTO tbl_itelectualprop
            (researcherID, title, coauth, type, date_applied, date_granted) 
            VALUES 
            (:researcherID, :title, :coauth, :type, :date_applied, :date_granted)
            ";

            $object->execute($data);
//console.log($data);
            $success = '<div class="alert alert-success">Intellectual Property Added</div>';
        // }

        $output = array(
            'error' => $error,
            'success' => $success
        );

        echo json_encode($output);
    }

    // Fetch single Intellectual Property data for editing
    if ($_POST["action_intellectualprop"] == 'fetch_single') {
        $object->query = "
        SELECT * FROM tbl_itelectualprop
        WHERE id = '" . $_POST["intellectualPropID"] . "'
        ";

        $result = $object->get_result();
        $data = array();

        foreach ($result as $row) {
            $data['title'] = $row["title"];
            $data['coauth'] = $row["coauth"];
            $data['type'] = $row["type"];
            $data['date_applied'] = $row["date_applied"];
            $data['date_granted'] = $row["date_granted"];
        }

        echo json_encode($data);
    }

    // Edit Intellectual Property
    if ($_POST["action_intellectualprop"] == 'Edit') {
        $error = '';
        $success = '';

        // Check if the title already exists in the database (excluding the current id)
        // $data = array(
        //     ':title' => $_POST['title_ip'],
        //     ':hidden_intellectualPropID' => $_POST['hidden_intellectualPropID']
        // );

        // $object->query = "
        // SELECT * FROM tbl_itelectualprop
        // WHERE title = :title 
        // AND id != :hidden_intellectualPropID
        // ";


        $timestampu = strtotime($_POST['date_applied']);
 
// Creating new date format from that timestamp
$date_appliedu = date("m-d-Y", $timestampu);




$timestamp1u = strtotime($_POST['date_granted']);
 
// Creating new date format from that timestamp
$date_grantedu = date("m-d-Y", $timestamp1u);


        // $object->execute($data);

        // if ($object->row_count() > 0) {
        //     $error = '<div class="alert alert-danger">Intellectual Property Already Exists</div>';
        // } else {
            $data = array(
                ':title' => $_POST['title_ip'],
                ':coauth' => $_POST['coauth'],
                ':type' => $_POST['type_ip'],
                ':date_applied' => $date_appliedu,
                ':date_granted' => $date_grantedu,
                ':hidden_intellectualPropID' => $_POST['hidden_intellectualPropID']
            );

            $object->query = "
            UPDATE tbl_itelectualprop
            SET title = :title, 
                coauth = :coauth, 
                type = :type, 
                date_applied = :date_applied, 
                date_granted = :date_granted
            WHERE id = :hidden_intellectualPropID
            ";

            $object->execute($data);

            $success = '<div class="alert alert-success">Intellectual Property Updated</div>';
        // }

        $output = array(
            'error' => $error,
            'success' => $success
        );

        echo json_encode($output);
    }

    // Delete Intellectual Property
    if ($_POST["action_intellectualprop"] == 'delete') {
        $object->query = "
        DELETE FROM tbl_itelectualprop
        WHERE id = '" . $_POST["intellectualPropID"] . "'
        ";
        $object->execute();

        echo '<div class="alert alert-success">Intellectual Property Deleted</div>';
    }
}

?>
