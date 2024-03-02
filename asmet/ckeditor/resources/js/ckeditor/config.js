/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function (config) {

  // %REMOVE_START%
  // The configuration options below are needed when running CKEditor from source files.
  config.plugins = 'dialogui,dialog,a11yhelp,dialogadvtab,basicstyles,blockquote,notification,button,toolbar,clipboard,panel,floatpanel,menu,contextmenu,copyformatting,div,resize,elementspath,entities,popup,filetools,filebrowser,find,fakeobjects,floatingspace,listblock,richcombo,format,htmlwriter,wysiwygarea,indent,indentblock,indentlist,justify,link,list,liststyle,magicline,maximize,pastetext,pastefromword,preview,print,removeformat,selectall,showblocks,showborders,sourcearea,specialchar,stylescombo,tab,table,tabletools,tableselection,undo,lineutils,widgetselection,widget,notificationaggregator,xml,ajax,autolink,autoembed,menubutton,deselect,emojione,fixed,panelbutton,image,uploadimage,uploadwidget,base64image,colorbutton,youtube,font';
  config.skin = 'office2013';
  config.removeButtons = 'Paste,PasteText,PasteFromWord,Copy,Cut,Anchor';
  // %REMOVE_END%

  config.extraAllowedContent = 'td(biz-*);div(biz-*);a(biz-*);p(biz-*);ul(biz-*);li(biz-*)';

  // Define changes to default configuration here. For example:
  // config.language = 'fr';
  // config.uiColor = '#AADC6E';
};

CKEDITOR.on('instanceReady', function(ev) {
  ev.editor.dataProcessor.writer.selfClosingEnd = '>';
});