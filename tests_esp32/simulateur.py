"""
simulateur.py — Tests automatisés API Orion
Valeurs alignées sur les données réelles en base.
"""

import requests
import json

# ─── Configuration ────────────────────────────────────────────────────────────
BASE_URL = "http://localhost/orion/api"
TOKEN    = "orion-token-secret-2026"
HEADERS  = {
    "Content-Type":  "application/json",
    "Authorization": f"Bearer {TOKEN}",
}

# ─── Valeurs de test (doivent exister en base avec actif=1) ──────────────────
RFID_VALIDE   = "AB:CD:EF:12"   # table badge, ligne id=9
RFID_INVALIDE = "00:00:00:00"   # n'existe pas en base

QRCODE_VALIDE   = "QRTEST123"           # table qrcode, ligne id=4
QRCODE_INVALIDE = "QRCODE-INCONNU-999"  # n'existe pas en base

PIN_VALIDE   = "1234"   # table code, ligne id=3
PIN_INVALIDE = "0000"   # n'existe pas en base

# Couleurs terminal
VERT  = "\033[92m"
ROUGE = "\033[91m"
JAUNE = "\033[93m"
RESET = "\033[0m"
GRAS  = "\033[1m"

# ─── Utilitaires ──────────────────────────────────────────────────────────────
def titre(texte):
    print(f"\n{GRAS}{'─' * 50}{RESET}")
    print(f"{GRAS}{texte}{RESET}")
    print(f"{GRAS}{'─' * 50}{RESET}")

def afficher_resultat(nom, reponse, attendu):
    ok = reponse.status_code == attendu
    symbole = f"{VERT}✓" if ok else f"{ROUGE}✗"
    print(f"  {symbole} {nom}{RESET}")
    print(f"    HTTP : {reponse.status_code} (attendu {attendu})")
    try:
        print(f"    JSON : {json.dumps(reponse.json(), ensure_ascii=False)}")
    except Exception:
        print(f"    Body : {reponse.text[:120]}")
    return ok

# ─── Tests Badge RFID ─────────────────────────────────────────────────────────
def test_badge_autorise():
    r = requests.post(f"{BASE_URL}/badge.php", headers=HEADERS, json={"rfid": RFID_VALIDE})
    return afficher_resultat("Badge RFID autorisé", r, 200)

def test_badge_refuse():
    r = requests.post(f"{BASE_URL}/badge.php", headers=HEADERS, json={"rfid": RFID_INVALIDE})
    return afficher_resultat("Badge RFID refusé", r, 403)

# ─── Tests QR Code ────────────────────────────────────────────────────────────
def test_qrcode_autorise():
    r = requests.post(f"{BASE_URL}/qrcode.php", headers=HEADERS, json={"token": QRCODE_VALIDE})
    return afficher_resultat("QR Code autorisé", r, 200)

def test_qrcode_refuse():
    r = requests.post(f"{BASE_URL}/qrcode.php", headers=HEADERS, json={"token": QRCODE_INVALIDE})
    return afficher_resultat("QR Code refusé / expiré", r, 403)

# ─── Tests PIN ────────────────────────────────────────────────────────────────
def test_pin_autorise():
    r = requests.post(f"{BASE_URL}/pin.php", headers=HEADERS, json={"pin": PIN_VALIDE})
    return afficher_resultat("Code PIN autorisé", r, 200)

def test_pin_refuse():
    r = requests.post(f"{BASE_URL}/pin.php", headers=HEADERS, json={"pin": PIN_INVALIDE})
    return afficher_resultat("Code PIN refusé", r, 403)

# ─── Tests sécurité ───────────────────────────────────────────────────────────
def test_sans_token():
    r = requests.post(f"{BASE_URL}/badge.php",
                      headers={"Content-Type": "application/json"},
                      json={"rfid": RFID_VALIDE})
    return afficher_resultat("Sans token Bearer → 401", r, 401)

def test_rfid_format_invalide():
    r = requests.post(f"{BASE_URL}/badge.php", headers=HEADERS, json={"rfid": "pas_du_hex!!"})
    return afficher_resultat("RFID format invalide → 400", r, 400)

# ─── Runner ───────────────────────────────────────────────────────────────────
def main():
    titre("ORION — Simulateur de tests API")

    tests = [
        ("Badge RFID", [test_badge_autorise,  test_badge_refuse]),
        ("QR Code",    [test_qrcode_autorise, test_qrcode_refuse]),
        ("Code PIN",   [test_pin_autorise,    test_pin_refuse]),
        ("Sécurité",   [test_sans_token,      test_rfid_format_invalide]),
    ]

    total = reussis = 0
    for categorie, fonctions in tests:
        print(f"\n{JAUNE}[{categorie}]{RESET}")
        for fn in fonctions:
            total += 1
            if fn():
                reussis += 1

    titre(f"Résultats : {reussis}/{total} tests réussis")
    if reussis == total:
        print(f"{VERT}{GRAS}✓ Tous les tests sont passés !{RESET}\n")
    else:
        print(f"{ROUGE}{GRAS}✗ {total - reussis} test(s) échoué(s){RESET}\n")

if __name__ == "__main__":
    main()
