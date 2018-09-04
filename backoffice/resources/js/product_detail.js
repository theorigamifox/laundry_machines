						lastMinusID = -1;

						function captureSearchGroup(obj,e) {
							if (e.keyCode==13) {
								e.keyCode = 0;
								searchGroup();
								return false;
							} else {
								return true;
							}
						}
						
						function captureSearchGroupFirefox(e) {
							var keyCode = e ? e.which : event.keyCode;
							if (e.type!="keypress") { return true; }
							if (generalNav(e)) { return true; }
							if (keyCode==13) {
								keyCode = 0;
								searchGroup();
								return false;
							} else {
								return true;
							}
						}
						
						function captureSearchAssoc(obj,e) {
							if (e.keyCode==13) {
								e.keyCode = 0;
								searchAssoc();
								return false;
							} else {
								return true;
							}
						}
						
						function captureSearchAssocFirefox(e) {
							var keyCode = e ? e.which : event.keyCode;
							if (e.type!="keypress") { return true; }
							if (generalNav(e)) { return true; }
							if (keyCode==13) {
								keyCode = 0;
								searchAssoc();
								return false;
							} else {
								return true;
							}
						}
						
						function addGroup(productID,productCode,productName) {
							foundMe = -1;
							for (f = 0; f < document.detailsForm.xGroupProducts.options.length; f++) {
								thisOption = document.detailsForm.xGroupProducts.options[f].value;
								if (thisOption == productID+":0" || thisOption == productID+":1") {
									foundMe = f;
								}
							}
							if (foundMe == -1) {
								theqty = parseInt(document.detailsForm.xGroupQty.value);
								if (productCode != "") {
									newText = productCode+" : "+productName+" ("+theqty+")";
								} else {
									newText = productName;
								}
								document.detailsForm.xGroupProducts.options[document.detailsForm.xGroupProducts.options.length] = new Option(newText,productID+":"+theqty);
							}
							recalculateGroup();
						}
						
						function recalculateGroup() {
							sectionList = "";
							for (f = 0; f < document.detailsForm.xGroupProducts.options.length; f++) {
								thisOption = document.detailsForm.xGroupProducts.options[f].value;
								sectionList = sectionList + thisOption+";";
							}
							document.detailsForm.xGroupSend.value = sectionList;
						}
						
						function groupDeleteItem() {
							eval("thisBox = document.detailsForm.xGroupProducts");
							theSelected = thisBox.selectedIndex;
							if (theSelected == -1) {
								rc=alert("Please select a product to delete first.");
							} else {
								theID = thisBox.options[theSelected].value;
								thisBox.options[theSelected] = null;
								document.detailsForm.xGroupDelete.value = document.detailsForm.xGroupDelete.value + theID+";";
								recalculateGroup();
							}
						}
						
						function groupMoveItem(direction) {
							eval("thisBox = document.detailsForm.xGroupProducts");
							currentSelected = thisBox.selectedIndex;
							totalItems = thisBox.length;
							if (currentSelected == -1) {
								rc=alert("Please select a product to move first.");
							} else {
								if ((direction=="up" && currentSelected == 0) || (direction=="down" && currentSelected == totalItems-1)) {
								} else {
									if (direction=="up") {
										changeWith = currentSelected - 1;
									}
									if (direction=="down") {
										changeWith = currentSelected + 1;
									}
									optionValue = thisBox.options[changeWith].value;
									optionText = thisBox.options[changeWith].text;
									thisBox.options[changeWith].value = thisBox.options[currentSelected].value;
									thisBox.options[changeWith].text = thisBox.options[currentSelected].text;
									thisBox.options[currentSelected].value = optionValue;
									thisBox.options[currentSelected].text = optionText;
									thisBox.selectedIndex = changeWith;
									recalculateGroup();
								}
							}
						}
						
						
						function addAssoc(productID,productCode,productName) {
							foundMe = -1;
							for (f = 0; f < document.detailsForm.xAssociatedProducts.options.length; f++) {
								thisOption = document.detailsForm.xAssociatedProducts.options[f].value;
								if (thisOption == productID+":0" || thisOption == productID+":1") {
									foundMe = f;
								}
							}
							if (foundMe == -1) {
								if (document.detailsForm.xAssociatedBi.checked == true) {
									biLink = " (<->)";
									biBit = ":1";
								} else {
									biLink = "";
									biBit = ":0";
								}
								if (productCode != "") {
									newText = productCode+" : "+productName+biLink;
								} else {
									newText = productName;
								}
								document.detailsForm.xAssociatedProducts.options[document.detailsForm.xAssociatedProducts.options.length] = new Option(newText,productID+biBit);
							}
							recalculateAssoc();
						}
						
						function recalculateAssoc() {
							sectionList = "";
							for (f = 0; f < document.detailsForm.xAssociatedProducts.options.length; f++) {
								thisOption = document.detailsForm.xAssociatedProducts.options[f].value;
								sectionList = sectionList + thisOption+";";
							}
							document.detailsForm.xAssociatedSend.value = sectionList;
						}
						
						function assocDeleteItem() {
							eval("thisBox = document.detailsForm.xAssociatedProducts");
							theSelected = thisBox.selectedIndex;
							if (theSelected == -1) {
								rc=alert("Please select a product to delete first.");
							} else {
								theID = thisBox.options[theSelected].value;
								thisBox.options[theSelected] = null;
								document.detailsForm.xAssociatedDelete.value = document.detailsForm.xAssociatedDelete.value + theID+";";
								recalculateAssoc();
							}
						}
						
						function assocMoveItem(direction) {
							eval("thisBox = document.detailsForm.xAssociatedProducts");
							currentSelected = thisBox.selectedIndex;
							totalItems = thisBox.length;
							if (currentSelected == -1) {
								rc=alert("Please select a product to move first.");
							} else {
								if (direction=="up" && currentSelected == 0) { return false; }
								if (direction=="down" && currentSelected == totalItems-1) { return false; }
								if (direction=="up") {
									changeWith = currentSelected - 1;
								}
								if (direction=="down") {
									changeWith = currentSelected + 1;
								}
								optionValue = thisBox.options[changeWith].value;
								optionText = thisBox.options[changeWith].text;
								thisBox.options[changeWith].value = thisBox.options[currentSelected].value;
								thisBox.options[changeWith].text = thisBox.options[currentSelected].text;
								thisBox.options[currentSelected].value = optionValue;
								thisBox.options[currentSelected].text = optionText;
								thisBox.selectedIndex = changeWith;
								recalculateAssoc();
							}
						}								

		function extraAddItem(thisField,addMode) {
			theValue = eval("document.detailsForm.xExtraAdd"+thisField+".value");
			thePercent = eval("document.detailsForm.xExtraPercent"+thisField+".value");
			if (thePercent == "") { thePercent = 0; }
			theVisible = eval("document.detailsForm.xExtraVisible"+thisField+".checked");
			theCustomer = eval("document.detailsForm.xExtraCustomer"+thisField+".options[document.detailsForm.xExtraCustomer"+thisField+".selectedIndex].value");
			theCustomerText = eval("document.detailsForm.xExtraCustomer"+thisField+".options[document.detailsForm.xExtraCustomer"+thisField+".selectedIndex].text");
			if (theVisible == true) {
				theVisible = "Y";
			} else {
				theVisible = "N";
			}
			if (theValue == "") {
				rc=alert("You cannot insert blank options. Please try again.");
			} else {
				foundMe = -1;
				if (addMode == 0) {
					boxLength = eval("document.detailsForm.xExtraSelect"+thisField+".options.length");
					for (f = 0; f < boxLength; f++) {
						thisOption = eval("document.detailsForm.xExtraSelect"+thisField+".options["+f+"].text");
						if (thisOption == theValue) {
							foundMe = f;
						}
					}
				}
				if (foundMe == -1) {
					thisOption = new Array();
					thisOption["exvalID"] = 0;
					thisOption["productID"] = 0;
					thisOption["extraFieldID"] = thisField;
					thisOption["content"] = theValue;
					for (f = 0; f < langCount; f++) {
						if (langArray[f]["languageID"] != 1) {
							thisOption["content"+langArray[f]["languageID"]] = eval("document.detailsForm.xExtraAdd"+thisField+"_"+langArray[f]["languageID"]+".value;");
						}
					}
					thisOption["visible"] = theVisible;
					thisOption["accTypeID"] = theCustomer;
					thisOption["position"] = 9999;
					thisOption["percent"] = thePercent;
					outputPrices = "";
					for (f = 0; f < currCount; f++) {
						thisOption["price"+currArray[f]["currencyID"]] = eval("document.detailsForm.xExtra"+thisField+"Price"+currArray[f]["currencyID"]+".value;");
						if (outputPrices != "") { outputPrices = outputPrices + " "; }
						outputPrices = outputPrices + presentValue(thisOption["price"+currArray[f]["currencyID"]],currArray[f]["decimals"],currArray[f]["pretext"],currArray[f]["middletext"],currArray[f]["posttext"]);
					}
					eval("thisBox = document.detailsForm.xExtraSelect"+thisField);
					priceAddition = "";
					if (thisOption["percent"] < 0 || thisOption["percent"] > 0) {
						priceAddition = " ("+thisOption["percent"] + "%)";
					} else {
						if (thisOption["price1"] > 0 || thisOption["price1"] < 0) {
							priceAddition = " ("+outputPrices+")";
						}
					}
					theDisplayString = theValue + " ("+theCustomerText+")"+priceAddition;
					if (addMode == 0) {
						thisBox.options[thisBox.options.length] = new Option(theDisplayString,implodeQueryString(thisOption));
						eval("thisAPbox = document.detailsForm.xAPef"+thisField);
						thisAPbox.options[thisAPbox.options.length] = new Option(theValue,lastMinusID);
						lastMinusID--;
						theIndex = thisBox.options.length -1;
						if (theVisible == "N") {
							thisBox.options[theIndex].style.color = "#777777";
						} else {
							thisBox.options[theIndex].style.color = "#000000";
						}						
					} else {
						//ok we're editing, so we need to get the exvalID from the current option.
						thisQS = explodeQueryString(thisBox.options[thisBox.options.selectedIndex].value);
						thisOption["exvalID"] = thisQS["exvalID"];
						eval("thisAPbox = document.detailsForm.xAPef"+thisField);
						for (f=0;f<thisAPbox.length;f++) {
							if (thisAPbox.options[f].text == thisQS["content"]) {
								thisAPbox.options[f].text = thisOption["content"];
							}
						}
						theIndex = thisBox.options.selectedIndex;
						thisBox.options[thisBox.options.selectedIndex] = new Option(theDisplayString,implodeQueryString(thisOption));
						if (theVisible == "N") {
							thisBox.options[theIndex].style.color = "#777777";
						} else {
							thisBox.options[theIndex].style.color = "#000000";
						}
					}
					extraClearItem(thisField);
					extraRecalculate(thisField);
				} else {
					rc=alert("An option of this name already exists. Please try again.");
				}
			}

		}
		
		function extraItemShow(thisField) {
			eval("thisBox = document.detailsForm.xExtraSelect"+thisField);
			thisValue = explodeQueryString(thisBox.options[thisBox.selectedIndex].value);
			if (thisValue["visible"] == "Y") {
				thisVisible = true;
			} else {
				thisVisible = false;
			}
			accTypeID = thisValue["accTypeID"];
			eval("customerBox = document.detailsForm.xExtraCustomer"+thisField);
			for (f=0; f < customerBox.options.length; f++) {
				if (customerBox.options[f].value == accTypeID) {
					customerBox.selectedIndex = f;
				}
			}
			for (f = 0; f < langCount; f++) {
				if (langArray[f]["languageID"] != 1) {
					eval("document.detailsForm.xExtraAdd"+thisField+"_"+langArray[f]["languageID"]+".value = thisValue['content"+langArray[f]["languageID"]+"'];");
				}
			}
			for (f = 0; f < currCount; f++) {
				eval("document.detailsForm.xExtra"+thisField+"Price"+currArray[f]["currencyID"]+".value = thisValue['price"+currArray[f]["currencyID"]+"'];");
			}
			eval("document.detailsForm.xExtraAdd"+thisField+".value = thisValue['content'];");
			eval("document.detailsForm.xExtraPercent"+thisField+".value = thisValue['percent'];");
			eval("document.detailsForm.xExtraVisible"+thisField+".checked = thisVisible;");
			thisApplyButton = document.getElementById("xExtraApplyButton"+thisField);
			thisApplyButton.style.visibility = "visible";
		}

						function captureExtraReturn(obj,e,thisField) {
							if (e.keyCode==13) {
								e.keyCode = 0;
								thisApplyButton = document.getElementById("xExtraApplyButton"+thisField);
								if (thisApplyButton.style.visibility == "visible") {
									addMode = 1;
								} else {
									addMode = 0;
								}
								extraAddItem(thisField,addMode);
								return false;
							} else {
								if (e.type!="keypress") { return true; }
								if (isAmp(e.keyCode) || isEquals(e.keyCode)) {
									e.keyCode = 0;
									return false;
								} else {
									return true;
								}
							}
						}
						
						function captureExtraReturnFirefox(e) {
							//rc=alert(e.currentTarget.alt);
							//rc=alert(e.which);
							//rc=alert(e.keyCode);
							var keyCode = e ? e.which : event.keyCode;
							thisField = e.currentTarget.alt;
							if (keyCode==13) {
								keyCode = 0;
								thisApplyButton = document.getElementById("xExtraApplyButton"+thisField);
								if (thisApplyButton.style.visibility == "visible") {
									addMode = 1;
								} else {
									addMode = 0;
								}
								extraAddItem(thisField,addMode);
								return false;
							} else {
								if (e.type!="keypress") { return true; }
								if (generalNav(e)) { return true; }
								if (isAmp(keyCode) || isEquals(keyCode)) {
									keyCode = 0;
									return false;
								} else {
									return true;
								}
							}
						}
		
		function implodeQueryString(theArray) {
			queryString = "";
			for (var f in theArray) {
				if (queryString != "") {
					queryString = queryString + "&";
				}
				queryString = queryString + f + "="+theArray[f];
			}
			return queryString;
		}
		
		function explodeQueryString(theQS) {
			theArray = new Array();
			theQSbits = theQS.split("&");
			for (f = 0; f < theQSbits.length; f++) {
				if (theQSbits[f] != "") {
					thisPair = theQSbits[f].split("=");
					theArray[thisPair[0]] = thisPair[1];
				}
			}	
			return theArray;		
		}
		
		function extraDeleteItem(thisField) {
			eval("thisBox = document.detailsForm.xExtraSelect"+thisField);
			theSelected = thisBox.selectedIndex;
			theValue = thisBox.options[theSelected].value;
			thisQS = explodeQueryString(theValue);
			if (theSelected == -1) {
				rc=alert("Please select an option to delete first.");
			} else {				
				eval("thisAPbox = document.detailsForm.xAPef"+thisField);
				for (f=0;f<thisAPbox.length;f++) {
					if (thisAPbox.options[f].text == thisQS["content"]) {
						thisAPbox.options[f] = null;
					}
				}
				thisBox.options[theSelected] = null;
				eval("thisHidden = document.detailsForm.xExtra"+thisField+"Delete");
				thisHidden.value = thisHidden.value + theValue + "|";
				extraRecalculate(thisField);
				extraClearItem(thisField);
			}
		}
		
		function extraMoveItem(thisField,direction) {
			eval("thisBox = document.detailsForm.xExtraSelect"+thisField);
			currentSelected = thisBox.selectedIndex;
			totalItems = thisBox.length;
			if (currentSelected == -1) {
				rc=alert("Please select an option to move first.");
			} else {
				doMove = true;
				if (direction=="up" && currentSelected == 0) { doMove = false; }
				if (direction=="down" && currentSelected == totalItems-1) { doMove = false; }
				if (direction=="up") {
					changeWith = currentSelected - 1;
				}
				if (direction=="down") {
					changeWith = currentSelected + 1;
				}
				if (doMove == true) {
					optionValue = thisBox.options[changeWith].value;
					optionText = thisBox.options[changeWith].text;
					thisBox.options[changeWith].value = thisBox.options[currentSelected].value;
					thisBox.options[changeWith].text = thisBox.options[currentSelected].text;
					thisBox.options[currentSelected].value = optionValue;
					thisBox.options[currentSelected].text = optionText;
					thisBox.selectedIndex = changeWith;
					extraRecalculate(thisField);
				}
			}
		}		
		
		function extraClearItem(thisField) {
			eval("document.detailsForm.xExtraAdd"+thisField+".value = '';");
			eval("document.detailsForm.xExtraPercent"+thisField+".value = '0';");
			eval("document.detailsForm.xExtraVisible"+thisField+".checked = true;");
			eval("document.detailsForm.xExtraCustomer"+thisField+".selectedIndex = 0;");
			thisApplyButton = document.getElementById("xExtraApplyButton"+thisField);
			thisApplyButton.style.visibility = "hidden";
			
			for (f = 0; f < currCount; f++) {
				eval("document.detailsForm.xExtra"+thisField+"Price"+currArray[f]["currencyID"]+".value = 0;");
			}			
			for (f = 0; f < langCount; f++) {
				if (langArray[f]["languageID"] != 1) {
					eval("document.detailsForm.xExtraAdd"+thisField+"_"+langArray[f]["languageID"]+".value = '';");
				}
			}			
			eval("thisBox = document.detailsForm.xExtraSelect"+thisField);
			thisBox.selectedIndex = -1;
		}
		
		function extraRecalculate(thisField) {
			thisValue = "";
			eval("thisBox = document.detailsForm.xExtraSelect"+thisField);
			for (f = 0; f < thisBox.options.length; f++) {
				thisValue = thisValue + thisBox.options[f].value+"|";
			}
			eval("thisHidden = document.detailsForm.xExtra"+thisField+"Send");
			thisHidden.value = thisValue;
		}
		
	function addSection() {
		theID = document.detailsForm.xSections.options[document.detailsForm.xSections.selectedIndex].value;
		theText = document.detailsForm.xSections.options[document.detailsForm.xSections.selectedIndex].text;
		foundMe = -1;
		for (f = 0; f < document.detailsForm.xSelectedSections.options.length; f++) {
			thisOption = document.detailsForm.xSelectedSections.options[f].value;
			if (thisOption == theID) {
				foundMe = f;
			}
		}
		if (foundMe == -1) {
			document.detailsForm.xSelectedSections.options[document.detailsForm.xSelectedSections.options.length] = new Option(theText,theID);
		}
		recalculateSections();
	}
	
	function removeSection() {
		selectedSection = document.detailsForm.xSelectedSections.selectedIndex;
		if (selectedSection == -1) {
			rc=alert("Please select a section first!");
		} else {
			theID = document.detailsForm.xSelectedSections.options[selectedSection].value;
			document.detailsForm.xSectionsDelete.value = document.detailsForm.xSectionsDelete.value + theID+";";
			document.detailsForm.xSelectedSections.options[selectedSection] = null;
		}
		recalculateSections();
	}
	
	function recalculateSections() {
		sectionList = "";
		for (f = 0; f < document.detailsForm.xSelectedSections.options.length; f++) {
			thisOption = document.detailsForm.xSelectedSections.options[f].value;
			sectionList = sectionList + thisOption+";";
		}
		document.detailsForm.xSectionsSend.value = sectionList;
	}
	
		function toggleDiv(thisDiv) {
			daDiv = document.getElementById(thisDiv);
			daDivToggle = document.getElementById(thisDiv+"Toggle");
			if (daDiv.style.display != "none") {
				daDiv.style.display = "none";
				daDivToggle.innerText = "show";
			} else {
				daDiv.style.display = "inline";
				daDivToggle.innerText = "hide";
				self.location.href='#'+thisDiv+"Anchor";
				backTimes--;
			}
		}		
		
    function presentValue(value,dp,pt,mt,at) {
    	if (value < 0) {
    		isMinus = "-";
    	} else {
    		isMinus = "";
    	}
    	value = eval(Math.abs(value));
        if(value<=0.9999) {
            newPounds='0';
        } else {
            newPounds=parseInt(value);
        }
        dec='1';
        for (var i=1; i<=dp;i++) {
            dec=dec+'0';
        }
        if (value>0) {
            newPence=Math.round((eval(value)+.000008 - newPounds)*(eval(dec)));
        } else {
            newPence=0;
        }
        compstring='9';
        for (var i=1; i <=dp-1;i++) {
            if (eval(newPence) <= eval(compstring)) newPence='0'+newPence;
            compstring=compstring+'9';
        }
        if (dp>0) {
            if (newPence==eval(dec)) { newPounds++; newPence=0; }
            newString=isMinus+pt+newPounds+mt+newPence+at;
        } else {
            newString=isMinus+pt+newPounds+at;
        }
        return (newString);
    }		
    
    
    	function showPane(thisPane) {
			//divver = document.getElementById("divComboBoxes");
			
			qtydisc = document.getElementById("xQtyDiscSelect");
			pricecomb = document.getElementById("xPriceCombSelect");
			attcomb = document.getElementById("xAttCombSelect");
			oneoff = document.getElementById("xOneOffSelect");
			
			APextrafields = document.getElementById("xAPextrafields");
			APattributes = document.getElementById("xAPattributes");
			APprices = document.getElementById("xAPprices");
			APqty = document.getElementById("xAPqty");
			APcustomer = document.getElementById("xAPcustomer");
			APStockExclude = document.getElementById("xAPStockExclude");
			
			qtydisc.style.display = "none";
			pricecomb.style.display = "none";
			attcomb.style.display = "none";
			oneoff.style.display = "none";
			qtydisc.style.visibility = "hidden";
			pricecomb.style.visibility = "hidden";
			attcomb.style.visibility = "hidden";
			oneoff.style.visibility = "hidden";
			
			if (thisPane== "xQtyDiscSelect") {
				qtydisc.style.display = "inline";
				qtydisc.style.visibility = "visible";
				APextrafields.style.visibility = "hidden";
				APattributes.style.visibility = "hidden";
				APprices.style.visibility = "visible";
				APqty.style.visibility = "visible";
				APcustomer.style.visibility = "visible";
				APStockExclude.style.visibility = "hidden";
				qtydisc.selectedIndex = -1;
			}
			if (thisPane== "xPriceCombSelect") {
				pricecomb.style.display = "inline";
				pricecomb.style.visibility = "visible";
				APextrafields.style.visibility = "visible";
				APattributes.style.visibility = "hidden";
				APprices.style.visibility = "visible";
				APqty.style.visibility = "visible";
				APcustomer.style.visibility = "visible";
				APStockExclude.style.visibility = "hidden";
				pricecomb.selectedIndex = -1;
			}
			if (thisPane== "xAttCombSelect") {
				attcomb.style.display = "inline";
				attcomb.style.visibility = "visible";
				APextrafields.style.visibility = "visible";
				APattributes.style.visibility = "visible";
				APprices.style.visibility = "hidden";
				APqty.style.visibility = "hidden";
				APcustomer.style.visibility = "hidden";
				APStockExclude.style.visibility = "hidden";
				pricecomb.selectedIndex = -1;
			}
			if (thisPane== "xOneOffSelect") {
				oneoff.style.display = "inline";
				oneoff.style.visibility = "visible";
				APextrafields.style.visibility = "visible";
				APattributes.style.visibility = "hidden";
				APprices.style.visibility = "visible";
				APqty.style.visibility = "hidden";
				APcustomer.style.visibility = "visible";
				APStockExclude.style.visibility = "hidden";
				pricecomb.selectedIndex = -1;
			}
			apClear();
		}
		
		
		function apAdd(addMode) {
			thisOption = new Array();
			if (document.detailsForm.radio1[0].checked == true) {
				thisBox = document.getElementById("xQtyDiscSelect");
				qtyFrom = document.detailsForm.xAPfrom.value;
				qtyTo = document.detailsForm.xAPto.value;
				percentage = document.detailsForm.xAPpercent.value;
				custAcc = document.detailsForm.xAPcustomerbox.options[document.detailsForm.xAPcustomerbox.selectedIndex].value;
				custAccText = document.detailsForm.xAPcustomerbox.options[document.detailsForm.xAPcustomerbox.selectedIndex].text;			
				for (f = 0; f < currCount; f++) {
					eval("thisPrice"+currArray[f]["currencyID"]+" = document.detailsForm.xAPprice"+currArray[f]["currencyID"]+".value;");
				}
				if (eval(qtyFrom) < 1 || eval(qtyTo) < 1) {
					rc=alert("Please enter valid quantities.");
				} else {
					if (percentage != 0) { 
						optionText = "Customer: "+custAccText+", Qty: "+qtyFrom+"-"+qtyTo+", Percent: "+percentage+"%";
						for (f = 0; f < currCount; f++) {
							eval("thisPrice"+currArray[f]["currencyID"]+" = 0;");
						}
					} else {
						percentage = 0;
						optionText = "Customer: "+custAccText+", Qty: "+qtyFrom+"-"+qtyTo+", Price: ";
						for (f = 0; f < currCount; f++) {
							eval("optionText = optionText + ' ' + presentValue(thisPrice"+currArray[f]["currencyID"]+","+currArray[f]["decimals"]+",'"+currArray[f]["pretext"]+"','"+currArray[f]["middletext"]+"','"+currArray[f]["posttext"]+"');");
						}
					}
					thisOption["advID"] = 0;
					thisOption["productID"] = 0;
					thisOption["accTypeID"] = custAcc;
					thisOption["percentage"] = percentage;
					for (f = 0; f < currCount; f++) {
						eval("thisOption['price"+currArray[f]["currencyID"]+"'] = thisPrice"+currArray[f]["currencyID"]+";");
					}
					thisOption["qtyfrom"] = qtyFrom;
					thisOption["qtyto"] = qtyTo;

					if (addMode == 0) {
						thisBox.options[thisBox.options.length] = new Option(optionText,implodeQueryString(thisOption));
						theIndex = thisBox.options.length -1;					
					} else {
						thisQS = explodeQueryString(thisBox.options[thisBox.options.selectedIndex].value);
						thisOption["advID"] = thisQS["advID"];
						theIndex = thisBox.options.selectedIndex;
						thisBox.options[thisBox.options.selectedIndex] = new Option(optionText,implodeQueryString(thisOption));
					}
					apRecalculate();
					apClear();
				}
			}
			if (document.detailsForm.radio1[1].checked == true) {
				thisBox = document.getElementById("xPriceCombSelect");
				qtyFrom = document.detailsForm.xAPfrom.value;
				qtyTo = document.detailsForm.xAPto.value;
				percentage = document.detailsForm.xAPpercent.value;
				custAcc = document.detailsForm.xAPcustomerbox.options[document.detailsForm.xAPcustomerbox.selectedIndex].value;
				custAccText = document.detailsForm.xAPcustomerbox.options[document.detailsForm.xAPcustomerbox.selectedIndex].text;			
				for (f = 0; f < currCount; f++) {
					eval("thisPrice"+currArray[f]["currencyID"]+" = document.detailsForm.xAPprice"+currArray[f]["currencyID"]+".value;");
				}
				if (false) {
					rc=alert("Please enter valid quantities.");
				} else {
					optionText = "Customer: "+custAccText;
					extraText = "";
					for (f = 0; f < efCount; f++) {
						if (efArray[f]["type"] == "SELECT" || efArray[f]["type"] == "CHECKBOXES" || efArray[f]["type"] == "RADIOBUTTONS") {
							thisEFBox = document.getElementById("xAPef"+efArray[f]["extraFieldID"]);
							thisEF = thisEFBox.options[thisEFBox.selectedIndex].value;
							thisEFtext = thisEFBox.options[thisEFBox.selectedIndex].text;
							eval("thisOption['extrafield"+efArray[f]["extraFieldID"]+"'] = thisEF;");
							eval("thisOption['textrafield"+efArray[f]["extraFieldID"]+"'] = thisEFtext;");
							optionText = optionText + ", "+efArray[f]["name"]+": "+thisEFtext;
						}
					}		
					optionText = optionText +", Qty: "+qtyFrom+"-"+qtyTo;		
					if (percentage != 0) { 
						optionText = optionText + ", Percent: "+percentage+"%";
						for (f = 0; f < currCount; f++) {
							eval("thisPrice"+currArray[f]["currencyID"]+" = 0;");
						}
					} else {
						percentage = 0;
						optionText = optionText + ", Price: ";
						for (f = 0; f < currCount; f++) {
							eval("optionText = optionText + ' ' + presentValue(thisPrice"+currArray[f]["currencyID"]+","+currArray[f]["decimals"]+",'"+currArray[f]["pretext"]+"','"+currArray[f]["middletext"]+"','"+currArray[f]["posttext"]+"');");
						}
					}
					thisOption["advID"] = 0;
					thisOption["productID"] = 0;
					thisOption["accTypeID"] = custAcc;
					thisOption["percentage"] = percentage;
					for (f = 0; f < currCount; f++) {
						eval("thisOption['price"+currArray[f]["currencyID"]+"'] = thisPrice"+currArray[f]["currencyID"]+";");
					}
					thisOption["qtyfrom"] = qtyFrom;
					thisOption["qtyto"] = qtyTo;
					if (addMode == 0) {
						thisBox.options[thisBox.options.length] = new Option(optionText,implodeQueryString(thisOption));
						theIndex = thisBox.options.length -1;					
					} else {
						thisQS = explodeQueryString(thisBox.options[thisBox.options.selectedIndex].value);
						thisOption["advID"] = thisQS["advID"];
						theIndex = thisBox.options.selectedIndex;
						thisBox.options[thisBox.options.selectedIndex] = new Option(optionText,implodeQueryString(thisOption));
					}
					apRecalculate();
					apClear();
				}
			}
			if (document.detailsForm.radio1[2].checked == true) {
				thisBox = document.getElementById("xAttCombSelect");
				percentage = document.detailsForm.xAPpercent.value;
				theContent = document.detailsForm.xAPcontent.value;
				if (document.detailsForm.xAPStockExcludeZero.checked == true) {
					theExclude = "Y";
					exBit = " (X)";
				} else {
					theExclude = "N";
					exBit = "";
				}
				combType = document.detailsForm.xAPattribute.options[document.detailsForm.xAPattribute.selectedIndex].value;
				combTypeText = document.detailsForm.xAPattribute.options[document.detailsForm.xAPattribute.selectedIndex].text;
				if (combType != "E" && theContent == "") {
					rc=alert("Please enter a value for this attribute.");
				} else {
					optionText = combTypeText;
					if (combType != "E") {
						optionText = optionText + "="+theContent;
					}
					if (combType == "S") {
						optionText = optionText + exBit;
					}
					extraText = "";
					for (f = 0; f < efCount; f++) {
						if (efArray[f]["type"] == "SELECT" || efArray[f]["type"] == "CHECKBOXES" || efArray[f]["type"] == "RADIOBUTTONS") {
							thisEFBox = document.getElementById("xAPef"+efArray[f]["extraFieldID"]);
							thisEF = thisEFBox.options[thisEFBox.selectedIndex].value;
							thisEFtext = thisEFBox.options[thisEFBox.selectedIndex].text;
							eval("thisOption['extrafield"+efArray[f]["extraFieldID"]+"'] = thisEF;");
							eval("thisOption['textrafield"+efArray[f]["extraFieldID"]+"'] = thisEFtext;");
							optionText = optionText + ", "+efArray[f]["name"]+": "+thisEFtext;
						}
					}				
					thisOption["combID"] = 0;
					thisOption["productID"] = 0;
					thisOption["type"] = combType;
					if (combType != "E") {
						thisOption["content"] = theContent;
					}
					if (combType == "S") {
						thisOption["exclude"] = theExclude;
					}
					if (addMode == 0) {
						thisBox.options[thisBox.options.length] = new Option(optionText,implodeQueryString(thisOption));
						theIndex = thisBox.options.length -1;					
					} else {
						thisQS = explodeQueryString(thisBox.options[thisBox.options.selectedIndex].value);
						thisOption["combID"] = thisQS["combID"];
						theIndex = thisBox.options.selectedIndex;
						thisBox.options[thisBox.options.selectedIndex] = new Option(optionText,implodeQueryString(thisOption));
					}
					apRecalculate();
					apClear();
				}
				
			}
			if (document.detailsForm.radio1[3].checked == true) {
				thisBox = document.getElementById("xOneOffSelect");
				percentage = document.detailsForm.xAPpercent.value;
				custAcc = document.detailsForm.xAPcustomerbox.options[document.detailsForm.xAPcustomerbox.selectedIndex].value;
				custAccText = document.detailsForm.xAPcustomerbox.options[document.detailsForm.xAPcustomerbox.selectedIndex].text;			
				for (f = 0; f < currCount; f++) {
					eval("thisPrice"+currArray[f]["currencyID"]+" = document.detailsForm.xAPprice"+currArray[f]["currencyID"]+".value;");
				}
				if (false) {
					rc=alert("Please enter valid quantities.");
				} else {
					optionText = "Customer: "+custAccText;
					extraText = "";
					for (f = 0; f < efCount; f++) {
						if (efArray[f]["type"] == "SELECT" || efArray[f]["type"] == "CHECKBOXES" || efArray[f]["type"] == "RADIOBUTTONS") {
							thisEFBox = document.getElementById("xAPef"+efArray[f]["extraFieldID"]);
							thisEF = thisEFBox.options[thisEFBox.selectedIndex].value;
							thisEFtext = thisEFBox.options[thisEFBox.selectedIndex].text;
							eval("thisOption['extrafield"+efArray[f]["extraFieldID"]+"'] = thisEF;");
							eval("thisOption['textrafield"+efArray[f]["extraFieldID"]+"'] = thisEFtext;");
							optionText = optionText + ", "+efArray[f]["name"]+": "+thisEFtext;
						}
					}				
					if (percentage != 0) { 
						optionText = optionText + ", Percent: "+percentage+"%";
						for (f = 0; f < currCount; f++) {
							eval("thisPrice"+currArray[f]["currencyID"]+" = 0;");
						}
					} else {
						percentage = 0;
						optionText = optionText + ", Price: ";
						for (f = 0; f < currCount; f++) {
							eval("optionText = optionText + ' ' + presentValue(thisPrice"+currArray[f]["currencyID"]+","+currArray[f]["decimals"]+",'"+currArray[f]["pretext"]+"','"+currArray[f]["middletext"]+"','"+currArray[f]["posttext"]+"');");
						}
					}
					thisOption["advID"] = 0;
					thisOption["productID"] = 0;
					thisOption["accTypeID"] = custAcc;
					thisOption["percentage"] = percentage;
					for (f = 0; f < currCount; f++) {
						eval("thisOption['price"+currArray[f]["currencyID"]+"'] = thisPrice"+currArray[f]["currencyID"]+";");
					}
					if (addMode == 0) {
						thisBox.options[thisBox.options.length] = new Option(optionText,implodeQueryString(thisOption));
						theIndex = thisBox.options.length -1;					
					} else {
						thisQS = explodeQueryString(thisBox.options[thisBox.options.selectedIndex].value);
						thisOption["advID"] = thisQS["advID"];
						theIndex = thisBox.options.selectedIndex;
						thisBox.options[thisBox.options.selectedIndex] = new Option(optionText,implodeQueryString(thisOption));
					}
					apRecalculate();
					apClear();
				}
			}
		}
		
		function apDelete() {
			if (document.detailsForm.radio1[0].checked == true) {
				thisBox = document.getElementById("xQtyDiscSelect");
				thisDelete = document.getElementById("xQtyDiscDelete");
			}
			if (document.detailsForm.radio1[1].checked == true) {
				thisBox = document.getElementById("xPriceCombSelect");
				thisDelete = document.getElementById("xPriceCombDelete");
			}
			if (document.detailsForm.radio1[2].checked == true) {
				thisBox = document.getElementById("xAttCombSelect");
				thisDelete = document.getElementById("xAttCombDelete");
			}
			if (document.detailsForm.radio1[3].checked == true) {
				thisBox = document.getElementById("xOneOffSelect");
				thisDelete = document.getElementById("xOneOffDelete");
			}		
			theSelected = thisBox.selectedIndex;
			theValue = thisBox.options[theSelected].value;
			if (theSelected == -1) {
				rc=alert("Please select an option to delete first.");
			} else {
				thisBox.options[theSelected] = null;
				thisDelete.value = thisDelete.value + theValue + "|";
				apRecalculate();
				apClear();
			}			
		}
		
		function apShow() {
			if (document.detailsForm.radio1[0].checked == true) {
				thisBox = document.getElementById("xQtyDiscSelect");
				thisValue = explodeQueryString(thisBox.options[thisBox.selectedIndex].value);
				customerBox = document.getElementById("xAPcustomerbox");
				for (f=0; f < customerBox.options.length; f++) {
					if (customerBox.options[f].value == thisValue["accTypeID"]) {
						customerBox.selectedIndex = f;
					}
				}
				for (f = 0; f < currCount; f++) {
					eval("document.detailsForm.xAPprice"+currArray[f]["currencyID"]+".value = thisValue['price"+currArray[f]["currencyID"]+"'];");
				}
				document.detailsForm.xAPfrom.value = thisValue["qtyfrom"];				
				document.detailsForm.xAPto.value = thisValue["qtyto"];
				document.detailsForm.xAPpercent.value = thisValue["percentage"];
			}
			if (document.detailsForm.radio1[1].checked == true) {
				thisBox = document.getElementById("xPriceCombSelect");
				thisValue = explodeQueryString(thisBox.options[thisBox.selectedIndex].value);
				customerBox = document.getElementById("xAPcustomerbox");
				for (f=0; f < customerBox.options.length; f++) {
					if (customerBox.options[f].value == thisValue["accTypeID"]) {
						customerBox.selectedIndex = f;
					}
				}
				for (f = 0; f < currCount; f++) {
					eval("document.detailsForm.xAPprice"+currArray[f]["currencyID"]+".value = thisValue['price"+currArray[f]["currencyID"]+"'];");
				}
				for (f = 0; f < efCount; f++) {
					if (efArray[f]["type"] == "SELECT" || efArray[f]["type"] == "CHECKBOXES" || efArray[f]["type"] == "RADIOBUTTONS") {
						thisEFBox = document.getElementById("xAPef"+efArray[f]["extraFieldID"]);
						for (g = 0; g < thisEFBox.length; g++) {
							if (thisEFBox.options[g].value == thisValue["extrafield"+efArray[f]["extraFieldID"]]) {
								thisEFBox.options[g].selected = true;
							}
						}
					}
				}					
				document.detailsForm.xAPfrom.value = thisValue["qtyfrom"];				
				document.detailsForm.xAPto.value = thisValue["qtyto"];
				document.detailsForm.xAPpercent.value = thisValue["percentage"];			
			}
			if (document.detailsForm.radio1[2].checked == true) {
				thisBox = document.getElementById("xAttCombSelect");
				thisValue = explodeQueryString(thisBox.options[thisBox.selectedIndex].value);
				attributeBox = document.getElementById("xAPattribute");
				for (f=0; f < attributeBox.options.length; f++) {
					if (attributeBox.options[f].value == thisValue["type"]) {
						attributeBox.selectedIndex = f;
					}
				}
				if (thisValue["exclude"] == "Y") {
					document.detailsForm.xAPStockExcludeZero.checked = true;
				} else {
					document.detailsForm.xAPStockExcludeZero.checked = false;
				}
				for (f = 0; f < efCount; f++) {
					if (efArray[f]["type"] == "SELECT" || efArray[f]["type"] == "CHECKBOXES" || efArray[f]["type"] == "RADIOBUTTONS") {
						thisEFBox = document.getElementById("xAPef"+efArray[f]["extraFieldID"]);
						for (g = 0; g < thisEFBox.length; g++) {
							if (thisEFBox.options[g].value == thisValue["extrafield"+efArray[f]["extraFieldID"]]) {
								thisEFBox.options[g].selected = true;
							}
						}
					}
				}					
				document.detailsForm.xAPcontent.value = thisValue["content"];			
			}	
			if (document.detailsForm.radio1[3].checked == true) {
				thisBox = document.getElementById("xOneOffSelect");
				thisValue = explodeQueryString(thisBox.options[thisBox.selectedIndex].value);
				customerBox = document.getElementById("xAPcustomerbox");
				for (f=0; f < customerBox.options.length; f++) {
					if (customerBox.options[f].value == thisValue["accTypeID"]) {
						customerBox.selectedIndex = f;
					}
				}
				for (f = 0; f < currCount; f++) {
					eval("document.detailsForm.xAPprice"+currArray[f]["currencyID"]+".value = thisValue['price"+currArray[f]["currencyID"]+"'];");
				}
				for (f = 0; f < efCount; f++) {
					if (efArray[f]["type"] == "SELECT" || efArray[f]["type"] == "CHECKBOXES" || efArray[f]["type"] == "RADIOBUTTONS") {
						thisEFBox = document.getElementById("xAPef"+efArray[f]["extraFieldID"]);
						for (g = 0; g < thisEFBox.length; g++) {
							if (thisEFBox.options[g].value == thisValue["extrafield"+efArray[f]["extraFieldID"]]) {
								thisEFBox.options[g].selected = true;
							}
						}
					}
				}					
				document.detailsForm.xAPpercent.value = thisValue["percentage"];			
			}
			applyButton = document.getElementById("xAPApplyButton");
			applyButton.style.visibility = "visible";	
			attributeStockDiv();
		}
		
		function attributeStockDiv() {
			currentlySelected = document.detailsForm.xAPattribute.options[document.detailsForm.xAPattribute.selectedIndex].value;
			if (currentlySelected == "S") {
				thisDiv = document.getElementById("xAPStockExclude");
				thisDiv.style.visibility = "visible";						
			} else {
				thisDiv = document.getElementById("xAPStockExclude");
				thisDiv.style.visibility = "hidden";
			}
		}
		
		function apClear() {
			applyButton = document.getElementById("xAPApplyButton");
			applyButton.style.visibility = "hidden";
			thisElement = document.getElementById("xAPStockExclude");
			thisElement.style.visibility = "hidden";
			document.detailsForm.xAPStockExcludeZero.checked = false;
			document.detailsForm.xAPattribute.selectedIndex = 0;
			document.detailsForm.xAPpercent.value = 0;
			document.detailsForm.xAPcontent.value = 0;
			for (f = 0; f < currCount; f++) {
				eval("document.detailsForm.xAPprice"+currArray[f]["currencyID"]+".value = 0;");
			}
			for (f = 0; f < efCount; f++) {
				if (efArray[f]["type"] == "SELECT" || efArray[f]["type"] == "CHECKBOXES" || efArray[f]["type"] == "RADIOBUTTONS") {
					thisEFBox = document.getElementById("xAPef"+efArray[f]["extraFieldID"]);
					thisEFBox.selectedIndex = 0;
				}
			}				
			document.detailsForm.xAPfrom.value = 0;
			document.detailsForm.xAPto.value = 0;
			document.detailsForm.xAPcustomerbox.selectedIndex = 0;
		}
		
		function apRecalculate() {
			if (document.detailsForm.radio1[0].checked == true) {
				thisBox = document.getElementById("xQtyDiscSelect");
				thisSend = document.getElementById("xQtyDiscSend");
			}
			if (document.detailsForm.radio1[1].checked == true) {
				thisBox = document.getElementById("xPriceCombSelect");
				thisSend = document.getElementById("xPriceCombSend");
			}
			if (document.detailsForm.radio1[2].checked == true) {
				thisBox = document.getElementById("xAttCombSelect");
				thisSend = document.getElementById("xAttCombSend");
			}
			if (document.detailsForm.radio1[3].checked == true) {
				thisBox = document.getElementById("xOneOffSelect");
				thisSend = document.getElementById("xOneOffSend");
			}
			thisValue = "";
			for (f = 0; f < thisBox.options.length; f++) {
				thisValue = thisValue + thisBox.options[f].value+"|";
			}
			thisSend.value = thisValue;
		}