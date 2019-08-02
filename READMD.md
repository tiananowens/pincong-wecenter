# pincong-wecenter

## 运行 MariaDB 测试容器

```
# 生成配置文件，設置編碼
mkdir -p $(pwd)/tmp/docker-volumes/test/mariadb.conf.d && \
echo '[client]
default-character-set = utf8mb4

[mysqld]
character_set_server   = utf8mb4
collation_server       = utf8mb4_general_ci' > $(pwd)/tmp/docker-volumes/test/mariadb.conf.d/mysql-charset.cnf

# 運行容器
docker run --name pincong-mariadb --rm -itd \
    -v $(pwd)/install/db:/docker-entrypoint-initdb.d:ro \
    -v $(pwd)/tmp/docker-volumes/test/mariadb.conf.d/:/etc/mysql/mariadb.conf.d:ro \
    -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
    -e MYSQL_DATABASE=db \
    -e MYSQL_USER=user1 \
    -e MYSQL_PASSWORD=123456 \
    -p 3306:3306/tcp \
    mariadb:10

# 退出
docker kill pincong-mariadb

docker attach pincong-mariadb
docker exec -it pincong-mariadb bash
```
