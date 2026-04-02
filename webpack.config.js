/* eslint-disable @typescript-eslint/no-var-requires */
/*
 * Webpack main configuration file
 */

const path = require('path');
const glob = require('glob');
const globAll = require('glob-all');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const { PurgeCSSPlugin } = require('purgecss-webpack-plugin');
const MediaQueryPlugin = require('media-query-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const environment = require('./configuration/environment');
const files = glob.sync(environment.paths.source + `/{js,scss}/**/!(_)*.+(ts|js|scss|sass)`);


const entries = files.map((file) => ({
  name: path.parse(file).name,
  path: file, // MacOS
  // path: './'+ file, // Windows
})).reduce((red, file) => {
  if (red[file.name] !== undefined) {
    red[file.name].push(file.path);
  } else {
    // eslint-disable-next-line no-param-reassign
    red[file.name] = [file.path];
  }
  return red;
}, {});

module.exports = {
  entry: entries,
  output: {
    filename: 'js/' + environment.prefix + '[name].js',
    path: environment.paths.output,
  },
  module: {
    rules: [
      {
        test: /\.((c|sa|sc)ss)$/i,
        use: [
          {
            loader: MiniCssExtractPlugin.loader
          },
          {
            loader: 'css-loader',
            options: {
              sourceMap: true,
              url: false,
            }
          },
          {
            loader: MediaQueryPlugin.loader
          },
          {
            loader: 'postcss-loader'
          },
          {
            loader: 'sass-loader',
            options: {
              implementation: require('sass'),
              sourceMap: true,
            }
          }
        ],
      },
      {
        test: /\.ts?$/,
        use: 'ts-loader',
        exclude: /node_modules/,
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: 'babel-loader',
      },
      {
        test: /\.(png|gif|jpg|jpeg|svg)$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              name: 'images/[name].[ext]',
              publicPath: '../',
              limit: environment.limits.images,
              useRelativePath: true
            },
          },
        ],
      },
      {
        test: /\.(eot|ttf|woff|woff2|svg)$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              name: 'fonts/' + environment.prefix + '[name].[ext]',
              publicPath: '../',
              limit: environment.limits.fonts,
            },
          },
        ],
      },
    ],
  },
  externals: {
    jQuery: 'jQuery',
    $: 'jQuery',
    wp: 'wp',
  },
  resolve: {
    extensions: ['.tsx', '.ts', '.js'],
  },
  plugins: [
    new RemoveEmptyScriptsPlugin(),
    new MiniCssExtractPlugin({
      filename: 'css/' + environment.prefix + '[name].css'
    }),
    // new PurgeCSSPlugin({
    //   paths: globAll.sync([
    //     path.join(__dirname, '/*.php'),
    //     path.join(__dirname, '/**/*.php'),
    //     path.join(__dirname, '/views/*.php'),
    //     path.join(__dirname, '/views/**/*.php'),
    //     path.join(__dirname, '/assets/js/*.*'),
    //     path.join(__dirname, '/assets/js/**/*.*'),
    //   ]),
    //   safelist: {
    //     standard: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'ins', 'blockquote', 'figure', 'noscript', 'img', 'ol', 'ul', 'li', 'dl', 'dt', 'dd', 'table', 'tbody', 'thead', 'tfoot', 'tr', 'td', 'th', 'fieldset', 'input', 'textarea', 'sub-menu', 'menu-item-has-children', 'current-menu-item', 'open', 'active', 'aligncenter', 'alignleft', 'alignright', 'alignnone', 'page-numbers', 'next', 'prev', 'current', 'screen-reader-text'],
    //     deep: [
    //       /^pt-u-md-(0|10|20|30|40|50|60|70|80|90|100)/,
    //       /^pb-u-md-(0|10|20|30|40|50|60|70|80|90|100)/,
    //       /^pt-d-sm-(0|10|20|30|40|50|60|70|80|90|100)/,
    //       /^pb-d-sm-(0|10|20|30|40|50|60|70|80|90|100)/,
    //       /^mt-u-md-(0|10|20|30|40|50|60|70|80|90|100)/,
    //       /^mb-u-md-(0|10|20|30|40|50|60|70|80|90|100)/,
    //       /^mt-d-sm-(0|10|20|30|40|50|60|70|80|90|100)/,
    //       /^mb-d-sm-(0|10|20|30|40|50|60|70|80|90|100)/,
    //       /^bg-/,
    //       /^bc-/,
    //       /^tc-/,
    //       /^ta-u-md-/,
    //       /^ta-d-sm-/,
    //       /^flatpickr/,
    //     ],
    //   }
    // }),
    new CopyWebpackPlugin({
      patterns: [
        {
          from: path.resolve(environment.paths.source, 'images'),
          to: path.resolve(environment.paths.output, 'images'),
          noErrorOnMissing: true,
          toType: 'dir',
          globOptions: {
            ignore: ['*.DS_Store', 'Thumbs.db'],
          },
        },
        {
          from: path.resolve(environment.paths.source, 'fonts'),
          to: path.resolve(environment.paths.output, 'fonts'),
          noErrorOnMissing: true,
          toType: 'dir',
          globOptions: {
            ignore: ['*.DS_Store', 'Thumbs.db'],
          },
        },
      ],
    }),
    new CleanWebpackPlugin({
      verbose: false,
    }),
  ],
  target: 'web',
};
