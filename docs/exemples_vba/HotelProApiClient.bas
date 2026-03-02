Attribute VB_Name = "HotelProApiClient"
' =============================================================================
' Module VBA - Client API HotelPro
' À importer dans votre projet VBA (Excel, Access, etc.)
' Référence requise : Microsoft Scripting Runtime (pour Dictionary si utilisé)
' Sinon, supprimez les appels à JsonParse simple et utilisez uniquement les
' fonctions qui retournent la réponse brute (GetApiResponse).
' =============================================================================

Option Explicit

' URL de base de l'API (par défaut ; peut être modifiée avec SetBaseUrl)
Private sBaseUrl As String
Private Const DEFAULT_BASE_URL As String = "https://votre-domaine.com"
Private Const API_PREFIX As String = "/api/v1"

' Token pour les endpoints protégés (optionnel, laisser vide si pas d'auth)
Private sApiToken As String

' -----------------------------------------------------------------------------
' Configuration
' -----------------------------------------------------------------------------

Public Sub SetBaseUrl(ByVal baseUrl As String)
    ' Appeler au démarrage : SetBaseUrl "https://hotelpro.mon-domaine.com"
    ' Ne pas mettre de slash final
    sBaseUrl = baseUrl
End Sub

Private Function BaseUrl() As String
    If Len(sBaseUrl) = 0 Then BaseUrl = DEFAULT_BASE_URL Else BaseUrl = sBaseUrl
End Function

Public Sub SetApiToken(ByVal token As String)
    sApiToken = token
End Sub

' -----------------------------------------------------------------------------
' Health check - vérifier que l'API répond
' -----------------------------------------------------------------------------

Public Function ApiHealthCheck() As String
    ApiHealthCheck = GetApiResponse("GET", "/api/health", "")
End Function

' -----------------------------------------------------------------------------
' Liste des hôtels - GET /api/v1/hotels
' -----------------------------------------------------------------------------

Public Function GetHotels() As String
    GetHotels = GetApiResponse("GET", API_PREFIX & "/hotels", "")
End Function

' -----------------------------------------------------------------------------
' Détail d'un hôtel - GET /api/v1/hotels/{id}
' -----------------------------------------------------------------------------

Public Function GetHotelById(ByVal hotelId As Long) As String
    GetHotelById = GetApiResponse("GET", API_PREFIX & "/hotels/" & hotelId, "")
End Function

' -----------------------------------------------------------------------------
' Types de chambres - GET /api/v1/hotels/{id}/room-types
' -----------------------------------------------------------------------------

Public Function GetRoomTypes(ByVal hotelId As Long) As String
    GetRoomTypes = GetApiResponse("GET", API_PREFIX & "/hotels/" & hotelId & "/room-types", "")
End Function

' -----------------------------------------------------------------------------
' Chambres - GET /api/v1/hotels/{id}/rooms
' -----------------------------------------------------------------------------

Public Function GetRooms(ByVal hotelId As Long, Optional status As String = "", Optional roomTypeId As Long = 0) As String
    Dim url As String
    url = API_PREFIX & "/hotels/" & hotelId & "/rooms"
    If status <> "" Then url = url & "?status=" & status
    If roomTypeId > 0 Then url = url & IIf(status = "", "?", "&") & "room_type_id=" & roomTypeId
    GetRooms = GetApiResponse("GET", url, "")
End Function

' -----------------------------------------------------------------------------
' Disponibilités - GET /api/v1/hotels/{id}/availability?check_in_date=...&check_out_date=...
' -----------------------------------------------------------------------------

Public Function GetAvailability(ByVal hotelId As Long, ByVal checkInDate As String, ByVal checkOutDate As String, Optional roomTypeId As Long = 0) As String
    Dim url As String
    url = API_PREFIX & "/hotels/" & hotelId & "/availability?check_in_date=" & checkInDate & "&check_out_date=" & checkOutDate
    If roomTypeId > 0 Then url = url & "&room_type_id=" & roomTypeId
    GetAvailability = GetApiResponse("GET", url, "")
End Function

' -----------------------------------------------------------------------------
' Créer une réservation - POST /api/v1/hotels/{id}/reservations
' -----------------------------------------------------------------------------

Public Function CreateReservation(ByVal hotelId As Long, _
    ByVal roomTypeId As Long, _
    ByVal checkInDate As String, _
    ByVal checkOutDate As String, _
    ByVal clientName As String, _
    ByVal clientEmail As String, _
    ByVal clientPhone As String, _
    ByVal guestsCount As Long, _
    Optional roomId As Long = 0) As String

    Dim body As String
    body = "{" & _
        """room_type_id"":" & roomTypeId & "," & _
        """check_in_date"":""" & checkInDate & """," & _
        """check_out_date"":""" & checkOutDate & """," & _
        """client_name"":""" & EscapeJson(clientName) & """," & _
        """client_email"":""" & EscapeJson(clientEmail) & """," & _
        """client_phone"":""" & EscapeJson(clientPhone) & """," & _
        """guests_count"":" & guestsCount
    If roomId > 0 Then
        body = body & ",""room_id"":" & roomId
    Else
        body = body & ",""room_id"":null"
    End If
    body = body & "}"

    CreateReservation = GetApiResponse("POST", API_PREFIX & "/hotels/" & hotelId & "/reservations", body)
End Function

' -----------------------------------------------------------------------------
' Liste des réservations d'un hôtel (nécessite token) - GET /api/v1/hotels/{id}/reservations
' -----------------------------------------------------------------------------

Public Function GetHotelReservations(ByVal hotelId As Long, Optional status As String = "", Optional dateFrom As String = "", Optional dateTo As String = "") As String
    Dim url As String
    url = API_PREFIX & "/hotels/" & hotelId & "/reservations"
    If status <> "" Then url = url & "?status=" & status
    If dateFrom <> "" Then url = url & IIf(status = "", "?", "&") & "date_from=" & dateFrom
    If dateTo <> "" Then url = url & IIf(status = "" And dateFrom = "", "?", "&") & "date_to=" & dateTo
    GetHotelReservations = GetApiResponse("GET", url, "", True)
End Function

' -----------------------------------------------------------------------------
' Requête HTTP générique
' -----------------------------------------------------------------------------

Public Function GetApiResponse(ByVal method As String, ByVal path As String, ByVal body As String, Optional requireAuth As Boolean = False) As String
    On Error GoTo ErrHandler

    Dim url As String
    url = BaseUrl() & path

    ' Utiliser WinHttp si disponible, sinon MSXML2
    Dim http As Object
    On Error Resume Next
    Set http = CreateObject("WinHttp.WinHttpRequest.5.1")
    If http Is Nothing Then Set http = CreateObject("MSXML2.ServerXMLHTTP.6.0")
    If http Is Nothing Then Set http = CreateObject("MSXML2.XMLHTTP.6.0")
    On Error GoTo ErrHandler

    If http Is Nothing Then
        GetApiResponse = "{""success"":false,""message"":""Impossible de créer l'objet HTTP""}"
        Exit Function
    End If

    http.Open method, url, False
    http.setRequestHeader "Content-Type", "application/json"
    http.setRequestHeader "Accept", "application/json"
    If requireAuth And sApiToken <> "" Then
        http.setRequestHeader "Authorization", "Bearer " & sApiToken
    End If
    http.setTimeouts 5000, 5000, 5000, 5000

    If method = "POST" Or method = "PUT" Then
        http.send body
    Else
        http.send
    End If

    GetApiResponse = http.responseText
    Exit Function

ErrHandler:
    GetApiResponse = "{""success"":false,""message"":""Erreur: " & Replace(Err.Description, """", "\""") & """}"
End Function

Private Function EscapeJson(ByVal s As String) As String
    EscapeJson = Replace(Replace(Replace(s, "\", "\\"), """", "\"""), vbCrLf, "\n")
End Function
