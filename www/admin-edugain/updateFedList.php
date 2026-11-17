<?php

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://technical.edugain.org/api?action=list_feds&format=json');
curl_setopt($ch, CURLOPT_USERAGENT, 'Release-check');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_NOBODY, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$res = curl_exec($ch);
$data = json_decode($res, true, 4);
$handle = fopen(__DIR__ . "/fedlist.php", "w");

fwrite($handle, '<?php

$fedList = [
');

foreach ($data as $key => $row) {
  if ($row['status'] == 6) {
    fwrite($handle, sprintf("  '%s' => '%s', \n", $key, $row['reg_auth']));
  }
}
fwrite($handle, "];\n");
fclose($handle);
