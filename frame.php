<?php

// Frame used to deal with the fact we can't navigate within web splash screens in GM 8

$query_string = $_SERVER['QUERY_STRING'];

?><!doctype html>
<meta charset=utf-8>
<title>GG2 Rewards Area Redirection Frame</title>
<iframe src="./index.php?<?=htmlspecialchars($query_string)?>" width="100%" height="100%" frameborder="0" style="position: fixed; left: 0; right: 0; top: 0; bottom: 0;"></iframe>
