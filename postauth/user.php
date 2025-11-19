<?php
session_start();
include '../database/db_connect.php';


// logged in confirmation
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// check that ?id= exists.
if (!isset($_GET['id'])) {
    echo "No user selected.";
    exit();
}

// get user_id
$user_id = intval($_GET['id']);



// get the username with the user_id
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

// check that user exists
if ($res->num_rows !== 1) {
    echo "User not found.";
    exit();
}

// get user if it exists
$user = $res->fetch_assoc();
$username = $user['username'];
$stmt->close();



// get stats for the user
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

// set stats for the user to display
$match_count = $stats_res['match_count'];
$total_kills = $stats_res['total_kills'] ?? 0;
$total_deaths = $stats_res['total_deaths'] ?? 0;
$avg_kills = round($stats_res['avg_kills'] ?? 0, 2);
$avg_deaths = round($stats_res['avg_deaths'] ?? 0, 2);

// if there are no deaths then pretend there is 1 for kd purposes
$kd = ($total_deaths > 0)
    ? round($total_kills / $total_deaths, 2)
    : $total_kills;



// get most common playstyle
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

// set most common playstyle
$most_common_style = ($style_res->num_rows > 0)
    ? $style_res->fetch_assoc()['playstyle']
    : "None";

// get the last 5 matches
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
    <link rel="stylesheet" href="../public/css/styles.css">
</head>

<body>
    <div class="dashboard-wrapper">
        <!-- the little arrow is fun. -->
        <a class="back-link" href="dashboard.php">← Back to Dashboard</a>
        <h1 class="user-title"><?php echo htmlspecialchars($username); ?> — Stats</h1>

        <div class="overview-container">
            <h2>Overview</h2>

            <div class="overview-grid">
                <!-- TOP ROW -->
                <div class="stat-card">
                    <strong>Total Kills</strong>
                    <span><?php echo $total_kills; ?></span>
                </div>

                <div class="stat-card">
                    <strong>Total Deaths</strong>
                    <span><?php echo $total_deaths; ?></span>
                </div>

                <div class="stat-card">
                    <strong>K/D Ratio</strong>
                    <span><?php echo $kd; ?></span>
                </div>

                <!-- BOTTOM ROW -->
                <div class="stat-card">
                    <strong>Average Kills</strong>
                    <span><?php echo $avg_kills; ?></span>
                </div>

                <div class="stat-card">
                    <strong>Average Deaths</strong>
                    <span><?php echo $avg_deaths; ?></span>
                </div>

                <div class="stat-card">
                    <strong>Playstyle</strong>
                    <span><?php echo ucfirst($most_common_style); ?></span>
                </div>
            </div>
        </div>

        <h2>Last 5 Matches</h2>
        <div class="match-list">
            <?php while ($m = $matches->fetch_assoc()): ?>
                <div class="match-card">

                    <div class="match-card-header">
                        <a class="match-username" href="user.php?id=<?php echo $m['user_id']; ?>">
                            <?php echo htmlspecialchars($username); ?>
                        </a>
                        <span class="match-date"><?php echo $m['played_at']; ?></span>
                    </div>

                    <div class="match-stats-grid">
                        <div>Kills: <?php echo $m['kills']; ?></div>
                        <div>Deaths: <?php echo $m['deaths']; ?></div>
                        <div>KD: <?php echo round($m['kills'] / max($m['deaths'],1), 2); ?></div>
                        <div>Playstyle: <?php echo ucfirst($m['playstyle']); ?></div>
                    </div>

                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
