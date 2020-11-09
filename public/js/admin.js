/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/admin.js":
/*!*******************************!*\
  !*** ./resources/js/admin.js ***!
  \*******************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _components_layouts_header__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/layouts/header */ \"./resources/js/components/layouts/header.js\");\n// import 'bootstrap'\n// import feather from 'feather-icons'\n// import './admin/media-selector'\n// import './admin/product'\n// jQuery(function($) {\n//     feather.replace()\n// })\n// React\nvar render = wp.element.render;\n\nwindow.addEventListener('DOMContentLoaded', function (event) {\n  var SmartPay = function SmartPay() {\n    return /*#__PURE__*/React.createElement(\"div\", null, /*#__PURE__*/React.createElement(_components_layouts_header__WEBPACK_IMPORTED_MODULE_0__[\"Header\"], null));\n  };\n\n  render( /*#__PURE__*/React.createElement(SmartPay, null), document.getElementById('smartpay'));\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvYWRtaW4uanM/MDcyMiJdLCJuYW1lcyI6WyJyZW5kZXIiLCJ3cCIsImVsZW1lbnQiLCJ3aW5kb3ciLCJhZGRFdmVudExpc3RlbmVyIiwiZXZlbnQiLCJTbWFydFBheSIsImRvY3VtZW50IiwiZ2V0RWxlbWVudEJ5SWQiXSwibWFwcGluZ3MiOiJBQUFBO0FBQUE7QUFBQTtBQUNBO0FBRUE7QUFDQTtBQUVBO0FBQ0E7QUFDQTtBQUVBO0lBRVFBLE0sR0FBV0MsRUFBRSxDQUFDQyxPLENBQWRGLE07QUFFUjtBQUVBRyxNQUFNLENBQUNDLGdCQUFQLENBQXdCLGtCQUF4QixFQUE0QyxVQUFBQyxLQUFLLEVBQUk7QUFDakQsTUFBTUMsUUFBUSxHQUFHLFNBQVhBLFFBQVcsR0FBTTtBQUNuQix3QkFDSSw4Q0FDSSxvQkFBQyxpRUFBRCxPQURKLENBREo7QUFLSCxHQU5EOztBQVFBTixRQUFNLGVBQUMsb0JBQUMsUUFBRCxPQUFELEVBQWVPLFFBQVEsQ0FBQ0MsY0FBVCxDQUF3QixVQUF4QixDQUFmLENBQU47QUFDSCxDQVZEIiwiZmlsZSI6Ii4vcmVzb3VyY2VzL2pzL2FkbWluLmpzLmpzIiwic291cmNlc0NvbnRlbnQiOlsiLy8gaW1wb3J0ICdib290c3RyYXAnXHJcbi8vIGltcG9ydCBmZWF0aGVyIGZyb20gJ2ZlYXRoZXItaWNvbnMnXHJcblxyXG4vLyBpbXBvcnQgJy4vYWRtaW4vbWVkaWEtc2VsZWN0b3InXHJcbi8vIGltcG9ydCAnLi9hZG1pbi9wcm9kdWN0J1xyXG5cclxuLy8galF1ZXJ5KGZ1bmN0aW9uKCQpIHtcclxuLy8gICAgIGZlYXRoZXIucmVwbGFjZSgpXHJcbi8vIH0pXHJcblxyXG4vLyBSZWFjdFxyXG5cclxuY29uc3QgeyByZW5kZXIgfSA9IHdwLmVsZW1lbnRcclxuXHJcbmltcG9ydCB7IEhlYWRlciB9IGZyb20gJy4vY29tcG9uZW50cy9sYXlvdXRzL2hlYWRlcidcclxuXHJcbndpbmRvdy5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgZXZlbnQgPT4ge1xyXG4gICAgY29uc3QgU21hcnRQYXkgPSAoKSA9PiB7XHJcbiAgICAgICAgcmV0dXJuIChcclxuICAgICAgICAgICAgPGRpdj5cclxuICAgICAgICAgICAgICAgIDxIZWFkZXIgLz5cclxuICAgICAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgKVxyXG4gICAgfVxyXG5cclxuICAgIHJlbmRlcig8U21hcnRQYXkgLz4sIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzbWFydHBheScpKVxyXG59KVxyXG4iXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/js/admin.js\n");

/***/ }),

/***/ "./resources/js/components/layouts/header.js":
/*!***************************************************!*\
  !*** ./resources/js/components/layouts/header.js ***!
  \***************************************************/
/*! exports provided: Header */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"Header\", function() { return Header; });\nvar Header = function Header() {\n  return /*#__PURE__*/React.createElement(\"div\", {\n    className: \"text-black bg-white border-bottom\"\n  }, /*#__PURE__*/React.createElement(\"div\", {\n    className: \"container\"\n  }, /*#__PURE__*/React.createElement(\"div\", {\n    className: \"wrap d-none\"\n  }, /*#__PURE__*/React.createElement(\"h2\", null)), /*#__PURE__*/React.createElement(\"div\", {\n    className: \"d-flex align-items-center justify-content-between\"\n  }, /*#__PURE__*/React.createElement(\"h2\", {\n    className: \"text-black\"\n  }, \"SmartPay\"), /*#__PURE__*/React.createElement(\"div\", {\n    className: \"ml-auto\"\n  }))));\n};//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvY29tcG9uZW50cy9sYXlvdXRzL2hlYWRlci5qcz9kMGQwIl0sIm5hbWVzIjpbIkhlYWRlciJdLCJtYXBwaW5ncyI6IkFBQUE7QUFBQTtBQUFPLElBQU1BLE1BQU0sR0FBRyxTQUFUQSxNQUFTLEdBQU07QUFDeEIsc0JBQ0k7QUFBSyxhQUFTLEVBQUM7QUFBZixrQkFDSTtBQUFLLGFBQVMsRUFBQztBQUFmLGtCQUNJO0FBQUssYUFBUyxFQUFDO0FBQWYsa0JBQ0ksK0JBREosQ0FESixlQUlJO0FBQUssYUFBUyxFQUFDO0FBQWYsa0JBQ0k7QUFBSSxhQUFTLEVBQUM7QUFBZCxnQkFESixlQUVJO0FBQUssYUFBUyxFQUFDO0FBQWYsSUFGSixDQUpKLENBREosQ0FESjtBQWFILENBZE0iLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvY29tcG9uZW50cy9sYXlvdXRzL2hlYWRlci5qcy5qcyIsInNvdXJjZXNDb250ZW50IjpbImV4cG9ydCBjb25zdCBIZWFkZXIgPSAoKSA9PiB7XHJcbiAgICByZXR1cm4gKFxyXG4gICAgICAgIDxkaXYgY2xhc3NOYW1lPVwidGV4dC1ibGFjayBiZy13aGl0ZSBib3JkZXItYm90dG9tXCI+XHJcbiAgICAgICAgICAgIDxkaXYgY2xhc3NOYW1lPVwiY29udGFpbmVyXCI+XHJcbiAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cIndyYXAgZC1ub25lXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgPGgyPjwvaDI+XHJcbiAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgIDxkaXYgY2xhc3NOYW1lPVwiZC1mbGV4IGFsaWduLWl0ZW1zLWNlbnRlciBqdXN0aWZ5LWNvbnRlbnQtYmV0d2VlblwiPlxyXG4gICAgICAgICAgICAgICAgICAgIDxoMiBjbGFzc05hbWU9XCJ0ZXh0LWJsYWNrXCI+U21hcnRQYXk8L2gyPlxyXG4gICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3NOYW1lPVwibWwtYXV0b1wiPjwvZGl2PlxyXG4gICAgICAgICAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgICAgIDwvZGl2PlxyXG4gICAgICAgIDwvZGl2PlxyXG4gICAgKVxyXG59XHJcbiJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./resources/js/components/layouts/header.js\n");

/***/ }),

/***/ "./resources/sass/admin.scss":
/*!***********************************!*\
  !*** ./resources/sass/admin.scss ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("// removed by extract-text-webpack-plugin//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvc2Fzcy9hZG1pbi5zY3NzP2EzN2EiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUEiLCJmaWxlIjoiLi9yZXNvdXJjZXMvc2Fzcy9hZG1pbi5zY3NzLmpzIiwic291cmNlc0NvbnRlbnQiOlsiLy8gcmVtb3ZlZCBieSBleHRyYWN0LXRleHQtd2VicGFjay1wbHVnaW4iXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/sass/admin.scss\n");

/***/ }),

/***/ "./resources/sass/app.scss":
/*!*********************************!*\
  !*** ./resources/sass/app.scss ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("// removed by extract-text-webpack-plugin//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvc2Fzcy9hcHAuc2Nzcz8wZTE1Il0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBIiwiZmlsZSI6Ii4vcmVzb3VyY2VzL3Nhc3MvYXBwLnNjc3MuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvLyByZW1vdmVkIGJ5IGV4dHJhY3QtdGV4dC13ZWJwYWNrLXBsdWdpbiJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./resources/sass/app.scss\n");

/***/ }),

/***/ 0:
/*!*******************************************************************************************!*\
  !*** multi ./resources/js/admin.js ./resources/sass/app.scss ./resources/sass/admin.scss ***!
  \*******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! F:\xampp\htdocs\wpsmartpay\wp-content\plugins\wp-smartpay\resources\js\admin.js */"./resources/js/admin.js");
__webpack_require__(/*! F:\xampp\htdocs\wpsmartpay\wp-content\plugins\wp-smartpay\resources\sass\app.scss */"./resources/sass/app.scss");
module.exports = __webpack_require__(/*! F:\xampp\htdocs\wpsmartpay\wp-content\plugins\wp-smartpay\resources\sass\admin.scss */"./resources/sass/admin.scss");


/***/ })

/******/ });