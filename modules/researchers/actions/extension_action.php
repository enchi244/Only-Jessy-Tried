<?php
// external_training_action.php
include('../../../core/rms.php');

$object = new rms();

if (isset($_POST["action_ext"])) {

    if ($_POST["action_ext"] == 'fetch_all') {
        $order_column = array('tbl_researchdata.familyName', 'tbl_ext.title', 'tbl_ext.proj_lead', 'tbl_ext.period_implement', 'tbl_ext.budget');
        $main_query = "SELECT tbl_ext.*, tbl_researchdata.id AS author_db_id, tbl_researchdata.firstName, tbl_researchdata.familyName, tbl_researchdata.middleName, tbl_researchdata.Suffix FROM tbl_ext LEFT JOIN tbl_researchdata ON tbl_ext.researcherID = tbl_researchdata.id";
        
        $search_query = " WHERE tbl_ext.status = 1 "; // HIDE TRASH
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
        $object->query = $main_query;
        $object->execute();
        $total_rows = $object->row_count();

        $data = array();
        foreach ($result as $row) {
            $sub_array = array();
            $author_name = $row["familyName"] ? $row["familyName"].", ".$row["firstName"]." ".$row["middleName"]." ".$row["Suffix"] : "Unknown Author";
            $sub_array[] = '<span class="font-weight-bold">'.$author_name.'</span>';
            $sub_array[] = $row["title"];
            $sub_array[] = $row["proj_lead"];
            $sub_array[] = $row["period_implement"];
            $sub_array[] = "₱" . number_format((float)$row["budget"], 2);
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-danger btn-sm delete_master_ext" data-id="'.$row["id"].'" title="Delete"><i class="far fa-trash-alt"></i></button><a href="view_researcher.php?id='.$row["author_db_id"].'&tab=ext" class="btn d-none"></a></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }
    
    if ($_POST["action_ext"] == 'fetch') {
        $order_column = array('title', 'description', 'proj_lead', 'assist_coordinators', 'period_implement', 'budget', 'fund_source', 'target_beneficiaries', 'partners', 'stat');
        $output = array();
        $main_query = "SELECT * FROM tbl_ext";
        
        $search_query = " WHERE researcherID = '" . $_POST["rid"] . "' AND status = 1 "; // HIDE TRASH

        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' OR description LIKE '%" . $search_value . "%' OR proj_lead LIKE '%" . $search_value . "%' OR stat LIKE '%" . $search_value . "%') ";
        }
        if (isset($_POST["order"]) && isset($order_column[$_POST["order"]["0"]["column"]])) {
            $order_query = "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " ";
        } else {
            $order_query = "ORDER BY id ASC ";
        }
        $limit_query = "";
        if ($_POST["length"] != -1) { $limit_query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length']; }

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
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-primary btn-sm edit_button_ext" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button> <button type="button" class="btn btn-danger btn-sm delete_button_ext" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
    }

    if ($_POST["action_ext"] == 'Add') {
        $error = ''; $success = '';
        $data = array(':title' => $_POST["title_ext"]);
        $object->query = "SELECT * FROM tbl_ext WHERE title = :title";
        $object->execute($data);

        if ($object->row_count() > 0) {
            $error = '<div class="alert alert-danger">External Project Already Exists</div>';
        } else {
            $data = array(
                ':researcherID' => $_POST['hidden_researcherID_ext'],
                ':title' => $_POST['title_ext'],
                ':description' => $_POST['description_ext'],
                ':proj_lead' => $_POST['proj_lead'],
                ':assist_coordinators' => $_POST['assist_coordinators'],
                ':period_implement' => $_POST['period_implement'],
                ':budget' => $_POST['budget'],
                ':fund_source' => $_POST['fund_source'],
                ':target_beneficiaries' => $_POST['target_beneficiaries'],
                ':partners' => $_POST['partners'],
                ':stat' => $_POST['stat_ext']
            );
            $object->query = "INSERT INTO tbl_ext (researcherID, title, description, proj_lead, assist_coordinators, period_implement, budget, fund_source, target_beneficiaries, partners, stat, status) VALUES (:researcherID, :title, :description, :proj_lead, :assist_coordinators, :period_implement, :budget, :fund_source, :target_beneficiaries, :partners, :stat, 1)";
            $object->execute($data);
            $new_ext_id = $object->connect->lastInsertId();

            $object->query = "CREATE TABLE IF NOT EXISTS tbl_extension_activity_links (id INT AUTO_INCREMENT PRIMARY KEY, extension_activity_id INT NOT NULL, extension_project_id INT NOT NULL)";
            $object->execute();

            if(!empty($_POST['linked_extension_project'])) {
                $object->query = "INSERT INTO tbl_extension_activity_links (extension_activity_id, extension_project_id) VALUES ('".$new_ext_id."', '".intval($_POST['linked_extension_project'])."')";
                $object->execute();
            }
            $success = '<div class="alert alert-success">Extension Activity Added</div>';
        }
        echo json_encode(array('error' => $error, 'success' => $success));
    }

    if ($_POST["action_ext"] == 'fetch_single') {
        $object->query = "SELECT * FROM tbl_ext WHERE id = '" . $_POST["extID"] . "'";
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

    if ($_POST["action_ext"] == 'Edit') {
        $error = ''; $success = '';
        $data = array(':title' => $_POST['title_ext'], ':hidden_extID' => $_POST['hidden_extID']);
        $object->query = "SELECT * FROM tbl_ext WHERE title = :title AND id != :hidden_extID";
        $object->execute($data);

        if ($object->row_count() > 0) {
            $error = '<div class="alert alert-danger">External Project Already Exists</div>';
        } else {
            $data = array(
                ':title' => $_POST['title_ext'],
                ':description' => $_POST['description_ext'],
                ':proj_lead' => $_POST['proj_lead'],
                ':assist_coordinators' => $_POST['assist_coordinators'],
                ':period_implement' => $_POST['period_implement'],
                ':budget' => $_POST['budget'],
                ':fund_source' => $_POST['fund_source'],
                ':target_beneficiaries' => $_POST['target_beneficiaries'],
                ':partners' => $_POST['partners'],
                ':stat' => $_POST['stat_ext'],
                ':hidden_extID' => $_POST['hidden_extID']
            );
            $object->query = "UPDATE tbl_ext SET title = :title, description = :description, proj_lead = :proj_lead, assist_coordinators = :assist_coordinators, period_implement = :period_implement, budget = :budget, fund_source = :fund_source, target_beneficiaries = :target_beneficiaries, partners = :partners, stat = :stat WHERE id = :hidden_extID";
            $object->execute($data);
            $success = '<div class="alert alert-success">External Project Updated</div>';
        }
        echo json_encode(array('error' => $error, 'success' => $success));
    }

    // SOFT DELETE FIX
    if ($_POST["action_ext"] == 'delete') {
        $object->query = "UPDATE tbl_ext SET status = 0 WHERE id = '" . $_POST["extID"] . "'";
        $object->execute();
        echo '<div class="alert alert-success">External Project moved to Recycle Bin</div>';
    }
}
?>