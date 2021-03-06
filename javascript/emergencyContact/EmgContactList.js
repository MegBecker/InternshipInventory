(function($){
    
    var methods = {
        // Init Method
        init: function(initialData){
            return this.each(function() {
                $this = $(this);
                // For each initial value, insert a new EmergContact object into the ContactList
                if(initialData != null){
                    $.each(initialData, function(index, value){
                        $this.EmgContactList('add', value.id, value.name, value.relation, value.phone);
                    });
                }
                
                //$this.html('<pre>ohhh hai</pre>');
            });
            
        },
                
        // Add method
        add: function(id, name, relation, phone){
            
            var newContact = $('<li class="list-group-item">');
            newContact.html(name + ' &bull; ' + relation + ' &bull; ' + phone);
            
            var deleteLink = $('<button type="button" class="close">');
            deleteLink.html("&times;");
            deleteLink.addClass('contact-delete-link');
            //deleteLink.hide();
            
            
            deleteLink.click(function(){
                
                $("#emergency-contact-delete-confirm").dialog({
                    resizable: false,
                    height:150,
                    modal: true,
                    buttons: {
                        "Delete Contact": function() {
                            $(this).dialog('close');
                            // Do some ajax
                            $.ajax({
                                type: 'POST',
                                url: 'index.php',
                                data: {module:'intern',
                                       action:'removeEmergencyContact',
                                       contactId: id
                                      },
                                dataType: 'text',
                                success: function(){
                                    newContact.remove();
                                },
                                error: function(){
                                    alert('Sorry, there was an error and the emergency contact could not be removed.');
                                }
                            });
                        },
                        "Cancel": function() {
                            $(this).dialog('close');
                        }
                    }
                });
            });
            
            
            newContact.append(deleteLink);
            $this.append(newContact);
            
        }
    };
    
    $.fn.EmgContactList = function(method) {

        if(methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments,1));
        } else if(typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.EmgContactList');
        }
    };
    
})( jQuery );