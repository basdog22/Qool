/*
----------------------------------------------------------
Image Text for Qool CMS
-----------------------------------------------------------
*/



picEditMenu.prototype.undo = function(){

	var _PICEDITOR = this.editor;
	var Filters = {};
	Filters.tmpCanvas = document.createElement('canvas');
	Filters.tmpCtx = Filters.tmpCanvas.getContext('2d');

	Filters.createImageData = function(data,w,h) {
		this.tmpCtx.putImageData(data,0,0,0,0,w,h);
	};
	Filters.createImageData(this.editor.context.getImageData(0,0,this.editor.imgWidth,this.editor.imgHeight),this.editor.imgWidth,this.editor.imgHeight);
	 var tmp = Filters.tmpCtx.getImageData(0,0,this.editor.imgWidth,this.editor.imgHeight);
	// adding the buttons and other stuff required
	
	var button = document.createElement('button');
	button.innerHTML = 'Undo';
	//add style
	button.className = 'btn';

	// adding the events
	button.onclick = function(){
		this.editor = _PICEDITOR;
		var newtmp = tmp;
		this.editor.context.putImageData(newtmp,0,0,0,0,this.editor.imgWidth,this.editor.imgHeight);	
	};

	// attaching the button to the menu

	document.getElementById(this.id).appendChild(button);

}