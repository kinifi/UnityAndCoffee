<?
// This is the url your database is located at.
// Your webhost would be able to provide you with this info if you don't know
$loginURL		="127.0.0.1:3306";

// The username of a user who has authority to access and update the database
// The person who setup your database should have this info, alternatively, your webhost
$username		="myUsername";

// The password associated with the above $username
$password		="myPassword";

//the name of the database that contains your data
$database		="myDatabase";

// In emails this is the name that will identify your game to the recipient.
// Example: "Thank you for registering an account for My Awesome Game"
$gamename		="My Awesome Game";

// The subject line of the email the person will receive to activate their account
// This can be anything you like
$subject 		= $gamename . " account creation validation" ;

// The full path to the validation script (validate.php) on your server.
// This link will be used in the email as the location to send the validation reply to
$validationurl	="http://localhost/validate.php";

// The URL to your website home page. In the emails I provide links to the website so
// people can have somewhere to navigate to after they do their validation. Also just for
// extra advertising. Always a good idea to advertise your site everywhere you can. In this
// instance I take into account that the user may have received this email but never went
// to the website. Months later they redescover this email and go: "What is this?". The
// website URL, combined with the game name helps get people back to your site.
$websiteurl		="http://localhost:80/";

// I give customers piece of mind by giving them someone to contact in case they have
// and questions/ concerns. This is who they are told to contact. Use a name or position
// Example 1: If you have any questions, please contact customer support
// Example 2: If you have and questions, please contact the head guild master, John Baptiiste
$contactperson	="customer support";

// Used in combination with the $contactperson, this is the email address people will
// actually send mails to when trying to contact $contactpersonabove.
$contactemail	="accounts@mybadstudios.com";

// This is the URL to where you keep the PHP files that you got with this product.
// Make sure all the php files are located there and make sure this URL does not end with
// a trailing back slash
$phpurl			="http://localhost:80";

?>