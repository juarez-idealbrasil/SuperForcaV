<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php $appBasePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#145c46">
    <title>ForçaV - Acesso</title>
    <link href="<?php echo html_escape($appBasePath . '/css/mobile.css'); ?>" rel="stylesheet">
</head>
<body class="mobile-page">
    <main class="auth-shell">
        <section class="auth-card" aria-labelledby="login-title">
            <div class="brand-mark" aria-hidden="true">FV</div>
            <p class="eyebrow">Força de vendas</p>
            <h1 id="login-title">Acesse sua conta</h1>
            <p class="muted">Entre com seu usuário do sistema.</p>

            <?php if ($error !== ''): ?>
                <div class="alert" role="alert"><?php echo html_escape($error); ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo html_escape($appBasePath . '/index.php/mobile/auth'); ?>" autocomplete="on">
                <label for="username">Usuário</label>
                <input id="username" name="username" type="text" maxlength="30"
                       autocomplete="username" autocapitalize="none" required>

                <label for="password">Senha</label>
                <input id="password" name="password" type="password" maxlength="15"
                       autocomplete="current-password" required>

                <button class="primary-button" type="submit">Entrar</button>
            </form>
        </section>
        <p class="page-note">Acesso ao ambiente de vendas</p>
    </main>
</body>
</html>
