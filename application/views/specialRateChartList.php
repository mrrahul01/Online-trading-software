<?php
  //echo count($vendorListSelect);
	echo '<p class="oprtMessage" style="width:100%"></p>';
 
  if($carrierRateChartInfo != null)
  {
    // edit form
    foreach($carrierRateChartInfo as $crInfoRate)
    {
      $carrierTypeEdit = $crInfoRate['carrierType'];
      $carrierNameEdit = $crInfoRate['carrierName'];
      $rateUploadDate = $crInfoRate['rateUploadDate'];
      $trafficTypeEdit = $crInfoRate['trafficTypeId'];
      $carrierIdEdit = $crInfoRate['carrierId'];

    }
    echo form_open(base_url().'alveron/specialRateChartTrafficTypeEdit',array('id' => 'editForm','style'=>'display:none;'));
    echo form_fieldset('Traffic Type Edit');

    echo '<p class="formP">';
      $rateUploadDateData =  array('name'=>'rateUploadDate', 'id'=>'rateUploadDate','value'=>$rateUploadDate,'size'=>'20','readonly'=>'true');
      echo form_label('Uplaod Date','rateUploadDate',array('class' => 'formLabel'));
      echo form_input($rateUploadDateData);
    echo '</p>';

    echo '<p class="formP">';
      $carrierTypeData =  array('name'=>'carrierTypeEdit', 'id'=>'carrierTypeEdit','value'=>$carrierTypeEdit,'size'=>'20','readonly'=>'true');
      echo form_label('Partner Type','carrierTypeEdit',array('class' => 'formLabel'));
      echo form_input($carrierTypeData);
    echo '</p>';

    echo '<p class="formP">';
      $carrierNameData =  array('name'=>'carrierNameEdit', 'id'=>'carrierNameEdit','value'=>strtoupper($carrierNameEdit),'size'=>'20','readonly'=>'true');
      echo form_label('Partner Name','carrierNameEdit',array('class' => 'formLabel'));
      echo form_input($carrierNameData);
    echo '</p>';

    echo '<p class="formP">';
      $attr =  'id="trafficTypeEdit" class="formList"';
      echo form_label('Traffic Type','trafficTypeEdit',array('class' => 'formLabel'));
      echo form_dropdown('trafficTypeEdit',$trafficTypeListForUpdate,$trafficTypeEdit,$attr);
    echo '</p>';

    echo '<p><input type="hidden" id="carrierIdEdit" name="carrierIdEdit" value="'.$carrierIdEdit.'"></p>';
    echo '<p><input type="hidden" id="trafficTypeOld" name="trafficTypeOld" value="'.$trafficTypeEdit.'"></p>';

    echo '<p>';
      $buttonData = array('name' => 'buttonEdit','id' => 'buttonEdit',
      'class' => 'formButton','content' => 'Update',
      'type'=>'submit','value'=>'true');
    echo form_button($buttonData);
        
    echo '</p>';

    echo form_fieldset_close();
    echo form_close();  

    // edit form ends
  }
  
  
  echo '<div id="rateChartListTable">';
  	   
  // inline search option
  
  echo '<p class="formP" >';
    $attr =  'id="searchOption" class="formList"';
    $searchOptionList = array('0'=>'Select Search Option', '1'=>'Partner Type','2'=>'Traffic Type','3'=>'Partner Name');
    echo form_label('Search by','searchOption',array('class' => 'formLabel'));
    echo form_dropdown('searchOption',$searchOptionList,$searchOptionSelected,$attr);
  echo '</p>';
  
  echo '<p class="formP" id="hiddenCarrierTypeSearch"  style="display:none;">';
    $attr =  'id="carrierTypeSearch" class="formList"';
    echo form_label('Partner Type','carrierTypeSearch',array('class' => 'formLabel'));
    echo form_dropdown('carrierTypeSearch',$carrierTypeList,$carrierTypeIdSelected,$attr);
  echo '</p>';

  echo '<p class="formP" id="hiddenTrafficTypeSearch"  style="display:none;">';
    $attr =  'id="trafficTypeSearch" class="formList"';
    echo form_label('Traffic Type','trafficTypeSearch',array('class' => 'formLabel'));
    echo form_dropdown('trafficTypeSearch',$trafficTypeList,$trafficTypeIdSelected,$attr);
  echo '</p>';

  echo '<p class="formP" id="hiddenCarrierNameSearch"  style="display:none;">';
    $attr =  'id="carrierNameSearch" class="formList"';
    echo form_label('Partner Name','carrierNameSearch',array('class' => 'formLabel'));
    echo form_dropdown('carrierNameSearch',$carrierNameList,$carrierNameIdSelected,$attr);
  echo '</p>';

  // inline search option ends

  if($rateChartListView)
  {
    echo '<div class="updateTrafficTypeAccess" style="display:none;" id="'.$updateTrafficTypeAccess.'"></div>';
    echo '<div class="deleteRateChartAccess" style="display:none;" id="'.$deleteRateChartAccess.'"></div>';
    
	  $tmpl = array (
      'table_open'  => '<table  cellpadding="1" cellspacing="1" class="display" id="rateChartListView">',

                
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
      
  	$this-> table-> set_template($tmpl);
    $this-> table-> set_empty("-");
      
    $headerArray = array('Partner Type','Partner Name', 'Traffic Type','Upload Date');
    if($updateTrafficTypeAccess == 1)
      array_push($headerArray,'Update');
    if($deleteRateChartAccess == 1)
      array_push($headerArray,'Delete');
      
    $this-> table-> set_heading($headerArray);

    foreach($rateChartListView as $list)
		{
	    $rowData = array();
      $rowData['carrierType'] = $list['carrierType'];
      $rowData['carrierName'] = strtoupper($list['carrierName']);
      $rowData['trafficType'] = $list['trafficType'];
      $rowData['rateUploadDate'] = $list['rateUploadDate'];
      if($updateTrafficTypeAccess == 1)
      {
        $url = base_url().'alveron/specialRateChartTrafficTypeEdit/'.$list['carrierId'].'/'.$list['rateUploadDate'].'/';
        //$url .= $searchOptionSelected.'/'.$carrierTypeIdSelected.'/'.$trafficTypeIdSelected.'/'.$carrierNameIdSelected;
        $rowData['update'] = anchor($url,'<img src="'.base_url().'images/edit.png" height="15px" width="15px">');
      }
        
      if($deleteRateChartAccess == 1)
      {
        $url = base_url().'alveron/specialRateChartDelete/'.$list['carrierId'].'/'.$list['rateUploadDate'].'/';
        $url .= $searchOptionSelected.'/'.$carrierTypeIdSelected.'/'.$trafficTypeIdSelected.'/'.$carrierNameIdSelected;
        $rowData['delete'] = anchor($url,'<img src="'.base_url().'images/delete.png" height="15px" width="15px">');
      }
        
      $this-> table-> add_row(array('data'=>$rowData, 'id'=> $list['carrierId']));
		}
  	$table = $this-> table-> generate();
  	echo $table;
  }
   

  echo '</div>';

?>

<script>
	$(function()
  {
		specialRateChartListPhp();
	});
</script>

<?php
/* End of file rateChartList.php */
/* Location: ./application/views/rateChartList.php */	