<?php
  $aid = (isset($_GET['aid']) && ctype_digit($_GET['aid'])) ? $_GET['aid'] : '';

  function getalarminfo($areaid = '') {
    if ($areaid == '') {
      return array();
    }

    $linkdata = getdata('http://product.weather.com.cn/alarm/grepalarm.php?areaid='.$areaid);
    $linkdata = parsejsjson($linkdata);
    $info = parselinkdata($linkdata);
    return $info;
  }

  function getdata($url) {
    if (!function_exists('curl_init')) {
      do {
        $data = file_get_contents($url);
      } while ($data == '');
    }
    else {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
      do {
        $data = curl_exec($ch);
      } while ($data == '');
      curl_close($ch);
    }
    return $data;
  }

  function parsejsjson($json) {
    $json = substr($json, 14);
    $json = rtrim($json, ';');
    $json = json_decode($json, TRUE);
    return $json;
  }

  function parselinkdata($linkdata) {
    $info = array();
    for ($i=0; $i<$linkdata['count']; $i++) {
      $detail = getdetail($linkdata['data'][$i][1]);

      $typeid = explode('-', rtrim($linkdata['data'][$i][1], '.html'));
      $typeid = $typeid[2];

      $info[$i]['area'] = $detail['PROVINCE'].$detail['CITY'];
      $info[$i]['typeid'] = $typeid;
      $info[$i]['type'] = $detail['SIGNALTYPE'].$detail['SIGNALLEVEL'];
      $info[$i]['time'] = strtotime($detail['ISSUETIME']);
      $info[$i]['content'] = $detail['ISSUECONTENT'];
      $info[$i]['picurl'] = getpicurl($typeid);
//    $info[$i]['measures'] = getmeasures($typeid);
    }
    return $info;
  }

  function getdetail($url) {
    $detail = getdata('http://www.weather.com.cn/data/alarm/'.$url);
    $detail = parsejsjson($detail);
    return $detail;
  }

  function getmeasures($typeid) {
    $measures = getdata('http://www.weather.com.cn/data/alarminfo/'.$typeid.'.html');
    $measures = parsejsjson($measures);
    $measures = explode('<br>', $measures[3]);
    return $measures;
  }

  function getpicurl($typeid, $pictype = 'big') {
    if ($pictype == 'small') {
      return 'http://www.weather.com.cn/m2/i/alarm_s/'.$typeid.'.gif';
    }
    return 'http://www.weather.com.cn/m2/i/about/alarmpic/'.$typeid.'.gif';
  }

  function outputbody($info) {
    if ($info == array()) {
      return '<h3>地区代码非法或该地区当前无预警信息！</h3>';
    }

    $body = '';
    $count = count($info);
    for ($i=0; $i<$count; $i++) {
      if (date('i', $info[$i]['time']) == '00') {
        $time = date('Y年m月d日H时', $info[$i]['time']);
      }
      else {
        $time = date('Y年m月d日H时i分', $info[$i]['time']);
      }

      $body .= '【'.$info[$i]['area'].$time.'发布'.$info[$i]['type']."预警信号】<br />\n".$info[$i]['content']."<br /><br />\n";
    }
    return $body;
  }
?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>灾害预警信息</title>
</head>
<body>
<?php echo outputbody(getalarminfo($aid)); ?>
</body>
</html>