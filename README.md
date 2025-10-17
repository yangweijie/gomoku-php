# PHP五子棋游戏

这是一个使用PHP和Libui库构建的跨平台五子棋游戏。游戏提供了完整的图形界面，支持双人对战。

## 功能特性

- 标准15×15棋盘
- 双人对战（黑方和白方轮流落子）
- 自动胜负判定
- 操作按钮：
  - 跳过本轮(Pass Turn)
  - 撤销上步(Undo Turn)
  - 认输(Resign)
  - 重新开始
- 获胜提示（红色连线标记和弹窗提示）

## 系统要求

- PHP 8.2 或更高版本
- PHP FFI 扩展
- libui 库（通过 `kingbes/libui` 提供）

## 安装依赖

```bash
composer install
```

## 运行游戏

```bash
php src/UI/GomokuGameDemo.php
```

## 游戏规则

1. 双方轮流在棋盘上放置黑白棋子
2. 黑方先行
3. 率先在横、竖或对角线上连成五子的一方获胜
4. 可以通过点击"认输"按钮主动认输

## 项目结构

```
src/
├── UI/
│   ├── GomokuGame.php          # 五子棋游戏核心逻辑
│   └── GomokuGameDemo.php      # 游戏演示程序
└── Core/
    └── GomokuGame.php          # 五子棋游戏核心类（备用）
```

## 技术实现

- 使用 `kingbes/libui` 和 `yangweijie/kingbes-libui-sdk` 构建图形界面
- 基于LibDrawArea抽象类实现绘图功能
- 使用FFI调用C语言的libui库实现跨平台GUI
- 实现了模板方法设计模式，便于扩展

## 开发说明

主要的五子棋游戏逻辑实现在 `src/UI/GomokuGame.php` 文件中，该类继承自LibDrawArea抽象类，实现了以下方法：

- `draw()`: 绘制棋盘、棋子和获胜连线
- `onMouseClick()`: 处理鼠标点击事件，放置棋子
- `checkWin()`: 检查是否有玩家获胜
- `resign()`: 处理认输逻辑

游戏演示程序在 `src/UI/GomokuGameDemo.php` 中，展示了如何使用GomokuGame类创建完整的游戏界面。