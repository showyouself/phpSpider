#!/bin/bash
for loop in {1..7}
do
	for loop in {1..50}
	do
		sleep 0.15
		step=`tail /home/ben/work/swoole/spider/crontab/step.lock -n1`
		step=`expr "${step}" + "1"`
		`echo ${step} >> /home/ben/work/swoole/spider/crontab/step.lock`
		curl "127.0.0.1:9501?id=${step}"
	done
	sleep 5;
done
