/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function leerGET() { 
    
   
 var cadGET = location.search.substr(1, location.search.length); 
 cadGET=cadGET.split("=");
 cadGET=cadGET[1];
 return cadGET;
 }


$('#guardar').click(function(){
    


 var calificacion= $("#example-fontawesome option:selected").attr('id');





var parametros = {

                        "id" : leerGET(),
                        
	                "calificacion" : calificacion,
                        
                        "comentario" : $('#comentario').val()

};
$.ajax({

	                data:  parametros,

	                url:   'http://www.lavapp.com.mx/app/ws/guardarCalificacion.php',

	                type:  'get',

	                beforeSend: function () {

	                },

	                success:  function (response) {
                            if (response==0)
                            {
                                $("#elmensaje").text("Gracias por calificarnos!");
                                $(location).attr('href',  "#mensaje");
                             
                            }
                            if (response==1)
                            {
                             $("#elmensaje").text("Por favor vuelva a intentarlo");   
                             $(location).attr('href',  "#mensaje");
                            }
	                },
                        
                          error: function (arg1, arg2, arg3)
                        {
                           console.log(arg3);  
                        }
                        
	    });


});