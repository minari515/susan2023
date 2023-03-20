# use mysql-server for Apple Silicon Mac
FROM mysql/mysql-server:5.6

# timezone environment
ENV TZ=Asia/Tokyo

# initialize database
COPY initdb.d/* /docker-entrypoint-initdb.d/

CMD [ "mysqld" ]