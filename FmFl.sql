USE `here'stoyou`;USE `here'stoyou`;

-- Primero eliminamos las columnas existentes que no necesitamos
ALTER TABLE reseñas
DROP COLUMN IF EXISTS contenido,
DROP COLUMN IF EXISTS fecha;

-- Añadimos las nuevas columnas requeridas
ALTER TABLE reseñas
ADD COLUMN titulo VARCHAR(255) NOT NULL AFTER id_production,
ADD COLUMN categoria VARCHAR(100) NOT NULL AFTER titulo,
ADD COLUMN calificacion DECIMAL(3,1) NOT NULL CHECK (calificacion >= 0 AND calificacion <= 10) AFTER categoria,
ADD COLUMN pros TEXT AFTER calificacion,
ADD COLUMN contras TEXT AFTER pros,
ADD COLUMN descripcion TEXT AFTER contras,
ADD COLUMN recomendacion BOOLEAN AFTER descripcion,
ADD COLUMN imagenes JSON AFTER recomendacion,
ADD COLUMN temporadas INT AFTER imagenes,
ADD COLUMN episodios INT AFTER temporadas,
ADD COLUMN duracion INT COMMENT 'Duración en minutos' AFTER episodios,
ADD COLUMN paginas INT AFTER duracion,
ADD COLUMN fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER paginas;

-- Creamos los índices necesarios
CREATE INDEX idx_categoria ON reseñas(categoria);
CREATE INDEX idx_calificacion ON reseñas(calificacion);
CREATE INDEX idx_fecha_creacion ON reseñas(fecha_creacion);