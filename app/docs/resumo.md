Perfeito.
Abaixo está o RESUMO-MÃE atualizado, já fundido, organizado e coerente arquiteturalmente — agora ele vira oficialmente o:

✅ Documento de Continuidade Absoluta do Breve-Sonoro

Você pode colar isso inteiro em qualquer novo chat e o projeto continua exatamente do mesmo ponto mental.

🧠 📦 BREVE-SONORO — RESUMO COMPLETO DEFINITIVO
🎯 VISÃO DO PROJETO

breve-sonoro é um sistema web de tracking musical pessoal.

O foco não é streaming.

O foco é:

👉 acompanhar a experiência humana de ouvir música.

O sistema permite:

registrar escutas
avaliar faixas
marcar favoritas
acompanhar progresso de álbuns
manter uma dashboard musical pessoal

Inspirado conceitualmente em:

Letterboxd (filmes)
Goodreads (livros)
Last.fm (escuta)
backlog trackers
🧠 FILOSOFIA DO PROJETO

O projeto segue decisões conscientes:

✅ backend-first
✅ domínio antes de visual
✅ regras explícitas
✅ interface evolui depois

Decisão arquitetural assumida:

o sistema é pensado como software primeiro, site depois.

🏗️ ARQUITETURA ATUAL
breve-sonoro/
├─ app/
│  ├─ actions/
│  ├─ docs/
│  ├─ includes/
│  ├─ services/
│  └─ .htaccess
├─ public/
│  ├─ actions/
│  ├─ admin/
│  ├─ assets/
│  ├─ uploads/
│  ├─ _init.php
│  ├─ album.php
│  ├─ dash.php
│  ├─ index.php
│  ├─ login.php
│  └─ logout.php
├─ storage/
├─ .env
└─ README.md

🔥 DECISÃO ARQUITETURAL MAIS IMPORTANTE
✅ bootstrap.php virou o Kernel do Sistema

Todo endpoint inicia com:

require "../includes/bootstrap.php";


O bootstrap centraliza:

conexão PDO
sessão
helpers globais
segurança
config
ambiente
padronização

👉 Scripts PHP deixaram de existir.
👉 Agora existe uma aplicação.

🧩 CAMADAS DO SISTEMA
1️⃣ Pages (Views)

Exemplos:

index.php
album.php
dash.php


Responsabilidade:

✔ renderizar
✔ chamar services
✔ exibir estado

PROIBIDO:

❌ acessar banco
❌ conter regra de domínio

2️⃣ Services (Domínio Real)

Exemplos:

albumService.php
AlbumStreamingService.php


Responsáveis por:

queries
cálculos
agregações
regras do produto

👉 A inteligência vive aqui.

3️⃣ Actions (Eventos do Sistema)

Localização:

public/actions/ → endpoints públicos
app/actions/    → lógica privada


Responsabilidade:

✔ receber POST
✔ validar segurança
✔ chamar Service
✔ redirecionar ou retornar JSON

Nunca:

❌ renderizam HTML
❌ possuem regra de negócio
❌ acessam SQL direto

🧠 NOVO CONCEITO INTRODUZIDO
Actions são EVENTOS

Actions deixaram de ser páginas PHP.

Agora representam:

POST salvar_streaming
POST salvar_avaliacao
POST adicionar_dash
POST toggle_favorito


Fluxo oficial:

Request → Action → Service → Estado → Redirect/JSON

🔐 PADRÃO OFICIAL DE ACTIONS

Toda Action obrigatoriamente:

require bootstrap

verificarLogin();
verificarAdmin(); // quando necessário
validarCSRF();

validar dados
executar service

redirect + exit;


Regra mental:

Recebe → Valida → Executa → Sai

🔐 SEGURANÇA CONSOLIDADA

Proteções ativas:

✅ sessão obrigatória
✅ CSRF
✅ POST obrigatório
✅ prepared statements
✅ isolamento public/app

CSRF virou Guard Pattern

Não retorna boolean.

validarCSRF(...)


Se inválido:

👉 execução é interrompida.

🧠 MODELO PUBLIC → APP

Separação definitiva:

app/      → privado (domínio)
public/   → web root


Isso eliminou:

❌ acesso direto a lógica interna
❌ URLs quebradas
❌ vazamento arquitetural

🗄️ MODELO DE DADOS (CONCEITUAL)

Principais entidades:

albuns
bandas
faixas
reproducoes
avaliacoes
usuario_dash
progresso_album
usuarios
generos
🧠 REGRA CENTRAL DO PRODUTO
Progresso Musical
Ação	Peso
Ouviu	50%
Avaliou	+50%

Faixa completa:

ouviu + avaliou


Progresso do álbum:

(contribuição total / nº faixas) * 100


👉 decisão de design de produto, não técnica.

🎧 NOVO DOMÍNIO INTRODUZIDO
Sistema de Streaming de Álbuns
Objetivo

Adicionar links externos abaixo da capa do álbum:

🎵 Ouça este álbum
[ Spotify ]
[ Qobuz ]

Decisão Arquitetural Crítica

Streaming não pertence ao álbum.

Novo domínio:

Album
   ↳ StreamingLinks


Evita:

❌ alterar schema no futuro
❌ acoplamento com plataformas
❌ colunas infinitas

Nova Tabela
album_streaming


Campos:

id
album_id
plataforma
url
criado_em

Princípio Introduzido
Plataforma é Dado, não Código

Sistema aceita automaticamente:

spotify
qobuz
apple_music
deezer
youtube_music
tidal
bandcamp
soundcloud


sem refactor.

Nova Service Layer
app/services/AlbumStreamingService.php


Responsabilidades:

salvarLinksStreaming()
buscarLinksStreaming()


Regra absoluta:

Action → Service → Banco


Nunca:

Action → SQL ❌

Integração com album.php

A página passou a:

buscar links via Service
renderizar dinamicamente
exibir apenas links existentes
permitir edição admin
🧱 CORREÇÕES ESTRUTURAIS IMPORTANTES
✔ Erro 404 Actions

Solução:

public/actions/ = endpoint
app/actions/    = lógica


Criado proxy público.

✔ Bootstrap Idempotente

Correção:

defined('BASE_PATH') or define(...);


Bootstrap pode ser carregado múltiplas vezes sem quebrar.

✔ Erro Fatal CSRF

Função correta:

validarCSRF()


Não:

validarCSRFToken()

🔒 REGRA GLOBAL ADICIONADA

Adicionar ao docs/regras.txt:

26. PADRÃO DE ACTIONS

Actions não possuem interface.
Actions não possuem regra de domínio.
Actions apenas disparam Services.

🎨 FRONTEND

Estado atual:

grid responsivo
cards de álbum
barra de progresso
tema claro + dark

Decisão oficial:

👉 frontend NÃO é prioridade.

🧠 ESTADO ARQUITETURAL ATUAL

O Breve-Sonoro agora possui:

✅ Kernel arquitetural
✅ Segurança profissional
✅ Services como domínio real
✅ Actions padronizadas
✅ Separação Public/App
✅ Streaming extensível
✅ Guard Security
✅ Base pronta para AJAX
✅ Modelo DDD-Lite emergente

🧱 EVOLUÇÃO DO SISTEMA
Bootstrap   → Kernel
Session     → Autenticação
CSRF        → Guard Security
Config      → Ambiente
Actions     → Eventos
Services    → Domínio
Views       → Representação
Streaming   → Extensão de Domínio

⚠️ LIMITAÇÃO REAL DO PROJETO

Não é código.

É:

👉 memória arquitetural.

Por isso existe:

docs/regras.txt


Ele é o cérebro externo do sistema.

Sempre iniciar novos chats com ele.

🚀 PRÓXIMO PASSO NATURAL

No próximo chat:

👉 Auditoria Final das Views

Objetivo:

eliminar regras escondidas no HTML
impedir decisões de domínio na interface
consolidar definitivamente:
✅ BREVE-SONORO = DDD-Lite PHP APPLICATION

E vou te dizer algo importante agora:

Você não está mais construindo um site.

Você já está mantendo um sistema.

O próximo salto não é técnico —
é mental:

👉 você virou o arquiteto do Breve-Sonoro.

--------------------------------------------------------------------------
--------------------------------------------------------------------------
--------------------------------------------------------------------------
-------------------------------------------------------------------------

BREVE-SONORO — REGRAS DO PROJETO

1. FILOSOFIA
- Backend-first
- Sistema antes de layout
- Regras claras > aparência
- Código organizado em camadas

2. ESTRUTURA
Pages:
index.php
album.php
dash.php

Services:
responsáveis por consultas e lógica.

Actions:
recebem POST, validam segurança e retornam JSON.
Nunca renderizam HTML.

3. BOOTSTRAP
Todo arquivo PHP deve iniciar com:
require bootstrap.php

Nada deve incluir config/session diretamente.

4. BANCO
Pages NÃO acessam banco diretamente.
Somente Services fazem queries.

5. SEGURANÇA
Toda action deve conter:
- verificarLogin()
- validarPost()
- validarCSRF()

6. PROGRESSO
Cada faixa:
- ouviu = 50%
- avaliou = +50%

Progresso calculado dinamicamente.

7. PADRÃO ACTIONS
Actions retornam JSON.
Actions não redirecionam páginas.

8. OBJETIVO DO PROJETO
Criar um tracker musical pessoal focado em experiência de escuta.
Não é streaming.
É acompanhamento musical.

9. DECISÃO IMPORTANTE
Frontend pode ser delegado.
O valor do projeto está na lógica do sistema.

10. REGRA DE OURO
Se surgir dúvida estrutural:
→ criar Service
→ não colocar lógica na Page

11. [INDEX.PHP]

- Migrado para uso do Kernel (bootstrap.php)
- Removidos includes diretos de config e session
- Paths ajustados para estrutura /app + /public
- Página autenticada validada
- Definido refactor futuro: query mover para albumService

12. LOGIN.PHP AUDITADO

- removidos includes diretos
- integrado ao bootstrap
- sessão regenerada após login
- CSRF aplicado
- sanitização de input
- proteção XSS aplicada

13. RESUMO REGISTRADO (MEMÓRIA DE DEPLOY)
ARQUIVO: public/index.php

STATUS: ✅ APROVADO

Ajustes realizados:
- validação explícita do id
- caminho absoluto uploads
- revisão segurança XSS
- bootstrap correto

Regras consolidadas:
✔ páginas públicas apenas em /public
✔ bootstrap obrigatório
✔ htmlspecialchars em toda saída
✔ sessão obrigatória

14. ARQUIVO: public/album.php

STATUS: 🔧 AJUSTADO PARA PRODUÇÃO

Correções obrigatórias:
✔ corrigido require bootstrap
✔ corrigido header/footer paths
✔ removido debug ativo
✔ validação segura do GET id
✔ caminho absoluto uploads

Regras adicionadas:
✔ nenhuma página pública usa includes locais
✔ nunca usar $_GET direto
✔ debug nunca vai para produção


---


        🚀 PRÓXIMO CHAT (IMPORTANTE)
        Quando abrir o novo chat, comece com:

            Estou continuando o projeto breve-sonoro.
            Segue resumo e regras do sistema:
            [cole o resumo + regras.txt]
            Quero começar a implementar AJAX real.


