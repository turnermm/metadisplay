<?php
/**
 * Plugin metadisplay"
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca
 */

 
/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
define ("METADISP_CMDL",  'php ' .DOKU_INC . 'bin/plugin.php metadisplay ');
class admin_plugin_metadisplay extends DokuWiki_Admin_Plugin {

    private $output ='';
	private $month = "";
    private $year = ""; 
    private $day = "";
    private $page ="";
    private $startdir = "";
    private $CommandLine = "";
    private $search = "";
    private $stchecked_exact = 'checked';
    private $stchecked_fuzzy = "";
    private $dtype="";
    private $ltype="";
    private $media_checked = "";
    private $links_checked = "";
    private $descr_checked = "";
     
  
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
   //msg('<pre>'.print_r($_REQUEST,1).'</pre>');
    $start_dir = $commands['namespace'] ? $commands['namespace'] : '.';  
    $cmdline = METADISP_CMDL  .'-n ' . $start_dir;    
    if(!empty($commands['page'])) {
         $cmdline .= " -p " . $commands['page'];
         if(!empty($commands['exact'])) {
            $cmdline .= " -e " . $commands['exact'];
        }  
        else  $cmdline .= " -e " . 'off';     
    }
    if(!empty($commands['search'])) {
        $this->search = $commands['search'];   
        $this->stchecked_exact = "";
        $this->stchecked_fuzzy = "";        
        if($commands['srch_type'] == 'fuzzy') {       
            $cmdline .= " -f " .  $this->search;
            $this->stchecked_fuzzy = 'checked';
        }
        else {
            $cmdline .= " -s " .  $this->search;
            $this->stchecked_exact = 'checked';
        }      
        
    $ltype = $_REQUEST['ltype']; 
    if($ltype) {     
       if($ltype == 'links') {
           $this->links_checked = 'checked';
       }  
       else if($ltype == 'media') { 
           $this->media_checked = 'checked';
       }
       else if($ltype == 'descr') {
           $this->descr_checked = 'checked';
       }  
               
       msg($ltype,1);
       $cmdline .= " -l $ltype ";     
    }
    }
     if(!empty($commands['pcreated']) || !empty($commands['pmodified']) ) {
		 $date_not_set = "";
		 if(empty($commands['year'] )) {
			 $commands['year'] = date('Y');
			 $date_not_set =  $commands['year'] ;
		 }
		 if(empty($commands['month'] )) {
			 $date_not_set .= '1';
			 $commands['month'] = '1';
		 }
		 if(empty($commands['day'] )) {
			 $date_not_set .= '1';
			 $commands['day'] = '1';
		 }
		  if($date_not_set) {
			  $date_not_set =   $commands['year'] . '-' . $commands['month'] . '-' .  $commands['day'] = '1';
			  msg("Using defaults for missing date fields: $date_not_set" ,1);
		  }
	     $timestamp = $commands['year'] .'-'. $commands['month'] .'-'. $commands['day'];
         $w = (isset($_REQUEST['when']) && $_REQUEST['when'] == 'earlier') ? ' -b  ': ' -a ';       
          $cmdline .= $w . "$timestamp";         
		  $dtm = $commands['pcreated']?'created':'modified';
		  $cmdline .= " --dtype $dtm"; 
         $this->dtype = $dtm;
    }
    $cmdline .= ' -c html';
 
   // msg('<pre>'.print_r($commands,1).'</pre>');
    $this->year = $commands['year'];
    $this->month = $commands['month'];
    $this->day = $commands['day'];
    $this->start_dir = $start_dir;
    $this->page = (!empty($commands['page'])) ? $commands['page'] : "";   
    if(!$commands['testcl']) {
        $this->output =shell_exec($cmdline);
    } else {
       $this->CommandLine = preg_replace('#^'. METADISP_CMDL .'(.*?)-c html#','php plugin.php metadisplay '."$1",$cmdline);
    }
    

    } 

    function html() {     

      ptln('<form action="'.wl($ID).'" method="post">');  
      
      // output hidden values to ensure dokuwiki will return back to this plugin
          ptln('  <input type="hidden" name="do"   value="admin" />');
          ptln('<input type="hidden" name="page" value="'.$this->getPluginName().'" />');
          formSecurityToken();
     
          ptln('<div>Namespace: <input type="text" name="cmd[namespace]" placeholder="namespace:n2:n3. . ." value = "' . $this->start_dir .'" />');
          ptln('&nbsp; Page: <input type="text" name="cmd[page]" placeholder="page without extension" value = "' . $this->page .'" />');
          ptln('&nbsp; ' .$this->getLang('exact').':&nbsp <input type = "checkbox" name="cmd[exact]" />');
          ptln('<br />');
          ptln($this->getLang('date') . ':&nbsp;&nbsp;'); 
          ptln('<input type="text" size = "6" name="cmd[year]" placeholder="Year"  value = "'. $this->year  .'"/>');
          ptln('<input type="text" size = "12" name="cmd[month]" placeholder="Month (1-12)"  value = "' . $this->month .'"/>');
          ptln('<input type="text" size = "12" name="cmd[day]" placeholder="Day (1-31)" value = "'.$this->day .'" />');
          ptln('<br />' . $this->getLang('when') );
          
          $dtype_c = "";
          $dtype_m = "";
          if($this->dtype == 'modified') {
              $dtype_m = 'checked';           ;
          }
          if($this->dtype == 'created') {
              $dtype_c = 'checked';            
          }
          
          ptln( '<input type="checkbox" ' . $dtype_c . ' id = "pcreated" name="cmd[pcreated]">');
          ptln($this->getLang('andor') . ' <input type="checkbox" '.$dtype_m .' id="pmodified" name="cmd[pmodified]"');
          ptln ('<ul><li> <input type="radio" id="earlier" name="when" value="earlier"><label for="earlier"> ' .$this->getLang('earlier').'</label></li>');
          ptln('<li> <input type="radio" id="later" name="when" value="later"><label for="later"> ' .$this->getLang('later').'</label></li></ul>');
          
           /* Search */
          ptln("<br />Only description field is currently active <br />");
          ptln($this->getLang("search") . ':&nbsp; <input type = "text" size = "20" name = "cmd[search]" value="'.$this->search .'" placeholder = "Search term" />');
          $_fchecked = $this->stchecked_fuzzy; $_echecked = $this->stchecked_exact;
          ptln ('&nbsp;&nbsp;&nbsp;<input type="radio" id="exact_match" name="cmd[srch_type]" value="exact" ' ." $_echecked " .'/><label for="exact_match"> '.$this->getLang('exact_match').'</label>');
          ptln('&nbsp;<input type="radio" id="fuzzy_match" name="cmd[srch_type]" value="fuzzy" ' . " $_fchecked " . '><label for="fuzzy_match"> ' .$this->getLang('fuzzy_match').'</label>'); 
          
          ptln('&nbsp;&nbsp;&nbsp;<input type="radio" id="link" name="ltype" value="links" '. $this->links_checked .'><label for="link"> ' .$this->getLang('links').'</label>');
          ptln(' <input type="radio" id="media" name="ltype" value="media" ' . $this->media_checked .'><label for="media"> ' .$this->getLang('media').'</label>');
          ptln(' <input type="radio" id="descr" name="ltype" value="descr" '. $this->descr_checked .'><label for="descr"> ' .$this->getLang('descr').'</label>');     
  
          ptln('<div><input type="checkbox" id = "testcl" name="cmd[testcl]"> Test Command line: '. $this->CommandLine .'</div>'); 
          ptln('</div>');          
 
          ptln('<div style="line-height:2">');
          ptln('<input type="submit" name="submit"/>&nbsp;&nbsp;<input type="submit" value="help" name="help" /></div>');  
          
      ptln('</form>');
      ptln('<div><br />'.$this->output.'</div>');    
    }
 
}