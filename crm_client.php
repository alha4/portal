<?
require_once($_SERVER["DOCUMENT_ROOT"]."/curl_client.php");

class CrmClient {

  private function __construct() {}

  public static function sendToCRM(array $params) {

  if(!array_key_exists('TITLE',$params) || !array_key_exists('EMAIL_HOME',$params) 
     || !array_key_exists('NAME',$params)) {

     return false;
  }

  $default_params = array(
    "LOGIN" => "admin",
    "PASSWORD" => "212121",
    "SOURCE_ID" => "WEB_FORM",
    "STATUS_ID" => "NEW",
    "ASSIGNED_BY_ID" => 1,
  );
  $params = array_merge($default_params,$params);

  $http_client = new CurlClient("http://b24.internetdeveloper.ru/crm/configs/import/lead.php");
  $http_client->setParams($params);
  $responce = $http_client->send(true);

  if($responce['error'] == 201) {

      return true;
  }

  return false;
 }
}
?>