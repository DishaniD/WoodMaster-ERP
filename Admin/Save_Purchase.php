<?php include('header.php');  include('Wooden_Aura.php'); ?>
 
 <?php 
// start code to add selected materialuct to customer list
if(isset($_POST['AddTolist'])){
$qty = $_POST['qty']; 
$material_name = $_POST['material_name'];


//get selected materialuct's price
$readAction = mysqli_query($Wooden_AuraConnection,"SELECT * FROM material WHERE material_id='$material_name'")or die(mysqli_error($Wooden_AuraConnection));
$row=mysqli_fetch_array($readAction);

$price=$row['material_price'];
		
//check material already in list
    $query1 = mysqli_query($Wooden_AuraConnection,"select * from purchase_hold where material_id='$material_name'")or die(mysqli_error($Wooden_AuraConnection));
    $count=mysqli_fetch_array($query1);
      
// get total ammount for selected materialuct
		$total=$price*$qty;

		if ($count>0){
      // if same materialuct found update it
			mysqli_query($Wooden_AuraConnection,"update purchase_hold set qty=qty+'$qty',price=price+'$total' where material_id='$material_name'  ")or die(mysqli_error());
	
		}
		else{
      // if materialuct is new save it
			mysqli_query($Wooden_AuraConnection,"INSERT INTO purchase_hold(material_id,qty,price) VALUES('$material_name','$qty','$price')")or die(mysqli_error($Wooden_AuraConnection));
		}

    echo '<script type="text/javascript">
    swal("Added!", "  Successfully Added  " , "success");
      </script>';
 
 
 
} // end code to add selected materialuct to customer Invoice
?>
                                

<?php   // start code to final save from list  
if(isset($_POST['purchaseDone'])){
	 	$total = $_POST['total']; // get main variables to save into first table
	 $material_supplier = $_POST['material_supplier']; 
	date_default_timezone_set("Asia/colombo"); 
	$date = date("Y-m-d H:i:s");
  
	mysqli_query($Wooden_AuraConnection,"INSERT INTO purchase(total,date_added,supplier)  
	VALUES('$total','$date','$material_supplier')")or die(mysqli_error($Wooden_AuraConnection)); // save to first table
			 
	$Request_id=mysqli_insert_id($Wooden_AuraConnection); // genarate forgin key to save into second table

	$query=mysqli_query($Wooden_AuraConnection,"select * from purchase_hold ")or die(mysqli_error($Wooden_AuraConnection));
		while ($row=mysqli_fetch_array($query)) // select all material from Invoice to save into second table with forgin key
		{
			$material_id=$row['material_id'];	
 			$qty=$row['qty'];
			$price=$row['price'];
			
			// save into second table
			mysqli_query($Wooden_AuraConnection,"INSERT INTO purchaseetails(material_id,qty,price,Requested_id) VALUES('$material_id','$qty','$price','$Request_id')")or die(mysqli_error($Wooden_AuraConnection));



      // update materialuct qty (+)
			mysqli_query($Wooden_AuraConnection,"UPDATE material SET material_qty=material_qty+'$qty' where material_id='$material_id' ") or die(mysqli_error($Wooden_AuraConnection)); 
		}
		//clear  Invoice
		$result=mysqli_query($Wooden_AuraConnection,"DELETE FROM purchase_hold")	or die(mysqli_error($Wooden_AuraConnection));
 
	//	echo "<script>document.location='Customer_Invocie.php?Gotid=$Request_id'</script>";  	
	
	    echo '<script type="text/javascript">
    swal("Added!", " Purchase   Successfully Added  " , "success");
      </script>';

        // Save into payment_hold table
       // Genarade Serial Code   
       $serial = $_POST['serial'];  
  $readAction = mysqli_query($Wooden_AuraConnection,"SELECT serial FROM payment_backup WHERE serial='$serial'")or die(mysqli_error($Wooden_AuraConnection));
  $readAction=mysqli_num_rows($readAction);  
  if ($readAction > 0)
  {
    echo "<script> serial = " . json_encode($serial) . "</script>";
    echo '<script type="text/javascript">
    swal("Cant be Saved!", " " + serial + "  Serial  Found" , "warning");
      </script>';
	  }
    else
	{
    

    mysqli_query($Wooden_AuraConnection,"INSERT INTO payment_hold(serial,qty,total,requested_id,supplier,date) VALUES('$serial','$qty','$total','$Request_id','$material_supplier','$date')")or die(mysqli_error($Wooden_AuraConnection));
    mysqli_query($Wooden_AuraConnection,"INSERT INTO payment_backup(serial,qty,total,requested_id,supplier,date) VALUES('$serial','$qty','$total','$Request_id','$material_supplier','$date')")or die(mysqli_error($Wooden_AuraConnection));
    
    
	  }
	  
	  
		
}

$query=mysqli_query($Wooden_AuraConnection,"select * from payment_backup  order by serial DESC LIMIT 1")or die(mysqli_error()); 
$result =mysqli_num_rows ($query);
if ($result == 0) {
  $newidNew = "INV00001";
} else {
 $rec= mysqli_fetch_assoc($query);
  $lastid = $rec["serial"];
  $num = substr($lastid, 3);
  $num++;
  $newidNew = "INV" . str_pad($num, 5, "0", STR_PAD_LEFT);


}
?>

 

    <main class="app-content">
   

      <div class="app-title">
        <div>
        <h1 >  <a class="fa fa-home" href="Dashborad.php"> </a>  </h1>
       
        </div>
        <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"> </li>
          <li class="breadcrumb-item"><a > purchase       </a></li>
        </ul>
      </div>



      <div class="row">
 <div class="col-md-3">
          <div class="tile">
          
            <div class="tile-body">
              <form class="row"  method="post" enctype='multipart/form-data'>
                <div class="form-group col-md-11">
                  <label class="control-label"> Select Material    </label>
                  <select class="form-control"  id="demoSelect"  name="material_name"  required>
               <option selected disabled> Select</option> 
               <?php

$query2=mysqli_query($Wooden_AuraConnection,"select * from material  ")or die(mysqli_error());
   while($row=mysqli_fetch_array($query2)){

?>	
<option value="<?php echo $row['material_id'];?>"> <?php echo $row['material_name']." Available(".$row['material_qty'].")";?></option>
<?php }?>
              </select>
                </div>

                <div class="form-group col-md-12">
                  <label class="control-label">  Qty  </label>
                  <input class="form-control" type="number"   name="qty" min="1" required>
                </div>
                </div>
          
              <button class="btn btn-primary" type="submit" name="AddTolist"> +  </button>
           
            

            
            </form>
 
   </div>   </div>
  
  

   
 <div class="col-md-9">
          <div class="tile">
            <div class="tile-body">
            <table class="table table-hover table-bordered" >
                <thead>
                  <tr>
                  <th>    </th>
                  <th>Material </th>
						            <th>Qty</th>
                        <th>Price </th>
                        <th>Total </th>
					 
				
                    
                  </tr>
                </thead>
                <tbody>
                <?php
	 		
   $query=mysqli_query($Wooden_AuraConnection,"select * from purchase_hold left join material on purchase_hold.material_id = material.material_id ")or die(mysqli_error());
   while($row=mysqli_fetch_array($query)){
   
?>
                  <tr>
           
                  <td>

<a href="Delete_PurchaseList.php?id=<?php echo $row['purchase_hold_id']; ?>" 
onclick="return confirm('Are you sure to Delete?')"  class="btn btn-danger btn-sm"  ><i class="fa fa-lg fa-trash"></i></a> 
</td>

                  <td><?php echo $row['material_name'];?></td>
                        <td><?php echo $row['qty'];?></td>
                        <td><?php echo $row['price'];?></td>
                       
                        <td><?php echo  $row['qty'] * $row['price'];?>.00</td>
                       
                       
                     



                </tr>
                  </tr>

                  <?php } ?>             
                </tbody>
              </table>
              <form method="post"  >         
              
     
          
  
    
  <!--Start Auto calcuation -->
 

<script type="text/javascript"> 
   function setval2()
   {
      document.getElementById('balance').value = document.getElementById('total').value - document.getElementById('paid').value;
   }
 </script>
  <!--End Auto calcuation -->
  
                  <?php 
                        $query=mysqli_query($Wooden_AuraConnection,"select *  from purchase_hold")or die(mysqli_error());
              $grand=0;
              while($row=mysqli_fetch_array($query)){ 
              $total= $row['qty']*$row['price'];
              $grand=$grand+$total;
            }
                        ?>  
                 <label class="control-label"> Payment  Code</label>
                  <input class="form-control" type="text"  name="serial" value="<?php echo($newidNew)?>" readonly >
              
   Total      <input size="15" type="text" style="text-align:right" class="form-control" id="total" name="total"  
                  value="<?php echo "$grand"; ?>"     readonly>   </a>
 
               

                  
      
       
               

     
                  <label class="control-label">Supplier    </label>
                  <select class="form-control"   name="material_supplier"  id="demoSelect" required>
               <option selected disabled> Select</option> 
                <?php
            
              $queryc=mysqli_query($Wooden_AuraConnection,"select * from material_supplier  ")or die(mysqli_error());
                while($rowc=mysqli_fetch_array($queryc)){
                ?>
                  <option value="<?php echo $rowc['supplier_id'];?>"><?php echo $rowc['supplier_name'];?></option>
                <?php }?>
              </select>
               
	  
   


                 
  <br/>  <br/>
               
  <P>
   
  <button class="btn btn btn-block btn-danger"   name="purchaseDone" type="submit"   >
    <i class="fa fa-fw fa-lg fa-check-circle"></i> Done       </button>   
                      </div>
  
  
  
                
                  <form>

</div></div></div>


        </div>

 
      </div>
    </main>

    <?php include('footer.php');?>