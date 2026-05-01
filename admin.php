<?php
// ── Protection basique (changez ces identifiants !) ──────
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'lumiere2024');  // ← À changer absolument
define('MESSAGES_FILE', __DIR__ . '/messages.json');

session_start();

// Connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['username'] === ADMIN_USER && $_POST['password'] === ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
    } else {
        $login_error = "Identifiants incorrects.";
    }
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Suppression d'un message
if (isset($_GET['delete']) && $_SESSION['logged_in'] ?? false) {
    $id_to_delete = $_GET['delete'];
    $messages = file_exists(MESSAGES_FILE) ? json_decode(file_get_contents(MESSAGES_FILE), true) : [];
    $messages = array_filter($messages, fn($m) => $m['id'] !== $id_to_delete);
    file_put_contents(MESSAGES_FILE, json_encode(array_values($messages), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php');
    exit;
}

// Chargement des messages
$messages = [];
if (($_SESSION['logged_in'] ?? false) && file_exists(MESSAGES_FILE)) {
    $messages = json_decode(file_get_contents(MESSAGES_FILE), true) ?? [];
}

$logged_in = $_SESSION['logged_in'] ?? false;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Messages reçus</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700&family=Syne+Mono&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg: #030308;
    --surface: #0a0a14;
    --surface2: #0f0f1c;
    --border: rgba(255,255,255,0.07);
    --text: #e8e6f0;
    --muted: rgba(232,230,240,0.45);
    --accent: #6c63ff;
    --accent3: #63ffda;
    --danger: #ff6b6b;
    --success: #63ffda;
}

body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Syne', sans-serif;
    min-height: 100vh;
}

/* ── LOGIN ── */
.login-wrap {
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
}

.login-box {
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 3rem;
    width: 100%; max-width: 400px;
}

.login-logo {
    font-family: 'Instrument Serif', serif;
    font-size: 1.6rem; font-style: italic;
    color: var(--accent);
    margin-bottom: 0.3rem;
}

.login-sub {
    font-size: 0.78rem; color: var(--muted);
    font-family: 'Syne Mono', monospace;
    letter-spacing: 0.1em; text-transform: uppercase;
    margin-bottom: 2.5rem;
}

.field { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1.2rem; }

label {
    font-size: 0.7rem; letter-spacing: 0.15em; text-transform: uppercase;
    color: var(--muted); font-family: 'Syne Mono', monospace;
}

input[type=text], input[type=password] {
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    color: var(--text); padding: 0.85rem 1.1rem;
    font-family: 'Syne', sans-serif; font-size: 0.92rem;
    outline: none; transition: border-color 0.3s, box-shadow 0.3s;
    width: 100%;
}
input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(108,99,255,0.1);
}

.btn-login {
    width: 100%; padding: 0.95rem;
    background: linear-gradient(135deg, var(--accent), #9c6bff);
    color: white; border: none;
    font-family: 'Syne', sans-serif; font-size: 0.85rem;
    font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase;
    cursor: pointer; transition: opacity 0.3s, transform 0.3s;
    margin-top: 0.5rem;
}
.btn-login:hover { opacity: 0.9; transform: translateY(-1px); }

.error-msg {
    background: rgba(255,107,107,0.08);
    border: 1px solid rgba(255,107,107,0.25);
    color: #ff8f8f; padding: 0.8rem 1rem;
    font-size: 0.83rem; margin-bottom: 1.2rem;
}

/* ── ADMIN LAYOUT ── */
.admin-layout { display: flex; min-height: 100vh; }

.sidebar {
    width: 240px; flex-shrink: 0;
    background: var(--surface);
    border-right: 1px solid var(--border);
    padding: 2rem 1.5rem;
    display: flex; flex-direction: column; gap: 2rem;
    position: sticky; top: 0; height: 100vh;
}

.sidebar-logo {
    font-family: 'Instrument Serif', serif;
    font-size: 1.2rem; font-style: italic;
    color: var(--text);
}
.sidebar-logo span { color: var(--accent); }

.sidebar-nav { display: flex; flex-direction: column; gap: 0.4rem; }

.sidebar-link {
    display: flex; align-items: center; gap: 0.7rem;
    padding: 0.7rem 1rem;
    color: var(--muted); text-decoration: none;
    font-size: 0.83rem; font-weight: 500;
    border-radius: 2px; transition: background 0.2s, color 0.2s;
}
.sidebar-link.active, .sidebar-link:hover {
    background: rgba(108,99,255,0.1);
    color: var(--text);
}
.sidebar-link .icon { font-size: 0.9rem; }

.sidebar-bottom { margin-top: auto; }

.logout-btn {
    display: flex; align-items: center; gap: 0.7rem;
    padding: 0.7rem 1rem;
    color: var(--danger); text-decoration: none;
    font-size: 0.83rem; font-weight: 500;
    border-radius: 2px; transition: background 0.2s;
}
.logout-btn:hover { background: rgba(255,107,107,0.08); }

/* ── MAIN ── */
.main { flex: 1; padding: 2.5rem 3rem; }

.page-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.page-title {
    font-family: 'Instrument Serif', serif;
    font-size: 1.8rem; font-style: italic;
}

.badge {
    display: inline-flex; align-items: center;
    background: rgba(108,99,255,0.15);
    border: 1px solid rgba(108,99,255,0.3);
    color: var(--accent); padding: 0.3rem 0.8rem;
    font-size: 0.75rem; font-family: 'Syne Mono', monospace;
    letter-spacing: 0.1em;
}

/* ── STATS ── */
.stats-row {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 1rem; margin-bottom: 2rem;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 1.5rem;
}
.stat-card-num {
    font-family: 'Instrument Serif', serif;
    font-size: 2.5rem; color: var(--accent); display: block;
    line-height: 1;
}
.stat-card-label {
    font-size: 0.7rem; color: var(--muted);
    letter-spacing: 0.15em; text-transform: uppercase;
    font-family: 'Syne Mono', monospace; margin-top: 0.3rem;
}

/* ── MESSAGES TABLE ── */
.table-wrap {
    background: var(--surface);
    border: 1px solid var(--border);
    overflow: hidden;
}

.table-header {
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}

.table-title {
    font-size: 0.75rem; color: var(--muted);
    font-family: 'Syne Mono', monospace;
    letter-spacing: 0.15em; text-transform: uppercase;
}

table { width: 100%; border-collapse: collapse; }

thead tr {
    background: rgba(255,255,255,0.02);
    border-bottom: 1px solid var(--border);
}

th {
    padding: 0.9rem 1.5rem; text-align: left;
    font-size: 0.68rem; color: var(--muted);
    font-family: 'Syne Mono', monospace;
    letter-spacing: 0.15em; text-transform: uppercase;
    font-weight: 400;
}

td {
    padding: 1.1rem 1.5rem;
    font-size: 0.88rem; color: var(--text);
    border-bottom: 1px solid var(--border);
    vertical-align: top;
}

tr:last-child td { border-bottom: none; }

tr:hover td { background: rgba(255,255,255,0.015); }

.msg-nom { font-weight: 600; color: var(--text); }
.msg-email {
    font-family: 'Syne Mono', monospace;
    font-size: 0.78rem; color: var(--accent);
}
.msg-date {
    font-family: 'Syne Mono', monospace;
    font-size: 0.75rem; color: var(--muted);
    white-space: nowrap;
}
.msg-sujet {
    font-size: 0.78rem; color: var(--muted);
    max-width: 150px; overflow: hidden;
    text-overflow: ellipsis; white-space: nowrap;
}
.msg-body {
    font-size: 0.83rem; color: var(--muted);
    max-width: 300px; overflow: hidden;
    text-overflow: ellipsis; white-space: nowrap;
}

.delete-btn {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.35rem 0.75rem;
    background: rgba(255,107,107,0.08);
    border: 1px solid rgba(255,107,107,0.2);
    color: var(--danger); font-size: 0.72rem;
    font-family: 'Syne', sans-serif; font-weight: 600;
    letter-spacing: 0.05em; text-decoration: none;
    transition: background 0.2s, border-color 0.2s;
    cursor: pointer;
}
.delete-btn:hover { background: rgba(255,107,107,0.15); border-color: rgba(255,107,107,0.4); }

.empty-state {
    text-align: center; padding: 4rem 2rem;
    color: var(--muted);
}
.empty-icon { font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.4; }
.empty-text { font-size: 0.88rem; }

/* Modal pour lire le message complet */
.msg-preview {
    display: inline-block; cursor: pointer;
    border-bottom: 1px dashed rgba(255,255,255,0.15);
    transition: border-color 0.2s;
}
.msg-preview:hover { border-color: var(--accent); }

details summary { cursor: pointer; color: var(--muted); font-size: 0.82rem; }
details summary::-webkit-details-marker { color: var(--accent); }
details p {
    margin-top: 0.6rem; padding: 0.8rem;
    background: rgba(255,255,255,0.03);
    border-left: 2px solid var(--accent);
    font-size: 0.85rem; line-height: 1.7; color: var(--text);
    max-width: 400px;
}

@media (max-width: 768px) {
    .sidebar { display: none; }
    .main { padding: 1.5rem; }
    .stats-row { grid-template-columns: 1fr; }
    table { font-size: 0.8rem; }
    th, td { padding: 0.7rem 0.8rem; }
}
</style>
</head>
<body>

<?php if (!$logged_in): ?>
<!-- ── PAGE DE CONNEXION ── -->
<div class="login-wrap">
    <div class="login-box">
        <div class="login-logo">Lumière<span style="color:var(--muted)">Studio</span></div>
        <div class="login-sub">Espace administrateur</div>

        <?php if (isset($login_error)): ?>
        <div class="error-msg">⚠ <?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label for="u">Identifiant</label>
                <input type="text" id="u" name="username" placeholder="admin" autocomplete="username">
            </div>
            <div class="field">
                <label for="p">Mot de passe</label>
                <input type="password" id="p" name="password" placeholder="••••••••" autocomplete="current-password">
            </div>
            <button type="submit" name="login" class="btn-login">Se connecter →</button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ── INTERFACE ADMIN ── -->
<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">Lumière<span>Studio</span></div>

        <nav class="sidebar-nav">
            <a href="admin.php" class="sidebar-link active">
                <span class="icon">✉</span> Messages
            </a>
            <a href="index.php" class="sidebar-link" target="_blank">
                <span class="icon">↗</span> Voir le site
            </a>
        </nav>

        <div class="sidebar-bottom">
            <a href="admin.php?logout=1" class="logout-btn">
                <span>⏻</span> Déconnexion
            </a>
        </div>
    </aside>

    <!-- Main -->
    <main class="main">
        <div class="page-header">
            <h1 class="page-title">Messages reçus</h1>
            <span class="badge"><?= count($messages) ?> message<?= count($messages) !== 1 ? 's' : '' ?></span>
        </div>

        <!-- Stats -->
        <?php
        $today = date('Y-m-d');
        $today_count = count(array_filter($messages, fn($m) => str_starts_with($m['date'], $today)));
        $week_count  = count(array_filter($messages, fn($m) => strtotime($m['date']) > strtotime('-7 days')));
        ?>
        <div class="stats-row">
            <div class="stat-card">
                <span class="stat-card-num"><?= count($messages) ?></span>
                <div class="stat-card-label">Total messages</div>
            </div>
            <div class="stat-card">
                <span class="stat-card-num"><?= $today_count ?></span>
                <div class="stat-card-label">Aujourd'hui</div>
            </div>
            <div class="stat-card">
                <span class="stat-card-num"><?= $week_count ?></span>
                <div class="stat-card-label">Cette semaine</div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <div class="table-header">
                <span class="table-title">Tous les messages</span>
            </div>

            <?php if (empty($messages)): ?>
            <div class="empty-state">
                <div class="empty-icon">✉</div>
                <div class="empty-text">Aucun message reçu pour l'instant.</div>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Sujet</th>
                        <th>Message</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                    <tr>
                        <td><span class="msg-date"><?= htmlspecialchars($msg['date']) ?></span></td>
                        <td><span class="msg-nom"><?= htmlspecialchars($msg['nom']) ?></span></td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="msg-email">
                                <?= htmlspecialchars($msg['email']) ?>
                            </a>
                        </td>
                        <td><span class="msg-sujet"><?= htmlspecialchars($msg['sujet'] ?: '—') ?></span></td>
                        <td>
                            <details>
                                <summary class="msg-body"><?= htmlspecialchars(mb_substr($msg['message'], 0, 50)) ?>...</summary>
                                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                            </details>
                        </td>
                        <td>
                            <a href="admin.php?delete=<?= urlencode($msg['id']) ?>"
                               class="delete-btn"
                               onclick="return confirm('Supprimer ce message ?')">
                               ✕ Supprimer
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php endif; ?>

</body>
</html>
