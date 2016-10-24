<?php
  require_once "vendor/autoload.php";
  require_once 'HTTP/Request2.php';

  header('Content-Type: application/json');

  // Emotion API Subscription key
  $key = $_ENV['Emotion_API_Key'];

  $happy = false;
  $result = "";
  $error = "";

  //Getting form POST values
  $filename = $_FILES['file']['tmp_name'];
  $param1 = $_POST['param1'];
  $param2 = $_POST['param2'];

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

  // ######### To Fix the SSL issue ###########
  $request->setConfig(array(
    'ssl_verify_peer'   => FALSE,
    'ssl_verify_host'   => FALSE
  ));
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
  }
  else
  {
    //TODO
    $result = ('Déploiement annulé pour cause de mauvaise humeur !');
  }

  // //Returning the result for display
   echo ('{"result":"' . $result . '","error":"' . $error . '"}');
?>