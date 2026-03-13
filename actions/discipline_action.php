<?php

//category_action.php

include('../core/rms.php');

$object = new rms();

if(isset($_POST["action"]))
{
	if($_POST["action"] == 'fetch')
	{
		$order_column = array('major', 'disc_status');

		$output = array();

		$main_query = "
		SELECT * FROM tbl_majordiscipline ";

		$search_query = '';

		if(isset($_POST["search"]["value"]))
		{
			$search_query .= 'WHERE major LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR disc_status LIKE "%'.$_POST["search"]["value"].'%" ';
		}

		if(isset($_POST["order"]))
		{
			$order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_query = 'ORDER BY majorID ASC ';
		}

		$limit_query = '';

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
			$sub_array[] = html_entity_decode($row["major"]);
			$status = '';
			if($row["disc_status"] == 'Enable')         
			{
				$status = '<button type="button" name="status_button" class="btn btn-success btn-sm status_button" data-id="'.$row["majorID"].'" data-status="'.$row["disc_status"].'">Enable</button>';
			}
			else
			{
				$status = '<button type="button" name="status_button" class="btn btn-danger btn-sm status_button" data-id="'.$row["majorID"].'" data-status="'.$row["disc_status"].'">Disable</button>';
			}
			$sub_array[] = $status;
			$sub_array[] = '
			<div align="center">
			<button type="button" name="edit_button" title="Update Discipline" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" data-toggle="tooltip" class="btn btn-primary btn-sm edit_button" name="edit_button"  data-id="'.$row["majorID"].'"><i class="fas fa-pencil-alt"></i></button>
			
					</div>
			';
			$data[] = $sub_array;
		}
		// <button type="button" name="delete_button" title="Delete Category" style="margin-left: 5px;" data-toggle="tooltip" class="btn btn-danger btn-sm delete_button" data-id="'.$row["majorID"].'" data-status="'.$row["disc_status"].'"><i class="far fa-trash-alt"></i></button>


		$output = array(
			"draw"    			=> 	intval($_POST["draw"]),
			"recordsTotal"  	=>  $total_rows,
			"recordsFiltered" 	=> 	$filtered_rows,
			"data"    			=> 	$data
		);
			
		echo json_encode($output);

	}

	if($_POST["action"] == 'Add')
	{
		$error = '';

		$success = '';

		$data = array(
			':category_name'	=>	$_POST["category_name"]
		);

		$object->query = "
		SELECT * FROM tbl_majordiscipline
		WHERE major = :category_name
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Discipline Already Exists</div>';
		}
		else
		{
			$data = array(
				':major'			=>	$object->clean_input($_POST["category_name"]),
				':disc_status'			=>	'Enable',
			);

			$object->query = "
			INSERT INTO tbl_majordiscipline 
			(major, disc_status) 
			VALUES (:major, :disc_status)
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Discipline Added</div>';
		}

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);

	}

	if($_POST["action"] == 'fetch_single')
	{
		$object->query = "
		SELECT major,disc_status FROM tbl_majordiscipline
		WHERE majorID = '".$_POST["category_id"]."'
		";

		$result = $object->get_result();

		$data = array();

		foreach($result as $row)
		{
			$data['category_name'] = $row['major'];
		}

		echo json_encode($data);
	}

	if($_POST["action"] == 'Edit')
	{
		$error = '';

		$success = '';

		$data = array(
			':category_name'	=>	$_POST["category_name"],
			':category_id'	=>	$_POST['hidden_id']
		);

		$object->query = "
			SELECT major,disc_status FROM tbl_majordiscipline
		WHERE major = :category_name 
		AND majorID != :category_id
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Discipline Already Exists</div>';
		}
		else
		{

			$data = array(
				':category_name'		=>	$object->clean_input($_POST["category_name"])
			);

			$object->query = "
			UPDATE tbl_majordiscipline 
			SET major = :category_name 
			WHERE majorID = '".$_POST['hidden_id']."'
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Discipline Updated</div>';
		}

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);

	}

	if($_POST["action"] == 'change_status')
	{
		$data = array(
			':category_status'		=>	$_POST['next_status']
		);

		$object->query = "
		UPDATE tbl_majordiscipline 
		SET disc_status = :category_status 
		WHERE majorID = '".$_POST["id"]."'
		";

		$object->execute($data);

		echo '<div class="alert alert-success">Discipline Status change to '.$_POST['next_status'].'</div>';
	}

	// if($_POST["action"] == 'delete')
	// {
	// 	$object->query = "
	// 	DELETE FROM tbl_majordiscipline 
	// 	WHERE majorID = '".$_POST["id"]."'
	// 	";

	// 	$object->execute();

	// 	echo '<div class="alert alert-success">Category Deleted</div>';
	// }
}

?>