<?php
if(!defined('DOKU_INC')) die();
use splitbrain\phpcli\Options;

class cli_plugin_metadisplay extends DokuWiki_CLI_Plugin {
private $helper;    
protected function setup(Options $options) {
    
    $options->setHelp('Displays metadata for specified namespace or page' . "\n".
    "USAGE:\n" .   "php plugin.php metadisplay " .
"[-h] [--no-colors]  [--loglevel ] \n [[-n --namespace|.] [[-p -page] [-e --exact ]][-c --cmdL]][[-b --before|-a --after]: timestamp[modified|created]] "  
    );
    $options->registerOption('version', 'print version and exit', 'v');
    $options->registerOption('namespace',
	'metadata namespace; the -n option with dot [.]	defaults to the top level. This option cannot be left blank if it is not followed by a page name','n');
    $options->registerOption('page', 'page name without namespace or extension, e.g. start', 'p');
    $options->registerOption('exact', 'set to "on"  for exact <b><u>page</u></b> match', 'e');
$options->registerOption('cmdL', 'set to "on" when accessing from command line in DOKU_INC/bin', 'c');
   $options->registerOption('before',  'before timestamp:[modified|created]', 'b');
    $options->registerOption('after', 'after timestamp:[modified|created]', 'a');
    $options->registerOption('dtype', '"created" or "modified" date for "--before" and "--after"', 'd');	  
    $options->registerOption('search', 'set to search term', 's');
}

// implement your code
protected function main(Options $options)
{       
  
    if ($options->getOpt('namespace')) {    
       $opts = $options->getArgs();
	    //$this->write_debug(print_r($opts,1) );
        $namespace; $page;$exact;$search;$cl;$tm;$dtype;		
        $this->get_commandLineOptions($namespace, $page,$exact,$search,$cl,$tm,$dtype,$opts);  
       // $this->write_debug("namespace $namespace, page $page,\n exact $exact, time $tm, dtype: $dtype");		
           if($cl == 'on') {
               $helper =  plugin_load('helper','metadisplay_plaintext');
           }           
           else {
               $helper =  plugin_load('helper','metadisplay_html'); 
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

                              //  &$namespace, &$page,&$exact,&$search,&$cl,&$tm, $opts
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
	        //$this->write_debug($tm);
		
          break;
        case 'a':  
        case 'after':
            $tm = $this->get_timestamp( $opts[$i+1]) . ':a';
            break;       
        case 'd':  
        case 'dtype':
            $dtype = $opts[$i+1];
		   // $this->write_debug("modified: $dtype");
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



