<?php

if(!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../../../') . '/');
chdir(DOKU_INC . 'bin');

?>
 <form action="test.php" method="post">
 <div>
<input type="text" name="namespace" placeholder="namespace" />
<input type="text" name="page" placeholder="page name" />
<input type="text" name="user" placeholder="user" />
<input type="password" name="pwd" placeholder="password"/>
</div>
<div style="line-height:2">
<input type="submit" name="submit"/>
<input type="submit" value="help" name="help" />
</div>
</form> 
<?php
if ( isset( $_POST['submit'] ) ) { 
    $start_dir = $_POST['namespace'] ? $_POST['namespace'] : '.';
    $cmdline = 'php ./plugin.php  metadisplay  -n ' . $start_dir;
    if(isset($_POST['page'])) {
        $cmdline .= " -p " . $_POST['page'];
    }    
    if(isset($_POST['user'])) {
        $cmdline .= " -u " . $_POST['user'];
    }     
     if(isset($_POST['pwd'])) {
        $cmdline .= " -l " . $_POST['pwd'];
    }    
  $res=shell_exec($cmdline);
echo $res;
}
else if ( isset( $_POST['help'] ) ) { 
   echo "<h3>Command Line Options</h3>";
    $res=shell_exec('php ./plugin.php  metadisplay  -h');
    echo '<pre>' .$res  .'</pre>';
}
?>