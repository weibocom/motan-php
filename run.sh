#!/usr/bin/env bash

### BEGIN ###
# Author: idevz
# Since: 2018/03/12
# Description:       Auto genertate PHPUnit Tests and phpt tests.
# ./run.sh                                see help
### END ###

# set -x

BASE_DIR=$(dirname $(cd $(dirname "$0") && pwd -P)/$(basename "$0"))
PHPUNIT_SKELGEN_EXECUTABLE=/usr/local/bin/phpunit-skelgen
PHPUNIT_EXECUTABLE=${BASE_DIR}/vendor/bin/phpunit
PHPUNIT_TEST_BOOT_STRAP=${BASE_DIR}/tests/bootstrap.php

PHP_EXECUTABLE=$(which php)

PHPT_SKELGEN_EXECUTABLE=${BASE_DIR}/phpts/generate-phpt
PHPT_EXECUTABLE=${BASE_DIR}/phpts/run-tests.php
# https://qa.php.net/phpt_details.php check phpt sections detail
DEFAULT_PHPT_SECTIONS='skipif:ini:clean:done'
PHPT_SECTIONS=${PTSEC:-${DEFAULT_PHPT_SECTIONS}}

new_ptest_4_cls_method() {
	local CLASS_FILE="${BASE_DIR}/phpts/bootstrap.php"
	local CLASS_NAME=$1
	local METHOD_NAME=$2
	local PHPT_FILE_NAME=$(echo ${CLASS_NAME} | sed 's/\\/_/g')/${METHOD_NAME}_basic.phpt
	${PHP_EXECUTABLE} -d output_buffering=0 -d memory_limit=-1 \
		-d auto_prepend_file=${CLASS_FILE} \
		${PHPT_SKELGEN_EXECUTABLE} \
		-c ${CLASS_NAME} -m ${METHOD_NAME} \
		-b -s ${PHPT_SECTIONS}
	mkdir -p $(dirname $BASE_DIR/phpts/${PHPT_FILE_NAME})
	mv ${CLASS_NAME}_${METHOD_NAME}_basic.phpt $BASE_DIR/phpts/${PHPT_FILE_NAME}
}

new_ptest_4_func_in_file() {
	local SRC_FILE=$1
	local FUNC_NAME=$2
	local PHPT_FILE_NAME=${FUNC_NAME}_basic.phpt
	${PHP_EXECUTABLE} -d open_basedir= -d output_buffering=0 -d memory_limit=-1 \
		-d auto_prepend_file=${SRC_FILE} \
		${PHPT_SKELGEN_EXECUTABLE} \
		-f ${FUNC_NAME} \
		-b -s ${PHPT_SECTIONS}
	mv ${PHPT_FILE_NAME} $BASE_DIR/phpts/${PHPT_FILE_NAME}
}

run_ptests() {
	local phpts_dir="${BASE_DIR}/phpts"
	[ ! -z ${1} ] && phpts_dir=${1}
	TEST_PHP_EXECUTABLE=$(which php) ${PHPT_EXECUTABLE} ${phpts_dir}
}

new_utest() {
	local CLASS_FILE=$1
	local CLASS_NAME=$(echo $CLASS_FILE | sed "s/\//\\\\/g" | sed "s/\.php//g")
	local TEST_CLASS_FILE=$(echo $CLASS_FILE | sed "s/\.php/Test\.php/g")
	${PHPUNIT_SKELGEN_EXECUTABLE} generate-test \
		--bootstrap=${TEST_BOOT_STRAP} ${CLASS_NAME} \
		${BASE_DIR}/src/${CLASS_FILE} \
		${CLASS_NAME}Test \
		${BASE_DIR}/tests/${TEST_CLASS_FILE}

	local NAME_SPACE=$(
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

new_all_utests() {
	for file in $(ls $1); do
		local SRC_FILE_OR_DIR=$1"/"${file}
		local BASE_SRC=${BASE_DIR}/src/
		if [ -d ${SRC_FILE_OR_DIR} ]; then
			new_all_utests ${SRC_FILE_OR_DIR}
		else
			local CLASS_FILE=$(echo ${SRC_FILE_OR_DIR} | sed 's/'$(echo ${BASE_SRC} | sed 's/\//\\\//g')'//g')
			if [ ! -z $(cat ${SRC_FILE_OR_DIR} | grep '<?php') ]; then
				if [ ! -z "$(cat ${SRC_FILE_OR_DIR} | grep -E '^class |^abstract class')" ]; then
					TEST_CLASS_FILE_DIR=$(echo $(dirname ${SRC_FILE_OR_DIR}) | sed 's/src/tests/g')
					if [ ! -d ${TEST_CLASS_FILE_DIR} ]; then
						mkdir -p ${TEST_CLASS_FILE_DIR}
					fi
					echo "\n\ntesting ${CLASS_FILE} \n"
					new_utest $CLASS_FILE
				fi
			fi
		fi
	done
}

show_help() {
	echo "
    ./run.sh                show this help
    ./run.sh -h             show this help


    ./run.sh nut            new unit test for php file, generate in tests/ dir. 
                            eg:
                            ./run.sh nut Motan/Client.php
                            please make sure the tests/Motan/... dirs are exist.
    ./run.sh naut           new php unit test for all file under src, just need run once.
    ./run.sh raut           run all unit tests of PHP UnitTest
    ./run.sh rutf           run unit test from unit test file of PHP Unit 
                            eg.
                            ./run.sh rutf tests/Motan/Serialize/MotanTest.php
							

    ./run.sh ncmpt          new phpt test for class methods
                            eg.
                            ./run.sh ncmpt 'Motan\URL' getMethod,... Methods
    ./run.sh nfpt           new phpt test for functions from a php file
    ./run.sh rpt            run phpt test under phpts dir or any dir have phpts
    "
}

if [ $# != 0 ]; then
	if [ $1 == "nut" ]; then
		if [ $# != 2 ]; then
			echo "err args num.
            ./run.sh nut class_name class_file_src test_file_src
			like this:
					./run.sh nut Motan/Client.php
            "
			exit 1
		fi
		new_utest $2
	elif [ $1 == "naut" ]; then
		TO_TEST_DIR=${BASE_DIR}/src
		if [ ! -z $2 ]; then
			TO_TEST_DIR=$2
		fi
		new_all_utests ${TO_TEST_DIR}
	elif [ $1 == "raut" ]; then
		${PHPUNIT_EXECUTABLE} --bootstrap=${PHPUNIT_TEST_BOOT_STRAP} \
			--testdox ${BASE_DIR}/tests \
			--coverage-html ${BASE_DIR}/tests/coverage/
	elif [ $1 == "rutf" ]; then
		${PHPUNIT_EXECUTABLE} --bootstrap=${PHPUNIT_TEST_BOOT_STRAP} $2
	elif [ $1 == "ncmpt" ]; then
		for METHOD in $(echo ${3//,/ }); do
			new_ptest_4_cls_method $2 ${METHOD}
		done
	elif [ $1 == "nfpt" ]; then
		for FUNC in $(echo ${3//,/ }); do
			new_ptest_4_func_in_file $2 ${FUNC}
		done
	elif [ $1 == "rpt" ]; then
		run_ptests $2
	elif [ $1 == "-h" ]; then
		show_help
	fi
else
	show_help
fi
