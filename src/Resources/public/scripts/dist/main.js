var LeadMatching;(()=>{var t={747:(t,e,o)=>{"use strict";function r(){var t={},e=!1,o=0,n=arguments.length;"[object Boolean]"===Object.prototype.toString.call(arguments[0])&&(e=arguments[0],o++);for(var i=function(o){for(var n in o)Object.prototype.hasOwnProperty.call(o,n)&&(e&&"[object Object]"===Object.prototype.toString.call(o[n])?t[n]=r(!0,t[n],o[n]):t[n]=o[n])};o<n;o++){var a=arguments[o];i(a)}return t}o.r(e),o.d(e,{LeadMatching:()=>n});class n{constructor(t){this.options=r(!0,{form:".hasteform_estate > form",configId:null,proximitySearch:{active:!1,engine:"system",selector:'[name="regions"]'},countLive:{active:!0,sourceUrl:"",route:"/leadmatching/count",selector:"[data-counter]",format:t=>t}},t||{}),this.form=document.querySelector(this.options.form),this.options.countLive?.active&&this._initLiveCounting(),this.options.proximitySearch?.active&&this._initProximitySearch()}_initLiveCounting(){this.domCounter=document.querySelectorAll(this.options.countLive.selector),this.domState=[];for(const t of this.form.elements){let e="blur";switch(t.tagName.toLowerCase()){case"select":e="change";break;case"button":continue}this.domState[t.name]=t.value,t.addEventListener(e,(t=>this._count(t)))}}_count(t){const e=t.target;if(this.domState[e.name]===e.value)return;this.domState[e.name]=e.value;const o=this.options.countLive.sourceUrl+this.options.countLive.route+"/"+this.options.configId,r=new FormData(this.form);fetch(o,{method:"POST",body:r}).then((t=>t.json())).then((t=>{for(const e of this.domCounter)e.innerText=this.options.countLive.format(t.count)}))}_initProximitySearch(){}}},36:(t,e,o)=>{const{LeadMatching:r}=o(747);t.exports=r}},e={};function o(r){var n=e[r];if(void 0!==n)return n.exports;var i=e[r]={exports:{}};return t[r](i,i.exports,o),i.exports}o.d=(t,e)=>{for(var r in e)o.o(e,r)&&!o.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:e[r]})},o.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),o.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})};var r=o(36);LeadMatching=r})();