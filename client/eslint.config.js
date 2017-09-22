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
      "SwitchCase": 1
    }],                                         //缩进，两个空格
    'comma-dangle': ['error', "only-multiline"],  //元素与属性与闭括号在不同行时允许使用拖尾逗号，同一行时禁止
    'no-mixed-spaces-and-tabs': 'error',        //禁止混用tab和空格缩进
    'no-console': 'off',                        //禁用 console(关闭)
    'no-var': 'error',                          //要求使用 let 或 const 而不是 var
    'no-unused-vars': 'error',                  //禁止出现未使用过的变量
    'no-mixed-operators': 'error'               //封闭的复杂表达式使用括号括起来明确了开发者的意图，使代码更具可读性
  }
}