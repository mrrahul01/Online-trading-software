<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='utf-8'>
		<title><?php echo $title;?></title>
		<input class='baseUrl' value='<?php echo base_url();?>' type='hidden' />
		<!-- Style Sheets-->
		<link rel="stylesheet" href="<?php echo base_url();?>css/alveron.css">
		<link rel="stylesheet" href="<?php echo base_url();?>css/menu.css">
		<link rel="stylesheet" href="<?php echo base_url();?>css/login.css">
		<link rel="stylesheet" href="<?php echo base_url();?>css/jquery.datetimepicker.css">
		<!-- js files -->
		<script src="<?php echo base_url();?>js/jquery-1.9.1.min.js"></script>
		<script src="<?php echo base_url();?>js/jquery.easing.1.3.js"></script>
		<script src="<?php echo base_url();?>js/jquery.datetimepicker.js"></script>
		<script src="<?php echo base_url();?>js/global.js"></script>
		<script src="<?php echo base_url();?>js/ajaxfileupload.js"></script>
		<noscript> Javascript is not enabled! Please turn on Javascript to use this site </noscript>
		<script type="text/javascript">
			// <![CDATA[
			base_url = '<?php echo base_url();?> ';
			//]]>
		</script>
	</head>	
	<body>
		
        <header><?php if(!IS_AJAX) $this-> load-> view('header');?></header>
		<div id='wrapper'>
			<?php
				if(!IS_AJAX && $userId)
					$this-> load-> view('navbar');
			?>
			<div id='main'><?php $this-> load-> view($main);?></div>	
		</div>
		<footer><?php if(!IS_AJAX) $this-> load-> view('footer'); ?> </footer>
	</body>
</html>

<?php
/* End of file template.php */
/* Location: ./application/views/template.php */	