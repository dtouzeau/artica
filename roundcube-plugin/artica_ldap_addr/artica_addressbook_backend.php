<?php
include_once(dirname(__FILE__).'/class.ldap.inc');
/**
 * Example backend class for a custom address book
 *
 * This one just holds a static list of address records
 *
 * @author Thomas Bruederli
 */
class artica_addressbook_backend extends rcube_addressbook
{
  public $primary_key = 'ID';
  public $readonly = false;
  
  private $filter;
  private $result;
  
  public function __construct(){    
  	$this->ready = true;
  }
  
  public function set_search_set($filter){
    $this->filter = $filter;
  }
  
  public function get_search_set(){
  	return $this->filter;
  }

  public function reset()
  {
    $this->result = null;
    $this->filter = null;
  }

  public function list_records($cols=null, $subset=0){
  	
  	$this->debug("request to list_records...",__FUNCTION__);
  	$this->result = $this->count();
  	
$ldap=new roundcube_ldap();
	$res=$ldap->SearchAddressBook($_SESSION["username"],"");
	if(is_array($res)){
		$this->debug("Search =".count($res). " results",__FUNCTION__);
		$this->result = $this->count();
		while (list ($num, $final_array) = each ($res) ){
			$this->result->add($final_array);
		}
	}

     return $this->result;  	
    
   
  }

  public function search($fields, $value, $strict=false, $select=true){
    // no search implemented, just list all records
    $this->debug("Search $value",__FUNCTION__);
	$ldap=new roundcube_ldap();
	$res=$ldap->SearchAddressBook($_SESSION["username"],$value);
	if(is_array($res)){
		$this->debug("Search =".count($res). " results",__FUNCTION__);
		$this->result = $this->count();
		while (list ($num, $final_array) = each ($res) ){
			$this->result->add($final_array);
		}
	}

     return $this->result;
    //return $this->list_records();
  }

  public function count(){
    return new rcube_result_set(1, ($this->list_page-1) * $this->page_size);
  }

  public function get_result(){
    return $this->result;
  }

  public function get_record($id, $assoc=false){
  	
 	//$this->list_records();
    //$first = $this->result->first();
   	
  	
    if(preg_match("#^([A-Z]+)-(.+)#",$id,$re)){$this->debug("get_record {$re[1]} {$re[2]} for \"$id\" ",__FUNCTION__);}
    else{
    	if(!preg_match("#([0-9]+)-([0-9]+)#",$id,$re)){$this->debug("failed to preg_match  $id",__FUNCTION__);return;}
    	$re[1]="NAB";
    	$re[2]=$id;
    }
    		
    		
    $ldap=new roundcube_ldap();
    if($re[1]=="ORG"){
    	
    	$array=$ldap->GetLocalUserinfos($re[2]);
    	
    	
    }
  if($re[1]=="NAB"){
    	
    	$array=$ldap->GetNABUserinfos($re[2]);
    	
    	
    }    
    
    
    if(is_array($array)){
		$this->result = new rcube_result_set(1); 
		$this->result->add($array);		
    	//return $this->result;
    }
    
  }
  

 public function insert($save_cols){
 	//Array ( [name] => toto [firstname] => titi [surname] => toto [email] => toto@titi.com ) 
 	$ldap=new roundcube_ldap();
 	$result=$ldap->add_contact($save_cols);
	if($result==null){return false;}
    return $result;
  } 

public function update($id, $save_cols){
	print_r($save_cols);
}

 function delete($ids){
    $this->debug("Delete $ids requested ",__FUNCTION__);
    return false;
  }
  
 function delete_contact($ids){
    $this->debug("Delete $ids requested ",__FUNCTION__);
    return false;
  }

 function save_contact($ids){
    $this->debug("Delete $ids requested ",__FUNCTION__);
    return false;
  }    
  
 function delete_all(){
      $this->debug("delete_all request $ids",__FUNCTION__);
    }  
    
  
  private function debug($text,$function){
  		$file=basename(__FILE__);
  		$logFile=dirname(__FILE__).'/logs/debug.log';
  		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		@fwrite($f, "$date: $file [$function] $text\n");
		@fclose($f);
  }
  
  
  
}
