
<?php


require_once('class/pdf.php');
require('vendor/vendor/autoload.php');

include('core/rms.php');

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

                               // $con1 = mysqli_connect("localhost","root","","rms");

                        

                                if(isset($_POST['from_date']) && isset($_POST['to_date']))
                                {
                                    $from_date = $_POST['from_date'];
                                    $to_date = $_POST['to_date'];
                                    
// Creating timestamp from given date
$timestamp = strtotime($from_date);
 
// Creating new date format from that timestamp
$from_date = date("m-d-Y", $timestamp);
echo $from_date; // Outputs: 31-03-2019

$timestamp1 = strtotime($to_date);
 
// Creating new date format from that timestamp
$to_date = date("m-d-Y", $timestamp1);
//echo $to_date; // Outputs: 31-03-2019
$t=date("h:i a");

$object->query = "
		select sum(order_item_table.`product_quantity`) as qty FROM `order_item_table` JOIN order_table ON order_item_table.order_id=order_table.order_id where order_table.payment='cash' and order_table.order_date between '$from_date' and '$to_date' ";

	$r = $object->get_result();

	foreach($r as $rw)
	{
if($rw["qty"]==0){
$qty="0.00";

}else{
$qty=$rw["qty"];
}	
}
$object->query = "
		select sum(`order_net_amount`) as cash FROM `order_table` where payment='cash' and order_date between '$from_date' and '$to_date'";

	$aa = $object->get_result();

	foreach($aa as $raoow)
	{
if($raoow["cash"]==0){
$cash="0.00";

}else{
$cash=$raoow["cash"];
}	
}
$object->query = "
		select sum(`order_gross_amount`) as gross, min(`order_number`) as min,max(`order_number`) as max FROM `order_table` where order_date='$from_date'";

	$daf = $object->get_result();

	foreach($daf as $roow)
	{
if($roow["gross"]==0){
$ttt="0.00";

}else{
$ttt=$roow["gross"];
}	
	}


     }
                                   
                           

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
				<span style="font-size:56px;">'.$row["restaurant_tag_line"].'</span>
				<br /><br />
				<span style="font-size:48px;">'.$row["restaurant_address"].'</span>
				<br />
				<span style="font-size:48px;"><b>Contact No. - </b>'.$row["restaurant_contact_no"].'</span>
				<br />

				<span style="font-size:48px;"><b>TIN - </b>769-976-098-000</span>


				<br /><br />

				<hr />



			
			</td>

		</tr>


		';
	}

	

	




	$object->query = "select sum(`order_gross_amount`) as gross,sum(`exc`) as exc,sum(`regular`) as regular,sum(`gcash`) as gcash,sum(`credit`) as credit,sum(`debit`) as debit, sum(`discount`) as discount,sum(`order_net_amount`) as net, min(`order_number`) as min,max(`order_number`) as max FROM `order_table` where order_date between '$from_date' and '$to_date';
		
	";

	$order_result = $object->get_result();

	foreach($order_result as $order)
	{


 $tott=$order["credit"]+$order["debit"]+$order["gcash"]+$cash; 

		$file_name = $order["order_number"].".pdf";
		$output .= '
		<tr>
					<td  align="center" style="font-size:45px;" width="20%"><b>Z READING</b></td>

					<hr>
					</tr>
		<tr>

			<td align="center">
				<table width="100%" border="0" cellpadding="5" cellspacing="5">


					<tr >

					<tr> <th style="font-size:40px;" colspan="3" align="left">Date from: <td style="font-size:40px; " colspan="3" align="right">'.$from_date.'</tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Date to: <td style="font-size:40px;" colspan="3" align="right">'.$to_date.'</tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Report Time: <td style="font-size:40px;" colspan="3" align="right">'.$t.'</tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Register #: <td style="font-size:40px;" colspan="3" align="right">POS-0001</tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Location #: <td style="font-size:40px;" colspan="3" align="right">Guiwan, Zamboanga City</tr>
					
					<tr><th colspan="8"><br/><hr width="100%"></th><tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Beginning SI #: <td style="font-size:40px;" colspan="3" align="right">'.$order["min"].'</tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Ending SI #: <td style="font-size:40px;" colspan="3" align="right">'.$order["max"].'</tr>





					<tr><th colspan="8"><br/><hr width="100%"></th><tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Beginning: <td style="font-size:40px;" colspan="3" align="right">'.$ttt.'</tr>


					<tr> <th style="font-size:40px;" colspan="3" align="left">Ending: <td style="font-size:40px;" colspan="3" align="right">'.$order["gross"].'</tr>

					<tr><th colspan="8"><br/><hr width="100%"></th><tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">GROSS Sales: <td style="font-size:40px;" colspan="3" align="right">'.$order["gross"].'</tr>
					<tr> <th style="font-size:40px;" colspan="3" align="left">Discount Sales: <td style="font-size:40px;" colspan="3" align="right">'.$order["discount"].'</tr>

					
					<tr> <th style="font-size:40px;" colspan="3" align="left">Exempt Sales: <td style="font-size:40px;" colspan="3" align="right">'.$order["exc"].'</tr>
					<tr> <th style="font-size:40px;" colspan="3" align="left">Regular Sales: <td style="font-size:40px;" colspan="3" align="right">'.$order["regular"].'</tr>
					<tr> <th style="font-size:40px;" colspan="3" align="left">Net Sales: <td style="font-size:40px;" colspan="3" align="right">'.$order["net"].'</tr>

					<tr><th colspan="8"><br/><hr width="100%"></th><tr>

					
					<tr> <th style="font-size:40px;" colspan="3" align="left">TOTAL CREDIT: <td style="font-size:40px;" colspan="3" align="right">'.$order["credit"].'</tr>
					<tr> <th style="font-size:40px;" colspan="3" align="left">TOTAL DEBIT: <td style="font-size:40px;" colspan="3" align="right">'.$order["debit"].'</tr>
					<tr> <th style="font-size:40px;" colspan="3" align="left">TOTAL Gcash: <td style="font-size:40px;" colspan="3" align="right">'.$order["gcash"].'</tr>
					<tr> <th style="font-size:40px;" colspan="3" align="left">TOTAL Cash: <td style="font-size:40px;" colspan="3" align="right">'.$cash.'</tr>
					<tr> <th style="font-size:40px;" colspan="3" align="left">GRAND TOTAL: <td style="font-size:40px;" colspan="3" align="right">'.$tott.'</tr>
					<tr> <th style="font-size:40px;" colspan="3" align="left">Total Qty Sold: <td style="font-size:40px;" colspan="3" align="right">'.$qty.'</tr>
					<tr><th colspan="8"><br/><hr width="100%"></th><tr>
					<tr><th colspan="8" style="font-size:35px; font-weight:bold;">KAMSAHAMNIDA!</th><tr>
					</tr>';





$object->query = "select order_table.order_date as datee FROM `order_item_table` JOIN order_table ON order_item_table.order_id=order_table.order_id where  order_table.order_date between '$from_date' and '$to_date'
			GROUP BY order_table.order_date 
			ORDER BY order_table.order_date ASC
		
	";

	$order_d = $object->get_result();

	foreach($order_d as $od)
	{

$tg=$od["datee"]; 

//		$file_name = $order["order_number"].".pdf";
		$output .= '
		<tr>
					<td  align="center" style="font-size:45px;" width="20%"><b>'.$od["datee"].'</b></td>

					<hr>
					</tr>';





		



					$object->query = "select order_item_table.`product_name` as product,order_item_table.order_id as idd, order_table.order_date as datee FROM `order_item_table` JOIN order_table ON order_item_table.order_id=order_table.order_id where  order_table.order_date ='$$tg'";

	$order_result = $object->get_result();

	foreach($order_z as $oz)
	{

$output .= '

		<tr>

			<td align="center">
				<table width="100%" border="0" cellpadding="5" cellspacing="5">


					<tr >
<tr> <th style="font-size:40px;" colspan="3" align="left">Report Time: <td style="font-size:40px;" colspan="3" align="right">'.$oz["idd"].'</tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Date from: <td style="font-size:40px; " colspan="3" align="right">'.$oz["idd"].'</tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Date to: <td style="font-size:40px;" colspan="3" align="right">'.$oz["product"].'</tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Report Time: <td style="font-size:40px;" colspan="3" align="right">'.$oz["datee"].'</tr>




					';





}


	$output .= '</table>
	</tr>		';
}



 $tott=$order["credit"]+$order["debit"]+$order["gcash"]+$cash; 

		$file_name = $order["order_number"].".pdf";
		$output .= '
		<tr>
					<td  align="center" style="font-size:45px;" width="20%"><b>Z READING</b></td>

					<hr>
					</tr>
		<tr>

			<td align="center">
				<table width="100%" border="0" cellpadding="5" cellspacing="5">


					<tr >

					<tr> <th style="font-size:40px;" colspan="3" align="left">Date from: <td style="font-size:40px; " colspan="3" align="right">'.$from_date.'</tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Date to: <td style="font-size:40px;" colspan="3" align="right">'.$to_date.'</tr>

					<tr> <th style="font-size:40px;" colspan="3" align="left">Report Time: <td style="font-size:40px;" colspan="3" align="right">'.$t.'</tr>












					
				


				
				</table>
		</tr>
		'; 




		$object->query = "
			SELECT * FROM order_item_table 
			WHERE order_id = '3' 
			ORDER BY order_item_id ASC
		";

		$\05 = $object->get_result();

		$output .= '
			<tr>
				<td align="center">
					<table width="100%" border="0" cellpadding="5" cellspacing="5">
						<tr>
							<th style="font-size:37px; color:white;"  width="20%">Sr#</th>
							<th style="font-size:37px; color:white;"  width="25%">Item</th>
							<th style="font-size:37px; color:white;"  width="20%">Qty.</th>
							<th style="font-size:37px; color:white;"  width="20%">Price</th>
							<th style="font-size:37px; color:white;"  width="20%">Amount</th>
						</tr>

						';
		$count = 0;
		foreach($order_item_result as $item)
		{
			$count++;
			$output .= '
					

			';
		}

		

		

		$output .= '
						<tr >
							
							

						</tr>
		
				</td>
			</tr>
			<tr>
				<td align="center" style="font-size:35px; ">'.$object->Get_user_name($_SESSION['user_id']).'</td>
				<tr><th colspan="8"><br/><hr width="50%"></th><tr>
			</tr>	
				
			<tr>
				<td align="center" style="font-size:35px;">Prepared By</td>
			</tr>

			<tr><th colspan="8"><br/><hr width="50%"></th><tr>

			<tr>
				<td align="center" style="font-size:35px;">Signed By</td>
			</tr>
			';


}
	





	$output .= '</table>';

$mpdf=new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [76, 297]]);
$mpdf->AddPageByArray([
    'margin-left' => 6,
    'margin-right' => 6,
    'margin-top' => 6,
    'margin-bottom' => 6,
    'margin_header' => 6, 
    'margin_footer' => 6,
]);
$mpdf->WriteHTML($output);
$file='media/'.time().'.pdf';
$mpdf->output($file,'I');



?>



