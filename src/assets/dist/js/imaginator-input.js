"use strict";function _classCallCheck(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}var _createClass=function(){function t(t,e){for(var n=0;n<e.length;n++){var i=e[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(t,i.key,i)}}return function(e,n,i){return n&&t(e.prototype,n),i&&t(e,i),e}}(),ImaginatorInput=function(){function t(){return _classCallCheck(this,t),this.init(),this}return _createClass(t,[{key:"init",value:function(){var t=document.querySelectorAll("[data-imaginator]");if(t.length<1)return!1;for(var e=0;e<t.length;e++)this.setImaginatorInput(t[e])}},{key:"clickHandler",value:function(t){var e=this;this.clickedInput=t.target;var n=this.clickedInput.value,i=this.clickedInput.getAttribute("data-imaginator-template"),a=window.ImaginatorCreateUrl.replace("{template}",i);n.length>0&&(a+="?imaginator="+n),swal({html:'<iframe src="'+a+'" class="imaginator-lightbox"></iframe>',width:"90vw",showConfirmButton:!1,showCloseButton:!0,focusCancel:!1,padding:"0px",animation:!1,onClose:function(){"undefined"!=typeof window.lightboxResult&&setTimeout(function(){e.clickedInput.value=window.lightboxResult})}})["catch"](swal.noop)}},{key:"setImaginatorInput",value:function(t){t.removeEventListener("click",this.clickHandler),t.addEventListener("click",this.clickHandler)}}]),t}(),imaginatorInput=new ImaginatorInput;