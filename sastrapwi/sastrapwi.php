<?php

/**
 * An API for SASTRA's Parent Web Interface.
 * @author: Vignesh Rajagopalan <vignesh@campuspry.com>
 *
 * A one day hack! (@date 07-07-2011)
 */

require('phpQuery.php');

class SASTRA
{
	/**
	 * BETA
	 * @version 1.0
	 */

	/**
	 * The 2 Params!
	 */
	protected $regno; //Register Number of the Student.
	
	protected $pass; //Password => Birthday(ddmmyyyy)
	
	/**
	 * Initialize the API.
	 */
	public function __construct($params) {
		$this->setRegNo($params['regno']);
		$this->setPass($params['pass']);
		$this->setCurlBehaviour();
		$this->loginToPWI();
	}
	
	/**
	 * Set the Params.
	 */
	public function setRegNo($regno){
		$this->regno = $regno;
		return $this;
	}
	
	public function setPass($pass){
		$this->pass = $pass;
		return $this;
	}
	
 
	/**
	 * Set the required CURL Behaviour.
	 */
	public function setCurlBehaviour(){
		$options = array(CURLOPT_POST => true,
										 CURLOPT_FOLLOWLOCATION => true,
										 CURLOPT_COOKIEJAR => "cookies.txt",
										 CURLOPT_COOKIEFILE => "cookies.txt",
										 CURLOPT_RETURNTRANSFER => true,
                 		 CURLOPT_HEADER => false
                	  );
     $this->ch = curl_init();
     curl_setopt_array($this->ch, $options);
     return $this;
	}

	/**
	 * Login to the PWI
	 */
	function loginToPWI(){
		if (isset($this->regno) && isset($this->pass)) {
			$ch = $this->ch;
			
			curl_setopt($ch, CURLOPT_URL, "http://webstream.sastra.edu/sastrapwi/usermanager/youLogin.jsp");
			curl_setopt($ch, CURLOPT_POSTFIELDS, "txtRegNumber=iamalsouser&txtPwd=thanksandregards&txtSN={$this->regno}&txtPD={$this->pass}&txtPA=1");
			curl_setopt ($ch, CURLOPT_REFERER, "http://webstream.sastra.edu/sastrapwi/usermanager/youLogin.jsp");
			$this->home = curl_exec($ch);
			//echo curl_error($ch).'<br /><br />'.$home;
		} else die("Register Number or Password not set.");
	}
	
	/**
	 * Fetch Attendance.
	 */
	public function getAttendance() {
		if (isset($this->regno) && isset($this->pass)) {
		
			/**
			 * Fetch the Attendance from PWI
			 */
			$ch = $this->ch;
			curl_setopt($ch, CURLOPT_URL, "http://webstream.sastra.edu/sastrapwi/resource/StudentDetailsResources.jsp?resourceid=7");
			curl_setopt ($ch, CURLOPT_REFERER, "http://webstream.sastra.edu/sastrapwi/usermanager/home.jsp");
			$html = curl_exec($ch);
			
			/**
			 * Parse the content.
			 */
			phpQuery::newDocument($html);
			pq('table:not(:first)')->remove();
			pq('td:not(.tablecontent01,.tablecontent02,.tablecontent03,.tabletitle05)')->remove();
			pq('tr:empty')->remove();
			pq('tr:first')->remove();
			pq('tr:first')->remove();
			
			$rows = pq('table tr');
			$attendance = array();
			foreach ($rows as $key => $row) {
				if (pq($row)->find('td:eq(0)')->text() != ' TOTAL ') {
				 	$attendance[$key]['SUBCODE'] = pq($row)->find('td:eq(0)')->text();
				 	$attendance[$key]['SUBNAME'] = pq($row)->find('td:eq(1)')->text();
				 	$attendance[$key]['TOTAL'] = pq($row)->find('td:eq(2)')->text();
				 	$attendance[$key]['PRESENT'] = pq($row)->find('td:eq(3)')->text();
				 	$attendance[$key]['ABSENT'] = pq($row)->find('td:eq(4)')->text();
				 	$attendance[$key]['%'] = pq($row)->find('td:eq(5)')->text();
				} else {
					$attendance['%'] = pq($row)->find('td:eq(4)')->text();	
				}
			}
			
			/**
			 * Encode into JSON and return. 
			 */
			return json_encode($attendance);
		} else die("Register Number or Password not set.");
	}

	/**
	 * Fetch Attendace Details.
	 */
	public function getAttendanceDetails() {
		if (isset($this->regno) && isset($this->pass)) {
		
			/**
			 * Get the Attendance Details from PWI
			 */
			$ch = $this->ch;
			curl_setopt($ch, CURLOPT_URL, "http://webstream.sastra.edu/sastrapwi/resource/StudentDetailsResources.jsp?resourceid=25");
			curl_setopt ($ch, CURLOPT_REFERER, "http://webstream.sastra.edu/sastrapwi/usermanager/home.jsp");
			$html = curl_exec($ch);

			/**
			 * Parse the content.
			 */
			phpQuery::newDocument($html);
			pq('table:not(:first)')->remove();
			pq('td:not(.tablecontent01,.tablecontent02,.tablecontent03,.tabletitle05)')->remove();
			pq('tr:empty')->remove();
			pq('tr:first')->remove();
			pq('tr:first')->remove();

			$rows = pq('table tr');
			$details = array();
			foreach ($rows as $key => $row) {
				$details[$key]['DATE'] = pq($row)->find('td:eq(1)')->text();
			 	$details[$key]['SUBCODE'] = pq($row)->find('td:eq(2)')->text();
			 	$details[$key]['SUBNAME'] = pq($row)->find('td:eq(3)')->text();
			 	$details[$key]['HOUR'] = pq($row)->find('td:eq(4)')->text();
			}
			
			/**
			 * Encode into a JSON object and return.
			 */			
			return json_encode($details);
		} else die("Register Number or Password not set.");
	}

	/**
	 * Fetch Internal Marks.
	 */	
	public function getInternalMarks() {
		if (isset($this->regno) && isset($this->pass)) {
			$ch = $this->ch;
			curl_setopt($ch, CURLOPT_URL, "http://webstream.sastra.edu/sastrapwi/resource/StudentDetailsResources.jsp?resourceid=22");
			curl_setopt ($ch, CURLOPT_REFERER, "http://webstream.sastra.edu/sastrapwi/usermanager/home.jsp");
			$details = curl_exec($ch);
			echo $details;
		} else die("Register Number or Password not set.");
	}

	/**
	 * Fetch Credits.
	 */	
	public function getGrades() {
		if (isset($this->regno) && isset($this->pass)) {
			$ch = $this->ch;
			curl_setopt($ch, CURLOPT_URL, "http://webstream.sastra.edu/sastrapwi/resource/StudentDetailsResources.jsp?resourceid=21");
			curl_setopt ($ch, CURLOPT_REFERER, "http://webstream.sastra.edu/sastrapwi/usermanager/home.jsp");
			$details = curl_exec($ch);
			echo $details;
		} else die("Register Number or Password not set.");
	}
	
	public function getTimeTable() {
		if (isset($this->regno) && isset($this->pass)) {
		
			/**
			 * Get the timetable from PWI.
			 */	
			$ch = $this->ch;
			curl_setopt($ch, CURLOPT_URL, "http://webstream.sastra.edu/sastrapwi/academy/frmStudentTimetable.jsp");
			curl_setopt ($ch, CURLOPT_REFERER, "http://webstream.sastra.edu/sastrapwi/usermanager/home.jsp");
			$html = curl_exec($ch);

			/**
			 * Parse the timetable.
			 */	
			phpQuery::newDocument($html);
			pq('table[bgcolor="#eeeeee"]')->remove();
			pq('table:not(:first) tr')->appendTo('table:first');
			pq('table:empty')->remove();
			pq('table tr:empty')->remove();
			pq('tr:first')->remove();
			pq('tr:first')->remove();
			pq('tr:first')->remove(); 
			pq('tr:first')->remove();
			pq('tr:first')->remove();
			pq('tr:first')->remove(); 

			$rows = pq('table tr');
			$details = array();
			foreach ($rows as $key => $row) {
				
				$week = pq($row)->find('td:eq(0)')->text();
				
				if (pq($row)->find('td:eq(1)')->text() != NULL)
					$details[$week]['08:40-09:30'] = pq($row)->find('td:eq(1)')->text();
				else $details[$week]['08:40-09:30'] = "FREE";
				
				if (pq($row)->find('td:eq(2)')->text() != NULL)
					$details[$week]['09:30-10:20'] = pq($row)->find('td:eq(2)')->text();
				else $details[$week]['09:30-10:20'] = "FREE";
				
				if (pq($row)->find('td:eq(3)')->text() != NULL)
					$details[$week]['10:20-11:10'] = pq($row)->find('td:eq(3)')->text();
				else $details[$week]['10:20-11:10'] = "FREE";
				
				if (pq($row)->find('td:eq(4)')->text() != NULL)
					$details[$week]['11:30-12:20'] = pq($row)->find('td:eq(4)')->text();
				else $details[$week]['11:30-12:20'] = "FREE";
				
				if (pq($row)->find('td:eq(5)')->text() != NULL)
					$details[$week]['12:20-13:10'] = pq($row)->find('td:eq(5)')->text();
				else $details[$week]['12:20-13:10'] = "FREE";
				
				if (pq($row)->find('td:eq(6)')->text() != NULL)
					$details[$week]['13:10-14:00'] = pq($row)->find('td:eq(6)')->text();
				else $details[$week]['13:10-14:00'] = "FREE";
				
				if (pq($row)->find('td:eq(7)')->text() != NULL)
					$details[$week]['14:20-15:10'] = pq($row)->find('td:eq(7)')->text();
				else $details[$week]['14:20-15:10'] = "FREE";
				
				if (pq($row)->find('td:eq(8)')->text() != NULL)
					$details[$week]['15:10-16:00'] = pq($row)->find('td:eq(8)')->text();
				else $details[$week]['15:10-16:00'] = "FREE";
				
				if (pq($row)->find('td:eq(9)')->text() != NULL)
					$details[$week]['16:00-16:50'] = pq($row)->find('td:eq(9)')->text();
				else $details[$week]['16:00-16:50'] = "FREE";
			}
			
			/**
			 * Encode into a JSON object and return.
			 */	
			return json_encode($details);
			
		} else die("Register Number or Password not set.");
	}
}

?>
