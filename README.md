# SuperForcaV

Aplicação de força de vendas baseada em CodeIgniter, com serviços REST e telas mobile para painel e pedidos.

## Configuração local

1. Use PHP com a extensão Firebird (`ibase`) habilitada e configure o Apache para servir este diretório.
2. Copie `application/config/database.php.example` para `application/config/database.php`.
3. Configure no ambiente do Apache:
   - `FORCAVWS_DB_HOST`
   - `FORCAVWS_DB_USERNAME`
   - `FORCAVWS_DB_PASSWORD`
   - `FORCAVWS_DB_2222_PATH`
   - `FORCAVWS_DB_3333_PATH`
   - `FORCAVWS_DB_4444_PATH`
4. Reinicie o Apache após configurar as variáveis de ambiente.

Os três caminhos de banco correspondem aos grupos selecionados pelas portas HTTP 2222, 3333 e 4444. Não versione credenciais, arquivos de banco nem dados reais de pedidos ou clientes.

## Dados de banco

O dump local de banco e arquivos de conexão com credenciais/dados operacionais foram intencionalmente excluídos deste repositório público. Obtenha o schema e os dados de inicialização por um canal autorizado.
