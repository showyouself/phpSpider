#!/bin/bash
for loop in {1..2}
do
	for loop in {1..10}
	do
		step=`tail /home/ben/work/swoole/spider/crontab/step.lock -n1`
		step=`expr "${step}" + "1"`
		`echo ${step} >> /home/ben/work/swoole/spider/crontab/step.lock`
		curl "127.0.0.1:9501?id=${step}"
	done
done
