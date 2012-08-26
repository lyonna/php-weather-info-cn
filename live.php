<?php

  if (!isset($_GET['cid']) || strlen($_GET['cid'])!=9 || !ctype_digit($_GET['cid'])) {
    $city = '';
    $body = '<h3>城市代码不合法！</h3>';
  }
  else {
    $live = getlivedata($_GET["cid"]);
    $city = $live['city'];
    $body = "{$live['time']}发布<br />\n温度：{$live['temp']}℃<br />\n湿度：{$live['SD']}<br />\n风向：{$live['WD']}<br />\n风力：{$live['WS']}<br />\n";
  }

  function getlivedata($cid) {
    if (!function_exists('curl_init')) {
      do {
        $data = file_get_contents('http://www.weather.com.cn/data/sk/'.$cid.'.html');
      } while ($data == '');
    }
    else {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'http://www.weather.com.cn/data/sk/'.$cid.'.html');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
      do {
        $data = curl_exec($ch);
      } while ($data == '');
      curl_close($ch);
    }
    $data = json_decode($data, TRUE);
    return $data['weatherinfo'];
  }

?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title><?php echo $city; ?>天气实况</title>
</head>
<body>
<?php echo $body; ?>
</body>
</html>