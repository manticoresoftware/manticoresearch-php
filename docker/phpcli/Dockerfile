FROM php:7.1-cli-stretch

RUN apt -y update && apt -y upgrade
RUN apt -y install figlet git zip unzip
RUN apt -y autoremove

# alter bash prompt
ENV PS1A="\u@manticore.test:\w> "
RUN echo 'PS1=$PS1A' >> ~/.bashrc

# intro message when attaching to shell
RUN echo 'figlet -w 120 manticore unit testing' >> ~/.bashrc

# install composer - see https://medium.com/@c.harrison/speedy-composer-installs-in-docker-builds-41eea6d0172b
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && \
    composer global require hirak/prestissimo --no-plugins --no-scripts

# Prevent the container from exiting
CMD tail -f /dev/null
