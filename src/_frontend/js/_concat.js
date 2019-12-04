/**
 * Každý klíč se vybuildí (a zminifikuje) do souboru /assets/dist/js/{key}.js
 */

module.exports = {
  "imaginator-create": [
    "./js/components/imaginator-create.js"
  ],
  "imaginator-view": [
    "./js/components/imaginator-view.js"
  ],
  "imaginator-input": [
    "./js/components/imaginator-input.js"
  ],
  "libs-admin": [
    "./node_modules/vue/dist/vue.js",
    "./node_modules/dropzone/dist/dropzone.js",
    "./node_modules/lodash/lodash.js",
    "./js/libs/croppie.js",
    "./js/libs/exif.js"
  ],
  "libs-imaginator": [
    "./node_modules/sweetalert2/dist/sweetalert2.min.js"
  ],
  "libs-head": [
    "./node_modules/picturefill/dist/picturefill.min.js",
    "./node_modules/es6-promise/dist/es6-promise.min.js",
    "./node_modules/es6-promise/dist/es6-promise.auto.min.js"
  ],
  "common": [
    "./js/common.js"
  ]
};
