
pipeline {
    agent any

    environment {
        AWS_REGION = 'eu-north-1'
        AWS_ACCOUNT_ID = '928535088701'
        ECR_REPO = 'laravel-ecr'
        EKS_CLUSTER = 'Laravel-EKS'

        ECR_REGISTRY = "${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com"
        IMAGE_URI = "${ECR_REGISTRY}/${ECR_REPO}:${BUILD_NUMBER}"
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
                sh 'docker build -t ${ECR_REPO}:${BUILD_NUMBER} .'
            }
        }

        stage('ECR Login') {
            steps {
                sh '''
                    aws ecr get-login-password --region $AWS_REGION |
                    docker login --username AWS --password-stdin $ECR_REGISTRY
                '''
            }
        }

        stage('Push to ECR') {
            steps {
                sh '''
                    docker tag ${ECR_REPO}:${BUILD_NUMBER} ${IMAGE_URI}
                    docker push ${IMAGE_URI}
                '''
            }
        }

        stage('Configure EKS') {
            steps {
                sh '''
                    aws eks update-kubeconfig \
                    --region $AWS_REGION \
                    --name $EKS_CLUSTER

                    kubectl get nodes
                '''
            }
        }

        stage('Deploy MySQL') {
            steps {
                sh '''
                    kubectl apply -f k8s/mysql.yaml

                    kubectl rollout status deployment/mysql \
                    --timeout=180s
                '''
            }
        }

        stage('Deploy TaskFlow') {
            steps {
                sh '''
                    sed "s|image: .*|image: ${IMAGE_URI}|" \
                    k8s/taskflow.yaml | kubectl apply -f -

                    kubectl rollout status deployment/taskflow \
                    --timeout=180s

                    kubectl get pods
                    kubectl get services
                '''
            }
        }
    }

    post {
        success {
            echo 'TaskFlow successfully deployed to EKS!'
        }

        failure {
            echo 'TaskFlow pipeline failed. Check the stage logs.'
        }
    }
}
