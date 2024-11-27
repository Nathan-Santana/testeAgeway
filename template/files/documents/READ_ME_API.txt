Documentação da Integração com a API Numverify
1. API Escolhida
A integração foi realizada com a API Numverify, uma API dedicada à validação de números de telefone em diversos formatos internacionais. Ela fornece informações como validade do número, operadora, tipo de linha (móvel ou fixa), entre outras.

Link da API: Numverify API

2. Como foi implementada a integração
A integração foi feita através de uma requisição HTTP GET para o endpoint disponibilizado pela API. O fluxo básico consiste em:

Capturar o número de telefone informado pelo usuário.

Limpar o número de caracteres especiais para o formato numérico.

Montar a URL da requisição com os parâmetros necessários, como:

Chave de acesso (API Key);
Número de telefone;
Código de país (BR para Brasil);
Formato da resposta (JSON).
Enviar a requisição utilizando a biblioteca cURL do PHP.

Processar a resposta JSON retornada pela API e exibir os dados na interface do sistema.

3. Configurações adicionais necessárias
3.1 Chave de API
Uma API Key válida é necessária para o funcionamento. A chave é fornecida no cadastro no site oficial da API Numverify.
No código, a chave de API deve ser atribuída na variável $apiKey:
php
Copiar código
$apiKey = 'sua_chave_de_api_aqui';
3.2 Dependências do PHP
Certifique-se de que a extensão cURL do PHP está habilitada no servidor. Para verificar, use o comando:
bash
Copiar código
php -m | grep curl
Caso não esteja habilitada, instale-a:
bash
Copiar código
sudo apt-get install php-curl
sudo service apache2 restart
3.3 Banco de Dados
O programa foi registrado no sistema por meio da tabela system_program. O registro foi feito com o comando:
sql
Copiar código
INSERT INTO system_program (id, name, controller)
VALUES (67, 'Verificação de Telefone', 'VerificacaoTelefone');
Adicione as permissões necessárias ao grupo de usuários no sistema.
3.4 Menu XML
Foi configurado um item no menu.xml para permitir acesso à funcionalidade:
xml
Copiar código
<menu label="Verificação de Telefone" action="VerificacaoTelefone" icon="fa:phone" />
4. Notas importantes
O uso da API em ambientes de produção pode estar sujeito a limitações de requisições no plano gratuito. Para maior volume de consultas, considere assinar um plano pago.
A aplicação trata exceções no caso de falhas na requisição à API ou respostas inválidas, garantindo estabilidade.