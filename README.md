# Vehicle Operator Licence (VOL) application

[![CI](https://github.com/dvsa/vol-app/actions/workflows/ci.yaml/badge.svg)](https://github.com/dvsa/vol-app/actions/workflows/ci.yaml) [![CD](https://github.com/dvsa/vol-app/actions/workflows/cd.yaml/badge.svg)](https://github.com/dvsa/vol-app/actions/workflows/cd.yaml)

The `vol-app` repository is the mono-repository for the Vehicle Operator Licence (VOL) application. It is a web application that allows users to apply for a vehicle operator licence, manage their licence and view their licence history.

**Live service:** https://www.vehicle-operator-licensing.service.gov.uk/

## Requirements

- Node.js >= 20.19.2
- npm (compatible version with Node.js)

### Setting up Node.js

This project uses Node.js v20.19.2 or higher. If you're using nvm, you can run:

```bash
nvm use
```

This will automatically switch to the correct Node.js version specified in `.nvmrc`.

### Running the refresh command

After ensuring you have the correct Node version:

```bash
npm run refresh
```

## Documentation

The documentation can be found in the `docs/` directory or hosted as a GitHub page at: https://dvsa.github.io/vol-app.

## Licence

Unless stated otherwise, the codebase is released under the MIT License. This covers both the codebase and any sample code in the documentation. The documentation is © Crown copyright and available under the terms of the Open Government 3.0 licence.
