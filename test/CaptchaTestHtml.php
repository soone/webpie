<?php
include 'request.php';
new Webpie;
session_start();
$a = new Webpie_Captcha();
$a->createImage();
