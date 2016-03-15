var keys = {};
var count = 65;
var str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
for(var i=0; i<str.length; i++){
  var nextChar = str.charAt(i);
  keys[nextChar] = count;
  count++;
}

var harlemShake = {}, ripple = {}, asteroid = {}, katamari = {}, bomb = {}, pony = {}, myLittlePony = {};
harlemShake[keys.H] = false;
harlemShake[keys.S] = false;
ripple[keys.R] = false;
ripple[keys.P] = false;
asteroid[keys.A] = false;
asteroid[keys.D] = false;
katamari[keys.K] = false;
katamari[keys.I] = false;
bomb[keys.B] = false;
bomb[keys.M] = false;
pony[keys.F] = false;
pony[keys.T] = false;
myLittlePony[keys.L] = false;
myLittlePony[keys.P] = false;

$(document).keydown(function(e) {
	//Harlem Shake
  if (e.keyCode in harlemShake) {
    harlemShake[e.keyCode] = true;
    if (harlemShake[keys.H] && harlemShake[keys.S]) {
    	var s = document.createElement('script');
			s.setAttribute('src', "http://localhost/delphinium/plugins/delphinium/blossom/assets/javascript/harlem-shake.js");
			document.body.appendChild(s);
    }
  }
  //Page Ripple
  if (e.keyCode in ripple) {
    ripple[e.keyCode] = true;
    if (ripple[keys.R] && ripple[keys.P]) {
    	var s = document.createElement('script');
			s.setAttribute('src', "http://localhost/delphinium/plugins/delphinium/blossom/assets/javascript/jquery.ripples.js");
			document.body.appendChild(s);
      setInterval(function() {
        var $el = $('body');
        var x = Math.random() * $el.outerWidth();
        var y = Math.random() * $el.outerHeight();
        var dropRadius = 20;
        var strength = 0.04 + Math.random() * 0.04;

        $el.ripples('drop', x, y, dropRadius, strength);
  }, 400);
    }
  }
  //Asteroids 
  if (e.keyCode in asteroid) {
    asteroid[e.keyCode] = true;
    if (asteroid[keys.A] && asteroid[keys.D]) {
    	var s = document.createElement('script');
			s.setAttribute('src', "http://erkie.github.com/asteroids.min.js");
			document.body.appendChild(s);
    }
  }
  //Katamari 
  if (e.keyCode in katamari) {
    katamari[e.keyCode] = true;
    if (katamari[keys.K] && katamari[keys.I]) {
    	var s = document.createElement('script');
			s.setAttribute('src', "http://kathack.com/js/kh.js");
			document.body.appendChild(s);
    }
  }
  //Bombs 
  if (e.keyCode in bomb) {
    bomb[e.keyCode] = true;
    if (bomb[keys.B] && bomb[keys.M]) {
    	window.FONTBOMB_HIDE_CONFIRMATION = true;
    	var s = document.createElement('script');
			s.setAttribute('src', "http://fontbomb.ilex.ca/js/main.js");
			document.body.appendChild(s);
    }
  }
  //Ponies 
  if (e.keyCode in pony) {
    pony[e.keyCode] = true;
    if (pony[keys.F] && pony[keys.T]) {
    	var s = document.createElement('script');
			s.setAttribute('src', "http://websplat.bitbucket.org/websplat/loader.js");
			document.body.appendChild(s);
    }
  }
  //MyLittlePony
  if (e.keyCode in myLittlePony) {
    myLittlePony[e.keyCode] = true;
    if (myLittlePony[keys.L] && myLittlePony[keys.P]) {
    	var s = document.createElement('script');
    	s.setAttribute('src', "https://panzi.github.io/Browser-Ponies/basecfg.js");
    	document.body.appendChild(s);
    	var b = document.createElement('script');
    	b.setAttribute('src', "https://panzi.github.io/Browser-Ponies/browserponies.js");
    	document.body.appendChild(b);
    	setTimeout(function(){
    		(function (cfg) {
				BrowserPonies.setBaseUrl(cfg.baseurl);
				BrowserPonies.loadConfig(BrowserPoniesBaseConfig);
				BrowserPonies.loadConfig(cfg);
			})({"baseurl":"https://panzi.github.io/Browser-Ponies/","fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"spawn":{"applejack":1,"fluttershy":1,"pinkie pie":1,"rainbow dash":1,"rarity":1,"twilight sparkle":1},"autostart":true}); 
    	},500);
    }
  }
}).keyup(function(e) {
  if (e.keyCode in harlemShake) {
    harlemShake[e.keyCode] = false;
  }
  if (e.keyCode in ripple) {
    ripple[e.keyCode] = false;
  }
  if (e.keyCode in asteroid) {
    asteroid[e.keyCode] = false;
  }
  if (e.keyCode in katamari) {
    katamari[e.keyCode] = false;
  }
  if (e.keyCode in bomb) {
    bomb[e.keyCode] = false;
  }
  if (e.keyCode in pony) {
    pony[e.keyCode] = false;
  }
  if (e.keyCode in myLittlePony) {
    myLittlePony[e.keyCode] = false;
  }
});