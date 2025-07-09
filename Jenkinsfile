// ========================================
// JENKINSFILE - SISTEMA DE CERTIFICADOS
// ========================================

pipeline {
    agent any
    
    environment {
        // Variables de entorno
        PHP_VERSION = '8.1'
        NODE_VERSION = '18'
        DOCKER_REGISTRY = 'your-registry.com'
        IMAGE_NAME = 'certificados'
        IMAGE_TAG = "${env.BUILD_NUMBER}"
        
        // Credenciales (configurar en Jenkins)
        DOCKER_CREDENTIALS = credentials('docker-registry-credentials')
        SSH_CREDENTIALS = credentials('ssh-deploy-credentials')
        DB_CREDENTIALS = credentials('database-credentials')
        
        // URLs de despliegue
        STAGING_URL = 'staging.certificados.com'
        PRODUCTION_URL = 'certificados.com'
    }
    
    options {
        // Configuraciones del pipeline
        timeout(time: 2, unit: 'HOURS')
        disableConcurrentBuilds()
        buildDiscarder(logRotator(numToKeepStr: '10'))
    }
    
    stages {
        // ========================================
        // PREPARACIÓN Y CHECKOUT
        // ========================================
        stage('Checkout') {
            steps {
                script {
                    // Limpiar workspace
                    cleanWs()
                    
                    // Checkout del código
                    checkout scm
                    
                    // Mostrar información del build
                    echo "Build #${env.BUILD_NUMBER}"
                    echo "Branch: ${env.BRANCH_NAME}"
                    echo "Commit: ${env.GIT_COMMIT}"
                }
            }
        }
        
        // ========================================
        // ANÁLISIS DE CÓDIGO
        // ========================================
        stage('Code Analysis') {
            parallel {
                stage('PHP CodeSniffer') {
                    steps {
                        script {
                            sh '''
                                docker run --rm -v ${WORKSPACE}:/app composer:latest \
                                    composer require --dev squizlabs/php_codesniffer
                                docker run --rm -v ${WORKSPACE}:/app php:8.1-cli \
                                    ./vendor/bin/phpcs --standard=PSR12 --extensions=php --ignore=vendor/ .
                            '''
                        }
                    }
                }
                
                stage('PHP Mess Detector') {
                    steps {
                        script {
                            sh '''
                                docker run --rm -v ${WORKSPACE}:/app composer:latest \
                                    composer require --dev phpmd/phpmd
                                docker run --rm -v ${WORKSPACE}:/app php:8.1-cli \
                                    ./vendor/bin/phpmd . text cleancode,codesize,controversial,design,naming,unusedcode --exclude vendor/
                            '''
                        }
                    }
                }
                
                stage('Security Scan') {
                    steps {
                        script {
                            sh '''
                                docker run --rm -v ${WORKSPACE}:/app composer:latest \
                                    composer require --dev enlightn/security-checker
                                docker run --rm -v ${WORKSPACE}:/app php:8.1-cli \
                                    ./vendor/bin/security-checker security:check composer.lock
                            '''
                        }
                    }
                }
            }
        }
        
        // ========================================
        // PRUEBAS UNITARIAS
        // ========================================
        stage('Unit Tests') {
            steps {
                script {
                    // Configurar base de datos de prueba
                    sh '''
                        docker run --rm -d --name test-mysql \
                            -e MYSQL_ROOT_PASSWORD=root \
                            -e MYSQL_DATABASE=certificados_test \
                            mysql:8.0
                        
                        # Esperar a que MySQL esté listo
                        sleep 30
                        
                        # Ejecutar pruebas
                        docker run --rm --link test-mysql:mysql \
                            -v ${WORKSPACE}:/app \
                            -e DB_HOST=test-mysql \
                            -e DB_DATABASE=certificados_test \
                            -e DB_USERNAME=root \
                            -e DB_PASSWORD=root \
                            php:8.1-cli \
                            bash -c "
                                composer install --prefer-dist --no-progress
                                composer require --dev phpunit/phpunit
                                ./vendor/bin/phpunit --coverage-clover=coverage.xml
                            "
                        
                        # Limpiar contenedor de prueba
                        docker stop test-mysql
                        docker rm test-mysql
                    '''
                }
            }
            post {
                always {
                    // Publicar reporte de cobertura
                    publishCoverage adapters: [cloverAdapter('coverage.xml')], 
                                   sourceFileResolver: sourceFiles('STORE_LAST_BUILD')
                }
            }
        }
        
        // ========================================
        // PRUEBAS DE INTEGRACIÓN
        // ========================================
        stage('Integration Tests') {
            steps {
                script {
                    sh '''
                        # Levantar stack completo para pruebas
                        docker-compose -f docker-compose.test.yml up -d
                        
                        # Esperar a que los servicios estén listos
                        sleep 60
                        
                        # Ejecutar pruebas de integración
                        docker run --rm --network certificados-network \
                            -v ${WORKSPACE}:/app \
                            php:8.1-cli \
                            bash -c "
                                composer install --prefer-dist --no-progress
                                ./vendor/bin/phpunit --testsuite=integration
                            "
                        
                        # Limpiar
                        docker-compose -f docker-compose.test.yml down
                    '''
                }
            }
        }
        
        // ========================================
        // CONSTRUCCIÓN DE IMAGEN DOCKER
        // ========================================
        stage('Build Docker Image') {
            steps {
                script {
                    // Construir imagen Docker
                    sh '''
                        docker build -t ${DOCKER_REGISTRY}/${IMAGE_NAME}:${IMAGE_TAG} .
                        docker build -t ${DOCKER_REGISTRY}/${IMAGE_NAME}:latest .
                        
                        # Etiquetar con el nombre de la rama si no es main
                        if [ "${BRANCH_NAME}" != "main" ]; then
                            docker tag ${DOCKER_REGISTRY}/${IMAGE_NAME}:${IMAGE_TAG} \
                                ${DOCKER_REGISTRY}/${IMAGE_NAME}:${BRANCH_NAME}
                        fi
                    '''
                }
            }
        }
        
        // ========================================
        // PRUEBAS DE IMAGEN
        // ========================================
        stage('Test Docker Image') {
            steps {
                script {
                    sh '''
                        # Ejecutar contenedor de prueba
                        docker run -d --name test-container \
                            -p 8080:80 \
                            ${DOCKER_REGISTRY}/${IMAGE_NAME}:${IMAGE_TAG}
                        
                        # Esperar a que esté listo
                        sleep 30
                        
                        # Ejecutar pruebas de smoke
                        curl -f http://localhost:8080/health.php
                        curl -f http://localhost:8080/
                        curl -f http://localhost:8080/admin/
                        
                        # Limpiar
                        docker stop test-container
                        docker rm test-container
                    '''
                }
            }
        }
        
        // ========================================
        // DESPLIEGUE A STAGING
        // ========================================
        stage('Deploy to Staging') {
            when {
                branch 'develop'
            }
            steps {
                script {
                    // Push imagen a registro
                    sh '''
                        echo ${DOCKER_CREDENTIALS_PSW} | docker login ${DOCKER_REGISTRY} -u ${DOCKER_CREDENTIALS_USR} --password-stdin
                        docker push ${DOCKER_REGISTRY}/${IMAGE_NAME}:${IMAGE_TAG}
                        docker push ${DOCKER_REGISTRY}/${IMAGE_NAME}:${BRANCH_NAME}
                    '''
                    
                    // Desplegar a staging
                    sshagent(['ssh-deploy-credentials']) {
                        sh '''
                            ssh -o StrictHostKeyChecking=no ${STAGING_URL} "
                                # Crear backup
                                sudo cp -r /var/www/staging.certificados.com /var/www/staging.certificados.com.backup.\$(date +%Y%m%d_%H%M%S)
                                
                                # Detener servicios
                                sudo systemctl stop apache2
                                
                                # Pull nueva imagen
                                docker pull ${DOCKER_REGISTRY}/${IMAGE_NAME}:${IMAGE_TAG}
                                
                                # Actualizar docker-compose
                                sed -i 's|image: .*|image: ${DOCKER_REGISTRY}/${IMAGE_NAME}:${IMAGE_TAG}|g' /opt/certificados/docker-compose.yml
                                
                                # Reiniciar servicios
                                cd /opt/certificados
                                docker-compose up -d
                                
                                # Verificar despliegue
                                sleep 30
                                curl -f http://localhost/health.php
                            "
                        '''
                    }
                }
            }
        }
        
        // ========================================
        // PRUEBAS DE STAGING
        // ========================================
        stage('Staging Tests') {
            when {
                branch 'develop'
            }
            steps {
                script {
                    sh '''
                        # Pruebas automatizadas en staging
                        curl -f http://${STAGING_URL}/
                        curl -f http://${STAGING_URL}/admin/
                        curl -f http://${STAGING_URL}/verificar-certificado.php
                        
                        # Pruebas de funcionalidad
                        ./scripts/test-staging.sh
                    '''
                }
            }
        }
        
        // ========================================
        // DESPLIEGUE A PRODUCCIÓN
        // ========================================
        stage('Deploy to Production') {
            when {
                branch 'main'
            }
            input {
                message "¿Desplegar a producción?"
                ok "Deploy"
            }
            steps {
                script {
                    // Push imagen de producción
                    sh '''
                        echo ${DOCKER_CREDENTIALS_PSW} | docker login ${DOCKER_REGISTRY} -u ${DOCKER_CREDENTIALS_USR} --password-stdin
                        docker push ${DOCKER_REGISTRY}/${IMAGE_NAME}:latest
                    '''
                    
                    // Desplegar a producción
                    sshagent(['ssh-deploy-credentials']) {
                        sh '''
                            ssh -o StrictHostKeyChecking=no ${PRODUCTION_URL} "
                                # Crear backup completo
                                sudo cp -r /var/www/certificados.com /var/www/certificados.com.backup.\$(date +%Y%m%d_%H%M%S)
                                
                                # Backup de base de datos
                                sudo mysqldump -u root -p${DB_CREDENTIALS_PSW} certificados > /backup/certificados_\$(date +%Y%m%d_%H%M%S).sql
                                
                                # Detener servicios
                                sudo systemctl stop apache2
                                sudo systemctl stop mysql
                                
                                # Pull nueva imagen
                                docker pull ${DOCKER_REGISTRY}/${IMAGE_NAME}:latest
                                
                                # Actualizar docker-compose
                                sed -i 's|image: .*|image: ${DOCKER_REGISTRY}/${IMAGE_NAME}:latest|g' /opt/certificados/docker-compose.yml
                                
                                # Reiniciar servicios
                                cd /opt/certificados
                                docker-compose up -d
                                
                                # Verificar despliegue
                                sleep 30
                                curl -f https://localhost/health.php
                            "
                        '''
                    }
                }
            }
        }
        
        // ========================================
        // MONITOREO POST-DESPLIEGUE
        // ========================================
        stage('Post-Deployment Monitoring') {
            when {
                branch 'main'
            }
            steps {
                script {
                    sh '''
                        # Health checks
                        curl -f https://${PRODUCTION_URL}/health.php
                        curl -f https://${PRODUCTION_URL}/admin/health.php
                        
                        # Performance test
                        ab -n 100 -c 10 https://${PRODUCTION_URL}/
                        
                        # Security scan
                        curl -f https://${PRODUCTION_URL}/security-scan.php
                    '''
                }
            }
        }
    }
    
    post {
        always {
            // Limpiar imágenes Docker
            sh '''
                docker system prune -f
                docker image prune -f
            '''
            
            // Limpiar workspace
            cleanWs()
        }
        
        success {
            script {
                if (env.BRANCH_NAME == 'main') {
                    // Notificar éxito en producción
                    emailext (
                        subject: "✅ Despliegue exitoso - Sistema de Certificados v${env.BUILD_NUMBER}",
                        body: """
                        <h2>Despliegue Completado Exitosamente</h2>
                        <p><strong>Build:</strong> #${env.BUILD_NUMBER}</p>
                        <p><strong>Commit:</strong> ${env.GIT_COMMIT}</p>
                        <p><strong>URL:</strong> https://${PRODUCTION_URL}</p>
                        <p><strong>Fecha:</strong> ${new Date().format("yyyy-MM-dd HH:mm:ss")}</p>
                        """,
                        recipientProviders: [[$class: 'DevelopersRecipientProvider']]
                    )
                }
            }
        }
        
        failure {
            script {
                // Notificar fallo
                emailext (
                    subject: "❌ Fallo en CI/CD - Sistema de Certificados",
                    body: """
                    <h2>Fallo en el Pipeline</h2>
                    <p><strong>Build:</strong> #${env.BUILD_NUMBER}</p>
                    <p><strong>Rama:</strong> ${env.BRANCH_NAME}</p>
                    <p><strong>Commit:</strong> ${env.GIT_COMMIT}</p>
                    <p><strong>Stage:</strong> ${env.STAGE_NAME}</p>
                    <p><strong>URL del Build:</strong> ${env.BUILD_URL}</p>
                    """,
                    recipientProviders: [[$class: 'DevelopersRecipientProvider']]
                )
                
                // Rollback automático si es producción
                if (env.BRANCH_NAME == 'main') {
                    sshagent(['ssh-deploy-credentials']) {
                        sh '''
                            ssh -o StrictHostKeyChecking=no ${PRODUCTION_URL} "
                                # Encontrar backup más reciente
                                LATEST_BACKUP=\$(ls -t /var/www/certificados.com.backup.* | head -1)
                                
                                if [ -n \"\$LATEST_BACKUP\" ]; then
                                    # Restaurar backup
                                    sudo rm -rf /var/www/certificados.com
                                    sudo cp -r \$LATEST_BACKUP /var/www/certificados.com
                                    
                                    # Reiniciar servicios
                                    sudo systemctl restart apache2
                                    sudo systemctl restart mysql
                                    
                                    echo \"Rollback completado a: \$LATEST_BACKUP\"
                                fi
                            "
                        '''
                    }
                }
            }
        }
    }
} 