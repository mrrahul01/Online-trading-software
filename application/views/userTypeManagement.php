<?php
	echo '<p class="oprtMessage" style="width:100%;"></p>';
  echo  '<p id="userTypeAdd"><img src="/images/users.png" alt="Add User Type" title="Add User Type" height="25px" width="25px">Add User Type</p>';
  echo form_input(array('name' => 'userTypeCreate', 'id' => 'userTypeCreate', 
        'class' => 'formInput','size' => 15,'style'=> 'display:none; margin-top: 25px;'));
	if(count($userTypeView))
  	{

    	// generate table data
		  $tmpl = array (
        'table_open'  => '<table  cellpadding="1" cellspacing="1" class="display" id="userTypeView">',

                  
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
      
	  	$this->table->set_template($tmpl);
	    $this->table->set_empty("-");
  		$this->table->set_heading('User Type', 'Action Count','User Count');
      // Hidden Id will contain userId
      // Hidden Active will contain userActivationMailSent; if it is 0 then user is not active, else active
    	foreach($userTypeView as $list)
  		{
       
        //$attr =  'class="formInput" style="display:none" type="hidden" id="userTypeInput-'.$list['userTypeId'].'" value="'.$list['userType'].'"';
        //$userTypeInput = form_input('userTypeInput-'.$list['userTypeId'],$attr);
        $userTypeInput = form_input(array('name' => 'userTypeInput-'.$list['userTypeId'], 'id' => 'userTypeInput-'.$list['userTypeId'], 
        'class' => 'formInput','size' => 15,'style'=> 'display:none;','value' =>$list['userType']));
        $row_data = array( 
    			'<div id="userTypeText-'.$list['userTypeId'].'">'.$list['userType'].'</div>'.$userTypeInput,
    			$list['userActionCount'],
    			$list['userCount']
   			);
               
        $this->table->add_row(array('data'=>$row_data, 'id'=> $list['userTypeId']));
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
		userTypeManagementPhp();
	});
</script>

<?php
/* End of file userManagement.php */
/* Location: ./application/views/userTypeManagement.php */	