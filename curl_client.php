<?php

class CurlClient {

   private $curl;
   private $options;
   private $url;
 
   public function __construct($url,$header = false,$headers = false) {

      $this->url  = $url;
      $this->curl = curl_init();
      curl_setopt($this->curl,CURLOPT_SSL_VERIFYPEER,0);
      curl_setopt($this->curl,CURLOPT_SSL_VERIFYHOST,0);
      curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,1);
      curl_setopt($this->curl,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
      curl_setopt($this->curl,CURLOPT_FOLLOWLOCATION,1);

      if($header) 
          curl_setopt($this->curl,CURLOPT_HEADER,1);

       if($headers) {

          curl_setopt($this->curl,CURLOPT_HTTPHEADER,$headers);

       } else {

         curl_setopt($this->curl,CURLOPT_HTTPHEADER, array(
                                               'Content-Type: application/x-www-form-urlencoded'
                                              ));
       }
   }
   public function __destruct() {

       curl_close($this->curl);
   }
   function setParams(array $params) {

       $this->options = $params;  
   }

   public function send($is_post = false) {

       if($is_post) {
          
          curl_setopt($this->curl,CURLOPT_URL, $this->url);
          curl_setopt($this->curl,CURLOPT_POST,1);
          curl_setopt($this->curl,CURLOPT_POSTFIELDS,http_build_query($this->options));
 

       } else {

          curl_setopt($this->curl,CURLOPT_URL, $this->url.'?'.http_build_query($this->options));
    
       }

       $responce = curl_exec($this->curl);

       if(curl_errno($this->curl)) {
          return curl_error($this->curl);
       }

       $json_in = array("{'", "'}", "':'", "','");
       $json_out = array('{"', '"}', '":"', '","');
       $json = str_replace($json_in, $json_out, $responce);
       $json_arr = json_decode($json, true);

       return $json_arr;
   }
}
?>