<?php
// actions/researchconducted_action.php
include('../../../core/rms.php');
$object = new rms();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide visual errors so JSON doesn't break

// Keep this because it's specifically for parsing legacy date formats
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

try {
    if(isset($_POST["action_researchedconducted"])) {
        
        // -------------------------------------------------------------------------
        // 1. FETCH COLLABORATORS
        // -------------------------------------------------------------------------
        if($_POST["action_researchedconducted"] == 'fetch_collaborators') {
            header('Content-Type: application/json');
            $id = intval($_POST['id']);
            $object->query = "SELECT d.id, d.firstName, d.familyName, d.department FROM tbl_research_collaborators col JOIN tbl_researchdata d ON col.researcher_id = d.id WHERE col.research_id = '$id'";
            $result = $object->get_result();
            $collaborators_array = array();
            foreach($result as $row) { $collaborators_array[] = array('id' => $row['id'], 'firstName' => $row['firstName'], 'familyName' => $row['familyName'], 'department' => $row['department']); }
            echo json_encode($collaborators_array);
            exit;
        }

        // -------------------------------------------------------------------------
        // 2. FETCH ALL (MASTER TABLE)
        // -------------------------------------------------------------------------
        if($_POST["action_researchedconducted"] == 'fetch_all') {
            $order_column = array('primary_familyName', 'rc.title', 'rc.research_agenda_cluster', 'rc.sdgs', 'rc.stat');
            $main_query = "
                SELECT rc.*, 
                        (SELECT GROUP_CONCAT(CONCAT(d.familyName, ', ', d.firstName) SEPARATOR ' | ') FROM tbl_research_collaborators col JOIN tbl_researchdata d ON col.researcher_id = d.id WHERE col.research_id = rc.id) AS all_authors,
                       pd.id AS author_db_id, pd.familyName AS primary_familyName, pd.academic_rank, pd.program AS primary_discipline
                FROM tbl_researchconducted rc
                LEFT JOIN tbl_researchdata pd ON (pd.id = rc.lead_researcher_id OR pd.id = rc.researcherID OR pd.researcherID = rc.researcherID)
            ";
            
            $search_query = " WHERE rc.status = 1 "; 
            if (isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"])) {
                $search_value = addslashes($_POST["search"]["value"]);
                $search_query .= " AND (rc.title LIKE '%" . $search_value . "%' OR pd.familyName LIKE '%" . $search_value . "%') ";
            }
            $order_query = isset($_POST["order"]) ? " ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : " ORDER BY rc.id DESC ";
            $limit_query = ($_POST["length"] != -1) ? ' LIMIT ' . intval($_POST['start']) . ', ' . intval($_POST['length']) : "";
            
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
                $sub_array[] = htmlspecialchars($row["title"]);
                $sub_array[] = htmlspecialchars($row["research_agenda_cluster"]);
                $sub_array[] = htmlspecialchars($row["sdgs"]);
                $sub_array[] = htmlspecialchars($row["stat"]);
                $sub_array[] = '<div align="center"><button type="button" class="btn btn-info btn-sm view_collaborators" data-id="'.$row["id"].'" title="View Collaborators"><i class="fas fa-users"></i></button> <button type="button" class="btn btn-danger btn-sm delete_master_researchconducted" data-id="'.$row["id"].'" title="Delete Record"><i class="far fa-trash-alt"></i></button><a href="view_researcher.php?id='.$author_db_id.'&tab=education" class="btn d-none"></a></div>';
                $data[] = $sub_array;
            }
            echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
            exit;
        }

        // -------------------------------------------------------------------------
        // 3. FETCH SINGLE PROFILE TABLE (WITH FILE COUNT)
        // -------------------------------------------------------------------------
        if($_POST["action_researchedconducted"] == 'fetch') {
            $rid = intval($_POST["rid"]);
            $order_column = array('rc.id', 'rc.title', 'rc.research_agenda_cluster', 'rc.sdgs', 'rc.started_date', 'rc.completed_date', 'rc.funding_source', 'rc.approved_budget', 'rc.stat');
            
            // We use a subquery to count actual files in the new tbl_rde_files table!
            $main_query = "SELECT rc.*, (SELECT COUNT(id) FROM tbl_rde_files WHERE entity_id = rc.id AND entity_type = 'research') as file_count FROM tbl_researchconducted rc JOIN tbl_research_collaborators col ON rc.id = col.research_id";
            
            $search_query = " WHERE col.researcher_id = '$rid' AND rc.status = 1 "; 
            if (isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"])) {
                $search_value = addslashes($_POST["search"]["value"]);
                $search_query .= " AND (rc.title LIKE '%" . $search_value . "%') ";
            }
            $order_query = isset($_POST["order"]) ? " ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " " : " ORDER BY rc.id DESC ";
            $limit_query = ($_POST["length"] != -1) ? ' LIMIT ' . intval($_POST['start']) . ', ' . intval($_POST['length']) : "";
            
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
                // Determine file badge based on the new file_count logic
                $file_badge = ($row["file_count"] > 0) ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-paperclip mr-1"></i> Files</span>' : '<span class="badge badge-secondary px-2 py-1">None</span>';
                
                $sub_array = array();
                $sub_array[] = htmlspecialchars($row["title"]);
                $sub_array[] = htmlspecialchars($row["research_agenda_cluster"]);
                $sub_array[] = htmlspecialchars($row["sdgs"]);
                $sub_array[] = parse_legacy_date_php($row["started_date"]);
                $sub_array[] = parse_legacy_date_php($row["completed_date"]);
                $sub_array[] = htmlspecialchars($row["funding_source"]);
                $sub_array[] = htmlspecialchars($row["approved_budget"]);
                $sub_array[] = htmlspecialchars($row["stat"]);
                $sub_array[] = '<div align="center">' . $file_badge . '</div>';
                $sub_array[] = '<div align="center"><button type="button" class="btn btn-primary btn-sm edit_buttonrc edit_button_researchconducted" data-id="'.$row["id"].'"><i class="fas fa-pencil-alt"></i></button> <button type="button" class="btn btn-danger btn-sm delete_buttonrc" data-id="'.$row["id"].'"><i class="far fa-trash-alt"></i></button></div>';
                $data[] = $sub_array;
            }
            echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data));
            exit;
        }

        // -------------------------------------------------------------------------
        // 4. ADD RECORD
        // -------------------------------------------------------------------------
        if($_POST["action_researchedconducted"] == 'Add') {
            $from_date = addslashes(date("Y-m-d", strtotime($_POST['started_date'])));
            $to_date = addslashes(date("Y-m-d", strtotime($_POST['completed_date'])));
            $lead_researcher_id = intval($_POST['lead_researcher_id']);
            
            $title = addslashes($_POST['title']);
            $rac = addslashes($_POST['research_agenda_cluster']);
            $sdgs = isset($_POST['sdgs']) ? addslashes(implode(", ", $_POST['sdgs'])) : '';
            $fs = addslashes($_POST['funding_source']);
            $stat = addslashes($_POST['stat']);
            $ab = floatval($_POST['approved_budget']);

            // Handle Cover Photo Upload
            $cover_photo = '';
            if (isset($_FILES["cover_photo"]["name"]) && $_FILES["cover_photo"]["name"] != '') {
                $upload_result = $object->uploadCoverPhoto($_FILES['cover_photo'], '../../../uploads/covers/', 'uploads/covers/');
                if ($upload_result) { $cover_photo = $upload_result; }
            }

            // Notice we completely removed the has_files and moa_files columns
            $object->query = "INSERT INTO tbl_researchconducted (researcherID, lead_researcher_id, title, research_agenda_cluster, sdgs, started_date, completed_date, funding_source, approved_budget, stat, cover_photo, terminal_report, status) VALUES ('$lead_researcher_id', '$lead_researcher_id', '$title', '$rac', '$sdgs', '$from_date', '$to_date', '$fs', '$ab', '$stat', '$cover_photo', 'Legacy Replaced', 1)";
            $object->execute();
            
            $new_research_id = intval($object->connect->lastInsertId());
            
            // Insert Collaborators
            $collaborators = isset($_POST['collaborators']) ? $_POST['collaborators'] : [];
            if (!in_array($lead_researcher_id, $collaborators)) { $collaborators[] = $lead_researcher_id; }
            foreach($collaborators as $res_id) {
                $uid = intval($res_id);
                $object->query = "INSERT INTO tbl_research_collaborators (research_id, researcher_id) VALUES ('$new_research_id', '$uid')";
                $object->execute();
            }

            // Handle Document Uploads using the Unified File Table
            if(isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) {
                $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
                $object->handle_generic_files($_FILES['research_files'], $categories, $new_research_id, '../../../uploads/documents/', 'uploads/documents/', 'research');
            }
            echo json_encode(array('error' => '', 'success' => '<div class="alert alert-success">Project Added Successfully</div>'));
            exit;
        }

        // -------------------------------------------------------------------------
        // 5. FETCH SINGLE (FOR MODAL POPULATION)
        // -------------------------------------------------------------------------
        if($_POST["action_researchedconducted"] == 'fetch_single') {
            $rid = intval($_POST["rcid"]);
            $object->query = "SELECT * FROM tbl_researchconducted WHERE id = '$rid'";
            $result = $object->get_result();
            $data = array();
            
            foreach($result as $row) {
                $data['title'] = htmlspecialchars_decode($row["title"]);
                $data['research_agenda_cluster'] = htmlspecialchars_decode($row["research_agenda_cluster"]);
                $data['sdgs'] = $row["sdgs"];
                $data['started_date'] = parse_legacy_date_php($row["started_date"]);
                $data['completed_date'] = parse_legacy_date_php($row["completed_date"]);
                $data['funding_source'] = htmlspecialchars_decode($row["funding_source"]);
                $data['approved_budget'] = $row["approved_budget"];
                $data['stat'] = $row["stat"];
                $data['cover_photo'] = $row["cover_photo"];
                $data['lead_researcher_id'] = $row["lead_researcher_id"];
                
                $default_image = 'img/default_research_cover.png'; 
                $db_cover = trim($row["cover_photo"] ?? '');
                
                if (empty($db_cover)) {
                    $data['cover_photo'] = $default_image;
                } else {
                    $data['cover_photo'] = $db_cover;
                }
            }
            
            $object->query = "SELECT researcher_id FROM tbl_research_collaborators WHERE research_id = '$rid'";
            $collab_result = $object->get_result();
            $collab_array = [];
            foreach($collab_result as $c) { $collab_array[] = $c['researcher_id']; }
            $data['collaborators'] = $collab_array;
            
            // Query the new unified tbl_rde_files table
            $object->query = "SELECT id, file_category, file_name, file_path FROM tbl_rde_files WHERE entity_id = '$rid' AND entity_type = 'research'";
            $file_result = $object->get_result();
            $files_array = [];
            foreach($file_result as $f) {
                $files_array[] = array('id' => $f['id'], 'category' => htmlspecialchars_decode($f['file_category']), 'name' => htmlspecialchars_decode($f['file_name']), 'path' => '../../' . $f['file_path']);
            }
            $data['existing_files'] = $files_array;
            
            echo json_encode($data);
            exit;
        }

        // -------------------------------------------------------------------------
        // 6. EDIT RECORD
        // -------------------------------------------------------------------------
        if ($_POST["action_researchedconducted"] == 'Edit') {
            $research_id = intval($_POST['hidden_id_researchedconducted']);
            $lead_researcher_id = intval($_POST['lead_researcher_id']);
            
            $from_dateu = addslashes(date("Y-m-d", strtotime($_POST['started_date'])));  
            $to_dateu = addslashes(date("Y-m-d", strtotime($_POST['completed_date']))); 
            
            $title = addslashes($_POST['title']);
            $rac = addslashes($_POST['research_agenda_cluster']);
            $sdgs = isset($_POST['sdgs']) ? addslashes(implode(", ", $_POST['sdgs'])) : '';
            $fs = addslashes($_POST['funding_source']);
            $stat = addslashes($_POST['stat']);
            $ab = floatval($_POST['approved_budget']);

            // Handle Cover Photo Upload for Edit
            $cover_photo_query = "";
            if (isset($_FILES["cover_photo"]["name"]) && $_FILES["cover_photo"]["name"] != '') {
                $upload_result = $object->uploadCoverPhoto($_FILES['cover_photo'], '../../../uploads/covers/', 'uploads/covers/');
                if ($upload_result) {
                    $cover_photo_query = ", cover_photo = '" . $upload_result . "'";
                }
            }

            // Update main data
            $object->query = "UPDATE tbl_researchconducted SET researcherID = '$lead_researcher_id', lead_researcher_id = '$lead_researcher_id', title = '$title', research_agenda_cluster = '$rac', sdgs = '$sdgs', started_date = '$from_dateu', completed_date = '$to_dateu', funding_source = '$fs', approved_budget = '$ab', stat = '$stat' $cover_photo_query WHERE id = '$research_id'";
            $object->execute();

            // Handle Collaborators
            $object->query = "DELETE FROM tbl_research_collaborators WHERE research_id = '$research_id'";
            $object->execute();
            
            $collaborators = isset($_POST['collaborators']) ? $_POST['collaborators'] : [];
            if (!in_array($lead_researcher_id, $collaborators)) { $collaborators[] = $lead_researcher_id; }
            foreach($collaborators as $res_id) {
                $uid = intval($res_id);
                $object->query = "INSERT INTO tbl_research_collaborators (research_id, researcher_id) VALUES ('$research_id', '$uid')";
                $object->execute();
            }

            // Handle Document Uploads using the Unified File Table
            if(isset($_FILES['research_files']['name']) && is_array($_FILES['research_files']['name']) && !empty($_FILES['research_files']['name'][0])) {
                $categories = isset($_POST['file_categories']) ? $_POST['file_categories'] : [];
                $object->handle_generic_files($_FILES['research_files'], $categories, $research_id, '../../../uploads/documents/', 'uploads/documents/', 'research');
            }

            echo json_encode(array('error' => '', 'success' => '<div class="alert alert-success">Project Updated Successfully</div>'));
            exit;
        }

        // -------------------------------------------------------------------------
        // 7. DELETE SINGLE FILE (THE FIX!)
        // -------------------------------------------------------------------------
        if($_POST["action_researchedconducted"] == 'delete_file') {
            // Using the 2-parameter function correctly
            $success = $object->delete_generic_file($_POST['file_id'], '../../../');
            
            if($success) {
                echo json_encode(['status' => 'success', 'message' => 'File deleted.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'File not found.']);
            }
            exit;
        }

        // -------------------------------------------------------------------------
        // 8. DELETE RECORD (MOVE TO TRASH)
        // -------------------------------------------------------------------------
        if($_POST["action_researchedconducted"] == 'delete') {
            $xid = intval($_POST["xid"]);
            $object->query = "UPDATE tbl_researchconducted SET status = 0 WHERE id = '$xid'";
            $object->execute();
            
            echo json_encode(['status' => 'success']);
            exit;
        }

    }
} catch (Throwable $e) {
    // Master catch block: Prevents 500 Network errors and sends the exact PHP/SQL error back to the JS popup
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Backend Error: ' . $e->getMessage() . ' on line ' . $e->getLine()]);
    exit;
}
?>