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

	if (!empty($_POST['pageState'])) 
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

function PrintLoginScreen($stat)
{
	echo  "<form method=\"post\" action=\"UpdateAccount.php\" >"
		. "<input type=\"hidden\" name=\"pageState\" value=\"$stat\">"
		. "<table>";
	PrintHeading($pageState, "Username", 	 4, true,  "username",	15, $user,		false);
	PrintHeading($pageState, "Password",	 5, true,  "passw",		15, $passw,		true);
	echo  "<tr><td colspan=3><input type=\"submit\" value=\"Sign in\" /></td></tr></table></form>";
}

function PrintError($message)
{
	echo "<font color=\"red\">";
	
	switch($message)
	{
		case 02: echo "Invalid username and password combination"; break;
		case 03: echo "This account is currently suspended"; break;
		case 04: echo "This account is still awaiting activation"; break;
		case 57: echo "Name is required"; break;
		case 58: echo "Surname is required"; break;
		case 59: echo "A valid email address is required"; break;
	}
	
	echo "</font><br>";
}

function UpdateAccount($VARS, $phpurl, $stat)
{
	$VARS .= "&intent=" . $stat;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $phpurl . "/updateacct.php");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $VARS);
	curl_exec($ch);
	curl_close($ch);
}

?>

<html>
<body>

<div>
<?
if ($pageState < 50)
{
	switch ($pageState)
	{
		case 0:
			PrintLoginScreen(1);
			break;

		case 1:
			mysql_connect($loginURL,$username,$password);
			@mysql_select_db($database) or die( "Error connecting to database. Contact webmaster");

			$cleanpw = mysql_real_escape_string( crypt( md5($passw), md5($user) ) );
			$query="SELECT * FROM login WHERE username = '$user' AND passw = '$cleanpw'";
			$result=mysql_query($query);

			$num = mysql_numrows($result);
			$returncode = 50; //okay

			if ($num == 0)
				$returncode = 2; // not found
			else
			{
				$status=mysql_result($result,0,"status");
				if ($status == 1)
					$returncode = 3;	// account suspended
				if ($status == 0)
					$returncode = 4;	// account awaiting activation
			}
			mysql_close();
			
			if ($returncode == 50)
			{
				UpdateAccount($VARS, $phpurl, 50); 
			} else
			{
				PrintError($returncode);
				PrintLoginScreen(1);
			}
			break;
	}
}
else


if ($pageState == 50)
{
	echo  "<form method=\"post\" action=\"UpdateAccount.php\" >"
		. "<input type=\"hidden\" name=\"pageState\" value=\"51\">"
		. "<input type=\"hidden\" name=\"username\" value=\"$user\">"
		. "<input type=\"hidden\" name=\"passw\" value=\"$passw\">"
		. "<table>";
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
	echo  "<tr><td colspan=3><input type=\"submit\" value=\"Update Details\" /></td></tr></table></form>";
}

else
{
	// test for empty values on required fields.
	// Start in reverse order of priority so highest priority will overwrite everything else
	if ($email == "")
		$pageState = 59;
	else
		if (!preg_match("/^(\w+((-\w+)|(\w.\w+))*)\@(\w+((\.|-)\w+)*\.\w+$)/",$email))
			$pageState = 59;

	if ($surname == "")		$pageState = 58;
	if ($name == "") 		$pageState = 57;

	switch ($pageState)
	{
		// by this stage, everything is validated so create the account...
		// this will either cause an error code 3 or 6 to occur and this script will be called again
		// or everything will be just dandy and thi form will be replaced by a "Thanks" message...
		case 51:
			UpdateAccount($VARS, $phpurl, 51);
			break;
		
		case 57:
		case 58:
		case 59:
			PrintError($pageState);
			echo  "<form method=\"post\" action=\"UpdateAccount.php\" >"
			. "<input type=\"hidden\" name=\"pageState\" value=\"51\">"
			. "<input type=\"hidden\" name=\"username\" value=\"$user\">"
			. "<input type=\"hidden\" name=\"passw\" value=\"$passw\">"
			. "<table>";
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
			echo  "<tr><td colspan=3><input type=\"submit\" value=\"Update Details\" /></td></tr></table></form>";
			break;
	}
}
?>

</div>
</body>
</html>
