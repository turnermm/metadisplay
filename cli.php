<?php
if(!defined('DOKU_INC')) die();
use splitbrain\phpcli\Options;

class cli_plugin_metadisplay extends DokuWiki_CLI_Plugin {
private $helper;    
protected function setup(Options $options) {
    
    $options->setHelp('Displays metadata for specified namespace or page' . "\n".
     "[[--no-colors]  [--loglevel ]  -n  namespace [ -p page] -u user]"  
    );
    $options->registerOption('version', 'print version and exit', 'v');
    $options->registerOption('namespace', 'metadata namespace; the -n option with no namespace defaults to the top level.', 'n');
    $options->registerOption('user', 'user login name', 'u');
    $options->registerOption('page', 'page name without namespace or extension, e.g. start', 'p');
}

// implement your code
protected function main(Options $options)
{       
    $helper =  plugin_load('helper','metadisplay_html');   
    if ($options->getOpt('namespace')) {    
       print_r($options->getArgs());
      $opts = $options->getArgs();
      if(!empty($opts[2])  &&  ($opts[1] == '-p' || $opts[1] == '--page') ){
           $page = $opts[2];
      }  
      else $page = "";
     $helper->init(($options->getArgs())[0], $page);
    }
    else if ($options->getOpt('version')) {
        $info = $this->getInfo();    
        $this->success($info['date']);
    } else {
        echo $options->help();
    }
}
}

