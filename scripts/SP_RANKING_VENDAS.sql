SET TERM ^ ;

CREATE OR ALTER procedure SP_RANKING_VENDAS (
    P_ID_EMPRESA integer,
    P_DATA_INI date,
    P_DATA_FIM date,
    P_TIPO smallint,
    P_ID_REPRESENTANTE integer,
    P_ID_EQUIPE integer,
    P_ID_CLIENTE integer)
returns (
    TIPO_RANKING varchar(15),
    DESCRICAO varchar(100),
    VALOR_TOTAL numeric(18,2),
    QUANTIDADE numeric(18,4))
as
declare variable TIPO_PRODUTO integer = 0;
declare variable TIPO_REPRESENTANTE integer = 1;
declare variable TIPO_FABRICANTE integer = 2;
declare variable TIPO_CIDADE integer = 3;
BEGIN
  IF (P_TIPO = TIPO_PRODUTO) THEN BEGIN -- Tipo 0: Top 20 produtos mais vendidos
    FOR
      SELECT FIRST 20
             'PRODUTO' AS TIPO_RANKING,
             PRODUTO.DESCRICAO AS NOME,
             SUM(ITEM_PED.VALOR_ITEM) AS VALOR_TOTAL,
             SUM(ITEM_PED.QUANT) AS QUANTIDADE
        FROM PEDIDO
        JOIN ITEM_PED  ON (PEDIDO.ID_PED  = ITEM_PED.ID_PED)
        JOIN CFOP      ON (CFOP.ID_CFOP  = ITEM_PED.ID_CFOP)
         AND CFOP.CLASSIF = 1
        JOIN CLIENTE   ON (PEDIDO.ID_PES    = CLIENTE.ID_PES)
        JOIN PESSOA    ON (PESSOA.ID_PES    = CLIENTE.ID_PES)
        JOIN CIDADE    ON (CIDADE.ID_CID    = PESSOA.ID_CID)
        JOIN REPRES    ON (REPRES.ID_PES    = PEDIDO.ID_REP)
        JOIN EQUIPE    ON (EQUIPE.ID_EQU    = REPRES.ID_EQU)
        JOIN RAMO_ATIV ON (RAMO_ATIV.ID_RAT = CLIENTE.ID_RAT)
        JOIN PRODUTO   ON (PRODUTO.ID_PRO   = ITEM_PED.ID_PRO)
        JOIN FORNEC    ON (FORNEC.ID_PES    = PRODUTO.ID_FOR)
        JOIN SUB_BRAND ON (SUB_BRAND.ID_SBR = PRODUTO.ID_SBR)
       WHERE PEDIDO.ID_EMP = :P_ID_EMPRESA
         AND PEDIDO.EMISSAO >= :P_DATA_INI
         AND PEDIDO.EMISSAO <= :P_DATA_FIM
         AND PEDIDO.STATUS IN (1,2)
         AND PEDIDO.ID_COP = 1
         AND (:P_ID_REPRESENTANTE = 0 OR PEDIDO.ID_REP = :P_ID_REPRESENTANTE)
         AND (:P_ID_CLIENTE = 0 OR PEDIDO.ID_PES = :P_ID_CLIENTE)
         AND (:P_ID_EQUIPE = 0 OR REPRES.ID_EQU = :P_ID_EQUIPE)
       GROUP BY PRODUTO.ID_PRO, PRODUTO.DESCRICAO
       ORDER BY 4 DESC
        INTO :TIPO_RANKING,
             :DESCRICAO,
             :VALOR_TOTAL,
             :QUANTIDADE
    DO
      SUSPEND;
  END ELSE IF (P_TIPO = TIPO_REPRESENTANTE) THEN BEGIN -- Tipo 1: Top 20 representantes que mais venderam
    FOR
      SELECT FIRST 20
             'REPRESENTANTE' AS TIPO_RANKING,
             PE.RAZ_SOC AS NOME,
             SUM(P.TOTAL_NEG) AS VALOR_TOTAL,
             COUNT(ID_PED) AS QUANTIDADE
        FROM PEDIDO P
        JOIN PESSOA PE ON (P.ID_REP = PE.ID_PES)
        JOIN REPRES R ON (R.ID_PES = P.ID_REP)
       WHERE P.ID_EMP = :P_ID_EMPRESA
         AND P.EMISSAO >= :P_DATA_INI
         AND P.EMISSAO <= :P_DATA_FIM
         AND P.STATUS IN (1,2)
         AND P.ID_COP = 1
         AND PE.TIPO_PESSOA = 1
         AND (:P_ID_REPRESENTANTE = 0 OR P.ID_REP = :P_ID_REPRESENTANTE)
         AND (:P_ID_CLIENTE = 0 OR P.ID_PES = :P_ID_CLIENTE)
         AND (:P_ID_EQUIPE = 0 OR R.ID_EQU = :P_ID_EQUIPE)
       GROUP BY PE.RAZ_SOC
       ORDER BY 3 DESC
        INTO :TIPO_RANKING,
             :DESCRICAO,
             :VALOR_TOTAL,
             :QUANTIDADE
    DO
      SUSPEND;
  END ELSE IF (P_TIPO = TIPO_FABRICANTE) THEN BEGIN -- Tipo 2: Top 20 fornecedores que mais venderam
    FOR
      SELECT FIRST 20
             'FORNECEDORES' AS TIPO_RANKING,
             PE.RAZ_SOC AS NOME,
             SUM(I.VALOR_ITEM) AS VALOR_TOTAL,
             SUM(I.QUANT) AS QUANTIDADE
        FROM PEDIDO P
        JOIN ITEM_PED I ON (P.ID_PED = I.ID_PED)
        JOIN PRODUTO PR ON (I.ID_PRO = PR.ID_PRO)
        JOIN PESSOA PE ON (PR.ID_FOR = PE.ID_PES)
       WHERE P.ID_EMP = :P_ID_EMPRESA
         AND P.EMISSAO >= :P_DATA_INI
         AND P.EMISSAO <= :P_DATA_FIM
         AND P.STATUS IN (1,2)
         AND P.ID_COP = 1
         AND PE.TIPO_PESSOA = 2
         AND (:P_ID_REPRESENTANTE = 0 OR P.ID_REP = :P_ID_REPRESENTANTE)
         AND (:P_ID_CLIENTE = 0 OR P.ID_PES = :P_ID_CLIENTE)
         AND (:P_ID_EQUIPE = 0 OR EXISTS (
             SELECT 1
               FROM REPRES R
              WHERE R.ID_PES = P.ID_REP
                AND R.ID_EQU = :P_ID_EQUIPE))
       GROUP BY PE.RAZ_SOC
       ORDER BY 3 DESC
        INTO :TIPO_RANKING,
             :DESCRICAO,
             :VALOR_TOTAL,
             :QUANTIDADE
    DO
      SUSPEND;
  END ELSE IF (P_TIPO = TIPO_CIDADE) THEN BEGIN -- Tipo 3: Top 20 cidades que mais venderam
    FOR
      SELECT FIRST 20
             'CIDADE' AS TIPO_RANKING,
             C.DESCRICAO AS NOME,
             SUM(P.TOTAL_NEG) AS VALOR_TOTAL,
             COUNT(ID_PED) AS QUANTIDADE
        FROM PEDIDO P
        JOIN PESSOA PE ON (P.ID_PES = PE.ID_PES)
        JOIN CIDADE C ON (PE.ID_CID = C.ID_CID)
       WHERE P.ID_EMP = :P_ID_EMPRESA
         AND P.EMISSAO >= :P_DATA_INI
         AND P.EMISSAO <= :P_DATA_FIM
         AND P.STATUS IN (1,2)
         AND P.ID_COP = 1
         AND PE.TIPO_PESSOA = 0
         AND (:P_ID_REPRESENTANTE = 0 OR P.ID_REP = :P_ID_REPRESENTANTE)
         AND (:P_ID_CLIENTE = 0 OR P.ID_PES = :P_ID_CLIENTE)
         AND (:P_ID_EQUIPE = 0 OR EXISTS (
             SELECT 1
               FROM REPRES R
              WHERE R.ID_PES = P.ID_REP
                AND R.ID_EQU = :P_ID_EQUIPE))
       GROUP BY C.DESCRICAO
       ORDER BY 3 DESC
        INTO :TIPO_RANKING,
             :DESCRICAO,
             :VALOR_TOTAL,
             :QUANTIDADE
    DO
      SUSPEND;
  END
END^

SET TERM ; ^
