<?php
// extension_project_action.php
include('../../../core/rms.php');
$object = new rms();

if (!function_exists('parse_legacy_date_php')) {
    function parse_legacy_date_php($date_str) {
        if (empty($date_str) || $date_str === 'null' || $date_str === '0000-00-00') return '';
        $date_str = trim(str_replace('/', '-', $date_str));
        $parts = explode('-', $date_str);
        if (count($parts) === 1 && strlen($parts[0]) === 4) { return $parts[0] . '-01-01'; }
        if (count($parts) === 2) {
            if (strlen($parts[1]) === 4) return $parts[1] . '-' . str_pad($parts[0], 2, '0', STR_PAD_LEFT) . '-01';
            if (strlen($parts[0]) === 4) return $parts[0] . '-' . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . '-01';
        }
        if (count($parts) === 3) {
            if (strlen($parts[2]) === 4) return $parts[2] . '-' . str_pad($parts[0], 2, '0', STR_PAD_LEFT) . '-' . str_pad($parts[1], 2, '0', STR_PAD_LEFT);
            if (strlen($parts[0]) === 4) return $parts[0] . '-' . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($parts[2], 2, '0', STR_PAD_LEFT);
        }
        $time = strtotime($date_str);
        return ($time !== false) ? date('Y-m-d', $time) : '';
    }
}

if (!function_exists('handle_extension_files')) {
    function handle_extension_files($object, $extension_id, $categories, $files) {
        if(isset($files['name']) && is_array($files['name'])) {
            $upload_dir = '../../../uploads/research_files/';
            if (!file_exists($upload_dir)) { mkdir($upload_dir, 0755, true); }
            
            for($i = 0; $i < count($files['name']); $i++) {
                if($files['error'][$i] == 0) {
                    $category = isset($categories[$i]) ? $categories[$i] : 'Other';
                    $original_name = basename($files['name'][$i]);
                    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                    $safe_name = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($original_name, PATHINFO_FILENAME));
                    $new_name = 'EXT_' . $safe_name . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    $target_file = $upload_dir . $new_name;
                    $db_path = 'uploads/research_files/' . $new_name; 
                    if(move_uploaded_file($files['tmp_name'][$i], $target_file)) {
                        $object->query = "INSERT INTO tbl_extension_files (extension_id, file_category, file_name, file_path) VALUES (:eid, :cat, :fname, :fpath)";
                        $object->execute([':eid' => $extension_id, ':cat' => $category, ':fname' => $original_name, ':fpath' => $db_path]);
                    }
                }
            }
        }
    }
}

if (isset($_POST["action_extension"])) {

    if ($_POST["action_extension"] == 'fetch_all') {
        $order_column = array('tbl_researchdata.familyName', 'tbl_extension_project_conducted.title', 'tbl_extension_project_conducted.funding_source', 'tbl_extension_project_conducted.target_beneficiaries_communities', 'tbl_extension_project_conducted.status_exct');
        $main_query = "SELECT tbl_extension_project_conducted.*, tbl_researchdata.id AS author_db_id, tbl_researchdata.firstName, tbl_researchdata.familyName, tbl_researchdata.middleName, tbl_researchdata.Suffix FROM tbl_extension_project_conducted LEFT JOIN tbl_researchdata ON tbl_extension_project_conducted.researcherID = tbl_researchdata.id";
        
        $search_query = " WHERE tbl_extension_project_conducted.status = 1 "; // HIDE TRASH
        
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
        $order_column = array('title', 'start_date', 'completed_date', 'funding_source', 'approved_budget', 'target_beneficiaries_communities', 'partners', 'status_exct', 'has_files');
        $main_query = "SELECT * FROM tbl_extension_project_conducted";
        
        $search_query = " WHERE researcherID = '" . $_POST["rid"] . "' AND status = 1 "; // HIDE TRASH

        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' OR funding_source LIKE '%" . $search_value . "%' OR status_exct LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY id ASC ";
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
            $file_badge = ($row["has_files"] == 'With') ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-paperclip mr-1"></i> Files</span>' : '<span class="badge badge-secondary px-2 py-1">None</span>';
            $sub_array = array();
            $sub_array[] = $row["title"];
            $sub_array[] = parse_legacy_date_php($row["start_date"]);
            $sub_array[] = parse_legacy_date_php($row["completed_date"]);
            $sub_array[] = $row["funding_source"];
            $sub_array[] = $row["approved_budget"];
            $sub_array[] = $row["target_beneficiaries_communities"];
            $sub_array[] = $row["partners"];
            $sub_array[] = $row["status_exct"];
            $sub_array[] = '<div align="center">' . $file_badge . '</div>';
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-primary btn-sm edit_button_extension_project" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button> <button type="button" class="btn btn-danger btn-sm delete_button_extension_project" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if ($_POST["action_extension"] == 'Add') {
        $error = ''; $success = '';
        $start_date = date("m-d-Y", strtotime($_POST['start_date_extc']));
        $completed_date = date("m-d-Y", strtotime($_POST['completion_date_extc']));
        $has_files = $_POST['has_files_extp'];

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
            ':terminal_report' => 'Legacy Replaced', 
            ':has_files' => $has_files
        );
        $object->query = "INSERT INTO tbl_extension_project_conducted (researcherID, title, start_date, completed_date, funding_source, approved_budget, target_beneficiaries_communities, partners, status_exct, terminal_report, has_files, status) VALUES (:researcherID, :title, :start_date, :completed_date, :funding_source, :approved_budget, :target_beneficiaries_communities, :partners, :status_exct, :terminal_report, :has_files, 1)";
        $object->execute($data);
        $new_extension_id = $object->connect->lastInsertId();

        if(isset($_POST['linked_research_projects']) && is_array($_POST['linked_research_projects'])) {
            foreach($_POST['linked_research_projects'] as $research_id) {
                $object->query = "INSERT INTO tbl_extension_research_links (extension_id, research_id) VALUES ('".$new_extension_id."', '".intval($research_id)."')";
                $object->execute();
            }
        }
        if($has_files == 'With' && isset($_FILES['extp_files'])) {
            $categories = isset($_POST['extp_file_categories']) ? $_POST['extp_file_categories'] : [];
            handle_extension_files($object, $new_extension_id, $categories, $_FILES['extp_files']);
        }

        $success = '<div class="alert alert-success">Extension Project Added</div>';
        echo json_encode(array('error' => $error, 'success' => $success));
        exit;
    }

    if ($_POST["action_extension"] == 'fetch_single') {
        $object->query = "SELECT * FROM tbl_extension_project_conducted WHERE id = '" . $_POST["extensionID"] . "'";
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
            $data['has_files'] = $row["has_files"];
        }

        $data['linked_projects'] = array();
        $object->query = "SELECT research_id FROM tbl_extension_research_links WHERE extension_id = '" . $_POST["extensionID"] . "'";
        $links = $object->get_result();
        foreach($links as $link) { $data['linked_projects'][] = (string)$link['research_id']; }

        $object->query = "SELECT id, file_category, file_name, file_path FROM tbl_extension_files WHERE extension_id = '".$_POST["extensionID"]."'";
        $file_result = $object->get_result();
        $files_array = [];
        foreach($file_result as $f) {
            $files_array[] = array('id' => $f['id'], 'category' => $f['file_category'], 'name' => $f['file_name'], 'path' => '../../' . $f['file_path']);
        }
        $data['existing_files'] = $files_array;
        echo json_encode($data);
        exit;
    }

    if ($_POST["action_extension"] == 'Edit') {
        $error = ''; $success = '';
        $start_dateu = date("m-d-Y", strtotime($_POST['start_date_extc']));
        $completed_dateu = date("m-d-Y", strtotime($_POST['completion_date_extc']));
        $ext_id = $_POST['hidden_extensionID'];
        $has_files = $_POST['has_files_extp'];

        $data = array(
            ':title' => $_POST['title_extp'],
            ':start_date' => $start_dateu,
            ':completed_date' => $completed_dateu,
            ':funding_source' => $_POST['funding_source_exct'],
            ':approved_budget' => $_POST['approved_budget_exct'],
            ':target_beneficiaries_communities' => $_POST['target_beneficiaries_communities'],
            ':partners' => $_POST['partners'],
            ':status_exct' => $_POST['status_exct'],
            ':has_files' => $has_files,
            ':hidden_extensionID' => $ext_id
        );

        $object->query = "UPDATE tbl_extension_project_conducted SET title = :title, start_date = :start_date, completed_date = :completed_date, funding_source = :funding_source, approved_budget = :approved_budget, target_beneficiaries_communities = :target_beneficiaries_communities, partners = :partners, status_exct = :status_exct, has_files = :has_files WHERE id = :hidden_extensionID";
        $object->execute($data);

        $object->query = "DELETE FROM tbl_extension_research_links WHERE extension_id = '".$ext_id."'";
        $object->execute();

        if(isset($_POST['linked_research_projects']) && is_array($_POST['linked_research_projects'])) {
            foreach($_POST['linked_research_projects'] as $research_id) {
                $object->query = "INSERT INTO tbl_extension_research_links (extension_id, research_id) VALUES ('".$ext_id."', '".intval($research_id)."')";
                $object->execute();
            }
        }

        if($has_files == 'With' && isset($_FILES['extp_files'])) {
            $categories = isset($_POST['extp_file_categories']) ? $_POST['extp_file_categories'] : [];
            handle_extension_files($object, $ext_id, $categories, $_FILES['extp_files']);
        }
        if($has_files == 'None') {
            $clean_id = intval($ext_id);
            $object->query = "SELECT file_path FROM tbl_extension_files WHERE extension_id = '".$clean_id."'";
            $files_to_delete = $object->get_result();
            foreach($files_to_delete as $file) {
                $physical_path = '../../../' . $file['file_path'];
                if(file_exists($physical_path)) { unlink($physical_path); }
            }
            $object->query = "DELETE FROM tbl_extension_files WHERE extension_id = '".$clean_id."'";
            $object->execute();
        }

        $success = '<div class="alert alert-success">Extension Project Updated</div>';
        echo json_encode(array('error' => $error, 'success' => $success));
        exit;
    }

    if($_POST["action_extension"] == 'delete_file') {
        $file_id = intval($_POST['file_id']);
        $object->query = "SELECT file_path FROM tbl_extension_files WHERE id = '".$file_id."'";
        $file_data = $object->get_result();
        $file_deleted = false;
        foreach($file_data as $row) {
            $file_deleted = true;
            $physical_path = '../../../' . $row['file_path'];
            if(file_exists($physical_path)) { unlink($physical_path); }
        }
        if($file_deleted) {
            $object->query = "DELETE FROM tbl_extension_files WHERE id = '".$file_id."'";
            $object->execute();
            echo json_encode(['status' => 'success', 'message' => 'File deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found.']);
        }
        exit;
    }

    // SOFT DELETE FIX
    if ($_POST["action_extension"] == 'delete') {
        $ext_id = intval($_POST["extensionID"]);
        $object->query = "UPDATE tbl_extension_project_conducted SET status = 0 WHERE id = '" . $ext_id . "'";
        $object->execute();
        echo '<div class="alert alert-success">Extension Project moved to Recycle Bin</div>';
        exit;
    }
}
?>