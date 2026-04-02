/* eslint-disable @typescript-eslint/no-var-requires */

const { merge } = require('webpack-merge');
const path = require('path');

const webpackConfiguration = require('../webpack.config');
const environment = require('./environment');

module.exports = merge(webpackConfiguration, {
  mode: 'development',

  /* Manage source maps generation process */
  devtool: 'eval-source-map',

  /* Additional plugins configuration */
  plugins: [],
});
