/*
----------------------------------------------------------
Image Threshold for Qool CMS
-----------------------------------------------------------
*/




// menu for the undo options

picEditMenu.prototype.blur = function(){

	var _PICEDITOR = this.editor;
	var Filters = {};
	Filters.tmpCanvas = document.createElement('canvas');
	Filters.tmpCtx = Filters.tmpCanvas.getContext('2d');

	Filters.createImageData = function(w,h) {
		return this.tmpCtx.createImageData(w,h);
	};


	Filters.convolute = function(pixels, weights, opaque) {
		var side = Math.round(Math.sqrt(weights.length));
		var halfSide = Math.floor(side/2);
		var src = pixels.data;
		var sw = pixels.width;
		var sh = pixels.height;
		// pad output by the convolution matrix
		var w = sw;
		var h = sh;
		var output = Filters.createImageData(w, h);
		var dst = output.data;
		// go through the destination image pixels
		var alphaFac = opaque ? 1 : 0;
		for (var y=0; y<h; y++) {
			for (var x=0; x<w; x++) {
				var sy = y;
				var sx = x;
				var dstOff = (y*w+x)*4;
				// calculate the weighed sum of the source image pixels that
				// fall under the convolution matrix
				var r=0, g=0, b=0, a=0;
				for (var cy=0; cy<side; cy++) {
					for (var cx=0; cx<side; cx++) {
						var scy = sy + cy - halfSide;
						var scx = sx + cx - halfSide;
						if (scy >= 0 && scy < sh && scx >= 0 && scx < sw) {
							var srcOff = (scy*sw+scx)*4;
							var wt = weights[cy*side+cx];
							r += src[srcOff] * wt;
							g += src[srcOff+1] * wt;
							b += src[srcOff+2] * wt;
							a += src[srcOff+3] * wt;
						}
					}
				}
				dst[dstOff] = r;
				dst[dstOff+1] = g;
				dst[dstOff+2] = b;
				dst[dstOff+3] = a + alphaFac*(255-a);
			}
		}
		return output;
	};


	// adding the buttons and other stuff required

	var button = document.createElement('button');
	button.innerHTML = 'Blur';
	//add style
	button.className = 'btn';

	// adding the events
	button.onclick = function(){
		this.editor = _PICEDITOR;

		var imgdata = this.editor.context.getImageData(0,0,this.editor.imgWidth,this.editor.imgHeight);

		// converting imgdata into grayscale
		var d = imgdata;
		d = Filters.convolute(d,[ 1/9, 1/9, 1/9,1/9, 1/9, 1/9,1/9, 1/9, 1/9 ]);
		imgdata = d;
		this.editor.context.putImageData(imgdata,0,0,0,0,this.editor.imgWidth,this.editor.imgHeight);
	};

	// attaching the button to the menu

	document.getElementById(this.id).appendChild(button);

}

