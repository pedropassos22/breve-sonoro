📐 REGRAS OFICIAIS DO SISTEMA

    Este documento define as leis arquiteturais permanentes do sistema.
    Não são sugestões. São regras estruturais.

---

🧱 1. Estrutura do Sistema
    
    Regra 1 — Sistema, não Projeto
        O repositório representa um sistema em evolução contínua, não um projeto temporário.

    Regra 2 — Camadas de Deploy
        /public   → interface web (único acesso HTTP)
        /app      → backend da aplicação
        /storage  → dados internos
    
    Regra 3 — Web Root
        Apenas /public é acessível pela web.
        Nenhum PHP executável existe fora dele.
        Todo código interno permanece protegido em /app.

    Regra 4 — Root Limpo
        A raiz do projeto não contém arquivos PHP executáveis.
        Regra 5 — Uploads Públicos
        Arquivos acessíveis ao usuário ficam exclusivamente em:
            /public/uploads

    Regra 6 — Organização de Pastas
        app/
        ├── actions/
        ├── services/
        ├── includes/
        └── docs/
        actions/

            Executa somente:

            recebe requisição
            valida dados
            chama Service

            Nunca contém regra de negócio.

        services/
            Responsável por:

            lógica do sistema
            regras de negócio
            acesso ao banco
            decisões de domínio

        includes/

        Arquivos de suporte:

            bootstrap
            helpers
            configurações
            utilidades globais

---

⚙️ 2. Bootstrap e Inicialização
    
    Regra 7 — Entrypoint Único

        Todo arquivo público deve iniciar obrigatoriamente com:
        require bootstrap.php;
        Nenhuma lógica roda antes do bootstrap.

    Regra 8 — Bootstrap Central

        O bootstrap controla:

        ambiente (.env)
        encoding
        timezone
        erros
        logs
        sessão
        includes globais

    Regra 9 — Paths Absolutos

        O bootstrap define constantes globais:

        APP_PATH
        PUBLIC_PATH
        STORAGE_PATH

        Nunca usar caminhos relativos frágeis.

    Regra 10 — EntryPath Seguro

        Arquivos públicos carregam bootstrap via:
        require dirname(__DIR__, N) . '/app/includes/bootstrap.php';

    Regra 11 — Includes Seguros

        Pages nunca incluem arquivos por caminhos relativos instáveis.
        Sempre usar:
        __DIR__

---

🧠 3. Arquitetura de Camadas
    Regra 12 — Responsabilidade das Camadas
        
        Pages
        recebem request
        chamam services
        exibem dados
        Actions
        recebem eventos
        validam requisição
        chamam services
        Services
        executam regras
        executam SQL
        garantem consistência

    Regra 13 — Services Obrigatórios

        Regras de negócio vivem exclusivamente em:
        /app/services
        Pages e Actions não escrevem SQL quando existir Service equivalente.

    Regra 14 — Padrão de Action

        Toda Action deve:

        carregar bootstrap
        executar validarPost()
        obter usuário via obterUsuarioId()

    Regra 15 — Consistência de Domínio

        Quando um estado é alterado, o Service deve atualizar automaticamente:

        progresso
        estatísticas
        dashboards
        dependências relacionadas

    Regra 16 — Sem AJAX (Fase Atual)

        O sistema não utiliza AJAX por enquanto.
        Fluxo atual:
        Request → Action → Service → Redirect

---

🔐 4. Segurança
    
    Regra 17 — Configuração Segura
        config.php nunca possui credenciais.
        Variáveis sensíveis vivem no .env.
        Deploy altera ambiente, nunca o código.

    Regra 18 — Sessão Segura

        Obrigatório:

        cookies seguros
        SameSite = Strict
        proteção CSRF
        regenerar sessão após login

Regra 19 — Public Root Seguro

    Nada interno pode ser acessado diretamente por URL.

---

🗄️ 5. Banco de Dados
    
    Regra 20 — Schema Versionado

        O banco deve possuir:
        app/docs/schema.sql
        O sistema deve poder ser reconstruído do zero apenas com o repositório.

---

🎵 6. Domínio Musical

    Regra 21 — Progresso do Sistema

        O progresso é baseado em:
        reproduções válidas

    Regra 22 — Dashboard

        Dashboards são sempre:
        por usuário
        Nunca globais por padrão.

    Regra 23 — Links de Streaming

        Plataformas de streaming são entidades independentes.

        Regras:

        álbuns possuem zero ou múltiplos links
        views não conhecem plataformas fixas
        novas plataformas funcionam sem alteração estrutural

---

🧠 7. Sistema de Interações (Avaliações)
    
    Conceito Central
    avaliacoes = Interactions

    Não representa apenas notas.

    Regra 24 — Independência das Ações

        Ações independentes:

        ⭐ avaliação
        ❤️ favorito
        ▶ reprodução

        Nenhuma depende da outra.

    Regra 25 — Criação do Registro

        Criar registro quando existir:

        nota definida
        OU
        favorito definido

    Regra 26 — Remoção do Registro

        Remover somente quando:

        nota === null
        AND
        favorita === null

    Regra 27 — Updates Parciais

        Nunca sobrescrever campos ausentes.

        Se não veio no POST → não altera.

    Regra 28 — Reprodução Automática

        Reprodução automática ocorre apenas quando:

        nota > 0

        Favoritar não gera reprodução.

    Regra 29 — Progresso de Álbum

        Conta apenas:

        faixa ouvida
        faixa avaliada

        Favorito isolado não gera progresso.

    Regra 30 — Anti-Regras

        Nunca:

        exigir avaliação para favoritar
        exigir reprodução para favoritar
        sobrescrever dados não enviados
        manter registros vazios

---

📚 8. Documentação e Continuidade

    Regra 31 — Memória Arquitetural

        Chats utilizam apenas contexto reduzido.

        A memória oficial vive em:

        /app/docs

    Regra 32 — Documentação Permanente

        A arquitetura nunca depende do histórico de chat para existir.

        O repositório é a fonte da verdade.

---