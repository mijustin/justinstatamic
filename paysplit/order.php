<?php

	/* Simple order form script 
		Uses $_POST variables: email, name, date, suggestions
	**/

	$email = htmlspecialchars($_POST['email']);
	$name = htmlspecialchars($_POST['name']);
	$date = htmlspecialchars($_POST['date']);
	$suggestions = htmlspecialchars($_POST['suggestions']);

	/* You can edit the templates below to customize reservation emails. Remember to change $mail_address to your email address. */
	$mail_subject = "New booking";
	$mail_content = "Someone has booked a table!\r\n \r\nName: ".$name."\r\nDate: ".$date."\r\nEmail: ".$email."\r\nSuggestions: ".$suggestions."\r\n";
	$mail_address = "yourmail@mail.com";   /*  Your email **/

	$mail_content = wordwrap($mail_content, 70, "\r\n");
	$headers = 'X-Mailer: PHP/'.phpversion();
	mail($mail_address, $mail_subject, $mail_content, $headers);

	header('Location: /');
?>
