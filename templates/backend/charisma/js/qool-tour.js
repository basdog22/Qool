$(document).ready(function(){
	//tour
	$(".tour").click(function(){
		var tour = new Tour();
		tour.addStep({
			element: "#themaincontent", /* html element next to which the step popover should be shown */
			placement: "top",
			title: "Qool CMS v2.0 Help", /* title of the popover */
			content: "Follow this tour to get help about the dashboard of Qool CMS v2.0. After this tour, you will be familiar of how things work with Qool." 
		});
		tour.addStep({
			element: "#navtabs",
			placement: "right",
			title: "Widget Tabs",
			content: "These tabs hold your dashboard widgets. Widgets in the System tab can be created programmatically by addons and more tabs can also be created by an addon. The Feeds tab can be used by you to add your favorite feeds. "
		});
		tour.addStep({
			element: "#topbar",
			title: "Shortcuts Bar",
			placement: "bottom",
			content: "This bar will help you do things with Qool. You can search, create shortcuts and tasks. Other options will appear here also. So always keep an eye on this bar until you are familiar with Qool CMS."
		});
		tour.addStep({
			element: "#leftmenu",
			placement: "right",
			title: "Administration Menus",
			content: "This is where you will mostly hang out. These menus hold all commands to create and manage content, and to administrate your site. There are 4 menus here. The first is the 'New' menu where you can create content, the second one is 'Content' menu where you can manage content types, the third one is 'System' menu with options for managing your system and the fourth one is called 'Addon Options' where addons create their menus."
		});
		tour.addStep({
			element: "#showhelp",
			placement: "left",
			title: "Help Button",
			content: "You can always go through this tour again by clicking the Help button. You can hide the button from the 'Site Settings' option in the 'System' menu."
		});
		
		tour.restart();
	});
});