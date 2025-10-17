<?php

// LibDrawArea 抽象类演示程序
// 基于极简设计理念的五子棋游戏实现

require_once 'vendor/autoload.php';

use Kingbes\Libui\SDK\GomokuDrawArea;
use Kingbes\Libui\SDK\LibuiApplication;
use Kingbes\Libui\SDK\LibuiWindow;

/**
 * 五子棋游戏 - LibDrawArea 抽象类完整演示
 * 
 * 展示了 LibDrawArea 的核心优势：
 * 1. 极简封装：只关注业务逻辑，无需处理底层 FFI
 * 2. 模板方法模式：子类只需实现 draw() 和 onMouseClick()
 * 3. 内置错误处理：所有异常都被捕获和处理
 * 4. 标准化接口：统一的绘图上下文和事件处理
 */
class LibDrawAreaDemo
{
    private LibuiApplication $app;
    private LibuiWindow $window;
    private GomokuDrawArea $drawArea;

    public function __construct()
    {
        // 初始化应用
        $this->app = LibuiApplication::getInstance();
        $this->app->init();

        // 创建窗口
        $this->window = $this->app->createWindow("五子棋 - LibDrawArea 抽象类演示", 600, 600);
        
        // 创建基于抽象类的绘图区域 - 极简设计！
        $this->drawArea = new GomokuDrawArea(600, 600);
        
        // 添加到窗口并显示
        $this->window->setChild($this->drawArea->getHandle());
        $this->window->show();
    }

    public function run(): void
    {
        try {
            echo "=== LibDrawArea 抽象类演示 ===\n";
            echo "设计理念：基于 main2.php 的极简封装\n";
            echo "使用方法：点击棋盘空白处下棋\n\n";
            
            $this->app->run();
            
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . "\n";
        }
    }
}

// 运行演示
$demo = new LibDrawAreaDemo();
$demo->run();