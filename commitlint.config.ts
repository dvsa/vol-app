import type {UserConfig} from '@commitlint/types';
import * as fs from "fs";

const Configuration: UserConfig = {
  extends: ['@commitlint/config-conventional'],
  rules: {
    'scope-enum': [2, 'always', fs.readdirSync('app').filter((file) => fs.statSync(`app/${file}`).isDirectory())],
  }
};

module.exports = Configuration;
