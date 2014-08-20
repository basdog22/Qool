/*
----------------------------------------------------------
Image Text for Qool CMS
-----------------------------------------------------------
*/



picEditMenu.prototype.text = function(){

	var _PICEDITOR = this.editor;
	
	// adding the buttons and other stuff required

	var button = document.createElement('button');
	button.innerHTML = 'Add Text';
	//add style
	button.className = 'btn';

	// adding the events
	button.onclick = function(){
		this.editor = _PICEDITOR;

		var text=prompt("Please type your text","Qool CMS");
		if (text!=null && text!=""){
			this.editor.context.fillStyle = "#fff";
			var font = "110px Coda, Helvetica, sans-serif";
			this.editor.context.shadowColor = "#000";
			// Specify the shadow offset.
			this.editor.context.shadowOffsetX = 1;
			this.editor.context.shadowOffsetY = 1;
			this.editor.context.font = font;
			this.editor.context.shadowBlur = 1;
			this.editor.context.fillText(text, 5, 100);
		}
	};

	// attaching the button to the menu

	document.getElementById(this.id).appendChild(button);

}