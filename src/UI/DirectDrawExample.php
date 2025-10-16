<?php

namespace phpgo\UI;

use Kingbes\Libui\Base;
use Kingbes\Libui\App;
use Kingbes\Libui\Window;
use Kingbes\Libui\Area;
use Kingbes\Libui\Draw;
use Kingbes\Libui\DrawFillMode;
use Kingbes\Libui\DrawBrushType;

class DirectDrawExample
{
    private $ffi;
    private $window;
    private $area;
    private $areaHandler;

    public function __construct()
    {
        // 初始化FFI
        $this->ffi = Base::ffi();
        
        // 初始化应用程序
        App::init();
        
        // 创建窗口
        $this->window = Window::create("Direct Draw Example", 600, 400, true);
        \Kingbes\Libui\Control::show($this->window);
    }

    public function createDrawArea()
    {
        // 创建AreaHandler
        $this->areaHandler = Area::handler(
            function ($handler, $area, $params) {
                $this->drawCallback($handler, $area, $params);
            },
            function ($handler, $area, $keyEvent) {
                return $this->keyEventCallback($handler, $area, $keyEvent);
            },
            function ($handler, $area, $mouseEvent) {
                $this->mouseEventCallback($handler, $area, $mouseEvent);
            },
            function ($handler, $area, $left) {
                $this->mouseCrossedCallback($handler, $area, $left);
            },
            function ($handler, $area) {
                $this->dragBrokenCallback($handler, $area);
            }
        );
        
        // 创建Area
        $this->area = Area::create($this->areaHandler);
        
        // 将Area设置为窗口的子元素
        Window::setChild($this->window, $this->area);
        
        return $this;
    }

    private function drawCallback($handler, $area, $params)
    {
        // 创建画刷
        $brush = Draw::createBrush(DrawBrushType::Solid, 0.8, 0.8, 0.8, 1.0);
        
        // 创建路径
        $path = Draw::createPath(DrawFillMode::Winding);
        
        // 添加矩形
        Draw::pathAddRectangle($path, 0, 0, $params->AreaWidth, $params->AreaHeight);
        Draw::pathEnd($path);
        
        // 填充背景
        $this->ffi->uiDrawFill($params->Context, $path, $brush);
        
        // 释放资源
        Draw::freePath($path);
        
        // 绘制一个红色矩形
        $brush = Draw::createBrush(DrawBrushType::Solid, 1.0, 0.0, 0.0, 1.0);
        
        $path = Draw::createPath(DrawFillMode::Winding);
        Draw::pathAddRectangle($path, 50, 50, 100, 100);
        Draw::pathEnd($path);
        
        $this->ffi->uiDrawFill($params->Context, $path, $brush);
        
        Draw::freePath($path);
    }

    private function mouseEventCallback($handler, $area, $mouseEvent)
    {
        // 简单的鼠标事件处理
        if ($mouseEvent->Down) {
            echo "Mouse down at: {$mouseEvent->X}, {$mouseEvent->Y}\n";
        }
    }

    private function keyEventCallback($handler, $area, $keyEvent)
    {
        // 键盘事件处理
        return 0; // 不处理
    }

    private function mouseCrossedCallback($handler, $area, $left)
    {
        // 鼠标交叉事件处理
    }

    private function dragBrokenCallback($handler, $area)
    {
        // 拖拽中断事件处理
    }

    public function run()
    {
        // 设置窗口关闭回调
        Window::onClosing($this->window, function ($window) {
            App::quit();
            return 1;
        });
        
        // 运行主循环
        App::main();
    }
}
