# Casa das Casinhas

Loja de peças artesanais de madeira sob encomenda e com produtos em estoque. Inclui catálogo, busca e ordenação de produtos, carrinho, checkout, acompanhamento de pedidos, perfil do cliente e administração de produtos e pedidos.

## Estado atual e tecnologias

O projeto é um monólito Laravel com storefront público, checkout para clientes autenticados ou visitantes e painel administrativo. O estado atual inclui catálogo, produtos sob demanda e em estoque, personalização com referências, reserva transacional de estoque, consulta de endereço, cotação e etiqueta de frete, pagamento hospedado e webhooks.

| Camada | Versão suportada | Versão resolvida/auditada |
| --- | --- | --- |
| PHP | 8.2 ou superior | 8.4.27 no ambiente auditado |
| Laravel | 12 | 12.70.1 |
| Livewire | 3 | 3.8.11 |
| TallStackUI | 3 | 3.5.14 |
| Alpine.js | 3, fornecido pelo Livewire | gerenciado pelo Livewire |
| Tailwind CSS | 4 | 4.3.3 |
| Vite | 7 | 7.3.7 |
| PHPUnit | 11 | 11.5.57 |
| Banco da aplicação | MySQL | definido pelo ambiente |
| Banco dos testes | SQLite em memória | requer `pdo_sqlite` e `sqlite3` |

As faixas autorizadas ficam em `composer.json` e `package.json`; as versões reproduzíveis estão em `composer.lock` e `package-lock.json`. Não atualize versões principais sem revisar compatibilidade.

## Pré-requisitos

Instale Git, Composer 2, MySQL e Node.js compatível com Vite 7 (20.19+ ou 22.12+; recomenda-se Node 22.12+). O PHP precisa das extensões exigidas pelo Composer, além de `pdo_mysql` para acessar o MySQL. Para os testes, habilite `pdo_sqlite` e `sqlite3`. Confira o PHP do terminal com `php --ini` e `php -m`.

## Clonar e instalar

```sh
git clone https://github.com/souza-yan/casa-das-casinhas-2.git
cd casa-das-casinhas-2
composer install
npm ci
```

Crie o arquivo de configuração local **somente se `.env` ainda não existir**:

```sh
# Linux / macOS
cp .env.example .env
```

```powershell
# Windows / PowerShell
if (!(Test-Path .env)) { Copy-Item .env.example .env }
```

Crie um banco vazio no MySQL e ajuste o `.env` com os seus dados:

```dotenv
APP_NAME="Casa das Casinhas"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db-casa-das-casinhas
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

Não publique o `.env` nem credenciais. Em uma instalação nova, execute:

```sh
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
php artisan serve
```

Abra http://127.0.0.1:8000. O MySQL deve estar em execução. As migrations criam também as tabelas de sessão, cache e fila usadas pela configuração padrão.

## Desenvolvimento

Para atualizar CSS e JavaScript automaticamente, mantenha dois terminais abertos:

```sh
# Terminal 1
php artisan serve
```

```sh
# Terminal 2
npm run dev
```

Para processar tarefas assíncronas, abra outro terminal com `php artisan queue:work`. Alternativamente, `composer run dev` inicia servidor, fila, logs e Vite juntos em ambientes compatíveis com Laravel Pail.

No PowerShell, caso `npm.ps1` seja bloqueado pela política de execução, use `npm.cmd ci`, `npm.cmd run dev` e `npm.cmd run build`.

## Dados de demonstração

`php artisan db:seed` cria ou atualiza cinco produtos e dois usuários locais:

| Perfil | E-mail padrão | Senha padrão |
| --- | --- | --- |
| Administrador | admin@casadascasinhas.test | password |
| Cliente | cliente@casadascasinhas.test | password |

O administrador acessa `/admin`. Configure `ADMIN_NAME`, `ADMIN_EMAIL` e `ADMIN_PASSWORD` no `.env` para alterar os dados do administrador. O cliente usa opcionalmente `CUSTOMER_NAME`, `CUSTOMER_EMAIL` e `CUSTOMER_PASSWORD`; na ausência delas, valem os padrões de `config/customer.php`.

Os seeders são de demonstração e recusam execução em produção. Reexecutá-los atualiza os dados dos produtos e usuários de demonstração, incluindo senhas, preços e imagens.

## Modalidade de venda e estoque

No cadastro e na edição em `/admin/produtos`, use o switch **Em estoque**: desligado para **Sob demanda**, ligado para informar a quantidade disponível.

- Sob demanda: produção em 15 dias, sem controle de quantidade. Os produtos anteriores permanecem nesta modalidade.
- Em estoque: quantidade inteira obrigatória, a partir de zero. Zero mostra o produto como esgotado e impede a compra. Não há prazo de produção.
- O campo de estoque representa unidades disponíveis para novas vendas, sem incluir as reservadas em pedidos.
- O carrinho valida a soma de todas as linhas do mesmo produto, inclusive personalizadas; adicionar ao carrinho não reserva unidades.
- Ao criar o pedido, a aplicação revalida e reduz o estoque dentro de uma transação com bloqueio dos produtos. Repetir o mesmo checkout não reduz o estoque novamente.
- Pedidos aguardando pagamento mantêm a reserva. Não há expiração automática: cancele o pedido no painel para devolver as unidades. Reembolso de pagamento não cancela o pedido automaticamente.
- O cancelamento devolve somente unidades de itens vendidos em estoque, usando a modalidade registrada no pedido. A devolução acontece uma única vez, inclusive se o produto tiver sido excluído logicamente.
- Pedidos mistos mantêm o prazo de produção das peças sob demanda. Pedidos apenas de estoque podem passar de pagamento confirmado para pronto para envio.
- O cadastro não tem uma opção separada de personalização: produtos sob demanda aceitam personalização; produtos em estoque são peças prontas e não solicitam instruções nem referências.

Ao atualizar uma instalação existente, execute `php artisan migrate` para adicionar os campos sem apagar os dados.

## Imagens dos produtos

As imagens de demonstração ficam em `database/seeders/assets/products/`, com nomes terminando em `-1200x1500.png`. O `ProductSeeder` copia esses arquivos para `storage/app/public/products` e registra os caminhos no banco.

Depois de trocar uma imagem de demonstração, atualize o arquivo na pasta do seeder e execute:

```sh
php artisan db:seed --class=ProductSeeder
```

Os nomes precisam corresponder aos caminhos definidos em `database/seeders/ProductSeeder.php`. Imagens cadastradas pela administração são salvas no disco público. O link criado por `php artisan storage:link` permite acessar os arquivos por `/storage/products/...`.

`storage` contém arquivos locais e não substitui a pasta de imagens versionadas do seeder. Inclua as novas imagens de origem no commit para que funcionem em outra instalação. As imagens estáticas das páginas ficam em `public/images`.

## Integrações

A navegação do catálogo não exige credenciais externas. Fluxos de pagamento, frete e notificações dependem das configurações correspondentes no `.env`:

| Serviço | Configuração |
| --- | --- |
| Asaas | `ASAAS_URL`, `ASAAS_TOKEN`, `ASAAS_WEBHOOK_TOKEN`, `ASAAS_USER_AGENT`, `ASAAS_SUCCESS_URL` |
| SuperFrete | `SUPERFRETE_URL`, `SUPERFRETE_TOKEN`, `SUPERFRETE_WEBHOOK_SECRET`, dados `SUPERFRETE_ORIGIN_*`, `SUPERFRETE_NON_COMMERCIAL`, `SUPERFRETE_USER_AGENT` |
| WhatsApp | `WHATSAPP_URL`, `WHATSAPP_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_RECIPIENT`, `WHATSAPP_TEMPLATE`, `WHATSAPP_VERIFY_TOKEN`, `WHATSAPP_APP_SECRET` |
| E-mail | Variáveis `MAIL_*`; por padrão, `MAIL_MAILER=log` grava as mensagens em `storage/logs/laravel.log` |

Use os ambientes sandbox durante o desenvolvimento. `ASAAS_SUCCESS_URL` deve ser a URL pública cadastrada no Asaas; a aplicação acrescenta a rota segura do pedido. O retorno do navegador apenas informa que o pagamento está em análise: a confirmação real ocorre pelo webhook. Consulte [docs/arquitetura.md](docs/arquitetura.md) para detalhes de regras de negócio e webhooks.

## Redes sociais

Os ícones do topo, menu mobile e rodapé compartilham os links de  `config/social.php`. Configure `SOCIAL_INSTAGRAM_URL`, `SOCIAL_FACEBOOK_URL` e `SOCIAL_WHATSAPP_URL` no .env. Para abrir uma conversa, use um link como `https://wa.me/55DDDNUMERO`, substituindo pelo número real com código do país e DDD, apenas dígitos. Os valores padrão usam os perfis oficiais da Casa das Casinhas e o WhatsApp +55 (12) 98896-1502.

## Checkout, CEP e frete

O checkout possui quatro etapas: dados pessoais, endereço, escolha da entrega e revisão/pagamento. CPF ou CNPJ válido é obrigatório, é persistido sem pontuação no endereço do pedido e aparece mascarado nas telas de acompanhamento. Telefone e documento mantêm a máscara visual sem enviar uma requisição Livewire a cada tecla.

Na etapa de entrega, o CEP completo consulta o [ViaCEP](https://viacep.com.br/) pelo backend e preenche rua, bairro, cidade e UF. O cliente informa o número e, se necessário, o complemento. Campos não fornecidos pela consulta ficam editáveis; em caso de indisponibilidade, o preenchimento manual é liberado.

A cotação da SuperFrete também é acionada automaticamente após o CEP, sem botão de cálculo. Ela exige `SUPERFRETE_TOKEN` e `SUPERFRETE_ORIGIN_POSTAL_CODE`. Ao mudar o CEP, o endereço anterior, o número, o complemento e a seleção de frete são limpos. O frete é revalidado no servidor antes de criar o pedido, e a resposta selecionada é preservada para a futura emissão da etiqueta.

O resumo exibe subtotal e, após selecionar uma entrega, o valor do frete e o total. A consulta do ViaCEP não requer token. Os testes de integração usam respostas simuladas, sem depender de credenciais ou da rede.

Ao finalizar, a aplicação cria ou reaproveita a cobrança e redireciona para a página HTTPS hospedada pelo Asaas. Visitantes recebem por e-mail um link secreto de acompanhamento, válido por 180 dias e reutilizável em qualquer dispositivo, sem necessidade de cadastro. Caso percam esse e-mail, podem solicitar um novo link em `/acompanhar-pedido` informando o número do pedido e o mesmo e-mail da compra; a resposta pública é sempre genérica para não expor pedidos existentes. Clientes autenticados acompanham e retomam o pagamento em `/pedidos`. Se o Asaas estiver temporariamente indisponível, o pedido permanece criado e pode ser pago depois.

No painel administrativo, a etiqueta SuperFrete pode ser emitida quando o pedido estiver pronto para envio e seu PDF pode ser aberto para impressão. Além do token, a emissão exige os dados completos de origem em `SUPERFRETE_ORIGIN_*`; o documento do destinatário vem do checkout. Após a emissão, o código de rastreio é consultado imediatamente, repetido pela fila enquanto estiver em processamento e recuperado de hora em hora pelo agendador. Quando o código fica disponível, o cliente recebe uma única notificação por e-mail. Administrador e cliente também podem solicitar uma atualização manual.

Cadastre no painel/API da SuperFrete a URL pública `https://SEU-DOMINIO/webhooks/superfrete` para os eventos `order.created`, `order.released`, `order.generated`, `order.posted`, `order.delivered` e `order.cancelled`. Salve o `secret_token` retornado em `SUPERFRETE_WEBHOOK_SECRET`. A assinatura `X-ME-Signature` é validada antes do processamento; postagem e entrega atualizam o frete e o pedido automaticamente.

Se a consulta real apresentar `cURL error 60`, confira o PHP usado pelo servidor (`php --ini`) e configure `curl.cainfo` e `openssl.cafile` com um arquivo de autoridades certificadoras confiável para o ambiente. Em redes com inspeção HTTPS, esse arquivo também precisa incluir a autoridade da rede. Reinicie o servidor após ajustar o PHP. A aplicação mantém a validação SSL ativa e permite preencher o endereço manualmente quando a consulta falha.

## Testes e qualidade

```sh
php artisan test
php artisan test --filter=DevelopmentSeederTest
vendor/bin/pint --test
npm run build
```

O `phpunit.xml` configura SQLite em memória para isolar os testes do MySQL local. O erro `could not find driver` nesse contexto indica que falta habilitar o driver SQLite no PHP usado pelo terminal.

## Problemas comuns

- **Vite manifest not found:** execute `npm ci` e `npm run build`, ou mantenha `npm run dev` em execução durante o desenvolvimento. O diretório `public/build` não é versionado.
- **Imagem não aparece:** confira o nome do arquivo, o caminho no banco e o link `public/storage`. Execute `php artisan storage:link` se o link estiver ausente; para as imagens de demonstração, execute o `ProductSeeder` após atualizar os arquivos de origem.
- **Imagem de origem não encontrada:** confira se os arquivos de `database/seeders/assets/products` foram incluídos no clone e se os nomes coincidem com o seeder.
- **Erro de conexão com MySQL:** confirme que o serviço está ativo, que o banco existe e que as variáveis `DB_*` estão corretas.
- **Configuração antiga após editar `.env`:** execute `php artisan config:clear`.
- **Application key ausente:** em uma instalação nova, execute `php artisan key:generate`.

## Estrutura

- `app/Livewire`: comportamento dinâmico da loja e da administração.
- `app/Actions` e `app/Services`: regras de negócio e integrações.
- `resources/views/components`: componentes Blade reutilizáveis.
- `resources/views/layouts`: layouts.
- `resources/views`: páginas e views Livewire.
- `database/migrations` e `database/seeders`: estrutura do banco e dados de demonstração.
- `tests`: testes automatizados.

## Publicação

No servidor, configure o document root para `public/`, use `APP_ENV=production` e `APP_DEBUG=false`, configure credenciais próprias e permita escrita em `storage` e `bootstrap/cache`. Instale as dependências PHP sem as de desenvolvimento, gere os assets com `npm ci && npm run build`, execute as migrations do deploy e crie o link de storage. Configure também HTTPS, e-mail, um processo permanente para `php artisan queue:work` e o cron do Laravel com `php artisan schedule:run` a cada minuto. Esses processos são necessários para webhooks, notificações e novas tentativas de rastreio. Não execute os seeders de demonstração em produção.

Modelos de unidades systemd para o worker e o agendador estão em `deploy/systemd`. Antes de copiá-los para `/etc/systemd/system`, ajuste `WorkingDirectory`, `User`, `Group` e o caminho do PHP conforme o servidor. Depois, habilite `casa-das-casinhas-queue.service` e `casa-das-casinhas-scheduler.timer` com `systemctl enable --now`.
