<?php
require_once '../includes/loader.php';
do_logout();
header('Location: '.SITE_URL);
exit;
