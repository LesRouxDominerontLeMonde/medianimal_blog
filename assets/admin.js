import $ from 'jquery';
window.$ = $;
window.jQuery = $;

import 'trumbowyg';
import 'trumbowyg/dist/ui/trumbowyg.min.css';

function initTrumbowyg() {
  $('.trumbowyg-editor').trumbowyg();
}

$(function () {
  initTrumbowyg();
  // Ré-initialise après chaque chargement AJAX d'EasyAdmin
  document.addEventListener('ea.form.change', initTrumbowyg);
});