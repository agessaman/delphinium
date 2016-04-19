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
 */var eggs = ['harlem_shake','ripples','asteroids','katamari','bombs','ponies','my_little_pony'];
var eggsIcons = ['bolt','bullseye','rocket','soccer-ball-o','bomb','linux','github-alt'];
var comands = ['Press "h" and "a" at the same time', 'Press "r" and "i" at the same time and move the mouse', 'Press "a" and "s" at the same time, space to shoot, arrow keys to move', 'Press "k" and "a" at the same time(I cant remember how to make this one move)', 'Press "b" and "o" at the same time, click mouse to drop them', 'Press "p" and "o" at the same time(I cant remember the controls)', 'Press "m" and "y" at the same time(I cant remember the controls)'];
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

$(document).keydown(function(e) {
    //Harlem Shake
    //console.log(current_grade);
    if(!harlemShake["loaded"]){
        if(current_grade >= config.harlem_shake){
            if (e.keyCode in harlemShake) {
                harlemShake[e.keyCode] = true;
                if (harlemShake[keys.H] && harlemShake[keys.A]) {
                    harlemShake["loaded"] = true;
                    var s = document.createElement('script');
                    s.setAttribute('src', path + 'plugins/delphinium/blossom/assets/javascript/harlem-shake.js');
                    document.body.appendChild(s);
                }
            }
        }
    }
    //Page Ripple
    if(!ripple["loaded"]){
        if(current_grade >= config.ripples){
            if (e.keyCode in ripple) {
                ripple[e.keyCode] = true;
                if (ripple[keys.R] && ripple[keys.I]) {
                    ripple["loaded"] = true;
                    var s = document.createElement('script');
                    s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/jquery.ripples.js");
                    document.body.appendChild(s);
                    console.log(path);
                    $('body').css('backgroundImage', 'url(' + path + 'plugins/delphinium/blossom/assets/images/pebbles.png)');
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
    if(!asteroid["loaded"]){
        if(current_grade >= config.asteroids){
            if (e.keyCode in asteroid) {
                asteroid[e.keyCode] = true;
                if (asteroid[keys.A] && asteroid[keys.S]) {
                    asteroid["loaded"] = true;
                    var s = document.createElement('script');
                    s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/kickass.js");
                    document.body.appendChild(s);
                }
            }
        }
    }
    //Katamari
    if(!katamari["loaded"]){
        if(current_grade >= config.katamari){
            if (e.keyCode in katamari) {
                katamari[e.keyCode] = true;
                if (katamari[keys.K] && katamari[keys.A]) {
                    katamari["loaded"] = true;
                    var s = document.createElement('script');
                    s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/kh.js");
                    document.body.appendChild(s);
                }
            }
        }
    }
    //Bombs
    if(!bomb["loaded"]){
        if(current_grade >= config.bombs){
            if (e.keyCode in bomb) {
                bomb[e.keyCode] = true;
                if (bomb[keys.B] && bomb[keys.O]) {
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
    if(!pony["loaded"]){
        if(current_grade >= config.ponies){
            if (e.keyCode in pony) {
                pony[e.keyCode] = true;
                if (pony[keys.P] && pony[keys.O]) {
                    pony["loaded"] = true;
                    var s = document.createElement('script');
                    s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/loader.js");
                    document.body.appendChild(s);
                }
            }
        }
    }
    //MyLittlePony
    if(!myLittlePony["loaded"]){
        if(current_grade >= config.my_little_pony){
            if (e.keyCode in myLittlePony) {
                myLittlePony[e.keyCode] = true;
                if (myLittlePony[keys.M] && myLittlePony[keys.Y]) {
                    myLittlePony["loaded"] = true;
                    var s = document.createElement('script');
                    s.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/basecfg.js");
                    document.body.appendChild(s);
                    var b = document.createElement('script');
                    b.setAttribute('src', path + "plugins/delphinium/blossom/assets/javascript/browserponies.js");
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