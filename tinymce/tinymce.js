function init() {
	tinyMCEPopup.resizeToInnerSize();
}


function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

function SponsorsSlideshowInsertLink() {
	
	var tagtext;
	
	var source = document.getElementById('source').value;
	var category = document.getElementById('category').value;
	var order = document.getElementById('order').value;
	var width = document.getElementById('width').value;
	var height = document.getElementById('height').value;
	var timeout = document.getElementById('timeout').value;
	var speed = document.getElementById('speed').value;
	var fade = document.getElementById('fade').value;
	var navigation_arrows = getCheckedValue(document.getElementById('navigation_arrows'));
	var navigation_pager = getCheckedValue(document.getElementById('navigation_pager'));
	var bounding_box = getCheckedValue(document.getElementById('bounding_box'));
	var alignment = document.getElementById('alignment').value;
	var post_excerpt_length = document.getElementById('post_excerpt_length').value;
	
	if (bounding_box == 1)
		bounding_box = 'true';
	else
		bounding_box = 'false';
	

	if (post_excerpt_length != "")
		post_excerpt_length = ' post_excerpt_length=' + post_excerpt_length;
	
	tagtext = "[slideshow source=" + source + " category=" + category + " random=" + order + " width=" + width + " height=" + height + " timeout=" + timeout + " speed=" + speed + " fade=" + fade + " show_navigation_arrows=" + navigation_arrows + " show_pager=" + navigation_pager + " box=" + bounding_box + " align=" + alignment + post_excerpt_length + "]";

	if(window.tinyMCE) {
		/* get the TinyMCE version to account for API diffs */
		var tmce_ver=window.tinyMCE.majorVersion;
		
		if (tmce_ver>="4") {
			window.tinyMCE.execCommand('mceInsertContent', false, tagtext);
		} else {
			window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
		}
		//Peforms a clean up of the current editor HTML. 
		//tinyMCEPopup.editor.execCommand('mceCleanup');
		//Repaints the editor. Sometimes the browser has graphic glitches. 
		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.close();
	}
	return;
}
