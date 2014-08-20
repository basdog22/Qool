/*
----------------------------------------------------------
Image Threshold for Qool CMS
-----------------------------------------------------------
*/


// menu for the undo options

picEditMenu.prototype.threshold = function(){

	var _PICEDITOR = this.editor;

	// adding the buttons and other stuff required

	var button = document.createElement('button');
	button.innerHTML = 'Invert';
	//add style
	button.className = 'btn';

	// adding the events
	button.onclick = function(){
		this.editor = _PICEDITOR;

		var imgdata = this.editor.context.getImageData(0,0,this.editor.imgWidth,this.editor.imgHeight);

		// converting imgdata into grayscale
		var d = imgdata.data;
		for (var i=0; i<d.length; i+=4) {
			var r = d[i];
			var g = d[i+1];
			var b = d[i+2];
			var v = (0.2126*r + 0.7152*g + 0.0722*b >= 140) ? 255 : 0;
			d[i] = d[i+1] = d[i+2] = v
		}
		imgdata.data = d;
		this.editor.context.putImageData(imgdata,0,0,0,0,this.editor.imgWidth,this.editor.imgHeight);
	};

	// attaching the button to the menu

	document.getElementById(this.id).appendChild(button);

}




