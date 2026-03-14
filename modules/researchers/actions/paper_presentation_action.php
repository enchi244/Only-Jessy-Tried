<?php
// paper_presentation_action.php

include('../../../core/rms.php');  // Assuming this is your database handler class

$object = new rms();

if (isset($_POST["action_paper_presentation"])) {

    // Fetch Paper Presentation data for the Researcher
    if ($_POST["action_paper_presentation"] == 'fetch_all') {
        $order_column = array('tbl_researchdata.familyName', 'tbl_paperpresentation.title', 'tbl_paperpresentation.conference_title', 'tbl_paperpresentation.conference_venue', 'tbl_paperpresentation.date_paper');
        $main_query = "SELECT tbl_paperpresentation.*, tbl_researchdata.id AS author_db_id, tbl_researchdata.firstName, tbl_researchdata.familyName, tbl_researchdata.middleName, tbl_researchdata.Suffix FROM tbl_paperpresentation LEFT JOIN tbl_researchdata ON tbl_paperpresentation.researcherID = tbl_researchdata.id";
        $search_query = " WHERE 1=1 ";
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (tbl_paperpresentation.title LIKE '%" . $search_value . "%' OR tbl_researchdata.familyName LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY tbl_paperpresentation.id DESC ";
        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'] : "";

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
            $author_name = $row["familyName"] ? $row["familyName"].", ".$row["firstName"]." ".$row["middleName"]." ".$row["Suffix"] : "Unknown Author";
            $sub_array[] = '<span class="font-weight-bold">'.$author_name.'</span>';
            $sub_array[] = $row["title"];
            $sub_array[] = $row["conference_title"];
            $sub_array[] = $row["conference_venue"];
            $sub_array[] = $row["date_paper"];
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-danger btn-sm delete_master_paper_presentation" data-id="'.$row["id"].'" title="Delete"><i class="far fa-trash-alt"></i></button><a href="view_researcher.php?id='.$row["author_db_id"].'&tab=pp" class="btn d-none"></a></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }
    if ($_POST["action_paper_presentation"] == 'fetch') {
        $order_column = array(
            'title',  // Title of the paper presentation
            'conference_title',  // Conference title
            'conference_venue',  // Conference venue
            'conference_organizer',  // Conference organizer
            'date_paper',  // Date of presentation
            'type',  // Type of paper (Oral/Poster)
            'discipline'  // Discipline
        );

        $output = array();

        $main_query = "SELECT * FROM tbl_paperpresentation";
        $search_query = " WHERE researcherID = '" . $_POST["rid"] . "' ";

        // Search functionality
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' ";
            $search_query .= "OR conference_title LIKE '%" . $search_value . "%' ";
            $search_query .= "OR conference_venue LIKE '%" . $search_value . "%' ";
            $search_query .= "OR conference_organizer LIKE '%" . $search_value . "%' ";
            $search_query .= "OR type LIKE '%" . $search_value . "%' ";
            $search_query .= "OR discipline LIKE '%" . $search_value . "%') ";
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
            $sub_array[] = $row["conference_title"];
            $sub_array[] = $row["conference_venue"];
            $sub_array[] = $row["conference_organizer"];
            $sub_array[] = $row["date_paper"];
            $sub_array[] = $row["type"];
            $sub_array[] = $row["discipline"];
            $sub_array[] = '
            <div align="center">
                <button type="button" name="edit_button_paper_presentation" title="Edit Paper Presentation" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" data-toggle="tooltip" class="btn btn-primary btn-sm edit_button_paper_presentation" name="edit_button_paper_presentation" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button>
                <button type="button" name="delete_button_paper_presentation" title="Delete Paper Presentation" style="margin-left: 5px;" data-toggle="tooltip" class="btn btn-danger btn-sm delete_button_paper_presentation" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button>
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

    // Add new Paper Presentation
    if ($_POST["action_paper_presentation"] == 'Add') {
        $error = '';
        $success = '';

        // Check if the paper title already exists
        // $data = array(
        //     ':title' => $_POST["title_pp"]
        // );

        // $object->query = "
        // SELECT * FROM tbl_paperpresentation
        // WHERE title = :title
        // ";
        // $object->execute($data);

        $timestamp = strtotime($_POST['date_paper']);
 
// Creating new date format from that timestamp
$date_applied = date("m-d-Y", $timestamp);


        // if ($object->row_count() > 0) {
        //     $error = '<div class="alert alert-danger">Paper Presentation Already Exists</div>';
        // } else {
            $data = array(
                ':researcherID' => $_POST['hidden_researcherID_pp'],
                ':title' => $_POST['title_pp'],
                ':conference_title' => $_POST['conference_title'],
                ':conference_venue' => $_POST['conference_venue'],
                ':conference_organizer' => $_POST['conference_organizer'],
                ':date_paper' => $date_applied,
                ':type_pp' => $_POST['type_pp'],
                ':discipline' => $_POST['discipline']
            );

            $object->query = "
            INSERT INTO tbl_paperpresentation
            (researcherID, title, conference_title, conference_venue, conference_organizer, date_paper, type, discipline) 
            VALUES 
            (:researcherID, :title, :conference_title, :conference_venue, :conference_organizer, :date_paper, :type_pp, :discipline)
            ";
            $object->execute($data);

            $success = '<div class="alert alert-success">Paper Presentation Added</div>';
        // }

        $output = array(
            'error' => $error,
            'success' => $success
        );

        echo json_encode($output);
    }

    // Fetch single Paper Presentation data for editing
    if ($_POST["action_paper_presentation"] == 'fetch_single') {
        $object->query = "
        SELECT * FROM tbl_paperpresentation
        WHERE id = '" . $_POST["paperPresentationID"] . "'
        ";

        $result = $object->get_result();
        $data = array();

        foreach ($result as $row) {
            $data['title'] = $row["title"];
            $data['conference_title'] = $row["conference_title"];
            $data['conference_venue'] = $row["conference_venue"];
            $data['conference_organizer'] = $row["conference_organizer"];
            $data['date_paper'] = $row["date_paper"];
            $data['type'] = $row["type"];
            $data['discipline'] = $row["discipline"];
        }

        echo json_encode($data);
    }

    // Edit Paper Presentation
    if ($_POST["action_paper_presentation"] == 'Edit') {
        $error = '';
        $success = '';

        // Check if the paper title already exists in the database (excluding the current id)
        // $data = array(
        //     ':title' => $_POST['title_pp'],
        //     ':hidden_paperPresentationID' => $_POST['hidden_paperPresentationID']
        // );

        // $object->query = "
        // SELECT * FROM tbl_paperpresentation
        // WHERE title = :title 
        // AND id != :hidden_paperPresentationID
        // ";

        // $object->execute($data);

        $timestampu = strtotime($_POST['date_paper']);
 
// Creating new date format from that timestamp
$date_paperu = date("m-d-Y", $timestampu);



        // if ($object->row_count() > 0) {
        //     $error = '<div class="alert alert-danger">Paper Presentation Already Exists</div>';
        // } else {
            $data = array(
                ':title' => $_POST['title_pp'],
                ':conference_title' => $_POST['conference_title'],
                ':conference_venue' => $_POST['conference_venue'],
                ':conference_organizer' => $_POST['conference_organizer'],
                ':date_paper' => $date_paperu,
                ':type' => $_POST['type_pp'],
                ':discipline' => $_POST['discipline'],
                ':hidden_paperPresentationID' => $_POST['hidden_paperPresentationID']
            );

            $object->query = "
            UPDATE tbl_paperpresentation
            SET title = :title, 
                conference_title = :conference_title, 
                conference_venue = :conference_venue, 
                conference_organizer = :conference_organizer, 
                date_paper = :date_paper, 
                type = :type, 
                discipline = :discipline
            WHERE id = :hidden_paperPresentationID
            ";

            $object->execute($data);

            $success = '<div class="alert alert-success">Paper Presentation Updated</div>';
        // }

        $output = array(
            'error' => $error,
            'success' => $success
        );

        echo json_encode($output);
    }

    // Delete Paper Presentation
    if ($_POST["action_paper_presentation"] == 'delete') {
        $object->query = "
        DELETE FROM tbl_paperpresentation
        WHERE id = '" . $_POST["paperPresentationID"] . "'
        ";
        $object->execute();

        echo '<div class="alert alert-success">Paper Presentation Deleted</div>';
    }
}
?>
