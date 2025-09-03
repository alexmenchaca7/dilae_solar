#!/bin/bash

# --- Script de Despliegue para Dilae Solar en Hostinger ---

# Terminar el script inmediatamente si un comando falla
set -e

echo "✅ Iniciando despliegue..."

# Navega al directorio raíz de tu proyecto en el servidor
# Usamos una ruta absoluta para mayor seguridad.
PROJECT_DIR="/home/u387411157/domains/dilaesolar.com/public_html"
cd "$PROJECT_DIR" || { echo "❌ Error: No se pudo acceder al directorio del proyecto."; exit 1; }

# Limpia cualquier cambio local no guardado para evitar conflictos.
echo "🔄 Limpiando repositorio local..."
git reset --hard HEAD
git fetch origin master
git checkout master
git pull origin master

echo "✅ Repositorio actualizado."

# Instala/actualiza las dependencias de PHP.
echo "📦 Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

echo "✅ Dependencias de Composer instaladas."

# Carga NVM. Es crucial definir NVM_DIR explícitamente.
echo "📦 Configurando entorno de Node.js..."
export NVM_DIR="/home/u387411157/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Verificar que node y npm están disponibles
if ! command -v npm &> /dev/null
then
    echo "❌ Error: npm no se encontró. Revisa la ruta de NVM."
    exit 1
fi

# Instala/actualiza las dependencias de Node.js.
echo "📦 Instalando dependencias de NPM..."
npm install

echo "✅ Dependencias de NPM instaladas."

# Ejecuta el script de compilación de producción.
echo "🚀 Compilando assets..."
npm run build

echo "✅ Assets compilados con 'npm run build'."
echo "🎉 ¡Despliegue finalizado con éxito!"