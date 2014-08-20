/*
----------------------------------------------------------
	Image Brightness Library including the menu options
-----------------------------------------------------------
*/


// menu for the brightness options

picEditMenu.prototype.brightness = function(){
	
	var _PICEDITOR = this.editor;
	
	// adding the buttons and other stuff required
	
	var buttonAdd = document.createElement('button');
		buttonAdd.innerHTML = '+';
		
	var buttonSub = document.createElement('button');
		buttonSub.innerHTML = '-';
		
	var input = document.createElement('input');
		input.setAttribute('type','text');
		input.value = 0;
		input.disabled = true;
	// adding the events 
	
	buttonAdd.onclick = function(){
		
		this.editor = _PICEDITOR;
		
		// getting the brightness value
		
		if( input.value == 100 ) alert('max value reached');
		else{
			
			input.value = parseInt(input.value) + 1;
		
			this.editor.brightness(input.value);
		
		}
		
	}
	
	buttonSub.onclick = function(){
	
		this.editor = _PICEDITOR;
		
		// getting the brightness value
		
		if( input.value == -100 ) alert('min value reached');
		else{
			
			input.value = parseInt(input.value) - 1;
		
			this.editor.brightness(input.value);
		
		}
	
	}
	
	// attaching the button to the menu
	
	document.getElementById(this.id).appendChild(input);
	document.getElementById(this.id).appendChild(buttonAdd);
	document.getElementById(this.id).appendChild(buttonSub);
	
}


picEditor.prototype.brightness = function(percent){

	var val = parseFloat(percent/100);
	
	// reading the imagedata from canvas
		
	var imgdata = this.context.getImageData(0,0,this.imgWidth,this.imgHeight);
	
	if(val > 0 ){
	
		for(i=0; i<imgdata.width*imgdata.height*4; i+=4)
		{
			imgdata.data[i] += (255-imgdata.data[i])*val;
			imgdata.data[i+1] += (255-imgdata.data[i+1])*val;
			imgdata.data[i+2] += (255-imgdata.data[i+2])*val;
		}
		
	}
	
	else if(val < 0 ){
	
		for(i=0; i<imgdata.width*imgdata.height*4; i+=4)
		{
			imgdata.data[i] += (imgdata.data[i])*val;
			imgdata.data[i+1] += (imgdata.data[i+1])*val;
			imgdata.data[i+2] += (imgdata.data[i+2])*val;
		}
		
	}
	
	// loading the imagedata onto the canvas again
	
	this.context.putImageData(imgdata,0,0,0,0,this.imgWidth,this.imgHeight);

}


