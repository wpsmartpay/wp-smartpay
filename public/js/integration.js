/*
 * ATTENTION: An "eval-source-map" devtool has been used.
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file with attached SourceMaps in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/admin/integration.js"
/*!*******************************************!*\
  !*** ./resources/js/admin/integration.js ***!
  \*******************************************/
() {

eval("{jQuery(function ($) {\n  $(document.body).on('change', '.smartpay-integrations .custom-control-input', function (e) {\n    e.preventDefault();\n    var action = 'deactivate';\n    var namespace = $(e.target).data('namespace');\n    var nonce = $('#smartpay_integrations_toggle_activation').val();\n    if (e.target.checked) {\n      action = 'activate';\n    }\n    var data = {\n      action: 'toggle_integration_activation',\n      payload: {\n        action: action,\n        namespace: namespace,\n        nonce: nonce\n      }\n    };\n    jQuery.post(smartpay.ajaxUrl, data, function (response) {\n      if (response) {\n        $(e.target).parents('.actions').find('.integration-status').html(response);\n        window.location.reload();\n      } else {\n        console.error('Something wrong!');\n      }\n    });\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyJqUXVlcnkiLCIkIiwiZG9jdW1lbnQiLCJib2R5Iiwib24iLCJlIiwicHJldmVudERlZmF1bHQiLCJhY3Rpb24iLCJuYW1lc3BhY2UiLCJ0YXJnZXQiLCJkYXRhIiwibm9uY2UiLCJ2YWwiLCJjaGVja2VkIiwicGF5bG9hZCIsInBvc3QiLCJzbWFydHBheSIsImFqYXhVcmwiLCJyZXNwb25zZSIsInBhcmVudHMiLCJmaW5kIiwiaHRtbCIsIndpbmRvdyIsImxvY2F0aW9uIiwicmVsb2FkIiwiY29uc29sZSIsImVycm9yIl0sInNvdXJjZXMiOlsid2VicGFjazovL3dwLXNtYXJ0cGF5Ly4vcmVzb3VyY2VzL2pzL2FkbWluL2ludGVncmF0aW9uLmpzP2RkMTkiXSwic291cmNlc0NvbnRlbnQiOlsialF1ZXJ5KGZ1bmN0aW9uICgkKSB7XG4gICAgJChkb2N1bWVudC5ib2R5KS5vbihcbiAgICAgICAgJ2NoYW5nZScsXG4gICAgICAgICcuc21hcnRwYXktaW50ZWdyYXRpb25zIC5jdXN0b20tY29udHJvbC1pbnB1dCcsXG4gICAgICAgIChlKSA9PiB7XG4gICAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KClcblxuICAgICAgICAgICAgbGV0IGFjdGlvbiA9ICdkZWFjdGl2YXRlJ1xuICAgICAgICAgICAgbGV0IG5hbWVzcGFjZSA9ICQoZS50YXJnZXQpLmRhdGEoJ25hbWVzcGFjZScpXG4gICAgICAgICAgICBsZXQgbm9uY2UgPSAkKCcjc21hcnRwYXlfaW50ZWdyYXRpb25zX3RvZ2dsZV9hY3RpdmF0aW9uJykudmFsKClcblxuICAgICAgICAgICAgaWYgKGUudGFyZ2V0LmNoZWNrZWQpIHtcbiAgICAgICAgICAgICAgICBhY3Rpb24gPSAnYWN0aXZhdGUnXG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGxldCBkYXRhID0ge1xuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3RvZ2dsZV9pbnRlZ3JhdGlvbl9hY3RpdmF0aW9uJyxcbiAgICAgICAgICAgICAgICBwYXlsb2FkOiB7IGFjdGlvbiwgbmFtZXNwYWNlLCBub25jZSB9LFxuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBqUXVlcnkucG9zdChzbWFydHBheS5hamF4VXJsLCBkYXRhLCAocmVzcG9uc2UpID0+IHtcbiAgICAgICAgICAgICAgICBpZiAocmVzcG9uc2UpIHtcbiAgICAgICAgICAgICAgICAgICAgJChlLnRhcmdldClcbiAgICAgICAgICAgICAgICAgICAgICAgIC5wYXJlbnRzKCcuYWN0aW9ucycpXG4gICAgICAgICAgICAgICAgICAgICAgICAuZmluZCgnLmludGVncmF0aW9uLXN0YXR1cycpXG4gICAgICAgICAgICAgICAgICAgICAgICAuaHRtbChyZXNwb25zZSlcbiAgICAgICAgICAgICAgICAgICAgd2luZG93LmxvY2F0aW9uLnJlbG9hZCgpO1xuICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIGNvbnNvbGUuZXJyb3IoJ1NvbWV0aGluZyB3cm9uZyEnKVxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgKVxufSlcbiJdLCJtYXBwaW5ncyI6IkFBQUFBLE1BQU0sQ0FBQyxVQUFVQyxDQUFDLEVBQUU7RUFDaEJBLENBQUMsQ0FBQ0MsUUFBUSxDQUFDQyxJQUFJLENBQUMsQ0FBQ0MsRUFBRSxDQUNmLFFBQVEsRUFDUiw4Q0FBOEMsRUFDOUMsVUFBQ0MsQ0FBQyxFQUFLO0lBQ0hBLENBQUMsQ0FBQ0MsY0FBYyxDQUFDLENBQUM7SUFFbEIsSUFBSUMsTUFBTSxHQUFHLFlBQVk7SUFDekIsSUFBSUMsU0FBUyxHQUFHUCxDQUFDLENBQUNJLENBQUMsQ0FBQ0ksTUFBTSxDQUFDLENBQUNDLElBQUksQ0FBQyxXQUFXLENBQUM7SUFDN0MsSUFBSUMsS0FBSyxHQUFHVixDQUFDLENBQUMsMENBQTBDLENBQUMsQ0FBQ1csR0FBRyxDQUFDLENBQUM7SUFFL0QsSUFBSVAsQ0FBQyxDQUFDSSxNQUFNLENBQUNJLE9BQU8sRUFBRTtNQUNsQk4sTUFBTSxHQUFHLFVBQVU7SUFDdkI7SUFFQSxJQUFJRyxJQUFJLEdBQUc7TUFDUEgsTUFBTSxFQUFFLCtCQUErQjtNQUN2Q08sT0FBTyxFQUFFO1FBQUVQLE1BQU0sRUFBTkEsTUFBTTtRQUFFQyxTQUFTLEVBQVRBLFNBQVM7UUFBRUcsS0FBSyxFQUFMQTtNQUFNO0lBQ3hDLENBQUM7SUFFRFgsTUFBTSxDQUFDZSxJQUFJLENBQUNDLFFBQVEsQ0FBQ0MsT0FBTyxFQUFFUCxJQUFJLEVBQUUsVUFBQ1EsUUFBUSxFQUFLO01BQzlDLElBQUlBLFFBQVEsRUFBRTtRQUNWakIsQ0FBQyxDQUFDSSxDQUFDLENBQUNJLE1BQU0sQ0FBQyxDQUNOVSxPQUFPLENBQUMsVUFBVSxDQUFDLENBQ25CQyxJQUFJLENBQUMscUJBQXFCLENBQUMsQ0FDM0JDLElBQUksQ0FBQ0gsUUFBUSxDQUFDO1FBQ25CSSxNQUFNLENBQUNDLFFBQVEsQ0FBQ0MsTUFBTSxDQUFDLENBQUM7TUFDNUIsQ0FBQyxNQUFNO1FBQ0hDLE9BQU8sQ0FBQ0MsS0FBSyxDQUFDLGtCQUFrQixDQUFDO01BQ3JDO0lBQ0osQ0FBQyxDQUFDO0VBQ04sQ0FDSixDQUFDO0FBQ0wsQ0FBQyxDQUFDIiwiaWdub3JlTGlzdCI6W10sImZpbGUiOiIuL3Jlc291cmNlcy9qcy9hZG1pbi9pbnRlZ3JhdGlvbi5qcyIsInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./resources/js/admin/integration.js\n\n}");

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/admin/integration.js"]();
/******/ 	
/******/ })()
;