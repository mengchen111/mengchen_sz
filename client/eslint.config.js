module.exports = {
  extends: 'eslint:recommended',
  plugins: [
    'html'
  ],
  env: {
    browser: true,
    es6: true
  },
  parserOptions: {
    ecmaVersion: 6,
    sourceType: 'module'
  },
  rules: {
    'semi': ['error', 'never'],                 //语句结尾不使用分号
    'indent': ['error', 2, {
      "SwitchCase": 1,
    }],                                         //缩进，两个空格
    'brace-style': ['error', '1tbs'],           //强制 one true brace style
    'block-spacing': ['warn', 'always'],        //在单行块内要求使用一个或多个空格
    'comma-dangle': ['error', 'always-multiline'],  //元素与属性与闭括号在不同行时允许使用拖尾逗号，同一行时禁止
    'comma-spacing': ['error', {
      'before': false,
      'after': true,
    }],                                         //禁止逗号前面使用空格，要求逗号后使用空格
    'comma-style': ['error', 'last'],           //要求逗号放在数组元素、对象属性或变量声明之后，且在同一行
    'no-mixed-spaces-and-tabs': 'error',        //禁止混用tab和空格缩进
    'no-console': 'off',                        //禁用 console(关闭)
    'no-var': 'error',                          //要求使用 let 或 const 而不是 var
    'no-unused-vars': 'warn',                   //未使用过的变量提出警告
    'no-useless-escape': 'warn',                //多余的转义符提出警告(从错误改为警告，防止正则上多余的转义符编译失败)
    'no-mixed-operators': 'error',              //封闭的复杂表达式使用括号括起来明确了开发者的意图，使代码更具可读性
    'space-before-function-paren': 'warn',      //函数声明的参数左括号之前需要一个空格
    'func-call-spacing': ['error', 'never'],    //函数调用时，函数与参数的括号间不允许存在空格
  },
}