const webpack = require('webpack')
const UglifyJSPlugin = require('uglifyjs-webpack-plugin')
const dirConfig = require('./dir.config.js')
const entryConfig = require('./entry.config.js')

const config = {
  target: 'web',      //can be omitted as default is 'web'
  entry: entryConfig,

  output: {
    path: dirConfig.distDir,
    publicPath: '/dist/webpack',
    filename: '[name]'
  },

  module: {
    rules: [
      {
        test: /\.js$/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              presets: ['env']
            },
          },
          {
            loader: 'eslint-loader',
            options: {
              configFile: `${dirConfig.rootDir}/eslint.config.js`
            }
          }
        ],
        exclude: /node_modules/
      },
      {
        test: /\.vue$/,
        use: [
          {
            loader: 'vue-loader'
          },
          {
            loader: 'eslint-loader',
            options: {
              configFile: `${dirConfig.rootDir}/eslint.config.js`
            }
          }
        ],
        exclude: /node_modules/
      },
      {
        test: /\.css$/,
        loader: 'style-loader!css-loader',
      },
      {
        test: /\.less$/,
        loader: 'style-loader!css-loader!less-loader',
      },
    ],
  },

  resolve: {
    alias: {
      'vue$': 'vue/dist/vue.esm.js',
      'moment$': 'moment/moment.js',
    }
  },

  plugins: [
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: '"production"'
      }
    }),
    new UglifyJSPlugin({
      uglifyOptions: {
        compress: {
          warnings: false
        },
      }
    }),
    new webpack.ProvidePlugin({                   //引用时自动加载
      Vue: ['vue/dist/vue.esm.js', 'default'],
      jQuery: 'jquery',
      'window.jQuery': 'jquery',
      $: 'jquery',
      moment: 'moment',
      _: 'lodash',
      axios: 'axios',
    }),
  ]
};

module.exports = config
