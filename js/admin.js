
jQuery(function($) {
    $('div.button.resetparam').click(function() {
        $( "input.param" ).each(function( index ) {
            if( $( this ).attr('type') == 'checkbox' ){
                if($( this ).data( "default" ) == true ){
                    $( this ).prop( "checked", true);
                }else{
                    $( this ).prop( "checked", false) ;
                }
            }else{
                $( this ).val($( this ).data( "default" ));
            }
        });
        $( "select.param" ).each(function( index ) {
            var datadefault = $( this ).data( "default" );
            $("option", this).each(function() {
                if($( this ).val() == datadefault ){
                    $( this ).prop( "selected", true) ;
                }
            });
        });

    });
})//