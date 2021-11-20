
jQuery( document ).ready(function() {   
jQuery( "#pmodified, #pcreated" ).on( "click", function() {
   if(this.id == 'pcreated') {
       jQuery("#pmodified").prop('checked',false);
   }
   else jQuery("#pcreated").prop('checked',false);
});
});
