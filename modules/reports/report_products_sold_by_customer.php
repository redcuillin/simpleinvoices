<?php 
include("./include/include_main.php");

//stop the direct browsing to this file - let index.php handle which files get displayed
if (!defined("BROWSE")) {
   echo "You Cannot Access This Script Directly, Have a Nice Day.";
   exit();
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

</head>
<body>
<b>Products Sold - Group by Customer - Total</b>
<hr></hr>
<div id="container">

<?php
   // include the PHPReports classes on the PHP path! configure your path here
   include "./modules/reports/PHPReportMaker.php";
   include "config/config.php";

   $sSQL = "select  sum({$tb_prefix}invoice_items.inv_it_quantity), {$tb_prefix}customers.c_name, {$tb_prefix}products.prod_description  from  {$tb_prefix}customers, {$tb_prefix}invoice_items, {$tb_prefix}invoices, {$tb_prefix}products  where  {$tb_prefix}invoice_items.inv_it_product_id = {$tb_prefix}products.prod_id and {$tb_prefix}invoices.inv_customer_id =  {$tb_prefix}customers.c_id and {$tb_prefix}invoices.inv_id = {$tb_prefix}invoice_items.inv_it_invoice_id GROUP BY inv_it_quantity ORDER BY c_name";

   $oRpt = new PHPReportMaker();

   $oRpt->setXML("./modules/reports/xml/report_products_sold_by_customer.xml");
   $oRpt->setUser("$db_user");
   $oRpt->setPassword("$db_password");
   $oRpt->setConnection("$db_host");
   $oRpt->setDatabaseInterface("mysql");
   $oRpt->setSQL($sSQL);
   $oRpt->setDatabase("$db_name");
   $oRpt->run();

?>

<hr></hr>
<a href="./modules/documentation/info_pages/reports_xsl.html" rel="gb_page_center[450, 450]"><font color="red">Did you get an "OOOOPS, THERE'S AN ERROR HERE." error?</font></a>
</div>
<div id="footer"></div>