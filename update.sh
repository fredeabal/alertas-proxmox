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

# 3. Entrar a la carpeta e iniciar actualización
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

# 3.1. Actualizar URL/Dominio si ha cambiado
if [ -f ".env" ]; then
    CURRENT_URL=$(grep -E "^app\.baseURL" .env | head -n 1 | cut -d'=' -f2 | tr -d " '" | tr -d '"')
    CURRENT_URL=${CURRENT_URL:-"http://localhost/"}
    
    echo -e "\n${BLUE}======================================================================${NC}"
    echo -e "🔗  CONFIGURACIÓN DE URL / DOMINIO"
    echo -e "${BLUE}======================================================================${NC}"
    echo -e "La URL actual configurada es: ${YELLOW}${CURRENT_URL}${NC}"
    echo -e "Si has cambiado el dominio o configurado SSL (https), ingresa el nuevo valor."
    echo -e "IMPORTANTE: Debe empezar con http:// o https:// y terminar con /"
    echo -e "Ejemplo: ${BLUE}https://alertas.mi-dominio.com/${NC} o ${BLUE}http://192.168.0.116/${NC}"
    echo -e "----------------------------------------------------------------------"
    
    read -p "👉 Nueva URL [Presiona Enter para mantener la actual]: " INPUT_DOMAIN
    
    if [ -n "$INPUT_DOMAIN" ]; then
        if echo "$INPUT_DOMAIN" | grep -qE '^https?://'; then
            PROTOCOL=$(echo "$INPUT_DOMAIN" | grep -oE '^https?')
            DOMAIN=$(echo "$INPUT_DOMAIN" | sed 's|^https\?://||' | sed 's|/$||')
        else
            PROTOCOL="http"
            DOMAIN=$(echo "$INPUT_DOMAIN" | sed 's|/$||')
        fi
        BASE_URL="${PROTOCOL}://${DOMAIN}/"
        
        sed -i "s|app\.baseURL\s*=.*|app.baseURL = '${BASE_URL}'|g" .env
        echo -e "${GREEN}✅ URL de la aplicación actualizada a: ${BASE_URL}${NC}"
    else
        echo -e "${GREEN}✅ Se mantiene la URL actual.${NC}"
    fi
fi


echo -e "${YELLOW}⏳ [4/5] Restaurando permisos y limpiando caché de la aplicación...${NC}"
# Limpiar caché interno de CodeIgniter 4
php spark cache:clear 2>/dev/null || true

# Asegurar que la carpeta uploads exista antes de darle permisos
mkdir -p "$INSTALL_DIR/public/uploads"

chown -R www-data:www-data "$INSTALL_DIR"
chmod -R 775 "$INSTALL_DIR/writable"
chmod -R 775 "$INSTALL_DIR/public/uploads"

# Asegurar permisos del comando ping para usuarios no root
setcap cap_net_raw+ep $(which ping) 2>/dev/null || chmod +s $(which ping) 2>/dev/null || true

# Asegurar que el cron de monitoreo esté configurado para el usuario www-data
PHP_BIN=$(which php || echo "/usr/bin/php")
CRON_JOB="*/5 * * * * cd ${INSTALL_DIR} && ${PHP_BIN} spark monitor:ping > /dev/null 2>&1"
(crontab -u www-data -l 2>/dev/null | grep -F "spark monitor:ping") || (
    (crontab -u www-data -l 2>/dev/null; echo "$CRON_JOB") | crontab -u www-data -
)

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
