CREATE DATABASE IF NOT EXISTS ms_historias_db CHARACTER SET utf8mb4;
USE ms_historias_db;

    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    responsable VARCHAR(100) NOT NULL,
    estado ENUM('nueva', 'activa', 'finalizada', 'impedimento') NOT NULL DEFAULT 'nueva',
    puntos INT NOT NULL,
    fecha_creacion DATE NOT NULL,
    fecha_finalizacion DATE DEFAULT NULL,
    sprint_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
