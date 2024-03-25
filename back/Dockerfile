FROM php:8.1-cli

RUN apt-get update && apt-get -y install cron
RUN docker-php-ext-install pdo_mysql

RUN mkdir /back
WORKDIR /back

COPY check.php /back
COPY check_job.php /back
COPY send.php /back
COPY send_job.php /back

RUN touch check_first_cron.log
RUN touch check_third_cron.log
RUN touch send_first_cron.log
RUN touch send_third_cron.log
RUN touch check_job_cron.log
RUN touch send_job_cron.log

COPY ./crontab /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab
RUN /usr/bin/crontab /etc/cron.d/crontab