<?php

if(!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../../../') . '/');
chdir(DOKU_INC . 'bin');

?>
 <form action="test.php" method="post">
<input type="text" name="namespace" placeholder="namespace" />
<input type="text" name="user" placeholder="user" />
<input type="submit" name="submit" />
</form> 
<?php
if ( isset( $_POST['submit'] ) ) { 
echo '<h3>Form POST Method</h3>'; 
echo 'Your name is ' . $_POST['user'] .'</br> ';
echo  'Namespace: ' . $_POST['namespace']; 
 $res=shell_exec('php ./plugin.php  metadisplay  -n ' . $_POST['namespace']);
echo $res;
echo "done";
}
?>