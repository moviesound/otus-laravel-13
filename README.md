### Инструкция по Установке: 
1. Собрать и запустить контейнер командой:

       docker compose up -d --build --no-cache

2. Зайти в терминал контейнера "app" через services в Phpstorm
или командой в терминале:

       docker exec -it app bash

3. Вы окажетесь в папке /var/www. Необходимо в 
контейнере перейти в каталог app командой и установить
все для работы

       cd app
       composer setup

4. Разрешить писать файлы в папку bootstrap/cache:

       chown -R www-data:www-data storage bootstrap/cache
       chmod -R 775 storage bootstrap/cache

5. Настраиваем домены. В .env Laravel должны быть 
прописаны домены, можно использовать любые url.
env.example полностью рабочий и его можно юзать, переименовав в .env

       API_HOST="api.localhost"
       WEB_HOST="web.localhost"
       ADMIN_HOST="admin.localhost"

   Далее необходимо направить эти урлы в hosts на ip:
127.0.0.1. 
На линукс открыть и отредактировать файл:

       sudo nano /etc/hosts
   Добавить туда

       127.0.0.1 api.localhost
       127.0.0.1 web.localhost
       127.0.0.1 admin.localhost

