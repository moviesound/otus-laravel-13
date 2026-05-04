FROM nginx:latest

# Добавляем пользователя nginx в группу www-data
#RUN usermod -aG www-data nginx

# даём nginx доступ к сокету
RUN mkdir -p /sock && chmod 777 /sock