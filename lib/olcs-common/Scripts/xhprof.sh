#!/bin/sh
git clone https://github.com/phacility/xhprof.git /workspace/xhprof
yum update
yum install php55w-devel || yum install php-devel
pecl install xhprof-beta
echo 'extension=xhprof.so
xhprof.output_dir=/tmp' | sudo tee --append /etc/php.d/xhprof.ini
service httpd restart
ln -s /workspace/xhprof /var/www/html/xhprof