/**
 * Show modal
 * @author Bobi me@borislazarov.com on 20 Nov 2014
 * return void
 */
function modal(title, body, color) {
    jQuery("#myModalLabel").text(title);
    jQuery("#myModalBody").html(body);
    jQuery("#myModal").modal("show");

    if (color) {
        switch (color) {
            case 'success':
                jQuery('#myModal h4, #myModal .modal-body').css('color','#009933');
                break;
            case 'warning':
                jQuery('#myModal h4, #myModal .modal-body').css('color','#999933');
                break;
            case 'error':
                jQuery('#myModal h4, #myModal .modal-body').css('color','#FF0033');
        }
    }
}

/**
 * @author Adam Plocher on 5 Sep 2010
 * @source http://stackoverflow.com/questions/3646914/how-do-i-check-if-file-exists-in-jquery-or-javascript
 * @returns boolean
 */
function urlExists(url) {
    var http = new XMLHttpRequest();
    http.open('HEAD', url, false);
    http.send();
    return http.status!=404;
}

var load = new Object();

//show wait cursor
load.show = function() { jQuery('body').css('cursor', 'wait'); };

//hide wait cursor
load.hide = function() { jQuery('body').css('cursor', 'auto'); };

var cookie = new Object();

//store data using name/value format
cookie.set = function(key, value) {
    if (cookie.check(key) == true) {
        cookie.destroy(key);
    }
    document.cookie = key + "=" + value + "; path=/";
};

//Get cookie
cookie.get = function(key) {
    var name = key + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) != -1) return c.substring(name.length,c.length);
    }
    return "";
}

//Delate cookie
cookie.destroy = function(key) {
    document.cookie = encodeURIComponent(key) + "=deleted; expires=" + new Date(0).toUTCString();
}

//Check cookie
cookie.check = function(key) {
    var name = cookie.get(key);
    if (name != "") {
        return true;
    } else {
        return false;
    }
}

var file = new Object();

//Download file
file.download = function(url, name) {
    var anchor = document.createElement('a');
        anchor.setAttribute('href', url);
        anchor.setAttribute('download', name);
    
    // This works in Chrome, not in Firefox
    //jQuery(anchor)[0].click();
    
    // For Firefox, we need to manually do a click event
    
    // Create event
    var ev = document.createEvent('MouseEvents');
        ev.initMouseEvent('click', true, false, self, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
    
    // Fire event
    anchor.dispatchEvent(ev);
}

//Search an array for a value and get its key
function arraySearch(arr, val) {
  var index;
  for (var i = 0; i < arr.length; i++) {
    // use '===' if you strictly want to find the same type
    if (arr[i] == val) {
      if (index == undefined) index = i;
      // return false if duplicate is found
      else return false;
    }
  }

  // return false if no element found, or index of the element
  return index == undefined ? false : index;
}


//from new template JS is below -->
//Sliding Effect Control
head.js("/app/templates/sbydev_realdark/js/skin-select/jquery.cookie.js");
head.js("/app/templates/sbydev_realdark/js/skin-select/skin-select.js");

//Showing Date
head.js("/app/templates/sbydev_realdark/js/clock/date.js");

//Bootstrap
//head.js("assets/js/bootstrap.js");

//NEWS STICKER
//head.js("/app/templates/sbydev_realdark/js/newsticker/jquery.newsTicker.js", function() {
//
//    var nt_title = $('#nt-title').newsTicker({
//        row_height: 18,
//        max_rows: 1,
//        duration: 5000,
//        pauseOnHover: 0
//    });
//
//
//});

//------------------------------------------------------------- 


////Acordion and Sliding menu

head.js("/app/templates/sbydev_realdark/js/custom/scriptbreaker-multiple-accordion-1.js", function() {

    $(".topnav").accordionze({
        accordionze: true,
        speed: 500,
        closedSign: '<img src="/app/templates/sbydev_realdark/img/plus.png">',
        openedSign: '<img src="/app/templates/sbydev_realdark/img/minus.png">'
    });

});

////Right Sliding menu

head.js("/app/templates/sbydev_realdark/js/slidebars/slidebars.min.js", "http://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js", function() {

    $(document).ready(function() {
        var mySlidebars = new $.slidebars();

        $('.toggle-left').on('click', function() {
            mySlidebars.toggle('right');
        });
    });
});

//-------------------------------------------------------------

//SEARCH MENU
head.js("/app/templates/sbydev_realdark/js/search/jquery.quicksearch.js", function() {

    $('input.id_search').quicksearch('#menu-showhide li, .menu-left-nest li');
   
   

});
//-------------------------------------------------------------



//EASY PIE CHART
//head.js("/app/templates/sbydev_realdark/js/gage/jquery.easypiechart.min.js", function() {
//
//    $(function() {
//
//
//        $('.chart').easyPieChart({
//            easing: 'easeOutBounce',
//            trackColor: '#ffffff',
//            scaleColor: '#ffffff',
//            barColor: '#FF0064',
//            onStep: function(from, to, percent) {
//                $(this.el).find('.percent').text(Math.round(percent));
//            }
//        });
//        var chart = window.chart = $('.chart').data('easyPieChart');
//        $('.js_update').on('click', function() {
//            chart.update(Math.random() * 100);
//        });
//
//        $('.speed-car').easyPieChart({
//            easing: 'easeOutBounce',
//            trackColor: 'rgba(0,0,0,0.3)',
//            scaleColor: 'transparent',
//            barColor: '#0085DF',
//
//            lineWidth: 8,
//            onStep: function(from, to, percent) {
//                $(this.el).find('.percent2').text(Math.round(percent));
//            }
//        });
//        var chart = window.chart = $('.chart2').data('easyPieChart');
//        $('.js_update').on('click', function() {
//            chart.update(Math.random() * 100);
//        });
//        $('.overall').easyPieChart({
//            easing: 'easeOutBounce',
//            trackColor: 'rgba(0,0,0,0.3)',
//            scaleColor: '#323A45',
//            lineWidth: 35,
//            lineCap: 'butt',
//            barColor: '#FFB900',
//            onStep: function(from, to, percent) {
//                $(this.el).find('.percent3').text(Math.round(percent));
//            }
//        });
//    });
//
//});
//-------------------------------------------------------------

//TOOL TIP

head.js("/app/templates/sbydev_realdark/js/tip/jquery.tooltipster.js", function() {

    $('.tooltip-tip-x').tooltipster({
        position: 'right'

    });

    $('.tooltip-tip').tooltipster({
        position: 'right',
        animation: 'slide',
        theme: '.tooltipster-shadow',
        delay: 1,
        offsetX: '-12px',
        onlyOne: true

    });
    $('.tooltip-tip2').tooltipster({
        position: 'right',
        animation: 'slide',
        offsetX: '-12px',
        theme: '.tooltipster-shadow',
        onlyOne: true

    });
    $('.tooltip-top').tooltipster({
        position: 'top'
    });
    $('.tooltip-right').tooltipster({
        position: 'right'
    });
    $('.tooltip-left').tooltipster({
        position: 'left'
    });
    $('.tooltip-bottom').tooltipster({
        position: 'bottom'
    });
    $('.tooltip-reload').tooltipster({
        position: 'right',
        theme: '.tooltipster-white',
        animation: 'fade'
    });
    $('.tooltip-fullscreen').tooltipster({
        position: 'left',
        theme: '.tooltipster-white',
        animation: 'fade'
    });
    //For icon tooltip



});
//------------------------------------------------------------- 

//NICE SCROLL

head.js("/app/templates/sbydev_realdark/js/nano/jquery.nanoscroller.js", function() {

    $(".nano").nanoScroller({
        //stop: true 
        scroll: 'top',
        scrollTop: 0,
        sliderMinHeight: 40,
        preventPageScrolling: true
        //alwaysVisible: false

    });

});
//------------------------------------------------------------- 






//------------------------------------------------------------- 
//PAGE LOADER
head.js("/app/templates/sbydev_realdark/js/pace/pace.js", function() {

    paceOptions = {
        ajax: false, // disabled
        document: false, // disabled
        eventLag: false, // disabled
        elements: {
            selectors: ['.my-page']
        }
    };

});

//------------------------------------------------------------- 

//SPARKLINE CHART
head.js("/app/templates/sbydev_realdark/js/chart/jquery.sparkline.js", function() {

    $(function() {
        $('.inlinebar').sparkline('html', {
            type: 'bar',
            barWidth: '8px',
            height: '30px',
            barSpacing: '2px',
            barColor: '#A8BDCF'
        });
        $('.linebar').sparkline('html', {
            type: 'bar',
            barWidth: '5px',
            height: '30px',
            barSpacing: '2px',
            barColor: '#44BBC1'
        });
        $('.linebar2').sparkline('html', {
            type: 'bar',
            barWidth: '5px',
            height: '30px',
            barSpacing: '2px',
            barColor: '#AB6DB0'
        });
        $('.linebar3').sparkline('html', {
            type: 'bar',
            barWidth: '5px',
            height: '30px',
            barSpacing: '2px',
            barColor: '#19A1F9'
        });
    });

    $(function() {
        var sparklineLogin = function() {
            $('#sparkline').sparkline(
                [5, 6, 7, 9, 9, 5, 3, 2, 2, 4, 6, 7], {
                    type: 'line',
                    width: '100%',
                    height: '25',
                    lineColor: '#ffffff',
                    fillColor: '#0DB8DF',
                    lineWidth: 1,
                    spotColor: '#ffffff',
                    minSpotColor: '#ffffff',
                    maxSpotColor: '#ffffff',
                    highlightSpotColor: '#ffffff',
                    highlightLineColor: '#ffffff'
                }
            );
        }
        var sparkResize;
        $(window).resize(function(e) {
            clearTimeout(sparkResize);
            sparkResize = setTimeout(sparklineLogin, 500);
        });
        sparklineLogin();
    });


});

//------------------------------------------------------------- 

//DIGITAL CLOCK
head.js("/app/templates/sbydev_realdark/js/clock/jquery.clock.js", function() {

    //clock
    $('#digital-clock').clock({
        offset: '+3',
        type: 'digital'
    });


});


//------------------------------------------------------------- 

//head.js("/app/templates/sbydev_realdark/js/gage/raphael.2.1.0.min.js", "/app/templates/sbydev_realdark/js/gage/justgage.js", function() {
//
//
//
//    var g1;
//    window.onload = function() {
//        var g1 = new JustGage({
//            id: "g1",
//            value: getRandomInt(0, 1000),
//            min: 0,
//            max: 1000,
//            relativeGaugeSize: true,
//            gaugeColor: "rgba(0,0,0,0.4)",
//            levelColors: "#0DB8DF",
//            labelFontColor : "#ffffff",
//            titleFontColor: "#ffffff",
//            valueFontColor :"#ffffff",
//            label: "VISITORS",
//            gaugeWidthScale: 0.2,
//            donut: true
//        });
//    };
//
//});