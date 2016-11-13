#!/bin/bash

step=$1

case $step in
    before_install)
    ;;
    install)
        composer install --optimize-autoloader;
    ;;
    script)
        vendor/bin/phpunit --testsuite ${TESTSUITE};
    ;;
esac
