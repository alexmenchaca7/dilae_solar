#!/bin/bash

# --- Script de Despliegue para Dilae Solar en Hostinger ---

# Terminar el script inmediatamente si un comando falla
set -e

echo "✅ Iniciando despliegue..."

# Define la ruta del proyecto para mayor seguridad.
PROJECT_DIR="/home/u387411157/domains/dilaesolar.com/public_html"
cd "$PROJECT_DIR" || { echo "❌ Error: No se pudo acceder al directorio del proyecto."; exit 1; }

# Limpia y actualiza el repositorio para que coincida con la rama master remota.
echo "🔄 Sincronizando repositorio con origin/master..."
git fetch origin
git reset --hard origin/master

echo "✅ Repositorio actualizado."

# Define la variable HOME para que la usen los siguientes comandos (especialmente NVM).
export HOME="/home/u387411157"

# Instala dependencias de PHP, pasando la variable HOME directamente al comando.
echo "📦 Instalando dependencias de Composer..."
HOME="/home/u387411157" composer install --no-dev --optimize-autoloader

echo "✅ Dependencias de Composer instaladas."

# Carga NVM. NVM_DIR ahora usará la variable HOME exportada arriba.
echo "📦 Configurando entorno de Node.js..."
export NVM_DIR="$HOME/.nvm"
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
echo "🚀 Compilando assets del servidor (CSS & JS)..."
npm run build:server

echo "✅ Assets compilados"
echo "🎉 ¡Despliegue finalizado con éxito!"