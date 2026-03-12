<?php 
   session_start();
   require '../config.php';
   $action = $_GET['action'];
   if ($action == "loadRegion") {
      $sql = "SELECT * FROM tbl_region";
      $result = mysqli_query($link, $sql);
      $html = "<option value='0'>All Region</option>";
      if (mysqli_num_rows($result) > 0) {
         while ($row = mysqli_fetch_assoc($result)) {
            $html =  $html."<option value='".$row['region_id']."'>".$row['Region']."</option>";
         }
      }
      echo json_encode($html);
   }

   if ($action == "loadCluster") {
      $region = $_GET['region'];
      $html = "<option value='0'>All Cluster</option>";
      if ($region) {
         $sql = "SELECT * FROM tbl_cluster WHERE region_id = '$region'";
         $result = mysqli_query($link, $sql);
         if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
               $html = $html."<option value='".$row['cluster_id']."'>".$row['cluster']."</option>";
            }
         }
      }
      echo json_encode($html);
   }

   if ($action == "loadHEI") {
      $region = $_GET['region'];
      $cluster = $_GET['cluster'];
      $html = "<option value='0'>All HEI</option>";
      if ($cluster) {
         $sql = "SELECT uii, school_name FROM school_list WHERE region_id = '$region' AND cluster_id = '$cluster'";
         $result = mysqli_query($link, $sql);
         if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
               $html = $html."<option value='".$row['uii']."'>".$row['school_name']."</option>";
            }
         }
      }
      echo json_encode($html);
   }

   if ($action == "loadCharts") {
      $region = $_GET['region'];
      $cluster = $_GET['cluster'];
      $hei = $_GET['hei'];

      // chart 1
      $data = array(
         'exesum' => array(),
         'akeypeople' => array(),
         'bmatrix' => array(),
         'csupport' => array(),
         'dfaculty' => array(),
         'esystem' => array(),
         'policies' => array(),
         'health' => array(),
         'orientation' => array(),
         'mechanisms' => array(),
         'linkages' => array(),
         'moa' => array(),
         'data_responses' => 0,
         'data_percentage' => 0,
      );
      $data['exesum'] = queryCharts($region, $cluster, $hei, 'exesum', $link);
      $data['akeypeople'] = queryCharts($region, $cluster, $hei, 'akeypeople', $link);
      $data['bmatrix'] = queryCharts($region, $cluster, $hei, 'bmatrix', $link);
      $data['csupport'] = queryCharts($region, $cluster, $hei, 'csupport', $link);
      $data['dfaculty'] = queryCharts($region, $cluster, $hei, 'dfaculty', $link);
      $data['esystem'] = queryCharts($region, $cluster, $hei, 'esystem', $link);
      $data['policies'] = queryCharts($region, $cluster, $hei, 'policies', $link);
      $data['health'] = queryCharts($region, $cluster, $hei, 'health', $link);
      $data['orientation'] = queryCharts($region, $cluster, $hei, 'orientation', $link);
      $data['mechanisms'] = queryCharts($region, $cluster, $hei, 'mechanisms', $link);
      $data['linkages'] = queryCharts($region, $cluster, $hei, 'linkages', $link);
      $data['moa'] = queryCharts($region, $cluster, $hei, 'moa', $link);

      $region_name = $hei_name = "";
      if ($region) {
         $region_name_sql = "SELECT Region AS region FROM tbl_region WHERE region_id = '$region'";
         $result = mysqli_query($link, $region_name_sql);
         $result = mysqli_fetch_assoc($result);
         $region_name = $result['region']; 
      }
      if ($hei) {
         $hei_name_sql = "SELECT school_name FROM school_list WHERE uii = '$hei'";
         $result = mysqli_query($link, $hei_name_sql);
         $result = mysqli_fetch_assoc($result);
         $hei_name = $result['school_name'];
      }

      $sql = "SELECT COUNT('school_list.school_name') AS count, SUM(tbl_lcpevaluation.total) AS total FROM tbl_lcpevaluation JOIN tbl_region ON tbl_lcpevaluation.region = tbl_region.Region JOIN school_list ON tbl_lcpevaluation.heiname = school_list.school_name";
      if ($region) {
         $sql = $sql." AND tbl_lcpevaluation.region = '$region_name'";
      }
      if ($cluster) {
         $sql = $sql." AND school_list.cluster_id = '$cluster'";
      }
      if ($hei) {
         $sql = $sql." AND school_list.school_name = '$hei_name'";
      }
      $result = mysqli_query($link, $sql);
      $result = mysqli_fetch_assoc($result);
      $data['data_responses'] = $result['count'];
      if ($result['total']) {
         $data['data_percentage'] = round(($result['total']/($result['count'] * 72))*100, 2);
      } else {
         $data['data_percentage'] = 0;
      }
      echo json_encode($data);
   } 

   function queryCharts ($region, $cluster, $hei, $column, $link) {
      $region_name = $hei_name = "";
      if ($region) {
         $region_name_sql = "SELECT Region AS region FROM tbl_region WHERE region_id = '$region'";
         $result = mysqli_query($link, $region_name_sql);
         $result = mysqli_fetch_assoc($result);
         $region_name = $result['region']; 
      }
      if ($hei) {
         $hei_name_sql = "SELECT school_name FROM school_list WHERE uii = '$hei'";
         $result = mysqli_query($link, $hei_name_sql);
         $result = mysqli_fetch_assoc($result);
         $hei_name = $result['school_name'];
      }

      $data = array(
         'rate1' => 0,
         'rate2' => 0,
         'rate3' => 0,
         'rate4' => 0,
         'rate5' => 0,
         'rate6' => 0,
      );

      for ($i = 1; $i <= 6; $i++) {
         $sql = "SELECT COUNT('school_list.school_name') AS count FROM tbl_lcpevaluation JOIN tbl_region ON tbl_lcpevaluation.region = tbl_region.Region JOIN school_list ON tbl_lcpevaluation.heiname = school_list.school_name WHERE ".$column." = ".$i;
         if ($region) {
            $sql = $sql." AND tbl_lcpevaluation.region = '$region_name'";
         }
         if ($cluster) {
            $sql = $sql." AND school_list.cluster_id = '$cluster'";
         }
         if ($hei) {
            $sql = $sql." AND school_list.school_name = '$hei_name'";
         }
         $result = mysqli_query($link, $sql);
         if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
               $data['rate'.$i] = $row['count'];
            }
         }
      }
      return $data;
   }
?>