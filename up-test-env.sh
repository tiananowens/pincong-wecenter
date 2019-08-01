#/bin/sh

minikube start
eval $(minikube docker-env)
docker build -t pincong-app -f minikube/pincong-app/Dockerfile .
docker build -t pincong-mariadb -f minikube/pincong-mariadb/Dockerfile .
kubectl create -f minikube/pincong-namespace.yaml
kubectl create -f minikube/pincong-mariadb-deployment.yaml
kubectl create -f minikube/pincong-mariadb-service.yaml
kubectl create -f minikube/pincong-mariadb-nodeport.yaml
eval $(minikube docker-env -u)