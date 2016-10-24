<?php
require_once __DIR__."/../vendor/autoload.php";
require_once 'HTTP/Request2.php';

header('Content-Type: application/json');

// Emotion API Subscription key
if($key = getenv('EMOTION_API_KEY')){ 

  $happy = false;
  //Getting form POST values
  if (isset($_FILES['file']['tmp_name'])){
    $filename = $_FILES['file']['tmp_name'];
    //Calling the Cognitive Services API
    $request = new Http_Request2('https://api.projectoxford.ai/emotion/v1.0/recognize');
    $url = $request->getUrl();
  
    $headers = array(
      // Request headers
      'Content-Type' => 'application/octet-stream',
      'Ocp-Apim-Subscription-Key' => $key,
    );
  
    $request->setHeader($headers);
  
    $parameters = array(
      // Request parameters
    );
  
    $url->setQueryVariables($parameters);
  
    $request->setMethod(HTTP_Request2::METHOD_POST);
    $content = file_get_contents($filename);
  
    $request->setBody($content);
    $result=[];
    try
    {
      $response = $request->send();
      $arr = json_decode($response->getBody(), true);
      $result["raw"]=$arr;
      if (is_array($arr) && !empty($arr)){
      if(round($arr[0]['scores']['happiness']) == 1)
        {
          $happy = true;
        }
      }
    }
    catch (HttpException $ex)
    {
      $error = $ex;
    }
    //Calling the platform.sh API
    if($happy)
    {

      $result["status"] = 1;
      $result["message"] = 'Déploiement Lancé !';
    } else {
      $result["status"] = 0;
      $result["message"] = ('Déploiement annulé pour cause de mauvaise humeur !');
    }
  } else {
    $result["status"] = 0;
    $result["error"] = ('Aucune image fournie.');
    $result["message"] = ('Humeur inconnue, déploiement réfusé !');
  }
} else
{
  $result["status"] = 0;
  $result["error"] = ('Clé API non fournie.');
  $result["message"] = ('Humeur inconnue, déploiement réfusé !');

}
// //Returning the result for display
echo (json_encode($result));