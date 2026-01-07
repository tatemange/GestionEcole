CREATE DATABASE IF NOT EXISTS eco_note;
USE eco_note;

CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_classe VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS matieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_matiere VARCHAR(100) NOT NULL,
    coefficient FLOAT NOT NULL DEFAULT 1.0
);

CREATE TABLE IF NOT EXISTS eleves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE NOT NULL,
    genre ENUM('M', 'F') NOT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    classe_id INT,
    FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    eleve_id INT NOT NULL,
    matiere_id INT NOT NULL,
    valeur_note FLOAT NOT NULL CHECK (valeur_note BETWEEN 0 AND 20),
    trimestre INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS classe_matieres (
    classe_id INT NOT NULL,
    matiere_id INT NOT NULL,
    PRIMARY KEY (classe_id, matiere_id),
    FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE
);

-- Table des utilisateurs (Authentification)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'professeur') NOT NULL DEFAULT 'professeur',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion du compte administrateur par défaut
-- Email: admin@eco.school
-- Mot de passe: admin123 (à changer après le premier login)
INSERT INTO users (nom, email, password, role) VALUES 
('Administrateur', 'admin@eco.school', '$2y$10$gKTSN06aPjdzCXfNXkpHc.70vmT8J2LnXBq9OGOc8dEHPuOSLc9Wy', 'admin')
ON DUPLICATE KEY UPDATE id=id;
