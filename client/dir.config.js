/**
 * Created by liudian on 9/13/17.
 */

const path = require('path')

module.exports = {
  rootDir: path.resolve(__dirname),
  srcDir: path.resolve(__dirname, './src/pages'),                 //源代码目录
  distDir: path.resolve(__dirname, '../public/dist/webpack'),     //目标文件存放目录
}