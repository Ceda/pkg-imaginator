"use strict";var ImaginatorView={init:function(){return this.el=document.querySelector(".imaginator-view"),this.el?(this.vue=new Vue(this.vueOptions()),this.isInited=!0,!0):!1},vueOptions:function(){return{el:".imaginator-view",methods:{exitLightbox:function(i){"undefined"!=typeof window.parent.swal&&(window.parent.lightboxResult=i,window.parent.swal.clickCancel())}}}}};