<?php
require_once __DIR__."/../vendor/autoload.php";
require_once 'HTTP/Request2.php';

header('Content-Type: application/json');

// Emotion API Subscription key
if($key = getenv('EMOTION_API_KEY')){ 

  $happy = false;
  $result = "";
  $error = "";

  //Getting form POST values
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

  try
  {
    $response = $request->send();
    $arr = json_decode($response->getBody(), true);
    if(round($arr[0]['scores']['happiness']) == 1)
    {
      $happy = true;
    }
  }
  catch (HttpException $ex)
  {
    $error = $ex;
  }
  
  //Calling the platform.sh API
  if($happy)
  {
    //TODO
    $result = 'Déploiement confirmé !';
  } else {
    //TODO
    $result = ('Déploiement annulé pour cause de mauvaise humeur !');
  }
} else {
  $error = ('Clé API non fournie.');
  $result = ('Humeur inconnue, déploiement réfusé !');
}
// //Returning the result for display
echo ('{"result":"' . $result . '","error":"' . $error . '"}');