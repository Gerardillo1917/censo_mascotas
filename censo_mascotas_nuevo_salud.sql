-- Base de datos: `censo_mascotas`
CREATE TABLE `consultas_medicas` (
  `id_consulta` int(11) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `id_tutor` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `temperatura` decimal(4,2) DEFAULT NULL,
  `frecuencia_cardiaca` varchar(20) DEFAULT NULL,
  `frecuencia_respiratoria` varchar(20) DEFAULT NULL,
  `diagnostico` text NOT NULL,
  `tratamiento` text NOT NULL,
  `firma_imagen` varchar(255) DEFAULT NULL,
  `nombre_firmante` varchar(100) DEFAULT NULL,
  `dni_firmante` varchar(20) DEFAULT NULL,
  `fecha_firma` datetime DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `esterilizaciones` (`id_esterilizacion`, `id_mascota`, `id_tutor`, `fecha_procedimiento`, `hora_procedimiento`, `tipo_procedimiento`, `responsable`, `localidad`, `vacunacion_rabia`, `vacunacion_basica`, `desparasitacion_producto`, `desparasitacion_fecha`, `antecedentes`, `fr_respiratoria`, `fc_cardiaca`, `cc_capilar`, `tllc`, `reflejo_tusigeno`, `reflejo_deglutorio`, `mucosas`, `temperatura`, `nodulos_linfaticos`, `plan_anestesico`, `medicacion_previa`, `observaciones_quirurgicas`, `cuidados_postoperatorios`, `medicacion_postoperatoria`, `firma_imagen`, `nombre_firmante`, `dni_firmante`, `fecha_firma`, `fecha_registro`) VALUES
CREATE TABLE `esterilizaciones` (
  `id_esterilizacion` int(11) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `id_tutor` int(11) NOT NULL,
  `fecha_procedimiento` date NOT NULL,
  `hora_procedimiento` time NOT NULL,
  `tipo_procedimiento` enum('Ovario Histerectomía','Orquiectomía') NOT NULL,
  `responsable` varchar(100) NOT NULL,
  `localidad` varchar(100) NOT NULL,
  `vacunacion_rabia` tinyint(1) DEFAULT 0,
  `vacunacion_basica` tinyint(1) DEFAULT 0,
  `desparasitacion_producto` varchar(100) DEFAULT NULL,
  `desparasitacion_fecha` date DEFAULT NULL,
  `antecedentes` text DEFAULT NULL,
  `fr_respiratoria` varchar(20) DEFAULT NULL,
  `fc_cardiaca` varchar(20) DEFAULT NULL,
  `cc_capilar` varchar(20) DEFAULT NULL,
  `tllc` varchar(20) DEFAULT NULL,
  `reflejo_tusigeno` enum('Presente','Ausente','Disminuido') DEFAULT NULL,
  `reflejo_deglutorio` enum('Presente','Ausente','Disminuido') DEFAULT NULL,
  `mucosas` enum('Rosadas','Pálidas','Ictéricas','Cianóticas') DEFAULT NULL,
  `temperatura` decimal(4,2) DEFAULT NULL,
  `nodulos_linfaticos` varchar(100) DEFAULT NULL,
  `plan_anestesico` enum('Intramuscular','Intravenoso','Inhalatorio') DEFAULT NULL,
  `medicacion_previa` text DEFAULT NULL,
  `observaciones_quirurgicas` text DEFAULT NULL,
  `cuidados_postoperatorios` text DEFAULT NULL,
  `medicacion_postoperatoria` text DEFAULT NULL,
  `firma_imagen` varchar(255) DEFAULT NULL,
  `nombre_firmante` varchar(100) DEFAULT NULL,
  `dni_firmante` varchar(20) DEFAULT NULL,
  `fecha_firma` datetime DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `mascotas` (`id_mascota`, `id_tutor`, `nombre`, `especie`, `especie_personalizada`, `raza`, `color`, `edad`, `genero`, `tiene_vacuna`, `fecha_vacunacion`, `detalle_vacuna`, `esterilizado`, `incapacidad`, `descripcion_incapacidad`, `comentarios`, `foto_ruta`, `estado`, `fecha_registro`, `motivo_baja`, `comentarios_baja`, `fecha_baja`, `comentarios_encontrado`, `fecha_encontrado`) VALUES
CREATE TABLE `mascotas` (
  `id_mascota` int(11) NOT NULL,
  `id_tutor` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `especie` varchar(50) NOT NULL,
  `especie_personalizada` varchar(50) DEFAULT NULL,
  `raza` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `genero` enum('Macho','Hembra') DEFAULT NULL,
  `tiene_vacuna` tinyint(1) DEFAULT 0,
  `fecha_vacunacion` date DEFAULT NULL,
  `detalle_vacuna` text DEFAULT NULL,
  `esterilizado` tinyint(1) DEFAULT 0,
  `incapacidad` tinyint(1) DEFAULT 0,
  `descripcion_incapacidad` text DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `foto_ruta` varchar(255) DEFAULT NULL,
  `estado` enum('Vivo','Fallecido','Extravío') DEFAULT 'Vivo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo_baja` varchar(50) DEFAULT NULL,
  `comentarios_baja` text DEFAULT NULL,
  `fecha_baja` datetime DEFAULT NULL,
  `comentarios_encontrado` text DEFAULT NULL,
  `fecha_encontrado` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `reportes` (`id_reporte`, `tipo`, `id_referencia`, `tipo_reporte`, `descripcion`, `foto_ruta`, `fecha_reporte`, `estado`, `comentarios_resolucion`, `id_usuario`, `nombre_denunciante`, `telefono_denunciante`, `email_denunciante`) VALUES
CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `tipo` enum('tutor','mascota') NOT NULL,
  `id_referencia` int(11) NOT NULL COMMENT 'ID del tutor o mascota',
  `tipo_reporte` varchar(50) NOT NULL COMMENT 'Maltrato, jauria, etc.',
  `descripcion` text NOT NULL,
  `foto_ruta` varchar(255) DEFAULT NULL,
  `fecha_reporte` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','investigando','resuelto') NOT NULL DEFAULT 'pendiente',
  `comentarios_resolucion` text DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL COMMENT 'Usuario que registró el reporte',
  `nombre_denunciante` varchar(100) DEFAULT NULL,
  `telefono_denunciante` varchar(20) DEFAULT NULL,
  `email_denunciante` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `salud_mascotas` (`id_interaccion`, `id_mascota`, `tipo`, `fecha`, `hora`, `responsable`, `campana_lugar`, `motivo`, `signos_clinicos`, `diagnostico`, `estado_general`, `estado_hidratacion`, `temperatura`, `frecuencia_cardiaca`, `frecuencia_respiratoria`, `medicacion`, `via_administracion`, `primeros_auxilios`, `referido_otro_centro`, `tipo_procedimiento`, `diagnostico_previo`, `riesgos_informados`, `medicacion_previa`, `tipo_anestesia`, `cuidados_postoperatorios`, `observaciones`, `fecha_registro`, `id_firma`, `fecha_firma`, `estado`, `firma_imagen`, `nombre_firmante`, `dni_firmante`) VALUES
CREATE TABLE `salud_mascotas` (
  `id_interaccion` int(11) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `tipo` enum('Consulta','Urgencia','Procedimiento','Vacunación') NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `responsable` varchar(100) NOT NULL,
  `campana_lugar` varchar(100) NOT NULL,
  `motivo` text DEFAULT NULL,
  `signos_clinicos` text DEFAULT NULL,
  `diagnostico` text DEFAULT NULL,
  `estado_general` varchar(100) DEFAULT NULL,
  `estado_hidratacion` varchar(100) DEFAULT NULL,
  `temperatura` decimal(4,2) DEFAULT NULL,
  `frecuencia_cardiaca` varchar(50) DEFAULT NULL,
  `frecuencia_respiratoria` varchar(50) DEFAULT NULL,
  `medicacion` text DEFAULT NULL,
  `via_administracion` varchar(100) DEFAULT NULL,
  `primeros_auxilios` text DEFAULT NULL,
  `referido_otro_centro` tinyint(1) DEFAULT 0,
  `tipo_procedimiento` varchar(100) DEFAULT NULL,
  `diagnostico_previo` text DEFAULT NULL,
  `riesgos_informados` text DEFAULT NULL,
  `medicacion_previa` text DEFAULT NULL,
  `tipo_anestesia` varchar(100) DEFAULT NULL,
  `cuidados_postoperatorios` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_firma` int(11) DEFAULT NULL,
  `fecha_firma` datetime DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'pendiente',
  `firma_imagen` varchar(255) DEFAULT NULL COMMENT 'Ruta de la imagen de firma',
  `nombre_firmante` varchar(100) DEFAULT NULL COMMENT 'Nombre de quien firma',
  `dni_firmante` varchar(20) DEFAULT NULL COMMENT 'DNI de quien firma'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `tutores` (`id_tutor`, `nombre`, `apellido_paterno`, `apellido_materno`, `edad`, `telefono`, `email`, `calle`, `numero_exterior`, `numero_interior`, `colonia`, `codigo_postal`, `foto_ruta`, `fecha_registro`, `acepto_privacidad`) VALUES
CREATE TABLE `tutores` (
  `id_tutor` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido_paterno` varchar(50) NOT NULL,
  `apellido_materno` varchar(50) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `telefono` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `calle` varchar(100) NOT NULL,
  `numero_exterior` varchar(10) NOT NULL,
  `numero_interior` varchar(10) DEFAULT NULL,
  `colonia` varchar(100) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL,
  `foto_ruta` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `acepto_privacidad` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `usuarios` (`id_usuario`, `username`, `password_hash`, `rol`, `nombre_completo`, `campana_lugar`, `fecha_registro`, `ultimo_acceso`, `activo`, `acepto_privacidad`, `comentarios`) VALUES
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','veterinario','registrador') NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `campana_lugar` varchar(150) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `acepto_privacidad` tinyint(1) NOT NULL DEFAULT 0,
  `comentarios` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `vacunas` (`id_vacuna`, `id_mascota`, `nombre_vacuna`, `fecha_aplicacion`, `comentarios`) VALUES
CREATE TABLE `vacunas` (
  `id_vacuna` int(11) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `nombre_vacuna` varchar(100) NOT NULL,
  `fecha_aplicacion` date NOT NULL,
  `comentarios` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
