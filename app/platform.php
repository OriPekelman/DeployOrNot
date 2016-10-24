<?php
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
    <link href="/styles/css.css" rel="stylesheet">
</head>
<body class="console">
<pre>
<?php
  if ($project_id = getenv('PLATFORM_DEPLOY_PROJECT')){ 
    $environment_to_merge = $_GET["environment"];
    $command = "platform environment:merge -y -p ". $project_id . " ".escapeshellarg($environment_to_merge) . " 2>&1";
    system($command);
  } else
  {
    echo "Vous devez configurer un projet à déployer";
  }
?>
</pre>
</body>