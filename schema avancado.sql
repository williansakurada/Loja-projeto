SET NAMES utf8mb4;

USE loja_virtual;

-- tabelas de clientes, pedidos e itens do pedido
DROP TABLE IF EXISTS itens_pedido;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS clientes;

CREATE TABLE clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  telefone VARCHAR(20) NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pendente',
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pedidos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE itens_pedido (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  produto_id INT NOT NULL,
  tamanho VARCHAR(5) NOT NULL,
  quantidade INT NOT NULL DEFAULT 1,
  preco_unitario DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_itens_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
  CONSTRAINT fk_itens_produto FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- clientes de teste
INSERT INTO clientes (nome, email, senha_hash) VALUES
  ('Ana Souza', 'ana@teste.com', SHA2('senha123', 256)),
  ('Bruno Lima', 'bruno@teste.com', SHA2('senha123', 256)),
  ('Carla Dias', 'carla@teste.com', SHA2('senha123', 256));

-- pedidos de teste
INSERT INTO pedidos (cliente_id, status) VALUES
  (1, 'concluido'),
  (1, 'concluido'),
  (2, 'pendente'),
  (3, 'concluido');

-- itens de teste dos pedidos
INSERT INTO itens_pedido (pedido_id, produto_id, tamanho, quantidade, preco_unitario) VALUES
  (1, 1, 'M', 2, 159.90),
  (1, 3, 'G', 1, 109.90),
  (2, 2, 'M', 1, 139.90),
  (3, 4, 'GG', 1, 129.90),
  (4, 1, 'P', 3, 159.90);

-- function reutilizável pra classificar o status de um produto
DROP FUNCTION IF EXISTS fn_status_produto;
DELIMITER $$
CREATE FUNCTION fn_status_produto(p_estoque INT, p_preco DECIMAL(10,2), p_preco_orig DECIMAL(10,2))
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
  IF p_estoque <= 0 THEN
    RETURN 'esgotado';
  ELSEIF p_preco_orig IS NOT NULL AND p_preco_orig > p_preco THEN
    RETURN 'promocao';
  ELSE
    RETURN 'normal';
  END IF;
END$$
DELIMITER ;

-- procedure pra gerar produtos de teste em massa
DROP PROCEDURE IF EXISTS sp_popular_produtos_teste;
DELIMITER $$
CREATE PROCEDURE sp_popular_produtos_teste(IN p_quantidade INT)
BEGIN
  DECLARE i INT DEFAULT 0;
  WHILE i < p_quantidade DO
    INSERT INTO produtos (nome, slug, preco, preco_orig, estoque, img)
    VALUES (
      CONCAT('Produto Teste ', FLOOR(RAND() * 100000)),
      CONCAT('produto-teste-', UUID_SHORT()),
      ROUND(50 + RAND() * 250, 2),
      IF(RAND() > 0.5, ROUND(50 + RAND() * 350, 2), NULL),
      FLOOR(RAND() * 20),
      'imgs/placeholder.png'
    );
    SET i = i + 1;
  END WHILE;
END$$
DELIMITER ;

-- procedure de busca, filtro e paginação de produtos
DROP PROCEDURE IF EXISTS sp_buscar_produtos;
DELIMITER $$
CREATE PROCEDURE sp_buscar_produtos(
  IN p_termo VARCHAR(150),
  IN p_status VARCHAR(20),
  IN p_pagina INT,
  IN p_por_pagina INT
)
BEGIN
  DECLARE v_offset INT;
  SET v_offset = GREATEST(p_pagina - 1, 0) * p_por_pagina;

  SELECT *
  FROM vw_produtos_catalogo
  WHERE (p_termo IS NULL OR p_termo = '' OR nome LIKE CONCAT('%', p_termo, '%'))
    AND (p_status IS NULL OR p_status = '' OR status = p_status)
  ORDER BY ordem_exibicao, nome
  LIMIT p_por_pagina OFFSET v_offset;
END$$
DELIMITER ;

-- view que cruza pedido, cliente, item e produto
CREATE OR REPLACE VIEW vw_pedidos_detalhado AS
SELECT
  ped.id AS pedido_id,
  ped.status AS status_pedido,
  ped.criado_em AS data_pedido,
  cli.id AS cliente_id,
  cli.nome AS cliente_nome,
  cli.email AS cliente_email,
  prod.id AS produto_id,
  prod.nome AS produto_nome,
  it.tamanho,
  it.quantidade,
  it.preco_unitario,
  ROUND(it.quantidade * it.preco_unitario, 2) AS subtotal
FROM pedidos ped
JOIN clientes cli ON cli.id = ped.cliente_id
JOIN itens_pedido it ON it.pedido_id = ped.id
JOIN produtos prod ON prod.id = it.produto_id;