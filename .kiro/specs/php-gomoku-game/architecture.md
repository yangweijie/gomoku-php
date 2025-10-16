# PHP 五子棋游戏架构设计

## 概述

本项目旨在使用 PHP 和 kingbes/libui 库创建一个跨平台的五子棋游戏 GUI 应用。架构设计将参考 rubygo 项目的实现和 .kiro/specs/php-gomoku-game/tasks.md 中的计划。

## 架构模式

采用经典的 MVC (Model-View-Controller) 架构模式来组织代码：

- **Model (模型)**: 负责游戏的核心逻辑和数据，包括棋盘状态、玩家信息、游戏规则等。
- **View (视图)**: 负责用户界面的展示，使用 kingbes/libui SDK 创建图形界面。
- **Controller (控制器)**: 作为模型和视图的中介，处理用户输入并更新模型和视图。

## 目录结构

```
src/
├── Core/              # 核心游戏逻辑
│   ├── Player.php     # 玩家模型
│   ├── Board.php      # 棋盘模型
│   ├── GameRules.php  # 游戏规则工具类
│   └── GameState.php  # 游戏状态管理
├── UI/                # 用户界面组件
│   ├── MainWindow.php # 主窗口
│   ├── GameBoard.php  # 棋盘视图
│   ├── Menu.php       # 菜单系统
│   └── Dialogs/       # 对话框 (NewGameDialog, GameOverDialog)
├── Controller/        # 控制器
│   └── GameController.php # 游戏控制器
├── Utils/             # 工具类
│   ├── CrossPlatform.php # 跨平台工具
│   └── ErrorHandler.php  # 错误处理
└── GomokuApp.php      # 主应用类
```

## 核心组件

### 1. 模型层 (Model)

- **Player**: 代表玩家，包含玩家名称、颜色等属性。
- **Board**: 代表棋盘，管理棋子的位置和状态。
- **GameRules**: 包含游戏规则，如判断胜负的逻辑。
- **GameState**: 管理游戏的整体状态，包括当前玩家、游戏历史、胜负判断等。

### 2. 视图层 (View)

- **MainWindow**: 应用的主窗口，包含菜单栏和状态栏。
- **GameBoard**: 棋盘的图形表示，负责绘制棋盘和棋子。
- **Menu**: 菜单系统，提供新建游戏、悔棋等选项。
- **Dialogs**: 各种对话框，如新游戏设置、游戏结束提示等。

### 3. 控制器层 (Controller)

- **GameController**: 协调模型和视图，处理用户输入，更新游戏状态。

### 4. 工具类 (Utils)

- **CrossPlatform**: 处理跨平台相关的功能，如路径管理。
- **ErrorHandler**: 统一的错误处理机制。

## 技术选型

- **核心语言**: PHP 8.2+
- **GUI 库**: kingbes/libui (通过 FFI) 和 yangweijie/kingbes-libui-sdk (面向对象封装)
- **构建工具**: Box (用于打包 PHAR)
- **测试框架**: PestPHP

## 集成与流程

1. `GomokuApp` 初始化应用，创建 `GameController` 和 `MainWindow`。
2. `GameController` 管理 `GameState` 和处理游戏逻辑。
3. `MainWindow` 和其子组件 (`GameBoard`, `Menu`) 负责 UI 展示。
4. 用户交互通过 `GameController` 处理，更新 `GameState` 并刷新 UI。

## 开发约定

- 遵循 PSR-4 自动加载标准。
- 使用面向对象的设计原则。
- 通过 Composer 管理依赖。
- 使用 kingbes-libui-sdk 提供的 API 构建 GUI。