#!/bin/sh
now="$(date +'%Y%m%d_%H%M%S')"
filename="db_file$now".sql.gz
backupfolder="/path/to/folder/back/db"
fullpathbackupfile="$backupfolder/$filename"
pathdump="/opt/mysql_client/mysql_80/bin/"
maxNumberFile=30

mysqlHost="127.0.0.1"
mysqlUser="root"
mysqlPass="password"
mysqlDb="db_name"

mkdir -p $backupfolder
"$pathdump"mysqldump --host=$mysqlHost --user=$mysqlUser --password=$mysqlPass \
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