CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) DEFAULT 'usuario'
) ENGINE=InnoDB;

CREATE TABLE bandas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    slug VARCHAR(150) UNIQUE,
    imagem VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ano_formacao INT,
    cidade VARCHAR(120)
) ENGINE=InnoDB;

CREATE TABLE generos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE,
    ativo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE banda_genero (
    banda_id INT,
    genero_id INT,
    PRIMARY KEY (banda_id, genero_id),
    FOREIGN KEY (banda_id) REFERENCES bandas(id) ON DELETE CASCADE,
    FOREIGN KEY (genero_id) REFERENCES generos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE albuns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    banda_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    ano INT,
    capa VARCHAR(255),
    criado_por INT,
    FOREIGN KEY (banda_id) REFERENCES bandas(id) ON DELETE CASCADE,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE faixas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    album_id INT NOT NULL,
    numero INT,
    nome VARCHAR(150),
    duracao VARCHAR(20),
    total_ouvidas INT DEFAULT 0,
    FOREIGN KEY (album_id) REFERENCES albuns(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reproducoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    faixa_id INT NOT NULL,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (faixa_id) REFERENCES faixas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    faixa_id INT NOT NULL,
    nota INT,
    favorita TINYINT(1) DEFAULT 0,
    UNIQUE(usuario_id, faixa_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (faixa_id) REFERENCES faixas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE progresso_album (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    album_id INT NOT NULL,
    progresso DECIMAL(5,2) DEFAULT 0,
    UNIQUE(usuario_id, album_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES albuns(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE usuario_dash (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    album_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES albuns(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE album_streaming_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    album_id INT NOT NULL,
    plataforma_id INT,
    url VARCHAR(255),
    FOREIGN KEY (album_id) REFERENCES albuns(id) ON DELETE CASCADE
) ENGINE=InnoDB;

