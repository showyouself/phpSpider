#!/bin/bash
#*/30 * * * * ~/work/swoole/spider/bin/admin.sh restart

typezzb=4
type70b=3
typecililian=5

D=`date  +%y%m%d-%k%M`
`echo "$D, start runing" >> /home/ben/work/swoole/spider/log/second.log` 

while :
do
		sleep 2
		step=`tail /home/ben/work/swoole/spider/crontab/step.lock -n1`
		while  [ "$step" == "" ]
		do	
			sed -i '$d' /home/ben/work/swoole/spider/crontab/step.lock
			step=`tail /home/ben/work/swoole/spider/crontab/step.lock -n1`
		done

		step=`expr "${step}" + "1"`
		result=`curl -s "127.0.0.1:9501?id=${step}&type=${typezzb}"`
#		result=`curl -s "127.0.0.1:9501?id=${step}&type=${type70b}"`
		result=`curl -s "127.0.0.1:9501?id=${step}&type=${typecililian}"`
		if [ "$result" == "" ]
		then
			echo -e "php main.php is down";
			sleep 10
			continue;
		fi

		result=${result#*\<}
		result=${result%>*}
		if [ $result -gt 3 ]
		then
			echo -e "tasking is full...wait 10 second;tasking:${result}"
			sleep 10;
		else
			`echo ${step} >> /home/ben/work/swoole/spider/crontab/step.lock`
		fi
done
