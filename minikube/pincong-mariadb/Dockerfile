FROM mariadb:10

ADD install/db /docker-entrypoint-initdb.d
RUN echo 'bind-address=0.0.0.0' >> /etc/mysql/my.cnf
