<?php
//publication_action.php
include('../../../core/rms.php');
$object = new rms();

// Global Server-Side Legacy Date Parser
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

// Reusable function to handle multiple file uploads
if (!function_exists('handle_publication_files')) {
    function handle_publication_files($object, $publication_id, $categories, $files) {
        if(isset($files['name']) && is_array($files['name'])) {
            $upload_dir = '../../../uploads/research_files/';
            if (!file_exists($upload_dir)) { mkdir($upload_dir, 0755, true); }
            
            for($i = 0; $i < count($files['name']); $i++) {
                if($files['error'][$i] == 0) {
                    $category = isset($categories[$i]) ? $categories[$i] : 'Other';
                    $original_name = basename($files['name'][$i]);
                    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                    $safe_name = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($original_name, PATHINFO_FILENAME));
                    $new_name = 'PUB_' . $safe_name . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    
                    $target_file = $upload_dir . $new_name;
                    $db_path = 'uploads/research_files/' . $new_name; 
                    
                    if(move_uploaded_file($files['tmp_name'][$i], $target_file)) {
                        $object->query = "INSERT INTO tbl_publication_files (publication_id, file_category, file_name, file_path) VALUES (:pid, :cat, :fname, :fpath)";
                        $object->execute([
                            ':pid' => $publication_id,
                            ':cat' => $category,
                            ':fname' => $original_name,
                            ':fpath' => $db_path
                        ]);
                    }
                }
            }
        }
    }
}

if(isset($_POST["action_publication"]))
{
    // --- FETCH FOR SPECIFIC RESEARCHER PROFILE ---
	if($_POST["action_publication"] == 'fetch')
	{
		$order_column = array(
            'p.title',
            'p.start',
            'p.end',
            'p.journal',
            'p.vol_num_issue_num',
            'p.issn_isbn',
            'p.indexing',
            'p.publication_date',
            'p.has_files'
		);

        $main_query = "
            SELECT p.* FROM tbl_publication p
            JOIN tbl_publication_collaborators col ON p.id = col.publication_id
        ";
        $search_query = " WHERE col.researcher_id = '".$_POST["rid"]."' ";

        if (isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (p.title LIKE '%" . $search_value . "%' ";
            $search_query .= "OR p.publication_date LIKE '%" . $search_value . "%' ";
            $search_query .= "OR p.journal LIKE '%" . $search_value . "%' ) ";
        }

        if (isset($_POST["order"])) {
            $order_query = "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " ";
        } else {
            $order_query = "ORDER BY p.id DESC ";
        }

        $limit_query = "";
		if($_POST["length"] != -1) {
			$limit_query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}

		$object->query = $main_query . $search_query . $order_query;
		$object->execute();
		$filtered_rows = $object->row_count();

		$object->query .= $limit_query;
		$result = $object->get_result();

		$object->query = $main_query . $search_query;
		$object->execute();
		$total_rows = $object->row_count();

		$data = array();

		foreach($result as $row)
		{
            $file_badge = ($row["has_files"] == 'With') ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-paperclip mr-1"></i> Files</span>' : '<span class="badge badge-secondary px-2 py-1">None</span>';

			$sub_array = array();
            $sub_array[] = $row["title"];
			$sub_array[] = parse_legacy_date_php($row["start"]);
            $sub_array[] = parse_legacy_date_php($row["end"]);
            $sub_array[] = $row["journal"];
            $sub_array[] = $row["vol_num_issue_num"];
            $sub_array[] = $row["issn_isbn"];
            $sub_array[] = $row["indexing"];
            $sub_array[] = parse_legacy_date_php($row["publication_date"]);
            $sub_array[] = '<div align="center">' . $file_badge . '</div>';
            
            $sub_array[] = '
			<div align="center">
				<button type="button" title="Update Publication" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" class="btn btn-primary btn-sm edit_button_publication" data-id="'.$row["id"].'"><i class="fas fa-pencil-alt"></i></button>
			    <button type="button" title="Delete Publication" style="margin-left: 5px;" class="btn btn-danger btn-sm delete_button_publication" data-id="'.$row["id"].'"><i class="far fa-trash-alt"></i></button>
			</div>
			';
			$data[] = $sub_array;
		}

		$output = array(
			"draw"    			=> 	intval($_POST["draw"]),
			"recordsTotal"  	=>  $total_rows,
			"recordsFiltered" 	=> 	$filtered_rows,
			"data"    			=> 	$data
		);
		echo json_encode($output);
	}

    // --- ADD PUBLICATION ---
	if($_POST["action_publication"] == 'Add')
	{
		$error = '';
		$success = '';

		$fpublication_date = date("Y-m-d", strtotime($_POST['publication_date']));
        $lead_author_id = $_POST['lead_author_id'];
        $has_files = $_POST['has_files_pub'];

        $data = array(
            ':researcherID'        => $lead_author_id, // Legacy failsafe
            ':lead_author_id'      => $lead_author_id,
            ':title'               => $_POST['title_pub'],
            ':start'               => date("Y-m-d", strtotime($_POST['start'])),
            ':end'                 => date("Y-m-d", strtotime($_POST['end'])),
            ':journal'             => $_POST['journal'],
            ':vol_num_issue_num'   => $_POST['vol_num_issue_num'],
            ':issn_isbn'           => $_POST['issn_isbn'],
            ':indexing'            => $_POST['indexing'],
            ':publication_date'    => $fpublication_date,
            ':has_files'           => $has_files
        );

        $object->query = "
            INSERT INTO tbl_publication
            (researcherID, lead_author_id, title, start, end, journal, vol_num_issue_num, issn_isbn, indexing, publication_date, has_files) 
            VALUES 
            (:researcherID, :lead_author_id, :title, :start, :end, :journal, :vol_num_issue_num, :issn_isbn, :indexing, :publication_date, :has_files)
        ";
        $object->execute($data);
        $new_pub_id = $object->connect->lastInsertId();

        // Sync Collaborators
        $collaborators = isset($_POST['collaborators_pub']) ? $_POST['collaborators_pub'] : [];
        if (!in_array($lead_author_id, $collaborators)) {
            $collaborators[] = $lead_author_id;
        }

        foreach($collaborators as $res_id) {
            $object->query = "INSERT INTO tbl_publication_collaborators (publication_id, researcher_id) VALUES (:pid, :uid)";
            $object->execute([':pid' => $new_pub_id, ':uid' => $res_id]);
        }

        // Upload Files
        if($has_files == 'With' && isset($_FILES['pub_files'])) {
            $categories = isset($_POST['pub_file_categories']) ? $_POST['pub_file_categories'] : [];
            handle_publication_files($object, $new_pub_id, $categories, $_FILES['pub_files']);
        }

        $success = '<div class="alert alert-success">Publication Added</div>';
		$output = array('error' => $error, 'success' => $success);
		echo json_encode($output);
	}

    // --- FETCH SINGLE FOR EDIT ---
	if($_POST["action_publication"] == 'fetch_single')
	{
		$object->query = "SELECT * FROM tbl_publication WHERE id = '".$_POST["publicationID"]."'";
		$result = $object->get_result();
		$data = array();

		foreach($result as $row)
		{
            $data['title'] = $row["title"];
            $data['start'] = parse_legacy_date_php($row["start"]);
            $data['end'] = parse_legacy_date_php($row["end"]);
            $data['journal'] = $row["journal"];
            $data['vol_num_issue_num'] = $row["vol_num_issue_num"];
            $data['issn_isbn'] = $row["issn_isbn"];
            $data['indexing'] = $row["indexing"];
            $data['publication_date'] = parse_legacy_date_php($row["publication_date"]);
            $data['lead_author_id'] = $row["lead_author_id"];
            $data['has_files'] = $row["has_files"];
		}

        $object->query = "SELECT researcher_id FROM tbl_publication_collaborators WHERE publication_id = '".$_POST["publicationID"]."'";
        $collab_result = $object->get_result();
        $collab_array = [];
        foreach($collab_result as $c) { $collab_array[] = $c['researcher_id']; }
        $data['collaborators'] = $collab_array;

        $object->query = "SELECT id, file_category, file_name, file_path FROM tbl_publication_files WHERE publication_id = '".$_POST["publicationID"]."'";
        $file_result = $object->get_result();
        $files_array = [];
        foreach($file_result as $f) {
            $files_array[] = array(
                'id' => $f['id'],
                'category' => $f['file_category'],
                'name' => $f['file_name'],
                'path' => '../../' . $f['file_path'] 
            );
        }
        $data['existing_files'] = $files_array;

		echo json_encode($data);
	}

    // --- EDIT PUBLICATION ---
	if ($_POST["action_publication"] == 'Edit') {
		$publication_datep = date("Y-m-d", strtotime($_POST['publication_date']));  
		$error = '';
		$success = '';
        $pub_id = $_POST['hidden_publicationID'];
        $lead_author_id = $_POST['lead_author_id'];
        $has_files = $_POST['has_files_pub'];

        $data = array(
            ':lead_author_id' => $lead_author_id,
            ':title' => $_POST['title_pub'],
            ':start' => date("Y-m-d", strtotime($_POST['start'])),
            ':end' => date("Y-m-d", strtotime($_POST['end'])),
            ':journal' => $_POST['journal'],
            ':vol_num_issue_num' => $_POST['vol_num_issue_num'],
            ':issn_isbn' => $_POST['issn_isbn'],
            ':indexing' => $_POST['indexing'],
            ':publication_date' => $publication_datep,
            ':has_files' => $has_files,
            ':hidden_publicationID' => $pub_id
        );

        $object->query = "
            UPDATE tbl_publication 
            SET lead_author_id = :lead_author_id,
                title = :title, 
                start = :start, 
                end = :end, 
                journal = :journal, 
                vol_num_issue_num = :vol_num_issue_num, 
                issn_isbn = :issn_isbn, 
                indexing = :indexing, 
                publication_date = :publication_date,
                has_files = :has_files
            WHERE id = :hidden_publicationID
        ";
        $object->execute($data);

        // Sync Collaborators
        $object->query = "DELETE FROM tbl_publication_collaborators WHERE publication_id = :pid";
        $object->execute([':pid' => $pub_id]);

        $collaborators = isset($_POST['collaborators_pub']) ? $_POST['collaborators_pub'] : [];
        if (!in_array($lead_author_id, $collaborators)) {
            $collaborators[] = $lead_author_id;
        }

        foreach($collaborators as $res_id) {
            $object->query = "INSERT INTO tbl_publication_collaborators (publication_id, researcher_id) VALUES (:pid, :uid)";
            $object->execute([':pid' => $pub_id, ':uid' => $res_id]);
        }

        // Upload New Files
        if($has_files == 'With' && isset($_FILES['pub_files'])) {
            $categories = isset($_POST['pub_file_categories']) ? $_POST['pub_file_categories'] : [];
            handle_publication_files($object, $pub_id, $categories, $_FILES['pub_files']);
        }

        // Failsafe: if user switched to "None", delete existing files
        if($has_files == 'None') {
            $clean_id = intval($pub_id);
            $object->query = "SELECT file_path FROM tbl_publication_files WHERE publication_id = '".$clean_id."'";
            $files_to_delete = $object->get_result();
            foreach($files_to_delete as $file) {
                $physical_path = '../../../' . $file['file_path'];
                if(file_exists($physical_path)) { unlink($physical_path); }
            }
            $object->query = "DELETE FROM tbl_publication_files WHERE publication_id = '".$clean_id."'";
            $object->execute();
        }

        $success = '<div class="alert alert-success">Publication Updated</div>';
		$output = array('error' => $error, 'success' => $success);
		echo json_encode($output);
	}

    // --- INDIVIDUAL FILE DELETION AJAX ---
    if($_POST["action_publication"] == 'delete_file')
    {
        $file_id = intval($_POST['file_id']);
        
        $object->query = "SELECT file_path FROM tbl_publication_files WHERE id = '".$file_id."'";
        $file_data = $object->get_result();
        
        $file_deleted = false;
        foreach($file_data as $row) {
            $file_deleted = true;
            $physical_path = '../../../' . $row['file_path'];
            if(file_exists($physical_path)) {
                unlink($physical_path);
            }
        }
        
        if($file_deleted) {
            $object->query = "DELETE FROM tbl_publication_files WHERE id = '".$file_id."'";
            $object->execute();
            echo json_encode(['status' => 'success', 'message' => 'File deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found in database.']);
        }
        exit;
    }

    // --- CASCADE DELETE FULL PUBLICATION ---
	if($_POST["action_publication"] == 'delete')
	{
        $pid = intval($_POST["publicationID"]);

        $object->query = "SELECT file_path FROM tbl_publication_files WHERE publication_id = '".$pid."'";
        $files_to_delete = $object->get_result();
        foreach($files_to_delete as $file) {
            $physical_path = '../../../' . $file['file_path'];
            if(file_exists($physical_path)) { unlink($physical_path); }
        }

        $object->query = "DELETE FROM tbl_publication_files WHERE publication_id = '".$pid."'";
        $object->execute();

        $object->query = "DELETE FROM tbl_publication_collaborators WHERE publication_id = '".$pid."'";
        $object->execute();

		$object->query = "DELETE FROM tbl_publication WHERE id = '".$pid."'";
		$object->execute();

		echo '<div class="alert alert-success">Publication Deleted</div>';
	}
}

// --- MASTER TABLE FETCH (ALL PUBLICATIONS) ---
if(isset($_POST["action_publication"]) && $_POST["action_publication"] == 'fetch_all')
{
    $order_column = array(
        'primary_familyName',
        'pub.title',
        'pub.journal',
        'pub.publication_date',
        'pub.issn_isbn'
    );

    $main_query = "
        SELECT pub.*, 
               (SELECT GROUP_CONCAT(CONCAT(d.familyName, ', ', d.firstName) SEPARATOR ' | ') 
                FROM tbl_publication_collaborators col 
                JOIN tbl_researchdata d ON col.researcher_id = d.id 
                WHERE col.publication_id = pub.id) AS all_authors,
               pub.lead_author_id AS author_db_id,
               pd.familyName AS primary_familyName
        FROM tbl_publication pub
        LEFT JOIN tbl_researchdata pd ON pub.lead_author_id = pd.id
    ";
    $search_query = " WHERE 1=1 ";

    if (isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"])) {
        $search_value = $_POST["search"]["value"];
        $search_query .= "AND (pub.title LIKE '%" . $search_value . "%' ";
        $search_query .= "OR pd.familyName LIKE '%" . $search_value . "%' ";
        $search_query .= "OR pd.firstName LIKE '%" . $search_value . "%' ";
        $search_query .= "OR CONCAT(pd.firstName, ' ', pd.familyName) LIKE '%" . $search_value . "%' ";
        $search_query .= "OR pub.journal LIKE '%" . $search_value . "%' ) ";
    }

    if (isset($_POST["order"])) {
        $order_query = "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " ";
    } else {
        $order_query = "ORDER BY pub.id DESC ";
    }

    $limit_query = "";
    if($_POST["length"] != -1) {
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

    foreach($result as $row) {
        $sub_array = array();
        $author_name = $row["all_authors"] ? $row["all_authors"] : "<span class='text-danger'>No Authors Assigned</span>";
        $author_db_id = $row["author_db_id"] ? $row["author_db_id"] : 0; 
        
        $sub_array[] = '<span class="font-weight-bold">'.$author_name.'</span>';
        $sub_array[] = $row["title"];
        $sub_array[] = $row["journal"];
        $sub_array[] = parse_legacy_date_php($row["publication_date"]);
        $sub_array[] = $row["issn_isbn"];
        
        $sub_array[] = '
        <div align="center">
            <button type="button" class="btn btn-danger btn-sm delete_master_publication" data-id="'.$row["id"].'" title="Delete Publication"><i class="far fa-trash-alt"></i></button>
            <a href="view_researcher.php?id='.$author_db_id.'&tab=degree" class="btn d-none"></a>
        </div>
        ';
        $data[] = $sub_array;
    }

    $output = array(
        "draw"              =>  intval($_POST["draw"]),
        "recordsTotal"      =>  $total_rows,
        "recordsFiltered"   =>  $filtered_rows,
        "data"              =>  $data
    );
        
    echo json_encode($output);
}
?>