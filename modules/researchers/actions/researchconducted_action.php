<?php
//researchconducted_action.php
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

if (!function_exists('handle_research_files')) {
    function handle_research_files($object, $research_id, $categories, $files) {
        if(isset($files['name']) && is_array($files['name'])) {
            $upload_dir = '../../../uploads/research_files/';
            if (!file_exists($upload_dir)) { mkdir($upload_dir, 0755, true); }
            
            foreach($files['name'] as $input_index => $name_data) {
                if(is_array($name_data)) {
                    // Handled by 'multiple' attribute (3D Array)
                    foreach($name_data as $file_index => $actual_name) {
                        if($files['error'][$input_index][$file_index] == 0) {
                            $category = isset($categories[$input_index]) ? $categories[$input_index] : 'Other';
                            $original_name = basename($actual_name);
                            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                            $safe_name = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($original_name, PATHINFO_FILENAME));
                            $new_name = $safe_name . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                            $target_file = $upload_dir . $new_name;
                            $db_path = 'uploads/research_files/' . $new_name; 
                            if(move_uploaded_file($files['tmp_name'][$input_index][$file_index], $target_file)) {
                                $object->query = "INSERT INTO tbl_research_files (research_id, file_category, file_name, file_path) VALUES (:rid, :cat, :fname, :fpath)";
                                $object->execute([':rid' => $research_id, ':cat' => $category, ':fname' => $original_name, ':fpath' => $db_path]);
                            }
                        }
                    }
                } else {
                    // Fallback for single file selection (2D Array)
                    if($files['error'][$input_index] == 0) {
                        $category = isset($categories[$input_index]) ? $categories[$input_index] : 'Other';
                        $original_name = basename($name_data);
                        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                        $safe_name = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($original_name, PATHINFO_FILENAME));
                        $new_name = $safe_name . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                        $target_file = $upload_dir . $new_name;
                        $db_path = 'uploads/research_files/' . $new_name; 
                        if(move_uploaded_file($files['tmp_name'][$input_index], $target_file)) {
                            $object->query = "INSERT INTO tbl_research_files (research_id, file_category, file_name, file_path) VALUES (:rid, :cat, :fname, :fpath)";
                            $object->execute([':rid' => $research_id, ':cat' => $category, ':fname' => $original_name, ':fpath' => $db_path]);
                        }
                    }
                }
            }
        }
    }
}

// --- NEW FUNCTION MOVED OUTSIDE THE IF STATEMENT ---
if (!function_exists('update_has_files_status')) {
    function update_has_files_status($object, $research_id) {
        // Count how many files actually exist for this research
        $object->query = "SELECT COUNT(*) as file_count FROM tbl_research_files WHERE research_id = :rid";
        $object->execute([':rid' => $research_id]);
        $result = $object->get_result();
        
        $status = ($result[0]['file_count'] > 0) ? 'With' : 'None';
        
        // Force the main table to update its status
        $object->query = "UPDATE tbl_researchconducted SET has_files = :status WHERE id = :rid";
        $object->execute([':status' => $status, ':rid' => $research_id]);
    }
}
// --- END OF NEW FUNCTION ---

if(isset($_POST["action_researchedconducted"])) {

    if($_POST["action_researchedconducted"] == 'fetch_collaborators') {
        header('Content-Type: application/json');
        $id = intval($_POST['id']);
        $object->query = "SELECT d.id, d.firstName, d.familyName, d.department FROM tbl_research_collaborators col JOIN tbl_researchdata d ON col.researcher_id = d.id WHERE col.research_id = '".$id."'";
        $result = $object->get_result();
        $collaborators_array = array();
        foreach($result as $row) { $collaborators_array[] = array('id' => $row['id'], 'firstName' => $row['firstName'], 'familyName' => $row['familyName'], 'department' => $row['department']); }
        echo json_encode($collaborators_array);
        exit;
    }

    if($_POST["action_researchedconducted"] == 'fetch_all') {
        $order_column = array('primary_familyName', 'rc.title', 'rc.research_agenda_cluster', 'rc.sdgs', 'rc.stat');
        $main_query = "
            SELECT rc.*, 
                   (SELECT GROUP_CONCAT(CONCAT(d.familyName, ', ', d.firstName) SEPARATOR ' | ') FROM tbl_research_collaborators col JOIN tbl_researchdata d ON col.researcher_id = d.id WHERE col.research_id = rc.id) AS all_authors,
                   pd.id AS author_db_id, pd.familyName AS primary_familyName, pd.academic_rank, pd.program AS primary_discipline
            FROM tbl_researchconducted rc
            LEFT JOIN tbl_researchdata pd ON (pd.id = rc.lead_researcher_id OR pd.id = rc.researcherID OR pd.researcherID = rc.researcherID)
        ";
        
        $search_query = " WHERE rc.status = 1 "; // HIDE TRASH

        if (isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= " AND (rc.title LIKE '%" . $search_value . "%' OR pd.familyName LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? " ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : " ORDER BY rc.id DESC ";
        $limit_query = ($_POST["length"] != -1) ? ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'] : "";

        $object->query = $main_query . $search_query . $order_query;
        $object->execute();
        $filtered_rows = $object->row_count();
        $object->query .= $limit_query;
        $result = $object->get_result();
        $object->query = $main_query;
        $object->execute();
        $total_rows = $object->row_count();

        $data = array();
        foreach($result as $row) {
            $sub_array = array();
            $author_db_id = $row["author_db_id"] ? $row["author_db_id"] : 0; 
            $primary_author = $row["primary_familyName"] ? $row["primary_familyName"] : "<span class='text-danger'>Unknown Lead</span>";
            $co_authors = $row["all_authors"] ? $row["all_authors"] : "<span class='text-muted'>None</span>";
            $rank_badge = !empty($row["academic_rank"]) ? '<span class="badge badge-success px-2 py-1 ml-1 align-text-top" style="font-size:0.65rem;"><i class="fas fa-award"></i> ' . htmlspecialchars($row["academic_rank"]) . '</span>' : '';
            $discipline_badge = !empty($row["primary_discipline"]) ? '<div class="small text-muted mt-1 mb-1"><i class="fas fa-book-reader mr-1"></i> ' . htmlspecialchars($row["primary_discipline"]) . '</div>' : '';
            $author_display = '<div class="mb-1"><span class="badge badge-primary px-2 py-1 mr-1">Lead</span> <span class="font-weight-bold text-gray-800">' . $primary_author . '</span>' . $rank_badge . '</div>' . $discipline_badge . '<div class="small text-muted" style="line-height: 1.2;"><i class="fas fa-users mr-1"></i> ' . $co_authors . '</div>';
            $sub_array[] = $author_display;
            $sub_array[] = $row["title"];
            $sub_array[] = $row["research_agenda_cluster"];
            $sub_array[] = $row["sdgs"];
            $sub_array[] = $row["stat"];
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-info btn-sm view_collaborators" data-id="'.$row["id"].'" title="View Collaborators"><i class="fas fa-users"></i></button> <button type="button" class="btn btn-danger btn-sm delete_master_researchconducted" data-id="'.$row["id"].'" title="Delete Record"><i class="far fa-trash-alt"></i></button><a href="view_researcher.php?id='.$author_db_id.'&tab=education" class="btn d-none"></a></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
        exit;
    }

    if($_POST["action_researchedconducted"] == 'fetch') {
        $order_column = array('rc.id', 'rc.title', 'rc.research_agenda_cluster', 'rc.sdgs', 'rc.started_date', 'rc.completed_date', 'rc.funding_source', 'rc.approved_budget', 'rc.stat', 'rc.has_files');
        $main_query = "SELECT rc.* FROM tbl_researchconducted rc JOIN tbl_research_collaborators col ON rc.id = col.research_id";
        
        $search_query = " WHERE col.researcher_id = '".$_POST["rid"]."' AND rc.status = 1 "; // HIDE TRASH

        if (isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= " AND (rc.title LIKE '%" . $search_value . "%') ";
        }
        $order_query = isset($_POST["order"]) ? " ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : " ORDER BY rc.id DESC ";
        $limit_query = ($_POST["length"] != -1) ? ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'] : "";

        $object->query = $main_query . $search_query . $order_query;
        $object->execute();
        $filtered_rows = $object->row_count();
        $object->query .= $limit_query;
        $result = $object->get_result();
        $object->query = $main_query . $search_query;
        $object->execute();
        $total_rows = $object->row_count();

        $data = array();
        foreach($result as $row) {
            $file_badge = ($row["has_files"] == 'With') ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-paperclip mr-1"></i> Files</span>' : '<span class="badge badge-secondary px-2 py-1">None</span>';
            $sub_array = array();
            $sub_array[] = $row["title"];
            $sub_array[] = $row["research_agenda_cluster"];
            $sub_array[] = $row["sdgs"];
            $sub_array[] = parse_legacy_date_php($row["started_date"]);
            $sub_array[] = parse_legacy_date_php($row["completed_date"]);
            $sub_array[] = $row["funding_source"];
            $sub_array[] = $row["approved_budget"];
            $sub_array[] = $row["stat"];
            $sub_array[] = '<div align="center">' . $file_badge . '</div>';
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-primary btn-sm edit_buttonrc edit_button_researchconducted" data-id="'.$row["id"].'"><i class="fas fa-pencil-alt"></i></button> <button type="button" class="btn btn-danger btn-sm delete_buttonrc" data-id="'.$row["id"].'"><i class="far fa-trash-alt"></i></button></div>';
            $data[] = $sub_array;
        }
        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
    }

    if($_POST["action_researchedconducted"] == 'Add') {
        $error = ''; $success = '';
        $from_date = date("Y-m-d", strtotime($_POST['started_date']));
        $to_date = date("Y-m-d", strtotime($_POST['completed_date']));
        $lead_researcher_id = $_POST['lead_researcher_id'];
        $has_files = (isset($_FILES['research_files']) && count($_FILES['research_files']['name']) > 0) ? 'With' : 'None';

        $data = array(
                ':researcherID'                => $lead_researcher_id, 
                ':lead_researcher_id'          => $lead_researcher_id,
                ':title'                       => $_POST['title'],
                ':research_agenda_cluster'     => $_POST['research_agenda_cluster'],
                ':sdgs'                        => implode(", ", $_POST['sdgs']), 
                ':started_date'                => $from_date,
                ':completed_date'              => $to_date,
                ':funding_source'              => $_POST['funding_source'],
                ':approved_budget'             => $_POST['approved_budget'],
                ':stat'                        => $_POST['stat'],
                ':has_files'                   => $has_files,
                ':terminal_report'             => 'Legacy Replaced' 
        );

        $object->query = "INSERT INTO tbl_researchconducted (researcherID, lead_researcher_id, title, research_agenda_cluster, sdgs, started_date, completed_date, funding_source, approved_budget, stat, has_files, terminal_report, status) VALUES (:researcherID, :lead_researcher_id, :title, :research_agenda_cluster, :sdgs, :started_date, :completed_date, :funding_source, :approved_budget, :stat, :has_files, :terminal_report, 1)";
        $object->execute($data);
        $new_research_id = $object->connect->lastInsertId();

        $collaborators = isset($_POST['collaborators']) ? $_POST['collaborators'] : [];
        if (!in_array($lead_researcher_id, $collaborators)) { $collaborators[] = $lead_researcher_id; }
        foreach($collaborators as $res_id) {
            $object->query = "INSERT INTO tbl_research_collaborators (research_id, researcher_id) VALUES (:rid, :uid)";
            $object->execute([':rid' => $new_research_id, ':uid' => $res_id]);
        }

        if($has_files == 'With' && isset($_FILES['research_files'])) {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            handle_research_files($object, $new_research_id, $categories, $_FILES['research_files']);
        }

        $success = '<div class="alert alert-success">Project and Files Added Successfully</div>';
        echo json_encode(array('error' => $error, 'success' => $success));
    }

    if($_POST["action_researchedconducted"] == 'fetch_single') {
        $object->query = "SELECT * FROM tbl_researchconducted WHERE id = '".$_POST["rcid"]."'";
        $result = $object->get_result();
        $data = array();
        foreach($result as $row) {
            $data['title'] = $row["title"];
            $data['research_agenda_cluster'] = $row["research_agenda_cluster"];
            $data['sdgs'] = $row["sdgs"];
            $data['started_date'] = parse_legacy_date_php($row["started_date"]);
            $data['completed_date'] = parse_legacy_date_php($row["completed_date"]);
            $data['funding_source'] = $row["funding_source"];
            $data['approved_budget'] = $row["approved_budget"];
            $data['stat'] = $row["stat"];
            $data['has_files'] = $row["has_files"];
            $data['lead_researcher_id'] = $row["lead_researcher_id"];
        }

        $object->query = "SELECT researcher_id FROM tbl_research_collaborators WHERE research_id = '".$_POST["rcid"]."'";
        $collab_result = $object->get_result();
        $collab_array = [];
        foreach($collab_result as $c) { $collab_array[] = $c['researcher_id']; }
        $data['collaborators'] = $collab_array;

        $object->query = "SELECT id, file_category, file_name, file_path FROM tbl_research_files WHERE research_id = '".$_POST["rcid"]."'";
        $file_result = $object->get_result();
        $files_array = [];
        foreach($file_result as $f) {
            $files_array[] = array('id' => $f['id'], 'category' => $f['file_category'], 'name' => $f['file_name'], 'path' => '../../' . $f['file_path']);
        }
        $data['existing_files'] = $files_array;
        echo json_encode($data);
    }

    if ($_POST["action_researchedconducted"] == 'Edit') {
        $from_dateu = date("Y-m-d", strtotime($_POST['started_date']));  
        $to_dateu = date("Y-m-d", strtotime($_POST['completed_date'])); 
        $error = ''; $success = '';
        $research_id = $_POST['hidden_id_researchedconducted'];
        $lead_researcher_id = $_POST['lead_researcher_id'];

        $data = array(
            ':lead_researcher_id' => $lead_researcher_id,
            ':title' => $_POST['title'],
            ':research_agenda_cluster' => $_POST['research_agenda_cluster'],
            ':sdgs'   => implode(", ", $_POST['sdgs']), 
            ':started_date' => $from_dateu,  
            ':completed_date' => $to_dateu,  
            ':funding_source' => $_POST['funding_source'],
            ':approved_budget' => $_POST['approved_budget'],
            ':stat' => $_POST['stat'],
            ':hidden_id_researchedconducted' => $research_id
        );

        // 1. Update main data (excluding has_files for now)
        $object->query = "UPDATE tbl_researchconducted SET lead_researcher_id = :lead_researcher_id, title = :title, research_agenda_cluster = :research_agenda_cluster, sdgs = :sdgs, started_date = :started_date, completed_date = :completed_date, funding_source = :funding_source, approved_budget = :approved_budget, stat = :stat WHERE id = :hidden_id_researchedconducted";
        $object->execute($data);

        // 2. Handle Collaborators
        $object->query = "DELETE FROM tbl_research_collaborators WHERE research_id = :rid";
        $object->execute([':rid' => $research_id]);

        $collaborators = isset($_POST['collaborators']) ? $_POST['collaborators'] : [];
        if (!in_array($lead_researcher_id, $collaborators)) { $collaborators[] = $lead_researcher_id; }
        foreach($collaborators as $res_id) {
            $object->query = "INSERT INTO tbl_research_collaborators (research_id, researcher_id) VALUES (:rid, :uid)";
            $object->execute([':rid' => $research_id, ':uid' => $res_id]);
        }

        // 3. Handle newly added files (this was failing before because it relied on $_POST['has_files'])
        if(isset($_FILES['research_files']) && !empty($_FILES['research_files']['name'][0])) {
            $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
            handle_research_files($object, $research_id, $categories, $_FILES['research_files']);
        }

        // 4. AUTOMATICALLY sync the 'has_files' status based on actual DB records
        update_has_files_status($object, $research_id);

        $success = '<div class="alert alert-success">Project & Collaborators Updated Successfully</div>';
        echo json_encode(array('error' => $error, 'success' => $success));
    }

    if($_POST["action_researchedconducted"] == 'delete_file') {
        $file_id = intval($_POST['file_id']);
        
        // 1. Grab the research_id before deleting so we can update the parent table later
        $object->query = "SELECT research_id, file_path FROM tbl_research_files WHERE id = '".$file_id."'";
        $file_data = $object->get_result();
        
        $file_deleted = false;
        $research_id = null;
        
        foreach($file_data as $row) {
            $file_deleted = true;
            $research_id = $row['research_id'];
            $physical_path = '../../../' . $row['file_path'];
            if(file_exists($physical_path)) { unlink($physical_path); }
        }
        
        if($file_deleted) {
            // 2. Delete the file record from the database
            $object->query = "DELETE FROM tbl_research_files WHERE id = '".$file_id."'";
            $object->execute();
            
            // 3. AUTOMATICALLY sync the 'has_files' status (if that was the last file, it becomes 'None')
            if ($research_id) {
                update_has_files_status($object, $research_id);
            }
            
            echo json_encode(['status' => 'success', 'message' => 'File deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found.']);
        }
        exit;
    }

    // STRICT ERROR HANDLING DELETE FIX
    if($_POST["action_researchedconducted"] == 'delete') {
        $xid = intval($_POST["xid"]);
        $object->query = "UPDATE tbl_researchconducted SET status = 0 WHERE id = '".$xid."'";
        $object->execute();
        
        // Only return this if the execute() doesn't die from a database error
        echo json_encode(['status' => 'success']);
        exit;
    }
}
?>