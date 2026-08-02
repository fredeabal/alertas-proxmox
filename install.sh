#!/usr/bin/env bash
# ==============================================================================
# 🚀 PROXMOX ALERT - SCRIPT DE INSTALACIÓN AUTOMÁTICA
# ==============================================================================
# Uso recomendado:
#   curl -fsSL https://raw.githubusercontent.com/fredeabal/alertas-proxmox/main/install.sh -o /tmp/proxmox-alert-install.sh && bash /tmp/proxmox-alert-install.sh
# ==============================================================================

# Colores para la consola
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# 1. Verificar si el usuario es root
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}❌ Error: Este script debe ejecutarse como root (ej: sudo bash install.sh).${NC}"
  exit 1
fi

clear
echo -e "${BLUE}"
echo "======================================================================"
echo "      🚀 BIENVENIDO AL INSTALADOR AUTOMÁTICO DE PROXMOX ALERT        "
echo "======================================================================"
echo -e "${NC}"

# 2. Detectar IP del servidor como valor por defecto
SERVER_IP=$(hostname -I | awk '{print $1}')
read -p "👉 Ingresa la IP o Dominio del servidor (con o sin http/https) [Predeterminado: ${SERVER_IP}]: " INPUT_DOMAIN
INPUT_DOMAIN=${INPUT_DOMAIN:-$SERVER_IP}
INPUT_DOMAIN=$(echo "$INPUT_DOMAIN" | xargs)
INPUT_DOMAIN="${INPUT_DOMAIN%/}"

# Determinar protocolo automáticamente
if [[ "$INPUT_DOMAIN" =~ ^https?:// ]]; then
    # Ya viene con protocolo: respetar tal cual
    DOMAIN="${INPUT_DOMAIN}/"
elif [[ "$INPUT_DOMAIN" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    # Dirección IP pura → siempre http
    DOMAIN="http://${INPUT_DOMAIN}/"
else
    # Nombre de dominio sin protocolo → preguntar
    echo -e "👉 Has ingresado un dominio sin protocolo (http/https)."
    read -p "❓ ¿Deseas configurar la aplicación bajo HTTPS (Recomendado)? [S/n]: " USE_HTTPS
    USE_HTTPS=$(echo "$USE_HTTPS" | tr '[:lower:]' '[:upper:]')
    if [[ "$USE_HTTPS" == "N" ]]; then
        DOMAIN="http://${INPUT_DOMAIN}/"
    else
        DOMAIN="https://${INPUT_DOMAIN}/"
    fi
fi

# Extraer host limpio para Nginx (sin protocolo, sin barra)
DOMAIN_HOST=$(echo "$DOMAIN" | sed -e 's|^[^/]*//||' -e 's|/.*$||' -e 's|:.*$||')

echo -e "\n✅ Usando: ${GREEN}${DOMAIN}${NC} (servidor Nginx: ${DOMAIN_HOST})\n"

# 3. Actualizar paquetes e instalar dependencias del sistema
echo -e "${YELLOW}⏳ [1/6] Actualizando paquetes e instalando dependencias del sistema...${NC}"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get install -y nginx git unzip curl \
    php-fpm php-cli php-sqlite3 php-curl php-intl php-mbstring php-xml php-zip

# Instalar Composer si no está presente
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}⏳ Instalando Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# 4. Directorio de instalación
INSTALL_DIR="/var/www/proxmox-alert"

if [ ! -f "./spark" ]; then
    echo -e "${YELLOW}⏳ [2/6] Descargando el código de Proxmox Alert desde GitHub...${NC}"
    if [ -d "$INSTALL_DIR" ]; then
        cd /tmp
        rm -rf "$INSTALL_DIR"
    fi
    git clone https://github.com/fredeabal/alertas-proxmox.git "$INSTALL_DIR"
else
    echo -e "${YELLOW}⏳ [2/6] Preparando el directorio del proyecto en ${INSTALL_DIR}...${NC}"
    mkdir -p "$INSTALL_DIR"
    cp -r ./* "$INSTALL_DIR/"
    cp -r ./.env* "$INSTALL_DIR/" 2>/dev/null || true
fi

cd "$INSTALL_DIR"

if [ ! -f "composer.json" ]; then
    echo -e "\n${RED}❌ Error: No se encontraron los archivos del proyecto (composer.json no existe en ${INSTALL_DIR}).${NC}"
    echo -e "${YELLOW}Si el repositorio es PRIVADO en GitHub, ejecuta estos pasos manualmente:${NC}"
    echo -e "  1. git clone https://github.com/fredeabal/alertas-proxmox.git"
    echo -e "  2. cd alertas-proxmox"
    echo -e "  3. bash install.sh\n"
    exit 1
fi

# 5. Instalar dependencias PHP con Composer
echo -e "\n${YELLOW}⏳ [3/6] Instalando dependencias PHP de Composer...${NC}"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

# 6. Configurar archivo .env
echo -e "\n${YELLOW}⏳ [4/6] Configurando archivo de entorno (.env)...${NC}"
if [ ! -f ".env" ]; then
    if [ -f "env" ]; then
        cp env .env
    else
        echo -e "${RED}❌ Error: No se encontró la plantilla del archivo 'env'.${NC}"
        exit 1
    fi
fi

# Crear directorio para SQLite si no existe
DB_DIR="${INSTALL_DIR}/writable/database"
mkdir -p "$DB_DIR"
DB_PATH="${DB_DIR}/database.sqlite"

# Ajustar valores en .env
sed -i "s|# CI_ENVIRONMENT = .*|CI_ENVIRONMENT = development|g" .env
sed -i "s|CI_ENVIRONMENT = .*|CI_ENVIRONMENT = development|g" .env
sed -i "s|# app.baseURL = .*|app.baseURL = '${DOMAIN}'|g" .env
sed -i "s|app.baseURL = .*|app.baseURL = '${DOMAIN}'|g" .env
sed -i "s|# database.default.hostname = .*|database.default.hostname = localhost|g" .env
sed -i "s|# database.default.database = .*|database.default.database = ${DB_PATH}|g" .env
sed -i "s|# database.default.DBDriver = .*|database.default.DBDriver = SQLite3|g" .env

# Generar llave de encriptación
php spark key:generate --force > /dev/null 2>&1 || true

# 7. Migraciones y semilla de base de datos
echo -e "\n${YELLOW}⏳ [5/6] Configurando la base de datos y creando usuario inicial...${NC}"
chmod -R 777 writable/

php spark migrate --all
php spark db:seed SuperAdminSeeder

# Pasar a entorno producción
sed -i "s|CI_ENVIRONMENT = development|CI_ENVIRONMENT = production|g" .env

# 8. Configurar Nginx
echo -e "\n${YELLOW}⏳ [6/6] Configurando el servidor web Nginx...${NC}"

PHP_VER=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
PHP_SOCK="/var/run/php/php${PHP_VER}-fpm.sock"

cat <<EOF > /etc/nginx/sites-available/proxmox-alert
server {
    listen 80;
    server_name ${DOMAIN_HOST};

    root ${INSTALL_DIR}/public;
    index index.php index.html index.htm;

    location / {
        try_files \$uri \$uri/ /index.php\$is_args\$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHP_SOCK};
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    error_log  /var/log/nginx/proxmox_alert_error.log;
    access_log /var/log/nginx/proxmox_alert_access.log;
}
EOF

# Activar sitio y desactivar default
ln -sf /etc/nginx/sites-available/proxmox-alert /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test y reinicio de Nginx
nginx -t && systemctl restart nginx
systemctl restart "php${PHP_VER}-fpm" 2>/dev/null || systemctl restart php-fpm 2>/dev/null || true

# 9. Permisos finales
chown -R www-data:www-data "$INSTALL_DIR"
chmod -R 755 "$INSTALL_DIR"
chmod -R 775 "${INSTALL_DIR}/writable"
mkdir -p "${INSTALL_DIR}/public/uploads"
chmod -R 775 "${INSTALL_DIR}/public/uploads"

clear
echo -e "${GREEN}"
echo "======================================================================"
echo "       🎉 ¡INSTALACIÓN DE PROXMOX ALERT COMPLETADA CON ÉXITO!        "
echo "======================================================================"
echo -e "${NC}"
echo -e "👉 Acceso Web: ${GREEN}${DOMAIN}${NC}"
echo -e "👉 Usuario Admin: ${YELLOW}admin${NC}"
echo -e "👉 Contraseña:  ${YELLOW}admin123${NC}"
echo -e "----------------------------------------------------------------------"
echo -e "${YELLOW}⚠️  Recuerda cambiar la contraseña desde el panel tras tu primer inicio de sesión.${NC}\n"
