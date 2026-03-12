<?php

//category_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action_publication"]))
{
	if($_POST["action_publication"] == 'fetch')
	{
		$order_column = array(
            'researcherID',
            'title',
            'publication_date',
            'journal',
            'vol_num_issue_num',
            'issn_isbn',
            'indexing',
            'start',  // 'start' is now a number (e.g., year or identifier)
            'end'
		);

		$output = array();

        $main_query = "SELECT * FROM tbl_publication";
        $search_query = " WHERE researcherID = '".$_POST["rid"]."' ";

        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= "AND (title LIKE '%" . $search_value . "%' ";
            $search_query .= "OR publication_date LIKE '%" . $search_value . "%' ";
            $search_query .= "OR journal LIKE '%" . $search_value . "%' ) ";
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
            $sub_array[] = $row["start"];  // start as a number (e.g., year or identifier)
            $sub_array[] = $row["end"];
            $sub_array[] = $row["journal"];
            $sub_array[] = $row["vol_num_issue_num"];
            $sub_array[] = $row["issn_isbn"];
            $sub_array[] = $row["indexing"];
            $sub_array[] = $row["publication_date"];
            $sub_array[] = '
			<div align="center">
				<button type="button" name="edit_button_publication" title="Update Publication" style="margin-left: 5px; margin-bottom: 5px; margin-top:5px;" data-toggle="tooltip" class="btn btn-primary btn-sm edit_button_publication" name="edit_button_publication"  data-id="'.$row["id"].'"><i class="fas fa-pencil-alt"></i></button>
			
			<button type="button" name="delete_button_publication" title="Delete Pubication" style="margin-left: 5px;" data-toggle="tooltip" class="btn btn-danger btn-sm delete_button_publication" data-id="'.$row["id"].'"><i class="far fa-trash-alt"></i></button>
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

	if($_POST["action_publication"] == 'Add')
	{
		$error = '';

		$success = '';

	// 	$data = array(
	// 		':title'	=>	$_POST["title_pub"]
	// 	);

    //     $object->query = "
    //     SELECT * FROM tbl_publication
    //     WHERE title = :title
    // ";
//'$from_date'




$timestamp = strtotime($_POST['publication_date']);
 
// Creating new date format from that timestamp
$fpublication_date = date("m-d-Y", $timestamp);



// $timestamp1 = strtotime($_POST['start']);
 
// // Creating new date format from that timestamp
// $fstart_date = date("m-d-Y", $timestamp1);
// $timestamp2 = strtotime($_POST['end']);
 
// // Creating new date format from that timestamp
// $fend_date = date("m-d-Y", $timestamp2);




                   















		// $object->execute($data);

		// if($object->row_count() > 0)
		// {
		// 	$error = '<div class="alert alert-danger">Publication Already Exists</div>';
		// }
		// else
		// {  
			$data = array(
                    ':researcherID'        => $_POST['hidden_researcherID'],
                    ':title'               => $_POST['title_pub'],
                    ':start' => $_POST['start'],
                    ':end'   => $_POST['end'],
                    ':journal'             => $_POST['journal'],
                    ':vol_num_issue_num'  => $_POST['vol_num_issue_num'],
                    ':issn_isbn'           => $_POST['issn_isbn'],
                    ':indexing'            => $_POST['indexing'],
                    ':publication_date'    => $fpublication_date
            
            
                );

			$object->query = "
			    INSERT INTO tbl_publication
                (researcherID, title, start, end, journal, vol_num_issue_num, issn_isbn, indexing, publication_date) 
                VALUES 
                (:researcherID, :title, :start, :end, :journal, :vol_num_issue_num, :issn_isbn, :indexing, :publication_date)
			";


		




			$object->execute($data);

			$success = '<div class="alert alert-success">Publication Added</div>';
		// }

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);

	}

	if($_POST["action_publication"] == 'fetch_single')
	{

		$object->query = "
		   SELECT * FROM tbl_publication
		WHERE id = '".$_POST["publicationID"]."'
		";

		$result = $object->get_result();

		$data = array();

		foreach($result as $row)
		{
            $data['title'] = $row["title"];
            $data['start'] = $row["start"];  // 'start' is now a number
            $data['end'] = $row["end"];
            $data['journal'] = $row["journal"];
            $data['vol_num_issue_num'] = $row["vol_num_issue_num"];
            $data['issn_isbn'] = $row["issn_isbn"];
            $data['indexing'] = $row["indexing"];
            $data['publication_date'] = $row["publication_date"];
		}

		echo json_encode($data);
	}

	if ($_POST["action_publication"] == 'Edit') {
		// Convert started_date and completed_date to MM-DD-YYYY format
	
	
		$timestampu12 = strtotime($_POST['publication_date']);
		$publication_datep = date("m-d-Y", $timestampu12);  // Convert to MM-DD-YYYY
	
	
		$error = '';
		$success = '';
	
		// Check if the title already exists in the database (excluding the current id)
		// $data = array(
		// 	':title' => $_POST['title_pub'],
		// 	':hidden_publicationID' => $_POST['hidden_publicationID']
		// );
	
		// $object->query = "
		// SELECT * FROM tbl_publication 
		// 	WHERE title = :title 
		// 	AND id != :hidden_publicationID
		// ";
	
		// $object->execute($data);
	
		// if ($object->row_count() > 0) {
		// 	$error = '<div class="alert alert-danger">Publication Already Exists</div>';
		// } else {
			// Proceed with updating the record in the database
			$data = array(
			   ':title' => $_POST['title_pub'],
					':start' => $_POST['start'],
					':end' => $_POST['end'],
					':journal' => $_POST['journal'],
					':vol_num_issue_num' => $_POST['vol_num_issue_num'],
					':issn_isbn' => $_POST['issn_isbn'],
					':indexing' => $_POST['indexing'],
					':publication_date' => $publication_datep,
					':hidden_publicationID' => $_POST['hidden_publicationID']
				);
	
			$object->query = "
				 UPDATE tbl_publication 
					SET title = :title, 
						start = :start, 
						end = :end, 
						journal = :journal, 
						vol_num_issue_num = :vol_num_issue_num, 
						issn_isbn = :issn_isbn, 
						indexing = :indexing, 
						publication_date = :publication_date  
					WHERE id = :hidden_publicationID
				";
			$object->execute($data);
	
			$success = '<div class="alert alert-success">Publication Updated</div>';
		// }
	
		$output = array(
			'error' => $error,
			'success' => $success
		);
	
		echo json_encode($output);
	}
	
	



























	if($_POST["action_publication"] == 'delete')
	{

		$object->query = "
		DELETE FROM tbl_publication
		WHERE id = '".$_POST["publicationID"]."'
		";

		$object->execute();

		echo '<div class="alert alert-success">Publication Deleted</div>';
	}
}

?>