/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'en';
	// config.uiColor = '#AADC6E';
		// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'tools' },
        { name: 'colors' },
        { name: 'others' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },
        { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] }
	];

	// Remove some buttons, provided by the standard plugins, which we don't
	// need to have in the Standard(s) toolbar.
	config.removeButtons = 'Replace,Save,Print,NewPage,DocProps,Preview,document,Templates,Find,SelectAll,PageBreak,Language';

    config.allowedContent = true;

	// Se the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

    config.filebrowserBrowseUrl = '/api.php?iface=back&object=ckbrowser&type=0';
    config.filebrowserUploadUrl = '/api.php?iface=back&object=ckloader&type=0';

    config.filebrowserImageBrowseUrl = '/api.php?iface=back&object=ckbrowser&type=1';
    config.filebrowserImageUploadUrl = '/api.php?iface=back&object=ckloader&type=1';

    config.filebrowserFlashBrowseUrl = '/api.php?iface=back&object=ckbrowser&type=2';
    config.filebrowserFlashUploadUrl = '/api.php?iface=back&object=ckloader&type=2';

	// Make dialogs simpler.
	config.removeDialogTabs = 'image:advanced;link:advanced';

    config.scayt_autoStartup = false;
    config.disableNativeSpellChecker = false;
	
	config.extraPlugins = 'youtube,widget,lineutils,codesnippet,leaflet,wordcount';

    config.wordcount = {
        showWordCount: true,
        showCharCount: true,
        countHTML: false
    };

    config.height = '400';

    config.startupFocus = false;

    config.basicEntities = false;
};
