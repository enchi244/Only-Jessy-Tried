<?php

//category_action.php

include('rms.php');

$object = new rms();
 function Get_sub_department_data($department)
{
    // Query to fetch the sub-department details based on the department passed
    $this->query = "SELECT CONCAT(`familyName`, ', ', `firstName`, ' ', `middleName`, ' ', `Suffix`) as name,
               COUNT(*) AS count
        FROM `tbl_researchdata`
        WHERE `department` = '".$department."' AND `status` = 1
        GROUP BY `familyName`, `firstName`, `middleName`, `Suffix`
    ";

    // Execute the query and fetch results
    $result = $this->get_result(); // Assuming get_result() fetches the result of the query

    $subDepartments = [];

    // Loop through the result set and check if 'count' is null
    foreach ($result as $row) {
        // Check if 'count' is null, and set it to 0 if so
        if (!is_null($row["count"])) {
            $subDepartments[] = [
                'subDepartment' => $row['name'],
                'count' => $row['count']
            ];
        } else {
            // If count is null, set it to '0'
            $subDepartments[] = [
                'subDepartment' => $row['name'],
                'count' => 0 // Return 0 for null counts
            ];
        }
    }

    // Return the data as JSON for JavaScript to use
    return json_encode($subDepartments);
}




?>