<?php
	echo '<p class="oprtMessage"></p>';
  
	if(count($userView))
  	{
		
      // generate table data
		  $tmpl = array (
        'table_open'  => '<table  cellpadding="1" cellspacing="1" class="display" id="userView">',

                  
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
  		$this->table->set_heading('User Id', 'Name','Email','User Status','User Type');
      // Hidden Id will contain userId
      // Hidden Active will contain userActivationMailSent; if it is 0 then user is not active, else active
    	foreach($userView as $list)
  		{
  			

  			$list['userStatus'] = ucfirst($list['userStatus']);
      	$list['userType'] = ucfirst($list['userType']);
      	$list['userFullName'] = ucfirst($list['userFirstName']).' '.ucfirst($list['userLastName']);
      	
        $attr =  'class="formList" style="display:none" id="userStatusSelectBox-'.$list['userId'].'"';
        $userStatusDropdownList = form_dropdown('userStatusSelectBox-'.$list['userId'],$userStatusList,$list['userStatusId'],$attr);

        $attr =  'class="formList" style="display:none" id="userTypeSelectBox-'.$list['userId'].'"';
        $userTypeDropdownList = form_dropdown('userTypeSelectBox-'.$list['userId'],$userTypeList,$list['userTypeId'],$attr);
        
        $row_data = array( 
    			$list['userName'],
    			$list['userFullName'],
    			$list['userEmail'],
    			'<div id="userStatus-'.$list['userId'].'">'.$list['userStatus'].'</div>'.$userStatusDropdownList,
          '<div id="userType-'.$list['userId'].'">'.$list['userType'].'</div>'.$userTypeDropdownList
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
		userManagementPhp();
	});
</script>

<?php
/* End of file userManagement.php */
/* Location: ./application/views/userManagement.php */	