#include "display_manager.h"
#include "config.h"
#include <LiquidCrystal_I2C.h>
#include <Wire.h>

static LiquidCrystal_I2C lcd(LCD_ADDR, LCD_COLS, LCD_LINES);

// ─── Init ─────────────────────────────────────────────────────────────────────
void initialiserDisplay()
{
    Wire.begin(I2C_SDA, I2C_SCL);
    lcd.init();
    lcd.backlight();
    lcd.clear();
}

// ─── Utilitaire ───────────────────────────────────────────────────────────────
static void lcdEcrire(const char* l0, const char* l1)
{
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(l0);
    lcd.setCursor(0, 1); lcd.print(l1);
}

// ─── Écrans ───────────────────────────────────────────────────────────────────
void afficherAccueil()
{
    lcdEcrire("** ORION v1.0 **", "Entrez votre PIN");
}

void afficherSaisie(const String& pin)
{
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("PIN: ");
    for (int i = 0; i < (int)pin.length(); i++) lcd.print("*");
    for (int i = pin.length(); i < PIN_MAX_LEN; i++) lcd.print("-");

    lcd.setCursor(0, 1);
    lcd.print("# valid  * effa.");
}

void afficherVerification()
{
    lcdEcrire("Verification...", "API en cours... ");
}

void afficherSucces(const String& prenom)
{
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print("ACCES AUTORISE !");
    lcd.setCursor(0, 1);
    if (prenom.length() > 0) {
        String msg = "Bonjour " + prenom;
        lcd.print(msg.substring(0, 16));
    } else {
        lcd.print("Bienvenue !     ");
    }
}

void afficherRefus()
{
    lcdEcrire("CODE INCORRECT !", "Reessayez...    ");
}

void afficherErreurReseau()
{
    lcdEcrire("Erreur reseau ! ", "Verif. serveur..");
}
