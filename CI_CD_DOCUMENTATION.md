# Sistema CI/CD - Sistema de Certificados

## 📋 Tabla de Contenidos

1. [Descripción General](#descripción-general)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Configuración de Entornos](#configuración-de-entornos)
4. [GitHub Actions](#github-actions)
5. [GitLab CI/CD](#gitlab-cicd)
6. [Jenkins Pipeline](#jenkins-pipeline)
7. [Docker y Docker Compose](#docker-y-docker-compose)
8. [Scripts de Despliegue](#scripts-de-despliegue)
9. [Monitoreo y Alertas](#monitoreo-y-alertas)
10. [Seguridad](#seguridad)
11. [Troubleshooting](#troubleshooting)
12. [Mejores Prácticas](#mejores-prácticas)

## 🎯 Descripción General

Este sistema CI/CD está diseñado específicamente para el **Sistema de Certificados** que incluye:

- **Frontend**: Interfaz de usuario para verificación de certificados
- **Backend**: Panel de administración y API
- **Base de Datos**: MySQL para almacenamiento de datos
- **Generación de PDFs**: Certificados con QR codes
- **Sistema de Pagos**: Integración con pasarelas de pago
- **Email**: Envío automático de certificados

### Características Principales

- ✅ **Integración Continua**: Análisis de código, pruebas unitarias e integración
- ✅ **Despliegue Continuo**: Automatización completa de despliegues
- ✅ **Múltiples Plataformas**: GitHub Actions, GitLab CI, Jenkins
- ✅ **Containerización**: Docker para consistencia entre entornos
- ✅ **Monitoreo**: Prometheus + Grafana para métricas
- ✅ **Backup Automático**: Sistema de respaldo y recuperación
- ✅ **Rollback Automático**: Recuperación rápida en caso de fallos
- ✅ **Seguridad**: Escaneo de vulnerabilidades y análisis de código
- ✅ **Despliegue FTP**: Compatible con hosting compartido

## 🏗️ Arquitectura del Sistema

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   GitHub/GitLab │    │     Jenkins     │    │   Docker Hub    │
│   Repository    │    │     Server      │    │   Registry      │
└─────────┬───────┘    └─────────┬───────┘    └─────────┬───────┘
          │                      │                      │
          └──────────────────────┼──────────────────────┘
                                 │
                    ┌─────────────┴─────────────┐
                    │      CI/CD Pipeline       │
                    │  (Build, Test, Deploy)    │
                    └─────────────┬─────────────┘
                                  │
                    ┌─────────────┴─────────────┐
                    │      Docker Images        │
                    │  (certificados:latest)    │
                    └─────────────┬─────────────┘
                                  │
                    ┌─────────────┴─────────────┐
                    │     Staging Environment   │
                    │   (Testing & Validation)  │
                    └─────────────┬─────────────┘
                                  │
                    ┌─────────────┴─────────────┐
                    │   Production Environment  │
                    │    (Live Application)     │
                    └───────────────────────────┘
```

## ⚙️ Configuración de Entornos

### Variables de Entorno Requeridas

```bash
# FTP Credentials
FTP_HOST=ftp.iestpsanmarcos.edu.pe
FTP_USER=certificados@iestpsanmarcos.edu.pe
FTP_PASSWORD=TuContraseñaFTP

# Base de Datos
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=certificados
DB_USERNAME=certificados_user
DB_PASSWORD=your_secure_password
MYSQL_ROOT_PASSWORD=your_root_password

# Email (Gmail SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_USER=your_email@gmail.com
MAIL_PASS=your_app_password
MAIL_PORT=587
MAIL_FROM_NAME=Sistema de Certificados

# Docker Registry (opcional)
DOCKER_REGISTRY=your-registry.com
DOCKER_REGISTRY_USER=your_username
DOCKER_REGISTRY_PASSWORD=your_password

# Monitoreo
GRAFANA_PASSWORD=your_grafana_password
SLACK_WEBHOOK_URL=your_slack_webhook_url
SNYK_TOKEN=your_snyk_token
```

### Estructura de Directorios

```
certificado/
├── .github/
│   └── workflows/
│       └── ci-cd.yml
├── docker/
│   ├── apache/
│   │   └── vhost.conf
│   ├── php/
│   │   ├── php.ini
│   │   └── opcache.ini
│   ├── nginx/
│   │   ├── nginx.conf
│   │   └── conf.d/
│   ├── mysql/
│   │   ├── init/
│   │   └── conf/
│   ├── redis/
│   │   └── redis.conf
│   ├── prometheus/
│   │   └── prometheus.yml
│   ├── grafana/
│   │   ├── provisioning/
│   │   └── dashboards/
│   └── scripts/
│       ├── entrypoint.sh
│       └── backup.sh
├── scripts/
│   ├── deploy.sh
│   └── backup.sh
├── Dockerfile
├── docker-compose.yml
├── docker-compose.test.yml
├── Jenkinsfile
├── .gitlab-ci.yml
└── CI_CD_DOCUMENTATION.md
```

## 🚀 GitHub Actions

### Configuración

El workflow de GitHub Actions se encuentra en `.github/workflows/ci-cd.yml` y incluye:

#### Jobs Principales

1. **code-quality**: Análisis de calidad de código
2. **tests**: Pruebas unitarias e integración
3. **security**: Escaneo de seguridad
4. **build**: Construcción de imagen Docker
5. **deploy-staging**: Despliegue a staging via FTP
6. **deploy-production**: Despliegue a producción via FTP
7. **monitoring**: Monitoreo post-despliegue
8. **rollback**: Rollback automático

#### Secrets Requeridos

```bash
# Configurar en GitHub Repository > Settings > Secrets
FTP_HOST=ftp.iestpsanmarcos.edu.pe
FTP_USER=certificados@iestpsanmarcos.edu.pe
FTP_PASSWORD=TuContraseñaFTP

SLACK_WEBHOOK_URL=https://hooks.slack.com/services/...
SNYK_TOKEN=your_snyk_token
```

#### Uso

```bash
# Despliegue automático a staging (rama develop)
git push origin develop

# Despliegue a producción (crear release)
git tag v1.0.0
git push origin v1.0.0
```

## 🔧 GitLab CI/CD

### Configuración

El pipeline de GitLab se encuentra en `.gitlab-ci.yml` y incluye:

#### Stages

1. **validate**: Validación de código
2. **test**: Pruebas unitarias e integración
3. **build**: Construcción de imagen Docker
4. **deploy-staging**: Despliegue a staging via FTP
5. **deploy-production**: Despliegue a producción via FTP

#### Variables de Entorno

```bash
# Configurar en GitLab > Settings > CI/CD > Variables
FTP_HOST=ftp.iestpsanmarcos.edu.pe
FTP_USER=certificados@iestpsanmarcos.edu.pe
FTP_PASSWORD=TuContraseñaFTP
```

#### Uso

```bash
# Despliegue automático a staging (rama develop)
git push origin develop

# Despliegue manual a producción (rama main)
git push origin main
# Luego ir a GitLab > CI/CD > Pipelines y ejecutar manualmente
```

## 🔄 Jenkins Pipeline

### Configuración

El Jenkinsfile incluye un pipeline completo con:

#### Credenciales Requeridas

```bash
# Configurar en Jenkins > Manage Jenkins > Credentials
docker-registry-credentials
ftp-deploy-credentials
database-credentials
```

#### Pipeline Stages

1. **Checkout**: Preparación del código
2. **Code Analysis**: Análisis de calidad
3. **Unit Tests**: Pruebas unitarias
4. **Integration Tests**: Pruebas de integración
5. **Build Docker Image**: Construcción de imagen
6. **Test Docker Image**: Pruebas de imagen
7. **Deploy to Staging**: Despliegue a staging via FTP
8. **Deploy to Production**: Despliegue a producción via FTP
9. **Post-Deployment Monitoring**: Monitoreo

#### Uso

```bash
# Crear job en Jenkins
# Configurar webhook desde GitHub/GitLab
# El pipeline se ejecutará automáticamente
```

## 🐳 Docker y Docker Compose

### Dockerfile

El Dockerfile está optimizado para PHP 8.1 con Apache e incluye:

- Extensiones PHP necesarias (GD, MySQL, etc.)
- Configuración de Apache
- Instalación de Composer
- Configuración de permisos
- Health checks

### Docker Compose

El `docker-compose.yml` incluye todos los servicios:

```yaml
services:
  app: # Aplicación principal
  mysql: # Base de datos
  redis: # Cache
  nginx: # Reverse proxy
  prometheus: # Monitoreo
  grafana: # Visualización
  backup: # Backup automático
  cleanup: # Limpieza de archivos
```

### Uso

```bash
# Desarrollo local
docker-compose up -d

# Producción
docker-compose -f docker-compose.prod.yml up -d

# Pruebas
docker-compose -f docker-compose.test.yml up -d
```

## 📜 Scripts de Despliegue

### deploy.sh

Script de despliegue manual con las siguientes características:

#### Opciones

```bash
./scripts/deploy.sh -e staging -b develop
./scripts/deploy.sh -e production -t v1.2.3
./scripts/deploy.sh -e production -r  # Rollback
```

#### Funcionalidades

- ✅ Backup automático antes del despliegue via FTP
- ✅ Verificación de health checks
- ✅ Pruebas post-despliegue
- ✅ Rollback automático en caso de fallo
- ✅ Notificaciones por Slack/Email
- ✅ Limpieza de backups antiguos
- ✅ Compatible con hosting compartido

### backup.sh

Script de backup automático con las siguientes características:

#### Opciones

```bash
./scripts/backup.sh -d  # Solo base de datos
./scripts/backup.sh -f  # Solo archivos
./scripts/backup.sh -a  # Backup completo
./scripts/backup.sh -l  # Listar backups
./scripts/backup.sh -r backup.sql database  # Restaurar
```

#### Funcionalidades

- ✅ Backup de base de datos con mysqldump
- ✅ Backup de archivos con tar
- ✅ Compresión automática con gzip
- ✅ Verificación de integridad
- ✅ Limpieza automática de backups antiguos
- ✅ Restauración selectiva

## 📊 Monitoreo y Alertas

### Prometheus

Configuración para métricas del sistema:

```yaml
# docker/prometheus/prometheus.yml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: "certificados-app"
    static_configs:
      - targets: ["app:80"]
    metrics_path: "/metrics"
```

### Grafana

Dashboards predefinidos para:

- Métricas de aplicación
- Rendimiento de base de datos
- Uso de recursos del sistema
- Errores y logs
- Generación de certificados

### Alertas

Configuración de alertas para:

- CPU > 80%
- Memoria > 85%
- Espacio en disco > 90%
- Errores HTTP > 5%
- Tiempo de respuesta > 2s

## 🔒 Seguridad

### Análisis de Código

- **PHP CodeSniffer**: Estándares PSR-12
- **PHP Mess Detector**: Detección de problemas
- **PHPStan**: Análisis estático
- **Security Checker**: Vulnerabilidades en dependencias

### Escaneo de Seguridad

- **OWASP ZAP**: Escaneo de vulnerabilidades web
- **Snyk**: Análisis de dependencias
- **Docker Scout**: Análisis de imágenes Docker

### Configuración de Seguridad

```apache
# docker/apache/vhost.conf
<VirtualHost *:80>
    # Headers de seguridad
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000"

    # Configuración de SSL
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/certificados.crt
    SSLCertificateKeyFile /etc/ssl/private/certificados.key
</VirtualHost>
```

## 🛠️ Troubleshooting

### Problemas Comunes

#### 1. Error de Conexión FTP

```bash
# Verificar conectividad FTP
lftp -c "
  set ssl:verify-certificate no;
  open -u $FTP_USER,$FTP_PASSWORD $FTP_HOST;
  ls;
"

# Verificar credenciales
echo "Usuario: $FTP_USER"
echo "Host: $FTP_HOST"
```

#### 2. Error de Permisos en Hosting

```bash
# Verificar permisos de archivos
ls -la /public_html/

# Configurar permisos correctos
chmod 644 *.php
chmod 755 admin/
chmod 777 assets/uploads/
chmod 777 admin/img/qr/
chmod 777 admin/temp/
```

#### 3. Error de Conexión a Base de Datos

```bash
# Verificar conectividad
mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD -e "SELECT 1;"

# Verificar variables de entorno
echo "DB_HOST: $DB_HOST"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_USERNAME: $DB_USERNAME"
```

#### 4. Error de Generación de PDF

```bash
# Verificar extensiones PHP
php -m | grep -E "(gd|dompdf)"

# Verificar logs de error
tail -f /var/log/apache2/error.log
```

#### 5. Error de Email

```bash
# Verificar configuración SMTP
php -r "
require 'vendor/autoload.php';
\$mail = new PHPMailer\PHPMailer\PHPMailer();
\$mail->SMTPDebug = 2;
\$mail->isSMTP();
\$mail->Host = 'smtp.gmail.com';
\$mail->SMTPAuth = true;
\$mail->Username = '$MAIL_USER';
\$mail->Password = '$MAIL_PASS';
\$mail->SMTPSecure = 'tls';
\$mail->Port = 587;
\$mail->setFrom('$MAIL_USER', 'Test');
\$mail->addAddress('test@example.com');
\$mail->Subject = 'Test';
\$mail->Body = 'Test';
if(\$mail->send()) echo 'OK'; else echo 'Error: ' . \$mail->ErrorInfo;
"
```

### Logs y Debugging

#### Verificar Logs

```bash
# Logs de aplicación
tail -f /var/log/apache2/error.log

# Logs de base de datos
tail -f /var/log/mysql/error.log

# Logs de FTP
tail -f /var/log/vsftpd.log
```

#### Health Checks

```bash
# Verificar health check de aplicación
curl -f https://iestpsanmarcos.edu.pe/health.php

# Verificar health check de base de datos
mysql -u $DB_USERNAME -p$DB_PASSWORD -e "SELECT 1;"

# Verificar conectividad FTP
lftp -c "open -u $FTP_USER,$FTP_PASSWORD $FTP_HOST; ls;"
```

## 📚 Mejores Prácticas

### 1. Gestión de Versiones

```bash
# Usar semantic versioning
git tag v1.2.3
git push origin v1.2.3

# Crear releases en GitHub/GitLab
# Incluir changelog y notas de despliegue
```

### 2. Seguridad

```bash
# Rotar credenciales regularmente
# Usar secrets management
# Escanear vulnerabilidades antes de cada despliegue
# Mantener dependencias actualizadas
```

### 3. Monitoreo

```bash
# Configurar alertas proactivas
# Monitorear métricas de negocio
# Revisar logs regularmente
# Configurar dashboards personalizados
```

### 4. Backup

```bash
# Backup diario automático
# Verificar integridad de backups
# Probar restauración regularmente
# Mantener múltiples copias
```

### 5. Despliegue

```bash
# Usar blue-green deployment
# Implementar feature flags
# Monitorear métricas post-despliegue
# Tener plan de rollback
```

### 6. Hosting Compartido

```bash
# Usar FTP para despliegue
# Configurar permisos correctos
# Optimizar para recursos limitados
# Usar CDN para assets estáticos
```

## 📞 Soporte

### Contacto

- **Email**: soporte@certificados.com
- **Slack**: #certificados-dev
- **Documentación**: https://docs.certificados.com

### Recursos Adicionales

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [GitLab CI/CD Documentation](https://docs.gitlab.com/ee/ci/)
- [Jenkins Documentation](https://www.jenkins.io/doc/)
- [Docker Documentation](https://docs.docker.com/)
- [Prometheus Documentation](https://prometheus.io/docs/)
- [FTP Deployment Guide](https://docs.github.com/en/actions/deployment/deploying-to-your-hosting-provider/deploying-to-a-hosting-provider-with-ftp)

---

**Nota**: Este sistema CI/CD está diseñado específicamente para el Sistema de Certificados y es compatible con hosting compartido usando FTP. Asegúrese de adaptar las configuraciones según sus necesidades específicas y entorno de producción.
