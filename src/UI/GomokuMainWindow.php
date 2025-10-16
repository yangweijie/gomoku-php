<?php

namespace phpgo\UI;

use Kingbes\Libui\Base;
use Kingbes\Libui\App;
use Kingbes\Libui\Window;
use Kingbes\Libui\Control;
use Kingbes\Libui\Menu;
use phpgo\Core\GomokuGame;

class GomokuMainWindow
{
    private \FFI $ffi;
    private GomokuGame $game;
    private GomokuBoard $board;
    private $window;
    
    public function __construct()
    {
        $this->ffi = Base::ffi();
        $this->game = new GomokuGame(15);
        
        // 初始化应用程序
        App::init();
        
        // 立即创建菜单，确保在任何窗口显示之前
        $this->createMenus();
        
        $this->createWindow();
        $this->board = new GomokuBoard($this->game, $this->window);
    }
    
    private function createWindow(): void
    {
        // 创建窗口 - 使用固定大小
        $this->window = Window::create("Ruby Go", 800, 600, 1);
        
        // 设置窗口关闭回调
        Window::onClosing($this->window, function ($window) {
            App::quit();
            return 1;
        });
    }
    
    public function createMenus(): void
    {
        // 创建游戏菜单
        $gameMenu = Menu::create("游戏");
        Menu::appendQuitItem($gameMenu);
        
        $newGameItem = Menu::appendItem($gameMenu, "新游戏");
        $this->ffi->uiMenuItemOnClicked($newGameItem, function ($item, $window, $data) {
            $this->newGame();
        }, null);
        
        // 创建帮助菜单
        $helpMenu = Menu::create("帮助");
        $aboutItem = Menu::appendItem($helpMenu, "关于");
        $this->ffi->uiMenuItemOnClicked($aboutItem, function ($item, $window, $data) {
            $this->showAbout();
        }, null);
    }
    
    private function newGame(): void
    {
        $this->game->reset();
        // 注意：这里不再直接重绘区域，因为GomokuBoard会处理更新
    }
    
    private function showAbout(): void
    {
        $this->ffi->uiMsgBox(
            $this->window,
            "关于五子棋游戏",
            "五子棋游戏 v1.0.0\n\n使用PHP和libui库构建的五子棋游戏。"
        );
    }
    
    public function show(): void
    {
        Control::show($this->window);
    }
    
    public function getWindow()
    {
        return $this->window;
    }
    
    public function getGame(): GomokuGame
    {
        return $this->game;
    }
}


