<?
include("settings.php");

mysql_connect($loginURL,$username,$password);
@mysql_select_db($database) or die( "9");

$user = mysql_real_escape_string(strip_tags($_POST['username']));
$passw = strip_tags($_POST['passw']);
$cleanpw = mysql_real_escape_string( crypt( md5($passw), md5($user) ) );
$intent=$_POST['intent'];

$query="SELECT * FROM login WHERE username = '$user' AND passw = '$cleanpw'";
$result=mysql_query($query);
$num = mysql_numrows($result);

if ($num != 1)
{
	mysql_close();
	echo "2";
	die(2);
}

$UID=mysql_result($result,0,"UID");

$query="SELECT * FROM account WHERE UID = '$UID'";
$result=mysql_query($query);
$num = mysql_numrows($result);
	
if ($num == 0)
{
	mysql_close();
	echo "2";
	die(2);
}

switch ($intent)
{
	//retrieve personal data. Username and password not included
	case 0:
	case 50:
	{
		$name		= mysql_result($result,0,"name");
		$surname	= mysql_result($result,0,"surname");
		$addr1		= mysql_result($result,0,"addr1");
		$addr2		= mysql_result($result,0,"addr2");
		$city		= mysql_result($result,0,"city");
		$stat		= mysql_result($result,0,"state");
		$zipp		= mysql_result($result,0,"zip");
		$country	= mysql_result($result,0,"country");
		$email		= mysql_result($result,0,"email");
		$paypal		= mysql_result($result,0,"paypal");

		if ($intent == 50)
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
					. "&paypal=" 	. $paypal 
					. "&pageState=" . 50;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $phpurl . "/UpdateAccount.php");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $VARS);
			curl_exec($ch);
			curl_close($ch);
		}
		else
			echo "$name|$surname| $addr1| $city| $addr2| $stat| $zipp| $country| $email| $paypal";
	}
	break;
			
	//update personal data
	case 1:
	case 51:
	{
		$name		= mysql_real_escape_string(strip_tags($_POST['name']));
		$surname	= mysql_real_escape_string(strip_tags($_POST['surname']));
		$addr1		= mysql_real_escape_string(strip_tags($_POST['addr1']));
		$addr2		= mysql_real_escape_string(strip_tags($_POST['addr2']));
		$city		= mysql_real_escape_string(strip_tags($_POST['city']));
		$stat		= mysql_real_escape_string(strip_tags($_POST['state']));
		$zipp		= mysql_real_escape_string(strip_tags($_POST['zip']));
		$country	= mysql_real_escape_string(strip_tags($_POST['country']));
		$email		= mysql_real_escape_string(strip_tags($_POST['email']));
		$paypal		= mysql_real_escape_string(strip_tags($_POST['paypal']));

		$query="UPDATE account SET name='$name', surname='$surname', addr1='$addr1', addr2='$addr2', "
		. "city='$city', state='$stat', zip='$zipp', country='$country', email='$email' ,paypal='$paypal' "
		. "WHERE UID='$UID'";
		mysql_query($query);

		if ($intent == 51)
		{
			echo "<html><body>Account details successfully updated<br><a href=\"$phpurl/UpdateAccount.php\">Back</a></body></html>";
		}
		else
			echo "0";
	}
}

mysql_close();


?>