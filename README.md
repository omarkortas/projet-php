# ✨ Lumière Studio — Site Web PHP

Site vitrine moderne pour un studio créatif, avec effets 3D, animations et formulaire de contact avec stockage JSON.

---

## 🚀 Technologies utilisées

- **PHP 8+** — backend et gestion du formulaire
- **Three.js** — particules 3D et globe interactif
- **CSS3** — animations, glassmorphism, curseur personnalisé
- **JSON** — stockage des messages sans base de données

---

## 📁 Structure du projet

```
projet-php/
├── index.php        → Site principal (hero, projets, services, contact)
├── admin.php        → Interface d'administration des messages
├── messages.json    → Créé automatiquement au premier message (ignoré par git)
└── README.md
```

---

## ✨ Fonctionnalités

- 🌌 Background 3D avec particules animées (Three.js)
- 🌍 Globe 3D interactif
- 🖱️ Curseur personnalisé avec effet magnétique
- 📐 Effet tilt 3D sur les cartes projets
- 📩 Formulaire de contact avec validation PHP
- 💾 Stockage des messages en JSON
- 🔐 Interface admin protégée par mot de passe
- 📱 Responsive mobile

---

## ⚙️ Installation locale

```bash
# Cloner le projet
git clone https://github.com/omarkortas/projet-php.git

# Lancer avec PHP built-in server
cd projet-php
php -S localhost:8000
```

Puis ouvrir **http://localhost:8000**

---

## 🌐 Déploiement sur InfinityFree

1. Créer un compte sur [infinityfree.com](https://infinityfree.com)
2. Uploader `index.php` et `admin.php` dans le dossier `htdocs/`
3. Mettre les permissions du dossier `htdocs` en **755**
4. Accéder au site via l'URL fournie par InfinityFree

---

## 🔐 Accès Admin

```
URL      : votresite.rf.gd/admin.php
Login    : admin
Password : lumiere2024  ← À changer dans admin.php
```

> ⚠️ Pensez à changer les identifiants avant la mise en ligne !

---

## 📬 Stockage des messages

Les messages du formulaire sont sauvegardés dans `messages.json` :

```json
[
  {
    "id": "msg_abc123",
    "date": "2024-01-15 14:32:00",
    "nom": "Jean Dupont",
    "email": "jean@exemple.com",
    "sujet": "Branding",
    "message": "Bonjour, j'ai un projet...",
    "ip": "192.168.1.1"
  }
]
```

---

## 👤 Auteur

**Omar Kortas** — [@omarkortas](https://github.com/omarkortas)

---

## 📄 Licence

MIT — libre d'utilisation et de modification.
