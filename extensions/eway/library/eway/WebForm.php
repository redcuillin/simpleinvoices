<?php
use Inc\Claz\Util;

global $LANG;

require_once('EwayPaymentLive.php');
if (isset($_POST['btnProcess'])){

  $txtFirstName = $_POST['txtFirstName'];
  $txtLastName = $_POST['txtLastName'];
  $txtEmail = $_POST['txtEmail'];
  $txtAddress = $_POST['txtAddress'];
  $txtPostcode = $_POST['txtPostcode'];
  $txtTxnNumber = $_POST['txtTxnNumber'];
  $txtInvDesc = $_POST['txtInvDesc'];
  $txtInvRef = $_POST['txtInvRef'];
  $txtOption1 = $_POST['txtOption1'];
  $txtOption2 = $_POST['txtOption2'];
  $txtOption3 = $_POST['txtOption3'];
  $txtCCNumber = $_POST['txtCCNumber'];
  $ddlExpiryMonth = $_POST['ddlExpiryMonth'];
  $ddlExpiryYear = $_POST['ddlExpiryYear'];
  $txtCCName = $_POST['txtCCName'];
  $txtAmount = $_POST['txtAmount'];

  // Set the payment details
  $eway = new Eway($ewayCustomerID, $ewayPaymentMethod, $ewayUseLive);

  $eway->setTransactionData("TotalAmount", $txtAmount); //mandatory field
  $eway->setTransactionData("CustomerFirstName", $txtFirstName);
  $eway->setTransactionData("CustomerLastName", $txtLastName);
  $eway->setTransactionData("CustomerEmail", $txtEmail);
  $eway->setTransactionData("CustomerAddress", $txtAddress);
  $eway->setTransactionData("CustomerPostcode", $txtPostcode);
  $eway->setTransactionData("CustomerInvoiceDescription", $txtInvDesc);
  $eway->setTransactionData("CustomerInvoiceRef", $txtInvRef);
  $eway->setTransactionData("CardHoldersName", $txtCCName); //mandatory field
  $eway->setTransactionData("CardNumber", $txtCCNumber); //mandatory field
  $eway->setTransactionData("CardExpiryMonth", $ddlExpiryMonth); //mandatory field
  $eway->setTransactionData("CardExpiryYear", $ddlExpiryYear); //mandatory field
  $eway->setTransactionData("TrxnNumber", "");
  $eway->setTransactionData("Option1", $txtOption1);
  $eway->setTransactionData("Option2", $txtOption2);
  $eway->setTransactionData("Option3", $txtOption3);

  $eway->setCurlPreferences(CURLOPT_SSL_VERIFYPEER, 0); // Require for Windows hosting

  // Send the transaction
  $ewayResponseFields = $eway->doPayment();

  if(strtolower($ewayResponseFields["EWAYTRXNSTATUS"])=="false")
  {
      print "Transaction Error: " . $ewayResponseFields["EWAYTRXNERROR"] . "<br>\n";
      foreach($ewayResponseFields as $key => $value)
          print "\n<br>\${$LANG['ewayResponseFields']}[\"$key\"] = $value";
  }
  else if(strtolower($ewayResponseFields["EWAYTRXNSTATUS"])=="true")
  {
       // payment successfully sent to gateway
       // Payment succeeded get values returned
       $lblResult = " Result: " . $ewayResponseFields["EWAYTRXNSTATUS"] . "<br>";
       $lblResult .= " AuthCode: " . $ewayResponseFields["EWAYAUTHCODE"] . "<br>";
       $lblResult .= " Error: " . $ewayResponseFields["EWAYTRXNERROR"] . "<br>";
       $lblResult .= " eWAYInvoiceRef: " . $ewayResponseFields["EWAYTRXNREFERENCE"] . "<br>";
       $lblResult .= " Amount: " . $ewayResponseFields["EWAYRETURNAMOUNT"] . "<br>";
       $lblResult .= " Txn Number: " . $ewayResponseFields["EWAYTRXNNUMBER"] . "<br>";
       $lblResult .= " Option1: " . $ewayResponseFields["EWAYOPTION1"] . "<br>";
       $lblResult .= " Option2: " . $ewayResponseFields["EWAYOPTION2"] . "<br>";
       $lblResult .= " Option3: " . $ewayResponseFields["EWAYOPTION3"] . "<br>";
       echo $lblResult;
  }
  else
  {
       // invalid response received from server.
       $lblResult =  "Error: An invalid response was received from the payment gateway.";
       echo $lblResult;
  }

}
else {
?>
<html>
<head><title>eWAY PHP Example</title></head>
<body>
<form id="Form1" method="post" action="<?php echo Util::htmlSafe($_SERVER['PHP_SELF']); ?>" ENCTYPE="multipart/form-data">
  <div id="pnlBeforeProcess" style="height:328px;width:488px;">&nbsp; * Fields in Red are required
    <table id=Table1 style="WIDTH: 352px; HEIGHT: 264px;border-collapse: collapse; border-spacing: 0;padding:0;">
      <tr>
        <td bgColor=#000000 colSpan=2>
          <FONT color=#ffffff><STRONG>Process Transaction</STRONG></FONT>
        </td>
      </tr>
      <tr>
        <td bgColor=gainsboro>
          <span id="Label1">First Name:</span>
        </td>
        <td bgColor=gainsboro>
          <input name="txtFirstName" type="text" id="txtFirstName" />
        </td>
      </tr>
      <tr>
        <td bgColor=gainsboro>
          <span id="Label5">Last Name:</span>
        </td>
        <td bgColor=gainsboro>
          <input name="txtLastName" type="text" id="txtLastName" />
        </td>
      </tr>
      <tr>
        <td bgColor=gainsboro>
          <span id="Label7">Email Address:</span>
        </td>
        <td bgColor=gainsboro>
          <input name="txtEmail" type="text" id="txtEmail" />
        </td>
      </tr>
      <tr>
        <td bgColor=gainsboro>
          <span id="Label11">Address:</span>
        </td>
        <td bgColor=gainsboro>
          <input name="txtAddress" type="text" id="txtAddress" />
        </td>
      </tr>
      <tr>
        <td bgColor=gainsboro>
          <span id="Label12">Postcode:</span>
        </td>
        <td bgColor=gainsboro>
          <input name="txtPostcode" type="text" id="txtPostcode" />
        </td>
      </tr>
      <tr>
        <td bgColor=gainsboro>
          <span id="Label8">Invoice Description:</span>
        </td>
        <td bgColor=gainsboro>
          <input name="txtInvDesc" type="text" id="txtInvDesc" />
        </td>
      </tr>
      <tr>
        <td bgColor=gainsboro>
          <span id="Label9">Invoice Reference:</span>
        </td>
        <td bgColor=gainsboro>
          <input name="txtInvRef" type="text" id="txtInvRef" />
        </td>
      </tr>
      <tr>
        <td bgColor=gainsboro>
          <span id="Label13">Transaction Number:</span>
        </td>
        <td bgColor=gainsboro>
          <input name="txtTxnNumber" type="text" id="txtTxnNumber" />
        </td>
      </tr>
      <tr>
        <td bgColor=red>
          <span id="Label10">Card Holders Name:</span>
        </td>
        <td bgColor=red>
          <input name="txtCCName" type="text" id="txtCCName" />
        </td>
      </tr>
      <tr>
        <td bgColor=red>
          <span id="Label2">Card Number:</span>
        </td>
        <td bgColor=red>
          <input name="txtCCNumber" type="text" maxlength="17" id="txtCCNumber" />
        </td>
      </tr>
      <tr>
        <td bgColor=red>
          <span id="Label3">Card Expiry:</span>
        </td>
        <td bgColor=red>
          <select name="ddlExpiryMonth" id="ddlExpiryMonth">
            <option value="01">01</option>
            <option value="02">02</option>
            <option value="03">03</option>
            <option value="04">04</option>
            <option value="05">05</option>
            <option value="06">06</option>
            <option value="07">07</option>
            <option value="08">08</option>
            <option value="09">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
          </select>
          <select name="ddlExpiryYear" id="ddlExpiryYear">
            <option value="04">04</option>
            <option value="05">05</option>
            <option value="06">06</option>
            <option value="07">07</option>
            <option value="08">08</option>
            <option value="09">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
          </select>
        </td>
      </tr>
      <tr>
        <td style="HEIGHT: 24px" bgColor=red>
          <span id="Label4">Card Type:</span>
        </td>
        <td style="HEIGHT: 24px" bgColor=red>
          <select name="ddlCardType" id="ddlCardType">
            <option value="VISA">VISA</option>
            <option value="MASTERCARD">MASTERCARD</option>
            <option value="AMEX">AMEX</option>
          </select>
        </td>
      </tr>
      <tr>
        <td bgColor=red>
          <span id="Label6">Total Amount:</span>
        </td>
        <td bgColor=red>
            <input name="txtAmount" type="text" id="txtAmount" style="width:64px;" />
        </td>
      </tr>
      <tr>
        <td bgColor=gainsboro colSpan=2>
          <input type="submit" name="btnProcess" value="Process Transaction" id="btnProcess" />
        </td>
      </tr>
    </table>
  </div>
  <br />
</form>
</body>
</html>
<?php }?>
