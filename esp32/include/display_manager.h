#pragma once
#include <Arduino.h>

void initialiserDisplay();
void afficherAccueil();
void afficherSaisie(const String& pin);
void afficherVerification();
void afficherSucces(const String& prenom);
void afficherRefus();
void afficherErreurReseau();
