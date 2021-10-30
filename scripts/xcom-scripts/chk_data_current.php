#!/usr/bin/php

<?php
define ('PAGES', '/home/samba/html/mturner/devel/data/pages');
echo "<pre>";
/** 
@Auth Myron Turner <turnermm02@shaw.ca>
find your timezone here http://php.net/manual/en/timezones.php
This script can be run from the command line without parameters and will use the current directory as its starting point:
     chk_date  or ./chk+date (if the script is in your current directory)
It can also be run with a directory:  
    chk_date /var/www/html/dolkuwiki/meta 
    
 Sample outut   
 
./test/links.meta
Date created: Mon, 24 Oct 2016 00:17:36 +0000 (1477268256)  //installed from an external source and never modfied

./functions.meta
Date created: Tue, 25 Oct 2016 01:37:53 +0000 (1477359473)  UTC date and (UNIX timestamp)
Last modified: Tue, 25 Oct 2016 01:58:33 +0000  (1477360713)

./changes.meta
Date created: Tue, 25 Oct 2016 10:25:15 +0000 (1477391115)
Last modified: Tue, 25 Oct 2016 11:09:56 +0000  (1477393796)

*/
global $timezone, $current;

$timezone = 'UTC'; // default timezone is set to Coordinated Univeral Time. You can reset your timezone here , for instance "America/Chicago", "Europe/Berlin"
date_default_timezone_set($timezone);
if ($argc > 1) {
    echo $argv[1].
    "\n";
    chdir($argv[1]);
}
global $prefix;
$realpath = realpath('.');
echo "realpath=$realpath\n";
$prefix = preg_replace("/.*?\/data\/meta/", "", $realpath);
$prefix = ($depth = str_replace('/', ':', $prefix)) ? $depth : '';

echo "prefix = $prefix\n";

recurse('.');


function get_data($file) {
    global $current;
    $data = file_get_contents($file);
    $data_array = @unserialize(file_get_contents($file));

  
    if ($data_array === false || !is_array($data_array)) return;

   //  $keys = array_keys($data_array['persistent']);
    // echo print_r($keys,1) ."\n";
  
    if (!isset($data_array['current'])) return;
   echo "-----------------------------\n\n";   
    $current = $data_array['current'];
    echo print_r($current,1) ."\n";
    $keys = array_keys($data_array['current']);
    echo print_r($keys,1) ."\n";
   
    $date_created = getcurrent('date', 'created');
    if ($date_created) {
        $rfc_cr = date("r", $date_created);
        echo "Date created: ".$rfc_cr.
        " (".$date_created.
        ")\n";
    }
    $date_modified = getcurrent('date', 'modified');
    if ($date_modified) {
        $rfc_mod = date("r", $date_modified);
        echo "Last modified: ".
        "$rfc_mod  (".$date_modified.
        ")\n";
    }
    $creator = getcurrent('creator', null);
    $creator_id = getcurrent('user', null);
    echo "Created by: $creator  (userid: $creator_id)\n";

    $contributors = getcurrent('contributor', null);
    if (is_array($contributors)) {
        echo "Contributors:\n";
        print_key_values($contributors);
    }
    $last_change = getcurrent('last_change', null);
    if (is_array($last_change)) {
        echo "Last Change: \n";
        print_key_values($last_change);
    }

    $relation = isset($data_array['current']['relation']) ? $data_array['current']['relation'] : array();
    if (!empty($relation) && !empty($relation['references'])) {
        echo "Internal links:\n";
        print_key_values($relation['references'], true);
    }
  echo "==========================\n\n";   
    $current = array();
}

function print_key_values($ar, $keys_only = false) {
    foreach($ar as $key => $val) {
        if ($keys_only) {
            echo "\t$key\n";
        } else echo "\t$key => $val\n";
    }

}

function getcurrent($which, $other) {
    global $current;
    if (!isset($current)) return "";
    if ($other) {
        if (isset($current[$which][$other])) {
            return $current[$which][$other];
        }
    }
    if (isset($current[$which]) && $other === null) {
        return $current[$which];
    }
    return "";
}



function recurse($dir) {
    global $prefix;
    static $count;
    $dh = opendir($dir);
    if (!$dh) return;
    if (!isset($count)) $count = 1;

    while (($file = readdir($dh)) !== false) {
        if ($file == '.' || $file == '..') continue;
        if (is_dir("$dir/$file")) recurse("$dir/$file");
        if (preg_match("/\.meta$/", $file)) {            
            $store_name = preg_replace('/^\./', $prefix, "$dir/$file");
            $id_name = PAGES . preg_replace("/\.meta$/","",$store_name) . '.txt';
            echo "ID NAME $id_name\n";
            if(!file_exists($id_name)) continue;
            echo "storage name = $store_name\n";
            $store_name = str_replace('/', ':', $store_name);
            echo "storage name = $store_name\n";
            echo "($count) $dir/$file\n";
            $count++;
            echo "NEW FILE: $dir/$file\n";
            get_data("$dir/$file");
            echo "\n";
        }
    }

    closedir($dh);
}
echo "</pre>";
