/*
----------------------------------------------------------
	Image Grayscaling Library including the menu options
-----------------------------------------------------------
*/


// menu for the grayscale options

picEditMenu.prototype.grayscale = function(){
	
	var _PICEDITOR = this.editor;
	
	// adding the buttons and other stuff required
	
	var button = document.createElement('button');
		button.innerHTML = 'Grayscale';
	//add style
	button.className = 'btn';
	// adding the events 
	button.onclick = function(){
		
		this.editor = _PICEDITOR;
		
		// reading the imagedata from canvas
		
		var imgdata = this.editor.context.getImageData(0,0,this.editor.imgWidth,this.editor.imgHeight);
		
		// converting imgdata into grayscale
		
		for(i=0; i<imgdata.width*imgdata.height*4; i+=4)
		{
				gray = (imgdata.data[i]+imgdata.data[i+1]+imgdata.data[i+2])/3;
				
				imgdata.data[i] = gray;
				imgdata.data[i+1] = gray;
				imgdata.data[i+2] = gray;
		}
	
		// loading the imagedata onto the canvas again
	
		this.editor.context.putImageData(imgdata,0,0,0,0,this.editor.imgWidth,this.editor.imgHeight);
	};
		
	// attaching the button to the menu
	
	document.getElementById(this.id).appendChild(button);
	
}



