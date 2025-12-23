# Script pour créer le raccourci sur le Bureau
# Exécuter une seule fois pour créer le raccourci

$WshShell = New-Object -ComObject WScript.Shell
$DesktopPath = [Environment]::GetFolderPath("Desktop")
$ShortcutPath = Join-Path $DesktopPath "Hotel Pro.lnk"

# Chemin vers le launcher (utiliser le VBS pour être complètement silencieux)
$LauncherPath = Join-Path $PSScriptRoot "launcher-vbs.vbs"

# Créer le raccourci
$Shortcut = $WshShell.CreateShortcut($ShortcutPath)
$Shortcut.TargetPath = "wscript.exe"
$Shortcut.Arguments = '"""' + $LauncherPath + '"""'
$Shortcut.WorkingDirectory = $PSScriptRoot
$Shortcut.Description = "Hotel Pro - Gestion Hôtelière"
$Shortcut.IconLocation = Join-Path $PSScriptRoot "..\public\Template\logo.jpg"

# Optionnel : Changer l'icône si vous avez un fichier .ico
$IconPath = Join-Path $PSScriptRoot "icon.ico"
if (Test-Path $IconPath) {
    $Shortcut.IconLocation = $IconPath
}

$Shortcut.Save()

Write-Host "Raccourci créé sur le Bureau : Hotel Pro.lnk" -ForegroundColor Green
Write-Host ""
Write-Host "Le client peut maintenant double-cliquer sur ce raccourci" -ForegroundColor Yellow
Write-Host "Le serveur se lancera automatiquement en arrière-plan." -ForegroundColor Yellow

