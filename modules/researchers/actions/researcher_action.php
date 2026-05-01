<?php
// actions/researcher_action.php
include('../../../core/rms.php');
$object = new rms();

if(isset($_POST["action_link"]) && $_POST["action_link"] == 'link_multiple') {
    $research_id = intval($_POST["existing_research_id"]);
    if($research_id > 0) {
        $object->query = "DELETE FROM tbl_research_collaborators WHERE research_id = '".$research_id."'";
        $object->execute();
        $primary_researcher = null;
        if(isset($_POST['linked_researchers']) && is_array($_POST['linked_researchers'])) {
            foreach($_POST['linked_researchers'] as $index => $new_researcher_id) {
                $r_id = intval($new_researcher_id);
                if ($index === 0) { $primary_researcher = $r_id; }
                $object->query = "INSERT INTO tbl_research_collaborators (research_id, researcher_id) VALUES ('".$research_id."', '".$r_id."')";
                $object->execute();
            }
        }
        if ($primary_researcher !== null) {
            $object->query = "UPDATE tbl_researchconducted SET researcherID = '".$primary_researcher."' WHERE id = '".$research_id."'";
            $object->execute();
        }
        echo 'Success';
    }
    exit;
}

if(isset($_POST["action"])) {

    if($_POST["action"] == 'fetch') {
        $order_column = array(
            'researcherID', 'familyName', 'firstName', 'middleName', 'Suffix', 'department', 'program', 
            'bachelor_degree', 'bachelor_institution', 'bachelor_YearGraduated', 'masterDegree', 
            'masterInstitution', 'masterYearGraduated', 'doctorateDegree', 'doctorateInstitution', 
            'doctorateYearGraduate', 'postDegree', 'postInstitution', 'postYearGraduate', 'user_created_on'
        );
        $main_query = "SELECT * FROM tbl_researchdata WHERE status = 1 ";

        if (!empty($_POST["filter_rank"])) {
            $search_query .= 'AND academic_rank = "' . $_POST["filter_rank"] . '" ';
        }
        if (!empty($_POST["filter_program"])) {
            $search_query .= 'AND program = "' . $_POST["filter_program"] . '" ';
        }
        if (isset($_POST["search"]["value"])) {
            $search_value = $_POST["search"]["value"];
            $search_query .= 'AND (researcherID LIKE "%' . $search_value . '%" ';
            $search_query .= 'OR familyName LIKE "%' . $search_value . '%") ';
        }
        if(isset($_POST["order"])) {
            $order_column = ['researcherID', 'familyName', 'department', 'program', 'user_created_on'];
            $order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
        } else {
            $order_query = 'ORDER BY id ASC ';
        }
        if (strpos($order_query, 'department') === false) {
            $order_query .= ', department ASC'; 
        }
        
        $limit_query = '';
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
            $sub_array[] = $row["researcherID"];
            $rank_badge = !empty($row["academic_rank"]) ? '<span class="badge badge-success px-2 py-1 ml-2 align-text-top" style="font-size:0.7rem;"><i class="fas fa-award"></i> ' . htmlspecialchars($row["academic_rank"]) . '</span>' : '';
            $discipline_badge = !empty($row["program"]) ? '<div class="small text-muted mt-1"><i class="fas fa-book-reader"></i> ' . htmlspecialchars($row["program"]) . '</div>' : '';
            $sub_array[] = "<div class='font-weight-bold text-gray-800'>" . htmlspecialchars($row["familyName"].", ".$row["firstName"]." ".trim($row["middleName"]." ".$row["Suffix"])) . $rank_badge . "</div>" . $discipline_badge;
            
            // Render custom department name on the master table
            $dept_display = $row["department"];
            if ($dept_display === 'Others' && !empty($row["departments_units"])) {
                $dept_display = htmlspecialchars($row["departments_units"]);
            }
            $sub_array[] = !empty($dept_display) ? $dept_display : "N/A";
            $sub_array[] = !empty($row["program"]) ? $row["program"] : "N/A";
            $sub_array[] = $row["user_created_on"];
            $sub_array[] = '
            <div align="center">
                <button type="button" name="delete_buttona" title="Move to Recycle Bin" style="margin-left: 5px;" data-toggle="tooltip" class="btn btn-danger btn-sm delete_buttona" data-id="'.$row["id"].'"><i class="far fa-trash-alt"></i></button>
                <a href="view_researcher.php?id='.$row["id"].'" class="btn d-none"></a>
            </div>
            ';
            $data[] = $sub_array;
        }
        
        $output = array(
            "draw"            => intval($_POST["draw"]),
            "recordsTotal"    => $total_rows,
            "recordsFiltered" => $filtered_rows,
            "data"            => $data
        );
        echo json_encode($output);
    }

    if($_POST["action"] == 'Add') {
        $error = ''; $success = '';
        $researcherID = $_POST['researcherID'] ?? $_POST['researcherIDu'] ?? '';
        
        $data = array(':researcherID' => $researcherID);
        $object->query = "SELECT * FROM tbl_researchdata WHERE researcherID = :researcherID";
        $object->execute($data);
        
        if($object->row_count() > 0) {
            $error = '<div class="alert alert-danger">Researcher Already Exists</div>';
        } else {
            $dept = $_POST['department'] ?? $_POST['departmentu'] ?? '';
            $units = ($dept === 'Others') ? ($_POST['departments_units'] ?? '') : '';

            $data = array(
                ':researcherID'          => $researcherID,
                ':familyName'            => strtoupper($_POST['familyName'] ?? $_POST['familyNameu'] ?? ''),
                ':firstName'             => strtoupper($_POST['firstName'] ?? $_POST['firstNameu'] ?? ''),
                ':middleName'            => strtoupper($_POST['middleName'] ?? $_POST['middleNameu'] ?? ''),
                ':Suffix'                => strtoupper($_POST['Suffix'] ?? $_POST['Suffixu'] ?? ''),
                ':department'            => $dept,
                ':departments_units'     => strtoupper($units), // <-- ADDED
                ':program'               => $_POST['program'] ?? $_POST['programu'] ?? '',
                ':academic_rank'         => $_POST['academic_rank'] ?? $_POST['academic_ranku'] ?? '',
                ':bachelor_degree'       => strtoupper($_POST['bachelor_degree'] ?? $_POST['bachelor_degreeu'] ?? ''),
                ':bachelor_institution'  => strtoupper($_POST['bachelor_institution'] ?? $_POST['bachelor_institutionu'] ?? ''),
                ':bachelor_YearGraduated'=> strtoupper($_POST['bachelor_YearGraduated'] ?? $_POST['bachelor_YearGraduatedu'] ?? ''),
                ':masterDegree'          => strtoupper($_POST['masterDegree'] ?? $_POST['masterDegreeu'] ?? ''),
                ':masterInstitution'     => strtoupper($_POST['masterInstitution'] ?? $_POST['masterInstitutionu'] ?? ''),
                ':masterYearGraduated'   => strtoupper($_POST['masterYearGraduated'] ?? $_POST['masterYearGraduatedu'] ?? ''),
                ':doctorateDegree'       => strtoupper($_POST['doctorateDegree'] ?? $_POST['doctorateDegreeu'] ?? ''),
                ':doctorateInstitution'  => strtoupper($_POST['doctorateInstitution'] ?? $_POST['doctorateInstitutionu'] ?? ''),
                ':doctorateYearGraduate' => strtoupper($_POST['doctorateYearGraduate'] ?? $_POST['doctorateYearGraduateu'] ?? ''),
                ':postDegree'            => strtoupper($_POST['postDegree'] ?? $_POST['postDegreeu'] ?? ''),
                ':postInstitution'       => strtoupper($_POST['postInstitution'] ?? $_POST['postInstitutionu'] ?? ''),
                ':postYearGraduate'      => strtoupper($_POST['postYearGraduate'] ?? $_POST['postYearGraduateu'] ?? ''),
                ':user'                  => $object->Get_user_name($_SESSION['user_id'])
            );
            $object->query = "
            INSERT INTO tbl_researchdata (
                researcherID, familyName, firstName, middleName, Suffix, department, departments_units, program, 
                academic_rank, bachelor_degree, bachelor_institution, bachelor_YearGraduated, masterDegree, 
                masterInstitution, masterYearGraduated, doctorateDegree, doctorateInstitution, 
                doctorateYearGraduate, postDegree, postInstitution, postYearGraduate, status, user
            ) 
            VALUES (
                :researcherID, :familyName, :firstName, :middleName, :Suffix, :department, :departments_units, :program, :academic_rank,
                :bachelor_degree, :bachelor_institution, :bachelor_YearGraduated, :masterDegree, 
                :masterInstitution, :masterYearGraduated, :doctorateDegree, :doctorateInstitution, 
                :doctorateYearGraduate, :postDegree, :postInstitution, :postYearGraduate, 1, :user
            )";
            $object->execute($data);
            $success = '<div class="alert alert-success">Researcher Added</div>';
        }
        echo json_encode(array('error' => $error, 'success' => $success));
    }

    if($_POST["action"] == 'fetch_single') {
        $object->query = "SELECT * FROM tbl_researchdata WHERE id = '".$_POST["id"]."'";
        $result = $object->get_result();
        $data = array();
        foreach($result as $row) {
            $data['researcherID'] = $row['researcherID'];
            $data['familyName'] = $row['familyName'];
            $data['firstName'] = $row['firstName'];
            $data['middleName'] = $row['middleName'];
            $data['Suffix'] = $row['Suffix'];
            $data['department'] = $row['department'];
            $data['departments_units'] = $row['departments_units']; // <-- ADDED
            $data['program'] = $row['program'];
            $data['academic_rank'] = $row['academic_rank'];
            $data['bachelor_degree'] = $row['bachelor_degree'];
            $data['bachelor_institution'] = $row['bachelor_institution'];
            $data['bachelor_YearGraduated'] = $row['bachelor_YearGraduated'];
            $data['masterDegree'] = $row['masterDegree'];
            $data['masterInstitution'] = $row['masterInstitution'];
            $data['masterYearGraduated'] = $row['masterYearGraduated'];
            $data['doctorateDegree'] = $row['doctorateDegree'];
            $data['doctorateInstitution'] = $row['doctorateInstitution'];
            $data['doctorateYearGraduate'] = $row['doctorateYearGraduate'];
            $data['postDegree'] = $row['postDegree'];
            $data['postInstitution'] = $row['postInstitution'];
            $data['postYearGraduate'] = $row['postYearGraduate'];
        }
        echo json_encode($data);
    }

    if ($_POST["action"] == 'Edit') {
        $error = ''; $success = '';
        
        $dept = $_POST['departmentu'] ?? $_POST['department'] ?? '';
        $units = ($dept === 'Others') ? ($_POST['departments_units'] ?? '') : '';

        $data = array(
            ':id'                     => $_POST['hidden_id'] ?? '',
            ':researcherID'           => $_POST['researcherIDu'] ?? $_POST['researcherID'] ?? '',
            ':familyName'             => strtoupper($_POST['familyNameu'] ?? $_POST['familyName'] ?? ''),
            ':firstName'              => strtoupper($_POST['firstNameu'] ?? $_POST['firstName'] ?? ''),
            ':middleName'             => strtoupper($_POST['middleNameu'] ?? $_POST['middleName'] ?? ''),
            ':Suffix'                 => strtoupper($_POST['Suffixu'] ?? $_POST['Suffix'] ?? ''),
            ':department'             => $dept,
            ':departments_units'      => strtoupper($units), // <-- ADDED
            ':program'                => $_POST['programu'] ?? $_POST['program'] ?? '',
            ':academic_rank'          => $_POST['academic_ranku'] ?? $_POST['academic_rank'] ?? '',
            ':bachelor_degree'        => strtoupper($_POST['bachelor_degreeu'] ?? $_POST['bachelor_degree'] ?? ''),
            ':bachelor_institution'   => strtoupper($_POST['bachelor_institutionu'] ?? $_POST['bachelor_institution'] ?? ''),
            ':bachelor_YearGraduated' => strtoupper($_POST['bachelor_YearGraduatedu'] ?? $_POST['bachelor_YearGraduated'] ?? ''),
            ':masterDegree'           => strtoupper($_POST['masterDegreeu'] ?? $_POST['masterDegree'] ?? ''),
            ':masterInstitution'      => strtoupper($_POST['masterInstitutionu'] ?? $_POST['masterInstitution'] ?? ''),
            ':masterYearGraduated'    => strtoupper($_POST['masterYearGraduatedu'] ?? $_POST['masterYearGraduated'] ?? ''),
            ':doctorateDegree'        => strtoupper($_POST['doctorateDegreeu'] ?? $_POST['doctorateDegree'] ?? ''),
            ':doctorateInstitution'  => strtoupper($_POST['doctorateInstitutionu'] ?? $_POST['doctorateInstitution'] ?? ''),
            ':doctorateYearGraduate' => strtoupper($_POST['doctorateYearGraduateu'] ?? $_POST['doctorateYearGraduate'] ?? ''),
            ':postDegree'            => strtoupper($_POST['postDegreeu'] ?? $_POST['postDegree'] ?? ''),
            ':postInstitution'       => strtoupper($_POST['postInstitutionu'] ?? $_POST['postInstitution'] ?? ''),
            ':postYearGraduate'      => strtoupper($_POST['postYearGraduateu'] ?? $_POST['postYearGraduate'] ?? ''),
            ':user'                  => $object->Get_user_name($_SESSION['user_id'])
        );
        
        $object->query = "
        UPDATE tbl_researchdata 
        SET researcherID = :researcherID, familyName = :familyName, firstName = :firstName, middleName = :middleName,
            Suffix = :Suffix, department = :department, departments_units = :departments_units, program = :program, 
            academic_rank = :academic_rank, bachelor_degree = :bachelor_degree, bachelor_institution = :bachelor_institution, 
            bachelor_YearGraduated = :bachelor_YearGraduated, masterDegree = :masterDegree, masterInstitution = :masterInstitution, 
            masterYearGraduated = :masterYearGraduated, doctorateDegree = :doctorateDegree, doctorateInstitution = :doctorateInstitution, 
            doctorateYearGraduate = :doctorateYearGraduate, postDegree = :postDegree, postInstitution = :postInstitution, 
            postYearGraduate = :postYearGraduate, user = :user
        WHERE id = :id";
        
        $object->execute($data);
        $success = '<div class="alert alert-success">Researcher Data Updated</div>';
        echo json_encode(array('error' => $error, 'success' => $success));
    }

    if($_POST["action"] == 'delete') {
        $res_id = intval($_POST["id"]);
        $object->query = "UPDATE tbl_researchdata SET status = 0 WHERE id = '".$res_id."'";
        $object->execute();
        echo '<div class="alert alert-success">Researcher moved to Recycle Bin! All projects are safely preserved.</div>';
    }
}
?>