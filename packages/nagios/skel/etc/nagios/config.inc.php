<?php
// Diese Datei muss pro Instanz existieren!


$cfg['cgi_config_file']='@ROOT@/etc/nagios/cgi.cfg';  // location of the CGI config file

$cfg['cgi_base_url']='/muc/nagios/cgi-bin';


// FILE LOCATION DEFAULTS
$cfg['main_config_file']='@ROOT@/etc/nagios/nagios.cfg';  // default location of the main Nagios config file
$cfg['status_file']='@ROOT@/tmp/nagios/status.dat'; // default location of Nagios status file
$cfg['state_retention_file']='@ROOT@/spool/nagios/retention.dat'; // default location of Nagios retention file



// utilities
require_once('@ROOT@/share/nagios/htdocs/includes/utils.inc.php');

?>
