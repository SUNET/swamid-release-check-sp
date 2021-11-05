FROM debian:buster
MAINTAINER el@sunet.se
RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
RUN apt-get -q update
RUN apt-get -y upgrade
#
RUN apt-get -y install apache2 libapache2-mod-shib libapache2-mod-php php-sqlite3 php-xml python3 php-curl
#
#RUN apt-get -y install strace vim
RUN a2enmod ssl 
RUN a2enmod headers
ENV SP_HOSTNAME sp.example.com
ENV SP_CONTACT noc@nordu.net
ENV SP_ABOUT /
ENV DEFAULT_LOGIN md.nordu.net
RUN mkdir -p /etc/shibboleth/certs /etc/shibboleth/metadata
RUN chown _shibd /etc/shibboleth/metadata
ADD md-signer2.crt /etc/shibboleth/md-signer2.crt
ADD shibboleth2.xml /etc/shibboleth/shibboleth2.xml
ADD attribute-map.xml /etc/shibboleth/attribute-map.xml
ADD default-ssl.conf /etc/apache2/sites-available/default-ssl.conf 
RUN a2ensite default-ssl
ADD start.sh /start.sh
RUN chmod a+rx /start.sh
ADD shibd.logger /etc/shibboleth/shibd.logger
EXPOSE 443
ENTRYPOINT ["/start.sh"]
