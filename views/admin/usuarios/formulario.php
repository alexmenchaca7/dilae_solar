<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Información del Usuario</legend>

    <!-- Nombre del usuario -->
    <div class="formulario__campo">
        <label for="nombre" class="formulario__label">Nombre</label>
        <input 
            type="text"
            class="formulario__input"
            id="nombre"
            name="nombre"
            placeholder="Nombre Usuario"
            value="<?php echo $usuario->nombre ?? ''; ?>"
        >
    </div>

    <!-- Apellido del usuario -->
    <div class="formulario__campo">
        <label for="apellido" class="formulario__label">Apellido</label>
        <input 
            type="text"
            class="formulario__input"
            id="apellido"
            name="apellido"
            placeholder="Apellido Usuario"
            value="<?php echo $usuario->apellido ?? ''; ?>"
        >
    </div>

    <!-- Email del usuario -->
    <div class="formulario__campo">
        <label for="email" class="formulario__label">Email</label>
        <input 
            type="email"
            class="formulario__input"
            id="email"
            name="email"
            placeholder="Email Usuario"
            value="<?php echo $usuario->email ?? ''; ?>"
        >
    </div>
</fieldset>