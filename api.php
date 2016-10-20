<?php
  header('Content-Type: application/json');

  $happy = false;
  $result = "";
  $error = "";

  //Calling the Cognitive Services API
  //TODO
  
  //Calling the platform.sh API
  if($happy)
  {
    //TODO
    $result = 'Déploiement confirmé !';
  }
  else
  {
    //TODO
    $result = 'Déploiement annulé pour cause de mauvaise humeur !';
  }

  //Returning the result for display
  echo ('{"result":"' . $result . '","error":"' . $error . '"}');
?>