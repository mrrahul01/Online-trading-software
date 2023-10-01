<?php
	echo '<p class="oprtMessage" style="width:100%;"></p>';
	
	$formAttr = array('id'=>'specialRateUploadForm');
	echo form_open_multipart(base_url().'alveron/spacialRateUpload/1',$formAttr);
	echo "<fieldset><legend>Top/Push Chart Uplaod</legend>";
	
	
	echo '<p class="formP">';
	    $attr =  'id="carrierId" class="formList"';
	    echo form_label('Partner','carrierId',array('class' => 'formLabel'));
	    echo form_dropdown('carrierId',$carrierList,'',$attr);
  	echo '</p>';
  	
  	echo '<p class="formP">';
  		echo '<br>';
  	echo '</p>';
  	
  	echo '<p class="formP">';
	  	$fData = array('name'=> 'userfile','id'=> 'userfile','size'=> 20,'class'=>'formInput');
	  	echo "<label for='fileUpload' class='formLabel'> Top/Push Chart </label>";
		echo form_upload($fData);
	echo '</p>';

  	echo '<p>';
      $buttonData = array('name' => 'buttonSpecialRateUpload','id' => 'buttonSpecialRateUpload',
        'class' => 'formButton','content' => 'Upload',
        'type'=>'submit','value'=>'true');
      echo form_button($buttonData);
  	echo '</p>';

	
	echo '</fieldset>';
	echo '<div><a href="'.base_url().'uploads/sampleSpecial.xlsx"><strong>Download Sample File for Top/Push Chart Upload</strong></a> Max File Size is 2MB</div>';
	echo form_close();

	//echo '<h2 id="fileTitles">Files</h2>';
	echo '<div id="files"></div>';
	
?>
<script>
	$(function()
	{
		specialRateUploadPhp(); // go to global.js
	}); // ready ends
</script>


<?php

/* End of file specialRateUpload.php */
/* Location: ./application/views/specialRateUpload.php */	