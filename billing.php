<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();
ensureLearningTables();

$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$plans = billingPlans();
$subscription = currentUserSubscription($userId);
$assistantUsage = aiUsageSummary($userId, 'assistant_message');
$teacherUsage = aiUsageSummary($userId, 'teacher_question');
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$checkoutState = (string)($_GET['checkout'] ?? '');
$flash = consumeFlash();
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Planos - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Planos Free e Plus do LexStudy.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-billing">
    <div class="app-shell">
        <aside class="sidebar" aria-label="Dock principal">
            <a class="brand" href="index.php" aria-label="LexStudy">
                <span class="brand-mark"><img src="assets/lexstudy-mark.svg" alt=""></span>
                <span>
                    <strong>LexStudy</strong>
                    <small>Direito aplicado</small>
                </span>
            </a>

            <nav class="nav-list">
                <a href="index.php" data-short="P">Painel</a>
                <a href="sala.php" data-short="S">Sala</a>
                <a href="foco.php" data-short="FO">Foco</a>
                <a href="assistant.php" data-short="AI">Assistente</a>
                <a href="relatorio.php" data-short="R">Relatório</a>
                <a href="ferramentas.php" data-short="FX">Ferramentas</a>
            </nav>

            <div class="profile-panel">
                <span class="eyebrow">Plano atual</span>
                <strong><?= e($subscription['is_plus'] ? 'Plus' : 'Free') ?></strong>
                <div class="meter"><span style="width: <?= e($progress['percent']) ?>%"></span></div>
                <small><?= e($initialState['xp']) ?> XP</small>
            </div>
        </aside>

        <main class="workspace">
            <header class="topbar billing-hero">
                <div>
                    <span class="eyebrow">Planos e mensalidade</span>
                    <h1>Monetização simples, sem mexer em cartões.</h1>
                    <p>O plano Free serve para começar. O Plus desbloqueia mais aulas e aumenta muito o limite diário do Professor IA.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="sala.php">Ver aulas</a>
                    <a class="ghost-btn" href="assistant.php">Assistente</a>
                    <a class="primary-btn" href="index.php">Painel</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <?php if ($checkoutState === 'success'): ?>
                <div class="notice success">Pagamento recebido pelo Stripe. A subscrição fica ativa quando o webhook confirmar o evento.</div>
            <?php elseif ($checkoutState === 'cancelled'): ?>
                <div class="notice warning">Checkout cancelado. Não foi feita cobrança.</div>
            <?php endif; ?>

            <?php if (!stripeBillingIsConfigured()): ?>
                <div class="notice warning">
                    Stripe ainda não está configurado. Define `STRIPE_SECRET_KEY`, `STRIPE_PRICE_PLUS_MONTHLY` e `STRIPE_WEBHOOK_SECRET` no Apache/XAMPP antes de cobrar dinheiro real.
                </div>
            <?php endif; ?>

            <section class="billing-status">
                <article>
                    <span class="eyebrow">Plano atual</span>
                    <strong><?= e($subscription['is_plus'] ? 'Plus' : 'Free') ?></strong>
                    <small><?= e($subscription['status']) ?></small>
                </article>
                <article>
                    <span class="eyebrow">Assistente hoje</span>
                    <strong><?= e($assistantUsage['used']) ?> / <?= e($assistantUsage['limit']) ?></strong>
                    <div class="meter"><span style="width: <?= e($assistantUsage['percent']) ?>%"></span></div>
                </article>
                <article>
                    <span class="eyebrow">Professor IA hoje</span>
                    <strong><?= e($teacherUsage['used']) ?> / <?= e($teacherUsage['limit']) ?></strong>
                    <div class="meter"><span style="width: <?= e($teacherUsage['percent']) ?>%"></span></div>
                </article>
            </section>

            <section class="pricing-grid">
                <?php foreach ($plans as $planKey => $plan): ?>
                    <article class="pricing-card <?= $planKey === 'plus' ? 'is-featured' : '' ?>">
                        <span class="eyebrow"><?= e($plan['name']) ?></span>
                        <h2><?= e($plan['price']) ?></h2>
                        <p><?= e($plan['period']) ?></p>
                        <ul>
                            <?php foreach ($plan['features'] as $feature): ?>
                                <li><?= e($feature) ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if ($planKey === 'free'): ?>
                            <a class="secondary-btn" href="sala.php">Começar grátis</a>
                        <?php elseif (!$currentUser): ?>
                            <a class="primary-btn" href="index.php">Criar conta primeiro</a>
                        <?php elseif ($subscription['is_plus']): ?>
                            <button class="primary-btn" type="button" disabled>Plus ativo</button>
                        <?php elseif (stripeBillingIsConfigured()): ?>
                            <form method="post" action="billing_checkout.php">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <button class="primary-btn" type="submit">Subscrever Plus</button>
                            </form>
                        <?php else: ?>
                            <button class="primary-btn" type="button" disabled>Configurar Stripe</button>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="billing-notes">
                <article>
                    <span class="eyebrow">Recomendação direta</span>
                    <h2>Não compliques os planos no início.</h2>
                    <p>Um plano pago chega. Cobra por valor claro: mais aulas, mais mensagens e uso mais intensivo da IA. Evita vender “ilimitado” porque IA tem custo real.</p>
                </article>
                <article>
                    <span class="eyebrow">Segurança</span>
                    <h2>O pagamento deve ficar fora da app.</h2>
                    <p>O checkout é hospedado pelo Stripe. A tua aplicação só guarda o estado da subscrição e os limites de utilização.</p>
                </article>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
