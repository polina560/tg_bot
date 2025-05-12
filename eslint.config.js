import js from '@eslint/js';
import ts from '@typescript-eslint/eslint-plugin';
import vueParser from 'vue-eslint-parser';
import tsParser from '@typescript-eslint/parser';
import vuePlugin from 'eslint-plugin-vue';
import jqueryPlugin from 'eslint-plugin-jquery';
import prettier from 'eslint-config-prettier';
import globals from 'globals';

export default [
  js.configs.recommended,
  {
    files: ['**/*.ts', '**/*.tsx', '**/*.vue'],
    languageOptions: {
      parser: vueParser,
      parserOptions: {
        parser: tsParser,
        ecmaFeatures: {
          jsx: true,
        },
        ecmaVersion: 'latest',
        sourceType: 'module',
      },
      globals: {
        // Глобальные переменные браузера
        ...globals.browser,
        ...globals.node,
        // Другие глобальные переменные
        jQuery: 'readonly',
        $: 'readonly',
        NodeJS: true,
        JQuery: true,
        JSX: true,
      },
    },
    plugins: {
      '@typescript-eslint': ts,
      vue: vuePlugin,
      jquery: jqueryPlugin,
    },
    rules: {
      '@typescript-eslint/consistent-type-definitions': ['error', 'interface'],
      '@typescript-eslint/no-explicit-any': 'off',
      '@typescript-eslint/no-namespace': ['error', { allowDeclarations: true }],
      '@typescript-eslint/no-unused-vars': 'error',
      'constructor-super': 'off',
      'import/extensions': 'off',
      'import/prefer-default-export': 'off',
      'lines-between-class-members': ['error', 'always', { exceptAfterSingleLine: true }],
      'no-bitwise': ['error', { allow: ['~'], int32Hint: true }],
      'no-console': 'error',
      'no-debugger': 'error',
      'no-param-reassign': 'off',
      'no-plusplus': 'off',
      'no-unused-expressions': 'error',
      'no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
      'prefer-destructuring': ['error', { array: true, object: true }, { enforceForRenamedProperties: false }],
      'vue/component-definition-name-casing': ['error', 'PascalCase'],
      'vue/component-name-in-template-casing': ['error', 'PascalCase', { registeredComponentsOnly: true }],
      'vue/match-component-file-name': ['error', { extensions: ['vue'], shouldMatchCase: false }],
      'vue/no-dupe-keys': ['error', { groups: [] }],
      'vue/no-irregular-whitespace': [
        'error',
        {
          skipStrings: true,
          skipComments: false,
          skipRegExps: false,
          skipTemplates: false,
          skipHTMLAttributeValues: false,
          skipHTMLTextContents: false,
        },
      ],
      'vue/no-unused-vars': 'error',
      'vue/order-in-components': ['error'],
      'vue/v-on-event-hyphenation': ['error', 'always', { autofix: true }],
      'vue/script-setup-uses-vars': 'error',
    },
  },
  {
    ignores: ['vue/dist', '/*'],
  },
  prettier,
];
