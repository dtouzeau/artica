<?php
if(!is_file("class.ldap.inc")){include_once(dirname(__FILE__) . '/class.ldap.inc');}else{include_once('class.ldap.inc');}


class spamassassin{
	var $rewrite_header;
	var $main_array=array();
	var $required_score="5.0";
	var $rewrite_headers=array();
	var $trusted_networks=array();
	var $add_headers=array();
	var $dn;
	var $global_conf;
	var $block_with_required_score=null;
	var $CountriesCode;
	var $main_country=array();
	
	
	
function spamassassin(){
	
	$this->LoadConfig();
	$this->ParseConfig();
	$this->Parse_rewrite_headers();
	$this->Parse_add_headers();
	$this->Parse_trusted_networks();
	$this->CountriesCode=$this->LoadCountriesCodes();
	$this->LoadCountries();
	
}
	


function ParseConfig(){
	$tbl=explode("\n",$this->global_conf);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#^([a-z_0-9]+)\s+(.+)#',$ligne,$re)){
			$this->main_array[$re[1]]=$re[2];
		}
		
	}
	$this->buildDefault();
}

function Parse_rewrite_headers(){
	$tbl=explode("\n",$this->global_conf);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#^rewrite_header\s+(Subject|From|To)\s+(.+)#',$ligne,$re)){
			$this->rewrite_headers[$re[1]]=$re[2];
		}
		
		if(preg_match('#milter_block_with_required_score:([0-9\.]+)#',$ligne,$re)){
			$this->block_with_required_score=$re[1];
		}
		
	}
}
function Parse_add_headers(){
	$tbl=explode("\n",$this->global_conf);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#^add_header\s+(spam|ham|all)\s+(.+)#',$ligne,$re)){
			$this->add_headers[$re[1]]=$re[2];
		}
	}
}

function Parse_trusted_networks(){
	$tbl=explode("\n",$this->global_conf);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#^trusted_networks\s+(.+)#',$ligne,$re)){
			$ARR[]=$re[1];
		}
	}	
	
	$this->trusted_networks=$ARR;
}

function SaveToLdap(){
	$sock=new sockets();
	$sock->SaveConfigFile($this->BuildConfig(),"SpamAssassinConfFile");
	$tpl=new templates();
	$this->SaveRelayCountry();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?SpamAssassin-Reload=yes");
}



function buildDefault(){
	
	if($this->main_array["required_score"]==null){$this->main_array["required_score"]="5.0";}
	if($this->main_array["report_safe"]==null){$this->main_array["report_safe"]="1";}
	if($this->main_array["bayes_auto_learn"]==null){$this->main_array["bayes_auto_learn"]="1";}
	if($this->main_array["use_bayes"]==null){$this->main_array["use_bayes"]="1";}
	if($this->main_array["use_terse_report"]==null){$this->main_array["use_terse_report"]="1";}
	if($this->main_array["use_auto_whitelist"]==null){$this->main_array["use_auto_whitelist"]="1";}
	if($this->main_array["use_razor2"]==null){$this->main_array["use_razor2"]=0;}
	if($this->main_array["use_pyzor"]==null){$this->main_array["use_pyzor"]=0;}
	if($this->block_with_required_score==null){$this->block_with_required_score=10;}
	
	$users=new usersMenus();
	if($users->razor_installed){
		$this->main_array["razor_config"]=$users->razor_config;
	}else{
		$this->main_array["use_razor2"]=0;
	}
	
	if(!$users->pyzor_installed){
		$this->main_array["use_pyzor"]=0;
	}	
	
	
	
	
	
}


function spamassassin_reloadAndSave(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?SpamAssassin-Reload=yes");
	
}


function LoadCountries(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("RelayCountryPlugin"));
	$this->main_country=$ini->_params;
	
}

function SaveRelayCountry(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->_params=$this->main_country;
	$sock->SaveConfigFile($ini->toString(),"RelayCountryPlugin");
	$this->spamassassin_reloadAndSave();
	}


	function LoadConfig(){
		$sock=new sockets();
		$this->global_conf=$sock->GET_INFO("SpamAssassinConfFile");
	}
	
	
	function BuildConfig(){
				
				$this->buildDefault();
		
				
				$conf=$conf ."# This is the right place to customize your installation of SpamAssassin.\n";
				$conf=$conf ."#\n";
				$conf=$conf ."# See 'perldoc Mail::SpamAssassin::Conf' for details of what can be\n";
				$conf=$conf ."# tweaked.\n";
				$conf=$conf ."#\n";
				$conf=$conf ."# Only a small subset of options are listed below\n";
				$conf=$conf ."#Used by spamass-milter:\n";
				$conf=$conf ."#milter_block_with_required_score:$this->block_with_required_score";
				$conf=$conf ."#\n";
				$conf=$conf ."# more infos in http://spamassassin.apache.org/full/3.2.x/doc/Mail_SpamAssassin_Conf.html\n";
				$conf=$conf ."###########################################################################\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Add *****SPAM***** to the Subject header of spam e-mails\n";
				$conf=$conf ."#\n";
				while (list ($num, $ligne) = each ($this->rewrite_headers) ){
					if(trim($ligne)<>null){
						$conf=$conf ."rewrite_header $num $ligne\n";
					}
				}
				$conf=$conf ."#\n";
				while (list ($num, $ligne) = each ($this->add_headers) ){
					if(trim($ligne)<>null){
						$conf=$conf ."add_header $num $ligne\n";
					}
				}				
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Save spam messages as a message/rfc822 MIME attachment instead of\n";
				$conf=$conf ."#   modifying the original message (0: off, 2: use text/plain instead)\n";
				$conf=$conf ."#\n";
				$conf=$conf ."report_safe {$this->main_array["report_safe"]}\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Set which networks or hosts are considered 'trusted' by your mail\n";
				$conf=$conf ."#   server (i.e. not spammers)\n";
				$conf=$conf ."#\n";
				if(is_array($this->trusted_networks)){
					reset($this->trusted_networks);
					while (list ($num, $ligne) = each ($this->trusted_networks) ){
						if(trim($ligne)<>null){
							$conf=$conf ."trusted_networks $ligne\n";
						}
					}	
				}
				
				$conf=$conf ."# trusted_networks 212.17.35.\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Set file-locking method (flock is not safe over NFS, but is faster)\n";
				$conf=$conf ."#\n";
				$conf=$conf ."lock_method flock\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Set the threshold at which a message is considered spam (default: 5.0)\n";
				$conf=$conf ."#\n";
				$conf=$conf ."required_score {$this->main_array["required_score"]}\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Use Bayesian classifier (default: 1)\n";
				$conf=$conf ."#\n";
				$conf=$conf ."use_bayes {$this->main_array["use_bayes"]}\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n";
				$conf=$conf ."#   Bayesian classifier auto-learning (default: 1)\n";
				$conf=$conf ."#\n";
				$conf=$conf ."bayes_auto_learn {$this->main_array["bayes_auto_learn"]}\n";
				$conf=$conf ."\n";
				$conf=$conf ."\n"; 
				$conf=$conf ."#Auto white-listing\n";       
				$conf=$conf ."auto_whitelist_file_mode 0666\n";
				$conf=$conf ."auto_whitelist_path /etc/spamassassin/auto_whitelist\n";
				$conf=$conf ."\n";
				
				
				$conf=$conf ."\n"; 
				$conf=$conf ."#plugins\n";				
				if($this->main_array["razor_config"]<>null){
					$conf=$conf ."use_razor2 {$this->main_array["use_razor2"]}\n";
					$conf=$conf ."razor_config {$this->main_array["razor_config"]}\n";
				}
				$conf=$conf ."use_pyzor {$this->main_array["use_pyzor"]}\n";
				$conf=$conf ."\n"; 
				
				$conf=$conf ."#   Set headers which may provide inappropriate cues to the Bayesian\n";
				$conf=$conf ."#   classifier\n";
				$conf=$conf ."#\n";
				$conf=$conf ."# bayes_ignore_header X-Bogosity\n";
				$conf=$conf ."# bayes_ignore_header X-Spam-Flag\n";
				$conf=$conf ."# bayes_ignore_header X-Spam-Status\n";
				return $conf;
		
		
	}
	
function LoadCountriesCodes(){
			$CountriesCode["AC"]="Ascension Island";
			$CountriesCode["AD"]="Andorra";
			$CountriesCode["AE"]="United Arab Emirates";
			$CountriesCode["AF"]="Afghanistan";
			$CountriesCode["AG"]="Antigua and Barbuda";
			$CountriesCode["AI"]="Anguilla";
			$CountriesCode["AL"]="Albania";
			$CountriesCode["AM"]="Armenia";
			$CountriesCode["AN"]="Netherlands Antilles";
			$CountriesCode["AO"]="Angola";
			$CountriesCode["AQ"]="Antarctica";
			$CountriesCode["AR"]="Argentina";
			$CountriesCode["AS"]="American Samoa";
			$CountriesCode["ASIA"]="Pan-Asia and Asia Pacific community";
			$CountriesCode["AT"]="Austria";
			$CountriesCode["AU"]="Australia";
			$CountriesCode["AW"]="Aruba";
			$CountriesCode["AX"]="Aland Islands";
			$CountriesCode["AZ"]="Azerbaijan";
			$CountriesCode["BA"]="Bosnia and Herzegovina";
			$CountriesCode["BB"]="Barbados";
			$CountriesCode["BD"]="Bangladesh";
			$CountriesCode["BE"]="Belgium";
			$CountriesCode["BF"]="Burkina Faso";
			$CountriesCode["BG"]="Bulgaria";
			$CountriesCode["BH"]="Bahrain";
			$CountriesCode["BI"]="Burundi";
			$CountriesCode["BIZ"]="Restricted for Business";
			$CountriesCode["BJ"]="Benin";
			$CountriesCode["BL"]="Saint Barthelemy";
			$CountriesCode["BM"]="Bermuda";
			$CountriesCode["BN"]="Brunei Darussalam";
			$CountriesCode["BO"]="Bolivia";
			$CountriesCode["BR"]="Brazil";
			$CountriesCode["BS"]="Bahamas";
			$CountriesCode["BT"]="Bhutan";
			$CountriesCode["BV"]="Bouvet Island";
			$CountriesCode["BW"]="Botswana";
			$CountriesCode["BY"]="Belarus";
			$CountriesCode["BZ"]="Belize";
			$CountriesCode["CA"]="Canada";
			$CountriesCode["CAT"]="Catalan linguistic";
			$CountriesCode["CC"]="Cocos (Keeling) Islands";
			$CountriesCode["CD"]="Congo, The Democratic Republic";
			$CountriesCode["CF"]="Central African Republic";
			$CountriesCode["CG"]="Congo";
			$CountriesCode["CH"]="Switzerland";
			$CountriesCode["CI"]="Cote d'Ivoire";
			$CountriesCode["CK"]="Cook Islands";
			$CountriesCode["CL"]="Chile";
			$CountriesCode["CM"]="Cameroon";
			$CountriesCode["CN"]="China";
			$CountriesCode["CO"]="Colombia";
			$CountriesCode["COM"]="top-level domain .com";
			$CountriesCode["COOP"]="Cooperative associations";
			$CountriesCode["CR"]="Costa Rica";
			$CountriesCode["CU"]="Cuba";
			$CountriesCode["CV"]="Cape Verde";
			$CountriesCode["CX"]="Christmas Island";
			$CountriesCode["CY"]="Cyprus";
			$CountriesCode["CZ"]="Czech Republic";
			$CountriesCode["DE"]="Germany";
			$CountriesCode["DJ"]="Djibouti";
			$CountriesCode["DK"]="Denmark";
			$CountriesCode["DM"]="Dominica";
			$CountriesCode["DO"]="Dominican Republic";
			$CountriesCode["DZ"]="Algeria";
			$CountriesCode["EC"]="Ecuador";
			$CountriesCode["EDU"]="Post-secondary institutions";
			$CountriesCode["EE"]="Estonia";
			$CountriesCode["EG"]="Egypt";
			$CountriesCode["EH"]="Western Sahara";
			$CountriesCode["ER"]="Eritrea";
			$CountriesCode["ES"]="Spain";
			$CountriesCode["ET"]="Ethiopia";
			$CountriesCode["EU"]="European Union";
			$CountriesCode["FI"]="Finland";
			$CountriesCode["FJ"]="Fiji";
			$CountriesCode["FK"]="Falkland Islands (Malvinas)";
			$CountriesCode["FM"]="Micronesia, Federated States of";
			$CountriesCode["FO"]="Faroe Islands";
			$CountriesCode["FR"]="France";
			$CountriesCode["GA"]="Gabon";
			$CountriesCode["GB"]="United Kingdom";
			$CountriesCode["GD"]="Grenada";
			$CountriesCode["GE"]="Georgia";
			$CountriesCode["GF"]="French Guiana";
			$CountriesCode["GG"]="Guernsey";
			$CountriesCode["GH"]="Ghana";
			$CountriesCode["GI"]="Gibraltar";
			$CountriesCode["GL"]="Greenland";
			$CountriesCode["GM"]="Gambia";
			$CountriesCode["GN"]="Guinea";
			$CountriesCode["GOV"]="Government";
			$CountriesCode["GP"]="Guadeloupe";
			$CountriesCode["GQ"]="Equatorial Guinea";
			$CountriesCode["GR"]="Greece";
			$CountriesCode["GS"]="South Georgia and the South Sandwich Islands";
			$CountriesCode["GT"]="Guatemala";
			$CountriesCode["GU"]="Guam";
			$CountriesCode["GW"]="Guinea-Bissau";
			$CountriesCode["GY"]="Guyana";
			$CountriesCode["HK"]="Hong Kong";
			$CountriesCode["HM"]="Heard Island and McDonald Islands";
			$CountriesCode["HN"]="Honduras";
			$CountriesCode["HR"]="Croatia";
			$CountriesCode["HT"]="Haiti";
			$CountriesCode["HU"]="Hungary";
			$CountriesCode["ID"]="Indonesia";
			$CountriesCode["IE"]="Ireland";
			$CountriesCode["IL"]="Israel";
			$CountriesCode["IM"]="Isle of Man";
			$CountriesCode["IN"]="India";
			$CountriesCode["INFO"]="top-level domain .info";
			$CountriesCode["INT"]="Treaties between governments";
			$CountriesCode["IO"]="British Indian Ocean Territory";
			$CountriesCode["IQ"]="Iraq";
			$CountriesCode["IR"]="Iran, Islamic Republic of";
			$CountriesCode["IS"]="Iceland";
			$CountriesCode["IT"]="Italy";
			$CountriesCode["JE"]="Jersey";
			$CountriesCode["JM"]="Jamaica";
			$CountriesCode["JO"]="Jordan";
			$CountriesCode["JOBS"]="Human resource managers";
			$CountriesCode["JP"]="Japan";
			$CountriesCode["KE"]="Kenya";
			$CountriesCode["KG"]="Kyrgyzstan";
			$CountriesCode["KH"]="Cambodia";
			$CountriesCode["KI"]="Kiribati";
			$CountriesCode["KM"]="Comoros";
			$CountriesCode["KN"]="Saint Kitts and Nevis";
			$CountriesCode["KP"]="Korea, Democratic People's Republic of";
			$CountriesCode["KR"]="Korea, Republic of";
			$CountriesCode["KW"]="Kuwait";
			$CountriesCode["KY"]="Cayman Islands";
			$CountriesCode["KZ"]="Kazakhstan";
			$CountriesCode["LA"]="Lao People's Democratic Republic";
			$CountriesCode["LB"]="Lebanon";
			$CountriesCode["LC"]="Saint Lucia";
			$CountriesCode["LI"]="Liechtenstein";
			$CountriesCode["LK"]="Sri Lanka";
			$CountriesCode["LR"]="Liberia";
			$CountriesCode["LS"]="Lesotho";
			$CountriesCode["LT"]="Lithuania";
			$CountriesCode["LU"]="Luxembourg";
			$CountriesCode["LV"]="Latvia";
			$CountriesCode["LY"]="Libyan Arab Jamahiriya";
			$CountriesCode["MA"]="Morocco";
			$CountriesCode["MC"]="Monaco";
			$CountriesCode["MD"]="Moldova, Republic of";
			$CountriesCode["ME"]="Montenegro";
			$CountriesCode["MF"]="Saint Martin";
			$CountriesCode["MG"]="Madagascar";
			$CountriesCode["MH"]="Marshall Islands";
			$CountriesCode["MIL"]="United States Military";
			$CountriesCode["MK"]="Macedonia, The Former Yugoslav Republic of";
			$CountriesCode["ML"]="Mali";
			$CountriesCode["MM"]="Myanmar";
			$CountriesCode["MN"]="Mongolia";
			$CountriesCode["MO"]="Macao";
			$CountriesCode["MOBI"]="mobile products and services";
			$CountriesCode["MP"]="Northern Mariana Islands";
			$CountriesCode["MQ"]="Martinique";
			$CountriesCode["MR"]="Mauritania";
			$CountriesCode["MS"]="Montserrat";
			$CountriesCode["MT"]="Malta";
			$CountriesCode["MU"]="Mauritius";
			$CountriesCode["MUSEUM"]="museums";
			$CountriesCode["MV"]="Maldives";
			$CountriesCode["MW"]="Malawi";
			$CountriesCode["MX"]="Mexico";
			$CountriesCode["MY"]="Malaysia";
			$CountriesCode["MZ"]="Mozambique";
			$CountriesCode["NA"]="Namibia";
			$CountriesCode["NAME"]="individuals";
			$CountriesCode["NC"]="New Caledonia";
			$CountriesCode["NE"]="Niger";
			$CountriesCode["NET"]="top-level domain .net";
			$CountriesCode["NF"]="Norfolk Island";
			$CountriesCode["NG"]="Nigeria";
			$CountriesCode["NI"]="Nicaragua";
			$CountriesCode["NL"]="Netherlands";
			$CountriesCode["NO"]="Norway";
			$CountriesCode["NP"]="Nepal";
			$CountriesCode["NR"]="Nauru";
			$CountriesCode["NU"]="Niue";
			$CountriesCode["NZ"]="New Zealand";
			$CountriesCode["OM"]="Oman";
			$CountriesCode["ORG"]="top-level domain .org";
			$CountriesCode["PA"]="Panama";
			$CountriesCode["PE"]="Peru";
			$CountriesCode["PF"]="French Polynesia";
			$CountriesCode["PG"]="Papua New Guinea";
			$CountriesCode["PH"]="Philippines";
			$CountriesCode["PK"]="Pakistan";
			$CountriesCode["PL"]="Poland";
			$CountriesCode["PM"]="Saint Pierre and Miquelon";
			$CountriesCode["PN"]="Pitcairn";
			$CountriesCode["PR"]="Puerto Rico";
			$CountriesCode["PRO"]="professionals and related entities";
			$CountriesCode["PS"]="Palestinian Territory, Occupied";
			$CountriesCode["PT"]="Portugal";
			$CountriesCode["PW"]="Palau";
			$CountriesCode["PY"]="Paraguay";
			$CountriesCode["QA"]="Qatar";
			$CountriesCode["RE"]="Reunion";
			$CountriesCode["RO"]="Romania";
			$CountriesCode["RS"]="Serbia";
			$CountriesCode["RU"]="Russian Federation";
			$CountriesCode["RW"]="Rwanda";
			$CountriesCode["SA"]="Saudi Arabia";
			$CountriesCode["SB"]="Solomon Islands";
			$CountriesCode["SC"]="Seychelles";
			$CountriesCode["SD"]="Sudan";
			$CountriesCode["SE"]="Sweden";
			$CountriesCode["SG"]="Singapore";
			$CountriesCode["SH"]="Saint Helena";
			$CountriesCode["SI"]="Slovenia";
			$CountriesCode["SJ"]="Svalbard and Jan Mayen";
			$CountriesCode["SK"]="Slovakia";
			$CountriesCode["SL"]="Sierra Leone";
			$CountriesCode["SM"]="San Marino";
			$CountriesCode["SN"]="Senegal";
			$CountriesCode["SO"]="Somalia";
			$CountriesCode["SR"]="Suriname";
			$CountriesCode["ST"]="Sao Tome and Principe";
			$CountriesCode["SU"]="Soviet Union (being phased out)";
			$CountriesCode["SV"]="El Salvador";
			$CountriesCode["SY"]="Syrian Arab Republic";
			$CountriesCode["SZ"]="Swaziland";
			$CountriesCode["TC"]="Turks and Caicos Islands";
			$CountriesCode["TD"]="Chad";
			$CountriesCode["TEL"]="individuals to publish their contact data";
			$CountriesCode["TF"]="French Southern Territories";
			$CountriesCode["TG"]="Togo";
			$CountriesCode["TH"]="Thailand";
			$CountriesCode["TJ"]="Tajikistan";
			$CountriesCode["TK"]="Tokelau";
			$CountriesCode["TL"]="Timor-Leste";
			$CountriesCode["TM"]="Turkmenistan";
			$CountriesCode["TN"]="Tunisia";
			$CountriesCode["TO"]="Tonga";
			$CountriesCode["TP"]="Portuguese Timor (being phased out)";
			$CountriesCode["TR"]="Turkey";
			$CountriesCode["TRAVEL"]="activity is in the travel industry";
			$CountriesCode["TT"]="Trinidad and Tobago";
			$CountriesCode["TV"]="Tuvalu";
			$CountriesCode["TW"]="Taiwan";
			$CountriesCode["TZ"]="Tanzania, United Republic of";
			$CountriesCode["UA"]="Ukraine";
			$CountriesCode["UG"]="Uganda";
			$CountriesCode["UK"]="United Kingdom";
			$CountriesCode["UM"]="United States Minor Outlying Islands";
			$CountriesCode["US"]="United States";
			$CountriesCode["UY"]="Uruguay";
			$CountriesCode["UZ"]="Uzbekistan";
			$CountriesCode["VA"]="Holy See (Vatican City State)";
			$CountriesCode["VC"]="Saint Vincent and the Grenadines";
			$CountriesCode["VE"]="Venezuela, Bolivarian Republic of";
			$CountriesCode["VG"]="Virgin Islands, British";
			$CountriesCode["VI"]="Virgin Islands, U.S.";
			$CountriesCode["VN"]="Viet Nam";
			$CountriesCode["VU"]="Vanuatu";
			$CountriesCode["WF"]="Wallis and Futuna";
			$CountriesCode["WS"]="Samoa";
			$CountriesCode["YE"]="Yemen";
			$CountriesCode["YT"]="Mayotte";
			$CountriesCode["YU"]="Yugoslavia (being phased out)";
			$CountriesCode["ZA"]="South Africa";
			$CountriesCode["ZM"]="Zambia";
			$CountriesCode["ZW"]="Zimbabwe";	
			return $CountriesCode;
			}
	
	
}