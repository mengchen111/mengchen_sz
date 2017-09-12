const path = require('path');
const webpack = require('webpack');
const config = {
    target: 'web',      //can be omitted as default is 'web'
    devtool: '#source-map',
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
    devServer: {
        contentBase: "./",          //本地服务器所加载的页面所在的目录
        historyApiFallback: true,   //不跳转
        inline: true,               //实时刷新
        hot: true,                  //HMR热加载
    },
    plugins: [
        /*new webpack.optimize.UglifyJsPlugin({       //compress js
            compress: {
                warnings: false,
            }
        }),*/
        new webpack.HotModuleReplacementPlugin(),   //热加载（Hot Module Replacement）插件
    ],
};

module.exports = config;
