<?php

namespace phpgo\UI;

use Kingbes\Libui\SDK\LibDrawArea;
use FFI\CData;
use Kingbes\Libui\Draw;
use Kingbes\Libui\DrawBrushType;
use Kingbes\Libui\DrawFillMode;
use Kingbes\Libui\Window;

/**
 * 五子棋游戏类 - 基于LibDrawArea抽象类实现
 * 
 * 实现功能：
 * 1. 15×15标准五子棋棋盘绘制
 * 2. 棋子放置与显示
 * 3. 胜负判定算法
 * 4. 玩家轮流机制
 * 5. 操作按钮功能（通过外部按钮控制）
 */
class GomokuGame extends LibDrawArea
{
    // 棋盘大小
    private int $boardSize = 15;
    
    // 棋盘格子大小（像素）
    private int $cellSize = 30;
    
    // 棋盘宽度和高度（像素）
    private int $boardWidth;
    private int $boardHeight;
    
    // 棋盘偏移量（使棋盘居中）
    private float $offsetX;
    private float $offsetY;
    
    // 棋盘状态数组（0=空，1=黑子，2=白子）
    private array $board = [];
    
    // 当前玩家（1=黑方，2=白方）
    private int $currentPlayer = 1;
    
    // 游戏历史记录（用于撤销功能）
    private array $history = [];
    
    // 游戏状态（0=进行中，1=黑方获胜，2=白方获胜，3=平局）
    private int $gameStatus = 0;
    
    // 获胜信息
    private array $winInfo = [];
    
    // 父窗口引用
    private ?CData $parentWindow = null;

    public function __construct(int $width = 500, int $height = 500) {
        // 计算棋盘尺寸和偏移
        $this->boardWidth = ($this->boardSize - 1) * $this->cellSize;
        $this->boardHeight = ($this->boardSize - 1) * $this->cellSize;
        $this->offsetX = ($width - $this->boardWidth) / 2;
        $this->offsetY = ($height - $this->boardHeight) / 2;
        
        // 初始化棋盘数组
        $this->initializeBoard();
        
        parent::__construct($width, $height);
    }
    
    /**
     * 设置父窗口
     */
    public function setParentWindow(CData $window): void {
        $this->parentWindow = $window;
    }

    /**
     * 初始化棋盘数组
     */
    private function initializeBoard(): void {
        $this->board = [];
        for ($row = 0; $row < $this->boardSize; $row++) {
            $this->board[$row] = array_fill(0, $this->boardSize, 0);
        }
        $this->history = [];
        $this->gameStatus = 0;
        $this->winInfo = [];
    }

    /**
     * 实现抽象方法：绘制棋盘和棋子
     */
    protected function draw(CData $params): void {
        $width = $params->AreaWidth;
        $height = $params->AreaHeight;

        // 1. 绘制背景
        $this->drawBackground($params, $width, $height);
        
        // 2. 绘制棋盘
        $this->drawBoard($params);
        
        // 3. 绘制棋子
        $this->drawStones($params);
        
        // 4. 绘制获胜连线（如果有）
        $this->drawWinLine($params);
        
        // 5. 绘制获胜提示（如果有）
        $this->drawWinMessage($params);
    }

    /**
     * 绘制背景
     */
    private function drawBackground(CData $params, float $width, float $height): void {
        $backgroundBrush = Draw::createBrush(DrawBrushType::Solid, 240/255, 240/255, 240/255, 1.0); // 浅灰色
        $backgroundPath = Draw::createPath(DrawFillMode::Winding);
        Draw::pathAddRectangle($backgroundPath, 0, 0, $width, $height);
        Draw::pathEnd($backgroundPath);
        Draw::fill($params, $backgroundPath, $backgroundBrush);
    }

    /**
     * 绘制棋盘
     */
    private function drawBoard(CData $params): void {
        // 绘制棋盘背景
        $boardBrush = Draw::createBrush(DrawBrushType::Solid, 227/255, 180/255, 99/255, 1.0); // 木质颜色
        $boardPath = Draw::createPath(DrawFillMode::Winding);
        Draw::pathAddRectangle($boardPath, $this->offsetX - 10, $this->offsetY - 10, $this->boardWidth + 20, $this->boardHeight + 20);
        Draw::pathEnd($boardPath);
        Draw::fill($params, $boardPath, $boardBrush);
        
        // 绘制网格线
        $lineBrush = Draw::createBrush(DrawBrushType::Solid, 0.0, 0.0, 0.0, 1.0); // 黑色

        // 绘制垂直线
        for ($i = 0; $i < $this->boardSize; $i++) {
            $x = $this->offsetX + $i * $this->cellSize;
            $y1 = $this->offsetY;
            $y2 = $this->offsetY + $this->boardHeight;
            
            $linePath = Draw::createPath(DrawFillMode::Winding);
            Draw::pathAddRectangle($linePath, $x, $y1, 1, $y2 - $y1);
            Draw::pathEnd($linePath);
            Draw::fill($params, $linePath, $lineBrush);
        }

        // 绘制水平线
        for ($i = 0; $i < $this->boardSize; $i++) {
            $y = $this->offsetY + $i * $this->cellSize;
            $x1 = $this->offsetX;
            $x2 = $this->offsetX + $this->boardWidth;
            
            $linePath = Draw::createPath(DrawFillMode::Winding);
            Draw::pathAddRectangle($linePath, $x1, $y, $x2 - $x1, 1);
            Draw::pathEnd($linePath);
            Draw::fill($params, $linePath, $lineBrush);
        }
        
        // 绘制天元和星位点
        $this->drawStarPoints($params);
    }

    /**
     * 绘制天元和星位点
     */
    private function drawStarPoints(CData $params): void {
        $pointBrush = Draw::createBrush(DrawBrushType::Solid, 0.0, 0.0, 0.0, 1.0); // 黑色
        $positions = [
            [3, 3], [3, 11], [11, 3], [11, 11], // 四个星位
            [7, 7] // 天元
        ];
        
        foreach ($positions as [$row, $col]) {
            $x = $this->offsetX + $col * $this->cellSize;
            $y = $this->offsetY + $row * $this->cellSize;
            
            $pointPath = Draw::createPath(DrawFillMode::Winding);
            Draw::createPathFigureWithArc($pointPath, $x, $y, 3, 0, 360, false);
            Draw::pathEnd($pointPath);
            Draw::fill($params, $pointPath, $pointBrush);
        }
    }

    /**
     * 绘制棋子
     */
    private function drawStones(CData $params): void {
        for ($row = 0; $row < $this->boardSize; $row++) {
            for ($col = 0; $col < $this->boardSize; $col++) {
                if ($this->board[$row][$col] !== 0) {
                    $this->drawStone($params, $row, $col, $this->board[$row][$col]);
                }
            }
        }
    }

    /**
     * 绘制单个棋子
     */
    private function drawStone(CData $params, int $row, int $col, int $player): void {
        $x = $this->offsetX + $col * $this->cellSize;
        $y = $this->offsetY + $row * $this->cellSize;
        $radius = $this->cellSize / 2 - 2;

        // 创建圆形路径
        $stonePath = Draw::createPath(DrawFillMode::Winding);
        Draw::createPathFigureWithArc($stonePath, $x, $y, $radius, 0, 360, false);
        Draw::pathEnd($stonePath);

        // 根据玩家选择颜色
        if ($player === 1) {
            $stoneBrush = Draw::createBrush(DrawBrushType::Solid, 0.0, 0.0, 0.0, 1.0); // 黑子
        } else {
            $stoneBrush = Draw::createBrush(DrawBrushType::Solid, 1.0, 1.0, 1.0, 1.0); // 白子
        }

        Draw::fill($params, $stonePath, $stoneBrush);
        
        // 绘制边框（白子）
        if ($player === 2) {
            $borderBrush = Draw::createBrush(DrawBrushType::Solid, 0.0, 0.0, 0.0, 1.0); // 黑色边框
            $strokeParams = Draw::createStrokeParams(
                \Kingbes\Libui\DrawLineCap::Flat,
                \Kingbes\Libui\DrawLineJoin::Miter,
                \Kingbes\Libui\DrawLineJoin::Miter,
                1.0 // 边框宽度
            );
            Draw::Stroke($params, $stonePath, $borderBrush, $strokeParams);
        }
    }

    /**
     * 绘制获胜连线
     */
    private function drawWinLine(CData $params): void {
        if ($this->gameStatus === 1 || $this->gameStatus === 2) {
            $lineBrush = Draw::createBrush(DrawBrushType::Solid, 1.0, 0.0, 0.0, 1.0); // 红色
            $start = $this->winInfo['start'];
            $end = $this->winInfo['end'];
            
            $startX = $this->offsetX + $start[1] * $this->cellSize;
            $startY = $this->offsetY + $start[0] * $this->cellSize;
            $endX = $this->offsetX + $end[1] * $this->cellSize;
            $endY = $this->offsetY + $end[0] * $this->cellSize;
            
            // 创建更粗的获胜连线，使用矩形而不是线条
            $lineWidth = 8.0; // 增加宽度
            $dx = $endX - $startX;
            $dy = $endY - $startY;
            $length = sqrt($dx * $dx + $dy * $dy);
            
            if ($length > 0) {
                // 计算垂直于连线方向的偏移量
                $perpX = -$dy / $length * $lineWidth / 2;
                $perpY = $dx / $length * $lineWidth / 2;
                
                // 创建四边形路径
                $linePath = Draw::createPath(DrawFillMode::Winding);
                Draw::createPathFigure($linePath, $startX + $perpX, $startY + $perpY);
                Draw::pathLineTo($linePath, $endX + $perpX, $endY + $perpY);
                Draw::pathLineTo($linePath, $endX - $perpX, $endY - $perpY);
                Draw::pathLineTo($linePath, $startX - $perpX, $startY - $perpY);
                Draw::pathCloseFigure($linePath);
                Draw::pathEnd($linePath);
                
                Draw::fill($params, $linePath, $lineBrush);
            }
        }
    }

    /**
     * 绘制获胜提示信息
     */
    private function drawWinMessage(CData $params): void {
        // 暂时不实现文本绘制，避免FFI复杂调用导致的错误
        // 可以通过其他方式提示获胜状态，比如改变窗口标题或添加一个获胜标记
    }

    /**
     * 实现抽象方法：处理鼠标点击事件
     */
    protected function onMouseClick(float $x, float $y, CData $mouseEvent): void {
        // 只有在游戏进行中才能落子
        if ($this->gameStatus !== 0) {
            return;
        }
        
        // 计算点击的棋盘格子
        $col = (int) round(($x - $this->offsetX) / $this->cellSize);
        $row = (int) round(($y - $this->offsetY) / $this->cellSize);

        // 验证点击是否在有效范围内
        if ($this->isValidPosition($row, $col)) {
            // 检查该位置是否为空
            if ($this->board[$row][$col] === 0) {
                // 放置棋子
                $this->placeStone($row, $col, $this->currentPlayer);
                
                // 检查是否获胜
                if ($this->checkWin($row, $col)) {
                    $this->gameStatus = $this->currentPlayer;
                    // 先重绘棋盘显示获胜连线
                    $this->redraw();
                    // 延迟弹出获胜消息，让用户能看到获胜连线
                    $this->delayShowGameOverMessage();
                } else {
                    // 切换玩家
                    $this->switchPlayer();
                    // 重绘棋盘
                    $this->redraw();
                }
            }
        }
    }

    /**
     * 验证位置是否有效
     */
    private function isValidPosition(int $row, int $col): bool {
        return $row >= 0 && $row < $this->boardSize && $col >= 0 && $col < $this->boardSize;
    }

    /**
     * 放置棋子
     */
    private function placeStone(int $row, int $col, int $player): void {
        $this->board[$row][$col] = $player;
        // 记录历史
        $this->history[] = ['row' => $row, 'col' => $col, 'player' => $player];
    }

    /**
     * 切换当前玩家
     */
    private function switchPlayer(): void {
        $this->currentPlayer = ($this->currentPlayer === 1) ? 2 : 1;
    }

    /**
     * 获取当前玩家
     */
    public function getCurrentPlayer(): int {
        return $this->currentPlayer;
    }

    /**
     * 获取棋盘状态
     */
    public function getBoard(): array {
        return $this->board;
    }

    /**
     * 获取游戏状态
     */
    public function getGameStatus(): int {
        return $this->gameStatus;
    }

    /**
     * 重新开始游戏
     */
    public function resetGame(): void {
        $this->initializeBoard();
        $this->currentPlayer = 1;
        $this->redraw();
    }

    /**
     * 跳过本轮落子
     */
    public function passTurn(): void {
        // 在五子棋中，跳过落子通常不适用，但我们可以实现为切换玩家
        if ($this->gameStatus === 0) {
            $this->switchPlayer();
            $this->redraw();
        }
    }

    /**
     * 撤销上一步棋
     */
    public function undoTurn(): void {
        // 只有在游戏进行中才能撤销
        if ($this->gameStatus !== 0 || empty($this->history)) {
            return;
        }
        
        // 获取最后一步棋的信息
        $lastMove = array_pop($this->history);
        $row = $lastMove['row'];
        $col = $lastMove['col'];
        $player = $lastMove['player'];
        
        // 恢复棋盘状态
        $this->board[$row][$col] = 0;
        
        // 如果还有历史记录，恢复当前玩家
        if (!empty($this->history)) {
            $previousMove = end($this->history);
            $this->currentPlayer = $previousMove['player'];
        } else {
            // 如果没有历史记录，恢复到初始玩家（黑方）
            $this->currentPlayer = 1;
        }
        
        // 重置游戏状态
        $this->gameStatus = 0;
        $this->winInfo = [];
        
        $this->redraw();
    }

    /**
     * 认输
     */
    public function resign(): void {
        // 只有在游戏进行中才能认输
        if ($this->gameStatus === 0) {
            // 当前玩家认输，对方获胜
            $this->gameStatus = ($this->currentPlayer === 1) ? 2 : 1;
            // 设置获胜信息（在中心位置显示一条水平线）
            $this->winInfo = [
                'start' => [7, 5],
                'end' => [7, 9]
            ];
            $this->redraw();
            // 延迟弹出获胜消息，让用户能看到获胜连线
            $this->delayShowGameOverMessage();
        }
    }

    /**
     * 显示游戏结束消息
     */
    private function showGameOverMessage(): void {
        if ($this->parentWindow !== null && $this->gameStatus !== 0) {
            $message = $this->gameStatus === 1 ? "黑方获胜!" : "白方获胜!";
            Window::msgBox($this->parentWindow, "游戏结束", $message);
        }
    }
    
    /**
     * 延迟显示游戏结束消息
     */
    private function delayShowGameOverMessage(): void {
        // 使用定时器延迟弹出消息框，让用户能看到获胜连线
        \Kingbes\Libui\App::timer(500, function() {
            $this->showGameOverMessage();
        });
    }

    /**
     * 检查是否有玩家获胜
     */
    public function checkWin(int $row, int $col): bool {
        $player = $this->board[$row][$col];
        if ($player === 0) return false;

        // 检查四个方向：水平、垂直、对角线
        $directions = [
            [[0, 1], [0, -1]],   // 水平
            [[1, 0], [-1, 0]],   // 垂直
            [[1, 1], [-1, -1]],  // 主对角线
            [[1, -1], [-1, 1]]   // 副对角线
        ];

        foreach ($directions as $direction) {
            $count = 1; // 包括当前位置
            $start = [$row, $col];
            $end = [$row, $col];
            
            // 检查两个方向
            foreach ($direction as [$dr, $dc]) {
                $r = $row + $dr;
                $c = $col + $dc;
                
                while ($this->isValidPosition($r, $c) && $this->board[$r][$c] === $player) {
                    $count++;
                    // 更新起始或结束位置
                    if ($dr > 0 || ($dr === 0 && $dc > 0)) {
                        $end = [$r, $c];
                    } else {
                        $start = [$r, $c];
                    }
                    $r += $dr;
                    $c += $dc;
                }
            }
            
            if ($count >= 5) {
                $this->winInfo = ['start' => $start, 'end' => $end];
                return true;
            }
        }
        
        return false;
    }
}