createdb transitdata
createlang -d transitdata plpgsql
psql -d transitdata -f /var/www/lib/postgis.sql
# curl https://github.com/maxious/ACTBus-ui/raw/master/transitdata.cbrfeed.sql.gz -o transitdata.cbrfeed.sql.gz 
#made with pg_dump transitdata | gzip -c >  transitdata.cbrfeed.sql.gz
gunzip /var/www/transitdata.cbrfeed.sql.gz
psql -d transitdata -f /var/www/transitdata.cbrfeed.sql
#createuser transitdata -SDRP
#password transitdata
#psql -d transitdata -c "GRANT SELECT ON TABLE agency,calendar,calendar_dates,routes,stop_times,stops,trips TO transitdata;"
#psql -d transitdata -c "GRANT SELECT,INSERT ON	TABLE myway_observations,myway_routes,myway_stops,myway_timingdeltas TO transitdata;"
#psql -d transitdata -c	"GRANT SELECT,INSERT,UPDATE ON TABLE myway_routes,myway_stops TO transitdata;"
##psql -d transitdata -c "GRANT SELECT ON ALL TABLES IN SCHEMA public TO transitdata;"
php /var/www/updatedb.php