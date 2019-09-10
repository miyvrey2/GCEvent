/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/assets/js/app.js":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("./resources/assets/js/slider.js");
__webpack_require__("./resources/assets/js/footer.js");
__webpack_require__("./resources/assets/js/header_search.js");

/***/ }),

/***/ "./resources/assets/js/footer.js":
/***/ (function(module, exports) {

// if page is taller than the viewport height, redo the footer
window.onload = function () {
    setFooterPosition();
};

document.body.onresize = function () {
    setFooterPosition();
};

function setFooterPosition() {
    var body = document.body,
        html = document.documentElement;

    var pageHeight = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);

    var vwHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);

    if (pageHeight <= vwHeight) {
        document.getElementsByTagName("footer")[0].style.bottom = "0";
    }
}

/***/ }),

/***/ "./resources/assets/js/header_search.js":
/***/ (function(module, exports) {

document.getElementById("search-button").onclick = function (e) {
    foldingSearch(e);
};

function foldingSearch(e) {

    var searchInput = document.getElementById("search-input");
    var searchButton = document.getElementById("search-button");

    if (searchInput.offsetWidth < 250) {

        // prevent from searching
        e.preventDefault();

        // show the search bar
        searchInput.style.width = "250px";
        searchInput.focus();
        console.log('a');

        searchButton.classList.add('active');
    }
}

/***/ }),

/***/ "./resources/assets/js/slider.js":
/***/ (function(module, exports) {

if (document.getElementsByClassName("enzow_slider")[0]) {
    window.addEventListener('load', initEnzowSlider(1, 4, 'cover', '400px'));
}

global_interval_speed = 4000;
tid = "";

function initEnzowSlider(id, interval_speed, contain_or_cover, slider_height) {

    global_interval_speed = interval_speed * 1000;
    // Let's find our slider
    outerSlider = document.getElementById("enzow_slider_" + id);

    // Also get our slides
    sliderSlides = document.getElementsByClassName("enzow_slide");

    // Amount of slides
    AmountSlides = sliderSlides.length;
    currentSlide = 0;
    busy = 0;
    var_pause = 0;

    // container around the slides
    slidesContainer = document.getElementsByClassName("exeptionContainer");
    slidesContainerChoice = true;
    slidesContainerWidth = '100%';

    // Check before act
    if (slider_height == 'predefined') {
        slider_height = "30%";
    }

    // Set some variables for the slider
    sliderWidth = '100%';
    sliderHeight = slider_height;

    // Give the slider the right sizes
    outerSlider.style.height = sliderHeight;
    outerSlider.className += " " + contain_or_cover;

    // Give the slides also some sizes
    for (i = 0; i < sliderSlides.length; i++) {
        sliderSlides[i].style.width = sliderWidth;
        sliderSlides[i].style.height = sliderHeight;
    }

    // Check for container option
    if (slidesContainerChoice == true) {
        for (i = 0; i < slidesContainer.length; i++) {
            slidesContainer[i].style.width = slidesContainerWidth;
            slidesContainer[i].style.left = '0px';
        }
    }

    EnzowSlider();
    EnzowCheckForVideos();

    // https://github.com/anselmh/object-fit
    //objectFit.polyfill({
    //    selector: '.enzow_slide img', // this can be any CSS selector
    //    fittype: 'cover', // either contain, cover, fill or none
    //    disableCrossDomain: 'true' // either 'true' or 'false' to not parse external CSS files.
    //});
}

function EnzowCheckForVideos() {
    // First, check all wrappers
    enzow_slider_video_wrapper = document.getElementsByClassName('video_wrapper');

    for (var i = 0; i < enzow_slider_video_wrapper.length; i++) {

        // Initiate the video resume play option
        enzow_slider_video_wrapper[i].addEventListener("click", function () {
            video_play(this);
        });

        // Now, check the containing video
        var enzow_slider_video = enzow_slider_video_wrapper[i].getElementsByTagName('video');

        // Initiate the video resume play option
        enzow_slider_video[0].addEventListener("ended", function () {
            video_ended(this.parentElement);
        });
    }
}

function EnzowSlider() {
    // In the end, be sure the slider is loaded :)
    //document.getElementById("demo").innerHTML = "slider is loaded.";
    for (i = 0; i < sliderSlides.length; i++) {
        sliderSlides[i].style.opacity = 0;
        sliderSlides[i].style.display = "none";
    }
    sliderSlides[0].style.display = "inline-block";
    sliderSlides[0].style.opacity = 1;
    tid = setInterval(function () {
        EnzowSliderTransitionOne(currentSlide, 'add');
    }, global_interval_speed);
}

function EnzowSliderTransitionOne(div, direction) {

    if (var_pause != 1) {
        // Add the next image
        if (direction == 'add') {
            if (div == AmountSlides - 1) {
                currentSlide = 0;
            } else {
                currentSlide += 1;
            }
        } else {
            // Less the image
            if (div == 0) {
                currentSlide = AmountSlides - 1;
            } else {
                currentSlide -= 1;
            }
        }

        // TODO Make this for multiple video's possible
        video = document.getElementById("enzow_video_id_" + currentSlide);

        if (sliderSlides[currentSlide].contains(video) && var_pause == 0) {
            removeOpacity(div);
            addOpacity(currentSlide);
            video.play();
            slider_pause();
        } else {

            removeOpacity(div);
            addOpacity(currentSlide);
        }

        if (var_pause == 2) {
            if (video != null) {
                video.pause();
                video_pause(currentSlide);
            }
        }
    }
}

function removeOpacity(div) {
    sliderSlides[div].style.opacity = 1;

    checkup = tabVisible();
    if (checkup == false) {
        (function fade() {
            if ((sliderSlides[div].style.opacity -= .05) < 0.05) {
                sliderSlides[div].style.display = "none";
                sliderSlides[div].style.opacity = 0;
                busy = 0;
            } else {
                setTimeout(fade, 20);
                busy = 1;
            }
        })();
    } else {
        sliderSlides[div].style.display = "none";
        sliderSlides[div].style.opacity = 0;
    }
}

function addOpacity(div) {
    sliderSlides[div].style.display = "block";

    checkup = tabVisible();
    if (checkup == false) {
        (function fade() {
            var val = parseFloat(sliderSlides[div].style.opacity);
            if (!((val += .05) > 1)) {
                sliderSlides[div].style.opacity = val;
                setTimeout(fade, 20);
                busy = 1;
            } else {
                busy = 0;
            }
        })();
    } else {
        sliderSlides[div].style.opacity = 1;
    }
}

function previous() {
    if (var_pause == 1) {
        var_pause = 2;
    }

    if (busy == 0) {
        EnzowSliderTransitionOne(currentSlide, 'less');
        clearInterval(tid);
        tid = setInterval(function () {
            EnzowSliderTransitionOne(currentSlide, 'add');
        }, global_interval_speed);
    }
}

function next() {
    if (var_pause == 1) {
        var_pause = 2;
    }

    if (busy == 0) {
        EnzowSliderTransitionOne(currentSlide, 'add');
        clearInterval(tid);
        tid = setInterval(function () {
            EnzowSliderTransitionOne(currentSlide, 'add');
        }, global_interval_speed);
    }
}

function slider_pause() {
    var_pause = 1;
    clearInterval(tid);
}

function video_play(div) {
    var video_wrapper_element = div;
    var video_element = video_wrapper_element.getElementsByTagName("video");

    if (hasClass(video_wrapper_element, 'press_to_play')) {

        // Remove the overlay
        removeClass(video_wrapper_element, 'press_to_play');

        // Play the video
        video_element[0].play();

        // Pause the slider so we can see the video
        slider_pause();
    }
}

function video_pause(div) {
    var video_wrapper_element = sliderSlides[div].getElementsByClassName("video_wrapper");
    var video_element = sliderSlides[div].getElementsByTagName("video");

    if (!hasClass(video_wrapper_element[0], 'press_to_play')) {
        video_wrapper_element[0].className += " press_to_play";
    }
}

function video_ended(div) {
    var video_wrapper_element = div;

    // add the overlay
    if (!hasClass(video_wrapper_element, 'press_to_play')) {
        video_wrapper_element.className += " press_to_play";
    }
}

function tabVisible() {

    var hidden = "hidden";

    // Standards:
    if (hidden in document) document.addEventListener("visibilitychange", onchange);else if ((hidden = "mozHidden") in document) document.addEventListener("mozvisibilitychange", onchange);else if ((hidden = "webkitHidden") in document) document.addEventListener("webkitvisibilitychange", onchange);else if ((hidden = "msHidden") in document) document.addEventListener("msvisibilitychange", onchange);

    // IE 9 and lower:
    else if ("onfocusin" in document) document.onfocusin = document.onfocusout = onchange;

        // All others:
        else window.onpageshow = window.onpagehide = window.onfocus = window.onblur = onchange;

    function onchange(evt) {
        var v = "visible",
            h = "hidden",
            evtMap = {
            focus: v, focusin: v, pageshow: v, blur: h, focusout: h, pagehide: h
        };

        evt = evt || window.event;
        if (evt.type in evtMap) {
            document.body.className = document.body.className.replace(new RegExp('(?:^|\\s)' + 'visible' + '(?:\\s|$)'), '');
            document.body.className = document.body.className.replace(new RegExp('(?:^|\\s)' + 'hidden' + '(?:\\s|$)'), '');

            if (!hasClass(document.body, evtMap[evt.type])) {
                document.body.className += " " + evtMap[evt.type];
            }
        }

        if (busy == 0) {
            clearInterval(tid);
            tid = setInterval(function () {
                EnzowSliderTransitionOne(currentSlide, 'add');
            }, global_interval_speed);
        }
    }

    // set the initial state (but only if browser supports the Page Visibility API)
    if (document[hidden] !== undefined) {
        onchange({ type: document[hidden] ? "blur" : "focus" });
    }

    return document[hidden];
}

/**
 * function hasClass - Check wether a class is on the requested element
 * @param element
 * @param cls
 * @returns {boolean}
 */

function hasClass(element, cls) {
    return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1;
}

/**
 * function removeClass - removes the given class on the requested element
 * @param element
 * @param cls
 */
function removeClass(element, cls) {
    element.className = element.className.replace(new RegExp('(?:^|\\s)' + cls + '(?:\\s|$)'), '');
}

/**
 * On Document Ready in vanilla Javascript
 * @param fn
 */
function ready(fn) {
    if (document.readyState != 'loading') {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

/***/ }),

/***/ "./resources/assets/sass/app.scss":
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/assets/sass/backend-app.scss":
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("./resources/assets/js/app.js");
__webpack_require__("./resources/assets/sass/app.scss");
module.exports = __webpack_require__("./resources/assets/sass/backend-app.scss");


/***/ })

/******/ });