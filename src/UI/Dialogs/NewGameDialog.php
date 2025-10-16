<?php

declare(strict_types=1);

namespace phpgo\UI\Dialogs;

use Kingbes\Libui\SDK\LibuiApplication;
use Kingbes\Libui\SDK\LibuiWindow;
use Kingbes\Libui\SDK\LibuiVBox;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiGroup;
use Kingbes\Libui\SDK\LibuiLabel;
use Kingbes\Libui\SDK\LibuiSpinbox;
use Kingbes\Libui\SDK\LibuiButton;

/**
 * 新游戏对话框类
 * 
 * 允许用户配置新游戏的设置，如棋盘大小。
 */
class NewGameDialog
{
    /**
     * @var LibuiWindow 对话框窗口
     */
    private LibuiWindow $dialog;

    /**
     * @var int 棋盘大小
     */
    private int $boardSize;

    /**
     * @var callable|null 确认回调函数
     */
    private $onConfirmCallback;

    /**
     * @var callable|null 取消回调函数
     */
    private $onCancelCallback;

    /**
     * 构造函数
     *
     * @param int $defaultBoardSize 默认棋盘大小
     */
    public function __construct(int $defaultBoardSize = 15)
    {
        $this->boardSize = $defaultBoardSize;
        $this->onConfirmCallback = null;
        $this->onCancelCallback = null;

        // 创建对话框窗口
        $app = LibuiApplication::getInstance();
        $this->dialog = $app->createWindow("New Game", 300, 200);
        // 注意：LibuiWindow 类中没有 setMargined 方法，所以我们不调用它

        // 设置窗口关闭事件
        $this->dialog->on('window.closing', function () {
            $this->handleCancel();
        });

        // 设置窗口内容
        $this->setupContent();
    }

    /**
     * 设置对话框内容
     *
     * @return void
     */
    private function setupContent(): void
    {
        // 创建垂直布局容器
        $vbox = new LibuiVBox();
        $vbox->setPadded(true);
        
        // 创建棋盘大小设置组
        $group = new LibuiGroup("Game Size");
        $group->setPadded(true);
        
        // 创建水平布局容器用于棋盘大小设置
        $hbox = new LibuiHBox();
        $hbox->setPadded(true);
        
        // 添加标签和微调器
        $label = new LibuiLabel("Board Size:");
        $hbox->append($label, false);
        
        $spinbox = new LibuiSpinbox(5, 19);
        $spinbox->setValue($this->boardSize);
        $spinbox->on('spinbox.changed', function () use ($spinbox) {
            $this->boardSize = $spinbox->getValue();
        });
        $hbox->append($spinbox, false);
        
        // 将水平布局添加到组中
        $group->append($hbox, false);
        
        // 将组添加到垂直布局
        $vbox->append($group, false);
        
        // 创建按钮水平布局
        $buttonHbox = new LibuiHBox();
        $buttonHbox->setPadded(true);
        
        // 取消按钮
        $cancelButton = new LibuiButton("Cancel");
        $cancelButton->on('button.clicked', function () {
            $this->handleCancel();
        });
        $buttonHbox->append($cancelButton, true);
        
        // 确认按钮
        $okButton = new LibuiButton("OK");
        $okButton->on('button.clicked', function () {
            $this->handleConfirm();
        });
        $buttonHbox->append($okButton, true);
        
        // 将按钮布局添加到垂直布局
        $vbox->append($buttonHbox, false);
        
        // 设置窗口内容
        $this->dialog->setChild($vbox);
    }

    /**
     * 处理确认事件
     *
     * @return void
     */
    private function handleConfirm(): void
    {
        if ($this->onConfirmCallback !== null) {
            call_user_func($this->onConfirmCallback, $this->boardSize);
        }
        $this->dialog->destroy();
    }

    /**
     * 处理取消事件
     *
     * @return void
     */
    private function handleCancel(): void
    {
        if ($this->onCancelCallback !== null) {
            call_user_func($this->onCancelCallback);
        }
        $this->dialog->destroy();
    }

    /**
     * 设置确认回调函数
     *
     * @param callable $callback 回调函数
     * @return void
     */
    public function setOnConfirmCallback(callable $callback): void
    {
        $this->onConfirmCallback = $callback;
    }

    /**
     * 设置取消回调函数
     *
     * @param callable $callback 回调函数
     * @return void
     */
    public function setOnCancelCallback(callable $callback): void
    {
        $this->onCancelCallback = $callback;
    }

    /**
     * 显示对话框
     *
     * @return void
     */
    public function show(): void
    {
        $this->dialog->show();
    }
}