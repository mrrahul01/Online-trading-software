<style>

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
  // inline search option
  echo '<div class="carrierId" style="display:none;" id='.$carrierId.'></div>';
  $searchOptionList = array('0'=>'Select Search Option', '1'=>'Country','2'=>'Area Code');
  echo '<p class="formP" id="hiddenSearchOption" style="display:none;">';
    $attr =  'id="searchOption" class="formList"';
    echo form_label('Search by','searchOption',array('class' => 'formLabel'));
    echo form_dropdown('searchOption',$searchOptionList,$searchOptionSelected,$attr);
  echo '</p>';
  
  echo '<p class="formP" id="hiddenCountrySearch"  style="display:none;">';
    $countryData =  array('name'=>'country', 'id'=>'country','value'=>$country,'size'=>'20');
    echo form_label('Country','country',array('class' => 'formLabel'));
    echo form_input($countryData);
  echo '</p>';

  echo '<p class="formP" id="hiddenPrefixSearch"  style="display:none;">';
    $prefixData =  array('name'=>'prefix', 'id'=>'prefix','value'=>$prefix,'size'=>'20');
    echo form_label('Area Code','prefix',array('class' => 'formLabel'));
    echo form_input($prefixData);
  echo '</p>';
  // inline search option ends 
  if($operation == 0)
  {
 
    if($carrierListSelect ) // check whether carrier/partner is available
    {
      
      echo form_open(base_url().'alveron/partnerRateChart',array('id' => 'rateChartSearchForm'));
      echo form_fieldset('Rate Chart Search');
  
      echo '<p class="formP">';
        $attr =  'id="carrierId" class="formList"';
        echo form_label('Partner Name','carrierId',array('class' => 'formLabel'));
        echo form_dropdown('carrierId',$carrierListSelect,'',$attr);
      echo '</p>';
      
      
      echo '<p>';
          $buttonData = array('name' => 'buttonRateChartSearch','id' => 'buttonRateChartSearch',
            'class' => 'formButton','content' => 'Search',
            'type'=>'submit','value'=>'true','disabled'=>'true');
          echo form_button($buttonData);
          
      echo '</p>';

      echo form_fieldset_close();
      echo form_close();  
    } 
    else // if $trafficTypeListSelect is null
    {
      echo '<p> No partner rate chart available</p>';
    }
      
  } // search form create, kept hidden - ends   
  

  elseif($operation == 1)
  {

    echo '<div id="rateTable">';
    echo '<div id="downloadLink" style="display:none;margin-top:10px;"></div>'; // global.js will show download link here
    if($partnerRateChartView)
    {
       foreach($carrierInfo as $list)
      {
        $tableCaption = 'Rate Chart of ['.$list['carrierType'].'] '.strtoupper($list['carrierName']).' - '.strtoupper($list['trafficType']);
        $tableCaption .= ' ( Upload Date: '.$rateUploadDate.' )';
      }
      echo '<div align="left"><strong>'.$tableCaption.'</strong>'.$subtitle.'</div>';

      // generate table data
  	  $tmpl = array (
        'table_open'  => '<table  cellpadding="1" cellspacing="1" class="display" id="partnerRateChartView">',

                  
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
     
      //$this-> table-> set_caption($tableCaption);
      // set the dynamic column header
      $headerArray = array('Country','Destination','Prefix','Rate','Effective From');
      //$columnCount = 3;
      
      $this-> table-> set_heading($headerArray);

    	foreach($partnerRateChartView as $list)
  		{
  	    if($list['rate'] == 0)
          $list['rate'] = '<div style="background-color:#CD5C5C;">'.$list['rate'].'</div>';
        
        $this-> table-> add_row(array('data'=>$list, 'id'=> $list['prefix']));
  		}
    	$table = $this-> table-> generate();
    	echo $table;
    }
   
  	else // if $partnerRateChartView is NULL then it is view form
  	{
  		echo '<p> No data found</p>';
  	}

  echo '</div>';
}
?>

<script>
	$(function()
  {
		partnerRateChartPhp();
	});
</script>

<?php
/* End of file partnerRateChart.php */
/* Location: ./application/views/partnerRateChart.php */	