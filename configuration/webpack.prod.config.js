/* eslint-disable @typescript-eslint/no-var-requires */
const { merge } = require('webpack-merge');
const webpackConfiguration = require('../webpack.config');
const TerserPlugin = require('terser-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const WebpackEmptyFilesCleanUpPlugin = require('webpack-empty-files-cleanup-plugin');

module.exports = merge(webpackConfiguration, {
  mode: 'production',

  /* Manage source maps generation process. Refer to https://webpack.js.org/configuration/devtool/#production */
  devtool: false,

  /* Optimization configuration */
  optimization: {
    minimize: true,
    removeEmptyChunks: true,
    minimizer: [
      new TerserPlugin()
    ]
  },

  /* Additional plugins configuration */
  plugins: [
    new CleanWebpackPlugin({
      verbose: false,
    }),
    new WebpackEmptyFilesCleanUpPlugin({
      verbose: true,
      dry: false
    }),
  ],
});
