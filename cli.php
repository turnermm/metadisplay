<?php
if(!defined('DOKU_INC')) die();
use splitbrain\phpcli\Options;

//include_once(DOKU_PLUGIN . "example/createMetaArray.php");
class cli_plugin_example extends DokuWiki_CLI_Plugin {
private $helper;    
protected function setup(Options $options) {
     $this->helper =  plugin_load('helper','example');  
    $options->setHelp('A very minimal example that does nothing but print the plugin version info');
    $options->registerOption('version', 'print version', 'v');
}

// implement your code
protected function main(Options $options)
{       

    $this->helper->init();
    if ($options->getOpt('version')) {
        $info = $this->getInfo(); // method available in all DokuWiki plugins      
      // $info['date'] = "2021-10-28";        
        $this->success($info['date']);
    } else {
        echo $options->help();
    }
}
}

