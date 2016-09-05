단체협약 데이터베이스
==================================

1. 소개
=======

노동조합들의 조직현황과 단체협약을 모아. 검색서비스를 제공합니다.

<img src="https://github.com/jinbonetwork/collective-agreement-database/blob/master/document/images/1.jpg?raw=true">

2. Requrement
=============

* PHP 5.4 이상.
  * phpredis module : https://github.com/phpredis/phpredis
* MySQL 5.7.6 이상.
* Mecab 최신버젼.
* npm(nodeJS)과 webpack이 global 설치되어 있어야 합니다.
* redis 3.2.3 이상
  * http://redis.io/

3. 설치
=======

단체협약데이터베이스 시스템은 다른 프로젝트들을 서브모듈로 가지고 있습니다. 따라서 git clone으로 소스를 다운받을 시, --recursive 옵션으로 설치하세요.

현재는 설치 페이지를 별도로 제공하지 않습니다.. 몇몇 단계로 나누어 수동으로 설치하셔야 합니다.

1) Apache 설정
--------------
* 이 시스템은 웹서버 rewrite module을 사용합니다. 이 사이트가 rewrite module을 사용할 수 있도록 웹서버 설정을 해주세요.

2) mysqlDB 생성
--------------
* 단체협약데이터베이스 시스템은 현재 MySQL 만 지원합니다.
* MySQL에 Mecab plugin을 설치하셔야 합니다.
* 사용하실 MySQL DB를 수동으로 만드셔야 합니다.
* install/sql/schema.sql 파일을 이용해서 새로 생성된 DB에 필요한 테이블을 생성합니다.

3) settings.php 생성
--------------------
* config 폴더에 있는 settings.dist.php 파일을 settings.php 로 복사한후, DB 접속정보와 도메인 정보등 필요한 정보를 수정/저장합니다.
* 정확한 settings.php 의 위치는 config/settings.php
* files 폴더를 웹서버가 접근할 수 있도록 707 권한 부여.

4) Gnuboard5 설치
-----------------
* gnu5 폴더에 data 폴더 생성. 웹서버가 접근할 수 있도록 707 권한 부여.
* http://domain/gnu5 에 접속하여 GNU5 보드 설치.
* G5_MYSQL_SET_MODE는 true로 설정(MySQL 5.6 부터는 MySQL query의 field 정확도를 강력하게 요구하는데, 아직 GNU5는 쿼리가 그렇지 못하다.)

5) react 코드를 위한 설정
-----------------------------------
* cd resource/react
* 'npm install' 실행하여 필요한 nodejs package 설치
* 'webpack' 실행.

6) TCPDF 한글 지원을 위한 한글폰트 세팅
-----------------------------------------
* 단협을 PDF 로 출력받기 위한 서비스를 위해 TCPDF 라이브러리를 이용합니다. 단 한글출력을 위한 한글웹폰트를 TCPDF에 추가합니다.
```
cd contribute/TCPDF/tools
ls ../../NanumBarunGothic/fonts/*.ttf | xargs -I TTF php tcpdf_addfont.php -i TTF
```

7) redis 설치
-------------
* redis 서버 설치.
```
$ /usr/local/src
$ wget http://download.redis.io/releases/redis-3.2.3.tar.gz
$ tar xzvpf redis-3.2.3.tar.gz
$ cd redis-3.2.3
$ make -j4 && make install -j4
$ cd utils
$ sh install_server.sh
```
* phpredis 설치
```
$ git clone https://github.com/phpredis/phpredis
$ cd phpredis
$ phpize
$ ./configure (--enable-redis-igbinary)
$ make -j4
$ make install
$ vim php.ini
extension=redis.so
```

4. document
===========

자세한 설명은 <a href="https://github.com/jinbonetwork/collective-agreement-database/wiki">위키</a> 참조

5. License
==========

아직 미정
