#!/bin/bash

usage() {
    if [ -n "$1" ]; then
        echo;
        echo Error : $1
    fi

    echo;
    echo Usage: generate-conf.sh [options];
    echo;
    echo "-c <file>       : bash file containing config";
    echo "-e <hostname>   : Elasticsearch server hostname";
    echo "-h <dbname>     : Database host";
    echo "-u <dbuser>     : Database user";
    echo "-p <dbpassword> : Database password";
    echo "-m <dbname>     : Database name";
    echo "-d <dirpath>    : Directory for configuration files to be saved to";
    echo "-i <index>      : Generate config for named index. Config files will be generated as '<INDEX_NAME>.conf'"
    echo
    exit;
}

log() {
    if [ "$logfile" == "" ]; then
        echo -e $(date) $1
    else
        echo $(date) >> $logfile
        echo -e $1 >> $logfile
    fi
}

while getopts "c:e:h:u:p:m:d:i:" opt; do
  case $opt in
    c)
        if [ ! -f $OPTARG ]; then
          usage "Config file $OPTARG doesn't exist";
        fi
        source $OPTARG
      ;;
    e)
        ELASTIC_HOST=$OPTARG
      ;;
    h)
        DBHOST=$OPTARG
      ;;
    u)
        DBUSER=$OPTARG
      ;;
    p)
        DBPASSWORD=$OPTARG
      ;;
    m)
        DBNAME=$OPTARG
      ;;
    d)
        DIRPATH=$OPTARG
      ;;
    i)
        INDEXES=(${INDEXES[@]} "${OPTARG}")
      ;;
    \?)
      usage "Invalid option: -$OPTARG";
      ;;
    :)
      usage "Option -$OPTARG requires an argument.";
      ;;
  esac
done

if [ -z "$ELASTIC_HOST" ]
then
    usage "-e parameter must be set"
    exit;
fi

if [ -z "$DBHOST" ]
then
    usage "-h parameter must be set"
    exit;
fi

if [ -z "$DBNAME" ]
then
    usage "-m parameter must be set"
    exit;
fi

if [ -z "$DBUSER" ]
then
    usage "-u parameter must be set"
    exit;
fi

if [ -z "$DBPASSWORD" ]
then
    usage "-p parameter must be set"
    exit;
fi

if [ -z "$DIRPATH" ]
then
    DIRPATH="/usr/share/logstash/pipeline/"
fi

if [ -z "$INDEXES" ]
then
    INDEXES=( "irfo" "busreg" "case" "application" "user" "licence" "psv_disc" "address" "person" "vehicle_current" "publication"  "vehicle_removed" )
fi

newVersion=$(date +%s) #timestamp

JDBC_LIBRARY=$(basename "`ls /opt/dvsa/olcs/mysql-connector*.jar`")
log "Replace placeholders in logstash config file(s)"
for INDEX in "${INDEXES[@]}"; do
  CONFFILE="${INDEX}.conf"
  cp /usr/share/logstash/config/populate_indices.conf.dist $DIRPATH/$CONFFILE
  sed "s/<JDBC_LIBRARY>/$JDBC_LIBRARY/" -i $DIRPATH/$CONFFILE
  sed "s/<DB_HOST>/$DBHOST/" -i $DIRPATH/$CONFFILE
  sed "s/<DB_NAME>/$DBNAME/" -i $DIRPATH/$CONFFILE
  sed "s/<DB_USER>/$DBUSER/" -i $DIRPATH/$CONFFILE
  sed "s/<DB_PASSWORD>/$DBPASSWORD/" -i $DIRPATH/$CONFFILE
  sed "s/<ES_HOST>/$ELASTIC_HOST/" -i $DIRPATH/$CONFFILE
  sed "s/<INDEX_VERSION>/$newVersion/" -i $DIRPATH/$CONFFILE
  sed "s#<LOGSTASH_PATH>#$LOGSTASH_PATH#" -i $DIRPATH/$CONFFILE
  sed "s/<INDEX_NAME>/$INDEX/" -i $DIRPATH/$CONFFILE
  chmod 644 $DIRPATH/$CONFFILE
done

log "Done"