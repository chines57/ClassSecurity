<?php
//retrieve our data from POST
$username_unesc = $_POST['username'];
$firstname_unesc = $_POST['firstname'];
$lastname_unesc = $_POST['lastname'];
$company_unesc = $_POST['company'];
$emailaddress_unesc = $_POST['email'];
$phone_unesc = $_POST['phone'];
$pass1_unesc = $_POST['pass1'];
$pass2_unesc = $_POST['pass2'];

// Error checking on data entry

function CheckEmail($email_local) 
{
	$p = '/^[a-z0-9!#$%&*+-=?^_`{|}~]+(\.[a-z0-9!#$%&*+-=?^_`{|}~]+)*';
	$p.= '@([-a-z0-9]+\.)+([a-z]{2,3}';
	$p.= '|info|arpa|aero|coop|name|museum)$/ix';
	return preg_match($p, $email_local);
}

$error = '';
if (strlen($username_unesc)<3) $error = '"User Name" must be at least 3 characters long'."\n";
if (strlen($username_unesc)>30) $error .= '"User Name" must be less than 31 characters long'."\n";
if (strlen($firstname_unesc)<2) $error .= '"First Name" is required'."\n";
if (strlen($lastname_unesc)<2) $error .= '"Last Name" is required'."\n";
if (strlen($company_unesc)<2) $error .= '"Company" is required'."\n";
if (strlen($emailaddress_unesc)<1) $error .= '"E-mail" address is required'."\n";
if ($pass1_unesc != $pass2_unesc) $error .= '"Passwords" don\'t match';
if (strlen($pass1_unesc)>64) $error .= '"Password" must be less than 65 characters long.\n';
if (!CheckEmail($emailaddress_unesc)) $error .= 'Incorrect e-mail address'."\n";

if (!empty($error))
{
//	header('Location: ../bin/form-login.php');
//	print '<div id="contentright"><div id="contact-right">';
	echo '<p align="center" style="color: #DD0000; font-weight: bold;">'.$error.'</p>';
	echo '<p align="center"><a href="javascript:history.go(-1);">Please try again.</a></p>';
//	print '<div><div>';
}
else
{
	//creates a 3 character sequence
	function createSalt()
	{
	// add uniqueid to lengthen string passed to md5 beyond RANDMAX, only 32768 on Windows
	// encrypt uniqueid to include more characters than just numbers
	$string = md5(uniqid(mt_rand(), true));
	return $string;
	}
	
	$salt = createSalt();

	$hash = sha1($pass1_unesc);
	$hash = sha1($salt . $hash);
	
	$dbhost = 'localhost';
	$dbuser = 'rafnnxrm_user1rw';
	$dbpass = 'bgT%vgY&234'; 
	$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
if (mysqli_connect_errno()) {
	printf("db connect failed: %s\n", mysqli_connect_error());
}
	$dbname = 'rafnnxrm_backend';
	mysqli_select_db($conn, $dbname);
if (mysqli_connect_errno()) {
	printf("db select failed: %s\n", mysqli_connect_error());
}

	//sanitize username 
	$username_esc = addslashes($username_unesc);
	$firstname_esc = addslashes($firstname_unesc);
	$lastname_esc = addslashes($lastname_unesc);
	$company_esc = addslashes($company_unesc);
	$emailaddress_esc = addslashes($emailaddress_unesc);
	$phone_esc = addslashes($phone_unesc);

	// generate validation string
	$validatestring = sha1($salt . $username_esc);

$query1 = "SELECT person_ui FROM a_userinfo WHERE person_ui = '$username_esc';";

	if (!mysqli_query($conn, $query1)) {
	// user already exists
	
if (mysqli_connect_errno()) {
	printf("Select person_ui failed: %s\n", mysqli_connect_error());
}
	// Close DB connection
	mysqli_close($conn);
if (mysqli_connect_errno()) {
	printf("Close DB failed: %s\n", mysqli_connect_error());
}
	echo '<p align="center" style="color: #DD0000; font-weight: bold;">'.$username_esc.'That user name already exists.  Please login with that user name if it is yours, or pick another user name to register."</p>';
	echo '<p align="center"><a href="javascript:history.go(-1);">Please try again.</a></p>';

	}

	else { 
	// user doesn't already exist
	$datecreated = time();
	$datevalidate = time() + (60*60*24*4);
	$datecreatedf = date('Y m d h: s, D, Z', $datecreated);
	$query2 = "INSERT INTO a_userinfo (name_first, name_last, company, email, phone, person_ui , person_qt, seasoning, date_created, date_createdf , validatestring , date_validateby)
	VALUES ( '$firstname_esc', '$lastname_esc', '$company_esc', '$emailaddress_esc', '$phone_esc', '$username_esc' , '$hash' , '$salt' , '$datecreated' , '$datecreatedf' , '$validatestring' , '$datevalidate');";  
	mysqli_query($conn, $query2);
if (mysqli_connect_errno()) {
	printf("Insert new customer failed: %s\n", mysqli_connect_error());
}
	$user_id = mysqli_insert_id($conn);
if (mysqli_connect_errno()) {
	printf("Get new user id failed: %s\n", mysqli_connect_error());
}
	// Close DB connection
	mysqli_close($conn);
if (mysqli_connect_errno()) {
	printf("Close DB failed: %s\n", mysqli_connect_error());
}
	setcookie("USERSTATUS", "registered", time()+60*60*24*352, "/", ".hinesindustries.com");

	$validate_link = "https://hinesindustries.com/bin/form-login.php?ref=".$user_id."&vs=".$validatestring;
$msg = "Thank-you for signing up to be a registered customer with Hines Industries. Click on the link below to complete your registration. If you have any issues completing the verification please let us know by sending an email to webmaster@hinesindustries.com. Please do not reply to this email as the mailbox is not reviewed by a human.  \n\n".$validate_link."\n\n Sincerely,\n\n Hines Industries";
$recipient = $emailaddress_esc;
$mailheaders = "From: Hines Website<no-reply@hinesindustries.com>\n";
$mailheaders .= "Reply-To: no-reply@hineindustries.com";
$subject = "Hines Industries Customer Registration"; 
mail($recipient, $subject, $msg, $mailheaders);
$url = "../bin/emailsent.php";
	header('Location: '.$url);
	} 
}
?>


