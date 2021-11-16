<?php
/**
 * Plugin metadisplay"
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca
 */
if(!defined('DOKU_INC')) die();
global $timezone, $current,$conf;

class helper_plugin_metadisplay_html extends DokuWiki_Plugin {
private $subdir = "";    
private $page;
private $match = false;
private $exact_page_match = false;
private $timestamp;
private $t_when;
private $dtype;
function init($subdir="", $page="",$exact="off", $search="", $tm="", $dtype="") {
  global $conf;  

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
    ob_start();
    $this->recurse('.');
    if(!$this->match){
        echo "No match for  $subdir:$page" ."<br />\n";
    }
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
             if($this->page && !preg_match("/" . $this->page ."/",$file)) continue;
             if($this->exact_page_match) {
                if(!preg_match("/^" . $this->page ."\.meta$/",$file)) continue;                 
             }
             $this->match = true;
             $store_name = preg_replace('/^\./', $this->subdir, "$dir/$file");         
             $id_name = PAGES . preg_replace("/\.meta$/","",$store_name) . '.txt';        
             if(!file_exists($id_name)) continue;            
             $success = $this->get_data("$dir/$file","$id_name",$store_name);
             if($success) {
             echo "\n<br />";
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
    $data = file_get_contents($file);
    $data_array = @unserialize(file_get_contents($file));   
    $creator =""; $creator_id="";
  
    if ($data_array === false || !is_array($data_array)) return; 
    if (!isset($data_array['current'])) return;

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
    $this->match = true;
    echo $store_name ."\n";
    echo "\n" . '<table style="border-top:2px solid">' ."\n";
    echo "<tr><th colspan='2'>$id_path</th></tr>\n";	
    $keys =  array('title','date','creator','last_change','relation', 'description');
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
                    echo "<tr><th colspan='2'>Last Change</th>\n"; 
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
            case 'description':
                echo "<tr><th colspan='2'>Description</th></tr>\n"; 
                $description = htmlentities($this->getcurrent($header,'abstract'));
                $description = preg_replace("/[\n]+/",'<br />', $description);
                echo "<td colspan='2'>$description</td></tr>\n";            
                break;         
            default:
                 break;
            }

        }  
       echo "\n</table>\n";
       return true;
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
     echo "<tr><th colspan='2'>Relation</th></tr>\n";
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
function get_timestamp($hour=12, $min=60, $second=60,$month=1,$day=1,$year=1950) {
    return  mktime($hour, $min, $second,$month,$day,$year);
}
}