/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var $=jQuery;

function leerGET() { 
    
   
 var cadGET = location.search.substr(1, location.search.length); 
 cadGET=cadGET.split("=");
 cadGET=cadGET[1];
 return cadGET;
 }


$(document).on('ready',function() {
        //alert("Hola");
        getSlider();
});




function getSlider()
    {
        
        var parametros = {

	                "id" : leerGET(),
                        "tipo": $("#tipoe").val()
	        }; 
    
        $.ajax({
                        data: parametros,
                        
	                url:   'http://www.dast.com.mx/pgb/ws/sliderc.php',

	                type:  'get',

	                beforeSend: function () {

	                       

	                },

	                success:  function (response) {
                            
                            $("#elslider").html(response);
                            
          var slideIndex = 1;
showSlides(slideIndex);

// Next/previous controls
function plusSlides(n) {
  showSlides(slideIndex += n);
}

// Thumbnail image controls
function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
    
  var i;
  var slides = document.getElementsByClassName("mySlides");
  var dots = document.getElementsByClassName("dot");
  if (n > slides.length) {slideIndex = 1} 
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none"; 
  }
  for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" activea", "");
  }
  slides[slideIndex-1].style.display = "block"; 
  dots[slideIndex-1].className += " activea";
}                  

$('.beliminar').click(function(){
   eliminar(this.id);
});
                            
	                },
                        
                          error: function (arg1, arg2, arg3)
                        {
                           console.log(arg3);  
                        }
                        
	    });
    }
    
function eliminar(id)
{
    var parametros = {
        
	                "id" : id
                    
	        };
                

	        $.ajax({

	                data:  parametros,

	                url:   'http://www.dast.com.mx/pgb/ws/eliminarMultimedia.php',

	                type:  'get',

	                beforeSend: function () {

	                       

	                },

	                success:  function (response) {
                            
                               data="["+response+"]";
                          
                          var content = JSON.parse(data);
                            
                             if (content[0].error==0)
                            {
                                
                                $("#mensaje").text(content[0].mensajea);
                                
                                $(location).attr("href","#confirmacion");
                                
                            } 
                            
                            else
                            {
                                $("#mensaje").text(content[0].mensajeb);
                                
                                $(location).attr("href","#confirmacion");
                                
                                
                            }
                            
                            
	                },
                        
                          error: function (arg1, arg2, arg3)
                        {
                           console.log(arg3);  
                        }
                        
	    });
}

$(function(){
        $("#formuploadajax").on("submit", function(e){
            e.preventDefault();
            
        
        var error=0;
        
        
    if ($('#archivo').val().length <5 )
    {
    error=1;
    $('#archivo').css('border-color','#FF0000');
    }
        
        if (error==0)
            {
     //alert($('#tipo').val()+"---"+tipo);   
         
         var f = $(this);
            var formData = new FormData(document.getElementById("formuploadajax"));
            formData.append("tipom", 1);
            formData.append("tipoe", $("#tipoe").val());
            formData.append("idee", leerGET());
            formData.append("portadag", $("#portada").val());
            
            $.ajax({
                url: "http://www.dast.com.mx/pgb/ws/agregarMultimedia.php",
                type: "post",
                dataType: "html",
                data: formData,
                cache: false,
                contentType: false,
	     processData: false,
             
             beforeSend: function () {

	                       

	                },

	                success:  function (response) {
                            response="["+response+"]";     
                            var myObj = JSON.parse(response);
                            
                          
                            
                            
                            if (myObj[0].error==0)
                            {
                                
                                $("#mensaje").text(myObj[0].mensajea);
                                
                                
                                $(location).attr("href","#confirmacion");
                                
                            } 
                            
                            if (myObj[0].error!=0 && myObj[0].error!=6)
                            {
                                $("#mensaje").text("Hubo un problema por favor vuelva a intentarlo");
                                
                                $(location).attr("href","#confirmacion");
                                
                                
                            }
                            
                            if (myObj[0].error==6)
                            {
                                $("#mensaje").text("La imagen no contiene la dimensiones esperadas (600x300) su imagen es de "+myObj[0].mensajeb);
                                
                                $(location).attr("href","#confirmacion");
                                
                            }
                            
                           
                        },
                        
                          error: function (arg1, arg2, arg3)
                        {
                           alert(arg3);  
                        }
             
            });
            
            
            }
            
        
        
        });
        });

$('#salir').click(function(){
   $(location).attr("href","editarExpo.html?id="+leerGET());
});

var slideIndex = 1;
showSlides(slideIndex);

// Next/previous controls
function plusSlides(n) {
  showSlides(slideIndex += n);
}

// Thumbnail image controls
function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
    
  var i;
  var slides = document.getElementsByClassName("mySlides");
  var dots = document.getElementsByClassName("dot");
  if (n > slides.length) {slideIndex = 1} 
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none"; 
  }
  for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" activea", "");
  }
  slides[slideIndex-1].style.display = "block"; 
  dots[slideIndex-1].className += " activea";
}



