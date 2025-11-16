<?php
session_start();
include '../database/db_connect.php';
include '../includes/functions.php';

// stuff that needs to happen serverside
// check if admin (function probably) TASK 1
// if admin then display the match input TASK 2
// for everyone get all matches and put them on screen. you can scroll down (no pagination i dont wanna its fine) TASK 3
// make it so clicking on users takes to user page TASK 4

// confirm user is logged in otherwise kick them out
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// ADMIN CHECK (placeholder stuff functions.php later)
$is_admin = false;

// admin check for now. will move
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

// adin check yup
if ($res->num_rows === 1) {
    $row = $res->fetch_assoc();
    $is_admin = (bool)$row['is_admin'];
}
$stmt->close();

// MATCH SUBMISSION (ADMIN ONLY)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $kills = $_POST['kills'];
    $deaths = $_POST['deaths'];
    $playstyle = $_POST['playstyle'];

    $stmt = $conn->prepare("
        INSERT INTO matches (user_id, kills, deaths, playstyle)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiis", $user_id, $kills, $deaths, $playstyle);
    $stmt->execute();
    $stmt->close();

    // refresh page if user is admin to add thing
    header("Location: dashboard.php");
    exit();
}

// get all matches for everyone and sort them by played at data (recent first)
$matches = $conn->query("
    SELECT m.*, u.username 
    FROM matches m
    JOIN users u ON m.user_id = u.id
    ORDER BY m.played_at DESC
");

// stuff that needs to happen serverside
// check if admin (function probably) TASK 1
// if admin then display the match input TASK 2
// for everyone get all matches and put them on screen. you can scroll down (no pagination i dont wanna its fine) TASK 3
// make it so clicking on users takes to user page TASK 4
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Halo: ST - Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
    <a href="../auth/logout.php">Logout</a>


    <hr>

    <!-- ADMIN MATCH SUBMISSION SECTION -->
    <?php if ($is_admin): ?>
        <div class="admin-panel">
            <h2>Submit New Match</h2>
            <form method="POST" action="">
                <input type="number" name="kills" placeholder="Kills" required><br>
                <input type="number" name="deaths" placeholder="Deaths" required><br>

                <select name="playstyle" required>
                    <option value="" disabled selected>Playstyle</option>
                    <option value="infantry">Infantry</option>
                    <option value="vehicle">Vehicle</option>
                    <option value="mixed">Mixed</option>
                </select><br>

                <button type="submit">Post Match</button>
            </form>
        </div>
    <?php endif; ?>

    <hr>

    <!-- DISPLAY MATCH SECTION -->
     <h2>Recent Matches</h2>

    <div class="match-list">
        <?php while ($m = $matches->fetch_assoc()): ?>
            <div class="match-card">
                <p>
                    <strong>
                        <a href="user.php?id=<?php echo $m['user_id']; ?>">
                            <?php echo htmlspecialchars($m['username']); ?>
                        </a>
                    </strong>
                    played a match:
                </p>

                <ul>
                    <li>Kills: <?php echo $m['kills']; ?></li>
                    <li>Deaths: <?php echo $m['deaths']; ?></li>
                    <li>Playstyle: <?php echo ucfirst($m['playstyle']); ?></li>
                </ul>

                <small>Played at: <?php echo $m['played_at']; ?></small>
            </div>
        <?php endwhile; ?>
    </div>


</body>
</html>
