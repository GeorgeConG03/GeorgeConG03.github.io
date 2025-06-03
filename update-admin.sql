-- Primero, agregar una columna 'es_admin' a la tabla usuarios si no existe
ALTER TABLE usuarios ADD COLUMN es_admin TINYINT(1) DEFAULT 0;

-- Convertir al usuario con ID 1 en administrador
UPDATE usuarios SET es_admin = 1 WHERE id_usuario = 1;

-- También podemos insertar un registro en la tabla administradores
INSERT INTO administradores (nombre, correo, contraseña) 
SELECT nombre, correo, contraseña FROM usuarios WHERE id_usuario = 1
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
