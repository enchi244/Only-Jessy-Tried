<?php
// extension_project_action.php

include('../../../core/rms.php');  // Assuming this is your database handler class

$object = new rms();

if (isset($_POST["action_extension"])) {

    // Fetch Extension Project data for the Researcher
    if ($_POST["action_extension"] == 'fetch_all') {
        $order_column = array('tbl_researchdata.familyName', 'tbl_extension_project_conducted.title', 'tbl_extension_project_conducted.funding_source', 'tbl_extension_project_conducted.target_beneficiaries_communities', 'tbl_extension_project_conducted.status_exct');
        $main_query = "SELECT tbl_extension_project_conducted.*, tbl_researchdata.id AS author_db_id, tbl_researchdata.firstName, tbl_researchdata.familyName, tbl_researchdata.middleName, tbl_researchdata.Suffix FROM tbl_extension_project_conducted LEFT JOIN tbl_researchdata ON tbl_extension_project_conducted.researcherID = tbl_researchdata.id";
        $search_query = " WHERE 1=1 ";
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (tbl_extension_project_conducted.title LIKE '%" . $search_value . "%' OR tbl_researchdata.familyName LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY tbl_extension_project_conducted.id DESC ";
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
            $sub_array[] = $row["funding_source"];
            $sub_array[] = $row["target_beneficiaries_communities"];
            $sub_array[] = $row["status_exct"];
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-danger btn-sm delete_master_extension_project" data-id="'.$row["id"].'" title="Delete"><i class="far fa-trash-alt"></i></button><a href="view_researcher.php?id='.$row["author_db_id"].'&tab=epc" class="btn d-none"></a></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }
    
    if ($_POST["action_extension"] == 'fetch') {
        $order_column = array(
            'title',  
            'start_date',  
            'completed_date',  
            'funding_source',  
            'approved_budget',  
            'target_beneficiaries_communities',  
            'partners',  
            'status_exct_exct',  
            'terminal_report'  
        );

        $output = array();

        $main_query = "SELECT * FROM tbl_extension_project_conducted";
        $search_query = " WHERE researcherID = '" . $_POST["rid"] . "' ";

        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' ";
            $search_query .= "OR funding_source LIKE '%" . $search_value . "%' ";
            $search_query .= "OR target_beneficiaries_communities LIKE '%" . $search_value . "%' ";
            $search_query .= "OR partners LIKE '%" . $search_value . "%' ";
            $search_query .= "OR status_exct LIKE '%" . $search_value . "%') ";
        }

        if (isset($_POST["order"])) {
            $order_query = "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " ";
        } else {
            $order_query = "ORDER BY id ASC ";  
        }

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
            $file_badge = (!empty($row["terminal_report_file"])) ? ' <a href="../../uploads/documents/'.$row["terminal_report_file"].'" target="_blank" class="text-success ml-1" title="View File"><i class="fas fa-file-download"></i></a>' : '';
            
            $sub_array = array();
            $sub_array[] = $row["title"];
            $sub_array[] = $row["start_date"];
            $sub_array[] = $row["completed_date"];
            $sub_array[] = $row["funding_source"];
            $sub_array[] = $row["approved_budget"];
            $sub_array[] = $row["target_beneficiaries_communities"];
            $sub_array[] = $row["partners"];
            $sub_array[] = $row["status_exct"];
            $sub_array[] = $row["terminal_report"] . $file_badge;
            $sub_array[] = '
            <div align="center">
                <button type="button" name="edit_button_extension" title="Edit Extension Project" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" data-toggle="tooltip" class="btn btn-primary btn-sm edit_button_extension_project" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button>
                <button type="button" name="delete_button_extension" title="Delete Extension Project" style="margin-left: 5px;" data-toggle="tooltip" class="btn btn-danger btn-sm delete_button_extension_project" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button>
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

        $timestamp = strtotime($_POST['start_date_extc']);
        $start_date = date("m-d-Y", $timestamp);
        $timestamp2 = strtotime($_POST['completion_date_extc']);
        $completed_date = date("m-d-Y", $timestamp2);

        // Handle File Upload
        $terminal_report_file = null;
        if(isset($_FILES['terminal_report_file']['name']) && $_FILES['terminal_report_file']['name'] != '') {
            $ext = pathinfo($_FILES['terminal_report_file']['name'], PATHINFO_EXTENSION);
            $allowed = ['png', 'doc', 'docx', 'xls', 'xlsx', 'pdf'];
            if(in_array(strtolower($ext), $allowed)) {
                $new_name = 'ext_report_' . time() . '_' . rand(100,999) . '.' . $ext;
                $path = '../../../uploads/documents/' . $new_name;
                if(move_uploaded_file($_FILES['terminal_report_file']['tmp_name'], $path)) {
                    $terminal_report_file = $new_name;
                }
            } else {
                $error = '<div class="alert alert-danger">Invalid file format. Only PNG, DOC, DOCX, XLS, XLSX, and PDF allowed.</div>';
            }
        }

        if ($error == '') {
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
                ':terminal_report' => $_POST['terminal_report_extc'],
                ':terminal_report_file' => $terminal_report_file
            );

            $object->query = "
            INSERT INTO tbl_extension_project_conducted
            (researcherID, title, start_date, completed_date, funding_source, approved_budget, target_beneficiaries_communities, partners, status_exct, terminal_report, terminal_report_file) 
            VALUES 
            (:researcherID, :title, :start_date, :completed_date, :funding_source, :approved_budget, :target_beneficiaries_communities, :partners, :status_exct, :terminal_report, :terminal_report_file)
            ";
            $object->execute($data);

            $new_extension_id = $object->connect->lastInsertId();

            if(isset($_POST['linked_research_projects']) && is_array($_POST['linked_research_projects'])) {
                foreach($_POST['linked_research_projects'] as $research_id) {
                    $object->query = "INSERT INTO tbl_extension_research_links (extension_id, research_id) VALUES ('".$new_extension_id."', '".intval($research_id)."')";
                    $object->execute();
                }
            }

            $success = '<div class="alert alert-success">Extension Project Added</div>';
        }

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
            $data['terminal_report_file'] = $row["terminal_report_file"];
        }

        // Fetch Linked Projects Array
        $data['linked_projects'] = array();
        $object->query = "SELECT research_id FROM tbl_extension_research_links WHERE extension_id = '" . $_POST["extensionID"] . "'";
        $links = $object->get_result();
        foreach($links as $link) {
            $data['linked_projects'][] = (string)$link['research_id']; 
        }

        echo json_encode($data);
    }

    // Edit Extension Project
    if ($_POST["action_extension"] == 'Edit') {
        $error = '';
        $success = '';

        $timestampu = strtotime($_POST['start_date_extc']);
        $start_dateu = date("m-d-Y", $timestampu);
        $timestamp2u = strtotime($_POST['completion_date_extc']);
        $completed_dateu = date("m-d-Y", $timestamp2u);

        // Keep old file by default
        $terminal_report_file = isset($_POST['hidden_terminal_report_file']) ? $_POST['hidden_terminal_report_file'] : null;

        // If 'None' is selected, wipe the file
        if($_POST['terminal_report_extc'] == 'None') {
            $terminal_report_file = null;
        } 
        // Else process new upload if one exists
        else if(isset($_FILES['terminal_report_file']['name']) && $_FILES['terminal_report_file']['name'] != '') {
            $ext = pathinfo($_FILES['terminal_report_file']['name'], PATHINFO_EXTENSION);
            $allowed = ['png', 'doc', 'docx', 'xls', 'xlsx', 'pdf'];
            if(in_array(strtolower($ext), $allowed)) {
                $new_name = 'ext_report_' . time() . '_' . rand(100,999) . '.' . $ext;
                $path = '../../../uploads/documents/' . $new_name;
                if(move_uploaded_file($_FILES['terminal_report_file']['tmp_name'], $path)) {
                    $terminal_report_file = $new_name;
                }
            } else {
                $error = '<div class="alert alert-danger">Invalid file format. Only PNG, DOC, DOCX, XLS, XLSX, and PDF allowed.</div>';
            }
        }

        if ($error == '') {
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
                ':terminal_report_file' => $terminal_report_file,
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
                terminal_report = :terminal_report,
                terminal_report_file = :terminal_report_file
            WHERE id = :hidden_extensionID
            ";

            $object->execute($data);

            // Sync Linked Research Projects
            $ext_id = intval($_POST['hidden_extensionID']);
            $object->query = "DELETE FROM tbl_extension_research_links WHERE extension_id = '".$ext_id."'";
            $object->execute();

            if(isset($_POST['linked_research_projects']) && is_array($_POST['linked_research_projects'])) {
                foreach($_POST['linked_research_projects'] as $research_id) {
                    $object->query = "INSERT INTO tbl_extension_research_links (extension_id, research_id) VALUES ('".$ext_id."', '".intval($research_id)."')";
                    $object->execute();
                }
            }

            $success = '<div class="alert alert-success">Extension Project Updated</div>';
        }

        $output = array(
            'error' => $error,
            'success' => $success
        );

        echo json_encode($output);
    }

    // Delete Extension Project
    if ($_POST["action_extension"] == 'delete') {
        $ext_id = intval($_POST["extensionID"]);
        
        $object->query = "DELETE FROM tbl_extension_research_links WHERE extension_id = '" . $ext_id . "'";
        $object->execute();

        $object->query = "DELETE FROM tbl_extension_project_conducted WHERE id = '" . $ext_id . "'";
        $object->execute();

        echo '<div class="alert alert-success">Extension Project Deleted</div>';
    }
}
?>