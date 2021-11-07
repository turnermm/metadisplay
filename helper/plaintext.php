<?php
if(!defined('DOKU_INC')) die();
global $timezone, $current,$conf;

class helper_plugin_metadisplay_plaintext extends DokuWiki_Plugin {
private $subdir = "";    
private $page;
private $match;
private $exact_page_match = false;

function init($subdir="", $page="",$exact="off", $search="") {
   global $conf;  
  if($conf['savedir'] == './data') {
      chdir(DOKU_INC . trim($conf['savedir'],'.\/') . '/meta');  
      define ('PAGES', DOKU_INC . '/'.trim( $conf['savedir'],"\/\\\.") . '/pages');  
  }      
   else {
      chdir( '/'.trim( $conf['savedir'],"\/\\") . '/meta'); 
      define ('PAGES',  '/'.trim( $conf['savedir'],"\/\\") . '/pages') ;    
   }    
    if($subdir == '.') $subdir = "";
    $this->page=str_replace(':', "",$page); 
    if($subdir) {
         $subdir = trim($subdir,':\\\/');
         $this->subdir ="/$subdir";   
         chdir($subdir);
    }
    if($exact == 'on') $this->exact_page_match = true;
    $timezone = 'UTC'; // default timezone is set to Coordinated Univeral Time. You can reset your timezone here
    date_default_timezone_set($timezone);
    ob_start();
    $this->recurse('.');
    if(!$this->match){
        echo "No match for  $subdir:$page" ."\n";
    }
    $contents = ob_get_contents();
    ob_end_clean();
     echo $contents;
 }
 
function recurse($dir) {

    $dh = opendir($dir);
    if (!$dh) return;

    while (($file = readdir($dh)) !== false) {
        if ($file == '.' || $file == '..') continue;
        if (is_dir("$dir/$file")) $this->recurse("$dir/$file");
        if (preg_match("/\.meta$/", $file)) {      
             if($this->page && !preg_match("/" . $this->page ."/",$file)) continue;
             if($this->exact_page_match) {
                if(!preg_match("/^" . $this->page ."\.meta$/",$file)) continue;                 
             }
             $this->$match = true;
             $store_name = preg_replace('/^\./', $this->subdir, "$dir/$file");         
             $id_name = PAGES . preg_replace("/\.meta$/","",$store_name) . '.txt';        
             if(!file_exists($id_name)) continue;            
             $this->get_data("$dir/$file","$id_name",$store_name);
      
        }
    }

    closedir($dh);
}

/*
  @param string $file, the meta file
  @param string $id_path, path to dokuwiki page   
*/
function get_data($file,$id_path,$store_name="") {
    global $current;
    $data = file_get_contents($file);
    $data_array = @unserialize(file_get_contents($file));   
    $creator =""; $creator_id="";
  
    if ($data_array === false || !is_array($data_array)) return; 
    if (!isset($data_array['current'])) return;
    $this->match = true;
    echo "\n----------------\n$store_name";  
    echo "\n$id_path\n";  
    $current = $data_array['current'];
    $keys =  array('title','date','creator','last_change','relation');
    foreach ($keys AS $header) {
        switch($header) {
            case 'title':               
                 $title = $this->getcurrent($header, null);
                 echo "\n[Title: $title]";
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
                    echo "[Last Change] \n$last_change\n"; 
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
       echo "\n\n";
       $current = array();
}

/*
*  @param array $ar metadata field
*  @param string $which which field  
*/
function getSimpleKeyValue($ar,$which="") {
    $retv = "";

    $types = array('C'=>'>Create','E'=>'>Edit','e' =>'minor >edit','D'=>'>Delete',
    'R'=>'>Revert');
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

           $retv .= "$key: $val\n";
       }
    }
    return $retv;
}

function process_users($creator,$user) {
        if(empty($creator)) {
            echo "\n"; return;
         }
        echo "\n\nCreated by: $creator (userid: $user)\n";
}

function process_dates($created, $modified) {   
    $retv = "";

    if ($created) {
        $rfc_cr = date("r", $created);
        echo "\nDate created:".$rfc_cr.
        "   $created\n";
        }
   
    if ($modified) {
        $rfc_mod = date("r", $modified);
        echo "Last modified: " . $rfc_mod .
        "  $modified\n"; 
     }

}

function insertListInTable($list,$type) {
    if($list) echo "$type $list\n";
}
function process_relation($isreferencedby,$references,$media,$firstimage,$haspart,$subject) {
  
    if(!empty($isreferencedby)) {         
        $list = $this->create_list(array_keys($isreferencedby));
        $this->insertListInTable($list,'Backlinks');
    }
    if(!empty($references)) {           
       $list = $this->create_list(array_keys($references));
       $this->insertListInTable($list,'[Links]');           
    }
    if(!empty($media)) {          
       $list = $this->create_list(array_keys($media));
       $this->insertListInTable($list,'[Media]');           
    }
    if(!empty($firstimage)) {
       echo "First Image] \n$firstimage\n";      
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
    $list = "\n";
    for($i=0; $i<count($ar); $i++) {
        $list .= "$i) ". $ar[$i] . "\n";
    }
     $list .= "\n";
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