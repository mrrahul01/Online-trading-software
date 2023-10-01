<style>
  #rateSearch{
    float: left;
    margin-right: 25px;
    margin-top: 0px;
  }
  p img{
    float: left;
    margin-right: 150px;
      
    box-shadow:inset 0px 0px 9px -24px #cae3fc;
    background-color:#79bbff;
    text-indent:0;
    border:1px solid #469df5;
    display:inline-block;
    color:#ffffff;
    font-weight:bold;
    font-style:normal;
    
    text-decoration:none;
    text-align:center;
    text-shadow:1px 8px 7px #287ace;
    margin-top: 10px;
  }
</style>
<?php
  //echo count($vendorListSelect);
	echo '<p class="oprtMessage" style="width:100%;"></p>';
  
  	
  if($operation == 0)
  {
    // search form create, kept hidden
    if($carrierTypeListSelect ) // check whether carrier/partner types are available
    {
      if($trafficTypeListSelect) // check whether traffic types are available
      {
        echo form_open(base_url().'alveron',array('id' => 'rateSearchForm'));
        echo form_fieldset('Search Criteria');
    
        echo '<p class="formP">';
          $attr =  'id="carrierTypeId" class="formList"';
          echo form_label('Partner Type','carrierTypeId',array('class' => 'formLabel'));
          echo form_dropdown('carrierTypeId',$carrierTypeListSelect,'',$attr);
        echo '</p>';
        
        echo '<p class="formP">';
          $attr =  'id="trafficTypeId" class="formList"';
          echo form_label('Traffic Type','trafficTypeId',array('class' => 'formLabel'));
          echo form_dropdown('trafficTypeId',$trafficTypeListSelect,'',$attr);
        echo '</p>';

        // Here Partner Name list will be appended from global.js, 
        //from global.js a function will be called to alveron.php named carrierListForRateCompare() via ajax
        echo '<p class="formP" id="hiddenSelect"></p>';
     
        echo '<p class="formP">';
          $countryData =  array('name'=>'country', 'id'=>'country','value'=>'','size'=>'20');
          echo form_label('Country','country',array('class' => 'formLabel'));
          echo form_input($countryData);
        echo '</p>';

        echo '<p class="formP">';
          $destinationData =  array('name'=>'destination', 'id'=>'prefix','value'=>'','size'=>'20');
          echo form_label('Destination','destination',array('class' => 'formLabel'));
          echo form_input($destinationData);
        echo '</p>';

        echo '<p class="formP">';
          $prefixData =  array('name'=>'prefix', 'id'=>'prefix','value'=>'','size'=>'20');
          echo form_label('Area Code','prefix',array('class' => 'formLabel'));
          echo form_input($prefixData);
        echo '</p>';

        echo '<p class="formP">';
          $searchDateTimeData =  array('name'=>'searchDateTime', 'id'=>'searchDateTime','value'=>$searchDateTime,'size'=>'20');
          echo form_label('Date Time','searchDateTime',array('class' => 'formLabel'));
          echo form_input($searchDateTimeData);
        echo '</p>';

        echo '<p>';
            $buttonData = array('name' => 'buttonRateSearch','id' => 'buttonRateSearch',
              'class' => 'formButton','content' => 'Search',
              'type'=>'submit','value'=>'true','disabled'=>'true');
            echo form_button($buttonData);
            
        echo '</p>';

        echo form_fieldset_close();
        echo form_close();  
      } 
      else // if $trafficTypeListSelect is null
      {
        echo '<p> No traffic type available</p>';
      }
      
    }
    else // if $carrierTypeListSelect is null
    {
      echo '<p> No partner type available</p>';
    }
    
  }
  // search form create, kept hidden - ends 

 elseif($operation == 1)
 {

  	
  
  echo '<div id="rateTable">';
  	   
    $columnCount = count($carrierList)*2+3;
    echo '<div id="columnCount" style="display:none;">'.$columnCount.'</div>'; // this value is transfered to global.js for table width management
    echo '<div id="downloadLink" style="display:none;margin-top:10px;"></div>'; // global.js will show download link here
    if($rateCompareView)
    {
      // generate table data
  	  $tmpl = array (
        'table_open'  => '<table  cellpadding="1" cellspacing="1" class="display" id="rateCompareView">',

                  
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
      // set the dynamic column header
      $headerArray = array('Country','Destination', 'Area Code');
      //$columnCount = 3;
      $carrierCount = 0;
      foreach ($carrierList as $carrier) 
      {
        
        array_push($headerArray,ucwords($carrier['carrierName']).' - '.ucwords($carrier['trafficType']));
        array_push($headerArray,'Effective From');
        //$columnCount++;
        $carrierCount++;
      }
      $this-> table-> set_heading($headerArray);

    	foreach($rateCompareView as $list)
  		{
  	    for($i=1;$i<=$carrierCount;$i++)
        {
          if($list['carrierRate'.$i] == 0) // coloring rate and effective date if rate is 0, which means that prefix for that partner is deleted
          {
            $list['carrierRate'.$i] = '<div style="background-color:#CD5C5C;">'.$list['carrierRate'.$i].'</div>';
            $list['carrierRateEffectiveFrom'.$i] = '<div style="background-color:#CD5C5C;">'.$list['carrierRateEffectiveFrom'.$i].'</div>';
          }
        }  
        $this-> table-> add_row(array('data'=>$list, 'id'=> $list['prefix']));
  		}
    	$table = $this-> table-> generate();
    	echo $table;
    }
   
  	else // if $vendorListSelect is NULL then it is view form
  	{
  		echo '<p> No data found</p>';
  	}

  echo '</div>';
}
?>

<script>
	$(function()
  {
		rateComparePhp();
	});
</script>

<?php
/* End of file rateCompare.php */
/* Location: ./application/views/rateCompare.php */	