<?php
	echo '<p class="oprtMessage" style="width:100%;"></p>';
  echo  '<p id="userTypeAccessAddImg"><img src="/images/users.png" alt="New User Type Access" title="New User Type Access" height="25px" width="25px">New User Type Access</p>';
  
  // insert form create, kept hidden
  echo form_open(base_url().'alveron/addUserTypeAccess',array('id' => 'userTypeAccessCreate','style'=>'display:none;'));
  echo form_fieldset('New User Type Access');
  
  echo '<p class="formP">';
    $attr =  'id="userTypeNew" class="formList"';
    echo form_label('User Type','userTypeNew',array('class' => 'formLabel'));
    echo form_dropdown('userTypeNew',$userTypeList,'',$attr);
  echo '</p>';

  echo '<p class="formP" id="hiddenSelect"></p>'; // this will be populated via global.js in alveron.php actionListForUserTypeAccessManagement()
 
  echo '<p>';
      $buttonData = array('name' => 'userTypeAccessAdd','id' => 'userTypeAccessAdd',
        'class' => 'formButton','content' => 'Submit',
        'type'=>'submit','value'=>'true');
      echo form_button($buttonData);
  echo '</p>';

  echo form_fieldset_close();
  echo form_close();
	
  // insert form create, kept hidden - ends
  
  // inline search option
  $searchOptionList = array('0'=>'Select Search Option', '1'=>'User Type','2'=>'Action Page');
  echo '<p class="formP" >';
    $attr =  'id="searchOption" class="formList"';
    echo form_label('Search by','searchOption',array('class' => 'formLabel'));
    echo form_dropdown('searchOption',$searchOptionList,$searchOptionSelected,$attr);
  echo '</p>';
  
  echo '<p class="formP" id="hiddenUserTypeSearch"  style="display:none;">';
    $attr =  'id="userTypeSearch" class="formList"';
    echo form_label('User Type','userTypeSearch',array('class' => 'formLabel'));
    echo form_dropdown('userTypeSearch',$userTypeList,$userTypeIdSelected,$attr);
  echo '</p>';

  echo '<p class="formP" id="hiddenUserActionSearch"  style="display:none;">';
    $attr =  'id="userActionSearch" class="formList"';
    echo form_label('Action Page','userActionSearch',array('class' => 'formLabel'));
    echo form_dropdown('userActionSearch',$userActionList,$userActionIdSelected,$attr);
  echo '</p>';

  if(count($userTypeAccessView))
  	{
		
     
    	// generate table data
		  $tmpl = array (
        'table_open'  => '<table  cellpadding="1" cellspacing="1" class="display" id="userTypeAccessView">',

                  
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
  		$this->table->set_heading('User Type', 'User Action','Access');
      
    	foreach($userTypeAccessView as $list)
  		{
       
        $userAccessList =  array(1  => '1',0    => '0');

        $attr =  'class="formList" style="display:none" id="userTypeAccessSelectBox-'.$list['userTypeAccessId'].'"';
        $userTypeAccessDropdownList = form_dropdown('userTypeAccessSelectBox-'.$list['userTypeAccessId'],$userAccessList,$list['userTypeAccessValue'],$attr);

        $row_data = array( 
    			$list['userType'],
    			$list['userAction'],
          '<div id="userTypeAccessText-'.$list['userTypeAccessId'].'">'.$list['userTypeAccessValue'].'</div>'.$userTypeAccessDropdownList
   			);
               
        $this->table->add_row(array('data'=>$row_data, 'id'=> $list['userTypeAccessId']));
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
		userTypeAccessManagementPhp();
	});
</script>

<?php
/* End of file userManagement.php */
/* Location: ./application/views/userTypeAccessManagement.php */	