const path = require('path');
const webpack = require('webpack');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');
const config = {
    target: 'web',      //can be omitted as default is 'web'
    entry: './src/statement/hourly.js',

    output: {
        path: path.resolve(__dirname, '../public/dist/webpack'),
        filename: 'statement/hourly.js'
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
                include: path.resolve(__dirname, 'src'),
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
