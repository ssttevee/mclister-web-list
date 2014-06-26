<?php
$GLOBALS['dbdir'] = 'data-collection/test';
$GLOBALS['dbfile'] = $GLOBALS['dbdir'].'/max.rrd';
$GLOBALS['dbavg'] = $GLOBALS['dbdir'].'/avg.rrd';


$options = array(
 "--step", "300",            // Use a step-size of 5 minutes
 "--start", "-1 day",     // this rrd started 6 months ago
 "DS:votes:COUNTER:600:0:U",
 "DS:infoviews:COUNTER:600:0:U",
 "DS:promoviews:COUNTER:600:0:U",
 "RRA:MAX:0.5:1:288",
 "RRA:MAX:0.5:12:168",
 );

$ret = rrd_create($GLOBALS['dbfile'], $options);
if (! $ret) {
 echo "<b>Creation error: </b>".rrd_error()."\n";
}

$now = time();
$v = 1;
$iv = 1;
$pv = 1;
// Simulate last 180 days of login, with a step of 5 minutes
for ($t=$now - (3600 * 24); $t<=$now+300; $t+=300) {
	$td = 1;
	$v+=mt_rand(1,20);
	$iv += mt_rand(10, 20);
	$pv += mt_rand(2, 30);
	$ret = rrd_update($GLOBALS['dbfile'], array("-t","votes:infoviews:promoviews","$t:".($v*300).":".($iv*300).":".($pv*300)));
}
print_r(rrd_error());

$options = array(
 "--step", "300",            // Use a step-size of 5 minutes
 "--start", "-1 day",     // this rrd started 6 months ago
 "DS:players:GAUGE:600:0:U",
 "RRA:AVERAGE:0.5:1:288",
 "RRA:AVERAGE:0.5:12:168",
 );

$ret = rrd_create($GLOBALS['dbavg'], $options);
if (! $ret) {
 echo "<b>Creation error: </b>".rrd_error()."\n";
}

// Simulate last 180 days of login, with a step of 5 minutes
for ($t=$now - (3600 * 24); $t<=$now+300; $t+=300) {
	$td = 1;
	$players=mt_rand(1,70);
	$ret = rrd_update($GLOBALS['dbavg'], array("-t","players","$t:$players"));
}
print_r(rrd_error());

create_graph($GLOBALS['dbdir']."/hour.gif", "-1h", "Hourly login attempts");
create_graph($GLOBALS['dbdir']."/day.gif", "-1d", "Daily login attempts");
create_graph($GLOBALS['dbdir']."/week.gif", "-1w", "Weekly login attempts");
create_graph($GLOBALS['dbdir']."/month.gif", "-1m", "Monthly login attempts");

echo "<table>";
echo "<tr><td>";
echo "<img src='$dbdir/hour.gif' alt='Generated RRD image'>";
echo "</td><td>";
echo "<img src='$dbdir/day.gif' alt='Generated RRD image'>";
echo "</td></tr>";
echo "<tr><td>";
echo "<img src='$dbdir/week.gif' alt='Generated RRD image'>";
echo "</td><td>";
echo "<img src='$dbdir/month.gif' alt='Generated RRD image'>";
echo "</td></tr>";
echo "</table>";
exit;

function create_graph($output, $start, $title) {
  $options = array(
    "--slope-mode",
    "--start", $start,
    "--title=$title",
    "--vertical-label=Simulated McLister Data",
    "--lower=0",
    "DEF:votes=$GLOBALS[dbfile]:votes:MAX",
    "DEF:players=$GLOBALS[dbavg]:players:AVERAGE",
    "DEF:flow=$GLOBALS[dbfile]:infoviews:MAX",
    "DEF:views=$GLOBALS[dbfile]:promoviews:MAX",
    
    "AREA:players#FF8000:Players",
    "AREA:votes#00FF00:Server Votes",
    "LINE:flow#FF0000:Server Views",
    "LINE:views#0000FF:Promo Impressions",
    
    "GPRINT:players:AVERAGE:Avg. Players %6.2lf",
    "GPRINT:votes:AVERAGE:Votes %6.2lf",
    "GPRINT:views:AVERAGE:Promos %6.2lf",
  );

  $ret = rrd_graph($output, $options);
  if (! $ret) {
    echo "<b>Graph error: </b>".rrd_error()."\n";
  }
}

/*
print_r(rrd_fetch( $GLOBALS['dbfile'], array( "MAX" ) ));
print_r(rrd_fetch( $GLOBALS['dbavg'], array( "AVERAGE" ) ));
*/

?>