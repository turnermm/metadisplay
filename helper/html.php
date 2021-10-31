<?php
global $timezone, $current,$conf;
define ('PAGES',  '/'.trim( $conf['savedir'],"\/\\") . '/pages') ;

class helper_plugin_metadisplay_html extends DokuWiki_Plugin {
function init($dir) {
    global $conf;
    echo $dir ."\n"; exit;
    chdir( '/'.trim( $conf['savedir'],"\/\\") . '/meta');  
    $timezone = 'UTC'; // default timezone is set to Coordinated Univeral Time. You can reset your timezone here
    date_default_timezone_set($timezone);
    ob_start();
    $this->recurse('.');
    $contents = ob_get_contents();
    ob_end_clean();
    $contents = str_replace("<table.*?>\n</table>","",$contents);
    echo $contents;
 }
 
function recurse($dir) {

    $dh = opendir($dir);
    if (!$dh) return;

    while (($file = readdir($dh)) !== false) {
        if ($file == '.' || $file == '..') continue;
        if (is_dir("$dir/$file")) $this->recurse("$dir/$file");
        if (preg_match("/\.meta$/", $file)) {            
            $store_name = preg_replace('/^\./', $prefix, "$dir/$file");
            $store_name = preg_replace('/^\./', "", "$dir/$file");
            $id_name = PAGES . preg_replace("/\.meta$/","",$store_name) . '.txt';            
            if(!file_exists($id_name)) continue;            
            $this->get_data("$dir/$file","$id_name");
            echo "\n";
        }
    }

    closedir($dh);
}

/*
  @param string $file, the meta file
  @param string $id_path, path to dokuwiki page   
*/
function get_data($file,$id_path) {
    global $current;
    $data = file_get_contents($file);
    $data_array = @unserialize(file_get_contents($file));   
    $creator =""; $creator_id="";
  
    if ($data_array === false || !is_array($data_array)) return; 
    if (!isset($data_array['current'])) return;
    echo "\n" . '<table style="border-top:2px solid">' ."\n";
    echo "<tr><td colspan='2'>$id_path</td></tr>\n";
    echo "<tr><td colspan='2'>$file</td></tr>\n";
    $current = $data_array['current'];
    $keys =  array('title','date','creator','last_change','relation');
    foreach ($keys AS $header) {
        switch($header) {
            case 'title':               
                 $title = $this->getcurrent($header, null);
                 echo "<tr><td colspan='2'>Title: <b>$title</b></td></tr>\n";
                 break;                     
                
            case 'date':                        
                 $this->process_dates($this->getcurrent('date', 'created'),$this->getcurrent('date', 'modified'));  
                 break;                 
            case 'user':
                if($creator || $creator_id) break; 
            case 'creator':
                $creator = $this->getcurrent('creator', null);
                $creator_id = $this->getcurrent('user', null);
                $this->process_users($creator,$creator_id);  
                 break;
           
            case 'last_change':                                           
                $last_change = $this->getSimpleKeyValue($this->getcurrent($header, null),"last_change");
                 if($last_change) {
                    echo "<tr><td colspan='2'>Last Change</td>\n"; 
                    echo "<td>$last_change</td></tr>\n"; 
                }
                break;              
            case 'contributor':       
                 $contributors = $this->getSimpleKeyValue($this->getcurrent($header, null));
                 break;   
            case 'relation':                
                $isreferencedby = $this->getcurrent($header,'isreferencedby');
                $references = $this->getcurrent($header,'references');
                $media = $this->getcurrent($header,'media');
                $firstimage = $this->getcurrent($header,'firstimage');
                $haspart = $this->getcurrent($header,'haspart');
                $subject = $this->getcurrent($header,'subject');
                $this->process_relation($isreferencedby,$references,$media,$firstimage,$haspart,$subject);
                break;
            default:

                 break;
            }

        }  
       echo "\n</table>\n";
       $current = array();
}

/*
*  @param array $ar metadata field
*  @param string $which which field  
*/
function getSimpleKeyValue($ar,$which="") {
    $retv = "";

    $types = array('C'=>'<u>C</u>reate','E'=>'<u>E</u>dit','e' =>'minor <u>e</u>dit','D'=>'<u>D</u>elete',
    'R'=>'<u>R</u>evert');
    if(!is_array($ar)) return false;         
    foreach ($ar As $key=>$val) {       
        if(!empty($val)) {           
           if($which == 'last_change')  {  
               if($key == 'date') {
                   $val = date("r", $val);
                }
                if($key == 'type')  {
                    $val = $types[$val];  
                }
           }

           $retv .= "<tr><td>$key:</td><td>$val</td></tr>\n";
       }
    }
    return $retv;
}

function process_users($creator,$user) {
        if(empty($creator)) {
            echo "\n"; return;
         }
        echo "<tr><td>Created by:</td><td> $creator (userid: $user)</tr></td>\n";
}

function process_dates($created, $modified) {   
    $retv = "";

    if ($created) {
        $rfc_cr = date("r", $created);
        echo "<tr><td>Date created:</td><td>".$rfc_cr.
        "</td><td>$created</td></tr>\n";
        }
   
    if ($modified) {
        $rfc_mod = date("r", $modified);
        echo "<tr><td>Last modified:</td><td>" . $rfc_mod .
        "</td><td>$modified</td></tr>\n"; 
     }

}

function insertListInTable($list,$type) {
    if($list) echo "<tr><td>$type</td><td>$list</td></tr>\n";
}
function process_relation($isreferencedby,$references,$media,$firstimage,$haspart,$subject) {
  
    if(!empty($isreferencedby)) {         
        $list = $this->create_list(array_keys($isreferencedby));
        $this->insertListInTable($list,'Backlinks');
    }
    if(!empty($references)) {           
       $list = $this->create_list(array_keys($references));
       $this->insertListInTable($list,'Links');           
    }
    if(!empty($media)) {          
       $list = $this->create_list(array_keys($media));
       $this->insertListInTable($list,'Media');           
    }
    if(!empty($firstimage)) {
       echo "<tr><td>First Image</td><td colspan='2'>$firstimage</td></tr>";      
    }   
    if(!empty($haspart)) {      
       $list = $this->create_list(array_keys($haspart)); 
      $this->insertListInTable($list,'haspart');
    }  
    if(!empty($subject)) {
       $list = create_list(array_keys($subject));
       $this->insertListInTable($list,'Subject');
    }       
 
}

function create_list($ar) {
    $list = "\n<ol>\n";
    for($i=0; $i<count($ar); $i++) {
        $list .= '<li>'. $ar[$i] . "</li>\n";
    }
     $list .= "</ol>\n";
     return $list;
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
}