<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

session_destroy();
redirect('index.php');
