<?php
// trainings_attended_action.php
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

if (!function_exists('handle_training_files')) {
    function handle_training_files($object, $training_id, $categories, $files) {
        if(isset($files['name']) && is_array($files['name'])) {
            $upload_dir = '../../../uploads/research_files/';
            if (!file_exists($upload_dir)) { mkdir($upload_dir, 0755, true); }
            
            for($i = 0; $i < count($files['name']); $i++) {
                if($files['error'][$i] == 0) {
                    $category = isset($categories[$i]) ? $categories[$i] : 'Other';
                    $original_name = basename($files['name'][$i]);
                    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                    $safe_name = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($original_name, PATHINFO_FILENAME));
                    $new_name = 'TRAIN_' . $safe_name . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    $target_file = $upload_dir . $new_name;
                    $db_path = 'uploads/research_files/' . $new_name; 
                    if(move_uploaded_file($files['tmp_name'][$i], $target_file)) {
                        $object->query = "INSERT INTO tbl_training_files (training_id, file_category, file_name, file_path) VALUES (:tid, :cat, :fname, :fpath)";
                        $object->execute([':tid' => $training_id, ':cat' => $category, ':fname' => $original_name, ':fpath' => $db_path]);
                    }
                }
            }
        }
    }
}

if (isset($_POST["action_training"])) {

    if ($_POST["action_training"] == 'fetch_all') {
        $order_column = array('tbl_researchdata.familyName', 'tbl_trainingsattended.title', 'tbl_trainingsattended.type', 'tbl_trainingsattended.date_train');
        $main_query = "SELECT tbl_trainingsattended.*, tbl_researchdata.id AS author_db_id, tbl_researchdata.firstName, tbl_researchdata.familyName, tbl_researchdata.middleName, tbl_researchdata.Suffix FROM tbl_trainingsattended LEFT JOIN tbl_researchdata ON tbl_trainingsattended.researcherID = tbl_researchdata.id";
        
        $search_query = " WHERE tbl_trainingsattended.status = 1 "; // HIDE TRASH
        
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (tbl_trainingsattended.title LIKE '%" . $search_value . "%' OR tbl_researchdata.familyName LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY tbl_trainingsattended.id DESC ";
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
            $sub_array[] = $row["type"];
            $sub_array[] = parse_legacy_date_php($row["date_train"]);
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-danger btn-sm delete_master_training" data-id="'.$row["id"].'" title="Delete"><i class="far fa-trash-alt"></i></button><a href="view_researcher.php?id='.$row["author_db_id"].'&tab=tra" class="btn view_button d-none"></a></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if ($_POST["action_training"] == 'fetch') {
        $order_column = array('title', 'type', 'venue', 'date_train', 'lvl', 'type_learning_dev', 'sponsor_org', 'totnh', 'has_files');
        $main_query = "SELECT * FROM tbl_trainingsattended";
        
        $search_query = " WHERE researcherID = '" . $_POST["rid"] . "' AND status = 1 "; // HIDE TRASH

        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' OR type LIKE '%" . $search_value . "%') ";
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
            $file_badge = ($row["has_files"] == 'With') ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-paperclip mr-1"></i> Files</span>' : '<span class="badge badge-secondary px-2 py-1">None</span>';
            $sub_array = array();
            $sub_array[] = $row["title"];
            $sub_array[] = $row["type"];
            $sub_array[] = $row["venue"];
            $sub_array[] = parse_legacy_date_php($row["date_train"]);
            $sub_array[] = $row["lvl"];
            $sub_array[] = $row["type_learning_dev"];
            $sub_array[] = $row["sponsor_org"];
            $sub_array[] = $row["totnh"];
            $sub_array[] = '<div align="center">' . $file_badge . '</div>';
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-primary btn-sm edit_button_training" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button> <button type="button" class="btn btn-danger btn-sm delete_button_training" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if ($_POST["action_training"] == 'Add') {
        $error = ''; $success = '';
        $date_train = date("m-d-Y", strtotime($_POST['date_training'])); 
        $has_files = $_POST['has_files_training'];

        $a_link_str = '';
        if(isset($_POST['a_link_training']) && is_array($_POST['a_link_training'])) {
            $valid_links = array_filter(array_map('trim', $_POST['a_link_training']));
            $a_link_str = implode("\n", $valid_links);
        }

        $data = array(
            ':researcherID' => $_POST['hidden_researcherID_training'],
            ':title' => $_POST['title_training'],
            ':type' => $_POST['type_training'],
            ':venue' => $_POST['venue_training'],
            ':date_train' => $date_train,
            ':lvl' => $_POST['level_training'],
            ':type_learning_dev' => $_POST['type_learning_dev'],
            ':sponsor_org' => $_POST['sponsor_org'],
            ':totnh' => $_POST['total_hours_training'],
            ':has_files' => $has_files,
            ':a_link' => $a_link_str
        );
        $object->query = "INSERT INTO tbl_trainingsattended (researcherID, title, type, venue, date_train, lvl, type_learning_dev, sponsor_org, totnh, has_files, a_link, status) VALUES (:researcherID, :title, :type, :venue, :date_train, :lvl, :type_learning_dev, :sponsor_org, :totnh, :has_files, :a_link, 1)";
        $object->execute($data);
        $new_training_id = $object->connect->lastInsertId();

        if($has_files == 'With' && isset($_FILES['training_files'])) {
            $categories = isset($_POST['training_file_categories']) ? $_POST['training_file_categories'] : [];
            handle_training_files($object, $new_training_id, $categories, $_FILES['training_files']);
        }

        $success = '<div class="alert alert-success">Training Added</div>';
        echo json_encode(array('error' => $error, 'success' => $success));
        exit;
    }

    if ($_POST["action_training"] == 'fetch_single') {
        $object->query = "SELECT * FROM tbl_trainingsattended WHERE id = '" . $_POST["trainingID"] . "'";
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
            $data['has_files'] = $row["has_files"];
            $data['a_link'] = $row["a_link"];
        }

        $object->query = "SELECT id, file_category, file_name, file_path FROM tbl_training_files WHERE training_id = '".$_POST["trainingID"]."'";
        $file_result = $object->get_result();
        $files_array = [];
        foreach($file_result as $f) {
            $files_array[] = array('id' => $f['id'], 'category' => $f['file_category'], 'name' => $f['file_name'], 'path' => '../../' . $f['file_path']);
        }
        $data['existing_files'] = $files_array;
        echo json_encode($data);
        exit;
    }

    if ($_POST["action_training"] == 'Edit') {
        $error = ''; $success = '';
        $date_trainu = date("m-d-Y", strtotime($_POST['date_training'])); 
        $tid = $_POST['hidden_trainingID'];
        $has_files = $_POST['has_files_training'];

        $a_link_str = '';
        if(isset($_POST['a_link_training']) && is_array($_POST['a_link_training'])) {
            $valid_links = array_filter(array_map('trim', $_POST['a_link_training']));
            $a_link_str = implode("\n", $valid_links);
        }

        $data = array(
            ':title' => $_POST['title_training'],
            ':type' => $_POST['type_training'],
            ':venue' => $_POST['venue_training'],
            ':date_train' => $date_trainu,
            ':lvl' => $_POST['level_training'],
            ':type_learning_dev' => $_POST['type_learning_dev'],
            ':sponsor_org' => $_POST['sponsor_org'],
            ':totnh' => $_POST['total_hours_training'],
            ':has_files' => $has_files,
            ':a_link' => $a_link_str,
            ':hidden_trainingID' => $tid
        );

        $object->query = "UPDATE tbl_trainingsattended SET title = :title, type = :type, venue = :venue, date_train = :date_train, lvl = :lvl, type_learning_dev = :type_learning_dev, sponsor_org = :sponsor_org, totnh = :totnh, has_files = :has_files, a_link = :a_link WHERE id = :hidden_trainingID";
        $object->execute($data);

        if($has_files == 'With' && isset($_FILES['training_files'])) {
            $categories = isset($_POST['training_file_categories']) ? $_POST['training_file_categories'] : [];
            handle_training_files($object, $tid, $categories, $_FILES['training_files']);
        }

        if($has_files == 'None') {
            $clean_id = intval($tid);
            $object->query = "SELECT file_path FROM tbl_training_files WHERE training_id = '".$clean_id."'";
            $files_to_delete = $object->get_result();
            foreach($files_to_delete as $file) {
                $physical_path = '../../../' . $file['file_path'];
                if(file_exists($physical_path)) { unlink($physical_path); }
            }
            $object->query = "DELETE FROM tbl_training_files WHERE training_id = '".$clean_id."'";
            $object->execute();
        }

        $success = '<div class="alert alert-success">Training Updated</div>';
        echo json_encode(array('error' => $error, 'success' => $success));
        exit;
    }

    if($_POST["action_training"] == 'delete_file') {
        $file_id = intval($_POST['file_id']);
        $object->query = "SELECT file_path FROM tbl_training_files WHERE id = '".$file_id."'";
        $file_data = $object->get_result();
        $file_deleted = false;
        foreach($file_data as $row) {
            $file_deleted = true;
            $physical_path = '../../../' . $row['file_path'];
            if(file_exists($physical_path)) { unlink($physical_path); }
        }
        if($file_deleted) {
            $object->query = "DELETE FROM tbl_training_files WHERE id = '".$file_id."'";
            $object->execute();
            echo json_encode(['status' => 'success', 'message' => 'File deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found.']);
        }
        exit;
    }

    // SOFT DELETE FIX
    if ($_POST["action_training"] == 'delete') {
        $tid = intval($_POST["trainingID"]);
        $object->query = "UPDATE tbl_trainingsattended SET status = 0 WHERE id = '".$tid."'";
        $object->execute();
        echo '<div class="alert alert-success">Training moved to Recycle Bin</div>';
        exit;
    }
}
?>