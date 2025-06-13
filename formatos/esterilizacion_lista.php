<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/database/conexion.php';

// Obtener hasta 10 mascotas esterilizadas
$query = "
    SELECT m.*, t.nombre AS nombre_tutor, t.apellido_paterno, t.apellido_materno, t.telefono, t.calle, t.numero_exterior, t.numero_interior, t.colonia
    FROM mascotas m
    JOIN tutores t ON m.id_tutor = t.id_tutor
    WHERE m.esterilizado = 1
    LIMIT 10
";
$resultado = $conn->query($query);
$beneficiarios = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista Beneficiarios Esterilización</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            color: #000;
        }

        h1, h2 {
            text-align: center;
        }

        .datos-jornada {
            margin-top: 10px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #eee;
        }

        .observaciones, .autorizacion {
            margin-top: 20px;
            font-size: 11px;
            text-align: justify;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <h1>DIRECCIÓN DE ADOPCIÓN Y BIENESTAR ANIMAL</h1>
    <h2>LISTA DE PERSONAS BENEFICIARIAS DE ESTERILIZACIÓN DE MASCOTAS</h2>

    <div class="datos-jornada">
        <strong>LUGAR DE LA JORNADA:</strong> _________________________<br>
        <strong>FECHA:</strong> _________________________
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Nombre Propietario</th>
                <th>Domicilio</th>
                <th>Teléfono</th>
                <th>Nombre Mascota</th>
                <th>Edad</th>
                <th>Perro</th>
                <th>Gato</th>
                <th>H</th>
                <th>M</th>
                <th>Vacunado</th>
                <th>Desparasitado</th>
                <th>Observaciones</th>
                <th>Autorización</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $n = 1;
            foreach ($beneficiarios as $row):
                $domicilio = "{$row['calle']} {$row['numero_exterior']}" . ($row['numero_interior'] ? " Int. {$row['numero_interior']}" : "") . ", {$row['colonia']}";
            ?>
            <tr>
                <td><?= $n++ ?></td>
                <td><?= "{$row['nombre_tutor']} {$row['apellido_paterno']} {$row['apellido_materno']}" ?></td>
                <td><?= $domicilio ?></td>
                <td><?= $row['telefono'] ?></td>
                <td><?= $row['nombre'] ?></td>
                <td><?= $row['edad'] ?></td>
                <td><?= $row['especie'] == 'Perro' ? '✔' : '' ?></td>
                <td><?= $row['especie'] == 'Gato' ? '✔' : '' ?></td>
                <td><?= $row['genero'] == 'Hembra' ? '✔' : '' ?></td>
                <td><?= $row['genero'] == 'Macho' ? '✔' : '' ?></td>
                <td><?= $row['tiene_vacuna'] ? '✔' : '✘' ?></td>
                <td><?= $row['desparasitado'] ?? '✘' ?></td>
                <td><?= htmlspecialchars($row['comentarios']) ?></td>
                <td>________</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="autorizacion">
        <p>
        Declaro que me han explicado el procedimiento quirúrgico al que se someterá mi animal y manifiesto entender que se extraerán definitivamente los órganos reproductivos de este. Se me ha explicado que, por su naturaleza, este procedimiento involucra riesgos generales y complicaciones que, a pesar de todas las medidas y cuidados efectuados por el equipo médico, pueden ser inevitables y en un bajo porcentaje de los casos llegar a causar la muerte de mi animal. 
        Se me comunica y aclararon todos los riesgos e implicancias de una anestesia general. La persona propietaria exonera civil y penalmente a la Persona Médica Veterinaria y a su personal por alguna complicación o fallecimiento del animal durante el proceso quirúrgico o en días posteriores al mismo. 
        Las suturas externas son absorbibles, y se aplica antibiótico, analgésico y antiinflamatorio para el mismo día de la cirugía; de requerirlo se mandará extra para días posteriores. Estoy consciente de los riesgos y complicaciones que puedan presentarse después de la intervención por el mal cuidado del paciente. De ser necesaria una consulta o nueva intervención, queda a mi cargo. 
        El animal de compañía se presenta a cirugía con ayuno de 8 a 12 horas. Habiendo leído este documento, entendido y aclarado todas mis dudas, autorizo la realización de la cirugía para la esterilización de mi animal.
        </p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px;">Imprimir / Guardar como PDF</button>
    </div>
</body>
</html>
