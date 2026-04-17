#! /bin/env bash

#####################################################################
#                                                                   #
# anonymise generic answers                         			    #
#                                                                   #
#####################################################################

connection=$1
ANON_DB=$2
ANON_DATA_DIR=$3
ANSWERS_DATA_FILE=$4
ANSWERS_ANON_DATA_FILE=$5

log () {
printf "\n%s %s\n" "$(date "+%Y-%m-%d %H:%M:%S")" "$1"
}

log_error () {
log "$1"
exit 1
}

get_data() {

conn=$1
mysql --local-infile=1 -sNB $conn -e "SELECT * FROM $ANON_DB.answer ;" | tr '^' ' ' | tr '\t' '^' | sed -e 's/NULL//g' > $ANON_DATA_DIR/$ANSWERS_DATA_FILE
}

anonymise_answer () {

declare -A ans_string
declare -A ans_text

[ -e $ANON_DATA_DIR/$ANSWERS_ANON_DATA_FILE ] && rm $ANON_DATA_DIR/$ANSWERS_ANON_DATA_FILE

OLDIFS=$IFS

while IFS=$'^' read id question_text_id irhp_application_id irhp_permit_application_id ans_integer ans_string ans_decimal ans_date ans_datetime ans_boolean ans_text ans_array created_by last_modified_by created_on last_modified_on version ; do

Text=

if [[ ! -z $ans_text ]]; then
    Text="Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo"
fi


printf "%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n" "$id" "$question_text_id" "$irhp_application_id" "$irhp_permit_application_id" "$ans_integer" "$ans_string" "$ans_decimal" "$ans_date" "$ans_datetime" "$ans_boolean" "$Text" "$ans_array" "$created_by" "$last_modified_by" "$created_on" "$last_modified_on" "$version"

done < $ANON_DATA_DIR/$ANSWERS_DATA_FILE >> $ANON_DATA_DIR/$ANSWERS_ANON_DATA_FILE || log_error "anonymise_answers FAILED!"

iconv -f utf-8 -t utf-8 -c  $ANON_DATA_DIR/$ANSWERS_ANON_DATA_FILE >  $ANON_DATA_DIR/tmp-$ANSWERS_ANON_DATA_FILE
mv $ANON_DATA_DIR/tmp-$ANSWERS_ANON_DATA_FILE  $ANON_DATA_DIR/$ANSWERS_ANON_DATA_FILE

IFS=$OLDIFS
}

reload_answer ()
{

connection=$1

mysql --local-infile=1 $connection  --show-warnings -vve "use $ANON_DB;

SET FOREIGN_KEY_CHECKS = 0;
SET @DISABLE_TRIGGERS = 1;
truncate table answer;
SET FOREIGN_KEY_CHECKS = 1;

LOAD DATA LOCAL INFILE '$ANON_DATA_DIR/$ANSWERS_ANON_DATA_FILE' INTO table answer FIELDS TERMINATED BY '\t'
( id
,@question_text_id
,@irhp_application_id
,@irhp_permit_application_id
,@ans_integer
,@ans_string
,@ans_decimal
,@ans_date
,@ans_datetime
,@ans_boolean
,@ans_text
,@ans_array
,@created_by
,@last_modified_by
,@created_on
,@last_modified_on
,@version
)
SET question_text_id=nullif(@question_text_id,'')
,irhp_application_id=nullif(@irhp_application_id,'')
,irhp_permit_application_id=nullif(@irhp_permit_application_id,'')
,\`ans_integer\`=nullif(@ans_integer,'')
,\`ans_string\`=nullif(@ans_string,'')
,\`ans_decimal\`=nullif(@ans_decimal,'')
,\`ans_date\`=nullif(@ans_date,'')
,\`ans_datetime\`=nullif(@ans_datetime,'')
,ans_boolean=nullif(@ans_boolean,'')
,ans_text=nullif(@ans_text,'')
,ans_array=nullif(@ans_array,'')
,created_by=nullif(@created_by,'')
,last_modified_by=nullif(@last_modified_by,'')
,created_on=nullif(@created_on,'')
,last_modified_on=nullif(@last_modified_on,'')
,version=nullif(@version,'');


SET @DISABLE_TRIGGERS = null;" || log_error "reload_answer FAILED!"

}

get_data "$connection"
anonymise_answer
reload_answer "$connection"
