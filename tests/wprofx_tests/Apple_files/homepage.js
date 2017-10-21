(function(){if(s&&Event&&AC&&typeof AC.onDOMReady==="function"&&AC.Tracking&&typeof s==="object"&&typeof AC.Tracking.pageName==="function"&&typeof AC.Tracking.trackClick==="function"){var c=false;
var d=1;if(Event.Listener&&typeof Event.Listener.listenForEvent==="function"&&typeof AC.ViewMaster==="object"&&typeof AC.Element.addClassName==="function"&&typeof AC.Element.removeClassName==="function"){AC.onDOMReady(function(){var a=true;
Event.Listener.listenForEvent(AC.ViewMaster,"ViewMasterWillShowNotification",false,function(f){var j=f.event_data.data.sender;
var e=(j.__slideshow&&j.__slideshow._active);if(a===true||a==="sneaky"){if(a===true||!e){j.sections.each(function(i){i=i[1];
if(i.content){if(e){AC.Element.addClassName(i.content,"sneaky")}else{AC.Element.removeClassName(i.content,"sneaky")
}}});a="sneaky"}if(!e){a=false}}if(j._currentTrigger===undefined&&!e){j._currentTrigger="swipe"
}});Event.Listener.listenForEvent(AC.ViewMaster,"ViewMasterDidShowNotification",false,function(f){var j=f.event_data.data.sender;
var e=(j.__slideshow&&j.__slideshow._active);if(c===true){if(j&&j._currentTrigger===undefined){if(e){AC.Tracking.trackClick({prop3:"ai@"+j.currentSection.id+" - "+AC.Tracking.pageName()},this,"o","ai@"+j.currentSection.id+" - "+AC.Tracking.pageName())
}}}d+=1;j._currentTrigger=undefined});if(c===true){var b=AC.AutoGallery.galleries;
var g,h;for(g in b){if(b.hasOwnProperty(g)){h=b[g];if(h.__slideshow){AC.Tracking.trackClick({prop3:"ai@"+h.currentSection.id+" - "+AC.Tracking.pageName()},this,"o","ai@"+h.currentSection.id+" - "+AC.Tracking.pageName())
}}}}if(window.tracker){window.tracker.setDelegate({sectionDidChange:function(n,q,r,e,o){var f=e+" - "+AC.Tracking.pageName();
var p=q._currentTrigger;if(p&&typeof Element.up==="function"){if(p==="arrow_right"||p==="arrow_left"){o.pageName="ki@"+f;
return o}if(p==="swipe"){o.pageName="si@"+f;return o}if(Element.up(p,".dot")){o.pageName="bi@"+f;
return o}if(Element.up(link,".thumb")){o.pageName="gi@"+f;return o}if(Element.up(link,".paddle-nav")){o.pageName="pi@"+f;
return o}}return o}})}})}if(AC.Element&&typeof AC.Element.selectAll==="function"&&typeof AC.Element.select==="function"&&typeof AC.Element.hasClassName==="function"&&typeof AC.Element.addEventListener==="function"){AC.onDOMReady(function(){var a=function(l){var m=(l.innerText)?l.innerText.trim():l.textContent.trim();
var n=AC.Element.select("img",l);var k=l.href.replace(new RegExp("^"+window.location.protocol+"//"),"").replace(new RegExp("^"+window.location.host+"/"),"").replace(/\/$/,"");
var j;if(typeof Element.up==="function"){j=Element.up(l,'[id*="MASKED-"]');if(j){j=j.id.replace("MASKED-","")
}if(AC.Element.hasClassName(l,"learn")){return m+" - "+j}if(Element.up(l,"#hero")){return j
}}if(m!==""){return k}if(n){n=n.getAttribute("src");if(n){return n.substring(n.lastIndexOf("/")+1,n.length)
}}return k};var b=function(o){var m=o.target;var k=false;var p=false;var l;var n;
while(m&&m.parentNode&&m.tagName&&m.tagName.toLowerCase()!=="a"){m=m.parentNode
}if(!m||!m.href){return}k=a(m);if(k&&k!==""&&typeof Element.up==="function"){if(Element.up(m,".dot")||Element.up(m,".thumb")||Element.up(m,".paddle-nav")){return
}l="l@";n={prop1:d};if(Element.up(m,"#globalheader")){l="t@";p="tab"}if(Element.up(m,"#billboard")){l="h@";
p="hero"}if(Element.up(m,".promos")){l="p@";p="promos"}n.prop3=(l+k+" - "+AC.Tracking.pageName()).toLowerCase();
AC.Tracking.trackClick(n,m,"o",n.prop3)}if(p&&AC.Storage&&typeof AC.Storage.setItem==="function"){AC.Storage.setItem("s_nav",p,"0")
}};AC.Element.addEventListener(document.body,"mouseup",b)})}}}());