<?php
include '/data/www/webpie/core/webpie.php';
new Webpie;
session_start();
$a = new Webpie_Captcha();
$a->createImage();
