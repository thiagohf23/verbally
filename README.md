# Verbally

Verbally é um treinador pessoal de compreensão oral, conversação e escrita em
inglês com apoio de IA. O fluxo principal prioriza ouvir antes de ler, responder
por voz e receber feedback curto depois de manter uma conversa real.

## Funcionalidades

- Correção de texto em inglês com streaming.
- Exibição de detalhes da correção com diff estruturado.
- Rotina diária guiada de aproximadamente 12 minutos.
- Diálogos curtos de listening com transcrição opcional e compreensão.
- Conversas por cenário com áudio antes do texto.
- Resposta por microfone com transcrição e análise da gravação pelo Gemini.
- Feedback de pronúncia com sons IPA e orientação de língua, lábios e fluxo de ar.
- Modos de fluência (feedback no fim) e estudo (feedback por turno).
- Controle de velocidade, shadowing e histórico de sessões.
- Flashcards áudio-first com repetição espaçada.
- Ações de acompanhamento:
  - reescrita natural;
  - exemplo curto relacionado à correção.
- Bloqueio de sessão enquanto uma operação está em andamento.
- Limite de 20 correções concluídas por sessão.
- Tratamento de erros recuperáveis, incluindo falta de configuração, timeout,
  limite de taxa e respostas inválidas do provedor.

## Captura

![Tela do Verbally](screenshots/verbally.png)

## Requisitos

- PHP `^8.3`
- Composer
- Node.js e npm
- Laravel `13`
- Livewire `4`
- Uma configuração válida do provedor Gemini usada pelas ações de IA do
  projeto

## Configuração

O projeto lê as seguintes variáveis de ambiente relacionadas à IA no .env:

```env
GEMINI_API_KEY=AQ.XXXXXXXXXXXXXXXXXXXXXXX # https://aistudio.google.com/api-keys
VERBALLY_GEMINI_MODEL=gemini-flash-latest # Modelo desejado
VERBALLY_AI_TIMEOUT_SECONDS=120 # timeout
```

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
```

## Desenvolvimento

O `composer.json` já expõe os comandos principais do projeto:

```bash
composer run setup
composer run dev
composer test
```

O comando `composer run dev` sobe a aplicação com servidor, fila, logs e
Vite em paralelo.

## Testes

```bash
composer test
```

Os testes cobrem a rota inicial, a lógica das ações de IA e o fluxo de browser
da interface.

## Estrutura relevante

- `routes/web.php`: rota principal da aplicação.
- `resources/views/pages/today/`: rotina diária e progresso recente.
- `resources/views/pages/listen/`: prática de compreensão com diálogos.
- `resources/views/pages/conversations/`: conversa por voz e feedback de pronúncia.
- `app/Actions/GenerateCorrection.php`: gera a correção em streaming e valida
  os detalhes estruturados.
- `app/Actions/AnalyzePronunciation.php`: envia áudio temporário ao Gemini e
  normaliza o feedback articulatório.
- `app/Actions/GenerateSessionFeedback.php`: produz o resumo final da conversa.
- `app/Actions/GenerateFollowUp.php`: gera os seguimentos de reescrita e exemplo.
- `app/Ai/`: agentes, prompts e runtime de IA.
- `resources/views/pages/index/`: página principal Livewire da aplicação.
- `resources/views/components/`: componentes reutilizáveis da interface.
- `tests/Feature/`: testes das ações e da rota.
- `tests/Browser/`: fluxo end-to-end da interface.

## Privacidade das gravações

As gravações do microfone usam o upload temporário do Livewire, são analisadas
durante a requisição e não são adicionadas ao histórico da aplicação. O banco
guarda somente transcrição, métricas e feedback. O Livewire remove uploads
temporários expirados automaticamente.

## Licença

MIT. O repositório declara `license: "MIT"` em `composer.json`.
