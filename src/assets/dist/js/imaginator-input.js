"use strict";var ImaginatorInput={init:function(){var t=this,n=document.querySelectorAll("[data-imaginator]");return n.length<1?!1:void n.forEach(function(n){t.bindClickEvents.setImaginatorInput(n)})},bindClickEvents:{setImaginatorInput:function(t){var n=this;t.addEventListener("click",function(t){n.clickedInput=t.target;var i=n.clickedInput.value,a=n.clickedInput.getAttribute("data-imaginator-template"),e=window.ImaginatorCreateUrl.replace("{template}",a);i.length>0&&(e+="?imaginator="+i),swal({html:'<iframe src="'+e+'" class="imaginator-lightbox"></iframe>',width:"90vw",showConfirmButton:!1,showCloseButton:!0,focusCancel:!1,padding:"0px",animation:!1,onClose:function(){"undefined"!=typeof window.lightboxResult&&setTimeout(function(){n.clickedInput.value=window.lightboxResult})}})["catch"](swal.noop)})}}};ImaginatorInput.init();