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
    "USAGE:\n" .   "php plugin.php metadisplay " .
     "[-h] [--no-colors]  [--loglevel ] \n [[-n --namespace|.] [[-p -page] [-e --exact ]][-c --cmdL]][[-b --before|-a --after] timestamp -d -dtype[modified|created]]. "   
	 .  '<b>timestamp</b> can be actual timestamp or a numerical date of the form <b>Year-Month-Day</b>' 
    );
    $options->registerOption('version', 'print version and exit', 'v');
    $options->registerOption('namespace',
	'metadata namespace; the -n option with dot [.]	defaults to the top level. This option cannot be left blank if it is not followed by a page name','n');
    $options->registerOption('page', 'page name without namespace or extension, e.g. start', 'p');
    $options->registerOption('exact', 'set to "on"  for exact <b><u>page</u></b> match', 'e');
    $options->registerOption('cmdL', 'set automatically to "html" when accessing from admin.php', 'c');
   $options->registerOption('before',  'before timestamp:[modified|created]', 'b');
    $options->registerOption('after', 'after timestamp:[modified|created]', 'a');
    $options->registerOption('dtype', '"created" or "modified",  for "--before" and "--after" timestamp', 'd');	  
    $options->registerOption('search', 'set to search term', 's');
}

// implement your code
protected function main(Options $options) {      
    if ($options->getOpt('namespace')) {    
       $opts = $options->getArgs();
        $namespace; $page;$exact;$search;$cl;$tm;$dtype;		
        $this->get_commandLineOptions($namespace, $page,$exact,$search,$cl,$tm,$dtype,$opts);  

        if($cl == 'html') {
            $helper =  plugin_load('helper','metadisplay_html'); 
         } else {
               $helper =  plugin_load('helper','metadisplay_plaintext');
           }           
            $helper->init($namespace, $page,$exact,$search, $tm, $dtype);
            
    }
    else if ($options->getOpt('version')) {
        $info = $this->getInfo();    
        $this->success($info['date']);
    } else {
        echo $options->help();
    }
}

function get_commandLineOptions(&$namespace, &$page,&$exact,&$search,&$cl,&$tm,&$dtype,$opts) {
    if(function_exists(is_countable($opts)) &&!is_countable($opts)) return;
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
        }
      }
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
	io_saveFile($dfile , "$date_time\n$msg\n\n",true);
}	
}  //end class definition



