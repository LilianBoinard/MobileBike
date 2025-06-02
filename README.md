
# 🚴‍♂️ MobileBike — Refonte MVC PSR-Compliant

Refonte de l’application **MobileBike**, un projet de gestion de service de réparation de vélos, développé avec un **mini framework MVC PHP** respectant les **standards PSR**. Cette architecture modulaire est conçue pour être simple, maintenable et évolutive.

---

## 📁 Structure du projet

```

mobilebike/
├── public/                 # Répertoire public (point d’entrée)
│   └── index.php
├── src/
│   ├── App/                # Code métier de l'application (Controllers, Models, Routes)
│   └── Core/               # Mini-framework MVC (Http, Router, Container, View, etc.)
├── src/scss/              # Feuilles de style SASS
├── .env                   # Variables d’environnement
├── composer.json
└── README.md

````

---

## ⚙️ Fonctionnalités

- 🔧 Architecture **MVC légère**
- 📦 Conteneur d'injection de dépendances conforme à **PSR-11**
- 🌐 Routing simple et intuitif
- 🖼️ Intégration **Twig** pour les vues
- 🌱 Chargement d’environnement via **Dotenv**
- 🧱 Respect des standards **PSR-7**, **PSR-15**, **PSR-11**, **PSR-3**
- 💅 Compilation SASS avec **scripts NPM-like via Composer**

---

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/liliaboinard/mobilebike.git
cd mobilebike
````

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Configurer l’environnement

Crée un fichier `.env` à la racine :

```dotenv
DB_HOST=localhost
DB_NAME=mobilebike
DB_USER=root
DB_PASSWORD=secret
```

### 4. Lancer le serveur de développement

```bash
composer dev
```

L'application sera accessible sur [http://localhost:8000](http://localhost:8000)

---

## 🎨 Compilation SCSS

* Lancer en mode **watch** :

```bash
composer sass:watch
```

* Compiler une seule fois (mode compressé) :

```bash
composer sass:build
```

Les fichiers CSS sont générés dans `public/assets/css`.

---

## 🧪 Standards et dépendances

| Nom                     | Description                            |
| ----------------------- | -------------------------------------- |
| `psr/http-message`      | Interfaces pour requêtes/réponses HTTP |
| `psr/http-server-*`     | Middleware & handlers                  |
| `psr/container`         | Injection de dépendances               |
| `psr/log`               | Standard de logging                    |
| `guzzlehttp/psr7`       | Implémentation PSR-7                   |
| `httpsoft/http-emitter` | Émetteur HTTP compatible PSR-7         |
| `twig/twig`             | Moteur de templates                    |
| `vlucas/phpdotenv`      | Chargement de fichiers `.env`          |



---

## 📝 Licence

Projet développé dans un cadre pédagogique. Aucun usage commercial sans autorisation explicite.

