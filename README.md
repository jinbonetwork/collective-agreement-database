단체협약 데이터베이스
==================================

1. 소개
=======

노동조합들의 조직현황과 단체협약을 모아. 검색서비스를 제공합니다.

<img src="https://github.com/jinbonetwork/collective-agreement-database/blob/master/document/images/1.jpg?raw=true">

2. Requrement
=============

* PHP 5.4 이상
* MySQL 5.7 이상.

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

4. document
===========

자세한 설명은 <a href="https://github.com/jinbonetwork/collective-agreement-database/wiki">위키</a> 참조

5. License
==========

아직 미정
