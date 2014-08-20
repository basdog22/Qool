$(document).ready(function(){
	
	$(".qoolmodal").click(function(e) {
		var url = $(this).attr('href');
		$("#modaltitle").html($(this).attr('title'));
		$("#qoolmodal").load(url,{ajaxcalled:1});
		e.preventDefault();
	});
	$(".ttip").tooltip();
	$(".pop").popover({placement:'left'});
	$(".popright").popover({placement:'right'});
	$(".poptop").popover({placement:'top'});
	$('.drg').draggable({
		cursor: 'move',          // sets the cursor apperance
		revert: 'valid',
		revertDuration: 600,
		opacity: 0.5
	});

	$(".navbar-fixed-top .openedpop").popover({placement:'bottom',trigger:'click'});
	$(".sidebar-nav .closedpop").popover({placement:'right',trigger:'hover'});
	$(".navbar-fixed-bottom .openedpop").popover({placement:'top',trigger:'click'});
	$("#showhelp").click(function(e){
		$(".openedpop").popover('toggle');
		var frtop = ($(".popover").css('top').replace('px','')*1)+7;
		$(".popover").css('top',frtop+'px');
	});
	$(".popover").live('hover',function(){
		$(".popover").css('z-index','2');
		$(this).css('z-index','100');
	});
	// sets droppable
	$('.drop').droppable({
		hoverClass: 'well2',
		tolerance: "fit",
		drop: function(event, ui) {
			// after the draggable is droped, hides it with a hide() effect
			$(this).html("<div id='"+$(this).attr('id')+"alert' class='alert'>"+ui.draggable.html()+"<a class='close' data-id='"+$(this).attr('id')+"' data-dismiss='alert' href='#'>&times;</a></div>");
			adminSaveWidget($("#"+$(this).attr('id')+"alert .label").attr('id'),$(this).attr('id'));
		}
	});
	$(".drop .alert").live('closed',function(e){
		var me = e.target;
		var labeldiv = $(me).parents(-1);
		adminSaveWidget('',$(labeldiv).attr('id'));
	});


	$("#formSubmiter").click(function(e){
		$("#themaincontent form").each(function(){
			$(this).submit();
		});
		e.preventDefault();
	});
	$("#toggler").click(function(e){
		tinymce.execCommand('mceToggleEditor',false,'content');
		if($(this).html()=='Visual'){
			$(this).html('HTML');
		}else{
			$(this).html('Visual');
		}
		e.preventDefault();
	});

	

	var myevent;
	$(".warnme").live('click',function(e){

		$('#myModal').modal('show');
		$("#delbutton").show();
		$("#delbutton").attr('href',e.target.href);
		$("#modaltitle").html(e.target.title);
		$("#qoolmodal").html(e.target.rel);
		var goon = false;
		myevent = e;
		$("#delbutton").click(function(){
			window.location = myevent.target.href;
			//$('#myModal').modal('hide');
		});

		myevent.preventDefault();
	});

	$(".input-file").change(function(){
		var input = document.getElementById($(this).attr('id'));
		var list = document.getElementById('filelist');

		//empty list for now...
		while (list.hasChildNodes()) {
			list.removeChild(list.firstChild);
		}
		$('#filelist').attr('class','hidden');
		var totalsize = 0;
		//for every file...
		for (var x = 0; x < input.files.length; x++) {
			//add to list
			var li = document.createElement('li');
			li.innerHTML = '<a href="javascript:void(0)">'+ (x+1) + ') ' +input.files[x].size + ' bytes - ' + input.files[x].name + '</a>';
			list.appendChild(li);
			totalsize = totalsize+input.files[x].size;
		}
		totalsize = totalsize/1024;

		var li = document.createElement('li');
		li.innerHTML = '<a href="javascript:void(0)">_____________________________________</a>';
		list.appendChild(li);
		var li = document.createElement('li');
		li.innerHTML = '<a href="javascript:void(0)"><strong>Total Size: ' + totalsize.toPrecision(6) + 'kb</strong></a>';
		list.appendChild(li);
		$('#filelist').appendTo($(this).parent());
		$('#filelist').removeClass('hidden');
		$('#filelist').addClass('filelistfull');
		$('#filelist').addClass('nav');

		$('#filelist').addClass('nav-tabs');
		$('#filelist').addClass('nav-stacked');

	});

	$(".ajaxdelete").click(function(e){
		$.post(qool_url+"/admin/ajaxdelete", { deleteId: $(this).attr('id'), dbtable: $(this).attr('rev') },function(data) {
			try{
				showNotification('icon.png','Notification',data,'notification'+Math.random());
			}catch(err){
				$("#msg").show(5);
				$("#msg").html(data);
				$("#msg").delay(4000).fadeOut(300);
			}
		});
		$(this).remove();
		e.preventDefault();
	});


	$(".calendarSource").click(function(){
		if($(this).hasClass('removed')){
			$("#calendar").fullCalendar( 'addEventSource', $(this).attr('data-source') );
			$(this).removeClass('removed');
		}else{
			$("#calendar").fullCalendar( 'removeEventSource', $(this).attr('data-source') );
			$(this).addClass('removed');
		}
	});

	//gallery delete
	$('.thumbnails').on('click','.gallery-delete',function(e){
		e.preventDefault();
		//get image id
		var theimage = $(this).parents('.thumbnail').attr('data-src');

		$(this).parents('.thumbnail').fadeOut();
		$.post(qool_url+"/admin/gallerydelete", { deleteId: theimage },function(data) {
			try{
				showNotification('icon.png','Notification',data,'notification'+Math.random());
			}catch(err){
				$("#msg").show(5);
				$("#msg").html(data);
				$("#msg").delay(4000).fadeOut(300);
			}
		});
	});
	//gallery edit
	$('.thumbnails').on('click','.gallery-edit',function(e){

		//get image id
		var theimage = $(this).parents('.thumbnail').attr('data-src');
		var url = 'galleryedit';
		$("#modaltitle").html('Qool Image Editor');
		$("#qoolmodal").load(url,{ajaxcalled:1,image:theimage},function(){
			$("#myModal").addClass('bigmodal');
			$("#myModal").on('shown',function(){
				$(this).find("#editormenu").appendTo(".modal-footer");
			});
			$("#myModal").on('hidden',function(){
				$(this).find("#editormenu").remove();
				$("#myModal").removeClass('bigmodal');
			});
			$('#myModal').modal({
				backdrop: 'static'
			});
		});


		e.preventDefault();
	});




	$("select[rel=taxonomy]").change(function(e){
		var taxarray = [];
		var myidt = $(this).attr('data-myid');
		var objid = $(this).attr('rev');
		var selected = $("#"+$(this).attr('id')+" option:selected");
		for (var x = 0; x < selected.length; x++) {
			taxarray.push(selected[x].value);
		}
		$.post(qool_url+"/admin/ajaxtaxonomyupdate", { objectid: objid, taxonomies: taxarray, myid: myidt },function(data) {
			//$("#tags-label").html(data);
		});

	});

	$("select#icon option").live('hover',function(){
		$("#icon-preview").appendTo('#icon-label label');
		$("#icon-preview").attr('class',$(this).val() + '');
	});

	$(".alert #text").live('dblclick',function(e){
		var me = e.target;
		var labeldiv = $(me).parents(-1);
		var tt = labeldiv[1];

		var url = qool_url+'/admin/loadtextwidget';
		$('#myModal').modal();
		$("#modaltitle").html('Text Contents');
		$("#qoolmodal").load(url,{ajaxcalled:1,textid:$(tt).attr('id')});
	});

	$(".alert #menu").live('dblclick',function(e){
		var me = e.target;
		var labeldiv = $(me).parents(-1);
		var tt = labeldiv[1];

		var url = qool_url+'/admin/loadmenuwidget';
		$('#myModal').modal();
		$("#modaltitle").html('Custom Menu');
		$("#qoolmodal").load(url,{ajaxcalled:1,textid:$(tt).attr('id')});
	});

	$(".alert #feed").live('dblclick',function(e){
		var me = e.target;
		var labeldiv = $(me).parents(-1);
		var tt = labeldiv[1];

		var url = qool_url+'/admin/loadfeedwidget';
		$('#myModal').modal();
		$("#modaltitle").html('Feed Reader');
		$("#qoolmodal").load(url,{ajaxcalled:1,textid:$(tt).attr('id')});
	});

	$("#generatedalert").show(5);
	$("#generatedalert").delay(4000).fadeOut(300);
	$(".imageselector").each(function(){
		var cmdBtn = "<button rel='"+$(this).attr('id')+"' type='button' title='Select image' class='selectorimage btn btn-info'><i class='icon-picture'> </i></button>";
		var myfather = $(this).parent();
		$(myfather).addClass('input-append');
		$(cmdBtn).appendTo(myfather);
	});
	$(".selectorimage").live('click',function(e){
		e.preventDefault();
		elFinderBrowser($(this).attr('rel'),'src','image',window);
	});
	$(".imageselector").live('blur',function(){
		var myval = $(this).val();
		myval = myval.replace(domain_url,'');
		$(this).val(domain_url+myval);
	});

	$("#allownotifications").click(function(){
		authorizeNotifications();
	});

	$("#archivedEvents").live('change',function(event){
		var url = qool_url+'/admin/archivedaudit';
		$("#events_accordion").load(url,{ajaxcalled:1,datestr:$(this).val()});
	});
	//if there is a twitter username

	readTweets();

	interval = setInterval(readTweets,60000);

});

function readTweets(){
	if(twitterUsername!=''){
		var script = document.createElement("script");
		script.id = 'twitterloaded';
		script.src = 'https://api.twitter.com/1/statuses/user_timeline/'+ twitterUsername+'.json?callback=fetchTweets&count=5';
		document.body.appendChild(script);
	}
}

function authorizeNotifications(){
	Notification.requestPermission(function(perm) {
		$.cookie('ALLOW_NOTIFICATIONS',1,{expires:365});
	});
}

function fetchTweets(data) {
	var tweet;
	var i = data.length;
	while (i--) {
		tweet = data[i];
		showNotification(tweet.user.profile_image_url,tweet.user.name,tweet.text,tweet.id);
	}
	$("#twitterloaded").remove();
}
var tData = (JSON.parse( localStorage.getItem('Notifications_Seen') ))? JSON.parse( localStorage.getItem('Twitter_Seen') ): [];

function showNotification(image,title,body,id) {
	if($.cookie('ALLOW_NOTIFICATIONS')>0){
		if($.inArray(id, tData)>-1){
			console.info($.inArray(id, tData));
			return;
		}
		var notification = new Notification(title, {
			iconURL:image,
			dir: "auto",
			lang: "",
			body: body,
			tag: id
		});
		//we don't want our localstorage to go huge... we allow only up to 50 ids.
		if(tData.length>50){
			tData.splice(0,1);
		}
		tData.push(id);
		localStorage.setItem( 'Notifications_Seen', JSON.stringify(tData) );
		notification.ondisplay = function(event) {
			setTimeout(function() {
				event.currentTarget.cancel();
			}, 15000);
		};
		notification.onclick = function() {
			window.focus();
			this.cancel();
		};
	}else{
		throw "Notifications not allowed";
	}
}

function dataURItoBlob(dataURI) {
	var binary = atob(dataURI.split(',')[1]);
	var array = [];
	for (var i = 0; i < binary.length; i++) {
		array.push(binary.charCodeAt(i));
	}
	return new Blob([new Uint8Array(array)], {type: 'image/jpeg'});
}

function adminSaveWidget(widget,slot){
	$.post(qool_url+"/admin/savewidgetstate", { widgetname: widget, slotname: slot },function(data) {
		try{
			showNotification('icon.png','Notification',data,'notification'+Math.random());
		}catch(err){
			$("#msg").show(5);
			$("#msg").html(data);
			$("#msg").delay(4000).fadeOut(300);
		}

	});
}
function elFinderBrowser (field_name, url, type, win) {
	var cmsURL = qool_url+'/'+libs_dir+'/js/elfinder/elfinder.php';    // script URL - use an absolute path!
	if (cmsURL.indexOf("?") < 0) {
		//add the type as the only query parameter
		cmsURL = cmsURL + "?type=" + type;
	}
	else {
		//add the type as an additional query parameter
		// (PHP session ID is now included if there is one at all)
		cmsURL = cmsURL + "&type=" + type;
	}

	tinyMCE.activeEditor.windowManager.open({
		file : cmsURL,
		title : 'Qool File Manager (elfinder)',
		width : 900,
		height : 450,
		resizable : "yes",
		inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
		popup_css : false, // Disable TinyMCE's default popup CSS
		close_previous : "no"
	}, {
		window : win,
		input : field_name
	});
	return false;
}

//html5 uploading
jQuery.event.props.push('dataTransfer');
(function() {

	var s;
	var qoolhtmlupload = {

		settings: {
			bod: $(".uploadlistener"),
			img: '',
			fileInput: $("#uploader")
		},

		init: function() {
			s = qoolhtmlupload.settings;
			qoolhtmlupload.bindUIActions();

		},

		bindUIActions: function() {

			var timer;

			s.bod.on("dragover", function(event) {

				clearTimeout(timer);
				if (event.currentTarget == s.bod[0]) {
					qoolhtmlupload.showDroppableArea();
				}

				// Required for drop to work
				return false;
			});

			s.bod.on('dragleave', function(event) {
				if (event.currentTarget == s.bod[0]) {
					// Flicker protection
					timer = setTimeout(function() {
						qoolhtmlupload.hideDroppableArea();
					}, 200);
				}
			});

			s.bod.on('drop', function(event) {
				// Or else the browser will open the file
				event.preventDefault();

				qoolhtmlupload.handleDrop(event.dataTransfer.files);
			});

			s.fileInput.on('change', function(event) {
				qoolhtmlupload.handleDrop(event.target.files);
			});
		},

		showDroppableArea: function() {
			s.bod.addClass("droppable");
		},

		hideDroppableArea: function() {
			s.bod.removeClass("droppable");
		},

		handleDrop: function(files) {

			qoolhtmlupload.hideDroppableArea();
			var resizeto = false;
			if($.cookie('QOOL_GALLERY_RESIZE_TO')>0){
				resizeto = $.cookie('QOOL_GALLERY_RESIZE_TO');
			}

			for(var i=0;i<files.length;i++){
				file = files[i];
				if (file.type.match('image.*')) {
					qoolhtmlupload.resizeImage(file, resizeto, function(data) {
						var xhr = new XMLHttpRequest();
						var fd = new FormData();
						fd.append('file', data);
						fd.append('name', file.name);
						xhr.open('POST', qool_url+"/admin/gallerysaveimage", true);
						xhr.setRequestHeader("X-Requested-With","XMLHttpRequest");
						xhr.send(fd);
						qoolhtmlupload.createHolder(data,file);
					});
				} else {
					alert("That file wasn't an image.");
					return false;
				}
			}
		},
		createHolder: function(data,file){
			$("<li id='newimg-"+file.size+"' class='thumbnail' data-src='"+cms_dir+"/uploads/"+file.name+"'><a class='clboxer' style='background:url("+qool_url+"/uploads/"+file.name+")' href='"+qool_url+"/uploads/"+file.name+"'><img class='grayscale' src='"+data+"' alt='"+file.name+"' /></a></li>").appendTo('#newitemsholder');
		},
		resizeImage: function(file, size, callback) {
			var fileTracker = new FileReader;
			if(size){
				fileTracker.onload = function() {
					Resample(
					this.result,
					size,
					size,
					callback
					);
				}
			}else{
				fileTracker.onload = function(event){
					var pic = event.target;
					callback(pic.result);
				}

			}
			fileTracker.readAsDataURL(file);

			fileTracker.onabort = function() {
				alert("The upload was aborted.");
			}
			fileTracker.onerror = function() {
				alert("An error occured while reading the file.");
			}

		},

		placeImage: function(data) {
			s.img.attr("src", data);

		}

	}

	qoolhtmlupload.init();

})();