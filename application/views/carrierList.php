<?php
	echo '<p class="oprtMessage" style="width:100%"></p>';
  
  if($insertAccess == 1) // if the user has insert access
  {
    echo  '<p id="carrierAdd"><img src="/images/carriers.png" alt="New Partner" title="New Partner" height="25px" width="25px">New Partner</p>';
  
    // input box create, kept hidden
    echo form_open(base_url().'alveron/carrierList/1',array('id' => 'carrierAddForm','style'=>'display:none;'));
    echo form_fieldset('Partner Add');
    
    $nData = array('name'=> 'carrierNameNew','id'=> 'carrierNameNew','size'=> 20,'class'=>'formInput','value'=>'');
    echo " <p class='formP'> <label for='carrierNameNew' class='formLabel'> Partner Name </label>";
      echo form_input($nData);
      echo '<span id="carrierNameVerify" class="verify"></span>';
    echo '</p>';

    echo '<p class="formP">';
      echo form_label('Description','carrierDescriptionNew',array('class' => 'formLabel'));
      echo form_textarea(array('name' => 'carrierDescriptionNew', 'id' => 'carrierDescriptionNew', 'class' =>'formInput','rows' => 5, 'cols'=>30));
      echo '<span id="carrierDescriptionVerify" class="verify"></span>';
    echo '</p>';

    echo '<p>';
        $buttonData = array('name' => 'buttonCarrierAdd','id' => 'buttonCarrierAdd',
          'class' => 'formButton','content' => 'Submit',
          'type'=>'submit','value'=>'true');
        echo form_button($buttonData);
    echo '</p>';

    echo form_fieldset_close();
    echo form_close();
    
    // insert form create, kept hidden - ends
  }
  


	if(count($carrierListView))
  	{
		
      // generate table data
		  $tmpl = array (
        'table_open'  => '<table  cellpadding="1" cellspacing="1" class="display" id="carrierListView">',

                  
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
  		$this->table->set_heading('Partner Name','Description');
      // Hidden Id will contain userId
      // Hidden Active will contain userActivationMailSent; if it is 0 then user is not active, else active
    	foreach($carrierListView as $list)
  		{
  			if($editAccess)
        {
           $carrierNameInput = form_input(array('name' => 'carrierNameInput-'.$list['carrierNameId'], 'id' => 'carrierNameInput-'.$list['carrierNameId'], 
            'class' => 'formInput','size' => 15,'style'=> 'display:none;','value' =>$list['carrierName']));
        
          $list['carrierName'] = '<div id="carrierNameText-'.$list['carrierNameId'].'">'.ucfirst($list['carrierName']).'</div>'.$carrierNameInput;

        
          $carrierDescriptionInput= form_textarea(array('name' => 'carrierDescriptionInput-'.$list['carrierNameId'], 
            'id' => 'carrierDescriptionInput-'.$list['carrierNameId'], 'style' => 'display:none;',
            'class' =>'formInput','rows' => 5, 'cols'=>30,'value' =>$list['carrierDescription']));

          $list['carrierDescription'] = '<div id="carrierDescriptionText-'.$list['carrierNameId'].'">'.$list['carrierDescription'].'</div>'.$carrierDescriptionInput;
        }
        else
        {
          $list['carrierName'] = ucfirst($list['carrierName']);
        }

        $row_data = array( 
     			$list['carrierName'],
          $list['carrierDescription']
   			);
               
        $this->table->add_row(array('data'=>$row_data, 'id'=> $list['carrierNameId']));
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
		carrierListPhp();
	});
</script>

<?php
/* End of file carrierList.php */
/* Location: ./application/views/carrierList.php */	