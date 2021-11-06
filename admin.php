<?php
/**
 * Plugin metadisplY"
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca
 */

 
/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
define ("METADISP_CMDL",  'php ' .DOKU_INC . '/bin/plugin.php metadisplay ');
class admin_plugin_metadisplay extends DokuWiki_Admin_Plugin {

    var $output ='';
  
    /**
     * handle user request
     */
    function handle() {    

      
      if (!isset($_REQUEST['cmd']) && empty($_REQUEST['help'])) return;   // first time - nothing to do
 
      $this->output = '';
      if (!checkSecurityToken()) return;
      if(!empty($_REQUEST['help'])) {            
          //  $this->output = '<pre>' . shell_exec(' php ' .DOKU_INC . '/bin/plugin.php metadisplay -h') .'</pre>';
          $this->output = '<pre>' . shell_exec(METADISP_CMDL  .'-h') .'</pre>';
            return;
      }

   $commands =  $_REQUEST['cmd'];
  // msg(print_r($commands,1));
    $start_dir = $commands['namespace'] ? $commands['namespace'] : '.';  
    $cmdline = METADISP_CMDL  .'-n ' . $start_dir;    
    if(!empty($commands['page'])) {
         $cmdline .= " -p " . $commands['page'];
         if(!empty($commands['exact'])) {
            $cmdline .= " -e " . $commands['exact'];
        }  
        else  $cmdline .= " -e " . 'off';     
    }
        
    /*user, pwd not currently in use */    
    if(!empty($commands['user'])) {
        $cmdline .= " -u " . $commands['user'];
    }     
     if(!empty($commands['pwd'])) {
        $cmdline .= " -l " . $commands['pwd'];
    }
    
    $this->output =shell_exec($cmdline);

    } 

    function html() {     

      ptln('<form action="'.wl($ID).'" method="post">');  
      
      // output hidden values to ensure dokuwiki will return back to this plugin
          ptln('  <input type="hidden" name="do"   value="admin" />');
          ptln('<input type="hidden" name="page" value="'.$this->getPluginName().'" />');
          formSecurityToken();
     
          ptln('<div>Namespace: <input type="text" name="cmd[namespace]" placeholder="namespace:n2:n3. . ." />');
          ptln('&nbsp; Page: <input type="text" name="cmd[page]" placeholder="page without extension" />');
          ptln('&nbsp; Exact match on page name:&nbsp <input type = "checkbox" name="cmd[exact]" />');
          ptln('</div>');
          
      /* // Not currently implemented
         ptln('<div  style="line-height:2"><input type="text" name="cmd[user]" placeholder="user" />');
         ptln('<input type="password" name="cmd[pwd]" placeholder="password"/></div>');
      */   
          ptln('<div style="line-height:2">');
          ptln('<input type="submit" name="submit"/>&nbsp;&nbsp;<input type="submit" value="help" name="help" /></div>');  
          
      ptln('</form>');
      
      ptln('<div><br />'.$this->output.'</div>');    
    }
 
}