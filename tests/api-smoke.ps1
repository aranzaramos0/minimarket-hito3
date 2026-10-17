param([string]$BaseUrl = 'http://localhost:8081/api/productos')
$ErrorActionPreference = 'Stop'
function Check($Condition, [string]$Label) {
    if (-not $Condition) { throw "FALLO: $Label" }
    Write-Host "OK: $Label"
}
$Body = @{ nombre = 'Prueba API ' + (Get-Date -Format 'HHmmss'); proveedor = 'Proveedor de prueba'; fecha_vencimiento = '2027-12-31'; precio = '15.50'; stock = 20 } | ConvertTo-Json
$Created = Invoke-RestMethod -Uri $BaseUrl -Method Post -ContentType 'application/json; charset=utf-8' -Body $Body
$Id = $Created.data.id
Check ($Id -gt 0) 'Crear producto'
$Body = @{ nombre = 'Prueba API editada'; proveedor = 'Proveedor de prueba'; fecha_vencimiento = '2027-12-31'; precio = '16.00'; stock = 30 } | ConvertTo-Json
$Updated = Invoke-RestMethod -Uri "$BaseUrl/$Id" -Method Put -ContentType 'application/json; charset=utf-8' -Body $Body
Check ($Updated.data.stock -eq 30) 'Editar producto'
$Listed = Invoke-RestMethod -Uri $BaseUrl
Check (@($Listed.data | Where-Object { $_.id -eq $Id }).Count -eq 1) 'Listar producto activo'
$null = Invoke-RestMethod -Uri "$BaseUrl/$Id" -Method Delete
$Listed = Invoke-RestMethod -Uri $BaseUrl
Check (@($Listed.data | Where-Object { $_.id -eq $Id }).Count -eq 0) 'Excluir producto eliminado del listado'
Write-Host "Registro de prueba conservado con eliminado=1. Verifica en MySQL el id $Id."
Write-Host "SELECT id, nombre, eliminado FROM Producto WHERE id = $Id;"
