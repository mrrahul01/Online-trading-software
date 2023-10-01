<div id="login">
<?php
	$udata = array('name'=> 'userNameLogin','id'=> 'userNameLogin','size'=> 20,'class'=>'formInput');
	$pdata = array('name'=> 'userPasswordLogin','id'=> 'userPasswordLogin','size'=> 20, 'class'=>'formInput');
	//echo form_open(base_url()."myblog/verify");
	echo "<form method='post' action='".base_url()."alveron/signInUser' id='loginForm'>";
	
	echo "<div id='loginMessage'></div>";
	
	echo "<p id='forgetPassword' style='display:none'>".anchor(base_url().'alveron/forgetPassword','Forget Password')."</p>";
	
	echo "<fieldset><legend>Login</legend>";
	echo " <p class='formP'> <label for='userNameLogin' class='formLabel'> User Id </label>";
	echo form_input($udata) . " </p> ";
	
	echo " <p class='formP'> <label for='userPasswordLogin' class='formLabel'> Password </label>";
	echo form_password($pdata) . " </p> ";
	
	echo  anchor('#','Sign Up');
	echo '<input type="submit" name="button" id="button" value="Sign In" class="formButton" >';
	
	echo "</fieldset>";
	echo form_close();

	// user registration form
	$udata = array('name'=> 'userName','id'=> 'userName','size'=> 20,'class'=>'formInput');
	$fdata = array('name'=> 'userFirstName','id'=> 'userFirstName','size'=> 20,'class'=>'formInput');
	$ldata = array('name'=> 'userLastName','id'=> 'userLastName','size'=> 20,'class'=>'formInput');
	$edata = array('name'=> 'userEmail','id'=> 'userEmail','size'=> 20,'class'=>'formInput');
	$pdata = array('name'=> 'userPassword','id'=> 'userPassword','size'=> 20, 'class'=>'formInput');
	
	echo "<form method='post' action='".base_url()."alveron/signUpUser' id='registrationForm' style='display: none;'>";
	echo "<div id='registrationMessage'></div>";
	echo "<fieldset><legend>Sign Up</legend>";
	
	
	echo " <p class='formP'> <label for='userName' class='formLabel'> User Id </label>";
	echo form_input($udata);
	echo '<span id="userNameVerify" class="verify"></span></p>';
	
	echo " <p class='formP'> <label for='userFirstName' class='formLabel'> First Name </label>";
	echo form_input($fdata);
	echo '<span id="userFirstNameVerify" class="verify"></span></p>';

	echo " <p class='formP'> <label for='userLastName' class='formLabel'> Last Name </label>";
	echo form_input($ldata);
	echo '<span id="userLastNameVerify" class="verify"></span></p>';	

	echo " <p class='formP'> <label for='userEmail' class='formLabel'> Email </label>";
	echo form_input($edata);
	echo '<span id="userEmailVerify" class="verify"></span></p>';

	echo " <p class='formP'> <label for='userPassword' class='formLabel'> Password </label>";
	echo form_password($pdata);
	echo '<span id="userPasswordVerify" class="verify"></span></p>';

	echo '<p class="formP">';
	echo form_label('Retype','userPasswordRetype',array('class' => 'formLabel'));
	echo form_password(array('name' => 'userPasswordRetype', 'id' => 'userPasswordRetype', 'class' => 'formInput',
		'title'=>'Retype your password', 'size' => 20, 'disabled' => true));
	echo '<span id="userPasswordRetypeVerify" class="verify"></span>';
	echo '</p>';
	
	echo '<input type="submit" name="buttonReg" id="buttonReg" value="Sign Up" class="formButton" >';
	echo '<input type="hidden" name="userTypeId" id="userTypeId" value = "4">'; // 4 means not assigned whether admin, NOC, or CR
	echo '<input type="hidden" name="userStatusId" id="userStatusId" value = "3">'; // 3 means pending, 1 means active, 2 means inactive
	echo "</fieldset>";
	echo form_close();

	
?>
</div>
<script>
	$(function()
	{
		loginPhp(); // go to global.js
	}); // ready ends
</script>

<?php
/* End of file login.php */
/* Location: ./application/views/login.php */	