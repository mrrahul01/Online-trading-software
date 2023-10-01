<div id ='login'>
<?php
	if(count($userInfo))
	{
		foreach($userInfo as $list)
  		{
  			$userName = $list['userName'];
  			$userFirstName = $list['userFirstName'];
  			$userLastName = $list['userLastName'];
  			$userEmail = $list['userEmail'];
  			$userType = $list['userType'];
  			$userIdReference = $list['userId'];
  		}	
	}

	// user registration form
	$uData = array('name'=> 'userName','id'=> 'userName','size'=> 20,'class'=>'formInput','value'=>$userName);
	$fData = array('name'=> 'userFirstName','id'=> 'userFirstName','size'=> 20,'class'=>'formInput', 'value'=> $userFirstName);
	$lData = array('name'=> 'userLastName','id'=> 'userLastName','size'=> 20,'class'=>'formInput','value'=>$userLastName);
	$eData = array('name'=> 'userEmail','id'=> 'userEmail','size'=> 20,'class'=>'formInput','disabled'=> true,'value'=>$userEmail);
	$tData = array('name'=> 'userType','id'=> 'userType','size'=> 20,'class'=>'formInput','disabled'=> true,'value'=>$userType);
	$pOldData = array('name'=> 'userOldPassword','id'=> 'userOldPassword','size'=> 20, 'class'=>'formInput');
	$pNewData = array('name'=> 'userNewPassword','id'=> 'userNewPassword','size'=> 20, 'class'=>'formInput');

	echo "<form method='post' action='".base_url()."alveron/accountManagement/1' id='accountManagementForm'>";
	echo "<div id='accountManagementMessage'></div>";
	echo "<div class='userIdReference' style='display:none;' id='".$userIdReference."'></div>";
	echo "<fieldset><legend>Update Info</legend>";
	
	
	echo " <p class='formP'> <label for='userName' class='formLabel'> User Id </label>";
	echo form_input($uData);
	echo '<span id="userNameVerify" class="verify"></span>';
	echo '</p>';
	
	echo " <p class='formP'> <label for='userFirstName' class='formLabel'> First Name </label>";
	echo form_input($fData);
	echo '<span id="userFirstNameVerify" class="verify"></span>';
	echo '</p>';

	echo " <p class='formP'> <label for='userLastName' class='formLabel'> Last Name </label>";
	echo form_input($lData);
	echo '<span id="userLastNameVerify" class="verify"></span>';	
	echo '</p>';

	echo " <p class='formP'> <label for='userEmail' class='formLabel'> Email </label>";
	echo form_input($eData);
	echo '</p>';

	echo " <p class='formP'> <label for='userType' class='formLabel'> Role </label>";
	echo form_input($tData);
	echo '</p>';


	echo " <p class='formP'> <label for='userOldPassword' class='formLabel'> Old Password </label>";
	echo form_password($pOldData);
	echo '<span id="userOldPasswordVerify" class="verify"></span>';	
	echo '</p>';

	echo " <p class='formP'> <label for='userNewPassword' class='formLabel'> New Password </label>";
	echo form_password($pNewData);
	echo '<span id="userNewPasswordVerify" class="verify"></span>';
	echo '</p>';

	echo '<p class="formP">';
	echo form_label('Retype','userPasswordRetype',array('class' => 'formLabel'));
	echo form_password(array('name' => 'userPasswordRetype', 'id' => 'userPasswordRetype', 'class' => 'formInput',
		'title'=>'Retype your password', 'size' => 20, 'disabled' => true));
	echo '<span id="userPasswordRetypeVerify" class="verify"></span>';
	echo '</p>';
	
	echo '<input type="submit" name="buttonAccountManagement" id="buttonAccountManagement" value="Update" class="formButton" >';
	
	echo "</fieldset>";
	echo form_close();

	
?>
</div>
<script>
	$(function()
	{
		accountManagementPhp(); // go to global.js
	}); // ready ends
</script>

<?php
/* End of file accountManagement.php */
/* Location: ./application/views/login.php */	