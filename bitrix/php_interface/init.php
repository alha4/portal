<?php
AddEventHandler("support","OnAfterTicketAdd","reindexTickets");
AddEventHandler("support","OnAfterTicketUpdate","reindexTickets");

AddEventHandler("im", "OnBeforeMessageNotifyAdd", "OnBeforeMessageNotifyAddHandler");
AddEventHandler("search", "OnSearchCheckPermissions", "OnSearchPermissionsHandler");

function createNewLead($messFields) {

 CModule::IncludeModule('crm');
 CModule::IncludeModule('blog');	
 CModule::IncludeModule('mail');	
 $CCrmLead = new CCrmLead(false);

 $emailfrom = CMailUtil::ExtractMailAddress($messFields['MESSAGE_AUTHOR_SID']);
		$fullname = preg_replace('/<(.*?)>/','', $messFields['MESSAGE_AUTHOR_SID']);

		if (filter_var($fullname, FILTER_VALIDATE_EMAIL)) { // IF FIELD FROM DONT HAVE NAME
			$arFullName = explode("@", $fullname);
			$fullname = $arFullName[0];
		}

		$assignedBy = 20;
		$parserBlog = new blogTextParser(false, "/bitrix/images/socialnetwork/smile/");
		$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "Y", "VIDEO" => "Y");
		$text4message = $parserBlog->convert($messFields['MESSAGE'], true, array(), $arAllow);

		$arFields = array(
			"TITLE" => $messFields['TITLE'],
			"COMMENTS" => $text4message,
			"NAME" => trim($fullname),
			"STATUS_ID" => "NEW",
			"ASSIGNED_BY_ID" => $assignedBy,
			"CURRENCY_ID" => "USD",
			"EXCH_RATE" => 1,
			"FM" => array(
				"EMAIL" => array(
					"n1" => array(
						"VALUE" => $emailfrom,
						"VALUE_TYPE" => "WORK"
					)
				)
			)
		);

		$ID = $CCrmLead->Add($arFields, true, array('REGISTER_SONET_EVENT' => true));
		return $ID;

}

function OnSearchPermissionsHandler(&$FIELD) {
  return $FIELD;
}


function OnBeforeMessageNotifyAddHandler(&$arFields) {
 
 $user_added = (int)preg_replace("#\[USER=([0-9]+)\]#is","$1",$arFields['MESSAGE']);

 $arFields['TO_USER_ID'] = $user_added;

 $addedUser     = CUser::GetByID($user_added);
 $addedUserRs   = $addedUser->Fetch();
 $departamentID = $addedUserRs['UF_DEPARTMENT'][0];

 $currentUser   = CUser::GetByID(CUser::GetID());
 $currentUserRs = $currentUser->Fetch();
 $currentDepartamentID = $currentUserRs['UF_DEPARTMENT'][0];

 $f = fopen($_SERVER['DOCUMENT_ROOT']."/im.txt","a");
 fwrite($f,json_encode($arFields).'/ '.date("H:i:s")."\n\r");
 fclose($f);

/* if($currentDepartamentID !== $departamentID) {

   $arFields['TO_CHAT_ID'] = "";

   return $arFields;

 }
 
 return $arFields;*/
 
}


define(TICKET_EDIT_PATH,'/services/support/ticket_edit.php?ID=');
define(TICKET_EDIT_PATH_TEST,'/services/support.php?edit=1&ID=');

use \Bitrix\Main\Loader as Loader;

function reindexTickets($arFields) {

if(strlen($arFields['MESSAGE']) > 0) {

Loader::IncludeModule("search");
Loader::IncludeModule("support");

$searchGroupPerms = array(1,6,7);

$arFilterOpen = array(); 
$isFiltered   = true; 
$checkRights  ='Y';
$getUserInfo = 'Y';
$errors = array();

$ticketsNew = CTicket::GetList($by,$order,$arFilterOpen,$isFiltered,$checkRights,$getUserInfo); 

while($rs = $ticketsNew->GetNext()) {
	 
  $mess = CTicket::GetMessageList($a='s_id', $b='desc', array("TICKET_ID" => $rs['ID'], "TICKET_ID_EXACT_MATCH" => "Y","IS_MESSAGE" => "Y"), $c=true, $checkRights);

  while($ar_mess = $mess->GetNext()) {
	  	
       $data_element = array(
             "TITLE"=> $rs['TITLE'],
             "BODY" => $ar_mess['MESSAGE'],
             "SITE_ID"=> SITE_ID,
             "TAGS" => $rs['OWNER_EMAIL'],
             "URL"=> TICKET_EDIT_PATH_TEST.$rs['ID'],
             "PERMISSIONS" => $searchGroupPerms,
             "DATE_CHANGE" => date("d.m.Y")
       );

       $resultID = CSearch::Index(
          "support",
           $ar_mess['ID'],
           $data_element,
           true
       );

       if(!$resultID) {
          $errors[] = array("TICKET_ID"=>$rs['ID'],"MESSAGE_ID"=>$ar_mess['ID']);
       }
  }
 } 
 if(count($errors) > 0) {
 
   ticketErrorLog($errors);

  }
 }
}

function ticketErrorLog($mess) {

  $f = fopen($_SERVER['DOCUMENT_ROOT']."/terror.txt","w+");
  fwrite($f,json_encode($mess));
  fclose($f);

}
?>