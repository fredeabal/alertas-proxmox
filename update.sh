#!/usr/bin/env bash
# =================================================================================
# PROXMOX ALERT - AUTOMATIC UPDATER SCRIPT
# =================================================================================
# Este script descarga e instala la última versión de Proxmox Alert sin perder datos.
# =================================================================================

# Si se ejecuta mediante curl/pipe, guardar en un archivo temporal y reconectar la terminal
if [ ! -t 0 ] && [ -z "$PROXMOXALERT_SELF_RUN" ]; then
    TMP_SCRIPT=$(mktemp /tmp/proxmoxalert_update.XXXXXX.sh)
    cat > "$TMP_SCRIPT"
    chmod +x "$TMP_SCRIPT"
    export PROXMOXALERT_SELF_RUN=1
    exec "$TMP_SCRIPT" "$@" < /dev/tty
fi

# Colores para salida de terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}======================================================================${NC}"
echo -e "${GREEN}          🚀 PROXMOX ALERT - ACTUALIZADOR AUTOMÁTICO 🚀          ${NC}"
echo -e "${BLUE}======================================================================${NC}"
echo ""

# 1. Verificar si es root
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}¡ERROR! Por favor, ejecuta este script como root (ej: sudo bash <(curl...))${NC}"
  exit 1
fi

# 2. Verificar si Proxmox Alert está instalado
INSTALL_DIR="/var/www/proxmox-alert"
if [ ! -d "$INSTALL_DIR" ]; then
  echo -e "${RED}¡ERROR! No se encontró una instalación de Proxmox Alert en ${INSTALL_DIR}.${NC}"
  echo -e "Si aún no lo has instalado, utiliza el instalador primero."
  exit 1
fi

cd "$INSTALL_DIR"

# 3. Preguntar si desea actualizar el dominio
CURRENT_URL=$(grep -oP "app\.baseURL\s*=\s*'\K[^']+" .env 2>/dev/null || echo "")
echo -e "${YELLOW}🌐 URL actual en .env:${NC} ${CURRENT_URL:-No configurada}"
read -p "❓ ¿Deseas actualizar la URL o dominio? [s/N]: " UPDATE_DOMAIN
UPDATE_DOMAIN=$(echo "$UPDATE_DOMAIN" | tr '[:lower:]' '[:upper:]')

if [[ "$UPDATE_DOMAIN" == "S" ]]; then
    read -p "👉 Ingresa la nueva IP o Dominio (con o sin http/https): " INPUT_DOMAIN
    INPUT_DOMAIN=$(echo "$INPUT_DOMAIN" | xargs)
    INPUT_DOMAIN="${INPUT_DOMAIN%/}"

    if [[ ! "$INPUT_DOMAIN" =~ ^https?:// ]]; then
        if [[ "$INPUT_DOMAIN" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            NEW_DOMAIN="http://${INPUT_DOMAIN}/"
        else
            echo -e "👉 Has ingresado un dominio sin protocolo (http/https)."
            read -p "❓ ¿Deseas configurar la aplicación bajo HTTPS (Recomendado)? [S/n]: " USE_HTTPS
            USE_HTTPS=$(echo "$USE_HTTPS" | tr '[:lower:]' '[:upper:]')
            if [[ "$USE_HTTPS" == "N" ]]; then
                NEW_DOMAIN="http://${INPUT_DOMAIN}/"
            else
                NEW_DOMAIN="https://${INPUT_DOMAIN}/"
            fi
        fi
    else
        NEW_DOMAIN="${INPUT_DOMAIN}/"
    fi

    sed -i "s|app.baseURL = .*|app.baseURL = '${NEW_DOMAIN}'|g" .env
    echo -e "${GREEN}✅ URL actualizada a: ${NEW_DOMAIN}${NC}"
fi

# 4. Entrar a la carpeta e iniciar actualización
echo -e "${YELLOW}⏳ [1/5] Descargando última versión desde GitHub...${NC}"

# Configurar Git para confiar en el directorio aunque pertenezca a www-data
git config --global --add safe.directory "$INSTALL_DIR"

# Guardar cambios locales sin commitear por seguridad
git stash > /dev/null 2>&1 || true

# Descargar la última versión y forzar sincronización exacta con GitHub
git fetch origin
git reset --hard origin/main

echo -e "${YELLOW}⏳ [2/5] Actualizando dependencias de PHP (Composer)...${NC}"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${YELLOW}⏳ [3/5] Aplicando migraciones de base de datos...${NC}"
php spark migrate

echo -e "${YELLOW}⏳ [4/5] Restaurando permisos y limpiando caché de la aplicación...${NC}"
# Limpiar caché interno de CodeIgniter 4
php spark cache:clear 2>/dev/null || true

# Asegurar que la carpeta uploads exista antes de darle permisos
mkdir -p "$INSTALL_DIR/public/uploads"

chown -R www-data:www-data "$INSTALL_DIR"
chmod -R 775 "$INSTALL_DIR/writable"
chmod -R 775 "$INSTALL_DIR/public/uploads"

# Restaurar el .env personalizado si git stash lo pisó
# (el git reset --hard no toca .env porque está en .gitignore, así que es seguro)

echo -e "${YELLOW}⏳ [5/5] Reiniciando servicios y limpiando caché (OPCache / Nginx)...${NC}"
# Buscar dinámicamente cualquier versión de PHP-FPM instalada
for service in $(systemctl list-unit-files --type=service 2>/dev/null | grep -oE 'php[0-9.]*-fpm\.service'); do
    systemctl restart "$service" 2>/dev/null || true
done
systemctl restart php-fpm 2>/dev/null || true
systemctl reload nginx 2>/dev/null || true

echo ""
echo -e "${GREEN}======================================================================${NC}"
echo -e "${GREEN}  ✅ ACTUALIZACIÓN COMPLETADA CON ÉXITO                             ${NC}"
echo -e "${GREEN}======================================================================${NC}"
echo -e "Tu servidor Proxmox Alert ya está en la última versión."
echo -e "Tus configuraciones, empresas y alertas están a salvo."
echo -e "${BLUE}======================================================================${NC}"

