<?php
// update_researcher.php

include('../../../core/rms.php');

$object = new rms();

// Ensure session is started if Get_user_name relies on it
if(!isset($_SESSION)) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action_rd = $_POST['action_rd'];

    if ($action_rd == 'update') {
        $error = '';
        $success = '';
    
        // 1. Prepare data array - Fixed missing comma after academic_ranku
        $update_data = array(
            ':id'                     => $_POST['hidden_id_rd'],
            ':researcherID'           => $_POST['researcherIDu'],
            ':familyName'             => $_POST['familyNameu'],
            ':firstName'              => $_POST['firstNameu'],
            ':middleName'             => $_POST['middleNameu'],
            ':Suffix'                 => $_POST['Suffixu'],
            ':department'             => $_POST['departmentu'],
            ':program'                => $_POST['programu'],
            ':academic_rank'          => $_POST['academic_ranku'], // Added comma here
            ':bachelor_degree'        => $_POST['bachelor_degreeu'],
            ':bachelor_institution'   => $_POST['bachelor_institutionu'],
            ':bachelor_YearGraduated' => $_POST['bachelor_YearGraduatedu'],
            ':masterDegree'           => $_POST['masterDegreeu'],
            ':masterInstitution'      => $_POST['masterInstitutionu'],
            ':masterYearGraduated'    => $_POST['masterYearGraduatedu'],
            ':doctorateDegree'        => $_POST['doctorateDegreeu'],
            ':doctorateInstitution'   => $_POST['doctorateInstitutionu'],
            ':doctorateYearGraduate'  => $_POST['doctorateYearGraduateu'],
            ':postDegree'             => $_POST['postDegreeu'],
            ':postInstitution'        => $_POST['postInstitutionu'],
            ':postYearGraduate'       => $_POST['postYearGraduateu'],
            ':user'                   => $object->Get_user_name($_SESSION['user_id'])
        );
    
        // 2. Updated SQL Query - Added academic_rank to the SET clause
        $object->query = "
            UPDATE tbl_researchdata 
            SET researcherID = :researcherID,
                familyName = :familyName,
                firstName = :firstName,
                middleName = :middleName,
                Suffix = :Suffix,
                department = :department,
                program = :program,
                academic_rank = :academic_rank, 
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

        if($object->execute($update_data)) {
            $success = '<div class="alert alert-success">Research Data Updated</div>';
        } else {
            $error = '<div class="alert alert-danger">Database Error: Could not update record.</div>';
        }
    
        $output = array(
            'error'   => $error,
            'success' => $success
        );
    
        echo json_encode($output);
    }
}
?>