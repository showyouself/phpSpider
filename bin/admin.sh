#!/bin/bash
#移动到运行命令
cd /home/ben/work/swoole/spider/bin
second_log=/home/ben/work/swoole/spider/log/second.log
process="main.php"

#接受信号
if [ $1 == "stop" ]
then
	`kill -9 $(ps -ef|grep $process|grep -v "grep"|awk '{print $2}')`
	`kill -9 $(ps -ef|grep "second"|grep -v "grep"|awk '{print $2}')`
	echo "stop success"
elif [ $1 == "start" ]
then	
	php main.php
	nohup ../crontab/second.sh >> $second_log 2>&1 &
	echo "start success"
elif [ $1 == "restart" ]
then 
	`kill -9 $(ps -ef|grep $process|grep -v "grep"|awk '{print $2}')`
	`kill -9 $(ps -ef|grep "second"|grep -v "grep"|awk '{print $2}')`
	php main.php
	nohup ../crontab/second.sh >> $second_log 2>&1 &
	echo "restart success"
elif [ $1 == "sh_restart" ]
then
	`kill -9 $(ps -ef|grep "second"|grep -v "grep"|awk '{print $2}')`
	nohup ../crontab/second.sh >> $second_log 2>&1 &
elif [ $1 == "status" ]
then 
	ps -ef|grep "main.php"
	ps -ef|grep "second"|grep -v "grep"
else
	echo "usage:<sh_restart|restart|start|stop>"
fi

