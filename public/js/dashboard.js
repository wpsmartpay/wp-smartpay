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

/***/ "./resources/js/admin/dashboard.js"
/*!*****************************************!*\
  !*** ./resources/js/admin/dashboard.js ***!
  \*****************************************/
() {

eval("{;\n(function ($) {\n  jQuery(document).on('submit', '.wpsmartpay-welcome .subscription-form form', function (event) {\n    event.preventDefault();\n    var form = jQuery(this);\n    var email = form.find('input[type=email]').val();\n    jQuery.ajax({\n      url: 'https://localhost/boss/wp-admin/admin-ajax.php',\n      method: 'POST',\n      crossDomain: true,\n      data: {\n        action: 'smartpay_customer_contact_optin',\n        email: email\n      },\n      success: function success(response) {\n        if (response.success) {\n          form.after('<div class=\"alert alert-success\"><p>Thank You, kindly check your email and allow subscription to receive the discount code. Cheers!!!</p></div>');\n          jQuery.ajax({\n            url: ajaxurl,\n            method: 'POST',\n            data: {\n              action: 'smartpay_contact_optin_notice_dismiss',\n              nonce: dashboardObj.nonce,\n              user_id: dashboardObj.user_id,\n              meta_value: 'opted_in'\n            }\n          });\n        }\n      }\n    });\n  });\n})(jQuery);//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyIkIiwialF1ZXJ5IiwiZG9jdW1lbnQiLCJvbiIsImV2ZW50IiwicHJldmVudERlZmF1bHQiLCJmb3JtIiwiZW1haWwiLCJmaW5kIiwidmFsIiwiYWpheCIsInVybCIsIm1ldGhvZCIsImNyb3NzRG9tYWluIiwiZGF0YSIsImFjdGlvbiIsInN1Y2Nlc3MiLCJyZXNwb25zZSIsImFmdGVyIiwiYWpheHVybCIsIm5vbmNlIiwiZGFzaGJvYXJkT2JqIiwidXNlcl9pZCIsIm1ldGFfdmFsdWUiXSwic291cmNlcyI6WyJ3ZWJwYWNrOi8vd3Atc21hcnRwYXkvLi9yZXNvdXJjZXMvanMvYWRtaW4vZGFzaGJvYXJkLmpzP2UwZDkiXSwic291cmNlc0NvbnRlbnQiOlsiOyhmdW5jdGlvbiAoJCkge1xuICAgIGpRdWVyeShkb2N1bWVudCkub24oXG4gICAgICAgICdzdWJtaXQnLFxuICAgICAgICAnLndwc21hcnRwYXktd2VsY29tZSAuc3Vic2NyaXB0aW9uLWZvcm0gZm9ybScsXG4gICAgICAgIGZ1bmN0aW9uIChldmVudCkge1xuICAgICAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKVxuICAgICAgICAgICAgbGV0IGZvcm0gPSBqUXVlcnkodGhpcylcbiAgICAgICAgICAgIGxldCBlbWFpbCA9IGZvcm0uZmluZCgnaW5wdXRbdHlwZT1lbWFpbF0nKS52YWwoKVxuICAgICAgICAgICAgalF1ZXJ5LmFqYXgoe1xuICAgICAgICAgICAgICAgIHVybDogJ2h0dHBzOi8vbG9jYWxob3N0L2Jvc3Mvd3AtYWRtaW4vYWRtaW4tYWpheC5waHAnLFxuICAgICAgICAgICAgICAgIG1ldGhvZDogJ1BPU1QnLFxuICAgICAgICAgICAgICAgIGNyb3NzRG9tYWluOiB0cnVlLFxuICAgICAgICAgICAgICAgIGRhdGE6IHtcbiAgICAgICAgICAgICAgICAgICAgYWN0aW9uOiAnc21hcnRwYXlfY3VzdG9tZXJfY29udGFjdF9vcHRpbicsXG4gICAgICAgICAgICAgICAgICAgIGVtYWlsOiBlbWFpbCxcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIHN1Y2Nlc3MocmVzcG9uc2UpIHtcbiAgICAgICAgICAgICAgICAgICAgaWYgKHJlc3BvbnNlLnN1Y2Nlc3MpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGZvcm0uYWZ0ZXIoXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJzxkaXYgY2xhc3M9XCJhbGVydCBhbGVydC1zdWNjZXNzXCI+PHA+VGhhbmsgWW91LCBraW5kbHkgY2hlY2sgeW91ciBlbWFpbCBhbmQgYWxsb3cgc3Vic2NyaXB0aW9uIHRvIHJlY2VpdmUgdGhlIGRpc2NvdW50IGNvZGUuIENoZWVycyEhITwvcD48L2Rpdj4nXG4gICAgICAgICAgICAgICAgICAgICAgICApXG5cbiAgICAgICAgICAgICAgICAgICAgICAgIGpRdWVyeS5hamF4KHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB1cmw6IGFqYXh1cmwsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbWV0aG9kOiAnUE9TVCcsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgZGF0YToge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBhY3Rpb246ICdzbWFydHBheV9jb250YWN0X29wdGluX25vdGljZV9kaXNtaXNzJyxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbm9uY2U6IGRhc2hib2FyZE9iai5ub25jZSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdXNlcl9pZDogZGFzaGJvYXJkT2JqLnVzZXJfaWQsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG1ldGFfdmFsdWU6ICdvcHRlZF9pbicsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgfSlcbiAgICAgICAgfVxuICAgIClcbn0pKGpRdWVyeSlcbiJdLCJtYXBwaW5ncyI6IkFBQUE7QUFBQyxDQUFDLFVBQVVBLENBQUMsRUFBRTtFQUNYQyxNQUFNLENBQUNDLFFBQVEsQ0FBQyxDQUFDQyxFQUFFLENBQ2YsUUFBUSxFQUNSLDZDQUE2QyxFQUM3QyxVQUFVQyxLQUFLLEVBQUU7SUFDYkEsS0FBSyxDQUFDQyxjQUFjLENBQUMsQ0FBQztJQUN0QixJQUFJQyxJQUFJLEdBQUdMLE1BQU0sQ0FBQyxJQUFJLENBQUM7SUFDdkIsSUFBSU0sS0FBSyxHQUFHRCxJQUFJLENBQUNFLElBQUksQ0FBQyxtQkFBbUIsQ0FBQyxDQUFDQyxHQUFHLENBQUMsQ0FBQztJQUNoRFIsTUFBTSxDQUFDUyxJQUFJLENBQUM7TUFDUkMsR0FBRyxFQUFFLGdEQUFnRDtNQUNyREMsTUFBTSxFQUFFLE1BQU07TUFDZEMsV0FBVyxFQUFFLElBQUk7TUFDakJDLElBQUksRUFBRTtRQUNGQyxNQUFNLEVBQUUsaUNBQWlDO1FBQ3pDUixLQUFLLEVBQUVBO01BQ1gsQ0FBQztNQUNEUyxPQUFPLFdBQVBBLE9BQU9BLENBQUNDLFFBQVEsRUFBRTtRQUNkLElBQUlBLFFBQVEsQ0FBQ0QsT0FBTyxFQUFFO1VBQ2xCVixJQUFJLENBQUNZLEtBQUssQ0FDTixpSkFDSixDQUFDO1VBRURqQixNQUFNLENBQUNTLElBQUksQ0FBQztZQUNSQyxHQUFHLEVBQUVRLE9BQU87WUFDWlAsTUFBTSxFQUFFLE1BQU07WUFDZEUsSUFBSSxFQUFFO2NBQ0ZDLE1BQU0sRUFBRSx1Q0FBdUM7Y0FDL0NLLEtBQUssRUFBRUMsWUFBWSxDQUFDRCxLQUFLO2NBQ3pCRSxPQUFPLEVBQUVELFlBQVksQ0FBQ0MsT0FBTztjQUM3QkMsVUFBVSxFQUFFO1lBQ2hCO1VBQ0osQ0FBQyxDQUFDO1FBQ047TUFDSjtJQUNKLENBQUMsQ0FBQztFQUNOLENBQ0osQ0FBQztBQUNMLENBQUMsRUFBRXRCLE1BQU0sQ0FBQyIsImlnbm9yZUxpc3QiOltdLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvYWRtaW4vZGFzaGJvYXJkLmpzIiwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/js/admin/dashboard.js\n\n}");

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/admin/dashboard.js"]();
/******/ 	
/******/ })()
;