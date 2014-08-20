/*
-------------------------------------------------------------------------------
	Default Canvas Builder Plugin  - Used to build the canvas for editing
-------------------------------------------------------------------------------
*/


// default function called at the beginning

function picEditor(width,height,id){

	this.width = width;
	this.height = height;
	
	this.id = id;
	
	this.imgWidth = width;
	this.imgHeight = height;
	
	this.loadMyCanvas();
	this.addMyCanvas();

}

// loads the canvas into the object as property

picEditor.prototype.loadMyCanvas = function(){

	// creating the canvas and adding as the object property
	
	picEditor.prototype.canvas = document.createElement('canvas');
	
	// adding specific width and height
	
	this.canvas.width = this.width;
	this.canvas.height = this.height;
	
	// generating the 2d context
	this.context = this.canvas.getContext('2d');

}

// appends the canvas into the given element if possible

picEditor.prototype.addMyCanvas = function(){
	
	// adding the canvas property to the element
	
	document.getElementById(this.id).appendChild(this.canvas);
	
}

// loads the image into the canvas given

picEditor.prototype.loadImage = function(editor,url){

	var myEditor = editor;
	
	// creating a pseudo image element
	
	var img = document.createElement('img');
		img.src = url;
		
	// after the image loads
	this.imageFile = url;
	img.onload = function(){
	
		// setting the editor
		
		this.editor = myEditor;
		
		// managing the image width and height according to the canvas
		
		this.imgWidth = img.width;
		this.imgHeight = img.height;
		
		if( this.imgWidth > this.editor.width ){
			
			// adjusting the width first
			this.imgHeight = (this.editor.width*this.imgHeight)/this.imgWidth;
			this.imgWidth = this.editor.width;
			
		}
		
		if( this.imgHeight > this.editor.height ){
		
			// adjusting the height first
			this.imgWidth = (this.editor.height*this.imgWidth)/this.imgHeight;
			this.imgHeight = this.editor.height;
		
		}
		
		
		// loading the image into the canvas
	
		this.editor.context.drawImage(img,0,0,this.imgWidth,this.imgHeight);


	}

	
}


// defining the menu to be used by the libraries

function picEditMenu(editor,id){

	// setting defaults required
	
	this.id = id;
	
	this.editor = editor;

}





























