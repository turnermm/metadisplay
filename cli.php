<?php
/**
 * Plugin metadisplay"
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca
 */
if(!defined('DOKU_INC')) die();
use splitbrain\phpcli\Options;

class cli_plugin_metadisplay extends DokuWiki_CLI_Plugin {
private $helper;    
protected function setup(Options $options) {
    $options->setHelp('Displays metadata for specified namespace or page' . "\n".
    "USAGE (from Command Line):\n" .   "php plugin.php metadisplay " .
     "[-h] [--no-colors]  [--loglevel ] \n [[-n --namespace|.] [[-p -page] [-e --exact ]][-c --cmdL]][[-b --before|-a --after] timestamp -d -dtype [modified|created]] [[-s --search|-f --fuzzy] [search-term] [-l --ltype contrib|creator]] -c --cmdL. "   
	 . "\n" . '<br /><b>timestamp</b> can be timestamp or numerical date of the form: <br /><b>Year-Month-Day</b>' 
    );
    $options->registerOption('version', 'print version and exit', 'v');
    $options->registerOption('namespace',
	'metadata namespace; the -n option with dot [.]	defaults to the top level. This option cannot be left blank if it is not followed by a page name','n');
    $options->registerOption('page', 'page name without namespace or extension, e.g. start', 'p');
    $options->registerOption('exact', 'set to "on"  for exact <b><u>page</u></b> match', 'e');
    $options->registerOption('cmdL', 'set automatically to "html" when accessing from admin.php', 'c');
    $options->registerOption('before',  'before timestamp', 'b');
    $options->registerOption('after', 'after timestamp', 'a');
    $options->registerOption('dtype', 'sets whether file\'s timestamp is read from "created" or "modified" field', 'd');	  
    $options->registerOption('search', 'set to search term, exact match', 's');
    $options->registerOption('fuzzy', 'set to search term, fuzzy match', 'f');
    $options->registerOption('ltype', 'set to search type: link, media, creator, contrib (contrib = contributor)', 'l');    
}

// implement your code
protected function main(Options $options) {      
    if ($options->getOpt('namespace')) {    
       $opts = $options->getArgs();
       		
        $clopts = $this->get_commandLineOptions($opts);  

        if($clopts['cl'] == 'html') {
            $helper =  plugin_load('helper','metadisplay_html'); 
         } else {
               $helper =  plugin_load('helper','metadisplay_plaintext');
           }           
         
          $helper->init($clopts);
            
    }
    else if ($options->getOpt('version')) {
        $info = $this->getInfo();    
        $this->success($info['date']);
    } else {
        echo $options->help();
    }
}

function get_commandLineOptions($opts) {
    if(function_exists('is_countable') &&!is_countable($opts)) return;
    
    $page=""; $exact=""; $cl=""; $search=""; $fuzzy=""; $tm=""; $dtype=""; $ltype="";
    $namespace = array_shift($opts);
    for($i=0; $i<count($opts); $i++) {
        $cl_switch = trim($opts[$i],'-');
        switch ($cl_switch) {
        case 'p':
        case 'page':    
            $page =  $opts[$i+1];         
            break;
        case 'e':
        case 'exact':
            $exact =  $opts[$i+1];            
            break;
        case 's':
        case 'search': 
           $search =   $opts[$i+1];  
            break;
        case 'f':
        case 'fuzzy': 
           $fuzzy =   $opts[$i+1];                   
           break;           
        case 'c':
        case 'cmdL':
          $cl = $opts[$i+1];
          break;
        case 'b':  
        case 'before':
            $tm = $this->get_timestamp( $opts[$i+1]) . ':b';
          break;
        case 'a':  
        case 'after':
            $tm = $this->get_timestamp( $opts[$i+1]) . ':a';
            break;       
        case 'd':  
        case 'dtype':
            $dtype = $opts[$i+1];
          break;   
        case 'l':
        case 'ltype':
          $ltype  = $opts[$i+1];
          break;
        }
      }
           
      $ret = array('namespace'=>$namespace,'page'=>$page,'exact'=>$exact,'search'=>$search,'fuzzy'=>$fuzzy,
           'cl'=>$cl,'tm'=>$tm,'dtype'=>$dtype, 'ltype'=>$ltype);
      return $ret;     
}

function get_timestamp($date_str){
    list($year,$month,$day) = explode('-',$date_str);
    $hour = '0'; $min = '01'; $second = '0';
    return  mktime($hour, $min, $second,$month,$day,$year);
}
public function write_debug($msg) {	
return;
	$dfile = $metafile = metaFN("dbg:debug",'.dbg');
	$date_time = date('Y-m-d h:i:s');
	io_saveFile($dfile , "$date_time\n$msg\n",true);
}	
}  //end class definition



