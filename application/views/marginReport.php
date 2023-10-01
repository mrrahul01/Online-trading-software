<?php
 	echo '<p class="oprtMessage" style="width:100%;"></p>';
 
  if($operation == 0)
  {
    // show the search form
    if($activeCustomerList) // check whether any customer rate chart is there
    {
      
      echo form_open(base_url().'alveron/marginReport',array('id' => 'marginReportForm'));
      echo form_fieldset('Customer Select');
  
      echo '<p class="formP">';
        $attr =  'id="customerId" class="formList"';
        echo form_label('Customer Name','customerId',array('class' => 'formLabel'));
        echo form_dropdown('customerId',$activeCustomerList,'',$attr);
      echo '</p>';
      
      echo '<p class="formP">';
        $countryData =  array('name'=>'country', 'id'=>'country','value'=>'','size'=>'20');
        echo form_label('Country','country',array('class' => 'formLabel'));
        echo form_input($countryData);
      echo '</p>';

      echo '<p class="formP">';
        $prefixData =  array('name'=>'prefix', 'id'=>'prefix','value'=>'','size'=>'20');
        echo form_label('Area Code','prefix',array('class' => 'formLabel'));
        echo form_input($prefixData);
      echo '</p>';
      
      echo '<p>';
          $buttonData = array('name' => 'buttonMarginReport','id' => 'buttonMarginReport',
            'class' => 'formButton','content' => 'Generate Report',
            'type'=>'submit','value'=>'true');
          echo form_button($buttonData);
      echo '</p>';

      echo form_fieldset_close();
      echo form_close();  
    } 
    else // if no customer rate chart is found
    {
      echo '<p> No customer rate chart available</p>';
    }
  }
  elseif($operation == 1)
  {
    // show the view form
     echo '<div class="customerId" style="display:none;" id='.$customerId.'></div>';

      // inline search option
      
      $searchOptionList = array('0'=>'Select Search Option', '1'=>'Country','2'=>'Area Code');
      echo '<p class="formP" id="hiddenSearchOption">';
        $attr =  'id="searchOption" class="formList"';
        echo form_label('Search by','searchOption',array('class' => 'formLabel'));
        echo form_dropdown('searchOption',$searchOptionList,$searchOptionSelected,$attr);
      echo '</p>';
      
      echo '<p class="formP" id="hiddenCountrySearch"  style="display:none;">';
        $countryData =  array('name'=>'countrySearch', 'id'=>'countrySearch','value'=>$country,'size'=>'20');
        echo form_label('Country','countrySearch',array('class' => 'formLabel'));
        echo form_input($countryData);
      echo '</p>';

      echo '<p class="formP" id="hiddenPrefixSearch"  style="display:none;">';
        $prefixData =  array('name'=>'prefixSearch', 'id'=>'prefixSearch','value'=>$prefix,'size'=>'20');
        echo form_label('Area Code','prefixSearch',array('class' => 'formLabel'));
        echo form_input($prefixData);
      echo '</p>';
      // inline search option ends    
 

    if($marginReport)
    {
 

      echo '<div id="rateTable">';
        $columnCount = count($carrierList)+3;
        echo '<div id="columnCount" style="display:none;">'.$columnCount.'</div>'; // this value is transfered to global.js for table width management
        echo '<div id="downloadLink">'.$downloadLink.'</div>'; // global.js will show download link here
        // set the caption
        $tableCaption = '';
        $carrierCount = 0;
        foreach ($carrierList as $carrier) 
        {
          $tableCaption = 'Margin Report of <font color="Red">'.strtoupper($carrier['carrierName']).'</font> - <font color ="Green">'.strtoupper($carrier['trafficType']).'</font>';
          $carrierCount++;
          if($carrierCount == 1)
            break;
        }

        $subtitle = '[ Green = Profit, Red = Loss, Yellow = Break Even]';
        echo '<div align="left" style="margin-top:10px; margin-bottom:5px;"><strong>'.$tableCaption.'</strong>  '.$subtitle.'</div>';

        // generate table data
        $tmpl = array (
          'table_open'  => '<table  cellpadding="1" cellspacing="1" class="display" id="marginReportView">',

                    
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
        $headerArray = array('Country','Area Code');
        $carrierCount = 0;
        foreach ($carrierList as $carrier) 
        {
          if($carrierCount == 0) // customerName
            $carName = '<font color="red">'.ucwords($carrier['carrierName']).'</font>';
          else
            $carName = ucwords($carrier['carrierName']);
          array_push($headerArray,$carName);
          $carrierCount++;
        }
        $this-> table-> set_heading($headerArray);

        foreach($marginReport as $list)
        {
          $clientRate = 0;
          $dataList = array();
          
          $dataList['country'] = $list['country'];
          //$dataList['destination'] = $list['destination'];
          $dataList['prefix'] = $list['prefix'];
          $clientRate = 0;
          for($i=1;$i<=$carrierCount;$i++)
          {
            if($i==1)
            {
              $clientRate = $list['carrierRate'.$i];
              $dataList['carrierRate'.$i] = $list['carrierRate'.$i];
            }
            elseif($i>1 && $list['carrierRate'.$i] != "" )
            {
              if($list['carrierRate'.$i] < $clientRate) // profit
              {
                $dataList['carrierRate'.$i] = '<div style="background-color:#82FA58;">'.$list['carrierRate'.$i];   
              }
              elseif($list['carrierRate'.$i] > $clientRate) // loss
              {
                $dataList['carrierRate'.$i] = '<div style="background-color:#FA5858;">'.$list['carrierRate'.$i];    
              }
              else // break-even
              {
                $dataList['carrierRate'.$i] = '<div style="background-color:#F4FA58;">'.$list['carrierRate'.$i];    
              }
            }
            else
              $dataList['carrierRate'.$i] = $list['carrierRate'.$i];  
              

            
          }
          if($dataList['carrierRate1'] != '')
            $this-> table-> add_row(array('data'=>$dataList, 'id'=> $list['prefix']));
        }  

        $table = $this-> table-> generate();
        echo $table;

      echo '</div>'; // '<div id="rateTable">'
    }  // if($marginReport) ends
  }
 
?>

<script>
	$(function()
  {
		marginReportPhp();
	});
</script>

<?php
/* End of file marginReport.php */
/* Location: ./application/views/marginReport.php */	