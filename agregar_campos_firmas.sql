-- Script para agregar campos de firmas digitales a las tablas instructor y especialista

-- Agregar campo firma_digital a la tabla instructor
ALTER TABLE instructor
ADD firma_digital TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

-- Agregar campo firma_especialista a la tabla especialista
ALTER TABLE especialista
ADD firma_especialista TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL; 