<?php
include ('../include/common.inc.php');

include_header("Service Alerts", "index");
echo '<ul data-role="listview" data-theme="e" data-groupingtheme="e">';
	
 if ($_SESSION['authed'] == true) {
 	echo'	<li><a href="servicealert_editor.php"><h3>servicealert_editor</h3>
		<p>servicealert_editor</p></a></li>';
          }
 	echo'	<li><a href="servicealert_viewer.php"><h3>Service Alert Viewer</h3>
		<p>Browse current network alerts</p></a></li>';
          
           echo'  </ul>';

?>	    </div>
<?php
include_footer()
?>
        
