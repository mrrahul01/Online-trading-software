<div id='menu'>
	<ul class='menu'>
		<li class='active current'><?php echo anchor('alveron','<span>Rate Compare</span>');?>
			<div>
				<ul>
					<li><?php echo anchor('alveron/specialRateCompare','<span>Top/Push Rate Compare</span>');?></li>
					<li><?php echo anchor('alveron/partnerRateChart','<span>Partner Rate Chart</span>');?></li>
					<li><?php echo anchor('alveron/partnerSpecialRateChart','<span>Partner Top/Push Chart</span>');?></li>
					<li><?php echo anchor('alveron/partnerRateHistory','<span>Partner Rate History</span>');?></li>
					<li><?php echo anchor('alveron/partnerSpecialRateHistory','<span>Partner Top/Push History</span>');?></li>
					
					

				</ul>
			</div>
		</li>
		<li><?php echo anchor('alveron/rateUpload','<span>Rate Chart Upload</span>');?>
			<div>
				<ul>					
					<li><?php echo anchor('alveron/spacialRateUpload','<span>Top/Push Chart Upload</span>');?></li>
					<li><?php echo anchor('alveron/rateChartList','<span>Rate Chart List</span>');?></li>
					<li><?php echo anchor('alveron/specialRateChartList','<span>Top/Push Chart List</span>');?></li>
					<li><?php echo anchor('alveron/marginReport','<span>Margin Report</span>');?></li>
				</ul>
			</div>
		</li>
		<li>
			<?php echo anchor('alveron/carrierManagement','<span>Partner-Traffic</span>');?>
			<div>
				<ul>
					<li><?php echo anchor('alveron/carrierList','<span>Partner List</span>');?></li>			
					<li><?php echo anchor('alveron/trafficTypeList','<span>Traffic Type List</span>');?></li>			
						
				</ul>
			</div>
		</li>
		<li>
			<?php echo anchor('alveron/userManagement','<span>Users</span>','class="parent"');?>
			<div>
				<ul>
					<li><?php echo anchor('alveron/userTypeManagement','<span>User Types</span>');?></li>
					<li><?php echo anchor('alveron/userTypeAccessManagement','<span>User Type Access</span>');?></li>
					<li><?php echo anchor('alveron/userPasswordReset','<span>User Password Reset</span>');?></li>

				</ul>
			</div>
		</li>
		<li><?php echo anchor('alveron/accountManagement','<span>My Account</span>');?></li>
		<li class='last'><?php echo anchor('alveron/signOutUser','<span>Sign Out</span>');?></li>
		<li><span><font color='white'>Welcome <?php echo ucfirst($this-> session-> userdata('userName'));?></font></span></li>
	</ul>
</div>
<script>
	$(function(){
		navbarPhp();
	});
</script>
<?php
/* End of file navbar.php */
/* Location: ./application/views/navbar.php */	