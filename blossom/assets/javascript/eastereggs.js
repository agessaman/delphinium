/*
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

var eggs = ['harlem_shake','ripples','asteroids','katamari','bombs','ponies','my_little_pony','snow'];
var eggsIcons = ['bolt','bullseye','rocket','fa fa-soccer-ball-o','fa fa-bomb','linux','github-alt','gears'];
var comands = ['Press "h" and "a" at the same time, make sure your sound is on', 'Press "r" and "i" at the same time and move the mouse', 'Press "a" and "s" at the same time, space to shoot, arrow keys to move', 'Press "k" and "a" at the same time, follow instructions', 'Press "b" and "o" at the same time, click mouse in text to drop them', 'Press "p" and "o" at the same time, space to re-spawn, arrow keys to move', 'Press "m" and "y" at the same time, watch and enjoy','Press "s" and "n" at the same time, watch and enjoy, follows mouse, break lights', 'Press "r" and "p" at the same time, make sure your sound is on'];
var keys = {};
var count = 65;
var str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
for(var i=0; i<str.length; i++){
  var nextChar = str.charAt(i);
  keys[nextChar] = count;
  count++;
}

var harlemShake = {}, ripple = {}, asteroid = {}, katamari = {}, bomb = {}, pony = {}, myLittlePony = {}, snow = {};
harlemShake[keys.H] = false;
harlemShake[keys.A] = false;
harlemShake['loaded'] = false;
ripple[keys.R] = false;
ripple[keys.I] = false;
ripple['loaded'] = false;
asteroid[keys.A] = false;
asteroid[keys.S] = false;
asteroid['loaded'] = false;
katamari[keys.K] = false;
katamari[keys.A] = false;
katamari['loaded'] = false;
bomb[keys.B] = false;
bomb[keys.O] = false;
bomb['loaded'] = false;
pony[keys.P] = false;
pony[keys.O] = false;
pony['loaded'] = false;
myLittlePony[keys.M] = false;
myLittlePony[keys.Y] = false;
myLittlePony['loaded'] = false;
snow[keys.S] = false;
snow[keys.N] = false;
snow['loaded'] = false;

$(document).keydown(function(e) {
	//Harlem Shake
  if(current_grade >= config.harlem_shake){
    if (e.keyCode in harlemShake) {
      harlemShake[e.keyCode] = true;
      if (harlemShake[keys.H] && harlemShake[keys.A]) {
        if(!harlemShake["loaded"]){
          harlemShake["loaded"] = true;
          var s = document.createElement('script');
          s.setAttribute('src', path + 'plugins/delphinium/blossom/assets/javascript/harlem-shake.js');
          document.body.appendChild(s);
        }else{
          for (var L = 0; L < C.length; L++) {
            var A = C[L];
            if (v(A)) {
              if (E(A)) {
                k = A;
                break
              }
            }
          }
          if (A === null) {
            console.warn("Could not find a node of the right size. Please try a different page.");
          }
          c();
          S();
          var O = [];
          for (var L = 0; L < C.length; L++) {
            var A = C[L];
            if (v(A)) {
              O.push(A)
            }
          }
        }
      }
    }
  }

  //Page Ripple
  if(current_grade >= config.ripples){
    if (e.keyCode in ripple) {
      ripple[e.keyCode] = true;
      if (ripple[keys.R] && ripple[keys.I]) {
        if(!ripple["loaded"]){
          ripple["loaded"] = true;
          var s = document.createElement('script');
          s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/jquery.ripples.js");
          document.body.appendChild(s);
          $('body').css('backgroundImage', 'url(' + path + 'plugins/delphinium/blossom/assets/images/pebbles.png)');
        } else {
          setInterval(function() {
            var $el = $('body');
            var x = Math.random() * $el.outerWidth();
            var y = Math.random() * $el.outerHeight();
            var dropRadius = 20;
            var strength = 0.04 + Math.random() * 0.04;

            $el.ripples('drop', x, y, dropRadius, strength);
          }, 2000);
        }        
      }
    }
  }

  //Asteroids 
  if(current_grade >= config.asteroids){
    if (e.keyCode in asteroid) {
      asteroid[e.keyCode] = true;
      if (asteroid[keys.A] && asteroid[keys.S]) {
        if(!asteroid["loaded"]){
          asteroid["loaded"] = true;
          var s = document.createElement('script');
          s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/kickass.js");
          document.body.appendChild(s);
        }
      }
    }
  }

  //Katamari 
  if(current_grade >= config.katamari){
    if (e.keyCode in katamari) {
      katamari[e.keyCode] = true;
      if (katamari[keys.K] && katamari[keys.A]) {
        if(!katamari["loaded"]){
          katamari["loaded"] = true;
        	var s = document.createElement('script');
    			s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/kh.js");
    			document.body.appendChild(s);
        }
      }
    }
  }

  //Bombs 
  if(current_grade >= config.bombs){
    if (e.keyCode in bomb) {
      bomb[e.keyCode] = true;
      if (bomb[keys.B] && bomb[keys.O]) {
        if(!bomb["loaded"]){
          bomb["loaded"] = true;
        	window.FONTBOMB_HIDE_CONFIRMATION = true;
        	var s = document.createElement('script');
    			s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/bomb.js");
    			document.body.appendChild(s);
        }
      }
    }
  }

  //Ponies 
  if(current_grade >= config.ponies){
    if (e.keyCode in pony) {
      pony[e.keyCode] = true;
      if (pony[keys.P] && pony[keys.O]) {
        if(!pony["loaded"]){
          pony["loaded"] = true;
        	var s = document.createElement('script');
    			s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/loader.js");
    			document.body.appendChild(s);
        }
      }
    }
  }

  //MyLittlePony
  if(current_grade >= config.my_little_pony){
    if (e.keyCode in myLittlePony) {
      myLittlePony[e.keyCode] = true;
      if (myLittlePony[keys.M] && myLittlePony[keys.Y]) {
        if(!myLittlePony["loaded"]){
          myLittlePony["loaded"] = true;
        	var b = document.createElement('script');
        	b.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/browserponies.js");
        	document.body.appendChild(b);
        }
      }
    }
  }

  //Snow
  if(current_grade >= config.snow){
    if (e.keyCode in snow) {
      snow[e.keyCode] = true;
      if (snow[keys.S] && snow[keys.N]) {
        if(!snow["loaded"]){
          snow["loaded"] = true;
          var s = document.createElement('script');
          s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/snowstorm.js");
          document.body.appendChild(s);
          var b = document.createElement('script');
          b.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/soundmanager2-nodebug-jsmin.js");
          document.body.appendChild(b);
          var a = document.createElement('script');
          a.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/animation-min.js");
          document.body.appendChild(a);
          var c = document.createElement('script');
          c.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/christmaslights.js");
          document.body.appendChild(c);
          var f = document.createElement('script');
          f.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/soundmanager2-nodebug-jsmin.js");
          document.body.appendChild(f);
          var d =document.createElement("link");
          d.setAttribute("rel", "stylesheet");
          d.setAttribute("type", "text/css");
          d.setAttribute("href", path + "/plugins/delphinium/blossom/assets/css/snow.css");
          document.body.appendChild(d);
          var e =document.createElement("link");
          e.setAttribute("rel", "stylesheet");
          e.setAttribute("type", "text/css");
          e.setAttribute("href", path + "/plugins/delphinium/blossom/assets/css/christmaslights.css");
          document.body.appendChild(e);
        }
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
  if (e.keyCode in snow) {
    snow[e.keyCode] = false;
  }
});