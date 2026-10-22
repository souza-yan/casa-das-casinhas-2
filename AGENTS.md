
# E-commerce sob demanda

## Stack obrigatória
- Laravel 12
- Livewire 3
- TallStackUI 3
- Alpine.js 3
- Tailwind CSS 4
- MySQL

Não atualizar as versões principais sem autorização.

## Arquitetura
- Backend monolítico Laravel.
- Componentes Blade para elementos visuais reutilizáveis.
- Livewire para comportamento dinâmico.
- Alpine para pequenas interações no navegador.
- TallStackUI para componentes adequados, especialmente administrativos.
- Eloquent para acesso ao banco.
- Actions ou Services para regras de negócio complexas.

## Frontend
- Reaproveitar o design do frontend de referência.
- Preservar identidade visual, tipografia e espaçamentos.
- Evitar gradientes e efeitos visuais desnecessários.
- Adotar responsividade mobile-first.
- Não criar componentes Livewire para conteúdo estático.
- Não substituir o design existente por interfaces genéricas.

## Segurança e execução
- Nunca modificar vendor ou node_modules.
- Nunca modificar o projeto usado como referência.
- Nunca sobrescrever arquivos .env ou credenciais.
- Nunca executar migrate:fresh sem autorização.
- Não instalar dependências sem justificar.
- Não executar operações destrutivas sem aprovação.
- Validar o código e executar testes relevantes.
- Trabalhar em etapas pequenas, relatando arquivos alterados.

## Organização
- Componentes visuais em resources/views/components.
- Layouts em resources/views/layouts.
- Componentes dinâmicos em app/Livewire.
- Testes em tests.
