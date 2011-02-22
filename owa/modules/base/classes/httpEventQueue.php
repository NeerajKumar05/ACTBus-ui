<?php

//
// Open Web Analytics - An Open Source Web Analytics Framework
//
// Copyright 2006 Peter Adams. All rights reserved.
//
// Licensed under GPL v2.0 http://www.gnu.org/copyleft/gpl.html
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//
// $Id$
//

require_once(OWA_BASE_CLASS_DIR.'eventQueue.php');

/**
 * http Event Queue
 * 
 * @author      Peter Adams <peter@openwebanalytics.com>
 * @copyright   Copyright &copy; 2006 Peter Adams <peter@openwebanalytics.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GPL v2.0
 * @category    owa
 * @package     owa
 * @version		$Revision$	      
 * @since		owa 1.0.0
 */

class owa_httpEventQueue extends owa_eventQueue {
	
	var $endpoint = '';
	
	function __construct($options = '') {
		// set the endpoint. move this to constructor
		if (array_key_exists('endpoint', $options)) {
			$this->endpoint = $options['endpoint'];
		} else {
			$this->endpoint = owa_coreAPI::getSetting('base', 'remote_event_queue_endpoint');
		}
	}
	
	function addToQueue($event) {
		
		if ($event) {
			$properties['owa_event'] = base64_encode(serialize($event));
			
			//$properties = array_map('urlencode', $properties);
			$properties = owa_lib::implode_assoc('=', '&', $properties);
			//print_r($properties);
			//return;
		} else {
			return;
		}
		
		$parts = parse_url($this->endpoint);
	 	
	  	$fp = fsockopen($parts['host'], isset($parts['port'])?$parts['port']:80, $errno, $errstr, 30);
	 	
	  	if (!$fp) {
	    	return false;
	  	} else {
	      	$out = "POST ".$parts['path']." HTTP/1.1\r\n";
	      	$out.= "Host: ".$parts['host']."\r\n";
	      	$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	      	$out.= "Content-Length: ".strlen($properties)."\r\n";
	      	$out.= "Connection: Close\r\n\r\n";
	    	$out.= $properties;
	 		owa_coreAPI::debug("out: $out");
	 		
	      	fwrite($fp, $out);
	      	fclose($fp);
	      	return true;
	  	}
	
	}
	
	function processQueue() {
	
	}

}

?>