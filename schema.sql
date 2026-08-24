-- =========================================================================
-- Gungnir Store — Schema, dados, views analíticas e trigger
-- Testado em MariaDB 10.11
-- =========================================================================

SET NAMES utf8mb4;
-- ^ garante que os acentos (Calça, Jaqueta...) sejam lidos corretamente
--   independente da configuração padrão do seu cliente MySQL/MariaDB.

CREATE DATABASE IF NOT EXISTS loja_virtual CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE loja_virtual;

-- ---------------------------------------------------------------------
-- Tabela bruta de produtos (dados "crus" do sistema)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS produtos;
CREATE TABLE produtos (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nome           VARCHAR(150)   NOT NULL,
    slug           VARCHAR(150)   NOT NULL UNIQUE,
    preco          DECIMAL(10,2)  NOT NULL,
    preco_orig     DECIMAL(10,2)  NULL,        -- preço "de", quando o produto está em promoção
    estoque        INT            NOT NULL DEFAULT 0,
    img            VARCHAR(255)   NOT NULL,
    criado_em      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO produtos (nome, slug, preco, preco_orig, estoque, img) VALUES
('Camiseta Racing',     'camiseta-racing',     159.90, 199.90, 8, 'imgs/camiseta1.png'),
('Camiseta 90',         'camiseta-90',         139.90, 179.90, 7, 'imgs/camiseta2.png'),
('Calça Reta',          'calca-reta',          109.90, 139.90, 8, 'imgs/calca2.png'),
('Jaqueta de Moletom',  'jaqueta-de-moletom',  129.90, NULL,   6, 'imgs/blusa3.png'),
('Calça Cargo',         'calca-cargo',         120.00, NULL,   5, 'imgs/calca1.png'),
('Calça Baggy Cargo',   'calca-baggy-cargo',   109.90, NULL,   3, 'imgs/calca3.png'),
('Camiseta de Gato',    'camiseta-de-gato',    49.90,  NULL,   0, 'imgs/camisetadogato.png'),
('Jaqueta Puffer',      'jaqueta-puffer',      119.90, NULL,   0, 'imgs/blusa1.png'),
('Jaqueta Y2k',         'jaqueta-y2k',         119.90, NULL,   0, 'imgs/blusa2.png');

-- ---------------------------------------------------------------------
-- View analítica principal: catálogo limpo e estruturado
--
-- Usa uma CTE em duas etapas:
--   1) produtos_padronizados  -> garante preco/preco_orig/estoque sempre
--      positivos (ABS/GREATEST), igual fazemos na trigger abaixo
--   2) produtos_classificados -> calcula status (promocao/normal/esgotado),
--      a ordem de exibição e o % de desconto
--
-- O PHP só faz "SELECT * ... ORDER BY ordem_exibicao" — toda a regra de
-- negócio que antes estava no foreach do index.php agora vive aqui.
-- ---------------------------------------------------------------------
CREATE OR REPLACE VIEW vw_produtos_catalogo AS
WITH produtos_padronizados AS (
    SELECT
        id,
        nome,
        slug,
        ABS(preco) AS preco,
        CASE WHEN preco_orig IS NOT NULL THEN ABS(preco_orig) END AS preco_orig,
        GREATEST(estoque, 0) AS estoque,
        img
    FROM produtos
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

-- ---------------------------------------------------------------------
-- View analítica secundária: resumo do estoque por status
-- (útil para um futuro painel/dashboard administrativo)
-- ---------------------------------------------------------------------
CREATE OR REPLACE VIEW vw_estoque_resumo AS
WITH catalogo AS (
    SELECT * FROM vw_produtos_catalogo
)
SELECT
    status,
    COUNT(*)                       AS total_produtos,
    SUM(estoque)                   AS total_unidades,
    ROUND(AVG(preco), 2)           AS preco_medio,
    ROUND(SUM(preco * estoque), 2) AS valor_total_estoque
FROM catalogo
GROUP BY status;

-- ---------------------------------------------------------------------
-- Trigger BEFORE UPDATE: padroniza valores positivos
-- Se alguém tentar salvar um preço/estoque negativo (erro de digitação,
-- bug em algum formulário futuro etc.), o valor é convertido para
-- positivo automaticamente antes de ser gravado.
-- ---------------------------------------------------------------------
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

-- ---------------------------------------------------------------------
-- Teste rápido da trigger (opcional — rode manualmente para ver o efeito)
-- ---------------------------------------------------------------------
-- UPDATE produtos SET preco = -50.00, estoque = -3 WHERE slug = 'calca-cargo';
-- SELECT preco, estoque FROM produtos WHERE slug = 'calca-cargo';
-- -- resultado esperado: preco = 50.00 e estoque = 3 (sempre positivos)