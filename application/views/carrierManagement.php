<?php
	echo '<p class="oprtMessage" style="width:100%;"></p>';
  if($insertAccess == 1) // if user has insert access
  {
    echo  '<p id="carrierActivateAdd"><img src="/images/carriers.png" alt="Activate Partner" title="Activate Partner" height="25px" width="25px">Activate Partner</p>';
  
    // insert form create, kept hidden
    echo form_open(base_url().'alveron/carrierManagement/1',array('id' => 'carrierActivateAddForm','style'=>'display:none;'));
    echo form_fieldset('Partner Activate');
    
    echo '<p class="formP">';
      $attr =  'id="carrierTypeNew" class="formList"';
      echo form_label('Partner Type','carrierTypeNew',array('class' => 'formLabel'));
      echo form_dropdown('carrierTypeNew',$carrierTypeList,'',$attr);
    echo '</p>';

    echo '<p class="formP">';
      $attr =  'id="carrierNameNew" class="formList"';
      echo form_label('Partner Name','carrierNameNew',array('class' => 'formLabel'));
      echo form_dropdown('carrierNameNew',$carrierNameList,'',$attr);
    echo '</p>';

    echo '<p class="formP">';
      $attr =  'id="trafficTypeNew" class="formList"';
      echo form_label('Traffic Type','trafficTypeNew',array('class' => 'formLabel'));
      echo form_multiselect('trafficTypeNew[]',$trafficTypeList,'',$attr);
    echo '</p>';

   

    echo '<p>';
        $buttonData = array('name' => 'buttonCarrierActivateAdd','id' => 'buttonCarrierActivateAdd',
          'class' => 'formButton','content' => 'Activate',
          'type'=>'submit','value'=>'true');
        echo form_button($buttonData);
    echo '</p>';

    echo form_fieldset_close();
    echo form_close();
    
    // insert form create, kept hidden - ends 
  }  
  

  // inline search option
  $searchOptionList = array('0'=>'Select Search Option', '1'=>'Partner Type','2'=>'Traffic Type','3'=>'Partner Status');
  echo '<p class="formP" >';
    $attr =  'id="searchOption" class="formList"';
    echo form_label('Search by','searchOption',array('class' => 'formLabel'));
    echo form_dropdown('searchOption',$searchOptionList,$searchOptionSelected,$attr);
  echo '</p>';
  
  echo '<p class="formP" id="hiddenCarrierTypeSearch"  style="display:none;">';
    $attr =  'id="carrierTypeSearch" class="formList"';
    echo form_label('Partner Type','carrierTypeSearch',array('class' => 'formLabel'));
    echo form_dropdown('carrierTypeSearch',$carrierTypeListSearch,$carrierTypeIdSelected,$attr);
  echo '</p>';

  echo '<p class="formP" id="hiddenTrafficTypeSearch"  style="display:none;">';
    $attr =  'id="trafficTypeSearch" class="formList"';
    echo form_label('Traffic Type','trafficTypeSearch',array('class' => 'formLabel'));
    echo form_dropdown('trafficTypeSearch',$trafficTypeListSearch,$trafficTypeIdSelected,$attr);
  echo '</p>';

  echo '<p class="formP" id="hiddenCarrierStatusSearch"  style="display:none;">';
    $attr =  'id="carrierStatusSearch" class="formList"';
    echo form_label('Partner Status','carrierStatusSearch',array('class' => 'formLabel'));
    echo form_dropdown('carrierStatusSearch',$carrierStatusListSearch,$carrierStatusIdSelected,$attr);
  echo '</p>';  

  // inline search option ends

	if(count($carrierManagementView))
  	{
		
      // generate table data
		  $tmpl = array (
        'table_open'  => '<table  cellpadding="1" cellspacing="1" class="display" id="carrierManagementView">',

                  
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
  		$this->table->set_heading('Partner Type','Traffic Type', 'Partner Name','Status');
      // Hidden Id will contain userId
      // Hidden Active will contain userActivationMailSent; if it is 0 then user is not active, else active
    	//$carrierStatusList = array('1'=>1,'0'=>0);
      foreach($carrierManagementView as $list)
  		{
  			

  			$list['carrierType'] = ucfirst($list['carrierType']);
        $list['trafficType'] = ucfirst($list['trafficType']);
      	
      	$list['carrierName'] = ucfirst($list['carrierName']);

        $list['carrierStatusId'] = $list['carrierStatusId'];
      	
        if($editAccess ==1)
        {
          $attr =  'class="formList" style="display:none" id="carrierStatusSelectBox-'.$list['carrierId'].'"';
          $carrierStatusDropdownList = form_dropdown('carrierStatusSelectBox-'.$list['carrierId'],$carrierStatusList,$list['carrierStatusId'],$attr);
          $list['carrierStatusId'] = '<div id="carrierStatus-'.$list['carrierId'].'"title="1 means Active, 0 means Not Active">'.$list['carrierStatusId'].'</div>'.$carrierStatusDropdownList;
        }        
        


        $row_data = array( 
     			$list['carrierType'],
          $list['trafficType'],
          $list['carrierName'],
          $list['carrierStatusId']
   			);
               
        $this->table->add_row(array('data'=>$row_data, 'id'=> $list['carrierId']));
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
		carrierManagementPhp();
	});
</script>

<?php
/* End of file carrierManagement.php */
/* Location: ./application/views/carrierManagement.php */	