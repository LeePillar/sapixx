/* Do version 2.0 pre
 * creator: kejun (listenpro@gmail.com)
 * 最新更新：2011-7-12
 */(function(a,b){var c={},d={},e={},f={autoLoad:!0,timeout:6e3,coreLib:["https://cdn.bootcdn.net/ajax/libs/jquery/2.2.4/jquery.min.js"],mods:{}},g=function(){var a=b.getElementsByTagName("script");return a[a.length-1]}(),h=[],i,j=[],k=!1,l={},m={},n=function(a){return a.constructor===Array},o=function(a){var b=f.mods,c;typeof a=="string"?c=b[a]?b[a]:{path:a}:c=a;return c},p=function(d,h,i,j){var k,l,m,n,o=function(){c[d]=1,j&&j(d),j=null,a.clearTimeout(k)};if(!!d){if(c[d]){e[d]=!1,j&&j(d);return}if(e[d]){setTimeout(function(){p(d,h,i,j)},10);return}e[d]=!0,k=a.setTimeout(function(){if(f.timeoutCallback)try{f.timeoutCallback(d)}catch(a){}},f.timeout),m=h||d.toLowerCase().substring(d.lastIndexOf(".")+1),m==="js"?(l=b.createElement("script"),l.setAttribute("type","text/javascript"),l.setAttribute("src",d),l.setAttribute("async",!0)):m==="css"&&(l=b.createElement("link"),l.setAttribute("type","text/css"),l.setAttribute("rel","stylesheet"),l.setAttribute("href",d)),i&&(l.charset=i),m==="css"?(n=new Image,n.onerror=function(){o(),n.onerror=null,n=null},n.src=d):(l.onerror=function(){o(),l.onerror=null},l.onload=l.onreadystatechange=function(){var a;if(!this.readyState||this.readyState==="loaded"||this.readyState==="complete")o(),l.onload=l.onreadystatechange=null}),g.parentNode.insertBefore(l,g)}},q=function(a,b){function k(){--j||(d[e]=1,b())}var c=f.mods,e,g,h,i=0,j;e=a.join(""),j=a.length;if(d[e])b();else for(;g=a[i++];)h=o(g),h.requires?q(h.requires,function(a){return function(){p(a.path,a.type,a.charset,k)}}(h)):p(h.path,h.type,h.charset,k)},r=function(b){var c=!1,d=!0,e=a.document,f=e.documentElement,g=e.addEventListener?"addEventListener":"attachEvent",h=e.addEventListener?"removeEventListener":"detachEvent",i=e.addEventListener?"":"on",j=function(d){if(d.type!="readystatechange"||e.readyState=="complete")(d.type=="load"?a:e)[h](i+d.type,j,!1),!c&&(c=!0)&&b.call(a,d.type||d)},k=function(){try{f.doScroll("left")}catch(a){setTimeout(k,50);return}j("poll")};if(e.readyState=="complete")b.call(a,"lazy");else{if(e.createEventObject&&f.doScroll){try{d=!a.frameElement}catch(l){}d&&k()}e[g](i+"DOMContentLoaded",j,!1),e[g](i+"readystatechange",j,!1),a[g](i+"load",j,!1)}},s=function(){var a=0,b;if(j.length)for(;b=j[a++];)t.apply(this,b)},t=function(){var a=[].slice.call(arguments),b,c;if(f.autoLoad&&!d[f.coreLib.join("")])q(f.coreLib,function(){t.apply(null,a)});else{if(h.length>0&&!d[h.join("")]){q(h,function(){t.apply(null,a)});return}typeof a[a.length-1]=="function"&&(b=a.pop()),c=a.join("");if((a.length===0||d[c])&&b){b();return}q(a,function(){d[c]=1,b&&b()})}};t.add=function(a,b){!a||!b||!b.path||(f.mods[a]=b)},t.delay=function(){var b=[].slice.call(arguments),c=b.shift();a.setTimeout(function(){t.apply(this,b)},c)},t.global=function(){var a=n(arguments[0])?arguments[0]:[].slice.call(arguments);h=h.concat(a)},t.ready=function(){var a=[].slice.call(arguments);if(k)return t.apply(this,a);j.push(a)},t.css=function(a){var c=b.getElementById("do-inline-css");c||(c=b.createElement("style"),c.type="text/css",c.id="do-inline-css",g.parentNode.insertBefore(c,g)),c.styleSheet?c.styleSheet.cssText=c.styleSheet.cssText+a:c.appendChild(b.createTextNode(a))},t.setData=t.setPublicData=function(a,b){var c=m[a];l[a]=b;if(!!c)while(c.length>0)c.pop().call(this,b)},t.getData=t.getPublicData=function(a,b){l[a]?b(l[a]):(m[a]||(m[a]=[]),m[a].push(function(a){b(a)}))},t.setConfig=function(a,b){f[a]=b;return t},t.getConfig=function(a){return f[a]},a.Do=t,r(function(){k=!0,s()}),i=g.getAttribute("data-cfg-autoload"),i&&(f.autoLoad=i.toLowerCase()==="true"?!0:!1),i=g.getAttribute("data-cfg-corelib"),i&&(f.coreLib=i.split(","))})(window,document)