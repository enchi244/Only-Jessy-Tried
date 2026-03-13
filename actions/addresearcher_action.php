<?php

//category_action.php

include('../core/rms.php');

$object = new rms();


	if($_POST["action"] == 'Add')
	{
        alert("glied");
		$error = '';

		$success = '';

		$data = array(
		':researcherID'          => $_POST['researcherIDu']

			
		);

		$object->query = "
		SELECT * FROM tbl_researchdata
		WHERE researcherID = :researcherID
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Researcher Already Exists</div>';
		}
		else
		{
			$data = array(
				':researcherID'          => $_POST['researcherIDu'],
				':familyName'            => $_POST['familyNameu'],
				':firstName'             => $_POST['firstNameu'],
				':middleName'            => $_POST['middleNameu'],
				':Suffix'                => $_POST['Suffixu'],
				':department'            => $_POST['departmentu'],
				':program'               => $_POST['programu'],
				':bachelor_degree'       => $_POST['bachelor_degreeu'],
				':bachelor_institution'  => $_POST['bachelor_institutionu'],
				':bachelor_YearGraduated'=> $_POST['bachelor_YearGraduatedu'],
				':masterDegree'          => $_POST['masterDegreeu'],
				':masterInstitution'     => $_POST['masterInstitutionu'],
				':masterYearGraduated'   => $_POST['masterYearGraduatedu'],
				':doctorateDegree'       => $_POST['doctorateDegreeu'],
				':doctorateInstitution'  => $_POST['doctorateInstitutionu'],
				':doctorateYearGraduate' => $_POST['doctorateYearGraduateu'],
				':postDegree'            => $_POST['postDegreeu'],
				':postInstitution'       => $_POST['postInstitutionu'],
				':postYearGraduate'      => $_POST['postYearGraduateu']
			);

			$object->query = "
			INSERT INTO tbl_researchdata (
    researcherID, 
    familyName, 
    firstName, 
    middleName, 
    Suffix, 
    department, 
    program, 
    bachelor_degree, 
    bachelor_institution, 
    bachelor_YearGraduated, 
    masterDegree, 
    masterInstitution, 
    masterYearGraduated, 
    doctorateDegree, 
    doctorateInstitution, 
    doctorateYearGraduate, 
    postDegree, 
    postInstitution, 
    postYearGraduate,
	status
) 
			VALUES (:researcherID, 
    :familyName, 
    :firstName, 
    :middleName, 
    :Suffix, 
    :department, 
    :program, 
    :bachelor_degree, 
    :bachelor_institution, 
    :bachelor_YearGraduated, 
    :masterDegree, 
    :masterInstitution, 
    :masterYearGraduated, 
    :doctorateDegree, 
    :doctorateInstitution, 
    :doctorateYearGraduate, 
    :postDegree, 
    :postInstitution, 
    :postYearGraduate)
	";

			$object->execute($data);

			$success = '<div class="alert alert-success">Researcher Added</div>';
		}

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);

	}
