<div>
    <x-slot:header>Guia do usuário</x-slot:header>

    <div class="max-w-3xl space-y-8">
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <h2 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">O que é a JMF Customer Intelligence</h2>
            <p class="text-sm text-slate-300 leading-relaxed">
                É a plataforma central de inteligência de clientes da JMF System. Qualquer sistema da empresa (Site Pessoal, Clube do Salão, Feira Esquerda Livre, e futuros projetos) envia eventos de comportamento
                para cá através de um SDK Laravel. A plataforma organiza esses eventos em visitantes, sessões e contatos, calcula indicadores, funis, lead score e recomendações — sem que cada sistema precise
                reimplementar essa lógica.
            </p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <h2 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Conceitos principais</h2>
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="font-semibold text-slate-200">Tenant</dt>
                    <dd class="text-slate-400">Uma empresa ou cliente da JMF System. Tem uma ou mais Aplicações.</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-200">Application</dt>
                    <dd class="text-slate-400">Um produto/projeto específico (ex.: Site Pessoal). É por Application que se gera o token usado pelo SDK.</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-200">Evento</dt>
                    <dd class="text-slate-400">Uma ação registrada pelo SDK (ex.: <code>article.viewed</code>, <code>appointment.completed</code>). É a matéria-prima de tudo na plataforma.</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-200">Visitor</dt>
                    <dd class="text-slate-400">Um visitante anônimo de uma Application, identificado por um <code>visitor_id</code> (cookie gerenciado pelo SDK).</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-200">Contact</dt>
                    <dd class="text-slate-400">Uma pessoa identificada (nome/e-mail), única por Tenant — ou seja, a mesma pessoa é reconhecida entre diferentes Applications do mesmo Tenant.</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <h2 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Como conectar uma aplicação nova</h2>
            <ol class="list-decimal list-inside space-y-2 text-sm text-slate-300">
                <li>Crie (ou reaproveite) um <strong>Tenant</strong> em "Tenants".</li>
                <li>Crie uma <strong>Application</strong> vinculada a esse Tenant.</li>
                <li>Em "Tokens" da Application, gere um token — copie o valor completo, ele só aparece uma vez.</li>
                <li>Instale o pacote <code>jmf-system/customer-intelligence-sdk</code> na aplicação cliente e configure <code>JMF_CI_BASE_URL</code>/<code>JMF_CI_TOKEN</code> no <code>.env</code> dela.</li>
                <li>A aplicação cliente já pode chamar <code>track()</code>, <code>identify()</code> e <code>conversion()</code> do SDK — visitante e sessão são resolvidos automaticamente.</li>
            </ol>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <h2 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Analytics</h2>
            <p class="text-sm text-slate-300 leading-relaxed">
                Escolha uma aplicação e um período. Você verá totais (eventos, visitantes, sessões, conversões), a tendência diária, o que mais foi acessado, de onde veio o tráfego (UTMs) e o funil de conversão
                — quantos visitantes avançaram de uma etapa para a próxima.
            </p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <h2 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Lead Score e recomendações</h2>
            <p class="text-sm text-slate-300 leading-relaxed">
                Todo dia a plataforma recalcula automaticamente o <strong>Lead Score</strong> de cada contato (quanto mais engajado, maior a nota) e a <strong>afinidade</strong> entre produtos/conteúdos, com base
                no que os visitantes costumam ver juntos. Essas recomendações ficam disponíveis para as aplicações clientes consumirem via API — não é necessário fazer nada manualmente aqui, só acompanhar
                os números em "Contatos".
            </p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <h2 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Consentimentos e LGPD</h2>
            <p class="text-sm text-slate-300 leading-relaxed">
                Quando uma aplicação cliente identifica um contato com consentimentos (ex.: aceite de marketing), isso fica registrado e visível na tela de detalhe do contato. Nenhum dado sensível
                (senhas, cartão de crédito) deve trafegar nos eventos — isso é bloqueado por regra desde o início do projeto.
            </p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <h2 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Perfis de acesso</h2>
            <p class="text-sm text-slate-300 leading-relaxed">
                <strong>Super Admin</strong> tem acesso completo a todas as telas e ações. <strong>Administrador</strong> tem acesso mais restrito (normalmente só visualização de Tenants/Aplicações/Contatos),
                configurável em "Usuários". Use perfis diferentes para dar acesso aos seus sócios sem abrir mão de controle sobre ações sensíveis (exclusões, tokens etc.).
            </p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <h2 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Precisa de mais ajuda?</h2>
            <p class="text-sm text-slate-300 leading-relaxed">
                Toda tela do painel tem um botão <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-slate-700 text-xs align-middle">?</span> no topo, com explicações específicas
                daquela tela.
            </p>
        </div>
    </div>
</div>
