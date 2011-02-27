#!/bin/bash
#this script should be run from a fresh git checkout from http://maxious.lambdacomplex.org
#ami base must have yum install lighttpd-fastcgi, git, tomcat6 
#screen php-cli php-gd tomcat6-webapps tomcat6-admin-webapps
#http://www.how2forge.org/installing-lighttpd-with-php5-and-mysql-support-on-fedora-12

cp -rfv /tmp/busui/* /var/www
cp /root/aws.php /tmp/
chcon -h system_u:object_r:httpd_sys_content_t /var/www
chcon -R -h root:object_r:httpd_sys_content_t /var/www/*
chcon -R -t httpd_sys_content_rw_t /var/www/staticmaplite/cache
chmod -R 777 /var/www/staticmaplite/cache 
wget http://s3-ap-southeast-1.amazonaws.com/busresources/cbrfeed.zip \
-O /var/www/cbrfeed.zip
easy_install transitfeed
easy_install simplejson
screen -d -m /var/www/view.sh

wget http://s3-ap-southeast-1.amazonaws.com/busresources/Graph.obj \
-O /tmp/Graph.obj
rm -rfv /usr/share/tomcat6/webapps/opentripplanner*
wget http://s3-ap-southeast-1.amazonaws.com/busresources/opentripplanner-webapp.war \
-O /usr/share/tomcat6/webapps/opentripplanner-webapp.war
wget http://s3-ap-southeast-1.amazonaws.com/busresources/opentripplanner-api-webapp.war \
-O /usr/share/tomcat6/webapps/opentripplanner-api-webapp.war
/etc/init.d/tomcat6 restart

