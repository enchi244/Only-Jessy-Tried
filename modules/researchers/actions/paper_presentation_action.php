<?php
// paper_presentation_action.php
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

if (!function_exists('handle_paper_files')) {
    function handle_paper_files($object, $paper_id, $categories, $files) {
        if(isset($files['name']) && is_array($files['name'])) {
            $upload_dir = '../../../uploads/research_files/';
            if (!file_exists($upload_dir)) { mkdir($upload_dir, 0755, true); }
            
            for($i = 0; $i < count($files['name']); $i++) {
                if($files['error'][$i] == 0) {
                    $category = isset($categories[$i]) ? $categories[$i] : 'Other';
                    $original_name = basename($files['name'][$i]);
                    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                    $safe_name = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($original_name, PATHINFO_FILENAME));
                    $new_name = 'PP_' . $safe_name . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    $target_file = $upload_dir . $new_name;
                    $db_path = 'uploads/research_files/' . $new_name; 
                    if(move_uploaded_file($files['tmp_name'][$i], $target_file)) {
                        $object->query = "INSERT INTO tbl_paper_files (paper_id, file_category, file_name, file_path) VALUES (:ppid, :cat, :fname, :fpath)";
                        $object->execute([':ppid' => $paper_id, ':cat' => $category, ':fname' => $original_name, ':fpath' => $db_path]);
                    }
                }
            }
        }
    }
}

if (isset($_POST["action_paper_presentation"])) {

    if ($_POST["action_paper_presentation"] == 'fetch_all') {
        $order_column = array('tbl_researchdata.familyName', 'tbl_paperpresentation.title', 'tbl_paperpresentation.conference_title', 'tbl_paperpresentation.conference_venue', 'tbl_paperpresentation.date_paper');
        $main_query = "SELECT tbl_paperpresentation.*, tbl_researchdata.id AS author_db_id, tbl_researchdata.firstName, tbl_researchdata.familyName, tbl_researchdata.middleName, tbl_researchdata.Suffix, tbl_researchdata.academic_rank, tbl_researchdata.program AS primary_discipline FROM tbl_paperpresentation LEFT JOIN tbl_researchdata ON tbl_paperpresentation.researcherID = tbl_researchdata.id";        
        $search_query = " WHERE tbl_paperpresentation.status = 1 "; // HIDE TRASH
        
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
            $author_name = $row["familyName"] ? htmlspecialchars($row["familyName"].", ".$row["firstName"]." ".trim($row["middleName"]." ".$row["Suffix"])) : "Unknown Author";
            $rank_badge = !empty($row["academic_rank"]) ? '<span class="badge badge-success px-2 py-1 ml-1 align-text-top" style="font-size:0.65rem;"><i class="fas fa-award"></i> ' . htmlspecialchars($row["academic_rank"]) . '</span>' : '';
            $discipline_badge = !empty($row["primary_discipline"]) ? '<div class="small text-muted mt-1"><i class="fas fa-book-reader mr-1"></i> ' . htmlspecialchars($row["primary_discipline"]) . '</div>' : '';
            $sub_array[] = '<div class="mb-1"><span class="font-weight-bold text-gray-800">'.$author_name.'</span>'.$rank_badge.'</div>'.$discipline_badge;
            $sub_array[] = $row["title"];
            $sub_array[] = $row["conference_title"];
            $sub_array[] = $row["conference_venue"];
            $sub_array[] = parse_legacy_date_php($row["date_paper"]);
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-danger btn-sm delete_master_paper_presentation" data-id="'.$row["id"].'" title="Delete"><i class="far fa-trash-alt"></i></button><a href="view_researcher.php?id='.$row["author_db_id"].'&tab=pp" class="btn d-none"></a></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if ($_POST["action_paper_presentation"] == 'fetch') {
        $order_column = array('title', 'conference_title', 'conference_venue', 'conference_organizer', 'date_paper', 'type', 'discipline', 'has_files');
        $main_query = "SELECT * FROM tbl_paperpresentation";
        
        $search_query = " WHERE researcherID = '" . $_POST["rid"] . "' AND status = 1 "; // HIDE TRASH

        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' OR conference_title LIKE '%" . $search_value . "%') ";
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
            $sub_array[] = $row["conference_title"];
            $sub_array[] = $row["conference_venue"];
            $sub_array[] = $row["conference_organizer"];
            $sub_array[] = parse_legacy_date_php($row["date_paper"]);
            $sub_array[] = $row["type"];
            $sub_array[] = $row["discipline"];
            $sub_array[] = '<div align="center">' . $file_badge . '</div>';
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-primary btn-sm edit_button_paper_presentation" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button> <button type="button" class="btn btn-danger btn-sm delete_button_paper_presentation" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if ($_POST["action_paper_presentation"] == 'Add') {
        $error = ''; $success = '';
        $date_applied = date("m-d-Y", strtotime($_POST['date_paper'])); 
        $has_files = (isset($_FILES['pp_files']) && !empty($_FILES['pp_files']['name'][0])) ? 'With' : 'None';

        $a_link_str = '';
        if(isset($_POST['a_link']) && is_array($_POST['a_link'])) {
            $valid_links = array_filter(array_map('trim', $_POST['a_link']));
            $a_link_str = implode("\n", $valid_links);
        }

        $data = array(
            ':researcherID' => $_POST['hidden_researcherID_pp'],
            ':title' => $_POST['title_pp'],
            ':conference_title' => $_POST['conference_title'],
            ':conference_venue' => $_POST['conference_venue'],
            ':conference_organizer' => $_POST['conference_organizer'],
            ':date_paper' => $date_applied,
            ':type_pp' => $_POST['type_pp'],
            ':discipline' => $_POST['discipline'],
            ':has_files' => $has_files,
            ':a_link' => $a_link_str
        );
        $object->query = "INSERT INTO tbl_paperpresentation (researcherID, title, conference_title, conference_venue, conference_organizer, date_paper, type, discipline, has_files, a_link, status) VALUES (:researcherID, :title, :conference_title, :conference_venue, :conference_organizer, :date_paper, :type_pp, :discipline, :has_files, :a_link, 1)";
        $object->execute($data);
        $new_pp_id = $object->connect->lastInsertId();

        if($has_files == 'With' && isset($_FILES['pp_files'])) {
            $categories = isset($_POST['pp_file_categories']) ? $_POST['pp_file_categories'] : [];
            handle_paper_files($object, $new_pp_id, $categories, $_FILES['pp_files']);
        }

        $success = '<div class="alert alert-success">Paper Presentation Added</div>';
        echo json_encode(array('error' => $error, 'success' => $success));
        exit;
    }

    if ($_POST["action_paper_presentation"] == 'fetch_single') {
        $object->query = "SELECT * FROM tbl_paperpresentation WHERE id = '" . $_POST["paperPresentationID"] . "'";
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
            $data['has_files'] = $row["has_files"];
            $data['a_link'] = $row["a_link"];
        }

        $object->query = "SELECT id, file_category, file_name, file_path FROM tbl_paper_files WHERE paper_id = '".$_POST["paperPresentationID"]."'";
        $file_result = $object->get_result();
        $files_array = [];
        foreach($file_result as $f) {
            $files_array[] = array('id' => $f['id'], 'category' => $f['file_category'], 'name' => $f['file_name'], 'path' => '../../' . $f['file_path']);
        }
        $data['existing_files'] = $files_array;
        echo json_encode($data);
        exit;
    }

    if ($_POST["action_paper_presentation"] == 'Edit') {
        $error = ''; $success = '';
        $date_paperu = date("m-d-Y", strtotime($_POST['date_paper'])); 
        $pp_id = $_POST['hidden_paperPresentationID'];
        $has_files = (isset($_FILES['pp_files']) && !empty($_FILES['pp_files']['name'][0])) ? 'With' : 'None';

        $a_link_str = '';
        if(isset($_POST['a_link']) && is_array($_POST['a_link'])) {
            $valid_links = array_filter(array_map('trim', $_POST['a_link']));
            $a_link_str = implode("\n", $valid_links);
        }

        $data = array(
            ':title' => $_POST['title_pp'],
            ':conference_title' => $_POST['conference_title'],
            ':conference_venue' => $_POST['conference_venue'],
            ':conference_organizer' => $_POST['conference_organizer'],
            ':date_paper' => $date_paperu,
            ':type' => $_POST['type_pp'],
            ':discipline' => $_POST['discipline'],
            ':has_files' => $has_files,
            ':a_link' => $a_link_str,
            ':hidden_paperPresentationID' => $pp_id
        );

        $object->query = "UPDATE tbl_paperpresentation SET title = :title, conference_title = :conference_title, conference_venue = :conference_venue, conference_organizer = :conference_organizer, date_paper = :date_paper, type = :type, discipline = :discipline, has_files = :has_files, a_link = :a_link WHERE id = :hidden_paperPresentationID";
        $object->execute($data);

        if($has_files == 'With' && isset($_FILES['pp_files'])) {
            $categories = isset($_POST['pp_file_categories']) ? $_POST['pp_file_categories'] : [];
            handle_paper_files($object, $pp_id, $categories, $_FILES['pp_files']);
        }

        if($has_files == 'None') {
            $clean_id = intval($pp_id);
            $object->query = "SELECT file_path FROM tbl_paper_files WHERE paper_id = '".$clean_id."'";
            $files_to_delete = $object->get_result();
            foreach($files_to_delete as $file) {
                $physical_path = '../../../' . $file['file_path'];
                if(file_exists($physical_path)) { unlink($physical_path); }
            }
            $object->query = "DELETE FROM tbl_paper_files WHERE paper_id = '".$clean_id."'";
            $object->execute();
        }

        $success = '<div class="alert alert-success">Paper Presentation Updated</div>';
        echo json_encode(array('error' => $error, 'success' => $success));
        exit;
    }

    if($_POST["action_paper_presentation"] == 'delete_file') {
        $file_id = intval($_POST['file_id']);
        $object->query = "SELECT file_path FROM tbl_paper_files WHERE id = '".$file_id."'";
        $file_data = $object->get_result();
        $file_deleted = false;
        foreach($file_data as $row) {
            $file_deleted = true;
            $physical_path = '../../../' . $row['file_path'];
            if(file_exists($physical_path)) { unlink($physical_path); }
        }
        if($file_deleted) {
            $object->query = "DELETE FROM tbl_paper_files WHERE id = '".$file_id."'";
            $object->execute();
            echo json_encode(['status' => 'success', 'message' => 'File deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found.']);
        }
        exit;
    }

    // SOFT DELETE FIX
    if ($_POST["action_paper_presentation"] == 'delete') {
        $pp_id = intval($_POST["paperPresentationID"]);
        $object->query = "UPDATE tbl_paperpresentation SET status = 0 WHERE id = '".$pp_id."'";
        $object->execute();
        echo '<div class="alert alert-success">Paper Presentation moved to Recycle Bin</div>';
        exit;
    }
}
?>