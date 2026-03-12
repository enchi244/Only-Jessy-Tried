<?php  
 
include('rms.php');
$d;



$object = new rms();
 $connect = mysqli_connect("localhost", "root", "", "rms");  
 if(isset($_POST["user_name"]))  
 {  
      $query = "  
      SELECT * FROM user_table  
      WHERE user_name = '".$_POST["user_name"]."'  
      AND user_password = '".$_POST["user_password"]."'
      ";  
      $result = mysqli_query($connect, $query);  
     

      foreach($result as $row)
          { 

           if($row['user_type'] == "Master"){
 
    
$object->query = "
          DELETE FROM order_item_table 
          WHERE order_id = '".$_POST["orderID"]."' 
          AND order_item_id = '".$_POST["orderID"]."'
          ";

          $object->execute();


$object->query = "
          DELETE FROM tbl_void 
          WHERE order_id = '".$_POST["orderID"]."' 
          ";

          $object->execute();




          $object->query = "
          DELETE FROM order_table 
          WHERE order_id = '".$_POST['orderID']."'
          ";

          $object->execute();

echo 'Order Deleted Successfully...';





//echo $row['user_type'];

 }elseif($row['user_type'] == "Manager"){
//echo $row['user_type'];
    
$order_data = array(
          
               ':order_status'          =>   3
          );

          $object->query = "

               UPDATE order_table 
          SET order_status = :order_status
          WHERE order_id = '".$_POST["orderID"]."'
          ";
          $object->execute($order_data);
         echo 'Order Void Successfully...';
     

     $void_data = array(
                    ':order_id'                   =>   $_POST['orderID'],
                    ':Voided_byID'   =>   $row['user_id'],
                    ':void_date'        =>   date('m-d-Y'),
                    ':void_time'   =>   date('h:i:s a')

               
               );

               $object->query = "
               INSERT INTO tbl_void 
               (order_id, Voided_byID, void_date,void_time) 
               VALUES (:order_id, :Voided_byID, :void_date, :void_time)
               ";

               $object->execute($void_data);


   
}
     






}
}else{
echo "false";

}

      
  
 ?>  