<aside class="dashboard__sidebar">
    <nav class="dashboard__menu">
        <a href="/admin/dashboard" class="dashboard__enlace <?php echo pagina_actual('/dashboard') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-house dashboard__icono"></i>

            <span class="dashboard__menu-texto">
                Inicio
            </span>  
        </a>

        <a href="/admin/blogs" class="dashboard__enlace <?php echo pagina_actual('/blogs') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-clipboard dashboard__icono"></i>

            <span class="dashboard__menu-texto">
                Blogs
            </span>  
        </a>

        <a href="/admin/suscripciones" class="dashboard__enlace <?php echo pagina_actual('/suscripciones') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-envelope-open-text dashboard__icono"></i>

            <span class="dashboard__menu-texto">
                Suscripciones
            </span>  
        </a>

        <a href="/admin/usuarios" class="dashboard__enlace <?php echo pagina_actual('/usuarios') ? 'dashboard__enlace--actual' : ''; ?>">
            <i class="fa-solid fa-user dashboard__icono"></i>

            <span class="dashboard__menu-texto">
                Usuarios
            </span>  
        </a>

        <form method="POST" action="/logout" class="dashboard__enlace dashboard__enlace--logout">
            <button type="submit" class="dashboard__menu-texto">
                <i class="fa-solid fa-right-from-bracket dashboard__icono"></i>
                <span class="dashboard__menu-texto">
                    Cerrar Sesión
                </span>
            </button>
        </form>
    </nav>
</aside>