import $ from 'jquery';
import 'trumbowyg';
import 'trumbowyg/dist/ui/trumbowyg.min.css';

function initTrumbowyg() {
  $('.trumbowyg-editor').trumbowyg({
    svgPath: '/build/ui/icons.svg' // Chemin vers les icônes SVG copiées dans public/build/ui/
  });
}

$(function () {
  initTrumbowyg();
  // Ré-initialise après chaque chargement AJAX d'EasyAdmin
  document.addEventListener('ea.form.change', initTrumbowyg);
});