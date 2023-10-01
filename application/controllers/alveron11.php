<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Alveron extends CI_Controller 
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/myBlog
	 *	- or -  
	 * 		http://example.com/index.php/myBlog/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */


	
	public function Alveron()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Dhaka');
		$this-> load-> model('MUsers','',TRUE);
		$this-> load-> model('MCarriers','',TRUE);
		$this-> load-> model('MRates','',TRUE);
		set_time_limit(0);
		ini_set('memory_limit','2048M');
	}

	
	// navBar link pages controller functions 
	/**********************************************************************************************************/
	
	public function index() 
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userName = $this-> session-> userdata('userName');
		$data = array();
		$userActionId = 1; // rate chart view
		if($userLoggedId > 0) // if user is logged in
		{
			
			$data['userId'] = $userLoggedId;
			$data['title'] = 'Alveron | Rate Compare';
			$data['main'] = 'rateCompare';
			//if($this-> input-> post('searchDateTime'))
			if(($this-> input-> post('searchDateTime') || $this-> input-> post('carrierId') || $this-> input-> post('prefix') || 
				$this-> input-> post('carrierTypeId') || $this-> input-> post('trafficTypeId') || $this-> input-> post('country') 
				|| $this-> input-> post('destination'))) // if search button is submitted, then view form has to be shown
			{
				
				$this-> load-> library('table');
				$data['searchDateTime'] = $this-> input-> post('searchDateTime');
				//$data['carrierListSelect'] = null;
				$data['carrierTypeListSelect'] = null;
				$data['trafficTypeListSelect'] = null;
				$data['carrierList'] = $this-> MRates-> getCarrierMaxStartDateTime($data['searchDateTime'],
					$this-> input-> post('carrierId'),
					$this-> input-> post('carrierTypeId'),
					$this-> input-> post('trafficTypeId')
					); // this function will give the carrier list
				$data['operation'] = 1; // view form
				if($data['carrierList'])
				{
					$data['rateCompareView'] = $this-> MRates-> rateCompare(
						$data['carrierList'],
						trim($this-> input-> post('prefix')),
						trim($this-> input-> post('country')),
						trim($this-> input-> post('destination')),
						$data['searchDateTime']
					);
					//var_dump($data['rateCompareView']);
					
					//$feedback = 1;
					if($data['rateCompareView'] == null)
					{
						$downloadLink = null;
						$feedback = 0;
					}
					elseif($data['rateCompareView'])
					{
						// writing the excel file
						$this-> load-> library('excel');
						$objPHPExcelWrite = new PHPExcel();
						$objWorksheetWrite = $objPHPExcelWrite->setActiveSheetIndex(0);
						
						$headerArray = array('Country','Destination','Prefix');
						$columnCount = 3;
						foreach ($data['carrierList'] as $carrier) // dynamically get the header column names
						{
							array_push($headerArray,ucfirst($carrier['carrierName']).' - '.strtoupper($carrier['trafficType']).'- Rate');
							array_push($headerArray,ucfirst($carrier['carrierName']).' - '.strtoupper($carrier['trafficType']).'- Effective From');
							$columnCount++;
						}
										
						$objWorksheetWrite->fromArray($headerArray, NULL, 'A1'); // write the header columns

						$i = 2;
						foreach($data['rateCompareView'] as $list) // get the data
						{
							$index = 'A'.$i;
							//var_dump($list);
							$objWorksheetWrite->fromArray($list, NULL, $index); // write the data in the excel file
							$i++;	
						}
						
						$objWriter = IOFactory::createWriter($objPHPExcelWrite, 'Excel5');
		 
				        $fileName = 'RateComparisonReport_'.ucfirst($userName).'.xls';

				        // Sending headers to force the user to download the file
				        //    reset all output buffering
				    	while (ob_get_level() > 0) 
				    	{
				        	ob_end_clean();
				    	}
				        header('Content-Type: application/vnd.ms-excel');
				        header('Content-Disposition: attachment;filename="'.$fileName.'"');
				        header('Cache-Control: max-age=0');
				        // we can't send any more headers after this
					    flush();

				    	$objWriter->save('./uploads/'.$fileName);
				    	$location = base_url().'uploads/'.$fileName;
				    	
				    	// writing the excel file ends
				        $downloadLink = '<a href="'.$location.'"><img src="'.base_url().'images/download.png" height="20px" width="60px"></a>';
				        $feedback = 1;
					}
					else
					{
						$downloadLink = null;
						$feedback = 1;
						
					}
				}
				
				else // if carrierList is null
				{
					$downloadLink = null;
					$data['rateCompareView'] = null;
					$feedback = 2;
				}
				
				

			}
			else // by default always show the search form,  and also the search link will point here
			{
				
				//date_default_timezone_set('Asia/Dhaka');
				$data['searchDateTime'] = date('Y-m-d H').':00';
				//$data['carrierListSelect'] = null;//$this-> MRates-> getActiveCarrierList();
				$data['carrierTypeListSelect'] = $this-> MCarriers-> getCarrierTypeList();
				$data['trafficTypeListSelect'] = $this-> MCarriers-> getTrafficTypeList();

				$data['rateCompareView'] = null;
				$data['carrierList'] = null;
				$data['operation'] = 0; // search form
				$feedback = 1;
				$downloadLink = null;
				
			}
			//$searchDateTime = '2013-12-10 00:00:00';
			
			
		}
		else // if not logged in then place to log in form
		{
			$data['userId'] = null;
			$data['title'] = 'Alveron | Login';
			$data['main'] = 'login';
			$feedback  = 1;	
			$downloadLink = null;	
		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback'=> $feedback,'downloadLink' => $downloadLink,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
		}
		else
		{
			$this-> load-> vars($data);
			$this-> load-> view('template',$data);	
		}
		
	}	// index() ends


	public function carrierListForRateCompare()
	{
		$carrierTypeId = $this-> input-> post('carrierTypeId');
		$trafficTypeId = $this-> input-> post('trafficTypeId');
		$carrierListSelect = $this-> MRates-> getActiveCarrierList($carrierTypeId,$trafficTypeId);
		$htmlBody = '';
		if($carrierListSelect)
		{
			$feedback = 1;
			$attr =  'id="carrierId" class="formList"';
			$htmlBody .= form_label('Partner List','carrierId',array('class' => 'formLabel'));
			$htmlBody .= form_multiselect('carrierId[]',$carrierListSelect,'',$attr);
		}
		else
		{
			$feedback = 0;
			$nData = array('name'=> 'carrierId','id'=> 'carrierId','size'=> 20,'class'=>'formInput','value'=>'No Partner Found','readonly'=>'true','style'=>'background-color:orange;');
    		$htmlBody .= "<label for='carrierId' class='formLabel'> Partner List </label>";
      		$htmlBody .= form_input($nData);
	      
		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback' => $feedback, 'htmlBody' => $htmlBody));
		}
	}

	public function specialRateCompare() 
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userName = $this-> session-> userdata('userName');
		$data = array();
		$userActionId = 1; // rate chart view
		if($userLoggedId > 0) // if user is logged in
		{
			
			$data['userId'] = $userLoggedId;
			$data['title'] = 'Alveron | Top/Push Rate Compare';
			$data['main'] = 'specialRateCompare';
			//if($this-> input-> post('searchDateTime'))
			if(($this-> input-> post('searchDateTime') || $this-> input-> post('carrierId') || $this-> input-> post('prefix') || 
				$this-> input-> post('carrierTypeId') || $this-> input-> post('trafficTypeId') || $this-> input-> post('country') 
				|| $this-> input-> post('destination'))) // if search button is submitted, then view form has to be shown
			{
				
				$this-> load-> library('table');
				$data['searchDateTime'] = $this-> input-> post('searchDateTime');
				//$data['carrierListSelect'] = null;
				$data['carrierTypeListSelect'] = null;
				$data['trafficTypeListSelect'] = null;
				$data['carrierList'] = $this-> MRates-> getCarrierMaxSpecialStartDateTime($data['searchDateTime'],
					$this-> input-> post('carrierId'),
					$this-> input-> post('carrierTypeId'),
					$this-> input-> post('trafficTypeId')
					); // this function will give the carrier list
				$data['operation'] = 1; // view form
				if($data['carrierList'])
				{
					$data['rateCompareView'] = $this-> MRates-> specialRateCompare(
						$data['carrierList'],
						trim($this-> input-> post('prefix')),
						trim($this-> input-> post('country')),
						trim($this-> input-> post('destination')),
						$data['searchDateTime']
					);
					//var_dump($data['rateCompareView']);
					
					//$feedback = 1;
					if($data['rateCompareView'] == null)
					{
						$downloadLink = null;
						$feedback = 0;
					}
					elseif($data['rateCompareView'])
					{
						// writing the excel file
						$this-> load-> library('excel');
						$objPHPExcelWrite = new PHPExcel();
						$objWorksheetWrite = $objPHPExcelWrite->setActiveSheetIndex(0);
						
						$headerArray = array('Country','Destination','Prefix');
						$columnCount = 3;
						foreach ($data['carrierList'] as $carrier) // dynamically get the header column names
						{
							array_push($headerArray,ucfirst($carrier['carrierName']).' - '.strtoupper($carrier['trafficType']).'- Rate');
							array_push($headerArray,'ASR %');
							array_push($headerArray,'ACD Min');
							array_push($headerArray,'Effective From');
							$columnCount++;
						}
										
						$objWorksheetWrite->fromArray($headerArray, NULL, 'A1'); // write the header columns

						$i = 2;
						foreach($data['rateCompareView'] as $list) // get the data
						{
							$index = 'A'.$i;
							//var_dump($list);
							$objWorksheetWrite->fromArray($list, NULL, $index); // write the data in the excel file
							$i++;	
						}
						
						$objWriter = IOFactory::createWriter($objPHPExcelWrite, 'Excel5');
		 
				        $fileName = 'AggressiveRateComparisonReport_'.ucfirst($userName).'.xls';

				        // Sending headers to force the user to download the file
				        //    reset all output buffering
				    	while (ob_get_level() > 0) 
				    	{
				        	ob_end_clean();
				    	}
				        header('Content-Type: application/vnd.ms-excel');
				        header('Content-Disposition: attachment;filename="'.$fileName.'"');
				        header('Cache-Control: max-age=0');
				        // we can't send any more headers after this
					    flush();

				    	$objWriter->save('./uploads/'.$fileName);
				    	$location = base_url().'uploads/'.$fileName;
				    	
				    	// writing the excel file ends
				        $downloadLink = '<a href="'.$location.'"><img src="'.base_url().'images/download.png" height="20px" width="60px"></a>';
				        $feedback = 1;
					}
					else
					{
						$downloadLink = null;
						$feedback = 1;
						
					}
				}
				
				else // if carrierList is null
				{
					$downloadLink = null;
					$data['rateCompareView'] = null;
					$feedback = 2;
				}
				
				

			}
			else // by default always show the search form,  and also the search link will point here
			{
				
				//date_default_timezone_set('Asia/Dhaka');
				$data['searchDateTime'] = date('Y-m-d H').':00';
				//$data['carrierListSelect'] = null;//$this-> MRates-> getActiveCarrierList();
				$data['carrierTypeListSelect'] = $this-> MCarriers-> getCarrierTypeList();
				$data['trafficTypeListSelect'] = $this-> MCarriers-> getTrafficTypeList();

				$data['rateCompareView'] = null;
				$data['carrierList'] = null;
				$data['operation'] = 0; // search form
				$feedback = 1;
				$downloadLink = null;
				
			}
			//$searchDateTime = '2013-12-10 00:00:00';
			
			
		}
		else // if not logged in then place to log in form
		{
			$data['userId'] = null;
			$data['title'] = 'Alveron | Login';
			$data['main'] = 'login';
			$feedback  = 1;	
			$downloadLink = null;	
		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback'=> $feedback,'downloadLink' => $downloadLink,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
		}
		else
		{
			$this-> load-> vars($data);
			$this-> load-> view('template',$data);	
		}
		
	}	// index() ends


	public function carrierListForSpecialRateCompare()
	{
		$carrierTypeId = $this-> input-> post('carrierTypeId');
		$trafficTypeId = $this-> input-> post('trafficTypeId');
		$carrierListSelect = $this-> MRates-> getSpecialActiveCarrierList($carrierTypeId,$trafficTypeId);
		$htmlBody = '';
		if($carrierListSelect)
		{
			$feedback = 1;
			$attr =  'id="carrierId" class="formList"';
			$htmlBody .= form_label('Partner List','carrierId',array('class' => 'formLabel'));
			$htmlBody .= form_multiselect('carrierId[]',$carrierListSelect,'',$attr);
		}
		else
		{
			$feedback = 0;
			$nData = array('name'=> 'carrierId','id'=> 'carrierId','size'=> 20,'class'=>'formInput','value'=>'No Partner Found','readonly'=>'true','style'=>'background-color:orange;');
    		$htmlBody .= "<label for='carrierId' class='formLabel'> Partner List </label>";
      		$htmlBody .= form_input($nData);
	      
		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback' => $feedback, 'htmlBody' => $htmlBody));
		}
	}

	public function marginReport()
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userName = $this-> session-> userdata('userName');
		$data = array();
		if($userLoggedId > 0) // if user is logged in
		{
			$data['userId'] = $userLoggedId;
			$data['title'] = 'Alveron | Margin Report';
			$data['main'] = 'marginReport';
			
			if($this-> input-> post('customerId') || $this-> input-> post('country') || $this->input-> post('prefix')) // show margin report
			{
				$data['operation'] = 1;
				$data['customerId'] = $this-> input-> post('customerId');

				// inline search field 
			
				if($this-> input-> post('country'))
					$country = $this-> input-> post('country');
				else
					$country = '';
				$data['country'] = $country;
			
				if($this-> input-> post('prefix'))
					$prefix = $this-> input-> post('prefix');
				else
					$prefix = '';
				$data['prefix'] = $prefix;

				if($this-> input-> post('country'))
				{
					$data['searchOptionSelected'] = 1;
					
				}
				
				elseif($this-> input-> post('prefix'))
				{
					$data['searchOptionSelected'] = 2;
					
				}
				
				else
				{
					$data['searchOptionSelected'] = 0;
					
				}
					
			// inline search field

				$data['carrierList'] = $this-> MRates-> getCustomerAndVendorList($data['customerId']); // carrierId, carrierName,max(startDateTime)
				$data['marginReport'] = $this-> MRates-> marginReport($data['carrierList'],date('Y-m-d').' 23:59:59',$data['prefix'],$data['country']);
				if($data['marginReport'])
				{
					if($data['marginReport'] != -1)
					{
						$feedback = 1;
						$this-> load-> library('table');

						// writing the excel file
						$this-> load-> library('excel');
						$objPHPExcelWrite = new PHPExcel();
						$objWorksheetWrite = $objPHPExcelWrite->setActiveSheetIndex(0);
						
						$headerArray = array('Country','Destination','Prefix');
						$columnCount = 3;
						foreach ($data['carrierList'] as $carrier) // dynamically get the header column names
						{
							if($columnCount == 3)
								$carrierT = 'Customer - ';
							else
								$carrierT = '';
							array_push($headerArray,$carrierT.ucWords($carrier['carrierName']));
							$columnCount++;
						}

										
						$objWorksheetWrite->fromArray($headerArray, NULL, 'A1'); // write the header columns

						$i = 2;
						foreach($data['marginReport'] as $list) // get the data
						{
							$index = 'A'.$i;
							//var_dump($list);
							$objWorksheetWrite->fromArray($list, NULL, $index); // write the data in the excel file
							$i++;	
						}
						
						$objWriter = IOFactory::createWriter($objPHPExcelWrite, 'Excel5');
		 
				        $fileName = 'ClientMarginReport_'.ucfirst($userName).'.xls';

				        // Sending headers to force the user to download the file
				        //    reset all output buffering
				    	while (ob_get_level() > 0) 
				    	{
				        	ob_end_clean();
				    	}
				        header('Content-Type: application/vnd.ms-excel');
				        header('Content-Disposition: attachment;filename="'.$fileName.'"');
				        header('Cache-Control: max-age=0');
				        // we can't send any more headers after this
					    flush();

				    	$objWriter->save('./uploads/'.$fileName);
				    	$location = base_url().'uploads/'.$fileName;


						$data['downloadLink'] = '<a href="'.$location.'"><img src="'.base_url().'images/download.png" height="20px" width="60px"></a>';	
					}
					else
					{
						$feedback = -2;
						$data['marginReport'] = null;	
					}
					
				}
					
				else
					$feedback = 0;
			}
			else // show the search form
			{
				$data['operation'] = 0;
				$data['activeCustomerList'] = $this-> MRates-> getActiveCarrierList(1,0,1); //($carrierTypeId,$trafficTypeId,$selectAll)
				$feedback = 1;
			}
		}
		else // if not logged in then place to log in form
		{
			$data['userId'] = null;
			$data['title'] = 'Alveron | Login';
			$data['main'] = 'login';
			$feedback  = 1;	
			$downloadLink = null;	
		}

		if(IS_AJAX)
		{
			echo json_encode(array('feedback'=> $feedback,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
		}
		else
		{
			$this-> load-> vars($data);
			$this-> load-> view('template',$data);	
		}

	} // marginReport() ends

	public function partnerRateChart()
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userName = $this-> session-> userdata('userName');
		$data = array();
		$userActionId = 1; // rate chart view
		if($userLoggedId > 0) // if user is logged in
		{
			$data['userId'] = $userLoggedId;
			$data['title'] = 'Alveron | Partner Rate Chart';
			$data['main'] = 'partnerRateChart';
			
			// inline search field 
			$data['carrierId'] = $this-> input-> post('carrierId');
			if($this-> input-> post('country'))
				$country = $this-> input-> post('country');
			else
				$country = '';
			$data['country'] = $country;
			if($this-> input-> post('prefix'))
				$prefix = $this-> input-> post('prefix');
			else
				$prefix = '';
			$data['prefix'] = $prefix;

			if($this-> input-> post('country'))
			{
				$data['searchOptionSelected'] = 1;
				$data['subtitle'] = '  <font color="red" weight="normal">Search by Country - '.$country.' </font>';
				//$showSearchInput = 1;
			}
				
			elseif($this-> input-> post('prefix'))
			{
				$data['searchOptionSelected'] = 2;
				$data['subtitle'] = '  <font color="red" weight="normal">Search by Area Code - '.$prefix.' </font>';	
				//$showSearchInput = 1;
			}
				
			else
			{
				$data['searchOptionSelected'] = 0;
				$data['subtitle'] = '';
				//$showSearchInput = 0;
			}
				
			// inline search field

			
			if($this-> input-> post('carrierId') || $this-> input-> post('country') || $this-> input-> post('prefix')) // search button is pressed, show view form
			{
				
				$data['operation'] = 1; // it will show the view form
				$data['carrierListSelect'] = null;
				if($data['carrierId'] == 0)
				{
					$data['carrierInfo'] = null;
					$data['rateUploadDate'] = null;
					$data['partnerRateChartView'] = null;

					$feedback = 0; // if feedback == 0, set message in global.js
					$downloadLink = null;
					
					
				}
				else
				{
					$this-> load-> library('table');
					
					$data['carrierInfo'] = $this-> MCarriers-> getCarrierInfo($data['carrierId']);
					$data['rateUploadDate'] = $this-> MRates-> getPartnerLatestUploadDate($data['carrierId']);

					$data['partnerRateChartView'] = $this-> MRates-> getPartnerLatestRateChart($data['carrierId'],$data['rateUploadDate'],$country,$prefix);
					//$feedback = 1;

					if($data['partnerRateChartView'] == null)
					{
						$downloadLink = null;
						$feedback = -2; // -2 means no rate chart found
					}
					else
					{
						// write the excel file
						$this-> load-> library('excel');
						$objPHPExcelWrite = new PHPExcel();
						$objWorksheetWrite = $objPHPExcelWrite->setActiveSheetIndex(0);
						
						$headerArray = array('Country','Destination','Prefix','Rate','Effective From');

						$objWorksheetWrite->fromArray($headerArray, NULL, 'A1'); // write the header columns

						$i = 2;
						foreach($data['partnerRateChartView'] as $list) // get the data
						{
							$index = 'A'.$i;
							//var_dump($list);
							$objWorksheetWrite->fromArray($list, NULL, $index); // write the data in the excel file
							$i++;	
						}
						
						$objWriter = IOFactory::createWriter($objPHPExcelWrite, 'Excel5');
		 
				        $fileName = 'RateChart_'.ucfirst($userName).'.xls';

				        // Sending headers to force the user to download the file
				        //    reset all output buffering
				    	while (ob_get_level() > 0) 
				    	{
				        	ob_end_clean();
				    	}
				        header('Content-Type: application/vnd.ms-excel');
				        header('Content-Disposition: attachment;filename="'.$fileName.'"');
				        header('Cache-Control: max-age=0');
				        // we can't send any more headers after this
					    flush();

				    	$objWriter->save('./uploads/'.$fileName);
				    	$location = base_url().'uploads/'.$fileName;
				    	
				    	// writing the excel file ends
				        $downloadLink = '<a href="'.$location.'"><img src="'.base_url().'images/download.png" height="20px" width="60px"></a>';
				        $feedback = 1;

					}

				}
					
			}
			else // search form
			{
				$data['operation'] = 0;
				$data['carrierInfo'] = null;
				$data['rateUploadDate'] = null;
				$data['partnerRateChartView'] = null;
				$feedback = 1;
				$downloadLink = null;
				$data['carrierListSelect'] = $this-> MRates-> getActiveCarrierList(0,0,1);
			}

		}
		else // if not logged in then place to log in form
		{
			$data['userId'] = null;
			$data['title'] = 'Alveron | Login';
			$data['main'] = 'login';
			$feedback  = 1;	
			$downloadLink = null;	
		}
		
		if(IS_AJAX)
		{
			echo json_encode(array('feedback'=> $feedback,'downloadLink' => $downloadLink,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
		}
		else
		{
			$this-> load-> vars($data);
			$this-> load-> view('template',$data);	
		}
	} // partnerRateChart() ends

	public function partnerSpecialRateChart()
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userName = $this-> session-> userdata('userName');
		$data = array();
		$userActionId = 1; // rate chart view
		if($userLoggedId > 0) // if user is logged in
		{
			$data['userId'] = $userLoggedId;
			$data['title'] = 'Alveron | Top/Push Chart';
			$data['main'] = 'partnerSpecialRateChart';
			
				// inline search field 
			$data['carrierId'] = $this-> input-> post('carrierId');
			if($this-> input-> post('country'))
				$country = $this-> input-> post('country');
			else
				$country = '';
			$data['country'] = $country;
			if($this-> input-> post('prefix'))
				$prefix = $this-> input-> post('prefix');
			else
				$prefix = '';
			$data['prefix'] = $prefix;

			if($this-> input-> post('country'))
			{
				$data['searchOptionSelected'] = 1;
				$data['subtitle'] = '  <font color="red" weight="normal">Search by Country - '.$country.' </font>';
				//$showSearchInput = 1;
			}
				
			elseif($this-> input-> post('prefix'))
			{
				$data['searchOptionSelected'] = 2;
				$data['subtitle'] = '  <font color="red" weight="normal">Search by Area Code - '.$prefix.' </font>';	
				//$showSearchInput = 1;
			}
				
			else
			{
				$data['searchOptionSelected'] = 0;
				$data['subtitle'] = '';
				//$showSearchInput = 0;
			}
				
			// inline search field

			
			if($this-> input-> post('carrierId') || $this-> input-> post('country') || $this-> input-> post('prefix')) // search button is pressed, show view form
			{
				
				$data['operation'] = 1; // it will show the view form
				$data['carrierListSelect'] = null;
				if($data['carrierId'] == 0)
				{
					$data['carrierInfo'] = null;
					$data['rateUploadDate'] = null;
					$data['partnerRateChartView'] = null;

					$feedback = 0; // if feedback == 0, set message in global.js
					$downloadLink = null;
					
					
				}
				else
				{
					$this-> load-> library('table');
					
					$data['carrierInfo'] = $this-> MCarriers-> getCarrierInfo($data['carrierId']);
					$data['rateUploadDate'] = $this-> MRates-> getPartnerLatestSpecialUploadDate($data['carrierId']);

					$data['partnerRateChartView'] = $this-> MRates-> getPartnerLatestSpecialRateChart($data['carrierId'],$data['rateUploadDate'],$country,$prefix);
					//$feedback = 1;

					if($data['partnerRateChartView'] == null)
					{
						$downloadLink = null;
						$feedback = -2; // -2 means no rate chart found
					}
					else
					{
						// write the excel file
						$this-> load-> library('excel');
						$objPHPExcelWrite = new PHPExcel();
						$objWorksheetWrite = $objPHPExcelWrite->setActiveSheetIndex(0);
						
						$headerArray = array('Country','Destination','Prefix','Rate','ASR %','ACD Min','Effective From');

						$objWorksheetWrite->fromArray($headerArray, NULL, 'A1'); // write the header columns

						$i = 2;
						foreach($data['partnerRateChartView'] as $list) // get the data
						{
							$index = 'A'.$i;
							//var_dump($list);
							$objWorksheetWrite->fromArray($list, NULL, $index); // write the data in the excel file
							$i++;	
						}
						
						$objWriter = IOFactory::createWriter($objPHPExcelWrite, 'Excel5');
		 
				        $fileName = 'RateChart_'.ucfirst($userName).'.xls';

				        // Sending headers to force the user to download the file
				        //    reset all output buffering
				    	while (ob_get_level() > 0) 
				    	{
				        	ob_end_clean();
				    	}
				        header('Content-Type: application/vnd.ms-excel');
				        header('Content-Disposition: attachment;filename="'.$fileName.'"');
				        header('Cache-Control: max-age=0');
				        // we can't send any more headers after this
					    flush();

				    	$objWriter->save('./uploads/'.$fileName);
				    	$location = base_url().'uploads/'.$fileName;
				    	
				    	// writing the excel file ends
				        $downloadLink = '<a href="'.$location.'"><img src="'.base_url().'images/download.png" height="20px" width="60px"></a>';
				        $feedback = 1;

					}

				}
					
			}
			else // search form
			{
				$data['operation'] = 0;
				$data['carrierInfo'] = null;
				$data['rateUploadDate'] = null;
				$data['partnerRateChartView'] = null;
				$feedback = 1;
				$downloadLink = null;
				$data['carrierListSelect'] = $this-> MRates-> getSpecialActiveCarrierList(0,0,1);
			}

			

			
		}
		else // if not logged in then place to log in form
		{
			$data['userId'] = null;
			$data['title'] = 'Alveron | Login';
			$data['main'] = 'login';
			$feedback  = 1;	
			$downloadLink = null;	
		}
		
		if(IS_AJAX)
		{
			echo json_encode(array('feedback'=> $feedback,'downloadLink' => $downloadLink,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
		}
		else
		{
			$this-> load-> vars($data);
			$this-> load-> view('template',$data);	
		}
	} // partnerSpecialRateChart() ends
	


	public function partnerRateHistory()
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userName = $this-> session-> userdata('userName');
		$data = array();
		$userActionId = 1; // rate chart view
		if($userLoggedId > 0) // if user is logged in
		{
			
			$data['userId'] = $userLoggedId;
			$data['title'] = 'Alveron | Partner Rate History';
			$data['main'] = 'partnerRateHistory';
			//if($this-> input-> post('searchDateTime'))
			if(($this-> input-> post('searchDateTimeFrom') || $this-> input-> post('searchDateTimeTo')||$this-> input-> post('carrierId') || $this-> input-> post('prefix') || 
				$this-> input-> post('carrierTypeId') || $this-> input-> post('trafficTypeId') || $this-> input-> post('country') 
				|| $this-> input-> post('destination'))) // if search button is submitted, then view form has to be shown
			{
				
				$this-> load-> library('table');
				$data['searchDateTimeFrom'] = $this-> input-> post('searchDateTimeFrom');
				$data['searchDateTimeTo'] = $this-> input-> post('searchDateTimeTo');
				$data['carrierId'] = $this-> input-> post('carrierId');
				$data['carrierTypeListSelect'] = null;
				$data['trafficTypeListSelect'] = null;
				$data['carrierInfo'] = $this-> MCarriers-> getCarrierInfo($data['carrierId']);
				$data['operation'] = 1; // view form
				if($data['carrierId']>0)
				{
					$data['dateList'] = $this-> MRates-> getCarrierDateList($data['searchDateTimeFrom'],
						$data['searchDateTimeTo'],
						$this-> input-> post('carrierId')
					);
					
					if($data['dateList'] == null)
						$data['partnerRateHistoryView'] = null;
					else
						$data['partnerRateHistoryView'] = $this-> MRates-> getPartnerRateHistory(
							$data['carrierId'],$data['dateList'],
							trim($this-> input-> post('prefix')),
							trim($this-> input-> post('country')),
							trim($this-> input-> post('destination'))
						); // this function will return country,destination,prefix,rate, effectiveFrom
					//var_dump($data['rateCompareView']);
					
					//$feedback = 1;
					if($data['partnerRateHistoryView'] == null)
					{
						$downloadLink = null;
						$feedback = 0;
					}
					elseif($data['partnerRateHistoryView'])
					{
							// writing the excel file
						$this-> load-> library('excel');
						$objPHPExcelWrite = new PHPExcel();
						$objWorksheetWrite = $objPHPExcelWrite->setActiveSheetIndex(0);
						
						$headerArray = array('Country','Destination','Prefix','Rate','Effective From');
						
										
						$objWorksheetWrite->fromArray($headerArray, NULL, 'A1'); // write the header columns

						$i = 2;
						foreach($data['partnerRateHistoryView'] as $list) // get the data
						{
							$index = 'A'.$i;
							//var_dump($list);
							$objWorksheetWrite->fromArray($list, NULL, $index); // write the data in the excel file
							$i++;	
						}
						
						$objWriter = IOFactory::createWriter($objPHPExcelWrite, 'Excel5');
		 
				        $fileName = 'Rate_History_'.ucfirst($userName).'.xls';

				        // Sending headers to force the user to download the file
				        //    reset all output buffering
				    	while (ob_get_level() > 0) 
				    	{
				        	ob_end_clean();
				    	}
				        header('Content-Type: application/vnd.ms-excel');
				        header('Content-Disposition: attachment;filename="'.$fileName.'"');
				        header('Cache-Control: max-age=0');
				        // we can't send any more headers after this
					    flush();

				    	$objWriter->save('./uploads/'.$fileName);
				    	$location = base_url().'uploads/'.$fileName;
				    	
				    	// writing the excel file ends
				        $downloadLink = '<a href="'.$location.'"><img src="'.base_url().'images/download.png" height="20px" width="60px"></a>';
				        $feedback = 1;
					}
					
				}
				
				else // if carrierId is 0
				{
					$downloadLink = null;
					$data['partnerRateHistoryView'] = null;
					$feedback = 2;
					$data['dateList'] = null;
				}
				
				

			}
			else // by default always show the search form,  and also the search link will point here
			{
				
				//date_default_timezone_set('Asia/Dhaka');
				$data['searchDateTimeFrom'] = 
				$data['searchDateTimeTo'] = date('Y-m-d');
				//$data['carrierListSelect'] = null;//$this-> MRates-> getActiveCarrierList();
				$data['carrierTypeListSelect'] = $this-> MCarriers-> getCarrierTypeList();
				$data['trafficTypeListSelect'] = $this-> MCarriers-> getTrafficTypeList();

				$data['partnerRateHistoryView'] = null;
				
				$data['operation'] = 0; // search form
				$feedback = 1;
				$downloadLink = null;
				
			}
			
			
			
		}
		else // if not logged in then place to log in form
		{
			$data['userId'] = null;
			$data['title'] = 'Alveron | Login';
			$data['main'] = 'login';
			$feedback  = 1;	
			$downloadLink = null;	

		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback'=> $feedback,'downloadLink' => $downloadLink,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
		}
		else
		{
			$this-> load-> vars($data);
			$this-> load-> view('template',$data);	
		}
	} // partnerRateHistory() ends

	public function carrierListForPartnerRateHistory()
	{
		$carrierTypeId = $this-> input-> post('carrierTypeId');
		$trafficTypeId = $this-> input-> post('trafficTypeId');
		$carrierListSelect = $this-> MRates-> getActiveCarrierList($carrierTypeId,$trafficTypeId,1);
		$htmlBody = '';
		if($carrierListSelect)
		{
			$feedback = 1;
			$attr =  'id="carrierId" class="formList"';
			$htmlBody .= form_label('Partner List','carrierId',array('class' => 'formLabel'));
			$htmlBody .= form_dropdown('carrierId',$carrierListSelect,'',$attr);
		}
		else
		{
			$feedback = 0;
			$nData = array('name'=> 'carrierId','id'=> 'carrierId','size'=> 20,'class'=>'formInput','value'=>'No Partner Found','readonly'=>'true','style'=>'background-color:orange;');
    		$htmlBody .= "<label for='carrierId' class='formLabel'> Partner List </label>";
      		$htmlBody .= form_input($nData);
		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback' => $feedback, 'htmlBody' => $htmlBody));
		}

	} // carrierListForPartnerRateHistory() ends



	public function partnerSpecialRateHistory()
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userName = $this-> session-> userdata('userName');
		$data = array();
		$userActionId = 1; // rate chart view
		if($userLoggedId > 0) // if user is logged in
		{
			
			$data['userId'] = $userLoggedId;
			$data['title'] = 'Alveron | Top/Push Rate History';
			$data['main'] = 'partnerSpecialRateHistory';
			//if($this-> input-> post('searchDateTime'))
			if(($this-> input-> post('searchDateTimeFrom') || $this-> input-> post('searchDateTimeTo')||$this-> input-> post('carrierId') || $this-> input-> post('prefix') || 
				$this-> input-> post('carrierTypeId') || $this-> input-> post('trafficTypeId') || $this-> input-> post('country') 
				|| $this-> input-> post('destination'))) // if search button is submitted, then view form has to be shown
			{
				
				$this-> load-> library('table');
				$data['searchDateTimeFrom'] = $this-> input-> post('searchDateTimeFrom');
				$data['searchDateTimeTo'] = $this-> input-> post('searchDateTimeTo');
				$data['carrierId'] = $this-> input-> post('carrierId');
				$data['carrierTypeListSelect'] = null;
				$data['trafficTypeListSelect'] = null;
				$data['carrierInfo'] = $this-> MCarriers-> getCarrierInfo($data['carrierId']);
				$data['operation'] = 1; // view form
				if($data['carrierId']>0)
				{
					$data['dateList'] = $this-> MRates-> getCarrierSpecialDateList($data['searchDateTimeFrom'],
						$data['searchDateTimeTo'],
						$this-> input-> post('carrierId')
					);
					
					if($data['dateList'] == null)
						$data['partnerRateHistoryView'] = null;
					else
						$data['partnerRateHistoryView'] = $this-> MRates-> getPartnerSpecialRateHistory(
							$data['carrierId'],$data['dateList'],
							trim($this-> input-> post('prefix')),
							trim($this-> input-> post('country')),
							trim($this-> input-> post('destination'))
						); // this function will return country,destination,prefix,rate, effectiveFrom
					//var_dump($data['rateCompareView']);
					
					//$feedback = 1;
					if($data['partnerRateHistoryView'] == null)
					{
						$downloadLink = null;
						$feedback = 0;
					}
					elseif($data['partnerRateHistoryView'])
					{
							// writing the excel file
						$this-> load-> library('excel');
						$objPHPExcelWrite = new PHPExcel();
						$objWorksheetWrite = $objPHPExcelWrite->setActiveSheetIndex(0);
						
						$headerArray = array('Country','Destination','Prefix','Rate','ASR %','ACD Min','Effective From');
						
										
						$objWorksheetWrite->fromArray($headerArray, NULL, 'A1'); // write the header columns

						$i = 2;
						foreach($data['partnerRateHistoryView'] as $list) // get the data
						{
							$index = 'A'.$i;
							//var_dump($list);
							$objWorksheetWrite->fromArray($list, NULL, $index); // write the data in the excel file
							$i++;	
						}
						
						$objWriter = IOFactory::createWriter($objPHPExcelWrite, 'Excel5');
		 
				        $fileName = 'Rate_History_'.ucfirst($userName).'.xls';

				        // Sending headers to force the user to download the file
				        //    reset all output buffering
				    	while (ob_get_level() > 0) 
				    	{
				        	ob_end_clean();
				    	}
				        header('Content-Type: application/vnd.ms-excel');
				        header('Content-Disposition: attachment;filename="'.$fileName.'"');
				        header('Cache-Control: max-age=0');
				        // we can't send any more headers after this
					    flush();

				    	$objWriter->save('./uploads/'.$fileName);
				    	$location = base_url().'uploads/'.$fileName;
				    	
				    	// writing the excel file ends
				        $downloadLink = '<a href="'.$location.'"><img src="'.base_url().'images/download.png" height="20px" width="60px"></a>';
				        $feedback = 1;
					}
					
				}
				
				else // if carrierId is 0
				{
					$downloadLink = null;
					$data['partnerRateHistoryView'] = null;
					$feedback = 2;
					$data['dateList'] = null;
				}
				
				

			}
			else // by default always show the search form,  and also the search link will point here
			{
				
				//date_default_timezone_set('Asia/Dhaka');
				$data['searchDateTimeFrom'] = 
				$data['searchDateTimeTo'] = date('Y-m-d');
				//$data['carrierListSelect'] = null;//$this-> MRates-> getActiveCarrierList();
				$data['carrierTypeListSelect'] = $this-> MCarriers-> getCarrierTypeList();
				$data['trafficTypeListSelect'] = $this-> MCarriers-> getTrafficTypeList();

				$data['partnerRateHistoryView'] = null;
				
				$data['operation'] = 0; // search form
				$feedback = 1;
				$downloadLink = null;
				
			}
			
			
			
		}
		else // if not logged in then place to log in form
		{
			$data['userId'] = null;
			$data['title'] = 'Alveron | Login';
			$data['main'] = 'login';
			$feedback  = 1;	
			$downloadLink = null;	
		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback'=> $feedback,'downloadLink' => $downloadLink,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
		}
		else
		{
			$this-> load-> vars($data);
			$this-> load-> view('template',$data);	
		}
	} // partnerRateHistory() ends

	

	public function carrierListForPartnerSpecialRateHistory()
	{
		$carrierTypeId = $this-> input-> post('carrierTypeId');
		$trafficTypeId = $this-> input-> post('trafficTypeId');
		$carrierListSelect = $this-> MRates-> getSpecialActiveCarrierList($carrierTypeId,$trafficTypeId,1);
		$htmlBody = '';
		if($carrierListSelect)
		{
			$feedback = 1;
			$attr =  'id="carrierId" class="formList"';
			$htmlBody .= form_label('Partner List','carrierId',array('class' => 'formLabel'));
			$htmlBody .= form_dropdown('carrierId',$carrierListSelect,'',$attr);
		}
		else
		{
			$feedback = 0;
			$nData = array('name'=> 'carrierId','id'=> 'carrierId','size'=> 20,'class'=>'formInput','value'=>'No Partner Found','readonly'=>'true','style'=>'background-color:orange;');
    		$htmlBody .= "<label for='carrierId' class='formLabel'> Partner List </label>";
      		$htmlBody .= form_input($nData);
		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback' => $feedback, 'htmlBody' => $htmlBody));
		}

	} // carrierListForPartnerSpecialRateHistory() ends

	public function rateUpload($operations = 0)
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$userActionId = 2; // 2 means Carrier Rate Upload
		$data['userId'] = $userLoggedId;
		$data['title'] = 'Alveron | Rate Chart Upload';
		$data['main'] = 'rateUpload';

		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);
		if($userAccessValue == 1)
		{

			$data['carrierList'] = $this-> MCarriers-> getCarrierListWithTrafficTypeAndCarrierType();
			// view the upload form
			if($operations == 0)
			{
				$this-> load-> vars($data);
			
				if(IS_AJAX)
				{
					echo json_encode(array('feedback' => 1,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
				}
				else
				{
					$this-> load-> view('template',$data);
				}	
			}

			// what have to do when upload button is pressed
			else if($operations == 1)
			{
				$fileElementId = 'userfile';
				$resultData = $this-> uploadFiles($fileElementId);
				//echo $uploadFileResult['upload_data'];
				if($resultData['status'] == 'success')
				{
					$carrierId = $this-> input-> post('carrierId');
					$fullPath = $resultData['uploadData']['full_path'];
					$filePath = $resultData['uploadData']['file_path'];
					$fileExtension = $resultData['uploadData']['file_ext'];
					$fileName = $resultData['uploadData']['file_name'];
					$rawName = $resultData['uploadData']['raw_name'];
					
					// read excel file

					// load new PHPExcel library
					
					$this-> load-> library('excel');
					
					if($fileExtension == '.xls')
						$excelFileType = 'Excel5';
					elseif($fileExtension == '.xlsx')
						$excelFileType = 'Excel2007';

					$fileNameNew = 'New'.$fileName; 
					
					//error_reporting(E_ALL);
					//set_time_limit(0);
					//ini_set("memory_limit", "512M"); // ini_set("memory_limit", "512M") or something else higher than 128M
					
					//$objReader = IOFactory::createReader($excelFileType); 
					$objReader = IOFactory::createReaderForFile($fullPath);
					$objReader->setReadDataOnly(true); // set this, to not read all excel properties, just data
					// to do this the className has to be made IOFactory instead of PHPExcel_IOFactory
					
					
					$objPHPExcel = $objReader-> load($fullPath);
					
					$objWorksheet = $objPHPExcel-> setActiveSheetIndex(0); // set 1st sheet to be active
					// Note: In PHPExcel column index is 0-based while row index is 1-based. That means 'A1' ~ (0,1)

					// Get the highest row number referenced in the worksheet
					$rowCount = $objWorksheet->getHighestRow(); // total number or dial code + 1 for header
					$maxColumn = $objWorksheet->getHighestColumn();  // 'F'
					$columnCount = PHPExcel_Cell::columnIndexFromString($maxColumn); // in this case it will be 6,5, or 4
					
					$data = array();
										
					$j = 1;
					for($i=2; $i<=$rowCount; $i++,$j++)
					{
						$country = $objWorksheet-> getCellByColumnAndRow(0,$i)-> getCalculatedValue();
						$destination = $objWorksheet-> getCellByColumnAndRow(1,$i)-> getCalculatedValue();
						$prefix = $objWorksheet-> getCellByColumnAndRow(2,$i)-> getCalculatedValue(); // try using getCalculatedValue
						$rate = $objWorksheet-> getCellByColumnAndRow(3,$i)-> getCalculatedValue();
												
						if($columnCount >= 6)
						{
							$startDateTime = PHPExcel_Shared_Date::ExcelToPHPObject($objWorksheet->getCellByColumnAndRow(4, $i)->getCalculatedValue())->format('Y-m-d H:i:s');
							$endDateTime = PHPExcel_Shared_Date::ExcelToPHPObject($objWorksheet->getCellByColumnAndRow(5, $i)->getCalculatedValue())->format('Y-m-d H:i:s');
						}
						elseif($columnCount == 5)
						{
							$startDateTime = PHPExcel_Shared_Date::ExcelToPHPObject($objWorksheet->getCellByColumnAndRow(4, $i)->getCalculatedValue())->format('Y-m-d H:i:s');
							$endDateTime = '2100-12-31 23:59:59';
						}
						elseif($columnCount == 4)
						{
							
							$startDateTime = date('Y-m-d').' 00:00:00';	
							$endDateTime = '2100-12-31 23:59:59';
						}
						$data[$j] = array();

						$data[$j][0] = $carrierId;
						$data[$j][1] = $country;
						$data[$j][2] = $destination;
						$data[$j][3] = $prefix;
						$data[$j][4] = $rate;
						$data[$j][5] = $startDateTime;
						$data[$j][6] = $endDateTime;

					}	

					// delete input xls file from server
					unlink($fullPath);

					
					$bulkInsert = $this-> MRates-> bulkCarrierPrefixRateInsert($data,$rowCount-1,$userLoggedId);
					
					
					//if($bulkInsert< 1) // if bulk insert fails
					if($bulkInsert['rateInsert'] == 0 && $bulkInsert['rateUpdate'] == 0) // if bulk insert fails
					{
						
						$msg = '';
						$error = $resultData['uploadData'];
					}	
					else
					{
						$msg ='success';
						$error = $fileName.' is uploaded in the location '.$filePath;
						if($bulkInsert['rateInsert']>0) $error .= '<br>New '.$bulkInsert['rateInsert'].' rates inserted';
						if($bulkInsert['rateUpdate']>0) $error .= '<br>Old '.$bulkInsert['rateUpdate'].' rates updated';
						if($bulkInsert['prefixInsert']>0) $error .= '<br>New '.$bulkInsert['prefixInsert'].' prefix added';

					}
				}
				else
				{
					$msg = '';
					$error = $resultData['uploadData'];
				}

				echo "{";
				echo "error: '" . $error . "',\n";
				echo "msg: '" . $msg . "'\n";
				echo "}";
				

			}

			
		}
		else
		{
			$this-> noPageAccess();
		}		

	} // rateUpload ends
	

	public function spacialRateUpload($operations = 0)
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$userActionId = 2; // 2 means Carrier Rate Upload
		$data['userId'] = $userLoggedId;
		$data['title'] = 'Alveron | Top/Push Chart Upload';
		$data['main'] = 'spacialRateUpload';

		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);
		if($userAccessValue == 1)
		{

			$data['carrierList'] = $this-> MCarriers-> getCarrierListWithTrafficTypeAndCarrierType();
			// view the upload form
			if($operations == 0)
			{
				$this-> load-> vars($data);
			
				if(IS_AJAX)
				{
					echo json_encode(array('feedback' => 1,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
				}
				else
				{
					$this-> load-> view('template',$data);
				}	
			}

			// what have to do when upload button is pressed
			else if($operations == 1)
			{
				$fileElementId = 'userfile';
				$resultData = $this-> uploadFiles($fileElementId);
				//echo $uploadFileResult['upload_data'];
				if($resultData['status'] == 'success')
				{
					$carrierId = $this-> input-> post('carrierId');
					$fullPath = $resultData['uploadData']['full_path'];
					$filePath = $resultData['uploadData']['file_path'];
					$fileExtension = $resultData['uploadData']['file_ext'];
					$fileName = $resultData['uploadData']['file_name'];
					$rawName = $resultData['uploadData']['raw_name'];
					
					// read excel file

					// load new PHPExcel library
					
					$this-> load-> library('excel');
					
					if($fileExtension == '.xls')
						$excelFileType = 'Excel5';
					elseif($fileExtension == '.xlsx')
						$excelFileType = 'Excel2007';

					$fileNameNew = 'New'.$fileName; 
					
					//error_reporting(E_ALL);
					//set_time_limit(0);
					//ini_set("memory_limit", "512M"); // ini_set("memory_limit", "512M") or something else higher than 128M
					
					//$objReader = IOFactory::createReader($excelFileType); 
					$objReader = IOFactory::createReaderForFile($fullPath);
					$objReader->setReadDataOnly(true); // set this, to not read all excel properties, just data
					// to do this the className has to be made IOFactory instead of PHPExcel_IOFactory
					
					
					$objPHPExcel = $objReader-> load($fullPath);
					
					$objWorksheet = $objPHPExcel-> setActiveSheetIndex(0); // set 1st sheet to be active
					// Note: In PHPExcel column index is 0-based while row index is 1-based. That means 'A1' ~ (0,1)

					// Get the highest row number referenced in the worksheet
					$rowCount = $objWorksheet->getHighestRow(); // total number or dial code + 1 for header
					$maxColumn = $objWorksheet->getHighestColumn();  // 'F'
					$columnCount = PHPExcel_Cell::columnIndexFromString($maxColumn); // in this case it will be 6,5, or 4
					
					$data = array();
										
					$j = 1;
					for($i=2; $i<=$rowCount; $i++,$j++)
					{
						$country = $objWorksheet-> getCellByColumnAndRow(0,$i)-> getCalculatedValue();
						$destination = $objWorksheet-> getCellByColumnAndRow(1,$i)-> getCalculatedValue();
						$prefix = $objWorksheet-> getCellByColumnAndRow(2,$i)-> getCalculatedValue(); // try using getCalculatedValue
						$rate = $objWorksheet-> getCellByColumnAndRow(3,$i)-> getCalculatedValue();
						$asr = $objWorksheet-> getCellByColumnAndRow(4,$i)-> getCalculatedValue();
						$acd = $objWorksheet-> getCellByColumnAndRow(5,$i)-> getCalculatedValue();
												
						if($columnCount >= 7)
						{
							$startDateTime = PHPExcel_Shared_Date::ExcelToPHPObject($objWorksheet->getCellByColumnAndRow(6, $i)->getCalculatedValue())->format('Y-m-d H:i:s');
							
						}
						elseif($columnCount == 6)
						{
							$startDateTime = PHPExcel_Shared_Date::ExcelToPHPObject($objWorksheet->getCellByColumnAndRow(6, $i)->getCalculatedValue())->format('Y-m-d H:i:s');
							
						}
						elseif($columnCount == 5)
						{
							
							$startDateTime = date('Y-m-d').' 00:00:00';	
							
						}
						$data[$j] = array();

						$data[$j][0] = $carrierId;
						$data[$j][1] = $country;
						$data[$j][2] = $destination;
						$data[$j][3] = $prefix;
						$data[$j][4] = $rate;
						$data[$j][5] = $asr;
						$data[$j][6] = $acd;
						$data[$j][7] = $startDateTime;

					}	

					// delete input xls file from server
					unlink($fullPath);

					
					$bulkInsert = $this-> MRates-> bulkCarrierPrefixSpeciaRateInsert($data,$rowCount-1,$userLoggedId);
					
					
					//if($bulkInsert< 1) // if bulk insert fails
					if($bulkInsert['rateInsert'] == 0 && $bulkInsert['rateUpdate'] == 0) // if bulk insert fails
					{
						
						$msg = '';
						$error = $resultData['uploadData'];
					}	
					else
					{
						$msg ='success';
						$error = $fileName.' is uploaded in the location '.$filePath;
						if($bulkInsert['rateInsert']>0) $error .= '<br>New '.$bulkInsert['rateInsert'].' rates inserted';
						if($bulkInsert['rateUpdate']>0) $error .= '<br>Old '.$bulkInsert['rateUpdate'].' rates updated';
						if($bulkInsert['prefixInsert']>0) $error .= '<br>New '.$bulkInsert['prefixInsert'].' prefix added';

					}
				}
				else
				{
					$msg = '';
					$error = $resultData['uploadData'];
				}

				echo "{";
				echo "error: '" . $error . "',\n";
				echo "msg: '" . $msg . "'\n";
				echo "}";
				

			}

			
		}
		else
		{
			$this-> noPageAccess();
		}		

	} // spacialRateUpload ends

	public function rateChartList()
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$feedback = 0; // variable initialization
		if($userLoggedId > 0)
		{
			$data['carrierRateChartInfo'] = null; // this is for edit form
			$data['updateTrafficTypeAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,11); // 11 = Rate Chart Update Access
			$data['deleteRateChartAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,13); // 13 = Rate Chart Update Access
			$this-> load-> library('table');
			$data['userId'] = $userLoggedId;
			$data['title'] = 'Alveron | Rate Chart List';
			$data['main'] = 'rateChartList';
			
			$data['carrierTypeList'] = $this-> MCarriers-> getCarrierTypeList(0);
			$data['trafficTypeList'] = $this-> MCarriers-> getTrafficTypeList(0);
			$data['carrierNameList'] = $this-> MCarriers-> getCarrierNameList(0);

			if($this-> input-> post('searchOption'))
				$data['searchOptionSelected'] = $this-> input-> post('searchOption');
			else
				$data['searchOptionSelected'] = 0;

			if($this-> input-> post('carrierTypeId'))
			{
				$data['carrierTypeIdSelected'] = $this-> input-> post('carrierTypeId');
				$data['searchOptionSelected'] = 1;
			}	
			else
				$data['carrierTypeIdSelected'] = 0;

			if($this-> input-> post('trafficTypeId'))
			{
				$data['trafficTypeIdSelected'] = $this-> input-> post('trafficTypeId');
				$data['searchOptionSelected'] = 2;
			}	
			else
				$data['trafficTypeIdSelected'] = 0;

			if($this-> input-> post('carrierNameId'))
			{
				$data['carrierNameIdSelected'] = $this-> input-> post('carrierNameId');
				$data['searchOptionSelected'] = 3;
			}	
			else
				$data['carrierNameIdSelected'] = 0;



			$data['rateChartListView'] = $this-> MRates-> getRateCharts($data['carrierTypeIdSelected'],$data['trafficTypeIdSelected'],$data['carrierNameIdSelected']);

			if($data['rateChartListView'])
			{
				$feedback = 1;

			}
			else
			{
				$feedback = 0;
			}
		}
		else // if not logged in then place to log in form
		{
			$data['userId'] = null;
			$data['title'] = 'Alveron | Login';
			$data['main'] = 'login';
			$feedback  = 1;	
			
		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback'=> $feedback,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
		}
		else
		{
			$this-> load-> vars($data);
			$this-> load-> view('template',$data);	
		}
	} // rateChartList() ends



	public function rateChartTrafficTypeEdit($carrierId=0,$rateUploadDate=0,$operation=0)
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,11);// 11 = Rate Chart Partner Traffic Type Update
		if($userAccessValue == 1)
		{
			$data = array();
			$data['main'] = 'rateChartList';
			$data['userId'] = $userLoggedId;
			if(!$this-> input-> post('trafficTypeEdit')) // view the edit form
			{
				$data['carrierRateChartInfo'] = $this-> MRates-> getCarrierInfoForRateChartEdit($carrierId,$rateUploadDate);
				$data['trafficTypeListForUpdate'] = $this-> MCarriers-> getTrafficTypeList(1);
				$data['title'] = 'Alveron | Rate Chart Traffic Type Edit';
				$data['rateChartListView'] = null;
				if($data['carrierRateChartInfo'])
					$feedback = 1;
				else
					$feedback = 0;
			} // view the edit form ends
			else // show the view form after update 
			{
				$carrierIdEdit = $this-> input-> post('carrierIdEdit');
				$rateUploadDate = $this-> input-> post('rateUploadDate');
				$trafficTypeId = $this-> input-> post('trafficTypeEdit');
				$trafficTypeOld = $this-> input-> post('trafficTypeOld');

				//echo $trafficTypeOld;
				
				$feedback = $this-> MRates-> updateCarrierTrafficTypeOfRateChart($carrierIdEdit,$rateUploadDate,$trafficTypeId);
				
				// show the view form

				$this-> load-> library('table');

				$data['carrierRateChartInfo'] = null;
				$data['title'] = 'Alveron | Rate Chart List';

				$data['carrierTypeList'] = $this-> MCarriers-> getCarrierTypeList(0);
				$data['trafficTypeList'] = $this-> MCarriers-> getTrafficTypeList(0);
				$data['carrierNameList'] = $this-> MCarriers-> getCarrierNameList(0);

				$data['searchOptionSelected'] = 2;
				$data['carrierTypeIdSelected'] = 0;
				$data['carrierNameIdSelected'] = 0;
				
				if($feedback == 1)
				{
					$data['trafficTypeIdSelected'] = $trafficTypeId;
				}
				else
				{
					$data['trafficTypeIdSelected'] = $trafficTypeOld;
				}
				
				$data['updateTrafficTypeAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,11); // 11 = Rate Chart Update Access
				$data['deleteRateChartAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,13); // 13 = Rate Chart Update Access
				$data['rateChartListView'] = $this-> MRates-> getRateCharts($data['carrierTypeIdSelected'],$data['trafficTypeIdSelected'],$data['carrierNameIdSelected']);


			} // show the view form after update  ends

			if(IS_AJAX)
			{
				echo json_encode(array('feedback'=> $feedback,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> vars($data);
				$this-> load-> view('template',$data);	
			}

		}
		else // if no access
		{
			$this-> noPageAccess();
		}
		
		
	} // rateChartEdit($carrierId,$rateUploadDate,$searchOptionSelected,$carrierTypeIdSelected,$trafficTypeIdSelected,$carrierNameIdSelected) ends
	public function rateChartDelete($carrierId,$rateUploadDate,$searchOptionSelected,$carrierTypeIdSelected,$trafficTypeIdSelected,$carrierNameIdSelected)
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,13);// 13 = Rate Chart Delete
		if($userAccessValue == 1)
		{
			$data = array();
			$data['carrierRateChartInfo'] = null; // this is for edit form
			$data['updateTrafficTypeAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,11); // 11 = Rate Chart Update Access
			$data['deleteRateChartAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,13); // 13 = Rate Chart Update Access
			$this-> load-> library('table');
			$data['userId'] = $userLoggedId;

			$data['main'] = 'rateChartList';
			$data['title'] = 'Alveron | Rate Chart List';

			$feedback = $this-> MRates-> deleteRateChart($carrierId,$rateUploadDate);


			$data['carrierTypeList'] = $this-> MCarriers-> getCarrierTypeList(0);
			$data['trafficTypeList'] = $this-> MCarriers-> getTrafficTypeList(0);
			$data['carrierNameList'] = $this-> MCarriers-> getCarrierNameList(0);

			$data['searchOptionSelected'] = $searchOptionSelected;
			$data['carrierTypeIdSelected'] = $carrierTypeIdSelected;
			$data['trafficTypeIdSelected'] = $trafficTypeIdSelected;
			$data['carrierNameIdSelected'] = $carrierNameIdSelected;

			$data['rateChartListView'] = $this-> MRates-> getRateCharts($data['carrierTypeIdSelected'],$data['trafficTypeIdSelected'],$data['carrierNameIdSelected']);

			if(IS_AJAX)
			{
				echo json_encode(array('feedback'=> $feedback,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> vars($data);
				$this-> load-> view('template',$data);	
			}
		}
		else
		{
			$this-> noPageAccess();
		}
	}  // rateChartDelete() ends

	public function specialRateChartList()
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$feedback = 0; // variable initialization
		if($userLoggedId > 0)
		{
			$data['carrierRateChartInfo'] = null; // this is for edit form
			$data['updateTrafficTypeAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,12); // 12 = Top/Push Chart Update Access
			$data['deleteRateChartAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,14); // 14 = Top/Push Chart Update Access
			$this-> load-> library('table');
			$data['userId'] = $userLoggedId;
			$data['title'] = 'Alveron | Top/Push Chart List';
			$data['main'] = 'specialRateChartList';
			
			$data['carrierTypeList'] = $this-> MCarriers-> getCarrierTypeList(0);
			$data['trafficTypeList'] = $this-> MCarriers-> getTrafficTypeList(0);
			$data['carrierNameList'] = $this-> MCarriers-> getCarrierNameList(0);

			if($this-> input-> post('searchOption'))
				$data['searchOptionSelected'] = $this-> input-> post('searchOption');
			else
				$data['searchOptionSelected'] = 0;

			if($this-> input-> post('carrierTypeId'))
			{
				$data['carrierTypeIdSelected'] = $this-> input-> post('carrierTypeId');
				$data['searchOptionSelected'] = 1;
			}	
			else
				$data['carrierTypeIdSelected'] = 0;

			if($this-> input-> post('trafficTypeId'))
			{
				$data['trafficTypeIdSelected'] = $this-> input-> post('trafficTypeId');
				$data['searchOptionSelected'] = 2;
			}	
			else
				$data['trafficTypeIdSelected'] = 0;

			if($this-> input-> post('carrierNameId'))
			{
				$data['carrierNameIdSelected'] = $this-> input-> post('carrierNameId');
				$data['searchOptionSelected'] = 3;
			}	
			else
				$data['carrierNameIdSelected'] = 0;



			$data['rateChartListView'] = $this-> MRates-> getSpecialRateCharts($data['carrierTypeIdSelected'],$data['trafficTypeIdSelected'],$data['carrierNameIdSelected']);

			if($data['rateChartListView'])
			{
				$feedback = 1;

			}
			else
			{
				$feedback = 0;
			}
		}
		else // if not logged in then place to log in form
		{
			$data['userId'] = null;
			$data['title'] = 'Alveron | Login';
			$data['main'] = 'login';
			$feedback  = 1;	
			
		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback'=> $feedback,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
		}
		else
		{
			$this-> load-> vars($data);
			$this-> load-> view('template',$data);	
		}
	} // rateChartList() ends

	

	public function specialRateChartTrafficTypeEdit($carrierId=0,$rateUploadDate=0,$operation=0)
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,12);// 11 = Top/Push Chart Partner Traffic Type Update
		if($userAccessValue == 1)
		{
			$data = array();
			$data['main'] = 'specialRateChartList';
			$data['userId'] = $userLoggedId;
			if(!$this-> input-> post('trafficTypeEdit')) // view the edit form
			{
				$data['carrierRateChartInfo'] = $this-> MRates-> getCarrierInfoForSpecialRateChartEdit($carrierId,$rateUploadDate);
				$data['trafficTypeListForUpdate'] = $this-> MCarriers-> getTrafficTypeList(1);
				$data['title'] = 'Alveron | Top/Push Chart Traffic Type Edit';
				$data['rateChartListView'] = null;
				if($data['carrierRateChartInfo'])
					$feedback = 1;
				else
					$feedback = 0;
			} // view the edit form ends
			else // show the view form after update 
			{
				$carrierIdEdit = $this-> input-> post('carrierIdEdit');
				$rateUploadDate = $this-> input-> post('rateUploadDate');
				$trafficTypeId = $this-> input-> post('trafficTypeEdit');
				$trafficTypeOld = $this-> input-> post('trafficTypeOld');

				//echo $trafficTypeOld;
				
				$feedback = $this-> MRates-> updateCarrierTrafficTypeOfSpecialRateChart($carrierIdEdit,$rateUploadDate,$trafficTypeId);
				
				// show the view form

				$this-> load-> library('table');

				$data['carrierRateChartInfo'] = null;
				$data['title'] = 'Alveron | Top/Push Chart List';

				$data['carrierTypeList'] = $this-> MCarriers-> getCarrierTypeList(0);
				$data['trafficTypeList'] = $this-> MCarriers-> getTrafficTypeList(0);
				$data['carrierNameList'] = $this-> MCarriers-> getCarrierNameList(0);

				$data['searchOptionSelected'] = 2;
				$data['carrierTypeIdSelected'] = 0;
				$data['carrierNameIdSelected'] = 0;
				
				if($feedback == 1)
				{
					$data['trafficTypeIdSelected'] = $trafficTypeId;
				}
				else
				{
					$data['trafficTypeIdSelected'] = $trafficTypeOld;
				}
				
				$data['updateTrafficTypeAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,12); // 12 = Top/Push Chart Update Access
				$data['deleteRateChartAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,14); // 14 = Top/Push Chart Update Access
				$data['rateChartListView'] = $this-> MRates-> getSpecialRateCharts($data['carrierTypeIdSelected'],$data['trafficTypeIdSelected'],$data['carrierNameIdSelected']);


			} // show the view form after update  ends

			if(IS_AJAX)
			{
				echo json_encode(array('feedback'=> $feedback,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> vars($data);
				$this-> load-> view('template',$data);	
			}

		}
		else // if no access
		{
			$this-> noPageAccess();
		}
		
		
	} // rateChartEdit($carrierId,$rateUploadDate,$searchOptionSelected,$carrierTypeIdSelected,$trafficTypeIdSelected,$carrierNameIdSelected) ends
	public function specialRateChartDelete($carrierId,$rateUploadDate,$searchOptionSelected,$carrierTypeIdSelected,$trafficTypeIdSelected,$carrierNameIdSelected)
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,14);// 14 = Top/Push Chart Delete
		if($userAccessValue == 1)
		{
			$data = array();
			$data['carrierRateChartInfo'] = null; // this is for edit form
			$data['updateTrafficTypeAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,12); // 12 = Top/Push Chart Update Access
			$data['deleteRateChartAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,14); // 14 = Top/Push Chart Update Access
			$this-> load-> library('table');
			$data['userId'] = $userLoggedId;

			$data['main'] = 'specialRateChartList';
			$data['title'] = 'Alveron | Top/Push Chart List';

			$feedback = $this-> MRates-> deleteSpecialRateChart($carrierId,$rateUploadDate);


			$data['carrierTypeList'] = $this-> MCarriers-> getCarrierTypeList(0);
			$data['trafficTypeList'] = $this-> MCarriers-> getTrafficTypeList(0);
			$data['carrierNameList'] = $this-> MCarriers-> getCarrierNameList(0);

			$data['searchOptionSelected'] = $searchOptionSelected;
			$data['carrierTypeIdSelected'] = $carrierTypeIdSelected;
			$data['trafficTypeIdSelected'] = $trafficTypeIdSelected;
			$data['carrierNameIdSelected'] = $carrierNameIdSelected;

			$data['rateChartListView'] = $this-> MRates-> getSpecialRateCharts($data['carrierTypeIdSelected'],$data['trafficTypeIdSelected'],$data['carrierNameIdSelected']);

			if(IS_AJAX)
			{
				echo json_encode(array('feedback'=> $feedback,'title' => $data['title'],'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> vars($data);
				$this-> load-> view('template',$data);	
			}
		}
		else
		{
			$this-> noPageAccess();
		}
	} 

	public function carrierManagement($operation=0,$carrierTypeIdSelect=0,$trafficTypeIdSelect=0,$carrierStatusIdSelect= -1) 
	// $operation = 0 means view, 1 means add carrier to traffic type
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$data['userId'] = $userLoggedId;
		$data['title'] = "Alveron | Partner Management";	
		if($operation == 1)
		{
			$userActionId = 4; // 4 means carrier insert
		}
		else
		{
			$userActionId = 15; // 15 means carrier view
		}
		
		
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		$data['insertAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,4); 
		// $data['insertAccess'] == 1 means insert Access, $data['insertAccess'] == 0 means no insert access

		$data['editAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,9); 
		// $data['editAccess'] == 1 means edit Access, $data['editAccess'] == 0 means no edit access
		
		if($userAccessValue == 1) 
		{
			
		
			if($operation == 1) // insert button pressed
			{
				$addSuccess = $this-> MCarriers-> addCarrier();
				if($addSuccess > 0)
				{
					$feedback = 'New '.$addSuccess.' Carrier with Traffic Type activated.';
				}	
				else if($addSuccess == -2)
				{

					$feedback = 'Select at least one trafficType';
					//$feedback = 'This carrier is already activated with selected carrier type, and traffic type';
				}	
				else
				{
					$feedback = 'Carrier Activation is failed for duplicate entry';
				}
			} 
			else
			{
				$feedback = 1;
			}

			$data['trafficTypeList'] = $this-> MCarriers-> getTrafficTypeList(1); // 1 means 'Select Traffic Type' will not be in the select box
			$data['trafficTypeListSearch'] = $this-> MCarriers-> getTrafficTypeList(0); // 0 means 'Select Traffic Type' will be in the select box
			
			$data['carrierTypeList'] = $this-> MCarriers-> getCarrierTypeList(1); // 1 means 'Select Traffic Type' will not be in the select box
			$data['carrierTypeListSearch'] = $this-> MCarriers-> getCarrierTypeList(0); // 0 means 'Select Traffic Type' will be in the select box

			$data['carrierStatusList'] = array('1'=> 1,'0'=> 0);
			$data['carrierStatusListSearch'] = array('-1'=>'Select Partner Status','0'=>'Inactive','1'=>'Active');
    

			$data['carrierNameList'] = $this-> MCarriers-> getCarrierNameList(1); // 1 means 'Select Traffic Type' will not be in the select box

			// initialize search option value
			$data['carrierTypeIdSelected'] = 0;
			$data['trafficTypeIdSelected'] = 0;
			$data['carrierStatusIdSelected'] = -1;

			if($carrierTypeIdSelect > 0)
			{
				$data['searchOptionSelected'] = 1; // search by carrier type
				$data['carrierTypeIdSelected'] = $carrierTypeIdSelect;
			}
				
			elseif($trafficTypeIdSelect > 0)
			{
				$data['searchOptionSelected'] = 2; // search by traffic type
				$data['trafficTypeIdSelected'] = $trafficTypeIdSelect;
			}
				
			elseif($carrierStatusIdSelect >= 0)
			{
				$data['searchOptionSelected'] = 3; // search by carrier status
				$data['carrierStatusIdSelected'] = $carrierStatusIdSelect;	
			}
				
			else
				$data['searchOptionSelected'] = 0; // no search option

			$this-> load-> library('table');
			$data['carrierManagementView'] = $this -> MCarriers -> getAllCarriers($carrierTypeIdSelect,$trafficTypeIdSelect,$carrierStatusIdSelect); // ($limit,$offset)
			
			$data['main'] = 'carrierManagement';
	
			if(IS_AJAX)
			{
				echo json_encode(array('feedback' => $feedback,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> vars($data);
				$this-> load-> view('template',$data);
			}
		}
		else
		{
			$this-> noPageAccess();
				
		}
	} // carrierManagement() ends

	
	public function carrierList($operation = 0) // 0 view, 1 insert 
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$data['userId'] = $userLoggedId;
		$data['title'] = "Alveron | Partner List";	
		if($operation == 1)
		{
			$userActionId = 4; // 4 means carrier insert
		}
		else
		{
			$userActionId = 15; // 15 means carrier view
		}
		
		
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);
		
		$data['insertAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,4); 
		// $data['insertAccess'] == 1 means insert Access, $data['insertAccess'] == 0 means no insert access

		$data['editAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,9); 
		// $data['editAccess'] == 1 means edit Access, $data['editAccess'] == 0 means no edit access

		if($userAccessValue == 1)
		{
			if($operation == 1) // insert
			{
				$addSuccess = $this-> MCarriers-> addCarrierName();
				if($addSuccess >= 1)
				{
					$feedback = 'New Carrier is created.';
				}	
					
				else
				{
					$feedback = 'Carrier add is failed.';
				}
			}
			else
			{
				$feedback = 1;
			}

			$this-> load-> library('table');
			$data['carrierListView'] = $this -> MCarriers -> getAllCarrierList(0,0); // ($limit,$offset)
			
			$data['main'] = 'carrierList';

			if(IS_AJAX)
			{
				echo json_encode(array('feedback' => $feedback,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> vars($data);
				$this-> load-> view('template',$data);
			}

		}

		else
		{
			$this-> noPageAccess();
				
		}
	} // carrierList() ends

	public function trafficTypeList($operation = 0) // 0 view, 1 insert 
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$data['userId'] = $userLoggedId;
		$data['title'] = "Alveron | Traffic Type List";	
		if($operation == 1)
		{
			$userActionId = 4; // 4 means carrier insert
		}
		else
		{
			$userActionId = 15; // 15 means carrier view
		}
		
		
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		$data['insertAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,4); 
		// $data['insertAccess'] == 1 means insert Access, $data['insertAccess'] == 0 means no insert access

		$data['editAccess'] = $this-> MUsers-> userAccessEligibility($userLoggedId,9); 
		// $data['editAccess'] == 1 means edit Access, $data['editAccess'] == 0 means no edit access
		
		if($userAccessValue == 1) // view Form
		{
			
			if($operation == 1) // insert input text
			{
				$addSuccess = $this-> MCarriers-> addTrafficType();
				if($addSuccess >= 1)
				{
					$feedback = 'New Traffic Type is created.';
				}	
					
				else
				{
					$feedback = 'Traffic type add is failed.';
				}
			}			
			else
				$feedback = 1;

			$this-> load-> library('table');
			$data['trafficTypeListView'] = $this -> MCarriers -> getAllTrafficTypeList(0,0); // ($limit,$offset)
			
			$data['main'] = 'trafficTypeList';
			
			
			if(IS_AJAX)
			{
				echo json_encode(array('feedback' => $feedback,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> vars($data);
				$this-> load-> view('template',$data);
			}
		}

		else
		{
			$this-> noPageAccess();
				
		}
	} // trafficTypeList() ends


	public function userManagement()
	{
		
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$data['userId'] = $userLoggedId;
		$data['title'] = "Alveron | User Management";	
		$userActionId = 5; // 5 means User View
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);
		if($userAccessValue == 1) // only Admin can access this 
		{
			$data['userTypeList'] = $this-> MUsers-> getUserTypeList();
			$data['userStatusList'] = $this-> MUsers-> getUserStatusList();
			$this-> load-> library('table');
			$data['userView'] = $this -> MUsers -> getAllUsers(0,0); // parameters are ($limit,$offset)
			$data['main'] = 'userManagement';
		
			$this-> load-> vars($data);
			
			if(IS_AJAX)
			{
				echo json_encode(array('feedback' => 1,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> view('template',$data);
			}
		}
		else
		{
			$this-> noPageAccess();
				
		}
	} // userManagement ends

	public function userTypeManagement()
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$data['userId'] = $userLoggedId;
		$data['title'] = "Alveron | User Type Management";	
		$userActionId = 6; // 6 means User Type Name View
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);
		if($userAccessValue == 1) 
		{
			
			
			$this-> load-> library('table');

			
			
			$data['userTypeView'] = $this -> MUsers -> getAllUserTypes(0,0); // ($limit,$offset)
			
			$data['main'] = 'userTypeManagement';
			
			
			
			
			if(IS_AJAX)
			{
				echo json_encode(array('feedback' => 1,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> vars($data);
				$this-> load-> view('template',$data);
			}
		}
		else
		{
			$this-> noPageAccess();
				
		}
	} // userTypeManagement($perPageRow=5,$offset = '') ends

	public function addUserTypeName($userType)
	{
		
		$userLoggedId = $this-> session-> userdata('userId');
		$userActionId = 8; // 8 means User Type Name Edit
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		if($userAccessValue == 1)
		{

			$userType = urldecode($userType);	// this will resolve the '%20' issue in case of 'space' character

								
			$addSuccess = $this-> MUsers-> addUserTypeName($userType);
			
			if($addSuccess)
			{
				$feedback = 'New user type "'.$userType.'" is created.'; 	
				$data['title'] = "Alveron | User Type Management";	
				$this-> load-> library('table');
		
				$data['userTypeView'] = $this -> MUsers -> getAllUserTypes(0,0); // ($limit,$offset)
				
				$data['main'] = 'userTypeManagement';
			}
			else
			{
				$feedback = 'User Type Add is not successful because of Database error. Please try again.';
			}
		
		
			echo json_encode(array('feedback' => $feedback,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
 		}
 		else
 		{
 			echo json_encode(array('feedback' => -1));
 		}

	} // addUserTypeName($userType) ends

	
	//public function userTypeAccessManagement($userTypeId=0,$userActionId=0,$perPageRow=5,$offset = '')
	public function userTypeAccessManagement($userTypeIdSearch=0,$userActionIdSearch=0)
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$data['userId'] = $userLoggedId;
		$data['title'] = "Alveron | User Type Access Management";	
		$data['userTypeList'] = $this-> MUsers-> getUserTypeListNotAssignedFiltered();
		$data['userTypeIdSelected'] = $userTypeIdSearch; 
		$data['userActionList'] = $this-> MUsers-> getUserActionList();
		$data['userActionIdSelected'] = $userActionIdSearch;
		if($userTypeIdSearch > 0 && $userActionIdSearch == 0)
			$data['searchOptionSelected'] = 1;  // search option will be selected as userType
		elseif($userTypeIdSearch == 0 && $userActionIdSearch > 0)
			$data['searchOptionSelected'] = 2; // search option will be selected as userAction
		else
			$data['searchOptionSelected'] = 0; // search option will be selected as 'Select Search Option'
		$userActionId = 6; // 6 means User Type Name View
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);
		if($userAccessValue == 1) 
		{
			
			
			$this-> load-> library('table');

			
			$data['userTypeAccessView'] = $this -> MUsers -> getAllUserTypeAccess($userTypeIdSearch,$userActionIdSearch); 
			
			
			
			$data['main'] = 'userTypeAccessManagement';
			
			
			
			
			if(IS_AJAX)
			{
				echo json_encode(array('feedback' => 1,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> vars($data);
				$this-> load-> view('template',$data);
			}
		}
		else
		{
			$this-> noPageAccess();
		}
	} // userTypeAccessManagement($perPageRow=5,$offset = '') ends

	public function addUserTypeAccess()
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userActionId = 24; // 24 means User Type Access Insert
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);
		if($userAccessValue == 1)
		{
			$addSuccess = $this-> MUsers-> addUserTypeAccess();
			if($addSuccess)
			{
				if($addSuccess == 0)
					$feedback = 'Select at least one action'; 	
				else
					$feedback = 'New user type access is created.'; 	
				
				// reloading updated view form
				$data['title'] = "Alveron | User Type Access Management";	
				$data['userTypeList'] = $this-> MUsers-> getUserTypeListNotAssignedFiltered();
				$data['userTypeIdSelected'] = 0; 
				$data['userActionList'] = $this-> MUsers-> getUserActionList();
				$data['userActionIdSelected'] = 0;
				$data['searchOptionSelected'] = 0;

				$this-> load-> library('table');
				$data['userTypeAccessView'] = $this -> MUsers -> getAllUserTypeAccess($data['userTypeIdSelected'],$data['userActionIdSelected']); 
				$data['main'] = 'userTypeAccessManagement';

				
			}
			else
			{
				$feedback = 'User Type Access Add is not successful because of Database error. Please try again.';
			}
			if(IS_AJAX)
				echo json_encode(array('feedback' => $feedback,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));	
		}
		else
		{
			echo json_encode(array('feedback' => -1));		
		}

	} // addUserTypeAccess() ends


	public function actionListForUserTypeAccessManagement() // this is for new user type access insert form
	{
		$userTypeId = $this-> input-> post('userTypeId');
		$actionListSelect = $this-> MUsers-> getRemainingActionList($userTypeId);
		$htmlBody = '';
		if($actionListSelect)
		{
			$feedback = 1;
			$attr =  'id="userActionNew" class="formList"';
			$htmlBody .= form_label('Action List','userActionNew',array('class' => 'formLabel'));
			$htmlBody .= form_multiselect('userActionNew[]',$actionListSelect,'',$attr);
		}
		else
		{
			$feedback = 0;
			$htmlBody .= 'No action access remained';
		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback' => $feedback, 'htmlBody' => $htmlBody));
		}
	}

	public function accountManagement($operation = 0) // $operation = 0 means form view, $operation = 1 means edit
	{
		$userId = $this-> session-> userdata('userId');
		$data['title'] = 'Alveron | Account Management';
		$data['main'] = 'accountManagement';
					
		if ($operation == 1) // if submit button is set
		{
			//echo 'ssad';
			$feedback = $this-> MUsers-> userAccountEdit($userId);
			if($feedback >= 1)
				$msgBody = 'User account is updated';
			else
				$msgBody = 'User account update failed.';
			if(IS_AJAX)
			{
				echo json_encode(array('feedback' => $feedback,'message'=> $msgBody));
			}
		}
		else
		{

			$feedback = 1;
			$data['userInfo'] = $this-> MUsers-> getUserInfo($userId);
			$this-> load-> vars($data);
			if(IS_AJAX)
			{
				echo json_encode(array('feedback' => 1,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> view('template',$data);
			}

		}
	
	} // accountManagement() ends


	
	// navBar link pages controller functions ends
	/**********************************************************************************************************/

	// login, logout, forget password functions
	/**********************************************************************************************************/

	public function forgetPassword($operation=0)
	{
		$data = array();
		$data['userId'] = NULL;
		$data['main'] = 'forgetPassword';
		$data['title'] = 'Alveron | Password Reset Request';
		
		if($operation == 1)
		{
			$userEmail = $this-> input-> post('userEmail');
			// run the update query to update passwordRequestSent
			$sendSuccess = $this-> MUsers-> sendPasswordResetRequest($userEmail);
			if($sendSuccess)
			{
				if($sendSuccess == -1)
				{
					$feedback = -1;
					$message = 'Already password reset request sent';
				}	
				else
				{
					$feedback = 1;
					$message = 'Password reset request sent';
				}
			}
			else
			{
				
				$feedback = 0;
				$message = 'Password reset request send failed, either you have not registered with this email id, or email id format is not correct';
			}
		}
		else
		{
			// display the passwordRequestForm
			
			$feedback = 1;
			$message = '';
		}

		//echo $feedback;

		if(IS_AJAX)
		{
			echo json_encode(array('feedback'=>$feedback,'message'=> $message,'title'=>$data['title'],'view'=>$this-> load-> view($data['main'],$data,TRUE)));
		}
		else
		{
			$this-> load-> vars($data);
			$this-> load-> view('template',$data);	
		}
		
	} // forgetPassword ends

	public function signInUser()
	{
		$this -> form_validation -> set_rules ('userNameLogin','User Id','trim|required');
		$this -> form_validation -> set_rules ('userPasswordLogin','Password','trim|required|sha1');
		if ($this -> form_validation -> run() == TRUE) 
		{
	
			$userName = $this -> input-> post('userNameLogin');
			$userPassword = $this -> input-> post('userPasswordLogin');	
			$row = $this-> MUsers-> verifyUser($userName,$userPassword);
			
			
			if($row)
			{
				$this-> session-> set_userdata( array(
			            'userId'=> $row['userId'],
			            'userName'=> $row['userName'],
			            'userTypeId'=> $row['userTypeId'],
			            'userStatusId' => $row['userStatusId'],
			            'userEmail' => $row['userEmail']
			        )
			    );
			    // if(IS_AJAX)
			    // {
			    // 	echo json_encode(array('success' => 1,'view' => 'alveron'));	
			    // }
			    // else
			    // 	redirect('alveron','refresh');

			}
			else
			{
				$this-> session-> set_userdata( array(
			            'userId'=> null,
			            'userName'=> null,
			            'userTypeId'=> null,
			            'userStatusId'=> null,
			            'userEmail'=> null
			        )
			    );
			 //    if(IS_AJAX)	
				// 	echo json_encode(array('feedback' => 'Either Username or Password incorrect!!','success' => 0));		
				// else
				// 	redirect('alveron','refresh');
			}
			
		}		
			
	} // signInUser ends

	public function signOutUser()
	{
		$this-> session-> sess_destroy();
		redirect(base_url(),'refresh');
	} // signOutUser ends

	public function signUpUser()
	{
		$addSuccess = $this-> MUsers-> addUser();

		if($addSuccess > 0)
		{
			$msgBody = 'Please wait for Admin to approve your registration. You will be notified by email.';
		}
		else
		{
			$msgBody = 'Sign Up is not successful; please try again.';
		}
		if(IS_AJAX)
		{
			echo json_encode(array('feedback' => $addSuccess,'message' => $msgBody));
		}	
	} // registration() ends

	// login, logout, forget password functions ends
	/**********************************************************************************************************/

	// edit functions
	/***********************************************************************************************************/

	public function editUserStatus($userId,$userStatusId)
	{
		
		$userLoggedId = $this-> session-> userdata('userId');
		$userActionId = 18; // 18 means User Status Change
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		if($userAccessValue)
		{
			$data = array();
				
			$editSuccess = $this-> MUsers-> editUserStatus($userId,$userStatusId);
			
			$userInfo = $this-> MUsers-> getUserInfo($userId);
			
			if($userInfo)
			{
				foreach($userInfo as $list)
	  			{
	  				$data['userId'] = $list['userId'];
	  				$data['userStatus'] = $list['userStatus'];
	  				$data['userStatusId'] = $list['userStatusId'];
	  				$data['userName'] = $list['userName'];
	  				$data['userFirstName'] = $list['userFirstName'];
	  				$data['userLastName'] = $list['userLastName'];
	  			}
	   		}

	   		if($editSuccess>=0)
			{
				$mailSubject = 'Alveron Rates Admin: User Id Activation';
				$messageBody = 'Your account has been activated; please login to <a href="http://www.alveron.org">Aleron Rate Chart Analysis</a> to continue.<br>';
				$mailSendResult = $this-> userMailSend($userInfo,$messageBody,$mailSubject,1);// 1 means user activation, 0 means password reset
				if($mailSendResult == 1 || $mailSendResult == 0)
				{
					$feedback = 'User Status Update of '.$data['userName'].' is successful.'; 	
				}
				elseif($mailSendResult == -1 || $mailSendResult == -2)
				{
					$this-> MUsers-> editUserType($userId,3); // 3 means Activation Pending
					$userInfoFailed = $this-> MUsers-> getUserInfo($userId);
					foreach($userInfoFailed as $list)
	  				{
		  				$data['userId'] = $list['userId'];
		  				$data['userType'] = $list['userType'];
		  				$data['userTypeId'] = $list['userTypeId'];
		  				$data['userName'] = $list['userName'];
	 				
	  				}
					if($mailSendResult == -1)
						$errorCause = 'Mail server error';
					else
						$errorCause = 'Database error';
					$feedback = 'User Status Update of '.$data['userName'].' is not successful because of '.$errorCause.'. Please try again.';
				}
				
			}
			else
			{
				$feedback = 'User Status Update of '.$data['userName'].' is not successful because of Database error. Please try again.';
			}
			

	 		echo json_encode(array('feedback' => $feedback,'userStatusUpdate' => $data['userStatus'],'userStatusIdUpdate' => $data['userStatusId']));	
		}
		else
		{
			echo json_encode(array('feedback' => -1));	
		}
		

	} // editUserStatus($userId,$userStatusId) ends

	public function editUserType($userId,$userTypeId)
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$userActionId = 19; // 19 means User Type Change
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		if($userAccessValue == 1)
		{
			$data = array();
				
			$editSuccess = $this-> MUsers-> editUserType($userId,$userTypeId);
			
			$userInfo = $this-> MUsers-> getUserInfo($userId);
			
			if($userInfo)
			{
				foreach($userInfo as $list)
	  			{
	  				$data['userId'] = $list['userId'];
	  				$data['userType'] = $list['userType'];
	  				$data['userTypeId'] = $list['userTypeId'];
	  				$data['userName'] = $list['userName'];
	  				
	  			}
	   		}
			
			if($editSuccess>=0)
			{
				$mailSubject = 'Alveron Rates Admin: User Id Activation';
				$messageBody = 'Your account has been activated; please login to <a href="http://www.alveron.org">Aleron Rate Chart Analysis</a> to continue.<br>';
				
				$mailSendResult = $this-> userMailSend($userInfo,$messageBody,$mailSubject,1); // 1 means user activation, 0 means password reset
				if($mailSendResult == 1 || $mailSendResult == 0)
				{
					
					$feedback = 'User Type Update of '.$data['userName'].' is successful.'; 	
				}
				elseif($mailSendResult == -1 || $mailSendResult == -2)
				{
					$this-> MUsers-> editUserType($data['userId'],4); // 4 means Not Assigned
					
					$userInfoFailed = $this-> MUsers-> getUserInfo($userId);
					foreach($userInfoFailed as $list)
	  				{
		  				$data['userId'] = $list['userId'];
		  				$data['userType'] = $list['userType'];
		  				$data['userTypeId'] = $list['userTypeId'];
		  				$data['userName'] = $list['userName'];
	 				
	  				}
	  				if($mailSendResult == -1)
						$errorCause = 'Mail server error';
					else
						$errorCause = 'Database error';
					$feedback = 'User Type Update of '.$data['userName'].' is not successful because of '.$errorCause.'. Please try again.';
				}
				
			}
			else
			{
				$feedback = 'User Type Update of '.$data['userName'].' is not successful because of Database error. Please try again.';
			}
				

	 		echo json_encode(array('feedback' => $feedback,'userTypeUpdate' => $data['userType'],'userTypeIdUpdate' => $data['userTypeId']));	
		}
		else
		{
			echo json_encode(array('feedback' => -1));
		}
		

	} // editUserType($userId,$userTypeId) ends

	public function editUserTypeName($userTypeId,$userType)
	{
		
		$userLoggedId = $this-> session-> userdata('userId');
		$userActionId = 8; // 8 means User Type Name Edit
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		if($userAccessValue == 1)
		{
			$userType = urldecode($userType);	// this will resolve the '%20' issue in case of 'space' character

			$data = array();
					
			$editSuccess = $this-> MUsers-> editUserTypeName($userTypeId,$userType);
			
			if($editSuccess>=0)
			{
				$feedback = 'User Type Update is successful.'; 	
				
			}
			else
			{
				$feedback = 'User Type Update is not successful because of Database error. Please try again.';
			}
			

	 		echo json_encode(array('feedback' => $feedback,'userTypeUpdate' => $userType,'userTypeIdUpdate' => $userTypeId));
		}
		else
		{
			echo json_encode(array('feedback' => -1));	
		}
		

	} // editUserTypeName($userTypeId,$userType) ends

	
	public function editUserTypeAccess($userTypeAccessId,$userTypeAccessValue)
	{
		
		$userLoggedId = $this-> session-> userdata('userId');
		$userActionId = 21; // 21 means User Type Access Edit
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		if($userAccessValue == 1)
		{
			$data = array();
					
			$editSuccess = $this-> MUsers-> editUserTypeAccess($userTypeAccessId,$userTypeAccessValue);
			
			if($editSuccess>=0)
			{
				$feedback = 'User Type Access Update is successful.'; 	
				
			}
			else
			{
				$feedback = 'User Type Access Update is not successful because of Database error. Please try again.';
			}
			

	 		echo json_encode(array('feedback' => $feedback,'userTypeAccessValueUpdate' => $userTypeAccessValue));
		}
		else
		{
			echo json_encode(array('feedback' => -1));	
		}
		

	} // editUserTypeAccess($userTypeAccessId,$userTypeAccessValue) ends


	function editCarrierStatusId($carrierId,$carrierStatusId) 
	{
		
		$userLoggedId = $this-> session-> userdata('userId');
		$userActionId = 9; // 9 means Carrier Edit
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		if($userAccessValue == 1)
		{
			$data = array();
					
			$editSuccess = $this-> MCarriers-> editCarrierStatusId($carrierId,$carrierStatusId);
			
			$carrierInfo = $this-> MCarriers-> getCarrierInfo($carrierId);
			
			if($carrierInfo)
			{
				foreach($carrierInfo as $list)
	  			{
	  				$data['carrierId'] = $list['carrierId'];
	  				$data['carrierStatusId'] = $list['carrierStatusId'];
	  			}
	   		}

			if($editSuccess>=0)
			{
				$feedback = 'Carrier Status Update is successful.'; 	
				
			}
			else
			{
				$feedback = 'Carrier Status Update is not successful because of Database error. Please try again.';
			}
			

	 		echo json_encode(array('feedback' => $feedback,'carrierStatusUpdateId' => $data['carrierStatusId']));
		}
		else
		{
			echo json_encode(array('feedback' => -1));	
		}
	} // editCarrierTypeId($carrierId,$carrierTypeId) ends

	function editCarrierName($carrierNameId,$carrierName) 
	{
		
		$userLoggedId = $this-> session-> userdata('userId');
		$userActionId = 9; // 9 means Carrier Edit
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		if($userAccessValue == 1)
		{
			$data = array();
					
			$editSuccess = $this-> MCarriers-> editCarrierName($carrierNameId,$carrierName);
			
			if($editSuccess>=0)
			{
				$feedback = 'Carrier Name Update is successful.'; 	
				
			}
			else
			{
				$feedback = 'Carrier name Update is not successful because of Database error. Please try again.';
			}
			

	 		echo json_encode(array('feedback' => $feedback,'carrierNameUpdate' => ucfirst($carrierName)));
		}
		else
		{
			echo json_encode(array('feedback' => -1));	
		}
	} // editCarrierName($carrierNameId,$carrierName) ends
	
	function editCarrierDescription($carrierNameId,$carrierDescription) 
	{
		
		$userLoggedId = $this-> session-> userdata('userId');
		$userActionId = 9; // 9 means Carrier Edit
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		if($userAccessValue == 1)
		{
			$data = array();
					
			$carrierDescription = urldecode($carrierDescription);

			$editSuccess = $this-> MCarriers-> editCarrierDescription($carrierNameId,$carrierDescription);
			
			if($editSuccess>=0)
			{
				$feedback = 'Carrier Name Update is successful.'; 	
				
			}
			else
			{
				$feedback = 'Carrier description Update is not successful because of Database error. Please try again.';
			}
			

	 		echo json_encode(array('feedback' => $feedback,'carrierDescriptionUpdate' => $carrierDescription));
		}
		else
		{
			echo json_encode(array('feedback' => -1));	
		}
	} // editCarrierDescription($carrierNameId,$carrierDescription) ends

	function editTrafficType($trafficTypeId,$trafficType) 
	{
		
		$userLoggedId = $this-> session-> userdata('userId');
		$userActionId = 9; // 9 means Carrier Edit
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		if($userAccessValue == 1)
		{
			$data = array();
					
			$editSuccess = $this-> MCarriers-> editTrafficType($trafficTypeId,$trafficType);
			
			if($editSuccess>=0)
			{
				$feedback = 'Traffic Type Update is successful.'; 	
				
			}
			else
			{
				$feedback = 'Traffic type Update is not successful because of Database error. Please try again.';
			}
			

	 		echo json_encode(array('feedback' => $feedback,'trafficTypeUpdate' => ucfirst($trafficType)));
		}
		else
		{
			echo json_encode(array('feedback' => -1));	
		}
	} // editTrafficType($trafficTypeId,$trafficType) ends
	
	function editTrafficDescription($trafficTypeId,$trafficDescription) 
	{
		
		$userLoggedId = $this-> session-> userdata('userId');
		$userActionId = 9; // 9 means Carrier Edit
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);

		if($userAccessValue == 1)
		{
			$data = array();
					
			$trafficDescription = urldecode($trafficDescription);
			$editSuccess = $this-> MCarriers-> editTrafficDescription($trafficTypeId,$trafficDescription);
			
			if($editSuccess>=0)
			{
				$feedback = 'Traffic type description Update is successful.'; 	
				
			}
			else
			{
				$feedback = 'Traffic type description Update is not successful because of Database error. Please try again.';
			}
			
			
	 		echo json_encode(array('feedback' => $feedback,'trafficDescriptionUpdate' => $trafficDescription));
		}
		else
		{
			echo json_encode(array('feedback' => -1));	
		}
	} // editTrafficDescription($trafficTypeId,$trafficDescription)  ends

	public function adminPasswordReset()
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$data['userId'] = $userLoggedId;
		$userActionId = 19; // 19 means Password Reset
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);
		if($userAccessValue == 1)
		{
			$userId = $this-> input-> post('userIdNew');
			$userPassword = trim($this-> input-> post('userPassword'));
			if($userId == 0 || $userPassword == '' || $userPassword == null)
				$resetSuccess = -1;
			else
				$resetSuccess = $this-> passwordReset($userId,$userPassword);	
			
			$data['userPasswordResetView'] = $this -> MUsers -> getAllUsers(0,0,1); // ($limit,$offset,$userPasswordRequest)
			$this-> load-> library('table');
			$data['userList'] = $this-> MUsers-> getUserList(1); // this list is required for admin password reset form
			$data['main'] = 'userPasswordReset';
			$data['title'] = "Alveron | User Password Reset";
			if(IS_AJAX)
			{
				echo json_encode(array('feedback' => $resetSuccess,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> vars($data);
				$this-> load-> view('template',$data);
			}	
		}
		else
		{
			$this-> noPageAccess();
				
		}
		

	} // adminPasswordReset ends

	public function userPasswordReset($userId = 0)
	{
		$userLoggedId = $this-> session-> userdata('userId');
		$data = array();
		$data['userId'] = $userLoggedId;
		
		$userActionId = 19; // 19 means User Password Reset
		$userAccessValue = $this-> MUsers-> userAccessEligibility($userLoggedId,$userActionId);
		
		if($userAccessValue == 1) 
		{
		
			if($userId>0)
			{
				$newPassword = $this-> generateRandomString(10);
				$resetSuccess = $this-> passwordReset($userId,$newPassword);
			}
			else
			{
				$resetSuccess = 1; // for view form feedback in ajax
			}

			$data['title'] = "Alveron | User Password Reset";	
			$data['userPasswordResetView'] = $this -> MUsers -> getAllUsers(0,0,1); // ($limit,$offset,$userPasswordRequest)
			$this-> load-> library('table');
			$data['userList'] = $this-> MUsers-> getUserList(1); // this list is required for admin password reset form
			$data['main'] = 'userPasswordReset';

			if(IS_AJAX)
			{
				echo json_encode(array('feedback' => $resetSuccess,'title' => $data['title'], 'view' => $this->load->view($data['main'],$data,TRUE)));
			}
			else
			{
				$this-> load-> vars($data);
				$this-> load-> view('template',$data);
			}	
		
		}
		else
		{
			$this-> noPageAccess();
				
		}
	} // userPasswordReset() ends

	// edit functions ends
	/****************************************************************************************************************************************/
	
	// validity check functions
	/******************************************************************************************************************/

	public function checkUser()
	{
		$this -> form_validation -> set_rules ('name','User Id','trim|required|min_length[4]|max_length[20]|xss_clean');
		if ($this -> form_validation -> run() == TRUE) 
		{	
			$name = $this-> input-> post('name');
			$id = $this-> input-> post('id');
			$feedback = $this -> MUsers -> checkUser($name,$id);
			if($feedback) 
				$msgBody = '';
			else
				$msgBody = 'User Id already exists';
			if(IS_AJAX){
				echo json_encode(array('feedback' => $feedback,'message' => $msgBody));
			}
		}
		else
		{
			if(IS_AJAX){
				echo json_encode(array('feedback' => 0, 'message' => validation_errors('<div class="validationError">', '</div>')));
			}
		}
	} // checkUser ends

	public function checkPassword()
	{
		$this -> form_validation -> set_rules ('password','Password','trim|required|sha1');
		if ($this -> form_validation -> run() == TRUE) 
		{
			$password = $this-> input-> post('password');
			$id = $this-> input-> post('id');
			$feedback = $this-> MUsers-> checkPassword($password,$id);
			if(IS_AJAX)
			{
				echo json_encode(array('feedback' => $feedback)); 
			}
		}
	}

	public function checkEmail()
	{
		$this -> form_validation -> set_rules ('email','Email','trim|required|valid_email|xss_clean');
		if ($this -> form_validation -> run() == TRUE) 
		{	
			$email = $this-> input-> post('email');
			//echo $name; 
			$id = $this-> input-> post('id');
			$feedback = $this -> MUsers -> checkEmail($email,$id);
			if($feedback) 
				$msgBody ='';
			else
				$msgBody = 'This email already exists';
			if(IS_AJAX){
				echo json_encode(array('feedback' => $feedback,'message' => $msgBody));
			}
		}
		else{
			if(IS_AJAX){
				echo json_encode(array('feedback' => 0, 'message' => validation_errors('<div class="validationError">', '</div>')));
			}
		}
	} // checkEmail ends

	function checkUserTypeName($userTypeId,$userType)
	{
		$userType = urldecode($userType);
		$feedback = $this-> MUsers-> checkUserTypeName($userTypeId,$userType);
		if(IS_AJAX)
			echo json_encode(array('feedback'=>$feedback));
	} // checkUserTypeName ends

	public function checkCarrierName($carrierNameId,$carrierName)
	{
		$carrierName = urldecode($carrierName);
		$feedback = $this-> MCarriers-> checkCarrierName($carrierNameId,$carrierName);
		
		if(IS_AJAX){
			echo json_encode(array('feedback' => $feedback));
		}
		
	} // checkCarrierName ends
	
	public function checkTrafficType($trafficTypeId,$trafficType)
	{
		$trafficType = urldecode($trafficType);
		$feedback = $this-> MCarriers-> checkTrafficType($trafficTypeId,$trafficType);
		
		if(IS_AJAX){
			echo json_encode(array('feedback' => $feedback));
		}
		
	} // checkTrafficType ends

	/// validity check functions
	/******************************************************************************************************************/

	// common controller functions 
	/*****************************************************************************************************************/
	public function passwordReset($userId,$userPassword)
	{
		$editSuccess = $this-> MUsers-> userPasswordReset($userId,$userPassword,0);
		if($editSuccess == 1)
		{
			// send user an email with new password
			$userInfo = $this-> MUsers-> getUserInfo($userId);
			
			
			foreach($userInfo as $list)
			{
				$userNameQuery = $list['userName'];
	
				$messageBody = '';
				$messageBody .= 'Your userId is <strong>'.$userNameQuery.'</strong><br>';
				$messageBody .= 'and your password is <strong>'.$userPassword.'</strong><br>';
				
				$mailSubject = 'Alveron: User Password Reset';
				
				$editSuccess = $this-> userMailSend($userInfo,$messageBody,$mailSubject,0); // 0 means password reset, 1 means user activation
				if($editSuccess == -1) // if mail is not sent 
				{
					$editSuccess = 0;
					$resetFlag = $this-> MUsers-> userPasswordReset($userId,$newPassword,1); // put userPasswordRequestSent to 1 again
				}
			}
				
		}
		return $editSuccess;
	} // passwordReset($userId,$userPassword) ends

	public function userMailSend($userInfo,$messageBody,$mailSubject,$mailSendReason)
	{
		if($userInfo)
		{
			foreach($userInfo as $list)
  			{
  				$userId = $list['userId'];
  				$userTypeId = $list['userTypeId'];
  				$userStatusId = $list['userStatusId'];
  				$userName = $list['userName'];
  				$userFirstName = $list['userFirstName'];
  				$userLastName = $list['userLastName'];
  				$userActivationMailSent = $list['userActivationMailSent'];
  				$userEmail = $list['userEmail'];
  				//echo 'mail send reason '.$mailSendReason;
		   		if(
		   			($userTypeId != 4 && $userStatusId == 1 && $userActivationMailSent == 0 && $mailSendReason == 1) // activation mail send check
		   			|| $mailSendReason == 0 // paswwrod reset mail send check
		   		)
		   		{
		   			
		   			// userTypeId = 4 means Not Assigned, userStatusId = 1 means Active
		   			// userActivationMailSent = 0 means user has not got any activation email
		   			// userActivationOrPasswordReset = 1 means mail to be sent for user activation, = 0 means for password reset

		   			/// First let us write down a beautiful email message body

					$emailMessage = '';
					$emailMessage .= 'Dear '.ucfirst($userFirstName).' '.ucfirst($userLastName).'<br><br>';
					$emailMessage .= $messageBody;
					$emailMessage .= '<br><br><strong>Rates Administrator</strong><br>';
					$emailMessage .= 'Alveron Rate Chart Analysis System';

					$config['protocol'] = 'smtp';
					$config['smtp_host'] =  'mail.alveron.org';//'ssl://smtp.gmail.com'; //'tls://smtp.gmail.com';
					$config['smtp_user'] =  'admin@alveron.org';//'rates@alveron.sg';
					$config['smtp_pass'] = 'Allah#One';
					$config['smtp_port'] = 25;//465;
					$config['smtp_timeout'] = 120; // seconds
					$config['wordwrap'] = TRUE;
					$config['mailtype'] = 'html';
					$config['charset'] =  'utf-8';//'iso-8859-1'; 
					$config['validate'] = FALSE;
				
					$this-> email-> initialize($config);
					
					$this->email->set_newline("\r\n");

					$this-> email-> from('admin@alveron.org','Rates Admin');
					$this-> email-> to($userEmail,ucfirst($userFirstName).' '.ucfirst($userLastName));
					$this-> email-> subject($mailSubject);
					$this-> email-> message($emailMessage);
					//echo $emailMessage;
					if($this-> email-> send())
					{
						if($mailSendReason == 1) // if this email is sent for user activation
						{
							$mailSuccess = $this-> MUsers-> setUserActivationMailSentStatus($userId);
							if($mailSuccess)
								return 1; // mail successfully sent, and database updated
							return -2; // mail successfully sent, but database is not updated	
						}
						// if this email is sent for password reset
						return 1; 
					}
						
					return -1; // if mail is not sent successfully
		   		} // if ends
	 			return 0; // it means that mail is not required to sent
	 		} // foreach ends
	 	} // if($userInfo) ends

	} // userMailSend($userInfo) ends

	public function uploadFiles($fileElementId)
	{
		
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = './uploads';

		// set the allowed file types
		$config['allowed_types'] = 'xls|xlsx';

		// set maximum file size in KB
		$config['max_size'] = 1024 * 10; // 10 MB

		$config['encrypt_name'] = FALSE; // placing it TRUE will encrypt the file name

		// load the upload library
		$this-> load-> library('upload',$config);
		$this-> upload-> initialize($config);
		
		

		$resultData = array();
		//if not successful, set the error message
		if (!$this->upload->do_upload($fileElementId)) // 'userfile'
		{
			//$resultData = array('upload_data' => $this->upload->display_errors());
			$resultData['uploadData'] = $this->upload->display_errors('','');
			$resultData['status'] = 'error';
		} 
		else 
		{ //else, set the success message
			$resultData['uploadData'] = $this-> upload-> data();
			$resultData['status'] = 'success';
			
		}
		return $resultData;
	} // uploadFiles() ends

	public function generateRandomString($name_length = 8) 
	{
		$alpha_numeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$%&!';
		return substr(str_shuffle($alpha_numeric), 0, $name_length);
	} // generateRandomString($name_length = 8)  ends

	public function noPageAccess()
	{
		$msgBody = 'You do not have access to view this page. Contact to System Administrator for further assistance';
		if(IS_AJAX)
			echo json_encode(array('feedback' => -1, 'message'=>$msgBody));
		else
		{
			$this-> session-> set_flashdata('message',$msgBody);
			redirect('alveron','refresh');
		}
	}
	// common controller functions ends
	/************************************************************************************************************/
}
/* End of file alveron.php */
/* Location: ./application/controllers/alveron.php */
