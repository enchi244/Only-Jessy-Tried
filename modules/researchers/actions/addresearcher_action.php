<?php

//addresearcher_action.php

include('../../../core/rms.php');

$object = new rms();

if(isset($_POST["action"]) && $_POST["action"] == 'Add')
{
    $error = '';
    $success = '';

    // Check both standard name and legacy 'u' appended name
    $researcherID = $_POST['researcherID'] ?? $_POST['researcherIDu'] ?? '';

    $data = array(
        ':researcherID' => $researcherID
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
            ':researcherID'          => $researcherID,
            ':familyName'            => $_POST['familyName'] ?? $_POST['familyNameu'] ?? '',
            ':firstName'             => $_POST['firstName'] ?? $_POST['firstNameu'] ?? '',
            ':middleName'            => $_POST['middleName'] ?? $_POST['middleNameu'] ?? '',
            ':Suffix'                => $_POST['Suffix'] ?? $_POST['Suffixu'] ?? '',
            ':department'            => $_POST['department'] ?? $_POST['departmentu'] ?? '',
            ':program'               => $_POST['program'] ?? $_POST['programu'] ?? '',
            ':academic_rank'         => $_POST['academic_rank'] ?? '', // NEW FIELD
            ':bachelor_degree'       => $_POST['bachelor_degree'] ?? $_POST['bachelor_degreeu'] ?? '',
            ':bachelor_institution'  => $_POST['bachelor_institution'] ?? $_POST['bachelor_institutionu'] ?? '',
            ':bachelor_YearGraduated'=> $_POST['bachelor_YearGraduated'] ?? $_POST['bachelor_YearGraduatedu'] ?? '',
            ':masterDegree'          => $_POST['masterDegree'] ?? $_POST['masterDegreeu'] ?? '',
            ':masterInstitution'     => $_POST['masterInstitution'] ?? $_POST['masterInstitutionu'] ?? '',
            ':masterYearGraduated'   => $_POST['masterYearGraduated'] ?? $_POST['masterYearGraduatedu'] ?? '',
            ':doctorateDegree'       => $_POST['doctorateDegree'] ?? $_POST['doctorateDegreeu'] ?? '',
            ':doctorateInstitution'  => $_POST['doctorateInstitution'] ?? $_POST['doctorateInstitutionu'] ?? '',
            ':doctorateYearGraduate' => $_POST['doctorateYearGraduate'] ?? $_POST['doctorateYearGraduateu'] ?? '',
            ':postDegree'            => $_POST['postDegree'] ?? $_POST['postDegreeu'] ?? '',
            ':postInstitution'       => $_POST['postInstitution'] ?? $_POST['postInstitutionu'] ?? '',
            ':postYearGraduate'      => $_POST['postYearGraduate'] ?? $_POST['postYearGraduateu'] ?? ''
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
            academic_rank, 
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
        VALUES (
            :researcherID, 
            :familyName, 
            :firstName, 
            :middleName, 
            :Suffix, 
            :department, 
            :program, 
            :academic_rank,
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
            :postYearGraduate,
            1
        )";

        $object->execute($data);

        $success = '<div class="alert alert-success">Researcher Added Successfully</div>';
    }

    $output = array(
        'error'     =>  $error,
        'success'   =>  $success
    );

    echo json_encode($output);
}
?>