<?php
// modules/researchers/actions/archive_action.php
include('../../../core/rms.php');
$object = new rms();

if(isset($_POST["action"])) {
    
    // Validate the target table to prevent SQL Injection
    $allowed_tables = ['tbl_researchdata', 'tbl_researchconducted', 'tbl_publication', 'tbl_itelectualprop', 'tbl_paperpresentation', 'tbl_extension_project_conducted', 'tbl_trainingsattended'];
    $target_table = $_POST['target_module'];
    if (!in_array($target_table, $allowed_tables)) { exit; }

    // FETCH TRASH BIN DATA (status = 0)
    if($_POST["action"] == 'fetch_all') {
        
        $main_query = "SELECT * FROM {$target_table} WHERE status = 0 ";
        
        if(isset($_POST["search"]["value"])) {
            $search = $_POST["search"]["value"];
            if ($target_table === 'tbl_researchdata') {
                $main_query .= "AND (firstName LIKE '%".$search."%' OR familyName LIKE '%".$search."%') ";
            } else {
                $main_query .= "AND (title LIKE '%".$search."%') ";
            }
        }
        
        $main_query .= "ORDER BY id DESC ";
        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'] : "";
        
        $object->query = $main_query . $limit_query;
        $result = $object->get_result();
        
        $object->query = $main_query;
        $object->execute();
        $total_rows = $object->row_count();

        $data = array();
        foreach($result as $row) {
            $sub_array = array();
            
            // Format labels cleanly based on the table
            if ($target_table === 'tbl_researchdata') {
                $sub_array[] = "<span class='badge badge-primary'>Researcher Profile</span>";
                $sub_array[] = "<strong>" . $row["firstName"] . " " . $row["familyName"] . "</strong><br><small class='text-muted'>Dept: " . $row["department"] . "</small>";
            } else {
                $clean_module_name = ucwords(str_replace(['tbl_', '_project_conducted', 'itelectualprop'], ['', '', 'Intellectual Property'], $target_table));
                $sub_array[] = "<span class='badge badge-info'>{$clean_module_name}</span>";
                $sub_array[] = "<strong>" . ($row["title"] ?? 'Untitled') . "</strong>";
            }
            
            $sub_array[] = '
            <div class="text-center">
                <button type="button" class="btn btn-success btn-sm restore_btn" data-id="'.$row["id"].'" title="Restore"><i class="fas fa-undo"></i> Restore</button>
                <button type="button" class="btn btn-danger btn-sm perma_delete_btn" data-id="'.$row["id"].'" title="Permanent Delete"><i class="fas fa-times-circle"></i></button>
            </div>';
            $data[] = $sub_array;
        }

        echo json_encode(array("draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $total_rows, "data" => $data));
    }

    // RESTORE LOGIC (Back to status = 1)
    if($_POST["action"] == 'restore') {
        $object->query = "UPDATE {$target_table} SET status = 1 WHERE id = '".$_POST["id"]."'";
        $object->execute();
        echo "Success";
    }

    // PERMANENT DELETE LOGIC 
    if($_POST["action"] == 'perma_delete') {
        $record_id = intval($_POST["id"]);

        // If deleting a researcher profile, clean up junction tables too
        if ($target_table === 'tbl_researchdata') {
            $object->query = "DELETE FROM tbl_research_collaborators WHERE researcher_id = '".$record_id."'";
            $object->execute();
            $object->query = "DELETE FROM tbl_publication_collaborators WHERE researcher_id = '".$record_id."'";
            $object->execute();
            $object->query = "DELETE FROM tbl_paper_collaborators WHERE researcher_id = '".$record_id."'";
            $object->execute();
        }

        $object->query = "DELETE FROM {$target_table} WHERE id = '".$record_id."'";
        $object->execute();

        echo "Success";
    }
}
?>