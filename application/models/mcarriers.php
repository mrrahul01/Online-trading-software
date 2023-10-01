<?php
	class MCarriers extends CI_Model
	{
		function MCarriers()
		{
			parent::__construct();
		}

		
		function getCarrierInfo($carrierId)
		{
			$data = array();
			
			$this -> db -> select('carrierName,carrierId,carrierType,trafficType,carrierStatusId');
			$this -> db -> from('tblcarrier as carrier');
			$this -> db -> join('tblcarriername as crName','carrier.carrierNameId = crName.carrierNameId');
			$this -> db -> join('tblcarriertype as crType','carrier.carrierTypeId = crType.carrierTypeId');
			$this -> db -> join('tbltraffictype as trfType','carrier.trafficTypeId = trfType.trafficTypeId');
			

			$this-> db-> where('carrierId',$carrierId);

			
			$q = $this -> db -> get();

			if($q -> num_rows() > 0){
				foreach ($q->result_array() as $row)
				{
			 		$data[] = $row;
				}
			}
			$q->free_result();    
			return $data; 
		} // getAllUsers($limit = 0, $offset = 0) ends


		// selectBox queries
		/********************************************************************************************************************************/		

		function getCarrierTypeList($selectOption = 0)
		{
			$data = array();
			$this-> db-> order_by('carrierType','desc');
			$q = $this-> db-> get('tblcarriertype');
			if($q-> num_rows() > 0)
			{
				if($selectOption == 0)
					$data[0] = 'Select Partner Type';
				foreach($q-> result_array() as $row)
				{
					$data[$row['carrierTypeId']] = ucfirst($row['carrierType']);
				}
				$q-> free_result();
				return $data;
			}
			return null;
		} // getCarrierTypeList() ends

		function getTrafficTypeList($selectOption = 0)
		{
			$data = array();
			$this-> db-> order_by('trafficType');
			$q = $this-> db-> get('tbltraffictype');
			
			if($q-> num_rows() > 0)
			{
				if($selectOption == 0)
					$data[0] = 'Select Traffic Type';
				foreach($q-> result_array() as $row)
				{
					$data[$row['trafficTypeId']] = ucfirst($row['trafficType']);
				}
				$q-> free_result();
				return $data;
			}
			return null;
		} // getTrafficTypeList() ends

		function getCarrierNameList($selectOption = 0)
		{
			$data = array();
			$this-> db-> order_by('carrierName');
			$q = $this-> db-> get('tblcarriername');
			if($q-> num_rows() > 0)
			{
				if($selectOption == 0)
					$data[0] = 'Select Partner Name';
				foreach($q-> result_array() as $row)
				{
					$data[$row['carrierNameId']] = ucfirst($row['carrierName']);
				}
				$q-> free_result();
				return $data;
			}
			return null;
		} // getCarrierNameList() ends

		

		function getCarrierListWithTrafficTypeAndCarrierType()
		{
			$data = array();
			$this-> db-> select('carrierId,carrierType,carrierName,trafficType');
			$this-> db-> from ('tblcarrier as cr');
			$this-> db-> join('tblcarriername as crName','crName.carrierNameId = cr.carrierNameId');
			$this-> db-> join('tblcarriertype as crType','crType.carrierTypeId = cr.carrierTypeId');
			$this-> db-> join('tbltraffictype as trType','trType.trafficTypeId = cr.trafficTypeId');
			$this-> db-> where('cr.carrierStatusId',1);
			$this-> db-> order_by('carrierType','desc');
			$this-> db-> order_by('carrierName','asc');
			$this-> db-> order_by('trafficType','asc');
			
			$q = $this-> db-> get();
			
			if($q-> num_rows() > 0)
			{
				$data[0] = 'Alveron Customer Offer';
				foreach($q-> result_array() as $row)
				{
					$data[$row['carrierId']] = '['.ucfirst($row['carrierType']).'] '.ucfirst($row['carrierName']).' - '.ucfirst($row['trafficType']);
				}
				$q-> free_result();
				return $data;
			}
			return null;

		} // getCarrierListWithTrafficTypeAndCarrierType() ends


		// selectBox queries ends
		/********************************************************************************************************************************/		

		// insert queries
		/********************************************************************************************************************************/		

		function addCarrierName()
		{
			$data = array(
				'carrierName' => strtolower($this -> input-> post('carrierNameNew')),
				'carrierDescription' => $this -> input-> post('carrierDescriptionNew')
			);
			
			if($this-> db-> insert('tblcarriername', $data) )
				return  $this-> db-> insert_id();
			else return 0;
		} // addCarrierName() ends

		function addTrafficType()
		{
			$data = array(
				'trafficType' => ucfirst($this -> input-> post('trafficTypeNew')),
				'trafficDescription' => $this -> input-> post('trafficDescriptionNew')
			);
			if($this-> db-> insert('tbltraffictype', $data) )
				return  $this-> db-> insert_id();
			else return 0;
		} // addTrafficType() ends
		
		function addCarrier()
		{
			
			$carrierNameId = $this-> input-> post('carrierNameNew');
			$carrierTypeId = $this-> input-> post('carrierTypeNew');
			$trafficTypeIdList = $this-> input-> post('trafficTypeNew');
			if($trafficTypeIdList == null)
				return -2;
			$count = 0;
			foreach($trafficTypeIdList as $trafficTypeId)
			{
				$data = array(
					'carrierNameId' => $carrierNameId,
					'carrierTypeId' => $carrierTypeId,
					'trafficTypeId' => $trafficTypeId
				);	
				if($this-> db-> insert('tblcarrier', $data))
					$count += 1;

			}
			//echo $count;
			return $count;
		} // addCarrier() ends
				
		// insert queries ends
		/********************************************************************************************************************************/		

		// edit queries
		/********************************************************************************************************************************/		
		
		function editCarrierName($carrierNameId,$carrierName)
		{
			$data = array('carrierName' => strtolower($carrierName));
			$this-> db-> where('carrierNameId',$carrierNameId);
			$this-> db-> update('tblcarriername',$data);
			return $this-> db-> affected_rows();
		} // carrierNametEdit($carrierId) ends

		function editCarrierDescription($carrierNameId,$carrierDescription)
		{
			$data = array('carrierDescription' => $carrierDescription);
			$this-> db-> where('carrierNameId',$carrierNameId);
			$this-> db-> update('tblcarriername',$data);
			return $this-> db-> affected_rows();
		} // carrierNametEdit($carrierId) ends
		
		function editTrafficType($trafficTypeId,$trafficType)
		{
			$data = array('trafficType' => ucfirst($trafficType));
			$this-> db-> where('trafficTypeId',$trafficTypeId);
			$this-> db-> update('tbltraffictype',$data);
			return $this-> db-> affected_rows();

		} // trafficTypeEdit($trafficTypeId) ends

		function editTrafficDescription($trafficTypeId,$trafficDescription)
		{
			$data = array('trafficDescription' => $trafficDescription);
			$this-> db-> where('trafficTypeId',$trafficTypeId);
			$this-> db-> update('tbltraffictype',$data);
			return $this-> db-> affected_rows();

		} // trafficTypeEdit($trafficTypeId) ends

		function editCarrierStatusId($carrierId,$carrierStatusId)
		{
			$data = array('carrierStatusId' => $carrierStatusId);
			$this-> db-> where('carrierId',$carrierId);
			$this-> db-> update('tblcarrier',$data);
			return $this-> db-> affected_rows();

		}


		// edit queries ends
		// select queries for count
		/********************************************************************************************************************************/		


		// select queries for count
		/********************************************************************************************************************************/		

		

		// select queries for count ends
		/********************************************************************************************************************************/		

		// select queries for view
		/********************************************************************************************************************************/

		function getAllCarriers($carrierTypeId = 0, $trafficTypeId = 0, $carrierStatusId = -1)
		{
			$data = array();
			
			
			$this -> db -> select('carrierName,carrierId,carrierType,trafficType,carrierStatusId');
			$this -> db -> from('tblcarrier as carrier');
			$this -> db -> join('tblcarriername as crName','carrier.carrierNameId = crName.carrierNameId');
			$this -> db -> join('tblcarriertype as crType','carrier.carrierTypeId = crType.carrierTypeId');
			$this -> db -> join('tbltraffictype as trType','carrier.trafficTypeId = trType.trafficTypeId');
			
			if($carrierTypeId>0)
				$this-> db-> where('crType.carrierTypeId',$carrierTypeId);
			if($trafficTypeId>0)
				$this-> db-> where('trType.trafficTypeId',$trafficTypeId);
			if($carrierStatusId>=0)
				$this-> db-> where('carrierStatusId',$carrierStatusId);
			
			
			$this-> db->order_by('carrierStatusId', 'desc'); 
			$this-> db->order_by('carrierType', 'desc'); 
			$this-> db->order_by('carrierName', 'asc'); 
			$this-> db->order_by('trafficType', 'asc'); 
			

			$q = $this -> db -> get();

			if($q -> num_rows() > 0)
			{
				foreach ($q->result_array() as $row)
				{
			 		$data[] = $row;
				}
			}
			$q->free_result();    
			return $data; 
		} // getAllCarriers($limit = 0, $offset = 0) ends

		function getAllCarrierList($limit =0 ,$offset = 0)
		{
			$data = array();
			if(!($limit == 0 && $offset == 0))
				$this -> db -> limit($limit,$offset);
			$this-> db->order_by('carrierName', 'asc'); 
			$q = $this-> db-> get('tblcarriername');
			if($q -> num_rows() > 0)
			{
				foreach ($q->result_array() as $row)
				{
			 		$data[] = $row;
				}
			}
			$q->free_result();    
			return $data; 

		} //getAllCarrierList($limit =0 ,$offset = 0) ends

		function getAllTrafficTypeList($limit =0 ,$offset = 0)
		{
			$data = array();
			if(!($limit == 0 && $offset == 0))
				$this -> db -> limit($limit,$offset);
			$this-> db->order_by('trafficType', 'asc'); 
			$q = $this-> db-> get('tbltraffictype');
			if($q -> num_rows() > 0)
			{
				foreach ($q->result_array() as $row)
				{
			 		$data[] = $row;
				}
			}
			$q->free_result();    
			return $data; 

		} //getAllTrafficTypeList($limit =0 ,$offset = 0) ends



		// select queries for view ends
		/********************************************************************************************************************************/
		
		// database validation queries
		/********************************************************************************************************************************/

		function checkCarrierName($carrierNameId,$carrierName)
		{
			$this -> db -> select('carrierName'); 
			$this -> db -> where('carrierNameId !=',$carrierNameId);
			$this -> db -> where('lower(carrierName)',strtolower($carrierName));
			$q = $this -> db -> get('tblcarriername');
			if($q -> num_rows() > 0)
			{
				$q->free_result();
				return 0; // it means that the user Id is not unique
			}
			$q->free_result();
			return 1;
		} // checkCarrierName($carrierNameId,$carrierName) ends
		
		function checkTrafficType($trafficTypeId,$trafficType) 
		{
			$this -> db -> select('trafficType'); 
			$this -> db -> where('trafficTypeId !=',$trafficTypeId);
			$this -> db -> where('lower(trafficType)',strtolower($trafficType));
			$q = $this -> db -> get('tbltraffictype');
			if($q -> num_rows() > 0)
			{
				$q->free_result();
				return 0; // it means that the user Id is not unique
			}
			$q->free_result();
			return 1;
		} // checkTrafficType($trafficTypeId,$trafficType)  ends


		// database validation queries ends
		/********************************************************************************************************************************/
	}

/* End of file mcarriers.php */
/* Location: ./application/models/mcarriers.php */	