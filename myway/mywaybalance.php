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
include ('../include/common.inc.php');
if (!isset($_REQUEST['card_number'])) {
    cache_modtime();
}
include_header("MyWay Balance", "mywayBalance", false, false, true);
echo '<div data-role="page"> 
	<div data-role="header" data-position="inline">
	<a href="' . $_SERVER["HTTP_REFERER"] . '" data-icon="arrow-l" data-rel="back" class="ui-btn-left">Back</a> 
		<h1>MyWay Balance</h1>
		<a href="mywaybalance.php?logout=yes" data-icon="delete" class="ui-btn-right">Logout</a>
	</div><!-- /header -->
        <a name="maincontent" id="maincontent"></a>
        <div data-role="content"> ';
$return = Array();

function logout() {
    setcookie("card_number", "", time() - 3600, "/");
    setcookie("date", "", time() - 3600, "/");
    setcookie("secret_answer", "", time() - 3600, "/");
    setcookie("contribute_myway", "", time() - 3600, "/");
}

function printBalance($mywayResult) {
    if (isset($mywayResult['error'])) {
        logout();
        echo '<h3><font color="red">' . $mywayResult['error'][0] . "</font></h3>";
    } else {
        echo "<h2>Balance: " . $mywayResult['myway_carddetails']['Card Balance'] . "</h2>";
        echo '<ul data-role="listview" data-inset="true"><li data-role="list-divider"> Recent Transactions </li>';
        $txCount = 0;
        foreach ($mywayResult['myway_transactions'] as $transaction) {
            echo "<li>";
            if ($transaction["Deduction Type"] == "DEFAULT")
                echo '<img src="css/images/warning.png" alt="Failed to tap off: " class="ui-li-icon">';
            echo "<b>" . $transaction["Date / Time"] . "</b>";
            echo "<br><small>" . $transaction["Route"] . " at " . $transaction["Stop Name"] . "<br>";
            echo $transaction["TX Reference No"] . " " . $transaction["TX Type"] . "</small>";
            echo '<p class="ui-li-aside">' . $transaction["TX Amount"] . '</p>';
            echo "</li>";
            $txCount++;
            if ($txCount > 10)
                break;
        }
        echo "</ul>";
    }
}

function recordMyWayObservations($mywayResult) {
    global $conn;
    if (!isset($mywayResult['error'])) {
        $stmt = $conn->prepare("insert into myway_observations (observation_id, myway_stop, time, myway_route,tag_on)
				      values (:observation_id, :myway_stop, :time, :myway_route, :tag_on)");
        $stmt->bindParam(':observation_id', $observation_hash);
        $stmt->bindParam(':myway_stop', $myway_stop);
        $stmt->bindParam(':time', $timestamp);
        $stmt->bindParam(':myway_route', $myway_route);
        $stmt->bindParam(':tag_on', $tag_on);
        // insert a record
        $resultCount = 0;
        foreach ($mywayResult['myway_transactions'] as $transaction) {
            if ($transaction["Stop Name"] != "" && $transaction["Deduction Type"] != "DEFAULT") {
                $observation_hash = md5($mywayResult['myway_carddetails']['MyWay Number'] . $transaction["TX Reference No"]);
                $timestamp = date("c", strtotime($transaction["Date / Time"]));
                $myway_stop = $transaction["Stop Name"];
                $myway_route = $transaction["Route"];
                $tag_on = (strpos($transaction["TX Type"], "TAP ON") !== false ? true : false);
                if ($stmt->execute()) {
                    $resultCount++;
                }
            }
        }
        echo "<h3>Thanks for participating in the study! $resultCount transactions were recorded</h3>";
    }
}

if (isset($_REQUEST['card_number']) && isset($_REQUEST['date']) && isset($_REQUEST['secret_answer'])) {
    $cardNumber = $_REQUEST['card_number'];
    $date = explode("/", $_REQUEST['date']);
    $pwrd = $_REQUEST['secret_answer'];
    if ($_REQUEST['remember'] == "on") {
        setcookie("card_number", $cardNumber, time() + 60 * 60 * 24 * 100, "/");
        setcookie("date", $_REQUEST['date'], time() + 60 * 60 * 24 * 100, "/");
        setcookie("contribute_myway", $_REQUEST['contribute_myway'], time() + 60 * 60 * 24 * 100, "/");
        setcookie("secret_answer", $pwrd, time() + 60 * 60 * 24 * 100, "/");
    }
    $mywayResult = json_decode(getPage(curPageURL() . "/myway_api.json.php?card_number=$cardNumber&DOBday={$date[0]}&DOBmonth={$date[1]}&DOByear={$date[2]}&secret_answer=$pwrd"), true);
    if ($_REQUEST['contribute_myway'] == "on")
        recordMyWayObservations($mywayResult);
    printBalance($mywayResult);
}
else if (isset($_REQUEST['logout'])) {
    logout();
    echo '<center><h3> Logged out of MyWay balance </h3><a href="/index.php">Back to main menu...</a><center>';
} else if (isset($_COOKIE['card_number']) && isset($_COOKIE['date']) && isset($_COOKIE['secret_answer'])) {
    $cardNumber = $_COOKIE['card_number'];
    $date = explode("/", $_COOKIE['date']);
    $pwrd = $_COOKIE['secret_answer'];
    $mywayResult = json_decode(getPage(curPageURL() . "/myway_api.json.php?card_number=$cardNumber&DOBday={$date[0]}&DOBmonth={$date[1]}&DOByear={$date[2]}&secret_answer=$pwrd"), true);
    if ($_COOKIE['contribute_myway'] == "on")
        recordMyWayObservations($mywayResult);
    printBalance($mywayResult);
}
else {
    $date = (isset($_REQUEST['date']) ? filter_var($_REQUEST['date'], FILTER_SANITIZE_STRING) : date("m/d/Y"));
    echo '<form action="" method="post">
    <div data-role="fieldcontain">
        <label for="card_number">Card number</label>
        <input type="text" name="card_number" id="card_number" value="' . $card_number . '"  />
    </div>
    <div data-role="fieldcontain">
        <label for="date"> Date of birth </label>
        <input type="text" name="date" id="date" value="' . $date . '"  />
    </div>
        <div data-role="fieldcontain">
        <label for="secret_answer"> Secret question answer </label>
        <input type="text" name="secret_answer" id="secret_answer" value="' . $secret_answer . '"  />
    </div>
        <div data-role="fieldcontain">
        <label for="remember"> Remember these details? </label>
        <input type="checkbox" name="remember" id="remember"  checked="yes"  />
    </div>
    <div data-role="fieldcontain">
        <label for="contribute_myway">Contribute MyWay records to timeliness study? </label>
        <input type="checkbox" name="contribute_myway" id="contribute_myway" defaultChecked="no"  />
    </div>
    <div data-role="fieldcontain">
        <label for="accept_warning">I accept that Transport for Canberra <a href="http://transport.act.gov.au/myway/protect.html">advise against the use of third party MyWay applications</a> </label>
        <input type="checkbox" name="accept_warning" id="accept_warning" defaultChecked="no"  />
    </div>
        <input type="submit" value="Go!"></form>';
}
include_footer();
?>
