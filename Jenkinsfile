pipeline {
    agent any

    options {
        ansiColor('xterm')
        timestamps()
    }

    environment {
        DOCKER_REGISTRY = "registry.example.com/poppins"
        DOCKER_IMAGE = "${DOCKER_REGISTRY}:${BUILD_NUMBER}"
        REGISTRY_CREDENTIALS = "docker-registry"
        COMPOSER_MEMORY_LIMIT = "-1"
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Install dependencies') {
            steps {
                sh 'composer install --prefer-dist --no-interaction --no-progress'
            }
        }

        stage('Test') {
            steps {
                sh 'composer run test'
            }
        }

        stage('Build production image') {
            steps {
                sh 'docker build -f Dockerfile.deploy -t ${DOCKER_IMAGE} .'
            }
        }

        stage('Deploy (docker-compose)') {
            when {
                allOf {
                    expression { return fileExists('docker-compose.prod.yml') }
                    expression { return env.DEPLOY_ON_BUILD?.toBoolean() }
                }
            }
            steps {
                sh '''
                    docker compose -f docker-compose.prod.yml pull db || true
                    docker compose -f docker-compose.prod.yml up -d --build
                '''
            }
        }
    }

    post {
        failure {
            echo 'Pipeline failed, review the logs.'
        }
        success {
            echo "Image ${DOCKER_IMAGE} built successfully."
        }
        always {
            sh 'docker image prune -f || true'
        }
    }
}
