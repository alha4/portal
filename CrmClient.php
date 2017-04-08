<?
require_once($_SERVER["DOCUMENT_ROOT"]."/curl_client.php");

class CrmClient {

  static $PORTAL_URL;
  static $RESPONSIBLE;
  static $LOGIN;
  static $PASSWORD;
  static $LAST_ERROR;

  const PATH_REST_API = '/crm/configs/import/lead.php';
 
  private function __construct() {}

  public static function sendToCRM(array $params) {

  if(!array_key_exists('TITLE',$params) 
    || !array_key_exists('EMAIL_HOME',$params) 
    || !array_key_exists('NAME',$params)) {

    return false;
  }

  $default_params = array(
    "LOGIN"     => CrmClient::$LOGIN,
    "PASSWORD"  => CrmClient::$PASSWORD,
    "SOURCE_ID" => "WEB_FORM",
    "STATUS_ID" => "NEW",
    "ASSIGNED_BY_ID" => CrmClient::$RESPONSIBLE,
  );

  $params = array_merge($default_params,$params);

  $url = self::$PORTAL_URL.CrmClient::PATH_REST_API;

  $http_client = new CurlClient($url);
  $http_client->setParams($params);
  $responce = $http_client->send(true);

  if($responce['error'] == 201) {

      return true;
  }

  self::$LAST_ERROR = $responce['error'];

  return false;
 }
}
?>