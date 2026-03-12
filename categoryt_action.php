<?php

//category_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action_t"]))
{
	if($_POST["action_t"] == 'fetch')
	{
		$order_column = array('category_name');

		$output = array();

		$main_query = "
		SELECT * FROM product_category_table ";

		$search_query = '';

		if(isset($_POST["search"]["value"]))
		{
			$search_query .= 'WHERE category_name LIKE "%'.$_POST["search"]["value"].'%" ';
		}

		if(isset($_POST["order"]))
		{
			$order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_query = 'ORDER BY category_id DESC ';
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
			$sub_array[] = html_entity_decode($row["category_name"]);
			
			
		
			$sub_array[] = '
			<div align="center">
			<button type="button" name="edit_buttonct" title="Update Category" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" data-toggle="tooltip" class="btn btn-primary btn-sm edit_buttonct" name="edit_buttonct"  data-id="'.$row["category_id"].'"><i class="fas fa-pencil-alt"></i></button>
			
			<button type="button" name="delete_buttonct" title="Delete Category" style="margin-left: 5px;" data-toggle="tooltip" class="btn btn-danger btn-sm delete_buttonct" data-id="'.$row["category_id"].'" ><i class="far fa-trash-alt"></i></button>
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

	if($_POST["action_t"] == 'Create')
	{
		$error = '';

		$success = '';

		$data = array(
			':category_name'	=>	$_POST["category_name_t"]
		);

		$object->query = "
		SELECT * FROM product_category_table 
		WHERE category_name = :category_name
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Category Already Exists</div>';
		}
		else
		{
			$data = array(
				':category_name'			=>	$object->clean_input($_POST["category_name_t"])
			);

			$object->query = "
			INSERT INTO product_category_table 
			(category_name) 
			VALUES (:category_name)
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Category Added</div>';
		}

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);

	}

	if($_POST["action_t"] == 'fetch_single')
	{
		$object->query = "
		SELECT * FROM product_category_table 
		WHERE category_id ='".$_POST['category_id2']."'
		";
		
		$result = $object->get_result();

		$data = array();

		foreach($result as $row)
		{
			$data['category_name'] = $row['category_name'];
		}

		echo json_encode($data);
	}

	if($_POST["action_t"] == 'Edit')
	{
		$error = '';

		$success = '';

		$data = array(
			':category_name'	=>	$_POST["category_name_t"],
			':category_id'	=>		$_POST['hidden_id_t']
		);

		$object->query = "
		SELECT * FROM product_category_table 
		WHERE category_name = :category_name 
		AND category_id != :category_id
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Category Already Exists</div>';
		}
		else
		{

			$data = array(
				':category_name'		=>	$object->clean_input($_POST["category_name_t"])
			);

			$object->query = "
			UPDATE product_category_table 
			SET category_name = :category_name 
			
			WHERE category_id ='".$_POST['hidden_id_t']."'
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Category Updated</div>';
		}

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);

	}



	if($_POST["action_t"] == 'delete')
	{
		$object->query = "
		DELETE FROM product_category_table 
		WHERE category_id = '".$_POST["ide"]."'
		";

		$object->execute();

		echo '<div class="alert alert-success">Category Deleted</div>';
	}
}

?>