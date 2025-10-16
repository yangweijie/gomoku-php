# 项目概述

这是一个使用 PHP 和 libui 库构建的跨平台 GUI 应用程序，名为 "OHA GUI Tool"。该应用旨在提供一个图形界面来执行 HTTP 负载测试，基于 `oha` 命令行工具。

## 核心功能

- **图形用户界面**: 使用 `kingbes/libui` 和 `yangweijie/kingbes-libui-sdk` 构建的跨平台桌面 GUI。
- **HTTP 负载测试**: 集成 `oha` 工具，用于执行 HTTP 负载测试。
- **跨平台支持**: 支持 Windows、Linux 和 macOS 操作系统。
- **自动依赖管理**: 自动检查和下载 `oha` 二进制文件（如果未在系统 PATH 中找到）。

## 项目结构

```
phpgo/
├── kingbes/
│   └── libui/              # 自定义的 kingbes/libui 库
│       ├── lib/            # 平台相关的 libui 库文件
│       └── src/            # PHP FFI 封装代码
├── scripts/                # 辅助脚本
├── src/                    # 主应用程序源代码（当前为空）
├── vendor/                 # Composer 依赖
├── main.php               # 应用程序入口点
├── composer.json          # 项目依赖和配置
└── README.md              # 项目说明文件
```

## 主要组件

- **main.php**: 应用程序的入口点，负责初始化、依赖检查和启动 GUI 应用。
- **kingbes/libui**: 一个 PHP FFI 封装库，用于与 libui C 库交互。
- **yangweijie/kingbes-libui-sdk**: 对 `kingbes/libui` 的面向对象封装，提供更易用的 API。
- **oha**: 外部的 HTTP 负载测试工具，由应用程序自动下载或从系统 PATH 中查找。

# 构建和运行

## 系统要求

- PHP 8.2 或更高版本
- PHP FFI 扩展
- libui 库（通过 `kingbes/libui` 提供）
- oha 二进制文件（可自动下载）

## 安装依赖

```bash
composer install
```

此命令会自动执行 `scripts/patchBase.php` 脚本，将自定义的 `Base.php` 文件复制到 `vendor/kingbes/libui/src/` 目录。

## 运行应用

```bash
php main.php
```

## 命令行选项

- `--help`, `-h`: 显示帮助信息
- `--version`, `-v`: 显示版本信息
- `--check`: 检查系统依赖

## 开发约定

- 使用 PSR-4 自动加载标准
- 主应用程序代码位于 `src/` 目录下（当前为空，主要逻辑在 `main.php` 中）
- 使用 Composer 管理依赖
- 使用 `kingbes/libui-sdk` 提供的面向对象 API 构建 GUI