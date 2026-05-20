# Changelog

## [9.0.1](https://github.com/dvsa/olcs-logging/compare/v9.0.0...v9.0.1) (2026-05-20)


### Bug Fixes

* tolerate E_USER_ERROR as laminas-log did before monolog ([#29](https://github.com/dvsa/olcs-logging/issues/29)) ([0008310](https://github.com/dvsa/olcs-logging/commit/00083104f7068c4df79d442fe9b75189f1973c41))

## [9.0.0](https://github.com/dvsa/olcs-logging/compare/v8.0.0...v9.0.0) (2026-05-05)


### ⚠ BREAKING CHANGES

* migrate from laminas-log to monolog VOL-6099 ([#27](https://github.com/dvsa/olcs-logging/issues/27))

### Features

* migrate from laminas-log to monolog VOL-6099 ([#27](https://github.com/dvsa/olcs-logging/issues/27)) ([d128abd](https://github.com/dvsa/olcs-logging/commit/d128abd2a63398fa7e2b5a7e89bb117e992f310e))


### Miscellaneous Chores

* vol 6349 secrets scan ([#25](https://github.com/dvsa/olcs-logging/issues/25)) ([a445452](https://github.com/dvsa/olcs-logging/commit/a445452658bdb054b6a6b7d3e5c620534e4ada55))

## [8.0.0](https://github.com/dvsa/olcs-logging/compare/v7.2.0...v8.0.0) (2025-09-19)


### ⚠ BREAKING CHANGES

* bump various deps and php versions, fix static analysis VOL-6497 ([#23](https://github.com/dvsa/olcs-logging/issues/23))

### Miscellaneous Chores

* bump various deps and php versions, fix static analysis VOL-6497 ([#23](https://github.com/dvsa/olcs-logging/issues/23)) ([5b7b550](https://github.com/dvsa/olcs-logging/commit/5b7b55084211a335986d3def7263debf533926df))

## [7.2.0](https://github.com/dvsa/olcs-logging/compare/v7.1.0...v7.2.0) (2024-04-08)


### Features

* always log HTTP errors at `DEBUG` level ([#15](https://github.com/dvsa/olcs-logging/issues/15)) ([5ada0b4](https://github.com/dvsa/olcs-logging/commit/5ada0b43645846c4629b516fa47df887fe0b6ca3))

## [7.1.0](https://github.com/dvsa/olcs-logging/compare/v7.0.0...v7.1.0) (2024-03-21)


### Features

* add PHP ^8.0 support ([#13](https://github.com/dvsa/olcs-logging/issues/13)) ([1c0bbd7](https://github.com/dvsa/olcs-logging/commit/1c0bbd7b949fa0c21728431b57a033aff991546b))

## [7.0.0](https://github.com/dvsa/olcs-logging/compare/v6.0.0...v7.0.0) (2024-03-18)


### ⚠ BREAKING CHANGES

* replace `laminas-mvc-console` with `laminas-cli` ([#10](https://github.com/dvsa/olcs-logging/issues/10))

### Features

* bump static analysis level ([#11](https://github.com/dvsa/olcs-logging/issues/11)) ([c7ca625](https://github.com/dvsa/olcs-logging/commit/c7ca6253e4dd1b7bfe5bcdb590fbb0327fbd6c34))
* replace `laminas-mvc-console` with `laminas-cli` ([#10](https://github.com/dvsa/olcs-logging/issues/10)) ([ac812e7](https://github.com/dvsa/olcs-logging/commit/ac812e79fde4fe74cf6f1e7dc7a30c5f4b96d590))

## [6.0.0](https://github.com/dvsa/olcs-logging/compare/v5.0.0...v6.0.0) (2024-02-19)


### ⚠ BREAKING CHANGES

* interop/container no longer supported

### Features

* VOL-3691 switch to Psr Container ([#8](https://github.com/dvsa/olcs-logging/issues/8)) ([4cd2426](https://github.com/dvsa/olcs-logging/commit/4cd242686884b8fb9feca57281cc3850097beffd))

## [5.0.0](https://github.com/dvsa/olcs-logging/compare/v5.0.0...v5.0.0) (2024-02-14)


### ⚠ BREAKING CHANGES

* upgrade to Laminas v3 (drop v2) ([#4](https://github.com/dvsa/olcs-logging/issues/4))
* migrate to GitHub ([#2](https://github.com/dvsa/olcs-logging/issues/2))

### Features

* migrate to GitHub ([#2](https://github.com/dvsa/olcs-logging/issues/2)) ([2312891](https://github.com/dvsa/olcs-logging/commit/2312891aeb3e67cd17c4fce9dbafe0f0e7c2e099))
* upgrade to Laminas v3 (drop v2) ([#4](https://github.com/dvsa/olcs-logging/issues/4)) ([41f38b3](https://github.com/dvsa/olcs-logging/commit/41f38b368ec4bd6530b04396215e577bb466b494))


### Miscellaneous Chores

* add Dependabot config ([#5](https://github.com/dvsa/olcs-logging/issues/5)) ([007ae01](https://github.com/dvsa/olcs-logging/commit/007ae01591312a1f2c81934c9594bd60dd8cd819))
* release 5.0.0 ([#7](https://github.com/dvsa/olcs-logging/issues/7)) ([30938d2](https://github.com/dvsa/olcs-logging/commit/30938d2828697aeb4193cfd5988bbb16bd201041))
