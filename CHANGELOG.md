# Changelog

## [5.15.4-rc1](https://github.com/dvsa/vol-app/compare/v5.15.3-rc1...v5.15.4-rc1) (2025-03-13)


### Bug Fixes

* another update .gitignore ([#701](https://github.com/dvsa/vol-app/issues/701)) ([103af16](https://github.com/dvsa/vol-app/commit/103af169874ce35ee656f60511f352674aaeca15))

## [5.15.3-rc1](https://github.com/dvsa/vol-app/compare/v5.15.2-rc1...v5.15.3-rc1) (2025-03-12)


### Bug Fixes

* update .gitignore ([#698](https://github.com/dvsa/vol-app/issues/698)) ([b6dedee](https://github.com/dvsa/vol-app/commit/b6dedeeea5af20236ea95349fd7fc23ba472384c))

## [5.15.2-rc1](https://github.com/dvsa/vol-app/compare/v5.15.1-rc1...v5.15.2-rc1) (2025-03-12)


### Bug Fixes

* trigger bump ([#695](https://github.com/dvsa/vol-app/issues/695)) ([240daca](https://github.com/dvsa/vol-app/commit/240daca125d834b7f63896e0572fa3c2b7d8869d))

## [5.15.1-rc1](https://github.com/dvsa/vol-app/compare/v5.15.0-rc1...v5.15.1-rc1) (2025-03-10)


### Bug Fixes

* update .gitignore for release please pr ([dab7272](https://github.com/dvsa/vol-app/commit/dab72722dc80b602c9cc7d1632366af621589e11))

## [5.15.0-rc1](https://github.com/dvsa/vol-app/compare/v5.14.2-rc1...v5.15.0-rc1) (2025-03-06)


### Features

* add zip ext as some filters used require it ([e38c619](https://github.com/dvsa/vol-app/commit/e38c619a1dfe0eb7559dba473cbab6795c7d8642))

## [5.14.2-rc1](https://github.com/dvsa/vol-app/compare/v5.14.1...v5.14.2-rc1) (2025-02-24)


### Features

* 5908 added ecs dashboard ([#633](https://github.com/dvsa/vol-app/issues/633)) ([c1f1d05](https://github.com/dvsa/vol-app/commit/c1f1d05ebd3a78ec75b0f456535a485cfb8f5850))
* 5912 batch dashboard - failed job count graph ([#624](https://github.com/dvsa/vol-app/issues/624)) ([82f20bd](https://github.com/dvsa/vol-app/commit/82f20bd0faf670a6f207ead0bf80b189044eb83b))
* add prerelase helper scripts ([#665](https://github.com/dvsa/vol-app/issues/665)) ([4022748](https://github.com/dvsa/vol-app/commit/402274836ca0cd72b41dffbca56e3aa4f02ab2f8))
* compatibility with twig version 3 ([#635](https://github.com/dvsa/vol-app/issues/635)) ([9e1eaa4](https://github.com/dvsa/vol-app/commit/9e1eaa40e46af01e4b94f7f53fd1baa6af19cb8c))
* internal users can't delete/modify last operator admin VOL-5918 VOL-4718 ([#628](https://github.com/dvsa/vol-app/issues/628)) ([4b7d88f](https://github.com/dvsa/vol-app/commit/4b7d88faf575071f937c0e0d6f34bddd18512358))
* use native arm runners for docker builds for performance and avoid segfault error ([#618](https://github.com/dvsa/vol-app/issues/618)) ([ee4eb6a](https://github.com/dvsa/vol-app/commit/ee4eb6a3e1f10b4d33bfbabea4f6278d7201100c))


### Bug Fixes

* 5908 fix metrics ([#642](https://github.com/dvsa/vol-app/issues/642)) ([bd4d06d](https://github.com/dvsa/vol-app/commit/bd4d06ddcfadfe6a4096bb17d0c9bc900d146820))
* 5908 service dashboard final ([#646](https://github.com/dvsa/vol-app/issues/646)) ([27bf866](https://github.com/dvsa/vol-app/commit/27bf8667d1b98114490d4b7408493eb76b7d4c17))
* 5908 service dashboard metric object ([#644](https://github.com/dvsa/vol-app/issues/644)) ([0ea981b](https://github.com/dvsa/vol-app/commit/0ea981b1a5be324b4a8a4c0e19c9dd101d773efe))
* 5908 service dashboard metrics again ([#645](https://github.com/dvsa/vol-app/issues/645)) ([418a8d4](https://github.com/dvsa/vol-app/commit/418a8d488cc26f6d6a040bf033921877fe0b92e7))
* 5912 batch dashboard target name ([#622](https://github.com/dvsa/vol-app/issues/622)) ([1b2dc11](https://github.com/dvsa/vol-app/commit/1b2dc11740700ded6501b8d736fb4e327aa73708))
* add schedule to int queue jobs ([#637](https://github.com/dvsa/vol-app/issues/637)) ([c425a15](https://github.com/dvsa/vol-app/commit/c425a15ba5a0d3bee56f1ef93c8c884c9b4abfdb))
* add schedule to int queue jobs ([#643](https://github.com/dvsa/vol-app/issues/643)) ([187c5a9](https://github.com/dvsa/vol-app/commit/187c5a92a9920c6114776fed6a01ece911e78190))
* batch failures log group policy ([#641](https://github.com/dvsa/vol-app/issues/641)) ([a59da34](https://github.com/dvsa/vol-app/commit/a59da34bee508fe7ad3185334f81963353b1c4d0))
* batch schedule per env names ([#638](https://github.com/dvsa/vol-app/issues/638)) ([4b3e6e2](https://github.com/dvsa/vol-app/commit/4b3e6e24e577127850af41bdcce6e55d9c9fa1d1))
* re add schedules ([#649](https://github.com/dvsa/vol-app/issues/649)) ([ab17c87](https://github.com/dvsa/vol-app/commit/ab17c87d2a33031a49fa67d3c51b28994867e13d))
* remove documentation ([#656](https://github.com/dvsa/vol-app/issues/656)) ([0985eb2](https://github.com/dvsa/vol-app/commit/0985eb2fac4cbc32b39ca655c0da6656eb98db05))
* transfer VRM filter now loaded correctly from filter manager VOL-5975 ([#647](https://github.com/dvsa/vol-app/issues/647)) ([35a6b92](https://github.com/dvsa/vol-app/commit/35a6b92b11e5281b7b96a51874981c86a54f98f2))
* try creating EBSR sub-tmp folder if not exists ([#650](https://github.com/dvsa/vol-app/issues/650)) ([bcaacdb](https://github.com/dvsa/vol-app/commit/bcaacdbd132b9debcf7104d9e316a911929e47b0))


### Miscellaneous Chores

* release v5.14.2-rc1 ([8914d27](https://github.com/dvsa/vol-app/commit/8914d270b62f10f833bbf1e0afadada0d05044c2))

## [5.14.1](https://github.com/dvsa/vol-app/compare/v5.14.0...v5.14.1) (2025-02-07)


### Bug Fixes

* check for uploaded supporting evidence now works correctly ([#615](https://github.com/dvsa/vol-app/issues/615)) ([ab92844](https://github.com/dvsa/vol-app/commit/ab92844d56bbf9f09b5b5085d1a9519775a59721))

## [5.14.0](https://github.com/dvsa/vol-app/compare/v5.13.1...v5.14.0) (2025-02-06)


### Features

* 5910 - updated dev job schedules ([#561](https://github.com/dvsa/vol-app/issues/561)) ([0dec628](https://github.com/dvsa/vol-app/commit/0dec628250b885cffd22612522bfaf09ffa5a24e))
* 5912 batch cloudwatch dashboard - initial ([#595](https://github.com/dvsa/vol-app/issues/595)) ([2c4f12c](https://github.com/dvsa/vol-app/commit/2c4f12cafa8bf9dde69058cd41d9aa43bb309062))
* 5912 improved monitoring dashboard with additional metrics ([#599](https://github.com/dvsa/vol-app/issues/599)) ([6b76648](https://github.com/dvsa/vol-app/commit/6b76648c7fc80477915ed5bf25f8998719358c57))
* 5950 build logstash container image pipeline ([#548](https://github.com/dvsa/vol-app/issues/548)) ([6e9ab18](https://github.com/dvsa/vol-app/commit/6e9ab186afa29cd9aefe1fd67d5a77c72fb1d68a))
* better approach to versioning etl containers and only building where necessary ([#601](https://github.com/dvsa/vol-app/issues/601)) ([65c8bda](https://github.com/dvsa/vol-app/commit/65c8bda6dca88c1912098c34e98d65f4a5277874))
* compatibility with erru schema 3.4 VOL-5817 ([#576](https://github.com/dvsa/vol-app/issues/576)) ([acd73b1](https://github.com/dvsa/vol-app/commit/acd73b106c68fc26491a713fec9cdb03ffa66b64))
* integrate liquibase runs into CD pipeline ([#553](https://github.com/dvsa/vol-app/issues/553)) ([f434069](https://github.com/dvsa/vol-app/commit/f4340694be60936ade2e0e1545068ae62dd473db))
* run smoke on int first ([#569](https://github.com/dvsa/vol-app/issues/569)) ([e13da65](https://github.com/dvsa/vol-app/commit/e13da657b77b381fd6634dc0aaa018cdee1778bd))
* show success banner on document upload ([#600](https://github.com/dvsa/vol-app/issues/600)) ([0519317](https://github.com/dvsa/vol-app/commit/0519317e429deb415d2083123809113174cbea1f))
* show success banner on document upload ([#608](https://github.com/dvsa/vol-app/issues/608)) ([d380a03](https://github.com/dvsa/vol-app/commit/d380a032954be151f6166feea855cf594b45450f))
* sort liquibase tags ([#606](https://github.com/dvsa/vol-app/issues/606)) ([5d63f45](https://github.com/dvsa/vol-app/commit/5d63f450d6127629c7845282ca6e88f834cb74b5))


### Bug Fixes

* 5588 containers missing tag fix ([#557](https://github.com/dvsa/vol-app/issues/557)) ([08e8d0b](https://github.com/dvsa/vol-app/commit/08e8d0b05c8071932396cb220a396c7c208794b0))
* 5588 missing tag ([#559](https://github.com/dvsa/vol-app/issues/559)) ([8359512](https://github.com/dvsa/vol-app/commit/8359512d0b004c1213d24b4613fe311d3bea6890))
* 5912 batch dashboard widget formatting ([#596](https://github.com/dvsa/vol-app/issues/596)) ([f838316](https://github.com/dvsa/vol-app/commit/f8383167e905520195981becd19fd231033e995a))
* 5912 metric filter name ([#605](https://github.com/dvsa/vol-app/issues/605)) ([57dbe1a](https://github.com/dvsa/vol-app/commit/57dbe1ad34652f4227f4136a444fd4801bd3ffbd))
* add permissions and secrets for prep rollback ([#571](https://github.com/dvsa/vol-app/issues/571)) ([34342d6](https://github.com/dvsa/vol-app/commit/34342d67752292d3f562bf1021035d6e5acb5f69))
* add tf to liquibase step dependencies ([#584](https://github.com/dvsa/vol-app/issues/584)) ([87334b1](https://github.com/dvsa/vol-app/commit/87334b11c576f46f94e7af5e07dbce7c1340ba1e))
* adding check to build-push docker ([#568](https://github.com/dvsa/vol-app/issues/568)) ([e73ab41](https://github.com/dvsa/vol-app/commit/e73ab41943dd8434d411cedf3640129fc4f8b90a))
* another formatting fix ([#598](https://github.com/dvsa/vol-app/issues/598)) ([fb76131](https://github.com/dvsa/vol-app/commit/fb76131c8c7b77043c34067c8664ae4d5433c1e4))
* batch tf issues ([#585](https://github.com/dvsa/vol-app/issues/585)) ([71922e9](https://github.com/dvsa/vol-app/commit/71922e9807c3299d8320e3bbb0457766f0a5ceec))
* better liquibase tagging strategy ([#603](https://github.com/dvsa/vol-app/issues/603)) ([c2157c4](https://github.com/dvsa/vol-app/commit/c2157c450c00a319b325706487810294a5be4099))
* change now secret arn is constructed to fix error seen when runn… ([#575](https://github.com/dvsa/vol-app/issues/575)) ([b596851](https://github.com/dvsa/vol-app/commit/b596851cc65939b36888fa76bf913d0b2b4d27be))
* error handling for registration ([#558](https://github.com/dvsa/vol-app/issues/558)) ([3ff5ad4](https://github.com/dvsa/vol-app/commit/3ff5ad4b1f0c5eda135c1e11a54c1c28b4e5f024))
* error handling for SS registration ([#586](https://github.com/dvsa/vol-app/issues/586)) ([db95037](https://github.com/dvsa/vol-app/commit/db950373bd803364579482dba894aa09d1a6b689))
* fix arn format for db secret ([#612](https://github.com/dvsa/vol-app/issues/612)) ([7963b32](https://github.com/dvsa/vol-app/commit/7963b32724983cd17f9567305671b1089d1c284a))
* fix db arn value in batch job definition ([#610](https://github.com/dvsa/vol-app/issues/610)) ([975e4fc](https://github.com/dvsa/vol-app/commit/975e4fc54f9b14b318004add4069902dde2a89b0))
* formatting of tm birth date on application overview ([#591](https://github.com/dvsa/vol-app/issues/591)) ([f14a88a](https://github.com/dvsa/vol-app/commit/f14a88a7fdf569b2c59eb31972ee7e4b1caec02c))
* incorrect secret arn ([#564](https://github.com/dvsa/vol-app/issues/564)) ([6117b4c](https://github.com/dvsa/vol-app/commit/6117b4c4a7c3043ec50223fe4ba8ffbb1fe7c9d1))
* liquibase job queue and definition tweaks ([#549](https://github.com/dvsa/vol-app/issues/549)) ([4e78843](https://github.com/dvsa/vol-app/commit/4e788437e05e2ba13e926d71a4b4134bc663765c))
* liquibase step dependencies ([#565](https://github.com/dvsa/vol-app/issues/565)) ([90f8dd9](https://github.com/dvsa/vol-app/commit/90f8dd91d1d5349a21d7ce4bae2719a1c884f4e6))
* only build liquibase once per run - submit batch for subsequent 3 ([#587](https://github.com/dvsa/vol-app/issues/587)) ([4f32ef7](https://github.com/dvsa/vol-app/commit/4f32ef75e0b05c72fa0cdac048971b1ae76895cd))
* pass repo secret to called workflow ([#560](https://github.com/dvsa/vol-app/issues/560)) ([e2709c6](https://github.com/dvsa/vol-app/commit/e2709c6a6cb897c3c0e163f8f8dc7af9b8136f0c))
* remove param being passed to workflow_call that was only valid for workflow_dispatch ([#556](https://github.com/dvsa/vol-app/issues/556)) ([e0f8202](https://github.com/dvsa/vol-app/commit/e0f8202c7775b30b1e12d645fba7295d9a4dce40))
* set job command default option ([#592](https://github.com/dvsa/vol-app/issues/592)) ([223ea40](https://github.com/dvsa/vol-app/commit/223ea40c11a453f1e1e9bf67c65b1f9f58962596))
* set share identifier to volapp as advised ([#563](https://github.com/dvsa/vol-app/issues/563)) ([b5fdc14](https://github.com/dvsa/vol-app/commit/b5fdc1440b6cf76912e7fb3f7231b6d0f5355c8e))
* specify scheduling params for liquibase batch job ([#562](https://github.com/dvsa/vol-app/issues/562)) ([3d05d72](https://github.com/dvsa/vol-app/commit/3d05d72c2f42f6612c5ef2ca1ae3dbf0a3f3abdd))
* spelling ([#567](https://github.com/dvsa/vol-app/issues/567)) ([b4f0d58](https://github.com/dvsa/vol-app/commit/b4f0d5871d9e4a5dca605bcb48a4a36a1878eb2f))
* test deployment ([#566](https://github.com/dvsa/vol-app/issues/566)) ([0d53702](https://github.com/dvsa/vol-app/commit/0d53702712be26ab32b2f5d1d947045c86954878))
* try stop liquibase being skipped despite terraform-env-dev completing ([7963b32](https://github.com/dvsa/vol-app/commit/7963b32724983cd17f9567305671b1089d1c284a))
* try stop liquibase being skipped despite terraform-env-dev completing ([#594](https://github.com/dvsa/vol-app/issues/594)) ([f542788](https://github.com/dvsa/vol-app/commit/f5427887243f159b883c048db34a85e1b079187b))
* typo in called workflow filename ([#554](https://github.com/dvsa/vol-app/issues/554)) ([327b833](https://github.com/dvsa/vol-app/commit/327b833a27b708856d4845c2d927522078e3d42f))

## [5.13.1](https://github.com/dvsa/vol-app/compare/v5.13.0...v5.13.1) (2025-01-13)


### Miscellaneous Chores

* release 5.13.1 ([#546](https://github.com/dvsa/vol-app/issues/546)) ([294488c](https://github.com/dvsa/vol-app/commit/294488c6a80d638a5efd0df5baf2d4f284420bd7))

## [5.13.0](https://github.com/dvsa/vol-app/compare/v5.12.1...v5.13.0) (2025-01-10)


### Features

* 5954 liquibase batch job definition ([#537](https://github.com/dvsa/vol-app/issues/537)) ([30c76cb](https://github.com/dvsa/vol-app/commit/30c76cbe8a52e4508aee10eca67663ff50b27826))
* add prerlease check before prod deploy, split regression into selfserve/internal steps ([#534](https://github.com/dvsa/vol-app/issues/534)) ([fee2674](https://github.com/dvsa/vol-app/commit/fee26745cf8a61b061e47d4a2413e778aad0272f))
* **api:** adding OAuth2 setup for INR calls ([#533](https://github.com/dvsa/vol-app/issues/533)) ([5985b43](https://github.com/dvsa/vol-app/commit/5985b43dd8b561f4a67dabfdf428397b56958045))
* RFC on proposed release-please config changes, to support RC releases up to PREP ([#524](https://github.com/dvsa/vol-app/issues/524)) ([30e09c6](https://github.com/dvsa/vol-app/commit/30e09c653c3265eeb9bacbcead9393d8b1792ca8))


### Bug Fixes

* change extra places where spellchecker language not defaulting to en_GB VOL-5907 ([#532](https://github.com/dvsa/vol-app/issues/532)) ([34e2c7f](https://github.com/dvsa/vol-app/commit/34e2c7fe54f77c863355e2049d91119672c91652))
* fixes path string being added to CSV uploaded/emailed to dft ([#538](https://github.com/dvsa/vol-app/issues/538)) ([4972164](https://github.com/dvsa/vol-app/commit/497216480b93e31ca938a44244b886e107d3e8b7))
* operator admin checks now account for disabled users VOL-5959 ([#528](https://github.com/dvsa/vol-app/issues/528)) ([b1096ab](https://github.com/dvsa/vol-app/commit/b1096abef2d4b20905c029eadb46a0772b77dddc))
* penalty info now displayed for erru cases VOL-5976 ([#536](https://github.com/dvsa/vol-app/issues/536)) ([bc8ecf3](https://github.com/dvsa/vol-app/commit/bc8ecf3b821e2a1de3057cc106b93e5ba1e065b1))
* remove &lt;/p&gt; being added to end of usernames ([#521](https://github.com/dvsa/vol-app/issues/521)) ([635cefe](https://github.com/dvsa/vol-app/commit/635cefea83f7f84cc64ec3d40b584e9c58968a88))
* remove conditionals ([#541](https://github.com/dvsa/vol-app/issues/541)) ([3f931e8](https://github.com/dvsa/vol-app/commit/3f931e879139b4d3a44a9ff09c7f000ab6b654e0))
* secret arn ([#539](https://github.com/dvsa/vol-app/issues/539)) ([8121f82](https://github.com/dvsa/vol-app/commit/8121f8248342b5fe220dab0b1a5445828c04f4e4))
* snyk scanning and errors fixed ([#523](https://github.com/dvsa/vol-app/issues/523)) ([b698d12](https://github.com/dvsa/vol-app/commit/b698d12bb1f14734ecbededf4fb6a1e15c36a129))
* validation added when loading safety inspectors VOL-5982 ([#542](https://github.com/dvsa/vol-app/issues/542)) ([2df7d1a](https://github.com/dvsa/vol-app/commit/2df7d1aa128cb29aa634bf86937b2b25f482bdb4))
* vol 5955 operators no admin role toggle off ([#531](https://github.com/dvsa/vol-app/issues/531)) ([bc869e2](https://github.com/dvsa/vol-app/commit/bc869e2dc1f6b15ab0be577b9712ec9521ccecf9))

## [5.12.1](https://github.com/dvsa/vol-app/compare/v5.12.0...v5.12.1) (2024-12-18)


### Features

* get token for olcs-etl checkout from new github app ([#514](https://github.com/dvsa/vol-app/issues/514)) ([db9a36d](https://github.com/dvsa/vol-app/commit/db9a36da6ea9c662065be0e35c6f9b1cd6da6b78))


### Bug Fixes

* add liquibase repo to ecr ([#516](https://github.com/dvsa/vol-app/issues/516)) ([9f7fc59](https://github.com/dvsa/vol-app/commit/9f7fc59a62f03b200e2d407ef769b86cddaa3996))
* correct email address in TC registration emails VOL-5963 ([#517](https://github.com/dvsa/vol-app/issues/517)) ([3b61cf1](https://github.com/dvsa/vol-app/commit/3b61cf105111dbc69ead5e1b2480a676c8678f56))


### Miscellaneous Chores

* release 5.12.1 ([#520](https://github.com/dvsa/vol-app/issues/520)) ([6d46ad4](https://github.com/dvsa/vol-app/commit/6d46ad45372563e60d3c34ac5bbc509467a7d658))

## [5.12.0](https://github.com/dvsa/vol-app/compare/v5.11.0...v5.12.0) (2024-12-16)


### Features

* added files for ERRU version 3.4 VOL-5800 ([#504](https://github.com/dvsa/vol-app/issues/504)) ([a3bdc1b](https://github.com/dvsa/vol-app/commit/a3bdc1b63bde4b23cfdd2526c1c6a21f1b33dceb))
* liquibase runs in containerised environments via aws batch. WIP ([#496](https://github.com/dvsa/vol-app/issues/496)) ([13f85f8](https://github.com/dvsa/vol-app/commit/13f85f8d194ad7172ef77ef15cc452af6167ac83))
* parameterise etl ref ([#513](https://github.com/dvsa/vol-app/issues/513)) ([ba1ee1f](https://github.com/dvsa/vol-app/commit/ba1ee1fbcaff6fffc7f194da2a4bc3aca973be3d))
* vol-5955 operators no admin role ([#512](https://github.com/dvsa/vol-app/issues/512)) ([9547b67](https://github.com/dvsa/vol-app/commit/9547b672bf0cdb62da0b0646eb208c0536eeb3ac))


### Bug Fixes

* 5936 lb toggle on ecs service ([#494](https://github.com/dvsa/vol-app/issues/494)) ([b6f0894](https://github.com/dvsa/vol-app/commit/b6f0894ad5b31e8e28f2f29a41a874e849e8fb81))
* default language for spellcheck to en_GB ([#482](https://github.com/dvsa/vol-app/issues/482)) ([22cb471](https://github.com/dvsa/vol-app/commit/22cb4714301f207fc3e2c744e754f5bd153e6d01))
* fix null previous image tags being provided to rollback step ([#481](https://github.com/dvsa/vol-app/issues/481)) ([909e697](https://github.com/dvsa/vol-app/commit/909e697f690f7eb18cbb59ce7f2bc1c7ad9750c0))
* liquibase workflow parameters ([#510](https://github.com/dvsa/vol-app/issues/510)) ([a32d688](https://github.com/dvsa/vol-app/commit/a32d6883a64bb3584e79e2d9387dbe1c36f5816e))
* pi records without a case no longer throw errors VOL-5297 ([#499](https://github.com/dvsa/vol-app/issues/499)) ([5b01069](https://github.com/dvsa/vol-app/commit/5b01069b92ab1ab3015a9c64aeb96de2234efb0b))
* replace inr/natreg config placeholders ([#497](https://github.com/dvsa/vol-app/issues/497)) ([2b7d1a6](https://github.com/dvsa/vol-app/commit/2b7d1a6dec3f2644967d07bd819d0a4597fce325))
* simplify dockerfile stages and etl checkout ([#511](https://github.com/dvsa/vol-app/issues/511)) ([6dfe2e7](https://github.com/dvsa/vol-app/commit/6dfe2e7c47ec243afa8e4cd55c667a53840149ef))
* terms and conditions link populated on TC journey VOL-5943 ([#498](https://github.com/dvsa/vol-app/issues/498)) ([7d29ff6](https://github.com/dvsa/vol-app/commit/7d29ff612d6ca0049a95ff18eccbbaa625897751))
* vol 5955 transport consultant bug ([#507](https://github.com/dvsa/vol-app/issues/507)) ([04461a2](https://github.com/dvsa/vol-app/commit/04461a2c6fa705c3b79b634eb086c49c71e4c2af))

## [5.11.0](https://github.com/dvsa/vol-app/compare/v5.10.2...v5.11.0) (2024-11-29)


### Features

* 5259 create prep environment ([#432](https://github.com/dvsa/vol-app/issues/432)) ([f7c7c7b](https://github.com/dvsa/vol-app/commit/f7c7c7bfdfaf27f564710e52679badf01089efe1))

## [5.10.2](https://github.com/dvsa/vol-app/compare/v5.10.1...v5.10.2) (2024-11-29)


### Bug Fixes

* internal to use previous auth package for now VOL-5896 ([#491](https://github.com/dvsa/vol-app/issues/491)) ([9f63591](https://github.com/dvsa/vol-app/commit/9f63591d9f8e5622435e26296d14a9d79a69b5b1))
* trust policies for PR ([#488](https://github.com/dvsa/vol-app/issues/488)) ([e064bdf](https://github.com/dvsa/vol-app/commit/e064bdfb707284fa5983c1f15c0c7244f0466543))

## [5.10.1](https://github.com/dvsa/vol-app/compare/v5.10.0...v5.10.1) (2024-11-29)


### Bug Fixes

* added policy for jenkins to access asset bucket ([#476](https://github.com/dvsa/vol-app/issues/476)) ([2a4309b](https://github.com/dvsa/vol-app/commit/2a4309bd8e2c7a097348f779da4b230b97f2237f))
* assets bucket policy ([#485](https://github.com/dvsa/vol-app/issues/485)) ([9e6a459](https://github.com/dvsa/vol-app/commit/9e6a459b662bd54ab4d45b4f48a251ce14647e39))
* list bucket policy on assets bucket ([#486](https://github.com/dvsa/vol-app/issues/486)) ([e83835c](https://github.com/dvsa/vol-app/commit/e83835c1035756383628e139c065e5f901a58a41))
* vol assets bucket ([#478](https://github.com/dvsa/vol-app/issues/478)) ([e28b505](https://github.com/dvsa/vol-app/commit/e28b505375e4c6883074d00a628bac67763d0e45))
* vol assets bucket role policy ([#484](https://github.com/dvsa/vol-app/issues/484)) ([9f6aa18](https://github.com/dvsa/vol-app/commit/9f6aa187a2e425caf60331a1e7631e8360877e0a))

## [5.10.0](https://github.com/dvsa/vol-app/compare/v5.9.4-alpha.1...v5.10.0) (2024-11-26)

### Features

* 5261 provision prod account ([#431](https://github.com/dvsa/vol-app/issues/431)) ([1de9af3](https://github.com/dvsa/vol-app/commit/1de9af35213a1a0e6509105fd33c4a8285b16998))
* add 2 new item to decision list, hide old one from dropdown but leave in refdata table for old records ([#453](https://github.com/dvsa/vol-app/issues/453)) ([9c0ec62](https://github.com/dvsa/vol-app/commit/9c0ec62d17b5fccfb5ddc11faddb69fb1a6b17af))
* add prep test stage ([#429](https://github.com/dvsa/vol-app/issues/429)) ([7360163](https://github.com/dvsa/vol-app/commit/7360163b55812adedb7894f9ddf88efeb8046d1d))
* apply prep account ([#463](https://github.com/dvsa/vol-app/issues/463)) ([336478a](https://github.com/dvsa/vol-app/commit/336478a9fa081d3fbde315b682864ff88eca65ae))
* bump govuk-frontend to 5.7.1 (royal coat of arms) ([#422](https://github.com/dvsa/vol-app/issues/422)) ([bb460ce](https://github.com/dvsa/vol-app/commit/bb460ceb19ce1234409819164c2df8d5d92eec0a))
* bumped php-govuk-account for did document support ([#424](https://github.com/dvsa/vol-app/issues/424)) ([58c503d](https://github.com/dvsa/vol-app/commit/58c503de8c3ff9baaac267da24169acf90cea542))
* e2e test runs after deploy, rollback in unsuccessful in int ([#402](https://github.com/dvsa/vol-app/issues/402)) ([145009a](https://github.com/dvsa/vol-app/commit/145009a38432ca86fc4311a30025ef47357d3a48))
* integrate doctrine migrations vol 5793 ([#420](https://github.com/dvsa/vol-app/issues/420)) ([bcaeab0](https://github.com/dvsa/vol-app/commit/bcaeab0480391d685fc3965dce777ff698b6fb63))
* permission fixes, improve permission tests, facilitate check for last op admin user VOL-4718 VOL-5796 ([#458](https://github.com/dvsa/vol-app/issues/458)) ([990f446](https://github.com/dvsa/vol-app/commit/990f446a5082bc7773ed8f17555533bfd3d874fc))
* use deploy-environment workflow for int rollback. ([#421](https://github.com/dvsa/vol-app/issues/421)) ([e70824e](https://github.com/dvsa/vol-app/commit/e70824e2a1bbf19deaa2c796cda44718b5efb77f))
* welcome page for external users with terms and conditions VOL-5664 ([#419](https://github.com/dvsa/vol-app/issues/419)) ([e4a5102](https://github.com/dvsa/vol-app/commit/e4a5102e7fc278338d0ac460a7afaf323f26c106))


### Bug Fixes

* 5261 role privileges ([#440](https://github.com/dvsa/vol-app/issues/440)) ([c3966da](https://github.com/dvsa/vol-app/commit/c3966dafca8e5b87bcea3a42bfd692f734a79a4e))
* change defualt `allowEmail` to 1 ([#428](https://github.com/dvsa/vol-app/issues/428)) ([4884242](https://github.com/dvsa/vol-app/commit/4884242b0dbad531af14682983f2ccf62010f547))
* give e22 tests requisite permissions ([#405](https://github.com/dvsa/vol-app/issues/405)) ([fb2330b](https://github.com/dvsa/vol-app/commit/fb2330bc01aad2d0300c70d7a5b6d4e34241af5a))
* only op-tc are affected by the submit app/var block op-adm check ([#447](https://github.com/dvsa/vol-app/issues/447)) ([4ce6321](https://github.com/dvsa/vol-app/commit/4ce632119fbe2b354c9f956f2fdb30647ef041d9))
* place explicit dependency between service and cluster ([#435](https://github.com/dvsa/vol-app/issues/435)) ([b00e969](https://github.com/dvsa/vol-app/commit/b00e9694afb985ee576ebef9bb53dbeb4c3d8b01))
* properly call reuseable workflow in test-dev and test-int ([#404](https://github.com/dvsa/vol-app/issues/404)) ([bdc9155](https://github.com/dvsa/vol-app/commit/bdc9155776dcebc42bbac48b406a35ce2e76e12a))
* reinstate transport manager ability to have TC role ([#465](https://github.com/dvsa/vol-app/issues/465)) ([fd9f8a5](https://github.com/dvsa/vol-app/commit/fd9f8a516ae7287b38d6d88f8626a9b741141540))
* remove healthcheck wait conditions ([#437](https://github.com/dvsa/vol-app/issues/437)) ([b6ea3c1](https://github.com/dvsa/vol-app/commit/b6ea3c14774b08fbe121e17bca2946c83588c5a3))
* remove placeholders that have been removed from paramstore - followup ticket will assess if config blocks can go too ([#466](https://github.com/dvsa/vol-app/issues/466)) ([41f8333](https://github.com/dvsa/vol-app/commit/41f8333331316e7c9001d853c74404c02876b4ca))
* temporary removal of check for operator admin login ([#423](https://github.com/dvsa/vol-app/issues/423)) ([2c3e25c](https://github.com/dvsa/vol-app/commit/2c3e25cd484220279badc5284d25a4cb0c93bb1b))
* tests called specifying environment ([#439](https://github.com/dvsa/vol-app/issues/439)) ([e4503ed](https://github.com/dvsa/vol-app/commit/e4503ed452a16514fe8c4ba767eaeb70893861af))
* tweak deployment approaches ([#438](https://github.com/dvsa/vol-app/issues/438)) ([985aaae](https://github.com/dvsa/vol-app/commit/985aaae1ab1b6f6f01cc13c01418e6d30fb4d9b7))
* Update dependency that had sub-dependency with security vuln ([#434](https://github.com/dvsa/vol-app/issues/434)) ([86179ed](https://github.com/dvsa/vol-app/commit/86179ed1ef79b90ac29bc272e0b9ee22efeff992))
* upgrade autoprefixer from 10.4.19 to 10.4.20 ([#397](https://github.com/dvsa/vol-app/issues/397)) ([1fdc262](https://github.com/dvsa/vol-app/commit/1fdc262aaac492828c30cecd7dff5654d5488d9f))
* use aws trivy java db registry avoid ratelimits ([#408](https://github.com/dvsa/vol-app/issues/408)) ([aab2c92](https://github.com/dvsa/vol-app/commit/aab2c92fe0544b3d7f436b1e06f7a9d51a82a4e6))


### Miscellaneous Chores

* release 5.10.0 ([#474](https://github.com/dvsa/vol-app/issues/474)) ([321c7e7](https://github.com/dvsa/vol-app/commit/321c7e71216eaa9833b8215e671ff24be0d186ab))


## [5.9.4-alpha.1](https://github.com/dvsa/vol-app/compare/v5.9.0...v5.9.4-alpha.1) (2024-11-25)


### Features

* 5261 provision prod account ([#431](https://github.com/dvsa/vol-app/issues/431)) ([1de9af3](https://github.com/dvsa/vol-app/commit/1de9af35213a1a0e6509105fd33c4a8285b16998))
* add 2 new item to decision list, hide old one from dropdown but leave in refdata table for old records ([#453](https://github.com/dvsa/vol-app/issues/453)) ([9c0ec62](https://github.com/dvsa/vol-app/commit/9c0ec62d17b5fccfb5ddc11faddb69fb1a6b17af))
* add prep test stage ([#429](https://github.com/dvsa/vol-app/issues/429)) ([7360163](https://github.com/dvsa/vol-app/commit/7360163b55812adedb7894f9ddf88efeb8046d1d))
* apply prep account ([#463](https://github.com/dvsa/vol-app/issues/463)) ([336478a](https://github.com/dvsa/vol-app/commit/336478a9fa081d3fbde315b682864ff88eca65ae))
* bump govuk-frontend to 5.7.1 (royal coat of arms) ([#422](https://github.com/dvsa/vol-app/issues/422)) ([bb460ce](https://github.com/dvsa/vol-app/commit/bb460ceb19ce1234409819164c2df8d5d92eec0a))
* bumped php-govuk-account for did document support ([#424](https://github.com/dvsa/vol-app/issues/424)) ([58c503d](https://github.com/dvsa/vol-app/commit/58c503de8c3ff9baaac267da24169acf90cea542))
* e2e test runs after deploy, rollback in unsuccessful in int ([#402](https://github.com/dvsa/vol-app/issues/402)) ([145009a](https://github.com/dvsa/vol-app/commit/145009a38432ca86fc4311a30025ef47357d3a48))
* integrate doctrine migrations vol 5793 ([#420](https://github.com/dvsa/vol-app/issues/420)) ([bcaeab0](https://github.com/dvsa/vol-app/commit/bcaeab0480391d685fc3965dce777ff698b6fb63))
* permission fixes, improve permission tests, facilitate check for last op admin user VOL-4718 VOL-5796 ([#458](https://github.com/dvsa/vol-app/issues/458)) ([990f446](https://github.com/dvsa/vol-app/commit/990f446a5082bc7773ed8f17555533bfd3d874fc))
* use deploy-environment workflow for int rollback. ([#421](https://github.com/dvsa/vol-app/issues/421)) ([e70824e](https://github.com/dvsa/vol-app/commit/e70824e2a1bbf19deaa2c796cda44718b5efb77f))
* welcome page for external users with terms and conditions VOL-5664 ([#419](https://github.com/dvsa/vol-app/issues/419)) ([e4a5102](https://github.com/dvsa/vol-app/commit/e4a5102e7fc278338d0ac460a7afaf323f26c106))


### Bug Fixes

* 5261 role privileges ([#440](https://github.com/dvsa/vol-app/issues/440)) ([c3966da](https://github.com/dvsa/vol-app/commit/c3966dafca8e5b87bcea3a42bfd692f734a79a4e))
* change defualt `allowEmail` to 1 ([#428](https://github.com/dvsa/vol-app/issues/428)) ([4884242](https://github.com/dvsa/vol-app/commit/4884242b0dbad531af14682983f2ccf62010f547))
* give e22 tests requisite permissions ([#405](https://github.com/dvsa/vol-app/issues/405)) ([fb2330b](https://github.com/dvsa/vol-app/commit/fb2330bc01aad2d0300c70d7a5b6d4e34241af5a))
* only op-tc are affected by the submit app/var block op-adm check ([#447](https://github.com/dvsa/vol-app/issues/447)) ([4ce6321](https://github.com/dvsa/vol-app/commit/4ce632119fbe2b354c9f956f2fdb30647ef041d9))
* place explicit dependency between service and cluster ([#435](https://github.com/dvsa/vol-app/issues/435)) ([b00e969](https://github.com/dvsa/vol-app/commit/b00e9694afb985ee576ebef9bb53dbeb4c3d8b01))
* properly call reuseable workflow in test-dev and test-int ([#404](https://github.com/dvsa/vol-app/issues/404)) ([bdc9155](https://github.com/dvsa/vol-app/commit/bdc9155776dcebc42bbac48b406a35ce2e76e12a))
* reinstate transport manager ability to have TC role ([#465](https://github.com/dvsa/vol-app/issues/465)) ([fd9f8a5](https://github.com/dvsa/vol-app/commit/fd9f8a516ae7287b38d6d88f8626a9b741141540))
* remove healthcheck wait conditions ([#437](https://github.com/dvsa/vol-app/issues/437)) ([b6ea3c1](https://github.com/dvsa/vol-app/commit/b6ea3c14774b08fbe121e17bca2946c83588c5a3))
* remove placeholders that have been removed from paramstore - followup ticket will assess if config blocks can go too ([#466](https://github.com/dvsa/vol-app/issues/466)) ([41f8333](https://github.com/dvsa/vol-app/commit/41f8333331316e7c9001d853c74404c02876b4ca))
* temporary removal of check for operator admin login ([#423](https://github.com/dvsa/vol-app/issues/423)) ([2c3e25c](https://github.com/dvsa/vol-app/commit/2c3e25cd484220279badc5284d25a4cb0c93bb1b))
* tests called specifying environment ([#439](https://github.com/dvsa/vol-app/issues/439)) ([e4503ed](https://github.com/dvsa/vol-app/commit/e4503ed452a16514fe8c4ba767eaeb70893861af))
* tweak deployment approaches ([#438](https://github.com/dvsa/vol-app/issues/438)) ([985aaae](https://github.com/dvsa/vol-app/commit/985aaae1ab1b6f6f01cc13c01418e6d30fb4d9b7))
* Update dependency that had sub-dependency with security vuln ([#434](https://github.com/dvsa/vol-app/issues/434)) ([86179ed](https://github.com/dvsa/vol-app/commit/86179ed1ef79b90ac29bc272e0b9ee22efeff992))
* upgrade autoprefixer from 10.4.19 to 10.4.20 ([#397](https://github.com/dvsa/vol-app/issues/397)) ([1fdc262](https://github.com/dvsa/vol-app/commit/1fdc262aaac492828c30cecd7dff5654d5488d9f))
* use aws trivy java db registry avoid ratelimits ([#408](https://github.com/dvsa/vol-app/issues/408)) ([aab2c92](https://github.com/dvsa/vol-app/commit/aab2c92fe0544b3d7f436b1e06f7a9d51a82a4e6))


### Miscellaneous Chores

* bump ([#473](https://github.com/dvsa/vol-app/issues/473)) ([da2d9a0](https://github.com/dvsa/vol-app/commit/da2d9a0775f9c4044aa2fc20073dfea0df4501fd))

## [5.9.0](https://github.com/dvsa/vol-app/compare/v5.8.2...v5.9.0) (2024-10-17)


### Features

* 5809 batch alarm dead letter queue ([#387](https://github.com/dvsa/vol-app/issues/387)) ([258431c](https://github.com/dvsa/vol-app/commit/258431c107b8dbc7cc7e6caa1d53e3149db2af58))
* 5809 batch alarm dlq module ([#388](https://github.com/dvsa/vol-app/issues/388)) ([1637fa9](https://github.com/dvsa/vol-app/commit/1637fa9dc3c087ce7ffe004889dbb93a1f9f9428))
* 5809 batch alarm email testing ([#377](https://github.com/dvsa/vol-app/issues/377)) ([2ecbd09](https://github.com/dvsa/vol-app/commit/2ecbd09873b7cdf262e0db83fdb6e5282def5715))
* add `Consultant Administrator` option to add users page ([#363](https://github.com/dvsa/vol-app/issues/363)) ([9703d68](https://github.com/dvsa/vol-app/commit/9703d68f4dab2111f38f9566d6162648d7ca22ed))
* cannot sign declaration unless op-adm for org has logged in ([#364](https://github.com/dvsa/vol-app/issues/364)) ([47e31e4](https://github.com/dvsa/vol-app/commit/47e31e42ba9ea1a04bd36f4332ca5e0f7e20f513))
* create monitoring in cloudwatch for aws batch failures ([#367](https://github.com/dvsa/vol-app/issues/367)) ([58c076c](https://github.com/dvsa/vol-app/commit/58c076c8cd203edd165855d8d360703a9f1df168))
* operator self-registration revamp post consultant toggle ([#375](https://github.com/dvsa/vol-app/issues/375)) ([cf3f117](https://github.com/dvsa/vol-app/commit/cf3f1179ce83ccd8f5e55b1dd8b2e87c42fc77a3))
* Update DataGovUkExport.php to upload to S3 bucket. ([#357](https://github.com/dvsa/vol-app/issues/357)) ([3414573](https://github.com/dvsa/vol-app/commit/34145736d0ba78b7f9142fe2bb0ec89ab09670d8))


### Bug Fixes

* 5809 batch alarm rule names ([#374](https://github.com/dvsa/vol-app/issues/374)) ([1168502](https://github.com/dvsa/vol-app/commit/1168502af325b0e36831e1438bc0c0ec21cd91ad))
* 5809 batch alarms topic policy ([#392](https://github.com/dvsa/vol-app/issues/392)) ([65c86e4](https://github.com/dvsa/vol-app/commit/65c86e4ef8562187a0c6d0d35e78cb3c87342c1d))
* add/manage user TC role hidden when TC toggle disabled ([#393](https://github.com/dvsa/vol-app/issues/393)) ([02aa445](https://github.com/dvsa/vol-app/commit/02aa445fea1ca7d98e5e9f7644d0db1854129175))
* added missing subheading for operator self registration ([#379](https://github.com/dvsa/vol-app/issues/379)) ([045ce36](https://github.com/dvsa/vol-app/commit/045ce36472f1251409be8c0bfd3fc7c9d8415bff))
* Operator representation registration form radio options switched ([#355](https://github.com/dvsa/vol-app/issues/355)) ([2984dde](https://github.com/dvsa/vol-app/commit/2984dde08ac191cc7f2412d8b8be274365ccf91c))
* sanitize the returned version - dont return string with slashes in ([#366](https://github.com/dvsa/vol-app/issues/366)) ([7d13277](https://github.com/dvsa/vol-app/commit/7d13277045a9bf957415f56e3e8aefa9e3ba6fa1))
* Two methods in DataGovUkExport did not call new parent uploadToS3 method. ([#378](https://github.com/dvsa/vol-app/issues/378)) ([d4b8b9f](https://github.com/dvsa/vol-app/commit/d4b8b9f8fb95c36069cd6934cec66b690b8a1501))
* updated businessType label for register for operator to have its own translation key ([#362](https://github.com/dvsa/vol-app/issues/362)) ([8918d18](https://github.com/dvsa/vol-app/commit/8918d185013c0725163c16c8266cdd3022231f3e))

## [5.8.2](https://github.com/dvsa/vol-app/compare/v5.8.1...v5.8.2) (2024-09-25)


### Bug Fixes

* bring button fix in via updated common dep ([#354](https://github.com/dvsa/vol-app/issues/354)) ([94a35c7](https://github.com/dvsa/vol-app/commit/94a35c7aee9cf731e82efef9d4b7e522914160c4))
* External search form button mis-alignment ([#352](https://github.com/dvsa/vol-app/issues/352)) ([2527e3c](https://github.com/dvsa/vol-app/commit/2527e3ceba39e68698f249696202b9efb5fc1af7))

## [5.8.1](https://github.com/dvsa/vol-app/compare/v5.8.0...v5.8.1) (2024-09-25)


### Bug Fixes

* Check records are actually related to an Operating Centre before accesing that property. ([#347](https://github.com/dvsa/vol-app/issues/347)) ([822ce06](https://github.com/dvsa/vol-app/commit/822ce063d0bce029e577ece8ca64c452c55ad9d5))
* Ensure filter button is rendered at the end of the filter form. ([#350](https://github.com/dvsa/vol-app/issues/350)) ([5dec8c9](https://github.com/dvsa/vol-app/commit/5dec8c97401d89496d5b0608931ad9409dec131c))
* Ensure filter button is rendered at the end of the filter form. Bump Common to pull in a fox on a shared form ([#349](https://github.com/dvsa/vol-app/issues/349)) ([b409c62](https://github.com/dvsa/vol-app/commit/b409c622c7c3d15de7d942fa337718ecfb66e11f))
* External search form button mis-alignment ([#351](https://github.com/dvsa/vol-app/issues/351)) ([32637e9](https://github.com/dvsa/vol-app/commit/32637e900df0e32ec9fc445b83c1a08c229de321))
* removes form__action field from failed postcode lookups ([#341](https://github.com/dvsa/vol-app/issues/341)) ([c1bca49](https://github.com/dvsa/vol-app/commit/c1bca491136b1de388c9346a870eb23b77ad4d8a))
* Resolves vehicle search being accessible incorrectly to logged out users ([#348](https://github.com/dvsa/vol-app/issues/348)) ([05e8d21](https://github.com/dvsa/vol-app/commit/05e8d21fd7c818bbc3559c965bf8155910576aff))

## [5.8.0](https://github.com/dvsa/vol-app/compare/v5.7.0...v5.8.0) (2024-09-19)


### Features

* add `ignore-platform-reqs` option to local refresh Composer ([#264](https://github.com/dvsa/vol-app/issues/264)) ([55d819a](https://github.com/dvsa/vol-app/commit/55d819a8d66c49ad3076cc5e9a098178d12f18fb))
* add e2e tests to cd dev int vol 5263 ([#322](https://github.com/dvsa/vol-app/issues/322)) ([fea08e8](https://github.com/dvsa/vol-app/commit/fea08e899d9e6feb44d8f791463c9ab0f2c177f4))
* add e2e tests to cd dev int vol 5263 ([#322](https://github.com/dvsa/vol-app/issues/322)) ([#323](https://github.com/dvsa/vol-app/issues/323)) ([d98fb7d](https://github.com/dvsa/vol-app/commit/d98fb7df6ae06217540fb2adb04ab7f8421b08ff))
* Add e2esmoke and full regression to workflow (WIP) ([#321](https://github.com/dvsa/vol-app/issues/321)) ([e0090fa](https://github.com/dvsa/vol-app/commit/e0090fa622c95f69aa84b96273c8b3895a5e00be))
* add Elasticache as session save handler ([#304](https://github.com/dvsa/vol-app/issues/304)) ([20df1db](https://github.com/dvsa/vol-app/commit/20df1dbe87dc0fbc5d30afe53563225cc9f7fbf5))
* add LDAP adapter (dvsa/olcs-backend[#210](https://github.com/dvsa/vol-app/issues/210)) ([f1b64c0](https://github.com/dvsa/vol-app/commit/f1b64c03bf3c1e55072e2d7da3693b96c2a55fe6))
* add local refresh script ([#198](https://github.com/dvsa/vol-app/issues/198)) ([4c597f5](https://github.com/dvsa/vol-app/commit/4c597f51c0589300c11757b39135cc263319a0f1))
* add mailpit container to compose stack and update docs to mention it. ([#271](https://github.com/dvsa/vol-app/issues/271)) ([1b6f0e3](https://github.com/dvsa/vol-app/commit/1b6f0e3cb200ad63ee92d8e0a8c57343c2ec4cad))
* bump common dep minimum version ([#336](https://github.com/dvsa/vol-app/issues/336)) ([d9517b7](https://github.com/dvsa/vol-app/commit/d9517b71e669c2146d4f9b3bd796458fb083a1a8))
* bump common dependency ([#269](https://github.com/dvsa/vol-app/issues/269)) ([4c89f3b](https://github.com/dvsa/vol-app/commit/4c89f3b7b7cf44902df8b74685b57466e3307662))
* change the continuation information email templates to use translation key. ([#286](https://github.com/dvsa/vol-app/issues/286)) ([b243853](https://github.com/dvsa/vol-app/commit/b2438530e9ffba9642015e6f3bb9608f0fa5298b))
* **docker:** exclude `/healthcheck` from access logs ([#279](https://github.com/dvsa/vol-app/issues/279)) ([f2b889e](https://github.com/dvsa/vol-app/commit/f2b889e6e7577f371ee9a533dfc1e86243ab19a2))
* **docker:** tweak PHP-FPM process manager values ([#278](https://github.com/dvsa/vol-app/issues/278)) ([f9e9916](https://github.com/dvsa/vol-app/commit/f9e9916684acf65ef30de5c2d17832fbeb9cedf0))
* **docker:** update PHP-FPM config ([#291](https://github.com/dvsa/vol-app/issues/291)) ([89da3a8](https://github.com/dvsa/vol-app/commit/89da3a8c5c7a9e2c9e73fbd32e4a352eea4819c7))
* expand list of licence and application statuses included in messaging dropdown ([#285](https://github.com/dvsa/vol-app/issues/285)) ([cfa8a36](https://github.com/dvsa/vol-app/commit/cfa8a363a03fbe9bb9e28bd54d6ae68cfdf0dd4a))
* externalise application log level ([#263](https://github.com/dvsa/vol-app/issues/263)) ([309edb8](https://github.com/dvsa/vol-app/commit/309edb813ccb0592d65c81122e69bc182b2c47ef))
* full CD, change in app/cdn ([#325](https://github.com/dvsa/vol-app/issues/325)) ([554f722](https://github.com/dvsa/vol-app/commit/554f7225e620532bf29d20c44289935b71476f36))
* isolated e2e test workflow to verify params/outputs etc ([#332](https://github.com/dvsa/vol-app/issues/332)) ([5a2e10d](https://github.com/dvsa/vol-app/commit/5a2e10d79e0fe5df5e9ce83be7556a1ff46515e1))
* remove send by post radio ([#317](https://github.com/dvsa/vol-app/issues/317)) ([4f45ba8](https://github.com/dvsa/vol-app/commit/4f45ba86eef3b4d4eb8595f57b8c2a11b2bd95cc))
* **terraform:** allow traffic routing to be controlled by boolean ([#241](https://github.com/dvsa/vol-app/issues/241)) ([74f3af5](https://github.com/dvsa/vol-app/commit/74f3af596e73079a7bdfd538cc3a37f1f142a26b))
* update local refresh script ([#258](https://github.com/dvsa/vol-app/issues/258)) ([f8c169e](https://github.com/dvsa/vol-app/commit/f8c169e9ad97d0b4c0ecae2c978cdb65f386361a))
* VOL-5239 create transport consultant reg journey ([#259](https://github.com/dvsa/vol-app/issues/259)) ([169d5a1](https://github.com/dvsa/vol-app/commit/169d5a1d7303ac650720e46d97ba065d342b2b65))
* VOL-5305 create transport consultant role ([#249](https://github.com/dvsa/vol-app/issues/249)) ([c12d621](https://github.com/dvsa/vol-app/commit/c12d6214a8796c65ab37cde5efd05c4e3e439458))
* VOL-5536 update addresses for Quarry House ([#284](https://github.com/dvsa/vol-app/issues/284)) ([61c4f10](https://github.com/dvsa/vol-app/commit/61c4f103d6b3be6c7bf56e4bdea0f7f11f748098))


### Bug Fixes

* alter CDN module to create public record rather than private ([#308](https://github.com/dvsa/vol-app/issues/308)) ([ab6895b](https://github.com/dvsa/vol-app/commit/ab6895b1e0dea0bfab63e1ca175a42f661acfd63))
* **api:** add status code to content store file uploader log ([#280](https://github.com/dvsa/vol-app/issues/280)) ([3209a44](https://github.com/dvsa/vol-app/commit/3209a44a15a493732dfa32276f646a94096828eb))
* **api:** fix the local register journey while using LDAP ([#303](https://github.com/dvsa/vol-app/issues/303)) ([2791b9c](https://github.com/dvsa/vol-app/commit/2791b9c1351a17981b6b9266fa2fa90818c1bf9f))
* **api:** handle errors better in `WebDavClient.php` ([#283](https://github.com/dvsa/vol-app/issues/283)) ([e8e1933](https://github.com/dvsa/vol-app/commit/e8e19332b046399d5e1069707cb4d6fc4f237ee6))
* bump `dvsa/laminas-config-cloud-parameters` and fix cast config ([#290](https://github.com/dvsa/vol-app/issues/290)) ([db1fcdd](https://github.com/dvsa/vol-app/commit/db1fcdd51a28c74e132352c8df43ece3f2f4038a))
* bus reg letters not generating on some Grants ([#306](https://github.com/dvsa/vol-app/issues/306)) ([59a4007](https://github.com/dvsa/vol-app/commit/59a4007dac4d549c007a45e919045fd809d4fdb5))
* cast log level params to int to fix CPMS error ([#288](https://github.com/dvsa/vol-app/issues/288)) ([8c9ae97](https://github.com/dvsa/vol-app/commit/8c9ae976e38e093893b70edada6ff0a663da8696))
* Cast str to int to satisfy typing on cpms client ([#282](https://github.com/dvsa/vol-app/issues/282)) ([1116488](https://github.com/dvsa/vol-app/commit/11164881890464d2fc6ec31897b48302b72f99a8))
* Cater for some "attached_to" CU fields being null in the DB. ([#337](https://github.com/dvsa/vol-app/issues/337)) ([6b46419](https://github.com/dvsa/vol-app/commit/6b4641974a686098e12855f589d7984eaa149462))
* **cdn:** exclude dependency from uglification causing errors with jQuery 3 ([#239](https://github.com/dvsa/vol-app/issues/239)) ([758c5d1](https://github.com/dvsa/vol-app/commit/758c5d1790b931a009c3f068a70c07363783b830))
* **cdn:** remove unused asset files ([#257](https://github.com/dvsa/vol-app/issues/257)) ([56a5db3](https://github.com/dvsa/vol-app/commit/56a5db3689121a8042f29da65efb82d93058e3f3))
* disable events in developer tools ([#267](https://github.com/dvsa/vol-app/issues/267)) ([2ebb6c8](https://github.com/dvsa/vol-app/commit/2ebb6c81e097b099598331cc85d4ffcd2830dd4f))
* **docker:** add `port_in_redirect off` for One Login redirect ([#300](https://github.com/dvsa/vol-app/issues/300)) ([9a08cf3](https://github.com/dvsa/vol-app/commit/9a08cf38029273d9e45af344a499836b0de16c42))
* **docker:** forward query parameters for GOV.UK OneLogin redirect ([#302](https://github.com/dvsa/vol-app/issues/302)) ([d1eb15f](https://github.com/dvsa/vol-app/commit/d1eb15fb7fed9ba4d9989c32b3b5dc286cd0a267))
* ecs environments defer to parameter assets_url for asset path ([#318](https://github.com/dvsa/vol-app/issues/318)) ([53da7aa](https://github.com/dvsa/vol-app/commit/53da7aad070646379dbb28636bfc704788230604))
* externalise app log level ([#265](https://github.com/dvsa/vol-app/issues/265)) ([42dec8b](https://github.com/dvsa/vol-app/commit/42dec8b6114c8de43b63ca54fca2e199d46cfe5c))
* extra-hosts entry for non Docker Desktop systems ([#256](https://github.com/dvsa/vol-app/issues/256)) ([4f126e7](https://github.com/dvsa/vol-app/commit/4f126e703132368d2fb415a7ca3dbc5e4b78f92e))
* fix for radio button alignment at smaller widths when hint is pr… ([#277](https://github.com/dvsa/vol-app/issues/277)) ([51b0f09](https://github.com/dvsa/vol-app/commit/51b0f09238c7ba304ff5f8acc1814647fbd91f17))
* fix local setup ([#254](https://github.com/dvsa/vol-app/issues/254)) ([1fb834c](https://github.com/dvsa/vol-app/commit/1fb834ce5d6dfb7c81fd945e8b6ef8637b03d72f))
* fix validation on two Reg For Operator form fields ([#307](https://github.com/dvsa/vol-app/issues/307)) ([b003847](https://github.com/dvsa/vol-app/commit/b003847c2f9f03ddb0a2d759b2eccbc4bafffb83))
* IDE removed an "unused" use statement. Adding back in as its actually essential. ([#320](https://github.com/dvsa/vol-app/issues/320)) ([f2fda0c](https://github.com/dvsa/vol-app/commit/f2fda0ce017e14752251ce9feaec2b9ee7a29a6d))
* internal modal/alert button group position and modal bottom spacing ([#305](https://github.com/dvsa/vol-app/issues/305)) ([47596ce](https://github.com/dvsa/vol-app/commit/47596ce5c6d5e80136aa3f8cb5bbdc8924d11dcf))
* **internal:** remove unnecessary login hop ([#299](https://github.com/dvsa/vol-app/issues/299)) ([6b844f1](https://github.com/dvsa/vol-app/commit/6b844f144aecde68634f5eda8451e1068d6c19a3))
* local.php.dist - api_service_mappings require full URLs ([#309](https://github.com/dvsa/vol-app/issues/309)) ([7e0ad37](https://github.com/dvsa/vol-app/commit/7e0ad37d4991a14fe1bdb536db9b2c1d3a268d13))
* refactor cascadeForm for JQ3 ([#240](https://github.com/dvsa/vol-app/issues/240)) ([fc34c80](https://github.com/dvsa/vol-app/commit/fc34c80702e1abb4b1dfe32f5edf76394e4c7e07))
* remove `laminas-form` workaround ([#268](https://github.com/dvsa/vol-app/issues/268)) ([64c5c3c](https://github.com/dvsa/vol-app/commit/64c5c3cafa2fef61acf76764152c1807e42c9fd5))
* remove display hardcoded style when unhiding elements using cascadeForm ([#311](https://github.com/dvsa/vol-app/issues/311)) ([76ee133](https://github.com/dvsa/vol-app/commit/76ee1339d9cc7f3db4041f0996a8439728faba95))
* restore "No (Operator to post)" radio for internal caseworker form. ([#338](https://github.com/dvsa/vol-app/issues/338)) ([bdd53bf](https://github.com/dvsa/vol-app/commit/bdd53bfb9b3984946b933cec27e73b03364cfc98))
* reversed lint changes to cacadeForm ([#312](https://github.com/dvsa/vol-app/issues/312)) ([ac10eda](https://github.com/dvsa/vol-app/commit/ac10eda78cc023c626084d9bb45ee19c499e1dbc))
* set filter button priority to be rendered below select filters ([#339](https://github.com/dvsa/vol-app/issues/339)) ([6b62805](https://github.com/dvsa/vol-app/commit/6b62805831bbc1bb6d6b5390a45e08dd2e485405))
* **terraform:** remove `latest` tag from variables ([#260](https://github.com/dvsa/vol-app/issues/260)) ([4ac8a30](https://github.com/dvsa/vol-app/commit/4ac8a301a565114f253225ffb911d5cd2bcd6c96))
* **terraform:** use `legacy_environment` for environment name ([#287](https://github.com/dvsa/vol-app/issues/287)) ([9f316af](https://github.com/dvsa/vol-app/commit/9f316af167a6d4453101072c9dcf631df700c928))
* variation status tag capital letter fix vol 5714 (dvsa/olcs-selfserve[#178](https://github.com/dvsa/vol-app/issues/178)) ([c02b7f8](https://github.com/dvsa/vol-app/commit/c02b7f87d25f23966edb6063f72cc20737f7f330))
