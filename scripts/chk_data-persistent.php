#!/usr/bin/php

<?php
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
global $timezone, $persistent;

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
    global $persistent;
    $data = file_get_contents($file);
    $data_array = @unserialize(file_get_contents($file));
    if ($data_array === false || !is_array($data_array)) return;

    if (!isset($data_array['persistent'])) return;
    $persistent = $data_array['persistent'];

    $date_created = persistent('date', 'created');
    if ($date_created) {
        $rfc_cr = date("r", $date_created);
        echo "Date created: ".$rfc_cr.
        " (".$date_created.
        ")\n";
    }
    $date_modified = persistent('date', 'modified');
    if ($date_modified) {
        $rfc_mod = date("r", $date_modified);
        echo "Last modified: ".
        "$rfc_mod  (".$date_modified.
        ")\n";
    }
    $creator = persistent('creator', null);
    $creator_id = persistent('user', null);
    echo "Created by: $creator  (userid: $creator_id)\n";

    $contributors = persistent('contributor', null);
    if (is_array($contributors)) {
        echo "Contributors:\n";
        print_key_values($contributors);
    }
    $last_change = persistent('last_change', null);
    if (is_array($last_change)) {
        echo "Last Change: \n";
        print_key_values($last_change);
    }

    $relation = isset($data_array['current']['relation']) ? $data_array['current']['relation'] : array();
    if (!empty($relation) && !empty($relation['references'])) {
        echo "Internal links:\n";
        print_key_values($relation['references'], true);
    }
    $persistent = array();
}

function print_key_values($ar, $keys_only = false) {
    foreach($ar as $key => $val) {
        if ($keys_only) {
            echo "\t$key\n";
        } else echo "\t$key => $val\n";
    }

}

function persistent($which, $other) {
    global $persistent;
    if (!isset($persistent)) return "";
    if ($other) {
        if (isset($persistent[$which][$other])) {
            return $persistent[$which][$other];
        }
    }
    if (isset($persistent[$which]) && $other === null) {
        return $persistent[$which];
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
            echo "storage name = $store_name\n";
            $store_name = str_replace('/', ':', $store_name);
            echo "storage name = $store_name\n";
            echo "($count) $dir/$file\n";
            $count++;
            get_data("$dir/$file");
            echo "\n";
        }
    }

    closedir($dh);
}
echo "</pre>";
