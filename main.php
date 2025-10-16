#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\Base;
use Kingbes\Libui\Menu;
use phpgo\UI\GomokuMainWindow;
use phpgo\Core\GomokuGame;

echo "五子棋游戏 v1.0.0\n";
echo "使用PHP和libui库构建的五子棋游戏\n";
echo "Starting application...\n\n";

// 检查libui库是否可用
try {
    $ffi = \Kingbes\Libui\Base::ffi();
    echo "✓ kingbes/libui library is available\n";
    echo "✓ libui library loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Failed to load libui library: " . $e->getMessage() . "\n";
    exit(1);
}

// 创建主窗口（菜单会在构造函数中创建）
$mainWindow = new GomokuMainWindow();

// 显示窗口
$mainWindow->show();

// 运行应用程序
echo "Running application...\n";
App::main();
echo "Application finished.\n";