$(document).ready(function(){
	$(".qoolmodal").click(function(e) {
		console.log("ddd");
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
		$(".form").each(function(){
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
	$(".warnme").click(function(e){
		$('#myModal').modal('show');
		$("#delbutton").show();
		$("#delbutton").attr('href',e.target.href);
		$("#modaltitle").html(e.target.title);
		$("#qoolmodal").html(e.target.rel);
		var goon = false;
		myevent = e;
		$("#delbutton").click(function(){

			$('#myModal').modal('hide');
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
		$.post(qool_url+"/admin/ajaxdelete", { deleteId: e.target.id, dbtable: e.target.rev },function(data) {
			$("#msg").show(5);
			$("#msg").html(data);
			$("#msg").delay(4000).fadeOut(300);
		});
		$(this).remove();
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
});


function adminSaveWidget(widget,slot){
	$.post(qool_url+"/admin/savewidgetstate", { widgetname: widget, slotname: slot },function(data) {
		$("#msg").show(5);
		$("#msg").html(data);
		$("#msg").delay(4000).fadeOut(300);
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