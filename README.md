# Casa das Casinhas

Loja de peças artesanais de madeira sob encomenda e com produtos em estoque. Inclui catálogo, busca e ordenação de produtos, carrinho, checkout, acompanhamento de pedidos, perfil do cliente e administração de produtos e pedidos.

## Tecnologias

- PHP 8.2 ou superior e Laravel 12.
- Livewire 3, Alpine.js 3 e TallStackUI 3.
- Tailwind CSS 4 e Vite 7.
- MySQL para a aplicação; SQLite em memória para os testes.

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

O administrador acessa `/admin`. Configure `ADMIN_NAME`, `ADMIN_EMAIL` e `ADMIN_PASSWORD` no `.env` para alterar os dados do administrador. O cliente usa `CUSTOMER_NAME`, `CUSTOMER_EMAIL` e `CUSTOMER_PASSWORD`.

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
| Asaas | `ASAAS_URL`, `ASAAS_TOKEN`, `ASAAS_WEBHOOK_TOKEN` |
| Melhor Envio | `MELHOR_ENVIO_URL`, `MELHOR_ENVIO_TOKEN`, `MELHOR_ENVIO_ORIGIN_ZIP_CODE`, `MELHOR_ENVIO_USER_AGENT` |
| WhatsApp | `WHATSAPP_URL`, `WHATSAPP_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_RECIPIENT`, `WHATSAPP_TEMPLATE`, `WHATSAPP_VERIFY_TOKEN`, `WHATSAPP_APP_SECRET` |
| E-mail | Variáveis `MAIL_*`; por padrão, `MAIL_MAILER=log` grava as mensagens em `storage/logs/laravel.log` |

Use os ambientes sandbox durante o desenvolvimento. Consulte [docs/arquitetura.md](docs/arquitetura.md) para detalhes de regras de negócio e webhooks.

## Redes sociais

Os ícones do topo, menu mobile e rodapé compartilham os links de  `config/social.php`. Configure `SOCIAL_INSTAGRAM_URL`, `SOCIAL_FACEBOOK_URL` e `SOCIAL_WHATSAPP_URL` no .env. Para abrir uma conversa, use um link como `https://wa.me/55DDDNUMERO`, substituindo pelo número real com código do país e DDD, apenas dígitos. Os valores padrão usam os perfis oficiais da Casa das Casinhas e o WhatsApp +55 (12) 98896-1502.

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

No servidor, configure o document root para `public/`, use `APP_ENV=production` e `APP_DEBUG=false`, configure credenciais próprias e permita escrita em `storage` e `bootstrap/cache`. Instale as dependências PHP sem as de desenvolvimento, gere os assets com `npm ci && npm run build`, execute as migrations do deploy e crie o link de storage. Configure também HTTPS, e-mail, processamento da fila e as integrações utilizadas. Não execute os seeders de demonstração em produção.