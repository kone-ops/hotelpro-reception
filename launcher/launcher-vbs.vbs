' Launcher VBScript pour Hotel Pro
' Version silencieuse qui masque complètement les fenêtres

Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

' Configuration
ServerPath = "C:\laragon"
AppURL = "http://hotelpro.test"
ServerType = ""

' Détecter le type de serveur
If objFSO.FileExists(ServerPath & "\laragon.exe") Then
    ServerType = "laragon"
    ServerExe = ServerPath & "\laragon.exe"
ElseIf objFSO.FileExists("C:\wamp64\wampmanager.exe") Then
    ServerType = "wamp"
    ServerPath = "C:\wamp64"
    ServerExe = ServerPath & "\wampmanager.exe"
ElseIf objFSO.FileExists("C:\xampp\xampp-control.exe") Then
    ServerType = "xampp"
    ServerPath = "C:\xampp"
    ServerExe = ServerPath & "\xampp-control.exe"
Else
    ' Aucun serveur trouvé
    MsgBox "Aucun serveur web trouvé (Laragon/WAMP/XAMPP). Installation requise.", vbCritical, "Hotel Pro - Erreur"
    WScript.Quit
End If

' Démarrer le serveur
Select Case ServerType
    Case "laragon"
        ' Laragon
        objShell.Run """" & ServerExe & """ start", 0, False
        WScript.Sleep 3000
    Case "wamp"
        ' WAMP - Démarrer les services
        objShell.Run "net start wampapache64", 0, True
        objShell.Run "net start wampmysqld64", 0, True
        WScript.Sleep 3000
    Case "xampp"
        ' XAMPP
        objShell.Run """" & ServerPath & "\apache_start.bat""", 0, False
        objShell.Run """" & ServerPath & "\mysql_start.bat""", 0, False
        WScript.Sleep 3000
End Select

' Attendre que le serveur soit prêt (vérification simple)
WScript.Sleep 2000

' Ouvrir l'application
' Option 1 : Electron si disponible
ElectronPath = objFSO.GetParentFolderName(WScript.ScriptFullName) & "\..\electron-example\dist\Hotel Pro.exe"
If objFSO.FileExists(ElectronPath) Then
    objShell.Run """" & ElectronPath & """", 1, False
Else
    ' Option 2 : Navigateur par défaut
    objShell.Run AppURL, 1, False
End If

WScript.Quit

