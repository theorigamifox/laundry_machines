
    jQuery(document).ready(function ($) {
    $("a[rel^='prettyPhoto']").prettyPhoto();
    $('#mainContainerInner').fitVids();
    $("#featured_prod ul").slick({

  // normal options...
  infinite: false,
  slidesToShow: 4,

  // the magic
  responsive: [{

      breakpoint: 1024,
      settings: {
        slidesToShow: 3,
        infinite: true
      }

    }, {

      breakpoint: 600,
      settings: {
        slidesToShow: 2,
        dots: true
      }

    }, {

      breakpoint: 300,
      settings: "unslick" // destroys slick

    }]
});
    $('#site-navigation').slicknav({
        label: '',
        prependTo:'#mobile-menu'
    });
    $('a[href*="#"]')
            // Remove links that don't actually link to anything
            .not('[href="#"]')
            .not('[href="#0"]')
            .click(function (event) {
                // On-page links
                if (
                        location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '')
                        &&
                        location.hostname == this.hostname
                        ) {
                    // Figure out element to scroll to
                    var target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                    // Does a scroll target exist?
                    if (target.length) {
                        // Only prevent default if animation is actually gonna happen
                        event.preventDefault();
                        $('html, body').animate({
                            scrollTop: target.offset().top
                        }, 1000, function () {
                            // Callback after animation
                            // Must change focus!
                            var $target = $(target);
                            $target.focus();
                            if ($target.is(":focus")) { // Checking if the target was focused
                                return false;
                            } else {
                                $target.attr('tabindex', '-1'); // Adding tabindex for elements not focusable
                                $target.focus(); // Set focus again
                            }
                            ;
                        });
                    }
                }
            });
});
function mobileToggleMenu() {

    if (jQuery(window).width() < 640) {
        jQuery("#footer .mobile_togglemenu").remove();
        jQuery("#footer .menu .menu-item-has-children").prepend("<a class='mobile_togglemenu'><i class='fa fa-chevron-down' aria-hidden='true'></i></a>");
        jQuery("#footer .menu .menu-item-has-children").addClass('toggle');
        jQuery("#footer .mobile_togglemenu").click(function () {
            jQuery(this).parent().toggleClass('active').parent().find('ul.sub-menu').toggle('slow');
        });
    } else {
        jQuery("#footer .menu .menu-item-has-children").parent().find('ul.sub-menu').removeAttr('style');
        jQuery("#footer .menu .menu-item-has-children").removeClass('active');
        jQuery("#footer .menu .menu-item-has-children").removeClass('toggle');
        jQuery("#footer .mobile_togglemenu").remove();
    }
}
jQuery(document).ready(function () {
    mobileToggleMenu();
});
jQuery(window).resize(function () {
    mobileToggleMenu();
});
//function stopError() {
//  return true;
//}
//
//window.onerror = stopError;
//
//			function recalcPrice(productID) {
//				eval("thisPrice = baseprice"+productID+";");
//				eval("thisPriceExTax = basepriceExTax"+productID+";");
//				eval("thisPriceIncTax = basepriceIncTax"+productID+";");
//				eval("thisPriceTax = basepriceTax"+productID+";");
//				eval("thisOOPrice = oobaseprice"+productID+";");
//				eval("thisOOPriceExTax = oobasepriceExTax"+productID+";");
//				eval("thisOOPriceIncTax = oobasepriceIncTax"+productID+";");
//				eval("thisOOPriceTax = oobasepriceTax"+productID+";");
//				currentValues = new Array(efcount);
//				eval ("qtybox = document.productForm"+productID+".qty"+productID+";");
//				qty = 1;
//				if (typeof qtybox != "undefined") {
//					eval("qtyboxtype = qtybox.type");
//					if (qtyboxtype == "select-one") {
//						qty = qtybox.options[qtybox.selectedIndex].text;
//					}
//					if (qtyboxtype == "text") {
//						qty = qtybox.value;
//					}
//				}
//				for (f = 0; f < efcount; f++) {
//					currentValues[extrafields[f]] = "";
//					eval("result = document.productForm"+productID+"."+extrafields[f]+";");
//					if (extrafieldstype[f] == "CHECKBOXES") {
//						eval("result = document.productForm"+productID+"."+extrafields[f]+"1;");
//					}
//					if (extrafieldstype[f] == "RADIOBUTTONS") {
//						if (eval("document.productForm"+productID+"."+extrafields[f]+";")) {
//							eval("result = document.productForm"+productID+"."+extrafields[f]+".length;");
//							if (result > 0) {
//								result = "radio";
//							}
//						}
//					}
//						
//					if (typeof result != "undefined" || result=="radio") {
//						if (result != "radio") {
//							eval("fieldtype = result.type;");
//						} else {
//							fieldtype = "radio";
//						}
//						if (fieldtype=="select-one") {
//							content = result.options[result.selectedIndex].value;
//							currentValues[extrafields[f]] = content;
//						}
//						if (fieldtype=="checkbox") {
//							content = "";
//							thisOne = 1;
//							while (typeof result != "undefined") {
//								if (result.checked == true) {
//									if (content != "") {
//										content = content+";";
//									}
//									content = content + result.value;
//								}
//								thisOne = thisOne + 1;
//								eval("result = document.productForm"+productID+"."+extrafields[f]+thisOne+";");
//							}
//							currentValues[extrafields[f]] = content;
//						}
//						if (fieldtype=="radio") {
//							eval("radlength = document.productForm"+productID+"."+extrafields[f]+".length;");
//							for (g = 0; g < radlength; g++) {
//								eval("thisoption = document.productForm"+productID+"."+extrafields[f]+"["+g+"].checked;");
//								if (thisoption == true) {
//									eval("thisvalue = document.productForm"+productID+"."+extrafields[f]+"["+g+"].value;");
//									currentValues[extrafields[f]] = thisvalue;
//								}
//							}
//						}
//					}
//				}
//				eval ("arraylength = parray"+productID+".length;");
//				eval ("advArray = parray"+productID+";");
//				for (f = 0; f <  arraylength; f++) {		
//					applicable = false;
//					if (parseInt(advArray[f]["qtyfrom"]) != -1 && parseInt(advArray[f]["qtyto"]) != -1 && parseInt(advArray[f]["qtyto"]) != 0) {
//						//quantity is applicable here
//						if (parseInt(qty) >= parseInt(advArray[f]["qtyfrom"]) && parseInt(qty) <= parseInt(advArray[f]["qtyto"])) {
//							applicable = true;
//						}
//					} else {
//						applicable = true;
//					}
//					thisapplic = true;
//					foundMatches = 0;
//					for (g = 0; g < efcount; g++) {
//						if (advArray[f][extrafields[g]] != "" && advArray[f][extrafields[g]] != "0") {
//							splitCheck = advArray[f][extrafields[g]].split(";");
//							splitapplic = false;
//							for (k = 0; k < splitCheck.length; k++) {
//								splitValues = currentValues[extrafields[g]].split(";");
//								for (l = 0; l < splitValues.length; l++) {
//									if ((splitCheck[k] == splitValues[l] && splitCheck[k] != "" && splitValues[l] != "")) {
//										splitapplic = true;
//										if (extrafieldstype[g] == "CHECKBOXES") {
//											foundMatches = foundMatches + 1;
//										}
//									}
//								}
//							}
//							if (splitapplic == true && thisapplic == true) {
//								thisapplic = true;
//							} else {
//								thisapplic = false;
//							}
//						}
//					}
//					if (thisapplic == true && applicable == true) {
//						applicable = true;
//					} else {
//						applicable = false;
//					}
//					if (applicable == true) {
//						//new base price
//						if (foundMatches == 0) { foundMatches =1; }
//						if (parseInt(advArray[f]["priceType"]) == 0) {
//							if (parseFloat(advArray[f]["percentage"]) > 0) {
//								thisPrice = thisPrice + (thisPrice  * ((eval(advArray[f]["percentage"])/100)));
//								thisPriceExTax = thisPriceExTax + (thisPriceExTax  * ((eval(advArray[f]["percentage"])/100)));
//								thisPriceIncTax = thisPriceIncTax + (thisPriceIncTax  * ((eval(advArray[f]["percentage"])/100)));
//								thisPriceTax = thisPriceTax + (thisPriceTax  * ((eval(advArray[f]["percentage"])/100)));
//							}
//							if (parseFloat(advArray[f]["percentage"]) < 0) {
//								thisPrice = thisPrice - (thisPrice  * (Math.abs(eval(advArray[f]["percentage"]))/100));
//								thisPriceExTax = thisPriceExTax - (thisPriceExTax  * (Math.abs(eval(advArray[f]["percentage"]))/100));
//								thisPriceIncTax = thisPriceIncTax - (thisPriceIncTax  * (Math.abs(eval(advArray[f]["percentage"]))/100));
//								thisPriceTax = thisPriceTax - (thisPriceTax  * (Math.abs(eval(advArray[f]["percentage"]))/100));
//							}
//							if (parseFloat(advArray[f]["percentage"]) == 0) {
//								thisPrice = eval(advArray[f]["price"]);
//								thisPriceExTax = eval(advArray[f]["priceExTax"]);
//								thisPriceIncTax = eval(advArray[f]["priceIncTax"]);
//								thisPriceTax = eval(advArray[f]["priceTax"]);
//							}
//						}
//						if (parseInt(advArray[f]["priceType"]) == 1) {
//							if (parseFloat(advArray[f]["percentage"]) > 0) {
//								for (m = 1; m <= foundMatches; m++) {
//									thisPrice = thisPrice + (thisPrice  * ((eval(advArray[f]["percentage"])/100)));
//									thisPriceExTax = thisPriceExTax + (thisPriceExTax  * ((eval(advArray[f]["percentage"])/100)));
//									thisPriceIncTax = thisPriceIncTax + (thisPriceIncTax  * ((eval(advArray[f]["percentage"])/100)));
//									thisPriceTax = thisPriceTax + (thisPriceTax  * ((eval(advArray[f]["percentage"])/100)));
//								}
//							}
//							if (parseFloat(advArray[f]["percentage"]) < 0) {
//								for (m = 1; m <= foundMatches; m++) {
//									thisPrice = thisPrice - (thisPrice  * (Math.abs(eval(advArray[f]["percentage"]))/100));
//									thisPriceExTax = thisPriceExTax - (thisPriceExTax  * (Math.abs(eval(advArray[f]["percentage"]))/100));
//									thisPriceIncTax = thisPriceIncTax - (thisPriceIncTax  * (Math.abs(eval(advArray[f]["percentage"]))/100));
//									thisPriceTax = thisPriceTax - (thisPriceTax  * (Math.abs(eval(advArray[f]["percentage"]))/100));
//								}
//							}
//							if (parseFloat(advArray[f]["percentage"]) == 0) {
//								for (m = 1; m <= foundMatches; m++) {
//									thisPrice = thisPrice + eval(advArray[f]["price"]);
//									thisPriceExTax = thisPriceExTax + eval(advArray[f]["priceExTax"]);
//									thisPriceIncTax = thisPriceIncTax + eval(advArray[f]["priceIncTax"]);
//									thisPriceTax = thisPriceTax + eval(advArray[f]["priceTax"]);
//								}
//							}
//						}	
//						if (parseInt(advArray[f]["priceType"]) == 2) {
//							if (parseFloat(advArray[f]["percentage"]) > 0) {
//								for (m = 1; m <= foundMatches; m++) {
//									thisPrice = thisPrice - (thisPrice * (eval(advArray[f]["percentage"])/100));
//									thisPriceExTax = thisPriceExTax - (thisPriceExTax * (eval(advArray[f]["percentage"])/100));
//									thisPriceIncTax = thisPriceIncTax - (thisPriceIncTax * (eval(advArray[f]["percentage"])/100));
//									thisPriceTax = thisPriceTax - (thisPriceTax * (eval(advArray[f]["percentage"])/100));
//								}
//							}
//							if (parseFloat(advArray[f]["percentage"]) < 0) {
//								for (m = 1; m <= foundMatches; m++) {
//									thisPrice = thisPrice - (thisPrice * (Math.abs(eval(advArray[f]["percentage"]))/100));
//									thisPriceExTax = thisPriceExTax - (thisPriceExTax * (Math.abs(eval(advArray[f]["percentage"]))/100));
//									thisPriceIncTax = thisPriceIncTax - (thisPriceIncTax * (Math.abs(eval(advArray[f]["percentage"]))/100));
//									thisPriceTax = thisPriceTax - (thisPriceTax * (Math.abs(eval(advArray[f]["percentage"]))/100));
//								}
//							}
//							if (parseFloat(advArray[f]["percentage"]) == 0) {
//								for (m = 1; m <= foundMatches; m++) {
//									thisPrice = thisPrice - eval(advArray[f]["price"]);
//									thisPriceExTax = thisPriceExTax - eval(advArray[f]["priceExTax"]);
//									thisPriceIncTax = thisPriceIncTax - eval(advArray[f]["priceIncTax"]);
//									thisPriceTax = thisPriceTax - eval(advArray[f]["priceTax"]);
//								}
//							}
//						}	
//						if (parseInt(advArray[f]["priceType"]) == 4) {
//							if (parseFloat(advArray[f]["percentage"]) > 0) {
//								for (m = 1; m <= foundMatches; m++) {
//									thisOOPrice = thisOOPrice - (thisOOPrice * (eval(advArray[f]["percentage"])/100));
//									thisOOPriceExTax = thisOOPriceExTax - (thisOOPriceExTax * (eval(advArray[f]["percentage"])/100));
//									thisOOPriceIncTax = thisOOPriceIncTax - (thisOOPriceIncTax * (eval(advArray[f]["percentage"])/100));
//									thisOOPriceTax = thisOOPriceTax - (thisOOPriceTax * (eval(advArray[f]["percentage"])/100));
//								}
//							}
//							if (parseFloat(advArray[f]["percentage"]) < 0) {
//								for (m = 1; m <= foundMatches; m++) {
//									thisOOPrice = thisOOPrice - (thisPrice * (Math.abs(eval(advArray[f]["percentage"]))/100));
//									thisOOPriceExTax = thisOOPriceExTax - (thisOOPriceExTax * (Math.abs(eval(advArray[f]["percentage"]))/100));
//									thisOOPriceIncTax = thisOOPriceIncTax - (thisOOPriceIncTax * (Math.abs(eval(advArray[f]["percentage"]))/100));
//									thisOOPriceTax = thisOOPriceTax - (thisOOPriceTax * (Math.abs(eval(advArray[f]["percentage"]))/100));
//								}
//							}
//							if (parseFloat(advArray[f]["percentage"]) == 0) {
//								for (m = 1; m <= foundMatches; m++) {
//									thisOOPrice = eval(advArray[f]["price"]);
//									thisOOPriceExTax = eval(advArray[f]["priceExTax"]);
//									thisOOPriceIncTax = eval(advArray[f]["priceIncTax"]);
//									thisOOPriceTax = eval(advArray[f]["priceTax"]);
//								}
//							}
//						}												
//					}							
//				}
//				displayPrice = presentValue(thisPrice,cDP,cPreT,cMidT,cPostT);
//				changeContent("priceSpan"+productID,"priceLayer"+productID,displayPrice);
//				displayPrice = presentValue(thisPriceExTax,cDP,cPreT,cMidT,cPostT);
//				changeContent("priceExTaxSpan"+productID,"priceExTaxLayer"+productID,displayPrice);
//				displayPrice = presentValue(thisPriceIncTax,cDP,cPreT,cMidT,cPostT);
//				changeContent("priceIncTaxSpan"+productID,"priceIncTaxLayer"+productID,displayPrice);
//				displayPrice = presentValue(thisPriceTax,cDP,cPreT,cMidT,cPostT);
//				changeContent("priceTaxSpan"+productID,"priceTaxLayer"+productID,displayPrice);
//				
//				displayPrice = presentValue(thisOOPrice,cDP,cPreT,cMidT,cPostT);
//				changeContent("oopriceSpan"+productID,"oopriceLayer"+productID,displayPrice);
//				displayPrice = presentValue(thisOOPriceExTax,cDP,cPreT,cMidT,cPostT);
//				changeContent("oopriceExTaxSpan"+productID,"oopriceExTaxLayer"+productID,displayPrice);
//				displayPrice = presentValue(thisOOPriceIncTax,cDP,cPreT,cMidT,cPostT);
//				changeContent("oopriceIncTaxSpan"+productID,"oopriceIncTaxLayer"+productID,displayPrice);
//				displayPrice = presentValue(thisOOPriceTax,cDP,cPreT,cMidT,cPostT);
//				changeContent("oopriceTaxSpan"+productID,"oopriceTaxLayer"+productID,displayPrice);
//			}
//			
//			isNS4 = (document.layers) ? true : false;
//isIE4 = (document.all && !document.getElementById) ? true : false;
//isIE5 = (document.all && document.getElementById) ? true : false;
//isNS6 = (!document.all && document.getElementById) ? true : false;
//			
//function changeContent(theDiv,theLayer,newText) {
//	if (isNS4){
//	   elm = document.layers[theLayer];
//	   elm.document.open();
//       elm.document.write(newText);
//       elm.document.close();
//
//	}
//	else if (isIE4) {
//	   elm = document.all[theDiv];
//	   elm.innerText = newText;
//	}
//	else if (isIE5) {
//	   elm = document.getElementById(theDiv);
//		if (elm) {
//	   		elm.innerText = newText;
//	   	}
//	}
//	else if (isNS6) {
//		var elmw = document.getElementById(theDiv);
//    	if (elmw) {
//    		elmw.childNodes[0].nodeValue = newText;
//    	}	
//	}
//}			
//
//    function presentValue(value,dp,pt,mt,at) {
//        if(value<=0.9999) {
//            newPounds='0';
//        } else {
//            newPounds=parseInt(value);
//        }
//        dec='1';
//        for (var i=1; i<=dp;i++) {
//            dec=dec+'0';
//        }
//        if (value>0) {
//            newPence=Math.round((eval(value)+.000008 - newPounds)*(eval(dec)));
//        } else {
//            newPence=0;
//        }
//        compstring='9';
//        for (var i=1; i <=dp-1;i++) {
//            if (eval(newPence) <= eval(compstring)) newPence='0'+newPence;
//            compstring=compstring+'9';
//        }
//        if (dp>0) {
//            if (newPence==eval(dec)) { newPounds++; newPence='00'; }
//            newString=pt+newPounds+mt+newPence+at;
//        } else {
//            newString=pt+newPounds+at;
//        }
//        return (newString);
//    }
//   
