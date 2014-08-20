/**
 * @author Bjarni Thorisson
 * @copyright Copyright © 2009, Bjarni Thorisson, All rights reserved.
 */

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('gallerycon');
	tinymce.create('tinymce.plugins.GalleryConPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('GalleryCon', function() {
				if (ed.dom.getAttrib(ed.selection.getNode(), 'class').indexOf('mceItem') != -1)
					return;

				ed.windowManager.open({
					file : url + '/gallerycon.htm',
					width : 610 + parseInt(ed.getLang('gallerycon.delta_width', 0)),
					height : 480 + parseInt(ed.getLang('gallerycon.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('gallerycon', { title : 'gallerycon.desc', cmd : 'GalleryCon', image : url + '/img/photo.gif' });
			// Hilight the button when an image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('gallerycon', n.nodeName == 'IMG');
				// TODO: only hilight the button if the image id mathces /^img__(\-?\w+?)__(\w+?)__(\w+?)$/
			});
		},

		getInfo : function() {
			return {
				longname: 'Gallery Connection',
				author: 'Bjarni Thorissom',
				authorurl: 'http://hladan.org',
				infourl: 'http://tinymce-gallery-connection.googlecode.com/',
				version: '0.1'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('gallerycon', tinymce.plugins.GalleryConPlugin);
})();