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
		"./node_modules/croppie/croppie.min.js"
	],
	"libs-imaginator": [
		"./node_modules/sweetalert2/dist/sweetalert2.min.js"
	],
	"common": [
		"./js/common.js"
	]
};
