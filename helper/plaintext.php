<?php
/**
 * Plugin metadisplay"
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca
 */
if(!defined('DOKU_INC')) die();
global $timezone, $current,$conf;

class helper_plugin_metadisplay_plaintext extends DokuWiki_Plugin {
private $subdir = "";    
private $page;
private $match = false;
private $exact_page_match = false;
private $timestamp;
private $t_when;
private $dtype;
private $search;
private $fuzzy;

//function init($subdir="", $page="", $exact="off", $search="", $fuzzy="", $tm="", $dtype="") {
function init($options) {    
   global $conf;  
 // $subdir=""; $page=""; $exact="off"; $search=""; $fuzzy=""; $tm=""; $dtype="";
  $subdir=$options['namespace'];
  $page=$options['page'];
  $exact=$options['exact'];
  $search=$options['search'];
  $fuzzy=$options['fuzzy'];
  $tm=$options['tm'];
  $dtype=$options['dtype'];

  if($conf['savedir'] == './data') {
      chdir(DOKU_INC . trim($conf['savedir'],'.\/') . '/meta');  
      define ('PAGES', DOKU_INC . trim( $conf['savedir'],"\/\\\.") . '/pages');  
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
	if($tm) {
	 list($this->timestamp,$this->t_when) = explode(':',$tm);
	 $this->dtype = $dtype;
	}
    if($search) {     
       $this->search = $search;
	}
    else if($fuzzy) {
       $this->fuzzy = $this->get_regex($fuzzy);
    }
	
    ob_start();
    $this->recurse('.');
    if(!$this->match){
        if($page) $page = ":$page";
        if($subdir) $subdir = "for $subdir";
        echo "No match  $subdir$page" ."\n";
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
            
             $store_name = preg_replace('/^\./', $this->subdir, "$dir/$file");         
             $id_name = PAGES . preg_replace("/\.meta$/","",$store_name) . '.txt';        
             if(!file_exists($id_name)) continue;            
             $success = $this->get_data("$dir/$file","$id_name",$store_name);
             if($success) {
                 $this->match = true;    
                 echo "\n";
        }
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
    $description = "";
    $data = file_get_contents($file);
    $data_array = @unserialize(file_get_contents($file));   
    $creator =""; $creator_id="";
  
    if ($data_array === false || !is_array($data_array)) return; 
    if (!isset($data_array['current'])) return false;

    $current = $data_array['current'];
	if($this->t_when) {
		$tmstmp = $this->getcurrent('date', $this->dtype);	
		if($this->t_when == 'b' && $tmstmp > $this->timestamp) {
			return false;
		}
		else if($this->t_when == 'a' && $tmstmp < $this->timestamp) {
			return false;
		}
	}
   
    $search = "";
    $regex = "";
    if($this->fuzzy) {
        $search = $this->fuzzy;
        $regex = '/' . $search . '/i';
    }
    else if($this->search) {
        $search = $this->search;
        $regex = '/\b' . $search . '\b/';
    }
    if($regex) {        
        $description = $this->getcurrent('description','abstract');        
        if(!preg_match($regex,$description)){
            return false;
		}
        $description = str_replace($search," [[$search]]",$description);    
	}
   
    $this->match = true;  
    echo "\n----------------\n$store_name";  
    echo "\n$id_path\n";  
    
    $keys =  array('title','date','creator','last_change','relation','description');
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
             case 'description':              
                echo "<tr><th colspan='2'>Description</th></tr>\n"; 
                if(!$description) {
                    $description = htmlentities($this->getcurrent($header,'abstract'));
                }
                $description = preg_replace("/[\n]+/","\n", $description);
                echo "<td colspan='2'>$description</td></tr>\n";            
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

function get_regex($str) {
    $str = preg_replace('{([aeiou])\1+}','$1',$str);
    $a = str_split($str);
    
    for($i = 0; $i < count($a); $i++) {
        if(preg_match("/[aeiou]/",$a[$i])) {
            $a[$i] = '[aeiou]+';
        }
    }
    return implode("",$a);
}
}