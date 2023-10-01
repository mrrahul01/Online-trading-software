<?php
	echo '<p class="oprtMessage" style="width:100%"></p>';
  
  if($insertAccess == 1)
  {
    echo  '<p id="trafficTypeAdd"><img src="/images/trafficType.png" alt="New Traffic Type" title="New Traffic Type" height="25px" width="25px">New Traffic Type</p>';
  
    // input box create, kept hidden
    echo form_open(base_url().'alveron/trafficTypeList/1',array('id' => 'trafficTypeAddForm','style'=>'display:none;'));
    echo form_fieldset('Traffic Type Add');
    
    $tData = array('name'=> 'trafficTypeNew','id'=> 'trafficTypeNew','size'=> 20,'class'=>'formInput','value'=>'');
    echo " <p class='formP'> <label for='trafficTypeNew' class='formLabel'> Traffic Type </label>";
      echo form_input($tData);
      echo '<span id="trafficTypeVerify" class="verify"></span>';
    echo '</p>';

    echo '<p class="formP">';
      echo form_label('Description','trafficDescriptionNew',array('class' => 'formLabel'));
      echo form_textarea(array('name' => 'trafficDescriptionNew', 'id' => 'trafficDescriptionNew', 'class' =>'formInput','rows' => 5, 'cols'=>30));
      echo '<span id="trafficDescriptionVerify" class="verify"></span>';
    echo '</p>';

    echo '<p>';
        $buttonData = array('name' => 'buttonTrafficTypeAdd','id' => 'buttonTrafficTypeAdd',
          'class' => 'formButton','content' => 'Submit',
          'type'=>'submit','value'=>'true');
        echo form_button($buttonData);
    echo '</p>';

    echo form_fieldset_close();
    echo form_close();
    
    // insert form create, kept hidden - ends  
  }
  


	if(count($trafficTypeListView))
  	{
		
      // generate table data
		  $tmpl = array (
        'table_open'  => '<table  cellpadding="1" cellspacing="1" class="display" id="trafficTypeListView">',

                  
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
  		$this->table->set_heading('Traffic Type','Description');
      // Hidden Id will contain userId
      // Hidden Active will contain userActivationMailSent; if it is 0 then user is not active, else active
    	foreach($trafficTypeListView as $list)
  		{
  			
        if($editAccess == 1)
        {
          $trafficTypeInput = form_input(array('name' => 'trafficTypeInput-'.$list['trafficTypeId'], 'id' => 'trafficTypeInput-'.$list['trafficTypeId'], 
            'class' => 'formInput','size' => 15,'style'=> 'display:none;','value' =>$list['trafficType']));
          $list['trafficType'] = '<div id="trafficTypeText-'.$list['trafficTypeId'].'">'.ucfirst($list['trafficType']).'</div>'.$trafficTypeInput;


          $trafficDescriptionInput= form_textarea(array('name' => 'trafficDescriptionInput-'.$list['trafficTypeId'], 
            'id' => 'trafficDescriptionInput-'.$list['trafficTypeId'], 'style' => 'display:none;',
            'class' =>'formInput','rows' => 5, 'cols'=>30,'value' =>$list['trafficDescription']));

          $list['trafficDescription'] = '<div id="trafficDescriptionText-'.$list['trafficTypeId'].'">'.$list['trafficDescription'].'</div>'.$trafficDescriptionInput;  
        }
        else
        {
          $list['trafficType'] = ucfirst($list['trafficType']);
        }
 
        $row_data = array( 
     			$list['trafficType'],
          $list['trafficDescription']
   			);
               
        $this->table->add_row(array('data'=>$row_data, 'id'=> $list['trafficTypeId']));
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
		trafficTypeListPhp();
	});
</script>

<?php
/* End of file trafficTypeList.php */
/* Location: ./application/views/trafficTypeList.php */	