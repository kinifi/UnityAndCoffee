<?
include("settings.php");

mysql_connect($loginURL,$username,$password);
@mysql_select_db($database) or die( "9");

$user = mysql_real_escape_string(strip_tags($_POST['username']));
$passw = strip_tags($_POST['passw']);
$cleanpw = mysql_real_escape_string( crypt( md5($passw), md5($user) ) );

$query="SELECT * FROM login WHERE username = '$user' AND passw = '$cleanpw'";
$result=mysql_query($query);

$num = mysql_numrows($result);
mysql_close();
$returncode = 0;

if ($num == 0)
	$returncode = 2;
else
{
	$status=mysql_result($result,0,"status");
	if ($status == 1)
		$returncode = 1;
	if ($status == 0)
		$returncode = 6;

	if ($returncode == 0)
	{
		//add code to pdate acces time if required...
	}

}

echo "$returncode";
?>