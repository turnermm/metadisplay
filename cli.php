<?php
if(!defined('DOKU_INC')) die();
use splitbrain\phpcli\Options;

class cli_plugin_metadisplay extends DokuWiki_CLI_Plugin {
private $helper;    
protected function setup(Options $options) {
    
    $options->setHelp('Displays metadata for specified namespace ' .
     "\n [--no-colors]  [--loglevel ] [--version]  -n  <:namespace> " .
     "\nIf no namespace is given, defaults to top level."
    );
    $options->registerOption('version', 'print version', 'v');
    $options->registerOption('namespace', 'metadata namespace', 'n');
}

// implement your code
protected function main(Options $options)
{       
    $helper =  plugin_load('helper','metadisplay_html');   
    if ($options->getOpt('namespace')) {    
        $helper->init(($options->getArgs())[0]);
    }
    if ($options->getOpt('version')) {
        $info = $this->getInfo(); // method available in all DokuWiki plugins      
      // $info['date'] = "2021-10-28";        
        $this->success($info['date']);
    } else {
        echo $options->help();
    }
}
}

