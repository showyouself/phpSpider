#!/bin/bash
D=`date  +%y%m%d-%k%M`
`echo "$D, start runing" >> /home/ben/work/swoole/spider/log/second.log` 

while :
do
		sleep 0.1
		step=`tail /home/ben/work/swoole/spider/crontab/step.lock -n1`
		while  [ "$step" == "" ]
		do	
			sed -i '$d' /home/ben/work/swoole/spider/crontab/step.lock
			step=`tail /home/ben/work/swoole/spider/crontab/step.lock -n1`
		done
		step=`expr "${step}" + "1"`
		`echo ${step} >> /home/ben/work/swoole/spider/crontab/step.lock`
		result=`curl -s "127.0.0.1:9501?id=${step}"`
		if [ "$result" == "" ]
		then
			echo -e "php main.php is down";
		fi

		result=${result#*\<}
		result=${result%>*}
		if [ $result -gt 0 ]
		then
			echo -e "tasking is full...wait 5 second;"
			sleep 5;
		fi
done
