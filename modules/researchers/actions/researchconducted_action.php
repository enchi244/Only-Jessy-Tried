<?php
//category_action.php
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

if(isset($_POST["action_researchedconducted"]))
{
    // --- FETCH COLLABORATORS VIA JUNCTION TABLE ---
	if($_POST["action_researchedconducted"] == 'fetch_collaborators')
    {
        header('Content-Type: application/json');
        $id = intval($_POST['id']); // Safely converted to integer
        
        $object->query = "
            SELECT d.id, d.firstName, d.familyName, d.department 
            FROM tbl_research_collaborators col 
            JOIN tbl_researchdata d ON col.researcher_id = d.id 
            WHERE col.research_id = '".$id."'
        ";
        $result = $object->get_result();
        $collaborators_array = array();
        
        // Loop through the database object and extract the rows into a standard array
        foreach($result as $row) {
            $collaborators_array[] = array(
                'id'         => $row['id'],
                'firstName'  => $row['firstName'],
                'familyName' => $row['familyName'],
                'department' => $row['department']
            );
        }
        
        // Now safely encode the pure array
        echo json_encode($collaborators_array);
        exit;
    }

    // --- MASTER TABLE LOGIC (RESTORED HIDDEN BUTTON & AUTHOR SEARCH) ---
    if($_POST["action_researchedconducted"] == 'fetch_all')
    {
        $order_column = array(
            'primary_familyName', // Restored sorting by author's family name
            'rc.title',
            'rc.research_agenda_cluster',
            'rc.sdgs',
            'rc.stat'
        );

        // Subquery gets the principal author's info so the Row Click works, 
        // while GROUP_CONCAT gets all authors for display.
        $main_query = "
            SELECT rc.*, 
                   (SELECT GROUP_CONCAT(CONCAT(d.familyName, ', ', d.firstName) SEPARATOR ' | ') 
                    FROM tbl_research_collaborators col 
                    JOIN tbl_researchdata d ON col.researcher_id = d.id 
                    WHERE col.research_id = rc.id) AS all_authors,
                   primary_collab.researcher_id AS author_db_id,
                   pd.familyName AS primary_familyName
            FROM tbl_researchconducted rc
            LEFT JOIN (
                SELECT research_id, MIN(researcher_id) as researcher_id
                FROM tbl_research_collaborators
                GROUP BY research_id
            ) primary_collab ON rc.id = primary_collab.research_id
            LEFT JOIN tbl_researchdata pd ON primary_collab.researcher_id = pd.id
        ";
        $search_query = " WHERE 1=1 ";

        // Restored searching by familyName
        if (isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= " AND (rc.title LIKE '%" . $search_value . "%' ";
            $search_query .= " OR pd.familyName LIKE '%" . $search_value . "%' ";
            $search_query .= " OR rc.research_agenda_cluster LIKE '%" . $search_value . "%') ";
        }

        if (isset($_POST["order"])) {
            $order_query = " ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " ";
        } else {
            $order_query = " ORDER BY rc.id DESC ";
        }

        $limit_query = "";
        if($_POST["length"] != -1) {
            $limit_query .= ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
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
            $author_db_id = $row["author_db_id"] ? $row["author_db_id"] : 0; // Failsafe
            
            $sub_array[] = '<span class="font-weight-bold">'.$author_name.'</span>';
            $sub_array[] = $row["title"];
            $sub_array[] = $row["research_agenda_cluster"];
            $sub_array[] = $row["sdgs"];
            $sub_array[] = $row["stat"];
            
            // Restored the hidden anchor tag 'a.btn.d-none' for Master Table row clicks
            $sub_array[] = '
            <div align="center">
                <button type="button" class="btn btn-info btn-sm view_collaborators" data-id="'.$row["id"].'" title="View Collaborators"><i class="fas fa-users"></i></button>
                <button type="button" class="btn btn-danger btn-sm delete_master_researchconducted" data-id="'.$row["id"].'" title="Delete Record"><i class="far fa-trash-alt"></i></button>
                <a href="view_researcher.php?id='.$author_db_id.'&tab=education" class="btn d-none"></a>
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

    // --- FETCH FOR SPECIFIC RESEARCHER PROFILE (USING JUNCTION) ---
	if($_POST["action_researchedconducted"] == 'fetch')
	{
		$order_column = array(
			'rc.id', 
			'rc.title', 
			'rc.research_agenda_cluster', 
			'rc.sdgs', 
			'rc.started_date', 
			'rc.completed_date', 
			'rc.funding_source', 
			'rc.approved_budget', 
			'rc.stat', 
			'rc.terminal_report'
		);

		$main_query = "
            SELECT rc.* FROM tbl_researchconducted rc
            JOIN tbl_research_collaborators col ON rc.id = col.research_id
        ";
        $search_query = " WHERE col.researcher_id = '".$_POST["rid"]."' ";

        if (isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= " AND (rc.title LIKE '%" . $search_value . "%' ";
            $search_query .= " OR rc.started_date LIKE '%" . $search_value . "%' ";
            $search_query .= " OR rc.completed_date LIKE '%" . $search_value . "%') ";
        }

        if (isset($_POST["order"])) {
            $order_query = " ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " ";
        } else {
            $order_query = " ORDER BY rc.id DESC ";
        }

        $limit_query = "";
		if($_POST["length"] != -1) {
			$limit_query .= ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
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
			$sub_array = array();
			$sub_array[] = $row["title"];
			$sub_array[] = $row["research_agenda_cluster"];
			$sub_array[] = $row["sdgs"];
			$sub_array[] = parse_legacy_date_php($row["started_date"]);
			$sub_array[] = parse_legacy_date_php($row["completed_date"]);
			$sub_array[] = $row["funding_source"];
			$sub_array[] = $row["approved_budget"];
			$sub_array[] = $row["stat"];
			$sub_array[] = $row["terminal_report"];
            
            // Restored ALL original HTML attributes (names, tooltips, margins)
			$sub_array[] = '
			<div align="center">
				<button type="button" name="edit_buttonrc" title="Update Category" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" data-toggle="tooltip" class="btn btn-primary btn-sm edit_buttonrc" data-id="'.$row["id"].'"><i class="fas fa-pencil-alt"></i></button>
			    <button type="button" name="delete_buttonrc" title="Delete Category" style="margin-left: 5px;" data-toggle="tooltip" class="btn btn-danger btn-sm delete_buttonrc" data-id="'.$row["id"].'"><i class="far fa-trash-alt"></i></button>
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

    // --- ADD RESEARCH & ALLOCATE COLLABORATORS ---
	if($_POST["action_researchedconducted"] == 'Add')
	{
		$error = '';
		$success = '';

		$timestamp = strtotime($_POST['started_date']);
		$from_date = date("m-d-Y", $timestamp);

		$timestamp1 = strtotime($_POST['completed_date']);
		$to_date = date("m-d-Y", $timestamp1);

        // Identify primary researcher for legacy DB structure
        $collaborators = isset($_POST['collaborators']) ? $_POST['collaborators'] : [];
        $profile_owner = isset($_POST['hiddeny']) ? $_POST['hiddeny'] : null;
        $primary_researcher = !empty($profile_owner) ? $profile_owner : (!empty($collaborators) ? $collaborators[0] : null);

        // 1. Insert Core Project
        $data = array(
                ':researcherID'                => $primary_researcher,
                ':title'                       => $_POST['title'],
                ':research_agenda_cluster'     => $_POST['research_agenda_cluster'],
                ':sdgs'                        => implode(", ", $_POST['sdgs']), 
                ':started_date'            	   => $from_date,
                ':completed_date'              => $to_date,
                ':funding_source'              => $_POST['funding_source'],
                ':approved_budget'             => $_POST['approved_budget'],
                ':stat'           			   => $_POST['stat'],
                ':terminal_report'             => $_POST['terminal_report']
        );

        $object->query = "
        INSERT INTO tbl_researchconducted 
        (researcherID, title, research_agenda_cluster, sdgs, started_date, completed_date, funding_source, approved_budget, stat, terminal_report) 
        VALUES 
        (:researcherID, :title, :research_agenda_cluster, :sdgs, :started_date, :completed_date, :funding_source, :approved_budget, :stat, :terminal_report)
        ";
        $object->execute($data);
        $new_research_id = $object->connect->lastInsertId();

        // 2. Map Researchers in Junction Table
        $collaborators = isset($_POST['collaborators']) ? $_POST['collaborators'] : [];
        $profile_owner = isset($_POST['hiddeny']) ? $_POST['hiddeny'] : null;

        if (!empty($profile_owner) && !in_array($profile_owner, $collaborators)) {
            $collaborators[] = $profile_owner;
        }

        foreach($collaborators as $researcher_id) {
            $object->query = "INSERT INTO tbl_research_collaborators (research_id, researcher_id) VALUES (:rid, :uid)";
            $object->execute([':rid' => $new_research_id, ':uid' => $researcher_id]);
        }

        $success = '<div class="alert alert-success">Research Conducted Added Successfully</div>';
		$output = array('error' => $error, 'success' => $success);
		echo json_encode($output);
	}

    // --- FETCH SINGLE FOR EDITING ---
	if($_POST["action_researchedconducted"] == 'fetch_single')
	{
		$object->query = "SELECT * FROM tbl_researchconducted WHERE id = '".$_POST["rcid"]."'";
		$result = $object->get_result();
		$data = array();

		foreach($result as $row)
		{
			$data['title'] = $row["title"];
			$data['research_agenda_cluster'] = $row["research_agenda_cluster"];
			$data['sdgs'] = $row["sdgs"];
			$data['started_date'] = parse_legacy_date_php($row["started_date"]);
			$data['completed_date'] = parse_legacy_date_php($row["completed_date"]);
			// Restored all failsafes
			$data['start_date'] = parse_legacy_date_php($row["started_date"]);
			$data['end_date'] = parse_legacy_date_php($row["completed_date"]);
            $data['start'] = parse_legacy_date_php($row["started_date"]);
			$data['end'] = parse_legacy_date_php($row["completed_date"]);
			$data['funding_source'] = $row["funding_source"];
			$data['approved_budget'] = $row["approved_budget"];
			$data['stat'] = $row["stat"];
			$data['terminal_report'] = $row["terminal_report"];
		}

        $object->query = "SELECT researcher_id FROM tbl_research_collaborators WHERE research_id = '".$_POST["rcid"]."'";
        $collab_result = $object->get_result();
        $collab_array = [];
        foreach($collab_result as $c) {
            $collab_array[] = $c['researcher_id'];
        }
        $data['collaborators'] = $collab_array;

		echo json_encode($data);
	}

    // --- EDIT RESEARCH & RE-SYNC COLLABORATORS ---
	if ($_POST["action_researchedconducted"] == 'Edit') {
		$timestampu = strtotime($_POST['started_date']);
		$from_dateu = date("m-d-Y", $timestampu);  
	
		$timestamp1u = strtotime($_POST['completed_date']);
		$to_dateu = date("m-d-Y", $timestamp1u); 
	
		$error = '';
		$success = '';
        $research_id = $_POST['hidden_id_researchedconducted'];

        $collaborators = isset($_POST['collaborators']) ? $_POST['collaborators'] : [];
        $profile_owner = isset($_POST['hiddeny']) ? $_POST['hiddeny'] : null;
        $primary_researcher = !empty($profile_owner) ? $profile_owner : (!empty($collaborators) ? $collaborators[0] : null);

        $data = array(
            ':researcherID' => $primary_researcher,
            ':title' => $_POST['title'],
            ':research_agenda_cluster' => $_POST['research_agenda_cluster'],
            ':sdgs'   => implode(", ", $_POST['sdgs']), 
            ':started_date' => $from_dateu,  
            ':completed_date' => $to_dateu,  
            ':funding_source' => $_POST['funding_source'],
            ':approved_budget' => $_POST['approved_budget'],
            ':stat' => $_POST['stat'],
            ':terminal_report' => $_POST['terminal_report'],
            ':hidden_id_researchedconducted' => $research_id
        );

        $object->query = "
            UPDATE tbl_researchconducted 
            SET researcherID = :researcherID,
                title = :title, 
                research_agenda_cluster = :research_agenda_cluster, 
                sdgs = :sdgs, 
                started_date = :started_date, 
                completed_date = :completed_date, 
                funding_source = :funding_source, 
                approved_budget = :approved_budget, 
                stat = :stat, 
                terminal_report = :terminal_report  
            WHERE id = :hidden_id_researchedconducted
        ";
        $object->execute($data);

        // Sync Junction Table
        $object->query = "DELETE FROM tbl_research_collaborators WHERE research_id = :rid";
        $object->execute([':rid' => $research_id]);

        $collaborators = isset($_POST['collaborators']) ? $_POST['collaborators'] : [];
        $profile_owner = isset($_POST['hiddeny']) ? $_POST['hiddeny'] : null;

        if (!empty($profile_owner) && !in_array($profile_owner, $collaborators)) {
            $collaborators[] = $profile_owner;
        }

        foreach($collaborators as $res_id) {
            $object->query = "INSERT INTO tbl_research_collaborators (research_id, researcher_id) VALUES (:rid, :uid)";
            $object->execute([':rid' => $research_id, ':uid' => $res_id]);
        }

        $success = '<div class="alert alert-success">Project & Collaborators Updated Successfully</div>';
		$output = array('error' => $error, 'success' => $success);
		echo json_encode($output);
	}

    // --- DELETE RESEARCH ---
	if($_POST["action_researchedconducted"] == 'delete')
	{
        $object->query = "DELETE FROM tbl_research_collaborators WHERE research_id = '".$_POST["xid"]."'";
        $object->execute();

		$object->query = "DELETE FROM tbl_researchconducted WHERE id = '".$_POST["xid"]."'";
		$object->execute();
		echo '<div class="alert alert-success">Research Data Deleted</div>';
	}
}
?>