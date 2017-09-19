const webpack = require('webpack');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');
const dirConfig = require('./dir.config.js');
const entryConfig = require('./entry.config.js');

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
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['es2015', 'stage-0']
                    },
                },
                exclude: /node_modules/,
            },
            {
                test: /\.vue$/,
                use: {
                    loader: 'vue-loader'
                }
            },
            {
                test: /\.css$/,
                use: [
                    {
                        loader: "style-loader"
                    }, {
                        loader: "css-loader",
                        options: {
                            modules: true,
                        }
                    }
                ]
            },
        ],
    },

    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js'
        }
    },

    plugins: [
        new UglifyJSPlugin({
            uglifyOptions: {
                warnings: false,
            }
        }),
    ]
};

module.exports = config;
