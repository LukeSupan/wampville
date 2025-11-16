<?php
session_start();
include '../database/db_connect.php';

// must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check that ?id= exists
if (!isset($_GET['id'])) {
    echo "No user selected.";
    exit();
}

$user_id = intval($_GET['id']);


// ----- GET USERNAME -----
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    echo "User not found.";
    exit();
}

$user = $res->fetch_assoc();
$username = $user['username'];
$stmt->close();


// ----- GET TOTAL + AVG KILLS & DEATHS -----
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS match_count,
        SUM(kills) AS total_kills,
        SUM(deaths) AS total_deaths,
        AVG(kills) AS avg_kills,
        AVG(deaths) AS avg_deaths
    FROM matches
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats_res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$match_count = $stats_res['match_count'];
$total_kills = $stats_res['total_kills'] ?? 0;
$total_deaths = $stats_res['total_deaths'] ?? 0;
$avg_kills = round($stats_res['avg_kills'] ?? 0, 2);
$avg_deaths = round($stats_res['avg_deaths'] ?? 0, 2);

// Avoid divide-by-zero
$kd = ($total_deaths > 0)
    ? round($total_kills / $total_deaths, 2)
    : $total_kills;    // If no deaths, KD = kills (infinite ratio)


// ----- MOST COMMON PLAYSTYLE -----
$stmt = $conn->prepare("
    SELECT playstyle, COUNT(*) AS count
    FROM matches
    WHERE user_id = ?
    GROUP BY playstyle
    ORDER BY count DESC
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$style_res = $stmt->get_result();
$stmt->close();

$most_common_style = ($style_res->num_rows > 0)
    ? $style_res->fetch_assoc()['playstyle']
    : "None";

// ----- GET ALL MATCHES FOR LISTING -----
$matches = $conn->query("
    SELECT *
    FROM matches
    WHERE user_id = $user_id
    ORDER BY played_at DESC
    LIMIT 5
");


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Halo: ST — <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <a href="dashboard.php">← Back to Dashboard</a>
    <h1><?php echo htmlspecialchars($username); ?> — Stats</h1>

    <hr>

    <h2>Overview</h2>
    <ul>
        <li><strong>Total Kills:</strong> <?php echo $total_kills; ?></li>
        <li><strong>Total Deaths:</strong> <?php echo $total_deaths; ?></li>
        <li><strong>Average Kills:</strong> <?php echo $avg_kills; ?></li>
        <li><strong>Average Deaths:</strong> <?php echo $avg_deaths; ?></li>
        <li><strong>K/D Ratio:</strong> <?php echo $kd; ?></li>
        <li><strong>Most Common Playstyle:</strong> <?php echo ucfirst($most_common_style); ?></li>
    </ul>

    <hr>

    <h2>Last 5 Matches</h2>

    <?php if ($matches->num_rows > 0): ?>
        <?php while ($m = $matches->fetch_assoc()): ?>
            <div class="match-card">
                <ul>
                    <li>Kills: <?php echo $m['kills']; ?></li>
                    <li>Deaths: <?php echo $m['deaths']; ?></li>
                    <li>Playstyle: <?php echo ucfirst($m['playstyle']); ?></li>
                </ul>
                <small>Played at: <?php echo $m['played_at']; ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No matches recorded.</p>
    <?php endif; ?>

</body>
</html>
