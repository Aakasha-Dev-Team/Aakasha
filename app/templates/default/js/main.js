/**
 * Show modal
 * @author Bobi me@borislazarov.com on 20 Nov 2014
 * return void
 */
function modal(title, body) {
    jQuery("#myModalLabel").text(title);
    jQuery("#myModalBody").html(body);
    jQuery("#myModal").modal("show");
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