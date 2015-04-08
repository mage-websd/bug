#!/bin/sh
#########################################################
##Lets gather some information about the Magento Install
## Written by Brian Nelson @ http://briansnelson.com
#########################################################
SCRIPT=$(readlink -f $0)
PWD=`dirname $SCRIPT`
CUT=$(which cut)
MYSQL=$(which mysql)
GREP=$(which grep)
PHP=$(which php)
RM=$(which rm)
TOUCH=$(which touch)

if [ -f $PWD/app/etc/local.xml ]
then
SOURCEFILE=$PWD/app/etc/local.xml
else
clear;
echo -e "Could not find your app/etc/local.xml File";
read -ep "Location of local.xml file: " _file
SOURCEFILE=$_file
fi
if [ -f $SOURCEFILE ]
then
HOST=$($GREP -i host $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
PRE=$($GREP -i table_prefix $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
DBNAME=$($GREP -i dbname $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
DBPASS=$($GREP -i password $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
BIRTH=$($GREP -i date $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
DBUSER=$($GREP -i username $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
DBHOST=$($GREP -i host $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
FILE="database-optimized-$(date +%m-%d-%y-%H:%M).txt"
OK=$(echo -e "...      [32m[OK]");

BASEURL=$($MYSQL -u $DBUSER -p$DBPASS -h $HOST $DBNAME -e'SELECT value from '$PRE'core_config_data where path ="web/unsecure/base_url"'| $GREP -i http);
NUM=1;
##start the fun
clear
echo;
echo -e '=============================';
echo 'Using Database: '$DBNAME

echo -e 'Written by Brian Nelson @ http://briansnelson.com';
echo -e 'Download at http://briansnelson.com/stuff/magento-ce-db-optimize.script'
echo -e '=============================';
echo;
echo -e 'Setting maintenance.flag ..';
echo -e 'Setting Maintenacne Flag'$OK;
echo;
$TOUCH maintenance.flag

echo 'Optimization Tables ...';
echo -e '=============================';

echo;
echo -e 'Truncating Log tables ..';

for table in core_cache core_cache_option core_cache_tag  core_session log_customer log_quote log_summary log_summary_type log_url log_url_info log_visitor log_visitor_info log_visitor_online index_event index_process_event report_event report_viewed_product_index report_compared_product_index dataflow_batch_export dataflow_batch_import; do $MYSQL -u $DBUSER -h $HOST -p$DBPASS $DBNAME -e"truncate table $PRE$table;" >> ~/$FILE ; echo '['$NUM'/20] Truncated: '$PRE$table $OK;NUM=$(($NUM + 1)) ; done;

TABLES=$($MYSQL -u $DBUSER -p$DBPASS -h $HOST $DBNAME -e"show tables"| $GREP -v 'Tables_in')
#echo $TABLES
echo;

echo -e 'Repairing Tables ..';
for table in $TABLES; do $MYSQL -u $DBUSER -p$DBPASS -h $HOST $DBNAME -e"REPAIR TABLE $table" >> ~/$FILE ; done;
echo -e 'Repairing Tables: '$OK
echo;
echo -e 'Optimizing Tables ..';
for table in $TABLES; do $MYSQL -u $DBUSER -p$DBPASS -h $HOST $DBNAME -e"OPTIMIZE TABLE $table" >> ~/$FILE ; done;
echo -e 'Optimizing Table: '$OK
echo;
echo -e '=============================';
echo;
echo -e 'Removing maintenance.flag ..';
echo -e 'Removing Maintenance Flag'$OK;
$RM maintenance.flag
echo;
echo -e 'New Database Size ..';

echo;
echo 'Check Site to Make sure its Working...'
echo -e '=============================';
echo -e 'Click Here -> [34m'$BASEURL;
echo -e '[0m';
echo;
else
echo -e "Sorry could not find your local.xml file"
fi

