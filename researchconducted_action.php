<?php

//category_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action_researchedconducted"]))
{
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
			$sub_array[] = $row["started_date"];
			$sub_array[] = $row["completed_date"];
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

		// $data = array(
		// 	':title'	=>	$_POST["title"]
		// );

		// $object->query = "
		// SELECT * FROM tbl_researchconducted
		// WHERE title = :title
		// ";
//'$from_date'




$timestamp = strtotime($_POST['started_date']);
 
// Creating new date format from that timestamp
$from_date = date("m-d-Y", $timestamp);
//

$timestamp1 = strtotime($_POST['completed_date']);
 
// Creating new date format from that timestamp
$to_date = date("m-d-Y", $timestamp1);













		// $object->execute($data);

		// if($object->row_count() > 0)
		// {
		// 	$error = '<div class="alert alert-danger">Category Already Exists</div>';
		// }
		// else
		// {
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
		// }

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
			$data['started_date'] = $row["started_date"];
			$data['completed_date'] = $row["completed_date"];
			$data['funding_source'] = $row["funding_source"];
			$data['approved_budget'] = $row["approved_budget"];
			$data['stat'] = $row["stat"];
			$data['terminal_report'] = $row["terminal_report"];
		}

		echo json_encode($data);
	}

	if ($_POST["action_researchedconducted"] == 'Edit') {
		// Convert started_date and completed_date to MM-DD-YYYY format
		$timestampu = strtotime($_POST['started_date']);
		$from_dateu = date("m-d-Y", $timestampu);  // Convert to MM-DD-YYYY
	
		$timestamp1u = strtotime($_POST['completed_date']);
		$to_dateu = date("m-d-Y", $timestamp1u);  // Convert to MM-DD-YYYY
	
		$error = '';
		$success = '';
	
		// Check if the title already exists in the database (excluding the current id)
		// $data = array(
		// 	':title' => $_POST['title'],
		// 	':hidden_id_researchedconducted' => $_POST['hidden_id_researchedconducted']
		// );
	
		// $object->query = "
		// 	SELECT * FROM tbl_researchconducted 
		// 	WHERE title = :title 
		// 	AND id != :hidden_id_researchedconducted
		// ";
	
		// $object->execute($data);
	
		// if ($object->row_count() > 0) {
		// 	$error = '<div class="alert alert-danger">Category Already Exists</div>';
		// } else {
			// Proceed with updating the record in the database
			$data = array(
				':title' => $_POST['title'],
				':research_agenda_cluster' => $_POST['research_agenda_cluster'],
				':sdgs'   => implode(", ", $_POST['sdgs']), 
				':started_date' => $from_dateu,  // Use the formatted date
				':completed_date' => $to_dateu,  // Use the formatted date
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
		// }
	
		$output = array(
			'error' => $error,
			'success' => $success
		);
	
		echo json_encode($output);
	}




	// if($_POST["action"] == 'Edit')
	// {
	// 	$error = '';

	// 	$success = '';

	// 	$data = array(
	// 		':category_name'	=>	$_POST["category_name"],
	// 		':category_id'	=>	$_POST['hidden_id']
	// 	);

	// 	$object->query = "
	// 	SELECT * FROM product_category_table 
	// 	WHERE category_name = :category_name 
	// 	AND category_id != :category_id
	// 	";

	// 	$object->execute($data);

	// 	if($object->row_count() > 0)
	// 	{
	// 		$error = '<div class="alert alert-danger">Category Already Exists</div>';
	// 	}
	// 	else
	// 	{

	// 		$data = array(
	// 			':category_name'		=>	$object->clean_input($_POST["category_name"])
	// 		);

	// 		$object->query = "
	// 		UPDATE product_category_table 
	// 		SET category_name = :category_name 
	// 		WHERE category_id = '".$_POST['hidden_id']."'
	// 		";

	// 		$object->execute($data);

	// 		$success = '<div class="alert alert-success">Category Updated</div>';
	// 	}

	// 	$output = array(
	// 		'error'		=>	$error,
	// 		'success'	=>	$success
	// 	);

	// 	echo json_encode($output);

	// }

























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