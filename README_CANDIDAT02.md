# 🛡️ ORION — Candidat 02 (Milantoni Guillaume)

> **Système de contrôle d'accès intelligent pour bâtiment scolaire**
> BTS CIEL Option A — Informatique et Réseaux — Session 2026
> Lycée Jean Zay, Jarny

---

## 🎯 Présentation

Orion est un système de contrôle d'accès sécurisé combinant trois méthodes d'authentification : **badge RFID**, **QR Code temporaire** et **code PIN**. Ce dépôt concerne spécifiquement la partie embarquée et l'API REST développées par le **Candidat 02**.

### 👥 Répartition de l'équipe

| Candidat | Nom | Responsabilité | Branche Git |
|----------|-----|----------------|-------------|
| 01 | LEPRINI Jonathan | Interface web Symfony (gestion utilisateurs, logs, QR temporaires) | `main` |
| **02** | **MILANTONI Guillaume** | **Module ESP32, API REST, identification RFID/QR/PIN, contrôle gâche** | **`esp32_1000An`** |
| 03 | DAKICHE Adelane | Monitoring physique, détection d'intrusion, supervision énergétique | À définir |

---

## 🏗️ Architecture de ma partie

```
┌─────────────────────────────────────────────────────────────────┐
│                         UTILISATEUR                             │
│                  (Badge RFID / QR Code / PIN)                   │
└──────────────────────────────┬──────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                    ESP32 (module embarqué)                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐ │
│  │ Lecteur RFID │  │ Clavier PIN  │  │ Écran tactile 5"     │ │
│  │   MFRC522    │  │    4x4       │  │    480x800 px        │ │
│  └──────────────┘  └──────────────┘  └──────────────────────┘ │
│                                                                 │
│  Contrôle : Gâche électrique (relais) + LEDs + Buzzer          │
└──────────────────────────────┬──────────────────────────────────┘
                               │ HTTPS + Bearer Token
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                   API REST (PHP - Laragon)                      │
│  • POST /api/badge.php    → Vérifie un badge RFID              │
│  • POST /api/qrcode.php   → Vérifie un QR Code                 │
│  • POST /api/pin.php      → Vérifie un code PIN                │
│  • POST /api/log.php      → Journalise les événements          │
│                                                                 │
│  Sécurité : Bearer Token • Rate Limiting • CORS • Regex RFID   │
└──────────────────────────────┬──────────────────────────────────┘
                               │ SQL
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Base de données MariaDB                      │
│   badge • qrcode • code • utilisateur • log_access • porte     │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📦 Prérequis

### Logiciels à installer

| Outil | Version | Usage |
|-------|---------|-------|
| **Laragon** | 6.0+ | Serveur Apache + MariaDB local |
| **VS Code** | Stable | Éditeur principal |
| **PlatformIO** (extension VS Code) | Récente | Développement ESP32 |
| **Python** | 3.10+ | Script de test `simulateur.py` |
| **Git** | 2.40+ | Versionning |
| **Wokwi** (extension VS Code) | Récente | Simulation ESP32 (optionnel) |
| **Postman** | Récente | Tests manuels API (optionnel) |

### Matériel (déploiement réel)

- ESP32 (DevKit v1 ou ESP32-C3)
- Lecteur RFID MFRC522
- Clavier matriciel 4x4
- Écran LCD 5" 480x800 (ou LCD1602 I2C en simulation)
- Relais 5V + gâche électrique
- LEDs (verte/rouge) + buzzer

---

## ⚙️ Installation pas à pas

### 1. Cloner le dépôt

```bash
cd C:\laragon\www
git clone https://github.com/Astreros/Orion.git orion
cd orion
git checkout esp32_1000An
```

### 2. Configurer l'API (partie serveur)

#### 2.1 Créer le fichier de sécurité

```bash
cp api/config/security.php.example api/config/security.php
```

Ouvrir `api/config/security.php` et remplacer `VOTRE_TOKEN_SECRET_ICI` par votre vrai token (ex : `orion-token-secret-2026`).

#### 2.2 Importer la base de données

1. Lancer Laragon et démarrer **Apache** + **MySQL**
2. Ouvrir HeidiSQL ou phpMyAdmin
3. Créer la base `orion`
4. Importer le fichier `orion_V2.sql` (à demander au groupe)

#### 2.3 Tester l'API

Ouvrir dans un navigateur : [http://localhost/orion/api/supervision.php](http://localhost/orion/api/supervision.php)

Tu dois voir l'état de l'API, la connexion BDD et les endpoints disponibles.

### 3. Configurer l'ESP32 (partie embarquée)

#### 3.1 Créer le fichier de configuration

```bash
cd esp32/include
cp config.h.example config.h
```

Ouvrir `config.h` et remplir :

```c
#define WIFI_SSID       "TonReseauWiFi"
#define WIFI_PASSWORD   "TonMotDePasse"
#define API_BASE        "http://192.168.X.X/orion/api"  // IP de ton PC
#define API_TOKEN       "orion-token-secret-2026"

// Décommenter pour simulation Wokwi :
// #define WOKWI_SIM
```

> 💡 **Astuce** : pour trouver ton IP locale, lance `ipconfig` dans PowerShell et prends l'adresse IPv4.

> ⚠️ **Contrainte lycée** : le réseau du lycée bloque les ESP32. Utiliser un **hotspot téléphone** avec le PC connecté dessus.

#### 3.2 Compiler et téléverser

Depuis VS Code avec PlatformIO installé :

1. Ouvrir le dossier `esp32/` dans VS Code (**pas la racine !**)
2. `Ctrl+Shift+P` → **PlatformIO: New Terminal**
3. Dans ce terminal :

```bash
pio run                  # Compilation
pio run --target upload  # Téléversement (ESP32 réel)
```

Pour la simulation Wokwi :
1. Ouvrir `esp32/wokwi.toml`
2. Cliquer sur **"Start Simulation"**

---

## 🧪 Tests

### Test 1 — Simulateur Python (automatisé)

Script qui envoie 6 requêtes test (3 autorisées + 3 refusées) pour valider l'API.

```bash
cd tests_esp32
python simulateur.py
```

**Résultat attendu** : 6/6 tests OK (badge, QR, PIN valides retournent 200 ; invalides retournent 403).

### Test 2 — Interface graphique HTML

```bash
# Ouvrir dans un navigateur :
tests_esp32/simulateur_graphique.html
```

Interface pour tester manuellement chaque endpoint avec des boutons.

### Test 3 — Postman (manuel)

Exemple de requête pour tester le badge :

```http
POST http://localhost/orion/api/badge.php
Authorization: Bearer orion-token-secret-2026
Content-Type: application/json

{
  "uid": "AB:CD:EF:12"
}
```

**Réponse attendue (accès autorisé)** :
```json
{
  "status": "success",
  "access": "granted",
  "user_id": 2,
  "timestamp": "2026-04-23T10:30:00+02:00"
}
```

### Test 4 — Simulation Wokwi

1. Ouvrir `esp32/wokwi.toml` dans VS Code
2. Lancer la simulation
3. **PIN test** : `1234`
4. **Badge test** : `AB:CD:EF:12`

---

## 📁 Structure des dossiers

```
orion/
├── api/                          ← API REST PHP (MA PARTIE)
│   ├── .htaccess                 ← Fix Bearer header Apache
│   ├── badge.php                 ← Endpoint vérification RFID
│   ├── qrcode.php                ← Endpoint vérification QR Code
│   ├── pin.php                   ← Endpoint vérification PIN
│   ├── supervision.php           ← Dashboard diagnostic
│   ├── config/
│   │   ├── security.php          ← 🔒 Secrets (gitignored)
│   │   └── security.php.example  ← Template public
│   ├── functions/
│   │   ├── badge_functions.php   ← Logique badge
│   │   ├── qrcode_functions.php  ← Logique QR
│   │   ├── pin_functions.php     ← Logique PIN
│   │   └── db_functions.php      ← Accès BDD
│   └── logs/                     ← Logs d'accès (RGPD, gitignored)
│
├── esp32/                        ← Firmware ESP32 (MA PARTIE)
│   ├── platformio.ini            ← Config PlatformIO
│   ├── wokwi.toml                ← Config simulation Wokwi
│   ├── diagram.json              ← Schéma Wokwi (composants)
│   ├── include/
│   │   ├── config.h              ← 🔒 Secrets WiFi + token (gitignored)
│   │   ├── config.h.example      ← Template public
│   │   ├── api_client.h
│   │   ├── wifi_manager.h
│   │   ├── display_manager.h
│   │   └── Buzzer.h
│   └── src/
│       ├── main.cpp              ← Boucle principale + machine à états
│       ├── api_client.cpp        ← Requêtes HTTPS vers l'API
│       ├── wifi_manager.cpp      ← Connexion WiFi
│       ├── rfid_reader.cpp       ← Lecture MFRC522
│       ├── qr_reader.cpp         ← Lecture scanner QR USB
│       ├── api_reader.cpp
│       ├── display_manager.cpp   ← Affichage écran
│       └── Buzzer.cpp
│
├── tests_esp32/                  ← Tests automatisés (MA PARTIE)
│   ├── simulateur.py             ← 6 tests API automatiques
│   └── simulateur_graphique.html ← Interface test manuelle
│
├── [Symfony]                     ← Interface web (Candidat 01 Jonathan)
│   ├── assets/ bin/ config/
│   ├── migrations/ public/ src/
│   ├── templates/ tests/ var/
│   └── composer.json
│
└── README_CANDIDAT02.md          ← Ce fichier
```

---

## 🔐 Sécurité implémentée

Mesures de cybersécurité appliquées conformément aux exigences BTS CIEL Option A :

### Côté API (PHP)

- 🔑 **Authentification Bearer Token** avec comparaison en temps constant (`hash_equals`) pour éviter les attaques par timing
- 🚦 **Rate limiting par IP** via fichiers temporaires (protection brute-force)
- 📝 **Validation stricte des entrées** par regex (ex : format RFID `AB:CD:EF:12`)
- 🛡️ **Headers de sécurité HTTP** : `X-Content-Type-Options`, `X-Frame-Options`, `Strict-Transport-Security`
- 🌐 **Gestion CORS** pour requêtes preflight OPTIONS
- 📊 **Journalisation centralisée** des accès dans `api/logs/`

### Côté ESP32

- 🔒 **Token stocké dans `config.h`** (jamais committé, via `.gitignore`)
- 🔐 **Communication HTTPS** avec certificats (en production)
- ⏱️ **Verrouillage anti-brute-force** sur clavier PIN (3 tentatives puis timeout)
- 📦 **Séparation code/config** : `config.h.example` public, `config.h` privé

### Respect du RGPD

- Logs contenant des données personnelles (UIDs badges, timestamps) **exclus du versioning Git**
- Accès aux logs restreint au dossier `api/logs/` côté serveur uniquement

---

## 🐛 Problèmes connus & solutions

| Problème | Solution |
|----------|----------|
| `Authorization: Bearer` header manquant en PHP | Fix via `.htaccess` (rewrite rule) — voir `api/.htaccess` |
| CORS preflight bloqué | Gérer `OPTIONS` dans `security.php` avant toute authentification |
| Compilation ESP32 échoue sur `Buzzer.h` | Header dans `include/`, implémentation dans `src/` |
| ArduinoJson v7 incompatible | Remplacer `StaticJsonDocument<N>` par `JsonDocument` |
| `pio run` ne marche pas | Lancer depuis un **PlatformIO Terminal** (pas PowerShell standard) |
| ESP32 ne se connecte pas au WiFi du lycée | Utiliser un hotspot téléphone (réseau lycée bloque IoT) |

---

## 🛠️ Workflow Git pour cette branche

```bash
# Vérifier l'état
git status
git branch   # doit afficher * esp32_1000An

# Ajouter et committer
git add .
git commit -m "feat: description claire du changement"

# Pousser sur GitHub
git push origin esp32_1000An

# Récupérer les changements de main (merge de Jonathan)
git fetch origin
git merge origin/main
```

---

## 📚 Ressources

- [Documentation PlatformIO](https://docs.platformio.org/)
- [ArduinoJson v7](https://arduinojson.org/v7/)
- [ESP32 Arduino Core](https://docs.espressif.com/projects/arduino-esp32/)
- [Wokwi Simulator](https://wokwi.com/)
- [Référentiel BTS CIEL](https://eduscol.education.fr/sti/ciel)

---

## ✉️ Contact

**Candidat 02 — MILANTONI Guillaume**
BTS CIEL Option A — Session 2026
Lycée Jean Zay, 2 rue de la tuilerie, 54800 Jarny

---

*Dernière mise à jour : 23 avril 2026*
