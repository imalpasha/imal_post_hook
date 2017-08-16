<?php

extract($GLOBALS);
 
//SELECT ADMIN EMAIL AND USERNAME (Combine Later)
	
	$sqlEmail = "SELECT settingvalue as Email FROM core_settings WHERE settingname = 'general_from_email' ";
	$stmt1 = $db2->prepare($sqlEmail);
	$stmt1->execute();
	$row = $stmt1->fetch();
	{
		$superemail = $row['Email'];
	}
	$sqlName = "SELECT settingvalue as Name FROM core_settings WHERE settingname = 'general_from_name' ";
	$stmt2 = $db2->prepare($sqlName);
	$stmt2->execute();
	$row = $stmt2->fetch();
	{
		$supername = $row['Name'];
	}
	
	$sqlG = "SELECT settingvalue as GdexEmail FROM core_settings WHERE settingname = 'gdex_email_setting' ";
	$stmt3 = $db2->prepare($sqlG);
	$stmt3->execute();
	$row = $stmt3->fetch();
	{
		$gdexemail = $row['GdexEmail'];
	}
	$subject = "JAM-BU Order Pickup";


//SELECT DISTINCT ID & COUNT ROW	
	
	$sql = "SELECT DISTINCT e.shop_id,e.user_id,e.order_status,e.email_id, f.shop_id FROM jambu_shop_order e JOIN jambu_shop f ON e.order_status = 'Order Ready To Pickup' AND e.email_id = '0' AND e.shop_id = f.shop_id ORDER BY e.shop_id DESC";

$stmt = $db2->prepare($sql);
$stmt->execute();
$cRowLoop = $stmt->rowCount();
//if($cRowLoop = 0){exit;}

$i = 0;
while($row = $stmt->fetch())
	{	
		$shop_id[$i] = $row['shop_id'];
		$user_id[$i] = $row['user_id'];
		$i++;
	}

//SELECT PACKAGE & ADDRESS
$text = "";
$reply = "";
$no = 1;
for ($z=0; $z<$cRowLoop;$z++)
{
/*
$sql = "SELECT e.delivery_address,e.shop_id,f.shop_id,f.shop_address,e.date_purchased FROM jambu_shop_order e JOIN jambu_shop ON order_status = 'Order Ready To Pickup' AND e.shop_id = '$shop_id[$z]' AND e.shop_id = f.shop_id ORDER BY e.date_purchased DESC";
*/
$sql = "	SELECT a.shop_address, a.shop_id, b.delivery_address, b.date_purchased, b.shop_id, b.email_id,
			b.order_status, b.order_no
			FROM jambu_shop_order b
			JOIN jambu_shop a ON b.order_status = 'Order Ready To Pickup'
			AND b.shop_id = '$shop_id[$z]'
			AND a.shop_id = b.shop_id
			AND b.email_id = '0'
			ORDER BY a.shop_id DESC";
			
$stmt = $db2->prepare($sql);
$stmt->execute();
//$loop = $stmt->rowCount();



$table_header = '<table border="0" cellpadding="2" cellspacing="1" width="100%" ><tr style="background-color:#ddd">';
$table_header .= '<b><td>No.</td><td>Seller Address</td><td>Buyer Address</td><td>Package Quantity</td></b></tr>';

$row = $stmt->fetch();

$shop_address = $row['shop_address'];
$delivery_address = $row['delivery_address'];
	
$text .= "<tr><td>$no</td><td>$shop_address</td><td>$delivery_address</td></tr>";
		
$s++;
$no++;

}

$table_footer = "</table>";
$reply2 = "$table_header"."$text"."$table_footer";

//INSERT DATA TO MAIL-OUTGOING BASED ON EMAIL


	$sql2 = "INSERT INTO core_mailoutgoing( sendername, senderemail, emailto, subject, ishtml, html, text, datetime, sent, created )
			VALUES(	'$supername', '$superemail', '$gdexemail', '$subject','1','$reply2','', NOW(), 'N', NOW() )";

$stmt = $db2->prepare($sql2);
$stmt->execute();	
$email_id = $db2->lastInsertId();		

//Insert Email ID at Jambu_shop_order

for($s=0;$s<$cRowLoop;$s++)
{
	$sql =  "UPDATE jambu_shop_order SET email_id='$email_id' WHERE shop_id = '$shop_id[$s]' AND order_status = 'Order Ready To Pickup'";
	$stmt = $db2->prepare($sql);
	$stmt->execute();	
}


echo "$sqlEmail<br>";
echo "$gdexemail<br>";
echo "$superemail<br>";
echo "$supername<br>";
echo "$cRowLoop<br>";
echo "$sql<br><br><br>";
echo "$reply2<br><br>";

/*
for($a=0;$a<$cRowLoop;$a++)
{
echo "$shop_address[$a]<br>";
echo "$delivery_address[$a]<br>";
echo "$shop_id[$a]<br>";

}

*/
?>