# metadisplay

USAGE:
   metadisplay 

   Displays metadata for specified namespace or page                      
   USAGE (from Command Line):                                             
   php plugin.php metadisplay [-h] [--no-colors]  [--loglevel ]   [[-n --namespace|.] [[-p -page] [-e --exact ]][-c --cmdL]][[-b        
   --before|-a --after] timestamp -d -dtype [modified|created]] [[-   --search|-f --fuzzy] [search-term]] -c --cmdL.                         
   
timestamp can be timestamp or numerical date of the form: 
   
Year-Month-Day                                            
                                                                          

OPTIONS:
   -v, --version         print version and exit                           

   -n, --namespace       metadata namespace; the -n option with dot       
                         [.]	defaults to the top level. This option cannot
                         be left blank if it is not followed by a page    
                         name                                             

   -p, --page            page name without namespace or extension, e.g.   
                         start                                            

   -e, --exact           set to "on"  for exact page match  

   -c, --cmdL            set automatically to "html" when accessing from  
                         admin.php                                        

   -b, --before          before timestamp                                 

   -a, --after           after timestamp                                  

   -d, --dtype           sets whether file's timestamp is read from       
                         "created" or "modified" field                    

   -s, --search          set to search term, exact match                  

   -f, --fuzzy           set to search term, fuzzy match                  

   -h, --help            Display this help screen and exit immediately.   

   --no-colors           Do not use any colors in output. Useful when     
                         piping output to other tools or files.           

   --loglevel     Minimum level of messages to display. Default is 
                         info. Valid levels are: debug, info, notice,     
                         success, warning, error, critical, alert,        
                         emergency.                                       
