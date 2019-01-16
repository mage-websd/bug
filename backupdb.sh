#!/bin/sh
now="$(date +'%Y%m%d_%H%M%S')"
filename="db_intranet_$now".sql.gz
backupfolder="/app/intranet/shared/backup/db"
fullpathbackupfile="$backupfolder/$filename"
maxNumberFile=7

mysqlHost="127.0.0.1"
mysqlUser="dev"
mysqlPass="dev@intranet"
mysqlDb="live_intranet_db"

mkdir -p $backupfolder
mysqldump --user=$mysqlUser --password=$mysqlPass \
    --default-character-set=utf8 $mysqlDb | gzip > "$fullpathbackupfile"

fileCount="$(ls -l $backupfolder/*.sql.gz | wc -l)"
if [ $fileCount -gt $maxNumberFile ]; then
    numberDelete="$(expr $fileCount - $maxNumberFile)"
    listFileDelete="$(ls $backupfolder | head -$numberDelete)"
    for file in $listFileDelete;
    do
        rm -f "$backupfolder/$file"
    done
fi

exit 0