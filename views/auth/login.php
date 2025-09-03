<main class="auth">
    <h1>Iniciar Sesión</h1>
    <?php require_once __DIR__ . '/../templates/alertas.php'; ?>

    <form method="POST" action="/login" class="formulario">
        <div class="formulario__campo">
            <label for="email" class="formulario__label">Email</label>
            <input 
                type="email"
                class="formulario__input"
                placeholder="Tu Email"
                id="email"
                name="email"
            >
        </div>
        <div class="formulario__campo">
            <label for="pass" class="formulario__label">Password</label>
            <input 
                type="password"
                class="formulario__input"
                placeholder="Tu Password"
                id="pass"
                name="pass"
            >
        </div>

        <input type="submit" class="formulario__submit" value="Iniciar Sesión">
    </form>

    <div class="acciones--centrar">
        <a href="/olvide" class="acciones__enlace">¿Olvidaste tu Password?</a>
    </div>
</main>