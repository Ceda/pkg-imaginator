/**
 * Kopírování složek a souborů z /_frontend/node_modules do /assets/dist/vendor
 *
 * Hodí se pro zkopírování celého distu nějakého balíčku
 * Obzvláště, pokud obsahuje hodně souborů, obrázků, fontů apod.
 *
 * V podstatě je to bower :)
 *
 * Příklady, co vložit do mapy:
 *
 * Zkopírování konkrétního souboru
 *
 * 'semantic-ui/dist/semantic.min.css': 'semantic-ui',
 * 'semantic-ui/dist/semantic.min.js': 'semantic-ui',
 *
 * Zkopírování složky včetně podsložek
 *
 * 'semantic-ui/dist/themes/default/**': 'semantic-ui/themes/default',
 * 'bootstrap/dist/**': 'bootstrap',
 * 'font-awesome/css/**': 'font-awesome/css',
 * 'font-awesome/fonts/**': 'font-awesome/fonts',
 *
 */

module.exports = {

  map: {
    //
  },

  afterCopyCallback() {
    // Pokud je třeba zasáhnout do zkopírovaných souborů, zde je prostor
  }

};
