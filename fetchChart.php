<?php

//category_action.php

include('rms.php');

$object = new rms();
function Get_department_list() {
    // SQL query to fetch department count
    $query = "
        SELECT tbl_researchdata.department AS department, COUNT(tbl_researchdata.department) AS countt
        FROM `tbl_researchconducted`
        JOIN tbl_researchdata ON tbl_researchconducted.researcherID = tbl_researchdata.id
        GROUP BY tbl_researchdata.department
    ";

    // Execute query and fetch results
    $result = $this->conn->query($query);

    $departments = [];
    
    // Fetch results and format them
    while ($row = $result->fetch_assoc()) {
        if (!is_null($row["countt"])) {
            $departments[] = [
                'department' => $row['department'],
                'countt' => number_format($row['countt'])
            ];
        } else {
            $departments[] = [
                'department' => 'Unknown',
                'countt' => '0'
            ];
        }
    }
    
    // Return the data as a JSON-encoded string
    return json_encode($departments);
}




?>