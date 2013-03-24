var xhrFetchingImages;

wysiHelpers = {
  getImageTemplate: function() {
    /* this is what goes in the wysiwyg content after the image has been chosen */
    var tmpl;
    var imgEntry = "<img src='<%= url %>' alt='<%= caption %>'>";
    tmpl = _.template( imgEntry );
    return tmpl;
  }
};

bootWysiOverrides = {
  initInsertImage: function(toolbar) {
    var self = this;
    var insertImageModal = toolbar.find('.bootstrap-wysihtml5-insert-image-modal');
    var urlInput = insertImageModal.find('.bootstrap-wysihtml5-insert-image-url');
    var insertButton = insertImageModal.find('a.btn-primary');
    var initialValue = urlInput.val();
    
    var chooser = insertImageModal.find('.image_chooser.images');
    /* this is the template we put in the image dialog */
    var optionTemplate = _.template(
      "<tr><td class='row' data-type='image' data-caption='<%= caption %>' data-url='<%= file %>'>" +
        "<img src='<%= file %>' width='100'>"+
        "<%= caption %>" + 
        "</td></tr>");
    
    var helpers = wysiHelpers;
    
    // populate chooser
    // TODO: this get's called once for each wysiwyg on the page.  we could 
    //       be smarter and cache the results after call 1 and use them later.
    if (!xhrFetchingImages) {
      $.ajax({
        url:'{$url}/api.php?u=1&action=lastimglist',
        success: function(data) {
          xhrFetchingImages = false;
          // populate dropdowns
          _.each(data, function(img) {
            chooser.append(optionTemplate(img));
          });
        }
      });
    }

    var insertImage = function(imageData) {
      if(imageData.url) {
        var clz = 'image_container';
        var doc = self.editor.composer.doc;
        var tmpl = helpers.getImageTemplate();
        var chunk = tmpl(imageData);
        self.editor.composer.commands.exec("insertHTML", chunk);
      }
    };
    
    chooser.on('click', 'td', function(ev) {
      var $row = $(ev.currentTarget);
      insertImage($row.data());
      insertImageModal.modal('hide');
    });
    
    insertImageModal.on('hide', function() {
      self.editor.currentView.element.focus();
    });
    
    toolbar.find('a[data-wysihtml5-command=insertImage]').click(function() {
      var activeButton = $(this).hasClass("wysihtml5-command-active");
      
      if (!activeButton) {
        insertImageModal.modal('show');
	         $('#file1').change(function() {
	        	var image_custom_caption = $('#img_caption').val();
	        	if(image_custom_caption.length < 1)
	        	{
	        		$('#uploadresult').html('Set image title and choise file again!').addClass('alert alert-error');
	        	}
	        	else
	        	{
		            $(this).uploadimage('{$url}/api.php?u=1&action=uploadimg&seo_title='+image_custom_caption, function(res) {
		                //$(res).insertAfter(this);
		                if(res.status)
		                {
							 chooser.append(optionTemplate({"file":res.file,"caption":image_custom_caption,"foreground":res.forground,"background":res.background}));
							 $('#uploadresult').html('Upload successful').addClass('alert alert-success');
						}
						else
						{
							$('#uploadresult').html('Upload failed').addClass('alert alert-error');
						}		
		            }, 'json');
	        	}
	        });
        
        insertImageModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
          e.stopPropagation();
        });
        return false;
      }
      else {
        return true;
      }
    });
  }
};

$.extend($.fn.wysihtml5.Constructor.prototype, bootWysiOverrides);

$(function() {

  // override options
  var wysiwygOptions = {
    customTags: {
      "em": {},
      "strong": {},
      "hr": {}
    },
    customStyles: {
      // keys with null are used to preserve items with these classes, but not show them in the styles dropdown
      'shrink_wrap': null,
      'credit': null,
      'tombstone': null,
      'chat': null,
      'caption': null
    },
    customTemplates: {
      /* this is the template for the image button in the toolbar */
      image: function(locale) {
        return "<li>" +
          "<div class='bootstrap-wysihtml5-insert-image-modal modal hide fade'>" +
          "<div class='modal-header'>" +
          "<a class='close' data-dismiss='modal'>&times;</a>" +
          "<h3>" + locale.image.insert + "</h3>" +
          "</div>" +
          "<div class='modal-body'>" +
          "<div class='chooser_wrapper'>" +
          "<table class='image_chooser images'></table><hr />" +
          "<table width=\"100%\">"+
          "<tr><td>Choice File</td><td>Set title</td></tr>"+
          "<tr>"+
          "<td><input name=\"file\" id=\"file1\" type=\"file\"></td>" +
          "<td><input name=\"img_caption\" id=\"img_caption\" type=\"text\" value=\"Untitled\"></td>" +
          "</tr>"+
          "</table>"+
          "<div id=\"uploadresult\"></div>" +
          "</div>" +
          "" +
          "</div>" +
          "<div class='modal-footer'>" +
          "<a href='#' class='btn' data-dismiss='modal'>" + locale.image.cancel + "</a>" +
          "</div>" +
          "</div>" +
          "<a class='btn' data-wysihtml5-command='insertImage' title='" + locale.image.insert + "'><i class='icon-picture'></i></a>" +
          "</li>";
      }
    }
  };

  $('textarea.wysi').each(function() {
	   $(this).wysihtml5($.extend(wysiwygOptions, {html:true, color:false, stylesheets:[]}));
  });
});