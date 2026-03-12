<?php
namespace Phppot;
require_once '../config.php';
use Phppot\CountryStatesc;

    $data = array(
        'html' => "",
        'count' => 0,
    );
    $country_id = $_POST["country_id"];
    if (! empty($country_id)) {
        
        $countryId = $_POST["country_id"];
        require_once __DIR__ . '/../Model/CountryStatesc.php';
        $countryState = new CountryState();
        $stateResult = $countryState->getStateByCountrId($countryId);

        $sql = "SELECT COUNT('cluster') AS count FROM tbl_cluster WHERE region_id = $country_id";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($result);
        $data['count'] = $row['count'];
        $data['html'] = $data['html'].'<option value="" disabled>Select User Role</option><option value="0">All Cluster</option>';
        foreach ($stateResult as $state) {
            $data['html'] = $data['html'].'<option value='.$state["cluster_id"].'>'.$state["cluster"].'</option>';
        }
    } else {
        $data['html'] = $data['html'].'<option value="0">All Cluster</option>';
    }

    echo json_encode($data);
?>