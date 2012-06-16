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
if (isset($stopid)) {
    $stop = getStop($stopid);
   
}

/* if ($stopcode != "" && $stop[5] != $stopcode) {
  $url = $APIurl . "/json/stopcodesearch?q=" . $stopcode;
  $stopsearch = json_decode(getPage($url));
  $stopid = $stopsearch[0][0];
  $url = $APIurl . "/json/stop?stop_id=" . $stopid;
  $stop = json_decode(getPage($url));
  }
  if (!startsWith($stop[5], "Wj") && strpos($stop[1], "Platform") === false) {
  // expand out to all platforms

  } */

$stops = Array();
$stopPositions = Array();
$stopNames = Array();
$tripStopNumbers = Array();
$allStopsTrips = Array();
$fetchedTripSequences = Array();
$stopLinks = "";
if (isset($stopids)) {
    foreach ($stopids as $sub_stopid) {
        $stops[] = getStop($sub_stopid);
    }
}
 if ($stop == NULL && (!isset($stops[0]) || $stops[0] == NULL) ) {
    
    header("Status: 404 Not Found");
    header("HTTP/1.0 404 Not Found");
    include_header("Stop Not Found", "404stop");
    echo "<h1>Error: Stop not found</h1>";
include_footer();
    die();
}
if (isset($stopids)) {
    $stop = $stops[0];
    $stopid = $stops[0]["stop_id"];
    $stopLinks.= "Individual stop pages: <br>";
    foreach ($stops as $key => $sub_stop) {

        $stopNames[$key] = $sub_stop["stop_name"];
        $stopLinks.= '<span itemscope itemtype="http://schema.org/BusStop"> 
            <a itemprop="url" href="stop.php?stopid=' . $sub_stop["stop_id"] . 
               '">' . $sub_stop["stop_name"] 
                . '</a><span class="geo" itemprop="geo" itemscope itemtype="http://schema.org/GeoCoordinates"><meta itemprop="latitude" content="'.$sub_stop["stop_lat"].'" />
                 <abbr class="latitude" title="'.$sub_stop["stop_lat"].'"></abbr> 
 <abbr class="longitude" title="'.$sub_stop["stop_lon"].'"></abbr>
    <meta itemprop="longitude" content="'.$sub_stop["stop_lon"].'" /></span></span>';

        $stopPositions[$key] = Array(
            $sub_stop["stop_lat"],
            $sub_stop["stop_lon"]
        );
        $trips = getStopTrips($sub_stop["stop_id"]);
        $tripSequence = "";
        foreach ($trips as $trip) {
            $tripSequence.= "{$trip['trip_id']},";
            $tripStopNumbers[$trip['trip_id']][] = $key;
        }
        if (!in_array($tripSequence, $fetchedTripSequences)) {
            // only fetch new trip sequences
            $fetchedTripSequences[] = $tripSequence;
            $trips = getStopTripsWithTimes($sub_stop["stop_id"]);
            foreach ($trips as $trip) {
                if (!isset($allStopsTrips[$trip["trip_id"]]))
                    $allStopsTrips[$trip["trip_id"]] = $trip;
            }
        }
        //else {
        //	echo "skipped sequence $tripSequence";
        //}
    }
}
if (sizeof($stops) > 0) {
    $stopDescParts = explode("<br>", $stop['stop_desc']);
    include_header(trim(str_replace("Street: ", "", $stopDescParts[0])), "stop");
} else {
    include_header($stop['stop_name'], "stop");
}


echo '<div class="content-secondary"><br>';
echo $stopLinks;
if (sizeof($stops) > 0) {
    trackEvent("View Stops", "View Combined Stops", $stop["stop_name"], $stop["stop_id"]);
    echo staticmap($stopPositions);
} else {
    trackEvent("View Stops", "View Single Stop", $stop["stop_name"], $stop["stop_id"]);
    echo staticmap(Array(
        0 => Array(
            $stop["stop_lat"],
            $stop["stop_lon"]
        )
    ));
}

timeSettings();

echo '</div><div class="content-primary">';
echo '  <ul data-role="listview"  data-inset="true">';
if (sizeof($allStopsTrips) > 0) {
    sktimesort($allStopsTrips, "arrival_time", true);
    $trips = $allStopsTrips;
} else {
    $trips = getStopTripsWithTimes($stopid, "", "", "", (isset($filterIncludeRoutes) || isset($filterHasStop) ? "75" : ""));
    
}

echo "<div class='ui-header' style='overflow: visible; height: 2.5em'>";
// if we have too many trips, cut down to size.
if (!isset($filterIncludeRoutes) && !isset($filterHasStop) && sizeof($trips) > 10) {
    $trips = array_splice($trips, 0, 10);
}

// later/earlier button setup
if (sizeof($trips) == 0) {
    $time = isset($_REQUEST['time']) ? strtotime($_REQUEST['time']) : time();
    $earlierTime = $time - (90 * 60);
    $laterTime = $time + (90 * 60);
} else {
    $tripsKeys = array_keys($trips);
    $earlierTime = strtotime($trips[$tripsKeys[0]]['arrival_time']) - (90 * 60);
    $laterTime = strtotime($trips[$tripsKeys[sizeof($trips) - 1]]['arrival_time']) - 60;
}
if (isset($stopids) && sizeof($stopids) > 0) {
    $stopidurl = "stopids=" . implode(",", $stopids);
} else {
    $stopidurl = "stopid=$stopid";
}
if (sizeof($trips) >= 10) {
    echo '<a href="stop.php?' . $stopidurl . '&amp;service_period=' . service_period() . '&amp;time=' . date("H:i", $laterTime) . '" data-icon="arrow-r" class="ui-btn-right">Later Trips</a>';
}
echo '<a href="stop.php?' . $stopidurl . '&amp;service_period=' . service_period() . '&amp;time=' . date("H:i", $earlierTime) . '" data-icon="arrow-l" class="ui-btn-left">Earlier Trips</a>';
echo "</div>";
if (sizeof($trips) == 0) {
    echo "<li style='text-align: center;'>No trips in the near future.</li>";
} else {
            if (isset($labs)) {
// ETA calculation
                
                $tripETA = Array();
                // max/min delay instead of stddev?
                $query = $query = "select 'lol', avg(timing_delta), stddev(timing_delta), count(*) from myway_timingdeltas where extract(hour from time) between ".date("H", $earlierTime)." and ".date("H", $laterTime);
       //select 'lol', stop_id,extract(hour from time), avg(timing_delta), stddev(timing_delta), count(*) from myway_timingdeltas where stop_id = '5501' group by stop_id, extract(hour from time) order by extract(hour from time)
                $query = $conn->prepare($query);
    $query->execute();
    if (!$query) {
        databaseError($conn->errorInfo());
        return Array();
    }
    $ETAparams = Array();
    foreach ($query->fetchAll() as $row) {
        $ETAparams[$row[0]] = Array("avg"=> $row[1], "stddev"=>floor($row[2]),"count"=>$row[3]);
    };
    //print_r($ETAparams);
    foreach ($trips as $trip) {
        $tripETA[$trip['trip_id']] = date("H:i",strtotime($trip['arrival_time']." - ".(floor($ETAparams['lol']['stddev']))." seconds"))." to ".
        date("H:i",strtotime($trip['arrival_time']." + ".(floor($ETAparams['lol']['stddev']))." seconds"));
    }
    //print_r($tripETA);
}
    foreach ($trips as $trip) {
        if (
                isset($filterHasStop) && (getTripHasStop($trip['trip_id'], $filterHasStop) == 1)
                || (isset($filterIncludeRoutes) && in_array($trip["route_short_name"], $filterIncludeRoutes))
                || (!isset($filterIncludeRoutes) && !isset($filterHasStop))
        ) {
            echo '<li class="vevent">';

            $destination = getTripDestination($trip['trip_id']);
            echo '<a class="url" href="'.curPageURL().'/trip.php?stopid=' . $stopid . '&amp;tripid=' . $trip['trip_id'] . '"><h3 class="summary">' . $trip['route_short_name'] . " towards " . $destination['stop_name'] . "</h3><p>";
            $viaPoints = viaPointNames($trip['trip_id'], $trip['stop_sequence']);
if (isset($labs)) {
                echo '<br><span class="eta">ETA: ' . $tripETA[$trip['trip_id']] . '</span>';
            }
            if ($viaPoints != "")
                echo '<br><span class="viaPoints">Via: ' . $viaPoints . '</span>';
            if (sizeof($tripStopNumbers) > 0) {
                echo '<br><small>Boarding At: ';
                if (sizeof($tripStopNumbers[$trip['trip_id']]) == sizeof($stopids)) {
                    echo "All Stops";
                } else {
                    foreach ($tripStopNumbers[$trip['trip_id']] as $key) {
                        echo $stopNames[$key] . ', ';
                    }
                }
                echo '</small>';
            }
            echo '</p>';
            echo '<p class="ui-li-aside"><span class="dtstart"><span class="value-title" title="'.date("c",strtotime($trip['arrival_time'])).'"></span>' . $trip['arrival_time'] . '</span></p>';
            echo '</a></li>';
            flush();
            @ob_flush();
        }
    }
}
echo '</ul>';
echo '</div>';
include_footer();
?>
