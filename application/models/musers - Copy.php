<?php
	class MUsers extends CI_Model
	{
		function MUsers()
		{
			parent::__construct();
		}

		function verifyUser($userName,$userPassword)
		{
			$this-> db-> select('userId,userName,userTypeId,userStatusId,userEmail');
			$this-> db-> where('userName', $userName);
			$this-> db-> where('userPassword', $userPassword);
			$this-> db-> where('userStatusId', 1); // 1 means Active
			$this-> db-> where('userTypeId !=',4); // 4 means not assigned
			$this-> db-> limit(1);
			$q = $this-> db-> get('tbluser');
			if ($q-> num_rows()> 0)
			{
				return $q-> row_array();

			}
			return null;
		} // verifyUser($userName,$userPassword) ends

		function userAccessEligibility($userId,$userActionId)
		{
			$this-> db-> select('userTypeAccessValue');
			$this-> db-> from('tblusertype as usrType');
			$this-> db-> join('tbluser as usr','usrType.userTypeId = usr.userTypeId');
			$this-> db-> join('tblusertypeaccess as usrTypeAccess','usrType.userTypeId = usrTypeAccess.userTypeId');
			$this-> db-> where('userId',$userId);
			$this-> db-> where('userActionId',$userActionId);
			$this-> db-> where('userTypeAccessValue',1);
			$q = $this-> db-> get();
			if($q-> num_rows() > 0)
				return 1;
			return 0;
		} // userAccessEligibility($userId,$userActionId) ends

		function getUserInfo($userId)
		{
			$data = array();
			
			$selectList = '';
			$selectList = $selectList.'usr.userId as userId,usr.userName as userName,usr.userEmail as userEmail,';
			$selectList = $selectList.'usr.userFirstName as userFirstName, usr.userLastName as userLastName,';
			$selectList = $selectList.'usrStatus.userStatusId as userStatusId, usrStatus.userStatus as userStatus,';
			$selectList = $selectList.'usrType.userTypeId as userTypeId, usrType.userType as userType, userActivationMailSent';

			$this -> db -> select($selectList);
			$this -> db -> from('tbluser as usr');
			$this-> db-> where('userId',$userId);
			$this -> db -> join('tbluserstatus as usrStatus','usr.userStatusId = usrStatus.userStatusId');
			$this -> db -> join('tblusertype as usrType','usr.userTypeId = usrType.userTypeId');
			
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

		function getUserList($selectOption = 0)
		{
			$data = array();
			$this-> db-> where('userTypeId !=',4); // 'Not Assigned' is not selected
			$this-> db-> where('userStatusId !=',3); // 'Activation Pending' is not selected
			$q = $this-> db-> get('tbluser');
			if($q-> num_rows() > 0)
			{
				if($selectOption <> 0)
				{
					$data[0] = 'Select User';
				}
				foreach($q-> result_array() as $row)
				{
					$data[$row['userId']] = ucfirst($row['userName']);
				}
				$q-> free_result();
				return $data;
			}
			return null;
		} // getUserActionList() ends

		function getUserStatusList()
		{
			$data = array();
			$q = $this-> db-> get('tbluserstatus');
			if($q-> num_rows() > 0)
			{
				foreach($q-> result_array() as $row)
				{
					$data[$row['userStatusId']] = ucfirst($row['userStatus']);
				}
				$q-> free_result();
				return $data;
			}
			return null;
		} // getUserStatusList() ends

		function getUserTypeList()
		{
			$data = array();
			$q = $this-> db-> get('tblusertype');
			if($q-> num_rows() > 0)
			{
				foreach($q-> result_array() as $row)
				{
					$data[$row['userTypeId']] = ucfirst($row['userType']);
				}
				$q-> free_result();
				return $data;
			}
			return null;
		} // getUserTypeList() ends

		function getUserTypeListNotAssignedFiltered()
		{
			$data = array();
			$this-> db-> where('userTypeId != ',4);
			$q = $this-> db-> get('tblusertype');
			$data[0] = 'Select User Type';
			if($q-> num_rows() > 0)
			{
				foreach($q-> result_array() as $row)
				{
					$data[$row['userTypeId']] = ucfirst($row['userType']);
				}
				$q-> free_result();
				return $data;
			}
			return null;
		} // getUserTypeListNotAssignedFiltered() ends

		function getUserActionList()
		{
			$data = array();
			$q = $this-> db-> get('tbluseraction');
			if($q-> num_rows() > 0)
			{
				$data[0] = 'Select Action Page';
				foreach($q-> result_array() as $row)
				{
					$data[$row['userActionId']] = ucfirst($row['userAction']);
				}
				$q-> free_result();
				return $data;
			}
			return null;
		} // getUserActionList() ends

		function getRemainingActionList($userTypeId)
		{
			$data = array();
			$qString = "select userActionId,userAction from tbluseraction
				where userActionId not in (
					select userActionId from tblusertypeaccess	
					where userTypeId =".$userTypeId.")
				order by userAction";
			$q = $this-> db-> query($qString);
			if($q-> num_rows() > 0)
			{
				//$data[0] = 'Select Action Page';
				foreach($q-> result_array() as $row)
				{
					$data[$row['userActionId']] = ucfirst($row['userAction']);
				}
				$q-> free_result();
				return $data;
			}
			return null;


		}


		// selectBox queries ends
		/********************************************************************************************************************************/		

		// insert queries
		/********************************************************************************************************************************/		

		function addUser()
		{
			$data = array(
				'userName' => strtolower($this -> input-> post('userName')),
				'userFirstName' => ucfirst($this -> input-> post('userFirstName')),
				'userLastName' => ucfirst($this -> input-> post('userLastName')),
				'userEmail' => strtolower($this -> input-> post('userEmail')),
				'userStatusId' => $this -> input-> post('userStatusId'),
				'userTypeId' => $this -> input-> post('userTypeId'),
				'userPassword' => sha1($this -> input-> post('userPassword'))
			);
			
			if($this-> db-> insert('tbluser', $data) )
				return  $this-> db-> insert_id();
			else return 0;
		} // addUser() ends

		function addUserTypeName($userType)
		{
			$data = array('userType' => $userType);
			if($this-> db-> insert('tblusertype', $data) )
				return  $this-> db-> insert_id();
			else return 0;
		} // addUserTypeName($userType) ends
		
		function addUserTypeAccess()
		{
			$userTypeId = $this-> input-> post('userTypeNew');
			$userActionIdList = $this-> input-> post('userActionNew');
			if($userActionIdList == null)
				return 0;
			foreach($userActionIdList as $userActionId)
			{
				$data = array(
					'userTypeId' => $userTypeId,
					'userActionId' => $userActionId,
					'userTypeAccessValue' => 1
				);
				$this-> db-> insert('tblusertypeaccess', $data);		
			}
			return 1;
		} // addUserTypeAccess() ends
		
		// insert queries ends
		/********************************************************************************************************************************/		

		// edit queries
		/********************************************************************************************************************************/		
		
		function userAccountEdit($userId)
		{
			$data = array(
					'userName' => $this-> input-> post('userName'),
					'userFirstName' => $this-> input-> post('userFirstName'),
					'userLastName' => $this-> input-> post('userLastName'),
					'userPassword' => sha1($this-> input-> post('userNewPassword'))
				);
			$this-> db-> where('userId',$userId);
			$this-> db-> update('tbluser',$data);
			return $this-> db-> affected_rows();
		} // editUserAccount($userId)	ends

		function userPasswordReset($userId,$newPassword,$flag)
		{
			$data = array('userPassword' => sha1($newPassword),'userPasswordResetRequest' => $flag);
			
			$this-> db-> where('userId',$userId);
			$this-> db-> update('tbluser',$data);
			return $this-> db-> affected_rows();						
		} // userPasswordReset($userId,$newPassword) ends

		function sendPasswordResetRequest($userEmail)
		{
			
			$this-> db-> where('userEmail',$userEmail);
			$this-> db-> where('userPasswordResetRequest',1);
			$q = $this-> db-> get('tbluser');
			if($q-> num_rows())
				return -1;

			$data = array('userPasswordResetRequest' => 1);
			$this-> db-> where('userEmail',$userEmail);
			$this-> db-> update('tbluser',$data);
			return $this-> db-> affected_rows();			

		} // sendPasswordResetRequest($userEmail)

		function editUserStatus($userId,$userStatusId)
		{
			$data = array(
					'userStatusId' => $userStatusId
				);
			$this-> db-> where('userId',$userId);
			$this-> db-> update('tbluser',$data);
			return $this-> db-> affected_rows();			

		} // editUserStatus($userId,$userStatusId) ends

		function editUserType($userId,$userTypeId)
		{
			$data = array(
					'userTypeId' => $userTypeId
				);
			$this-> db-> where('userId',$userId);
			$this-> db-> update('tbluser',$data);
			return $this-> db-> affected_rows();			

		} // editUserType($userId,$userTypeId) ends

		function editUserTypeName($userTypeId,$userType)
		{
			$data = array(
					'userType' => $userType
				);
			$this-> db-> where('userTypeId',$userTypeId);
			$this-> db-> update('tblusertype',$data);
			return $this-> db-> affected_rows();						
		} // editUserTypeName($userTypeId,$userType) ends

		function editUserTypeAccess($userTypeAccessId,$userTypeAccessValue)
		{
			$data = array(
					'userTypeAccessValue' => $userTypeAccessValue
				);
			$this-> db-> where('userTypeAccessId',$userTypeAccessId);
			$this-> db-> update('tblusertypeaccess',$data);
			return $this-> db-> affected_rows();
		} // editUserTypeAccess($userTypeAccessId,$userTypeAccessValue) ends

		function setUserActivationMailSentStatus($userId)
		{
			$data = array(
					'userActivationMailSent' => 1
				);
			$this-> db-> where('userId',$userId);
			$this-> db-> update('tbluser',$data);
			return $this-> db-> affected_rows();			

		} // setUserActivationMailSentStatus($userId) ends

		// edit queries ends
		// select queries for count
		/********************************************************************************************************************************/		

		// select queries for count
		/********************************************************************************************************************************/		

		function countUsers()
		{
			return $this->db->count_all('tbluser');
		} // countUsers() ends
		
		function countUserTypes()
		{
			return $this->db->count_all('tblusertype');
		}
		function countUserTypeAccess()
		{
			return $this->db->count_all('tblusertypeaccess');	
		}

		// select queries for count ends
		/********************************************************************************************************************************/		

		// select queries for view
		/********************************************************************************************************************************/

		function getAllUsers($limit = 0, $offset = 0, $userPasswordResetRequest = 0)
		{
			$data = array();
			
			$selectList = '';
			$selectList = $selectList.'usr.userId as userId,usr.userName as userName,usr.userEmail as userEmail,';
			$selectList = $selectList.'usr.userFirstName as userFirstName, usr.userLastName as userLastName,';
			$selectList = $selectList.'usrStatus.userStatusId as userStatusId, usrStatus.userStatus as userStatus,';
			$selectList = $selectList.'usrType.userTypeId as userTypeId, usrType.userType as userType, userActivationMailSent';

			$this -> db -> select($selectList);
			$this -> db -> from('tbluser as usr');
			$this -> db -> join('tbluserstatus as usrStatus','usr.userStatusId = usrStatus.userStatusId');
			$this -> db -> join('tblusertype as usrType','usr.userTypeId = usrType.userTypeId');
			if($userPasswordResetRequest !=0)
				$this-> db-> where('userPasswordResetRequest',1);
			if(!($limit == 0 && $offset == 0))
				$this -> db -> limit($limit,$offset);
			$this-> db->order_by('usrType.userTypeId', 'desc'); 
			$this-> db->order_by('usr.userId', 'asc'); 
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

		function getAllUserTypes($limit=0,$offset=0)
		{
			$data = array();
			if($limit == 0 && $offset == 0)
				$limitString = '';
			elseif($limit <> 0 && $offset == '')
				$limitString = 'limit '.$limit;
			elseif($limit <> 0 && $offset <> '')
				$limitString = 'limit '.$limit.','.$offset;
			$qString = '';
			$qString .= 'select usrType.userTypeId,userType,count(distinct usrTypeAccess.userActionId)as userActionCount,count(distinct usr.userId) as userCount ';
			$qString .= 'from tblusertype as usrType ';
			$qString .= 'left join tbluser as usr on usrType.userTypeId = usr.userTypeId ';
			$qString .= 'left join tblusertypeaccess as usrTypeAccess on usrType.userTypeId = usrTypeAccess.userTypeId ';
			$qString .= 'where usrType.userTypeId != 4 '; // omitting the 'Not Assigned' type
			$qString .= 'group by usrType.userTypeId,userType ';
			$qString .= $limitString;

			$q = $this-> db-> query($qString);

			if($q-> num_rows() > 0)
			{

				foreach ($q->result_array() as $row)
				{
			 		$data[] = $row;
				}
			
				$q->free_result();    
				return $data;
			}
			return null;
		} // getAllUserTypes($limit = 0, $offset=0) ends

		function getAllUserTypeAccess($userTypeId=0,$userActionId=0)
		{
			$data = array();

			$this-> db-> select('userTypeAccessId,userType,userAction,userTypeAccessValue,usrType.userTypeId,usrAction.userActionId');
			$this-> db-> from('tblusertypeaccess usrTypeAccess');
			$this-> db-> join('tblusertype usrType','usrTypeAccess.userTypeId = usrType.userTypeId');
			$this-> db-> join('tbluseraction usrAction','usrTypeAccess.userActionId = usrAction.userActionId');
			if($userTypeId != 0)
				$this-> db-> where('usrType.userTypeId',$userTypeId);
			if($userActionId != 0)
				$this-> db-> where('usrAction.userActionId',$userActionId);
			$this-> db-> order_by('userType');
			$this-> db-> order_by('userAction');
	
			$q = $this-> db-> get();

			if($q-> num_rows() > 0)
			{

				foreach ($q->result_array() as $row)
				{
			 		$data[] = $row;
				}
				$q->free_result();    
				return $data;
			}
			return null;
		} // getAllUserTypeAccess($userTypeId,$userActionId,$perPageRow,$offset) ends

		

		// select queries for view ends
		/********************************************************************************************************************************/
		
		// database validation queries
		/********************************************************************************************************************************/

		function checkUser($name,$id) 
		{
			$this -> db -> select('userName'); 
			$this -> db -> where('userId !=',$id);
			$this -> db -> where('userName',strtolower($name));
			$q = $this -> db -> get('tbluser');
			if($q -> num_rows() > 0)
			{
				$q->free_result();
				return 0; // it means that the user Id is not unique
			}
			$q->free_result();
			return 1;
		} // checkUser($name,$id) ends
		
		function checkEmail($email,$id) 
		{
			$this -> db -> select('userEmail'); 
			$this -> db -> where('userId !=',$id);
			$this -> db -> where('userEmail',strtolower($email));
			$q = $this -> db -> get('tbluser');
			if($q -> num_rows() > 0)
			{
				$q->free_result();
				return 0; // it means that the user Id is not unique
			}
			$q->free_result();
			return 1;
		} // checkEmail($email,$id) ends

		function checkUserTypeName($userTypeId,$userType)
		{
			$this-> db-> select('userType');
			$this-> db-> where('userTypeId !=',$userTypeId);
			$this-> db-> where('lower(userType)',strtolower($userType));
			$q = $this -> db -> get('tblusertype');
			if($q -> num_rows() > 0)
			{
				$q->free_result();
				return 0; // it means that the user Id is not unique
			}
			$q->free_result();
			return 1;
		} // checkUserTypeName($userTypeId,$userType) ends

		function checkPassword($password,$id)
		{
			$this-> db-> where('userPassword',$password);
			$this-> db-> where('userId',$id);
			$q = $this-> db-> get('tbluser');
			if($q-> num_rows() > 0)
			{
				$q-> free_result();
				return 1;
			}
			return 0;	

		}

		// database validation queries ends
		/********************************************************************************************************************************/
	}

/* End of file musers.php */
/* Location: ./application/models/musers.php */	