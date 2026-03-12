<?php
session_start();
$con2 = mysqli_connect("localhost", "root", "", "db_cfes");
$file = array(
		'approved' => 0,
		'pending' => 0,
		'recommended' => 0,
		'forwarded' => 0,
		'returned' => 0,
		'all' => 0,
	);
if (!$_GET['region']) {
	$sql1 = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id";
         $result1 = mysqli_query($con2, $sql1);

          if (mysqli_num_rows($result1) > 0) {
            while($row = mysqli_fetch_assoc($result1)) {
              
               $file['all']= $row["A"];
            }
         } else {
            echo 0;
         }

$sql2 = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id where uploadpdf.status = 'Approved'";
         $result2 = mysqli_query($con2, $sql2);

          if (mysqli_num_rows($result2) > 0) {
            while($row = mysqli_fetch_assoc($result2)) {
              
               $file['approved']= $row["A"];
            }
         } else {
            echo 0;
         }      

    // $sql3 = 'SELECT COUNT(`id`) FROM uploadpdf WHERE`status`="Pending"';
     $sql3="SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id where uploadpdf.status = 'Pending'";
         $result3 = mysqli_query($con2, $sql3);

          if (mysqli_num_rows($result3) > 0) {
            while($row = mysqli_fetch_assoc($result3)) {
              
               $file['pending']= $row["A"];
            }
         } else {
            echo 0;
         }    


          $sql4 = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id where uploadpdf.status = 'Forwarded'";
         $result4 = mysqli_query($con2, $sql4);

          if (mysqli_num_rows($result4) > 0) {
            while($row = mysqli_fetch_assoc($result4)) {
              
               $file['forwarded']= $row["A"];
            }
         } else {
            echo 0;
         }    

   $sql5 = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id where uploadpdf.status = 'Recommended'";
         $result5 = mysqli_query($con2, $sql5);

          if (mysqli_num_rows($result5) > 0) {
            while($row = mysqli_fetch_assoc($result5)) {
              
               $file['recommended']= $row["A"];
            }
         } else {
            echo 0;
         }    




 $sql6 = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id where uploadpdf.status = 'Returned'";
         $result6 = mysqli_query($con2, $sql6);

          if (mysqli_num_rows($result6) > 0) {
            while($row = mysqli_fetch_assoc($result6)) {
              
               $file['returned']= $row["A"];
            }
         } else {
            echo 0;
         }
     } else {
     	$region = $_GET['region'];
$cluster  = $_GET['cluster'];
$sql1 = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id where school_list.region_id= '$region'";
   if ($cluster) {
      $sql1 = $sql1." AND school_list.cluster_id = '$cluster'";  
   }
         $result1 = mysqli_query($con2, $sql1);

          if (mysqli_num_rows($result1) > 0) {
            while($row = mysqli_fetch_assoc($result1)) {
              
               $file['all']= $row["A"];
            }
         } else {
            echo 0;
         }

$sql2 = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id where school_list.region_id= '$region'";
   if ($cluster) {
         $sql2 = $sql2." AND school_list.cluster_id = '$cluster'";  
      }
      $sql2 = $sql2." AND uploadpdf.status = 'Approved'";
         $result2 = mysqli_query($con2, $sql2);

          if (mysqli_num_rows($result2) > 0) {
            while($row = mysqli_fetch_assoc($result2)) {
              
               $file['approved']= $row["A"];
            }
         } else {
            echo 0;
         }      

    // $sql3 = 'SELECT COUNT(`id`) FROM uploadpdf WHERE`status`="Pending"';
     $sql3="SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id where school_list.region_id= '$region'";
        if ($cluster) {
         $sql3 = $sql3." AND school_list.cluster_id = '$cluster'";  
      }
         $sql3 = $sql3." AND uploadpdf.status = 'Pending'";
         $result3 = mysqli_query($con2, $sql3);

          if (mysqli_num_rows($result3) > 0) {
            while($row = mysqli_fetch_assoc($result3)) {
              
               $file['pending']= $row["A"];
            }
         } else {
            echo 0;
         }    


          $sql4 = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id where school_list.region_id= '$region'";
          if ($cluster) {
            $sql4 = $sql4." AND school_list.cluster_id = '$cluster'";  
         }
         $sql4 = $sql4." AND uploadpdf.status = 'Forwarded'";
         $result4 = mysqli_query($con2, $sql4);

          if (mysqli_num_rows($result4) > 0) {
            while($row = mysqli_fetch_assoc($result4)) {
              
               $file['forwarded']= $row["A"];
            }
         } else {
            echo 0;
         }    

   $sql5 = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id where school_list.region_id= '$region'";
      if ($cluster) {
         $sql5 = $sql5." AND school_list.cluster_id = '$cluster'";  
      }
         $sql5 = $sql5." AND uploadpdf.status = 'Recommended'";
         $result5 = mysqli_query($con2, $sql5);

          if (mysqli_num_rows($result5) > 0) {
            while($row = mysqli_fetch_assoc($result5)) {
              
               $file['recommended']= $row["A"];
            }
         } else {
            echo 0;
         }    




 $sql6 = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id where school_list.region_id= '$region'";
   if ($cluster) {
      $sql6 = $sql6." AND school_list.cluster_id = '$cluster'";  
   }
      $sql6 = $sql6." AND uploadpdf.status = 'Returned'";
         $result6 = mysqli_query($con2, $sql6);

          if (mysqli_num_rows($result6) > 0) {
            while($row = mysqli_fetch_assoc($result6)) {
              
               $file['returned']= $row["A"];
            }
         } else {
            echo 0;
         }

     }



 echo json_encode($file);
?>