!function(){function consoleDebug(message){TargetMediator.debug===!0&&console.log("TargetMediator: "+message)}var Util={generateRandomId:function(){return(new Date).getTime()+"-"+Math.floor(999999*Math.random())},getQueryStringParameterByName:function(name){name=name.replace(/[\[]/,"\\[").replace(/[\]]/,"\\]");var re=new RegExp("[\\?&]"+name+"=([^&#]*)"),res=re.exec(window.location.search);return null===res?"":decodeURIComponent(res[1].replace(/\+/g," "))},hasCORSSupport:function(){return"withCredentials"in new XMLHttpRequest}},TargetError=function(){function TargetError(message){var err=new Error(message);err.name="TargetError",this.message=err.message,err.stack&&(this.stack=err.stack)}var err=new Error;return err.name="TargetError",TargetError.prototype=err,TargetError}(),Logger=function(){function Logger(){return Logger.prototype._singletonInstance?Logger.prototype._singletonInstance:(Logger.prototype._singletonInstance=this,void 0)}var logs=[];return Logger.prototype={log:function(obj){switch(obj.type){case"TargetRequesterXHRSuccess":break;case"TargetRequesterXHRError":break;case"TargetRequesterTimeoutError":break;case"TargetRequesterParameterError":break;case"InvalidOfferError":break;case"UnassociatedOfferError":break;case"UnparsableOfferError":break;case"DuplicateOfferError":break;case"AWSRequesterXHRSuccess":break;case"AWSRequesterXHRError":break;case"AWSRequesterTimeoutError":break;case"OfferInjectionError":break;default:throw new TargetError("LoggerError: Unknown log type: "+obj.type)}var currentTime=null;currentTime="currentTime"in obj?obj.currentTime:(new Date).getTime(),obj.namespace="TargetMediator",obj.time=currentTime,obj.elapsedPageTime=this.getElapsedTimeSinceScriptLoad(currentTime),obj.isFirstTimeVisitor=+TargetMediator.isFirstTimeVisitor,obj.isLiveUrl=+this.isLiveUrl(),obj.page=document.location.toString(),obj.requestTime="requestStartTime"in obj?currentTime-obj.requestStartTime:"X",obj.isClickRequest="isClickRequest"in obj?+obj.isClickRequest:0,logs.push(obj);var json=JSON.stringify(obj);if(consoleDebug(json),obj.page=obj.page.replace(/^https?:\/\/.*amazon\.com/,""),obj.page=obj.page.replace(/(\?|\#).*$/,""),""===obj.page&&(obj.page="/"),obj.type.match(/^AWSRequester/)){"/"===obj.data.substr(obj.data.length-1,1)&&(obj.data=obj.data.substr(0,obj.data.length-1));var lastSlash=obj.data.lastIndexOf("/");-1!==lastSlash&&(obj.data=obj.data.substring(lastSlash+1))}var logExternally=!0;obj.isLiveUrl||(logExternally=!1);var hour=(new Date).getHours();0===hour%2&&(logExternally=!1);var arrReportable=[obj.type,obj.elapsedPageTime,obj.requestTime,obj.isFirstTimeVisitor,obj.isLiveUrl,obj.isClickRequest,obj.page,obj.data],reportable=arrReportable.join("|"),startTime=(new Date).getTime(),maxTimeout=1e4,pollToReport=function(){try{if("object"==typeof aws_sc&&aws_sc.eventReport)return logExternally?aws_sc.eventReport(reportable):consoleDebug("External logging is disabled for this time or environment."),void 0}catch(ex){consoleDebug("UnreportableError: Logging failed: "+ex.message)}return(new Date).getTime()-startTime>maxTimeout?(consoleDebug("Failed to send log: "+reportable),void 0):(setTimeout(function(){pollToReport()},500),void 0)};pollToReport()},getLastLog:function(){return logs[logs.length-1]},getElapsedTimeSinceScriptLoad:function(currentTime){return currentTime=currentTime||(new Date).getTime(),currentTime-TargetMediator.scriptLoadTime},isLiveUrl:function(){var re=/^https?:\/\/aws\.amazon\.com/g,res=re.exec(document.location.toString());return null===res?!1:!0}},Logger}(),TargetRequestDataBuilder=function(){function TargetRequestDataBuilder(mboxParams,pageParams){this.mboxParams=mboxParams,this.pageParams=pageParams,this.postParams=null,this.clientCode="amazonwebservicesinc",this.url="http://amazonwebservicesinc.tt.omtrdc.net/m2/"+this.clientCode+"/ubox/raw",this.url=this.replaceUrlProtocolWithCurrent(this.url)}return TargetRequestDataBuilder.prototype={build:function(){this.postParams={};for(var key in this.pageParams)this.pageParams.hasOwnProperty(key)&&(this.postParams[key]=this.pageParams[key]);for(key in this.mboxParams)this.mboxParams.hasOwnProperty(key)&&(this.postParams[key]=this.mboxParams[key]);this.url=this.url+"?"+this.makeQueryString({mboxPC:this.postParams.mboxPC,mboxSession:this.postParams.mboxSession})},makeQueryString:function(params){var parts=[];for(var key in params)params.hasOwnProperty(key)&&parts.push(encodeURIComponent(key)+"="+encodeURIComponent(params[key]));return parts.join("&")},replaceUrlProtocolWithCurrent:function(url){var re=/^https?/g;return url.replace(re,document.location.toString().match(re)[0])}},TargetRequestDataBuilder}(),CookieManager=function(){function CookieManager(name){this.name=name,this.exists=!1,this.rawValue=null,this.value=null,this.expireSeconds=86400,document.cookie.length&&-1!==document.cookie.indexOf(this.name)&&(this.exists=!0)}return CookieManager.prototype={read:function(){var found=!1;if(document.cookie.length){var cookieStart=document.cookie.indexOf(this.name);if(-1!==cookieStart){found=!0;var valueStart=cookieStart+this.name.length+1,cookieEnd=document.cookie.indexOf(";",valueStart);-1===cookieEnd&&(cookieEnd=document.cookie.length),this.rawValue=document.cookie.substring(valueStart,cookieEnd)}}found||(this.exists=!1)},write:function(){var options={};options.value=this.name+"="+this.rawValue;var expiresDate=new Date;expiresDate.setSeconds(expiresDate.getSeconds()+this.expireSeconds),options.expires="; expires="+expiresDate.toUTCString(),options.path="; path=/";var hostname=document.location.hostname,periodCount=hostname.split(".").length-1;if(periodCount>2){for(var lastIndex=0,unneededPeriods=periodCount-2,i=0,l=unneededPeriods;l>i;i++)lastIndex=hostname.indexOf(".",lastIndex+1);hostname=hostname.substring(lastIndex+1)}hostname="localhost"===hostname?"":hostname,options.domain="; domain="+hostname,document.cookie=[options.value,options.expires,options.path,options.domain].join(""),this.exists=!0},encode:function(){this.rawValue=encodeURIComponent(this.value)},decode:function(){try{this.value=decodeURIComponent(this.rawValue)}catch(ex){throw new TargetError("CookieError: Failed to decode cookie "+this.name)}}},CookieManager}(),ParameterFetcher=function(){function ParameterFetcher(){this.mboxPageId=null,this.mboxXDomain=null,this.mboxNoRedirect=null,this.mboxHost=null,this.screenWidth=null,this.screenHeight=null,this.screenColorDepth=null,this.browserWidth=null,this.browserHeight=null,this.browserTimeOffset=null,this.currentUrl=null,this.referringUrl=null,this.registrationCookie=new CookieManager("regStatus"),this.awsXMainCookie=new CookieManager("aws-x-main"),this.sessionCookie=new CookieManager("aws-target-session-id"),this.visitorCookie=new CookieManager("aws-target-visitor-id")}return ParameterFetcher.prototype={init:function(){this.mboxPageId=this.generateMboxPageId(),this.mboxXDomain=this.getMboxXDomain(),this.mboxNoRedirect=this.getMboxNoRedirect(),this.mboxHost=this.getMboxHost(),this.screenWidth=this.getScreenWidth(),this.screenHeight=this.getScreenHeight(),this.screenColorDepth=this.getScreenColorDepth(),this.browserWidth=this.getBrowserWidth(),this.browserHeight=this.getBrowserHeight(),this.browserTimeOffset=this.getBrowserTimeOffset(),this.currentUrl=this.getCurrentUrl(),this.referringUrl=this.getReferringUrl(),this.registrationStatus=this.getRegistrationStatus(),this.hasAWSXMain=this.hasAWSXMainCookie(),this.sessionCookie.read(),this.getSessionId(),this.visitorCookie.read(),this.getVisitorId()},allParams:function(){return{mboxPC:this.mboxPCId,mboxSession:this.mboxSessionId,mboxPage:this.mboxPageId,mboxXDomain:this.mboxXDomain,mboxNoRedirect:this.mboxNoRedirect,mboxHost:this.mboxHost,screenWidth:this.screenWidth,screenHeight:this.screenHeight,colorDepth:this.screenColorDepth,browserWidth:this.browserWidth,browserHeight:this.browserHeight,browserTimeOffset:this.browserTimeOffset,mboxUrl:this.currentUrl,mboxReferrer:this.referringUrl,registrationStatus:this.registrationStatus,hasAWSXMain:this.hasAWSXMain}},generateMboxPCId:function(){return Util.generateRandomId()},generateMboxSessionId:function(){return Util.generateRandomId()},generateMboxPageId:function(){return Util.generateRandomId()},getMboxXDomain:function(){return"disabled"},getMboxNoRedirect:function(){return 1},getScreenWidth:function(){return screen.width},getScreenHeight:function(){return screen.height},getScreenColorDepth:function(){return screen.pixelDepth},getBrowserWidth:function(){return window.innerWidth?window.innerWidth:document.documentElement?document.documentElement.clientWidth:document.body.clientWidth},getBrowserHeight:function(){return window.innerHeight?window.innerHeight:document.documentElement?document.documentElement.clientHeight:document.body.clientHeight},getBrowserTimeOffset:function(){return-(new Date).getTimezoneOffset()},getCurrentUrl:function(){return document.location.toString()},getReferringUrl:function(){var referringUrl=document.referrer;return referringUrl.length<2e3?referringUrl:""},getMboxHost:function(){return document.location.hostname},getSessionId:function(){var isSessionCookieInvalid=!1;if(this.sessionCookie.exists)try{this.sessionCookie.decode(),this.mboxSessionId=this.sessionCookie.value}catch(ex){consoleDebug(ex.message),isSessionCookieInvalid=!0}else consoleDebug("Cookie "+this.sessionCookie.name+" does not exist yet so create it"),isSessionCookieInvalid=!0;isSessionCookieInvalid&&(this.mboxSessionId=this.generateMboxSessionId(),this.sessionCookie.value=this.mboxSessionId),this.sessionCookie.expireSeconds=1800,this.sessionCookie.encode(),this.sessionCookie.write()},getVisitorId:function(){var isVisitorCookieInvalid=!1;if(this.visitorCookie.exists)try{this.visitorCookie.decode(),this.mboxPCId=this.visitorCookie.value}catch(ex){consoleDebug(ex.message),isVisitorCookieInvalid=!0}else consoleDebug("Cookie "+this.visitorCookie.name+" does not exist yet so create it"),isVisitorCookieInvalid=!0;isVisitorCookieInvalid&&(this.mboxPCId=this.generateMboxPCId(),this.visitorCookie.value=this.mboxPCId,TargetMediator.isFirstTimeVisitor=!0),this.visitorCookie.expireSeconds=7257600,this.visitorCookie.encode(),this.visitorCookie.write()},getRegistrationStatus:function(){if(this.registrationCookie.exists){this.registrationCookie.read();var value=this.registrationCookie.rawValue;if("registered"===value||"registering"===value)return value}return"unregistered"},hasAWSXMainCookie:function(){return this.awsXMainCookie.exists?"true":"false"}},ParameterFetcher}(),TargetRequester=function(){function TargetRequester(callback,targetRequestDataBuilder,logger,isClickRequest){this.callback=callback,this.url=targetRequestDataBuilder.url,this.postData=targetRequestDataBuilder.postParams,this.logger=logger,this.isClickRequest=isClickRequest,this.rawData=null,this.data=null,this.state="pending",this.maxTimeout=6e3}var offerRegistry={};return TargetRequester.prototype={fetch:function(){var that=this;this.requestStartTime=(new Date).getTime();var pr=$.ajax({type:"POST",url:this.url,data:this.postData,crossDomain:!0,xhrFields:{withCredentials:!1}}).done(function(data,textStatus,jqXHR){that.done.call(that,data,textStatus,jqXHR)}).fail(function(data,textStatus,jqXHR){that.fail.call(that,data,textStatus,jqXHR)}),pollForResponse=function(){if("pending"===pr.state()){var currentTime=(new Date).getTime();if(currentTime-that.requestStartTime>=that.maxTimeout)return that.state="timeout",pr.abort(),void 0;var nextValue;return setTimeout(nextValue=function(){return pollForResponse()},100),nextValue}};pollForResponse()},done:function(data){this.rawData=data,consoleDebug("TargetRequester success for "+this.postData.mbox+" returned: "+this.rawData),this.logger.log({type:"TargetRequesterXHRSuccess",data:this.postData.mbox,requestStartTime:this.requestStartTime,isClickRequest:this.isClickRequest,currentTime:(new Date).getTime()}),this.validateResponseData(this.rawData),this.callback(this.response,this.state)},fail:function(){"timeout"===this.state?this.logger.log({type:"TargetRequesterTimeoutError",data:this.postData.mbox,requestStartTime:this.requestStartTime,isClickRequest:this.isClickRequest,currentTime:(new Date).getTime()}):this.logger.log({type:"TargetRequesterXHRError",data:this.postData.mbox,requestStartTime:this.requestStartTime,isClickRequest:this.isClickRequest,currentTime:(new Date).getTime()}),this.state="rejected",this.callback(this.rawData,this.state)},validateResponseData:function(raw){if(this.isClickRequest)this.state="resolved";else if(this.isUnassociatedOffer(raw))this.logger.log({type:"UnassociatedOfferError",data:this.postData.mbox,isClickRequest:this.isClickRequest}),this.state="rejected";else if(this.isValidDefaultOffer(raw))try{this.parseResponseData(raw),this.state="resolved"}catch(ex){this.logger.log({type:"UnparsableOfferError",data:this.postData.mbox,isClickRequest:this.isClickRequest}),this.state="rejected"}else if(this.isErrorOffer(raw))this.logger.log({type:"TargetRequesterParameterError",data:this.postData.mbox,isClickRequest:this.isClickRequest}),this.state="rejected";else if(this.isValidRegularOffer(raw))try{this.parseResponseData(raw),this.isDuplicateOffer()?(this.logger.log({type:"DuplicateOfferError",data:this.postData.mbox,isClickRequest:this.isClickRequest}),this.state="rejected"):(offerRegistry[this.response.url]=!0,this.state="resolved")}catch(ex){this.logger.log({type:"UnparsableOfferError",data:this.postData.mbox,isClickRequest:this.isClickRequest}),this.state="rejected"}else this.logger.log({type:"InvalidOfferError",data:this.postData.mbox,isClickRequest:this.isClickRequest}),this.state="rejected"},isUnassociatedOffer:function(raw){return"success"===raw},isDefaultOffer:function(raw){return"default offer"===raw},isErrorOffer:function(raw){var re=/^Mbox parameter \'(mbox|mboxTarget)\' not specified$/g,res=re.exec(raw);return null===res?!1:!0},isDuplicateOffer:function(){return this.response.url in offerRegistry},isValidDefaultOffer:function(raw){var re=/^\{"defaultOffer":true,"campaignId":"[0-9]{1,40}","environmentId":"[0-9]{1,40}","userPCId":"[0-9]{1,20}\-[0-9]{1,6}\.[0-9]{1,4}_[0-9]{1,4}"\}$/g,res=re.exec(raw);return null===res?!1:!0},isValidRegularOffer:function(raw){var re=/^\{"url":"https?:\/\/aws\.amazon\.com\/[a-zA-Z0-9\/_\.\-%\+\,\'\?\\#\$\&]*","campaignId":"[0-9]{1,40}","environmentId":"[0-9]{1,40}","userPCId":"[0-9]{1,20}\-[0-9]{1,6}\.[0-9]{1,4}_[0-9]{1,4}"\}$/g,res=re.exec(raw);return null===res?!1:!0},parseResponseData:function(raw){this.response=$.parseJSON(raw)}},TargetRequester}(),AWSRequester=function(){function AWSRequester(callback,logger){this.callback=callback,this.logger=logger,this.data=null,this.status="pending",this.maxTimeout=4e3}return AWSRequester.prototype={fetch:function(){var that=this;this.requestStartTime=(new Date).getTime();var pr=$.ajax({type:"GET",url:this.url}).done(function(data,textStatus,jqXHR){that.done.call(that,data,textStatus,jqXHR)}).fail(function(data,textStatus,jqXHR){that.fail.call(that,data,textStatus,jqXHR)}),pollForResponse=function(){if("pending"===pr.state()){var currentTime=(new Date).getTime();if(currentTime-that.requestStartTime>=that.maxTimeout)return that.state="timeout",pr.abort(),void 0;var nextValue;return setTimeout(nextValue=function(){return pollForResponse()},100),nextValue}};pollForResponse()},done:function(data){this.data=data,consoleDebug("AWSRequester success for "+this.url),this.logger.log({type:"AWSRequesterXHRSuccess",data:this.url,requestStartTime:this.requestStartTime,currentTime:(new Date).getTime()}),this.state="resolved",this.callback(this.data,this.state)},fail:function(){"timeout"===this.state?this.logger.log({type:"AWSRequesterTimeoutError",data:this.url,requestStartTime:this.requestStartTime,currentTime:(new Date).getTime()}):this.logger.log({type:"AWSRequesterXHRError",data:this.url,requestStartTime:this.requestStartTime,currentTime:(new Date).getTime()}),this.state="rejected",this.callback(this.data,this.state)},setUrl:function(url){url=document.location.toString().match(/^https?:\/\/aws\.amazon\.com/g)?this.replaceUrlProtocolWithCurrent(url):this.replaceUrlOriginWithCurrent(url),url=this.appendUrlFileSuffix(url),url=this.appendUrlQueryString(url),url=this.prependUrlPathname(url),this.url=url},replaceUrlOriginWithCurrent:function(url){var origin=window.location.protocol+"//"+window.location.host,re=/^https?:\/\/([a-z]*\.)?aws\.amazon\.com/g;return url.replace(re,origin)},replaceUrlProtocolWithCurrent:function(url){var re=/^https?/g;return url.replace(re,document.location.toString().match(re)[0])},appendUrlFileSuffix:function(url){return url.match(/\/$/g)?"/"===TargetMediator.offerFileSuffix?url:url.substr(0,url.length-1)+TargetMediator.offerFileSuffix:url+TargetMediator.offerFileSuffix},appendUrlQueryString:function(url){return url+TargetMediator.offerQueryString},prependUrlPathname:function(url){if(""!==TargetMediator.offerContentPath){var indexOfProtocolEnd=url.indexOf("//"),indexOfPathname=url.indexOf("/",indexOfProtocolEnd+2);return url.substr(0,indexOfPathname)+TargetMediator.offerContentPath+url.substr(indexOfPathname,url.length)}return url}},AWSRequester}(),Mbox=function(){function Mbox($elem,logger){this.$elem=$elem,this.logger=logger,this.name=this.getName($elem),TargetMediator.mboxRegistry[this.name]={state:"pending"},this.time=this.getMboxTime(),this.requestId=this.generateMboxRequestId(),consoleDebug("Instantiate mbox "+this.name+" at time "+(new Date).getTime()+"; "+((new Date).getTime()-TargetMediator.scriptLoadTime)+"ms elapsed")}return Mbox.prototype={init:function(){this.targetRequestDataBuilder=new TargetRequestDataBuilder(this.allParams(),TargetMediator.pageParams),this.targetRequestDataBuilder.build(),this.targetRequester=new TargetRequester($.proxy(this.handleOfferResponse,this),this.targetRequestDataBuilder,this.logger,!1),this.awsRequester=new AWSRequester($.proxy(this.handleContentResponse,this),this.logger),this.targetRequester.fetch()},handleOfferResponse:function(response,state){"resolved"===state&&(this.updateVisitorId(response),this.mboxTarget=this.buildMboxTarget(response)),"resolved"===state&&"undefined"==typeof response.defaultOffer?(this.awsRequester.setUrl(response.url),this.awsRequester.fetch()):"resolved"===state&&"boolean"==typeof response.defaultOffer&&response.defaultOffer?(this.showDefault(),this.setClickHandler()):this.showDefault()},handleContentResponse:function(response,state){if("resolved"===state){var contentInjectionFailed=!1,$defaultElem=this.$elem.clone();try{this.$elem.html(response)}catch(ex){throw contentInjectionFailed=!0,this.$elem.html($defaultElem.html()),this.showDefault(),this.logger.log({type:"OfferInjectionError",data:this.name,message:ex.name+": "+ex.message}),new TargetError("OfferInjectionError: "+ex.name+": "+ex.message)}contentInjectionFailed||(this.showOffer(),this.setClickHandler())}else this.showDefault()},handleClickResponse:function(){"clickUrl"in this&&(consoleDebug("Go to click url "+this.clickUrl),window.location=this.clickUrl)},setClickHandler:function(){var that=this;this.$elem.on("click",function(e){consoleDebug("Clicked "+that.name);var mboxParams={mbox:that.name+"-clicked",mboxTime:that.getMboxTime(),mboxRequestId:that.generateMboxRequestId(),mboxTarget:that.mboxTarget},targetRequestDataBuilder=new TargetRequestDataBuilder(mboxParams,TargetMediator.pageParams);targetRequestDataBuilder.build();var $closestAnchor=$(e.target).closest("a"),isClosestAnchorWithinMbox=!!$closestAnchor.parents(".js-mbox").length;if(isClosestAnchorWithinMbox&&(that.clickUrl=$closestAnchor.attr("href"),"undefined"!=typeof that.clickUrl&&""!==that.clickUrl&&null===that.clickUrl.match(/^(\#|javascript)/))){e.preventDefault();var targetRequester=new TargetRequester($.proxy(that.handleClickResponse,that),targetRequestDataBuilder,that.logger,!0);targetRequester.fetch()}})},getName:function($mbox){var name=$mbox.attr("data-mbox");if("undefined"==typeof name)throw this.showDefault(),new TargetError("MboxNameError: Data attribute is undefined");var langRegExp=/^(cn|en|de|es|fr|ja|ko|pt)_/g;if(0===name.length)throw this.showDefault(),new TargetError("MboxNameError: Name cannot be empty");if(name.length>250)throw this.showDefault(),new TargetError("MboxNameError: "+name+" exceeds max length of 250 characters");if(name.match(/^\s+|\s+$/g))throw this.showDefault(),new TargetError("MboxNameError: "+name+" has leading/trailing whitespace");if(!name.match(langRegExp))throw this.showDefault(),new TargetError("MboxNameError: "+name+" does not have a valid preceding locale code");var shortLangCode=TargetMediator.pageLangCode.substring(0,2);if(name.match(/^en_/)){var match=name.match(langRegExp),matchLength=match[0].length;name=name.substring(matchLength-1,name.length),name=shortLangCode+name}else if(0!==name.indexOf(shortLangCode+"_"))throw this.showDefault(),new TargetError("MboxNameError: "+name+" is not in the current page language");if(name in TargetMediator.mboxRegistry)throw this.showDefault(),new TargetError("MboxNameError: "+name+" is already registered in the current page");return name},getMboxTime:function(){var now=new Date;return now.getTime()-6e4*now.getTimezoneOffset()},generateMboxRequestId:function(){return Util.generateRandomId()},buildMboxTarget:function(response){return response.campaignId+"."+response.environmentId},allParams:function(){return{mbox:this.name,mboxTime:this.time,mboxRequestId:this.requestId}},updateVisitorId:function(response){if(TargetMediator.pageParams.mboxPCId!==response.userPCId){TargetMediator.pageParams.mboxPCId=response.userPCId;var visitorCookie=new CookieManager("aws-target-visitor-id");visitorCookie.value=TargetMediator.pageParams.mboxPCId,visitorCookie.expireSeconds=7257600,visitorCookie.encode(),visitorCookie.write()}},showDefault:function(){this.show("default content")},showOffer:function(){this.show("offer")},show:function(messageType){"undefined"!=typeof this.name&&(TargetMediator.mboxRegistry[this.name].state="resolved"),TargetMediator.hasEncounteredLastMbox&&TargetMediator.checkJqueryReadyHold(),this.$elem[0].style.visibility="visible",consoleDebug("Show "+messageType+" for "+this.name+" at time "+(new Date).getTime()+"; "+((new Date).getTime()-TargetMediator.scriptLoadTime)+"ms elapsed")}},Mbox}();mboxCreate=function(){var locatorId="js-mbox-"+TargetMediator.locatorNodeCount++;consoleDebug("Create mbox locator "+locatorId),document.write('<div id="'+locatorId+'" style="visibility:hidden;display:none;">&nbsp;</div>');var locatorNode=document.getElementById(locatorId),node=function(node){for(;null!==node;){if(1===node.nodeType&&(" "+node.className+" ").indexOf(" js-mbox ")>-1)return node;node=node.previousSibling}return null}(locatorNode);if(null===node)throw document.write("<style>.js-mbox { visibility:visible; }</style>"),new TargetError("MboxCreateError: Failed to find an mbox at locator id "+locatorId);var $elem=$(node);if(0===$elem.length)throw document.write("<style>.js-mbox { visibility:visible; }</style>"),new TargetError("MboxCreateError: Failed to create a jQuery object from mbox at locator id "+locatorId);new Mbox($elem,TargetMediator.logger).init()},mboxLast=function(){consoleDebug("Seen mboxLast"),TargetMediator.hasEncounteredLastMbox=!0,TargetMediator.checkJqueryReadyHold()};var TargetMediator={init:function(options){if(TargetMediator.scriptLoadTime=(new Date).getTime(),"true"===Util.getQueryStringParameterByName("debugTarget")&&(TargetMediator.debug=!0),consoleDebug("Script load at time "+TargetMediator.scriptLoadTime),!Util.hasCORSSupport())return TargetMediator.preventMboxCreation(),consoleDebug("Mboxes not initialized because CORS is not supported"),void 0;TargetMediator.pageLangCode=TargetMediator.determinePageLangCode(),TargetMediator.hideAllMboxes(),$.holdReady(!0);for(var key in options)options.hasOwnProperty(key)&&(TargetMediator[key]=options[key]);var parameterFetcher=new ParameterFetcher;parameterFetcher.init(),TargetMediator.pageParams=parameterFetcher.allParams(),TargetMediator.logger=new Logger},determinePageLangCode:function(){var pageLangCode=$("html").attr("lang");return"undefined"==typeof pageLangCode?consoleDebug("HTML tag lang attribute is undefined"):pageLangCode=pageLangCode.replace("-","_"),pageLangCode},hideAllMboxes:function(){document.write("<style>.js-mbox { visibility:hidden; }</style>")},preventMboxCreation:function(){mboxCreate=function(){},mboxLast=function(){}},allMboxesResolved:function(){var mboxRegistry=TargetMediator.mboxRegistry;for(var mbox in mboxRegistry)if(mboxRegistry.hasOwnProperty(mbox)&&"pending"===mboxRegistry[mbox].state)return!1;return!0},releaseJqueryHoldReady:function(){$.holdReady(!1),consoleDebug("Release jQuery ready at time "+(new Date).getTime()+"; "+((new Date).getTime()-TargetMediator.scriptLoadTime)+"ms elapsed")},checkJqueryReadyHold:function(){TargetMediator.allMboxesResolved()&&TargetMediator.releaseJqueryHoldReady()},debug:!1,locatorNodeCount:0,mboxRegistry:{},pageParams:{},pageLangCode:null,scriptLoadTime:null,logger:null,hasEncounteredLastMbox:!1,isFirstTimeVisitor:!1,offerFileSuffix:"/",offerContentPath:"",offerQueryString:""};"object"==typeof module&&module&&"object"==typeof module.exports&&(module.exports=function(){return TargetMediator.Util=Util,TargetMediator.TargetError=TargetError,TargetMediator.Logger=Logger,TargetMediator.TargetRequestDataBuilder=TargetRequestDataBuilder,TargetMediator.CookieManager=CookieManager,TargetMediator.ParameterFetcher=ParameterFetcher,TargetMediator.TargetRequester=TargetRequester,TargetMediator.AWSRequester=AWSRequester,TargetMediator.Mbox=Mbox,TargetMediator}),"object"==typeof window&&"object"==typeof window.document&&(AWS="undefined"!=typeof AWS?AWS:{},AWS.TargetMediator=TargetMediator,window.console||(console={log:function(){}}))}();