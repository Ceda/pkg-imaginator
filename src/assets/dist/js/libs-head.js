!function(t){var e=navigator.userAgent;t.HTMLPictureElement&&/ecko/.test(e)&&e.match(/rv\:(\d+)/)&&RegExp.$1<45&&addEventListener("resize",function(){var e,n=document.createElement("source"),r=function(t){var e,r,i=t.parentNode;"PICTURE"===i.nodeName.toUpperCase()?(e=n.cloneNode(),i.insertBefore(e,i.firstElementChild),setTimeout(function(){i.removeChild(e)})):(!t._pfLastSize||t.offsetWidth>t._pfLastSize)&&(t._pfLastSize=t.offsetWidth,r=t.sizes,t.sizes+=",100vw",setTimeout(function(){t.sizes=r}))},i=function(){var t,e=document.querySelectorAll("picture > img, img[srcset][sizes]");for(t=0;t<e.length;t++)r(e[t])},s=function(){clearTimeout(e),e=setTimeout(i,99)},o=t.matchMedia&&matchMedia("(orientation: landscape)"),u=function(){s(),o&&o.addListener&&o.addListener(s)};return n.srcset="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==",/^[c|i]|d$/.test(document.readyState||"")?u():document.addEventListener("DOMContentLoaded",u),s}())}(window),function(t,e,n){"use strict";function r(t){return" "===t||"	"===t||"\n"===t||"\f"===t||"\r"===t}function i(e,n){var r=new t.Image;return r.onerror=function(){T[e]=!1,et()},r.onload=function(){T[e]=1===r.width,et()},r.src=n,"pending"}function s(){B=!1,W=t.devicePixelRatio,I={},$={},g.DPR=W||1,F.width=Math.max(t.innerWidth||0,x.clientWidth),F.height=Math.max(t.innerHeight||0,x.clientHeight),F.vw=F.width/100,F.vh=F.height/100,A=[F.height,F.width,W].join("-"),F.em=g.getEmValue(),F.rem=F.em}function o(t,e,n,r){var i,s,o,u;return"saveData"===C.algorithm?t>2.7?u=n+1:(s=e-n,i=Math.pow(t-.6,1.5),o=s*i,r&&(o+=.1*i),u=t+o):u=n>1?Math.sqrt(t*e):t,u>n}function u(t){var e,n=g.getSet(t),r=!1;"pending"!==n&&(r=A,n&&(e=g.setRes(n),g.applySetCandidate(e,t))),t[g.ns].evaled=r}function c(t,e){return t.res-e.res}function a(t,e,n){var r;return!n&&e&&(n=t[g.ns].sets,n=n&&n[n.length-1]),r=f(e,n),r&&(e=g.makeUrl(e),t[g.ns].curSrc=e,t[g.ns].curCan=r,r.res||tt(r,r.set.sizes)),r}function f(t,e){var n,r,i;if(t&&e)for(i=g.parseSet(e),t=g.makeUrl(t),n=0;n<i.length;n++)if(t===g.makeUrl(i[n].url)){r=i[n];break}return r}function l(t,e){var n,r,i,s,o=t.getElementsByTagName("source");for(n=0,r=o.length;r>n;n++)i=o[n],i[g.ns]=!0,s=i.getAttribute("srcset"),s&&e.push({srcset:s,media:i.getAttribute("media"),type:i.getAttribute("type"),sizes:i.getAttribute("sizes")})}function h(t,e){function n(e){var n,r=e.exec(t.substring(h));return r?(n=r[0],h+=n.length,n):void 0}function i(){var t,n,r,i,s,c,a,f,l,h=!1,d={};for(i=0;i<u.length;i++)s=u[i],c=s[s.length-1],a=s.substring(0,s.length-1),f=parseInt(a,10),l=parseFloat(a),K.test(a)&&"w"===c?((t||n)&&(h=!0),0===f?h=!0:t=f):V.test(a)&&"x"===c?((t||n||r)&&(h=!0),0>l?h=!0:n=l):K.test(a)&&"h"===c?((r||n)&&(h=!0),0===f?h=!0:r=f):h=!0;h||(d.url=o,t&&(d.w=t),n&&(d.d=n),r&&(d.h=r),r||n||t||(d.d=1),1===d.d&&(e.has1x=!0),d.set=e,p.push(d))}function s(){for(n(G),c="",a="in descriptor";;){if(f=t.charAt(h),"in descriptor"===a)if(r(f))c&&(u.push(c),c="",a="after descriptor");else{if(","===f)return h+=1,c&&u.push(c),void i();if("("===f)c+=f,a="in parens";else{if(""===f)return c&&u.push(c),void i();c+=f}}else if("in parens"===a)if(")"===f)c+=f,a="in descriptor";else{if(""===f)return u.push(c),void i();c+=f}else if("after descriptor"===a)if(r(f));else{if(""===f)return void i();a="in descriptor",h-=1}h+=1}}for(var o,u,c,a,f,l=t.length,h=0,p=[];;){if(n(H),h>=l)return p;o=n(N),u=[],","===o.slice(-1)?(o=o.replace(Y,""),i()):s()}}function p(t){function e(t){function e(){s&&(o.push(s),s="")}function n(){o[0]&&(u.push(o),o=[])}for(var i,s="",o=[],u=[],c=0,a=0,f=!1;;){if(i=t.charAt(a),""===i)return e(),n(),u;if(f){if("*"===i&&"/"===t[a+1]){f=!1,a+=2,e();continue}a+=1}else{if(r(i)){if(t.charAt(a-1)&&r(t.charAt(a-1))||!s){a+=1;continue}if(0===c){e(),a+=1;continue}i=" "}else if("("===i)c+=1;else if(")"===i)c-=1;else{if(","===i){e(),n(),a+=1;continue}if("/"===i&&"*"===t.charAt(a+1)){f=!0,a+=2;continue}}s+=i,a+=1}}}function n(t){return f.test(t)&&parseFloat(t)>=0?!0:l.test(t)?!0:"0"===t||"-0"===t||"+0"===t?!0:!1}var i,s,o,u,c,a,f=/^(?:[+-]?[0-9]+|[0-9]*\.[0-9]+)(?:[eE][+-]?[0-9]+)?(?:ch|cm|em|ex|in|mm|pc|pt|px|rem|vh|vmin|vmax|vw)$/i,l=/^calc\((?:[0-9a-z \.\+\-\*\/\(\)]+)\)$/i;for(s=e(t),o=s.length,i=0;o>i;i++)if(u=s[i],c=u[u.length-1],n(c)){if(a=c,u.pop(),0===u.length)return a;if(u=u.join(" "),g.matchesMedia(u))return a}return"100vw"}e.createElement("picture");var d,v,m,A,g={},y=!1,_=function(){},w=e.createElement("img"),b=w.getAttribute,S=w.setAttribute,E=w.removeAttribute,x=e.documentElement,T={},C={algorithm:""},M="data-pfsrc",z=M+"set",P=navigator.userAgent,j=/rident/.test(P)||/ecko/.test(P)&&P.match(/rv\:(\d+)/)&&RegExp.$1>35,R="currentSrc",L=/\s+\+?\d+(e\d+)?w/,k=/(\([^)]+\))?\s*(.+)/,D=t.picturefillCFG,O="position:absolute;left:0;visibility:hidden;display:block;padding:0;border:none;font-size:1em;width:1em;overflow:hidden;clip:rect(0px, 0px, 0px, 0px)",U="font-size:100%!important;",B=!0,I={},$={},W=t.devicePixelRatio,F={px:1,"in":96},Q=e.createElement("a"),q=!1,G=/^[ \t\n\r\u000c]+/,H=/^[, \t\n\r\u000c]+/,N=/^[^ \t\n\r\u000c]+/,Y=/[,]+$/,K=/^\d+$/,V=/^-?(?:[0-9]+|[0-9]*\.[0-9]+)(?:[eE][+-]?[0-9]+)?$/,J=function(t,e,n,r){t.addEventListener?t.addEventListener(e,n,r||!1):t.attachEvent&&t.attachEvent("on"+e,n)},X=function(t){var e={};return function(n){return n in e||(e[n]=t(n)),e[n]}},Z=function(){var t=/^([\d\.]+)(em|vw|px)$/,e=function(){for(var t=arguments,e=0,n=t[0];++e in t;)n=n.replace(t[e],t[++e]);return n},n=X(function(t){return"return "+e((t||"").toLowerCase(),/\band\b/g,"&&",/,/g,"||",/min-([a-z-\s]+):/g,"e.$1>=",/max-([a-z-\s]+):/g,"e.$1<=",/calc([^)]+)/g,"($1)",/(\d+[\.]*[\d]*)([a-z]+)/g,"($1 * e.$2)",/^(?!(e.[a-z]|[0-9\.&=|><\+\-\*\(\)\/])).*/gi,"")+";"});return function(e,r){var i;if(!(e in I))if(I[e]=!1,r&&(i=e.match(t)))I[e]=i[1]*F[i[2]];else try{I[e]=new Function("e",n(e))(F)}catch(s){}return I[e]}}(),tt=function(t,e){return t.w?(t.cWidth=g.calcListLength(e||"100vw"),t.res=t.w/t.cWidth):t.res=t.d,t},et=function(t){if(y){var n,r,i,s=t||{};if(s.elements&&1===s.elements.nodeType&&("IMG"===s.elements.nodeName.toUpperCase()?s.elements=[s.elements]:(s.context=s.elements,s.elements=null)),n=s.elements||g.qsa(s.context||e,s.reevaluate||s.reselect?g.sel:g.selShort),i=n.length){for(g.setupRun(s),q=!0,r=0;i>r;r++)g.fillImg(n[r],s);g.teardownRun(s)}}};d=t.console&&console.warn?function(t){console.warn(t)}:_,R in w||(R="src"),T["image/jpeg"]=!0,T["image/gif"]=!0,T["image/png"]=!0,T["image/svg+xml"]=e.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#Image","1.1"),g.ns=("pf"+(new Date).getTime()).substr(0,9),g.supSrcset="srcset"in w,g.supSizes="sizes"in w,g.supPicture=!!t.HTMLPictureElement,g.supSrcset&&g.supPicture&&!g.supSizes&&!function(t){w.srcset="data:,a",t.src="data:,a",g.supSrcset=w.complete===t.complete,g.supPicture=g.supSrcset&&g.supPicture}(e.createElement("img")),g.supSrcset&&!g.supSizes?!function(){var t="data:image/gif;base64,R0lGODlhAgABAPAAAP///wAAACH5BAAAAAAALAAAAAACAAEAAAICBAoAOw==",n="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==",r=e.createElement("img"),i=function(){var t=r.width;2===t&&(g.supSizes=!0),m=g.supSrcset&&!g.supSizes,y=!0,setTimeout(et)};r.onload=i,r.onerror=i,r.setAttribute("sizes","9px"),r.srcset=n+" 1w,"+t+" 9w",r.src=n}():y=!0,g.selShort="picture>img,img[srcset]",g.sel=g.selShort,g.cfg=C,g.DPR=W||1,g.u=F,g.types=T,g.setSize=_,g.makeUrl=X(function(t){return Q.href=t,Q.href}),g.qsa=function(t,e){return"querySelector"in t?t.querySelectorAll(e):[]},g.matchesMedia=function(){return t.matchMedia&&(matchMedia("(min-width: 0.1em)")||{}).matches?g.matchesMedia=function(t){return!t||matchMedia(t).matches}:g.matchesMedia=g.mMQ,g.matchesMedia.apply(this,arguments)},g.mMQ=function(t){return t?Z(t):!0},g.calcLength=function(t){var e=Z(t,!0)||!1;return 0>e&&(e=!1),e},g.supportsType=function(t){return t?T[t]:!0},g.parseSize=X(function(t){var e=(t||"").match(k);return{media:e&&e[1],length:e&&e[2]}}),g.parseSet=function(t){return t.cands||(t.cands=h(t.srcset,t)),t.cands},g.getEmValue=function(){var t;if(!v&&(t=e.body)){var n=e.createElement("div"),r=x.style.cssText,i=t.style.cssText;n.style.cssText=O,x.style.cssText=U,t.style.cssText=U,t.appendChild(n),v=n.offsetWidth,t.removeChild(n),v=parseFloat(v,10),x.style.cssText=r,t.style.cssText=i}return v||16},g.calcListLength=function(t){if(!(t in $)||C.uT){var e=g.calcLength(p(t));$[t]=e?e:F.width}return $[t]},g.setRes=function(t){var e;if(t){e=g.parseSet(t);for(var n=0,r=e.length;r>n;n++)tt(e[n],t.sizes)}return e},g.setRes.res=tt,g.applySetCandidate=function(t,e){if(t.length){var n,r,i,s,u,f,l,h,p,d=e[g.ns],v=g.DPR;if(f=d.curSrc||e[R],l=d.curCan||a(e,f,t[0].set),l&&l.set===t[0].set&&(p=j&&!e.complete&&l.res-.1>v,p||(l.cached=!0,l.res>=v&&(u=l))),!u)for(t.sort(c),s=t.length,u=t[s-1],r=0;s>r;r++)if(n=t[r],n.res>=v){i=r-1,u=t[i]&&(p||f!==g.makeUrl(n.url))&&o(t[i].res,n.res,v,t[i].cached)?t[i]:n;break}u&&(h=g.makeUrl(u.url),d.curSrc=h,d.curCan=u,h!==f&&g.setSrc(e,u),g.setSize(e))}},g.setSrc=function(t,e){var n;t.src=e.url,"image/svg+xml"===e.set.type&&(n=t.style.width,t.style.width=t.offsetWidth+1+"px",t.offsetWidth+1&&(t.style.width=n))},g.getSet=function(t){var e,n,r,i=!1,s=t[g.ns].sets;for(e=0;e<s.length&&!i;e++)if(n=s[e],n.srcset&&g.matchesMedia(n.media)&&(r=g.supportsType(n.type))){"pending"===r&&(n=r),i=n;break}return i},g.parseSets=function(t,e,r){var i,s,o,u,c=e&&"PICTURE"===e.nodeName.toUpperCase(),a=t[g.ns];(a.src===n||r.src)&&(a.src=b.call(t,"src"),a.src?S.call(t,M,a.src):E.call(t,M)),(a.srcset===n||r.srcset||!g.supSrcset||t.srcset)&&(i=b.call(t,"srcset"),a.srcset=i,u=!0),a.sets=[],c&&(a.pic=!0,l(e,a.sets)),a.srcset?(s={srcset:a.srcset,sizes:b.call(t,"sizes")},a.sets.push(s),o=(m||a.src)&&L.test(a.srcset||""),o||!a.src||f(a.src,s)||s.has1x||(s.srcset+=", "+a.src,s.cands.push({url:a.src,d:1,set:s}))):a.src&&a.sets.push({srcset:a.src,sizes:null}),a.curCan=null,a.curSrc=n,a.supported=!(c||s&&!g.supSrcset||o&&!g.supSizes),u&&g.supSrcset&&!a.supported&&(i?(S.call(t,z,i),t.srcset=""):E.call(t,z)),a.supported&&!a.srcset&&(!a.src&&t.src||t.src!==g.makeUrl(a.src))&&(null===a.src?t.removeAttribute("src"):t.src=a.src),a.parsed=!0},g.fillImg=function(t,e){var n,r=e.reselect||e.reevaluate;t[g.ns]||(t[g.ns]={}),n=t[g.ns],(r||n.evaled!==A)&&((!n.parsed||e.reevaluate)&&g.parseSets(t,t.parentNode,e),n.supported?n.evaled=A:u(t))},g.setupRun=function(){(!q||B||W!==t.devicePixelRatio)&&s()},g.supPicture?(et=_,g.fillImg=_):!function(){var n,r=t.attachEvent?/d$|^c/:/d$|^c|^i/,i=function(){var t=e.readyState||"";s=setTimeout(i,"loading"===t?200:999),e.body&&(g.fillImgs(),n=n||r.test(t),n&&clearTimeout(s))},s=setTimeout(i,e.body?9:99),o=function(t,e){var n,r,i=function(){var s=new Date-r;e>s?n=setTimeout(i,e-s):(n=null,t())};return function(){r=new Date,n||(n=setTimeout(i,e))}},u=x.clientHeight,c=function(){B=Math.max(t.innerWidth||0,x.clientWidth)!==F.width||x.clientHeight!==u,u=x.clientHeight,B&&g.fillImgs()};J(t,"resize",o(c,99)),J(e,"readystatechange",i)}(),g.picturefill=et,g.fillImgs=et,g.teardownRun=_,et._=g,t.picturefillCFG={pf:g,push:function(t){var e=t.shift();"function"==typeof g[e]?g[e].apply(g,t):(C[e]=t[0],q&&g.fillImgs({reselect:!0}))}};for(;D&&D.length;)t.picturefillCFG.push(D.shift());t.picturefill=et,"object"==typeof module&&"object"==typeof module.exports?module.exports=et:"function"==typeof define&&define.amd&&define("picturefill",function(){return et}),g.supPicture||(T["image/webp"]=i("image/webp","data:image/webp;base64,UklGRkoAAABXRUJQVlA4WAoAAAAQAAAAAAAAAAAAQUxQSAwAAAABBxAR/Q9ERP8DAABWUDggGAAAADABAJ0BKgEAAQADADQlpAADcAD++/1QAA=="))}(window,document),!function(t,e){"object"==typeof exports&&"undefined"!=typeof module?module.exports=e():"function"==typeof define&&define.amd?define(e):t.ES6Promise=e()}(this,function(){"use strict";function t(t){var e=typeof t;return null!==t&&("object"===e||"function"===e)}function e(t){return"function"==typeof t}function n(t){Q=t}function r(t){q=t}function i(){return function(){return process.nextTick(a)}}function s(){return"undefined"!=typeof F?function(){F(a)}:c()}function o(){var t=0,e=new N(a),n=document.createTextNode("");return e.observe(n,{characterData:!0}),function(){n.data=t=++t%2}}function u(){var t=new MessageChannel;return t.port1.onmessage=a,function(){return t.port2.postMessage(0)}}function c(){var t=setTimeout;return function(){return t(a,1)}}function a(){for(var t=0;W>t;t+=2){var e=V[t],n=V[t+1];e(n),V[t]=void 0,V[t+1]=void 0}W=0}function f(){try{var t=Function("return this")().require("vertx");return F=t.runOnLoop||t.runOnContext,s()}catch(e){return c()}}function l(t,e){var n=this,r=new this.constructor(p);void 0===r[X]&&j(r);var i=n._state;if(i){var s=arguments[i-1];q(function(){return M(i,r,s,n._result)})}else x(n,r,t,e);return r}function h(t){var e=this;if(t&&"object"==typeof t&&t.constructor===e)return t;var n=new e(p);return w(n,t),n}function p(){}function d(){return new TypeError("You cannot resolve a promise with itself")}function v(){return new TypeError("A promises callback cannot return that same promise.")}function m(t){try{return t.then}catch(e){return nt.error=e,nt}}function A(t,e,n,r){try{t.call(e,n,r)}catch(i){return i}}function g(t,e,n){q(function(t){var r=!1,i=A(n,e,function(n){r||(r=!0,e!==n?w(t,n):S(t,n))},function(e){r||(r=!0,E(t,e))},"Settle: "+(t._label||" unknown promise"));!r&&i&&(r=!0,E(t,i))},t)}function y(t,e){e._state===tt?S(t,e._result):e._state===et?E(t,e._result):x(e,void 0,function(e){return w(t,e)},function(e){return E(t,e)})}function _(t,n,r){n.constructor===t.constructor&&r===l&&n.constructor.resolve===h?y(t,n):r===nt?(E(t,nt.error),nt.error=null):void 0===r?S(t,n):e(r)?g(t,n,r):S(t,n)}function w(e,n){e===n?E(e,d()):t(n)?_(e,n,m(n)):S(e,n)}function b(t){t._onerror&&t._onerror(t._result),T(t)}function S(t,e){t._state===Z&&(t._result=e,t._state=tt,0!==t._subscribers.length&&q(T,t))}function E(t,e){t._state===Z&&(t._state=et,t._result=e,q(b,t))}function x(t,e,n,r){var i=t._subscribers,s=i.length;t._onerror=null,i[s]=e,i[s+tt]=n,i[s+et]=r,0===s&&t._state&&q(T,t)}function T(t){var e=t._subscribers,n=t._state;if(0!==e.length){for(var r=void 0,i=void 0,s=t._result,o=0;o<e.length;o+=3)r=e[o],i=e[o+n],r?M(n,r,i,s):i(s);t._subscribers.length=0}}function C(t,e){try{return t(e)}catch(n){return nt.error=n,nt}}function M(t,n,r,i){var s=e(r),o=void 0,u=void 0,c=void 0,a=void 0;if(s){if(o=C(r,i),o===nt?(a=!0,u=o.error,o.error=null):c=!0,n===o)return void E(n,v())}else o=i,c=!0;n._state!==Z||(s&&c?w(n,o):a?E(n,u):t===tt?S(n,o):t===et&&E(n,o))}function z(t,e){try{e(function(e){w(t,e)},function(e){E(t,e)})}catch(n){E(t,n)}}function P(){return rt++}function j(t){t[X]=rt++,t._state=void 0,t._result=void 0,t._subscribers=[]}function R(){return new Error("Array Methods must be provided an Array")}function L(t){return new it(this,t).promise}function k(t){var e=this;return new e($(t)?function(n,r){for(var i=t.length,s=0;i>s;s++)e.resolve(t[s]).then(n,r)}:function(t,e){return e(new TypeError("You must pass an array to race."))})}function D(t){var e=this,n=new e(p);return E(n,t),n}function O(){throw new TypeError("You must pass a resolver function as the first argument to the promise constructor")}function U(){throw new TypeError("Failed to construct 'Promise': Please use the 'new' operator, this object constructor cannot be called as a function.")}function B(){var t=void 0;if("undefined"!=typeof global)t=global;else if("undefined"!=typeof self)t=self;else try{t=Function("return this")()}catch(e){throw new Error("polyfill failed because global object is unavailable in this environment")}var n=t.Promise;if(n){var r=null;try{r=Object.prototype.toString.call(n.resolve())}catch(e){}if("[object Promise]"===r&&!n.cast)return}t.Promise=st}var I=void 0;I=Array.isArray?Array.isArray:function(t){return"[object Array]"===Object.prototype.toString.call(t)};var $=I,W=0,F=void 0,Q=void 0,q=function(t,e){V[W]=t,V[W+1]=e,W+=2,2===W&&(Q?Q(a):J())},G="undefined"!=typeof window?window:void 0,H=G||{},N=H.MutationObserver||H.WebKitMutationObserver,Y="undefined"==typeof self&&"undefined"!=typeof process&&"[object process]"==={}.toString.call(process),K="undefined"!=typeof Uint8ClampedArray&&"undefined"!=typeof importScripts&&"undefined"!=typeof MessageChannel,V=new Array(1e3),J=void 0;J=Y?i():N?o():K?u():void 0===G&&"function"==typeof require?f():c();var X=Math.random().toString(36).substring(2),Z=void 0,tt=1,et=2,nt={error:null},rt=0,it=function(){function t(t,e){this._instanceConstructor=t,this.promise=new t(p),this.promise[X]||j(this.promise),$(e)?(this.length=e.length,this._remaining=e.length,this._result=new Array(this.length),0===this.length?S(this.promise,this._result):(this.length=this.length||0,this._enumerate(e),0===this._remaining&&S(this.promise,this._result))):E(this.promise,R())}return t.prototype._enumerate=function(t){for(var e=0;this._state===Z&&e<t.length;e++)this._eachEntry(t[e],e)},t.prototype._eachEntry=function(t,e){var n=this._instanceConstructor,r=n.resolve;if(r===h){var i=m(t);if(i===l&&t._state!==Z)this._settledAt(t._state,e,t._result);else if("function"!=typeof i)this._remaining--,this._result[e]=t;else if(n===st){var s=new n(p);_(s,t,i),this._willSettleAt(s,e)}else this._willSettleAt(new n(function(e){return e(t)}),e)}else this._willSettleAt(r(t),e)},t.prototype._settledAt=function(t,e,n){var r=this.promise;r._state===Z&&(this._remaining--,t===et?E(r,n):this._result[e]=n),0===this._remaining&&S(r,this._result)},t.prototype._willSettleAt=function(t,e){var n=this;x(t,void 0,function(t){return n._settledAt(tt,e,t)},function(t){return n._settledAt(et,e,t)})},t}(),st=function(){function t(e){this[X]=P(),this._result=this._state=void 0,this._subscribers=[],p!==e&&("function"!=typeof e&&O(),this instanceof t?z(this,e):U())}return t.prototype["catch"]=function(t){return this.then(null,t)},t.prototype["finally"]=function(t){var e=this,n=e.constructor;return e.then(function(e){return n.resolve(t()).then(function(){return e})},function(e){return n.resolve(t()).then(function(){throw e})})},t}();return st.prototype.then=l,st.all=L,st.race=k,st.resolve=h,st.reject=D,st._setScheduler=n,st._setAsap=r,st._asap=q,st.polyfill=B,st.Promise=st,st}),!function(t,e){"object"==typeof exports&&"undefined"!=typeof module?module.exports=e():"function"==typeof define&&define.amd?define(e):t.ES6Promise=e()}(this,function(){"use strict";function t(t){var e=typeof t;return null!==t&&("object"===e||"function"===e)}function e(t){return"function"==typeof t}function n(t){Q=t}function r(t){q=t}function i(){return function(){return process.nextTick(a)}}function s(){return"undefined"!=typeof F?function(){F(a)}:c()}function o(){var t=0,e=new N(a),n=document.createTextNode("");return e.observe(n,{characterData:!0}),function(){n.data=t=++t%2}}function u(){var t=new MessageChannel;return t.port1.onmessage=a,function(){return t.port2.postMessage(0)}}function c(){var t=setTimeout;return function(){return t(a,1)}}function a(){for(var t=0;W>t;t+=2){var e=V[t],n=V[t+1];e(n),V[t]=void 0,V[t+1]=void 0}W=0}function f(){try{var t=Function("return this")().require("vertx");return F=t.runOnLoop||t.runOnContext,s()}catch(e){return c()}}function l(t,e){var n=this,r=new this.constructor(p);void 0===r[X]&&j(r);var i=n._state;if(i){var s=arguments[i-1];q(function(){return M(i,r,s,n._result)})}else x(n,r,t,e);return r}function h(t){var e=this;if(t&&"object"==typeof t&&t.constructor===e)return t;var n=new e(p);return w(n,t),n}function p(){}function d(){return new TypeError("You cannot resolve a promise with itself")}function v(){return new TypeError("A promises callback cannot return that same promise.")}function m(t){try{return t.then}catch(e){return nt.error=e,nt}}function A(t,e,n,r){try{t.call(e,n,r)}catch(i){return i}}function g(t,e,n){q(function(t){var r=!1,i=A(n,e,function(n){r||(r=!0,e!==n?w(t,n):S(t,n))},function(e){r||(r=!0,E(t,e))},"Settle: "+(t._label||" unknown promise"));!r&&i&&(r=!0,E(t,i))},t)}function y(t,e){e._state===tt?S(t,e._result):e._state===et?E(t,e._result):x(e,void 0,function(e){return w(t,e)},function(e){return E(t,e)})}function _(t,n,r){n.constructor===t.constructor&&r===l&&n.constructor.resolve===h?y(t,n):r===nt?(E(t,nt.error),nt.error=null):void 0===r?S(t,n):e(r)?g(t,n,r):S(t,n)}function w(e,n){e===n?E(e,d()):t(n)?_(e,n,m(n)):S(e,n)}function b(t){t._onerror&&t._onerror(t._result),T(t)}function S(t,e){t._state===Z&&(t._result=e,t._state=tt,0!==t._subscribers.length&&q(T,t))}function E(t,e){t._state===Z&&(t._state=et,t._result=e,q(b,t))}function x(t,e,n,r){var i=t._subscribers,s=i.length;t._onerror=null,i[s]=e,i[s+tt]=n,i[s+et]=r,0===s&&t._state&&q(T,t)}function T(t){var e=t._subscribers,n=t._state;if(0!==e.length){for(var r=void 0,i=void 0,s=t._result,o=0;o<e.length;o+=3)r=e[o],i=e[o+n],r?M(n,r,i,s):i(s);t._subscribers.length=0}}function C(t,e){try{return t(e)}catch(n){return nt.error=n,nt}}function M(t,n,r,i){var s=e(r),o=void 0,u=void 0,c=void 0,a=void 0;if(s){if(o=C(r,i),o===nt?(a=!0,u=o.error,o.error=null):c=!0,n===o)return void E(n,v())}else o=i,c=!0;n._state!==Z||(s&&c?w(n,o):a?E(n,u):t===tt?S(n,o):t===et&&E(n,o))}function z(t,e){try{e(function(e){w(t,e)},function(e){E(t,e)})}catch(n){E(t,n)}}function P(){return rt++}function j(t){t[X]=rt++,t._state=void 0,t._result=void 0,t._subscribers=[]}function R(){return new Error("Array Methods must be provided an Array")}function L(t){return new it(this,t).promise}function k(t){var e=this;return new e($(t)?function(n,r){for(var i=t.length,s=0;i>s;s++)e.resolve(t[s]).then(n,r)}:function(t,e){return e(new TypeError("You must pass an array to race."))})}function D(t){var e=this,n=new e(p);return E(n,t),n}function O(){throw new TypeError("You must pass a resolver function as the first argument to the promise constructor")}function U(){throw new TypeError("Failed to construct 'Promise': Please use the 'new' operator, this object constructor cannot be called as a function.")}function B(){var t=void 0;if("undefined"!=typeof global)t=global;else if("undefined"!=typeof self)t=self;else try{t=Function("return this")()}catch(e){throw new Error("polyfill failed because global object is unavailable in this environment")}var n=t.Promise;if(n){var r=null;try{r=Object.prototype.toString.call(n.resolve())}catch(e){}if("[object Promise]"===r&&!n.cast)return}t.Promise=st}var I=void 0;I=Array.isArray?Array.isArray:function(t){return"[object Array]"===Object.prototype.toString.call(t)};var $=I,W=0,F=void 0,Q=void 0,q=function(t,e){V[W]=t,V[W+1]=e,W+=2,2===W&&(Q?Q(a):J())},G="undefined"!=typeof window?window:void 0,H=G||{},N=H.MutationObserver||H.WebKitMutationObserver,Y="undefined"==typeof self&&"undefined"!=typeof process&&"[object process]"==={}.toString.call(process),K="undefined"!=typeof Uint8ClampedArray&&"undefined"!=typeof importScripts&&"undefined"!=typeof MessageChannel,V=new Array(1e3),J=void 0;J=Y?i():N?o():K?u():void 0===G&&"function"==typeof require?f():c();var X=Math.random().toString(36).substring(2),Z=void 0,tt=1,et=2,nt={error:null},rt=0,it=function(){function t(t,e){this._instanceConstructor=t,this.promise=new t(p),this.promise[X]||j(this.promise),$(e)?(this.length=e.length,this._remaining=e.length,this._result=new Array(this.length),0===this.length?S(this.promise,this._result):(this.length=this.length||0,this._enumerate(e),0===this._remaining&&S(this.promise,this._result))):E(this.promise,R())}return t.prototype._enumerate=function(t){for(var e=0;this._state===Z&&e<t.length;e++)this._eachEntry(t[e],e)},t.prototype._eachEntry=function(t,e){var n=this._instanceConstructor,r=n.resolve;if(r===h){var i=m(t);if(i===l&&t._state!==Z)this._settledAt(t._state,e,t._result);else if("function"!=typeof i)this._remaining--,this._result[e]=t;else if(n===st){var s=new n(p);_(s,t,i),this._willSettleAt(s,e)}else this._willSettleAt(new n(function(e){return e(t)}),e)}else this._willSettleAt(r(t),e)},t.prototype._settledAt=function(t,e,n){var r=this.promise;r._state===Z&&(this._remaining--,t===et?E(r,n):this._result[e]=n),0===this._remaining&&S(r,this._result)},t.prototype._willSettleAt=function(t,e){var n=this;x(t,void 0,function(t){return n._settledAt(tt,e,t)},function(t){return n._settledAt(et,e,t)})},t}(),st=function(){function t(e){this[X]=P(),this._result=this._state=void 0,this._subscribers=[],p!==e&&("function"!=typeof e&&O(),this instanceof t?z(this,e):U())}return t.prototype["catch"]=function(t){return this.then(null,t)},t.prototype["finally"]=function(t){var e=this,n=e.constructor;return e.then(function(e){return n.resolve(t()).then(function(){return e})},function(e){return n.resolve(t()).then(function(){throw e})})},t}();return st.prototype.then=l,st.all=L,st.race=k,st.resolve=h,st.reject=D,st._setScheduler=n,st._setAsap=r,st._asap=q,st.polyfill=B,st.Promise=st,st.polyfill(),st});