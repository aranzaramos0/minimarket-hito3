USE db_minimarket_pro413;
SHOW CREATE TABLE Producto;
SELECT id, nombre, proveedor, fecha_vencimiento, precio, stock, eliminado
FROM Producto ORDER BY id;
SELECT id, nombre, eliminado FROM Producto WHERE eliminado = 1 ORDER BY id;
SELECT id, nombre, eliminado FROM Producto WHERE eliminado = 0 ORDER BY id;
