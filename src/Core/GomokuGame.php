<?php

namespace phpgo\Core;

class GomokuGame
{
    const EMPTY = 0;
    const BLACK = 1;
    const WHITE = 2;
    
    private int $size;
    private array $board;
    private int $currentPlayer;
    private bool $gameOver;
    private ?int $winner;
    private int $blackScore;
    private int $whiteScore;
    private array $moveHistory; // 记录棋步历史
    
    public function __construct(int $size = 15)
    {
        $this->size = $size;
        $this->blackScore = 0;
        $this->whiteScore = 0;
        $this->moveHistory = []; // 初始化棋步历史
        $this->reset();
    }
    
    public function reset(): void
    {
        $this->board = array_fill(0, $this->size, array_fill(0, $this->size, self::EMPTY));
        $this->currentPlayer = self::BLACK;
        $this->gameOver = false;
        $this->winner = null;
        $this->moveHistory = []; // 重置历史记录
    }
    
    public function newGame(): void
    {
        $this->reset();
    }
    
    public function getSize(): int
    {
        return $this->size;
    }
    
    public function getBoard(): array
    {
        return $this->board;
    }
    
    public function getCurrentPlayer(): int
    {
        return $this->currentPlayer;
    }
    
    public function isGameOver(): bool
    {
        return $this->gameOver;
    }
    
    public function getWinner(): ?int
    {
        return $this->winner;
    }
    
    public function getBlackScore(): int
    {
        return $this->blackScore;
    }
    
    public function getWhiteScore(): int
    {
        return $this->whiteScore;
    }
    
    // 添加方法来计算棋盘上黑子的数量
    public function getBlackStoneCount(): int
    {
        $count = 0;
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $this->size; $j++) {
                if ($this->board[$i][$j] === self::BLACK) {
                    $count++;
                }
            }
        }
        return $count;
    }
    
    // 添加方法来计算棋盘上白子的数量
    public function getWhiteStoneCount(): int
    {
        $count = 0;
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $this->size; $j++) {
                if ($this->board[$i][$j] === self::WHITE) {
                    $count++;
                }
            }
        }
        return $count;
    }
    
    public function makeMove(int $row, int $col): bool
    {
        // 检查游戏是否已结束
        if ($this->gameOver) {
            return false;
        }
        
        // 检查位置是否有效
        if ($row < 0 || $row >= $this->size || $col < 0 || $col >= $this->size) {
            return false;
        }
        
        // 检查位置是否为空
        if ($this->board[$row][$col] !== self::EMPTY) {
            return false;
        }
        
        // 放置棋子
        $this->board[$row][$col] = $this->currentPlayer;
        
        // 记录棋步到历史
        $this->moveHistory[] = ['row' => $row, 'col' => $col, 'player' => $this->currentPlayer];
        
        // 检查是否获胜
        if ($this->checkWin($row, $col)) {
            $this->gameOver = true;
            $this->winner = $this->currentPlayer;
            
            // 更新得分
            if ($this->currentPlayer === self::BLACK) {
                $this->blackScore++;
            } else {
                $this->whiteScore++;
            }
            
            return true;
        }
        
        // 检查是否平局（棋盘已满）
        if ($this->isBoardFull()) {
            $this->gameOver = true;
            $this->winner = self::EMPTY;
            return true;
        }
        
        // 切换玩家
        $this->currentPlayer = ($this->currentPlayer === self::BLACK) ? self::WHITE : self::BLACK;
        
        return true;
    }
    
    public function pass(): void
    {
        // 切换玩家
        $this->currentPlayer = ($this->currentPlayer === self::BLACK) ? self::WHITE : self::BLACK;
    }
    
    public function undo(): void
    {
        // 悔棋：撤销最后一步棋
        if (!empty($this->moveHistory) && !$this->gameOver) {
            // 取消最后一个棋步
            $lastMove = array_pop($this->moveHistory);
            $this->board[$lastMove['row']][$lastMove['col']] = self::EMPTY;
            
            // 恢复到之前玩家的回合
            $this->currentPlayer = $lastMove['player'];
            
            // 如果游戏已经结束（因为赢了），则撤销结束状态
            if ($this->gameOver) {
                $this->gameOver = false;
                $this->winner = null;
            }
        }
    }
    
    public function resign(): void
    {
        // 认输：当前玩家失败，对方获胜
        if (!$this->gameOver) {
            $this->gameOver = true;
            $this->winner = ($this->currentPlayer === self::BLACK) ? self::WHITE : self::BLACK;
            
            // 更新得分 - 获胜者得分
            if ($this->winner === self::BLACK) {
                $this->blackScore++;
            } else {
                $this->whiteScore++;
            }
        }
    }
    
    private function checkWin(int $row, int $col): bool
    {
        $player = $this->board[$row][$col];
        
        // 检查四个方向：水平、垂直、对角线1、对角线2
        $directions = [
            [0, 1],   // 水平
            [1, 0],   // 垂直
            [1, 1],   // 对角线1
            [1, -1]   // 对角线2
        ];
        
        foreach ($directions as $dir) {
            $count = 1; // 包括当前棋子
            
            // 向一个方向检查
            $r = $row + $dir[0];
            $c = $col + $dir[1];
            while ($r >= 0 && $r < $this->size && $c >= 0 && $c < $this->size && $this->board[$r][$c] === $player) {
                $count++;
                $r += $dir[0];
                $c += $dir[1];
            }
            
            // 向相反方向检查
            $r = $row - $dir[0];
            $c = $col - $dir[1];
            while ($r >= 0 && $r < $this->size && $c >= 0 && $c < $this->size && $this->board[$r][$c] === $player) {
                $count++;
                $r -= $dir[0];
                $c -= $dir[1];
            }
            
            // 如果连续5个棋子，获胜
            if ($count >= 5) {
                return true;
            }
        }
        
        return false;
    }
    
    private function isBoardFull(): bool
    {
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $this->size; $j++) {
                if ($this->board[$i][$j] === self::EMPTY) {
                    return false;
                }
            }
        }
        return true;
    }
}