<?php
/**
 * This script creates a gauge
 * Several parameters are sent over GET to customize the look of the gauge
 *
 * @author Tim Robert-Fitzgerald June 2016
 */
require '../includes/db.php';
require '../includes/class.Meter.php';
error_reporting(-1);
ini_set('display_errors', 'On');
$meter = new Meter($db);
if ($_GET['ver'] === 'html') {
  // Charset here is important for displaying unicode symbols correctly!
  header('Content-Type: text/html; charset=UTF-8'); // Output is HTML
} else {
  header('Content-Type: image/svg+xml; charset=UTF-8'); // Output is SVG
}
$log = array(); // For debugging purposes; when used in production this code should be removed
$meter_id = (!empty($_GET['meter_id'])) ? $_GET['meter_id'] : 0;
$rv_id = (!empty($_GET['rv_id'])) ? $_GET['rv_id'] : 0;
$color = (!empty($_GET['color'])) ? $_GET['color'] : '#333'; // Color of text
$bg = (!empty($_GET['bg'])) ? $_GET['bg'] : '#fff'; // Background color of gauge
$height = (!empty($_GET['height'])) ? $_GET['height'] : 190;
$width = (!empty($_GET['width'])) ? $_GET['width'] : 290;
$font_family = (!empty($_GET['font_family'])) ? $_GET['font_family'] : 'Futura, Helvetica, sans-serif';
$title = (!empty($_GET['title'])) ? $_GET['title'] : 'Untitled Gauge';
$title2 = (!empty($_GET['title2'])) ? $_GET['title2'] : null;
$border_radius = (!empty($_GET['border_radius'])) ? $_GET['border_radius'] : 3;
$rounding = (!empty($_GET['rounding'])) ? $_GET['rounding'] : null;
$units = (!empty($_GET['units'])) ? $_GET['units'] : $meter->getUnits($meter_id);
$stmt = $db->prepare('SELECT relative_value FROM relative_values WHERE id = ? LIMIT 1');
$stmt->execute(array($rv_id));
$relative_value = intval($stmt->fetchColumn());
$log[] = $relative_value;
$relative_value = ($_GET['ver'] === 'html') ? $meter->scale($relative_value, 14, 80) : $meter->scale($relative_value, 15, 85); // Placement of relative level indicator is different in HTML/SVG versions
$log[] = $relative_value;
$stmt = $db->prepare('SELECT current FROM meters WHERE id = ?');
$stmt->execute(array($meter_id));
$current = $stmt->fetchColumn();
if ($rounding === null) {
  if ($current < 3) {
    $rounding = 2;
  }
  elseif ($current < 10) {
    $rounding = 1;
  }
  else {
    $rounding = 0;
  }
}
?>


<?php if ($_GET['ver'] === 'html') { ?>

<!DOCTYPE html>
<html lang="en" onmouseover="hideTime()" onmouseout="showTime()">
<head>
<style>
html, body { overflow: hidden; }
.odometer.odometer-auto-theme, .odometer.odometer-theme-default {
  display: inline-block;
  vertical-align: middle;
  *vertical-align: auto;
  *zoom: 1;
  *display: inline;
  position: relative;
}
.odometer.odometer-auto-theme .odometer-digit, .odometer.odometer-theme-default .odometer-digit {
  display: inline-block;
  vertical-align: middle;
  *vertical-align: auto;
  *zoom: 1;
  *display: inline;
  position: relative;
}
.odometer.odometer-auto-theme .odometer-digit .odometer-digit-spacer, .odometer.odometer-theme-default .odometer-digit .odometer-digit-spacer {
  display: inline-block;
  vertical-align: middle;
  *vertical-align: auto;
  *zoom: 1;
  *display: inline;
  visibility: hidden;
}
.odometer.odometer-auto-theme .odometer-digit .odometer-digit-inner, .odometer.odometer-theme-default .odometer-digit .odometer-digit-inner {
  text-align: left;
  display: block;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow: hidden;
}
.odometer.odometer-auto-theme .odometer-digit .odometer-ribbon, .odometer.odometer-theme-default .odometer-digit .odometer-ribbon {
  display: block;
}
.odometer.odometer-auto-theme .odometer-digit .odometer-ribbon-inner, .odometer.odometer-theme-default .odometer-digit .odometer-ribbon-inner {
  display: block;
  -webkit-backface-visibility: hidden;
}
.odometer.odometer-auto-theme .odometer-digit .odometer-value, .odometer.odometer-theme-default .odometer-digit .odometer-value {
  display: block;
  -webkit-transform: translateZ(0);
}
.odometer.odometer-auto-theme .odometer-digit .odometer-value.odometer-last-value, .odometer.odometer-theme-default .odometer-digit .odometer-value.odometer-last-value {
  position: absolute;
}
.odometer.odometer-auto-theme.odometer-animating-up .odometer-ribbon-inner, .odometer.odometer-theme-default.odometer-animating-up .odometer-ribbon-inner {
  -webkit-transition: -webkit-transform 2s;
  -moz-transition: -moz-transform 2s;
  -ms-transition: -ms-transform 2s;
  -o-transition: -o-transform 2s;
  transition: transform 2s;
}
.odometer.odometer-auto-theme.odometer-animating-up.odometer-animating .odometer-ribbon-inner, .odometer.odometer-theme-default.odometer-animating-up.odometer-animating .odometer-ribbon-inner {
  -webkit-transform: translateY(-100%);
  -moz-transform: translateY(-100%);
  -ms-transform: translateY(-100%);
  -o-transform: translateY(-100%);
  transform: translateY(-100%);
}
.odometer.odometer-auto-theme.odometer-animating-down .odometer-ribbon-inner, .odometer.odometer-theme-default.odometer-animating-down .odometer-ribbon-inner {
  -webkit-transform: translateY(-100%);
  -moz-transform: translateY(-100%);
  -ms-transform: translateY(-100%);
  -o-transform: translateY(-100%);
  transform: translateY(-100%);
}
.odometer.odometer-auto-theme.odometer-animating-down.odometer-animating .odometer-ribbon-inner, .odometer.odometer-theme-default.odometer-animating-down.odometer-animating .odometer-ribbon-inner {
  -webkit-transition: -webkit-transform 2s;
  -moz-transition: -moz-transform 2s;
  -ms-transition: -ms-transform 2s;
  -o-transition: -o-transform 2s;
  transition: transform 2s;
  -webkit-transform: translateY(0);
  -moz-transform: translateY(0);
  -ms-transform: translateY(0);
  -o-transform: translateY(0);
  transform: translateY(0);
}

.odometer.odometer-auto-theme, .odometer.odometer-theme-default {
  font-family: <?php echo $font_family; ?>;
  line-height: 1.1em;
}
.odometer.odometer-auto-theme .odometer-value, .odometer.odometer-theme-default .odometer-value {
  text-align: center;
}
body {
  padding: 0px;
  margin: 0px;
}
h1, h2, h3 {
  font-weight: 300;
  margin: 0px;
}
.gauge {
  position: relative;
  height: <?php echo $height; ?>px;
  width: <?php echo $width; ?>px;
  background: <?php echo $bg; ?>;
  color: <?php echo $color; ?>;
  text-align: center;
  font-family: <?php echo $font_family; ?>;
  border-radius: <?php echo $border_radius; ?>px;
}
.title {
  /*font-size: <?php //echo (strlen($title) < 23) ? (($height + $width) / 20) : (($height + $width) / 25); ?>px;*/
  font-size: <?php echo ($title2 === null) ? (($height + $width) / 20) : (($height + $width) / 25); ?>px;
  position: relative;
  top: 3%;
  left: 0;
  right: 0;
}
.title2 {
  top: 5%;
}
.current {
  font-size: <?php echo ($height + $width) / 10; ?>px;
  /*margin-top: 15%;*/
  margin-top: 5%;
  font-weight: 400;
}
.units {
  font-size: <?php echo ($height + $width) / 23; ?>px;
  color: rgba(<?php list($r, $g, $b) = sscanf($color, "#%02x%02x%02x"); echo "{$r}, {$g}, {$b}"; ?>, 0.8);
}
.last-updated {
  position: absolute;
  font-family: Helvetica, sans-serif;
  left: 50%;
  transform: translate(-50%, 0);
  font-size: <?php echo ($height + $width) / 40; ?>px;
  font-weight: 100;
  color: rgba(<?php list($r, $g, $b) = sscanf($color, "#%02x%02x%02x"); echo "{$r}, {$g}, {$b}"; ?>, 0.9);
  margin: 0px;
  bottom: 1%;
  width: 200%;
}
.relative-value {
  position: absolute;
  bottom: 12%;
  width: 70%;
  left: 14%;
  height: <?php echo ($height / 35); ?>px;
  background: rgba(<?php list($r, $g, $b) = sscanf($color, "#%02x%02x%02x"); echo "{$r}, {$g}, {$b}"; ?>, 0.5);
  font-size: <?php echo ($height + $width) / 40; ?>px;
  font-weight: 100;
}
.relative-value:before {
  float: left;
  content: 'LOW';
  position: relative;
  right: 18%;
  bottom: 100%;
}
.relative-value:after {
  float: right;
  content: 'HIGH';
  position: relative;
  left: 19%;
  bottom: 100%;
}
.indicator {
  height: <?php echo ($height / 15); ?>px;
  width: <?php echo ($height / 15); ?>px;
  border-radius: 50%;
  background: #fff;
  position: relative;
  left: <?php echo $relative_value; ?>%;
  bottom: <?php echo ($height / 10); ?>px;
  position: absolute;
}
.animated {
  animation-duration: 1s;
  animation-fill-mode: both;
}
.animated.infinite {
  animation-iteration-count: infinite;
}
.animated.hinge {
  animation-duration: 2s;
}
.animated.flipOutX,
.animated.flipOutY,
.animated.bounceIn,
.animated.bounceOut {
  animation-duration: .75s;
}

@keyframes bounceIn {
  from, 20%, 40%, 60%, 80%, to {
    animation-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
  }
  0% {
    opacity: 0;
    transform: scale3d(.3, .3, .3);
  }
  20% {
    transform: scale3d(1.1, 1.1, 1.1);
  }
  40% {
    transform: scale3d(.9, .9, .9);
  }
  60% {
    opacity: 1;
    transform: scale3d(1.03, 1.03, 1.03);
  }
  80% {
    transform: scale3d(.97, .97, .97);
  }
  to {
    opacity: 1;
    transform: scale3d(1, 1, 1);
  }
}
.bounceIn {
  animation-name: bounceIn;
}
@keyframes fadeOut {
  from {
    opacity: 1;
  }

  to {
    opacity: 0;
  }
}

.fadeOut {
  animation-name: fadeOut;
}
</style>
</head>
<body>
<div class="gauge">
  <h2 class="title animated bounceIn" id="title"><?php echo $title; ?></h2>
  <?php if ($title2 !== null) { ?><h2 class="title title2" id="title2"><?php echo $title2; ?></h2><?php } ?>
  <h1 class="current odometer" id="odometer">0</h1>
  <h3 class="units animated bounceIn"><?php echo $units; ?></h3>
  <h5 id="last-updated" class="last-updated">Updated <?php
  $diff = time() - $meter->lastUpdated($meter_id, 'live');
  if ($diff <= 60) {
    echo "{$diff} seconds";
  }
  elseif ($diff <= 3600) {
    echo floor($diff/60) . ' minutes';
  }
  else {
    echo 'over an hour';
  }
  ?> ago</h5>
  <div class="relative-value animated bounceIn"></div>
  <div class="indicator animated bounceIn"></div>
</div>
</body>
<script src="js/odometer.js"></script>
<script type="text/javascript">
//<![CDATA[
console.log(<?php echo json_encode($log) ?>);
window.odometerOptions = { format: '(,ddd).<?php echo str_repeat('d', $rounding) ?>' };
window.onload=function() {
  odometer.innerHTML = <?php echo round($current, $rounding); ?>;
  parent.iframeLoaded();
}
function hideTime() {
  document.getElementById("last-updated").className = "last-updated";
}
function showTime() {
  document.getElementById("last-updated").className = "last-updated animated fadeOut";
}
setTimeout(function(){ window.location.reload(true); }, 60000);
setTimeout(function(){ document.getElementById("last-updated").className = "last-updated animated fadeOut" }, 3500);
//]]>
</script>
</html>

<?php } else { ?>


<svg xmlns="http://www.w3.org/2000/svg"
     xmlns:xlink="http://www.w3.org/1999/xlink"
     height="<?php echo $height; ?>"
     width="<?php echo $width; ?>">

<style>
/* <![CDATA[ */
@keyframes anim {
  0%, 25%, 50%, 75%, 100% {
    opacity: 1;
  }

  12.5%, 37.5%, 62.5%, 87.5% {
    opacity: 0;
  }
}
.anim { animation: anim 1s 1; }
/* ]]> */
</style>

<rect width="100%"
      height="100%"
      rx="<?php echo $border_radius; ?>" ry="<?php echo $border_radius; ?>"
      style="fill:<?php echo $bg; ?>" />

<?php
$c = str_split(number_format($current, $rounding));
$startx = ($width/2) - (count($c)-1)*14;
for ($i = 0; $i < count($c); $i++) {
  if ($c[$i] === '.' || $c[$i] === '-' || $c[$i] === ',') {
    $tmp = ($c[$i] === ',') ? $startx - 18 : $startx - 12;
    echo "<text x='{$tmp}' y='".$height/1.75."' style='font-weight: 100;font-family: {$font_family};font-size:".(($height + $width) / 10).";fill: {$color};'>{$c[$i]}</text>\n";
    $startx += 20;
  } else {
    echo "<text x='{$startx}' y='".$height*1.2."'\n
          text-anchor='middle'\n
          class='slot-machine digit'\n
          data-digit='{$c[$i]}'\n
          alignment-baseline='central'\n
          style='font-weight: 100;font-family: {$font_family};font-size:".(($height + $width) / 10).";fill: {$color};'>\n
          <tspan>0</tspan>\n";
    $counter = 1;
    for ($iterations = 0; $iterations < 100; $iterations++) { 
      echo "<tspan dy=\"45px\" dx=\"-44.5px\">{$counter}</tspan>\n";
      if ($counter === 9) {
        $counter = 0;
      } else {
        $counter++;
      }
    }
    echo "</text>\n";
    $startx += 30;
  }
}
?>
<rect width="<?php echo $width; ?>px"
      height="<?php echo $height*.35; ?>px"
      x="0"
      y="0"
      rx="<?php echo $border_radius; ?>" ry="<?php echo $border_radius; ?>"
      style="fill:<?php echo $bg; ?>;" />

<rect width="<?php echo $width; ?>px"
      height="<?php echo $height*.4; ?>px"
      x="0"
      y="<?php echo $height*.6; ?>"
      rx="<?php echo $border_radius; ?>" ry="<?php echo $border_radius; ?>"
      style="fill:<?php echo $bg; ?>;" />

<text x="<?php echo $width / 2; ?>"
      y="<?php echo $height / 10; ?>"
      text-anchor="middle"
      alignment-baseline="central"
      style="font-family: <?php echo $font_family; ?>;font-size: <?php echo ($title2 === null) ? (($height + $width) / 20) : (($height + $width) / 25); ?>px;fill: <?php echo $color; ?>;">
        <?php echo $title; ?>
</text>

<?php if ($title2 !== null) { ?>
<text x="<?php echo $width / 2; ?>"
      y="<?php echo ($height / 10) + (($height + $width) / 25); ?>"
      text-anchor="middle"
      alignment-baseline="central"
      style="font-family: <?php echo $font_family; ?>;font-size: <?php echo ($title2 === null) ? (($height + $width) / 20) : (($height + $width) / 25); ?>px;fill: <?php echo $color; ?>;">
        <?php echo $title2; ?>
</text>
<?php } ?>


<text x="<?php echo ($width/2) ?>"
      y="<?php echo $height / 1.5; ?>"
      text-anchor="middle"
      alignment-baseline="central"
      style="font-weight: 100;font-family: <?php echo $font_family; ?>;font-size: <?php echo ($height + $width) / 20; ?>;fill: rgba(<?php list($r, $g, $b) = sscanf($color, "#%02x%02x%02x"); echo "{$r}, {$g}, {$b}"; ?>, 0.8)">
        <?php echo $units; ?>
</text>

<g>
  <line x1="15%" y1="90%" x2="85%" y2="90%"
        style="stroke: <?php echo $color; ?>;stroke-width: <?php echo ($height + $width) / 70; ?>; opacity:0.5" />
  <circle cx="<?php echo $relative_value; ?>%"
          cy="90%"
          r="<?php echo ($height + $width) / 70; ?>"
          fill="<?php echo $color; ?>" />
</g>

<text x="3%" y="92%" text-anchor="left"
      style="font-weight: 100;font-family: <?php echo $font_family; ?>;font-size: <?php echo ($width / 30); ?>;fill: <?php echo $color; ?>">LOW</text>
<text x="97%" y="92%" text-anchor="end"
      style="font-weight: 100;font-family: <?php echo $font_family; ?>;font-size: <?php echo ($width / 30); ?>;fill: <?php echo $color; ?>">HIGH</text>

<?php if (!isset($_GET['nojs'])) {
  echo '<script type="text/javascript" xlink:href="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"/>';
  echo '<script type="text/javascript" xlink:href="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.18.5/TweenMax.min.js"/>';
} ?>
<script type="text/javascript">
// <![CDATA[
  // setTimeout(function(){ window.location.reload(true); }, 60000);
  /* Delete all this
  // https://stackoverflow.com/a/18120786/2624391
  Element.prototype.remove = function() {
    this.parentElement.removeChild(this);
  }
  NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
    for(var i = this.length - 1; i >= 0; i--) {
        if(this[i] && this[i].parentElement) {
            this[i].parentElement.removeChild(this[i]);
        }
    }
  }
  function spin_helper(e, digit, counter = 0) {
    e.setAttribute('y', e.getAttribute('y') + 5);
    if (counter < 10) {
      counter++;
      spin_helper(e, digit, counter);
    }
  }
  Element.prototype.spin = function(digit) {
    spin_helper(this, digit);
  };
  var e = document.getElementsByClassName("slot-machine");
  for (var i = e.length - 1; i >= 0; i--) {
    var digit = e[i].getAttribute("data-digit");
    if (digit === '.') {
      e[i].childNodes.remove();
      e[i].innerHTML = '.';
    } else {
      e[i].spin(digit);
    }
  }
  */
 var total = $('.slot-machine').length;
 $('.slot-machine').each(function(i) {
    var digit = $(this).data('digit');
    if (digit === 1) {
      var special = "-4215px";
    } else if (digit === 2) {
      var special = "-4260px";
    } else if (digit === 3) {
      var special = "-4305px";
    } else if (digit === 4) {
      var special = "-4350px";
    } else if (digit === 5) {
      var special = "-4395px";
    } else if (digit === 6) {
      var special = "-4440px";
    } else if (digit === 7) {
      var special = "-4485px";
    } else if (digit === 8) {
      var special = "-4530px";
    } else if (digit === 9) {
      var special = "-4575px";
    } else {
      var special = "-4620px";
    }
    TweenMax.to($(this), (i/total) + 2, {y:special, ease:Power4.easeOut});
  });
// ]]>
</script>
</svg>
<?php } ?>