
jQuery( document ).ready(function() {   
jQuery( "#pmodified, #pcreated" ).on( "click", function() {
   if(this.id == 'pcreated') {
       jQuery("#pmodified").prop('checked',false);
   }
   else jQuery("#pcreated").prop('checked',false);
  // console.log(jQuery("#pmodified").prop('checked'));
  // console.log(jQuery("#pcreated").prop('checked'));
});
});
