<?php
if(!defined('DOKU_INC')) die();
use splitbrain\phpcli\Options;

class cli_plugin_metadisplay extends DokuWiki_CLI_Plugin {
private $helper;    
protected function setup(Options $options) {
    
    $options->setHelp('A very minimal metadisplay that does nothing but print the plugin version info');
    $options->registerOption('version', 'print version', 'v');
}

// implement your code
protected function main(Options $options)
{       
    $helper =  plugin_load('helper','metadisplay_html');   
    $helper->init();
    if ($options->getOpt('version')) {
        $info = $this->getInfo(); // method available in all DokuWiki plugins      
      // $info['date'] = "2021-10-28";        
        $this->success($info['date']);
    } else {
        echo $options->help();
    }
}
}

