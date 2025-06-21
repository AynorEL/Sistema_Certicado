-- Script para agregar campo diseño a la tabla curso

-- Agregar campo diseño a la tabla curso
ALTER TABLE curso 
ADD diseño TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER estado; 