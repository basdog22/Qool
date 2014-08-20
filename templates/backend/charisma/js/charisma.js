$(document).ready(function(){

	$(".emulatelink").click(function(){
		$("#"+$(this).attr('rel')).toggle();
	});

	//disbaling some functions for Internet Explorer
	if($.browser.msie)
	{
		$('#is-ajax').prop('checked',false);
		$('#for-is-ajax').hide();
		$('#toggle-fullscreen').hide();
		$('.login-box').find('.input-large').removeClass('span10');

	}


	//highlight current / active link
	$('ul.main-menu li a').each(function(){
		if($($(this))[0].href==String(window.location))
		$(this).parent().addClass('active');
	});

	//establish history variables
	var
	History = window.History, // Note: We are using a capital H instead of a lower h
	State = History.getState(),
	$log = $('#log');

	//bind to State Change
	History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
		var State = History.getState(); // Note: We are using History.getState() instead of event.state
		$.ajax({
			url:State.url,
			success:function(msg){
				$('#content').html($(msg).find('#content').html());
				$('#loading').remove();
				$('#content').fadeIn();
				docReady();
			}
		});
	});



	//other things to do on document ready, seperated for ajax calls
	docReady();
});

$(window).load(function(){
	$("#leftmenu li.active").parent().parent().toggle();
});
function docReady(){
	//prevent # links from moving to top
	$('a[href="#"][data-top!=true]').click(function(e){
		e.preventDefault();
	});

	$(".datepicker").live("focus", function(){
		$(this).trigger('click');
		$(this).datepicker();
	});

	//chosen - improves select
	$('select').chosen();

	//tooltip
	$('[rel="tooltip"],[data-rel="tooltip"]').tooltip({"placement":"bottom",delay: { show: 400, hide: 200 }});

	//auto grow textarea
	$('textarea.autogrow').autogrow();

	$("#resizeto").live('change',function(){
		$.cookie("QOOL_GALLERY_RESIZE_TO",$(this).val(), {expires: 365});
	});
	//popover
	$('[rel="popover"],[data-rel="popover"]').popover();

	$("input:radio, input:file").not('[data-no-uniform="true"],#uniform-is-ajax').uniform();

	//iOS / iPhone style toggle switch
	$('.checkbox').iphoneStyle();



	//gallery colorbox
	$(".thumbnail a.clboxer").live('click', function (e) {
		e.preventDefault();
		$.colorbox({
			href: this.href,
			maxWidth:"95%",
			maxHeight:"95%",
			open: true,
			transition: "elastic",
			width: "995px",
			rel:'thumbnail a'
		});
	});

	//makes elements soratble, elements that sort need to have id attribute to save the result
	$('.sortablewidgets').sortable({
		revert:true,
		cancel:'.btn,.box-content,.nav-header,.qoolmodal',
		update:function(event,ui){
			//line below gives the ids of elements, you can make ajax call here to save it to the database
			//console.log($(this).sortable('toArray'));
			var order = $(this).sortable("toArray").join(',');
			$.cookie("box_order_"+$(this).attr('id'),order, {expires: 365, path: window.location.pathname});
		}
	});

	$('.sortableboxes').sortable({
		revert:true,
		cancel:'.btn,.box-content,.nav-header',
		update:function(event,ui){
			//line below gives the ids of elements, you can make ajax call here to save it to the database
			//console.log($(this).sortable('toArray'));
			var order = $(this).sortable("toArray").join(',');
			$.cookie("box_order",order, {expires: 365, path: window.location.pathname});
		}
	});

	$(".icon-remove").live('hover',function(){
		$(this).tooltip({"placement":"bottom",delay: { show: 400, hide: 200 }});
	})

	//gallery controlls container animation
	$('ul.gallery li').live({
		mouseenter:function(){
			$('img',this).fadeToggle(1000);
			$(this).find('.gallery-controls').remove();
			$(this).append('<div class="well gallery-controls">'+
			'<p><a href="#" class="gallery-edit btn"><i class="icon-edit"></i></a> <a href="#" class="gallery-delete btn"><i title="Deleting the image cannot be undone!" class="icon-remove"></i></a></p>'+
			'</div>');
			$(this).find('.gallery-controls').stop().animate({'margin-top':'-1'},400,'easeInQuint');
		},mouseleave:function(){
			$('img',this).fadeToggle(1000);
			$(this).find('.gallery-controls').stop().animate({'margin-top':'-30'},200,'easeInQuint',function(){
				$(this).remove();
			});
		}
	});

	//gallery fullscreen
	$('#toggle-fullscreen').button().click(function () {
		var button = $(this), root = document.documentElement;
		if (!button.hasClass('active')) {
			$('#thumbnails').addClass('modal-fullscreen');
			if (root.webkitRequestFullScreen) {
				root.webkitRequestFullScreen(
				window.Element.ALLOW_KEYBOARD_INPUT
				);
			} else if (root.mozRequestFullScreen) {
				root.mozRequestFullScreen();
			}
		} else {
			$('#thumbnails').removeClass('modal-fullscreen');
			(document.webkitCancelFullScreen ||
			document.mozCancelFullScreen ||
			$.noop).apply(document);
		}
	});




	//datatable
	$('.datatable').dataTable({
	"sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span12'i><'span12 center'p>>",
	"sPaginationType": "bootstrap",
	"oLanguage": {
	"sLengthMenu": "_MENU_ records per page"
	},
	"bStateSave": true,
	"fnStateSave": function (oSettings, oData) {
		localStorage.setItem( 'DataTables_'+window.location.pathname, JSON.stringify(oData) );
	},
	"fnStateLoad": function (oSettings) {
		return JSON.parse( localStorage.getItem('DataTables_'+window.location.pathname) );
	}
	} );
	$('.btn-close').click(function(e){
		e.preventDefault();
		$(this).parent().parent().parent().fadeOut();
	});
	$('.btn-minimize').click(function(e){
		e.preventDefault();
		var $target = $(this).parent().parent().next('.box-content');
		if($target.is(':visible')) $('i',$(this)).removeClass('icon-chevron-up').addClass('icon-chevron-down');
		else 					   $('i',$(this)).removeClass('icon-chevron-down').addClass('icon-chevron-up');
		$target.slideToggle();
	});
	$('.btn-setting').click(function(e){
		e.preventDefault();
		$('#myModal').modal('show');
	});

	//chart with points
	if($("#sincos").length)
	{
		var sin = [], cos = [];

		for (var i = 0; i < 14; i += 0.5) {
			sin.push([i, Math.sin(i)/i]);
			cos.push([i, Math.cos(i)]);
		}

		var plot = $.plot($("#sincos"),
		[ { data: sin, label: "sin(x)/x"}, { data: cos, label: "cos(x)" } ], {
			series: {
				lines: { show: true  },
				points: { show: true }
			},
			grid: { hoverable: true, clickable: true, backgroundColor: { colors: ["#fff", "#eee"] } },
			yaxis: { min: -1.2, max: 1.2 },
			colors: ["#539F2E", "#3C67A5"]
		});

		function showTooltip(x, y, contents) {
			$('<div id="tooltip">' + contents + '</div>').css( {
				position: 'absolute',
				display: 'none',
				top: y + 5,
				left: x + 5,
				border: '1px solid #fdd',
				padding: '2px',
				'background-color': '#dfeffc',
				opacity: 0.80
			}).appendTo("body").fadeIn(200);
		}

		var previousPoint = null;
		$("#sincos").bind("plothover", function (event, pos, item) {
			$("#x").text(pos.x.toFixed(2));
			$("#y").text(pos.y.toFixed(2));

			if (item) {
				if (previousPoint != item.dataIndex) {
					previousPoint = item.dataIndex;

					$("#tooltip").remove();
					var x = item.datapoint[0].toFixed(2),
					y = item.datapoint[1].toFixed(2);

					showTooltip(item.pageX, item.pageY,
					item.series.label + " of " + x + " = " + y);
				}
			}
			else {
				$("#tooltip").remove();
				previousPoint = null;
			}
		});



		$("#sincos").bind("plotclick", function (event, pos, item) {
			if (item) {
				$("#clickdata").text("You clicked point " + item.dataIndex + " in " + item.series.label + ".");
				plot.highlight(item.series, item.datapoint);
			}
		});
	}

	//flot chart
	if($("#flotchart").length)
	{
		var d1 = [];
		for (var i = 0; i < Math.PI * 2; i += 0.25)
		d1.push([i, Math.sin(i)]);

		var d2 = [];
		for (var i = 0; i < Math.PI * 2; i += 0.25)
		d2.push([i, Math.cos(i)]);

		var d3 = [];
		for (var i = 0; i < Math.PI * 2; i += 0.1)
		d3.push([i, Math.tan(i)]);

		$.plot($("#flotchart"), [
		{ label: "sin(x)",  data: d1},
		{ label: "cos(x)",  data: d2},
		{ label: "tan(x)",  data: d3}
		], {
			series: {
				lines: { show: true },
				points: { show: true }
			},
			xaxis: {
				ticks: [0, [Math.PI/2, "\u03c0/2"], [Math.PI, "\u03c0"], [Math.PI * 3/2, "3\u03c0/2"], [Math.PI * 2, "2\u03c0"]]
			},
			yaxis: {
				ticks: 10,
				min: -2,
				max: 2
			},
			grid: {
				backgroundColor: { colors: ["#fff", "#eee"] }
			}
		});
	}

	//stack chart
	if($("#stackchart").length)
	{
		var d1 = [];
		for (var i = 0; i <= 10; i += 1)
		d1.push([i, parseInt(Math.random() * 30)]);

		var d2 = [];
		for (var i = 0; i <= 10; i += 1)
		d2.push([i, parseInt(Math.random() * 30)]);

		var d3 = [];
		for (var i = 0; i <= 10; i += 1)
		d3.push([i, parseInt(Math.random() * 30)]);

		var stack = 0, bars = true, lines = false, steps = false;

		function plotWithOptions() {
			$.plot($("#stackchart"), [ d1, d2, d3 ], {
				series: {
					stack: stack,
					lines: { show: lines, fill: true, steps: steps },
					bars: { show: bars, barWidth: 0.6 }
				}
			});
		}

		plotWithOptions();

		$(".stackControls input").click(function (e) {
			e.preventDefault();
			stack = $(this).val() == "With stacking" ? true : null;
			plotWithOptions();
		});
		$(".graphControls input").click(function (e) {
			e.preventDefault();
			bars = $(this).val().indexOf("Bars") != -1;
			lines = $(this).val().indexOf("Lines") != -1;
			steps = $(this).val().indexOf("steps") != -1;
			plotWithOptions();
		});
	}

	//pie chart
	var data = [
	{ label: "Internet Explorer",  data: 12},
	{ label: "Mobile",  data: 27},
	{ label: "Safari",  data: 85},
	{ label: "Opera",  data: 64},
	{ label: "Firefox",  data: 90},
	{ label: "Chrome",  data: 112}
	];

	if($("#piechart").length)
	{
		$.plot($("#piechart"), data,
		{
			series: {
				pie: {
					show: true
				}
			},
			grid: {
				hoverable: true,
				clickable: true
			},
			legend: {
				show: false
			}
		});

		function pieHover(event, pos, obj)
		{
			if (!obj)
			return;
			percent = parseFloat(obj.series.percent).toFixed(2);
			$("#hover").html('<span style="font-weight: bold; color: '+obj.series.color+'">'+obj.series.label+' ('+percent+'%)</span>');
		}
		$("#piechart").bind("plothover", pieHover);
	}

	//donut chart
	if($("#donutchart").length)
	{
		$.plot($("#donutchart"), data,
		{
			series: {
				pie: {
					innerRadius: 0.5,
					show: true
				}
			},
			legend: {
				show: false
			}
		});
	}




	// we use an inline data source in the example, usually data would
	// be fetched from a server
	var data = [], totalPoints = 300;
	function getRandomData() {
		if (data.length > 0)
		data = data.slice(1);

		// do a random walk
		while (data.length < totalPoints) {
			var prev = data.length > 0 ? data[data.length - 1] : 50;
			var y = prev + Math.random() * 10 - 5;
			if (y < 0)
			y = 0;
			if (y > 100)
			y = 100;
			data.push(y);
		}

		// zip the generated y values with the x values
		var res = [];
		for (var i = 0; i < data.length; ++i)
		res.push([i, data[i]])
		return res;
	}

	// setup control widget
	var updateInterval = 30;
	$("#updateInterval").val(updateInterval).change(function () {
		var v = $(this).val();
		if (v && !isNaN(+v)) {
			updateInterval = +v;
			if (updateInterval < 1)
			updateInterval = 1;
			if (updateInterval > 2000)
			updateInterval = 2000;
			$(this).val("" + updateInterval);
		}
	});

	//realtime chart
	if($("#realtimechart").length)
	{
		var options = {
			series: { shadowSize: 1 }, // drawing is faster without shadows
			yaxis: { min: 0, max: 100 },
			xaxis: { show: false }
		};
		var plot = $.plot($("#realtimechart"), [ getRandomData() ], options);
		function update() {
			plot.setData([ getRandomData() ]);
			// since the axes don't change, we don't need to call plot.setupGrid()
			plot.draw();

			setTimeout(update, updateInterval);
		}

		update();
	}
}


//additional functions for data table
$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
{
	return {
	"iStart":         oSettings._iDisplayStart,
	"iEnd":           oSettings.fnDisplayEnd(),
	"iLength":        oSettings._iDisplayLength,
	"iTotal":         oSettings.fnRecordsTotal(),
	"iFilteredTotal": oSettings.fnRecordsDisplay(),
	"iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
	"iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
	};
}
$.extend( $.fn.dataTableExt.oPagination, {
"bootstrap": {
"fnInit": function( oSettings, nPaging, fnDraw ) {
	var oLang = oSettings.oLanguage.oPaginate;
	var fnClickHandler = function ( e ) {
		e.preventDefault();
		if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
			fnDraw( oSettings );
		}
	};

	$(nPaging).addClass('pagination').append(
	'<ul>'+
	'<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
	'<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
	'</ul>'
	);
	var els = $('a', nPaging);
	$(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
	$(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
},

"fnUpdate": function ( oSettings, fnDraw ) {
	var iListLength = 5;
	var oPaging = oSettings.oInstance.fnPagingInfo();
	var an = oSettings.aanFeatures.p;
	var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

	if ( oPaging.iTotalPages < iListLength) {
		iStart = 1;
		iEnd = oPaging.iTotalPages;
	}
	else if ( oPaging.iPage <= iHalf ) {
		iStart = 1;
		iEnd = iListLength;
	} else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
		iStart = oPaging.iTotalPages - iListLength + 1;
		iEnd = oPaging.iTotalPages;
	} else {
		iStart = oPaging.iPage - iHalf + 1;
		iEnd = iStart + iListLength - 1;
	}

	for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
		// remove the middle elements
		$('li:gt(0)', an[i]).filter(':not(:last)').remove();

		// add the new list items and their event handlers
		for ( j=iStart ; j<=iEnd ; j++ ) {
			sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
			$('<li '+sClass+'><a href="#">'+j+'</a></li>')
			.insertBefore( $('li:last', an[i])[0] )
			.bind('click', function (e) {
				e.preventDefault();
				oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
				fnDraw( oSettings );
			} );
		}

		// add / remove disabled classes from the static elements
		if ( oPaging.iPage === 0 ) {
			$('li:first', an[i]).addClass('disabled');
		} else {
			$('li:first', an[i]).removeClass('disabled');
		}

		if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
			$('li:last', an[i]).addClass('disabled');
		} else {
			$('li:last', an[i]).removeClass('disabled');
		}
	}
}
}
});
