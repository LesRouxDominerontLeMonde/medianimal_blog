"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["admin"],{

/***/ "./assets/admin.js":
/*!*************************!*\
  !*** ./assets/admin.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var trumbowyg__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! trumbowyg */ "./node_modules/trumbowyg/dist/trumbowyg.js");
/* harmony import */ var trumbowyg__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(trumbowyg__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var trumbowyg_dist_ui_trumbowyg_min_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! trumbowyg/dist/ui/trumbowyg.min.css */ "./node_modules/trumbowyg/dist/ui/trumbowyg.min.css");



function initTrumbowyg() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('.trumbowyg-editor').trumbowyg({
    svgPath: '/build/ui/icons.svg' // Chemin vers les icônes SVG copiées dans public/build/ui/
  });
}
jquery__WEBPACK_IMPORTED_MODULE_0___default()(function () {
  initTrumbowyg();
  // Ré-initialise après chaque chargement AJAX d'EasyAdmin
  document.addEventListener('ea.form.change', initTrumbowyg);
});

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_trumbowyg_dist_ui_trumbowyg_min_css-node_modules_trumbowyg_dist_trumbowyg_js"], () => (__webpack_exec__("./assets/admin.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYWRtaW4uanMiLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7Ozs7O0FBQXVCO0FBQ0o7QUFDMEI7QUFFN0MsU0FBU0MsYUFBYUEsQ0FBQSxFQUFHO0VBQ3ZCRCw2Q0FBQyxDQUFDLG1CQUFtQixDQUFDLENBQUNFLFNBQVMsQ0FBQztJQUMvQkMsT0FBTyxFQUFFLHFCQUFxQixDQUFDO0VBQ2pDLENBQUMsQ0FBQztBQUNKO0FBRUFILDZDQUFDLENBQUMsWUFBWTtFQUNaQyxhQUFhLENBQUMsQ0FBQztFQUNmO0VBQ0FHLFFBQVEsQ0FBQ0MsZ0JBQWdCLENBQUMsZ0JBQWdCLEVBQUVKLGFBQWEsQ0FBQztBQUM1RCxDQUFDLENBQUMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvYWRtaW4uanMiXSwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcclxuaW1wb3J0ICd0cnVtYm93eWcnO1xyXG5pbXBvcnQgJ3RydW1ib3d5Zy9kaXN0L3VpL3RydW1ib3d5Zy5taW4uY3NzJztcclxuXHJcbmZ1bmN0aW9uIGluaXRUcnVtYm93eWcoKSB7XHJcbiAgJCgnLnRydW1ib3d5Zy1lZGl0b3InKS50cnVtYm93eWcoe1xyXG4gICAgc3ZnUGF0aDogJy9idWlsZC91aS9pY29ucy5zdmcnIC8vIENoZW1pbiB2ZXJzIGxlcyBpY8O0bmVzIFNWRyBjb3Bpw6llcyBkYW5zIHB1YmxpYy9idWlsZC91aS9cclxuICB9KTtcclxufVxyXG5cclxuJChmdW5jdGlvbiAoKSB7XHJcbiAgaW5pdFRydW1ib3d5ZygpO1xyXG4gIC8vIFLDqS1pbml0aWFsaXNlIGFwcsOocyBjaGFxdWUgY2hhcmdlbWVudCBBSkFYIGQnRWFzeUFkbWluXHJcbiAgZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignZWEuZm9ybS5jaGFuZ2UnLCBpbml0VHJ1bWJvd3lnKTtcclxufSk7Il0sIm5hbWVzIjpbIiQiLCJpbml0VHJ1bWJvd3lnIiwidHJ1bWJvd3lnIiwic3ZnUGF0aCIsImRvY3VtZW50IiwiYWRkRXZlbnRMaXN0ZW5lciJdLCJzb3VyY2VSb290IjoiIn0=