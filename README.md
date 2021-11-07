# metadisplay

USAGE:
 On Command Line from bin/ directory:
           php plugin.php metadisplay <OPTIONS>

   Displays metadata for specified namespace or page
   Options (in following order):
   [[--no-colors]  [--loglevel ]  -n  [--namespace] [ -p --page|.] [-e <off|on>]


OPTIONS:
   -v, --version                     print version and exit

   -n, --namespace                   metadata namespace; the -n option with no namespace or  dot [.] defaults
                                     to the top level. The dot is required if -n option is followed by a second option, e.g -p

   -p, --page                        page name without namespace or extension, e.g. start

   -e, --exact                       exact page match, set to on for exact match, off for normal match

   -h, --help                        Display this help screen and exit immeadiately.

   --no-colors                       Do not use any colors in output. Useful when piping output to other tools
                                     or files.

   --loglevel <level>                Minimum level of messages to display. Default is info. Valid levels are:
                                     debug, info, notice, success, warning, error, critical, alert, emergency.

The plugin simplifies this from and admim.php panel accessed from the plugin's administration page



