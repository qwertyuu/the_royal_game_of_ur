#!/bin/sh
./wait-for-it.sh $DB_HOST:$DB_PORT -- /bin/sh /run.sh