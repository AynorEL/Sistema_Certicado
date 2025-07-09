#!/bin/bash

# ========================================
# SCRIPT DE DESPLIEGUE - SISTEMA DE CERTIFICADOS
# ========================================

set -e  # Salir en caso de error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes
print_message() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Función para mostrar ayuda
show_help() {
    echo "Script de Despliegue - Sistema de Certificados"
    echo ""
    echo "Uso: $0 [OPCIONES]"
    echo ""
    echo "Opciones:"
    echo "  -e, --environment ENV    Ambiente de despliegue (staging|production)"
    echo "  -b, --branch BRANCH      Rama a desplegar (default: main)"
    echo "  -t, --tag TAG            Tag específico de la imagen"
    echo "  -f, --force              Forzar despliegue sin confirmación"
    echo "  -r, --rollback           Ejecutar rollback"
    echo "  -h, --help               Mostrar esta ayuda"
    echo ""
    echo "Ejemplos:"
    echo "  $0 -e staging -b develop"
    echo "  $0 -e production -t v1.2.3"
    echo "  $0 -e production -r"
}

# Variables por defecto
ENVIRONMENT=""
BRANCH="main"
TAG=""
FORCE=false
ROLLBACK=false

# Parsear argumentos
while [[ $# -gt 0 ]]; do
    case $1 in
        -e|--environment)
            ENVIRONMENT="$2"
            shift 2
            ;;
        -b|--branch)
            BRANCH="$2"
            shift 2
            ;;
        -t|--tag)
            TAG="$2"
            shift 2
            ;;
        -f|--force)
            FORCE=true
            shift
            ;;
        -r|--rollback)
            ROLLBACK=true
            shift
            ;;
        -h|--help)
            show_help
            exit 0
            ;;
        *)
            print_error "Opción desconocida: $1"
            show_help
            exit 1
            ;;
    esac
done

# Validar argumentos
if [[ -z "$ENVIRONMENT" ]]; then
    print_error "Debe especificar un ambiente (-e staging|production)"
    exit 1
fi

if [[ "$ENVIRONMENT" != "staging" && "$ENVIRONMENT" != "production" ]]; then
    print_error "Ambiente debe ser 'staging' o 'production'"
    exit 1
fi

# Configuración según ambiente
if [[ "$ENVIRONMENT" == "staging" ]]; then
    FTP_HOST="ftp.iestpsanmarcos.edu.pe"
    FTP_USER="certificados@iestpsanmarcos.edu.pe"
    FTP_PASSWORD="${FTP_PASSWORD}"
    DEPLOY_PATH="/staging"
    BACKUP_PATH="/backup_staging"
    HEALTH_URL="https://iestpsanmarcos.edu.pe/staging/health.php"
elif [[ "$ENVIRONMENT" == "production" ]]; then
    FTP_HOST="ftp.iestpsanmarcos.edu.pe"
    FTP_USER="certificados@iestpsanmarcos.edu.pe"
    FTP_PASSWORD="${FTP_PASSWORD}"
    DEPLOY_PATH="/public_html"
    BACKUP_PATH="/backup_production"
    HEALTH_URL="https://iestpsanmarcos.edu.pe/health.php"
fi

# Función para confirmar despliegue
confirm_deployment() {
    if [[ "$FORCE" == "false" ]]; then
        echo ""
        print_warning "¿Está seguro de que desea desplegar a $ENVIRONMENT?"
        echo "  - Host: $FTP_HOST"
        echo "  - Usuario: $FTP_USER"
        echo "  - Rama: $BRANCH"
        echo "  - Tag: $TAG"
        echo "  - Path: $DEPLOY_PATH"
        echo ""
        read -p "Continuar? (y/N): " -n 1 -r
        echo ""
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_message "Despliegue cancelado"
            exit 0
        fi
    fi
}

# Función para crear backup
create_backup() {
    print_message "Creando backup..."
    
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_DIR="${BACKUP_PATH}_${TIMESTAMP}"
    
    # Crear backup usando FTP
    lftp -c "
        set ssl:verify-certificate no;
        open -u $FTP_USER,$FTP_PASSWORD $FTP_HOST;
        mirror --reverse --only-newer --delete $DEPLOY_PATH $BACKUP_DIR;
    "
    
    print_success "Backup creado: $BACKUP_DIR"
}

# Función para crear build
create_build() {
    print_message "Creando build..."
    
    # Crear directorio de build
    mkdir -p build/certificado
    
    # Copiar archivos necesarios
    cp -r admin build/certificado/
    cp -r assets build/certificado/
    cp -r vendor build/certificado/
    cp -r payment build/certificado/
    cp -r PHPMailer build/certificado/
    
    # Copiar archivos PHP principales
    cp *.php build/certificado/
    cp .htaccess build/certificado/
    cp composer.json build/certificado/
    cp composer.lock build/certificado/
    
    # Excluir archivos de desarrollo
    rm -rf build/certificado/admin/temp/*
    rm -rf build/certificado/admin/img/qr/*
    rm -f build/certificado/test_*.php
    rm -f build/certificado/debug_*.php
    
    # Crear archivo de versión
    echo "Build: $(date +%Y%m%d_%H%M%S)" > build/certificado/VERSION
    echo "Branch: $BRANCH" >> build/certificado/VERSION
    echo "Tag: $TAG" >> build/certificado/VERSION
    echo "Date: $(date)" >> build/certificado/VERSION
    
    print_success "Build creado exitosamente"
}

# Función para desplegar código
deploy_code() {
    print_message "Desplegando código via FTP..."
    
    # Desplegar usando FTP
    lftp -c "
        set ssl:verify-certificate no;
        open -u $FTP_USER,$FTP_PASSWORD $FTP_HOST;
        mirror --reverse --only-newer --delete ./build/certificado/ $DEPLOY_PATH;
    "
    
    print_success "Código desplegado exitosamente"
}

# Función para verificar despliegue
verify_deployment() {
    print_message "Verificando despliegue..."
    
    # Esperar a que los servicios estén listos
    sleep 30
    
    # Verificar health check
    if curl -f "$HEALTH_URL" > /dev/null 2>&1; then
        print_success "Health check exitoso"
    else
        print_error "Health check falló"
        return 1
    fi
    
    # Verificar endpoints principales
    BASE_URL="https://$FTP_HOST"
    if [[ "$ENVIRONMENT" == "staging" ]]; then
        BASE_URL="$BASE_URL/staging"
    fi
    
    curl -f "$BASE_URL/" > /dev/null 2>&1 || {
        print_error "Página principal no accesible"
        return 1
    }
    
    curl -f "$BASE_URL/admin/" > /dev/null 2>&1 || {
        print_error "Panel de administración no accesible"
        return 1
    }
    
    curl -f "$BASE_URL/verificar-certificado.php" > /dev/null 2>&1 || {
        print_error "Página de verificación no accesible"
        return 1
    }
    
    print_success "Despliegue verificado exitosamente"
}

# Función para ejecutar pruebas post-despliegue
run_post_deployment_tests() {
    print_message "Ejecutando pruebas post-despliegue..."
    
    BASE_URL="https://$FTP_HOST"
    if [[ "$ENVIRONMENT" == "staging" ]]; then
        BASE_URL="$BASE_URL/staging"
    fi
    
    # Pruebas básicas
    curl -f "$HEALTH_URL" > /dev/null 2>&1 || {
        print_error "Health check falló"
        return 1
    }
    
    # Pruebas de funcionalidad
    curl -f "$BASE_URL/" > /dev/null 2>&1 || {
        print_error "Página principal no accesible"
        return 1
    }
    
    curl -f "$BASE_URL/admin/" > /dev/null 2>&1 || {
        print_error "Panel de administración no accesible"
        return 1
    }
    
    print_success "Pruebas post-despliegue completadas"
}

# Función para rollback
perform_rollback() {
    print_message "Ejecutando rollback..."
    
    # Encontrar backup más reciente
    LATEST_BACKUP=$(lftp -c "
        set ssl:verify-certificate no;
        open -u $FTP_USER,$FTP_PASSWORD $FTP_HOST;
        ls $BACKUP_PATH* | sort | tail -1;
    " | grep -o "$BACKUP_PATH[^[:space:]]*")
    
    if [[ -n "$LATEST_BACKUP" ]]; then
        # Restaurar desde backup
        lftp -c "
            set ssl:verify-certificate no;
            open -u $FTP_USER,$FTP_PASSWORD $FTP_HOST;
            mirror --reverse --only-newer --delete $LATEST_BACKUP $DEPLOY_PATH;
        "
        
        print_success "Rollback completado desde: $LATEST_BACKUP"
    else
        print_error "No se encontró backup para rollback"
        return 1
    fi
}

# Función para limpiar backups antiguos
cleanup_old_backups() {
    print_message "Limpiando backups antiguos..."
    
    # Mantener solo los últimos 5 backups
    BACKUP_COUNT=$(lftp -c "
        set ssl:verify-certificate no;
        open -u $FTP_USER,$FTP_PASSWORD $FTP_HOST;
        ls $BACKUP_PATH* | wc -l;
    ")
    
    if [[ $BACKUP_COUNT -gt 5 ]]; then
        # Eliminar backups antiguos
        lftp -c "
            set ssl:verify-certificate no;
            open -u $FTP_USER,$FTP_PASSWORD $FTP_HOST;
            ls $BACKUP_PATH* | sort | head -n -5 | xargs -I {} rm -rf {};
        "
        
        print_success "Backups antiguos eliminados"
    else
        print_message "No hay backups antiguos para eliminar"
    fi
}

# Función para notificar
notify_deployment() {
    local status=$1
    local message=$2
    
    # Aquí puedes agregar notificaciones por email, Slack, etc.
    print_message "Notificación: $message"
    
    # Ejemplo para Slack
    if [[ -n "$SLACK_WEBHOOK_URL" ]]; then
        curl -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"$message\"}" \
            "$SLACK_WEBHOOK_URL" > /dev/null 2>&1
    fi
}

# Función principal de despliegue
main_deployment() {
    print_message "Iniciando despliegue a $ENVIRONMENT..."
    
    # Confirmar despliegue
    confirm_deployment
    
    # Crear backup
    create_backup
    
    # Crear build
    create_build
    
    # Desplegar código
    deploy_code
    
    # Verificar despliegue
    verify_deployment
    
    # Ejecutar pruebas post-despliegue
    run_post_deployment_tests
    
    # Limpiar backups antiguos
    cleanup_old_backups
    
    # Notificar éxito
    notify_deployment "success" "✅ Despliegue a $ENVIRONMENT completado exitosamente"
    
    print_success "Despliegue completado exitosamente"
}

# Función principal de rollback
main_rollback() {
    print_message "Iniciando rollback en $ENVIRONMENT..."
    
    # Confirmar rollback
    if [[ "$FORCE" == "false" ]]; then
        echo ""
        print_warning "¿Está seguro de que desea ejecutar rollback en $ENVIRONMENT?"
        read -p "Continuar? (y/N): " -n 1 -r
        echo ""
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_message "Rollback cancelado"
            exit 0
        fi
    fi
    
    # Ejecutar rollback
    perform_rollback
    
    # Verificar rollback
    verify_deployment
    
    # Notificar rollback
    notify_deployment "rollback" "🔄 Rollback en $ENVIRONMENT completado"
    
    print_success "Rollback completado exitosamente"
}

# Verificar dependencias
check_dependencies() {
    if ! command -v lftp &> /dev/null; then
        print_error "lftp no está instalado. Instálelo con: sudo apt-get install lftp"
        exit 1
    fi
    
    if ! command -v curl &> /dev/null; then
        print_error "curl no está instalado. Instálelo con: sudo apt-get install curl"
        exit 1
    fi
}

# Ejecutar función principal
check_dependencies

if [[ "$ROLLBACK" == "true" ]]; then
    main_rollback
else
    main_deployment
fi 