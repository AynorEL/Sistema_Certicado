-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-06-2025 a las 13:59:41
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `certificados`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `idcargo` int(11) NOT NULL,
  `nombre_cargo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`idcargo`, `nombre_cargo`, `descripcion`) VALUES
(1, 'Administrador', 'Gestiona todo el sistema'),
(2, 'Instructor', 'Dicta los cursos'),
(3, 'Asistente', 'Apoya en la gestión de cursos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `idcategoria` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`idcategoria`, `nombre_categoria`, `descripcion`) VALUES
(1, 'Desarrollo Web', 'Cursos de programación web'),
(2, 'Diseño Gráfico', 'Cursos de diseño y multimedia'),
(3, 'Marketing Digital', 'Cursos de marketing y redes sociales');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `idcliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `direccion` text NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idcliente`, `nombre`, `apellido`, `dni`, `telefono`, `email`, `direccion`, `fecha_registro`, `estado`, `observaciones`) VALUES
(1, 'Luis', 'González', '23456789', '987-123-456', 'luis@ejemplo.com', 'Av. Principal 456', '2025-06-09 16:42:40', 'Activo', NULL),
(4, 'Juan Pérez', 'González', '73452524', '925033437', 'admin@gmail.com', 'jsto', '2025-06-09 16:42:40', 'Activo', NULL),
(5, 'Laura', 'instrucor apellido', '23456789', '980456765', 'test@gmail.com', 'jato', '2025-06-09 16:43:55', 'Activo', NULL),
(6, 'Laura', 'González', '23456789', '987654322', 'admin@gmail.com', 'sd', '2025-06-09 16:50:18', 'Activo', NULL),
(7, 'Recepcionista', 'Ramirez', '23456789', '9250033437', 'admin@gmail.com', 'sdf', '2025-06-09 16:50:59', 'Activo', NULL),
(8, 'Laura', 'instrucor apellido', '72557246', '987654322', 'test@gmail.com', 'sdfhm,', '2025-06-09 17:40:17', 'Activo', NULL),
(9, 'Laura', 'González', '72557246', '925033437', 'usuario17@marketingmasters.lat', 'jato', '2025-06-18 19:00:27', 'Activo', NULL),
(10, 'Juan', 'Pérez', '12345678', '987654321', 'juan@test.com', 'Av. Test 123', '2025-06-18 19:57:13', 'Activo', NULL),
(13, 'yape', 'yape', '73452524', '925033437', 'fel10062003@gmail.com', 'jato', '2025-06-19 10:12:08', 'Activo', NULL),
(14, 'Laura', 'González', '23456789', '925033437', 'mesero@gmail.com', 'jatp', '2025-06-19 20:57:48', 'Activo', NULL),
(15, 'paypal', 'paypal', '73452524', '927481027', 'prupruebas090@gmail.com', 'jato', '2025-06-19 21:09:15', 'Activo', NULL),
(17, 'AYNOR', 'ESPINOZA LEON', '72557246', '930791412', 'prupruebas090@gmail.com', 'jato', '2025-06-19 23:26:25', 'Activo', NULL),
(23, 'invitado', 'invitado', '73452524', '923432567', 'fel10062003@gmail.com', 'Chavin de huantar', '2025-06-20 10:14:06', 'Activo', NULL),
(24, 'AYNOR FIDENCIO', 'ESPINOZA LEON', '72557246', '930791412', 'espinozaleonaynor@gmail.com', 'jato - chavin de huantar', '2025-06-20 12:56:57', 'Activo', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones`
--

CREATE TABLE `configuraciones` (
  `id` int(11) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `favicon` varchar(255) NOT NULL,
  `pie_pagina_descripcion` text NOT NULL,
  `pie_pagina_derechos` text NOT NULL,
  `direccion_contacto` text NOT NULL,
  `correo_contacto` text NOT NULL,
  `telefono_contacto` text NOT NULL,
  `fax_contacto` text NOT NULL,
  `mapa_contacto` text NOT NULL,
  `total_certificados_recientes` int(11) NOT NULL,
  `total_certificados_populares` int(11) NOT NULL,
  `servicios_activos` int(11) NOT NULL,
  `bienvenida_activa` int(11) NOT NULL,
  `certificados_activos` int(11) NOT NULL,
  `boletin_activo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `configuraciones`
--

INSERT INTO `configuraciones` (`id`, `logo`, `favicon`, `pie_pagina_descripcion`, `pie_pagina_derechos`, `direccion_contacto`, `correo_contacto`, `telefono_contacto`, `fax_contacto`, `mapa_contacto`, `total_certificados_recientes`, `total_certificados_populares`, `servicios_activos`, `bienvenida_activa`, `certificados_activos`, `boletin_activo`) VALUES
(1, 'logo_1749757156.png', 'favicon_1749494710.png', 'Sistema de Certificados Profesionales \r\nPlataforma confiable para gestionar cursos, inscripciones, pagos y emisión automatizada de certificados digitales profesionales.', 'Todos los derechos reservados', 'Calle Amauta José Carlos Mariátegui La Chira N° 001 - San Marcos - Huari', 'iestpsanmarcosoficial@gmail.com', '934235654', '123-456-789', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1798.3403420638315!2d-77.15725308695274!3d-9.52348190654787!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91a8fd8c1d97cb3b%3A0xe9ed1dd0601725bc!2sSan%20Marcos-Huari-Ancash!5e1!3m2!1sen!2spe!4v1750436426833!5m2!1sen!2spe\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 6, 6, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_pago`
--

CREATE TABLE `configuracion_pago` (
  `id` int(11) NOT NULL,
  `paypal_email` varchar(255) DEFAULT NULL,
  `paypal_sandbox` tinyint(1) NOT NULL DEFAULT 1,
  `banco_nombre` varchar(100) DEFAULT NULL,
  `banco_cuenta` varchar(50) DEFAULT NULL,
  `banco_titular` varchar(100) DEFAULT NULL,
  `yape_numero` varchar(20) DEFAULT NULL,
  `plin_numero` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_pago`
--

INSERT INTO `configuracion_pago` (`id`, `paypal_email`, `paypal_sandbox`, `banco_nombre`, `banco_cuenta`, `banco_titular`, `yape_numero`, `plin_numero`) VALUES
(1, 'sb-qrscx43668697@personal.example.com', 1, 'Banco de Ejemplo BCP', '191-0000000-0-00', 'Nombre Titular Ejemplo', '987654321', '987654321');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso`
--

CREATE TABLE `curso` (
  `idcurso` int(11) NOT NULL,
  `nombre_curso` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `duracion` int(11) NOT NULL,
  `idcategoria` int(11) NOT NULL,
  `idinstructor` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `diseño` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dias_semana` varchar(100) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `precio` decimal(10,2) DEFAULT 0.00,
  `cupos_disponibles` int(11) DEFAULT 0,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `requisitos` text DEFAULT NULL,
  `objetivos` text DEFAULT NULL,
  `config_certificado` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config_certificado`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `curso`
--

INSERT INTO `curso` (`idcurso`, `nombre_curso`, `descripcion`, `duracion`, `idcategoria`, `idinstructor`, `estado`, `diseño`, `dias_semana`, `hora_inicio`, `hora_fin`, `precio`, `cupos_disponibles`, `fecha_inicio`, `fecha_fin`, `requisitos`, `objetivos`, `config_certificado`) VALUES
(1, 'PHP Avanzado', 'Curso de PHP para desarrolladores', 40, 1, 1, 'Activo', 'curso_diseno_1750480476.png', 'Lunes,Martes,Miércoles,Jueves,Viernes', '00:00:00', '00:00:00', 20.00, 35, '2025-07-01', '2025-08-10', 'Conocimientos básicos de PHP', 'Dominar PHP orientado a objetos y MVC', '{\"campos\":[{\"tipo\":\"alumno\",\"left\":172,\"top\":273,\"width\":527,\"height\":52,\"fontSize\":30,\"fontFamily\":\"Arial\",\"color\":\"rgb(13, 13, 13)\",\"textAlign\":\"center\",\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textDecoration\":\"none\",\"lineHeight\":1.2,\"letterSpacing\":0,\"rotation\":0,\"opacity\":1,\"backgroundColor\":\"transparent\",\"borderWidth\":0,\"borderColor\":\"initial\",\"borderRadius\":0,\"shadowColor\":\"#000000\",\"shadowBlur\":0,\"shadowOffsetX\":0,\"shadowOffsetY\":0,\"texto\":\"AYNOR FIDENCIO ESPINOZA LEON\"},{\"tipo\":\"fecha\",\"left\":329,\"top\":536,\"width\":150,\"height\":25,\"fontSize\":16,\"fontFamily\":\"Arial\",\"color\":\"rgb(0, 0, 0)\",\"textAlign\":\"center\",\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textDecoration\":\"none\",\"lineHeight\":1.2,\"letterSpacing\":0,\"rotation\":0,\"opacity\":1,\"backgroundColor\":\"transparent\",\"borderWidth\":0,\"borderColor\":\"initial\",\"borderRadius\":0,\"shadowColor\":\"#000000\",\"shadowBlur\":0,\"shadowOffsetX\":0,\"shadowOffsetY\":0,\"texto\":\"FECHA\"},{\"tipo\":\"instructor\",\"left\":307,\"top\":490,\"width\":189,\"height\":41,\"fontSize\":16,\"fontFamily\":\"Arial\",\"color\":\"rgb(0, 0, 0)\",\"textAlign\":\"center\",\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textDecoration\":\"none\",\"lineHeight\":1.2,\"letterSpacing\":0,\"rotation\":0,\"opacity\":1,\"backgroundColor\":\"transparent\",\"borderWidth\":0,\"borderColor\":\"initial\",\"borderRadius\":0,\"shadowColor\":\"#000000\",\"shadowBlur\":0,\"shadowOffsetX\":0,\"shadowOffsetY\":0,\"texto\":\"NOMBRE DEL INSTRUCTOR\"},{\"tipo\":\"firma_instructor\",\"left\":282,\"top\":429,\"width\":240,\"height\":65,\"fontSize\":16,\"fontFamily\":\"Arial\",\"color\":\"rgb(0, 0, 0)\",\"textAlign\":\"left\",\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textDecoration\":\"none\",\"lineHeight\":1.2,\"letterSpacing\":0,\"rotation\":0,\"opacity\":1,\"backgroundColor\":\"transparent\",\"borderWidth\":0,\"borderColor\":\"initial\",\"borderRadius\":0,\"shadowColor\":\"#000000\",\"shadowBlur\":0,\"shadowOffsetX\":0,\"shadowOffsetY\":0,\"texto\":\"Firma Instructor\"},{\"tipo\":\"qr\",\"left\":615,\"top\":426,\"width\":120,\"height\":120,\"fontSize\":16,\"fontFamily\":\"Arial\",\"color\":\"rgb(0, 0, 0)\",\"textAlign\":\"left\",\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textDecoration\":\"none\",\"lineHeight\":1.2,\"letterSpacing\":0,\"rotation\":0,\"opacity\":1,\"backgroundColor\":\"transparent\",\"borderWidth\":0,\"borderColor\":\"initial\",\"borderRadius\":0,\"shadowColor\":\"#000000\",\"shadowBlur\":0,\"shadowOffsetX\":0,\"shadowOffsetY\":0,\"texto\":\"Código QR\"}],\"editorScaleX\":2.5,\"editorScaleY\":2.498233215547703,\"imagenOriginal\":{\"width\":2000,\"height\":1414},\"editorDisplay\":{\"width\":800,\"height\":566}}'),
(2, 'Diseño Web', 'Curso de diseño web responsivo', 5, 1, 2, 'Activo', 'curso_diseno_1750480491.png', 'Martes,Miércoles,Jueves,Viernes', '02:02:00', '06:05:00', 70.00, 51, '2025-07-15', '2025-08-20', 'Conocimientos básicos de HTML y CSS', 'Aprender a crear sitios web adaptables a dispositivos', '[{\"id\":\"1750488571218\",\"tipo\":\"alumno\",\"texto\":\"Laura instrucor apellido\",\"left\":\"1350px\",\"top\":\"87px\"},{\"id\":\"1750488762568\",\"tipo\":\"alumno\",\"texto\":\"NOMBRE DEL ALUMNO\",\"left\":\"540px\",\"top\":\"257px\"},{\"id\":\"1750488769477\",\"tipo\":\"instructor\",\"texto\":\"Juan Pérez\",\"left\":\"574px\",\"top\":\"465px\"},{\"id\":\"1750488774570\",\"tipo\":\"especialista\",\"texto\":\"Laura Díaz\",\"left\":\"576px\",\"top\":\"465px\"},{\"id\":\"1750488779100\",\"tipo\":\"firma_instructor\",\"texto\":\"<img src=\\\"..\\/assets\\/uploads\\/firmas\\/instructor_firma_1750463403.png\\\" style=\\\"max-width: 120px; max-height: 60px;\\\">\",\"left\":\"583px\",\"top\":\"390px\"},{\"id\":\"1750488784712\",\"tipo\":\"firma_especialista\",\"texto\":\"<img src=\\\"..\\/assets\\/uploads\\/firmas\\/especialista_firma_1750463794.png\\\" style=\\\"max-width: 120px; max-height: 60px;\\\">\",\"left\":\"580px\",\"top\":\"388px\"},{\"id\":\"1750488790053\",\"tipo\":\"fecha\",\"texto\":\"FECHA DE EMISIÓN\",\"left\":\"556px\",\"top\":\"506px\"},{\"id\":\"1750488799365\",\"tipo\":\"qr\",\"texto\":\"<img src=\\\"https:\\/\\/api.qrserver.com\\/v1\\/create-qr-code\\/?size=200x200&amp;data=%257B%2522curso_id%2522%253A2%252C%2522alumno_id%2522%253A5%252C%2522fecha_emision%2522%253A%25222025-06-21%2522%252C%2522verificacion_url%2522%253A%2522http%253A%255C%252F%255C%252Flocalhost%255C%252Fcertificado%255C%252Fverificar-certificado.php%2522%257D&amp;format=png&amp;margin=2&amp;ecc=M&amp;logo=http%253A%252F%252Flocalhost%252Fcertificado%252Fadmin%252Fimg%252Flogo.png&amp;logo_size=30%25&amp;logo_bg=FFFFFF&amp;logo_radius=10\\\" style=\\\"width: 120px; height: 120px; border: 1px solid #ddd; border-radius: 8px;\\\" alt=\\\"QR con Logo\\\">\",\"left\":\"803px\",\"top\":\"373px\"}]'),
(4, 'Excel Avanzado', 'Curso completo para dominar Excel avanzado', 1, 1, 1, 'Activo', 'curso_diseno_1750480506.png', 'Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo', '18:24:00', '18:25:00', 34.00, 41, '2025-06-09', '2025-06-13', 'Conocimientos básicos de Excel', 'Aprender funciones avanzadas y herramientas para ser profesional en Excel', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `idempleado` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `idcargo` int(11) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`idempleado`, `nombre`, `apellido`, `dni`, `idcargo`, `telefono`, `email`) VALUES
(1, 'Carlos', 'Rodríguez', '12345678', 1, '987-654-321', 'carlos@ejemplo.com'),
(2, 'Ana', 'Martínez', '87654321', 3, '123-456-789', 'ana@ejemplo.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entidad`
--

CREATE TABLE `entidad` (
  `identidad` int(11) NOT NULL,
  `nombre_entidad` varchar(100) NOT NULL,
  `ruc` varchar(20) NOT NULL,
  `direccion` text NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `entidad`
--

INSERT INTO `entidad` (`identidad`, `nombre_entidad`, `ruc`, `direccion`, `telefono`, `email`) VALUES
(1, 'Empresa ABC', '20123456789', 'Av. Industrial 123', '456-789-123', 'contacto@empresaabc.com'),
(2, 'Instituto XYZ', '20123456790', 'Av. Educativa 457', '789-123-456', 'info@institutoxyz.com'),
(3, 'fis', '345', 'sdfdghjkkdbnm,', '987654322', 'admin@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialista`
--

CREATE TABLE `especialista` (
  `idespecialista` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `especialidad` varchar(100) NOT NULL,
  `experiencia` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `firma_especialista` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `especialista`
--

INSERT INTO `especialista` (`idespecialista`, `nombre`, `apellido`, `especialidad`, `experiencia`, `email`, `telefono`, `firma_especialista`) VALUES
(1, 'Roberto', 'Sánchez', 'Desarrollo Web', 8, 'roberto@ejemplo.com', '456-789-123', 'especialista_firma_1750463803.png'),
(2, 'Laura', 'Díaz', 'Diseño UI/UX', 6, 'laura@ejemplo.com', '789-456-123', 'especialista_firma_1750463794.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genero`
--

CREATE TABLE `genero` (
  `idgenero` int(11) NOT NULL,
  `nombre_genero` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `genero`
--

INSERT INTO `genero` (`idgenero`, `nombre_genero`) VALUES
(1, 'Masculino'),
(2, 'Femenino'),
(3, 'Otro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hora_lectiva`
--

CREATE TABLE `hora_lectiva` (
  `idhora_lectiva` int(11) NOT NULL,
  `idcurso` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `tema` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `hora_lectiva`
--

INSERT INTO `hora_lectiva` (`idhora_lectiva`, `idcurso`, `fecha`, `hora_inicio`, `hora_fin`, `tema`) VALUES
(1, 1, '2024-03-01', '09:00:00', '11:00:00', 'Introducción a PHP'),
(2, 1, '2024-03-02', '09:00:00', '11:00:00', 'Variables y Operadores');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `idioma`
--

CREATE TABLE `idioma` (
  `id_idioma` int(11) NOT NULL,
  `nombre_idioma` varchar(255) NOT NULL,
  `valor_idioma` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `idioma`
--

INSERT INTO `idioma` (`id_idioma`, `nombre_idioma`, `valor_idioma`) VALUES
(1, 'Moneda', 'S/'),
(2, 'Buscar Curso', 'Buscar Curso'),
(3, 'Buscar', 'Buscar'),
(4, 'Enviar', 'Enviar'),
(5, 'Actualizar', 'Actualizar'),
(6, 'Leer Más', 'Leer Más'),
(7, 'Serie', 'Serie'),
(8, 'Foto', 'Foto'),
(9, 'Iniciar Sesión', 'Iniciar Sesión'),
(10, 'Inicio de Sesión Estudiante', 'Inicio de Sesión Estudiante'),
(11, 'Haz clic aquí para iniciar sesión', 'Haz clic aquí para iniciar sesión'),
(12, 'Volver a la página de inicio de sesión', 'Volver a la página de inicio de sesión'),
(13, 'Conectado como', 'Conectado como'),
(14, 'Cerrar Sesión', 'Cerrar Sesión'),
(15, 'Registrarse', 'Registrarse'),
(16, 'Registro de Estudiante', 'Registro de Estudiante'),
(17, 'Registro Exitoso', 'Registro Exitoso'),
(18, 'Mis Cursos', 'Mis Cursos'),
(19, 'Ver Mis Cursos', 'Ver Mis Cursos'),
(20, 'Actualizar Cursos', 'Actualizar Cursos'),
(21, 'Volver a Mis Cursos', 'Volver a Mis Cursos'),
(22, 'Inscripción', 'Inscripción'),
(23, 'Proceder a Inscripción', 'Proceder a Inscripción'),
(24, 'Certificados', 'Certificados'),
(25, 'Historial de Certificados', 'Historial de Certificados'),
(26, 'Detalles del Certificado', 'Detalles del Certificado'),
(27, 'Fecha y Hora de Emisión', 'Fecha y Hora de Emisión'),
(28, 'ID de Certificado', 'ID de Certificado'),
(29, 'Estado del Certificado', 'Estado del Certificado'),
(30, 'Método de Pago', 'Método de Pago'),
(31, 'ID de Pago', 'ID de Pago'),
(32, 'Sección de Pago', 'Sección de Pago'),
(33, 'Seleccionar Método de Pago', 'Seleccionar Método de Pago'),
(34, 'Seleccionar un Método', 'Seleccionar un Método'),
(35, 'PayPal', 'PayPal'),
(36, 'Stripe', 'Stripe'),
(37, 'Depósito Bancario', 'Depósito Bancario'),
(38, 'Número de Tarjeta', 'Número de Tarjeta'),
(39, 'CVV', 'CVV'),
(40, 'Mes', 'Mes'),
(41, 'Año', 'Año'),
(42, 'Enviar a estos Detalles', 'Enviar a estos Detalles'),
(43, 'Información de Transacción', 'Información de Transacción'),
(44, 'Incluir ID de transacción y otra información correctamente', 'Incluir ID de transacción y otra información correctamente'),
(45, 'Pagar Ahora', 'Pagar Ahora'),
(46, 'Nombre del Curso', 'Nombre del Curso'),
(47, 'Detalles del Curso', 'Detalles del Curso'),
(48, 'Categorías', 'Categorías'),
(49, 'Categoría:', 'Categoría:'),
(50, 'Todos los Cursos de', 'Todos los Cursos de'),
(51, 'Duración del Curso', 'Duración del Curso'),
(52, 'Horas', 'Horas'),
(53, 'Compartir', 'Compartir'),
(54, 'Compartir este Curso', 'Compartir este Curso'),
(55, 'Descripción del Curso', 'Descripción del Curso'),
(56, 'Características', 'Características'),
(57, 'Requisitos', 'Requisitos'),
(58, 'Política de Certificación', 'Política de Certificación'),
(59, 'Reseñas', 'Reseñas'),
(60, 'Reseña', 'Reseña'),
(61, 'Dejar una Reseña', 'Dejar una Reseña'),
(62, 'Escribe tu comentario (Opcional)', 'Escribe tu comentario (Opcional)'),
(63, 'Enviar Reseña', 'Enviar Reseña'),
(64, 'Ya has dejado una calificación', 'Ya has dejado una calificación'),
(65, 'Debes iniciar sesión para dejar una reseña', 'Debes iniciar sesión para dejar una reseña'),
(66, 'No se encontró descripción', 'No se encontró descripción'),
(67, 'No se encontraron características', 'No se encontraron características'),
(68, 'No se encontraron requisitos', 'No se encontraron requisitos'),
(69, 'No se encontró política de certificación', 'No se encontró política de certificación'),
(70, 'No se encontraron reseñas', 'No se encontraron reseñas'),
(71, 'Nombre del Estudiante', 'Nombre del Estudiante'),
(72, 'Comentario', 'Comentario'),
(73, 'Comentarios', 'Comentarios'),
(74, 'Calificación', 'Calificación'),
(75, 'Anterior', 'Anterior'),
(76, 'Siguiente', 'Siguiente'),
(77, 'Subtotal', 'Subtotal'),
(78, 'Total', 'Total'),
(79, 'Acción', 'Acción'),
(80, 'Costo de Inscripción', 'Costo de Inscripción'),
(81, 'Continuar con Inscripción', 'Continuar con Inscripción'),
(82, 'Actualizar Perfil', 'Actualizar Perfil'),
(83, 'Actualizar Información Personal', 'Actualizar Información Personal'),
(84, 'Tablero', 'Tablero'),
(85, 'Bienvenido al Tablero', 'Bienvenido al Tablero'),
(86, 'Volver al Tablero', 'Volver al Tablero'),
(87, 'Suscribirse', 'Suscribirse'),
(88, 'Suscríbete a Nuestro Boletín', 'Suscríbete a Nuestro Boletín'),
(89, 'Correo Electrónico', 'Correo Electrónico'),
(90, 'Ingresa tu Correo Electrónico', 'Ingresa tu Correo Electrónico'),
(91, 'Contraseña', 'Contraseña'),
(92, 'Olvidé mi Contraseña', 'Olvidé mi Contraseña'),
(93, 'Confirmar Contraseña', 'Confirmar Contraseña'),
(94, 'Actualizar Contraseña', 'Actualizar Contraseña'),
(95, 'Nueva Contraseña', 'Nueva Contraseña'),
(96, 'Confirmar Nueva Contraseña', 'Confirmar Nueva Contraseña'),
(97, 'Nombre Completo', 'Nombre Completo'),
(98, 'DNI', 'DNI'),
(99, 'Teléfono', 'Teléfono'),
(100, 'Dirección', 'Dirección'),
(101, 'País', 'País'),
(102, 'Ciudad', 'Ciudad'),
(103, 'Distrito', 'Distrito'),
(104, 'Código Postal', 'Código Postal'),
(105, 'Acerca de Nosotros', 'Acerca de Nosotros'),
(106, 'Cursos Destacados', 'Cursos Destacados'),
(107, 'Cursos Populares', 'Cursos Populares'),
(108, 'Cursos Recientes', 'Cursos Recientes'),
(109, 'Información de Contacto', 'Información de Contacto'),
(110, 'Formulario de Contacto', 'Formulario de Contacto'),
(111, 'Nuestra Oficina', 'Nuestra Oficina'),
(112, 'Enviar Mensaje', 'Enviar Mensaje'),
(113, 'Mensaje', 'Mensaje'),
(114, 'Encuéntranos en el Mapa', 'Encuéntranos en el Mapa'),
(115, '¡Felicidades! El pago fue exitoso.', '¡Felicidades! El pago fue exitoso.'),
(116, 'La información personal se actualizó correctamente.', 'La información personal se actualizó correctamente.'),
(117, 'El nombre no puede estar vacío.', 'El nombre no puede estar vacío.'),
(118, 'El teléfono no puede estar vacío.', 'El teléfono no puede estar vacío.'),
(119, 'La dirección no puede estar vacía.', 'La dirección no puede estar vacía.'),
(120, 'Debes seleccionar un país.', 'Debes seleccionar un país.'),
(121, 'La ciudad no puede estar vacía.', 'La ciudad no puede estar vacía.'),
(122, 'El distrito no puede estar vacío.', 'El distrito no puede estar vacío.'),
(123, 'El código postal no puede estar vacío.', 'El código postal no puede estar vacío.'),
(124, 'La información del perfil se actualizó correctamente.', 'La información del perfil se actualizó correctamente.'),
(125, 'El correo electrónico no puede estar vacío', 'El correo electrónico no puede estar vacío'),
(126, 'El correo electrónico y/o la contraseña no pueden estar vacíos.', 'El correo electrónico y/o la contraseña no pueden estar vacíos.'),
(127, 'El correo electrónico no coincide.', 'El correo electrónico no coincide.'),
(128, 'El correo electrónico debe ser válido.', 'El correo electrónico debe ser válido.'),
(129, 'Tu correo electrónico no se encuentra en nuestro sistema.', 'Tu correo electrónico no se encuentra en nuestro sistema.'),
(130, 'Por favor, revisa tu correo electrónico y confirma tu suscripción.', 'Por favor, revisa tu correo electrónico y confirma tu suscripción.'),
(131, 'Tu correo electrónico ha sido verificado con éxito. Ahora puedes iniciar sesión.', 'Tu correo electrónico ha sido verificado con éxito. Ahora puedes iniciar sesión.'),
(132, 'La contraseña no puede estar vacía.', 'La contraseña no puede estar vacía.'),
(133, 'Las contraseñas no coinciden.', 'Las contraseñas no coinciden.'),
(134, 'Por favor, ingresa y confirma las contraseñas.', 'Por favor, ingresa y confirma las contraseñas.'),
(135, 'La contraseña se actualizó correctamente.', 'La contraseña se actualizó correctamente.'),
(136, 'Para restablecer tu contraseña, haz clic en el siguiente enlace.', 'Para restablecer tu contraseña, haz clic en el siguiente enlace.'),
(137, 'SOLICITUD DE RESTABLECIMIENTO DE CONTRASEÑA - SISTEMA DE CERTIFICADOS', 'SOLICITUD DE RESTABLECIMIENTO DE CONTRASEÑA - SISTEMA DE CERTIFICADOS'),
(138, 'El tiempo del correo electrónico de restablecimiento (24 horas) ha expirado. Por favor, intenta nuevamente.', 'El tiempo del correo electrónico de restablecimiento (24 horas) ha expirado. Por favor, intenta nuevamente.'),
(139, 'Se ha enviado un enlace de confirmación a tu correo electrónico.', 'Se ha enviado un enlace de confirmación a tu correo electrónico.'),
(140, 'La contraseña se restableció correctamente. Ahora puedes iniciar sesión.', 'La contraseña se restableció correctamente. Ahora puedes iniciar sesión.'),
(141, 'El correo electrónico ya existe', 'El correo electrónico ya existe'),
(142, 'Lo siento. ¡Tu cuenta está inactiva! Por favor, contacta con el administrador.', 'Lo siento. ¡Tu cuenta está inactiva! Por favor, contacta con el administrador.'),
(143, 'Cambiar Contraseña', 'Cambiar Contraseña'),
(144, 'Confirmación de Registro - Sistema de Certificados', 'Confirmación de Registro - Sistema de Certificados'),
(145, '¡Gracias por registrarte! Tu cuenta ha sido creada. Para activarla, haz clic en el siguiente enlace:', '¡Gracias por registrarte! Tu cuenta ha sido creada. Para activarla, haz clic en el siguiente enlace:'),
(146, 'Tu registro ha sido completado. Revisa tu correo electrónico para confirmar tu registro.', 'Tu registro ha sido completado. Revisa tu correo electrónico para confirmar tu registro.'),
(147, 'No se encontraron cursos', 'No se encontraron cursos'),
(148, 'Inscribirse', 'Inscribirse'),
(149, 'Cursos Relacionados', 'Cursos Relacionados'),
(150, 'Ver todos los cursos relacionados abajo', 'Ver todos los cursos relacionados abajo'),
(151, 'Duración', 'Duración'),
(152, 'Precio', 'Precio'),
(153, 'Por favor, inicia sesión como estudiante para inscribirte', 'Por favor, inicia sesión como estudiante para inscribirte'),
(154, 'Información Personal', 'Información Personal'),
(155, '¡La calificación se ha enviado correctamente!', '¡La calificación se ha enviado correctamente!');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripcion`
--

CREATE TABLE `inscripcion` (
  `idinscripcion` int(11) NOT NULL,
  `idcliente` int(11) NOT NULL,
  `idcurso` int(11) NOT NULL,
  `fecha_inscripcion` date NOT NULL,
  `estado` enum('Pendiente','Aprobado','Rechazado','Cancelado') DEFAULT 'Pendiente',
  `nota_final` decimal(10,2) DEFAULT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `monto_pago` decimal(10,2) DEFAULT 0.00,
  `estado_pago` enum('Pendiente','Pagado','Reembolsado') DEFAULT 'Pendiente',
  `metodo_pago` varchar(50) DEFAULT NULL,
  `comprobante_pago` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `inscripcion`
--

INSERT INTO `inscripcion` (`idinscripcion`, `idcliente`, `idcurso`, `fecha_inscripcion`, `estado`, `nota_final`, `fecha_aprobacion`, `observaciones`, `monto_pago`, `estado_pago`, `metodo_pago`, `comprobante_pago`) VALUES
(8, 5, 2, '2025-06-19', 'Pendiente', NULL, NULL, '', 70.00, 'Pendiente', 'PayPal', NULL),
(16, 14, 4, '2025-06-19', 'Pendiente', NULL, NULL, NULL, 34.00, 'Pendiente', 'Plin', NULL),
(17, 4, 4, '2025-06-19', 'Pendiente', NULL, NULL, NULL, 34.00, 'Pendiente', 'Yape', NULL),
(18, 4, 4, '2025-06-19', 'Pendiente', NULL, NULL, NULL, 34.00, 'Pendiente', 'Yape', NULL),
(19, 13, 4, '2025-06-19', 'Pendiente', NULL, NULL, NULL, 34.00, 'Pendiente', 'Yape', NULL),
(20, 15, 4, '2025-06-19', 'Pendiente', NULL, NULL, NULL, 34.00, 'Pendiente', 'Yape', NULL),
(21, 13, 4, '2025-06-19', 'Pendiente', NULL, NULL, NULL, 34.00, 'Pendiente', 'Yape', NULL),
(22, 13, 1, '2025-06-19', 'Pendiente', NULL, NULL, NULL, 20.00, 'Pendiente', 'Yape', NULL),
(23, 24, 1, '2025-06-20', 'Pendiente', NULL, NULL, NULL, 20.00, 'Pendiente', 'Yape', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instructor`
--

CREATE TABLE `instructor` (
  `idinstructor` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `especialidad` varchar(100) NOT NULL,
  `experiencia` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `firma_digital` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `instructor`
--

INSERT INTO `instructor` (`idinstructor`, `nombre`, `apellido`, `especialidad`, `experiencia`, `email`, `telefono`, `firma_digital`) VALUES
(1, 'Juan', 'Pérez', 'Desarrollo Web', 5, 'juan@ejemplo.com', '123-456-789', 'instructor_firma_1750496053.png'),
(2, 'María', 'García', 'Diseño Gráfico', 3, 'maria@ejemplo.com', '987-654-321', 'instructor_firma_1750496041.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo`
--

CREATE TABLE `modulo` (
  `idmodulo` int(11) NOT NULL,
  `nombre_modulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `idcurso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `modulo`
--

INSERT INTO `modulo` (`idmodulo`, `nombre_modulo`, `descripcion`, `idcurso`) VALUES
(1, 'Fundamentos de PHP', 'Conceptos básicos de PHP', 1),
(2, 'PHP Avanzado', 'Temas avanzados de PHP', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo_rol`
--

CREATE TABLE `modulo_rol` (
  `idmodulo_rol` int(11) NOT NULL,
  `idmodulo` int(11) NOT NULL,
  `idrol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `modulo_rol`
--

INSERT INTO `modulo_rol` (`idmodulo_rol`, `idmodulo`, `idrol`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paginas`
--

CREATE TABLE `paginas` (
  `id` int(11) NOT NULL,
  `nombre_pagina` varchar(255) NOT NULL,
  `slug_pagina` varchar(255) NOT NULL,
  `contenido_pagina` text NOT NULL,
  `banner_pagina` varchar(255) NOT NULL,
  `meta_titulo` varchar(255) NOT NULL,
  `meta_palabras_clave` text NOT NULL,
  `meta_descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `paginas`
--

INSERT INTO `paginas` (`id`, `nombre_pagina`, `slug_pagina`, `contenido_pagina`, `banner_pagina`, `meta_titulo`, `meta_palabras_clave`, `meta_descripcion`) VALUES
(1, 'Acerca de Nosotros', 'acerca-de-nosotros', '<p data-start=\"73\" data-end=\"409\"><strong data-start=\"73\" data-end=\"210\">Somos una institución comprometida con la formación profesional de calidad, orientada al desarrollo integral de nuestros estudiantes.</strong> Nuestro objetivo es brindar una educación actualizada, pertinente y accesible, que responda a las demandas del entorno laboral y fomente el crecimiento personal y profesional de cada participante.</p>\r\n<p data-start=\"411\" data-end=\"759\">Contamos con un equipo docente calificado, programas de estudio innovadores y recursos tecnológicos que permiten una enseñanza efectiva y dinámica. Nos enfocamos en fortalecer competencias técnicas, habilidades blandas y valores éticos, promoviendo así el éxito de nuestros egresados en un mercado laboral competitivo y en constante transformación.</p>', 'pagina_6855b894a56ab.jpg', 'Acerca de Nosotros', 'certificados, formación, profesional, formacion', 'Conozca más sobre nuestra institución');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `idpago` int(11) NOT NULL,
  `idinscripcion` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` datetime NOT NULL DEFAULT current_timestamp(),
  `metodo_pago` enum('PayPal','Transferencia Bancaria','Yape','Plin') NOT NULL,
  `estado` enum('Pendiente','Completado','Reembolsado','Cancelado') NOT NULL DEFAULT 'Pendiente',
  `txn_id` varchar(100) DEFAULT NULL,
  `comprobante` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pago`
--

INSERT INTO `pago` (`idpago`, `idinscripcion`, `monto`, `fecha_pago`, `metodo_pago`, `estado`, `txn_id`, `comprobante`, `observaciones`) VALUES
(16, 16, 40.12, '2025-06-19 21:08:14', 'Plin', '', NULL, 'plin_16_1750385294.png', NULL),
(17, 20, 34.00, '2025-06-19 21:09:32', 'Yape', '', NULL, 'yape_20_1750385372.jpg', NULL),
(18, 21, 34.00, '2025-06-19 21:11:27', 'Yape', '', NULL, 'yape_21_1750385487.jpg', NULL),
(20, 23, 20.00, '2025-06-20 18:04:03', 'Yape', '', NULL, 'yape_23_1750460643.jpg', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_frecuentes`
--

CREATE TABLE `preguntas_frecuentes` (
  `id` int(11) NOT NULL,
  `titulo_pregunta` varchar(255) NOT NULL,
  `contenido_pregunta` text NOT NULL,
  `orden_pregunta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `preguntas_frecuentes`
--

INSERT INTO `preguntas_frecuentes` (`id`, `titulo_pregunta`, `contenido_pregunta`, `orden_pregunta`) VALUES
(1, '¿Cómo obtener un certificado?', 'Una vez que te hayas inscrito a un curso, completado las clases, y tu inscripción sea aprobada, podrás descargar tu certificado desde tu panel de usuario. Si tu curso fue aprobado, aparecerá un botón que dice \"Descargar Certificado\". Haz clic allí para obtenerlo en formato PDF.', 1),
(2, '¿Son válidos los certificados?', 'Sí, nuestros certificados son reconocidos', 2),
(3, '¿Por qué no puedo descargar mi certificado?', 'Para poder descargar tu certificado, tu inscripción debe estar aprobada y tu nota final debe haber sido registrada. Si aún no aparece el botón de descarga, asegúrate de haber completado todas las clases y espera la revisión del instructor. También puedes comunicarte con soporte si el problema persiste.', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `redes_sociales`
--

CREATE TABLE `redes_sociales` (
  `id_red` int(11) NOT NULL,
  `nombre_red` varchar(30) NOT NULL,
  `url_red` varchar(255) NOT NULL,
  `icono_red` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `redes_sociales`
--

INSERT INTO `redes_sociales` (`id_red`, `nombre_red`, `url_red`, `icono_red`) VALUES
(1, 'Facebook', 'https://facebook.com/certificados', 'fab fa-facebook'),
(2, 'Twitter', 'https://twitter.com/certificados', 'fab fa-twitter'),
(3, 'LinkedIn', 'https://twitter.com/certificados', 'fab fa-linkedin'),
(4, 'YouTube', '', 'fab fa-youtube'),
(5, 'Instagram', '', 'fab fa-instagram'),
(6, 'WhatsApp', '', 'fab fa-whatsapp'),
(7, 'Pinterest', '', 'fab fa-pinterest'),
(8, 'TikTok', '', 'fab fa-tiktok');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `idrol` int(11) NOT NULL,
  `nombre_rol` varchar(100) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`idrol`, `nombre_rol`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema'),
(2, 'Instructor', 'Gestiona cursos y certificados'),
(3, 'Estudiante actua', 'Acceso a cursos y certificados'),
(5, 'fd', 'sdg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `foto` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `titulo`, `contenido`, `foto`) VALUES
(1, 'Certificados Profesionales', 'Ofrecemos certificados reconocidos...', 'servicio1.jpg'),
(2, 'Cursos Online', 'Aprende desde cualquier lugar...', 'servicio2.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `texto_boton` varchar(255) NOT NULL,
  `url_boton` varchar(255) NOT NULL,
  `posicion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `sliders`
--

INSERT INTO `sliders` (`id`, `foto`, `titulo`, `contenido`, `texto_boton`, `url_boton`, `posicion`) VALUES
(1, 'slider-1750451732.jpg', 'Certificados Profesionales', 'Obtén tu certificado profesional', 'Ver Cursos', 'cursos.php', 'Izquierda'),
(2, 'slider-1750497984.jpg', 'Cursos Online', 'Aprende desde cualquier lugar', 'Inscribirse', 'registro.php', 'derecha');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suscriptores`
--

CREATE TABLE `suscriptores` (
  `id_suscriptor` int(11) NOT NULL,
  `correo_suscriptor` varchar(255) NOT NULL,
  `activo` int(11) NOT NULL,
  `fecha_suscripcion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `suscriptores`
--

INSERT INTO `suscriptores` (`id_suscriptor`, `correo_suscriptor`, `activo`, `fecha_suscripcion`) VALUES
(10, 'test@gmail.com', 1, '2025-06-09 14:18:43'),
(11, 'admin@gmail.com', 1, '2025-06-09 14:25:14'),
(13, 'prupruebas090@gmail.com', 1, '2025-06-09 14:39:11'),
(14, 'usuario17@marketingmasters.lat', 1, '2025-06-09 17:40:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idusuario` int(11) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `idrol` int(11) NOT NULL,
  `estado` varchar(20) DEFAULT 'Activo',
  `token` varchar(255) DEFAULT NULL,
  `idcliente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nombre_usuario`, `password`, `idrol`, `estado`, `token`, `idcliente`) VALUES
(17, 'prupruebas090@gmail.com', '$2y$10$j5atv4pVM0clphQsO.f1TuMZnSEXFOWZ/xo430ztxgOTgKegIpok2', 3, 'Activo', NULL, 17),
(32, 'fel10062003@gmail.com', '$2y$10$ZT7qRHNkxnoWYuq.hj7nZeFdJsWrUwFjTK0yitHzXOQjHYHoG1dsi', 3, 'Activo', '0dc64cb6dbb5bf92c7275e0968e9abb5509a68ba99017cfd30ef0fd46c917374', 23),
(33, 'espinozaleonaynor@gmail.com', '$2y$10$oNR3spr6flAo3xi0kITlN.FJE1ImoZIam22f9DRmfYg/0bZkDt1A6', 3, 'Activo', 'dbec9438bd3d4ffff50da837559dc07dadc7c88e2245089e8b217c30ffb14334', 24);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_admin`
--

CREATE TABLE `usuarios_admin` (
  `id_usuario` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `telefono` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `rol` varchar(30) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `remember_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `usuarios_admin`
--

INSERT INTO `usuarios_admin` (`id_usuario`, `nombre_completo`, `correo`, `telefono`, `contrasena`, `foto`, `rol`, `estado`, `remember_token`) VALUES
(1, 'Admin', 'admin@admin.com', '123-456-789', '$2y$10$jzwQ.v1nvpa3QDmcJeszPu7p4fWtflVjllOvYp80YbSlli9oLc92a', 'user-1-1749423283.jpg', 'Super Admin', 'Activo', '0bc92ac253e8c1c070953c12d33182389566383ed0ff42d8631ed0f9389e3a2b');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`idcargo`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`idcategoria`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idcliente`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_dni` (`dni`);

--
-- Indices de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion_pago`
--
ALTER TABLE `configuracion_pago`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`idcurso`),
  ADD KEY `idcategoria` (`idcategoria`),
  ADD KEY `idinstructor` (`idinstructor`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_inicio` (`fecha_inicio`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`idempleado`),
  ADD KEY `idcargo` (`idcargo`);

--
-- Indices de la tabla `entidad`
--
ALTER TABLE `entidad`
  ADD PRIMARY KEY (`identidad`);

--
-- Indices de la tabla `especialista`
--
ALTER TABLE `especialista`
  ADD PRIMARY KEY (`idespecialista`);

--
-- Indices de la tabla `genero`
--
ALTER TABLE `genero`
  ADD PRIMARY KEY (`idgenero`);

--
-- Indices de la tabla `hora_lectiva`
--
ALTER TABLE `hora_lectiva`
  ADD PRIMARY KEY (`idhora_lectiva`),
  ADD KEY `idcurso` (`idcurso`);

--
-- Indices de la tabla `idioma`
--
ALTER TABLE `idioma`
  ADD PRIMARY KEY (`id_idioma`);

--
-- Indices de la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD PRIMARY KEY (`idinscripcion`),
  ADD KEY `idcliente` (`idcliente`),
  ADD KEY `idcurso` (`idcurso`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_inscripcion` (`fecha_inscripcion`),
  ADD KEY `idx_estado_pago` (`estado_pago`);

--
-- Indices de la tabla `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`idinstructor`);

--
-- Indices de la tabla `modulo`
--
ALTER TABLE `modulo`
  ADD PRIMARY KEY (`idmodulo`),
  ADD KEY `idcurso` (`idcurso`);

--
-- Indices de la tabla `modulo_rol`
--
ALTER TABLE `modulo_rol`
  ADD PRIMARY KEY (`idmodulo_rol`),
  ADD KEY `idmodulo` (`idmodulo`),
  ADD KEY `idrol` (`idrol`);

--
-- Indices de la tabla `paginas`
--
ALTER TABLE `paginas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`idpago`),
  ADD KEY `idinscripcion` (`idinscripcion`);

--
-- Indices de la tabla `preguntas_frecuentes`
--
ALTER TABLE `preguntas_frecuentes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `redes_sociales`
--
ALTER TABLE `redes_sociales`
  ADD PRIMARY KEY (`id_red`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idrol`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `suscriptores`
--
ALTER TABLE `suscriptores`
  ADD PRIMARY KEY (`id_suscriptor`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idusuario`),
  ADD KEY `idrol` (`idrol`),
  ADD KEY `idx_idcliente` (`idcliente`);

--
-- Indices de la tabla `usuarios_admin`
--
ALTER TABLE `usuarios_admin`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `idcargo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `idcategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `configuracion_pago`
--
ALTER TABLE `configuracion_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `curso`
--
ALTER TABLE `curso`
  MODIFY `idcurso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `idempleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `entidad`
--
ALTER TABLE `entidad`
  MODIFY `identidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `especialista`
--
ALTER TABLE `especialista`
  MODIFY `idespecialista` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `genero`
--
ALTER TABLE `genero`
  MODIFY `idgenero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `hora_lectiva`
--
ALTER TABLE `hora_lectiva`
  MODIFY `idhora_lectiva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `idioma`
--
ALTER TABLE `idioma`
  MODIFY `id_idioma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT de la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  MODIFY `idinscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `instructor`
--
ALTER TABLE `instructor`
  MODIFY `idinstructor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `modulo`
--
ALTER TABLE `modulo`
  MODIFY `idmodulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `modulo_rol`
--
ALTER TABLE `modulo_rol`
  MODIFY `idmodulo_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `paginas`
--
ALTER TABLE `paginas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `idpago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `preguntas_frecuentes`
--
ALTER TABLE `preguntas_frecuentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `redes_sociales`
--
ALTER TABLE `redes_sociales`
  MODIFY `id_red` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `suscriptores`
--
ALTER TABLE `suscriptores`
  MODIFY `id_suscriptor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `usuarios_admin`
--
ALTER TABLE `usuarios_admin`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `curso`
--
ALTER TABLE `curso`
  ADD CONSTRAINT `curso_ibfk_1` FOREIGN KEY (`idcategoria`) REFERENCES `categoria` (`idcategoria`),
  ADD CONSTRAINT `curso_ibfk_2` FOREIGN KEY (`idinstructor`) REFERENCES `instructor` (`idinstructor`);

--
-- Filtros para la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD CONSTRAINT `empleado_ibfk_1` FOREIGN KEY (`idcargo`) REFERENCES `cargo` (`idcargo`);

--
-- Filtros para la tabla `hora_lectiva`
--
ALTER TABLE `hora_lectiva`
  ADD CONSTRAINT `hora_lectiva_ibfk_1` FOREIGN KEY (`idcurso`) REFERENCES `curso` (`idcurso`);

--
-- Filtros para la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD CONSTRAINT `fk_inscripcion_cliente` FOREIGN KEY (`idcliente`) REFERENCES `cliente` (`idcliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscripcion_curso` FOREIGN KEY (`idcurso`) REFERENCES `curso` (`idcurso`) ON UPDATE CASCADE,
  ADD CONSTRAINT `inscripcion_ibfk_1` FOREIGN KEY (`idcliente`) REFERENCES `cliente` (`idcliente`),
  ADD CONSTRAINT `inscripcion_ibfk_2` FOREIGN KEY (`idcurso`) REFERENCES `curso` (`idcurso`);

--
-- Filtros para la tabla `modulo`
--
ALTER TABLE `modulo`
  ADD CONSTRAINT `modulo_ibfk_1` FOREIGN KEY (`idcurso`) REFERENCES `curso` (`idcurso`);

--
-- Filtros para la tabla `modulo_rol`
--
ALTER TABLE `modulo_rol`
  ADD CONSTRAINT `modulo_rol_ibfk_1` FOREIGN KEY (`idmodulo`) REFERENCES `modulo` (`idmodulo`),
  ADD CONSTRAINT `modulo_rol_ibfk_2` FOREIGN KEY (`idrol`) REFERENCES `rol` (`idrol`);

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `fk_pago_inscripcion` FOREIGN KEY (`idinscripcion`) REFERENCES `inscripcion` (`idinscripcion`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_cliente` FOREIGN KEY (`idcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`idrol`) REFERENCES `rol` (`idrol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
