# Exemples VBA – API HotelPro

## Fichiers

- **HotelProApiClient.bas** : module à importer dans votre projet VBA (Excel, Access, etc.).

## Import dans VBA

1. Ouvrir l’éditeur VBA (Alt+F11).
2. Fichier → Importer un fichier… (ou clic droit sur le projet → Importer un fichier).
3. Choisir `HotelProApiClient.bas`.

## Configuration

Dans votre code (ou au démarrage du classeur/application) :

```vba
' URL de votre serveur HotelPro (sans slash final)
HotelProApiClient.SetBaseUrl "https://hotelpro.mon-domaine.com"
' ou en local :
HotelProApiClient.SetBaseUrl "http://localhost"
```

Pour les endpoints protégés (liste réservations, validation, etc.) :

```vba
HotelProApiClient.SetApiToken "votre_token_sanctum"
```

## Exemples d’appel

```vba
' Vérifier que l'API répond
Dim reponse As String
reponse = HotelProApiClient.ApiHealthCheck()
Debug.Print reponse

' Liste des hôtels
reponse = HotelProApiClient.GetHotels()

' Types de chambres de l'hôtel 1
reponse = HotelProApiClient.GetRoomTypes(1)

' Disponibilités du 10 au 12 février 2025
reponse = HotelProApiClient.GetAvailability(1, "2025-02-10", "2025-02-12")

' Créer une réservation
reponse = HotelProApiClient.CreateReservation(1, 1, "2025-02-10", "2025-02-12", _
    "Dupont Jean", "jean.dupont@email.com", "+33612345678", 2)
```

Les fonctions retournent la réponse JSON brute. Pour l’exploiter en VBA, vous pouvez parser le JSON (bibliothèque VBA-JSON ou parsing manuel sur des champs simples).

## Références VBA

Aucune référence obligatoire. Le module utilise `WinHttp.WinHttpRequest.5.1` ou `MSXML2.XMLHTTP` (disponibles sous Windows).

## Voir aussi

- [DOCUMENTATION_API_HOTELPRO_VBA.md](../DOCUMENTATION_API_HOTELPRO_VBA.md) : description complète des endpoints et de l’authentification.
