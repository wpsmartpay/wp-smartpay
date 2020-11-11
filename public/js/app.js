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

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\n\nfunction _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === \"string\") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === \"Object\" && o.constructor) n = o.constructor.name; if (n === \"Map\" || n === \"Set\") return Array.from(o); if (n === \"Arguments\" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }\n\nfunction _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }\n\nfunction _iterableToArrayLimit(arr, i) { if (typeof Symbol === \"undefined\" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i[\"return\"] != null) _i[\"return\"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\n;\n\n(function ($) {\n  // SmartPayFormValidation\n  window.SmartPayFormValidator = function (data, rules) {\n    var _this = this;\n\n    /** Instance to self. **/\n    var self = this;\n    this.data = data;\n    this.rules = rules;\n\n    self.validate = function () {\n      return Object.entries(_this.rules).reduce(function (errors, _ref) {\n        var _ref2 = _slicedToArray(_ref, 2),\n            property = _ref2[0],\n            requirements = _ref2[1];\n\n        itemErrors = []; // Check required validation\n\n        if (requirements.required) {\n          var errorMessage = _this.validateRequiredMessage(_this.data[property]);\n\n          if (errorMessage) itemErrors.push(errorMessage);\n        } // Check email validation\n\n\n        if (requirements.email) {\n          var _errorMessage = _this.validateEmailMessage(_this.data[property]);\n\n          if (_errorMessage) itemErrors.push(_errorMessage);\n        } // Check length validation\n\n\n        if (requirements.length) {\n          var _errorMessage2 = _this.validateLengthMessage(_this.data[property], requirements.length);\n\n          if (_errorMessage2) itemErrors.push(_errorMessage2);\n        } // Check value validation\n\n\n        if (requirements.value) {\n          var _errorMessage3 = _this.validateValueMessage(_this.data[property], requirements.value);\n\n          if (_errorMessage3) itemErrors.push(_errorMessage3);\n        }\n\n        if (itemErrors.length) {\n          errors[property] = itemErrors;\n        }\n\n        return errors;\n      }, {});\n    };\n\n    self.validateLengthMessage = function (value, length) {\n      if (value == null) return;\n\n      if (Array.isArray(length)) {\n        if (value.length >= length[0] && value.length <= length[1]) return;\n        return \"must be between \".concat(length[0], \" to \").concat(length[1], \" character\");\n      }\n\n      if (value.length >= length) return;\n      return \"must be \".concat(length, \" or more characters\");\n    };\n\n    self.validateRequiredMessage = function (value) {\n      if (value) return;\n      return 'is required';\n    };\n\n    self.validateEmailMessage = function (value) {\n      var emailFormat = /^([A-Za-z0-9_\\-\\.])+\\@([A-Za-z0-9_\\-\\.])+\\.([A-Za-z]{2,4})$/;\n      if (emailFormat.test(value)) return;\n      return 'is not a valid email';\n    };\n\n    self.validateValueMessage = function (value, compareValue) {\n      if (value === compareValue) return;\n      return \"must be same as \".concat(compareValue);\n    };\n  };\n\n  window.JSUcfirst = function (string) {\n    return string.charAt(0).toUpperCase() + string.slice(1);\n  };\n})(jQuery);//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvYXBwLmpzPzZkNDAiXSwibmFtZXMiOlsiJCIsIndpbmRvdyIsIlNtYXJ0UGF5Rm9ybVZhbGlkYXRvciIsImRhdGEiLCJydWxlcyIsInNlbGYiLCJ2YWxpZGF0ZSIsIk9iamVjdCIsImVudHJpZXMiLCJyZWR1Y2UiLCJlcnJvcnMiLCJwcm9wZXJ0eSIsInJlcXVpcmVtZW50cyIsIml0ZW1FcnJvcnMiLCJyZXF1aXJlZCIsImVycm9yTWVzc2FnZSIsInZhbGlkYXRlUmVxdWlyZWRNZXNzYWdlIiwicHVzaCIsImVtYWlsIiwidmFsaWRhdGVFbWFpbE1lc3NhZ2UiLCJsZW5ndGgiLCJ2YWxpZGF0ZUxlbmd0aE1lc3NhZ2UiLCJ2YWx1ZSIsInZhbGlkYXRlVmFsdWVNZXNzYWdlIiwiQXJyYXkiLCJpc0FycmF5IiwiZW1haWxGb3JtYXQiLCJ0ZXN0IiwiY29tcGFyZVZhbHVlIiwiSlNVY2ZpcnN0Iiwic3RyaW5nIiwiY2hhckF0IiwidG9VcHBlckNhc2UiLCJzbGljZSIsImpRdWVyeSJdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7O0FBQUE7O0FBQUUsQ0FBQyxVQUFVQSxDQUFWLEVBQWE7QUFDWjtBQUNBQyxRQUFNLENBQUNDLHFCQUFQLEdBQStCLFVBQVVDLElBQVYsRUFBZ0JDLEtBQWhCLEVBQXVCO0FBQUE7O0FBQ2xEO0FBQ0EsUUFBTUMsSUFBSSxHQUFHLElBQWI7QUFDQSxTQUFLRixJQUFMLEdBQVlBLElBQVo7QUFDQSxTQUFLQyxLQUFMLEdBQWFBLEtBQWI7O0FBRUFDLFFBQUksQ0FBQ0MsUUFBTCxHQUFnQixZQUFNO0FBQ2xCLGFBQU9DLE1BQU0sQ0FBQ0MsT0FBUCxDQUFlLEtBQUksQ0FBQ0osS0FBcEIsRUFBMkJLLE1BQTNCLENBQ0gsVUFBQ0MsTUFBRCxRQUFzQztBQUFBO0FBQUEsWUFBNUJDLFFBQTRCO0FBQUEsWUFBbEJDLFlBQWtCOztBQUNsQ0Msa0JBQVUsR0FBRyxFQUFiLENBRGtDLENBR2xDOztB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Q0FBYyxDQUFkLEVBQWlCQyxXQUFqQixLQUFpQ0YsTUFBTSxDQUFDRyxLQUFQLENBQWEsQ0FBYixDQUF4QztBQUNILEdBRkQ7QUFHSCxDQS9GQyxFQStGQ0MsTUEvRkQiLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvYXBwLmpzLmpzIiwic291cmNlc0NvbnRlbnQiOlsiOyAoZnVuY3Rpb24gKCQpIHtcclxuICAgIC8vIFNtYXJ0UGF5Rm9ybVZhbGlkYXRpb25cclxuICAgIHdpbmRvdy5TbWFydFBheUZvcm1WYWxpZGF0b3IgPSBmdW5jdGlvbiAoZGF0YSwgcnVsZXMpIHtcclxuICAgICAgICAvKiogSW5zdGFuY2UgdG8gc2VsZi4gKiovXHJcbiAgICAgICAgY29uc3Qgc2VsZiA9IHRoaXNcclxuICAgICAgICB0aGlzLmRhdGEgPSBkYXRhXHJcbiAgICAgICAgdGhpcy5ydWxlcyA9IHJ1bGVzXHJcblxyXG4gICAgICAgIHNlbGYudmFsaWRhdGUgPSAoKSA9PiB7XHJcbiAgICAgICAgICAgIHJldHVybiBPYmplY3QuZW50cmllcyh0aGlzLnJ1bGVzKS5yZWR1Y2UoXHJcbiAgICAgICAgICAgICAgICAoZXJyb3JzLCBbcHJvcGVydHksIHJlcXVpcmVtZW50c10pID0+IHtcclxuICAgICAgICAgICAgICAgICAgICBpdGVtRXJyb3JzID0gW11cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgLy8gQ2hlY2sgcmVxdWlyZWQgdmFsaWRhdGlvblxyXG4gICAgICAgICAgICAgICAgICAgIGlmIChyZXF1aXJlbWVudHMucmVxdWlyZWQpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgZXJyb3JNZXNzYWdlID0gdGhpcy52YWxpZGF0ZVJlcXVpcmVkTWVzc2FnZShcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMuZGF0YVtwcm9wZXJ0eV1cclxuICAgICAgICAgICAgICAgICAgICAgICAgKVxyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoZXJyb3JNZXNzYWdlKSBpdGVtRXJyb3JzLnB1c2goZXJyb3JNZXNzYWdlKVxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgLy8gQ2hlY2sgZW1haWwgdmFsaWRhdGlvblxyXG4gICAgICAgICAgICAgICAgICAgIGlmIChyZXF1aXJlbWVudHMuZW1haWwpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgZXJyb3JNZXNzYWdlID0gdGhpcy52YWxpZGF0ZUVtYWlsTWVzc2FnZShcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMuZGF0YVtwcm9wZXJ0eV1cclxuICAgICAgICAgICAgICAgICAgICAgICAgKVxyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoZXJyb3JNZXNzYWdlKSBpdGVtRXJyb3JzLnB1c2goZXJyb3JNZXNzYWdlKVxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgLy8gQ2hlY2sgbGVuZ3RoIHZhbGlkYXRpb25cclxuICAgICAgICAgICAgICAgICAgICBpZiAocmVxdWlyZW1lbnRzLmxlbmd0aCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBjb25zdCBlcnJvck1lc3NhZ2UgPSB0aGlzLnZhbGlkYXRlTGVuZ3RoTWVzc2FnZShcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMuZGF0YVtwcm9wZXJ0eV0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXF1aXJlbWVudHMubGVuZ3RoXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIClcclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGVycm9yTWVzc2FnZSkgaXRlbUVycm9ycy5wdXNoKGVycm9yTWVzc2FnZSlcclxuICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIC8vIENoZWNrIHZhbHVlIHZhbGlkYXRpb25cclxuICAgICAgICAgICAgICAgICAgICBpZiAocmVxdWlyZW1lbnRzLnZhbHVlKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbnN0IGVycm9yTWVzc2FnZSA9IHRoaXMudmFsaWRhdGVWYWx1ZU1lc3NhZ2UoXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aGlzLmRhdGFbcHJvcGVydHldLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgcmVxdWlyZW1lbnRzLnZhbHVlXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIClcclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGVycm9yTWVzc2FnZSkgaXRlbUVycm9ycy5wdXNoKGVycm9yTWVzc2FnZSlcclxuICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIGlmIChpdGVtRXJyb3JzLmxlbmd0aCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBlcnJvcnNbcHJvcGVydHldID0gaXRlbUVycm9yc1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICByZXR1cm4gZXJyb3JzXHJcbiAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAge31cclxuICAgICAgICAgICAgKVxyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgc2VsZi52YWxpZGF0ZUxlbmd0aE1lc3NhZ2UgPSAodmFsdWUsIGxlbmd0aCkgPT4ge1xyXG4gICAgICAgICAgICBpZiAodmFsdWUgPT0gbnVsbCkgcmV0dXJuXHJcblxyXG4gICAgICAgICAgICBpZiAoQXJyYXkuaXNBcnJheShsZW5ndGgpKSB7XHJcbiAgICAgICAgICAgICAgICBpZiAodmFsdWUubGVuZ3RoID49IGxlbmd0aFswXSAmJiB2YWx1ZS5sZW5ndGggPD0gbGVuZ3RoWzFdKVxyXG4gICAgICAgICAgICAgICAgICAgIHJldHVyblxyXG5cclxuICAgICAgICAgICAgICAgIHJldHVybiBgbXVzdCBiZSBiZXR3ZWVuICR7bGVuZ3RoWzBdfSB0byAke2xlbmd0aFsxXX0gY2hhcmFjdGVyYFxyXG4gICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICBpZiAodmFsdWUubGVuZ3RoID49IGxlbmd0aCkgcmV0dXJuXHJcblxyXG4gICAgICAgICAgICByZXR1cm4gYG11c3QgYmUgJHtsZW5ndGh9IG9yIG1vcmUgY2hhcmFjdGVyc2BcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHNlbGYudmFsaWRhdGVSZXF1aXJlZE1lc3NhZ2UgPSAodmFsdWUpID0+IHtcclxuICAgICAgICAgICAgaWYgKHZhbHVlKSByZXR1cm5cclxuXHJcbiAgICAgICAgICAgIHJldHVybiAnaXMgcmVxdWlyZWQnXHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBzZWxmLnZhbGlkYXRlRW1haWxNZXNzYWdlID0gKHZhbHVlKSA9PiB7XHJcbiAgICAgICAgICAgIGNvbnN0IGVtYWlsRm9ybWF0ID0gL14oW0EtWmEtejAtOV9cXC1cXC5dKStcXEAoW0EtWmEtejAtOV9cXC1cXC5dKStcXC4oW0EtWmEtel17Miw0fSkkL1xyXG5cclxuICAgICAgICAgICAgaWYgKGVtYWlsRm9ybWF0LnRlc3QodmFsdWUpKSByZXR1cm5cclxuXHJcbiAgICAgICAgICAgIHJldHVybiAnaXMgbm90IGEgdmFsaWQgZW1haWwnXHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBzZWxmLnZhbGlkYXRlVmFsdWVNZXNzYWdlID0gKHZhbHVlLCBjb21wYXJlVmFsdWUpID0+IHtcclxuICAgICAgICAgICAgaWYgKHZhbHVlID09PSBjb21wYXJlVmFsdWUpIHJldHVyblxyXG5cclxuICAgICAgICAgICAgcmV0dXJuIGBtdXN0IGJlIHNhbWUgYXMgJHtjb21wYXJlVmFsdWV9YFxyXG4gICAgICAgIH1cclxuICAgIH1cclxuXHJcbiAgICB3aW5kb3cuSlNVY2ZpcnN0ID0gZnVuY3Rpb24gKHN0cmluZykge1xyXG4gICAgICAgIHJldHVybiBzdHJpbmcuY2hhckF0KDApLnRvVXBwZXJDYXNlKCkgKyBzdHJpbmcuc2xpY2UoMSlcclxuICAgIH1cclxufSkoalF1ZXJ5KVxyXG4iXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/js/app.js\n");

/***/ }),

/***/ "./resources/sass/admin.scss":
/*!***********************************!*\
  !*** ./resources/sass/admin.scss ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("// removed by extract-text-webpack-plugin//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvc2Fzcy9hZG1pbi5zY3NzP2Q3YzIiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUEiLCJmaWxlIjoiLi9yZXNvdXJjZXMvc2Fzcy9hZG1pbi5zY3NzLmpzIiwic291cmNlc0NvbnRlbnQiOlsiLy8gcmVtb3ZlZCBieSBleHRyYWN0LXRleHQtd2VicGFjay1wbHVnaW4iXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/sass/admin.scss\n");

/***/ }),

/***/ "./resources/sass/app.scss":
/*!*********************************!*\
  !*** ./resources/sass/app.scss ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("// removed by extract-text-webpack-plugin//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvc2Fzcy9hcHAuc2Nzcz9kYTQxIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBIiwiZmlsZSI6Ii4vcmVzb3VyY2VzL3Nhc3MvYXBwLnNjc3MuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvLyByZW1vdmVkIGJ5IGV4dHJhY3QtdGV4dC13ZWJwYWNrLXBsdWdpbiJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./resources/sass/app.scss\n");

/***/ }),

/***/ 0:
/*!*****************************************************************************************!*\
  !*** multi ./resources/js/app.js ./resources/sass/app.scss ./resources/sass/admin.scss ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! E:\XAMPP\htdocs\wordpress\smartpay\wp-content\plugins\smartpay\resources\js\app.js */"./resources/js/app.js");
__webpack_require__(/*! E:\XAMPP\htdocs\wordpress\smartpay\wp-content\plugins\smartpay\resources\sass\app.scss */"./resources/sass/app.scss");
module.exports = __webpack_require__(/*! E:\XAMPP\htdocs\wordpress\smartpay\wp-content\plugins\smartpay\resources\sass\admin.scss */"./resources/sass/admin.scss");


/***/ })

/******/ });