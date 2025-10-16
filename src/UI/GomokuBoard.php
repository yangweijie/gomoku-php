<?php

namespace phpgo\UI;

use FFI;
use Kingbes\Libui\Base;
use Kingbes\Libui\App;
use Kingbes\Libui\Area;
use Kingbes\Libui\Draw;
use Kingbes\Libui\DrawBrushType;
use Kingbes\Libui\DrawFillMode;
use Kingbes\Libui\Button;
use Kingbes\Libui\Label;
use Kingbes\Libui\Box;
use Kingbes\Libui\Window;
use Kingbes\Libui\Control;
use phpgo\Core\GomokuGame;

class GomokuBoard
{
    private FFI $ffi;
    private GomokuGame $game;
    private int $boardSize;
    private int $cellSize;
    private int $borderX; // 棋盘X边框位置
    private int $borderY; // 棋盘Y边框位置
    private $areaHandler;
    private $area;
    private $parentWindow;
    private $topBox; // 顶部信息区域
    private $blackScoreLabel;
    private $whiteScoreLabel;
    private $currentPlayerLabel;
    
    public function __construct(GomokuGame $game, $parentWindow)
    {
        $this->ffi = Base::ffi();
        $this->game = $game;
        $this->parentWindow = $parentWindow;
        $this->boardSize = $game->getSize();
        $this->cellSize = 25;  // 每个格子的大小
        
        $this->createUI();
    }
    
    private function createUI(): void
    {
        // 创建垂直布局容器
        $mainBox = Box::newVerticalBox();
        Box::setPadded($mainBox, 1);
        
        // 创建顶部信息区域
        $this->createTopInfoArea($mainBox);
        
        // 创建棋盘区域
        $this->createBoardArea($mainBox);
        
        // 设置主容器为窗口的子元素
        Window::setChild($this->parentWindow, $mainBox);
    }
    
    private function createTopInfoArea($mainBox): void
    {
        // 创建顶部水平布局容器
        $topBox = Box::newHorizontalBox();
        Box::setPadded($topBox, 1);
        
        // 左侧：玩家得分信息
        $leftBox = Box::newVerticalBox();
        Box::setPadded($leftBox, 1);
        
        $this->blackScoreLabel = Label::create("Black Score: 0");
        $this->whiteScoreLabel = Label::create("White Score: 0");
        
        Box::append($leftBox, $this->blackScoreLabel, 0);
        Box::append($leftBox, $this->whiteScoreLabel, 0);
        
        // 中间：当前玩家信息
        $centerBox = Box::newVerticalBox();
        Box::setPadded($centerBox, 1);
        
        // 创建当前玩家标签和指示器的水平容器
        $playerBox = Box::newHorizontalBox();
        Box::setPadded($playerBox, 1);
        
        $this->currentPlayerLabel = Label::create("Current Player: Black");
        Box::append($playerBox, $this->currentPlayerLabel, 0);
        
        // 添加当前玩家指示器（彩色圆点）
        $indicatorLabel = Label::create(" ●"); // 彩色圆点
        Box::append($playerBox, $indicatorLabel, 0);
        
        Box::append($centerBox, $playerBox, 0);
        
        // 右侧：操作按钮 (Undo turn 和 Resign)
        $rightBox = Box::newVerticalBox();
        Box::setPadded($rightBox, 1);
        
        $undoButton = Button::create("Undo turn");
        $resignButton = Button::create("Resign");
        
        // 绑定按钮事件
        Button::onClicked($undoButton, function() {
            $this->game->undo();
            $this->updateUI();
        }, null);
        
        Button::onClicked($resignButton, function() {
            // 认输：当前玩家失败，对方获胜
            $this->game->resign();
            $this->updateUI();
        }, null);
        
        Box::append($rightBox, $undoButton, 0);
        Box::append($rightBox, $resignButton, 0);
        
        // 将三个部分添加到顶部区域
        Box::append($topBox, $leftBox, 0);
        Box::append($topBox, $centerBox, 1); // 中间部分可拉伸
        Box::append($topBox, $rightBox, 0);
        
        // 将顶部区域添加到主容器
        Box::append($mainBox, $topBox, 0);
    }
    
    private function createBoardArea($mainBox): void
    {
        // 创建"Pass Turn"按钮，放在棋盘上方
        $passButton = Button::create("Pass Turn");
        Button::onClicked($passButton, function() {
            $this->game->pass();
            $this->updateUI();
        }, null);
        
        // 创建包含"Pass Turn"按钮的水平容器，使其居中
        $passButtonBox = Box::newHorizontalBox();
        Box::setPadded($passButtonBox, 1);
        Box::append($passButtonBox, $passButton, 1); // 居中
        
        // 将"Pass Turn"按钮添加到主容器
        Box::append($mainBox, $passButtonBox, 0);
        
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
        
        // 将棋盘区域添加到主容器
        Box::append($mainBox, $this->area, 1); // 棋盘区域可拉伸
    }
    
    private function drawCallback($handler, $area, $params): void
    {
        $width = $params->AreaWidth;
        $height = $params->AreaHeight;
        
        // 绘制浅灰色背景（整个窗口）
        $backgroundBrush = Draw::createBrush(DrawBrushType::Solid, 240/255, 240/255, 240/255, 1.0); // 浅灰色
        $backgroundPath = Draw::createPath(DrawFillMode::Winding);
        Draw::pathAddRectangle($backgroundPath, 0, 0, $width, $height);
        Draw::pathEnd($backgroundPath);
        $this->ffi->uiDrawFill($params->Context, $backgroundPath, $backgroundBrush);
        Draw::freePath($backgroundPath);
        
        // 绘制棋盘
        $this->drawBoard($params->Context, $width, $height);
        
        // 绘制棋子
        $this->drawPieces($params->Context);
    }
    
    private function drawBoard($context, $width, $height): void
    {
        // 计算棋盘的大小和位置，使其居中
        // 为确保棋子在正确的交叉点上，使用 (boardSize-1) * cellSize 作为可视区域
        $boardWidth = ($this->boardSize - 1) * $this->cellSize; // 实际可视网格区域
        $boardHeight = ($this->boardSize - 1) * $this->cellSize; // 实际可视网格区域
        
        // 计算棋盘位置（居中）
        $boardX = intval(($width - $boardWidth) / 2);
        $boardY = intval(($height - $boardHeight) / 2);
        
        // 更新边框位置
        $this->borderX = $boardX;
        $this->borderY = $boardY;
        
        // 创建棋盘背景画刷（#E3D590）
        $boardBrush = Draw::createBrush(DrawBrushType::Solid, 227/255, 213/255, 144/255, 1.0);
        $boardPath = Draw::createPath(DrawFillMode::Winding);
        Draw::pathAddRectangle($boardPath, $this->borderX, $this->borderY, $boardWidth, $boardHeight);
        Draw::pathEnd($boardPath);
        $this->ffi->uiDrawFill($context, $boardPath, $boardBrush);
        Draw::freePath($boardPath);
        
        // 创建棋盘线条的画刷（使用非常深的黑色以确保高对比度）
        $lineBrush = Draw::createBrush(DrawBrushType::Solid, 0.0, 0.0, 0.0, 1.0);
        
        // 绘制垂直线 - 使用矩形填充方式替代描边
        for ($i = 0; $i < $this->boardSize; $i++) {
            $x = $this->borderX + $i * $this->cellSize;
            $y1 = $this->borderY;
            $y2 = $this->borderY + $boardHeight;
            
            // 使用矩形来绘制线条，确保线条可见
            $linePath = Draw::createPath(DrawFillMode::Winding);
            Draw::pathAddRectangle($linePath, $x - 1.0, $y1, 1, $y2 - $y1); // 2像素宽的矩形
            Draw::pathEnd($linePath);
            $this->ffi->uiDrawFill($context, $linePath, $lineBrush);
            Draw::freePath($linePath);
        }
        
        // 绘制水平线 - 使用矩形填充方式替代描边
        for ($i = 0; $i < $this->boardSize; $i++) {
            $y = $this->borderY + $i * $this->cellSize;
            $x1 = $this->borderX;
            $x2 = $this->borderX + $boardWidth;
            
            // 使用矩形来绘制线条，确保线条可见
            $linePath = Draw::createPath(DrawFillMode::Winding);
            Draw::pathAddRectangle($linePath, $x1, $y - 1.0, $x2 - $x1, 1); // 2像素高的矩形
            Draw::pathEnd($linePath);
            $this->ffi->uiDrawFill($context, $linePath, $lineBrush);
            Draw::freePath($linePath);
        }
        
        // 绘制天元和星位点
        $this->drawStarPoints($context);
    }
    
    private function drawStarPoints($context): void
    {
        // 星位点位置（15路棋盘）
        $starPoints = [];
        
        if ($this->boardSize === 15) {
            $starPoints = [
                [3, 3], [3, 7], [3, 11],
                [7, 3], [7, 7], [7, 11],
                [11, 3], [11, 7], [11, 11]
            ];
        } elseif ($this->boardSize === 19) {
            $starPoints = [
                [3, 3], [3, 9], [3, 15],
                [9, 3], [9, 9], [9, 15],
                [15, 3], [15, 9], [15, 15]
            ];
        }
        
        $dotBrush = Draw::createBrush(DrawBrushType::Solid, 0, 0, 0, 1.0);  // 黑色
        
        foreach ($starPoints as $point) {
            $x = $this->borderX + $point[0] * $this->cellSize;
            $y = $this->borderY + $point[1] * $this->cellSize;
            
            // 绘制圆形星位点
            $path = Draw::createPath(DrawFillMode::Winding);
            Draw::createPathFigureWithArc($path, $x, $y, 3, 0, 360, false);
            Draw::pathEnd($path);
            $this->ffi->uiDrawFill($context, $path, $dotBrush);
            Draw::freePath($path);
        }
    }
    
    private function drawPieces($context): void
    {
        $board = $this->game->getBoard();
        
        for ($row = 0; $row < $this->boardSize; $row++) {
            for ($col = 0; $col < $this->boardSize; $col++) {
                $piece = $board[$row][$col];
                if ($piece !== GomokuGame::EMPTY) {
                    $this->drawPiece($context, $row, $col, $piece);
                }
            }
        }
    }
    
    private function drawPiece($context, int $row, int $col, int $piece): void
    {
        $x = $this->borderX + $col * $this->cellSize;
        $y = $this->borderY + $row * $this->cellSize;
        $radius = 11; // 棋子半径
        
        $brush = null;
        if ($piece === GomokuGame::BLACK) {
            // 黑子 - 纯黑色
            $brush = Draw::createBrush(DrawBrushType::Solid, 0.0, 0.0, 0.0, 1.0);
        } elseif ($piece === GomokuGame::WHITE) {
            // 白子 - 纯白色
            $brush = Draw::createBrush(DrawBrushType::Solid, 1.0, 1.0, 1.0, 1.0);
        }
        
        if ($brush !== null) {
            // 绘制圆形棋子
            $path = Draw::createPath(DrawFillMode::Winding);
            Draw::createPathFigureWithArc($path, $x, $y, $radius, 0, 360, false);
            Draw::pathEnd($path);
            $this->ffi->uiDrawFill($context, $path, $brush);
            Draw::freePath($path);
        }
    }
    
    private function mouseEventCallback($handler, $area, $mouseEvent): void
    {
        // 处理鼠标点击事件
        if ($mouseEvent->Down && $mouseEvent->Count === 1) {
            $this->handleClick($mouseEvent->X, $mouseEvent->Y);
        }
    }
    
    private function handleClick(float $x, float $y): void
    {
        // 计算棋盘边界 - 为确保棋子不落在边缘线上，稍微缩小有效区域
        $boardRight = $this->borderX + ($this->boardSize - 1) * $this->cellSize;
        $boardBottom = $this->borderY + ($this->boardSize - 1) * $this->cellSize;
        
        // 设置一个安全边界，确保棋子不会落在边缘线上
        $safeMargin = $this->cellSize * 0.4; // 使用小于半个格子的边距
        
        // 检查点击是否在安全范围内
        if ($x < $this->borderX + $safeMargin || $x > $boardRight - $safeMargin ||
            $y < $this->borderY + $safeMargin || $y > $boardBottom - $safeMargin) {
            return; // 点击超出安全范围，不处理
        }
        
        // 将点击坐标转换为棋盘坐标
        $col = (int)round(($x - $this->borderX) / $this->cellSize);
        $row = (int)round(($y - $this->borderY) / $this->cellSize);
        
        // 确保坐标在有效的内部网格范围内（1 到 boardSize-2）
        if ($row >= 1 && $row < $this->boardSize - 1 && $col >= 1 && $col < $this->boardSize - 1) {
            // 尝试下棋
            if ($this->game->makeMove($row, $col)) {
                // 重绘棋盘
                if ($this->area) {
                    Area::queueRedraw($this->area);
                }
            }
        }
    }
    
    private function updateUI(): void
    {
        // 更新得分标签 - 显示当前棋盘上棋子的数量
        \Kingbes\Libui\Label::setText($this->blackScoreLabel, "Black Score: " . $this->game->getBlackStoneCount());
        \Kingbes\Libui\Label::setText($this->whiteScoreLabel, "White Score: " . $this->game->getWhiteStoneCount());
        
        // 更新当前玩家标签
        $currentPlayer = $this->game->getCurrentPlayer();
        if ($currentPlayer === GomokuGame::BLACK) {
            \Kingbes\Libui\Label::setText($this->currentPlayerLabel, "Current Player: Black");
        } else {
            \Kingbes\Libui\Label::setText($this->currentPlayerLabel, "Current Player: White");
        }
    }
    
    private function keyEventCallback($handler, $area, $keyEvent): int
    {
        return 0;
    }
    
    private function mouseCrossedCallback($handler, $area, $left): void
    {
        // 鼠标交叉事件处理
    }
    
    private function dragBrokenCallback($handler, $area): void
    {
        // 拖拽中断事件处理
    }
    
    public function getArea()
    {
        return $this->area;
    }
}