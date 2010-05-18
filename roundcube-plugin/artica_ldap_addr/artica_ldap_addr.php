<?php

/**
 * Sample plugin to add a new address book
 * with just a static list of contacts
 */
class artica_ldap_addr extends rcube_plugin
{
  private $abook_id ='';
  
  public function init(){
    $this->add_hook('address_sources', array($this, 'address_sources'));
    $this->add_hook('get_address_book', array($this, 'get_address_book'));
    $this->abook_id=md5($_SESSION["username"]);
    // use this address book for autocompletion queries
    // (maybe this should be configurable by the user?)
    $config = rcmail::get_instance()->config;
    $sources = $config->get('autocomplete_addressbooks', array('ldap'));

    if (!in_array($this->abook_id, $sources)) {
      $sources[] = $this->abook_id;
      $config->set('autocomplete_addressbooks', $sources);
    }
  }
  
  public function address_sources($p){
  	$username=$_SESSION["username"];
 	
  	
    $p['sources'][$this->abook_id] = array('id' => $this->abook_id, 'name' => "$username - Artica", 'readonly' => false);
    return $p;
  }
  
  public function get_address_book($p){
    if ($p['id'] == $this->abook_id) {
      require_once(dirname(__FILE__) . '/artica_addressbook_backend.php');
      $p['instance'] = new artica_addressbook_backend;
    }
    
    return $p;
  }
  
}
