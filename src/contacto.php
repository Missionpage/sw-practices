<?php

require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/Formulario.php';

$tituloPagina = 'Contacto';

$contenidoPrincipal = <<<EOS
  <div class="promociones">
    <h2>Atencion al cliente</h2>
    <div id="info">
    <h3>Información</h3>
    <p>
      EasyRent es un negocio dedicado al alquiler de coches de forma online.
      Contacta aquí.
    </p>
    </div>

    <div id="promo">
    <h4>Teléfono S</h4>
    <p>
      666666666
    </p>
    </div>

    <div id="promo">
    <h4>Ticket</h4>
    <p>
      ticket
    </p>
    </div>

    <div id="promo">
    <h4>Email</h4>
    <p>
      contacto@easyrent.com
    </p>
    </div>
	</div>
    
    

EOS;


require __DIR__.'/includes/vistas/plantillas/plantilla.php';