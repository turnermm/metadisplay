<?php
if(!defined('DOKU_INC')) die();
use splitbrain\phpcli\Options;

class cli_plugin_metadisplay extends DokuWiki_CLI_Plugin {
private $helper;    
protected function setup(Options $options) {
    
    $options->setHelp('Displays metadata for specified namespace or page' . "\n".
    "USAGE:\n" .   "php plugin.php metadisplay " .
"[-h] [--no-colors]  [--loglevel ] \n [[-n --namespace|.] [[-p -page] [-e --exact ]][-c --cmdL]]"  
    );
    $options->registerOption('version', 'print version and exit', 'v');
    $options->registerOption('namespace', 'metadata namespace; the -n option with dot [.] defaults to the top level. The dot is required if -n option is followed by a namespace or the -p option', 'n');
    $options->registerOption('page', 'page name without namespace or extension, e.g. start', 'p');
    $options->registerOption('exact', 'set to "on"  for exact <b><u>page</u></b> match', 'e');
$options->registerOption('cmdL', 'set to "on" when accessing from command line in DOKU_INC/bin', 'c');
$options->registerOption('timestamp', 'display metadata before or after this time ', 't');
$options->registerOption('when', '<b>b</b> before, <b>a</b> after the timestamp ', 'w');
  //  $options->registerOption('search', 'set to search tem', 's');

}

// implement your code
protected function main(Options $options)
{       
  
    if ($options->getOpt('namespace')) {    
       $opts = $options->getArgs();
        $namespace=""; $page="";$exact="";$search="";$cl = ""; $tm = ""; $when = "";
        $this->get_commandLineOptions($namespace,$page,$cl ,$exact,$tm = "",$opts);
   
           if($cl == 'on') {
               $helper =  plugin_load('helper','metadisplay_plaintext');
           }           
           else {
               $helper =  plugin_load('helper','metadisplay_html'); 
           }
            $helper->init($namespace, $page,$exact,$search, $tm, $when);
            
    }
    else if ($options->getOpt('version')) {
        $info = $this->getInfo();    
        $this->success($info['date']);
    } else {
        echo $options->help();
    }
}
function get_commandLineOptions(&$namespace, &$page,&$cl,&$exact,$tm, $opts) {
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
        case 't':
        case 'timestamp':
          $tm = $opts[$i+1];
          break;
        case 'w':  
        case 'when':
          $when = $opts[$i+1];
          break;   
        }
      }
}
}  //end class definition



