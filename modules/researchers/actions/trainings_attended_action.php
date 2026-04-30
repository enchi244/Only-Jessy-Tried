<?php
// actions/trainings_attended_action.php
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

if (isset($_POST["action_training"])) {
    
    if ($_POST["action_training"] == 'fetch_all') {
        $order_column = array('primary_familyName', 't.title', 't.type', 't.date_train');
        $main_query = "SELECT t.*, pd.id AS author_db_id, pd.firstName, pd.familyName, pd.middleName, pd.Suffix, pd.academic_rank, pd.program AS primary_discipline FROM tbl_trainingsattended t LEFT JOIN tbl_researchdata pd ON t.researcherID = pd.id";
        $search_query = " WHERE t.status = 1 "; 
        
        if (isset($_POST["search"]["value"])) {
            $search_value = addslashes($_POST["search"]["value"]);
            $search_query .= "AND (t.title LIKE '%" . $search_value . "%' OR pd.familyName LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY t.id DESC ";
        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . intval($_POST['start']) . ', ' . intval($_POST['length']) : "";
        
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
            $sub_array[] = htmlspecialchars($row["title"]);
            $sub_array[] = htmlspecialchars($row["venue"]);
            $sub_array[] = parse_legacy_date_php($row["date_train"]);
            $sub_array[] = htmlspecialchars($row["lvl"]);
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-danger btn-sm delete_master_training" data-id="'.$row["id"].'" title="Delete"><i class="far fa-trash-alt"></i></button><a href="view_researcher.php?id='.$row["author_db_id"].'&tab=tra" class="btn view_button d-none"></a></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if ($_POST["action_training"] == 'fetch') {
        $order_column = array('title', 'type', 'venue', 'date_train', 'lvl', 'type_learning_dev', 'sponsor_org', 'totnh');
        $main_query = "SELECT t.*, (SELECT COUNT(id) FROM tbl_rde_files WHERE entity_id = t.id AND entity_type = 'training') as file_count FROM tbl_trainingsattended t";
        $search_query = " WHERE t.researcherID = '" . intval($_POST["rid"]) . "' AND t.status = 1 "; 
        
        if (isset($_POST["search"]["value"])) {
            $search_value = addslashes($_POST["search"]["value"]);
            $search_query .= "AND (t.title LIKE '%" . $search_value . "%' OR t.type LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY t.id ASC "; 
        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . intval($_POST['start']) . ', ' . intval($_POST['length']) : "";
        
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
            $file_badge = ($row["file_count"] > 0) ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-paperclip mr-1"></i> Files</span>' : '<span class="badge badge-secondary px-2 py-1">None</span>';
            $sub_array = array();
            $sub_array[] = htmlspecialchars($row["title"]);
            $sub_array[] = htmlspecialchars($row["type"]);
            $sub_array[] = htmlspecialchars($row["venue"]);
            $sub_array[] = parse_legacy_date_php($row["date_train"]);
            $sub_array[] = htmlspecialchars($row["lvl"]);
            $sub_array[] = htmlspecialchars($row["type_learning_dev"]);
            $sub_array[] = htmlspecialchars($row["sponsor_org"]);
            $sub_array[] = htmlspecialchars($row["totnh"]);
            $sub_array[] = '<div align="center">' . $file_badge . '</div>';
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-primary btn-sm edit_button_training" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button> <button type="button" class="btn btn-danger btn-sm delete_button_training" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if ($_POST["action_training"] == 'Add') {
        $date_train = date("Y-m-d", strtotime($_POST['date_training'])); 
        $a_link_str = '';
        if(isset($_POST['a_link_training']) && is_array($_POST['a_link_training'])) {
            $valid_links = array_filter(array_map('trim', $_POST['a_link_training']));
            $a_link_str = implode("\n", $valid_links);
        }

        $cover_photo = '';
        if (isset($_FILES["cover_photo"]["name"]) && $_FILES["cover_photo"]["name"] != '') {
            $upload_result = $object->uploadCoverPhoto($_FILES['cover_photo'], '../../../uploads/covers/', 'uploads/covers/');
            if ($upload_result) { $cover_photo = $upload_result; }
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
            ':cover_photo' => $cover_photo,
            ':a_link' => $a_link_str
        );
        
        $object->query = "INSERT INTO tbl_trainingsattended (researcherID, title, type, venue, date_train, lvl, type_learning_dev, sponsor_org, totnh, cover_photo, a_link, status) VALUES (:researcherID, :title, :type, :venue, :date_train, :lvl, :type_learning_dev, :sponsor_org, :totnh, :cover_photo, :a_link, 1)";
        $object->execute($data);
        $new_training_id = intval($object->connect->lastInsertId());

        if(isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            $object->handle_generic_files($_FILES['research_files'], $categories, $new_training_id, '../../../uploads/documents/', 'uploads/documents/', 'training');
        }
        
        echo json_encode(array('error' => '', 'success' => '<div class="alert alert-success">Training Added</div>'));
        exit;
    }

    if ($_POST["action_training"] == 'fetch_single') {
        $object->query = "SELECT * FROM tbl_trainingsattended WHERE id = '" . intval($_POST["trainingID"]) . "'";
        $result = $object->get_result();
        $data = array();
        
        foreach ($result as $row) {
            $data['title'] = htmlspecialchars_decode($row["title"]);
            $data['type'] = htmlspecialchars_decode($row["type"]);
            $data['venue'] = htmlspecialchars_decode($row["venue"]);
            $data['date_train'] = parse_legacy_date_php($row["date_train"]); 
            $data['lvl'] = $row["lvl"];
            $data['type_learning_dev'] = htmlspecialchars_decode($row["type_learning_dev"]);
            $data['sponsor_org'] = htmlspecialchars_decode($row["sponsor_org"]);
            $data['totnh'] = $row["totnh"];
            $data['cover_photo'] = $row["cover_photo"];
            $data['a_link'] = $row["a_link"];
            
            $db_cover = trim($row["cover_photo"] ?? '');
            $data['cover_photo'] = empty($db_cover) ? 'img/default_research_cover.png' : $db_cover;
        }
        
        $object->query = "SELECT id, file_category, file_name, file_path FROM tbl_rde_files WHERE entity_id = '".$_POST["trainingID"]."' AND entity_type = 'training'";
        $file_result = $object->get_result();
        $files_array = [];
        foreach($file_result as $f) {
            $files_array[] = array('id' => $f['id'], 'category' => htmlspecialchars_decode($f['file_category']), 'name' => htmlspecialchars_decode($f['file_name']), 'path' => '../../' . $f['file_path']);
        }
        $data['existing_files'] = $files_array;
        
        echo json_encode($data);
        exit;
    }

    if ($_POST["action_training"] == 'Edit') {
        $date_trainu = date("Y-m-d", strtotime($_POST['date_training'])); 
        $tid = intval($_POST['hidden_trainingID']);
        $a_link_str = '';
        if(isset($_POST['a_link_training']) && is_array($_POST['a_link_training'])) {
            $valid_links = array_filter(array_map('trim', $_POST['a_link_training']));
            $a_link_str = implode("\n", $valid_links);
        }

        $cover_photo_query = "";
        if (isset($_FILES["cover_photo"]["name"]) && $_FILES["cover_photo"]["name"] != '') {
            $upload_result = $object->uploadCoverPhoto($_FILES['cover_photo'], '../../../uploads/covers/', 'uploads/covers/');
            if ($upload_result) {
                $cover_photo_query = ", cover_photo = '" . $upload_result . "'";
            }
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
            ':a_link' => $a_link_str,
            ':hidden_trainingID' => $tid
        );
        
        $object->query = "UPDATE tbl_trainingsattended SET title = :title, type = :type, venue = :venue, date_train = :date_train, lvl = :lvl, type_learning_dev = :type_learning_dev, sponsor_org = :sponsor_org, totnh = :totnh, a_link = :a_link $cover_photo_query WHERE id = :hidden_trainingID";
        $object->execute($data);

        if(isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            $object->handle_generic_files($_FILES['research_files'], $categories, $tid, '../../../uploads/documents/', 'uploads/documents/', 'training');
        }

        echo json_encode(array('error' => '', 'success' => '<div class="alert alert-success">Training Updated</div>'));
        exit;
    }

    if($_POST["action_training"] == 'delete_file') {
        $success = $object->delete_generic_file($_POST['file_id'], '../../../');
        if($success) {
            echo json_encode(['status' => 'success', 'message' => 'File deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found.']);
        }
        exit;
    }

    if ($_POST["action_training"] == 'delete') {
        $tid = intval($_POST["trainingID"]);
        $object->query = "UPDATE tbl_trainingsattended SET status = 0 WHERE id = '".$tid."'";
        $object->execute();
        echo json_encode(['status' => 'success', 'message' => 'Training moved to Recycle Bin']);
        exit;
    }
}
?>