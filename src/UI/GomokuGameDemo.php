<?php
namespace phpgo\UI;
// 五子棋游戏演示程序
// 基于LibDrawArea抽象类实现的完整五子棋游戏

use Kingbes\Libui\SDK\LibuiApplication;
use Kingbes\Libui\SDK\LibuiWindow;
use Kingbes\Libui\SDK\LibuiButton;
use Kingbes\Libui\SDK\LibuiVBox;
use Kingbes\Libui\SDK\LibuiHBox;

/**
 * 五子棋游戏演示
 * 
 * 展示了基于LibDrawArea抽象类实现的完整五子棋游戏功能：
 * 1. 15×15标准棋盘绘制
 * 2. 棋子放置与显示
 * 3. 胜负判定算法
 * 4. 玩家轮流机制
 * 5. 操作按钮功能实现
 */
class GomokuGameDemo
{
    private LibuiApplication $app;
    private LibuiWindow $window;
    private GomokuGame $gameArea;
    private LibuiVBox $vBox;

    public function __construct()
    {
        // 初始化应用
        $this->app = LibuiApplication::getInstance();
        $this->app->init();
        
        // 创建窗口
        $this->window = $this->app->createWindow("五子棋游戏", 550, 650);
        
        // 创建垂直布局容器
        $this->vBox = new LibuiVBox();
        $this->vBox->setPadded(true);
        
        // 创建游戏区域
        $this->gameArea = new GomokuGame(500, 500);
        // 设置游戏区域的父窗口
        $this->gameArea->setParentWindow($this->window->getHandle());
        
        // 创建按钮
        $passButton = new LibuiButton("跳过本轮(Pass Turn)");
        $undoButton = new LibuiButton("撤销上步(Undo Turn)");
        $resignButton = new LibuiButton("认输(Resign)");
        $restartButton = new LibuiButton("重新开始");
        
        // 设置按钮事件处理
        $gameArea = $this->gameArea; // 创建本地引用
        
        $passButton->onClick(function() use ($gameArea) {
            $gameArea->passTurn();
        });
        
        $undoButton->onClick(function() use ($gameArea) {
            $gameArea->undoTurn();
        });
        
        $resignButton->onClick(function() use ($gameArea) {
            $gameArea->resign();
        });
        
        $restartButton->onClick(function() use ($gameArea) {
            $gameArea->resetGame();
        });
        
        // 创建按钮水平布局容器
        $hBox = new LibuiHBox();
        $hBox->setPadded(true);
        $hBox->append($passButton->getHandle());
        $hBox->append($undoButton->getHandle());
        $hBox->append($resignButton->getHandle());
        $hBox->append($restartButton->getHandle());
        
        // 将按钮和游戏区域添加到垂直布局中（按钮在上方）
        $this->vBox->append($hBox->getHandle(), false);  // 按钮区域不拉伸
        $this->vBox->append($this->gameArea->getHandle(), true);  // stretchy = true，使游戏区域填充剩余空间
        
        // 将布局容器设置为窗口的子元素
        $this->window->setChild($this->vBox->getHandle());
        $this->window->show();
    }

    public function run(): void
    {
        try {
            echo "=== 五子棋游戏演示 ===\n";
            echo "游戏规则：双方轮流在棋盘上放置黑白棋子，先连成五子者获胜\n";
            echo "操作说明：\n";
            echo "  - 点击棋盘空白处落子\n";
            echo "  - 使用底部按钮控制游戏\n\n";
            
            $this->app->run();
            
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . "\n";
        }
    }
}

// 运行演示
$demo = new GomokuGameDemo();
$demo->run();