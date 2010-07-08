<?php
include_once(dirname(__FILE__) . '/class.users.menus.inc');
include_once(dirname(__FILE__) . '/class.mysql.inc');
include_once(dirname(__FILE__) . '/class.user.inc');
include_once(dirname(__FILE__) . '/class.ini.inc');
include_once(dirname(__FILE__) . '/class.templates.inc');

function format_line($line){
	$ini=new Bs_IniHandler();
	if(posix_getuid()<>0){
		$sock=new sockets();
		$ini->loadString($sock->GET_INFO("RTMMailConfig"));
	}else{
		if(is_file("/etc/artica-postfix/settings/Daemons/RTMMailConfig")){$ini->loadFile("/etc/artica-postfix/settings/Daemons/RTMMailConfig");}
	}
	
	if($ini->_params["Discard"]["row_color"]==null){$ini->_params["Discard"]["row_color"]="#D00000";}
	if($ini->_params["Discard"]["text_color"]==null){$ini->_params["Discard"]["text_color"]="#FFFFFF";}
	
	if($ini->_params["Greylisting"]["row_color"]==null){$ini->_params["Greylisting"]["row_color"]="#949494";}
	if($ini->_params["Greylisting"]["text_color"]==null){$ini->_params["Greylisting"]["text_color"]="#FFFFFF";}
	
	if($ini->_params["Relay_access_denied"]["row_color"]==null){$ini->_params["Relay_access_denied"]["row_color"]="#D00000";}
	if($ini->_params["Relay_access_denied"]["text_color"]==null){$ini->_params["Relay_access_denied"]["text_color"]="#FFFFFF";}	
	
	if($ini->_params["User_unknown_in_relay_recipient_table"]["row_color"]==null){$ini->_params["User_unknown_in_relay_recipient_table"]["row_color"]="#D00000";}
	if($ini->_params["User_unknown_in_relay_recipient_table"]["text_color"]==null){$ini->_params["User_unknown_in_relay_recipient_table"]["text_color"]="#FFFFFF";}	
	
	if($ini->_params["RBL"]["row_color"]==null){$ini->_params["RBL"]["row_color"]="#949494";}
	if($ini->_params["RBL"]["text_color"]==null){$ini->_params["RBL"]["text_color"]="#FFFFFF";}	
	
	if($ini->_params["hostname_not_found"]["row_color"]==null){$ini->_params["hostname_not_found"]["row_color"]="#FFECEC";}
	if($ini->_params["hostname_not_found"]["text_color"]==null){$ini->_params["hostname_not_found"]["text_color"]="#000000";}

	if($ini->_params["User_unknown"]["row_color"]==null){$ini->_params["User_unknown"]["row_color"]="#FFECEC";}
	if($ini->_params["User_unknown"]["text_color"]==null){$ini->_params["User_unknown"]["text_color"]="#000000";}	
		
	if($ini->_params["Domain_not_found"]["row_color"]==null){$ini->_params["Domain_not_found"]["row_color"]="#FFECEC";}
	if($ini->_params["Domain_not_found"]["text_color"]==null){$ini->_params["Domain_not_found"]["text_color"]="#000000";}

	if($ini->_params["DNS_Error"]["row_color"]==null){$ini->_params["DNS_Error"]["row_color"]="#D00000";}
	if($ini->_params["DNS_Error"]["text_color"]==null){$ini->_params["DNS_Error"]["text_color"]="#FFFFFF";}		
	
	if($ini->_params["SPAM"]["row_color"]==null){$ini->_params["SPAM"]["row_color"]="#F36C15";}
	if($ini->_params["SPAM"]["text_color"]==null){$ini->_params["SPAM"]["text_color"]="#FFFFFF";}

	if($ini->_params["SPAMMY"]["row_color"]==null){$ini->_params["SPAMMY"]["row_color"]="#FFC59E";}
	if($ini->_params["SPAMMY"]["text_color"]==null){$ini->_params["SPAMMY"]["text_color"]="#000000";}	

	if($ini->_params["Command died"]["text_color"]=$ini->_params["DNS_Error"]["text_color"]);
	if($ini->_params["Command died"]["row_color"]=$ini->_params["DNS_Error"]["row_color"]);
	
	
	
	$Country=$line["Country"];
	$country_img=GetFlags($Country);
	$Region=$line["Region"];
	$smtp_sender=$line["smtp_sender"];
	$SPAM=$line["SPAM"];
	$rcpt=$line["delivery_user"];
	$mailfrom=$line["sender_user"];
	$bounce_error=trim($line["bounce_error"]);
	$time=$line["time_connect"];
	$spammy=$line["spammy"];
	
	
	
	if(trim($mailfrom)==null && trim($rcpt)==null){return null;}
	
	if(preg_match('#User unknown#',$bounce_error)){$bounce_error="User unknown";}
	if(preg_match('#250.+?[OokK]+#',$bounce_error)){$bounce_error="Sended";}
	if(preg_match('#delivered via#',$bounce_error)){$bounce_error="Sended";}
	if(preg_match('#Command died with status\s+#',$bounce_error)){$bounce_error="Command died";}
	
	
	
	
	if($spammy==1){$bounce_error="SPAMMY";}
	if($SPAM==1){$bounce_error="SPAM";}
	
	
	$today=date('Y-m-d');
	$time=str_replace($today,"",$time);
	$flag_infos=trim("$Country ($Region) $smtp_sender");
	$flag_infos=str_replace("()","",$flag_infos);
	if($flag_infos==null){
		$flag_infos="{RTMM_NO_GEOIP_OR_LOOPBACK}";
	}
	
	
	if(trim($bounce_error)==null){$bounce_error="Sended";}
	if(preg_match("#(.+?)\s+(.+)#",$time,$re)){
		$time=texttooltip($re[2],"<strong style=font-size:14px>$time</strong>",null);
	}else{
		$time=texttooltip($time,"<strong style=font-size:14px>{today}</strong>",null);
	}
	
	if($mailfrom==null){$mailfrom="&nbsp;";}

	$bounce_error_key=str_replace(" ","_",$bounce_error);
	$bg_color="background-color:{$ini->_params[$bounce_error_key]["row_color"]};color:{$ini->_params[$bounce_error_key]["text_color"]}";
	if($bounce_error=="Mailbox unknown"){$bg_color="background-color:{$ini->_params["User_unknown"]["row_color"]};color:{$ini->_params["User_unknown"]["text_color"]}";}
	
	
	if(strlen($mailfrom)>23){
		$mailfrom =texttooltip(substr($mailfrom,0,20)."...","<strong style=font-size:14px>$mailfrom</strong>");
	}
	if(strlen($rcpt)>23){
		$rcpt =texttooltip(substr($rcpt,0,20)."...","<strong style=font-size:14px>$rcpt</strong>");
	}
	$html="
	<tr style='$bg_color'>
		<td width=1% style='background-color:white'>" . imgtootltip($country_img,"<strong style=font-size:14px>$flag_infos</strong>")."</td>
		<td width=1% nowrap><strong>$time</strong></td>
		<td><strong>$mailfrom</td>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$rcpt</td>
		<td><strong>&laquo;$bounce_error&raquo;</td>
	</tr>";
return $html;
	
	//Region 	kas 	infected 	spammy 	blacklisted 	whitelisted
}

function GetFlags($Country){
	
	$array["United States"]="us.png";
	$array["France"]="fr.png";
	$array["Vietnam"]="vn.png";
	$array["Brazil"]="br.png";
	$array["Bulgaria"]="bg.png";
	$array["India"]="in.png";
	$array["Korea, Republic of"]="kr.png";
	$array["Spain"]="es.png";
	$array["Slovakia"]="sk.png";
	$array["Poland"]="pl.png";
	$array["Egypt"]="eg.png";
	$array["China"]="cn.png";
	$array["Chile"]="cl.png";
	$array["Thailand"]="th.png";
	$array["Italy"]="it.png";
	$array["Indonesia"]="id.png";
	$array["Lebanon"]="lb.png";
	$array["Israel"]="il.png";
	$array["Japan"]="jp.png";
	$array['Venezuela']="ve.png";
	$array["Lithuania"]="lt.png";
	$array["Argentina"]="ar.png";
	$array["Mexico"]="mx.png";
	$array["Colombia"]="co.png";
	$array["Puerto Rico"]="pr.png";
	$array["Dominican Republic"]="do.png";
	$array["United Kingdom"]="uk.png";
	$array["Peru"]="pe.png";
	$array["Honduras"]="hn.png";
	$array["Hungary"]="hu.png";
	$array["Turkey"]="tr.png";
	$array["Belarus"]="by.png";
	$array["Canada"]="ca.png";
	$array["Germany"]="de.png";
	$array["Albania"]="al.png";
	$array["El Salvador"]="els.png";
	$array["Qatar"]="qa.png";
	$array["Singapore"]="sg.png";
	$array["Costa Rica"]="cr.png";
	$array["Romania"]="ro.png";
	$array["Ukraine"]="ua.png";
	$array["Portugal"]="pt.png";
	$array["Russian Federation"]="ru.png";
	$array["Oman"]="om.png";
	$array["Taiwan"]="tw.png";
	$array["Czech Republic"]="cs.png";
	$array["Greece"]="gr.png";
	$array["Morocco"]="ma.png";
	$array["Uruguay"]=="uy.png";
	$array["Kazakhstan"]="kz.png";
	$array["Libyan Arab Jamahiriya"]="ly.png";
	$array["Switzerland"]="ch.png";
	$array["Austria"]="at.png";
	$array["Philippines"]="ph.png";
	$array["Bahrain"]="bh.png";
	$array["Kenya"]="kn.png";
	$array["Iran, Islamic Republic of"]="ir.png";
	$array["Ireland"]="ie.png";
	$array["United Arab Emirates"]="ae.png";
	$array["Macedonia"]="mk.png";
	$array["Kuwait"]="kw.png";
	$array["Belgium"]="be.png";
	$array["Europe"]="eu.png";
	$array["Netherlands"]="nl.png";
	$array["Sweden"]="se.png";
	
	$flag=$array[$Country];
	if($flag==null){$flag="info.png";}
	return "flags/$flag";
	}
?>