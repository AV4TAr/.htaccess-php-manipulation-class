<?php
/**
* .htpasswd Manipulation class
*
*
* @package    Projects
* @module     Statistics
* @author     Diego Sapriza, AV4TAr@gmail.com
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @version    1.0
*/


/**
* Htpasswd class
*
* Class to manipulate htpasswd file for Apache
*/

class Htpasswd {
	public $username = '';
	public $password = '';
	public $htpasswdFile = '';
	
	/*
	* Check variables 
	*/
	private function checkVars($vars){
		$vars_arr = explode(',', $vars);
		foreach($vars_arr as $v){
			if($this->$v == ''){
				throw New Exception('You need to set '.$v);
			}
		}
		return true;
	}
	
	/*
	* Add a user / passwd to the htpasswdFile
	* @throws Exception 
	*/
	public function addUser(){
		$this->checkVars('username,password,htpasswdFile');
		if($this->existsUser()){
			return false;
		}
		$data = $this->username.":".$this->getHtpasswd($this->password)."\n";
		$filename = $this->htpasswdFile;
		if (is_writable($filename)) {   
            if (!$handle = fopen($filename, 'a')) {
				throw New Exception('Cannot open htpasswd file: '.$this->htpasswdFile);
            }   
            if (fwrite($handle, $data) === FALSE) {
				throw New Exception('Cannot write htpasswd file: '.$this->htpasswdFile);
            }
            fclose($handle);
			return true;
        } 
		throw New Exception('htpasswd file is not writable: '.$this->htpasswdFile);		
	}	
	
	/*
	* Returns an array with all the users of the htpasswdFile
	* @return array with username / password
	*/
	public function getUsers(){
		$this->checkVars('htpasswdFile');
		if(!is_readable($this->htpasswdFile)){
			throw New Exception('Cannot read htpasswd file: '.$this->htpasswdFile);
		}
		$file = file($this->htpasswdFile);
		$array = array();
		$count = count($file);
		for ($i = 0; $i < $count; $i++)
		{
				list($username, $password) = explode(':', $file[$i]);
				$array[] = $username;
		}
		return $array;
	}
	
	/*
	* Delete a user form the htpasswdFile

	* @return Exception if error / true
	*/
	public function delUser(){
		$this->checkVars('username,htpasswdFile');
		if (!is_readable($this->htpasswdFile)) {   
			throw New Exception('htpasswd file is not readable: '.$this->htpasswdFile);		
		}
		$fileName = file($this->htpasswdFile);
		$pattern = "/". $this->username."/";  
		$line = 0;
		foreach ($fileName as $key => $value) {   
			if(preg_match($pattern, $value)){ 
				$line = $key;  
			}
		} 
		if($line > 0){
			unset($fileName[$line]); 
			if (!$fp = fopen($this->htpasswdFile, 'w+')){  
				throw New Exception('Cannot open htpasswd file: '.$this->htpasswdFile);
			}

			if($fp){       
				foreach($fileName as $line) { 
					fwrite($fp,$line); 
				}       
				fclose($fp);
			} 
		}
		return true;
	}
	
	/*
	* Generate the compliant password
	* @param string password
	* @return strinxs htpasswd compliant
	*/
	private function getHtpasswd($pass){
		$pass = crypt(trim($pass),base64_encode(CRYPT_STD_DES));
		return $pass;
	}
	
	/*
	* Check if the user exists
	* @return true / false
	*/
	private function existsUser(){
		$this->checkVars('username,htpasswdFile');
		$fileName = file($this->htpasswdFile);
		$pattern = "/". $this->username."/";  
		foreach ($fileName as $key => $value) {   
			if(preg_match($pattern, $value)) { 
				return true;
			}
		} 
		return false;
	}
}
/*
try{
	$HT = New Htpasswd();
	$HT -> username = 'diego';
	$HT -> password = 'diego';
	$HT -> htpasswdFile = 'passwd';
	$HT->addUser();
	//$HT->addUser();
	//$HT->delUser();
	echo '<pre>';
	print_r($HT->showUsers());
	echo '</pre>';
} catch (Exception $e){
	echo $e->getMessage();
}
*/
?>
