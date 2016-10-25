<?php
require_once __DIR__."/../vendor/autoload.php";
use Symfony\Component\Process\Process;
// Turn off output buffering
ini_set('output_buffering', 'off');
// Turn off PHP output compression
ini_set('zlib.output_compression', false);
// Implicitly flush the buffer(s)
ini_set('implicit_flush', true);
ob_implicit_flush(true);
// Clear, and turn off output buffering
while (ob_get_level() > 0) {
    // Get the curent level
    $level = ob_get_level();
    // End the buffering
    ob_end_clean();
    // If the current level has not changed, abort
    if (ob_get_level() == $level) break;
  }
?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeployOrNot?</title>
    <style>
      body,.console {  font-family: Lucida Console,Lucida Sans Typewriter,monaco,Bitstream Vera Sans Mono,monospace; 
      background-color: #222;
      color: green;
      padding:80px;
      height: auto;
      overflow: auto;
      border:none;}

    pre.console {  font-family: Lucida Console,Lucida Sans Typewriter,monaco,Bitstream Vera Sans Mono,monospace; 
      background-color: #222;
      color: green;
      padding:80px;
      height: auto;
      overflow: auto;
      border:none;}
    </style>
</head>
<body>
<pre class="console">
<?php

$environment = preg_replace("/[^A-Za-z0-9 ]/", '', $_GET["environment"]);
$project = preg_replace("/[^A-Za-z0-9 ]/", '', $_GET["project"]);

  if ($environment && $project){
    $command = "platform environment:merge -y -p ". $project . " ".$environment ;
    $process = new Process($command);
    $process->run(function ($type, $buffer) {
        if (Process::ERR === $type) {
            echo '<span class="error">'.$buffer.'</span>';
        } else {
            echo  $buffer;
        }
    });
    
  } else
  {
    echo "Vous devez spécifier un projet et un environnment à déployer";
  }
?>
</pre>
</body>