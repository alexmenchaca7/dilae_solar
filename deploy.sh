#!/bin/bash

# --- Script de Despliegue para Dilae Solar en Hostinger ---

echo "✅ Iniciando despliegue..."

# Navega al directorio raíz de tu proyecto en el servidor
# IMPORTANTE: Asegúrate de que esta ruta sea la correcta para tu cuenta.
cd /home/u387411157/domains/dilaesolar.com/public_html || { echo "❌ Error: No se pudo acceder al directorio del proyecto."; exit 1; }

# Limpia cualquier cambio local no guardado para evitar conflictos.
git reset --hard HEAD

# Descarga los últimos cambios desde la rama 'master' (o 'main' si usas esa).
git pull origin master

echo "✅ Repositorio actualizado."

# Instala/actualiza las dependencias de PHP sin los paquetes de desarrollo.
composer install --no-dev --optimize-autoloader

echo "✅ Dependencias de Composer instaladas."

# Carga NVM para que los comandos de Node y NPM estén disponibles.
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Instala/actualiza las dependencias de Node.js.
npm install

echo "✅ Dependencias de NPM instaladas."

# Ejecuta el script de compilación de producción de tu package.json.
npm run build

echo "✅ Assets compilados con 'npm run build'."
echo "🚀 ¡Despliegue finalizado con éxito!"