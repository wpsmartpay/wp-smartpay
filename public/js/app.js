/******/ ;(function (modules) {
    // webpackBootstrap
    /******/ // The module cache
    /******/ var installedModules = {} // The require function
    /******/
    /******/ /******/ function __webpack_require__(moduleId) {
        /******/
        /******/ // Check if module is in cache
        /******/ if (installedModules[moduleId]) {
            /******/ return installedModules[moduleId].exports
            /******/
        } // Create a new module (and put it into the cache)
        /******/ /******/ var module = (installedModules[moduleId] = {
            /******/ i: moduleId,
            /******/ l: false,
            /******/ exports: {},
            /******/
        }) // Execute the module function
        /******/
        /******/ /******/ modules[moduleId].call(
            module.exports,
            module,
            module.exports,
            __webpack_require__
        ) // Flag the module as loaded
        /******/
        /******/ /******/ module.l = true // Return the exports of the module
        /******/
        /******/ /******/ return module.exports
        /******/
    } // expose the modules object (__webpack_modules__)
    /******/
    /******/
    /******/ /******/ __webpack_require__.m = modules // expose the module cache
    /******/
    /******/ /******/ __webpack_require__.c = installedModules // define getter function for harmony exports
    /******/
    /******/ /******/ __webpack_require__.d = function (exports, name, getter) {
        /******/ if (!__webpack_require__.o(exports, name)) {
            /******/ Object.defineProperty(exports, name, {
                enumerable: true,
                get: getter,
            })
            /******/
        }
        /******/
    } // define __esModule on exports
    /******/
    /******/ /******/ __webpack_require__.r = function (exports) {
        /******/ if (typeof Symbol !== 'undefined' && Symbol.toStringTag) {
            /******/ Object.defineProperty(exports, Symbol.toStringTag, {
                value: 'Module',
            })
            /******/
        }
        /******/ Object.defineProperty(exports, '__esModule', { value: true })
        /******/
    } // create a fake namespace object // mode & 1: value is a module id, require it // mode & 2: merge all properties of value into the ns // mode & 4: return value when already ns object // mode & 8|1: behave like require
    /******/
    /******/ /******/ /******/ /******/ /******/ /******/ __webpack_require__.t = function (
        value,
        mode
    ) {
        /******/ if (mode & 1) value = __webpack_require__(value)
        /******/ if (mode & 8) return value
        /******/ if (
            mode & 4 &&
            typeof value === 'object' &&
            value &&
            value.__esModule
        )
            return value
        /******/ var ns = Object.create(null)
        /******/ __webpack_require__.r(ns)
        /******/ Object.defineProperty(ns, 'default', {
            enumerable: true,
            value: value,
        })
        /******/ if (mode & 2 && typeof value != 'string')
            for (var key in value)
                __webpack_require__.d(
                    ns,
                    key,
                    function (key) {
                        return value[key]
                    }.bind(null, key)
                )
        /******/ return ns
        /******/
    } // getDefaultExport function for compatibility with non-harmony modules
    /******/
    /******/ /******/ __webpack_require__.n = function (module) {
        /******/ var getter =
            module && module.__esModule
                ? /******/ function getDefault() {
                      return module['default']
                  }
                : /******/ function getModuleExports() {
                      return module
                  }
        /******/ __webpack_require__.d(getter, 'a', getter)
        /******/ return getter
        /******/
    } // Object.prototype.hasOwnProperty.call
    /******/
    /******/ /******/ __webpack_require__.o = function (object, property) {
        return Object.prototype.hasOwnProperty.call(object, property)
    } // __webpack_public_path__
    /******/
    /******/ /******/ __webpack_require__.p = '/' // Load entry module and return exports
    /******/
    /******/
    /******/ /******/ return __webpack_require__((__webpack_require__.s = 0))
    /******/
})(
    /************************************************************************/
    /******/ {
        /***/ './resources/js/app.js':
            /*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
            /*! no exports provided */
            /***/ function (module, __webpack_exports__, __webpack_require__) {
                'use strict'
                eval(
                    '__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _frontend_payment_payment_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./frontend/payment/payment.js */ "./resources/js/frontend/payment/payment.js");\n/* harmony import */ var _frontend_payment_payment_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_frontend_payment_payment_js__WEBPACK_IMPORTED_MODULE_0__);\nfunction _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }\n\nfunction _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }\n\nfunction _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }\n\nfunction _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\njQuery(function ($) {\n  // SmartPayFormValidation\n  window.SmartPayFormValidator = function (data, rules) {\n    var _this = this;\n\n    /** Instance to self. **/\n    var self = this;\n    this.data = data;\n    this.rules = rules;\n\n    self.validate = function () {\n      return Object.entries(_this.rules).reduce(function (errors, _ref) {\n        var _ref2 = _slicedToArray(_ref, 2),\n            property = _ref2[0],\n            requirements = _ref2[1];\n\n        itemErrors = []; // Check required validation\n\n        if (requirements.required) {\n          var errorMessage = _this.validateRequiredMessage(_this.data[property]);\n\n          if (errorMessage) itemErrors.push(errorMessage);\n        } // Check email validation\n\n\n        if (requirements.email) {\n          var _errorMessage = _this.validateEmailMessage(_this.data[property]);\n\n          if (_errorMessage) itemErrors.push(_errorMessage);\n        } // Check length validation\n\n\n        if (requirements.length) {\n          var _errorMessage2 = _this.validateLengthMessage(_this.data[property], requirements.length);\n\n          if (_errorMessage2) itemErrors.push(_errorMessage2);\n        } // Check value validation\n\n\n        if (requirements.value) {\n          var _errorMessage3 = _this.validateValueMessage(_this.data[property], requirements.value);\n\n          if (_errorMessage3) itemErrors.push(_errorMessage3);\n        }\n\n        if (itemErrors.length) {\n          errors[property] = itemErrors;\n        }\n\n        return errors;\n      }, {});\n    };\n\n    self.validateLengthMessage = function (value, length) {\n      if (value == null) return;\n\n      if (Array.isArray(length)) {\n        if (value.length >= length[0] && value.length <= length[1]) return;\n        return "must be between ".concat(length[0], " to ").concat(length[1], " character");\n      }\n\n      if (value.length >= length) return;\n      return "must be ".concat(length, " or more characters");\n    };\n\n    self.validateRequiredMessage = function (value) {\n      if (value) return;\n      return \'is required\';\n    };\n\n    self.validateEmailMessage = function (value) {\n      var emailFormat = /^([A-Za-z0-9_\\-\\.])+\\@([A-Za-z0-9_\\-\\.])+\\.([A-Za-z]{2,4})$/;\n      if (emailFormat.test(value)) return;\n      return \'is not a valid email\';\n    };\n\n    self.validateValueMessage = function (value, compareValue) {\n      if (value === compareValue) return;\n      return "must be same as ".concat(compareValue);\n    };\n  };\n\n  window.JSUcfirst = function (string) {\n    return string.charAt(0).toUpperCase() + string.slice(1);\n  };\n});\n//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvYXBwLmpzPzZkNDAiXSwibmFtZXMiOlsialF1ZXJ5IiwiJCIsIndpbmRvdyIsIlNtYXJ0UGF5Rm9ybVZhbGlkYXRvciIsImRhdGEiLCJydWxlcyIsInNlbGYiLCJ2YWxpZGF0ZSIsIk9iamVjdCIsImVudHJpZXMiLCJyZWR1Y2UiLCJlcnJvcnMiLCJwcm9wZXJ0eSIsInJlcXVpcmVtZW50cyIsIml0ZW1FcnJvcnMiLCJyZXF1aXJlZCIsImVycm9yTWVzc2FnZSIsInZhbGlkYXRlUmVxdWlyZWRNZXNzYWdlIiwicHVzaCIsImVtYWlsIiwidmFsaWRhdGVFbWFpbE1lc3NhZ2UiLCJsZW5ndGgiLCJ2YWxpZGF0ZUxlbmd0aE1lc3NhZ2UiLCJ2YWx1ZSIsInZhbGlkYXRlVmFsdWVNZXNzYWdlIiwiQXJyYXkiLCJpc0FycmF5IiwiZW1haWxGb3JtYXQiLCJ0ZXN0IiwiY29tcGFyZVZhbHVlIiwiSlNVY2ZpcnN0Iiwic3RyaW5nIiwiY2hhckF0IiwidG9VcHBlckNhc2UiLCJzbGljZSJdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7Ozs7O0FBQUFBLE1BQU0sQ0FBQyxVQUFDQyxDQUFELEVBQU87QUFDVjtBQUNBQyxRQUFNLENBQUNDLHFCQUFQLEdBQStCLFVBQVVDLElBQVYsRUFBZ0JDLEtBQWhCLEVBQXVCO0FBQUE7O0FBQ2xEO0FBQ0EsUUFBTUMsSUFBSSxHQUFHLElBQWI7QUFDQSxTQUFLRixJQUFMLEdBQVlBLElBQVo7QUFDQSxTQUFLQyxLQUFMLEdBQWFBLEtBQWI7O0FBRUFDLFFBQUksQ0FBQ0MsUUFBTCxHQUFnQixZQUFNO0FBQ2xCLGFBQU9DLE1BQU0sQ0FBQ0MsT0FBUCxDQUFlLEtBQUksQ0FBQ0osS0FBcEIsRUFBMkJLLE1BQTNCLENBQ0gsVUFBQ0MsTUFBRCxRQUFzQztBQUFBO0FBQUEsWUFBNUJDLFFBQTRCO0FBQUEsWUFBbEJDLFlBQWtCOztBQUNsQ0Msa0JBQVUsR0FBRyxFQUFiLENBRGtDLENBR2xD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QVAsQ0FBYyxDQUFkLEVBQWlCQyxXQUFqQixLQUFpQ0YsTUFBTSxDQUFDRyxLQUFQLENBQWEsQ0FBYixDQUF4QztBQUNILEdBRkQ7QUFHSCxDQS9GSyxDQUFOIiwiZmlsZSI6Ii4vcmVzb3VyY2VzL2pzL2FwcC5qcy5qcyIsInNvdXJjZXNDb250ZW50IjpbImpRdWVyeSgoJCkgPT4ge1xyXG4gICAgLy8gU21hcnRQYXlGb3JtVmFsaWRhdGlvblxyXG4gICAgd2luZG93LlNtYXJ0UGF5Rm9ybVZhbGlkYXRvciA9IGZ1bmN0aW9uIChkYXRhLCBydWxlcykge1xyXG4gICAgICAgIC8qKiBJbnN0YW5jZSB0byBzZWxmLiAqKi9cclxuICAgICAgICBjb25zdCBzZWxmID0gdGhpc1xyXG4gICAgICAgIHRoaXMuZGF0YSA9IGRhdGFcclxuICAgICAgICB0aGlzLnJ1bGVzID0gcnVsZXNcclxuXHJcbiAgICAgICAgc2VsZi52YWxpZGF0ZSA9ICgpID0+IHtcclxuICAgICAgICAgICAgcmV0dXJuIE9iamVjdC5lbnRyaWVzKHRoaXMucnVsZXMpLnJlZHVjZShcclxuICAgICAgICAgICAgICAgIChlcnJvcnMsIFtwcm9wZXJ0eSwgcmVxdWlyZW1lbnRzXSkgPT4ge1xyXG4gICAgICAgICAgICAgICAgICAgIGl0ZW1FcnJvcnMgPSBbXVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAvLyBDaGVjayByZXF1aXJlZCB2YWxpZGF0aW9uXHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKHJlcXVpcmVtZW50cy5yZXF1aXJlZCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBjb25zdCBlcnJvck1lc3NhZ2UgPSB0aGlzLnZhbGlkYXRlUmVxdWlyZWRNZXNzYWdlKFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5kYXRhW3Byb3BlcnR5XVxyXG4gICAgICAgICAgICAgICAgICAgICAgICApXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChlcnJvck1lc3NhZ2UpIGl0ZW1FcnJvcnMucHVzaChlcnJvck1lc3NhZ2UpXHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAvLyBDaGVjayBlbWFpbCB2YWxpZGF0aW9uXHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKHJlcXVpcmVtZW50cy5lbWFpbCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBjb25zdCBlcnJvck1lc3NhZ2UgPSB0aGlzLnZhbGlkYXRlRW1haWxNZXNzYWdlKFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5kYXRhW3Byb3BlcnR5XVxyXG4gICAgICAgICAgICAgICAgICAgICAgICApXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChlcnJvck1lc3NhZ2UpIGl0ZW1FcnJvcnMucHVzaChlcnJvck1lc3NhZ2UpXHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAvLyBDaGVjayBsZW5ndGggdmFsaWRhdGlvblxyXG4gICAgICAgICAgICAgICAgICAgIGlmIChyZXF1aXJlbWVudHMubGVuZ3RoKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbnN0IGVycm9yTWVzc2FnZSA9IHRoaXMudmFsaWRhdGVMZW5ndGhNZXNzYWdlKFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5kYXRhW3Byb3BlcnR5XSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJlcXVpcmVtZW50cy5sZW5ndGhcclxuICAgICAgICAgICAgICAgICAgICAgICAgKVxyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoZXJyb3JNZXNzYWdlKSBpdGVtRXJyb3JzLnB1c2goZXJyb3JNZXNzYWdlKVxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgLy8gQ2hlY2sgdmFsdWUgdmFsaWRhdGlvblxyXG4gICAgICAgICAgICAgICAgICAgIGlmIChyZXF1aXJlbWVudHMudmFsdWUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgZXJyb3JNZXNzYWdlID0gdGhpcy52YWxpZGF0ZVZhbHVlTWVzc2FnZShcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMuZGF0YVtwcm9wZXJ0eV0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXF1aXJlbWVudHMudmFsdWVcclxuICAgICAgICAgICAgICAgICAgICAgICAgKVxyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoZXJyb3JNZXNzYWdlKSBpdGVtRXJyb3JzLnB1c2goZXJyb3JNZXNzYWdlKVxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKGl0ZW1FcnJvcnMubGVuZ3RoKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGVycm9yc1twcm9wZXJ0eV0gPSBpdGVtRXJyb3JzXHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgIHJldHVybiBlcnJvcnNcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICB7fVxyXG4gICAgICAgICAgICApXHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBzZWxmLnZhbGlkYXRlTGVuZ3RoTWVzc2FnZSA9ICh2YWx1ZSwgbGVuZ3RoKSA9PiB7XHJcbiAgICAgICAgICAgIGlmICh2YWx1ZSA9PSBudWxsKSByZXR1cm5cclxuXHJcbiAgICAgICAgICAgIGlmIChBcnJheS5pc0FycmF5KGxlbmd0aCkpIHtcclxuICAgICAgICAgICAgICAgIGlmICh2YWx1ZS5sZW5ndGggPj0gbGVuZ3RoWzBdICYmIHZhbHVlLmxlbmd0aCA8PSBsZW5ndGhbMV0pXHJcbiAgICAgICAgICAgICAgICAgICAgcmV0dXJuXHJcblxyXG4gICAgICAgICAgICAgICAgcmV0dXJuIGBtdXN0IGJlIGJldHdlZW4gJHtsZW5ndGhbMF19IHRvICR7bGVuZ3RoWzFdfSBjaGFyYWN0ZXJgXHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIGlmICh2YWx1ZS5sZW5ndGggPj0gbGVuZ3RoKSByZXR1cm5cclxuXHJcbiAgICAgICAgICAgIHJldHVybiBgbXVzdCBiZSAke2xlbmd0aH0gb3IgbW9yZSBjaGFyYWN0ZXJzYFxyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgc2VsZi52YWxpZGF0ZVJlcXVpcmVkTWVzc2FnZSA9ICh2YWx1ZSkgPT4ge1xyXG4gICAgICAgICAgICBpZiAodmFsdWUpIHJldHVyblxyXG5cclxuICAgICAgICAgICAgcmV0dXJuICdpcyByZXF1aXJlZCdcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHNlbGYudmFsaWRhdGVFbWFpbE1lc3NhZ2UgPSAodmFsdWUpID0+IHtcclxuICAgICAgICAgICAgY29uc3QgZW1haWxGb3JtYXQgPSAvXihbQS1aYS16MC05X1xcLVxcLl0pK1xcQChbQS1aYS16MC05X1xcLVxcLl0pK1xcLihbQS1aYS16XXsyLDR9KSQvXHJcblxyXG4gICAgICAgICAgICBpZiAoZW1haWxGb3JtYXQudGVzdCh2YWx1ZSkpIHJldHVyblxyXG5cclxuICAgICAgICAgICAgcmV0dXJuICdpcyBub3QgYSB2YWxpZCBlbWFpbCdcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHNlbGYudmFsaWRhdGVWYWx1ZU1lc3NhZ2UgPSAodmFsdWUsIGNvbXBhcmVWYWx1ZSkgPT4ge1xyXG4gICAgICAgICAgICBpZiAodmFsdWUgPT09IGNvbXBhcmVWYWx1ZSkgcmV0dXJuXHJcblxyXG4gICAgICAgICAgICByZXR1cm4gYG11c3QgYmUgc2FtZSBhcyAke2NvbXBhcmVWYWx1ZX1gXHJcbiAgICAgICAgfVxyXG4gICAgfVxyXG5cclxuICAgIHdpbmRvdy5KU1VjZmlyc3QgPSBmdW5jdGlvbiAoc3RyaW5nKSB7XHJcbiAgICAgICAgcmV0dXJuIHN0cmluZy5jaGFyQXQoMCkudG9VcHBlckNhc2UoKSArIHN0cmluZy5zbGljZSgxKVxyXG4gICAgfVxyXG59KVxyXG5cclxuaW1wb3J0ICcuL2Zyb250ZW5kL3BheW1lbnQvcGF5bWVudC5qcydcclxuIl0sInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./resources/js/app.js\n'
                )

                /***/
            },

        /***/ './resources/js/frontend/payment/payment.js':
            /*!**************************************************!*\
  !*** ./resources/js/frontend/payment/payment.js ***!
  \**************************************************/
            /*! no static exports found */
            /***/ function (module, exports) {
                eval(
                    'function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }\n\nfunction _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }\n\nfunction _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }\n\nfunction _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\n;\n\n(function ($) {\n  // SmartPayFormValidation\n  window.SmartPayFormValidator = function (data, rules) {\n    var _this = this;\n\n    /** Instance to self. **/\n    var self = this;\n    this.data = data;\n    this.rules = rules;\n\n    self.validate = function () {\n      return Object.entries(_this.rules).reduce(function (errors, _ref) {\n        var _ref2 = _slicedToArray(_ref, 2),\n            property = _ref2[0],\n            requirements = _ref2[1];\n\n        itemErrors = []; // Check required validation\n\n        if (requirements.required) {\n          var errorMessage = _this.validateRequiredMessage(_this.data[property]);\n\n          if (errorMessage) itemErrors.push(errorMessage);\n        } // Check email validation\n\n\n        if (requirements.email) {\n          var _errorMessage = _this.validateEmailMessage(_this.data[property]);\n\n          if (_errorMessage) itemErrors.push(_errorMessage);\n        } // Check length validation\n\n\n        if (requirements.length) {\n          var _errorMessage2 = _this.validateLengthMessage(_this.data[property], requirements.length);\n\n          if (_errorMessage2) itemErrors.push(_errorMessage2);\n        } // Check value validation\n\n\n        if (requirements.value) {\n          var _errorMessage3 = _this.validateValueMessage(_this.data[property], requirements.value);\n\n          if (_errorMessage3) itemErrors.push(_errorMessage3);\n        }\n\n        if (itemErrors.length) {\n          errors[property] = itemErrors;\n        }\n\n        return errors;\n      }, {});\n    };\n\n    self.validateLengthMessage = function (value, length) {\n      if (value == null) return;\n\n      if (Array.isArray(length)) {\n        if (value.length >= length[0] && value.length <= length[1]) return;\n        return "must be between ".concat(length[0], " to ").concat(length[1], " character");\n      }\n\n      if (value.length >= length) return;\n      return "must be ".concat(length, " or more characters");\n    };\n\n    self.validateRequiredMessage = function (value) {\n      if (value) return;\n      return \'is required\';\n    };\n\n    self.validateEmailMessage = function (value) {\n      var emailFormat = /^([A-Za-z0-9_\\-\\.])+\\@([A-Za-z0-9_\\-\\.])+\\.([A-Za-z]{2,4})$/;\n      if (emailFormat.test(value)) return;\n      return \'is not a valid email\';\n    };\n\n    self.validateValueMessage = function (value, compareValue) {\n      if (value === compareValue) return;\n      return "must be same as ".concat(compareValue);\n    };\n  };\n\n  window.JSUcfirst = function (string) {\n    return string.charAt(0).toUpperCase() + string.slice(1);\n  };\n})(jQuery);//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvYXBwLmpzPzZkNDAiXSwibmFtZXMiOlsiJCIsIndpbmRvdyIsIlNtYXJ0UGF5Rm9ybVZhbGlkYXRvciIsImRhdGEiLCJydWxlcyIsInNlbGYiLCJ2YWxpZGF0ZSIsIk9iamVjdCIsImVudHJpZXMiLCJyZWR1Y2UiLCJlcnJvcnMiLCJwcm9wZXJ0eSIsInJlcXVpcmVtZW50cyIsIml0ZW1FcnJvcnMiLCJyZXF1aXJlZCIsImVycm9yTWVzc2FnZSIsInZhbGlkYXRlUmVxdWlyZWRNZXNzYWdlIiwicHVzaCIsImVtYWlsIiwidmFsaWRhdGVFbWFpbE1lc3NhZ2UiLCJsZW5ndGgiLCJ2YWxpZGF0ZUxlbmd0aE1lc3NhZ2UiLCJ2YWx1ZSIsInZhbGlkYXRlVmFsdWVNZXNzYWdlIiwiQXJyYXkiLCJpc0FycmF5IiwiZW1haWxGb3JtYXQiLCJ0ZXN0IiwiY29tcGFyZVZhbHVlIiwiSlNVY2ZpcnN0Iiwic3RyaW5nIiwiY2hhckF0IiwidG9VcHBlckNhc2UiLCJzbGljZSIsImpRdWVyeSJdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7O0FBQUE7O0FBQUUsQ0FBQyxVQUFVQSxDQUFWLEVBQWE7QUFDWjtBQUNBQyxRQUFNLENBQUNDLHFCQUFQLEdBQStCLFVBQVVDLElBQVYsRUFBZ0JDLEtBQWhCLEVBQXVCO0FBQUE7O0FBQ2xEO0FBQ0EsUUFBTUMsSUFBSSxHQUFHLElBQWI7QUFDQSxTQUFLRixJQUFMLEdBQVlBLElBQVo7QUFDQSxTQUFLQyxLQUFMLEdBQWFBLEtBQWI7O0FBRUFDLFFBQUksQ0FBQ0MsUUFBTCxHQUFnQixZQUFNO0FBQ2xCLGFBQU9DLE1BQU0sQ0FBQ0MsT0FBUCxDQUFlLEtBQUksQ0FBQ0osS0FBcEIsRUFBMkJLLE1BQTNCLENBQ0gsVUFBQ0MsTUFBRCxRQUFzQztBQUFBO0FBQUEsWUFBNUJDLFFBQTRCO0FBQUEsWUFBbEJDLFlBQWtCOztBQUNsQ0Msa0JBQVUsR0FBRyxFQUFiLENBRGtDLENBR2xDOztB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Q0FBYyxDQUFkLEVBQWlCQyxXQUFqQixLQUFpQ0YsTUFBTSxDQUFDRyxLQUFQLENBQWEsQ0FBYixDQUF4QztBQUNILEdBRkQ7QUFHSCxDQS9GQyxFQStGQ0MsTUEvRkQiLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvYXBwLmpzLmpzIiwic291cmNlc0NvbnRlbnQiOlsiOyAoZnVuY3Rpb24gKCQpIHtcbiAgICAvLyBTbWFydFBheUZvcm1WYWxpZGF0aW9uXG4gICAgd2luZG93LlNtYXJ0UGF5Rm9ybVZhbGlkYXRvciA9IGZ1bmN0aW9uIChkYXRhLCBydWxlcykge1xuICAgICAgICAvKiogSW5zdGFuY2UgdG8gc2VsZi4gKiovXG4gICAgICAgIGNvbnN0IHNlbGYgPSB0aGlzXG4gICAgICAgIHRoaXMuZGF0YSA9IGRhdGFcbiAgICAgICAgdGhpcy5ydWxlcyA9IHJ1bGVzXG5cbiAgICAgICAgc2VsZi52YWxpZGF0ZSA9ICgpID0+IHtcbiAgICAgICAgICAgIHJldHVybiBPYmplY3QuZW50cmllcyh0aGlzLnJ1bGVzKS5yZWR1Y2UoXG4gICAgICAgICAgICAgICAgKGVycm9ycywgW3Byb3BlcnR5LCByZXF1aXJlbWVudHNdKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIGl0ZW1FcnJvcnMgPSBbXVxuXG4gICAgICAgICAgICAgICAgICAgIC8vIENoZWNrIHJlcXVpcmVkIHZhbGlkYXRpb25cbiAgICAgICAgICAgICAgICAgICAgaWYgKHJlcXVpcmVtZW50cy5yZXF1aXJlZCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgZXJyb3JNZXNzYWdlID0gdGhpcy52YWxpZGF0ZVJlcXVpcmVkTWVzc2FnZShcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aGlzLmRhdGFbcHJvcGVydHldXG4gICAgICAgICAgICAgICAgICAgICAgICApXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoZXJyb3JNZXNzYWdlKSBpdGVtRXJyb3JzLnB1c2goZXJyb3JNZXNzYWdlKVxuICAgICAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAgICAgLy8gQ2hlY2sgZW1haWwgdmFsaWRhdGlvblxuICAgICAgICAgICAgICAgICAgICBpZiAocmVxdWlyZW1lbnRzLmVtYWlsKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBjb25zdCBlcnJvck1lc3NhZ2UgPSB0aGlzLnZhbGlkYXRlRW1haWxNZXNzYWdlKFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMuZGF0YVtwcm9wZXJ0eV1cbiAgICAgICAgICAgICAgICAgICAgICAgIClcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChlcnJvck1lc3NhZ2UpIGl0ZW1FcnJvcnMucHVzaChlcnJvck1lc3NhZ2UpXG4gICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICAvLyBDaGVjayBsZW5ndGggdmFsaWRhdGlvblxuICAgICAgICAgICAgICAgICAgICBpZiAocmVxdWlyZW1lbnRzLmxlbmd0aCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgZXJyb3JNZXNzYWdlID0gdGhpcy52YWxpZGF0ZUxlbmd0aE1lc3NhZ2UoXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5kYXRhW3Byb3BlcnR5XSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXF1aXJlbWVudHMubGVuZ3RoXG4gICAgICAgICAgICAgICAgICAgICAgICApXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoZXJyb3JNZXNzYWdlKSBpdGVtRXJyb3JzLnB1c2goZXJyb3JNZXNzYWdlKVxuICAgICAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAgICAgLy8gQ2hlY2sgdmFsdWUgdmFsaWRhdGlvblxuICAgICAgICAgICAgICAgICAgICBpZiAocmVxdWlyZW1lbnRzLnZhbHVlKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBjb25zdCBlcnJvck1lc3NhZ2UgPSB0aGlzLnZhbGlkYXRlVmFsdWVNZXNzYWdlKFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMuZGF0YVtwcm9wZXJ0eV0sXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgcmVxdWlyZW1lbnRzLnZhbHVlXG4gICAgICAgICAgICAgICAgICAgICAgICApXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoZXJyb3JNZXNzYWdlKSBpdGVtRXJyb3JzLnB1c2goZXJyb3JNZXNzYWdlKVxuICAgICAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAgICAgaWYgKGl0ZW1FcnJvcnMubGVuZ3RoKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBlcnJvcnNbcHJvcGVydHldID0gaXRlbUVycm9yc1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIHJldHVybiBlcnJvcnNcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIHt9XG4gICAgICAgICAgICApXG4gICAgICAgIH1cblxuICAgICAgICBzZWxmLnZhbGlkYXRlTGVuZ3RoTWVzc2FnZSA9ICh2YWx1ZSwgbGVuZ3RoKSA9PiB7XG4gICAgICAgICAgICBpZiAodmFsdWUgPT0gbnVsbCkgcmV0dXJuXG5cbiAgICAgICAgICAgIGlmIChBcnJheS5pc0FycmF5KGxlbmd0aCkpIHtcbiAgICAgICAgICAgICAgICBpZiAodmFsdWUubGVuZ3RoID49IGxlbmd0aFswXSAmJiB2YWx1ZS5sZW5ndGggPD0gbGVuZ3RoWzFdKVxuICAgICAgICAgICAgICAgICAgICByZXR1cm5cblxuICAgICAgICAgICAgICAgIHJldHVybiBgbXVzdCBiZSBiZXR3ZWVuICR7bGVuZ3RoWzBdfSB0byAke2xlbmd0aFsxXX0gY2hhcmFjdGVyYFxuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBpZiAodmFsdWUubGVuZ3RoID49IGxlbmd0aCkgcmV0dXJuXG5cbiAgICAgICAgICAgIHJldHVybiBgbXVzdCBiZSAke2xlbmd0aH0gb3IgbW9yZSBjaGFyYWN0ZXJzYFxuICAgICAgICB9XG5cbiAgICAgICAgc2VsZi52YWxpZGF0ZVJlcXVpcmVkTWVzc2FnZSA9ICh2YWx1ZSkgPT4ge1xuICAgICAgICAgICAgaWYgKHZhbHVlKSByZXR1cm5cblxuICAgICAgICAgICAgcmV0dXJuICdpcyByZXF1aXJlZCdcbiAgICAgICAgfVxuXG4gICAgICAgIHNlbGYudmFsaWRhdGVFbWFpbE1lc3NhZ2UgPSAodmFsdWUpID0+IHtcbiAgICAgICAgICAgIGNvbnN0IGVtYWlsRm9ybWF0ID0gL14oW0EtWmEtejAtOV9cXC1cXC5dKStcXEAoW0EtWmEtejAtOV9cXC1cXC5dKStcXC4oW0EtWmEtel17Miw0fSkkL1xuXG4gICAgICAgICAgICBpZiAoZW1haWxGb3JtYXQudGVzdCh2YWx1ZSkpIHJldHVyblxuXG4gICAgICAgICAgICByZXR1cm4gJ2lzIG5vdCBhIHZhbGlkIGVtYWlsJ1xuICAgICAgICB9XG5cbiAgICAgICAgc2VsZi52YWxpZGF0ZVZhbHVlTWVzc2FnZSA9ICh2YWx1ZSwgY29tcGFyZVZhbHVlKSA9PiB7XG4gICAgICAgICAgICBpZiAodmFsdWUgPT09IGNvbXBhcmVWYWx1ZSkgcmV0dXJuXG5cbiAgICAgICAgICAgIHJldHVybiBgbXVzdCBiZSBzYW1lIGFzICR7Y29tcGFyZVZhbHVlfWBcbiAgICAgICAgfVxuICAgIH1cblxuICAgIHdpbmRvdy5KU1VjZmlyc3QgPSBmdW5jdGlvbiAoc3RyaW5nKSB7XG4gICAgICAgIHJldHVybiBzdHJpbmcuY2hhckF0KDApLnRvVXBwZXJDYXNlKCkgKyBzdHJpbmcuc2xpY2UoMSlcbiAgICB9XG59KShqUXVlcnkpXG4iXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/js/app.js\n'
                )

                /***/
            },

        /***/ './resources/sass/admin.scss':
            /*!***********************************!*\
  !*** ./resources/sass/admin.scss ***!
  \***********************************/
            /*! no static exports found */
            /***/ function (module, exports) {
                eval(
                    '// removed by extract-text-webpack-plugin//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvc2Fzcy9hZG1pbi5zY3NzP2EzN2EiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUEiLCJmaWxlIjoiLi9yZXNvdXJjZXMvc2Fzcy9hZG1pbi5zY3NzLmpzIiwic291cmNlc0NvbnRlbnQiOlsiLy8gcmVtb3ZlZCBieSBleHRyYWN0LXRleHQtd2VicGFjay1wbHVnaW4iXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/sass/admin.scss\n'
                )

                /***/
            },

        /***/ './resources/sass/app.scss':
            /*!*********************************!*\
  !*** ./resources/sass/app.scss ***!
  \*********************************/
            /*! no static exports found */
            /***/ function (module, exports) {
                eval(
                    '// removed by extract-text-webpack-plugin//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvc2Fzcy9hcHAuc2Nzcz8wZTE1Il0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBIiwiZmlsZSI6Ii4vcmVzb3VyY2VzL3Nhc3MvYXBwLnNjc3MuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvLyByZW1vdmVkIGJ5IGV4dHJhY3QtdGV4dC13ZWJwYWNrLXBsdWdpbiJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./resources/sass/app.scss\n'
                )

                /***/
            },

        /***/ 0:
            /*!*****************************************************************************************!*\
  !*** multi ./resources/js/app.js ./resources/sass/app.scss ./resources/sass/admin.scss ***!
  \*****************************************************************************************/
            /*! no static exports found */
            /***/ function (module, exports, __webpack_require__) {
                __webpack_require__(
                    /*! /home/alamin/www/smartpay/wp-content/plugins/smartpay/resources/js/app.js */ './resources/js/app.js'
                )
                __webpack_require__(
                    /*! /home/alamin/www/smartpay/wp-content/plugins/smartpay/resources/sass/app.scss */ './resources/sass/app.scss'
                )
                module.exports = __webpack_require__(
                    /*! /home/alamin/www/smartpay/wp-content/plugins/smartpay/resources/sass/admin.scss */ './resources/sass/admin.scss'
                )

                /***/
            },

        /******/
    }
)
