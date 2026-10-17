$ErrorActionPreference = 'Continue'
Write-Host '1. Git'
git --version
Write-Host '2. Docker'
docker --version
Write-Host '3. Docker Compose'
docker compose version
Write-Host '4. Motor Docker (debe responder sin error)'
docker info --format '{{.ServerVersion}}'
Write-Host '5. WSL en Windows'
wsl --version
Write-Host '6. Puertos usados por otros programas'
Get-NetTCPConnection -State Listen -ErrorAction SilentlyContinue | Where-Object { $_.LocalPort -in 8080,8081,3307 } | Select-Object LocalAddress,LocalPort,OwningProcess
Write-Host 'Un puerto ocupado se puede cambiar en .env. No necesitas instalar PHP, Java, Node ni MySQL local para el modo Docker.'
