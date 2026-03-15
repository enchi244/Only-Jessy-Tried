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
	if($_POST["action_researchedconducted"] == 'fetch_collaborators')
    {
        header('Content-Type: application/json');
        
        $id = intval($_POST['id']);
        
        // Use the ID to find the exact title natively in the DB, bypassing all string issues
        $object->query = "
            SELECT d.id, d.firstName, d.familyName, d.department 
            FROM tbl_researchconducted r 
            JOIN tbl_researchdata d ON r.researcherID = d.id 
            WHERE r.title = (SELECT title FROM tbl_researchconducted WHERE id = '".$id."')
        ";
        $object->execute();
        $collaborators = $object->get_result();
        echo json_encode($collaborators);
        exit;
    }
    // --- MASTER TABLE LOGIC (NEW) ---
    if($_POST["action_researchedconducted"] == 'fetch_all')
    {
        $order_column = array(
            'tbl_researchdata.familyName',
            'tbl_researchconducted.title',
            'tbl_researchconducted.research_agenda_cluster',
            'tbl_researchconducted.sdgs',
            'tbl_researchconducted.stat'
        );

        $output = array();

        $main_query = "
            SELECT tbl_researchconducted.*, 
                   (SELECT GROUP_CONCAT(d.familyName SEPARATOR ', ') 
                    FROM tbl_researchconducted rc 
                    LEFT JOIN tbl_researchdata d ON rc.researcherID = d.id 
                    WHERE rc.title = tbl_researchconducted.title) AS all_authors,
                   tbl_researchdata.id AS author_db_id, tbl_researchdata.firstName, tbl_researchdata.familyName, tbl_researchdata.middleName, tbl_researchdata.Suffix 
            FROM tbl_researchconducted 
            INNER JOIN (
                SELECT MIN(id) as original_id 
                FROM tbl_researchconducted 
                GROUP BY title
            ) as original_research ON tbl_researchconducted.id = original_research.original_id
            LEFT JOIN tbl_researchdata ON tbl_researchconducted.researcherID = tbl_researchdata.id
        ";
        $search_query = " WHERE 1=1 ";

        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (tbl_researchconducted.title LIKE '%" . $search_value . "%' ";
            $search_query .= "OR tbl_researchdata.familyName LIKE '%" . $search_value . "%' ";
            $search_query .= "OR tbl_researchconducted.research_agenda_cluster LIKE '%" . $search_value . "%' ) ";
        }

        if (isset($_POST["order"])) {
            $order_query = "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " ";
        } else {
            $order_query = "ORDER BY tbl_researchconducted.id DESC ";
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

			$author_name = $row["all_authors"] ? $row["all_authors"] : "Unknown Author";
            
            
            $sub_array[] = '<span class="font-weight-bold">'.$author_name.'</span>';
            $sub_array[] = $row["title"];
            $sub_array[] = $row["research_agenda_cluster"];
            $sub_array[] = $row["sdgs"];
            $sub_array[] = $row["stat"];
            
            $sub_array[] = '
            <div align="center">
                <button type="button" class="btn btn-info btn-sm view_collaborators" data-id="'.$row["id"].'" title="View Collaborators"><i class="fas fa-users"></i></button>
                <button type="button" class="btn btn-danger btn-sm delete_master_researchconducted" data-id="'.$row["id"].'" title="Delete Record"><i class="far fa-trash-alt"></i></button>
                <a href="view_researcher.php?id='.$row["author_db_id"].'&tab=education" class="btn d-none"></a>
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


	if($_POST["action_researchedconducted"] == 'fetch')
	{
		$order_column = array(
			'researcherID', 
			'title', 
			'research_agenda_cluster', 
			'sdgs', 
			'started_date', 
			'completed_date', 
			'funding_source', 
			'approved_budget', 
			'stat', 
			'terminal_report'
		);

		$output = array();

		$main_query = "
	SELECT *
FROM tbl_researchconducted";
$search_query = " WHERE researcherID = '".$_POST["rid"]."' ";
//$search_query = " WHERE researcherID = 1 "; // Initial condition to filter by status

if (isset($_POST["search"]["value"])) {
    $search_value = $_POST["search"]["value"];
    $search_query .= "AND (started_date LIKE '%" . $search_value . "%' ";
    $search_query .= "OR completed_date LIKE '%" . $search_value . "%') ";
}

if (isset($_POST["order"])) {
    $order_query = "ORDER BY " . $order_column[$_POST["order"]["0"]["column"]] . " " . $_POST["order"]["0"]["dir"] . " ";
} else {
    $order_query = "ORDER BY id ASC ";
}

$limit_query = "";
		if($_POST["length"] != -1)
		{
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
			$sub_array[] = '
			<div align="center">
				<button type="button" name="edit_buttonrc" title="Update Category" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" data-toggle="tooltip" class="btn btn-primary btn-sm edit_buttonrc" name="edit_buttonrc"  data-id="'.$row["id"].'"><i class="fas fa-pencil-alt"></i></button>
			
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

	if($_POST["action_researchedconducted"] == 'Add')
	{
		$error = '';
		$success = '';

		$timestamp = strtotime($_POST['started_date']);
		$from_date = date("m-d-Y", $timestamp);

		$timestamp1 = strtotime($_POST['completed_date']);
		$to_date = date("m-d-Y", $timestamp1);

        $data = array(
                ':researcherID'                => $_POST['hiddeny'],
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
        $success = '<div class="alert alert-success">Research Conducted Added</div>';

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);
	}

	if($_POST["action_researchedconducted"] == 'fetch_single')
	{
		$object->query = "
		SELECT * FROM tbl_researchconducted 
		WHERE id = '".$_POST["rcid"]."'
		";

		$result = $object->get_result();
		$data = array();

		foreach($result as $row)
		{
			$data['title'] = $row["title"];
			$data['research_agenda_cluster'] = $row["research_agenda_cluster"];
			$data['sdgs'] = $row["sdgs"];
			$data['started_date'] = parse_legacy_date_php($row["started_date"]);
			$data['completed_date'] = parse_legacy_date_php($row["completed_date"]);
            
			// Failsafes in case the JavaScript expects a different key name
			$data['start_date'] = parse_legacy_date_php($row["started_date"]);
			$data['end_date'] = parse_legacy_date_php($row["completed_date"]);
			$data['start'] = parse_legacy_date_php($row["started_date"]);
			$data['end'] = parse_legacy_date_php($row["completed_date"]);
			$data['funding_source'] = $row["funding_source"];
			$data['approved_budget'] = $row["approved_budget"];
			$data['stat'] = $row["stat"];
			$data['terminal_report'] = $row["terminal_report"];
		}
		echo json_encode($data);
	}

	if ($_POST["action_researchedconducted"] == 'Edit') {
		$timestampu = strtotime($_POST['started_date']);
		$from_dateu = date("m-d-Y", $timestampu);  
	
		$timestamp1u = strtotime($_POST['completed_date']);
		$to_dateu = date("m-d-Y", $timestamp1u); 
	
		$error = '';
		$success = '';

        $data = array(
            ':title' => $_POST['title'],
            ':research_agenda_cluster' => $_POST['research_agenda_cluster'],
            ':sdgs'   => implode(", ", $_POST['sdgs']), 
            ':started_date' => $from_dateu,  
            ':completed_date' => $to_dateu,  
            ':funding_source' => $_POST['funding_source'],
            ':approved_budget' => $_POST['approved_budget'],
            ':stat' => $_POST['stat'],
            ':terminal_report' => $_POST['terminal_report'],
            ':hidden_id_researchedconducted' => $_POST['hidden_id_researchedconducted']
        );

        $object->query = "
            UPDATE tbl_researchconducted 
            SET title = :title, 
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
        $success = '<div class="alert alert-success">Category Updated</div>';
	
		$output = array('error' => $error, 'success' => $success);
		echo json_encode($output);
	}

	if($_POST["action_researchedconducted"] == 'delete')
	{
		$object->query = "
		DELETE FROM tbl_researchconducted
		WHERE id = '".$_POST["xid"]."'
		";
		$object->execute();
		echo '<div class="alert alert-success">Category Deleted</div>';
	}
}
?>