<?php
// category_action.php

include('rms.php');  // Include your database logic

$object = new rms();

header('Content-Type: application/json');  // Set header to return JSON

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action_rd = $_POST['action_rd'];
  

	if ($action_rd == 'update') {
		$error = '';
		$success = '';
	
		// Clean input data and prepare for database interaction
		// $data = array(
		// 	':researcherID' => $_POST['researcherIDu'],
		// 	':id' => $_POST['hidden_id_rd']  // Hidden ID for identifying the record to update
		// );
	
		// // Check if researcher ID already exists (excluding current ID)
		// $object->query = "
		// 	SELECT * FROM tbl_researchdata 
		// 	WHERE researcherID = :researcherID 
		// 	AND id != :id
		// ";
		// $object->execute($data);
	
		// if ($object->row_count() > 0) {
		// 	// Duplicate researcher ID found
		// 	$error = '<div class="alert alert-danger">Researcher ID Already Exists</div>';
		// } else {
			// No duplicates, proceed with the update query
			$update_data = array(
				':id' => $_POST['hidden_id_rd'], // Hidden ID for identifying the record to update
				':researcherID' => $_POST['researcherIDu'],
				':familyName' => $_POST['familyNameu'],
				':firstName' => $_POST['firstNameu'],
				':middleName' => $_POST['middleNameu'],
				':Suffix' => $_POST['Suffixu'],
				':department' => $_POST['departmentu'],
				':program' => $_POST['programu'],
				':bachelor_degree' => $_POST['bachelor_degreeu'],
				':bachelor_institution' => $_POST['bachelor_institutionu'],
				':bachelor_YearGraduated' => $_POST['bachelor_YearGraduatedu'],
				':masterDegree' => $_POST['masterDegreeu'],
				':masterInstitution' => $_POST['masterInstitutionu'],
				':masterYearGraduated' => $_POST['masterYearGraduatedu'],
				':doctorateDegree' => $_POST['doctorateDegreeu'],
				':doctorateInstitution' => $_POST['doctorateInstitutionu'],
				':doctorateYearGraduate' => $_POST['doctorateYearGraduateu'],
				':postDegree' => $_POST['postDegreeu'],
				':postInstitution' => $_POST['postInstitutionu'],
				':postYearGraduate' => $_POST['postYearGraduateu'],
				':user'			=>	$object->Get_user_name($_SESSION['user_id'])
			);
	
			// Update the researcher data in the database
			
			// familyName = :familyName,
			// 		firstName = :firstName,
			// 		middleName = :middleName,
			// 		suffix = :suffix,
			// 		department = :department,
			// 		program = :program,
			// 		bachelor_degree = :bachelor_degree,
			// 		bachelor_institution = :bachelor_institution,
			// 		bachelor_year = :bachelor_year,
			// 		master_degree = :master_degree,
			// 		master_institution = :master_institution,
			// 		master_year = :master_year,
			// 		doctorate_degree = :doctorate_degree,
			// 		doctorate_institution = :doctorate_institution,
			// 		doctorate_year = :doctorate_year,
			// 		post_degree = :post_degree,
			// 		post_institution = :post_institution,
			// 		post_year = :post_year
			$object->query = "
			UPDATE tbl_researchdata 
SET researcherID = :researcherID,
    familyName = :familyName,
    firstName = :firstName,
    middleName = :middleName,
    Suffix = :Suffix,
    department = :department,
    program = :program,
    bachelor_degree = :bachelor_degree,
    bachelor_institution = :bachelor_institution,
    bachelor_YearGraduated = :bachelor_YearGraduated,
    masterDegree = :masterDegree,
    masterInstitution = :masterInstitution,
    masterYearGraduated = :masterYearGraduated,
    doctorateDegree = :doctorateDegree,
    doctorateInstitution = :doctorateInstitution,
    doctorateYearGraduate = :doctorateYearGraduate,
    postDegree = :postDegree,
    postInstitution = :postInstitution,
    postYearGraduate = :postYearGraduate,
		user = :user
				WHERE id = :id
			";
			$object->execute($update_data);
	
			// Success message
			$success = '<div class="alert alert-success">Research Data Updated</div>';
		// }
	
		// Prepare the response data
		$output = array(
			'error' => $error,
			'success' => $success
		);
	
		// Return the response as JSON
		echo json_encode($output);
	}
}
	?>













