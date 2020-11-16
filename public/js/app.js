!function(t){var e={};function r(a){if(e[a])return e[a].exports;var n=e[a]={i:a,l:!1,exports:{}};return t[a].call(n.exports,n,n.exports,r),n.l=!0,n.exports}r.m=t,r.c=e,r.d=function(t,e,a){r.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:a})},r.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},r.t=function(t,e){if(1&e&&(t=r(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var a=Object.create(null);if(r.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var n in t)r.d(a,n,function(e){return t[e]}.bind(null,n));return a},r.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return r.d(e,"a",e),e},r.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},r.p="/",r(r.s=26)}({26:function(t,e,r){r(27),r(523),t.exports=r(528)},27:function(t,e,r){"use strict";r.r(e);r(28);function a(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(t)))return;var r=[],a=!0,n=!1,o=void 0;try{for(var i,s=t[Symbol.iterator]();!(a=(i=s.next()).done)&&(r.push(i.value),!e||r.length!==e);a=!0);}catch(t){n=!0,o=t}finally{try{a||null==s.return||s.return()}finally{if(n)throw o}}return r}(t,e)||function(t,e){if(!t)return;if("string"==typeof t)return n(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);"Object"===r&&t.constructor&&(r=t.constructor.name);if("Map"===r||"Set"===r)return Array.from(t);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return n(t,e)}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function n(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,a=new Array(e);r<e;r++)a[r]=t[r];return a}jQuery((function(t){window.SmartPayFormValidator=function(t,e){var r=this;this.data=t,this.rules=e,this.validate=function(){return Object.entries(r.rules).reduce((function(t,e){var n=a(e,2),o=n[0],i=n[1],s=[];if(i.required){var u=r.validateRequiredMessage(r.data[o]);u&&s.push(u)}if(i.email){var c=r.validateEmailMessage(r.data[o]);c&&s.push(c)}if(i.length){var m=r.validateLengthMessage(r.data[o],i.length);m&&s.push(m)}if(i.value){var p=r.validateValueMessage(r.data[o],i.value);p&&s.push(p)}return s.length&&(t[o]=s),t}),{})},this.validateLengthMessage=function(t,e){if(null!=t){if(Array.isArray(e)){if(t.length>=e[0]&&t.length<=e[1])return;return"must be between ".concat(e[0]," to ").concat(e[1]," character")}if(!(t.length>=e))return"must be ".concat(e," or more characters")}},this.validateRequiredMessage=function(t){if(!t)return"is required"},this.validateEmailMessage=function(t){if(!/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/.test(t))return"is not a valid email"},this.validateValueMessage=function(t,e){if(t!==e)return"must be same as ".concat(e)}},window.JSUcfirst=function(t){return t.charAt(0).toUpperCase()+t.slice(1)}}))},28:function(t,e){function r(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(t)))return;var r=[],a=!0,n=!1,o=void 0;try{for(var i,s=t[Symbol.iterator]();!(a=(i=s.next()).done)&&(r.push(i.value),!e||r.length!==e);a=!0);}catch(t){n=!0,o=t}finally{try{a||null==s.return||s.return()}finally{if(n)throw o}}return r}(t,e)||function(t,e){if(!t)return;if("string"==typeof t)return a(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);"Object"===r&&t.constructor&&(r=t.constructor.name);if("Map"===r||"Set"===r)return Array.from(t);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return a(t,e)}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function a(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,a=new Array(e);r<e;r++)a[r]=t[r];return a}jQuery((function(t){function e(e){e.find(".step-1").show(),e.find(".step-2").hide(),t(".back-to-first-step").hide()}function a(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",r={smartpay_action:"smartpay_process_payment",smartpay_process_payment:t.find('input[name="smartpay_process_payment"]').val()||null,smartpay_gateway:t.find('input[name="smartpay_gateway"]:checked').val()||null,smartpay_first_name:t.find('input[name="smartpay_first_name"]').val()||null,smartpay_last_name:t.find('input[name="smartpay_last_name"]').val()||null,smartpay_email:t.find('input[name="smartpay_email"]').val()||null,smartpay_payment_type:t.find('input[name="smartpay_payment_type"]').val()||null};return"product_purchase"===r.smartpay_payment_type?(r.smartpay_product_id=t.find('input[name="smartpay_product_id"]').val()||null,r.smartpay_product_price=t.find('input[name="smartpay_product_price"]').val()||null):(r.smartpay_form_id=t.find('input[name="smartpay_form_id"]').val()||null,r.smartpay_form_amount=t.find('input[name="smartpay_form_amount"]').val()||null),e?r.index||null:r}t(document.body).on("click",".smartpay-product-shortcode .product-variations .variation",(function(e){t(e.currentTarget).parent().find(".variation").removeClass("selected"),t(e.currentTarget).addClass("selected"),t(e.currentTarget).parents(".smartpay-product-shortcode").find('input[name="smartpay_product_price"]').val(t(e.currentTarget).find(".sale-price").html())})),t(document.body).on("click",".smartpay-product-shortcode button.open-product-modal",(function(e){e.preventDefault();var r=t(e.currentTarget).parents(".smartpay-product-shortcode").find(".product-modal");setTimeout((function(){r.modal("show"),t(".modal-backdrop").last().appendTo(t(e.currentTarget).closest(".smartpay"))}),500)})),t(document.body).on("click",".smartpay-form-shortcode .form-amounts .form--fixed-amount",(function(e){t(e.currentTarget).parents(".form-amounts").find(".amount").removeClass("selected"),t(e.currentTarget).addClass("selected");var r=t(e.currentTarget).find('input[name="_form_amount"]').val();t(e.currentTarget).parents(".form-amounts").find(".form--custom-amount").val(r)})),t(document.body).on("focus",".smartpay-form-shortcode .form-amounts .form--custom-amount",(function(e){t(e.currentTarget).parents(".form-amounts").find(".amount").removeClass("selected"),t(e.currentTarget).addClass("selected")})),t(document.body).on("click",".smartpay-form-shortcode button.open-form-modal",(function(e){e.preventDefault();var r=t(e.currentTarget).parents(".smartpay-form-shortcode").find(".form-modal");setTimeout((function(){r.modal("show"),t(".modal-backdrop").last().appendTo(t(e.currentTarget).closest(".smartpay"))}),500)})),t(document.body).on("click",".smartpay-payment button.open-payment-form",(function(r){r.preventDefault(),$parentWrapper=t(r.currentTarget).parents(".smartpay-payment");var n=$parentWrapper.find(".payment-modal"),o=a($parentWrapper),i=0;"form_payment"===o.smartpay_payment_type?i=t("#smartpay_currency_symbol").data("value")+o.smartpay_form_amount:i=o.smartpay_product_price;n.find(".amount").html(i),e(n);var s=t(r.currentTarget).text();t(r.currentTarget).text("Processing...").attr("disabled","disabled"),setTimeout((function(){n.modal("show"),t(".modal-backdrop").last().appendTo(t(r.currentTarget).closest(".smartpay-payment")),t(r.currentTarget).text(s).removeAttr("disabled")}),500)})),t(document.body).on("click",".smartpay-payment button.back-to-first-step",(function(r){r.preventDefault(),e(t(r.currentTarget).parents(".smartpay-payment").find(".payment-modal"))})),t(document.body).on("click",".smartpay-payment button.smartpay-pay-now",(function(e){e.preventDefault(),$parentWrapper=t(e.currentTarget).parents(".smartpay-payment");var n=t(e.currentTarget).text(),o=t(e.currentTarget).parents(".step-1"),i=t(e.currentTarget).parents(".modal-content").children(".step-2");t(e.currentTarget).text("Processing...").attr("disabled","disabled"),$parentWrapper.find(".modal-loading").css("display","flex");var s=a($parentWrapper),u=function(t){var e=new SmartPayFormValidator(t,{smartpay_action:{required:!0,value:"smartpay_process_payment"},smartpay_process_payment:{required:!0},smartpay_gateway:{required:!0},smartpay_first_name:{required:!0},smartpay_last_name:{required:!0},smartpay_email:{required:!0,email:!0},smartpay_payment_type:{required:!0}}).validate();return{valid:Object.values(e).every((function(t){return 0===t.length})),errors:e}}(s);if($parentWrapper.find("input").removeClass("is-invalid"),o.find(".payment-modal--errors").hide(),u.valid){var c={action:"smartpay_process_payment",data:s};jQuery.post(smartpay.ajax_url,c,(function(e){i.css("display","flex"),t(".back-to-first-step").show(),o.hide(),setTimeout((function(){e?i.find(".dynamic-content").html(e):(i.find(".dynamic-content").html('<p class="text-danger">Something wrong!</p>'),console.error("Something wrong!")),$parentWrapper.find(".modal-loading").css("display","none")}),300)}))}else!function(t,e){var a=t.parents(".smartpay-payment"),n=[];if(Object.entries(e.errors).forEach((function(t){var e=r(t,2),o=e[0],i=e[1];a.find('input[name="'+o+'"]').addClass("is-invalid");var s=JSUcfirst(o.split("_").slice(1).join(" "));n.push('\n                <div class="alert alert-danger">\n                    <p class="m-0 form-error-text">'.concat(s," ").concat(i[0],"</p>\n                </div>"))})),!n.length)return;t.show(),t.html(n)}(o.find(".payment-modal--errors"),u),setTimeout((function(){$parentWrapper.find(".modal-loading").css("display","none")}),300);t(e.currentTarget).text(n).removeAttr("disabled")})),t(document.body).on("click",".smartpay-payment button.modal-close",(function(e){t(e.currentTarget).parents(".smartpay-payment").find(".payment-modal").modal("hide")})),t(document.body).on("show.bs.modal",".payment-modal",(function(t){document.body.style.overflow="hidden"})),t(document.body).on("hidden.bs.modal",".payment-modal",(function(t){document.body.style.overflow="auto"}))}))},523:function(t,e){},528:function(t,e){}});