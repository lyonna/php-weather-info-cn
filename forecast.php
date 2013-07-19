<?php
  date_default_timezone_set('Asia/Shanghai');
  $city = '';
  $body = '<h3>城市代码不合法！</h3>';

  if (isset($_GET['cid']) && strlen($_GET['cid'])==9 && ctype_digit($_GET['cid'])) {
    $forecast = getforecast($_GET['cid']);
    if ($forecast) {
      $city = $forecast['city'];
      $body = printbody($forecast);
    }
  }

  function getforecast($cityid) {
    if (!function_exists('curl_init')) {
      $forecast = file_get_contents('http://m.weather.com.cn/data/'.$cityid.'.html');
    }
    else {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'http://m.weather.com.cn/data/'.$cityid.'.html');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
      $forecast = curl_exec($ch);
      curl_close($ch);
    }
    $forecast = json_decode($forecast, TRUE);
    if ($forecast) {
      return $forecast['weatherinfo'];
    }
    return false;
  }

  function getdatetime($datestr, $hour) {
    return strtotime(preg_replace(array('/年/', '/月/', '/日/'), array('-', '-', ''), $datestr)." {$hour}:00:00");
  }

  function getday($datetime) {
    $days = array('星期日','星期一','星期二','星期三','星期四','星期五','星期六');
    return $days[date('w', $datetime)];
  }

  function getimageurl($forecast) {
    if ($forecast['fchh'] != '18') {
      $images[] = null;
      $remainder = 1;
    }
    else {
      $remainder = 0;
    }

    $preurl = 'http://www.weather.com.cn/m/i/icon_weather/42x30/';
    for ($i = 1; $i < 13; $i++) {
      $forecast["img$i"] = strlen($forecast["img$i"]) == 1 ? '0'.$forecast["img$i"] : $forecast["img$i"];

      if ($forecast["img$i"] == '99') {$forecast["img$i"] = $forecast['img'.($i-1)];}
      $images[] = $i%2 == $remainder ? $preurl.'d'.$forecast["img{$i}"].'.gif' : $preurl.'n'.$forecast["img{$i}"].'.gif';
    }
    return $images;
  }

  function fixforecast($forecast) {
    for ($i = 1; $i < 6; $i++) {
      $fix['weather'.$i][0] = explode('转', $forecast['weather'.$i]);
      $fix['weather'.$i][1] = explode('转', $forecast['weather'.($i+1)]);
      $fix['temp'.$i][0] = explode('~', $forecast['temp'.$i]);
      $fix['temp'.$i][1] = explode('~', $forecast['temp'.($i+1)]);

      preg_match('/(转.+风)/', $forecast['wind'.$i]) ? preg_match('/转([微东南西北风]+)/', $forecast['wind'.$i], $fix['wind'.$i][0]) : preg_match('/([微东南西北风]+)/', $forecast['wind'.$i], $fix['wind'.$i][0]);
      $fix['wind'.$i][0] = $fix['wind'.$i][0][1];
      preg_match('/^([微东南西北风]+)/', $forecast['wind'.($i+1)], $fix['wind'.$i][1]);
      $fix['wind'.$i][1] = $fix['wind'.$i][1][1];
      preg_match('/([0-9-大小于级]+)$/', $forecast['fl'.$i], $fix['fl'.$i][0]);
      $fix['fl'.$i][0] = $fix['fl'.$i][0][1];
      preg_match('/^([0-9-大小于级]+)/', $forecast['fl'.($i+1)], $fix['fl'.$i][1]);
      $fix['fl'.$i][1] = $fix['fl'.$i][1][1];

      (strlen($fix['wind'.$i][0]) % 3) != 0 && $fix['wind'.$i][0] = substr_replace($fix['wind'.$i][0], '', strlen($fix['wind'.$i][0])-strlen($fix['wind'.$i][0]) % 3);
      (strlen($fix['wind'.$i][1]) % 3) != 0 && $fix['wind'.$i][1] = substr_replace($fix['wind'.$i][1], '', strlen($fix['wind'.$i][1])-strlen($fix['wind'.$i][1]) % 3);

      if ($fix['fl'.$i][0] == $fix['fl'.$i][1]) {
        $fix['wind'.$i] = $fix['wind'.$i][0] == $fix['wind'.$i][1] ? $fix['wind'.$i][0].$fix['fl'.$i][0] : $fix['wind'.$i][0].'转'.$fix['wind'.$i][1].$fix['fl'.$i][0];
      }
      else {
        $fix['wind'.$i] = $fix['wind'.$i][0] == $fix['wind'.$i][1] ? $fix['wind'.$i][0].$fix['fl'.$i][0].'转'.$fix['fl'.$i][1] : $fix['wind'.$i][0].$fix['fl'.$i][0].'转'.$fix['wind'.$i][1].$fix['fl'.$i][1];
      }

      empty($fix['weather'.$i][0][1]) && $fix['weather'.$i][0][1] = $fix['weather'.$i][0][0];

      $fix['weather'.$i] = $fix['weather'.$i][0][1] == $fix['weather'.$i][1][0] ? $fix['weather'.$i][0][1] : $fix['weather'.$i][0][1].'转'.$fix['weather'.$i][1][0];
      $fix['temp'.$i] = $fix['temp'.$i][1][0].'~'.$fix['temp'.$i][0][1];
    }
    return $fix;
  }

  function printbody($forecast) {
    function printdate($datetime) {
      return date('n月j日', $datetime).' '.getday($datetime);
    }
    $date = getdatetime($forecast['date_y'], $forecast['fchh']);
    $images = getimageurl($forecast);
    $length = 7;
    $body = "<ul>\n<li>".date('Y-m-d H时', $date)."发布</li><br />\n";

    if ($forecast['fchh'] == '18') {
      $date += 86400;
      $forecast = fixforecast($forecast);
      $length = 6;
    }

    for ($i = 1; $i < $length; $i++) {
      $body .= '<li>'.printdate($date)."<br />";
      $body .= '<img src="'.$images[($i*2-1)].'" /><img src="'.$images[($i*2)]."\" /><br />";
      $body .= "{$forecast["weather$i"]} {$forecast["temp$i"]}<br />{$forecast["wind$i"]}</li><br />\n";
      $date += 86400;
    }
    return $body;
  }
?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title><?php echo $city; ?>天气预报</title>
</head>
<body>
<?php echo $body; ?>
</body>
</html>
