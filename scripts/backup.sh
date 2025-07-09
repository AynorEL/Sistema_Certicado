#!/bin/bash

# ========================================
# SCRIPT DE BACKUP - SISTEMA DE CERTIFICADOS
# ========================================

set -e

# Configuración
BACKUP_DIR="/backup"
DB_NAME="certificados"
DB_USER="root"
DB_PASSWORD="${DB_PASSWORD}"
WEB_DIR="/var/www/certificados.com"
RETENTION_DAYS=30
COMPRESS=true

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

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

# Función para crear backup de base de datos
backup_database() {
    print_message "Creando backup de base de datos..."
    
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    DB_BACKUP_FILE="${BACKUP_DIR}/db_certificados_${TIMESTAMP}.sql"
    
    # Crear directorio de backup si no existe
    mkdir -p "$BACKUP_DIR"
    
    # Backup de base de datos
    mysqldump -u "$DB_USER" -p"$DB_PASSWORD" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --add-drop-database \
        --databases "$DB_NAME" > "$DB_BACKUP_FILE"
    
    if [[ $? -eq 0 ]]; then
        print_success "Backup de base de datos creado: $DB_BACKUP_FILE"
        
        # Comprimir si está habilitado
        if [[ "$COMPRESS" == "true" ]]; then
            gzip "$DB_BACKUP_FILE"
            print_success "Backup comprimido: ${DB_BACKUP_FILE}.gz"
        fi
    else
        print_error "Error al crear backup de base de datos"
        return 1
    fi
}

# Función para crear backup de archivos
backup_files() {
    print_message "Creando backup de archivos..."
    
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    FILES_BACKUP_FILE="${BACKUP_DIR}/files_certificados_${TIMESTAMP}.tar"
    
    # Crear directorio de backup si no existe
    mkdir -p "$BACKUP_DIR"
    
    # Backup de archivos importantes
    tar -cf "$FILES_BACKUP_FILE" \
        -C "$WEB_DIR" \
        --exclude="admin/temp/*" \
        --exclude="admin/img/qr/*" \
        --exclude="*.log" \
        --exclude="vendor" \
        .
    
    if [[ $? -eq 0 ]]; then
        print_success "Backup de archivos creado: $FILES_BACKUP_FILE"
        
        # Comprimir si está habilitado
        if [[ "$COMPRESS" == "true" ]]; then
            gzip "$FILES_BACKUP_FILE"
            print_success "Backup comprimido: ${FILES_BACKUP_FILE}.gz"
        fi
    else
        print_error "Error al crear backup de archivos"
        return 1
    fi
}

# Función para crear backup completo
backup_full() {
    print_message "Creando backup completo..."
    
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    FULL_BACKUP_FILE="${BACKUP_DIR}/full_certificados_${TIMESTAMP}.tar"
    
    # Crear directorio de backup si no existe
    mkdir -p "$BACKUP_DIR"
    
    # Backup completo incluyendo base de datos
    mysqldump -u "$DB_USER" -p"$DB_PASSWORD" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --add-drop-database \
        --databases "$DB_NAME" > "${BACKUP_DIR}/temp_db.sql"
    
    tar -cf "$FULL_BACKUP_FILE" \
        -C "$WEB_DIR" \
        --exclude="admin/temp/*" \
        --exclude="admin/img/qr/*" \
        --exclude="*.log" \
        --exclude="vendor" \
        . \
        -C "$BACKUP_DIR" \
        temp_db.sql
    
    # Limpiar archivo temporal
    rm -f "${BACKUP_DIR}/temp_db.sql"
    
    if [[ $? -eq 0 ]]; then
        print_success "Backup completo creado: $FULL_BACKUP_FILE"
        
        # Comprimir si está habilitado
        if [[ "$COMPRESS" == "true" ]]; then
            gzip "$FULL_BACKUP_FILE"
            print_success "Backup comprimido: ${FULL_BACKUP_FILE}.gz"
        fi
    else
        print_error "Error al crear backup completo"
        return 1
    fi
}

# Función para limpiar backups antiguos
cleanup_old_backups() {
    print_message "Limpiando backups antiguos..."
    
    # Encontrar y eliminar backups más antiguos que RETENTION_DAYS
    find "$BACKUP_DIR" -name "*.sql" -mtime +$RETENTION_DAYS -delete
    find "$BACKUP_DIR" -name "*.tar" -mtime +$RETENTION_DAYS -delete
    find "$BACKUP_DIR" -name "*.gz" -mtime +$RETENTION_DAYS -delete
    
    print_success "Backups antiguos eliminados (más de $RETENTION_DAYS días)"
}

# Función para verificar integridad del backup
verify_backup() {
    local backup_file="$1"
    
    print_message "Verificando integridad del backup: $backup_file"
    
    if [[ "$backup_file" == *.gz ]]; then
        # Verificar archivo comprimido
        gunzip -t "$backup_file"
        if [[ $? -eq 0 ]]; then
            print_success "Backup verificado correctamente"
        else
            print_error "Backup corrupto: $backup_file"
            return 1
        fi
    elif [[ "$backup_file" == *.tar ]]; then
        # Verificar archivo tar
        tar -tf "$backup_file" > /dev/null
        if [[ $? -eq 0 ]]; then
            print_success "Backup verificado correctamente"
        else
            print_error "Backup corrupto: $backup_file"
            return 1
        fi
    elif [[ "$backup_file" == *.sql ]]; then
        # Verificar archivo SQL
        head -n 1 "$backup_file" | grep -q "MySQL dump"
        if [[ $? -eq 0 ]]; then
            print_success "Backup verificado correctamente"
        else
            print_error "Backup corrupto: $backup_file"
            return 1
        fi
    fi
}

# Función para listar backups
list_backups() {
    print_message "Listando backups disponibles..."
    
    echo ""
    echo "Backups de Base de Datos:"
    ls -lh "$BACKUP_DIR"/db_*.sql* 2>/dev/null || echo "No hay backups de base de datos"
    
    echo ""
    echo "Backups de Archivos:"
    ls -lh "$BACKUP_DIR"/files_*.tar* 2>/dev/null || echo "No hay backups de archivos"
    
    echo ""
    echo "Backups Completos:"
    ls -lh "$BACKUP_DIR"/full_*.tar* 2>/dev/null || echo "No hay backups completos"
    
    echo ""
    echo "Espacio utilizado:"
    du -sh "$BACKUP_DIR"
}

# Función para restaurar backup
restore_backup() {
    local backup_file="$1"
    local restore_type="$2"
    
    if [[ -z "$backup_file" ]]; then
        print_error "Debe especificar un archivo de backup"
        return 1
    fi
    
    if [[ ! -f "$backup_file" ]]; then
        print_error "Archivo de backup no encontrado: $backup_file"
        return 1
    fi
    
    print_warning "¿Está seguro de que desea restaurar desde: $backup_file?"
    read -p "Esto sobrescribirá los datos actuales. Continuar? (y/N): " -n 1 -r
    echo ""
    
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_message "Restauración cancelada"
        return 0
    fi
    
    print_message "Iniciando restauración..."
    
    case "$restore_type" in
        "database")
            restore_database_backup "$backup_file"
            ;;
        "files")
            restore_files_backup "$backup_file"
            ;;
        "full")
            restore_full_backup "$backup_file"
            ;;
        *)
            print_error "Tipo de restauración no válido"
            return 1
            ;;
    esac
}

# Función para restaurar backup de base de datos
restore_database_backup() {
    local backup_file="$1"
    
    print_message "Restaurando base de datos desde: $backup_file"
    
    # Detener servicios si es necesario
    systemctl stop apache2 2>/dev/null || true
    
    # Restaurar base de datos
    if [[ "$backup_file" == *.gz ]]; then
        gunzip -c "$backup_file" | mysql -u "$DB_USER" -p"$DB_PASSWORD"
    else
        mysql -u "$DB_USER" -p"$DB_PASSWORD" < "$backup_file"
    fi
    
    if [[ $? -eq 0 ]]; then
        print_success "Base de datos restaurada exitosamente"
    else
        print_error "Error al restaurar base de datos"
        return 1
    fi
    
    # Reiniciar servicios
    systemctl start apache2 2>/dev/null || true
}

# Función para restaurar backup de archivos
restore_files_backup() {
    local backup_file="$1"
    
    print_message "Restaurando archivos desde: $backup_file"
    
    # Detener servicios
    systemctl stop apache2 2>/dev/null || true
    
    # Crear backup de la versión actual
    backup_files
    
    # Restaurar archivos
    if [[ "$backup_file" == *.gz ]]; then
        gunzip -c "$backup_file" | tar -xf - -C "$WEB_DIR"
    else
        tar -xf "$backup_file" -C "$WEB_DIR"
    fi
    
    if [[ $? -eq 0 ]]; then
        # Restaurar permisos
        chown -R www-data:www-data "$WEB_DIR"
        chmod -R 755 "$WEB_DIR"
        chmod -R 777 "$WEB_DIR/assets/uploads"
        chmod -R 777 "$WEB_DIR/admin/img/qr"
        chmod -R 777 "$WEB_DIR/admin/temp"
        
        print_success "Archivos restaurados exitosamente"
    else
        print_error "Error al restaurar archivos"
        return 1
    fi
    
    # Reiniciar servicios
    systemctl start apache2 2>/dev/null || true
}

# Función para restaurar backup completo
restore_full_backup() {
    local backup_file="$1"
    
    print_message "Restaurando backup completo desde: $backup_file"
    
    # Detener servicios
    systemctl stop apache2 2>/dev/null || true
    systemctl stop mysql 2>/dev/null || true
    
    # Crear directorio temporal
    TEMP_DIR=$(mktemp -d)
    
    # Extraer backup
    if [[ "$backup_file" == *.gz ]]; then
        gunzip -c "$backup_file" | tar -xf - -C "$TEMP_DIR"
    else
        tar -xf "$backup_file" -C "$TEMP_DIR"
    fi
    
    if [[ $? -eq 0 ]]; then
        # Restaurar base de datos
        if [[ -f "$TEMP_DIR/temp_db.sql" ]]; then
            mysql -u "$DB_USER" -p"$DB_PASSWORD" < "$TEMP_DIR/temp_db.sql"
            rm -f "$TEMP_DIR/temp_db.sql"
        fi
        
        # Restaurar archivos
        rsync -av --delete "$TEMP_DIR/" "$WEB_DIR/"
        
        # Restaurar permisos
        chown -R www-data:www-data "$WEB_DIR"
        chmod -R 755 "$WEB_DIR"
        chmod -R 777 "$WEB_DIR/assets/uploads"
        chmod -R 777 "$WEB_DIR/admin/img/qr"
        chmod -R 777 "$WEB_DIR/admin/temp"
        
        print_success "Backup completo restaurado exitosamente"
    else
        print_error "Error al restaurar backup completo"
        rm -rf "$TEMP_DIR"
        return 1
    fi
    
    # Limpiar directorio temporal
    rm -rf "$TEMP_DIR"
    
    # Reiniciar servicios
    systemctl start mysql 2>/dev/null || true
    systemctl start apache2 2>/dev/null || true
}

# Función para mostrar estadísticas
show_stats() {
    print_message "Estadísticas de backup..."
    
    echo ""
    echo "=== ESTADÍSTICAS DE BACKUP ==="
    echo "Directorio: $BACKUP_DIR"
    echo "Retención: $RETENTION_DAYS días"
    echo "Compresión: $COMPRESS"
    echo ""
    
    # Contar backups por tipo
    DB_COUNT=$(ls "$BACKUP_DIR"/db_*.sql* 2>/dev/null | wc -l)
    FILES_COUNT=$(ls "$BACKUP_DIR"/files_*.tar* 2>/dev/null | wc -l)
    FULL_COUNT=$(ls "$BACKUP_DIR"/full_*.tar* 2>/dev/null | wc -l)
    
    echo "Backups de Base de Datos: $DB_COUNT"
    echo "Backups de Archivos: $FILES_COUNT"
    echo "Backups Completos: $FULL_COUNT"
    echo ""
    
    # Espacio utilizado
    echo "Espacio total utilizado:"
    du -sh "$BACKUP_DIR"
    
    # Backup más reciente
    echo ""
    echo "Backup más reciente:"
    ls -lt "$BACKUP_DIR" | head -5
}

# Función para mostrar ayuda
show_help() {
    echo "Script de Backup - Sistema de Certificados"
    echo ""
    echo "Uso: $0 [OPCIONES]"
    echo ""
    echo "Opciones:"
    echo "  -d, --database           Crear backup solo de base de datos"
    echo "  -f, --files              Crear backup solo de archivos"
    echo "  -a, --all                Crear backup completo (default)"
    echo "  -l, --list               Listar backups disponibles"
    echo "  -s, --stats              Mostrar estadísticas"
    echo "  -c, --cleanup            Limpiar backups antiguos"
    echo "  -r, --restore FILE TYPE  Restaurar backup (TYPE: database|files|full)"
    echo "  -v, --verify FILE        Verificar integridad de backup"
    echo "  -h, --help               Mostrar esta ayuda"
    echo ""
    echo "Ejemplos:"
    echo "  $0 -d                    # Backup de base de datos"
    echo "  $0 -f                    # Backup de archivos"
    echo "  $0 -a                    # Backup completo"
    echo "  $0 -l                    # Listar backups"
    echo "  $0 -r backup.sql database # Restaurar base de datos"
    echo "  $0 -v backup.sql.gz      # Verificar backup"
}

# Variables por defecto
BACKUP_TYPE="all"
LIST_BACKUPS=false
SHOW_STATS=false
CLEANUP_ONLY=false
RESTORE_FILE=""
RESTORE_TYPE=""
VERIFY_FILE=""

# Parsear argumentos
while [[ $# -gt 0 ]]; do
    case $1 in
        -d|--database)
            BACKUP_TYPE="database"
            shift
            ;;
        -f|--files)
            BACKUP_TYPE="files"
            shift
            ;;
        -a|--all)
            BACKUP_TYPE="all"
            shift
            ;;
        -l|--list)
            LIST_BACKUPS=true
            shift
            ;;
        -s|--stats)
            SHOW_STATS=true
            shift
            ;;
        -c|--cleanup)
            CLEANUP_ONLY=true
            shift
            ;;
        -r|--restore)
            RESTORE_FILE="$2"
            RESTORE_TYPE="$3"
            shift 3
            ;;
        -v|--verify)
            VERIFY_FILE="$2"
            shift 2
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

# Función principal
main() {
    print_message "Iniciando script de backup..."
    
    # Verificar que el directorio de backup existe
    if [[ ! -d "$BACKUP_DIR" ]]; then
        mkdir -p "$BACKUP_DIR"
        print_message "Directorio de backup creado: $BACKUP_DIR"
    fi
    
    # Ejecutar acciones según argumentos
    if [[ "$LIST_BACKUPS" == "true" ]]; then
        list_backups
    elif [[ "$SHOW_STATS" == "true" ]]; then
        show_stats
    elif [[ "$CLEANUP_ONLY" == "true" ]]; then
        cleanup_old_backups
    elif [[ -n "$RESTORE_FILE" ]]; then
        restore_backup "$RESTORE_FILE" "$RESTORE_TYPE"
    elif [[ -n "$VERIFY_FILE" ]]; then
        verify_backup "$VERIFY_FILE"
    else
        # Crear backup según tipo
        case "$BACKUP_TYPE" in
            "database")
                backup_database
                ;;
            "files")
                backup_files
                ;;
            "all")
                backup_full
                ;;
            *)
                print_error "Tipo de backup no válido"
                exit 1
                ;;
        esac
        
        # Limpiar backups antiguos después de crear uno nuevo
        cleanup_old_backups
        
        # Mostrar estadísticas
        show_stats
    fi
    
    print_success "Script de backup completado"
}

# Ejecutar función principal
main "$@" 