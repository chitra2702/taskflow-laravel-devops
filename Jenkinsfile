
pipeline {
    agent any

    environment {
        AWS_REGION = 'eu-north-1'
        AWS_ACCOUNT_ID = '928535088701'
        ECR_REPO = 'taskflow'
        ECR_URI = '928535088701.dkr.ecr.eu-north-1.amazonaws.com/taskflow'
    }

    stages {
        stage('Checkout') {
            steps {
                echo 'Checking out TaskFlow source code'
                checkout scm
            }
        }

        stage('Verify AWS') {
            steps {
                sh 'aws sts get-caller-identity'
            }
        }

        stage('Build Docker Image') {
            steps {
                sh 'docker build -t taskflow:${BUILD_NUMBER} .'
            }
        }

        stage('ECR Login') {
            steps {
                sh '''
                aws ecr get-login-password --region $AWS_REGION |
                docker login --username AWS --password-stdin \
                $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com
                '''
            }
        }

        stage('Push to ECR') {
            steps {
                sh '''
                docker tag taskflow:${BUILD_NUMBER} ${ECR_URI}:${BUILD_NUMBER}
                docker tag taskflow:${BUILD_NUMBER} ${ECR_URI}:latest

                docker push ${ECR_URI}:${BUILD_NUMBER}
                docker push ${ECR_URI}:latest
                '''
            }
        }
    }
}
