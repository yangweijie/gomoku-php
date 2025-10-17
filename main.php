#!/usr/bin/env php
<?php

use phpgo\UI\GomokuGameDemo;

require_once 'vendor/autoload.php';

// 运行演示
$demo = new GomokuGameDemo();
$demo->run();