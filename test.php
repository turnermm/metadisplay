<?php

if(!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../../../') . '/');
chdir(DOKU_INC . 'bin');

?>
 <form action="test.php" method="post">
 <div>
<input type="text" name="namespace" placeholder="namespace" />
<input type="text" name="page" placeholder=":page:name" />
<input type="text" name="user" placeholder="user" />
</div>
<div style="line-height:2">
<input type="submit" name="submit"/>
<input type="submit" value="help" name="help" />
</div>
</form> 
<?php
if ( isset( $_POST['submit'] ) ) { 
echo '<h3>Form POST Method</h3>'; 
    $res=shell_exec('php ./plugin.php  metadisplay  -n ' . $_POST['namespace'] . ' -p ' . $_POST['namespace']  . ' -u  ' . $_POST['user'] );
echo $res;
}
else if ( isset( $_POST['help'] ) ) { 
    $res=shell_exec('php ./plugin.php  metadisplay  -h');
    echo '<pre>' .$res  .'</pre>';
}
?>