<?php

$username_unesc = $_POST['username'];
$password_unesc = $_POST['password'];
$uid = $_POST['ref'];
$validatestring = $_POST['vs'];
//connect to the database here
$dbhost = 'localhost';
$dbuser = 'rafnnxrm_user1rw';
$dbpass = 'bgT%vgY&234'; 
$conn = mysqli_connect($dbhost, $dbuser, $dbpass);

$dbname = 'rafnnxrm_backend';
mysqli_select_db($conn, $dbname);

// check to see if user exisits
$username_esc = addslashes($username_unesc);
$query = "SELECT pk_user, name_first, email, person_ui, person_qt, seasoning, date_validateby, membershiptype FROM a_userinfo WHERE person_ui = '$username_esc';";
$result = mysqli_query($conn, $query);
if (mysqli_connect_errno()) {printf("DB connection failed: %s\n", mysqli_connect_error());}

if (mysqli_num_rows($result) < 1) {
	//no such user exists
	echo "No such user exists.  Please verify your login information before trying again or register to obtain login credentials.";
} 
else {
	// user exists
	$resArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
	$uid_db = $resArray['pk_user'];
	$name_first_db = $resArray['name_first'];
	$emailaddress_db = $resArray['email'];
	$person_ui_db = $resArray['person_ui'];
	$qt_db = $resArray['person_qt'];
	$seasoning_db = $resArray['seasoning'];
	$d_validateby_db = $resArray['date_validateby'];
	$memtype_db = $resArray['membershiptype'];
	
	// check to see if customer is initial customer that needs to be validated
	if ($memtype_db == "initial-login") {
		// user not yet validated
		// calculate validation string
		$username_esc = addslashes($username_unesc);
		$validatestring_calc = sha1($seasoning_db . $username_esc);
		if (($uid == $uid_db) && ($validatestring == $validatestring_calc) && ($d_validateby_db<=time())) {
			// customer information is valid
			$query_update = "UPDATE a_userinfo SET membershiptype = 'validated' WHERE pk_user = '$uid';";
			mysqli_query($conn, $query_update);
if (mysqli_connect_errno()) {
	printf("Membershiptype update failed: %s\n", mysqli_connect_error());
}
			$memtype_db = 'validated';
		}
		else {
			// customer information is invalid
			// re-generate date_validateby
			$validatestring = sha1($seasoning_db . $person_ui_db);
			$d_validateby_new = time();
			$query_update = "UPDATE a_userinfo SET date_validateby = '$d_validateby_new' WHERE pk_user = '$uid';";
			mysqli_query($conn, $query_update);
if (mysqli_connect_errno()) {
	printf("Validation date update failed: %s\n", mysqli_connect_error());
}
			// resend email
			$validate_link = "https://hinesindustries.com/bin/form-login.php?ref=".$uid_db."&vs=".$validatestring;
			$msg = "Thank-you for signing up to be a registered customer with Hines Industries. Click on the link below to complete your registration. If you have any issues completing the verification please let us know by sending an email to webmaster@hinesindustries.com. Please do not reply to this email as the mailbox is not reviewed by a human.  \n\n".$validate_link."\n\n Sincerely,\n\n Hines Industries";
			$recipient = $emailaddress_db;
			$mailheaders = "From: Hines Website<no-reply@hinesindustries.com>\n";
			$mailheaders .= "Reply-To: no-reply@hineindustries.com";
			$subject = "Hines Industries Customer Registration";
			mail($recipient, $subject, $msg, $mailheaders);
			$url = "../bin/emailsent.php";
			header('Location: '.$url);
		}
	}

	if ($memtype_db == "validated") {
	// validated user, check password and login if valid
		$hash = sha1( $seasoning_db . sha1($password_unesc) );
		if($hash == $qt_db) {
			session_start(); //must call session_start before using any $_SESSION variables
			session_regenerate_id(); //this is a security measure
			$_SESSION['valid'] = 1;
			$_SESSION['username'] = $username;
			$_SESSION['firstname'] = $firstname;
			setcookie("BREADCRUMB", $_SERVER['PHP_SELF'], time()+60*60*24*352, "/", ".hinesindustries.com");
			setcookie("USERSTATUS", "validated", time()+60*60*24*352, "/", ".hinesindustries.com");
			header('Location: ../bin/welcome.php');
		}
		else {
			// else give error messages
			echo '<p align="center" style="color: #DD0000; font-weight: bold;">Invalid password.</p>';
			echo '<p align="center"><a href="javascript:history.go(-1);">Please try again.</a></p>';
		}
	}

// end of user exisits
}

// Close DB connection
mysqli_close($conn);
if (mysqli_connect_errno()) {printf("Close DB failed: %s\n", mysqli_connect_error());}

?>
