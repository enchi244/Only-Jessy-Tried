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
    $action_rd = $_POST['action_rd'] ?? '';

    if ($action_rd == 'update') {
        $error = '';
        $success = '';
    
        // 1. Prepare data array 
        $update_data = array(
            ':id'                     => $_POST['hidden_id_rd'] ?? '',
            ':researcherID'           => $_POST['researcherID'] ?? '',
            ':familyName'             => strtoupper($_POST['familyName'] ?? ''),
            ':firstName'              => strtoupper($_POST['firstName'] ?? ''),
            ':middleName'             => strtoupper($_POST['middleName'] ?? ''),
            ':Suffix'                 => strtoupper($_POST['Suffix'] ?? ''),
            ':department'             => $_POST['department'] ?? '',
            ':program'                => $_POST['program'] ?? '',
            ':academic_rank'          => $_POST['academic_rank'] ?? '',
            ':bachelor_degree'        => strtoupper($_POST['bachelor_degree'] ?? ''),
            ':bachelor_institution'   => strtoupper($_POST['bachelor_institution'] ?? ''),
            ':bachelor_YearGraduated' => strtoupper($_POST['bachelor_YearGraduated'] ?? ''),
            ':masterDegree'           => strtoupper($_POST['masterDegree'] ?? ''),
            ':masterInstitution'      => strtoupper($_POST['masterInstitution'] ?? ''),
            ':masterYearGraduated'    => strtoupper($_POST['masterYearGraduated'] ?? ''),
            ':doctorateDegree'        => strtoupper($_POST['doctorateDegree'] ?? ''),
            ':doctorateInstitution'   => strtoupper($_POST['doctorateInstitution'] ?? ''),
            ':doctorateYearGraduate'  => strtoupper($_POST['doctorateYearGraduate'] ?? ''),
            ':postDegree'             => strtoupper($_POST['postDegree'] ?? ''),
            ':postInstitution'        => strtoupper($_POST['postInstitution'] ?? ''),
            ':postYearGraduate'       => strtoupper($_POST['postYearGraduate'] ?? ''),
            ':user'                   => $object->Get_user_name($_SESSION['user_id'])
        );
    
        // 2. Execute SQL Query
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

        // Call execute directly. If it fails, rms.php kills the script. 
        // If it passes this line, it was successful.
        $object->execute($update_data);
        
        $success = '<div class="alert alert-success">Research Data Updated</div>';
    
        $output = array(
            'error'   => $error,
            'success' => $success
        );
    
        echo json_encode($output);
    }
}
?>