<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php $appBasePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#145c46">
    <title>ForçaV - Acesso</title>
    <link href="<?php echo html_escape($appBasePath . '/css/mobile.css?v=7'); ?>" rel="stylesheet">
    <link href="<?php echo html_escape($appBasePath . '/css/idealbrasil-footer.css?v=1'); ?>" rel="stylesheet">
</head>
<body class="mobile-page">
    <main class="auth-shell">
        <section class="auth-card" aria-labelledby="login-title">
            <img class="auth-logo"
                 src="<?php echo html_escape($appBasePath . '/css/logo-cliente.png'); ?>"
                 alt="Ideal Brasil Sistemas">
                 <br>
            <h1 class="auth-copy" id="login-title">Acesse sua conta</h1>


            <?php if ($error !== ''): ?>
                <div class="alert" role="alert"><?php echo html_escape($error); ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo html_escape($appBasePath . '/index.php/mobile/auth'); ?>" autocomplete="on">
                <label for="username">Usuário</label>
                <input id="username" name="username" type="text" maxlength="30"
                       autocomplete="username" autocapitalize="characters" required>

                <label for="password">Senha</label>
                <input id="password" name="password" type="password" maxlength="15"
                       autocomplete="current-password" required>

                <button class="primary-button" type="submit">Entrar</button>
            </form>
        </section>
        <?php $this->load->view('partials/idealbrasil_footer', array('footer_class' => 'page-note')); ?>
    </main>
    <script>
        (function () {
            var username = document.getElementById('username');
            var form = username.form;

            username.addEventListener('input', function () {
                username.value = username.value.toUpperCase();
            });
            form.addEventListener('submit', function () {
                username.value = username.value.toUpperCase();
            });
        }());
    </script>
</body>
</html>
