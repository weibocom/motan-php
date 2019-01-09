#!/bin/sh

### BEGIN ###
# Author: idevz
# Since: 2018/03/12
# Description:       Run a Golang service with Weibo-Mesh.
# ./run.sh                                run hello world demo
# ./run.sh x                              clean the container and network
# ./run.sh -h                             show this help
### END ###

# set -e

BASE_DIR=$(dirname $(cd $(dirname "$0") && pwd -P)/$(basename "$0"))
PHPUNIT_SKELGEN_EXECUTABLE=/usr/local/bin/phpunit-skelgen
PHPUNIT_EXECUTABLE=${BASE_DIR}/vendor/bin/phpunit
TEST_BOOT_STRAP=${BASE_DIR}/tests/bootstrap.php
DEFAULT_EXCLUDE=''
EXCLUDE_FILE_OR_DIR=${EXCLUDE=$DEFAULT_EXCLUDE}

PHP_EXECUTABLE=php
PHPT_SKELGEN_EXECUTABLE=/usr/local/bin/generate-phpt

new_phpt() {
	CLASS_FILE=$1
	CLASS_FILE=$BASE_DIR/phpt_bootstrap.php
	CLASS_NAME=$2
	METHOD_NAME=$3
	PHPT_FILE_NAME=$(echo ${CLASS_NAME} | sed 's/\\/_/g')/${METHOD_NAME}_basic.phpt
	${PHP_EXECUTABLE} -d open_basedir= -d output_buffering=0 -d memory_limit=-1 \
		-d auto_prepend_file=${CLASS_FILE} \
		${PHPT_SKELGEN_EXECUTABLE} \
		-c ${CLASS_NAME} -m ${METHOD_NAME} \
		-b -s 'skipif:ini:clean:done'
	mkdir -p $(dirname $BASE_DIR/phpts/${PHPT_FILE_NAME})
	mv ${CLASS_NAME}_${METHOD_NAME}_basic.phpt $BASE_DIR/phpts/${PHPT_FILE_NAME}
}

new_phpft() {
	SRC_FILE=$1
	FUNC_NAME=$2
	PHPT_FILE_NAME=${FUNC_NAME}_basic.phpt
	${PHP_EXECUTABLE} -d open_basedir= -d output_buffering=0 -d memory_limit=-1 \
		-d auto_prepend_file=${SRC_FILE} \
		${PHPT_SKELGEN_EXECUTABLE} \
		-f ${FUNC_NAME} \
		-b -s 'skipif:ini:clean:done'
	mv ${PHPT_FILE_NAME} $BASE_DIR/phpts/${PHPT_FILE_NAME}
}

new_test() {
	CLASS_FILE=$1
	CLASS_NAME=$(echo $CLASS_FILE | sed "s/\//\\\\/g" | sed "s/\.php//g")
	TEST_CLASS_FILE=$(echo $CLASS_FILE | sed "s/\.php/Test\.php/g")
	${PHPUNIT_SKELGEN_EXECUTABLE} generate-test \
		--bootstrap=${TEST_BOOT_STRAP} ${CLASS_NAME} \
		${BASE_DIR}/src/${CLASS_FILE} \
		${CLASS_NAME}Test \
		${BASE_DIR}/tests/${TEST_CLASS_FILE}

	NAME_SPACE=$(
		cat ${BASE_DIR}/tests/${TEST_CLASS_FILE} |
			grep "namespace" | sed 's/namespace \(.*\);/\1/g' |
			sed 's/\\/\\\\/g'
	)'\\'
	if [ "$(uname -s)" = 'Darwin' ]; then
		sed -i '' 's/PHPUnit_Framework_TestCase/PHPUnit\\Framework\\TestCase/g' ${BASE_DIR}/tests/${TEST_CLASS_FILE} &&
			sed -i '' 's/  Motan\\/  \\Motan\\/g' ${BASE_DIR}/tests/${TEST_CLASS_FILE} &&
			sed -i '' "s/  ${NAME_SPACE}/  \\${NAME_SPACE}/g" ${BASE_DIR}/tests/${TEST_CLASS_FILE}
	else
		sed -i 's/PHPUnit_Framework_TestCase/PHPUnit\\Framework\\TestCase/g' ${BASE_DIR}/tests/${TEST_CLASS_FILE} &&
			sed -i 's/  Motan\\/  \\Motan\\/g' ${BASE_DIR}/tests/${TEST_CLASS_FILE} &&
			sed -i "s/  ${NAME_SPACE}/  \\${NAME_SPACE}/g" ${BASE_DIR}/tests/${TEST_CLASS_FILE}
	fi
}

new_all_test() {
	for file in $(ls $1); do
		SRC_FILE_OR_DIR=$1"/"${file}
		BASE_SRC=${BASE_DIR}/src/
		if [ -d ${SRC_FILE_OR_DIR} ]; then
			new_all_test ${SRC_FILE_OR_DIR}
		else
			CLASS_FILE=$(echo ${SRC_FILE_OR_DIR} | sed 's/'$(echo ${BASE_SRC} | sed 's/\//\\\//g')'//g')
			if [ ! -z $(cat ${SRC_FILE_OR_DIR} | grep '<?php') ]; then
				NEED_CONTINUE=0
				for exclude in ${EXCLUDE_FILE_OR_DIR}; do
					if [ ! -z "$(echo ${SRC_FILE_OR_DIR} | grep ${exclude})" ]; then
						NEED_CONTINUE=1
					fi
				done
				if [ ${NEED_CONTINUE} -eq 1 ]; then
					continue
				fi
				if [ ! -z "$(cat ${SRC_FILE_OR_DIR} | grep -E '^class |^abstract class')" ]; then
					TEST_CLASS_FILE_DIR=$(echo $(dirname ${SRC_FILE_OR_DIR}) | sed 's/src/tests/g')
					if [ ! -d ${TEST_CLASS_FILE_DIR} ]; then
						mkdir -p ${TEST_CLASS_FILE_DIR}
					fi
					echo "\n\ntesting ${CLASS_FILE} \n"
					new_test $CLASS_FILE
				fi
			fi
		fi
	done
}

# phpunit-skelgen generate-test --bootstrap=./tests/bootstrap.php   Motan\\Client ./src/Motan/Client.php Motan\\ClientTest ./tests/Motan/ClientTest.php
# php generate-phpt.php  -f <function_name> |-c <class_name> -m <method_name> -b|e|v [-s skipif:ini:clean:done] [-k win|notwin|64b|not64b] [-x ext]
# php /Users/idevz/code/src/php-5.3.27/scripts/dev/generate-phpt.phar -f sin -b -s 'skipif:ini:clean:done'
# TEST_PHP_EXECUTABLE=/usr/local/opt/php@7.1/bin/php php $MCODE/src/php-7.2.5/run-tests.php

show_help() {
	echo "
    ./run.sh                                show this help
    ./run.sh nt                             new php unit test for class like 
        ./run.sh nt Motan/Client.php

    ./run.sh nat                            clean the container and network
    ./run.sh rt                             run all tests of PHP Unit
    ./run.sh rtf                            run test file of PHP Unit like 
        ./run.sh rtf tests/Motan/Serialize/MotanTest.php

    ./run.sh npt                            new phpt test for class method
        ./run.sh npt ./src/Motan/URL.php 'Motan\URL' getMethod

    ./run.sh npft                           new phpt test for function
    ./run.sh -h                             show this help
    "
}

if [ $# != 0 ]; then
	if [ $1 == "nt" ]; then
		if [ $# != 2 ]; then
			echo "err args num.
            ./run.sh nt class_name class_file_src test_file_src
            "
			exit 1
		fi
		new_test $2
	elif [ $1 == "nat" ]; then
		TO_TEST_DIR=${BASE_DIR}/src
		if [ ! -z $2 ]; then
			TO_TEST_DIR=$2
		fi
		new_all_test ${TO_TEST_DIR}
	elif [ $1 == "rt" ]; then # run all tests of PHP Unit
		${PHPUNIT_EXECUTABLE} --bootstrap=${TEST_BOOT_STRAP} --testdox ${BASE_DIR}/tests
	elif [ $1 == "rtf" ]; then # run test file of PHP Unit like "tests/Motan/Serialize/MotanTest.php"
		${PHPUNIT_EXECUTABLE} --bootstrap=${TEST_BOOT_STRAP} $2
	elif [ $1 == "npt" ]; then
		new_phpt $2 $3 $4
	elif [ $1 == "npft" ]; then
		new_phpft $2 $3
	elif [ $1 == "-h" ]; then
		show_help
	fi
else
	show_help
fi
