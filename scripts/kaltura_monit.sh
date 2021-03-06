#! /bin/sh
#
# monit         Monitor Unix systems
#
# Author:	Clinton Work,   <work@scripty.com>
#
# chkconfig:    2345 98 02
# description:  Monit is a utility for managing and monitoring processes,
#               files, directories and filesystems on a Unix system. 
# processname:  monit
# pidfile:      /var/run/monit.pid
# config:       /etc/monit.conf

# Source networking configuration.
. /etc/sysconfig/network

SYSTEM_INI_FILE=/etc/kaltura.d/system.ini
if [ -r "$SYSTEM_INI_FILE" ];then
    . $SYSTEM_INI_FILE
else
    echo "I could not source $SYSTEM_INI_FILE. Exiting."
    exit 1
fi

# Source function library.
. $APP_DIR/scripts/functions.rc

MONIT="$BASE_DIR/bin/monit -c $APP_DIR/configurations/monit/monit.rc -l $LOG_DIR/kaltura_monit.log"

# Source monit configuration.
if [ -f /etc/sysconfig/monit ] ; then
        . /etc/sysconfig/monit
fi

[ -f $BASE_DIR/bin/monit ] || exit 0

RETVAL=0

# See how we were called.
case "$1" in
  start)
        echo -n "Starting monit: "
        daemon $NICELEVEL $MONIT
        RETVAL=$?
        echo
        [ $RETVAL = 0 ] && touch /var/lock/subsys/monit
        ;;
  stop)
        echo -n "Stopping monit: "
        killproc monit
        RETVAL=$?
        echo
        [ $RETVAL = 0 ] && rm -f /var/lock/subsys/monit
        ;;
  restart)
  	$0 stop
	$0 start
	RETVAL=$?
	;;
  condrestart)
       [ -e /var/lock/subsys/monit ] && $0 restart
       ;;
  status)
        status monit
	RETVAL=$?
	;;
  *)
	echo "Usage: $0 {start|stop|restart|condrestart|status}"
	exit 1
esac

exit $RETVAL
