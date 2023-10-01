<?php
	echo '<p class="oprtMessage" style="width:100%;"></p>';
  echo  '<p id="adminPasswordReset"><img src="/images/pwdres.png" alt="Admin Password Reset" title="Admin Password Reset" height="25px" width="25px">Admin Password Reset</p>';
	// insert form create, kept hidden
  echo form_open(base_url().'alveron/adminPasswordReset',array('id' => 'adminPasswordResetForm','style'=>'display:none;'));
  echo form_fieldset('Admin Password Reset');
  
  echo '<p class="formP">';
    $attr =  'id="userIdNew" class="formList"';
    echo form_label('User Name','userIdNew',array('class' => 'formLabel'));
    echo form_dropdown('userIdNew',$userList,'',$attr);
  echo '</p>';

  echo '<p class="formP">';
    $passwordData =  array('name'=>'userPassword', 'id'=>'userPassword','value'=>'','size'=>'20');
    echo form_label('Password','userPassword',array('class' => 'formLabel'));
    echo form_input($passwordData);
  echo '</p>';
 

  echo '<p>';
      $buttonData = array('name' => 'buttonAdminPasswordReset','id' => 'buttonAdminPasswordReset',
        'class' => 'formButton','content' => 'Reset',
        'type'=>'submit','value'=>'true');
      echo form_button($buttonData);
  echo '</p>';

  echo form_fieldset_close();
  echo form_close();
    
    // insert form create, kept hidden - ends 

  if(count($userPasswordResetView))
  	{
		
      // generate table data
		  $tmpl = array (
        'table_open'  => '<table  cellpadding="1" cellspacing="1" class="display" id="userPasswordResetView">',

                  
  			'heading_row_start'   => '<tr>',
  			'heading_row_end'     => '</tr>',
        'heading_cell_start'  => '<th>',
        'heading_cell_end'    => '</th>',

        'row_start'           => '<tr class="even">',
        'row_end'             => '</tr>',
        'cell_start'          => '<td>',
        'cell_end'            => '</td>',

        'row_alt_start'       => '<tr class="odd">',
        'row_alt_end'         => '</tr>',
        'cell_alt_start'      => '<td>',
        'cell_alt_end'        => '</td>',

        'table_close'         => '</table>'

        );
      $rowCount = 0;
	  	$this->table->set_template($tmpl);
	    $this->table->set_empty("-");
  		$this->table->set_heading('User Id', 'Name','Email','User Status','User Type','Reset');
      // Hidden Id will contain userId
      // Hidden Active will contain userActivationMailSent; if it is 0 then user is not active, else active
    	foreach($userPasswordResetView as $list)
  		{
  			

  			$list['userStatus'] = ucfirst($list['userStatus']);
      	$list['userType'] = ucfirst($list['userType']);
      	$list['userFullName'] = ucfirst($list['userFirstName']).' '.ucfirst($list['userLastName']);
      	$list['resetButton'] = '<div class="reset">'.anchor(base_url().'alveron/userPasswordReset/'.$list['userId'],'Reset')."</div>";
        $row_data = array( 
    			$list['userName'],
    			$list['userFullName'],
    			$list['userEmail'],
    			$list['userStatus'],
          $list['userType'],
          $list['resetButton']
   			);
               
        $this->table->add_row(array('data'=>$row_data, 'id'=> $list['userId']));
  		}
  		$table = $this->table->generate();
  		echo $table;
  	}
  	else
  	{
  		echo '<p> No data found</p>';
  	}
  	

?>

<script>
	$(function()
  {
		userPasswordResetPhp();
	});
</script>

<?php
/* End of file userManagement.php */
/* Location: ./application/views/userManagement.php */	