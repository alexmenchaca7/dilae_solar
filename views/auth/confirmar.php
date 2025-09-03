<main class="auth">
    <h1>Confirma tu Cuenta</h1>
    <?php require_once __DIR__ . '/../templates/alertas.php'; ?>

    <?php if(isset($alertas['exito'])): ?>
        <div class="acciones--centrar">
            <a href="/login" class="acciones__enlace">Iniciar Sesión</a>
        </div>
    <?php else: ?>
        <div class="acciones--centrar">
            <a href="/olvide" class="acciones__enlace">¿Olvidaste tu Password?</a>
        </div>
    <?php endif; ?>
</main>