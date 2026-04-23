#include "wifi_manager.h"
#include "config.h"
#include <WiFi.h>

bool connecterWiFi()
{
    Serial.print("[WiFi] Connexion a ");
    Serial.print(WIFI_SSID);

    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    int tentatives = 0;
    while (WiFi.status() != WL_CONNECTED && tentatives < 20) {
        delay(500);
        Serial.print(".");
        tentatives++;
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\n[WiFi] Connecte : " + WiFi.localIP().toString());
        return true;
    }

    Serial.println("\n[WiFi] Echec de connexion");
    return false;
}

bool wifiConnecte()
{
    return WiFi.status() == WL_CONNECTED;
}
