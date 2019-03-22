// Serialize form but into Object
$.fn.serializeObject = function()
{
   var o = {};
   var a = this.serializeArray();
   $.each(a, function() {
       if (o[this.name]) {
           if (!o[this.name].push) {
               o[this.name] = [o[this.name]];
           }
           o[this.name].push(this.value || '');
       } else {
           o[this.name] = this.value || '';
       }
   });
   return o;
};

// Return outer HTML
jQuery.fn.outerHTML = function(s) {
    return s ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
};

jQuery.fn.atBottom = function(s) {
    if (this[0].scrollHeight <= ($(this).outerHeight() + $(this).scrollTop() + 10)) 
          return true;
        else
          return false;
};

// Scroll element to bottom
jQuery.fn.scrollBottom = function(check) {
    this.scrollTop(this[0].scrollHeight);
};

// Return name of current file
function current_document() {
    var file_name = document.location.href;
    var end = (file_name.indexOf("?") == -1) ? file_name.length : file_name.indexOf("?");
    return file_name.substring(file_name.lastIndexOf("/")+1, end);
}