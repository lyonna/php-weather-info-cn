<?php
  function getWeatherData($cityid) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://m.weather.com.cn/data/'.$cityid.'.html');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    do {
      $weatherdata = curl_exec($ch);
    } while ($weatherdata == '');
    curl_close($ch);
    $weatherdata = json_decode($weatherdata, TRUE);
    return $weatherdata['weatherinfo'];
  }

  function getDateNum($datedata) {
    return sscanf($datedata, "%d%*[^0-9]%d%*[^0-9]%d");
  }

  function dayToNum($weekday) {
    switch($weekday) {
      case '星期一':
        $daynum = 0;
        break;
      case '星期二':
        $daynum = 1;
        break;
      case '星期三':
        $daynum = 2;
        break;
      case '星期四':
        $daynum = 3;
        break;
      case '星期五':
        $daynum = 4;
        break;
      case '星期六':
        $daynum = 5;
        break;
      case '星期日':
        $daynum = 6;
        break;
    }
    return $daynum;
  }

  function getDay($daynum) {
    $days = array('星期一','星期二','星期三','星期四','星期五','星期六','星期日');
    return $days[$daynum%7];
  }

  function getNextDate($date) {
    $year = $date[0];
    $month = $date[1];
    $day = $date[2];

    switch($month) {
      case 1:
      case 3:
      case 5:
      case 7:
      case 8:
      case 10:
      case 12:
        if ($day == 31) {
          if ($month == 12) {$year++; $month = 1;}
          else {$month++;}
          $day = 1;
        }
        else {
          $day++;
        }
        break;
      case 4:
      case 6:
      case 9:
      case 11:
        if ($day == 31) {$month++; $day = 1;}
        else {$day++;}
        break;
      case 2:
        if (($year%4 == 0 && ($year%100) != 0) || $year%400 == 0) {
          if ($day == 29) {$month++; $day = 1;}
          else {$day++;}
        }
        else {
          if ($day == 28) {$month++; $day = 1;}
          else {$day++;}
        }
        break;
    }
    return array($year, $month, $day);
  }

  function getImagesUrlFromData($weatherdata) {
    if ($weatherdata['fchh'] != '18') {
      $images[] = null;
      $remainder = 1;
    }
    else {
      $remainder = 0;
    }
    for ($i = 1; $i < 13; $i++) {
      if (strlen($weatherdata["img$i"]) == 1) {$weatherdata["img$i"] = "0".$weatherdata["img$i"];}
      if ($weatherdata["img$i"] == "99") {$weatherdata["img$i"] = $weatherdata['img'.($i-1)];}
      if ($i%2 == $remainder) {$images[] = 'http://www.weather.com.cn/m/i/icon_weather/42x30/d'.$weatherdata["img{$i}"].'.gif';}
      else {$images[] = 'http://www.weather.com.cn/m/i/icon_weather/42x30/n'.$weatherdata["img{$i}"].'.gif';}
    }
    return $images;
  }

  function fixWeatherData($weatherdata) {
    for ($i = 1; $i < 6; $i++) {
      $fixedweatherdata['weather'.$i][0] = explode('转', $weatherdata['weather'.$i]);
      $fixedweatherdata['weather'.$i][1] = explode('转', $weatherdata['weather'.($i+1)]);
      $fixedweatherdata['temp'.$i][0] = explode('~', $weatherdata['temp'.$i]);
      $fixedweatherdata['temp'.$i][1] = explode('~', $weatherdata['temp'.($i+1)]);

      if (preg_match('/(转.+风)/', $weatherdata['wind'.$i])) {
        preg_match('/转([微东南西北风]+)/', $weatherdata['wind'.$i], $fixedweatherdata['wind'.$i][0]);
      }
      else {
        preg_match('/([微东南西北风]+)/', $weatherdata['wind'.$i], $fixedweatherdata['wind'.$i][0]);
      }
      $fixedweatherdata['wind'.$i][0] = $fixedweatherdata['wind'.$i][0][1];
      preg_match('/^([微东南西北风]+)/', $weatherdata['wind'.($i+1)], $fixedweatherdata['wind'.$i][1]);
      $fixedweatherdata['wind'.$i][1] = $fixedweatherdata['wind'.$i][1][1];
      preg_match('/([0-9-大小于级]+)$/', $weatherdata['fl'.$i], $fixedweatherdata['fl'.$i][0]);
      $fixedweatherdata['fl'.$i][0] = $fixedweatherdata['fl'.$i][0][1];
      preg_match('/^([0-9-大小于级]+)/', $weatherdata['fl'.($i+1)], $fixedweatherdata['fl'.$i][1]);
      $fixedweatherdata['fl'.$i][1] = $fixedweatherdata['fl'.$i][1][1];

      (strlen($fixedweatherdata['wind'.$i][0]) % 3) != 0 && $fixedweatherdata['wind'.$i][0] = substr_replace($fixedweatherdata['wind'.$i][0], '', strlen($fixedweatherdata['wind'.$i][0])-strlen($fixedweatherdata['wind'.$i][0]) % 3);
      (strlen($fixedweatherdata['wind'.$i][1]) % 3) != 0 && $fixedweatherdata['wind'.$i][1] = substr_replace($fixedweatherdata['wind'.$i][1], '', strlen($fixedweatherdata['wind'.$i][1])-strlen($fixedweatherdata['wind'.$i][1]) % 3);

      if ($fixedweatherdata['fl'.$i][0] == $fixedweatherdata['fl'.$i][1]) {
        if ($fixedweatherdata['wind'.$i][0] == $fixedweatherdata['wind'.$i][1]) {
          $fixedweatherdata['wind'.$i] = $fixedweatherdata['wind'.$i][0].$fixedweatherdata['fl'.$i][0];
        }
        else {
          $fixedweatherdata['wind'.$i] = $fixedweatherdata['wind'.$i][0].'转'.$fixedweatherdata['wind'.$i][1].$fixedweatherdata['fl'.$i][0];
        }
      }
      else {
        if ($fixedweatherdata['wind'.$i][0] == $fixedweatherdata['wind'.$i][1]) {
          $fixedweatherdata['wind'.$i] = $fixedweatherdata['wind'.$i][0].$fixedweatherdata['fl'.$i][0].'转'.$fixedweatherdata['fl'.$i][1];
        }
        else {
          $fixedweatherdata['wind'.$i] = $fixedweatherdata['wind'.$i][0].$fixedweatherdata['fl'.$i][0].'转'.$fixedweatherdata['wind'.$i][1].$fixedweatherdata['fl'.$i][1];
        }
      }

      empty($fixedweatherdata['weather'.$i][0][1]) && $fixedweatherdata['weather'.$i][0][1] = $fixedweatherdata['weather'.$i][0][0];

      $fixedweatherdata['weather'.$i] = ($fixedweatherdata['weather'.$i][0][1] == $fixedweatherdata['weather'.$i][1][0]) ? $fixedweatherdata['weather'.$i][0][1] : $fixedweatherdata['weather'.$i][0][1].'转'.$fixedweatherdata['weather'.$i][1][0];
      $fixedweatherdata['temp'.$i] = $fixedweatherdata['temp'.$i][1][0].'~'.$fixedweatherdata['temp'.$i][0][1];
    }
    return $fixedweatherdata;
  }

  function printHead($city) { ?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title><?php echo $city; ?>天气预报</title>
</head>
<?php
  }

  function printReleaseTime($date, $time) { ?>
<body>
<ul>
<li><?php echo "{$date[0]}-{$date[1]}-{$date[2]} {$time}";?>时发布</li><br />
<?php
  }

  function printWeather($weatherdata, $date, $daynum, $images, $length) {
    for ($i = 1; $i < $length; $i++) {
      echo "<li>{$date[1]}月{$date[2]}日 ".getDay($daynum+$i-1).'<br />';
      echo '<img src="'.$images[($i*2-1)].'" /><img src="'.$images[($i*2)].'" /><br />';
      echo "{$weatherdata["weather$i"]} {$weatherdata["temp$i"]}<br />{$weatherdata["wind$i"]}</li>\n<br />";
      $date = getNextDate($date);
    }
  }

  if (!isset($_GET["cid"]) || strlen($_GET["cid"])!=9 || !ctype_digit($_GET["cid"])) {
    printHead('');
    echo "<body>\n<h3>城市代码不合法！</h3>";
  }
  else {
    $weatherdata = getWeatherData($_GET["cid"]);
    $date = getDateNum($weatherdata['date_y']);
    $daynum = dayToNum($weatherdata['week']);
    $images = getImagesUrlFromData($weatherdata);
    $length = 7;

    printHead($weatherdata['city']);
    printReleaseTime($date, $weatherdata['fchh']);

    if ($weatherdata['fchh'] == '18') {
      $date = getNextDate($date);
      $daynum++;
      $weatherdata = fixWeatherData($weatherdata);
      $length = 6;
    }

    printWeather($weatherdata, $date, $daynum, $images, $length);
  }
?>

</body>
</html>
