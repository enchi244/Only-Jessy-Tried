<?php
	session_start();
	require "../config.php";
	$action = $_GET['action'];

	if ($action == "evaluate") {
		$form = $_GET['form'];
		$region = $_SESSION['Region'];
		$acadyear = $_GET['acadyear'];
		$semester = $_GET['semester'];
		$fileID = $_GET['fileID'];
		$uii = $_GET['uii'];
		$hei_name = "SELECT school_name FROM school_list WHERE uii = '$uii'";
		$hei_name = mysqli_query($link, $hei_name);
		$hei_name = mysqli_fetch_assoc($hei_name);
		$hei_name = $hei_name['school_name'];
		$total = (int)$form[0]['value'] + (int)$form[1]['value'] + (int)$form[2]['value'] + (int)$form[3]['value'] + (int)$form[4]['value'] + (int)$form[5]['value'] + (int)$form[6]['value'] + (int)$form[7]['value'] + (int)$form[8]['value'] + (int)$form[9]['value'] + (int)$form[10]['value'] + (int)$form[11]['value'];
		$hei = "SELECT total FROM tbl_lcpevaluation WHERE heiname = '$hei_name'";
		$hei = mysqli_query($link, $hei);
		if (mysqli_num_rows($hei) > 0) {
			$hei = mysqli_fetch_assoc($hei);
			$hei = round(($hei['total']/72)*100, 2);
		} else {
			$hei = 0;
		}
		$ched = round(($total/72)*100, 2);
		$exesum = $form[0]['value'];
		$akeypeople = $form[1]['value'];
		$bmatrix = $form[2]['value'];
		$csupport = $form[3]['value'];
		$dfaculty = $form[4]['value'];
		$esystem = $form[5]['value'];
		$policies = $form[6]['value'];
		$health = $form[7]['value'];
		$orientation = $form[8]['value'];
		$mechanisms = $form[9]['value'];
		$linkages = $form[10]['value'];
		$moa = $form[11]['value'];
		$sql = "INSERT INTO tbl_chedlcpevaluation (region, heiname, exesum, akeypeople, bmatrix, csupport, dfaculty, esystem, policies, health, orientation, mechanisms, linkages, moa, acadyear, semester, ched_evaluation_result, hei_evaluation_result, total) VALUES ('$region', '$hei_name', '$exesum', '$akeypeople', '$bmatrix', '$csupport', '$dfaculty', '$esystem', '$policies', '$health', '$orientation', '$mechanisms', '$linkages', '$moa', '$acadyear', '$semester', '$ched', '$hei', '$total')";
		$test = mysqli_query($link, $sql);

		echo json_encode('success');
	}
?>