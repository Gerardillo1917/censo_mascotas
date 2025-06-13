<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historia Clínica de Primera Atención</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 30px;
      font-size: 14px;
      color: #000;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    header img {
      height: 80px;
    }

    h1 {
      text-align: center;
      font-size: 20px;
      margin: 10px 0;
    }

    .form-group {
      display: flex;
      flex-wrap: wrap;
      margin-bottom: 10px;
    }

    .form-group label {
      width: 200px;
      font-weight: bold;
    }

    .form-group input, .form-group textarea {
      flex: 1;
      border: none;
      border-bottom: 1px solid #000;
      margin-left: 10px;
    }

    section {
      margin-top: 20px;
    }

    .firmas {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
    }

    .firmas .campo {
      width: 48%;
      text-align: center;
      border-top: 1px solid #000;
      padding-top: 5px;
    }

    .legal {
      margin-top: 40px;
      text-align: justify;
      font-size: 13px;
    }
  </style>
</head>
<body>

  <header>
    <img src="\censo_mascotas\img\logo_rumbo.png" alt="Logo Izquierda">
    <img src="\censo_mascotas\img\logo_secretaria_agropecuario.png" alt="Logo Derecha">
  </header>

  <h1>HISTORIA CLÍNICA DE PRIMERA ATENCIÓN</h1>
  <div class="form-group"><label>Fecha:</label><input type="text"></div>
  <div class="form-group"><label>Hora:</label><input type="text"></div>
  <div class="form-group"><label>Lugar:</label><input type="text"></div>

  <section>
    <h3>Datos del Paciente</h3>
    <div class="form-group"><label>Nombre:</label><input type="text"></div>
    <div class="form-group"><label>Especie:</label><input type="text"></div>
    <div class="form-group"><label>Raza:</label><input type="text"></div>
    <div class="form-group"><label>Color:</label><input type="text"></div>
    <div class="form-group"><label>Peso:</label><input type="text"></div>
    <div class="form-group"><label>Edad:</label><input type="text"></div>
  </section>

  <section>
    <h3>Datos del Tutor</h3>
    <div class="form-group"><label>Nombre:</label><input type="text"></div>
    <div class="form-group"><label>Dirección:</label><input type="text"></div>
    <div class="form-group"><label>Teléfono:</label><input type="text"></div>
    <div class="form-group"><label>Municipio:</label><input type="text"></div>
  </section>

  <section>
    <h3>Constantes Fisiológicas</h3>
    <div class="form-group"><label>FR:</label><input type="text"></div>
    <div class="form-group"><label>FC:</label><input type="text"></div>
    <div class="form-group"><label>CC:</label><input type="text"></div>
    <div class="form-group"><label>TLLC:</label><input type="text"></div>
    <div class="form-group"><label>R. Tusígeno:</label><input type="text"></div>
    <div class="form-group"><label>S. Deglutorio:</label><input type="text"></div>
    <div class="form-group"><label>Mucosas:</label><input type="text"></div>
    <div class="form-group"><label>Temperatura:</label><input type="text"></div>
    <div class="form-group"><label>Nódulos linfáticos (¿cuáles?):</label><input type="text"></div>
  </section>

  <section>
    <h3>Historial Clínico</h3>
    <div class="form-group"><label>Vacunación - Rabia:</label><input type="text"></div>
    <div class="form-group"><label>Cuadro Básico:</label><input type="text"></div>
    <div class="form-group"><label>Alimentación:</label><input type="text"></div>
    <div class="form-group"><label>Desparasitación / Tiempo:</label><input type="text"></div>
    <div class="form-group"><label>Producto:</label><input type="text"></div>
    <div class="form-group"><label>Enfermedades Anteriores:</label><input type="text"></div>
    <div class="form-group"><label>Cirugías Anteriores:</label><input type="text"></div>
    <div class="form-group"><label>Procedencia:</label><input type="text"></div>
    <div class="form-group"><label>Procedimiento a realizar:</label><input type="text"></div>
    <div class="form-group"><label>Plan Anestésico (IM/IV):</label><input type="text"></div>
    <div class="form-group"><label>Observaciones Prequirúrgicas:</label><textarea></textarea></div>
    <div class="form-group"><label>Observaciones Quirúrgicas:</label><textarea></textarea></div>
  </section>

  <div class="firmas">
    <div class="campo">Nombre y firma de la persona tutora</div>
    <div class="campo">Nombre y firma de la o el MVZ tratante</div>
  </div>

  <div class="legal">
    La esterilización es ambulatoria, la cual se realiza sin exámenes de laboratorio previos, como lo son: sanguíneos, que nos indican la evaluación de glóbulos rojos (anemia), coagulación y valoración de hígado y riñones; el procedimiento se realiza con anestesia fija, endovenosa en el caso de los perros, y coctel en el caso de los felinos; no utilizamos anestesia inhalada. Cualquier procedimiento supone riesgos médicos, incluyendo el riesgo de muerte, el cual se incrementa si un animal presenta una mala nutrición o padece una patología no diagnosticada. Por lo tanto, una vez informado de lo anterior, y sabedor de los riesgos y de los que pudieran presentarse posteriormente, me comprometo a realizar los cuidados postoperatorios que se me indiquen. En caso de que mi mascota sufra algún daño o muera como consecuencia de esta intervención y/o una complicación médica, libero de toda responsabilidad legal al equipo médico veterinario, personal de la clínica veterinaria y cualquier otra persona involucrada en el procedimiento. Certifico con mi firma y nombre al final de este documento.
  </div>

</body>
</html>
