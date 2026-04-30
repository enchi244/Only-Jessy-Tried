<?php
// actions/extension_project_action.php
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

if (isset($_POST["action_extension"])) {
    
    if ($_POST["action_extension"] == 'fetch_all') {
        $order_column = array('tbl_researchdata.familyName', 'tbl_extension_project_conducted.title', 'tbl_extension_project_conducted.funding_source', 'tbl_extension_project_conducted.target_beneficiaries_communities', 'tbl_extension_project_conducted.status_exct');
        $main_query = "SELECT tbl_extension_project_conducted.*, tbl_researchdata.id AS author_db_id, tbl_researchdata.firstName, tbl_researchdata.familyName, tbl_researchdata.middleName, tbl_researchdata.Suffix, tbl_researchdata.academic_rank, tbl_researchdata.program AS primary_discipline, 
         (SELECT COUNT(id) FROM tbl_ext WHERE extension_project_id = tbl_extension_project_conducted.id AND (status = 1 OR status IS NULL)) AS ext_count 
         FROM tbl_extension_project_conducted LEFT JOIN tbl_researchdata ON tbl_extension_project_conducted.researcherID = tbl_researchdata.id";
        $search_query = " WHERE tbl_extension_project_conducted.status = 1 "; 
        
        if (isset($_POST["search"]["value"])) {
            $search_value = addslashes($_POST["search"]["value"]);
            $search_query .= "AND (tbl_extension_project_conducted.title LIKE '%" . $search_value . "%' OR tbl_researchdata.familyName LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY tbl_extension_project_conducted.id DESC ";
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
            $sub_array[] = htmlspecialchars($row["funding_source"]);
            $sub_array[] = htmlspecialchars($row["target_beneficiaries_communities"]);
            $sub_array[] = htmlspecialchars($row["status_exct"]);
            
            $has_extensions = ($row['ext_count'] > 0);
            $btn_class = $has_extensions ? 'btn-info text-white' : 'btn-secondary text-white';
            $btn_attr = $has_extensions ? 'title="View Associated Extensions"' : 'disabled title="No extensions linked to this project"';
            
            $sub_array[] = '<div align="center">
                <a href="view_researcher.php?id='.$row["author_db_id"].'&tab=ext" class="btn ' . $btn_class . ' btn-sm mr-1 isolate-click" ' . $btn_attr . '><i class="fas fa-hands-helping"></i></a>
                <button type="button" class="btn btn-danger btn-sm delete_master_extension_project" data-id="'.$row["id"].'" title="Delete"><i class="far fa-trash-alt"></i></button></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if ($_POST["action_extension"] == 'fetch') {
        $order_column = array('title', 'start_date', 'completed_date', 'funding_source', 'approved_budget', 'target_beneficiaries_communities', 'partners', 'status_exct');
        $main_query = "SELECT ep.*, (SELECT COUNT(id) FROM tbl_rde_files WHERE entity_id = ep.id AND entity_type = 'epc') as file_count, (SELECT COUNT(id) FROM tbl_ext WHERE extension_project_id = ep.id AND (status = 1 OR status IS NULL)) AS ext_count FROM tbl_extension_project_conducted ep";
        $search_query = " WHERE ep.researcherID = '" . intval($_POST["rid"]) . "' AND ep.status = 1 "; 
        
        if (isset($_POST["search"]["value"])) {
            $search_value = addslashes($_POST["search"]["value"]);
            $search_query .= "AND (ep.title LIKE '%" . $search_value . "%' OR ep.funding_source LIKE '%" . $search_value . "%' OR ep.status_exct LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY ep.id ASC ";
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
            $sub_array[] = parse_legacy_date_php($row["start_date"]);
            $sub_array[] = parse_legacy_date_php($row["completed_date"]);
            $sub_array[] = htmlspecialchars($row["funding_source"]);
            $sub_array[] = htmlspecialchars($row["approved_budget"]);
            $sub_array[] = htmlspecialchars($row["target_beneficiaries_communities"]);
            $sub_array[] = htmlspecialchars($row["partners"]);
            $sub_array[] = htmlspecialchars($row["status_exct"]);
            $sub_array[] = '<div align="center">' . $file_badge . '</div>';
            
            $has_extensions = ($row['ext_count'] > 0);
            $btn_class = $has_extensions ? 'btn-info text-white' : 'btn-secondary text-white';
            $btn_attr = $has_extensions ? 'title="View Associated Extensions"' : 'disabled title="No extensions linked to this project"';
            
            $sub_array[] = '<div align="center">
                <button type="button" onclick="$(\'#inner-ext-tab\').tab(\'show\');" class="btn ' . $btn_class . ' btn-sm mr-1" ' . $btn_attr . '><i class="fas fa-hands-helping"></i></button>
                <button type="button" class="btn btn-primary btn-sm edit_button_extension_project mr-1" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button> 
                <button type="button" class="btn btn-danger btn-sm delete_button_extension_project" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button>
            </div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if ($_POST["action_extension"] == 'Add') {
        $start_date = date("Y-m-d", strtotime($_POST['start_date_extc']));
        $completed_date = date("Y-m-d", strtotime($_POST['completion_date_extc']));

        $cover_photo = '';
        if (isset($_FILES["cover_photo"]["name"]) && $_FILES["cover_photo"]["name"] != '') {
            $upload_result = $object->uploadCoverPhoto($_FILES['cover_photo'], '../../../uploads/covers/', 'uploads/covers/');
            if ($upload_result) { $cover_photo = $upload_result; }
        }

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
            ':cover_photo' => $cover_photo
        );
        
        $object->query = "INSERT INTO tbl_extension_project_conducted (researcherID, title, start_date, completed_date, funding_source, approved_budget, target_beneficiaries_communities, partners, status_exct, cover_photo, terminal_report, status) VALUES (:researcherID, :title, :start_date, :completed_date, :funding_source, :approved_budget, :target_beneficiaries_communities, :partners, :status_exct, :cover_photo, 'Legacy Replaced', 1)";
        $object->execute($data);
        $new_extension_id = intval($object->connect->lastInsertId());
        
        if(isset($_POST['linked_research_projects']) && is_array($_POST['linked_research_projects'])) {
            foreach($_POST['linked_research_projects'] as $research_id) {
                $object->query = "INSERT INTO tbl_extension_research_links (extension_id, research_id) VALUES ('".$new_extension_id."', '".intval($research_id)."')";
                $object->execute();
            }
        }
        
        if(isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            $object->handle_generic_files($_FILES['research_files'], $categories, $new_extension_id, '../../../uploads/documents/', 'uploads/documents/', 'epc');
        }
        echo json_encode(array('error' => '', 'success' => '<div class="alert alert-success">Extension Project Added</div>'));
        exit;
    }

    if ($_POST["action_extension"] == 'fetch_single') {
        $ext_id = intval($_POST["extensionID"]);
        $object->query = "SELECT * FROM tbl_extension_project_conducted WHERE id = '$ext_id'";
        $result = $object->get_result();
        $data = array();
        
        foreach ($result as $row) {
            $data['title'] = htmlspecialchars_decode($row["title"]);
            $data['start_date'] = parse_legacy_date_php($row["start_date"]);
            $data['completed_date'] = parse_legacy_date_php($row["completed_date"]);
            $data['funding_source'] = htmlspecialchars_decode($row["funding_source"]);
            $data['approved_budget'] = $row["approved_budget"];
            $data['target_beneficiaries_communities'] = htmlspecialchars_decode($row["target_beneficiaries_communities"]);
            $data['partners'] = htmlspecialchars_decode($row["partners"]);
            $data['status_exct'] = $row["status_exct"];
            $data['cover_photo'] = $row["cover_photo"];
            
            $db_cover = trim($row["cover_photo"] ?? '');
            $data['cover_photo'] = empty($db_cover) ? 'img/default_research_cover.png' : $db_cover;
        }
        
        $data['linked_projects'] = array();
        $object->query = "SELECT research_id FROM tbl_extension_research_links WHERE extension_id = '$ext_id'";
        $links = $object->get_result();
        foreach($links as $link) { $data['linked_projects'][] = (string)$link['research_id']; }
        
        $object->query = "SELECT id, file_category, file_name, file_path FROM tbl_rde_files WHERE entity_id = '$ext_id' AND entity_type = 'epc'";
        $file_result = $object->get_result();
        $files_array = [];
        foreach($file_result as $f) {
            $files_array[] = array('id' => $f['id'], 'category' => htmlspecialchars_decode($f['file_category']), 'name' => htmlspecialchars_decode($f['file_name']), 'path' => '../../' . $f['file_path']);
        }
        $data['existing_files'] = $files_array;
        
        echo json_encode($data);
        exit;
    }

    if ($_POST["action_extension"] == 'Edit') {
        $start_dateu = date("Y-m-d", strtotime($_POST['start_date_extc']));
        $completed_dateu = date("Y-m-d", strtotime($_POST['completion_date_extc']));
        $ext_id = intval($_POST['hidden_extensionID']);

        $cover_photo_query = "";
        if (isset($_FILES["cover_photo"]["name"]) && $_FILES["cover_photo"]["name"] != '') {
            $upload_result = $object->uploadCoverPhoto($_FILES['cover_photo'], '../../../uploads/covers/', 'uploads/covers/');
            if ($upload_result) {
                $cover_photo_query = ", cover_photo = '" . $upload_result . "'";
            }
        }

        $data = array(
            ':title' => $_POST['title_extp'],
            ':start_date' => $start_dateu,
            ':completed_date' => $completed_dateu,
            ':funding_source' => $_POST['funding_source_exct'],
            ':approved_budget' => $_POST['approved_budget_exct'],
            ':target_beneficiaries_communities' => $_POST['target_beneficiaries_communities'],
            ':partners' => $_POST['partners'],
            ':status_exct' => $_POST['status_exct'],
            ':hidden_extensionID' => $ext_id
        );
        
        $object->query = "UPDATE tbl_extension_project_conducted SET title = :title, start_date = :start_date, completed_date = :completed_date, funding_source = :funding_source, approved_budget = :approved_budget, target_beneficiaries_communities = :target_beneficiaries_communities, partners = :partners, status_exct = :status_exct $cover_photo_query WHERE id = :hidden_extensionID";
        $object->execute($data);
        
        $object->query = "DELETE FROM tbl_extension_research_links WHERE extension_id = '".$ext_id."'";
        $object->execute();
        if(isset($_POST['linked_research_projects']) && is_array($_POST['linked_research_projects'])) {
            foreach($_POST['linked_research_projects'] as $research_id) {
                $object->query = "INSERT INTO tbl_extension_research_links (extension_id, research_id) VALUES ('".$ext_id."', '".intval($research_id)."')";
                $object->execute();
            }
        }

        if(isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            $object->handle_generic_files($_FILES['research_files'], $categories, $ext_id, '../../../uploads/documents/', 'uploads/documents/', 'epc');
        }

        echo json_encode(array('error' => '', 'success' => '<div class="alert alert-success">Extension Project Updated</div>'));
        exit;
    }

    if($_POST["action_extension"] == 'delete_file') {
        $success = $object->delete_generic_file($_POST['file_id'], '../../../');
        if($success) {
            echo json_encode(['status' => 'success', 'message' => 'File deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found.']);
        }
        exit;
    }

    if ($_POST["action_extension"] == 'delete') {
        $ext_id = intval($_POST["extensionID"]);
        $object->query = "UPDATE tbl_extension_project_conducted SET status = 0 WHERE id = '" . $ext_id . "'";
        $object->execute();
        echo json_encode(['status' => 'success', 'message' => 'Extension Project moved to Recycle Bin']);
        exit;
    }
}
?>