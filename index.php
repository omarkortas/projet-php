<?php
$site_name = "Lumière Studio";
$current_year = date('Y');

$projects = [
    ["titre" => "Identité Visuelle", "categorie" => "Branding", "desc" => "Marques qui marquent les esprits", "color" => "#ff6b35"],
    ["titre" => "Application Mobile", "categorie" => "UI/UX", "desc" => "Interfaces du futur, aujourd'hui", "color" => "#7c3aed"],
    ["titre" => "Campagne Digitale", "categorie" => "Marketing", "desc" => "Messages amplifiés à l'infini", "color" => "#0891b2"],
    ["titre" => "Site E-commerce", "categorie" => "Web", "desc" => "Expériences d'achat inoubliables", "color" => "#059669"],
    ["titre" => "Motion Design", "categorie" => "Animation", "desc" => "Vie et mouvement à vos idées", "color" => "#db2777"],
    ["titre" => "Packaging Produit", "categorie" => "Design", "desc" => "L'objet comme manifeste créatif", "color" => "#d97706"],
];

$services = [
    ["01", "Identité & Branding", "Construction d'identités visuelles cohérentes et percutantes qui reflètent votre vision."],
    ["02", "Design Web & UI", "Interfaces modernes, accessibles et performantes, pensées pour l'expérience avant tout."],
    ["03", "Motion & Vidéo", "Animations et productions qui donnent vie à vos idées et amplifient vos messages."],
    ["04", "Stratégie Digitale", "Accompagnement dans votre transformation numérique, de la vision à l'exécution."],
];

$message_sent = false;
$errors = [];

// ── Stockage JSON ──────────────────────────────────────────
define('MESSAGES_FILE', __DIR__ . '/messages.json');

function load_messages(): array {
    if (!file_exists(MESSAGES_FILE)) return [];
    $data = file_get_contents(MESSAGES_FILE);
    return json_decode($data, true) ?? [];
}

function save_message(array $entry): bool {
    $messages = load_messages();
    array_unshift($messages, $entry); // plus récent en premier
    return file_put_contents(
        MESSAGES_FILE,
        json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ) !== false;
}
// ──────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom']    ?? '');
    $email  = trim($_POST['email']  ?? '');
    $sujet  = trim($_POST['sujet']  ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($nom))    $errors[] = "Le nom est requis.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                        $errors[] = "Un email valide est requis.";
    if (empty($message)) $errors[] = "Le message est requis.";

    if (empty($errors)) {
        $entry = [
            'id'      => uniqid('msg_', true),
            'date'    => date('Y-m-d H:i:s'),
            'nom'     => $nom,
            'email'   => $email,
            'sujet'   => $sujet,
            'message' => $message,
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
        ];
        if (save_message($entry)) {
            $message_sent = true;
        } else {
            $errors[] = "Erreur lors de l'enregistrement. Vérifiez les permissions du dossier.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($site_name) ?> — Studio Créatif 3D</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Syne+Mono&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg: #030308;
    --surface: #0a0a14;
    --border: rgba(255,255,255,0.06);
    --text: #e8e6f0;
    --muted: rgba(232,230,240,0.45);
    --accent: #6c63ff;
    --accent2: #ff6b6b;
    --accent3: #63ffda;
    --glow: rgba(108,99,255,0.35);
}

html { scroll-behavior: smooth; }

body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Syne', sans-serif;
    font-weight: 400;
    overflow-x: hidden;
    cursor: none;
}

/* CUSTOM CURSOR */
#cursor {
    position: fixed; top: 0; left: 0; z-index: 9999;
    pointer-events: none; mix-blend-mode: difference;
}
#cursor-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: white;
    position: absolute; transform: translate(-50%,-50%);
    transition: width 0.2s, height 0.2s;
}
#cursor-ring {
    width: 36px; height: 36px; border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.6);
    position: absolute; transform: translate(-50%,-50%);
    transition: width 0.35s cubic-bezier(.23,1,.32,1), height 0.35s cubic-bezier(.23,1,.32,1), border-color 0.3s;
}
body.hovering #cursor-dot { width: 12px; height: 12px; }
body.hovering #cursor-ring { width: 56px; height: 56px; border-color: var(--accent); }

/* CANVAS BG */
#canvas-bg {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    z-index: 0; pointer-events: none;
}

/* NOISE OVERLAY */
body::after {
    content: '';
    position: fixed; inset: 0; z-index: 1;
    pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
    opacity: 0.4;
}

/* NAV */
nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.6rem 4rem;
    background: rgba(3,3,8,0.7);
    backdrop-filter: blur(20px) saturate(180%);
    border-bottom: 1px solid var(--border);
}

.logo {
    font-size: 1.1rem; font-weight: 700; letter-spacing: 0.05em;
    text-decoration: none; color: var(--text);
    display: flex; align-items: center; gap: 0.6rem;
}

.logo-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--accent);
    box-shadow: 0 0 12px var(--accent);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 8px var(--accent), 0 0 20px var(--accent); }
    50% { box-shadow: 0 0 16px var(--accent), 0 0 40px var(--accent); }
}

nav ul { list-style: none; display: flex; gap: 2.5rem; }
nav a { text-decoration: none; color: var(--muted); font-size: 0.82rem; letter-spacing: 0.1em; text-transform: uppercase; font-weight: 500; transition: color 0.3s; }
nav a:hover { color: var(--text); }

.nav-cta {
    background: var(--accent); color: white !important;
    padding: 0.5rem 1.3rem;
    border-radius: 2px;
    transition: box-shadow 0.3s, transform 0.3s !important;
}
.nav-cta:hover { box-shadow: 0 0 20px var(--glow); transform: translateY(-1px); color: white !important; }

/* HERO */
.hero {
    min-height: 100vh;
    display: flex; align-items: center;
    padding: 8rem 4rem 4rem;
    position: relative; z-index: 2;
}

.hero-content { max-width: 800px; }

.hero-tag {
    display: inline-flex; align-items: center; gap: 0.6rem;
    font-family: 'Syne Mono', monospace;
    font-size: 0.72rem; color: var(--accent3);
    letter-spacing: 0.2em; text-transform: uppercase;
    margin-bottom: 2rem;
    padding: 0.4rem 0.9rem;
    border: 1px solid rgba(99,255,218,0.25);
    border-radius: 2px;
    animation: fadeInDown 0.8s ease both;
}

.hero-tag::before { content: '▶'; font-size: 0.55rem; }

h1 {
    font-family: 'Instrument Serif', serif;
    font-size: clamp(3.5rem, 8vw, 7rem);
    line-height: 1.0;
    margin-bottom: 1.8rem;
    animation: fadeInUp 1s ease 0.2s both;
    letter-spacing: -0.02em;
}

h1 .outline {
    -webkit-text-stroke: 1px rgba(255,255,255,0.5);
    color: transparent;
    font-style: italic;
}

h1 .gradient-text {
    background: linear-gradient(135deg, var(--accent) 0%, var(--accent2) 50%, var(--accent3) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: gradientShift 4s ease-in-out infinite;
    background-size: 200% 200%;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.hero-desc {
    font-size: 1.05rem; line-height: 1.8; color: var(--muted);
    max-width: 520px; margin-bottom: 3rem;
    animation: fadeInUp 1s ease 0.4s both;
}

.hero-actions { display: flex; gap: 1rem; flex-wrap: wrap; animation: fadeInUp 1s ease 0.6s both; }

.btn-primary {
    display: inline-flex; align-items: center; gap: 0.6rem;
    padding: 1rem 2.2rem;
    background: linear-gradient(135deg, var(--accent), #9c6bff);
    color: white; text-decoration: none;
    font-size: 0.85rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
    border-radius: 3px; position: relative; overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}
.btn-primary::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, transparent, rgba(255,255,255,0.15));
}
.btn-primary:hover { transform: translateY(-3px); box-shadow: 0 20px 40px rgba(108,99,255,0.4); }
.btn-primary .arrow { transition: transform 0.3s; }
.btn-primary:hover .arrow { transform: translateX(4px); }

.btn-ghost {
    display: inline-flex; align-items: center; gap: 0.6rem;
    padding: 1rem 2.2rem;
    color: var(--text); text-decoration: none;
    font-size: 0.85rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
    border: 1px solid var(--border); border-radius: 3px;
    transition: border-color 0.3s, background 0.3s;
}
.btn-ghost:hover { border-color: var(--accent); background: rgba(108,99,255,0.08); }

.hero-stats {
    position: absolute; right: 4rem; bottom: 6rem;
    display: flex; flex-direction: column; gap: 2rem;
    animation: fadeInRight 1s ease 0.8s both;
    z-index: 2;
}

.stat-item {
    text-align: right; position: relative;
    padding-right: 1.5rem;
}

.stat-item::after {
    content: ''; position: absolute; right: 0; top: 50%;
    width: 2px; height: 40px; transform: translateY(-50%);
    background: linear-gradient(to bottom, transparent, var(--accent), transparent);
}

.stat-num {
    font-family: 'Instrument Serif', serif; font-size: 2.8rem;
    line-height: 1; color: var(--text); display: block;
}
.stat-label {
    font-size: 0.7rem; color: var(--muted); letter-spacing: 0.15em; text-transform: uppercase;
}

/* SCROLL INDICATOR */
.scroll-hint {
    position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%);
    display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
    color: var(--muted); font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase;
    z-index: 2; animation: fadeIn 1s 1.5s both;
}
.scroll-line {
    width: 1px; height: 40px;
    background: linear-gradient(to bottom, var(--accent), transparent);
    animation: scrollPulse 1.5s ease-in-out infinite;
}
@keyframes scrollPulse {
    0%, 100% { transform: scaleY(0); transform-origin: top; }
    50% { transform: scaleY(1); }
}

/* SECTIONS */
section { position: relative; z-index: 2; }
.section-inner { padding: 7rem 4rem; }

.section-label {
    font-family: 'Syne Mono', monospace;
    font-size: 0.7rem; color: var(--accent3);
    letter-spacing: 0.3em; text-transform: uppercase;
    margin-bottom: 1.2rem; display: flex; align-items: center; gap: 1rem;
}
.section-label::after { content: ''; flex: 0 0 40px; height: 1px; background: var(--accent3); opacity: 0.4; }

h2 {
    font-family: 'Instrument Serif', serif;
    font-size: clamp(2.2rem, 4vw, 3.5rem);
    line-height: 1.1; margin-bottom: 0.5rem;
    letter-spacing: -0.01em;
}

h2 em { font-style: italic; color: var(--accent); }

/* PROJETS */
.projets-section { background: linear-gradient(180deg, var(--bg) 0%, var(--surface) 100%); }

.projets-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5px;
    background: var(--border);
    border: 1px solid var(--border);
    margin-top: 4rem;
}

.projet-card {
    position: relative; overflow: hidden;
    background: var(--surface);
    aspect-ratio: 4/5;
    cursor: pointer;
    transition: background 0.4s;
}

.projet-card:hover { background: #0e0e1a; }

.projet-bg {
    position: absolute; inset: 0;
    opacity: 0; transition: opacity 0.5s;
}
.projet-card:hover .projet-bg { opacity: 1; }

.projet-3d-shape {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    transition: transform 0.6s cubic-bezier(.23,1,.32,1);
}
.projet-card:hover .projet-3d-shape { transform: scale(1.05) rotate(5deg); }

.shape-svg { opacity: 0.15; transition: opacity 0.4s; }
.projet-card:hover .shape-svg { opacity: 0.35; }

.projet-content {
    position: absolute; inset: 0;
    padding: 2rem;
    display: flex; flex-direction: column; justify-content: flex-end;
    background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, transparent 60%);
}

.projet-num {
    font-family: 'Syne Mono', monospace;
    font-size: 0.65rem; color: var(--muted);
    letter-spacing: 0.2em; margin-bottom: auto;
}

.projet-cat-pill {
    display: inline-block; padding: 0.25rem 0.7rem;
    border: 1px solid currentColor;
    font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase;
    border-radius: 20px; margin-bottom: 0.8rem;
    opacity: 0; transform: translateY(8px);
    transition: all 0.3s 0.05s;
}
.projet-card:hover .projet-cat-pill { opacity: 1; transform: translateY(0); }

.projet-titre {
    font-family: 'Instrument Serif', serif;
    font-size: 1.5rem; line-height: 1.2; margin-bottom: 0.4rem;
}

.projet-desc {
    font-size: 0.8rem; color: var(--muted); margin-bottom: 1rem;
    opacity: 0; transform: translateY(6px);
    transition: all 0.3s 0.1s;
}
.projet-card:hover .projet-desc { opacity: 1; transform: translateY(0); }

.projet-link {
    display: inline-flex; align-items: center; gap: 0.5rem;
    font-size: 0.75rem; font-weight: 600; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--text); text-decoration: none;
    opacity: 0; transform: translateX(-8px);
    transition: all 0.3s 0.15s;
}
.projet-card:hover .projet-link { opacity: 1; transform: translateX(0); }
.projet-link::after { content: '→'; transition: transform 0.2s; }
.projet-card:hover .projet-link:hover::after { transform: translateX(4px); }

/* MARQUEE */
.marquee-section {
    overflow: hidden;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    padding: 1.5rem 0;
    background: var(--surface);
}

.marquee-track {
    display: flex; gap: 3rem;
    white-space: nowrap; width: max-content;
    animation: marquee 20s linear infinite;
}
.marquee-track:hover { animation-play-state: paused; }

@keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }

.marquee-item {
    font-family: 'Instrument Serif', serif;
    font-size: 1.2rem; font-style: italic;
    color: var(--muted); display: flex; align-items: center; gap: 1.5rem;
}
.marquee-sep { color: var(--accent); font-size: 0.6rem; font-style: normal; }

/* SERVICES */
.services-section { background: var(--surface); }

.services-grid {
    display: grid; grid-template-columns: repeat(2, 1fr);
    gap: 1px; background: var(--border);
    border: 1px solid var(--border);
    margin-top: 4rem;
}

.service {
    padding: 3rem;
    background: var(--surface);
    position: relative; overflow: hidden;
    transition: background 0.4s;
}
.service::before {
    content: '';
    position: absolute; top: -50%; left: -50%;
    width: 200%; height: 200%;
    background: radial-gradient(circle at center, var(--accent-local, var(--accent)) 0%, transparent 60%);
    opacity: 0; transition: opacity 0.5s;
    transform: translate(var(--mx, 0px), var(--my, 0px));
}
.service:hover { background: #0d0d18; }
.service:hover::before { opacity: 0.04; }

.service-num {
    font-family: 'Syne Mono', monospace;
    font-size: 0.7rem; color: var(--accent);
    letter-spacing: 0.2em; margin-bottom: 2rem;
}

.service-icon {
    width: 48px; height: 48px; margin-bottom: 1.5rem;
    opacity: 0.7;
}

.service h3 {
    font-family: 'Instrument Serif', serif;
    font-size: 1.5rem; margin-bottom: 1rem;
    line-height: 1.2;
}

.service p { font-size: 0.88rem; line-height: 1.8; color: var(--muted); }

.service-arrow {
    display: inline-flex; align-items: center; gap: 0.4rem;
    font-size: 0.75rem; color: var(--accent);
    letter-spacing: 0.1em; text-transform: uppercase;
    margin-top: 1.5rem; text-decoration: none;
    opacity: 0; transform: translateX(-6px);
    transition: all 0.3s;
}
.service:hover .service-arrow { opacity: 1; transform: translateX(0); }

/* 3D GLOBE SECTION */
.globe-section {
    background: var(--bg);
    padding: 7rem 4rem;
    display: grid; grid-template-columns: 1fr 1fr;
    align-items: center; gap: 4rem;
}

.globe-canvas-wrap {
    position: relative; height: 500px;
    display: flex; align-items: center; justify-content: center;
}

#globe-canvas {
    border-radius: 50%;
    box-shadow: 0 0 80px rgba(108,99,255,0.2), 0 0 160px rgba(108,99,255,0.08);
}

.globe-ring {
    position: absolute; inset: -20px;
    border-radius: 50%;
    border: 1px solid rgba(108,99,255,0.15);
    animation: spin-slow 20s linear infinite;
}
.globe-ring-2 {
    position: absolute; inset: -40px;
    border-radius: 50%;
    border: 1px dashed rgba(108,99,255,0.08);
    animation: spin-slow 30s linear infinite reverse;
}

@keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.globe-text .section-label { margin-bottom: 1rem; }
.globe-text h2 { margin-bottom: 1.5rem; }
.globe-text p { color: var(--muted); line-height: 1.8; margin-bottom: 2rem; font-size: 0.95rem; }

.features-list { display: flex; flex-direction: column; gap: 0.8rem; }
.feature-item {
    display: flex; align-items: center; gap: 0.8rem;
    font-size: 0.88rem; color: var(--muted);
}
.feature-check {
    width: 20px; height: 20px; border-radius: 50%;
    background: rgba(108,99,255,0.15);
    border: 1px solid rgba(108,99,255,0.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.6rem; color: var(--accent); flex-shrink: 0;
}

/* CONTACT */
.contact-section { background: var(--bg); }

.contact-grid {
    display: grid; grid-template-columns: 1fr 1.3fr;
    gap: 5rem; align-items: start;
    margin-top: 4rem;
}

.contact-info-cards { display: flex; flex-direction: column; gap: 1.2rem; margin-top: 2rem; }

.info-card {
    padding: 1.5rem;
    border: 1px solid var(--border);
    background: var(--surface);
    display: flex; align-items: center; gap: 1.2rem;
    transition: border-color 0.3s, background 0.3s;
}
.info-card:hover { border-color: rgba(108,99,255,0.3); background: rgba(108,99,255,0.04); }

.info-card-icon {
    width: 42px; height: 42px; border-radius: 2px;
    background: rgba(108,99,255,0.1);
    border: 1px solid rgba(108,99,255,0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}

.info-card-label { font-size: 0.7rem; color: var(--muted); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.2rem; }
.info-card-value { font-size: 0.9rem; color: var(--text); }

/* FORM */
.form-card {
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 3rem;
}

form { display: flex; flex-direction: column; gap: 1.5rem; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }

.field { display: flex; flex-direction: column; gap: 0.5rem; }

label {
    font-size: 0.7rem; letter-spacing: 0.15em; text-transform: uppercase;
    color: var(--muted); font-family: 'Syne Mono', monospace;
}

input, textarea, select {
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    color: var(--text); padding: 0.9rem 1.2rem;
    font-family: 'Syne', sans-serif; font-size: 0.92rem;
    outline: none; transition: border-color 0.3s, background 0.3s, box-shadow 0.3s;
    border-radius: 2px;
}
input:focus, textarea:focus { 
    border-color: var(--accent); 
    background: rgba(108,99,255,0.04);
    box-shadow: 0 0 0 4px rgba(108,99,255,0.08);
}
textarea { min-height: 130px; resize: vertical; }

.btn-submit {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.7rem;
    background: linear-gradient(135deg, var(--accent), #9c6bff);
    color: white; border: none; padding: 1.1rem 2.5rem;
    font-family: 'Syne', sans-serif; font-size: 0.85rem;
    font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase;
    cursor: pointer; transition: transform 0.3s, box-shadow 0.3s;
    border-radius: 2px; align-self: flex-start; position: relative; overflow: hidden;
}
.btn-submit::after {
    content: '';
    position: absolute; top: -50%; left: -60%;
    width: 30%; height: 200%;
    background: rgba(255,255,255,0.2);
    transform: skewX(-15deg);
    transition: left 0.5s;
}
.btn-submit:hover::after { left: 130%; }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(108,99,255,0.4); }

.success-box {
    padding: 1.5rem; border: 1px solid rgba(99,255,218,0.3);
    background: rgba(99,255,218,0.05); border-radius: 2px;
    color: var(--accent3); font-size: 0.9rem;
    display: flex; align-items: center; gap: 0.8rem;
}
.error-box { padding: 1.2rem 1.5rem; border: 1px solid rgba(255,107,107,0.3); background: rgba(255,107,107,0.05); border-radius: 2px; }
.error-box li { color: #ff8f8f; font-size: 0.83rem; margin-left: 1rem; margin-bottom: 0.3rem; }

/* FOOTER */
footer {
    background: var(--surface);
    border-top: 1px solid var(--border);
    padding: 2.5rem 4rem;
    display: flex; justify-content: space-between; align-items: center;
    position: relative; z-index: 2;
}

.footer-logo { font-family: 'Instrument Serif', serif; font-size: 1.1rem; font-style: italic; color: var(--muted); }
footer p { color: var(--muted); font-size: 0.78rem; font-family: 'Syne Mono', monospace; }
.footer-links { display: flex; gap: 2rem; }
.footer-links a { color: var(--muted); text-decoration: none; font-size: 0.78rem; transition: color 0.3s; }
.footer-links a:hover { color: var(--accent); }

/* ANIMATIONS */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeInRight {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes fadeIn {
    from { opacity: 0; } to { opacity: 1; }
}

.reveal {
    opacity: 0; transform: translateY(40px);
    transition: opacity 0.8s cubic-bezier(.23,1,.32,1), transform 0.8s cubic-bezier(.23,1,.32,1);
}
.reveal.visible { opacity: 1; transform: translateY(0); }
.reveal-delay-1 { transition-delay: 0.1s; }
.reveal-delay-2 { transition-delay: 0.2s; }
.reveal-delay-3 { transition-delay: 0.3s; }
.reveal-delay-4 { transition-delay: 0.4s; }

/* RESPONSIVE */
@media (max-width: 1024px) {
    .globe-section { grid-template-columns: 1fr; }
    .globe-canvas-wrap { height: 350px; }
}
@media (max-width: 900px) {
    nav { padding: 1.2rem 2rem; }
    .section-inner, .globe-section, .contact-section .section-inner { padding: 5rem 2rem; }
    .hero { padding: 7rem 2rem 14rem; }
    .hero-stats { position: static; flex-direction: row; margin-top: 3rem; }
    .stat-item { text-align: left; padding-right: 0; padding-left: 1.5rem; }
    .stat-item::after { left: 0; right: auto; }
    .projets-grid { grid-template-columns: 1fr 1fr; }
    .services-grid { grid-template-columns: 1fr; }
    .contact-grid { grid-template-columns: 1fr; gap: 3rem; }
    footer { flex-direction: column; gap: 1rem; text-align: center; }
}
@media (max-width: 600px) {
    nav ul { display: none; }
    .projets-grid { grid-template-columns: 1fr; }
    h1 { font-size: 3rem; }
    .form-row { grid-template-columns: 1fr; }
    .hero-stats { flex-direction: column; gap: 1rem; }
}
</style>
</head>
<body>

<!-- CURSOR -->
<div id="cursor">
    <div id="cursor-ring"></div>
    <div id="cursor-dot"></div>
</div>

<!-- THREE.JS BACKGROUND -->
<canvas id="canvas-bg"></canvas>

<!-- NAV -->
<nav>
    <a href="#" class="logo"><span class="logo-dot"></span><?= htmlspecialchars($site_name) ?></a>
    <ul>
        <li><a href="#projets">Projets</a></li>
        <li><a href="#services">Services</a></li>
        <li><a href="#apropos">À Propos</a></li>
        <li><a href="#contact" class="nav-cta">Démarrer</a></li>
    </ul>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-tag">Studio Créatif · Fondé en 2018</div>
        <h1>
            Créer des<br>
            <span class="outline">expériences</span><br>
            <span class="gradient-text">mémorables</span>
        </h1>
        <p class="hero-desc">
            Nous sculptons des identités visuelles, des interfaces digitales et des 
            campagnes qui captivent, transforment et laissent une empreinte durable.
        </p>
        <div class="hero-actions">
            <a href="#projets" class="btn-primary">Voir nos projets <span class="arrow">→</span></a>
            <a href="#contact" class="btn-ghost">Parler de votre projet</a>
        </div>
    </div>

    <div class="hero-stats">
        <?php
        $stats = [["120+", "Projets réalisés"], ["48", "Clients actifs"], ["6", "Années d'expérience"]];
        foreach ($stats as $s): ?>
        <div class="stat-item">
            <span class="stat-num"><?= $s[0] ?></span>
            <span class="stat-label"><?= $s[1] ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="scroll-hint">
        <div class="scroll-line"></div>
        <span>Défiler</span>
    </div>
</section>

<!-- MARQUEE -->
<div class="marquee-section">
    <div class="marquee-track" id="marquee">
        <?php
        $words = ["Branding", "UI/UX Design", "Motion", "Web", "Identité", "Stratégie", "Animation", "Packaging", "Branding", "UI/UX Design", "Motion", "Web", "Identité", "Stratégie", "Animation", "Packaging"];
        $double = array_merge($words, $words);
        foreach ($double as $w): ?>
        <span class="marquee-item"><?= htmlspecialchars($w) ?> <span class="marquee-sep">◆</span></span>
        <?php endforeach; ?>
    </div>
</div>

<!-- PROJETS -->
<section class="projets-section" id="projets">
    <div class="section-inner">
        <div class="section-label reveal">Portfolio sélectionné</div>
        <h2 class="reveal reveal-delay-1">Nos dernières <em>réalisations</em></h2>
        <div class="projets-grid">
            <?php foreach ($projects as $i => $p):
                $letter = substr($p['titre'], 0, 1);
                $shapes = ['circle', 'square', 'triangle', 'diamond', 'hexagon', 'star'];
                $shape = $shapes[$i % count($shapes)];
            ?>
            <div class="projet-card reveal reveal-delay-<?= ($i % 3) + 1 ?>" data-color="<?= $p['color'] ?>">
                <div class="projet-bg" style="background: radial-gradient(circle at 30% 30%, <?= $p['color'] ?>22, transparent 70%)"></div>

                <div class="projet-3d-shape">
                    <svg class="shape-svg" width="200" height="200" viewBox="0 0 200 200">
                        <?php if ($shape === 'circle'): ?>
                        <circle cx="100" cy="100" r="70" fill="none" stroke="<?= $p['color'] ?>" stroke-width="1.5"/>
                        <circle cx="100" cy="100" r="45" fill="<?= $p['color'] ?>" opacity="0.3"/>
                        <?php elseif ($shape === 'square'): ?>
                        <rect x="30" y="30" width="140" height="140" fill="none" stroke="<?= $p['color'] ?>" stroke-width="1.5" transform="rotate(15,100,100)"/>
                        <rect x="55" y="55" width="90" height="90" fill="<?= $p['color'] ?>" opacity="0.3" transform="rotate(15,100,100)"/>
                        <?php elseif ($shape === 'triangle'): ?>
                        <polygon points="100,20 180,170 20,170" fill="none" stroke="<?= $p['color'] ?>" stroke-width="1.5"/>
                        <polygon points="100,55 155,155 45,155" fill="<?= $p['color'] ?>" opacity="0.3"/>
                        <?php elseif ($shape === 'diamond'): ?>
                        <polygon points="100,20 175,100 100,180 25,100" fill="none" stroke="<?= $p['color'] ?>" stroke-width="1.5"/>
                        <polygon points="100,45 150,100 100,155 50,100" fill="<?= $p['color'] ?>" opacity="0.3"/>
                        <?php elseif ($shape === 'hexagon'): ?>
                        <polygon points="100,20 170,60 170,140 100,180 30,140 30,60" fill="none" stroke="<?= $p['color'] ?>" stroke-width="1.5"/>
                        <polygon points="100,45 145,70 145,130 100,155 55,130 55,70" fill="<?= $p['color'] ?>" opacity="0.3"/>
                        <?php else: ?>
                        <polygon points="100,15 115,65 170,65 126,98 141,150 100,118 59,150 74,98 30,65 85,65" fill="none" stroke="<?= $p['color'] ?>" stroke-width="1.5"/>
                        <polygon points="100,35 111,68 146,68 119,88 130,122 100,102 70,122 81,88 54,68 89,68" fill="<?= $p['color'] ?>" opacity="0.3"/>
                        <?php endif; ?>
                    </svg>
                </div>

                <div class="projet-content">
                    <span class="projet-num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?> / <?= str_pad(count($projects), 2, '0', STR_PAD_LEFT) ?></span>
                    <span class="projet-cat-pill" style="color: <?= $p['color'] ?>; border-color: <?= $p['color'] ?>44"><?= htmlspecialchars($p['categorie']) ?></span>
                    <p class="projet-titre"><?= htmlspecialchars($p['titre']) ?></p>
                    <p class="projet-desc"><?= htmlspecialchars($p['desc']) ?></p>
                    <a href="#" class="projet-link" style="color: <?= $p['color'] ?>">Voir le projet</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section class="services-section" id="services">
    <div class="section-inner">
        <div class="section-label reveal">Ce que nous faisons</div>
        <h2 class="reveal reveal-delay-1">Domaines <em>d'expertise</em></h2>
        <div class="services-grid">
            <?php foreach ($services as $i => $s): ?>
            <div class="service reveal reveal-delay-<?= ($i % 2) + 1 ?>">
                <div class="service-num"><?= $s[0] ?></div>
                <svg class="service-icon" viewBox="0 0 48 48" fill="none">
                    <?php if ($i === 0): ?>
                    <rect x="4" y="4" width="18" height="18" stroke="rgba(108,99,255,0.6)" stroke-width="1.5"/>
                    <rect x="26" y="4" width="18" height="18" stroke="rgba(108,99,255,0.6)" stroke-width="1.5"/>
                    <rect x="4" y="26" width="18" height="18" stroke="rgba(108,99,255,0.6)" stroke-width="1.5"/>
                    <rect x="26" y="26" width="18" height="18" fill="rgba(108,99,255,0.2)" stroke="rgba(108,99,255,0.6)" stroke-width="1.5"/>
                    <?php elseif ($i === 1): ?>
                    <rect x="6" y="10" width="36" height="28" rx="3" stroke="rgba(108,99,255,0.6)" stroke-width="1.5"/>
                    <line x1="6" y1="18" x2="42" y2="18" stroke="rgba(108,99,255,0.4)" stroke-width="1.5"/>
                    <circle cx="12" cy="14" r="2" fill="rgba(108,99,255,0.6)"/>
                    <circle cx="19" cy="14" r="2" fill="rgba(108,99,255,0.4)"/>
                    <?php elseif ($i === 2): ?>
                    <polygon points="24,6 30,18 44,18 34,28 38,42 24,32 10,42 14,28 4,18 18,18" stroke="rgba(108,99,255,0.6)" stroke-width="1.5" fill="none"/>
                    <circle cx="24" cy="24" r="5" fill="rgba(108,99,255,0.3)"/>
                    <?php else: ?>
                    <path d="M8 36 Q24 8 40 36" stroke="rgba(108,99,255,0.6)" stroke-width="1.5" fill="none"/>
                    <circle cx="24" cy="24" r="4" fill="rgba(108,99,255,0.4)"/>
                    <line x1="8" y1="36" x2="40" y2="36" stroke="rgba(108,99,255,0.3)" stroke-width="1"/>
                    <?php endif; ?>
                </svg>
                <h3><?= htmlspecialchars($s[1]) ?></h3>
                <p><?= htmlspecialchars($s[2]) ?></p>
                <a href="#contact" class="service-arrow">En savoir plus →</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- GLOBE 3D SECTION -->
<section id="apropos">
    <div class="globe-section">
        <div class="globe-canvas-wrap">
            <div class="globe-ring"></div>
            <div class="globe-ring-2"></div>
            <canvas id="globe-canvas" width="400" height="400"></canvas>
        </div>
        <div class="globe-text">
            <div class="section-label reveal">Notre vision</div>
            <h2 class="reveal reveal-delay-1">Un studio <em>global</em>,<br>une âme locale</h2>
            <p class="reveal reveal-delay-2">
                Depuis Paris, nous travaillons avec des clients partout dans le monde. 
                Notre approche mêle rigueur créative française et sensibilité aux cultures 
                et marchés internationaux.
            </p>
            <div class="features-list reveal reveal-delay-3">
                <?php
                $feats = ["Approche centrée sur l'humain", "Design systems scalables", "Livraison dans les délais", "Support post-lancement inclus", "Transparence totale sur le process"];
                foreach ($feats as $f): ?>
                <div class="feature-item">
                    <div class="feature-check">✓</div>
                    <?= htmlspecialchars($f) ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT -->
<section class="contact-section" id="contact">
    <div class="section-inner">
        <div class="section-label reveal">Parlons de votre projet</div>
        <h2 class="reveal reveal-delay-1">Commençons <em>quelque chose</em></h2>
        <div class="contact-grid">
            <div>
                <p class="reveal reveal-delay-2" style="color: var(--muted); line-height: 1.8; font-size: 0.95rem; margin-bottom: 2rem;">
                    Vous avez un projet ambitieux ? Nous serions ravis d'en discuter et de voir 
                    comment nous pouvons vous aider à le concrétiser.
                </p>
                <div class="contact-info-cards">
                    <?php
                    $infos = [
                        ["✉", "Email", "contact@lumiere-studio.fr"],
                        ["☎", "Téléphone", "+33 1 23 45 67 89"],
                        ["⌖", "Localisation", "Paris, France"],
                        ["◷", "Disponibilité", "Lun–Ven, 9h–18h"],
                    ];
                    foreach ($infos as $info): ?>
                    <div class="info-card reveal">
                        <div class="info-card-icon"><?= $info[0] ?></div>
                        <div>
                            <div class="info-card-label"><?= $info[1] ?></div>
                            <div class="info-card-value"><?= htmlspecialchars($info[2]) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-card reveal reveal-delay-2">
                <?php if ($message_sent): ?>
                <div class="success-box">
                    <span style="font-size:1.2rem">✓</span>
                    Message envoyé avec succès ! Nous vous répondrons dans les 24h.
                </div>
                <?php else: ?>
                <?php if (!empty($errors)): ?>
                <ul class="error-box" style="margin-bottom:1.5rem">
                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <form method="POST" action="#contact">
                    <div class="form-row">
                        <div class="field">
                            <label for="nom">Nom complet</label>
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" placeholder="Jean Dupont">
                        </div>
                        <div class="field">
                            <label for="email">Adresse email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="jean@exemple.com">
                        </div>
                    </div>
                    <div class="field">
                        <label for="sujet">Type de projet</label>
                        <input type="text" id="sujet" name="sujet" placeholder="Branding, Web, Motion...">
                    </div>
                    <div class="field">
                        <label for="message">Votre message</label>
                        <textarea id="message" name="message" placeholder="Décrivez votre projet, vos objectifs, votre budget estimé..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Envoyer le message ✦</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <span class="footer-logo"><?= htmlspecialchars($site_name) ?></span>
    <p>&copy; <?= $current_year ?> · Tous droits réservés</p>
    <div class="footer-links">
        <a href="#">Confidentialité</a>
        <a href="#">Mentions légales</a>
        <a href="#">Instagram</a>
    </div>
</footer>

<script>
// ─── CURSOR ───────────────────────────────────────────
const dot = document.getElementById('cursor-dot');
const ring = document.getElementById('cursor-ring');
let mx = 0, my = 0, rx = 0, ry = 0;

document.addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY; });

function animCursor() {
    dot.style.left = mx + 'px'; dot.style.top = my + 'px';
    rx += (mx - rx) * 0.12; ry += (my - ry) * 0.12;
    ring.style.left = rx + 'px'; ring.style.top = ry + 'px';
    requestAnimationFrame(animCursor);
}
animCursor();

document.querySelectorAll('a, button, .projet-card, .service, .info-card').forEach(el => {
    el.addEventListener('mouseenter', () => document.body.classList.add('hovering'));
    el.addEventListener('mouseleave', () => document.body.classList.remove('hovering'));
});

// ─── THREE.JS BACKGROUND PARTICLES ─────────────────────
(function() {
    const canvas = document.getElementById('canvas-bg');
    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(window.innerWidth, window.innerHeight);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.z = 30;

    // Particles
    const geo = new THREE.BufferGeometry();
    const count = 3000;
    const positions = new Float32Array(count * 3);
    const colors = new Float32Array(count * 3);
    const palette = [
        new THREE.Color('#6c63ff'),
        new THREE.Color('#ff6b6b'),
        new THREE.Color('#63ffda'),
        new THREE.Color('#ffffff'),
    ];

    for (let i = 0; i < count; i++) {
        positions[i*3]   = (Math.random() - 0.5) * 80;
        positions[i*3+1] = (Math.random() - 0.5) * 80;
        positions[i*3+2] = (Math.random() - 0.5) * 80;
        const c = palette[Math.floor(Math.random() * palette.length)];
        colors[i*3] = c.r; colors[i*3+1] = c.g; colors[i*3+2] = c.b;
    }

    geo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    const mat = new THREE.PointsMaterial({
        size: 0.06, vertexColors: true, transparent: true, opacity: 0.6,
        sizeAttenuation: true
    });

    const particles = new THREE.Points(geo, mat);
    scene.add(particles);

    // Floating geometric wireframes
    const wireMat = new THREE.MeshBasicMaterial({ color: 0x6c63ff, wireframe: true, transparent: true, opacity: 0.04 });

    const shapes3d = [
        new THREE.IcosahedronGeometry(8, 1),
        new THREE.OctahedronGeometry(6, 0),
        new THREE.TorusGeometry(5, 1.5, 8, 20),
    ];

    const meshes = shapes3d.map((g, i) => {
        const m = new THREE.Mesh(g, wireMat.clone());
        m.position.set((i - 1) * 15, (Math.random() - 0.5) * 10, -10 + i * 3);
        scene.add(m);
        return m;
    });

    // Mouse parallax
    let tmx = 0, tmy = 0;
    document.addEventListener('mousemove', e => {
        tmx = (e.clientX / window.innerWidth - 0.5) * 2;
        tmy = (e.clientY / window.innerHeight - 0.5) * 2;
    });

    let camX = 0, camY = 0, t = 0;

    function animate() {
        requestAnimationFrame(animate);
        t += 0.005;

        camX += (tmx * 3 - camX) * 0.03;
        camY += (-tmy * 3 - camY) * 0.03;
        camera.position.x = camX;
        camera.position.y = camY;
        camera.lookAt(scene.position);

        particles.rotation.y = t * 0.05;
        particles.rotation.x = t * 0.02;

        meshes.forEach((m, i) => {
            m.rotation.x += 0.003 + i * 0.001;
            m.rotation.y += 0.005 + i * 0.002;
            m.position.y = Math.sin(t + i * 1.5) * 3;
        });

        renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
})();

// ─── GLOBE 3D ────────────────────────────────────────
(function() {
    const canvas = document.getElementById('globe-canvas');
    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(400, 400);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 100);
    camera.position.z = 4;

    // Globe wireframe
    const globeGeo = new THREE.SphereGeometry(1.5, 32, 32);
    const globeMat = new THREE.MeshBasicMaterial({
        color: 0x6c63ff, wireframe: true, transparent: true, opacity: 0.12
    });
    const globe = new THREE.Mesh(globeGeo, globeMat);
    scene.add(globe);

    // Solid inner sphere
    const innerGeo = new THREE.SphereGeometry(1.48, 32, 32);
    const innerMat = new THREE.MeshPhongMaterial({
        color: 0x0a0a20, transparent: true, opacity: 0.95,
        emissive: 0x120820
    });
    scene.add(new THREE.Mesh(innerGeo, innerMat));

    // Glowing atmosphere
    const atmGeo = new THREE.SphereGeometry(1.58, 32, 32);
    const atmMat = new THREE.MeshBasicMaterial({
        color: 0x6c63ff, transparent: true, opacity: 0.04, side: THREE.BackSide
    });
    scene.add(new THREE.Mesh(atmGeo, atmMat));

    // Dots on globe surface
    const dotGeo = new THREE.BufferGeometry();
    const dotCount = 400;
    const dotPos = new Float32Array(dotCount * 3);
    for (let i = 0; i < dotCount; i++) {
        const phi = Math.acos(-1 + (2 * i) / dotCount);
        const theta = Math.sqrt(dotCount * Math.PI) * phi;
        dotPos[i*3]   = 1.52 * Math.sin(phi) * Math.cos(theta);
        dotPos[i*3+1] = 1.52 * Math.sin(phi) * Math.sin(theta);
        dotPos[i*3+2] = 1.52 * Math.cos(phi);
    }
    dotGeo.setAttribute('position', new THREE.BufferAttribute(dotPos, 3));
    const dotMat = new THREE.PointsMaterial({ color: 0x6c63ff, size: 0.025, transparent: true, opacity: 0.7 });
    scene.add(new THREE.Points(dotGeo, dotMat));

    // Rings
    [1.7, 2.0, 2.3].forEach((r, i) => {
        const ringGeo = new THREE.TorusGeometry(r, 0.005, 2, 80);
        const ringMat = new THREE.MeshBasicMaterial({ color: i === 0 ? 0x6c63ff : 0x63ffda, transparent: true, opacity: 0.15 - i * 0.04 });
        const ring = new THREE.Mesh(ringGeo, ringMat);
        ring.rotation.x = Math.PI * (0.2 + i * 0.3);
        ring.rotation.y = i * 0.5;
        scene.add(ring);
    });

    // Lights
    scene.add(new THREE.AmbientLight(0x220033, 1));
    const point = new THREE.PointLight(0x6c63ff, 2, 10);
    point.position.set(3, 3, 3);
    scene.add(point);

    let t = 0;
    function animGlobe() {
        requestAnimationFrame(animGlobe);
        t += 0.005;
        globe.rotation.y = t * 0.3;
        renderer.render(scene, camera);
    }
    animGlobe();
})();

// ─── SCROLL REVEAL ───────────────────────────────────
const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.12 });

document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

// ─── SERVICE MOUSE GLOW ──────────────────────────────
document.querySelectorAll('.service').forEach(el => {
    el.addEventListener('mousemove', e => {
        const r = el.getBoundingClientRect();
        el.style.setProperty('--mx', (e.clientX - r.left - r.width / 2) + 'px');
        el.style.setProperty('--my', (e.clientY - r.top - r.height / 2) + 'px');
    });
});

// ─── PROJET CARD TILT ────────────────────────────────
document.querySelectorAll('.projet-card').forEach(card => {
    card.addEventListener('mousemove', e => {
        const r = card.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width - 0.5;
        const y = (e.clientY - r.top) / r.height - 0.5;
        card.style.transform = `perspective(800px) rotateY(${x * 10}deg) rotateX(${-y * 10}deg) translateY(-10px)`;
    });
    card.addEventListener('mouseleave', () => {
        card.style.transform = '';
    });
});
</script>
</body>
</html>