<?php
include('../../core/rms.php');
$object = new rms();

if(isset($_POST["action"])) {
    // FETCH FOR DATATABLE
    if($_POST["action"] == 'fetch') {
        $order_column = array('rank_name', 'rank_status');
        $output = array();
        $main_query = "SELECT * FROM tbl_academic_rank ";
        $search_query = '';
        
        if(isset($_POST["search"]["value"])) {
            $search_query .= 'WHERE rank_name LIKE "%'.$_POST["search"]["value"].'%" ';
            $search_query .= 'OR rank_status LIKE "%'.$_POST["search"]["value"].'%" ';
        }
        if(isset($_POST["order"])) {
            $order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
        } else {
            $order_query = 'ORDER BY rank_id DESC ';
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
            $sub_array[] = html_entity_decode($row["rank_name"]);
            $status = '';
            $delete_button = '';
            if($row["rank_status"] == 'Enable') {
                $delete_button = '<button type="button" name="delete_button" class="btn btn-outline-danger btn-sm delete_button_rank" data-id="'.$row["rank_id"].'" data-status="'.$row["rank_status"].'"><i class="fas fa-times"></i> Disable</button>';
                $status = '<span class="badge badge-success">Enable</span>';
            } else {
                $delete_button = '<button type="button" name="delete_button" class="btn btn-outline-success btn-sm delete_button_rank" data-id="'.$row["rank_id"].'" data-status="'.$row["rank_status"].'"><i class="fas fa-check"></i> Enable</button>';
                $status = '<span class="badge badge-danger">Disable</span>';
            }
            $sub_array[] = $status;
            $sub_array[] = '
            <div align="center">
            <button type="button" name="edit_button" class="btn btn-primary btn-sm edit_button_rank" data-id="'.$row["rank_id"].'"><i class="fas fa-edit"></i> Edit</button>
            '.$delete_button.'
            </div>';
            $data[] = $sub_array;
        }
        $output = array(
            "draw"    => intval($_POST["draw"]),
            "recordsTotal"  =>  $total_rows,
            "recordsFiltered" => $filtered_rows,
            "data"    => $data
        );
        echo json_encode($output);
    }

    // ADD RANK
    if($_POST["action"] == 'Add') {
        $error = '';
        $success = '';
        $data = array(':rank_name' => $_POST["rank_name"]);
        $object->query = "SELECT * FROM tbl_academic_rank WHERE rank_name = :rank_name";
        $object->execute($data);
        if($object->row_count() > 0) {
            $error = '<div class="alert alert-danger">Academic Rank Already Exists</div>';
        } else {
            $data = array(
                ':rank_name' => $_POST["rank_name"],
                ':rank_status' => 'Enable'
            );
            $object->query = "INSERT INTO tbl_academic_rank (rank_name, rank_status) VALUES (:rank_name, :rank_status)";
            $object->execute($data);
            $success = '<div class="alert alert-success">Academic Rank Added Successfully</div>';
        }
        $output = array('error' => $error, 'success' => $success);
        echo json_encode($output);
    }

    // FETCH SINGLE FOR EDIT
    if($_POST["action"] == 'fetch_single') {
        $object->query = "SELECT * FROM tbl_academic_rank WHERE rank_id = '".$_POST["rank_id"]."'";
        $result = $object->get_result();
        $data = array();
        foreach($result as $row) {
            $data['rank_name'] = $row['rank_name'];
        }
        echo json_encode($data);
    }

    // EDIT RANK
    if($_POST["action"] == 'Edit') {
        $error = '';
        $success = '';
        $data = array(
            ':rank_name' => $_POST["rank_name"],
            ':rank_id' => $_POST['hidden_id_rank']
        );
        $object->query = "SELECT * FROM tbl_academic_rank WHERE rank_name = :rank_name AND rank_id != :rank_id";
        $object->execute($data);
        if($object->row_count() > 0) {
            $error = '<div class="alert alert-danger">Academic Rank Already Exists</div>';
        } else {
            $data = array(
                ':rank_name' => $_POST["rank_name"],
                ':rank_id' => $_POST['hidden_id_rank']
            );
            $object->query = "UPDATE tbl_academic_rank SET rank_name = :rank_name WHERE rank_id = :rank_id";
            $object->execute($data);
            $success = '<div class="alert alert-success">Academic Rank Updated Successfully</div>';
        }
        $output = array('error' => $error, 'success' => $success);
        echo json_encode($output);
    }

    // ENABLE/DISABLE RANK
    if($_POST["action"] == 'delete') {
        $data = array(
            ':rank_status' => $_POST['next_status'],
            ':rank_id' => $_POST["id"]
        );
        $object->query = "UPDATE tbl_academic_rank SET rank_status = :rank_status WHERE rank_id = :rank_id";
        $object->execute($data);
        echo '<div class="alert alert-success">Rank Status changed to '.$_POST['next_status'].'</div>';
    }
}
?>