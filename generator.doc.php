<?php

$dir = __DIR__;
$output = shell_exec('php "'.$dir.'/phpdox-0.12.0.phar" ');
echo $output;