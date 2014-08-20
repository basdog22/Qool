/*
----------------------------------------------------------
Image Text for Qool CMS
-----------------------------------------------------------
*/



picEditMenu.prototype.saver = function(){

	var _PICEDITOR = this.editor;

	// adding the buttons and other stuff required


	var btngroup = document.createElement('div');
	var button = document.createElement('span');
	var drpbutton = '<button class="btn dropdown-toggle btn-success" data-toggle="dropdown">File <span class="caret"></span></button>';
	var sbutton = document.createElement('button');
	var dbutton = document.createElement('button');
	var btnlist = document.createElement('ul');
	var btnlistitem1 = document.createElement('li');
	var btnlistitem2 = document.createElement('li');


	

	// adding the events
	dbutton.onclick = function(){
		var cnv = _PICEDITOR.canvas;
		var data = cnv.toDataURL();
		var xhr = new XMLHttpRequest();
		var filename = _PICEDITOR.imageFile;
		filename = filename.replace(qool_url+"/uploads/","");
		var fd = new FormData();
		fd.append('file', data);
		fd.append('name', filename);
		xhr.open('POST', qool_url+"/admin/gallerysaveimage", true);
		xhr.setRequestHeader("X-Requested-With","XMLHttpRequest");
		xhr.send(fd);
	};
	sbutton.onclick = function(){
		var filename = _PICEDITOR.imageFile;
		filename = filename.replace(qool_url+"/uploads/","");
		var text=prompt("Save as...",filename);
		if (text!=null && text!=""){
			var cnv = _PICEDITOR.canvas;
			var data = cnv.toDataURL();
			var xhr = new XMLHttpRequest();
			
			var fd = new FormData();
			fd.append('file', data);
			fd.append('name', text);
			xhr.open('POST', qool_url+"/admin/gallerysaveimage", true);
			xhr.setRequestHeader("X-Requested-With","XMLHttpRequest");
			xhr.send(fd);
		}
	};
	
	button.innerHTML = 'Save here: ';
	sbutton.innerHTML = 'Save as...';
	dbutton.innerHTML = 'Save';
	//add style
	btngroup.className = 'btn-group dropup';
	sbutton.className = 'pull-left btn btn-success';

	btnlist.className = 'dropdown-menu pull-right';
	button.className = 'label label-info help-inline';
	dbutton.className = 'btn  saver btn-success';

	btnlistitem1.appendChild(sbutton);
	btnlistitem2.appendChild(dbutton);
	btnlist.appendChild(btnlistitem2);
	btnlist.appendChild(btnlistitem1);
//	btngroup.appendChild(button);
	btngroup.innerHTML +=drpbutton;
	btngroup.appendChild(btnlist);


	// attaching the button to the menu

	document.getElementById(this.id).appendChild(btngroup);

}