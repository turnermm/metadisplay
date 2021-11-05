<?php
/**
 * Plugin Skeleton: Displays "Hello World!"
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

 
/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_metadisplay extends DokuWiki_Admin_Plugin {

    var $output = 'world';
  
    /**
     * handle user request
     */
    function handle() {
    
      if (!isset($_REQUEST['cmd'])) return;   // first time - nothing to do

      $this->output = '';
      if (!checkSecurityToken()) return;
      if (!is_array($_REQUEST['cmd'])) return;
       msg('<pre>' . print_r($_REQUEST,1). '</pre>');
      // verify valid values
      
      switch (key($_REQUEST['cmd'])) {
           case 'page' : $this->output = 'goodbye'; break;
           case 'namespace' : $this->output = 'goodbye'; break;
      }         
    } 
    /**
     * output appropriate html
     */
    function html() {
      ptln('<p>'.htmlspecialchars($this->getLang($this->output)).'</p>');      

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
     // ptln($form_text);
      ptln('</form>');
    }
 
}