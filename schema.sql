SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS loja_virtual CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE loja_virtual;

-- tabela de categorias
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS categorias;

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categorias (nome) VALUES
  ('Camisetas'),
  ('Calcas'),
  ('Jaquetas');

-- tabela bruta dos produtos, ligada à categoria
CREATE TABLE produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NULL,
  nome VARCHAR(150) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  preco DECIMAL(10,2) NOT NULL,
  preco_orig DECIMAL(10,2) NULL,
  estoque INT NOT NULL DEFAULT 0,
  img VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_produto_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO produtos (categoria_id, nome, slug, preco, preco_orig, estoque, img) VALUES
  (1, 'Camiseta Racing', 'camiseta-racing', 159.90, 199.90, 8, 'imgs/camiseta1.png'),
  (1, 'Camiseta 90', 'camiseta-90', 139.90, 1.7990, 7, 'imgs/camiseta2.png'),
  (2, 'Calça Reta', 'calca-reta', 109.90, 139.90, 8, 'imgs/calca2.png'),
  (3, 'Jaqueta de Moletom', 'jaqueta-de-moletom', 129.90, NULL, 6, 'imgs/blusa3.png'),
  (2, 'Calça Cargo', 'calca-cargo', 120.00, NULL, 5, 'imgs/calca1.png'),
  (2, 'Calça Baggy Cargo', 'calca-baggy-cargo', 109.90, NULL, 3, 'imgs/calca3.png'),
  (1, 'Camiseta de Gato', 'camiseta-de-gato', 49.90, NULL, 0, 'imgs/camisetadogato.png'),
  (3, 'Jaqueta Puffer', 'jaqueta-puffer', 119.90, NULL, 0, 'imgs/blusa1.png'),
  (3, 'Jaqueta Y2k', 'jaqueta-y2k', 119.90, NULL, 0, 'imgs/blusa2.png');

-- view principal: catálogo limpo, já com a categoria
CREATE OR REPLACE VIEW vw_produtos_catalogo AS
WITH produtos_padronizados AS (
  SELECT
    p.id,
    p.categoria_id,
    c.nome AS categoria_nome,
    p.nome,
    p.slug,
    ABS(p.preco) AS preco,
    CASE WHEN p.preco_orig IS NOT NULL THEN ABS(p.preco_orig) END AS preco_orig,
    GREATEST(p.estoque, 0) AS estoque,
    p.img
  FROM produtos p
  LEFT JOIN categorias c ON c.id = p.categoria_id
),
produtos_classificados AS (
  SELECT
    pp.*,
    CASE
      WHEN pp.estoque = 0 THEN 'esgotado'
      WHEN pp.preco_orig IS NOT NULL AND pp.preco_orig > pp.preco THEN 'promocao'
      ELSE 'normal'
    END AS status,
    CASE
      WHEN pp.estoque = 0 THEN 2
      WHEN pp.preco_orig IS NOT NULL AND pp.preco_orig > pp.preco THEN 0
      ELSE 1
    END AS ordem_exibicao,
    CASE
      WHEN pp.preco_orig IS NOT NULL AND pp.preco_orig > pp.preco
      THEN ROUND((pp.preco_orig - pp.preco) / pp.preco_orig * 100)
    END AS percentual_desconto
  FROM produtos_padronizados pp
)
SELECT * FROM produtos_classificados;

-- view secundária: resumo do estoque por status
CREATE OR REPLACE VIEW vw_estoque_resumo AS
WITH catalogo AS (
  SELECT * FROM vw_produtos_catalogo
)
SELECT
  status,
  COUNT(*) AS total_produtos,
  SUM(estoque) AS total_unidades,
  ROUND(AVG(preco), 2) AS preco_medio,
  ROUND(SUM(preco * estoque), 2) AS valor_total_estoque
FROM catalogo
GROUP BY status;

-- se tentar salvar preço/estoque negativo, converte pra positivo automático
DROP TRIGGER IF EXISTS trg_produtos_valores_positivos;
DELIMITER $$
CREATE TRIGGER trg_produtos_valores_positivos
BEFORE UPDATE ON produtos
FOR EACH ROW
BEGIN
  SET NEW.preco = ABS(NEW.preco);
  IF NEW.preco_orig IS NOT NULL THEN
    SET NEW.preco_orig = ABS(NEW.preco_orig);
  END IF;
  SET NEW.estoque = ABS(NEW.estoque);
END$$
DELIMITER ;