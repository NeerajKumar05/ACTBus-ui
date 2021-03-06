<?php
/*
 *    Copyright 2010,2011 Alexander Sadleir 

  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
 */
include ('include/common.inc.php');
cache_modtime();
include_header("About", "about")
?>
<p>
    Busness Time - An ACT bus timetable webapp<br />
    Based on the maxious-canberra-transit-feed (<a 
        href="http://busresources.lambdacomplex.org/cbrfeed.zip">download</a>, 
    last updated <?php echo date("F d Y.", @filemtime('../busresources/cbrfeed.zip')); ?>)<br />
    Source code for the <a 
        href="https://github.com/maxious/ACTBus-data">transit 
        feed</a> and <a href="https://github.com/maxious/ACTBus-ui">this 
        site</a> available from github.<br />
    Uses jQuery Mobile, PHP, PostgreSQL, OpenTripPlanner, OpenLayers, OpenStreetMap, Cloudmade Geocoder and Tile Service<br />
    Suburb geocoding based on <A href="http://www.abs.gov.au/AUSSTATS/abs@.nsf/Lookup/1270.0.55.003Main+Features1July%202011?OpenDocument">Australian Bureau of Statistics data.</a><br />
    Street geocoding based on work by OpenStreetMap contributors<br>
    <br />
    Feedback encouraged; contact maxious@lambdacomplex.org<br />
    <br />
    Some icons by Joseph Wain / glyphish.com<br />
    Native clients also available for iPhone (<a href="http://itunes.apple.com/au/app/cbrtimetable/id444287349?mt=8">cbrTimetable by Sandor Kolotenko</a>
    , <a href="http://itunes.apple.com/au/app/act-buses/id376634797?mt=8">ACT Buses by David Sullivan</a>, <a href="http://itunes.apple.com/app/bus-trips-act/id489146525?mt=8">Bus Trips ACT by Molson Chengalath</a>)
    , Android (<a href="https://market.android.com/details?id=com.action">MyBus 2.0 by Imagine Team</a>, <A href="https://market.android.com/details?id=GetMe2CanberraFree.source">GetMe2 Canberra by
Colin Thompson </a>, <a href="https://market.android.com/details?id=au.com.transittimes.android">TransitTimes+ by Zervaas Enterprises</a>) 
    and Windows Phone 7 (<a href="http://www.windowsphone.com/en-AU/apps/d840375e-429c-4aa4-a358-80eec6ea9e66">TransHub Canberra by Soul Solutions</a>).
    <br />
    <!--GTFS-realtime API:<br />
    Alerts and Trip Updates (but only Cancelled or Stop Skipped)<br />
    Default format binary Protocol Buffer but can get JSON by adding ?ascii=yes<br />-->
    <br />
    <br />
    <small>Disclaimer: The content of this website is of a general and informative nature. Please check with printed timetables or those available on http://www.action.act.gov.au before your trip.
        Whilst every effort has been made to ensure the high quality and accuracy of the Site, the Author makes no warranty, 
        express or implied concerning the topicality, correctness, completeness or quality of the information, which is provided 
        "as is". The Author expressly disclaims all warranties, including but not limited to warranties of fitness for a particular purpose and warranties of merchantability. 
        All offers are not binding and without obligation. The Author expressly reserves the right, in his discretion, to suspend, 
        change, modify, add or remove portions of the Site and to restrict or terminate the use and accessibility of the Site 
        without prior notice. </small>
    <?php
    include_footer();
    ?>
