
<?php

//print.php

require_once('class/pdf.php');
require('vendor/vendor/autoload.php');

include('rms.php');

$object = new rms();

if(!$object->is_login())
{
    header("location:".$object->base_url."");
}

if(!$object->is_cashier_user() && !$object->is_master_user())
{
    header("location:".$object->base_url."dashboard.php");
}

$file_name = '';

	$output = '
	
	<table width="100%" border="0" cellpadding="5" cellspacing="5" style="font-family:Arial, san-sarif">';

	$object->query = "
		SELECT * FROM restaurant_table
	";

	$restaurant_data = $object->get_result();

	foreach($restaurant_data as $row)
	{
		$output .= '

<tr>
<td align="center"><img  class="fit-picture"  src="img/2.png"/></td>
</tr>
		<tr>

			<td align="center"> 


				
				<br />
				<span style="font-size:56px;"><b>'.$row["restaurant_tag_line"].'</b></span>
				<br /><br />
				<span style="font-size:48px;">'.$row["restaurant_address"].'</span>
				<br />
				<span style="font-size:48px;"><b>Contact No. - </b>'.$row["restaurant_contact_no"].'</span>
				<br />

				<span style="font-size:48px;"><b>TIN - </b>779-381-084-00000</span>


				<br /><br />

				<hr />



			
			</td>

		</tr>


		';
	}




	$object->query = "SELECT order_table.order_date as datr from order_table 
JOIN order_item_table
ON order_table.order_id=order_table.order_id
where order_table.order_date BETWEEN '01-01-2022' and '02-05-2022'
GROUP by order_table.order_date
	";

	$das = $object->get_result();

	foreach($das as $raaw)
	{
		$output .= '

		<tr>

			<td align="center"> 


				
			<tr>
							<td style="font-size:37px; text-align:center;">'.$raaw["datr"].'</td>
			

					<td align="center">
						<table width="100%" border="0" cellpadding="5" cellspacing="5">

		';


	$object->query = "
			SELECT * FROM order_item_table 
			
            JOIN order_table ON order_item_table.order_id=order_table.order_id
            
            WHERE order_table.order_date = '".$raaw["datr"]."'
			
            ORDER BY order_item_id ASC
		";

		$err = $object->get_result();

			$output .= '
			
							<tr>
					<td align="center">
						<table width="100%" border="0" cellpadding="5" cellspacing="5">
							<tr>
								<th style="font-size:37px;"  width="20%">Sr#</th>
								<th style="font-size:37px;"  width="25%">Item</th>
								<th style="font-size:37px;"  width="20%">Qty.</th>
								<th style="font-size:37px;"  width="20%">Price</th>
								<th style="font-size:37px;"  width="20%">Amount</th>
							</tr>


							';
		$count = 0;
		foreach($err as $atem)
		{
			$count++;
			$output .= '
						<tr>
							<td style="font-size:37px; text-align:center;">'.$count.'</td>
							<td style="font-size:37px;">'.$atem["product_name"].'</td>
							<td style="font-size:37px; text-align:center;">'.$atem["product_quantity"].'</td>
							<td style="font-size:37px; text-align:center;">'.$object->cur . $atem["product_rate"].'</td>
							<td style="font-size:37px; text-align:center;">'.$object->cur . $atem["product_amount"].'</td>

						</tr>

			';
		





	












	$output .= '</table>';



}
$mpdf=new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [330, 210]]);
$mpdf->AddPageByArray([
    'margin-left' => 5,
    'margin-right' => 5,
    'margin-top' => 5,
    'margin-bottom' => 5,
    'margin_header' => 5, 
    'margin_footer' => 5,
]);
$mpdf->WriteHTML($output);
$file='media/'.time().'.pdf';
$mpdf->output($file,'I');
}

?>



