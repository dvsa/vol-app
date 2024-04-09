---
sidebar_position: 10
---

# Overview

Docker images are built and pushed to ECR during the CD pipeline, refer to the [ci-cd.md](../../ci-cd.md) for more details.

![Docker Images](../../assets/docker-images.png)

# Layers

The VOL application images are built up of the following high-level layers:

```mermaid
---
config:
    securityLevel: "loose"
---
block-beta
    block:image
        columns 1
        label["VOL Application Image"]
        alpine["<a href='https://hub.docker.com/_/alpine'>Alpine Linux</a>"]
        php["<a href='https://hub.docker.com/_/php'>PHP</a>"]
        base["<a href='https://github.com/dvsa/dvsa-docker-images'>DVSA Base Image</a>"]
        app["<a href='https://github.com/dvsa/vol-app'>VOL Application</a>"]

        style label fill:transparent,stroke:0,stroke-width:0
    end
```

# Repositories

The following repositories are used to store the Docker images for the application components:

| ECR Repository                                                                                | Dockerfile Path                                                                              | Application path                                                           |
| --------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------- |
| [`vol-app/api`](https://054614622558.dkr.ecr.eu-west-1.amazonaws.com/vol-app/api)             | [`infra/docker/api`](https://github.com/dvsa/vol-app/tree/main/infra/docker/api)             | [`app/api`](https://github.com/dvsa/vol-app/tree/main/app/api)             |
| [`vol-app/selfserve`](https://054614622558.dkr.ecr.eu-west-1.amazonaws.com/vol-app/selfserve) | [`infra/docker/selfserve`](https://github.com/dvsa/vol-app/tree/main/infra/docker/selfserve) | [`app/selfserve`](https://github.com/dvsa/vol-app/tree/main/app/selfserve) |
| [`vol-app/internal`](https://054614622558.dkr.ecr.eu-west-1.amazonaws.com/vol-app/internal)   | [`infra/docker/internal`](https://github.com/dvsa/vol-app/tree/main/infra/docker/internal)   | [`app/internal`](https://github.com/dvsa/vol-app/tree/main/app/internal)   |

# Image tagging

The Docker images are tagged during the CD pipeline with the following tags:

```mermaid
gitGraph
    commit id: "[git-sha1]"
    commit tag: "v1.0.0" id: "1.0.0, [git-sha2]"
    commit id: "[git-sha3]"
    commit id: "[git-sha4]"
    commit tag: "v1.1.0" id: "1.1.0, [git-sha5]"
    commit tag: "v1.1.1" id: "1.1.1, [git-sha6]"
    commit id: "[git-sha7]"
    commit id: "[git-sha8]"
    commit tag: "v1.2.0" id: "1.2.0, [git-sha9]"
    commit id: "latest, [git-sha10]"
```

:::info

**Mutable tags**: `latest`

**Immutable tags**: `[major].[minor].[patch]`, & `[git-sha]`.

:::
