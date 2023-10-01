<?php
	class MRates extends CI_Model
	{
		function MRates()
		{
			parent::__construct();
		}

		// Rate Upload Functions
		/**********************************************************************************************************/
		//function bulkCarrierPrefixRateInsert($dataFile)
		function bulkCarrierPrefixRateInsert($carrierRateData,$rowCount,$userId)
		{
			

			$bulkInsert = array('rateInsert' => 0, 'rateUpdate' => 0, 'prefixInsert' => 0);
			for($i=1;$i<=$rowCount;$i++)
			{
				
				$carrierId = $carrierRateData[$i][0];
				$country = $carrierRateData[$i][1];
				$destination = $carrierRateData[$i][2];
				$prefix = $carrierRateData[$i][3];
				$rate = $carrierRateData[$i][4];
				$startDateTime = $carrierRateData[$i][5];
				$endDateTime = $carrierRateData[$i][6];
				$rateUploadDate = date('Y-m-d');

				$rateData = array(
					'carrierId' => $carrierId,
					'prefix' => $prefix,
					'rate' => $rate,
					'startDateTime'=> $startDateTime,
					'endDateTime' => $endDateTime,
					'rateUploadDate' => $rateUploadDate,
					'userId' => $userId
				);

				if($this-> db-> insert('tblratechart',$rateData))
				{
					$bulkInsert['rateInsert'] += 1;

					// try insert prefix if it is new
					$prefixData = array('prefix' => $prefix, 'country'=> $country,'destination'=>$destination);
					if($this-> db-> insert('tblprefix',$prefixData))
						$bulkInsert['prefixInsert'] += 1;
				}
				else
				{
					$this-> db-> where('carrierId',$carrierId);
					$this-> db-> where('prefix',$prefix);
					$this-> db-> where('startDateTime',$startDateTime);
					if($this-> db-> update('tblratechart',$rateData))
						$bulkInsert['rateUpdate'] += 1;
				}


			}


			return $bulkInsert;
		} // bulkCarrierPrefixRateInsert($dataFile)
		
		
		// Rate Upload Functions Ends
		/**********************************************************************************************************/
		function bulkCarrierPrefixSpeciaRateInsert($carrierRateData,$rowCount,$userId)
		{
			

			$bulkInsert = array('rateInsert' => 0, 'rateUpdate' => 0, 'prefixInsert' => 0);
			for($i=1;$i<=$rowCount;$i++)
			{
				
				$carrierId = $carrierRateData[$i][0];
				$country = $carrierRateData[$i][1];
				$destination = $carrierRateData[$i][2];
				$prefix = $carrierRateData[$i][3];
				$rate = $carrierRateData[$i][4];
				$asr = $carrierRateData[$i][5];
				$acd = $carrierRateData[$i][6];
				$startDateTime = $carrierRateData[$i][7];
				
				$rateUploadDate = date('Y-m-d');

				$rateData = array(
					'carrierId' => $carrierId,
					'prefix' => $prefix,
					'rate' => $rate,
					'asr' => $asr,
					'acd' => $acd,
					'startDateTime'=> $startDateTime,
					'rateUploadDate' => $rateUploadDate,
					'userId' => $userId
				);

				if($this-> db-> insert('tblspecialratechart',$rateData))
				{
					$bulkInsert['rateInsert'] += 1;

					// try insert prefix if it is new
					$prefixData = array('prefix' => $prefix, 'country'=> $country,'destination'=>$destination);
					if($this-> db-> insert('tblprefix',$prefixData))
						$bulkInsert['prefixInsert'] += 1;
				}
				else
				{
					$this-> db-> where('carrierId',$carrierId);
					$this-> db-> where('prefix',$prefix);
					$this-> db-> where('startDateTime',$startDateTime);
					if($this-> db-> update('tblspecialratechart',$rateData))
						$bulkInsert['rateUpdate'] += 1;
				}


			}


			return $bulkInsert;
		} // bulkCarrierPrefixRateInsert($dataFile)
		
		
		// Rate Upload Functions Ends
		/**********************************************************************************************************/
		// Rate Compare Functions
		/**********************************************************************************************************/

		function getCustomerAndVendorList($customerId) // this function will result customer and its similar type of traffic vendor list
		{
			$data = array();

			// first get the customer carrierNameId,TrafficTypeId
			$this-> db-> select('cr.carrierNameId,carrierName,cr.trafficTypeId,trafficType');
			$this-> db-> from('tblcarrier cr');
			$this-> db-> join('tblcarriername crName','cr.carrierNameId = crName.carrierNameId');
			$this-> db-> join('tbltraffictype trType','cr.trafficTypeId = trType.trafficTypeId');
			$this-> db-> where('carrierId',$customerId);
			$q = $this-> db-> get();
			$customerName = '';
			$customerNameId = 0;
			$customerTrafficTypeId = 0;
			if($q-> num_rows() == 1)
			{
				$row = $q-> row();
				$customerNameId = $row->carrierNameId;
				$customerName = $row->carrierName;
				$customerTrafficTypeId = $row->trafficTypeId;
				$customerTrafficType = $row->trafficType;
			}
			$q-> free_result();

			$data[0]['carrierId'] = $customerId;
			$data[0]['carrierTypeId'] = $customerTrafficTypeId;
			$data[0]['carrierName'] = $customerName;
			$data[0]['trafficType'] = $customerTrafficType;

			// get the similar vendor carrierId and filter it
			$this-> db-> select('carrierId');
			$this-> db-> from('tblcarrier');
			$this-> db-> where('carrierNameId',$customerNameId);
			$this-> db-> where('trafficTypeId',$customerTrafficTypeId);
			$this-> db-> where('carrierTypeId',2);
			$q = $this-> db-> get();
			$vendorCarrierId = 0;
			if($q-> num_rows() == 1)
			{
				$row = $q-> row();
				$vendorCarrierId = $row->carrierId;
			}
			$q-> free_result();

			// now select eligible vendors
			$this-> db-> select('cr.carrierId,cr.carrierTypeId,carrierName,trafficType');
			$this-> db-> from('tblratechart rchart');
			$this-> db-> join('tblcarrier cr','rchart.carrierId = cr.carrierId');
			$this-> db-> join('tblcarriername crName','cr.carrierNameId = crName.carrierNameId');
			$this-> db-> join('tbltraffictype trType','cr.trafficTypeId = trType.trafficTypeId');
			$this-> db-> where('cr.carrierNameId !=',$customerNameId);
			$this-> db-> where('cr.carrierTypeId',2);
			$this-> db-> where('cr.trafficTypeId',$customerTrafficTypeId);
			$this-> db-> group_by('cr.carrierId');
			$q = $this-> db-> get();
		
			if($q-> num_rows() > 0) // means if vendors are there
			{
				
				foreach($q-> result_array() as $row)
					$data[] = $row;
				$q-> free_result();
				return $data;
			}
			return null;

		} // getCustomerAndVendorList($customerId) 

		function marginReport($carrierList,$searchDateTime,$prefix,$country)
		{
			$carrierCount = 0;
			
			if($prefix == '' && $country == '')
				return -1;
			$selectString = 'select prf.country,prf.destination, prf.prefix, ';
			$joinString = ' from tblprefix prf';
	
			foreach($carrierList as $list)
			{
				if($carrierCount != 0)
				{
					$selectString .= ',';
					
				}
				$carrierCount++;

				$selectString .= 'ifnull(carrierRate'.$carrierCount.'.rate,"") carrierRate'.$carrierCount;
				
				$joinString .= ' left join tblratechart carrierRate'.$carrierCount.' on prf.prefix = carrierRate'.$carrierCount.'.prefix';
					$joinString .= ' and carrierRate'.$carrierCount.'.carrierId = '.$list['carrierId'];
					$joinString .= ' and carrierRate'.$carrierCount.'.startDateTime in ';
					$joinString .= ' (select max(startDateTime) from tblratechart where carrierId = '.$list['carrierId']; 
					$joinString .= ' and startDateTime <= "'.$searchDateTime.'" and prefix = prf.prefix) ';
				$joinString .= ' left join tblcarrier carrier'.$carrierCount;
					$joinString .= ' on carrierRate'.$carrierCount.'.carrierId = carrier'.$carrierCount.'.carrierId';
					$joinString .= ' and carrier'.$carrierCount.'.carrierStatusId = 1';
				
			}
			
			$whereString = ' where carrierRate1.rate != "" and carrierRate1.rate != 0 '; // check whether client has the rate
			
			if($prefix != '')
				$whereString .= ' and prf.prefix like "'.$prefix.'" ';

			if($country != '')
				$whereString .= ' and lower(prf.country) like "'.strtolower($country).'" ';

			$finalString = $selectString.$joinString.$whereString.' order by country, prf.prefix';

			//echo $finalString;

			$q = $this-> db-> query($finalString);

			$data = array();
			if($q-> num_rows() > 0)
			{
				foreach ($q-> result_array() as $row)
				{
			 		$data[] = $row;
				}
				$q-> free_result();   
			}
			else
				$data = null;
			 
			return $data; 
		}
	
		function getActiveCarrierList($carrierTypeId=0,$trafficTypeId=0,$selectAll = 0)
		{
			$this-> db-> select('rchart.carrierId,carrierName,trafficType,carrierType');
			$this-> db-> from('tblratechart rchart');
			$this-> db-> join('tblcarrier cr','rchart.carrierId = cr.carrierId');
			$this-> db-> join('tblcarriername crName','cr.carrierNameId = crName.carrierNameId');
			$this-> db-> join('tbltraffictype trType','cr.trafficTypeId = trType.trafficTypeId');
			$this-> db-> join('tblcarriertype crType','cr.carrierTypeId = crType.carrierTypeId');
			$this-> db-> where('carrierStatusId',1); // means carrier is active
			if($carrierTypeId>0)
				$this-> db-> where('cr.carrierTypeId',$carrierTypeId); 
			if($trafficTypeId>0)
				$this-> db-> where('cr.trafficTypeId',$trafficTypeId); 
			$this-> db-> group_by('rchart.carrierId');
			$this-> db-> order_by('carrierType','desc');
			$this-> db-> order_by('carrierName','asc');
			$this-> db-> order_by('trafficType','asc');
			

			$q = $this-> db-> get();
			$data = array();
			if($selectAll == 1)	
				$data[0] = 'Select Partner';
			//echo $q-> num_rows();
			if($q-> num_rows() > 0)
			{
				foreach($q-> result_array() as $row)
				{
					$data[$row['carrierId']] = '['.ucfirst($row['carrierType']).'] '.ucwords($row['carrierName']).' - '.strtoupper($row['trafficType']);	
				}
				$q-> free_result();
			}
			else
				$data = null;
			//var_dump($data);
			return $data;

		} // getActiveCarrierList()

		function getSpecialActiveCarrierList($carrierTypeId=0,$trafficTypeId=0,$selectAll = 0)
		{
			$this-> db-> select('rchart.carrierId,carrierName,trafficType,carrierType');
			$this-> db-> from('tblspecialratechart rchart');
			$this-> db-> join('tblcarrier cr','rchart.carrierId = cr.carrierId');
			$this-> db-> join('tblcarriername crName','cr.carrierNameId = crName.carrierNameId');
			$this-> db-> join('tbltraffictype trType','cr.trafficTypeId = trType.trafficTypeId');
			$this-> db-> join('tblcarriertype crType','cr.carrierTypeId = crType.carrierTypeId');
			$this-> db-> where('carrierStatusId',1); // means carrier is active
			if($carrierTypeId>0)
				$this-> db-> where('cr.carrierTypeId',$carrierTypeId); 
			if($trafficTypeId>0)
				$this-> db-> where('cr.trafficTypeId',$trafficTypeId); 
			$this-> db-> group_by('rchart.carrierId');
			$this-> db-> order_by('carrierType','desc');
			$this-> db-> order_by('carrierName','asc');
			$this-> db-> order_by('trafficType','asc');
			

			$q = $this-> db-> get();
			$data = array();
			if($selectAll == 1)	
				$data[0] = 'Select Partner';
			//echo $q-> num_rows();
			if($q-> num_rows() > 0)
			{
				foreach($q-> result_array() as $row)
				{
					$data[$row['carrierId']] = '['.ucfirst($row['carrierType']).'] '.ucwords($row['carrierName']).' - '.strtoupper($row['trafficType']);	
				}
				$q-> free_result();
			}
			else
				$data = null;
			//var_dump($data);
			return $data;

		} // getspecialActiveCarrierList()


		function getCarrierMaxStartDateTime($searchDateTime,$carrierIdList = null,$carrierTypeId=0,$trafficTypeId=0)
		// this function will detect the elligible partners who have at least one rate having maximum startDateTime which is less than the searchDateTime
		{	
			if($carrierIdList == null)
				return null;
			
			$this-> db-> select('rchart.carrierId, carrierName,trafficType, max(startDateTime) carrierMaxStartDateTime');
			$this-> db-> from('tblratechart rchart');
			$this-> db-> join('tblcarrier cr','rchart.carrierId = cr.carrierId');
			$this-> db-> join('tblcarriername crName','cr.carrierNameId = crName.carrierNameId');
			$this-> db-> join('tbltraffictype trType','cr.trafficTypeId = trType.trafficTypeId');
			$this-> db-> where('carrierStatusId',1); // means carrier is active
			if($carrierTypeId>0)
				$this-> db-> where('carrierTypeId',$carrierTypeId); 
			if($trafficTypeId>0)
				$this-> db-> where('cr.trafficTypeId',$trafficTypeId); 

			$this-> db-> where('startDateTime <=',$searchDateTime);
			
			if((count($carrierIdList) > 1) || (count($carrierIdList) == 1 && $carrierIdList[0] != 0))
				$this-> db-> where_in('rchart.carrierId',$carrierIdList);
				

			$this-> db-> group_by('rchart.carrierId');
			$this-> db-> order_by('trafficType','asc');
			$this-> db-> order_by('carrierName','asc');

			$q = $this-> db-> get();
			$data = array();
			if($q -> num_rows() > 0)
			{
				foreach ($q->result_array() as $row)
				{
			 		$data[] = $row;
				}
				$q->free_result();   
			}
			else
				$data = null;
			 
			return $data; 
		} // getCarrierMaxStartDateTime($carrierId = -1) ends

		function getCarrierMaxSpecialStartDateTime($searchDateTime,$carrierIdList = null,$carrierTypeId=0,$trafficTypeId=0)
		// this function will detect the elligible partners who have at least one rate having maximum startDateTime which is less than the searchDateTime
		{	
			if($carrierIdList == null)
				return null;
			
			$this-> db-> select('rchart.carrierId, carrierName,trafficType, max(startDateTime) carrierMaxStartDateTime');
			$this-> db-> from('tblspecialratechart rchart');
			$this-> db-> join('tblcarrier cr','rchart.carrierId = cr.carrierId');
			$this-> db-> join('tblcarriername crName','cr.carrierNameId = crName.carrierNameId');
			$this-> db-> join('tbltraffictype trType','cr.trafficTypeId = trType.trafficTypeId');
			$this-> db-> where('carrierStatusId',1); // means carrier is active
			if($carrierTypeId>0)
				$this-> db-> where('carrierTypeId',$carrierTypeId); 
			if($trafficTypeId>0)
				$this-> db-> where('cr.trafficTypeId',$trafficTypeId); 

			$this-> db-> where('startDateTime <=',$searchDateTime);
			
			if((count($carrierIdList) > 1) || (count($carrierIdList) == 1 && $carrierIdList[0] != 0))
				$this-> db-> where_in('rchart.carrierId',$carrierIdList);
				

			$this-> db-> group_by('rchart.carrierId');
			$this-> db-> order_by('trafficType','asc');
			$this-> db-> order_by('carrierName','asc');

			$q = $this-> db-> get();
			$data = array();
			if($q -> num_rows() > 0)
			{
				foreach ($q->result_array() as $row)
				{
			 		$data[] = $row;
				}
				$q->free_result();   
			}
			else
				$data = null;
			 
			return $data; 
		} // getCarrierMaxSpecialStartDateTime($carrierId = -1) ends

		function rateCompare($carrierList,$prefix='',$country='',$destination='',$searchDateTime)
		{
			$carrierCount = 0;
			/*if($prefix == '' && $country == '' && $destination == '' )
			{
				return null;
			}*/
			
			$selectString = 'select prf.country,prf.destination, prf.prefix, ';
			$joinString = ' from tblprefix prf';
			$whereString = ' where ( ';

			

			foreach($carrierList as $list)
			{
				if($carrierCount != 0)
				{
					$selectString .= ',';
					$whereString .= ' or ';
				}
					

				$carrierCount++;

				$selectString .= 'ifnull(carrierRate'.$carrierCount.'.rate,"") carrierRate'.$carrierCount.',';
				$selectString .= 'ifnull(date_format(carrierRate'.$carrierCount.'.startDateTime,"%d %b %Y"),"") carrierRateEffectiveFrom'.$carrierCount;

				$joinString .= ' left join tblratechart carrierRate'.$carrierCount.' on prf.prefix = carrierRate'.$carrierCount.'.prefix';
					$joinString .= ' and carrierRate'.$carrierCount.'.carrierId = '.$list['carrierId'];
					$joinString .= ' and carrierRate'.$carrierCount.'.startDateTime in ';
					$joinString .= ' (select max(startDateTime) from tblratechart where carrierId = '.$list['carrierId']; 
					$joinString .= ' and startDateTime <= "'.$searchDateTime.'" and prefix = prf.prefix) ';
				$joinString .= ' left join tblcarrier carrier'.$carrierCount;
					$joinString .= ' on carrierRate'.$carrierCount.'.carrierId = carrier'.$carrierCount.'.carrierId';
					$joinString .= ' and carrier'.$carrierCount.'.carrierStatusId = 1';
				$whereString .= ' carrierRate'.$carrierCount.'.rate != "" ';
			}
			
			$whereString .= ' ) '; // close the rate check
			
			if($prefix != '' && $prefix != null)
				$whereString .= ' and prf.prefix like "'.$prefix.'" ';
			if($country != '' && $country != null)
				$whereString .= ' and lower(prf.country) like "'.strtolower($country).'" ';
			if($destination != '' && $destination != null)
				$whereString .= ' and lower(prf.destination) like "'.strtolower($destination).'" ';

			$finalString = $selectString.$joinString.$whereString.' order by country, prf.prefix';

			//echo $finalString;

			$q = $this-> db-> query($finalString);

			$data = array();
			if($q-> num_rows() > 0)
			{
				foreach ($q-> result_array() as $row)
				{
			 		$data[] = $row;
				}
				$q-> free_result();   
			}
			else
				$data = null;
			 
			return $data; 
		}

		function specialRateCompare($carrierList,$prefix,$country,$destination,$searchDateTime)
		{
			$carrierCount = 0;
			/*if($prefix == '' && $country == '' && $destination == '' )
			{
				return null;
			}*/
			
			$selectString = 'select prf.country,prf.destination, prf.prefix, ';
			$joinString = ' from tblprefix prf';
			$whereString = ' where ( ';

			

			foreach($carrierList as $list)
			{
				if($carrierCount != 0)
				{
					$selectString .= ',';
					$whereString .= ' or ';
				}
					

				$carrierCount++;

				$selectString .= 'ifnull(carrierRate'.$carrierCount.'.rate,"") carrierRate'.$carrierCount.',';
				$selectString .= 'ifnull(carrierRate'.$carrierCount.'.asr,"") asr'.$carrierCount.',';
				$selectString .= 'ifnull(carrierRate'.$carrierCount.'.acd,"") acd'.$carrierCount.',';
				$selectString .= 'ifnull(date_format(carrierRate'.$carrierCount.'.startDateTime,"%d %b %Y"),"") carrierRateEffectiveFrom'.$carrierCount;

				$joinString .= ' left join tblspecialratechart carrierRate'.$carrierCount.' on prf.prefix = carrierRate'.$carrierCount.'.prefix';
					$joinString .= ' and carrierRate'.$carrierCount.'.carrierId = '.$list['carrierId'];
					$joinString .= ' and carrierRate'.$carrierCount.'.startDateTime in ';
					$joinString .= ' (select max(startDateTime) from tblspecialratechart where carrierId = '.$list['carrierId']; 
					$joinString .= ' and startDateTime <= "'.$searchDateTime.'" and prefix = prf.prefix) ';
				$joinString .= ' left join tblcarrier carrier'.$carrierCount;
					$joinString .= ' on carrierRate'.$carrierCount.'.carrierId = carrier'.$carrierCount.'.carrierId';
					$joinString .= ' and carrier'.$carrierCount.'.carrierStatusId = 1';
				$whereString .= ' carrierRate'.$carrierCount.'.rate != "" ';
			}
			
			$whereString .= ' ) '; // close the rate check
			
			if($prefix != '' && $prefix != null)
				$whereString .= ' and prf.prefix like "'.$prefix.'" ';
			if($country != '' && $country != null)
				$whereString .= ' and lower(prf.country) like "'.strtolower($country).'" ';
			if($destination != '' && $destination != null)
				$whereString .= ' and lower(prf.destination) like "'.strtolower($destination).'" ';

			$finalString = $selectString.$joinString.$whereString.' order by country, prf.prefix';

			//echo $finalString;

			$q = $this-> db-> query($finalString);

			$data = array();
			if($q-> num_rows() > 0)
			{
				foreach ($q-> result_array() as $row)
				{
			 		$data[] = $row;
				}
				$q-> free_result();   
			}
			else
				$data = null;
			 
			return $data; 
		}

		// Rate Compare Functions Ends
		/**********************************************************************************************************/
		function getCarrierDateList($dateFrom,$dateTo,$carrierId)
		{
			if($dateFrom > $dateTo)
			{
				$temp = $dateTo;
				$dateTo = $dateFrom;
				$dateFrom = $temp;
			}

			$data =array();
			$dString = 'select distinct date_format(startDateTime,"%Y-%m-%d") startDateTime ';
			$dString .= ' from tblratechart ';
			$dString .= ' where carrierId = '.$carrierId;
			$dString .= ' and startDateTime between "'.$dateFrom.'" and "'.$dateTo.'"';
			$dString .= ' order by startDateTime desc';

			$q = $this-> db-> query($dString);

			if($q-> num_rows()>0)
			{
				foreach($q-> result_array() as $row)
					$data[] = $row;
				$q-> free_result();
				return $data;
			}
			return null;
				
		} // getCarrierDateList($dataFrom,$dateTo,$carrierId)

		function getCarrierSpecialDateList($dateFrom,$dateTo,$carrierId)
		{
			if($dateFrom > $dateTo)
			{
				$temp = $dateTo;
				$dateTo = $dateFrom;
				$dateFrom = $temp;
			}

			$data =array();
			$dString = 'select distinct date_format(startDateTime,"%Y-%m-%d") startDateTime ';
			$dString .= ' from tblspecialratechart ';
			$dString .= ' where carrierId = '.$carrierId;
			$dString .= ' and startDateTime between "'.$dateFrom.'" and "'.$dateTo.'"';
			$dString .= ' order by startDateTime desc';

			$q = $this-> db-> query($dString);

			if($q-> num_rows()>0)
			{
				foreach($q-> result_array() as $row)
					$data[] = $row;
				$q-> free_result();
				return $data;
			}
			return null;
				
		} // getCarrierSpecialDateList($dataFrom,$dateTo,$carrierId)

		function getPartnerLatestUploadDate($carrierId)
		{
			$rateUploadDate = '0000-00-00';
			
			$this-> db-> select('rateUploadDate');
			$this-> db-> where('carrierId',$carrierId);
			$q = $this-> db-> get('tblratechart');
			if($q-> num_rows() > 0)
			{
				$row = $q-> row();
				$q-> free_result();
				$rateUploadDate = $row->rateUploadDate;
			}
			return $rateUploadDate;

		} // getPartnerLatestUploadDate($carrierId) ends

		function getPartnerLatestSpecialUploadDate($carrierId)
		{
			$rateUploadDate = '0000-00-00';
			
			$this-> db-> select('rateUploadDate');
			$this-> db-> where('carrierId',$carrierId);
			$q = $this-> db-> get('tblspecialratechart');
			if($q-> num_rows() > 0)
			{
				$row = $q-> row();
				$q-> free_result();
				$rateUploadDate = $row->rateUploadDate;
			}
			return $rateUploadDate;

		} // getPartnerLatestSpecialUploadDate($carrierId) ends


		function getPartnerLatestRateChart($carrierId,$rateUploadDate,$country,$prefix)
		{
			
			$qString = 'select country,destination,prf.prefix,rate,startDateTime ';
			$qString .= 'from tblratechart rchart join tblprefix prf on rchart.prefix = prf.prefix ';
			$qString .= 'where carrierId = '.$carrierId.' and rateUploadDate ="'.$rateUploadDate.'" ';
			
			if($country != '')
				$qString .= 'and lower(country) like "'.strtolower($country).'" ';
			if($prefix != '')
				$qString .= 'and prf.prefix like "'.$prefix.'" ';

			$qString .= ' order by country, prf.prefix';
			

			$data = array();
			$q = $this-> db-> query($qString);
			if($q-> num_rows() > 0)
			{
				foreach($q-> result_array() as $row)
				{
					$data[] = $row;
				}
				$q-> free_result();
			}
			else
				$data = null;
			return $data;

		} // getPartnerLatestRateChart($carrierId,$rateUploadDate,$country,$prefix) ends

		function getPartnerLatestSpecialRateChart($carrierId,$rateUploadDate,$country,$prefix)
		{
			
			$qString = 'select country,destination,prf.prefix,rate,asr,acd,startDateTime ';
			$qString .= 'from tblspecialratechart rchart join tblprefix prf on rchart.prefix = prf.prefix ';
			$qString .= 'where carrierId = '.$carrierId.' and rateUploadDate ="'.$rateUploadDate.'" ';
			
			if($country != '')
				$qString .= 'and lower(country) like "'.strtolower($country).'" ';
			if($prefix != '')
				$qString .= 'and prf.prefix like "'.$prefix.'" ';

			$qString .= ' order by country, prf.prefix';
			

			$data = array();
			$q = $this-> db-> query($qString);
			if($q-> num_rows() > 0)
			{
				foreach($q-> result_array() as $row)
				{
					$data[] = $row;
				}
				$q-> free_result();
			}
			else
				$data = null;
			return $data;

		} // getPartnerLatestSpecialRateChart($carrierId,$rateUploadDate,$country,$prefix) ends

		function getPartnerRateHistory($carrierId,$dateList,$prefix,$country,$destination)
		{
			/*if($prefix == '' && $country == '' && $destination == '' )
			{
				return null;
			}*/

			$selectString = 'select country,destination,prf.prefix,';
			$joinString = ' from tblprefix prf ';

			$count = 0;
			foreach($dateList as $list)
			{
				
				if($count != 0)
				{
					$selectString .= ',';
				}
				$count++;

				$selectString .= 'ifnull(rchart'.$count.'.rate,"") rate'.$count;
				$joinString .= ' left join tblratechart rchart'.$count.' on rchart'.$count.'.prefix = prf.prefix ';
					$joinString .= ' and rchart'.$count.'.startDateTime = "'.$list['startDateTime'].'" ';
					$joinString .= ' and rchart'.$count.'.carrierId = '.$carrierId;
			}

			$whereString = '';
			
			if($prefix != '' && $country == '' && $destination == '')
				$whereString .= ' where prf.prefix like "'.$prefix.'" ';
			elseif($prefix == '' && $country != '' && $destination == '')
				$whereString .= ' where lower(prf.country) like "'.strtolower($country).'" ';
			elseif($prefix == '' && $country == '' && $destination != '')
				$whereString .= ' where lower(prf.destination) like "'.strtolower($destination).'" ';
			
			elseif($prefix != '' && $country != '' && $destination == '')
				$whereString .= ' where prf.prefix like "'.$prefix.'" and lower(prf.country) like "'.strtolower($country).'" ';
			elseif($prefix != '' && $country == '' && $destination != '')
				$whereString .= ' where prf.prefix like "'.$prefix.'" lower(prf.destination) like "'.strtolower($destination).'" ';
			elseif($prefix == '' && $country != '' && $destination != '')
				$whereString .= ' where lower(prf.country) like "'.strtolower($country).'" lower(prf.destination) like "'.strtolower($destination).'" ';

			elseif($prefix != '' && $country != '' && $destination != '')
				$whereString .= ' where prf.prefix like "'.$prefix.'" and lower(prf.country) like "'.strtolower($country).'" lower(prf.destination) like "'.strtolower($destination).'" ';
			
			$finalString = $selectString.$joinString.$whereString.' order by country, prf.prefix';

	
			//echo $finalString;
			
			$q = $this-> db-> query($finalString);
			
			$data = array();
			if($q-> num_rows() > 0)
			{
				foreach ($q-> result_array() as $row)
				{
			 		$data[] = $row;
				}
				$q-> free_result();   
			}
			else
				$data = null;

			return $data;
		} // getPartnerRateHistory() ends


		function getPartnerSpecialRateHistory($carrierId,$dateList,$prefix,$country,$destination)
		{
			/*if($prefix == '' && $country == '' && $destination == '' )
			{
				return null;
			}*/

			$selectString = 'select country,destination,prf.prefix,';
			$joinString = ' from tblprefix prf ';

			$count = 0;
			foreach($dateList as $list)
			{
				
				if($count != 0)
				{
					$selectString .= ',';
				}
				$count++;

				$selectString .= 'ifnull(rchart'.$count.'.rate,"") rate'.$count.',';
				$selectString .= 'ifnull(rchart'.$count.'.asr,"") asr'.$count.',';
				$selectString .= 'ifnull(rchart'.$count.'.acd,"") acd'.$count;
				$joinString .= ' left join tblspecialratechart rchart'.$count.' on rchart'.$count.'.prefix = prf.prefix ';
					$joinString .= ' and rchart'.$count.'.startDateTime = "'.$list['startDateTime'].'" ';
					$joinString .= ' and rchart'.$count.'.carrierId = '.$carrierId;
			}

			$whereString = '';
			
			if($prefix != '' && $country == '' && $destination == '')
				$whereString .= ' where prf.prefix like "'.$prefix.'" ';
			elseif($prefix == '' && $country != '' && $destination == '')
				$whereString .= ' where lower(prf.country) like "'.strtolower($country).'" ';
			elseif($prefix == '' && $country == '' && $destination != '')
				$whereString .= ' where lower(prf.destination) like "'.strtolower($destination).'" ';
			
			elseif($prefix != '' && $country != '' && $destination == '')
				$whereString .= ' where prf.prefix like "'.$prefix.'" and lower(prf.country) like "'.strtolower($country).'" ';
			elseif($prefix != '' && $country == '' && $destination != '')
				$whereString .= ' where prf.prefix like "'.$prefix.'" lower(prf.destination) like "'.strtolower($destination).'" ';
			elseif($prefix == '' && $country != '' && $destination != '')
				$whereString .= ' where lower(prf.country) like "'.strtolower($country).'" lower(prf.destination) like "'.strtolower($destination).'" ';

			elseif($prefix != '' && $country != '' && $destination != '')
				$whereString .= ' where prf.prefix like "'.$prefix.'" and lower(prf.country) like "'.strtolower($country).'" lower(prf.destination) like "'.strtolower($destination).'" ';
			
			$finalString = $selectString.$joinString.$whereString.' order by country, prf.prefix';

	
			//echo $finalString;
			
			$q = $this-> db-> query($finalString);
			
			$data = array();
			if($q-> num_rows() > 0)
			{
				foreach ($q-> result_array() as $row)
				{
			 		$data[] = $row;
				}
				$q-> free_result();   
			}
			else
				$data = null;

			return $data;
		} // getPartnerSpecialRateHistory() ends


		function getRateCharts($carrierTypeId,$trafficTypeId,$carrierNameId)
		{
			$data = array();
			$this-> db-> select('rchart.carrierId,carrierType,carrierName,cr.trafficTypeId,trafficType,rateUploadDate');
			$this-> db-> from('tblratechart rchart');
			$this-> db-> join('tblcarrier cr','rchart.carrierId = cr.carrierId');
			$this-> db-> join('tblcarriername crName','cr.carrierNameId = crName.carrierNameId');
			$this-> db-> join('tblcarriertype crType','cr.carrierTypeId = crType.carrierTypeId');
			$this-> db-> join('tbltraffictype trType','cr.trafficTypeId = trType.trafficTypeId');
			if($carrierTypeId > 0)
				$this-> db-> where('cr.carrierTypeId',$carrierTypeId);
			if($trafficTypeId > 0)
				$this-> db-> where('cr.trafficTypeId',$trafficTypeId);
			if($carrierNameId > 0)
				$this-> db-> where('cr.carrierNameId',$carrierNameId);
			$this-> db-> group_by('carrierId,rateUploadDate');
			$this-> db-> order_by('carrierType,carrierName,trafficType,rateUploadDate desc');

			$q = $this-> db-> get();

			if($q-> num_rows() > 0)
			{
				foreach($q-> result_array() as $row)
				{
					$data[] = $row;
				}
				$q-> free_result();
				return $data;
			}
			return null;

		} // getRateCharts() ends

		function getSpecialRateCharts($carrierTypeId,$trafficTypeId,$carrierNameId)
		{
			$data = array();
			$this-> db-> select('rchart.carrierId,carrierType,carrierName,cr.trafficTypeId,trafficType,rateUploadDate');
			$this-> db-> from('tblspecialratechart rchart');
			$this-> db-> join('tblcarrier cr','rchart.carrierId = cr.carrierId');
			$this-> db-> join('tblcarriername crName','cr.carrierNameId = crName.carrierNameId');
			$this-> db-> join('tblcarriertype crType','cr.carrierTypeId = crType.carrierTypeId');
			$this-> db-> join('tbltraffictype trType','cr.trafficTypeId = trType.trafficTypeId');
			if($carrierTypeId > 0)
				$this-> db-> where('cr.carrierTypeId',$carrierTypeId);
			if($trafficTypeId > 0)
				$this-> db-> where('cr.trafficTypeId',$trafficTypeId);
			if($carrierNameId > 0)
				$this-> db-> where('cr.carrierNameId',$carrierNameId);
			$this-> db-> group_by('carrierId,rateUploadDate');
			$this-> db-> order_by('carrierType,carrierName,trafficType,rateUploadDate desc');

			$q = $this-> db-> get();

			if($q-> num_rows() > 0)
			{
				foreach($q-> result_array() as $row)
				{
					$data[] = $row;
				}
				$q-> free_result();
				return $data;
			}
			return null;

		} // getSpecialRateCharts() ends

		function deleteRateChart($carrierId,$rateUploadDate)
		{
			$this-> db-> where('carrierId',$carrierId);
			$this-> db-> where('rateUploadDate',$rateUploadDate);
			if($this-> db-> delete('tblratechart'))
				return 1;
			return 0;
		}

		function deleteSpecialRateChart($carrierId,$rateUploadDate)
		{
			$this-> db-> where('carrierId',$carrierId);
			$this-> db-> where('rateUploadDate',$rateUploadDate);
			if($this-> db-> delete('tblspecialratechart'))
				return 1;
			return 0;
		}

		function getCarrierInfoForRateChartEdit($carrierId,$rateUploadDate)
		{
			$this-> db-> select('rchart.carrierId,carrierType,cr.carrierTypeId,carrierName,cr.carrierNameId,cr.trafficTypeId,rateUploadDate');
			$this-> db-> from('tblratechart rchart');
			$this-> db-> join('tblcarrier cr','rchart.carrierId = cr.carrierId');
			$this-> db-> join('tblcarriername crName','cr.carrierNameId = crName.carrierNameId');
			$this-> db-> join('tblcarriertype crType','cr.carrierTypeId = crType.carrierTypeId');
			$this-> db-> where('cr.carrierId',$carrierId);
			$this-> db-> where('rateUploadDate',$rateUploadDate);
			$this-> db-> group_by('cr.carrierId, rateUploadDate');
			$q = $this-> db-> get();
			$data = array();
			if($q-> num_rows() > 0)
			{
				foreach($q-> result_array() as $row)
				{
					$data[] = $row;				
				}
				$q-> free_result();
				return $data;
			}
			return null;
		}

		function getCarrierInfoForSpecialRateChartEdit($carrierId,$rateUploadDate)
		{
			$this-> db-> select('rchart.carrierId,carrierType,cr.carrierTypeId,carrierName,cr.carrierNameId,cr.trafficTypeId,rateUploadDate');
			$this-> db-> from('tblspecialratechart rchart');
			$this-> db-> join('tblcarrier cr','rchart.carrierId = cr.carrierId');
			$this-> db-> join('tblcarriername crName','cr.carrierNameId = crName.carrierNameId');
			$this-> db-> join('tblcarriertype crType','cr.carrierTypeId = crType.carrierTypeId');
			$this-> db-> where('cr.carrierId',$carrierId);
			$this-> db-> where('rateUploadDate',$rateUploadDate);
			$this-> db-> group_by('cr.carrierId, rateUploadDate');
			$q = $this-> db-> get();
			$data = array();
			if($q-> num_rows() > 0)
			{
				foreach($q-> result_array() as $row)
				{
					$data[] = $row;				
				}
				$q-> free_result();
				return $data;
			}
			return null;
		}

		function updateCarrierTrafficTypeOfRateChart($carrierIdEdit,$rateUploadDate,$trafficTypeEdit)
		{
			// get carrierNameId,carrierTypeId,trafficTypeId of old carrierId
			$this-> db-> select('carrierNameId,carrierTypeId,trafficTypeId');
			$this-> db-> from('tblcarrier');
			$this-> db-> where('carrierId',$carrierIdEdit);
			$qOldCarrierId = $this-> db-> get();
			
			$oldData = array();
			$carrierNameId = 0;
			$carrierTypeId = 0;
			$trafficTypeId = 0;
			
			if($qOldCarrierId-> num_rows() > 0)
			{
				foreach ($qOldCarrierId-> result_array() as $row) 
				{
					$oldData[] = $row;
				}
				$qOldCarrierId-> free_result();

				foreach($oldData as $list)
				{
					$carrierNameId = $list['carrierNameId'];
					$carrierTypeId = $list['carrierTypeId'];
					$trafficTypeId = $list['trafficTypeId'];
				}

				if($trafficTypeId == $trafficTypeEdit)
					return 1; // if traffic type id is not changed, then do nothing
				
				// if traffic type id is changed, then find the corresponding carrierId
				$this-> db-> select('carrierId');
				$this-> db-> from('tblcarrier');
				$this-> db-> where('carrierTypeId',$carrierTypeId);
				$this-> db-> where('carrierNameId',$carrierNameId);
				$this-> db-> where('trafficTypeId',$trafficTypeEdit);
				$qNewCarrierId = $this-> db-> get();

				// get the required 
				$newData = array();
				$carrierIdNew = 0;

				if($qNewCarrierId-> num_rows()>0)
				{
					foreach ($qNewCarrierId-> result_array() as $row) 
					{
						$newData[] = $row;
					}
					$qNewCarrierId-> free_result();
					foreach($newData as $list)
					{
						$carrierIdNew = $list['carrierId'];
					}

					// if selected carrier Id is inactive, then make it active
					$carrierUpdate = array('carrierStatusId'=>1);
					$this-> db-> where('carrierId',$carrierIdNew);
					if(!$this-> db-> update('tblcarrier',$carrierUpdate))
						return -1;

				}	

				// first delete rates of same carrierIdNew and rateUploadDate 
				$this-> db-> where('carrierId',$carrierIdNew);
				$this-> db-> where('rateUploadDate',$rateUploadDate);
				$this-> db-> delete('tblratechart');

				// update rates with carrierIdOld and rateUploadDate with carrierIdNew
				$rateUpdate = array('carrierId' => $carrierIdNew);
				$this-> db-> where('carrierId',$carrierIdEdit);
				$this-> db-> where('rateUploadDate',$rateUploadDate);
				if($this-> db-> update('tblratechart',$rateUpdate))
					return 1;
				return -3;
			}
			return 0;

		} // updateCarrierTrafficTypeOfRateChart($carrierIdEdit,$rateUploadDate,$trafficTypeId) ends

		function updateCarrierTrafficTypeOfSpecialRateChart($carrierIdEdit,$rateUploadDate,$trafficTypeEdit)
		{
			// get carrierNameId,carrierTypeId,trafficTypeId of old carrierId
			$this-> db-> select('carrierNameId,carrierTypeId,trafficTypeId');
			$this-> db-> from('tblcarrier');
			$this-> db-> where('carrierId',$carrierIdEdit);
			$qOldCarrierId = $this-> db-> get();
			
			$oldData = array();
			$carrierNameId = 0;
			$carrierTypeId = 0;
			$trafficTypeId = 0;
			
			if($qOldCarrierId-> num_rows() > 0)
			{
				foreach ($qOldCarrierId-> result_array() as $row) 
				{
					$oldData[] = $row;
				}
				$qOldCarrierId-> free_result();


				foreach($oldData as $list)
				{
					$carrierNameId = $list['carrierNameId'];
					$carrierTypeId = $list['carrierTypeId'];
					$trafficTypeId = $list['trafficTypeId'];
				}

				if($trafficTypeId == $trafficTypeEdit)
					return 1; // if traffic type id is not changed, then do nothing
				
				// if traffic type id is changed, then find the corresponding carrierId
				$this-> db-> select('carrierId');
				$this-> db-> from('tblcarrier');
				$this-> db-> where('carrierTypeId',$carrierTypeId);
				$this-> db-> where('carrierNameId',$carrierNameId);
				$this-> db-> where('trafficTypeId',$trafficTypeEdit);
				$qNewCarrierId = $this-> db-> get();

				// get the required 
				$newData = array();
				$carrierIdNew = 0;

				if($qNewCarrierId-> num_rows()>0)
				{
					foreach ($qNewCarrierId-> result_array() as $row) 
					{
						$newData[] = $row;
					}
					$qNewCarrierId-> free_result();
					foreach($newData as $list)
					{
						$carrierIdNew = $list['carrierId'];
					}

					// if selected carrier Id is inactive, then make it active
					$carrierUpdate = array('carrierStatusId'=>1);
					$this-> db-> where('carrierId',$carrierIdNew);
					if(!$this-> db-> update('tblcarrier',$carrierUpdate))
						return -1;

				}	

				// first delete rates of same carrierIdNew and rateUploadDate 
				$this-> db-> where('carrierId',$carrierIdNew);
				$this-> db-> where('rateUploadDate',$rateUploadDate);
				$this-> db-> delete('tblspecialratechart');

				// update rates with carrierIdOld and rateUploadDate with carrierIdNew
				$rateUpdate = array('carrierId' => $carrierIdNew);
				$this-> db-> where('carrierId',$carrierIdEdit);
				$this-> db-> where('rateUploadDate',$rateUploadDate);
				if($this-> db-> update('tblspecialratechart',$rateUpdate))
					return 1;
				return -3;
			}
			return 0;

		} // updateCarrierTrafficTypeOfSpecialRateChart($carrierIdEdit,$rateUploadDate,$trafficTypeId) ends
	}

/* End of file mrates.php */
/* Location: ./application/models/mrates.php */	