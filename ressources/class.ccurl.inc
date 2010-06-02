<?php
class ccurl{
	var $uri;
	var $parms=array();
	var $data='';
	var $error;
	function ccurl($uri){
		$this->uri=$uri;
	}
	
	function get(){
				
			while (list ($num, $ligne) = each ($this->parms)){
				$curlPost .='&'.$num.'=' . urlencode($ligne);
			}		
				
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$this->uri");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);	
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
			$this->data=curl_exec($ch);
			$error=curl_errno($ch);		
			writelogs("datas: $this->uri (". strlen($this->data)." bytes) error:$error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			
			if($error==6){
				writelogs("error 6 CURLE_COULDNT_RESOLVE_HOST",__CLASS__.'/'.__FUNCTION__,__FILE__);
				curl_close($ch);
				$this->error="{CURLE_COULDNT_RESOLVE_HOST}";
				return false;
			}
			
			if($error==7){
				writelogs("error 7 CURLE_COULDNT_CONNECT",__CLASS__.'/'.__FUNCTION__,__FILE__);
				curl_close($ch);
				$this->error="{CURLE_COULDNT_CONNECT}";
				return false;
			}			
			
			curl_close($ch);
			return true;
	}
	
	
	function postFile($fieldname,$filepath){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$this->uri");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));   
    // same as <input type="file" name="file_box">
    $post = array(
        "$fieldname"=>"@$filepath",
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
    writelogs("Executing posting $fieldname/$filepath",__CLASS__.'/'.__FUNCTION__,__FILE__);
    $this->data = curl_exec($ch);

	$error=curl_errno($ch);		
			writelogs("datas: $this->uri (". strlen($this->data)." bytes) error:$error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			if($error==6){
				writelogs("error 6 ($this->uri) in protocol",__CLASS__.'/'.__FUNCTION__,__FILE__);
				curl_close($ch);
				return false;
			}
			
			
			curl_close($ch);
			return true;    
    
    
	}
	
	
}