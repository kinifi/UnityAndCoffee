<?
include("settings.php");

	$user		= strip_tags($_POST['username']);
	$passw		= strip_tags($_POST['passw']);
	$name		= strip_tags($_POST['name']);
	$surname	= strip_tags($_POST['surname']);
	$addr1		= strip_tags($_POST['addr1']);
	$addr2		= strip_tags($_POST['addr2']);
	$city		= strip_tags($_POST['city']);
	$stat		= strip_tags($_POST['state']);
	$zipp		= strip_tags($_POST['zip']);
	$country	= strip_tags($_POST['country']);
	$email		= strip_tags($_POST['email']);
	$paypal		= strip_tags($_POST['paypal']);

	if(!empty($_POST['pageState'])) 
		$pageState	= strip_tags($_POST['pageState']);
	else
		$pageState	= 0;

function PrintHeading($pageState, $heading, $val, $required, $fieldname, $fieldsize, $value, $protect)
{
	if ($pageState != $val)
	{
		$fcol = "black";
	}
	else
	{
		$fcol = "red";
	}
	echo "<tr><td width=200><font color=\"" . $fcol . "\"> $heading </font>";
	
	if ($required == TRUE)
	{
		echo "<font color=red> (*)</font>";
	}
		
	echo "</td><td width=20>&nbsp;</td><td width=200><input type=\"";
	if ($protect)
		echo "password";
	else
		echo "text";
		
	echo "\" name=\"" . $fieldname . "\" size=\"" . $fieldsize ."\" maxlength=\"" . $fieldsize ."\" value=\"" . $value . "\" /></td></tr>";
}

?>

<html>
<body>

<?
if ($pageState < 50)
{
?>

<div>

<?
//error message in case of existing username
	if ($pageState > 0)
		$message = "There were errors on this form. Please fix the fields in red and resubmit";
	else
		$message = "";
		
	switch ($pageState)
	{
		case 3:
			$message = "Username already exists";
			$pageState = 4;
			break;
			
		case 6:
			$message = "Email already awaiting activation";
			$pageState = 9;
			break;
	}

echo "<font color=\"red\">" . $message ."</font><br>";
?>

<form method="post" action="NewAccount.php" >
<input type="hidden" name="pageState" value="50">
<table>

<?
PrintHeading($pageState, "Username", 	 4, true,  "username",	15, $user,		false);
PrintHeading($pageState, "Password",	 5, true,  "passw",		15, $passw,		true);
PrintHeading($pageState, "Name",		 7, true,  "name",		15, $name,		false);
PrintHeading($pageState, "Surname",		 8, true,  "surname",	15, $surname,	false);
PrintHeading($pageState, "Email", 		 9, true,  "email",		50, $email,		false);
PrintHeading($pageState, "Address", 	99, FALSE, "addr1",		30, $addr1,		false);
PrintHeading($pageState, "", 			99, FALSE, "addr2",		30, $addr2,		false);
PrintHeading($pageState, "City", 		99, FALSE, "city",		20, $city,		false);
PrintHeading($pageState, "State", 		99, FALSE, "state",		20, $stat,		false);
PrintHeading($pageState, "Post code",	99, FALSE, "zip",		10, $zipp,		false);
PrintHeading($pageState, "Country",		99, FALSE, "country",	20, $country,	false);
PrintHeading($pageState, "PayPal",		99, FALSE, "paypal",	50, $paypal,	false);
?>

<tr><td colspan=3><input type="submit" value="Create account" /></td></tr>
</table>
</form>
</div>

<?
} else
{
	$VARS =   "username="	. $user
			. "&passw=" 	. $passw 
			. "&name=" 		. $name 
			. "&surname=" 	. $surname 
			. "&addr1=" 	. $addr1 
			. "&addr2=" 	. $addr2 
			. "&city=" 		. $city 
			. "&state=" 	. $stat 
			. "&zip=" 		. $zipp 
			. "&country=" 	. $country 
			. "&email=" 	. $email 
			. "&paypal=" 	. $paypal; 

	switch ($pageState)
	{
		//if data has just been submitted for the first time, do some validation on the data
		case "50":
		{
			//assume success...
			$pageState = 51;
			
			// test for empty values on required fields.
			// Start in reverse order of priority so highest priority will overwrite everything else
			if ($email == "")		$pageState = 9;
			if ($surname == "")		$pageState = 8;
			if ($name == "") 		$pageState = 7;
			if ($passw == "")		$pageState = 5;
			if ($username == "")	$pageState = 4;

			if ($pageState == 51)
			{
				// do additional tests like if an email verify fied was present, if they match or not
			}
			
			$VARS .= "&pageState=" . $pageState;
			
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $phpurl . "/NewAccount.php");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $VARS);
			curl_exec($ch);
			curl_close($ch);
		}
		break;

		// by this stage, everything is validated so create the account...
		// this will either cause an error code 3 or 6 to occur and this script will be called again
		// or everything will be just dandy and thi form will be replaced by a "Thanks" message...
		case "51":
		{
			$VARS .= "&pageState=" . $pageState;
			
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $phpurl . "/newacct.php");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $VARS);
			curl_exec($ch);
			curl_close($ch);
		}
			break;
			
		default:
			echo(" part3");
			break;
	}
}
?>

</body>
</html>
