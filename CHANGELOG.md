# Changelog

## 1.0.0 (2024-06-27)


### Features

* add `poppler-utils` to API image ([#95](https://github.com/dvsa/vol-app/issues/95)) ([84810f6](https://github.com/dvsa/vol-app/commit/84810f64bfa878b152332e72aaa390f563510a86))
* add API ECS resources ([#65](https://github.com/dvsa/vol-app/issues/65)) ([c437e21](https://github.com/dvsa/vol-app/commit/c437e219bb8029b89e7bdbd8f695390a68af8141))
* add devcontainers ([#34](https://github.com/dvsa/vol-app/issues/34)) ([fb3e8f4](https://github.com/dvsa/vol-app/commit/fb3e8f41bee33c8c3ca9e54a7839aec2c0bdbd4a))
* add README ([#35](https://github.com/dvsa/vol-app/issues/35)) ([cb49ecb](https://github.com/dvsa/vol-app/commit/cb49ecbccb7c6e1c623b94cb44d5b5eaefeb3d92))
* add RFC template ([#3](https://github.com/dvsa/vol-app/issues/3)) ([b88ee49](https://github.com/dvsa/vol-app/commit/b88ee49f17503e5e75cf196cda210e3267a18030))
* add security scanning on CI workflow ([#112](https://github.com/dvsa/vol-app/issues/112)) ([2832f47](https://github.com/dvsa/vol-app/commit/2832f472e4ac607323f58d8efea54f20b4ef0605))
* **docker:** add `clamav` to `selfserve` and `internal` ([#99](https://github.com/dvsa/vol-app/issues/99)) ([695d689](https://github.com/dvsa/vol-app/commit/695d689d07f3b89a33280974f3dbfdecfd1b0b83))
* **docker:** add `soffice` to API image ([#82](https://github.com/dvsa/vol-app/issues/82)) ([4f99f2e](https://github.com/dvsa/vol-app/commit/4f99f2e19afea5a200391f3aec447fc89faf7978))
* **docker:** add CLI image ([#91](https://github.com/dvsa/vol-app/issues/91)) ([b72614f](https://github.com/dvsa/vol-app/commit/b72614f3789daacc0840c76dc3cd1f9a1e9f3c38))
* **docker:** add development version of application images ([#90](https://github.com/dvsa/vol-app/issues/90)) ([b668e11](https://github.com/dvsa/vol-app/commit/b668e11a926bff666b3e7ea0d899dea2e2418038))
* **docker:** add GOV.UK One Login redirect to nginx ([#113](https://github.com/dvsa/vol-app/issues/113)) ([3ad631a](https://github.com/dvsa/vol-app/commit/3ad631aea241ee01bbdd39e7480e8eb57be96504))
* **docker:** add internal application Dockerfile ([#41](https://github.com/dvsa/vol-app/issues/41)) ([ae624a2](https://github.com/dvsa/vol-app/commit/ae624a28dd17b0aff0c77ddb3f0cd2dc80e5eb58))
* **docker:** add PHP7.4 API Dockerfile ([#25](https://github.com/dvsa/vol-app/issues/25)) ([77ab726](https://github.com/dvsa/vol-app/commit/77ab72633ba027887a279068c55ca634d9099289))
* **docker:** add selfserve application Docker image ([#78](https://github.com/dvsa/vol-app/issues/78)) ([d8b185d](https://github.com/dvsa/vol-app/commit/d8b185de524fa469d24bef67bdc569fc6e6efb2a))
* **docker:** added `lpr` to the API image ([#89](https://github.com/dvsa/vol-app/issues/89)) ([52e0b9d](https://github.com/dvsa/vol-app/commit/52e0b9d2af300e0e3981fc925f611506d10b7093))
* **docker:** disable OPCache in development build for all images ([#118](https://github.com/dvsa/vol-app/issues/118)) ([988f85a](https://github.com/dvsa/vol-app/commit/988f85af38e9689282d97b043bdeed61a72d9b66))
* **docker:** upgrade to PHP 8.2 ([#111](https://github.com/dvsa/vol-app/issues/111)) ([cfc659f](https://github.com/dvsa/vol-app/commit/cfc659f1359e93f7c7cc01d59ffa98d6c2715a5a))
* merge EC2 config with ECS for API ([#85](https://github.com/dvsa/vol-app/issues/85)) ([2187e98](https://github.com/dvsa/vol-app/commit/2187e9877044927099532ef7f11ec83d24878d0d))
* push Docker images to GHCR ([#66](https://github.com/dvsa/vol-app/issues/66)) ([144fe55](https://github.com/dvsa/vol-app/commit/144fe555a68d2e40660be8a5b7b8762b69868c3a))
* **terraform:** add boilerplate directory structure ([#22](https://github.com/dvsa/vol-app/issues/22)) ([f614038](https://github.com/dvsa/vol-app/commit/f61403840ce7c5cabfd99ee0da6a89411d41b170))
* **terraform:** add CDN infrastructure (part 1) ([#80](https://github.com/dvsa/vol-app/issues/80)) ([74a4dff](https://github.com/dvsa/vol-app/commit/74a4dff420870d45064e2af10f38d2814d69eddf))
* **terraform:** add default tags to resources ([#146](https://github.com/dvsa/vol-app/issues/146)) ([72591c9](https://github.com/dvsa/vol-app/commit/72591c9885defb7935521003191ef8859628d212))
* **terraform:** add ECR for application images ([#30](https://github.com/dvsa/vol-app/issues/30)) ([3e341a8](https://github.com/dvsa/vol-app/commit/3e341a850ab0ea119c7126dc1852e96aae447af2))
* **terraform:** add internal and selfserve ECS clusters ([#70](https://github.com/dvsa/vol-app/issues/70)) ([11a2617](https://github.com/dvsa/vol-app/commit/11a26178cb1e0d6be2d711fac8d0b44a27e76ccd))
* **terraform:** add permissions to ECS tasks role ([#101](https://github.com/dvsa/vol-app/issues/101)) ([e9c7938](https://github.com/dvsa/vol-app/commit/e9c793817d745860bd1c58b386239b95ae8c3426))
* **terraform:** add target group and listener rules ([#104](https://github.com/dvsa/vol-app/issues/104)) ([248de3d](https://github.com/dvsa/vol-app/commit/248de3da55ac605002af2e3eded34ed4c003f9ce))
* **terraform:** migrate to ARM64 architecture ([#97](https://github.com/dvsa/vol-app/issues/97)) ([cce23c3](https://github.com/dvsa/vol-app/commit/cce23c3bc8dcaf0b8337d32a0c14d5a9eddc555d))
* **terraform:** move `A` record for CDN to private zone ([#107](https://github.com/dvsa/vol-app/issues/107)) ([8bdeae9](https://github.com/dvsa/vol-app/commit/8bdeae909510697aed4e68b0d3bbf409453b5124))
* **terraform:** simplify the account setup remote state ([#119](https://github.com/dvsa/vol-app/issues/119)) ([9ed6203](https://github.com/dvsa/vol-app/commit/9ed6203a2e501e817e3908d6849c9b8c8862e42a))


### Bug Fixes

* **docker:** fix `igbinary` and `intl` extension ([#75](https://github.com/dvsa/vol-app/issues/75)) ([5f13859](https://github.com/dvsa/vol-app/commit/5f1385999931ecb856a34f576ead197885c91599))
* **docker:** fix nginx config for selfserve & internal ([#88](https://github.com/dvsa/vol-app/issues/88)) ([ecff991](https://github.com/dvsa/vol-app/commit/ecff991bcd2d2cc8c91c5283cba9ab4de3179da9))
* **docker:** migrate containers to port 8080 ([#96](https://github.com/dvsa/vol-app/issues/96)) ([878c50d](https://github.com/dvsa/vol-app/commit/878c50deb786d43e57475bdc47ad49ee59e605b5))
* **internal:** changed memory limit, max file size and session serialize handler ([#100](https://github.com/dvsa/vol-app/issues/100)) ([45f7dab](https://github.com/dvsa/vol-app/commit/45f7dabea248235f124844f82244fe41989671cc))
* **selfserve:** changed memory limit, max file size and session serialize handler ([#102](https://github.com/dvsa/vol-app/issues/102)) ([8619453](https://github.com/dvsa/vol-app/commit/86194534efa5450029ea5e5e7c2ce8ace93c20f4))
* support non-80 port in local environment ([#117](https://github.com/dvsa/vol-app/issues/117)) ([b5f3da2](https://github.com/dvsa/vol-app/commit/b5f3da2fcdb8dd713bee35cc8c21961f28ec40f1))
* **terraform:** fix assets bucket permissions ([#84](https://github.com/dvsa/vol-app/issues/84)) ([9d610f8](https://github.com/dvsa/vol-app/commit/9d610f82e3c698419f3327726576879795c29139))
* **terraform:** make the GitHub module more re-usable ([#125](https://github.com/dvsa/vol-app/issues/125)) ([3d32bbe](https://github.com/dvsa/vol-app/commit/3d32bbef4d713d5015011b305dbfe12b6f8456b8))
* **terraform:** set container definition `user` to `null` ([#98](https://github.com/dvsa/vol-app/issues/98)) ([6369ca4](https://github.com/dvsa/vol-app/commit/6369ca4f41f16a2a94166f89ff7aa80c8a083324))
