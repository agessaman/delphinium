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
harlemShake[keys.A] = false;
ripple[keys.R] = false;
ripple[keys.I] = false;
asteroid[keys.A] = false;
asteroid[keys.S] = false;
katamari[keys.K] = false;
katamari[keys.A] = false;
bomb[keys.B] = false;
bomb[keys.O] = false;
pony[keys.P] = false;
pony[keys.O] = false;
myLittlePony[keys.M] = false;
myLittlePony[keys.Y] = false;

$(document).keydown(function(e) {
	//Harlem Shake
  if(current_grade >= config.harlem_shake){
    if (e.keyCode in harlemShake) {
      harlemShake[e.keyCode] = true;
      if (harlemShake[keys.H] && harlemShake[keys.A]) {
      	var s = document.createElement('script');
  			s.setAttribute('src', path + 'plugins/delphinium/blossom/assets/javascript/harlem-shake.js');
  			document.body.appendChild(s);
      }
    }
  }
  //Page Ripple
  if(current_grade >= config.ripples){
    if (e.keyCode in ripple) {
      ripple[e.keyCode] = true;
      if (ripple[keys.R] && ripple[keys.I]) {
      	var s = document.createElement('script');
  			s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/jquery.ripples.js");
  			document.body.appendChild(s);
        $('body').css('backgroundImage', 'url(/delphinium/plugins/delphinium/blossom/assets/images/pebbles.png)');
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
  }
  //Asteroids 
  if(current_grade >= config.asteroids){
    if (e.keyCode in asteroid) {
      asteroid[e.keyCode] = true;
      if (asteroid[keys.A] && asteroid[keys.S]) {
      	var s = document.createElement('script');
  			s.setAttribute('src', "https://hi.kickassapp.com/kickass.js");
  			document.body.appendChild(s);
      }
    }
  }
  //Katamari 
  if(current_grade >= config.katamari){
    if (e.keyCode in katamari) {
      katamari[e.keyCode] = true;
      if (katamari[keys.K] && katamari[keys.A]) {
      	var s = document.createElement('script');
  			s.setAttribute('src', document.location.protocol + "//kathack.com/js/kh.js");
  			document.body.appendChild(s);
      }
    }
  }
  //Bombs 
  if(current_grade >= config.bombs){
    if (e.keyCode in bomb) {
      bomb[e.keyCode] = true;
      if (bomb[keys.B] && bomb[keys.O]) {
      	window.FONTBOMB_HIDE_CONFIRMATION = true;
      	var s = document.createElement('script');
  			s.setAttribute('src', document.location.protocol + "//fontbomb.ilex.ca/js/main.js");
  			document.body.appendChild(s);
      }
    }
  }
  //Ponies 
  if(current_grade >= config.ponies){
    if (e.keyCode in pony) {
      pony[e.keyCode] = true;
      if (pony[keys.P] && pony[keys.O]) {
      	var s = document.createElement('script');
  			s.setAttribute('src', document.location.protocol + "//websplat.bitbucket.org/websplat/loader.js");
  			document.body.appendChild(s);
      }
    }
  }
  //MyLittlePony
  if(current_grade >= config.my_little_pony){
    if (e.keyCode in myLittlePony) {
      myLittlePony[e.keyCode] = true;
      if (myLittlePony[keys.M] && myLittlePony[keys.Y]) {
      	var s = document.createElement('script');
      	s.setAttribute('src', document.location.protocol + "//panzi.github.io/Browser-Ponies/basecfg.js");
      	document.body.appendChild(s);
      	var b = document.createElement('script');
      	b.setAttribute('src', document.location.protocol + "//panzi.github.io/Browser-Ponies/browserponies.js");
      	document.body.appendChild(b);
      	setTimeout(function(){
      		(function (cfg) {
  				BrowserPonies.setBaseUrl(cfg.baseurl);
  				BrowserPonies.loadConfig(BrowserPoniesBaseConfig);
  				BrowserPonies.loadConfig(cfg);
  			})({"baseurl":document.location.protocol + "//panzi.github.io/Browser-Ponies/","fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"spawn":{"applejack":1,"fluttershy":1,"pinkie pie":1,"rainbow dash":1,"rarity":1,"twilight sparkle":1},"autostart":true}); 
      	},500);
      }
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