<?php
if (isset($_GET['url']) and $_GET['url'] != '') {
    $url = str_replace('https', 'http', $_GET['url']);
}
$dir = pathinfo($url);
$host = $dir['dirname'];
$refer = $host . '/';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_REFERER, $refer);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//Activation can modify the page
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);
header("Content-type: image/jpeg");
print($data);
?>