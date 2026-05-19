# Changelog

## [9.18.0](https://github.com/dvsa/olcs-common/compare/v9.17.0...v9.18.0) (2026-05-19)


### Features

* removed dependency on laminas-mvc-plugin-prg VOL-7230 ([#304](https://github.com/dvsa/olcs-common/issues/304)) ([4f222ff](https://github.com/dvsa/olcs-common/commit/4f222ffe0e5c026ef3ae4edf35032d4167c8ddb9))


### Bug Fixes

* removed duplicate validator that was causing duplicate error message ([#302](https://github.com/dvsa/olcs-common/issues/302)) ([8da2705](https://github.com/dvsa/olcs-common/commit/8da27058e9f0da9701a582556df7aaf08697fcfc))
* tolerate E_USER_ERROR in onFatalError so user journeys continue post-monolog ([#305](https://github.com/dvsa/olcs-common/issues/305)) ([de9f9da](https://github.com/dvsa/olcs-common/commit/de9f9da2d7ac2452a53ef8f458fe849c1bcc1e41))

## [9.17.0](https://github.com/dvsa/olcs-common/compare/v9.16.1...v9.17.0) (2026-05-14)


### Features

* remove unecessary VOL code, fall back to Laminas, fix static analysis VOL-6800 ([#300](https://github.com/dvsa/olcs-common/issues/300)) ([487bc1a](https://github.com/dvsa/olcs-common/commit/487bc1ac492ec8af5bb79e140f99ffe87284d799))
* swapped old-logging for laminas-log to monolog VOL-6099 ([#293](https://github.com/dvsa/olcs-common/issues/293)) ([#298](https://github.com/dvsa/olcs-common/issues/298)) ([9f069d5](https://github.com/dvsa/olcs-common/commit/9f069d52f305c9f98bebe421395735e57eda486d))


### Bug Fixes

* vol 5399 guidance error render bug ([#299](https://github.com/dvsa/olcs-common/issues/299)) ([eda6734](https://github.com/dvsa/olcs-common/commit/eda67346a5c87180f989cd02220032f40d5c68d7))

## [9.16.1](https://github.com/dvsa/olcs-common/compare/v9.16.0...v9.16.1) (2026-05-08)


### Miscellaneous Chores

* trigger ci ([#296](https://github.com/dvsa/olcs-common/issues/296)) ([f7426b9](https://github.com/dvsa/olcs-common/commit/f7426b9bf06ce19aa38dc219ab8b42793f58d01c))

## [9.16.0](https://github.com/dvsa/olcs-common/compare/v9.15.0...v9.16.0) (2026-05-06)


### Features

* in fallback business wants Govlogin along with print sign and return option ([#294](https://github.com/dvsa/olcs-common/issues/294)) ([2003d26](https://github.com/dvsa/olcs-common/commit/2003d26c909e6ca12ccd2b120488e9d65f997915))
* swapped old-logging for laminas-log to monolog VOL-6099 ([#293](https://github.com/dvsa/olcs-common/issues/293)) ([eb42cf8](https://github.com/dvsa/olcs-common/commit/eb42cf83e790b54fbed11a40208fe959e949412e))


### Miscellaneous Chores

* vol 6349 secrets scan ([#291](https://github.com/dvsa/olcs-common/issues/291)) ([a1323b4](https://github.com/dvsa/olcs-common/commit/a1323b49181c5bfab86f9a39ff556e10423f119b))

## [9.15.0](https://github.com/dvsa/olcs-common/compare/v9.14.0...v9.15.0) (2026-04-28)


### Features

* Mandate GOV One login for Licence application and fallback to Print retun and sign ([#289](https://github.com/dvsa/olcs-common/issues/289)) ([c3355b1](https://github.com/dvsa/olcs-common/commit/c3355b194ebf9052a910fb8f8515145b3e0a806a))

## [9.14.0](https://github.com/dvsa/olcs-common/compare/v9.13.0...v9.14.0) (2026-03-23)


### Features

* added new field to varaition application ([#288](https://github.com/dvsa/olcs-common/issues/288)) ([3a20213](https://github.com/dvsa/olcs-common/commit/3a202138f88ceab198eafb8bd856aea72e40ba54))
* vol 6119 remove post references from continuation journey wording ([#286](https://github.com/dvsa/olcs-common/issues/286)) ([817dddf](https://github.com/dvsa/olcs-common/commit/817dddf15712b2d1e94040977ae766ed42dec4e1))

## [9.13.0](https://github.com/dvsa/olcs-common/compare/v9.12.0...v9.13.0) (2026-03-06)


### Features

* edited review declaration for gv common ([#284](https://github.com/dvsa/olcs-common/issues/284)) ([c7ce302](https://github.com/dvsa/olcs-common/commit/c7ce30280c8fe8e93a6bafd99627f5ee531c3624))

## [9.12.0](https://github.com/dvsa/olcs-common/compare/v9.11.0...v9.12.0) (2026-03-01)


### Features

* new Constant for internal webdav ([#282](https://github.com/dvsa/olcs-common/issues/282)) ([ac36be1](https://github.com/dvsa/olcs-common/commit/ac36be10a705c04ef798a5c9e737c2f744737bdf))

## [9.11.0](https://github.com/dvsa/olcs-common/compare/v9.10.1...v9.11.0) (2026-02-25)


### Features

* updated accessibility statement VOL-7005 ([#280](https://github.com/dvsa/olcs-common/issues/280)) ([f2f18fa](https://github.com/dvsa/olcs-common/commit/f2f18fad921f4d6c04f6b6d4855d5097954227e8))

## [9.10.1](https://github.com/dvsa/olcs-common/compare/v9.10.0...v9.10.1) (2026-02-20)


### Bug Fixes

* remove PHP 8.3 typed constants for PHP 8.2 compatibility ([#278](https://github.com/dvsa/olcs-common/issues/278)) ([f470b5b](https://github.com/dvsa/olcs-common/commit/f470b5bd96c9e93ac7c8bb111a0179a4515cbdcd))

## [9.10.0](https://github.com/dvsa/olcs-common/compare/v9.9.0...v9.10.0) (2026-02-20)


### Features

* new Constants for new categories for letter appendix doc type ([#276](https://github.com/dvsa/olcs-common/issues/276)) ([7fcb8a7](https://github.com/dvsa/olcs-common/commit/7fcb8a76145a5d4544d798edebfeba2c0a019675))

## [9.9.0](https://github.com/dvsa/olcs-common/compare/v9.8.0...v9.9.0) (2026-02-10)


### Features

* added some shared variables to make internal/selfserve data service tests compatible with PHPUnit 12 (static data providers) ([#274](https://github.com/dvsa/olcs-common/issues/274)) ([c395f7a](https://github.com/dvsa/olcs-common/commit/c395f7a2f1646533506b9532dd3ce58bacaca3e9))

## [9.8.0](https://github.com/dvsa/olcs-common/compare/v9.7.0...v9.8.0) (2026-01-07)


### Features

* awaiting Grant Fee application redirect to fee page vol-6780 ([#272](https://github.com/dvsa/olcs-common/issues/272)) ([3a185d5](https://github.com/dvsa/olcs-common/commit/3a185d5ec524039ea5341570e492a987b1e65979))

## [9.7.0](https://github.com/dvsa/olcs-common/compare/v9.6.1...v9.7.0) (2025-12-16)


### Features

* vol-6580 edited content of cookie page ([#263](https://github.com/dvsa/olcs-common/issues/263)) ([aecf89e](https://github.com/dvsa/olcs-common/commit/aecf89e48141cdf63dd4744bc36ed6b996f2d7bb))

## [9.6.1](https://github.com/dvsa/olcs-common/compare/v9.6.0...v9.6.1) (2025-12-15)


### Bug Fixes

* cast application id for comparison ([#268](https://github.com/dvsa/olcs-common/issues/268)) ([e252b14](https://github.com/dvsa/olcs-common/commit/e252b1436934739234f12445e252f7b4918b9954))

## [9.6.0](https://github.com/dvsa/olcs-common/compare/v9.5.0...v9.6.0) (2025-12-02)


### Features

* filter advert documents by current application ([#264](https://github.com/dvsa/olcs-common/issues/264)) ([c6cfe1b](https://github.com/dvsa/olcs-common/commit/c6cfe1b20dff24c8fcfc8b8539c4e8f22a37abd3))


### Bug Fixes

* update codeowners ([#265](https://github.com/dvsa/olcs-common/issues/265)) ([9c411c5](https://github.com/dvsa/olcs-common/commit/9c411c5b920a6ceab5f142ddf940e04728eb312e))

## [9.5.0](https://github.com/dvsa/olcs-common/compare/v9.4.0...v9.5.0) (2025-11-24)


### Features

* update wording within convictions and penalty guidance ([#261](https://github.com/dvsa/olcs-common/issues/261)) ([3e49f6a](https://github.com/dvsa/olcs-common/commit/3e49f6a410fa044d0a1e4eb1bb3f3314d2f32e2b))

## [9.4.0](https://github.com/dvsa/olcs-common/compare/v9.3.0...v9.4.0) (2025-11-20)


### Features

* update wording within the traffic area guidance ([#259](https://github.com/dvsa/olcs-common/issues/259)) ([099c1bd](https://github.com/dvsa/olcs-common/commit/099c1bd20e70aefd2ed6a79a84d8810b027c3e19))

## [9.3.0](https://github.com/dvsa/olcs-common/compare/v9.2.0...v9.3.0) (2025-11-06)


### Features

* update content relating to survey provider ([#257](https://github.com/dvsa/olcs-common/issues/257)) ([e811876](https://github.com/dvsa/olcs-common/commit/e811876083e5f69690dc5804011aa27f17680a1a))

## [9.2.0](https://github.com/dvsa/olcs-common/compare/v9.1.4...v9.2.0) (2025-11-04)


### Features

* add new letters system feature toggle ([#255](https://github.com/dvsa/olcs-common/issues/255)) ([8225b5c](https://github.com/dvsa/olcs-common/commit/8225b5c94cea316dfa4d7a16cabcf642fd5adf48))

## [9.1.4](https://github.com/dvsa/olcs-common/compare/v9.1.3...v9.1.4) (2025-10-13)


### Bug Fixes

* Fix continuation declarations following removal of Verify VOL-4379 ([#253](https://github.com/dvsa/olcs-common/issues/253)) ([52642f7](https://github.com/dvsa/olcs-common/commit/52642f7c42e64f39aae2ed67f7cd00684d0d15a3))

## [9.1.3](https://github.com/dvsa/olcs-common/compare/v9.1.2...v9.1.3) (2025-10-07)


### Miscellaneous Chores

* vol-5649 - bumped olcs-transfer version ([#250](https://github.com/dvsa/olcs-common/issues/250)) ([64335ca](https://github.com/dvsa/olcs-common/commit/64335caf2f4a177c22e33222e046f208536e8c21))

## [9.1.2](https://github.com/dvsa/olcs-common/compare/v9.1.1...v9.1.2) (2025-10-07)


### Miscellaneous Chores

* bumped olcs-transfer version ([#248](https://github.com/dvsa/olcs-common/issues/248)) ([8eda65f](https://github.com/dvsa/olcs-common/commit/8eda65fab984dddbc26f2525196a41ee484d0c76))

## [9.1.1](https://github.com/dvsa/olcs-common/compare/v9.1.0...v9.1.1) (2025-09-26)


### Bug Fixes

* trigger release after operating centre document revert ([#246](https://github.com/dvsa/olcs-common/issues/246)) ([8b78ee1](https://github.com/dvsa/olcs-common/commit/8b78ee1b20e17b6de63bfed9052af1ca71704801))

## [9.1.0](https://github.com/dvsa/olcs-common/compare/v9.0.0...v9.1.0) (2025-09-25)


### Features

* filter documents by current application ([#243](https://github.com/dvsa/olcs-common/issues/243)) ([72f2229](https://github.com/dvsa/olcs-common/commit/72f2229bb61f69b6f2c297a3c4e1ed4a5db16bca))

## [9.0.0](https://github.com/dvsa/olcs-common/compare/v8.4.0...v9.0.0) (2025-09-19)


### ⚠ BREAKING CHANGES

* CI passing on PHP 8.2 and 8.3, bump dependencies, fix staic analysis VOL-6497 ([#239](https://github.com/dvsa/olcs-common/issues/239))

### Code Refactoring

* CI passing on PHP 8.2 and 8.3, bump dependencies, fix staic analysis VOL-6497 ([#239](https://github.com/dvsa/olcs-common/issues/239)) ([5b598ce](https://github.com/dvsa/olcs-common/commit/5b598ce22b860ab992a6e49319f5cebe9ae7ef99))

## [8.4.0](https://github.com/dvsa/olcs-common/compare/v8.3.0...v8.4.0) (2025-09-08)


### Features

* removed laminas-serializer package and laminas-json from relevant code ([#238](https://github.com/dvsa/olcs-common/issues/238)) ([cc0c940](https://github.com/dvsa/olcs-common/commit/cc0c940a80e17a09a0ac2d6b8cfcf1d36f9dc9c4))

## [8.3.0](https://github.com/dvsa/olcs-common/compare/v8.2.0...v8.3.0) (2025-07-31)


### Features

* add removal status indicator in name formatter for PeopleSelfserve Search object ([#235](https://github.com/dvsa/olcs-common/issues/235)) ([06a1a79](https://github.com/dvsa/olcs-common/commit/06a1a797b5577451f0a05b8fc18e8cc1cc32b0cc))
* added youtube embed for add operating centre newspaper adverts ([#236](https://github.com/dvsa/olcs-common/issues/236)) ([7139459](https://github.com/dvsa/olcs-common/commit/71394597728c41aefaa22a2ea2d60d9b4a81c457))

## [8.2.0](https://github.com/dvsa/olcs-common/compare/v8.1.0...v8.2.0) (2025-07-24)


### Features

* enhance PSV restricted app journey VOL-5882 ([#233](https://github.com/dvsa/olcs-common/issues/233)) ([9bdd684](https://github.com/dvsa/olcs-common/commit/9bdd684ba49297390873143e42e1da7146bb76b5))

## [8.1.0](https://github.com/dvsa/olcs-common/compare/v8.0.0...v8.1.0) (2025-07-16)


### Features

* add link to mprs ([#231](https://github.com/dvsa/olcs-common/issues/231)) ([9533933](https://github.com/dvsa/olcs-common/commit/9533933a9346a310cebac243297f4ee9f7f52821))

## [8.0.0](https://github.com/dvsa/olcs-common/compare/v7.23.1...v8.0.0) (2025-06-30)


### ⚠ BREAKING CHANGES

* Version Helper CD trigger ([#229](https://github.com/dvsa/olcs-common/issues/229))

### Code Refactoring

* Version Helper CD trigger ([#229](https://github.com/dvsa/olcs-common/issues/229)) ([e62bc70](https://github.com/dvsa/olcs-common/commit/e62bc70a8b8160c4f21972ac239ab5858ecbbebd))

## [7.23.1](https://github.com/dvsa/olcs-common/compare/v7.23.0...v7.23.1) (2025-05-27)


### Bug Fixes

* remove stored cards field and code from continuations VOL-6370 ([#226](https://github.com/dvsa/olcs-common/issues/226)) ([65c741c](https://github.com/dvsa/olcs-common/commit/65c741cac3235c6a7b484a91105b3f2b88f35184))

## [7.23.0](https://github.com/dvsa/olcs-common/compare/v7.22.0...v7.23.0) (2025-05-19)


### Features

* added missing brackets in the legislation for Ts&Cs ([#223](https://github.com/dvsa/olcs-common/issues/223)) ([0ece675](https://github.com/dvsa/olcs-common/commit/0ece675432c93b5b5ff9cbeb81b4b4b8672bf787))
* remove defective query ([#225](https://github.com/dvsa/olcs-common/issues/225)) ([ba6622a](https://github.com/dvsa/olcs-common/commit/ba6622a51f0c49bb5efbe5f0eb009f03ac4b6541))

## [7.22.0](https://github.com/dvsa/olcs-common/compare/v7.21.0...v7.22.0) (2025-05-13)


### Features

* remove subsidiary companies option for PSV applications ([#219](https://github.com/dvsa/olcs-common/issues/219)) ([96723aa](https://github.com/dvsa/olcs-common/commit/96723aa16cecebe044b70c2f13e1a83681602762))
* removed old version of one of the paragraphs that was in addition to the new one ([#221](https://github.com/dvsa/olcs-common/issues/221)) ([960489e](https://github.com/dvsa/olcs-common/commit/960489e859fcb2388183b269ca57a0eccae47726))
* update time on VOL messaging to use correct timezone ([#220](https://github.com/dvsa/olcs-common/issues/220)) ([febca28](https://github.com/dvsa/olcs-common/commit/febca28239327da740b186a48afbaf208b7e1531))

## [7.21.0](https://github.com/dvsa/olcs-common/compare/v7.20.1...v7.21.0) (2025-05-08)


### Features

* removed logic for stored cards from payment controller ([#216](https://github.com/dvsa/olcs-common/issues/216)) ([4a21e15](https://github.com/dvsa/olcs-common/commit/4a21e15b9ec5784c6d6614a176220d60cf924b9b))
* updated terms-and-conditions pages ([#217](https://github.com/dvsa/olcs-common/issues/217)) ([c84db20](https://github.com/dvsa/olcs-common/commit/c84db20b14a50c213db275fb6a6035344599218c))


### Bug Fixes

* use get not POST for payment form based on gatway url ([#215](https://github.com/dvsa/olcs-common/issues/215)) ([75ff07e](https://github.com/dvsa/olcs-common/commit/75ff07e7409ec6d86a84b9ebb73a69ad13696b7b))

## [7.20.1](https://github.com/dvsa/olcs-common/compare/v7.20.0...v7.20.1) (2025-05-02)


### Bug Fixes

* made changes to make up to fix irregularities VOL 5871 ([#213](https://github.com/dvsa/olcs-common/issues/213)) ([cd1ed32](https://github.com/dvsa/olcs-common/commit/cd1ed329c128dfec942e5c3c168d1df67eea83cc))

## [7.20.0](https://github.com/dvsa/olcs-common/compare/v7.19.0...v7.20.0) (2025-05-02)


### Features

* new main occupation criteria guidance page ([#211](https://github.com/dvsa/olcs-common/issues/211)) ([4aad9dd](https://github.com/dvsa/olcs-common/commit/4aad9dd3c3572e54526798b20ff05c61de65d74f))

## [7.19.0](https://github.com/dvsa/olcs-common/compare/v7.18.2...v7.19.0) (2025-04-23)


### Features

* updated wording on Ts ans Cs plus updated markup to meet GDS ([#209](https://github.com/dvsa/olcs-common/issues/209)) ([3756725](https://github.com/dvsa/olcs-common/commit/3756725789be1df3cd8419344000c7d7231382ea))

## [7.18.2](https://github.com/dvsa/olcs-common/compare/v7.18.1...v7.18.2) (2025-04-10)


### Miscellaneous Chores

* updated cookie related markups to meet GUDS standards ([#207](https://github.com/dvsa/olcs-common/issues/207)) ([1bd6a9d](https://github.com/dvsa/olcs-common/commit/1bd6a9dc6d9a3e4749821a98470beae2616a3bc4))

## [7.18.1](https://github.com/dvsa/olcs-common/compare/v7.18.0...v7.18.1) (2025-03-31)


### Bug Fixes

* set unlink flag so unwanted uploads are unlinked from licence before deletion to keep change history clean ([#205](https://github.com/dvsa/olcs-common/issues/205)) ([77fe082](https://github.com/dvsa/olcs-common/commit/77fe082d1e5fc2a04a920522c87e61fa9827b4b5))

## [7.18.0](https://github.com/dvsa/olcs-common/compare/v7.17.2...v7.18.0) (2025-03-13)


### Features

* filter messaging subjects by active categories VOL-6069 ([#201](https://github.com/dvsa/olcs-common/issues/201)) ([e719a31](https://github.com/dvsa/olcs-common/commit/e719a3198908a58ef6635e1463a16ab15423e44c))

## [7.17.2](https://github.com/dvsa/olcs-common/compare/v7.17.1...v7.17.2) (2025-02-28)


### Bug Fixes

* hidden messages when a user is deleted ([#198](https://github.com/dvsa/olcs-common/issues/198)) ([d91f507](https://github.com/dvsa/olcs-common/commit/d91f507b55f25948bcf31cb30848be9662e784cd))

## [7.17.1](https://github.com/dvsa/olcs-common/compare/v7.17.0...v7.17.1) (2025-02-11)


### Bug Fixes

* VOL-6064 internal search navigation now follows GDS pattern ([#192](https://github.com/dvsa/olcs-common/issues/192)) ([c9ab5a4](https://github.com/dvsa/olcs-common/commit/c9ab5a47bac27d35b49915cf23a4434233cc7eb6))

## [7.17.0](https://github.com/dvsa/olcs-common/compare/v7.16.0...v7.17.0) (2025-02-06)


### Features

* added new validator to make sure user is operator admin VOL-5918 ([#191](https://github.com/dvsa/olcs-common/issues/191)) ([1a184a3](https://github.com/dvsa/olcs-common/commit/1a184a38f38be70a39fbc416f73f3e5ae8bb2d18))
* notify user document has been uploaded ([#186](https://github.com/dvsa/olcs-common/issues/186)) ([17ee465](https://github.com/dvsa/olcs-common/commit/17ee465d7795a5206eb78ddc5935d70cc5b96e9b))
* notify user document has been uploaded ([#188](https://github.com/dvsa/olcs-common/issues/188)) ([897e972](https://github.com/dvsa/olcs-common/commit/897e972930b343fd144a9d371c9133e2e9a4857d))

## [7.16.0](https://github.com/dvsa/olcs-common/compare/v7.15.0...v7.16.0) (2024-12-10)


### Features

* operator user type now available in RefData ([#184](https://github.com/dvsa/olcs-common/issues/184)) ([20803c4](https://github.com/dvsa/olcs-common/commit/20803c463a71f6988da09b59d25810714d75df5c))

## [7.15.0](https://github.com/dvsa/olcs-common/compare/v7.14.0...v7.15.0) (2024-11-19)


### Features

* VOL-4718 permission service prevents last operator admin being deleted ([#182](https://github.com/dvsa/olcs-common/issues/182)) ([0e98b2a](https://github.com/dvsa/olcs-common/commit/0e98b2a16a915fe2712baec4fab6d8a5d4e1373a))

## [7.14.0](https://github.com/dvsa/olcs-common/compare/v7.13.1...v7.14.0) (2024-11-15)


### Features

* check whether terms agreed VOL-5664 ([#180](https://github.com/dvsa/olcs-common/issues/180)) ([d2b9b79](https://github.com/dvsa/olcs-common/commit/d2b9b795ec3aa2862db7d72c6253788f51c78673))

## [7.13.1](https://github.com/dvsa/olcs-common/compare/v7.13.0...v7.13.1) (2024-11-12)


### Bug Fixes

* span `status` col by 2 ([#178](https://github.com/dvsa/olcs-common/issues/178)) ([9a48391](https://github.com/dvsa/olcs-common/commit/9a48391b641c1ff551866088a494887dc6736616))

## [7.13.0](https://github.com/dvsa/olcs-common/compare/v7.12.0...v7.13.0) (2024-11-05)


### Features

* remove option to change correspondence type ([#176](https://github.com/dvsa/olcs-common/issues/176)) ([74c6f6c](https://github.com/dvsa/olcs-common/commit/74c6f6c5c3e6754b457690335a83b21a8dedff9d))

## [7.12.0](https://github.com/dvsa/olcs-common/compare/v7.11.3...v7.12.0) (2024-10-02)


### Features

* added markup for operator admin login required information box ([#172](https://github.com/dvsa/olcs-common/issues/172)) ([1019004](https://github.com/dvsa/olcs-common/commit/1019004877c9b0e2e3b7c82738b2f159353f24f0))

## [7.11.3](https://github.com/dvsa/olcs-common/compare/v7.11.2...v7.11.3) (2024-09-25)


### Bug Fixes

* ensure submit button renders at the end of the continuation finance form ([#170](https://github.com/dvsa/olcs-common/issues/170)) ([6ef8fc9](https://github.com/dvsa/olcs-common/commit/6ef8fc9f4a3177d18c9508a9294282d13860b19f))

## [7.11.2](https://github.com/dvsa/olcs-common/compare/v7.11.1...v7.11.2) (2024-09-24)


### Bug Fixes

* ensure submit button renders at the end of the Submit evidence form ([#168](https://github.com/dvsa/olcs-common/issues/168)) ([9ce54e6](https://github.com/dvsa/olcs-common/commit/9ce54e6f57fbc7c774e60509783797340101a7f2))

## [7.11.1](https://github.com/dvsa/olcs-common/compare/v7.11.0...v7.11.1) (2024-09-17)


### Bug Fixes

* VOL-5798 fix formatting for file upload radio buttons ([#166](https://github.com/dvsa/olcs-common/issues/166)) ([8cedfa8](https://github.com/dvsa/olcs-common/commit/8cedfa8027b3dd3fb481dccacfd0f337ae58127c))

## [7.11.0](https://github.com/dvsa/olcs-common/compare/v7.10.0...v7.11.0) (2024-09-10)


### Features

* remove send by post radios ([#164](https://github.com/dvsa/olcs-common/issues/164)) ([a95bca8](https://github.com/dvsa/olcs-common/commit/a95bca8bd5645c59b70f2de4aa2b686c076f60fa))

## [7.10.0](https://github.com/dvsa/olcs-common/compare/v7.9.0...v7.10.0) (2024-09-06)


### Features

* Render form elements in order defined; not fieldset first ([#162](https://github.com/dvsa/olcs-common/issues/162)) ([d2facff](https://github.com/dvsa/olcs-common/commit/d2facffa69c21892df838eedc745a1d620c76feb))

## [7.9.0](https://github.com/dvsa/olcs-common/compare/v7.8.0...v7.9.0) (2024-08-29)


### Features

* VOL-5536 update addresses to the new Quarry House ([#161](https://github.com/dvsa/olcs-common/issues/161)) ([4e3c899](https://github.com/dvsa/olcs-common/commit/4e3c899e7c28f07d6430b30b6b4fffc214237783))
* VOL-5748 update ReturnToAddress formatter for new Quarry House address ([#160](https://github.com/dvsa/olcs-common/issues/160)) ([ab115d5](https://github.com/dvsa/olcs-common/commit/ab115d5c39163e53e40a92508aaeb24aad70e4d8))


### Bug Fixes

* add PersonName helper support for title field RefData array. ([#158](https://github.com/dvsa/olcs-common/issues/158)) ([4c83d4d](https://github.com/dvsa/olcs-common/commit/4c83d4d5c9985a77d35498f5f1e29135bee9744d))

## [7.8.0](https://github.com/dvsa/olcs-common/compare/v7.7.1...v7.8.0) (2024-08-21)


### Features

* VOL-5305 add TC Consultant role ([#155](https://github.com/dvsa/olcs-common/issues/155)) ([f3ff4c6](https://github.com/dvsa/olcs-common/commit/f3ff4c618078484c2681340899dd503a5ea52312))

## [7.7.1](https://github.com/dvsa/olcs-common/compare/v7.7.0...v7.7.1) (2024-08-12)


### Bug Fixes

* **bug:** now shows conversations for licence variations ([#150](https://github.com/dvsa/olcs-common/issues/150)) ([35e1d50](https://github.com/dvsa/olcs-common/commit/35e1d5029588a01d1eb804fbd04757e50b32ad0c))
* Message status tags - case fix and add unit test for Formatter. ([#153](https://github.com/dvsa/olcs-common/issues/153)) ([2e0fccb](https://github.com/dvsa/olcs-common/commit/2e0fccb9f4be887bd404bb5460ff8e940fc08eb6))

## [7.7.0](https://github.com/dvsa/olcs-common/compare/v7.6.0...v7.7.0) (2024-08-08)


### Features

* VOL-5249 add feature toggle for transport consultant role ([#147](https://github.com/dvsa/olcs-common/issues/147)) ([ab6dcec](https://github.com/dvsa/olcs-common/commit/ab6dcec0baf6a31c974faf06614684c9e89f7a9e))


### Bug Fixes

* Adds a unit test for the message status formatter ([918df29](https://github.com/dvsa/olcs-common/commit/918df29d655510e108a86c058fc1943d5dad2db6))
* correct case of EBSR Variation and File Status tags. ([#151](https://github.com/dvsa/olcs-common/issues/151)) ([00e6938](https://github.com/dvsa/olcs-common/commit/00e69382e288da3d0c3cc98d7071db932e05ded1))
* fix "Special Restricted" radio alignment ([#148](https://github.com/dvsa/olcs-common/issues/148)) ([918df29](https://github.com/dvsa/olcs-common/commit/918df29d655510e108a86c058fc1943d5dad2db6))
* fix pattern escape console error align with validator ([#152](https://github.com/dvsa/olcs-common/issues/152)) ([917bce6](https://github.com/dvsa/olcs-common/commit/917bce6e6631e678a7648637a52f575e65ef6612))
* Make messaging status tags the correct case for GDS frontend ([#145](https://github.com/dvsa/olcs-common/issues/145)) ([cf42f69](https://github.com/dvsa/olcs-common/commit/cf42f6993868fba36c10d457f05af5ec73cc0717))
* Stop using display:block to unhide an element and just remove display:none ([918df29](https://github.com/dvsa/olcs-common/commit/918df29d655510e108a86c058fc1943d5dad2db6))

## [7.6.0](https://github.com/dvsa/olcs-common/compare/v7.5.1...v7.6.0) (2024-07-29)


### Features

* Make document links open in new browser window to accommodate html file snapshots better. ([#143](https://github.com/dvsa/olcs-common/issues/143)) ([7cebbb7](https://github.com/dvsa/olcs-common/commit/7cebbb71edfbaecb79b789fbdfd479936557ef9b))
* remove caseworkers family name on `selfserve` ([#141](https://github.com/dvsa/olcs-common/issues/141)) ([409b7b1](https://github.com/dvsa/olcs-common/commit/409b7b1e2a8123e97f582d90ed0f5c8f642d9f96))
* Set SameSite cookie attr to clean up some browser console warnings. ([#142](https://github.com/dvsa/olcs-common/issues/142)) ([4dcca2c](https://github.com/dvsa/olcs-common/commit/4dcca2cd24586106d19e0e82ed6cf00eceecd10c))

## [7.5.1](https://github.com/dvsa/olcs-common/compare/v7.5.0...v7.5.1) (2024-07-18)


### Bug Fixes

* Revert translation key fallback view ([#138](https://github.com/dvsa/olcs-common/issues/138)) ([f6e53d9](https://github.com/dvsa/olcs-common/commit/f6e53d9d14f4b61a70259b724f3daf22b23e05fd))
* Revert translation key fallback view ([#140](https://github.com/dvsa/olcs-common/issues/140)) ([671cd31](https://github.com/dvsa/olcs-common/commit/671cd31315b52213bc5ad64ecd0d276b389344dd))

## [7.5.0](https://github.com/dvsa/olcs-common/compare/v7.4.0...v7.5.0) (2024-07-18)


### Features

* make messaging timeframe string an editable translation ([01bd7a4](https://github.com/dvsa/olcs-common/commit/01bd7a421150e8ff6df951fb7bc52e936d5f012c))

## [7.4.0](https://github.com/dvsa/olcs-common/compare/v7.3.0...v7.4.0) (2024-06-18)


### Features

* VOL-4529 remove historic tm code ([#132](https://github.com/dvsa/olcs-common/issues/132)) ([0b2fee7](https://github.com/dvsa/olcs-common/commit/0b2fee7846c4d121d33a143b9705ef02481ed455))
* VOL-4635 remove docman support ([#134](https://github.com/dvsa/olcs-common/issues/134)) ([ce6f429](https://github.com/dvsa/olcs-common/commit/ce6f42981782dfd8b8968a3746f3a5c2a2f39c52))


### Bug Fixes

* VOL-5530 selfserve users don't now have a button to remove themselves ([#133](https://github.com/dvsa/olcs-common/issues/133)) ([fa62db4](https://github.com/dvsa/olcs-common/commit/fa62db4bf42117ab65450289e2ad2aafe7732f7d))

## [7.3.0](https://github.com/dvsa/olcs-common/compare/v7.2.5...v7.3.0) (2024-06-05)


### Features

* feature toggle for dvsa address service ([#129](https://github.com/dvsa/olcs-common/issues/129)) ([3d19f5d](https://github.com/dvsa/olcs-common/commit/3d19f5dd183f3ded8ab6aafe11a1d331115bf56f))


### Bug Fixes

* VOL-5389 Variation and Application now call the correct Application command ([#130](https://github.com/dvsa/olcs-common/issues/130)) ([5ec26bc](https://github.com/dvsa/olcs-common/commit/5ec26bc87e44123c83aafee21342c7c3adb702df))
* VOL-5466 compatibility with latest Laminas form (return formatting to default INTL pattern) ([#128](https://github.com/dvsa/olcs-common/issues/128)) ([6fffe40](https://github.com/dvsa/olcs-common/commit/6fffe407ba633709147cee5cf9856f96aa84efe0))

## [7.2.5](https://github.com/dvsa/olcs-common/compare/v7.2.4...v7.2.5) (2024-05-28)


### Bug Fixes

* convert methods `mapFromForm` and `mapFromErrors` to static ([#126](https://github.com/dvsa/olcs-common/issues/126)) ([f985ed6](https://github.com/dvsa/olcs-common/commit/f985ed6f77fbe55fb451e3540da15b04e9b3448c))

## [7.2.4](https://github.com/dvsa/olcs-common/compare/v7.2.3...v7.2.4) (2024-05-28)


### Reverts

* revert some potentially risky typing ([#124](https://github.com/dvsa/olcs-common/issues/124)) ([19fb66f](https://github.com/dvsa/olcs-common/commit/19fb66f6b84a82f44a672aef98bb5019142b63aa))

## [7.2.3](https://github.com/dvsa/olcs-common/compare/v7.2.2...v7.2.3) (2024-05-22)


### Bug Fixes

* Remove typing from more areas breaking journeys ([#122](https://github.com/dvsa/olcs-common/issues/122)) ([9b90c5d](https://github.com/dvsa/olcs-common/commit/9b90c5d53e2fdf7f61f3827b59daf971d5b96af3))

## [7.2.2](https://github.com/dvsa/olcs-common/compare/v7.2.1...v7.2.2) (2024-05-20)


### Bug Fixes

* Check other cases before trying count() with a null. ([#121](https://github.com/dvsa/olcs-common/issues/121)) ([25b255b](https://github.com/dvsa/olcs-common/commit/25b255bd0dc1e478d05c9cfba7776243e08c7959))
* Fix journey issues caused by typing added in php 8 upgrade ([#118](https://github.com/dvsa/olcs-common/issues/118)) ([96a143d](https://github.com/dvsa/olcs-common/commit/96a143db61f32a69695aba2cfcfd6cec09f1e76c))
* Local view operator journey broken by typing on Escape util. Removed. ([#119](https://github.com/dvsa/olcs-common/issues/119)) ([3db85a5](https://github.com/dvsa/olcs-common/commit/3db85a51f5f8864eadea4cedcb2686429729b2bf))
* Loosen typing - journeys require this to be nullable in some scenarios ([#116](https://github.com/dvsa/olcs-common/issues/116)) ([6132991](https://github.com/dvsa/olcs-common/commit/6132991a44b3c7e13c2baa628fd1b8a67d06f587))
* Remove typing breaking journeys ([#120](https://github.com/dvsa/olcs-common/issues/120)) ([f45c018](https://github.com/dvsa/olcs-common/commit/f45c018045943cf9edda1deeec877fc02f22277a))

## [7.2.1](https://github.com/dvsa/olcs-common/compare/v7.2.0...v7.2.1) (2024-05-10)


### Bug Fixes

* Remove return type causing errors on may journeys ([#114](https://github.com/dvsa/olcs-common/issues/114)) ([aeed864](https://github.com/dvsa/olcs-common/commit/aeed8641f9d3d44cf669e0231ada2e34d9ce7422))

## [7.2.0](https://github.com/dvsa/olcs-common/compare/v7.1.0...v7.2.0) (2024-04-30)


### Bug Fixes

* Missing form field ([#112](https://github.com/dvsa/olcs-common/issues/112)) ([c59cf08](https://github.com/dvsa/olcs-common/commit/c59cf08b2f52dbadec4b39eb9ff998928afd161d))

## [7.1.0](https://github.com/dvsa/olcs-common/compare/v7.0.3...v7.1.0) (2024-04-29)


### Features

* Filter active TM's only ([#108](https://github.com/dvsa/olcs-common/issues/108)) ([32198ba](https://github.com/dvsa/olcs-common/commit/32198ba529ccd3ac2e9ac1dceddb33af71077589))
* Support all PHP ^8.0 versions ([#111](https://github.com/dvsa/olcs-common/issues/111)) ([d644260](https://github.com/dvsa/olcs-common/commit/d644260e8a1dfcc1b463ca7157079cf9b94f3f14))

## [7.0.3](https://github.com/dvsa/olcs-common/compare/v7.0.2...v7.0.3) (2024-04-22)


### Bug Fixes

* What happens next detail missing text ([#104](https://github.com/dvsa/olcs-common/issues/104)) ([5bb5756](https://github.com/dvsa/olcs-common/commit/5bb57568af63807db8b4116de201d2d9385f5bac))

## [7.0.2](https://github.com/dvsa/olcs-common/compare/v7.0.1...v7.0.2) (2024-04-19)


### Bug Fixes

* release bugfixes merged in previous PR ([#105](https://github.com/dvsa/olcs-common/issues/105)) ([7a95eaf](https://github.com/dvsa/olcs-common/commit/7a95eaf2a7824e8732742ee5242b85b2213d26f2))

## [7.0.1](https://github.com/dvsa/olcs-common/compare/v7.0.0...v7.0.1) (2024-04-18)


### Bug Fixes

* locked php version to ~8.0.0 ([#101](https://github.com/dvsa/olcs-common/issues/101)) ([bd1f1a6](https://github.com/dvsa/olcs-common/commit/bd1f1a6bc64449d02363d652726d71b60d22bb19))

## [7.0.0](https://github.com/dvsa/olcs-common/compare/v6.8.0...v7.0.0) (2024-04-17)


### ⚠ BREAKING CHANGES

* PHP 8.0 Compatibility ([#96](https://github.com/dvsa/olcs-common/issues/96))

### Features

* PHP 8.0 Compatibility ([#96](https://github.com/dvsa/olcs-common/issues/96)) ([12ce20f](https://github.com/dvsa/olcs-common/commit/12ce20f7cf7500061d2b56597385bc597bce39e2))

## [6.8.0](https://github.com/dvsa/olcs-common/compare/v6.7.6...v6.8.0) (2024-04-17)


### Features

* TM search filter by app/licence ([#97](https://github.com/dvsa/olcs-common/issues/97)) ([598c790](https://github.com/dvsa/olcs-common/commit/598c7908f2ec5e8b4719339333e095477ef952ca))


### Bug Fixes

* VOL-5243 permission service can now check for manage selfserve user permissions ([#98](https://github.com/dvsa/olcs-common/issues/98)) ([ee1c67c](https://github.com/dvsa/olcs-common/commit/ee1c67ce26f9baf5a6b9b895c61f73da337c459d))

## [6.7.6](https://github.com/dvsa/olcs-common/compare/v6.7.5...v6.7.6) (2024-04-08)


### Bug Fixes

* String '0' should not be ignore when running filtering on search ([#94](https://github.com/dvsa/olcs-common/issues/94)) ([8cfce17](https://github.com/dvsa/olcs-common/commit/8cfce17de6063bec5364c934ba11bb2d6107ae4c))

## [6.7.5](https://github.com/dvsa/olcs-common/compare/v6.7.4...v6.7.5) (2024-04-08)


### Miscellaneous Chores

* add support for `^7.0` for `olcs-logging` ([#92](https://github.com/dvsa/olcs-common/issues/92)) ([829e88d](https://github.com/dvsa/olcs-common/commit/829e88d1e284fca338b1da09ce8158546ae5da8f))

## [6.7.4](https://github.com/dvsa/olcs-common/compare/v6.7.3...v6.7.4) (2024-04-08)


### Bug Fixes

* restore fallback CRUD actions ([#90](https://github.com/dvsa/olcs-common/issues/90)) ([f552ffe](https://github.com/dvsa/olcs-common/commit/f552ffe45fe87cf3b27f7d22ec5dce712e6eb653))

## [6.7.3](https://github.com/dvsa/olcs-common/compare/v6.7.2...v6.7.3) (2024-04-08)


### Bug Fixes

* fix regression in `AbstractGoodsVehiclesController.php` ([#88](https://github.com/dvsa/olcs-common/issues/88)) ([6474555](https://github.com/dvsa/olcs-common/commit/647455518391ebb87ed746d1a4992258d9e1f4b0))

## [6.7.2](https://github.com/dvsa/olcs-common/compare/v6.7.1...v6.7.2) (2024-04-05)


### Bug Fixes

* removed non-FQCN support in TableBuilder ([#84](https://github.com/dvsa/olcs-common/issues/84)) ([8bff6bc](https://github.com/dvsa/olcs-common/commit/8bff6bcf5e2eb059883b8dcd17a676c04e5817bb))

## [6.7.1](https://github.com/dvsa/olcs-common/compare/v6.7.0...v6.7.1) (2024-04-05)


### Bug Fixes

* Reinstate casting attributes to string ([#85](https://github.com/dvsa/olcs-common/issues/85)) ([7758876](https://github.com/dvsa/olcs-common/commit/775887637c823679d7bc8a4f2b9e20855eac1847))

## [6.7.0](https://github.com/dvsa/olcs-common/compare/v6.6.1...v6.7.0) (2024-04-03)


### Features

* Remove deleted status filter ([#82](https://github.com/dvsa/olcs-common/issues/82)) ([613ab07](https://github.com/dvsa/olcs-common/commit/613ab07ac63ede8a2dbffd38f4670bee2247e4b7))
* VOL-4786 fix CI ([#80](https://github.com/dvsa/olcs-common/issues/80)) ([3ad9d2d](https://github.com/dvsa/olcs-common/commit/3ad9d2d10caab8e890a7a95e06c4aba44d256a50))


### Bug Fixes

* Don't show first ready by for message if it's the author ([#81](https://github.com/dvsa/olcs-common/issues/81)) ([8c6b16d](https://github.com/dvsa/olcs-common/commit/8c6b16dd210f26e1acab330f1d686637a43fe356))

## [6.6.1](https://github.com/dvsa/olcs-common/compare/v6.6.0...v6.6.1) (2024-03-26)


### Bug Fixes

* Consistent callback call ([#77](https://github.com/dvsa/olcs-common/issues/77)) ([1702829](https://github.com/dvsa/olcs-common/commit/17028293322dc90c2445be1b7b81fe556841bfa6))

## [6.6.0](https://github.com/dvsa/olcs-common/compare/v6.5.0...v6.6.0) (2024-03-26)


### Features

* Add list conversations permission const ([#76](https://github.com/dvsa/olcs-common/issues/76)) ([052306f](https://github.com/dvsa/olcs-common/commit/052306f34a553d1c2fd02bdf18750b6a1d2c79da))
* Null check elastic search terms for filtering transport managers ([#75](https://github.com/dvsa/olcs-common/issues/75)) ([7705662](https://github.com/dvsa/olcs-common/commit/77056621549841b89eaf7f826175239d9d74c759))

## [6.5.0](https://github.com/dvsa/olcs-common/compare/v6.4.0...v6.5.0) (2024-03-20)


### Features

* ConversationMessage Formatter Refactor & Caseworker Name Reveal and Footer ([#72](https://github.com/dvsa/olcs-common/issues/72)) ([45d6a95](https://github.com/dvsa/olcs-common/commit/45d6a95c2527fb3e2e4a4b41d763381b7f2b55a4))

## [6.4.0](https://github.com/dvsa/olcs-common/compare/v6.3.3...v6.4.0) (2024-03-18)


### Features

* Message footer showing operator first read ([#69](https://github.com/dvsa/olcs-common/issues/69)) ([9782b09](https://github.com/dvsa/olcs-common/commit/9782b09514f977b17c56ed94989954dea7df03d7))

## [6.3.3](https://github.com/dvsa/olcs-common/compare/v6.3.2...v6.3.3) (2024-03-11)


### Bug Fixes

* add `isSelf` to `Permission` service ([#67](https://github.com/dvsa/olcs-common/issues/67)) ([73273eb](https://github.com/dvsa/olcs-common/commit/73273eb8dd9439402df87dd37908bcb4889f9c4f))

## [6.3.2](https://github.com/dvsa/olcs-common/compare/v6.3.1...v6.3.2) (2024-03-08)


### Miscellaneous Chores

* Tidy up laminas 2 3 compatibility vol 3761 ([#63](https://github.com/dvsa/olcs-common/issues/63)) ([d795d54](https://github.com/dvsa/olcs-common/commit/d795d54886d20c25a3d572c4572db94531644147))

## [6.3.1](https://github.com/dvsa/olcs-common/compare/v6.3.0...v6.3.1) (2024-03-08)


### Bug Fixes

* VOL-5103 change abstract factory to pass in permission service to application type of licence controller ([#64](https://github.com/dvsa/olcs-common/issues/64)) ([520f621](https://github.com/dvsa/olcs-common/commit/520f621277eed0dfba8972235a596e34aa787f36))

## [6.3.0](https://github.com/dvsa/olcs-common/compare/v6.2.2...v6.3.0) (2024-03-06)


### Features

* Messaging enable across all sections ([#57](https://github.com/dvsa/olcs-common/issues/57)) ([8b30557](https://github.com/dvsa/olcs-common/commit/8b30557bcfb33e926e16f3c1c073dc55bc709430))


### Bug Fixes

* apply PHP 7.4 Rector set ([#58](https://github.com/dvsa/olcs-common/issues/58)) ([da2e828](https://github.com/dvsa/olcs-common/commit/da2e82822bc9af5ebd446ddbbf75e1ad108ebfc7))

## [6.2.2](https://github.com/dvsa/olcs-common/compare/v6.2.1...v6.2.2) (2024-03-05)


### Bug Fixes

* VOL-5103 limit the access of read only users to certain data and  actions ([#52](https://github.com/dvsa/olcs-common/issues/52)) ([d4a40a4](https://github.com/dvsa/olcs-common/commit/d4a40a49aa8ae1149a6cf7f62a654b2608173cb7))

## [6.2.1](https://github.com/dvsa/olcs-common/compare/v6.2.0...v6.2.1) (2024-03-04)


### Bug Fixes

* Added missed system-admin and internal-irhp-admin as consts to RefData ([#59](https://github.com/dvsa/olcs-common/issues/59)) ([f4dbc1a](https://github.com/dvsa/olcs-common/commit/f4dbc1abd87ed5e315f20d217c631c4da5abfe52))

## [6.2.0](https://github.com/dvsa/olcs-common/compare/v6.1.1...v6.2.0) (2024-03-01)


### Features

* TableBuilder hide title ([#51](https://github.com/dvsa/olcs-common/issues/51)) ([c31bb1c](https://github.com/dvsa/olcs-common/commit/c31bb1c4cdba09326543dae6f8820e454b51ac4d))

## [6.1.1](https://github.com/dvsa/olcs-common/compare/v6.1.0...v6.1.1) (2024-02-29)


### Bug Fixes

* add `FormCollection` overwrite to service manager ([#53](https://github.com/dvsa/olcs-common/issues/53)) ([378c106](https://github.com/dvsa/olcs-common/commit/378c106e2d483fb7170f9b74de2784734c92f21b))
* remove beta badge from phase banner ([#55](https://github.com/dvsa/olcs-common/issues/55)) ([e21adde](https://github.com/dvsa/olcs-common/commit/e21adde6789aa9f1303fecfa372dbd6eb21de5a4))

## [6.1.0](https://github.com/dvsa/olcs-common/compare/v6.0.0...v6.1.0) (2024-02-28)


### Features

* Enable/Disable Messaging File Upload ([#49](https://github.com/dvsa/olcs-common/issues/49)) ([11481d2](https://github.com/dvsa/olcs-common/commit/11481d2b34052a81ad0ca601784a7e163f70939c))

## [6.0.0](https://github.com/dvsa/olcs-common/compare/v5.1.1...v6.0.0) (2024-02-20)


### ⚠ BREAKING CHANGES

* interop/container no longer supported

### Features

* VOL-3691 switch to Psr Container ([#40](https://github.com/dvsa/olcs-common/issues/40)) ([d15be03](https://github.com/dvsa/olcs-common/commit/d15be0383e723acb98351cfb2260bcaa75ade78c))


### Miscellaneous Chores

* bump `olcs/olcs-logging`, `olcs/olcs-transfer`, `olcs/olcs-utils` to ^6.0` ([#47](https://github.com/dvsa/olcs-common/issues/47)) ([75b606d](https://github.com/dvsa/olcs-common/commit/75b606d077005eb72c98e3e3d8d6a896865d22f5))

## [5.1.1](https://github.com/dvsa/olcs-common/compare/v5.1.0...v5.1.1) (2024-02-16)


### Bug Fixes

* reverted olcs-transfer version to target ^5.0.0 ([#43](https://github.com/dvsa/olcs-common/issues/43)) ([0870ed9](https://github.com/dvsa/olcs-common/commit/0870ed9a71e536af647784aa4ee44555b31d8651))

## [5.1.0](https://github.com/dvsa/olcs-common/compare/v5.0.0...v5.1.0) (2024-02-16)


### Features

* merge `project/messaging` to main ([#41](https://github.com/dvsa/olcs-common/issues/41)) ([8f693f0](https://github.com/dvsa/olcs-common/commit/8f693f0d73e0674afded022cb502392123baccdd))

## [5.0.0](https://github.com/dvsa/olcs-common/compare/v5.0.0-beta.10...v5.0.0) (2024-02-14)


### ⚠ BREAKING CHANGES

* createService, getServiceLocator and ServiceLocatorInterface uses are now gone
* migrate to GitHub ([#2](https://github.com/dvsa/olcs-common/issues/2))

### Features

* drop support for Laminas v2 ([#3](https://github.com/dvsa/olcs-common/issues/3)) ([26c78bc](https://github.com/dvsa/olcs-common/commit/26c78bc9a1bac4f71933b573ef808a034f2c89f8))
* migrate to GitHub ([#2](https://github.com/dvsa/olcs-common/issues/2)) ([0a9748e](https://github.com/dvsa/olcs-common/commit/0a9748ed58e43b414dbe572383a0ff85bf98f3de))
* update Convictions and Penalties guidance ([#27](https://github.com/dvsa/olcs-common/issues/27)) ([91df066](https://github.com/dvsa/olcs-common/commit/91df0663623a37079bded6899509def553293f2e))
* VOL-4336 - Table formatter for conversation list ([f7cdb00](https://github.com/dvsa/olcs-common/commit/f7cdb0083b8b3d272a9a02495384cdbb38e6a702))
* VOL-4576 List Messages in Conversation ([#6](https://github.com/dvsa/olcs-common/issues/6)) ([7e8cff2](https://github.com/dvsa/olcs-common/commit/7e8cff216df08d1f9957c4c37e46419473271f09))


### Bug Fixes

* add `create_empty_option` to date fields that can be empty ([#24](https://github.com/dvsa/olcs-common/issues/24)) ([d60833e](https://github.com/dvsa/olcs-common/commit/d60833ee4af99da77c4ac52d7bf2bb04176a3630))
* add `priority` to correctly order TM details form ([#32](https://github.com/dvsa/olcs-common/issues/32)) ([fbdbe8c](https://github.com/dvsa/olcs-common/commit/fbdbe8cee4b41d62cb37c3cfa02762d9510ac7a0))
* add priority to forms to order elements/fieldsets better ([#25](https://github.com/dvsa/olcs-common/issues/25)) ([3610268](https://github.com/dvsa/olcs-common/commit/3610268de9fe738d29007a78542d39089618d22b))
* consolidate `Navigation` and `navigation` ([#31](https://github.com/dvsa/olcs-common/issues/31)) ([cd3b15c](https://github.com/dvsa/olcs-common/commit/cd3b15cff444d0625fe31e691680bb6f60c516c0))
* fix `GenerateContinuationDetails` `service_name` ([#19](https://github.com/dvsa/olcs-common/issues/19)) ([f39ce17](https://github.com/dvsa/olcs-common/commit/f39ce176aca8e544e72fc5b89fe1618e9865e231))
* fix casing of `ViewHelperManager` in call to service manager ([#26](https://github.com/dvsa/olcs-common/issues/26)) ([f8d584e](https://github.com/dvsa/olcs-common/commit/f8d584e73ca7ead2a1cc767ffeba241e9fc8d65a))
* fix textarea character count label translation issue ([#23](https://github.com/dvsa/olcs-common/issues/23)) ([f546db1](https://github.com/dvsa/olcs-common/commit/f546db16a97e41a630988aa559f25250a4a66da1))
* fix type error in `AbstractInputSearch` when `this-&gt;messages` is empty ([#20](https://github.com/dvsa/olcs-common/issues/20)) ([27558a0](https://github.com/dvsa/olcs-common/commit/27558a0d2b7cf49bf5a6061f15a1ac4662a8fa31))
* fix undefined `formHelper` variable ([#37](https://github.com/dvsa/olcs-common/issues/37)) ([530e47d](https://github.com/dvsa/olcs-common/commit/530e47d0d8bc3574ded6ed980694c866894979ba))
* keep `SearchPostcode` fieldset at the top of the `Address` fieldset ([#22](https://github.com/dvsa/olcs-common/issues/22)) ([74fcfd8](https://github.com/dvsa/olcs-common/commit/74fcfd813e1a24e262e466b6ae8b75a331bf733d))
* lowercasing formdatetimeselect. Custom helper wasnt being used and some func. was missing. ([#34](https://github.com/dvsa/olcs-common/issues/34)) ([732adc9](https://github.com/dvsa/olcs-common/commit/732adc92875e5acc7174e62dbde47d30ecf07af3))
* remove cache of element in `FileUploadHelperService` ([#29](https://github.com/dvsa/olcs-common/issues/29)) ([7c4648f](https://github.com/dvsa/olcs-common/commit/7c4648f7ac076445a93e5c2f5772566b2a20442b))
* remove form unit tests ([#9](https://github.com/dvsa/olcs-common/issues/9)) ([6cab5bd](https://github.com/dvsa/olcs-common/commit/6cab5bde0d23951d7c5bd2fe1d1d8c925fa8aa51))
* rename `LicenceChecklist` to `licenceChecklist` ([#30](https://github.com/dvsa/olcs-common/issues/30)) ([25584d8](https://github.com/dvsa/olcs-common/commit/25584d84ca03ceb86d9e992d956ed86bbb25b2bf))
* return empty strings in situations where getValue now returns null since L3 changes ([#33](https://github.com/dvsa/olcs-common/issues/33)) ([8a85a42](https://github.com/dvsa/olcs-common/commit/8a85a4229ec5465b8d99dd0eaee24c16fbbf1fd1))
* update `MAX_LENGTH` to `string` to fix type error ([#35](https://github.com/dvsa/olcs-common/issues/35)) ([1e75cd0](https://github.com/dvsa/olcs-common/commit/1e75cd04abd68c75950d90f49a40cfaa78ff6691))
* VOL-4847 get search working with Laminas 3 ([#18](https://github.com/dvsa/olcs-common/issues/18)) ([cce5be1](https://github.com/dvsa/olcs-common/commit/cce5be127209151bb076b5bb0c48b0f74a2da189))


### Miscellaneous Chores

* add Dependabot config ([#8](https://github.com/dvsa/olcs-common/issues/8)) ([c5737f8](https://github.com/dvsa/olcs-common/commit/c5737f8afb76843ae1bf8895c68fc60a1f98723d))
* release 5.0.0 ([#39](https://github.com/dvsa/olcs-common/issues/39)) ([0858a62](https://github.com/dvsa/olcs-common/commit/0858a62ea43e1bc04742cfb94d0d05219633b4b7))
