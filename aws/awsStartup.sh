#!/bin/bash
#this script should be run from a fresh git checkout from github
#ami base must have yum install lighttpd-fastcgi, git, tomcat6 
#php-cli php-gd tomcat6-webapps tomcat6-admin-webapps svn maven2
#postgres postgres-server php-pg
#http://www.how2forge.org/installing-lighttpd-with-php5-and-mysql-support-on-fedora-12

cp /root/aws.php /tmp/
mkdir /var/www/lib/staticmaplite/cache 
chcon -h system_u:object_r:httpd_sys_content_t /var/www
chcon -R -h root:object_r:httpd_sys_content_t /var/www/*
chcon -R -t httpd_sys_content_rw_t /var/www/lib/staticmaplite/cache
chmod -R 777 /var/www/lib/staticmaplite/cache 
wget http://s3-ap-southeast-1.amazonaws.com/busresources/cbrfeed.zip \
-O /var/www/cbrfeed.zip

createdb transitdata
createlang -d transitdata plpgsql
psql -d transitdata -f /var/www/lib/postgis.sql
# curl https://github.com/maxious/ACTBus-ui/raw/master/transitdata.cbrfeed.sql.gz -o transitdata.cbrfeed.sql.gz 
#made with pg_dump transitdata | gzip -c >  transitdata.cbrfeed.sql.gz
gunzip /var/www/transitdata.cbrfeed.sql.gz
psql -d transitdata -f /var/www/transitdata.cbrfeed.sql
#createuser transitdata -SDRP
#password transitdata
#psql -d transitdata -c \"GRANT SELECT ON TABLE agency,calendar,calendar_dates,routes,stop_times,stops,trips TO transitdata;\"
php /var/www/updatedb.php

wget http://s3-ap-southeast-1.amazonaws.com/busresources/Graph.obj \
-O /tmp/Graph.obj
rm -rfv /usr/share/tomcat6/webapps/opentripplanner*
wget http://s3-ap-southeast-1.amazonaws.com/busresources/opentripplanner-webapp.war \
-O /usr/share/tomcat6/webapps/opentripplanner-webapp.war
wget http://s3-ap-southeast-1.amazonaws.com/busresources/opentripplanner-api-webapp.war \
-O /usr/share/tomcat6/webapps/opentripplanner-api-webapp.war
/etc/init.d/tomcat6 restart
