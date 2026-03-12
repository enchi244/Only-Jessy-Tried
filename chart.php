<?php

$conn = mysqli_connect("localhost", "root", "", "rms");













// SQL query to fetch department and program count
$departmentCountQuery = "SELECT department, COUNT(department) AS programcount 
                         FROM `tbl_researchdata` 
                         WHERE `status` = 1 
                         GROUP BY department 
                         ORDER BY department";

// Execute the query
$resultDepartmentCount = mysqli_query($conn, $departmentCountQuery);

// Prepare data for frontend
$departments = [];
if (mysqli_num_rows($resultDepartmentCount) > 0) {
    while ($row = mysqli_fetch_assoc($resultDepartmentCount)) {
        $departments[] = [
            'department' => $row['department'],
            'programcount' => $row['programcount']
        ];
	}
} else {
   echo "0 results";
}

?>