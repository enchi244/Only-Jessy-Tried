
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

if(isset($_GET["order_id"]))
{
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

				<span style="font-size:48px;"><b>TIN - </b>779-381-084-00000</span><br /><br>
				<span style="font-size:60px;"><b>BILL</b></span>


				<br /><br />

				<hr />



			
			</td>

		</tr>


		';
	}

	$object->query = "
		SELECT * FROM order_table 
		WHERE order_id = '".$_GET["order_id"]."'
	";

	$order_result = $object->get_result();

	foreach($order_result as $order)
	{
		$file_name = $order["order_number"].".pdf";
		$output .= '
		<tr>
					<td  align="center" style="font-size:50px;" width="20%">'.$order["order_date"].' '.$order["order_time"].' OR# '.$order["order_number"].'</td>

					<hr>
					</tr>
		<tr>

			<td align="center">
				<table width="100%" border="0" cellpadding="5" cellspacing="5">


					<tr>

						

						<td style="font-size:40px;" width="20%"><b>Table #: </b>'.$order["order_table"].'</td>
						

					</tr>
				</table>
			</td>
		</tr>
		'; 



		$object->query = "
			SELECT * FROM order_item_table 
			WHERE order_id = '".$_GET["order_id"]."' 
			ORDER BY order_item_id ASC
		";

		$order_item_result = $object->get_result();

		$output .= '
			<tr>
				<td align="center">
					<table width="100%" border="0" cellpadding="5" cellspacing="5">
						<tr>
							<th style="font-size:37px;"  width="20%">Sr#</th>
							<th style="font-size:37px;"  width="25%">Item</th>
							<th style="font-size:37px;"  width="20%">Price</th>
							<th style="font-size:37px;"  width="20%">Qty.</th>
							
							<th style="font-size:37px;"  width="20%">Amount</th>
						</tr>

						';
		$count = 0;
		foreach($order_item_result as $item)
		{
			$count++;
			$output .= '
						<tr>
							<td style="font-size:37px; text-align:center;">'.$count.'</td>
							<td style="font-size:37px;">'.$item["product_name"].'</td>
							<td style="font-size:37px; text-align:center;">'.$object->cur .number_format($item["product_rate"],2).'</td>
							<td style="font-size:37px; text-align:center;">'.$item["product_quantity"].'</td>
							
							<td style="font-size:37px; text-align:center;">'.$object->cur .number_format($item["product_amount"],2).'</td>

						</tr>

			';
		}

		$object->query = "
		SELECT * FROM order_tax_table 
		WHERE order_id = '".$_GET["order_id"]."'
		";

		$tax_result = $object->execute();

		$total_tax_row = $object->row_count();

		$rowspan = 2 + $total_tax_row;

		$tax_result = $object->statement_result();

		$output .= '

			<tr>
			<th colspan="8"><br/><hr width="100%"></th>
			
			</br>

			';

			$output .= '

			<tr> <th style="font-size:40px;" colspan="3" align="left">Total Sale: <td style="font-size:40px; " colspan="3" align="right">'.$object->cur . number_format($order["order_gross_amount"],2).'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Total Due: <td style="font-size:40px; " colspan="3" align="right">'.$object->cur . number_format($order["order_net_amount"],2).'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Regular: <td style="font-size:40px; " colspan="3" align="right">'.$object->cur . number_format($order["regular"],2).'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Exempt: <td style="font-size:40px; " colspan="3" align="right">'.$object->cur . number_format($order["exc"],2).'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">SC/PWD Discount: <td style="font-size:40px; " colspan="3" align="right">'.$object->cur . number_format($order["discount"],2).'</tr>



			

	
		';
if($order["payment"]=="Flexi"){

// 		$payable=$row["credit"]+$order["debit"]+$order["gcash_amount"]+$order["cash_tender"];
	
// $tot_gross_pay=$net_total-$payable;
// 	 $changeee=($tot_gross_pay*(-1));



		$output .= '

			<tr> <th style="font-size:40px;" colspan="3" align="left">Cash Tender: <td style="font-size:40px; " colspan="3" align="right">'.$object->cur . number_format($order["cash_tender"],2).'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Gcash: <td style="font-size:40px; " colspan="3" align="right">'.$object->cur . number_format($order["gcash_amount"],2).'</tr>
					<tr> <th style="font-size:40px;" colspan="3" align="left">Gcash Reference #: <td style="font-size:40px; " colspan="3" align="right">'.$order["gcash"].'</tr>
			<tr> <th style="font-size:40px;" colspan="3" align="left">Change: <td style="font-size:40px; " colspan="3" align="right">'.$object->cur . number_format($_SESSION['changeee'],2).'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Payment: <td style="font-size:40px; " colspan="3" align="right">'. $order["payment"].'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Cashier: <td style="font-size:40px; " colspan="3" align="right">'.$order["order_cashier"].'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Waiter: <td style="font-size:40px; " colspan="3" align="right">'.$order["order_waiter"].'</tr>

			


			<tr>

			


		';
}elseif($order["payment"]=="Gcash"){

		$output .= '

		

			<tr> <th style="font-size:40px;" colspan="3" align="left">Gcash: <td style="font-size:40px; " colspan="3" align="right">'.$object->cur . number_format($order["gcash_amount"],2).'</tr>
					<tr> <th style="font-size:40px;" colspan="3" align="left">Gcash Reference #: <td style="font-size:40px; " colspan="3" align="right">'.$order["gcash"].'</tr>
		
			<tr> <th style="font-size:40px;" colspan="3" align="left">Payment: <td style="font-size:40px; " colspan="3" align="right">'. $order["payment"].'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Cashier: <td style="font-size:40px; " colspan="3" align="right">'.$order["order_cashier"].'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Waiter: <td style="font-size:40px; " colspan="3" align="right">'.$order["order_waiter"].'</tr>

			


			<tr>

			


		';
}





else{

$output .= '

			<tr> <th style="font-size:40px;" colspan="3" align="left">Cash Tender: <td style="font-size:40px; " colspan="3" align="right">'.$object->cur . number_format($order["cash_tender"],2).'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Change: <td style="font-size:40px; " colspan="3" align="right">'.$object->cur . number_format($order["customer_change"],2).'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Payment: <td style="font-size:40px; " colspan="3" align="right">'. $order["payment"].'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Cashier: <td style="font-size:40px; " colspan="3" align="right">'.$order["order_cashier"].'</tr>

			<tr> <th style="font-size:40px;" colspan="3" align="left">Waiter: <td style="font-size:40px; " colspan="3" align="right">'.$order["order_waiter"].'</tr>

			


			<tr>

			


		';



}


		

		$output .= '
						<tr >
							
							

						</tr>
					</table>
				</td>
			</tr>
			
				
			<hr/>
			<tr>
				<td align="center" style="font-size:35px; font-weight:bold;">KAMSAHAMNIDA!</td>
			</tr>
			';
	}



	$output .= '</table>';

$mpdf=new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [76, 297]]);
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



