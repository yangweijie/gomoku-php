# Design Document

## Overview

This document outlines the design for a cross-platform PHP Gomoku (Five-in-a-Row) game using the yangweijie/kingbes-libui-sdk. The application will provide a native desktop GUI experience across Windows, macOS, and Linux platforms, featuring a clean game interface with customizable board settings and standard Gomoku gameplay mechanics.

The design follows a Model-View-Controller (MVC) architecture pattern, separating game logic from UI presentation, and leverages the LibUI framework through PHP FFI for native cross-platform GUI rendering.

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    PHP Gomoku Application                    │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │    View     │  │ Controller  │  │       Model         │  │
│  │   Layer     │◄─┤   Layer     ├─►│      Layer          │  │
│  │             │  │             │  │                     │  │
│  │ - MainWindow│  │ - GameCtrl  │  │ - GameState         │  │
│  │ - GameBoard │  │ - UIHandler │  │ - Board             │  │
│  │ - Dialogs   │  │             │  │ - Player            │  │
│  └─────────────┘  └─────────────┘  │ - GameRules         │  │
│                                    └─────────────────────┘  │
├─────────────────────────────────────────────────────────────┤
│              yangweijie/kingbes-libui-sdk                   │
├─────────────────────────────────────────────────────────────┤
│                    kingbes/libui                            │
├─────────────────────────────────────────────────────────────┤
│                      LibUI (C)                              │
├─────────────────────────────────────────────────────────────┤
│            Native OS GUI (Windows/macOS/Linux)              │
└─────────────────────────────────────────────────────────────┘
```

### Directory Structure

```
src/
├── App/
│   └── GomokuApp.php              # Main application class
├── Controller/
│   ├── GameController.php         # Game logic controller
│   └── UIController.php           # UI event handling
├── Model/
│   ├── GameState.php              # Game state management
│   ├── Board.php                  # Board representation
│   ├── Player.php                 # Player representation
│   └── GameRules.php              # Game rules and win detection
├── View/
│   ├── MainWindow.php             # Main application window
│   ├── GameBoard.php              # Game board rendering
│   ├── NewGameDialog.php          # New game configuration
│   └── GameOverDialog.php         # Game over notification
└── Utils/
    ├── CrossPlatform.php          # Platform-specific utilities
    └── UserMessages.php           # User message handling
```

## Components and Interfaces

### Model Layer

#### GameState Class
```php
class GameState
{
    private Board $board;
    private Player $currentPlayer;
    private Player $blackPlayer;
    private Player $whitePlayer;
    private bool $gameOver;
    private ?Player $winner;
    private array $moveHistory;
    private int $boardSize;
    
    public function __construct(int $boardSize = 15);
    public function makeMove(int $row, int $col): bool;
    public function undoLastMove(): bool;
    public function resetGame(): void;
    public function isGameOver(): bool;
    public function getWinner(): ?Player;
    public function getCurrentPlayer(): Player;
    public function getBoard(): Board;
    public function getMoveHistory(): array;
}
```

#### Board Class
```php
class Board
{
    private array $grid;
    private int $size;
    
    public function __construct(int $size);
    public function placePiece(int $row, int $col, Player $player): bool;
    public function removePiece(int $row, int $col): bool;
    public function getPiece(int $row, int $col): ?Player;
    public function isEmpty(int $row, int $col): bool;
    public function isValidPosition(int $row, int $col): bool;
    public function clear(): void;
    public function getSize(): int;
}
```

#### Player Class
```php
class Player
{
    private string $name;
    private string $color; // 'black' or 'white'
    private int $pieceValue; // 1 for black, -1 for white
    
    public function __construct(string $name, string $color);
    public function getName(): string;
    public function getColor(): string;
    public function getPieceValue(): int;
}
```

#### GameRules Class
```php
class GameRules
{
    public static function checkWin(Board $board, int $row, int $col, Player $player): bool;
    public static function checkDirection(Board $board, int $row, int $col, int $deltaRow, int $deltaCol, Player $player): int;
    public static function isBoardFull(Board $board): bool;
    public static function isValidMove(Board $board, int $row, int $col): bool;
}
```

### View Layer

#### MainWindow Class
```php
class MainWindow
{
    private \FFI $ui;
    private $window;
    private GameBoard $gameBoard;
    private GameController $controller;
    
    public function __construct(GameController $controller);
    public function show(): void;
    public function createMenuBar(): void;
    public function createStatusBar(): void;
    public function updateUI(): void;
    public function showNewGameDialog(): void;
    public function showGameOverDialog(Player $winner): void;
}
```

#### GameBoard Class
```php
class GameBoard
{
    private \FFI $ui;
    private $area;
    private GameState $gameState;
    private int $cellSize;
    private array $colors;
    
    public function __construct(\FFI $ui, GameState $gameState);
    public function render(): void;
    public function handleClick(int $x, int $y): void;
    public function drawGrid(): void;
    public function drawPieces(): void;
    public function drawHoverPreview(int $row, int $col): void;
    public function calculateCellPosition(int $x, int $y): array;
}
```

### Controller Layer

#### GameController Class
```php
class GameController
{
    private GameState $gameState;
    private MainWindow $mainWindow;
    private GameRules $gameRules;
    
    public function __construct();
    public function startNewGame(int $boardSize = 15): void;
    public function makeMove(int $row, int $col): bool;
    public function undoMove(): bool;
    public function resetGame(): void;
    public function handleGameOver(Player $winner): void;
    public function updateUI(): void;
}
```

## Data Models

### Game State Structure
```php
// Game state representation
[
    'board' => [
        'size' => 15,
        'grid' => [
            [0, 0, 0, ...], // 0 = empty, 1 = black, -1 = white
            [0, 0, 0, ...],
            ...
        ]
    ],
    'players' => [
        'black' => ['name' => 'Black', 'color' => 'black', 'value' => 1],
        'white' => ['name' => 'White', 'color' => 'white', 'value' => -1]
    ],
    'currentPlayer' => 'black',
    'gameOver' => false,
    'winner' => null,
    'moveHistory' => [
        ['row' => 7, 'col' => 7, 'player' => 'black', 'timestamp' => '...'],
        ['row' => 7, 'col' => 8, 'player' => 'white', 'timestamp' => '...'],
        ...
    ]
]
```

### UI Configuration Structure
```php
// UI configuration
[
    'window' => [
        'title' => 'PHP Gomoku',
        'width' => 800,
        'height' => 850,
        'resizable' => false
    ],
    'board' => [
        'cellSize' => 40,
        'lineWidth' => 1,
        'colors' => [
            'background' => '#F0D0A0',
            'lines' => '#000000',
            'black' => '#000000',
            'white' => '#FFFFFF',
            'hover' => '#808080'
        ]
    ],
    'menu' => [
        'items' => ['Game', 'Help']
    ]
]
```

## Error Handling

### Error Categories

1. **System Errors**
   - LibUI initialization failures
   - FFI loading errors
   - Platform compatibility issues

2. **Game Logic Errors**
   - Invalid move attempts
   - Game state corruption
   - Rule validation failures

3. **UI Errors**
   - Window creation failures
   - Event handling errors
   - Rendering issues

### Error Handling Strategy

```php
class ErrorHandler
{
    public static function handleSystemError(\Throwable $e): void;
    public static function handleGameError(GameException $e): void;
    public static function handleUIError(UIException $e): void;
    public static function showErrorDialog(string $message): void;
    public static function logError(string $message, array $context = []): void;
}
```

### Exception Classes
```php
class GomokuException extends \Exception {}
class GameException extends GomokuException {}
class UIException extends GomokuException {}
class SystemException extends GomokuException {}
```

## Testing Strategy

### Unit Testing
- **Model Layer**: Test game logic, rules, and state management
- **Controller Layer**: Test game flow and event handling
- **Utility Classes**: Test cross-platform functionality

### Integration Testing
- **UI Integration**: Test UI component interactions
- **Game Flow**: Test complete game scenarios
- **Platform Testing**: Test on Windows, macOS, and Linux

### Test Structure
```
tests/
├── Unit/
│   ├── Model/
│   │   ├── GameStateTest.php
│   │   ├── BoardTest.php
│   │   ├── PlayerTest.php
│   │   └── GameRulesTest.php
│   ├── Controller/
│   │   └── GameControllerTest.php
│   └── Utils/
│       └── CrossPlatformTest.php
├── Integration/
│   ├── GameFlowTest.php
│   └── UIIntegrationTest.php
└── Platform/
    ├── WindowsTest.php
    ├── MacOSTest.php
    └── LinuxTest.php
```

### Testing Tools
- **PHPUnit**: Primary testing framework
- **Mockery**: For mocking dependencies
- **Platform-specific CI**: GitHub Actions for multi-platform testing

## Cross-Platform Considerations

### Platform-Specific Handling
```php
class CrossPlatform
{
    public static function getLibUIPath(): string;
    public static function getExecutableName(): string;
    public static function getConfigPath(): string;
    public static function handlePlatformSpecificUI(): void;
}
```

### Build and Packaging
- **Box**: PHP application packaging
- **Platform-specific builds**: Separate builds for Windows (.exe), macOS (.app), Linux (binary)
- **Dependency bundling**: Include LibUI libraries for each platform

### UI Adaptations
- **Menu placement**: macOS menu bar vs Windows/Linux window menus
- **Keyboard shortcuts**: Platform-appropriate key combinations
- **File dialogs**: Native file dialog integration
- **Window behavior**: Platform-specific window management