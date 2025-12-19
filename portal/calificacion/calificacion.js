/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).on('ready',function() {
     
    
    var direccion = localStorage.getItem("direccion");
    
    if(direccion != null && direccion != "" && direccion != false && direccion != undefined){
       $("#idir").text(direccion);
    }
    
});




function leerGET() { 
    
   
 var cadGET = location.search.substr(1, location.search.length); 
 cadGET=cadGET.split("=");
 cadGET=cadGET[1];
 return cadGET;
 }


$('#guardar').click(function(){
    


 var calificacion= $("#example-fontawesome option:selected").attr('id');





var parametros = {

                        "pk_orden" : leerGET(),
                        
	                "calificacion" : calificacion,
                        
                        "comentarios" : $('#comentario').val()

};
$.ajax({

	                data:  parametros,

	                url:   'https://www.appsintegra.com.mx/mitani/ws/movil/guardarCalificacion.php',

	                type:  'POST',

	                beforeSend: function () {

	                },

	                success:  function (response) {
                            if (response==0)
                            {
                                $("#elmensaje").text("Tanks!");
                                $(location).attr('href',  "#mensaje");
                             
                            }
                            if (response==1)
                            {
                             $("#elmensaje").text("Please try again!");
                             $("#imge").attr("src","img/error.png")
                             $(location).attr('href',  "#mensaje");
                            }
	                },
                        
                          error: function (arg1, arg2, arg3)
                        {
                           console.log(arg3);  
                        }
                        
	    });


});


$('.back').click(function(){
    $(location).attr('href', "historial.html"); 
    
});


$('#aceptar').click(function(){
    $(location).attr('href', "historial.html"); 
    
});