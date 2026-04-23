/*************************************************************
 * ORION — Contrôle d'accès PIN
 * BTS CIEL 2026 — MILANTONI Guillaume (Candidat 02)
 * Hardware : ESP32-C3 + LCD1602 + Clavier 4x4
 * Inspiré de l'ESP32-C3 Alarm Clock (Wokwi)
 *************************************************************/

#include "Arduino.h"
#include "Keypad.h"
#include "config.h"
#include "display_manager.h"
#include "wifi_manager.h"
#include "api_client.h"
#include "Buzzer.h"

// ─── Clavier 4x4 (même câblage que Alarm Clock) ───────────────────────────────
static const char TOUCHES[KEYPAD_ROWS][KEYPAD_COLS] = {
    {'1','2','3','A'},
    {'4','5','6','B'},
    {'7','8','9','C'},
    {'*','0','#','D'}
};
static uint8_t RANGEES[KEYPAD_ROWS]  = {4, 5, 6, 7};
static uint8_t COLONNES[KEYPAD_COLS] = {1, 0, 3, 2};
static Keypad clavier = Keypad(makeKeymap(TOUCHES), RANGEES, COLONNES,
                               KEYPAD_ROWS, KEYPAD_COLS);

// ─── États ───────────────────────────────────────────────────────────────────
enum Etat { ACCUEIL, SAISIE, VERIFICATION, SUCCES, REFUS, ERREUR };
static Etat   etatActuel    = ACCUEIL;
static String pinEnCours    = "";
static String prenomAutorise = "";
static unsigned long tempsEtat = 0;

// ─── GPIO ─────────────────────────────────────────────────────────────────────
void allumerLED(int pin)  { digitalWrite(pin, HIGH); }
void eteindreLEDs()       { digitalWrite(PIN_LED_OK, LOW); digitalWrite(PIN_LED_DENY, LOW); }

void ouvrirPorte()
{
    allumerLED(PIN_LED_OK);
    buzzerTone(PIN_BUZZER, 100, 300);
    delay(100);
    buzzerTone(PIN_BUZZER, 100, 200);
    digitalWrite(PIN_RELAY, HIGH);
    delay(DUREE_OUVERTURE_MS);
    digitalWrite(PIN_RELAY, LOW);
}

void signalerRefus()
{
    allumerLED(PIN_LED_DENY);
    buzzerTone(PIN_BUZZER, 300, 800);
}

void bipTouche()
{
    buzzerTone(PIN_BUZZER, 20, 500);
}

// ─── Transitions ──────────────────────────────────────────────────────────────
void allerVersEtat(Etat nouvel)
{
    etatActuel = nouvel;
    tempsEtat  = millis();
}

void retourAccueil()
{
    pinEnCours = "";
    eteindreLEDs();
    allerVersEtat(ACCUEIL);
    afficherAccueil();
}

// ─── Validation PIN ───────────────────────────────────────────────────────────
void validerPin()
{
    if ((int)pinEnCours.length() < PIN_MIN_LEN) {
        bipTouche();
        return;
    }

    allerVersEtat(VERIFICATION);
    afficherVerification();

    ReponseAPI rep = verifierPin(pinEnCours);

    if (rep.erreurReseau) {
        afficherErreurReseau();
        signalerRefus();
        allerVersEtat(ERREUR);
    } else if (rep.autorise) {
        prenomAutorise = rep.prenom;
        afficherSucces(prenomAutorise);
        ouvrirPorte();
        allerVersEtat(SUCCES);
    } else {
        afficherRefus();
        signalerRefus();
        allerVersEtat(REFUS);
    }
}

// ─── Lecture clavier ─────────────────────────────────────────────────────────
void gererClavier()
{
    char touche = clavier.getKey();
    if (!touche) return;

    Serial.printf("[CLAVIER] %c\n", touche);

    if (etatActuel == VERIFICATION) return;

    // Effacer : *, A, D
    if (touche == KEY_CLEAR || touche == 'A' || touche == 'D') {
        retourAccueil();

    // Valider : #
    } else if (touche == KEY_CONFIRM) {
        validerPin();

    // Chiffre
    } else if (isDigit(touche)) {
        if ((int)pinEnCours.length() < PIN_MAX_LEN) {
            bipTouche();
            pinEnCours += touche;
            allerVersEtat(SAISIE);
            afficherSaisie(pinEnCours);

            // Auto-validation à PIN_MAX_LEN
            if ((int)pinEnCours.length() == PIN_MAX_LEN) {
                delay(300);
                validerPin();
            }
        }
    }
}

// ─── Timeouts ─────────────────────────────────────────────────────────────────
void gererTimeouts()
{
    unsigned long elapsed = millis() - tempsEtat;

    if ((etatActuel == SUCCES || etatActuel == REFUS || etatActuel == ERREUR)
        && elapsed > DUREE_FEEDBACK_MS + 500) {
        retourAccueil();
    }
    if (etatActuel == SAISIE && elapsed > 30000) {
        retourAccueil();
    }
}

// ─── Setup ───────────────────────────────────────────────────────────────────
void setup()
{
    Serial.begin(115200);
    Serial.println("\n[ORION] Demarrage ESP32-C3...");

    pinMode(PIN_LED_OK,   OUTPUT);
    pinMode(PIN_LED_DENY, OUTPUT);
    pinMode(PIN_BUZZER,   OUTPUT);
    pinMode(PIN_RELAY,    OUTPUT);
    eteindreLEDs();
    digitalWrite(PIN_RELAY, LOW);

    initialiserDisplay();

#ifdef WOKWI_SIM
    Serial.println("[ORION] Mode simulation Wokwi");
    Serial.println("[ORION] PIN autorise : " PIN_AUTORISE);
#else
    if (!connecterWiFi()) {
        afficherErreurReseau();
        delay(3000);
    }
#endif

    retourAccueil();
    Serial.println("[ORION] Pret !");
}

// ─── Loop ────────────────────────────────────────────────────────────────────
void loop()
{
    if (etatActuel != VERIFICATION) {
        gererClavier();
    }
    gererTimeouts();
}
