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
class admin_plugin_metadisplay extends DokuWiki_Admin_Plugin {

    var $output ='';
  
    /**
     * handle user request
     */
    function handle() {    
      if (!isset($_REQUEST['cmd']) || empty($_REQUEST['help'])) return;   // first time - nothing to do
      if (!checkSecurityToken()) return;
      if(!empty($_REQUEST['help'])) {
            $this->output = '<pre>' . shell_exec(' php ' .DOKU_INC . '/bin/plugin.php metadisplay -h') .'</pre>';
            return;
      }
           //   msg('<pre>' . print_r($_REQUEST,1). '</pre>');          
      switch (key($_REQUEST['cmd'])) {
       //    case 'page' : $this->output = 'goodbye'; break;
          // case 'namespace' : $this->output = 'goodbye'; break;
      }     
    } 

    function html() {     

      ptln('<form action="'.wl($ID).'" method="post">');  
      
      // output hidden values to ensure dokuwiki will return back to this plugin
          ptln('  <input type="hidden" name="do"   value="admin" />');
          ptln('  <input type="hidden" name="page" value="'.$this->getPluginName().'" />');
          formSecurityToken();
          
          ptln('<div><input type="text" name="cmd[namespace]" placeholder="namespace" />');
          ptln('<input type="text" name="cmd[page]" placeholder="page name" /></div>');
          ptln('<div  style="line-height:2"><input type="text" name="user" placeholder="user" />');
          ptln('<input type="password" name="pwd" placeholder="password"/></div>');
          ptln('<div style="line-height:2">');
          ptln('<input type="submit" name="submit"/>&nbsp;&nbsp;<input type="submit" value="help" name="help" /></div>');  
          
      ptln('</form>');
      
      ptln('<div><br />'.$this->output.'</div>');    
    }
 
}