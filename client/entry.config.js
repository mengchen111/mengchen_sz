/**
 * Created by liudian on 9/13/17.
 */

const dirConfig = require('./dir.config.js');
const Glob = require('glob').Glob;

var entryConfig = {};
var options = {
    cwd: dirConfig.srcDir,
    sync: true,
};

var files = new Glob('*/*.js', options).found;      //查找源代码目录下的js文件

files.forEach((file) => {
    entryConfig[file] = `${dirConfig.srcDir}/${file}`;
});

module.exports = entryConfig;

