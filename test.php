<?php

if(!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../../../') . '/');
chdir(DOKU_INC . 'bin');
$res=shell_exec('php ./plugin.php  metadisplay  -n playground');
echo $res;
echo "done";