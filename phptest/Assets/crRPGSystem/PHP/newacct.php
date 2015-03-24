<?
include ("settings.php");

mysql_connect($loginURL,$username,$password);
@mysql_select_db($database) or die( "9");

//I don't send a pageState from Unity so if this form is called from Unity then this value will be 0
//if this script is called from the web then this value will be 51
$pageState	= mysql_real_escape_string(strip_tags($_POST['pageState']));

$user		= mysql_real_escape_string( strip_tags($_POST['username']) );
$passw		= strip_tags($_POST['passw']);
$cleanpw	= mysql_real_escape_string( crypt( md5($passw), md5($user) ) );
$name		= mysql_real_escape_string(strip_tags($_POST['name']));
$surname	= mysql_real_escape_string(strip_tags($_POST['surname']));
$addr1		= mysql_real_escape_string(strip_tags($_POST['addr1']));
$addr2		= mysql_real_escape_string(strip_tags($_POST['addr2']));
$city		= mysql_real_escape_string(strip_tags($_POST['city']));
$stat		= mysql_real_escape_string(strip_tags($_POST['state']));
$zipp		= mysql_real_escape_string(strip_tags($_POST['zip']));
$country	= mysql_real_escape_string(strip_tags($_POST['country']));
$paypal		= mysql_real_escape_string(strip_tags($_POST['paypal']));
$email		= mysql_real_escape_string(strip_tags($_POST['email']));

//first test if account is already awaiting activation
$query="SELECT * FROM activate WHERE email = '$email'";
$result=mysql_query($query);
$num = mysql_numrows($result);

//if it is not awaiting activation, test if username is alreay taken
if($num == 0)
{	
	$query="SELECT * FROM login WHERE username = '$user'";
	$result=mysql_query($query);
	$num = mysql_numrows($result);
} else
{
	// if account is being created online, set return code here...
	if ($pageState > 0)
		$pageState = 6;
}

if ($num > 0)
{
	// if the account was created via the website...
	if ($pageState != 0)
	{
		if ($pageState != 6) // only return error code 6 (account awaiting validation)
			$pageState =  3; // or error code 3 (username already taken) (See Unity prefabs for error codes)
		
		$VARS =   "username=" . $username
				. "&passw=" . $passw 
				. "&name=" . $name 
				. "&surname=" . $surname 
				. "&addr1=" . $addr1 
				. "&addr2=" . $addr2 
				. "&city=" . $city 
				. "&state=" . $stat 
				. "&zip=" . $zipp 
				. "&country=" . $country 
				. "&email=" . $email 
				. "&paypal=" . $paypal
				. "&pageState=" . $pageState;
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $phpurl . "/NewAccount.php");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $VARS);
			curl_exec($ch);
			curl_close($ch);
	} else
	
	//else, if created via Unity project...
	{
		echo "3";
	}
} else
{
	$query 		= "INSERT INTO login (username, passw, status, dats, data) VALUES ('$user','$cleanpw','0',NOW(),NOW() )";
	mysql_query($query);

	$UID=mysql_insert_id();

	$query = "INSERT INTO account (UID, name, surname, addr1, addr2, city, state, zip, country, email, paypal, notes) VALUES "
	. "('$UID','$name','$surname','$addr1','$addr2','$city','$stat','$zipp','$country','$email','$paypal','')";
	mysql_query($query);
	
	$query = "INSERT INTO activate (UID, email) VALUES ('$UID', '$email')";
	mysql_query($query);
	
    $message = "<html><body><b>Good day</b><p>Someone claiming to be<b> $name $surname </b>has used this email address to create an account for $gamename"
    . "<p>If it was not you who created this account, no further action is required on your behalf but, if you want, simply click the relevant "
    . "link below to cancel this account immediately.<br>"
    . "If you did create the account, please click on the relevant link, below, to activate your account for $gamename."
    . "<p><a href=\"" . $validationurl . "?email=" . $email . "&activate=1\">Yes, please activate my account</a><br>"
    . "<a href=\"" . $validationurl . "?email=" . $email . "&activate=0\">I did not create this account. Please close it immediately</a>"
	. "<p>Thank you for registering for $gamename <p>If you have any further concerns please feel free to contact $contactperson on <a href=\"mailto:"
	. $contactemail . "\"> $contactemail</a><p>Customer support<br><a href=\"" . $websiteurl ."\"> $websiteurl</a>";

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= "From: $contactemail" . "\r\n";

    mail($email, $subject, $message, $headers );

	// if this formw as submitted from the web, show a message to indicate the account is created and awaiting validation
	// if not, return 0 to Unity to process from there...
	if ($pageState != 0)
	{
		$message = "<html><body><h2>Account created. Awaiting validation</h2><p>An email has been sent to $email with instructions on how to activate this account. "
		. "Simply click the relevant link in the mail and your account will be activated immediately.<br>If you have not received your activation email within 24 hours "
		. "please contact $contactperson for assistance.</p><p>Thank you for registering an account for $gamename.<br>We hope you enjoy your account</p><p>"
		. "<a href=\"mailto:" . $contactemail . "\"> $contactperson </a><br><h2><a href=\"" . $websiteurl . "\">$gamename</a></h2></p></body></html>";
		
		echo $message;
	} else
	{
		echo "0";
	}
}

mysql_close();
?>