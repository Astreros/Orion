# Orion API — Structure des fichiers

## Architecture (principe 1 fichier = 1 fonction principale)

```
orion-api/
│
├── badge.php          ← Point d'entrée RFID   (3 lignes, appelle traiter_requete_badge())
├── qrcode.php         ← Point d'entrée QR     (3 lignes, appelle traiter_requete_qrcode())
├── pin.php            ← Point d'entrée PIN     (3 lignes, appelle traiter_requete_pin())
│
├── config/
│   └── security.php   ← Fonctions partagées : token, rate limit, logs, réponses JSON
│
├── functions/
│   ├── db_functions.php      ← Connexion PDO + requêtes BDD (verifier_acces_bdd, etc.)
│   ├── badge_functions.php   ← Logique RFID   (traiter_requete_badge, valider_rfid)
│   ├── qrcode_functions.php  ← Logique QR     (traiter_requete_qrcode, valider_token_qr)
│   └── pin_functions.php     ← Logique PIN    (traiter_requete_pin, valider_pin)
│
├── logs/              ← Logs journaliers (orion_YYYY-MM-DD.log)
│
└── simulateur.py      ← 8 tests automatisés Python
```

## Sécurité appliquée sur les 3 endpoints

| Mesure                  | badge | qrcode | pin |
|-------------------------|-------|--------|-----|
| Méthode POST forcée     | ✓     | ✓      | ✓   |
| Bearer token            | ✓     | ✓      | ✓   |
| Rate limiting           | ✓     | ✓      | ✓ (renforcé 5/5min) |
| Validation input regex  | ✓     | ✓      | ✓   |
| Journalisation          | ✓     | ✓      | ✓   |
| En-têtes HTTP sécurité  | ✓     | ✓      | ✓   |
| PIN jamais en clair log | —     | —      | ✓   |
| Délai anti-brute-force  | —     | —      | ✓   |

## Lancer les tests

```bash
python simulateur.py
```

Résultat attendu : 8/8 tests réussis.
