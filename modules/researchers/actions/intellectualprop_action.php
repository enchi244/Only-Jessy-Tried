<?php
// intellectualprop_action.php
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

if (isset($_POST["action_intellectualprop"])) {

    if ($_POST["action_intellectualprop"] == 'fetch_all') {
        $order_column = array('primary_familyName', 'ip.title', 'ip.type', 'ip.date_granted');
        $main_query = "
            SELECT ip.*, 
                   (SELECT GROUP_CONCAT(CONCAT(d.familyName, ', ', d.firstName) SEPARATOR ' | ') FROM tbl_ip_collaborators col JOIN tbl_researchdata d ON col.researcher_id = d.id WHERE col.ip_id = ip.id) AS all_authors,
                   pd.id AS author_db_id, pd.familyName AS primary_familyName, pd.academic_rank, pd.program AS primary_discipline
            FROM tbl_itelectualprop ip
            LEFT JOIN tbl_researchdata pd ON (pd.id = ip.lead_researcher_id OR pd.id = ip.researcherID OR pd.researcherID = ip.researcherID)
        ";
        
        $search_query = " WHERE ip.status = 1 "; // HIDE TRASH
        
        if (isset($_POST["search"]["value"])) {
            $search_value = addslashes($_POST["search"]["value"]);
            $search_query .= "AND (ip.title LIKE '%" . $search_value . "%' OR pd.familyName LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY ip.id DESC ";
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
            $primary_author = $row["primary_familyName"] ? $row["primary_familyName"] : "<span class='text-danger'>Unknown</span>";
            $co_authors = $row["all_authors"] ? $row["all_authors"] : (!empty($row["coauth"]) ? htmlspecialchars($row["coauth"]) : "<span class='text-muted'>None</span>");
            $rank_badge = !empty($row["academic_rank"]) ? '<span class="badge badge-success px-2 py-1 ml-1 align-text-top" style="font-size:0.65rem;"><i class="fas fa-award"></i> ' . htmlspecialchars($row["academic_rank"]) . '</span>' : '';
            $discipline_badge = !empty($row["primary_discipline"]) ? '<div class="small text-muted mt-1"><i class="fas fa-book-reader mr-1"></i> ' . htmlspecialchars($row["primary_discipline"]) . '</div>' : '';
            $sub_array[] = '<div class="mb-1"><span class="font-weight-bold text-gray-800">'.$primary_author.'</span>'.$rank_badge.'</div>'.$discipline_badge;
            $sub_array[] = htmlspecialchars($row["title"]);
            $sub_array[] = $co_authors;
            $sub_array[] = htmlspecialchars($row["type"]);
            $sub_array[] = parse_legacy_date_php($row["date_granted"]);
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-danger btn-sm delete_master_intellectualprop" data-id="'.$row["id"].'" title="Delete"><i class="far fa-trash-alt"></i></button><a href="view_researcher.php?id='.$row["author_db_id"].'&tab=ip" class="btn view_button d-none"></a></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if ($_POST["action_intellectualprop"] == 'fetch') {
        $order_column = array('ip.title', 'ip.type', 'ip.date_applied', 'ip.date_granted', 'ip.has_files');
        $main_query = "SELECT ip.* FROM tbl_itelectualprop ip JOIN tbl_ip_collaborators col ON ip.id = col.ip_id";
        
        $search_query = " WHERE col.researcher_id = '" . intval($_POST["rid"]) . "' AND ip.status = 1 "; 

        if (isset($_POST["search"]["value"])) {
            $search_value = addslashes($_POST["search"]["value"]);
            $search_query .= "AND (ip.title LIKE '%" . $search_value . "%' OR ip.type LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : "ORDER BY ip.id ASC "; 
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
            $file_badge = ($row["has_files"] == 'With') ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-paperclip mr-1"></i> Files</span>' : '<span class="badge badge-secondary px-2 py-1">None</span>';
            $sub_array = array();
            $sub_array[] = htmlspecialchars($row["title"]);
            $sub_array[] = htmlspecialchars($row["type"]);
            $sub_array[] = parse_legacy_date_php($row["date_applied"]);
            $sub_array[] = parse_legacy_date_php($row["date_granted"]);
            $sub_array[] = '<div align="center">' . $file_badge . '</div>';
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-primary btn-sm edit_button_intellectualprop" data-id="' . $row["id"] . '"><i class="fas fa-pencil-alt"></i></button> <button type="button" class="btn btn-danger btn-sm delete_button_intellectualprop" data-id="' . $row["id"] . '"><i class="far fa-trash-alt"></i></button></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if ($_POST["action_intellectualprop"] == 'Add') {
        $error = ''; $success = '';
        $date_applied = date("Y-m-d", strtotime($_POST['date_applied']));
        $date_granted = date("Y-m-d", strtotime($_POST['date_granted']));
        $lead_researcher_id = intval($_POST['lead_researcher_id_ip']);
        $has_files = (isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) ? 'With' : 'None';

        $a_link_str = '';
        if(isset($_POST['a_link_ip']) && is_array($_POST['a_link_ip'])) {
            $valid_links = array_filter(array_map('trim', $_POST['a_link_ip']));
            $a_link_str = implode("\n", $valid_links);
        }

        $data = array(
            ':researcherID' => $lead_researcher_id,
            ':lead_researcher_id' => $lead_researcher_id,
            ':title' => $_POST['title_ip'],
            ':coauth' => 'Legacy Replaced', 
            ':type' => $_POST['type_ip'],
            ':date_applied' => $date_applied,
            ':date_granted' => $date_granted,
            ':has_files' => $has_files,
            ':a_link' => $a_link_str
        );

        $object->query = "INSERT INTO tbl_itelectualprop (researcherID, lead_researcher_id, title, coauth, type, date_applied, date_granted, has_files, a_link, status) VALUES (:researcherID, :lead_researcher_id, :title, :coauth, :type, :date_applied, :date_granted, :has_files, :a_link, 1)";
        $object->execute($data);
        $new_ip_id = intval($object->connect->lastInsertId());

        $collaborators = isset($_POST['collaborators_ip']) ? $_POST['collaborators_ip'] : [];
        if (!in_array($lead_researcher_id, $collaborators)) { $collaborators[] = $lead_researcher_id; }
        foreach($collaborators as $res_id) {
            $object->query = "INSERT INTO tbl_ip_collaborators (ip_id, researcher_id) VALUES (:ipid, :uid)";
            $object->execute([':ipid' => $new_ip_id, ':uid' => intval($res_id)]);
        }

        if($has_files == 'With') {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            $object->handle_generic_files($_FILES['research_files'], $categories, $new_ip_id, '../../../uploads/research_files/', 'uploads/research_files/', 'tbl_ip_files', 'ip_id');
        }

        $success = '<div class="alert alert-success">Intellectual Property Added</div>';
        echo json_encode(array('error' => $error, 'success' => $success));
        exit;
    }

    if ($_POST["action_intellectualprop"] == 'fetch_single') {
        $ip_id = intval($_POST["intellectualPropID"]);
        $object->query = "SELECT * FROM tbl_itelectualprop WHERE id = '$ip_id'";
        $result = $object->get_result();
        $data = array();
        foreach ($result as $row) {
            $data['title'] = htmlspecialchars_decode($row["title"]);
            $data['type'] = $row["type"];
            $data['date_applied'] = parse_legacy_date_php($row["date_applied"]);
            $data['date_granted'] = parse_legacy_date_php($row["date_granted"]);
            $data['lead_researcher_id'] = $row["lead_researcher_id"];
            $data['has_files'] = $row["has_files"];
            $data['a_link'] = $row["a_link"];
        }

        $object->query = "SELECT researcher_id FROM tbl_ip_collaborators WHERE ip_id = '$ip_id'";
        $collab_result = $object->get_result();
        $collab_array = [];
        foreach($collab_result as $c) { $collab_array[] = $c['researcher_id']; }
        $data['collaborators'] = $collab_array;

        $object->query = "SELECT id, file_category, file_name, file_path FROM tbl_ip_files WHERE ip_id = '$ip_id'";
        $file_result = $object->get_result();
        $files_array = [];
        foreach($file_result as $f) {
            $files_array[] = array('id' => $f['id'], 'category' => htmlspecialchars_decode($f['file_category']), 'name' => htmlspecialchars_decode($f['file_name']), 'path' => '../../' . $f['file_path']);
        }
        $data['existing_files'] = $files_array;
        echo json_encode($data);
        exit;
    }

    if ($_POST["action_intellectualprop"] == 'Edit') {
        $error = ''; $success = '';
        $date_appliedu = date("Y-m-d", strtotime($_POST['date_applied']));
        $date_grantedu = date("Y-m-d", strtotime($_POST['date_granted']));
        $ip_id = intval($_POST['hidden_intellectualPropID']);
        $lead_researcher_id = intval($_POST['lead_researcher_id_ip']);

        $a_link_str = '';
        if(isset($_POST['a_link_ip']) && is_array($_POST['a_link_ip'])) {
            $valid_links = array_filter(array_map('trim', $_POST['a_link_ip']));
            $a_link_str = implode("\n", $valid_links);
        }

        // BUG FIXED HERE: Removed the crashing $has_files variable and query binding.
        
        $data = array(
            ':lead_researcher_id' => $lead_researcher_id,
            ':title' => $_POST['title_ip'],
            ':type' => $_POST['type_ip'],
            ':date_applied' => $date_appliedu,
            ':date_granted' => $date_grantedu,
            ':a_link' => $a_link_str,
            ':hidden_intellectualPropID' => $ip_id
        );

        $object->query = "UPDATE tbl_itelectualprop SET lead_researcher_id = :lead_researcher_id, title = :title, type = :type, date_applied = :date_applied, date_granted = :date_granted, a_link = :a_link WHERE id = :hidden_intellectualPropID";
        $object->execute($data);

        $object->query = "DELETE FROM tbl_ip_collaborators WHERE ip_id = :ipid";
        $object->execute([':ipid' => $ip_id]);

        $collaborators = isset($_POST['collaborators_ip']) ? $_POST['collaborators_ip'] : [];
        if (!in_array($lead_researcher_id, $collaborators)) { $collaborators[] = $lead_researcher_id; }
        foreach($collaborators as $res_id) {
            $object->query = "INSERT INTO tbl_ip_collaborators (ip_id, researcher_id) VALUES (:ipid, :uid)";
            $object->execute([':ipid' => $ip_id, ':uid' => intval($res_id)]);
        }

        if(isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            $object->handle_generic_files($_FILES['research_files'], $categories, $ip_id, '../../../uploads/research_files/', 'uploads/research_files/', 'tbl_ip_files', 'ip_id');
        }

        // Sync Status Dynamically (This takes care of everything automatically)
        $object->update_generic_has_files($ip_id, 'tbl_itelectualprop', 'tbl_ip_files', 'ip_id');

        $success = '<div class="alert alert-success">Intellectual Property Updated</div>';
        echo json_encode(array('error' => $error, 'success' => $success));
        exit;
    }

    if($_POST["action_intellectualprop"] == 'delete_file') {
        $success = $object->delete_generic_file($_POST['file_id'], 'tbl_ip_files', 'tbl_itelectualprop', 'ip_id', '../../../');
        if($success) {
            echo json_encode(['status' => 'success', 'message' => 'File deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found.']);
        }
        exit;
    }

    if ($_POST["action_intellectualprop"] == 'delete') {
        $ip_id = intval($_POST["intellectualPropID"]);
        $object->query = "UPDATE tbl_itelectualprop SET status = 0 WHERE id = '".$ip_id."'";
        $object->execute();
        echo json_encode(['status' => 'success', 'message' => 'Intellectual Property moved to Recycle Bin']);
        exit;
    }
}
?>