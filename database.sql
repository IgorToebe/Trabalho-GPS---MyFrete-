-- MyFrete Database Schema
-- PostgreSQL Script

-- Create table for user login and registration
CREATE TABLE IF NOT EXISTS login_usuarios (
    id_usu SERIAL PRIMARY KEY,
    nomecompleto VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL, -- Will store password hash
    ehentregador BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create table for freight orders
CREATE TABLE IF NOT EXISTS frete (
    id_frete SERIAL PRIMARY KEY,
    id_cliente INTEGER NOT NULL REFERENCES login_usuarios(id_usu) ON DELETE CASCADE,
    id_fretista INTEGER REFERENCES login_usuarios(id_usu) ON DELETE SET NULL,
    data DATE NOT NULL,
    hora TIME NOT NULL,
    end_origem VARCHAR(100) NOT NULL,
    end_destino VARCHAR(100) NOT NULL,
    status VARCHAR(100) DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create table for freight evaluations
CREATE TABLE IF NOT EXISTS frete_avaliacao (
    id_avaliacao SERIAL PRIMARY KEY,
    id_frete INTEGER NOT NULL REFERENCES frete(id_frete) ON DELETE CASCADE,
    nota INTEGER NOT NULL CHECK (nota >= 1 AND nota <= 5),
    comentario VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_frete_cliente ON frete(id_cliente);
CREATE INDEX IF NOT EXISTS idx_frete_fretista ON frete(id_fretista);
CREATE INDEX IF NOT EXISTS idx_frete_status ON frete(status);
CREATE INDEX IF NOT EXISTS idx_avaliacao_frete ON frete_avaliacao(id_frete);
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON login_usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_entregador ON login_usuarios(ehentregador);

-- Insert some sample data for testing
INSERT INTO login_usuarios (nomecompleto, email, telefone, senha, ehentregador) VALUES
('João Silva', 'joao@example.com', '11987654321', '$2y$10$cRMDB6C3U4gb5mlvLNCySOIxgt4yzyyAWcGPKDs7.XIGfM5WuIVAe', TRUE),
('Maria Santos', 'maria@example.com', '11987654322', '$2y$10$cRMDB6C3U4gb5mlvLNCySOIxgt4yzyyAWcGPKDs7.XIGfM5WuIVAe', TRUE),
('Pedro Costa', 'pedro@example.com', '11987654323', '$2y$10$cRMDB6C3U4gb5mlvLNCySOIxgt4yzyyAWcGPKDs7.XIGfM5WuIVAe', FALSE),
('Admin User', 'teste@myfrete.com', '11987654324', '$2y$10$cRMDB6C3U4gb5mlvLNCySOIxgt4yzyyAWcGPKDs7.XIGfM5WuIVAe', FALSE)
ON CONFLICT (email) DO NOTHING;