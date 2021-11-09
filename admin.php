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
          $this->output = '<pre>' . shell_exec(METADISP_CMDL  .'-h') .'</pre>';
            return;
      }

   $commands =  $_REQUEST['cmd'];
 //  msg('<pre>'.print_r($_REQUEST,1).'</pre>');
    $start_dir = $commands['namespace'] ? $commands['namespace'] : '.';  
    $cmdline = METADISP_CMDL  .'-n ' . $start_dir;    
    if(!empty($commands['page'])) {
         $cmdline .= " -p " . $commands['page'];
         if(!empty($commands['exact'])) {
            $cmdline .= " -e " . $commands['exact'];
        }  
        else  $cmdline .= " -e " . 'off';     
    }
     if(!empty($commands['pcreated']) || !empty($commands['pmodified']) ) {
     //($hour=12, $min=60, $second=60,$month=1,$day=1,$year=1950) 
         $timestamp = $this->get_timestamp($hour, $min, $second,$month,$day,$year);
         $cmdline .= " -t   $timestamp";  
    }
    /*user, pwd not currently in use */    
    if(!empty($commands['user'])) {
        $cmdline .= " -u " . $commands['user'];
    }     
     if(!empty($commands['pwd'])) {
        $cmdline .= " -l " . $commands['pwd'];
    }
    //msg($cmdline);
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
          ptln('&nbsp; ' .$this->getLang('exact').':&nbsp <input type = "checkbox" name="cmd[exact]" />');
          ptln('<br />');
          ptln($this->getLang('date') . ':&nbsp;&nbsp;'); 
          ptln('<input type="text" size = "6" name="cmd[year]" placeholder="Year" />');
          ptln('<input type="text" size = "12" name="cmd[month]" placeholder="Month (1-12)" />');
          ptln('<input type="text" size = "12" name="cmd[day]" placeholder="Day (1-31)" />');
          ptln('<br />' . $this->getLang('when') );/*Only display files created*/
          ptln( '<input type="checkbox" name="cmd[pcreated]">');
          ptln($this->getLang('andor') . ' <input type="checkbox" name="cmd[pmodified]"');
          ptln ('<ol><li> <input type="radio" id="earlier" name="when" value="earlier"><label for="earlier"> ' .$this->getLang('earlier').'</label></li>');
          ptln('<li> <input type="radio" id="later" name="when" value="later"><label for="later"> ' .$this->getLang('later').'</label></li></ol>');
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
 
 function get_timestamp($hour=12, $min=60, $second=60,$month=1,$day=1,$year=1950) {
    return  mktime($hour, $min, $second,$month,$day,$year);
}
}