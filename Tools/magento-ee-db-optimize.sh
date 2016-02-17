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

if [ -f $PWD/app/etc/local.xml ]; then
	SOURCEFILE=$PWD/app/etc/local.xml
else
	clear;
	echo  "Could not find your app/etc/local.xml File";
	read p "Location of local.xml file: " _file
	SOURCEFILE=$_file
fi

if [ -f $SOURCEFILE ]; then
	HOST=$($GREP -i host $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
	PRE=$($GREP -i table_prefix $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
	DBNAME=$($GREP -i dbname $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
	DBPASS=$($GREP -i password $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
	BIRTH=$($GREP -i date $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
	DBUSER=$($GREP -i username $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
	DBHOST=$($GREP -i host $SOURCEFILE | $CUT -d[ -f3 | $CUT -d] -f1)
	FILE="database-optimized-$(date +%m-%d-%y-%H:%M).txt"
	OK=$(echo  "...      [OK]");

	BASEURL=$($MYSQL -u $DBUSER -p$DBPASS -h $HOST $DBNAME 'SELECT value from '$PRE'core_config_data where path ="web/unsecure/base_url"'| $GREP -i http);
	NUM=1;
	##start the fun
	clear
	echo  '=============================';
	echo  'Using Database: '$DBNAME 
	echo  'Written by Brian Nelson @ http://briansnelson.com';
	echo  'Download at http://briansnelson.com/stuff/magento-db-optimize.script'
	echo  '=============================';
	echo  'Setting maintenance.flag ..';
	echo  'Setting Maintenacne Flag'$OK;
	$TOUCH maintenance.flag
	echo 'Optimization Tables ...';
	echo  '=============================';
	echo  'Truncating Log tables ..';

	for table in core_cache core_cache_option core_cache_tag  core_session log_customer log_quote log_summary log_summary_type log_url log_url_info log_visitor log_visitor_info log_visitor_online index_event index_process_event report_event report_viewed_product_index report_compared_product_index dataflow_batch_export dataflow_batch_import enterprise_logging_event enterprise_logging_event_changes; do
	    $MYSQL -u $DBUSER -h $HOST -p$DBPASS $DBNAME "truncate table $PRE$table;" >> ~/$FILE ;
	    echo '['$NUM'/22] Truncated: '$PRE$table $OK;NUM=$(($NUM + 1)) ;
    done;

	TABLES=$($MYSQL -u $DBUSER -p$DBPASS -h $HOST $DBNAME "show tables"| $GREP -v 'Tables_in')
	#echo $TABLES
	echo;

	echo  'Repairing Tables ..';
	for table in $TABLES; do $MYSQL -u $DBUSER -p$DBPASS -h $HOST $DBNAME "REPAIR TABLE $table" >> ~/$FILE ; done;
	echo  'Repairing Tables: '$OK
	echo;
	echo  'Optimizing Tables ..';
	for table in $TABLES; do $MYSQL -u $DBUSER -p$DBPASS -h $HOST $DBNAME "OPTIMIZE TABLE $table" >> ~/$FILE ; done;
	echo  'Optimizing Table: '$OK
	echo;
	echo  '=============================';
	echo;
	echo  'Removing maintenance.flag ..';
	echo  'Removing Maintenance Flag'$OK;
	$RM maintenance.flag
	echo;
	echo;
	echo 'Check Site to Make sure its Working...'
	echo  '=============================';
	echo  'Click Here -> '$BASEURL;
	echo  '';
	echo;
else
	echo  "Sorry could not find your local.xml file"
fi

