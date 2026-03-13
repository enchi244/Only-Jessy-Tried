<?php

// Include necessary file
include('../core/rms.php');

// Instantiate the rms object to maintain any session/auth checks intact
$object = new rms();

// Check if the action is set
if (isset($_POST["action"])) {
    
    // Establish a secure, dedicated connection for fetching data
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli("localhost", "root", "", "rms");
        $conn->set_charset("utf8mb4");
    } catch (Exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        echo json_encode(array("Database connection failed."));
        exit;
    }

    // 1. Original Action (Preserved functionality, upgraded to strict prepared statements)
    if ($_POST["action"] == 'fetch_sub_department_data') {
        $department = $_POST["department"];
        $subDepartments = array();

        $query = "SELECT CONCAT(familyName, ', ', firstName, ' ', middleName, ' ', Suffix) AS name 
                  FROM tbl_researchdata 
                  WHERE department = ? AND status = 1";
        
        try {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $department);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                // Mitigating XSS vulnerabilities by escaping output
                $subDepartments[] = htmlspecialchars(trim($row['name']), ENT_QUOTES, 'UTF-8');
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Query error: " . $e->getMessage());
        }
        
        $conn->close();
        echo json_encode($subDepartments);
        exit;
    }

    // 2. New Dynamic Action for Contextual Chart Data
    if ($_POST["action"] == 'fetch_modal_details') {
        $department = $_POST["department"];
        $type = $_POST["type"];
        $dataList = array();

        $query = "";
        
        // Define exact queries per requested context based on rms.sql schemas
        if ($type === 'researchers') {
            $query = "SELECT CONCAT(familyName, ', ', firstName, ' ', middleName, ' ', Suffix) AS title 
                      FROM tbl_researchdata 
                      WHERE department = ? AND status = 1";
        } elseif ($type === 'research_conducted') {
            $query = "SELECT tbl_researchconducted.title 
                      FROM tbl_researchconducted 
                      JOIN tbl_researchdata ON tbl_researchconducted.researcherID = tbl_researchdata.id 
                      WHERE tbl_researchdata.department = ?";
        } elseif ($type === 'publications') {
            $query = "SELECT tbl_publication.title 
                      FROM tbl_publication 
                      JOIN tbl_researchdata ON tbl_publication.researcherID = tbl_researchdata.id 
                      WHERE tbl_researchdata.department = ?";
        } elseif ($type === 'ip') {
            $query = "SELECT tbl_itelectualprop.title 
                      FROM tbl_itelectualprop 
                      JOIN tbl_researchdata ON tbl_itelectualprop.researcherID = tbl_researchdata.id 
                      WHERE tbl_researchdata.department = ?";
        } elseif ($type === 'paper_presentation') {
            $query = "SELECT tbl_paperpresentation.title 
                      FROM tbl_paperpresentation 
                      JOIN tbl_researchdata ON tbl_paperpresentation.researcherID = tbl_researchdata.id 
                      WHERE tbl_researchdata.department = ?";
        } elseif ($type === 'trainings') {
            $query = "SELECT tbl_trainingsattended.title 
                      FROM tbl_trainingsattended 
                      JOIN tbl_researchdata ON tbl_trainingsattended.researcherID = tbl_researchdata.id 
                      WHERE tbl_researchdata.department = ?";
        } elseif ($type === 'extension') {
            $query = "SELECT tbl_extension_project_conducted.title 
                      FROM tbl_extension_project_conducted 
                      JOIN tbl_researchdata ON tbl_extension_project_conducted.researcherID = tbl_researchdata.id 
                      WHERE tbl_researchdata.department = ?";
        }

        if ($query !== "") {
            try {
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $department);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    if (!empty(trim($row['title']))) {
                        // Anti-XSS escaping
                        $dataList[] = htmlspecialchars(trim($row['title']), ENT_QUOTES, 'UTF-8');
                    }
                }
                $stmt->close();
            } catch (Exception $e) {
                error_log("Query error: " . $e->getMessage());
            }
        }
        
        $conn->close();
        echo json_encode($dataList);
        exit;
    }
}
?>