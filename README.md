# Documentação do Back-end

API REST em Laravel 13 para a aplicação Carteira Virtual. Esta documentação descreve a stack, as rotas disponíveis, como rodar com Docker e como testar os serviços.

## Stack do Back-end

- Laravel 13
- PHP 8.3
- MySQL 8.1
- Laravel Sanctum para autenticação de API
- Pest para testes automatizados
- Docker Compose para orquestração de contêineres

## Arquitetura

- `routes/api.php` — define todas as rotas da API
- `app/Http/Controllers/ContaController.php` — autenticação, criação de conta, logout e dados do usuário
- `app/Http/Controllers/OperationController.php` — operações financeiras e histórico
- `app/Services/ContaService.php` — regras de negócio de conta e usuário
- `app/Services/OperationService.php` — regras de negócio de operações de carteira
- `app/Repositories` — acesso a dados e persistência
- `app/Entities` — representações de domínio para User, Wallet e Transation

## Endpoints da API

### Autenticação

#### POST `/api/login`

- Descrição: realiza login do usuário.
- Parâmetros:
  - `email` (string, obrigatório)
  - `password` (string, obrigatório)
- Retorno sucesso:
  - Status `201`
  - JSON com `token` e `user`
- Retorno erro:
  - Status `401` em credenciais inválidas

#### POST `/api/conta`

- Descrição: cria nova conta e carteira associada.
- Parâmetros:
  - `name` (string, obrigatório)
  - `email` (string, obrigatório)
  - `password` (string, obrigatório, mínimo 6 caracteres)
- Retorno sucesso:
  - Status `201`
  - JSON com os dados do usuário e carteira

#### POST `/api/logout`

- Descrição: encerra a sessão do usuário atual.
- Requer autenticação via token
- Retorno sucesso:
  - Status `200`
  - JSON com mensagem de logout
- Erro:
  - Status `401` se o usuário não estiver autenticado

### Rotas autenticadas

Todas as rotas abaixo exigem `Authorization: Bearer <token>`.

#### GET `/api/conta`

- Descrição: recupera dados do usuário autenticado, incluindo a carteira.
- Retorno sucesso:
  - Status `200`
  - JSON com dados do usuário e `wallet`

#### GET `/api/history/{walletId}`

- Descrição: lista o histórico de transações da carteira.
- Parâmetros:
  - `walletId` (inteiro, obrigatório)
- Retorno sucesso:
  - Status `200`
  - JSON com lista de transações
- Retorno erro:
  - Status `404` se a carteira não existir

#### POST `/api/deposit`

- Descrição: realiza depósito em uma carteira.
- Parâmetros:
  - `wallet_id` (inteiro, obrigatório)
  - `amount` (numérico, obrigatório)
- Retorno sucesso:
  - Status `201`
  - JSON com carteira atualizada
- Retorno erro:
  - Status `404` se a carteira não existir

#### POST `/api/withdraw`

- Descrição: realiza saque de uma carteira.
- Parâmetros:
  - `wallet_id` (inteiro, obrigatório)
  - `amount` (numérico, obrigatório)
- Retorno sucesso:
  - Status `201`
  - JSON com carteira atualizada
- Retorno erro:
  - Status `404` se a carteira não existir
  - Status `400` se fundos insuficientes

#### POST `/api/transfer`

- Descrição: transfere saldo entre carteiras.
- Parâmetros:
  - `from_wallet_id` (inteiro, obrigatório)
  - `to_wallet_id` (inteiro, obrigatório)
  - `amount` (numérico, obrigatório)
- Retorno sucesso:
  - Status `201`
  - JSON com resultado da transferência
- Retorno erro:
  - Status `404` se uma das carteiras não existir
  - Status `400` se fundos insuficientes

## Execução com Docker

1. A partir da pasta raiz do projeto:

```bash
docker compose up --build
```

2. O serviço backend estará disponível em:

- `http://localhost:8000`

3. A API base é:

- `http://localhost:8000/api`

## Testes

- Os testes do back-end estão localizados em `back-end/tests/Unit/Services`.
- Executar os testes:

```bash
cd back-end
vendor/bin/pest
```

## Observações adicionais

- O serviço de backend está configurado para usar MySQL no container `mysql`.
- As rotas protegidas usam middleware `auth:sanctum`.
- Erros internos são registrados via `Log::error` e retornam mensagens genéricas para a API.
- O código do serviço financeiro trata depósitos, saques, transferências e histórico de transações em transações de banco de dados.
