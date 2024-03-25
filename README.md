# karma8-test

Запуск
```
docker-compose up -d
```

Для демо запускаются все задачи каждую минуту, для прода надо будет поменять файл `crontab`.
```
0 0 * * * /usr/local/bin/php /back/check.php 1 2 >> /back/check_first_cron.log 2>&1
0 0 * * * /usr/local/bin/php /back/check.php 3 4 >> /back/check_third_cron.log 2>&1
0 0 * * * /usr/local/bin/php /back/send.php 1 2 >> /back/send_first_cron.log 2>&1
0 0 * * * /usr/local/bin/php /back/send.php 3 4 >> /back/send_third_cron.log 2>&1

0 * * * * /usr/local/bin/php /back/check_job.php 0 60 >> /back/check_job_cron.log 2>&1
0 * * * * /usr/local/bin/php /back/check_job.php 60 60 >> /back/check_job_cron.log 2>&1
0 * * * * /usr/local/bin/php /back/check_job.php 120 60 >> /back/check_job_cron.log 2>&1
...
0 * * * * /usr/local/bin/php /back/send_job.php 0 360 >> /back/send_job_cron.log 2>&1
0 * * * * /usr/local/bin/php /back/send_job.php 360 360 >> /back/send_job_cron.log 2>&1
0 * * * * /usr/local/bin/php /back/send_job.php 720 360 >> /back/send_job_cron.log 2>&1
...
```

В идеальном мире кол-во джобов должно автоматически увеличиваться в зависимости от того, сколько задач в очереди.
Если бы речь шла про кубер, то можно было бы каждую минуту проверять длину очереди и если она больше N, то поднимать дополнительные поды.


По коду оставил `TODO`