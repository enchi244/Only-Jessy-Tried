<?php 
	session_start();
	include '../config.php';
	$action = $_GET['action'];

	if ($action == "loadRegionSelect") {
		$html = "<option value='0'>All Region</option>";
		$sql = "SELECT region_id AS ID, Region FROM tbl_region";
		$result = mysqli_query($link, $sql);
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$html = $html.'<option value="'.$row['ID'].'">'.$row['Region'].'</option>';
			}
		}
		echo json_encode($html);
	}

	if ($action == "loadClusterSelect") {
		$region = $_GET['region'];
		$html = "<option value='0'>All Cluster</option>";
		$sql = "SELECT cluster_id AS ID, cluster FROM tbl_cluster WHERE region_id = $region";
		$result = mysqli_query($link, $sql);
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$html = $html.'<option value="'.$row['ID'].'">'.$row['cluster'].'</option>';
			}
		}
		echo json_encode($html);
	}

	if ($action == "loadFilesUploaded") {
		$region = $_GET['region'];
		$cluster = $_GET['cluster'];
		$data = array (
			'approved' => 0,
			'pending' => 0,
			'recommended' => 0,
			'forwarded' => 0,
			'returned' => 0,
		);
		$approved = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id"; 
		if ($region) {
			$approved = $approved." WHERE school_list.region_id= '$region'";
		}
		if ($cluster) {
			$approved = $approved." AND school_list.cluster_id = '$cluster'";  
		}
		$approved = $approved." AND uploadpdf.status = 'Approved'";

		$approved = mysqli_query($link, $approved);
		$approved = mysqli_fetch_assoc($approved);
		$data['approved'] = $approved['A'];

		$pending = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id"; 
		if ($region) {
			$pending = $pending." WHERE school_list.region_id= '$region'";
		}
		if ($cluster) {
			$pending = $pending." AND school_list.cluster_id = '$cluster'";  
		}
		$pending = $pending." AND uploadpdf.status = 'Pending'";

		$pending = mysqli_query($link, $pending);
		$pending = mysqli_fetch_assoc($pending);
		$data['pending'] = $pending['A'];

		$recommended = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id"; 
		if ($region) {
			$recommended = $recommended." WHERE school_list.region_id= '$region'";
		}
		if ($cluster) {
			$recommended = $recommended." AND school_list.cluster_id = '$cluster'";  
		}
		$recommended = $recommended." AND uploadpdf.status = 'Recommended'";

		$recommended = mysqli_query($link, $recommended);
		$recommended = mysqli_fetch_assoc($recommended);
		$data['recommended'] = $recommended['A'];

		$forwarded = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id"; 
		if ($region) {
			$forwarded = $forwarded." WHERE school_list.region_id= '$region'";
		}
		if ($cluster) {
			$forwarded = $forwarded." AND school_list.cluster_id = '$cluster'";  
		}
		$forwarded = $forwarded." AND uploadpdf.status = 'Forwarded'";

		$forwarded = mysqli_query($link, $forwarded);
		$forwarded = mysqli_fetch_assoc($forwarded);
		$data['forwarded'] = $forwarded['A'];

		$returned = "SELECT COUNT(*) as A FROM uploadpdf JOIN school_list ON uploadpdf.addedby=school_list.account_id JOIN tbl_region ON school_list.region_id=tbl_region.region_id"; 
		if ($region) {
			$returned = $returned." WHERE school_list.region_id= '$region'";
		}
		if ($cluster) {
			$returned = $returned." AND school_list.cluster_id = '$cluster'";  
		}
		$returned = $returned." AND uploadpdf.status = 'Returned'";

		$returned = mysqli_query($link, $returned);
		$returned = mysqli_fetch_assoc($returned);
		$data['returned'] = $returned['A'];

		






		echo json_encode($data);
	}

	if ($action == "loadFileType") {
		$region = $_GET['region'];
		$cluster = $_GET['cluster'];
		$data = array (
			'courseware' => 0,
			'evaluation' => 0,
			'lcp' => 0,
			'letter' => 0,
			'modules' => 0,
			'syllabus' => 0,
			'permit' => 0,
		);
		// Courseware 3
		// evaluation 7
		// LCP 5 
		// letter 1
		// modules 4
		// syllabus 2
		// permit 6

		$courseware = "SELECT COUNT('uploadpdf.id') AS ID FROM uploadpdf JOIN school_list ON uploadpdf.addedby = school_list.account_id JOIN tbl_region ON school_list.region_id = tbl_region.region_id JOIN tbl_cluster ON school_list.cluster_id=tbl_cluster.cluster_id WHERE uploadpdf.documentype = 3";
		if ($region) {
			$courseware = $courseware." AND school_list.region_id = $region";
		}
		if ($cluster) {
			$courseware = $courseware." AND school_list.cluster_id = $cluster";
		}
		$courseware = mysqli_query($link, $courseware);
		$courseware = mysqli_fetch_assoc($courseware);
		$data['courseware'] = $courseware['ID'];

		$evaluation = "SELECT COUNT('uploadpdf.id') AS ID FROM uploadpdf JOIN school_list ON uploadpdf.addedby = school_list.account_id JOIN tbl_region ON school_list.region_id = tbl_region.region_id JOIN tbl_cluster ON school_list.cluster_id=tbl_cluster.cluster_id WHERE uploadpdf.documentype = 7";
		if ($region) {
			$evaluation = $evaluation." AND school_list.region_id = $region";
		}
		if ($cluster) {
			$evaluation = $evaluation." AND school_list.cluster_id = $cluster";
		}
		$evaluation = mysqli_query($link, $evaluation);
		$evaluation = mysqli_fetch_assoc($evaluation);
		$data['evaluation'] = $evaluation['ID'];

		$lcp = "SELECT COUNT('uploadpdf.id') AS ID FROM uploadpdf JOIN school_list ON uploadpdf.addedby = school_list.account_id JOIN tbl_region ON school_list.region_id = tbl_region.region_id JOIN tbl_cluster ON school_list.cluster_id=tbl_cluster.cluster_id WHERE uploadpdf.documentype = 5";
		if ($region) {
			$lcp = $lcp." AND school_list.region_id = $region";
		}
		if ($cluster) {
			$lcp = $lcp." AND school_list.cluster_id = $cluster";
		}
		$lcp = mysqli_query($link, $lcp);
		$lcp = mysqli_fetch_assoc($lcp);
		$data['lcp'] = $lcp['ID'];

		$letter = "SELECT COUNT('uploadpdf.id') AS ID FROM uploadpdf JOIN school_list ON uploadpdf.addedby = school_list.account_id JOIN tbl_region ON school_list.region_id = tbl_region.region_id JOIN tbl_cluster ON school_list.cluster_id=tbl_cluster.cluster_id WHERE uploadpdf.documentype = 1";
		if ($region) {
			$letter = $letter." AND school_list.region_id = $region";
		}
		if ($cluster) {
			$letter = $letter." AND school_list.cluster_id = $cluster";
		}
		$letter = mysqli_query($link, $letter);
		$letter = mysqli_fetch_assoc($letter);
		$data['letter'] = $letter['ID'];

		$modules = "SELECT COUNT('uploadpdf.id') AS ID FROM uploadpdf JOIN school_list ON uploadpdf.addedby = school_list.account_id JOIN tbl_region ON school_list.region_id = tbl_region.region_id JOIN tbl_cluster ON school_list.cluster_id=tbl_cluster.cluster_id WHERE uploadpdf.documentype = 4";
		if ($region) {
			$modules = $modules." AND school_list.region_id = $region";
		}
		if ($cluster) {
			$modules = $modules." AND school_list.cluster_id = $cluster";
		}
		$modules = mysqli_query($link, $modules);
		$modules = mysqli_fetch_assoc($modules);
		$data['modules'] = $modules['ID'];

		$syllabus = "SELECT COUNT('uploadpdf.id') AS ID FROM uploadpdf JOIN school_list ON uploadpdf.addedby = school_list.account_id JOIN tbl_region ON school_list.region_id = tbl_region.region_id JOIN tbl_cluster ON school_list.cluster_id=tbl_cluster.cluster_id WHERE uploadpdf.documentype = 2";
		if ($region) {
			$syllabus = $syllabus." AND school_list.region_id = $region";
		}
		if ($cluster) {
			$syllabus = $syllabus." AND school_list.cluster_id = $cluster";
		}
		$syllabus = mysqli_query($link, $syllabus);
		$syllabus = mysqli_fetch_assoc($syllabus);
		$data['syllabus'] = $syllabus['ID'];

		$permit = "SELECT COUNT('uploadpdf.id') AS ID FROM uploadpdf JOIN school_list ON uploadpdf.addedby = school_list.account_id JOIN tbl_region ON school_list.region_id = tbl_region.region_id JOIN tbl_cluster ON school_list.cluster_id=tbl_cluster.cluster_id WHERE uploadpdf.documentype = 6";
		if ($region) {
			$permit = $permit." AND school_list.region_id = $region";
		}
		if ($cluster) {
			$permit = $permit." AND school_list.cluster_id = $cluster";
		}
		$permit = mysqli_query($link, $permit);
		$permit = mysqli_fetch_assoc($permit);
		$data['permit'] = $permit['ID'];

		echo json_encode($data);
	}

	if ($action == "loadSystemUsers") {
		// variable acc is for total HEIs
		$data = array (
			'acc' => 0,
			'priv' => 0,
			'suc' => 0,
			'luc' => 0,
			'acc' => 0,
			'supp' => 0,
			'user' => 0,
			'acc_percentage' => 0,
			'priv_percentage' => 0,
			'suc_percentage' => 0,
			'luc_percentage' => 0,
			'acc_percentage' => 0,
			'supp_percentage' => 0,
			'user_percentage' => 0,
		);
		$region = $_GET['region'];
		$cluster = $_GET['cluster'];

		$acc = "SELECT  COUNT(NULLIF(`account_id`,0)) as count_id  FROM school_list"; 
		if ($region) {
			$acc = $acc." WHERE region_id= $region";
		}
		if ($cluster) {
			$acc = $acc." AND cluster_id = $cluster";  
		}
		$acc = mysqli_query($link, $acc);
		$acc = mysqli_fetch_assoc($acc);
		$data['acc'] = $acc['count_id'];
		$acc_percentage = "SELECT  COUNT(NULLIF(`account_id`,0)) as count_id  FROM school_list";
		$acc_percentage = mysqli_query($link, $acc_percentage);
		$acc_percentage = mysqli_fetch_assoc($acc_percentage);
		if (!$acc['count_id']) {
			$data['acc_percentage'] = 0;
		} else {
			$data['acc_percentage'] = round(($acc['count_id']/$acc_percentage['count_id'])*100);
		}
		$priv = "SELECT count(*) as c FROM `school_list` where type='Private'"; 
		if ($region) {
			$priv = $priv." and region_id= '$region'";
		}
		if ($cluster) {
			$priv = $priv." AND cluster_id = '$cluster'";  
		}
		//$pending = $pending." AND uploadpdf.status = 'Pending'";

		$priv = mysqli_query($link, $priv);
		$priv = mysqli_fetch_assoc($priv);
		$data['priv'] = $priv['c'];
		$priv_percentage = "SELECT count(*) as c FROM `school_list` where type='Private'";
		$priv_percentage = mysqli_query($link, $priv_percentage);
		$priv_percentage = mysqli_fetch_assoc($priv_percentage);
		if (!$priv['c']) {
			$data['priv_percentage'] = 0;
		} else {
			$data['priv_percentage'] = round(($priv['c']/$priv_percentage['c'])*100);
		}

		$suc = "SELECT count(*) as cq FROM `school_list` where type='SUC'"; 
		if ($region) {
			$suc = $suc." and region_id= $region";
		}
		if ($cluster) {
			$suc = $suc." AND cluster_id = $cluster";  
		}
		//$pending = $pending." AND uploadpdf.status = 'Pending'";

		$suc = mysqli_query($link, $suc);
		$suc = mysqli_fetch_assoc($suc);
		$data['suc'] = $suc['cq'];
		$suc_percentage = "SELECT count(*) as cq FROM `school_list` where type='SUC'";
		$suc_percentage = mysqli_query($link, $suc_percentage);
		$suc_percentage = mysqli_fetch_assoc($suc_percentage);
		if (!$suc['cq']) {
			$data['suc_percentage'] = 0;
		} else {
			$data['suc_percentage'] = round(($suc['cq']/$suc_percentage['cq'])*100);
		}

		$luc = "SELECT count(*) as cq1 FROM `school_list` where type='LUC'"; 
		if ($region) {
			$luc = $luc." and region_id= $region";
		}
		if ($cluster) {
			$luc = $luc." AND cluster_id = $cluster";  
		}
		//$pending = $pending." AND uploadpdf.status = 'Pending'";

		$luc = mysqli_query($link, $luc);
		$luc = mysqli_fetch_assoc($luc);
		$data['luc'] = $luc['cq1'];
		$luc_percentage = "SELECT count(*) as cq1 FROM `school_list` where type='LUC'";
		$luc_percentage = mysqli_query($link, $luc_percentage);
		$luc_percentage = mysqli_fetch_assoc($luc_percentage);
		if (!$luc['cq1']) {
			$data['luc_percentage'] = 0;
		} else {
			$data['luc_percentage'] = round(($luc['cq1']/$luc_percentage['cq1'])*100);
		}
		$supp = "SELECT count(*) as rolle FROM `users` WHERE `user_role` = 5"; 
		if ($region) {
			$supp = $supp." AND region_id= '$region'";
		}
		// if ($cluster) {
		//	$supp = $supp." AND cluster_id = '$cluster'";  
		//}
		//$pending = $pending." AND uploadpdf.status = 'Pending'";

		$supp = mysqli_query($link, $supp);
		$supp = mysqli_fetch_assoc($supp);
		$data['supp'] = $supp['rolle'];
		$supp_percentage = "SELECT count(*) as rolle FROM `users` WHERE `user_role`=5";
		$supp_percentage = mysqli_query($link, $supp_percentage);
		$supp_percentage = mysqli_fetch_assoc($supp_percentage);
		if (!$supp['rolle']) {
			$data['supp_percentage'] = 0;
		} else {
			$data['supp_percentage'] = round(($supp['rolle']/$supp_percentage['rolle'])*100);
		}

		$user = "SELECT COUNT('id') AS ID FROM users";
		if ($region) {
			$user = $user." WHERE region_id = '$region'";
		}
		$user = mysqli_query($link, $user);
		$user = mysqli_fetch_assoc($user);
		$data['user'] = $user['ID'];
		$user_percentage = "SELECT COUNT('id') AS ID FROM users";
		$user_percentage = mysqli_query($link, $user_percentage);
		$user_percentage = mysqli_fetch_assoc($user_percentage);
		if (!$user['ID']) {
			$data['user_percentage'] = 0;
		} else {
			$data['user_percentage'] = round(($user['ID']/$user_percentage['ID'])*100);
		}
		echo json_encode($data);
	}
?>