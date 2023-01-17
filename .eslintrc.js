module.exports = {
  env: {
    browser: true,
    es6: true
  },
  overrides: [
  ],
  parserOptions: {
    ecmaVersion: 'latest'
  },
  rules: {
    "indent": [2, "tab"],
    "no-tabs": 0
  },
  globals: {
    "jQuery": "readonly",
    "wp": "readonly"
  }
}
