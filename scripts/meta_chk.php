#!/usr/bin/php

<?php
echo "<pre>";

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
echo "prefix = $prefix\n";
$prefix = ($depth = str_replace('/', ':', $prefix)) ? $depth : '';

echo "prefix = $prefix\n";
//exit;
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
