#include "api_client.h"
#include "config.h"
#include <ArduinoJson.h>

#ifndef WOKWI_SIM
  #include <HTTPClient.h>
  #include <WiFi.h>
#endif

#ifndef WOKWI_SIM
static ReponseAPI appelHTTP(const String& endpoint, const String& bodyJson)
{
    ReponseAPI rep = { false, "", "", false };

    if (WiFi.status() != WL_CONNECTED) {
        rep.erreurReseau = true;
        rep.message = "WiFi deconnecte";
        return rep;
    }

    HTTPClient http;
    http.begin(String(API_BASE_URL) + endpoint);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Authorization", "Bearer " API_TOKEN);
    http.setTimeout(API_TIMEOUT_MS);

    int code = http.POST(bodyJson);
    String payload = http.getString();
    Serial.printf("[API] %s -> HTTP %d\n", endpoint.c_str(), code);

    if (code == 200) {
        JsonDocument doc;
        DeserializationError error = deserializeJson(doc, payload);

        if (error) {
            rep.erreurReseau = true;
            rep.message = "Reponse API invalide";
        } else {
            rep.autorise = (doc["status"].as<String>() == "success");
            rep.prenom = doc["prenom"] | "";
            rep.message = doc["message"] | "";
        }
    } else if (code < 0) {
        rep.erreurReseau = true;
        rep.message = "Erreur reseau";
    } else {
        rep.message = "Acces refuse";
    }

    http.end();
    return rep;
}
#endif

#ifdef WOKWI_SIM
static ReponseAPI simulerReponse(const String& type, const String& valeur)
{
    ReponseAPI rep = { false, "", "Acces refuse", false };

    bool ok = false;
    if (type == "pin" && valeur == PIN_AUTORISE) ok = true;
    if (type == "badge" && valeur == BADGE_AUTORISE) ok = true;
    if (type == "qrcode" && valeur == QR_AUTORISE) ok = true;

    if (ok) {
        rep.autorise = true;
        rep.prenom = "Etudiant";
        rep.message = "Acces autorise";
        Serial.printf("[SIM] %s='%s' -> AUTORISE\n", type.c_str(), valeur.c_str());
    } else {
        Serial.printf("[SIM] %s='%s' -> REFUSE\n", type.c_str(), valeur.c_str());
    }

    return rep;
}
#endif

ReponseAPI verifierPin(const String& pin)
{
#ifdef WOKWI_SIM
    return simulerReponse("pin", pin);
#else
    JsonDocument doc;
    doc["pin"] = pin;

    String body;
    serializeJson(doc, body);
    return appelHTTP("/pin.php", body);
#endif
}

ReponseAPI verifierBadge(const String& rfid)
{
#ifdef WOKWI_SIM
    return simulerReponse("badge", rfid);
#else
    JsonDocument doc;
    doc["rfid"] = rfid;

    String body;
    serializeJson(doc, body);
    return appelHTTP("/badge.php", body);
#endif
}

ReponseAPI verifierQR(const String& token)
{
#ifdef WOKWI_SIM
    return simulerReponse("qrcode", token);
#else
    JsonDocument doc;
    doc["token"] = token;

    String body;
    serializeJson(doc, body);
    return appelHTTP("/qrcode.php", body);
#endif
}
