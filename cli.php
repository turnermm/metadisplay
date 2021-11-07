<?php
if(!defined('DOKU_INC')) die();
use splitbrain\phpcli\Options;

class cli_plugin_metadisplay extends DokuWiki_CLI_Plugin {
private $helper;    
protected function setup(Options $options) {
    
    $options->setHelp('Displays metadata for specified namespace or page' . "\n".
    "USAGE:\n" .   "php plugin.php metadisplay " .
    "[-h] [--no-colors]  [--loglevel ] \n [[-n namespace|.] [[-p -page] [-e on|off]]]"  
    );
    $options->registerOption('version', 'print version and exit', 'v');
    $options->registerOption('namespace', 'metadata namespace; the -n option with dot [.] defaults to the top level. The dot is required if -n option is followed by a namespace or the -p option', 'n');
    $options->registerOption('page', 'page name without namespace or extension, e.g. start', 'p');
    $options->registerOption('exact', 'set to "on"  for exact <b><u>page</u></b> match', 'e');
  //  $options->registerOption('search', 'set to search tem', 's');

}

// implement your code
protected function main(Options $options)
{       
        $helper =  plugin_load('helper','metadisplay_plaintext'); 
    if ($options->getOpt('namespace')) {    
       $opts = $options->getArgs();
       $namespace=""; $page="";$exact="";$search="";
       $this->get_commandLineOptions($namespace,$page,$exact,$search,$opts);
        // echo "$namespace, $page,$exact,$search\n";
         $helper->init($namespace, $page,$exact,$search);
    }
    else if ($options->getOpt('version')) {
        $info = $this->getInfo();    
        $this->success($info['date']);
    } else {
        echo $options->help();
    }
}
function get_commandLineOptions(&$namespace, &$page,&$exact,&$search,$opts) {
    if(!is_countable($opts)) return;
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
        }
      }
}

}

