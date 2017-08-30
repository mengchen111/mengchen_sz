'use strict';

const webpackConfig = require('../webpack.config.js')
const webpack = require('webpack')

webpack(webpackConfig, function (err, stat) {
    if (err) throw err;
});