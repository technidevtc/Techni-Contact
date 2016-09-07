/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */
CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	//config.extraPlugins = 'imageuploader';
	config.htmlEncodeOutput = false;
	config.entities = false;

	//config.filebrowserUploadUrl = "http://www.techni-contact.com/ressources/images/tcarbo/";
	config.extraPlugins = 'wordcount,notification';
	config.wordcount = {
    // Whether or not you want to show the Paragraphs Count
    showParagraphs: true,
    // Whether or not you want to show the Word Count
    showWordCount: true,
    // Whether or not you want to show the Char Count
    showCharCount: true,
    // Whether or not you want to count Spaces as Chars
    countSpacesAsChars: false,
    // Whether or not to include Html chars in the Char Count
    countHTML: false,   
    // Maximum allowed Word Count, -1 is default for unlimited
    maxWordCount: -1,
    // Maximum allowed Char Count, -1 is default for unlimited
    maxCharCount: -1,
    // Add filter to add or remove element before counting (see CKEDITOR.htmlParser.filter), Default value : null (no filter)
    filter: new CKEDITOR.htmlParser.filter({
        elements: {
            div: function( element ) {
                if(element.attributes.class == 'mediaembed') {
                    return false;
                }
            }
        }
    })
};
};


