!function(e){var i={};function t(n){if(i[n])return i[n].exports;var s=i[n]={i:n,l:!1,exports:{}};return e[n].call(s.exports,s,s.exports,t),s.l=!0,s.exports}t.m=e,t.c=i,t.d=function(e,i,n){t.o(e,i)||Object.defineProperty(e,i,{configurable:!1,enumerable:!0,get:n})},t.n=function(e){var i=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(i,"a",i),i},t.o=function(e,i){return Object.prototype.hasOwnProperty.call(e,i)},t.p="",t(t.s=0)}({0:function(e,i,t){t("sV/x"),t("xZZD"),e.exports=t("TK1j")},TK1j:function(e,i){},dz4v:function(e,t){function n(e,i){1!=var_pause&&("add"==i?e==AmountSlides-1?currentSlide=0:currentSlide+=1:0==e?currentSlide=AmountSlides-1:currentSlide-=1,video=document.getElementById("enzow_video_id_"+currentSlide),sliderSlides[currentSlide].contains(video)&&0==var_pause?(s(e),d(currentSlide),video.play(),l()):(s(e),d(currentSlide)),2==var_pause&&null!=video&&(video.pause(),function(e){var i=sliderSlides[e].getElementsByClassName("video_wrapper");sliderSlides[e].getElementsByTagName("video");c(i[0],"press_to_play")||(i[0].className+=" press_to_play")}(currentSlide)))}function s(e){sliderSlides[e].style.opacity=1,checkup=a(),0==checkup?function i(){(sliderSlides[e].style.opacity-=.05)<.05?(sliderSlides[e].style.display="none",sliderSlides[e].style.opacity=0,busy=0):(setTimeout(i,20),busy=1)}():(sliderSlides[e].style.display="none",sliderSlides[e].style.opacity=0)}function d(e){sliderSlides[e].style.display="block",checkup=a(),0==checkup?function i(){var t=parseFloat(sliderSlides[e].style.opacity);(t+=.05)>1?busy=0:(sliderSlides[e].style.opacity=t,setTimeout(i,20),busy=1)}():sliderSlides[e].style.opacity=1}function l(){var_pause=1,clearInterval(tid)}function o(e){var i,t,n=e,s=n.getElementsByTagName("video");c(n,"press_to_play")&&(t="press_to_play",(i=n).className=i.className.replace(new RegExp("(?:^|\\s)"+t+"(?:\\s|$)"),""),s[0].play(),l())}function r(e){var i=e;c(i,"press_to_play")||(i.className+=" press_to_play")}function a(){var e="hidden";function i(e){var i="visible",t="hidden",s={focus:i,focusin:i,pageshow:i,blur:t,focusout:t,pagehide:t};(e=e||window.event).type in s&&(document.body.className=document.body.className.replace(new RegExp("(?:^|\\s)visible(?:\\s|$)"),""),document.body.className=document.body.className.replace(new RegExp("(?:^|\\s)hidden(?:\\s|$)"),""),c(document.body,s[e.type])||(document.body.className+=" "+s[e.type])),0==busy&&(clearInterval(tid),tid=setInterval(function(){n(currentSlide,"add")},global_interval_speed))}return e in document?document.addEventListener("visibilitychange",i):(e="mozHidden")in document?document.addEventListener("mozvisibilitychange",i):(e="webkitHidden")in document?document.addEventListener("webkitvisibilitychange",i):(e="msHidden")in document?document.addEventListener("msvisibilitychange",i):"onfocusin"in document?document.onfocusin=document.onfocusout=i:window.onpageshow=window.onpagehide=window.onfocus=window.onblur=i,void 0!==document[e]&&i({type:document[e]?"blur":"focus"}),document[e]}function c(e,i){return(" "+e.className+" ").indexOf(" "+i+" ")>-1}document.getElementsByClassName("enzow_slider")[0]&&window.addEventListener("load",function(e,t,s,d){global_interval_speed=1e3*t,outerSlider=document.getElementById("enzow_slider_"+e),sliderSlides=document.getElementsByClassName("enzow_slide"),AmountSlides=sliderSlides.length,currentSlide=0,busy=0,var_pause=0,slidesContainer=document.getElementsByClassName("exeptionContainer"),slidesContainerChoice=!0,slidesContainerWidth="100%","predefined"==d&&(d="30%");for(sliderWidth="100%",sliderHeight=d,outerSlider.style.height=sliderHeight,outerSlider.className+=" "+s,i=0;i<sliderSlides.length;i++)sliderSlides[i].style.width=sliderWidth,sliderSlides[i].style.height=sliderHeight;if(1==slidesContainerChoice)for(i=0;i<slidesContainer.length;i++)slidesContainer[i].style.width=slidesContainerWidth,slidesContainer[i].style.left="0px";(function(){for(i=0;i<sliderSlides.length;i++)sliderSlides[i].style.opacity=0,sliderSlides[i].style.display="none";sliderSlides[0].style.display="inline-block",sliderSlides[0].style.opacity=1,tid=setInterval(function(){n(currentSlide,"add")},global_interval_speed)})(),function(){enzow_slider_video_wrapper=document.getElementsByClassName("video_wrapper");for(var e=0;e<enzow_slider_video_wrapper.length;e++){enzow_slider_video_wrapper[e].addEventListener("click",function(){o(this)});var i=enzow_slider_video_wrapper[e].getElementsByTagName("video");i[0].addEventListener("ended",function(){r(this.parentElement)})}}()}(1,4,"cover","400px")),global_interval_speed=4e3,tid=""},"sV/x":function(e,i,t){t("dz4v"),t("uR02")},uR02:function(e,i){function t(){var e=document.body,i=document.documentElement;Math.max(e.scrollHeight,e.offsetHeight,i.clientHeight,i.scrollHeight,i.offsetHeight)<=Math.max(document.documentElement.clientHeight,window.innerHeight||0)&&(document.getElementsByTagName("footer")[0].style.bottom="0")}window.onload=function(){t()},document.body.onresize=function(){t()}},xZZD:function(e,i){}});