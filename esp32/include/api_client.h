#pragma once
#include <Arduino.h>

struct ReponseAPI {
    bool autorise;
    String prenom;
    String message;
    bool erreurReseau;
};

ReponseAPI verifierPin(const String& pin);
ReponseAPI verifierBadge(const String& rfid);
ReponseAPI verifierQR(const String& token);
