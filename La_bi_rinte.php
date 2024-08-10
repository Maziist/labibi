<?php

session_start();
function findRandomMousePosition($maze)
{
    $secondRow = $maze[1];
    $validPositions = [];
    for ($col = 0; $col < count($secondRow); $col++) {
        if ($secondRow[$col] !== '🧱') {
            $validPositions[] = $col;
        }
    }
    if (empty($validPositions)) {
        return null; // No valid position found
    }
    $randomCol = $validPositions[array_rand($validPositions)];
    return ['row' => 1, 'col' => $randomCol];
}

function findRandomCatPosition($maze)
{
    $lastRowIndex = count($maze) - 1;
    $secondToLastRow = $maze[$lastRowIndex - 1];
    $validPositions = [];
    for ($col = 0; $col < count($secondToLastRow); $col++) {
        if ($secondToLastRow[$col] !== '🧱') {
            $validPositions[] = $col;
        }
    }
    if (empty($validPositions)) {
        return null; // No valid position found
    }
    $randomCol = $validPositions[array_rand($validPositions)];
    return ['row' => $lastRowIndex - 1, 'col' => $randomCol];
}

function moveCat(&$maze, &$catPosition, $direction)
{
    $currentRow = $catPosition['row'];
    $currentCol = $catPosition['col'];
    $newRow = $currentRow;
    $newCol = $currentCol;
    switch ($direction) {
        case 'up':
            $newRow = $currentRow - 1;
            break;
        case 'down':
            $newRow = $currentRow + 1;
            break;
        case 'left':
            $newCol = $currentCol - 1;
            break;
        case 'right':
            $newCol = $currentCol + 1;
            break;
        default:
            return;
    }
    if (isValidMove($maze, $newRow, $newCol)) {
        $catPosition['row'] = $newRow;
        $catPosition['col'] = $newCol;
    }
}

function isValidMove($maze, $row, $col)
{
    if ($maze === null || empty($maze)) {
        return false;
    }
    if ($row < 0 || $row >= count($maze) || $col < 0 || $col >= count($maze[0])) {
        return false;
    }
    if ($maze[$row][$col] === '🧱') {
        return false;
    }
    return true;
}

function moveMouse($maze, &$mousePosition, $catPosition)
{
    $currentRow = $mousePosition['row'];
    $currentCol = $mousePosition['col'];
    $catRow = $catPosition['row'];
    $catCol = $catPosition['col'];

    $directions = [
        ['row' => -1, 'col' => 0], // up
        ['row' => 1, 'col' => 0],  // down
        ['row' => 0, 'col' => -1], // left
        ['row' => 0, 'col' => 1]   // right
    ];

    $bestDirection = null;
    $shortestDistance = PHP_INT_MAX;

    foreach ($directions as $direction) {
        $newRow = $currentRow + $direction['row'];
        $newCol = $currentCol + $direction['col'];
        if (isValidMove($maze, $newRow, $newCol)) {
            $distance = abs($newRow - $catRow) + abs($newCol - $catCol);
            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $bestDirection = $direction;
            }
        }
    }

    if ($bestDirection !== null) {
        $mousePosition['row'] = $currentRow + $bestDirection['row'];
        $mousePosition['col'] = $currentCol + $bestDirection['col'];
    }
}

function initializeFogOfWar($maze)
{
    $fogOfWar = [];
    for ($i = 0; $i < count($maze); $i++) {
        $fogOfWar[$i] = array_fill(0, count($maze[$i]), true);
    }
    return $fogOfWar;
}

function updateFogOfWar($maze, &$fogOfWar, $catPosition)
{
    // Reset all cells to foggy
    for ($i = 0; $i < count($maze); $i++) {
        for ($j = 0; $j < count($maze[$i]); $j++) {
            $fogOfWar[$i][$j] = true;
        }
    }

    // Clear fog for the cat's position and adjacent cells
    $directions = [[0, 0], [-1, 0], [1, 0], [0, -1], [0, 1]];
    foreach ($directions as $dir) {
        $newRow = $catPosition['row'] + $dir[0];
        $newCol = $catPosition['col'] + $dir[1];
        if (
            $newRow >= 0 && $newRow < count($maze) &&
            $newCol >= 0 && $newCol < count($maze[0])
        ) {
            $fogOfWar[$newRow][$newCol] = false;
        }
    }
}

function displayMaze($maze, $fogOfWar, $catPosition, $mousePosition)
{
    echo "<table>";
    for ($i = 0; $i < count($maze); $i++) {
        echo "<tr>";
        for ($j = 0; $j < count($maze[$i]); $j++) {
            echo "<td>";
            if (!$fogOfWar[$i][$j]) {
                if ($i == $catPosition['row'] && $j == $catPosition['col']) {
                    echo "🐱";
                } elseif ($i == $mousePosition['row'] && $j == $mousePosition['col']) {
                    echo "🐭";
                } elseif ($maze[$i][$j] == '🧱') {
                    echo "🧱";
                } else {
                    echo "⬜";
                }
            } else {
                echo "🌫️";
            }
            echo "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}
// Initialisation du jeu
$mazes = include("./maps.php");
if (!is_array($mazes)) {
    die("Error: maps.php did not return an array of mazes.");
}

// Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Check if we need to initialize a new game
if (!isset($_SESSION['maze'])) {
    $selectedMaze = $mazes[array_rand($mazes)];
    $mousePosition = findRandomMousePosition($selectedMaze);
    $catPosition = findRandomCatPosition($selectedMaze);
    $fogOfWar = initializeFogOfWar($selectedMaze);

    $_SESSION['maze'] = $selectedMaze;
    $_SESSION['catPosition'] = $catPosition;
    $_SESSION['mousePosition'] = $mousePosition;
    $_SESSION['fogOfWar'] = $fogOfWar;
    $_SESSION['gameWon'] = false; // Initialize gameWon
} else {
    $selectedMaze = $_SESSION['maze'];
    $catPosition = $_SESSION['catPosition'];
    $mousePosition = $_SESSION['mousePosition'];
    $fogOfWar = $_SESSION['fogOfWar'];
    // Ensure gameWon is set
    if (!isset($_SESSION['gameWon'])) {
        $_SESSION['gameWon'] = false;
    }
}

// Handle movement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['direction']) && !$_SESSION['gameWon']) {
    moveCat($selectedMaze, $catPosition, $_POST['direction']);
    moveMouse($selectedMaze, $mousePosition, $catPosition);
    $_SESSION['catPosition'] = $catPosition;
    $_SESSION['mousePosition'] = $mousePosition;

    // Check if the cat caught the mouse
    if ($catPosition['row'] == $mousePosition['row'] && $catPosition['col'] == $mousePosition['col']) {
        $_SESSION['gameWon'] = true;
    }
}

// Update fog of war
updateFogOfWar($selectedMaze, $fogOfWar, $catPosition);
$_SESSION['fogOfWar'] = $fogOfWar;

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bi Rint</title>
    <style>
        h1 {
            text-align: center;
        }

        main {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Times New Roman', Times, serif;
        }

        table {
            border-collapse: collapse;
            margin: 20px auto;
        }

        td {
            width: 30px;
            height: 30px;
            text-align: center;
            font-size: 20px;
        }

        #gameContainer {
            display: flex;
            align-items: center;
            margin-top: 50px;
        }

        #virtual-keyboard {
            position: fixed;
            left: 80%;
            transform: translateX(-50%);
            background-color: white;
            box-shadow: 2px 2px 2px 2px;
            padding: 10px;
            border-radius: 10px;
            display: flex;
            flex-direction: row;
            align-items: center;
        }

        .key-row {
            display: flex;
            justify-content: center;
            margin: 5px 0;
        }

        .key {
            width: 50px;
            height: 50px;
            background-color: #614c84a1;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 5px;
            border-radius: 5px;
            font-size: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #space {
            width: 200px;
        }

        .key.active {
            background-color: #b209a4;
        }
    </style>
</head>

<body>
    <header>
        <h1>La bi rinte ne fais pas le moine</h1>
    </header>
    <main>
        <div id="gameContainer">
            <div id="virtual-keyboard">
                <form method="post">
                    <div class="key-row">
                        <button class="key" id="arrowUp" type="submit" name="direction" value="up" <?php echo isset($_SESSION['gameWon']) && $_SESSION['gameWon'] ? 'disabled' : ''; ?>>↑</button>
                    </div>
                    <div class="key-row">
                        <button class="key" id="arrowLeft" type="submit" name="direction" value="left" <?php echo isset($_SESSION['gameWon']) && $_SESSION['gameWon'] ? 'disabled' : ''; ?>>←</button>
                        <button class="key" id="arrowDown" type="submit" name="direction" value="down" <?php echo isset($_SESSION['gameWon']) && $_SESSION['gameWon'] ? 'disabled' : ''; ?>>↓ </button>
                        <button class="key" id="arrowRight" type="submit" name="direction" value="right" <?php echo isset($_SESSION['gameWon']) && $_SESSION['gameWon'] ? 'disabled' : ''; ?>>→</button>
                    </div>
                    <div class="key-row">
                        <button class="key" id="space" type="submit" name="reset" value="true">Reset</button>
                    </div>
                </form>
            </div>
            <?php

            if (isset($_SESSION['gameWon']) && $_SESSION['gameWon']) {
                echo "<h2>Win!</h2>";
            }
            displayMaze($selectedMaze, $fogOfWar, $catPosition, $mousePosition);
            ?>
        </div>
    </main>
    <script>
        const keys = {};
document.addEventListener('keydown', (e) => {
    keys[e.code] = true;
    updateVirtualKeyboard(e.code, true);
});
document.addEventListener('keyup', (e) => {
    keys[e.code] = false;
    updateVirtualKeyboard(e.code, false);
});

// Fonction pour mettre à jour le clavier virtuel
function updateVirtualKeyboard(keyCode, isKeyDown) {
    let keyElement;
    switch(keyCode) {
        case 'ArrowUp':
            keyElement = document.getElementById('arrowUp');
            break;
        case 'ArrowDown':
            keyElement = document.getElementById('arrowDown');
            break;
        case 'ArrowLeft':
            keyElement = document.getElementById('arrowLeft');
            break;
        case 'ArrowRight':
            keyElement = document.getElementById('arrowRight');
            break;
        case 'Space':
            keyElement = document.getElementById('space');
            break;
    }

    if (keyElement) {
        if (isKeyDown) {
            keyElement.classList.add('active');
        } else {
            keyElement.classList.remove('active');
        }
    }
}
window.addEventListener("keydown", function(e) {
    if(["ArrowUp","ArrowDown","ArrowLeft","ArrowRight"].indexOf(e.code) > -1) {
        e.preventDefault();
    }
}, false);
    </script>
</body>

</html>