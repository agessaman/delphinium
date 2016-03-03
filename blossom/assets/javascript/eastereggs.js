var A_KEY     = 65;
var B_KEY     = 66;
var C_KEY     = 67;
var D_KEY     = 68;
var E_KEY     = 69;
var F_KEY     = 70;
var G_KEY     = 71;
var H_KEY     = 72;
var I_KEY     = 73;
var J_KEY     = 74;
var K_KEY     = 75;
var L_KEY     = 76;
var M_KEY     = 77;
var N_KEY     = 78;
var O_KEY     = 79;
var P_KEY     = 80;
var Q_KEY     = 81;
var R_KEY     = 82;
var S_KEY     = 83;
var T_KEY     = 84;
var U_KEY     = 85;
var V_KEY     = 86;
var W_KEY     = 87;
var X_KEY     = 88;
var Y_KEY     = 89;
var Z_KEY     = 90;

$(document).keydown(function(e) {
		if(e.keyCode == R_KEY){
			$('body').ripples({
				resolution: 512,
				dropRadius: 20, //px
				perturbance: 0.04,
			});
		}else if(e.keyCode == A_KEY){
			var s = document.createElement('script');
			s.type='text/javascript';document.body.appendChild(s);
			s.src='http://erkie.github.com/asteroids.min.js';
			void(0);
		} else if(e.keyCode == K_KEY){
			var i,s,ss=['http://kathack.com/js/kh.js','http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js'];
			for(i=0;i!=ss.length;i++){
				s=document.createElement('script');
				s.src=ss[i];
				document.body.appendChild(s);
			}
			void(0);
		} else if(e.keyCode == B_KEY){
			window.FONTBOMB_HIDE_CONFIRMATION = true;
			(function () {
				try{
					var s = document.createElement('script');
					s.setAttribute('src', 'http://fontbomb.ilex.ca/js/main.js');
					document.body.appendChild(s);
				}catch(err){
					alert("Your browser is not compatible, watch the video or try with Chrome.")
				}
			}());
		} else if(e.keyCode == P_KEY){
			(function(s){
				s=document.createElement('script');
				s.src='http://websplat.bitbucket.org/websplat/loader.js';
				document.getElementsByTagName('head')[0].appendChild(s);
			})()
		} else if(e.keyCode == M_KEY){
			(function (cfg) {
				BrowserPonies.setBaseUrl(cfg.baseurl);
				BrowserPonies.loadConfig(BrowserPoniesBaseConfig);
				BrowserPonies.loadConfig(cfg);
			})({"baseurl":"https://panzi.github.io/Browser-Ponies/","fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"spawn":{"applejack":1,"fluttershy":1,"pinkie pie":1,"rainbow dash":1,"rarity":1,"twilight sparkle":1},"autostart":true}); 
		} else {

		}
});

