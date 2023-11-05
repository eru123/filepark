# filepark
A file-hosting micro service

#### docker-compose production example
```yml
version: '3.8'
services:
  filepark:
    build: ../filepark
    container_name: filepark
    restart: unless-stopped
    env_file:
      - ./envs/filepark.env
```

#### docker-compose development example
```yml
version: '3.8'
services:
  filepark:
    build: ../filepark
    container_name: filepark
    restart: unless-stopped
    env_file:
      - ./envs/filepark.env
    volumes:
      - ../filepark:/var/www/html
```