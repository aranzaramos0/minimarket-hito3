$ErrorActionPreference = 'Stop'
$ProjectRoot = Split-Path -Parent $PSScriptRoot
$EnvPath = Join-Path $ProjectRoot '.env'
if (Test-Path $EnvPath) {
    Write-Host 'Ya existe .env. Se conserva su configuracion.'
    exit 0
}
$RootPassword = [guid]::NewGuid().ToString('N')
$AppPassword = [guid]::NewGuid().ToString('N')
$Content = Get-Content (Join-Path $ProjectRoot '.env.example') -Raw
$Content = $Content.Replace('cambiar_clave_root_local', $RootPassword).Replace('cambiar_clave_app_local', $AppPassword)
[System.IO.File]::WriteAllText($EnvPath, $Content, (New-Object System.Text.UTF8Encoding($false)))
Write-Host '.env creado con claves locales aleatorias. No lo subas a GitHub.'
Write-Host 'Siguiente comando: docker compose up -d --build'
