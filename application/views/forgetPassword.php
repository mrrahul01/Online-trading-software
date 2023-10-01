<div id="login">
<?php
	
	
	$emaildata = array('name'=> 'userEmail','id'=> 'userEmail','size'=> 25,'class'=>'formInput');
	
	echo "<form method='post' action='".base_url()."alveron/forgetPassword/1' id='forgetPasswordForm'>";
	
	echo "<div id='forgetPasswordMessage' style='width:100%;'></div>";
	
	echo "<fieldset><legend>Password Reset Request</legend>";
	
	echo " <p class='formP'> <label for='userEmail' class='formLabel'> User Email </label>";
	echo form_input($emaildata);
	echo '</p>';
	echo '<input type="submit" name="buttonForgetPassword" id="buttonForgetPassword" value="Send Request" class="formButton" >';
		
	echo "</fieldset>";
	echo form_close();
?>
</div>

<script>
	$(function()
	{
		forgetPasswordPhp(); // go to global.js
	}); // ready ends
</script>

<?php
/* End of file forgetPassword.php */
/* Location: ./application/views/forgetPassword.php */	