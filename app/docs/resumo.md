Este vai ser o resumo-mãe do breve-sonoro — feito exatamente para:

✅ iniciar um novo chat sem perda de contexto
✅ manter coerência arquitetural
✅ preservar decisões técnicas (isso é o mais importante)
✅ transformar você de usuário do projeto → arquiteto do sistema

---
🧠 📦 BREVE-SONORO — RESUMO COMPLETO ATUALIZADO
---
🎯 VISÃO DO PROJETO

breve-sonoro é um sistema web estilo tracker musical pessoal.

O foco não é streaming.
O foco é:

👉 acompanhar a experiência de ouvir música.

O sistema permite:

registrar escutas
avaliar faixas
marcar favoritas
acompanhar progresso de álbuns
manter uma dashboard pessoal

Inspirado conceitualmente em:

Letterboxd (filmes)
Goodreads (livros)
Last.fm (escuta)
backlog tracker
---
🧠 FILOSOFIA DO PROJETO (DECISÃO IMPORTANTE)

O projeto segue:

✅ backend-first
✅ lógica antes de visual
✅ regras claras de sistema
✅ interface pode evoluir depois

Você não é designer → e isso foi assumido como decisão arquitetural.
---
🏗️ ARQUITETURA ATUAL

breve-sonoro/
│
├── index.php
├── album.php
├── dash.php
├── atualizar_progresso.php
├── buscar_banda_mb.php
├── buscar_album_mb.php
├── desmarcar_faixa.php
├── editar_banda.php
├── gerar_senha.php
├── listar_bandas.php
├── login.php
├── logout.php
├── nova_banda.php
├── nova_faixa.php
├── nova_genero.php
├── novo_album.php
├── salvar_faixa_ajax.php
├── teste_faixas.php
├── teste_importar_faixas.php
├── vincular_banda_album.php
│
├── actions/
│   ├── adicionar_dash.php
│   ├── remover_dash.php
│   ├── registrar_reproducao.php
│   ├── remover_reproducao.php
│   ├── toggle_favorito.php
│   └── salvar_avaliacao.php
│
├── admin/
│   ├── admin.php
│   ├── albuns_sem_faixa.php
│   ├── excluir_album.php
│   └── excluir_faixa.php
│
├── includes/
│   ├── bootstrap.php
│   ├── config.php
│   ├── session.php
│   ├── action_helper.php
│   ├── header.php
│   ├── musicbrainz.php
│   └── footer.php
│
├── services/
│   └── albumService.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── imagens
│
└── docs/
---
🔥 DECISÃO ARQUITETURAL MAIS IMPORTANTE
✅ bootstrap.php virou o coração do sistema

Todo endpoint agora começa com:
    
    require "../includes/bootstrap.php";

O bootstrap centraliza:

conexão PDO
sessão
helpers globais
base_url
segurança
padronização

👉 Isso transforma scripts soltos em aplicação.
---
🧩 CAMADAS DO SISTEMA
---
1️⃣ Pages (interface)

Arquivos:

index.php
album.php
dash.php

Responsabilidade:

✔ renderizar
✔ chamar services
✔ mostrar dados

❌ NÃO acessam banco diretamente (regra nova).
---
2️⃣ Services (regra de negócio)

    albumService.php

Responsável por:

queries
cálculos
agregações
lógica do sistema

👉 Aqui mora a inteligência.
---
3️⃣ Actions (controladores)

Pasta criada:
    /actions

Responsabilidade:
    ✔ receber POST
    ✔ validar segurança
    ✔ executar ação
    ✔ retornar JSON

NUNCA renderizam HTML.

---
Actions atuais:

registrar_reproducao.php
    registra escuta
    retorna JSON

remover_reproducao.php
    remove escuta
    retorna JSON

adicionar_dash.php
    adiciona álbum à dashboard

remover_dash.php
    remove da dashboard

salvar_avaliacao.php ⭐ (arquivo mais avançado)
    
    Responsável por:

    salvar nota
    marcar favorita
    garantir reprodução mínima
    recalcular progresso do álbum
    retornar estado atualizado via JSON
👉 já preparado para AJAX.

---
🗄️ MODELO DE DADOS (CONCEITUAL)

albuns
    id, Primária, int(11)
    banda_id, Índice, int(11)
    titulo, Índice, varchar(200), utf8mb4_unicode_ci
    ano, int(11)
    capa, varchar(255), utf8mb4_unicode_ci
    criado_por, Índice, int(11)
    mbid, varchar(50), utf8mb4_unicode_ci

bandas
    id Primária	int(11)
    nome	varchar(255)	utf8mb4_unicode_ci
    slug	varchar(255)	utf8mb4_unicode_ci	
    imagem	varchar(255)	utf8mb4_unicode_ci
    criado_em	timestamp	
    ano_formacao	year(4)	
    cidade	varchar(150)	utf8mb4_unicode_ci
    nome_normalizado Índice	varchar(255)	utf8mb4_unicode_ci	


faixas
    id Primária	int(11)
    album_id Índice	int(11)
    disco	int(11)
    numero	int(11)
    nome	varchar(200)	utf8mb4_unicode_ci	
    duracao	varchar(10)	utf8mb4_unicode_ci	
    total_ouvidas	int(11)

reproducoes
    id Primária	int(11)
    usuario_id Índice	int(11)	
    faixa_id Índice	int(11)
    data_hora	datetime	

avaliacoes
    id Primária	int(11)
    usuario_id Índice	int(11)
    faixa_id Índice	int(11)
    nota	decimal(2,1)	
    favorita	tinyint(1)

usuario_dash
    id Primária	int(11)
    usuario_id Índice	int(11)	
    album_id Índice	int(11)	

banda_genero
    banda_id Primária	int(11)	
    genero_id PrimáriaÍndice	int(11)	

generos
    id Primária	int(11)
    nome Índice	varchar(100)	utf8mb4_unicode_ci
    slug	varchar(150)	utf8mb4_unicode_ci	
    ativo	tinyint(1)

progresso_album
    id Primária	int(11)	
    usuario_id Índice	int(11)	
    album_id Índice	int(11)
    progresso	int(11)	

usuarios
    id Primária	int(11)
    nome	varchar(100)
    email Índice	varchar(150)
    senha	varchar(255)
    tipo	enum('admin', 'usuario')

---
🧠 REGRA CENTRAL DO PRODUTO

COMO FUNCIONA O PROGRESSO
Cada faixa contribui:
    Ação        Peso
    Ouviu       50%
    Avaliou     +50%

Resultado:
    Faixa completa = ouviu + avaliou
O progresso do álbum:
    (contribuição total / nº de faixas) * 100

👉 Isso é design de produto, não só código.

---
🔐 PADRÃO DE SEGURANÇA ADOTADO

Toda action segue:
    verificarLogin();
    validarPost();
    validarCSRF();

Proteções:
    ✅ CSRF
    ✅ método HTTP correto
    ✅ sessão obrigatória
    ✅ prepared statements PDO

---
🎨 FRONTEND
Estado atual:

    grid responsivo
    cards de álbum
    barra de progresso
    tema claro (index)
    tema dark (dash)

Decisão:
👉 frontend não é prioridade agora.

---
🚀 ESTADO REAL DO PROJETO

Você já tem:
    ✅ sistema funcional
    ✅ arquitetura separada
    ✅ padrão profissional de actions
    ✅ regras de domínio claras
    ✅ backend pronto pra crescer
    ✅ base para AJAX

Isso já é nível júnior pleno de backend.

---
⚠️ LIMITAÇÃO ATUAL (A REAL)

Não é código.

É:

👉 memória arquitetural do projeto.

Por isso nasce o próximo passo:

---
📚 docs/regras.txt (OBRIGATÓRIO)

Esse arquivo vira o cérebro externo do projeto.

Sempre que abrir um novo chat, você cola ele.
---

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
---


        🚀 PRÓXIMO CHAT (IMPORTANTE)
        Quando abrir o novo chat, comece com:

            Estou continuando o projeto breve-sonoro.
            Segue resumo e regras do sistema:
            [cole o resumo + regras.txt]
            Quero começar a implementar AJAX real.
