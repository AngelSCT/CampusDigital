--
-- PostgreSQL database dump
--

\restrict 6TLOtapsDmIU4UIzeccuSj7AV18Y50AnMnz2JiM6NKtqJTntLS8FjqMqcefaHdq

-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: citext; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA public;


--
-- Name: EXTENSION citext; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION citext IS 'data type for case-insensitive character strings';


--
-- Name: set_updated_at(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.set_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
              NEW.updated_at = CURRENT_TIMESTAMP;
              RETURN NEW;
            END;
            $$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: acceso_bitacora; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.acceso_bitacora (
    id bigint NOT NULL,
    usuario_id bigint,
    sesion_id bigint,
    email_intentado character varying(255) DEFAULT ''::character varying NOT NULL,
    evento character varying(50) NOT NULL,
    exito boolean DEFAULT false NOT NULL,
    detalle text DEFAULT ''::text NOT NULL,
    ip inet,
    user_agent text DEFAULT ''::text NOT NULL,
    meta_json jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone,
    CONSTRAINT ck_acceso_bitacora__evento_no_vacio CHECK ((length(TRIM(BOTH FROM evento)) > 0))
);


--
-- Name: acceso_bitacora_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.acceso_bitacora_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: acceso_bitacora_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.acceso_bitacora_id_seq OWNED BY public.acceso_bitacora.id;


--
-- Name: actividad_bitacora; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.actividad_bitacora (
    id bigint NOT NULL,
    usuario_id bigint,
    sesion_id bigint,
    accion character varying(80) NOT NULL,
    modulo character varying(80) DEFAULT 'seguridad'::character varying NOT NULL,
    target_tabla character varying(63) DEFAULT ''::character varying NOT NULL,
    target_id bigint,
    exito boolean DEFAULT true NOT NULL,
    detalle text DEFAULT ''::text NOT NULL,
    ip inet,
    user_agent text DEFAULT ''::text NOT NULL,
    meta_json jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone,
    CONSTRAINT ck_actividad_bitacora__accion_no_vacia CHECK ((length(TRIM(BOTH FROM accion)) > 0))
);


--
-- Name: actividad_bitacora_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.actividad_bitacora_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: actividad_bitacora_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.actividad_bitacora_id_seq OWNED BY public.actividad_bitacora.id;


--
-- Name: archivo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.archivo (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    carpeta_id bigint,
    nombre_original character varying(300) NOT NULL,
    nombre_almacenado character varying(300) NOT NULL,
    ruta character varying(500) NOT NULL,
    mime_type character varying(100) DEFAULT ''::character varying NOT NULL,
    extension character varying(20) DEFAULT ''::character varying NOT NULL,
    tamanio bigint DEFAULT '0'::bigint NOT NULL,
    visto_admin boolean DEFAULT false NOT NULL,
    visto_admin_at timestamp(0) with time zone,
    visto_por bigint,
    notas_admin text DEFAULT ''::text NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: archivo_carpeta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.archivo_carpeta (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    nombre character varying(200) NOT NULL,
    padre_id bigint,
    ruta character varying(500) DEFAULT ''::character varying NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: archivo_carpeta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.archivo_carpeta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: archivo_carpeta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.archivo_carpeta_id_seq OWNED BY public.archivo_carpeta.id;


--
-- Name: archivo_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.archivo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: archivo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.archivo_id_seq OWNED BY public.archivo.id;


--
-- Name: area; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.area (
    id_area integer NOT NULL,
    name_area character varying(120) DEFAULT ''::character varying NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: area_id_area_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.area_id_area_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: area_id_area_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.area_id_area_seq OWNED BY public.area.id_area;


--
-- Name: areas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.areas (
    id_area integer NOT NULL,
    nombre character varying(100) NOT NULL
);


--
-- Name: areas_id_area_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.areas_id_area_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: areas_id_area_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.areas_id_area_seq OWNED BY public.areas.id_area;


--
-- Name: asignaciones_tecnicas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.asignaciones_tecnicas (
    id_asignacion integer NOT NULL,
    id_ticket integer NOT NULL,
    id_usuario_tecnico bigint NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: asignaciones_tecnicas_id_asignacion_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.asignaciones_tecnicas_id_asignacion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: asignaciones_tecnicas_id_asignacion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.asignaciones_tecnicas_id_asignacion_seq OWNED BY public.asignaciones_tecnicas.id_asignacion;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: carrito_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.carrito_items (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    producto_id bigint NOT NULL,
    cantidad integer DEFAULT 1 NOT NULL,
    guardado_para_despues boolean DEFAULT false NOT NULL,
    en_wishlist boolean DEFAULT false NOT NULL,
    ultima_actividad_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    motivo_movimiento character varying(50),
    es_regalo boolean DEFAULT false NOT NULL,
    mensaje_dedicatorio text,
    estado_regalo character varying(20),
    fecha_expiracion_regalo timestamp(0) with time zone,
    reservado_hasta timestamp(0) with time zone,
    regalo_hash character varying(64),
    destinatario_id bigint
);


--
-- Name: carrito_items_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.carrito_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: carrito_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.carrito_items_id_seq OWNED BY public.carrito_items.id;


--
-- Name: cart_bitacora; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cart_bitacora (
    id bigint NOT NULL,
    accion character varying(80) NOT NULL,
    modulo_id bigint,
    user_id bigint,
    jti character(36),
    carrito_uuid character(36),
    ip_address character varying(45),
    payload json,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: cart_bitacora_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cart_bitacora_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cart_bitacora_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cart_bitacora_id_seq OWNED BY public.cart_bitacora.id;


--
-- Name: cart_carritos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cart_carritos (
    id bigint NOT NULL,
    uuid character(36) NOT NULL,
    modulo_id bigint NOT NULL,
    usuario_ref character varying(120) NOT NULL,
    estado character varying(50) DEFAULT 'abierto'::character varying NOT NULL,
    requiere_saldo boolean DEFAULT false NOT NULL,
    total numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    metadata json,
    expira_at timestamp(0) without time zone,
    confirmed_at timestamp(0) without time zone,
    cancelled_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT cart_carritos_estado_check CHECK (((estado)::text = ANY ((ARRAY['abierto'::character varying, 'procesando_checkout'::character varying, 'confirmado'::character varying, 'cancelado'::character varying, 'expirado'::character varying, 'confirmado_pendiente_conciliacion'::character varying, 'revertido'::character varying])::text[])))
);


--
-- Name: cart_carritos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cart_carritos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cart_carritos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cart_carritos_id_seq OWNED BY public.cart_carritos.id;


--
-- Name: cart_categorias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cart_categorias (
    id bigint NOT NULL,
    slug character varying(60) NOT NULL,
    nombre character varying(120) NOT NULL,
    descripcion text,
    activa boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cart_categorias_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cart_categorias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cart_categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cart_categorias_id_seq OWNED BY public.cart_categorias.id;


--
-- Name: cart_conciliaciones_pendientes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cart_conciliaciones_pendientes (
    id bigint NOT NULL,
    carrito_uuid character(36) NOT NULL,
    modulo_id bigint NOT NULL,
    usuario_ref character varying(120) NOT NULL,
    monto numeric(10,2) NOT NULL,
    intentos integer DEFAULT 0 NOT NULL,
    ultimo_intento_at timestamp(0) without time zone,
    proximo_intento_at timestamp(0) without time zone,
    estado_conciliacion character varying(30) DEFAULT 'pendiente'::character varying NOT NULL,
    respuesta_saldo json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cart_conciliaciones_pendientes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cart_conciliaciones_pendientes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cart_conciliaciones_pendientes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cart_conciliaciones_pendientes_id_seq OWNED BY public.cart_conciliaciones_pendientes.id;


--
-- Name: cart_items_carrito; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cart_items_carrito (
    id bigint NOT NULL,
    carrito_id bigint NOT NULL,
    categoria_id bigint NOT NULL,
    referencia_externa character varying(180) NOT NULL,
    nombre character varying(255) NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    cantidad integer NOT NULL,
    duracion_horas integer,
    estado_item character varying(20) DEFAULT 'activo'::character varying NOT NULL,
    metadata json,
    added_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    removed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cart_items_carrito_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cart_items_carrito_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cart_items_carrito_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cart_items_carrito_id_seq OWNED BY public.cart_items_carrito.id;


--
-- Name: cart_modulos_clientes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cart_modulos_clientes (
    id bigint NOT NULL,
    solicitud_id bigint NOT NULL,
    nombre character varying(120) NOT NULL,
    slug character varying(60) NOT NULL,
    tipo_modulo character varying(60) NOT NULL,
    categorias_autorizadas json NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cart_modulos_clientes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cart_modulos_clientes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cart_modulos_clientes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cart_modulos_clientes_id_seq OWNED BY public.cart_modulos_clientes.id;


--
-- Name: cart_reglas_categoria; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cart_reglas_categoria (
    id bigint NOT NULL,
    categoria_id bigint NOT NULL,
    clave character varying(80) NOT NULL,
    valor character varying(255) NOT NULL,
    tipo_dato character varying(20) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cart_reglas_categoria_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cart_reglas_categoria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cart_reglas_categoria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cart_reglas_categoria_id_seq OWNED BY public.cart_reglas_categoria.id;


--
-- Name: cart_solicitudes_modulo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cart_solicitudes_modulo (
    id bigint NOT NULL,
    folio character(12) NOT NULL,
    nombre_modulo character varying(120) NOT NULL,
    tipo_modulo character varying(60) NOT NULL,
    categorias_solicitadas json NOT NULL,
    contacto_nombre character varying(120) NOT NULL,
    contacto_email character varying(180) NOT NULL,
    descripcion text,
    estado character varying(20) DEFAULT 'pendiente'::character varying NOT NULL,
    motivo_rechazo text,
    revisado_por bigint,
    revisado_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cart_solicitudes_modulo_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cart_solicitudes_modulo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cart_solicitudes_modulo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cart_solicitudes_modulo_id_seq OWNED BY public.cart_solicitudes_modulo.id;


--
-- Name: cart_tokens_modulo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cart_tokens_modulo (
    id bigint NOT NULL,
    modulo_id bigint NOT NULL,
    jti character(36) NOT NULL,
    tipo character varying(10) NOT NULL,
    estado character varying(20) DEFAULT 'activo'::character varying NOT NULL,
    emitido_at timestamp(0) without time zone NOT NULL,
    expira_at timestamp(0) without time zone NOT NULL,
    entregado_at timestamp(0) without time zone,
    revocado_at timestamp(0) without time zone,
    revocado_por bigint,
    motivo_revocacion character varying(255),
    pair_jti character(36),
    replaces_jti character(36),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cart_tokens_modulo_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cart_tokens_modulo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cart_tokens_modulo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cart_tokens_modulo_id_seq OWNED BY public.cart_tokens_modulo.id;


--
-- Name: catalogo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.catalogo (
    id_catalogo integer NOT NULL,
    nombre character varying(150) NOT NULL,
    descripcion text,
    tipo character varying(20) NOT NULL,
    id_categoria integer,
    aplica_iva boolean DEFAULT true NOT NULL,
    id_impuesto integer,
    activo boolean DEFAULT true NOT NULL,
    fecha_creacion timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: catalogo_area; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.catalogo_area (
    id_catalogo integer NOT NULL,
    id_area integer NOT NULL
);


--
-- Name: catalogo_id_catalogo_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.catalogo_id_catalogo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: catalogo_id_catalogo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.catalogo_id_catalogo_seq OWNED BY public.catalogo.id_catalogo;


--
-- Name: catalogo_vendedor; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.catalogo_vendedor (
    id_cv integer NOT NULL,
    id_vendedor integer NOT NULL,
    id_catalogo_base integer,
    nombre_personalizado character varying(150) NOT NULL,
    descripcion_personalizada text,
    tipo character varying(20) NOT NULL,
    id_categoria integer,
    activo boolean DEFAULT true NOT NULL,
    fecha_creacion timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: catalogo_vendedor_id_cv_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.catalogo_vendedor_id_cv_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: catalogo_vendedor_id_cv_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.catalogo_vendedor_id_cv_seq OWNED BY public.catalogo_vendedor.id_cv;


--
-- Name: categorias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categorias (
    id_categoria integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true NOT NULL
);


--
-- Name: categorias_id_categoria_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.categorias_id_categoria_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: categorias_id_categoria_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.categorias_id_categoria_seq OWNED BY public.categorias.id_categoria;


--
-- Name: categorias_ticket; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categorias_ticket (
    id_categoria integer NOT NULL,
    id_area integer NOT NULL,
    nombre_categoria character varying(120) DEFAULT ''::character varying NOT NULL,
    tiempo_sla_horas integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: categorias_ticket_id_categoria_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.categorias_ticket_id_categoria_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: categorias_ticket_id_categoria_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.categorias_ticket_id_categoria_seq OWNED BY public.categorias_ticket.id_categoria;


--
-- Name: comprobantes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.comprobantes (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    referencia_type character varying(255) NOT NULL,
    referencia_id bigint NOT NULL,
    total numeric(12,2) NOT NULL,
    fecha timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone
);


--
-- Name: comprobantes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.comprobantes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: comprobantes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.comprobantes_id_seq OWNED BY public.comprobantes.id;


--
-- Name: disponibilidad; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.disponibilidad (
    id_disponibilidad integer NOT NULL,
    id_catalogo integer NOT NULL,
    dia_semana character varying(15) NOT NULL,
    hora_inicio time(0) without time zone NOT NULL,
    hora_fin time(0) without time zone NOT NULL,
    disponible boolean DEFAULT true NOT NULL
);


--
-- Name: disponibilidad_id_disponibilidad_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.disponibilidad_id_disponibilidad_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: disponibilidad_id_disponibilidad_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.disponibilidad_id_disponibilidad_seq OWNED BY public.disponibilidad.id_disponibilidad;


--
-- Name: disponibilidad_vendedor; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.disponibilidad_vendedor (
    id_disp_v integer NOT NULL,
    id_cv integer NOT NULL,
    dia_semana character varying(15) NOT NULL,
    hora_inicio time(0) without time zone NOT NULL,
    hora_fin time(0) without time zone NOT NULL,
    disponible boolean DEFAULT true NOT NULL
);


--
-- Name: disponibilidad_vendedor_id_disp_v_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.disponibilidad_vendedor_id_disp_v_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: disponibilidad_vendedor_id_disp_v_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.disponibilidad_vendedor_id_disp_v_seq OWNED BY public.disponibilidad_vendedor.id_disp_v;


--
-- Name: equipos_activos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.equipos_activos (
    id_equipo integer NOT NULL,
    id_categoria integer NOT NULL,
    id_ubicacion integer NOT NULL,
    nombre_equipo character varying(120) DEFAULT ''::character varying NOT NULL,
    estado_actual character varying(50) DEFAULT 'activo'::character varying NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: equipos_activos_id_equipo_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.equipos_activos_id_equipo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: equipos_activos_id_equipo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.equipos_activos_id_equipo_seq OWNED BY public.equipos_activos.id_equipo;


--
-- Name: gastos_ticket; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.gastos_ticket (
    id_gasto integer NOT NULL,
    id_ticket integer NOT NULL,
    id_insumo integer NOT NULL,
    cantidad integer NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: gastos_ticket_id_gasto_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.gastos_ticket_id_gasto_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gastos_ticket_id_gasto_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.gastos_ticket_id_gasto_seq OWNED BY public.gastos_ticket.id_gasto;


--
-- Name: historial_tickets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.historial_tickets (
    id_historial integer NOT NULL,
    id_ticket integer NOT NULL,
    id_usuario bigint NOT NULL,
    estado_nuevo character varying(255) NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: historial_tickets_id_historial_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.historial_tickets_id_historial_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: historial_tickets_id_historial_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.historial_tickets_id_historial_seq OWNED BY public.historial_tickets.id_historial;


--
-- Name: impuestos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.impuestos (
    id_impuesto integer NOT NULL,
    nombre character varying(50),
    porcentaje numeric(5,2),
    activo boolean DEFAULT true NOT NULL
);


--
-- Name: impuestos_id_impuesto_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.impuestos_id_impuesto_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: impuestos_id_impuesto_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.impuestos_id_impuesto_seq OWNED BY public.impuestos.id_impuesto;


--
-- Name: insumos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.insumos (
    id_insumo integer NOT NULL,
    nombre_insumo character varying(255) NOT NULL,
    stock_actual integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: insumos_id_insumo_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.insumos_id_insumo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: insumos_id_insumo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.insumos_id_insumo_seq OWNED BY public.insumos.id_insumo;


--
-- Name: inventario; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inventario (
    id_inventario integer NOT NULL,
    id_catalogo integer NOT NULL,
    stock_actual integer DEFAULT 0 NOT NULL,
    stock_minimo integer DEFAULT 0 NOT NULL,
    fecha_actualizacion timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: inventario_id_inventario_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inventario_id_inventario_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inventario_id_inventario_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inventario_id_inventario_seq OWNED BY public.inventario.id_inventario;


--
-- Name: mantenimientos_preventivos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.mantenimientos_preventivos (
    id_preventivo integer NOT NULL,
    id_equipo integer NOT NULL,
    proxima_fecha_programada date NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: mantenimientos_preventivos_id_preventivo_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.mantenimientos_preventivos_id_preventivo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: mantenimientos_preventivos_id_preventivo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.mantenimientos_preventivos_id_preventivo_seq OWNED BY public.mantenimientos_preventivos.id_preventivo;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: movimientos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.movimientos (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    tipo character varying(255) NOT NULL,
    monto numeric(12,2) NOT NULL,
    referencia_type character varying(255) NOT NULL,
    referencia_id bigint NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone,
    estado character varying(255) NOT NULL,
    CONSTRAINT movimientos_tipo_check CHECK (((tipo)::text = ANY ((ARRAY['recarga'::character varying, 'pago'::character varying])::text[])))
);


--
-- Name: movimientos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.movimientos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: movimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.movimientos_id_seq OWNED BY public.movimientos.id;


--
-- Name: pagos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pagos (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    monto numeric(12,2) NOT NULL,
    concepto character varying(255) NOT NULL,
    estado character varying(255) DEFAULT 'completado'::character varying NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT pagos_estado_check CHECK (((estado)::text = ANY ((ARRAY['completado'::character varying, 'fallido'::character varying])::text[])))
);


--
-- Name: pagos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pagos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pagos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pagos_id_seq OWNED BY public.pagos.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: pedido; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pedido (
    id bigint NOT NULL,
    usuario_id bigint,
    numero_folio character varying(30) NOT NULL,
    estado character varying(30) DEFAULT 'creado'::character varying NOT NULL,
    modulo character varying(50) DEFAULT 'otro'::character varying NOT NULL,
    total numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    descripcion text DEFAULT ''::text NOT NULL,
    notas text DEFAULT ''::text NOT NULL,
    operador_usuario_id bigint,
    confirmado_con_tarjeta boolean DEFAULT false NOT NULL,
    confirmado_at timestamp(0) without time zone,
    tarjeta_lectura_id bigint,
    cobrado_de_saldo boolean DEFAULT false NOT NULL,
    saldo_movimiento_id bigint,
    meta_json jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    deleted_at timestamp(0) with time zone,
    tienda_id bigint,
    tipo_entrega character varying(20) DEFAULT 'directo'::character varying NOT NULL,
    repartidor_id bigint,
    carrito_uuid character varying(100),
    CONSTRAINT ck_pedido__estado CHECK (((estado)::text = ANY ((ARRAY['creado'::character varying, 'aceptado'::character varying, 'en_proceso'::character varying, 'listo'::character varying, 'entregado'::character varying, 'cancelado'::character varying])::text[]))),
    CONSTRAINT ck_pedido__tipo_entrega CHECK (((tipo_entrega)::text = ANY ((ARRAY['directo'::character varying, 'repartidor'::character varying])::text[])))
);


--
-- Name: pedido_detalles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pedido_detalles (
    id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    producto_id bigint,
    nombre_producto character varying(200) NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    cantidad integer NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: pedido_detalles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pedido_detalles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pedido_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pedido_detalles_id_seq OWNED BY public.pedido_detalles.id;


--
-- Name: pedido_historial; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pedido_historial (
    id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    estado_anterior character varying(30),
    estado_nuevo character varying(30) NOT NULL,
    usuario_id bigint,
    notas text DEFAULT ''::text NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone
);


--
-- Name: pedido_historial_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pedido_historial_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pedido_historial_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pedido_historial_id_seq OWNED BY public.pedido_historial.id;


--
-- Name: pedido_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pedido_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pedido_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pedido_id_seq OWNED BY public.pedido.id;


--
-- Name: pedido_item; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pedido_item (
    id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    producto_id bigint NOT NULL,
    nombre_producto character varying(200) NOT NULL,
    cantidad integer DEFAULT 1 NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    aplica_iva boolean DEFAULT false NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    iva_monto numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    total_linea numeric(10,2) NOT NULL,
    meta_json json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: pedido_item_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pedido_item_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pedido_item_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pedido_item_id_seq OWNED BY public.pedido_item.id;


--
-- Name: pedidos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pedidos (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    total numeric(10,2) NOT NULL,
    estado character varying(50) DEFAULT 'pendiente'::character varying NOT NULL,
    metodo_pago character varying(50),
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    destinatario_id bigint,
    gracia_hasta timestamp(0) with time zone,
    notificado_destinatario boolean DEFAULT false NOT NULL,
    pago_token character varying(64),
    pago_expira_en timestamp(0) with time zone
);


--
-- Name: pedidos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pedidos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pedidos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pedidos_id_seq OWNED BY public.pedidos.id;


--
-- Name: permiso; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permiso (
    id bigint NOT NULL,
    clave character varying(100) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: permiso_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.permiso_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: permiso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.permiso_id_seq OWNED BY public.permiso.id;


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: precios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.precios (
    id_precio integer NOT NULL,
    id_catalogo integer NOT NULL,
    precio numeric(10,2) NOT NULL,
    fecha_inicio date NOT NULL,
    fecha_fin date
);


--
-- Name: precios_id_precio_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.precios_id_precio_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: precios_id_precio_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.precios_id_precio_seq OWNED BY public.precios.id_precio;


--
-- Name: precios_vendedor; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.precios_vendedor (
    id_precio_v integer NOT NULL,
    id_cv integer NOT NULL,
    precio numeric(10,2) NOT NULL,
    fecha_inicio date NOT NULL,
    fecha_fin date
);


--
-- Name: precios_vendedor_id_precio_v_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.precios_vendedor_id_precio_v_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: precios_vendedor_id_precio_v_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.precios_vendedor_id_precio_v_seq OWNED BY public.precios_vendedor.id_precio_v;


--
-- Name: producto; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.producto (
    id bigint NOT NULL,
    nombre character varying(150) NOT NULL,
    descripcion text,
    precio numeric(10,2) NOT NULL,
    stock integer DEFAULT 0 NOT NULL,
    modulo character varying(50) NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    imagen_url text,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) without time zone,
    tienda_id bigint
);


--
-- Name: producto_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.producto_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: producto_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.producto_id_seq OWNED BY public.producto.id;


--
-- Name: productos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.productos (
    id bigint NOT NULL,
    nombre character varying(200) NOT NULL,
    precio numeric(10,2) NOT NULL,
    stock integer DEFAULT 0 NOT NULL,
    categoria character varying(100),
    imagen_url text,
    es_regalo boolean DEFAULT false NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    tienda character varying(100) DEFAULT 'General'::character varying,
    tipo character varying(50) DEFAULT 'producto'::character varying,
    fecha_inicio_evento timestamp(0) with time zone,
    limite_por_usuario integer DEFAULT 3 NOT NULL
);


--
-- Name: productos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.productos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: productos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.productos_id_seq OWNED BY public.productos.id;


--
-- Name: promociones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.promociones (
    id_promocion integer NOT NULL,
    nombre character varying(150),
    descripcion text,
    tipo character varying(50),
    valor numeric(10,2),
    fecha_inicio date,
    fecha_fin date,
    activa boolean DEFAULT true NOT NULL
);


--
-- Name: promociones_catalogo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.promociones_catalogo (
    id_promocion integer NOT NULL,
    id_catalogo integer NOT NULL
);


--
-- Name: promociones_id_promocion_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.promociones_id_promocion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: promociones_id_promocion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.promociones_id_promocion_seq OWNED BY public.promociones.id_promocion;


--
-- Name: promociones_vendedor; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.promociones_vendedor (
    id_promocion integer NOT NULL,
    id_cv integer NOT NULL
);


--
-- Name: recarga; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.recarga (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(50) DEFAULT 'tarjeta'::character varying NOT NULL,
    estado character varying(20) DEFAULT 'pendiente'::character varying NOT NULL,
    referencia_pago character varying(100),
    razon_fallo text,
    saldo_movimiento_id bigint,
    meta_json jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    deleted_at timestamp(0) with time zone,
    CONSTRAINT ck_recarga__estado CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'exitoso'::character varying, 'fallido'::character varying])::text[]))),
    CONSTRAINT ck_recarga__monto_positivo CHECK ((monto > (0)::numeric))
);


--
-- Name: recarga_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.recarga_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: recarga_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.recarga_id_seq OWNED BY public.recarga.id;


--
-- Name: recargas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.recargas (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    monto numeric(12,2) NOT NULL,
    metodo_pago character varying(255) NOT NULL,
    estado character varying(255) DEFAULT 'pendiente'::character varying NOT NULL,
    referencia character varying(255),
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) with time zone,
    razon_fallo text,
    saldo_movimiento_id bigint,
    CONSTRAINT recargas_estado_check CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'exitosa'::character varying, 'fallida'::character varying])::text[]))),
    CONSTRAINT recargas_metodo_pago_check CHECK (((metodo_pago)::text = ANY ((ARRAY['tarjeta'::character varying, 'efectivo'::character varying])::text[])))
);


--
-- Name: recargas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.recargas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: recargas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.recargas_id_seq OWNED BY public.recargas.id;


--
-- Name: reglas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.reglas (
    id_regla integer NOT NULL,
    id_catalogo integer NOT NULL,
    descripcion text NOT NULL,
    tipo_regla character varying(50)
);


--
-- Name: reglas_id_regla_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.reglas_id_regla_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: reglas_id_regla_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.reglas_id_regla_seq OWNED BY public.reglas.id_regla;


--
-- Name: reglas_vendedor; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.reglas_vendedor (
    id_regla_v integer NOT NULL,
    id_cv integer NOT NULL,
    descripcion text NOT NULL,
    tipo_regla character varying(50)
);


--
-- Name: reglas_vendedor_id_regla_v_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.reglas_vendedor_id_regla_v_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: reglas_vendedor_id_regla_v_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.reglas_vendedor_id_regla_v_seq OWNED BY public.reglas_vendedor.id_regla_v;


--
-- Name: rol; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rol (
    id bigint NOT NULL,
    nombre character varying(50) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: rol_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.rol_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: rol_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.rol_id_seq OWNED BY public.rol.id;


--
-- Name: rol_permiso; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rol_permiso (
    id bigint NOT NULL,
    rol_id bigint NOT NULL,
    permiso_id bigint NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: rol_permiso_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.rol_permiso_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: rol_permiso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.rol_permiso_id_seq OWNED BY public.rol_permiso.id;


--
-- Name: saldo_monedero; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.saldo_monedero (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    saldo_disponible numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    saldo_retenido numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    deleted_at timestamp(0) with time zone,
    CONSTRAINT ck_saldo_monedero__saldo_retenido_no_negativo CHECK ((saldo_retenido >= (0)::numeric))
);


--
-- Name: saldo_monedero_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.saldo_monedero_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: saldo_monedero_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.saldo_monedero_id_seq OWNED BY public.saldo_monedero.id;


--
-- Name: saldo_movimiento; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.saldo_movimiento (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    saldo_monedero_id bigint NOT NULL,
    tipo character varying(20) DEFAULT 'cargo'::character varying NOT NULL,
    monto numeric(10,2) NOT NULL,
    saldo_anterior numeric(10,2) NOT NULL,
    saldo_nuevo numeric(10,2) NOT NULL,
    modulo character varying(50) DEFAULT 'otro'::character varying NOT NULL,
    concepto character varying(255) DEFAULT ''::character varying NOT NULL,
    referencia_tabla character varying(63),
    referencia_id bigint,
    operador_usuario_id bigint,
    tarjeta_lectura_id bigint,
    meta_json jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    deleted_at timestamp(0) with time zone,
    CONSTRAINT ck_saldo_movimiento__monto_positivo CHECK ((monto > (0)::numeric)),
    CONSTRAINT ck_saldo_movimiento__tipo CHECK (((tipo)::text = ANY ((ARRAY['abono'::character varying, 'cargo'::character varying])::text[])))
);


--
-- Name: saldo_movimiento_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.saldo_movimiento_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: saldo_movimiento_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.saldo_movimiento_id_seq OWNED BY public.saldo_movimiento.id;


--
-- Name: saldo_reglas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.saldo_reglas (
    id bigint NOT NULL,
    usuario_id bigint,
    tipo_limite character varying(255) DEFAULT 'diario'::character varying NOT NULL,
    monto_limite numeric(10,2) NOT NULL,
    modulo character varying(255),
    activo boolean DEFAULT true NOT NULL,
    descripcion text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT saldo_reglas_tipo_limite_check CHECK (((tipo_limite)::text = ANY ((ARRAY['diario'::character varying, 'semanal'::character varying, 'mensual'::character varying])::text[])))
);


--
-- Name: COLUMN saldo_reglas.monto_limite; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.saldo_reglas.monto_limite IS 'Monto límite en la unidad de moneda';


--
-- Name: COLUMN saldo_reglas.modulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.saldo_reglas.modulo IS 'Módulo específico (cafeteria, copias, etc) o NULL para todos';


--
-- Name: saldo_reglas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.saldo_reglas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: saldo_reglas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.saldo_reglas_id_seq OWNED BY public.saldo_reglas.id;


--
-- Name: saldo_reserva; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.saldo_reserva (
    id bigint NOT NULL,
    uuid uuid NOT NULL,
    usuario_id bigint NOT NULL,
    saldo_monedero_id bigint NOT NULL,
    monto numeric(10,2) NOT NULL,
    carrito_uuid character varying(36),
    modulo_slug character varying(50),
    concepto character varying(255) DEFAULT ''::character varying NOT NULL,
    estado character varying(20) DEFAULT 'pendiente'::character varying NOT NULL,
    expira_at timestamp(0) with time zone NOT NULL,
    saldo_movimiento_id bigint,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    CONSTRAINT ck_saldo_reserva__estado CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'confirmada'::character varying, 'liberada'::character varying, 'expirada'::character varying])::text[]))),
    CONSTRAINT ck_saldo_reserva__monto_positivo CHECK ((monto > (0)::numeric))
);


--
-- Name: saldo_reserva_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.saldo_reserva_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: saldo_reserva_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.saldo_reserva_id_seq OWNED BY public.saldo_reserva.id;


--
-- Name: saldos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.saldos (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    saldo numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: saldos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.saldos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: saldos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.saldos_id_seq OWNED BY public.saldos.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: tarjeta_lectura; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tarjeta_lectura (
    id bigint NOT NULL,
    tarjeta_id bigint,
    uid_leido character varying(64) NOT NULL,
    modulo character varying(50) DEFAULT 'otro'::character varying NOT NULL,
    tipo_lectura character varying(50) DEFAULT 'acceso'::character varying NOT NULL,
    exito boolean DEFAULT true NOT NULL,
    detalle text DEFAULT ''::text NOT NULL,
    ip inet,
    user_agent text DEFAULT ''::text NOT NULL,
    operador_usuario_id bigint,
    meta_json jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    pedido_id bigint
);


--
-- Name: COLUMN tarjeta_lectura.uid_leido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.tarjeta_lectura.uid_leido IS 'UID que se intentó leer';


--
-- Name: COLUMN tarjeta_lectura.modulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.tarjeta_lectura.modulo IS 'cafeteria, copias, souvenirs, biblioteca, acceso, otro';


--
-- Name: COLUMN tarjeta_lectura.tipo_lectura; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.tarjeta_lectura.tipo_lectura IS 'acceso, consumo, consulta_saldo, confirmacion_entrega';


--
-- Name: tarjeta_lectura_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tarjeta_lectura_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tarjeta_lectura_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tarjeta_lectura_id_seq OWNED BY public.tarjeta_lectura.id;


--
-- Name: tarjeta_universitaria; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tarjeta_universitaria (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    uid character varying(64) NOT NULL,
    estado character varying(255) DEFAULT 'activa'::character varying NOT NULL,
    motivo_bloqueo text,
    registrado_por_usuario_id bigint,
    bloqueado_por_usuario_id bigint,
    bloqueado_at timestamp(0) without time zone,
    meta_json jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    pin_hash text,
    CONSTRAINT tarjeta_universitaria_estado_check CHECK (((estado)::text = ANY ((ARRAY['activa'::character varying, 'bloqueada'::character varying, 'perdida'::character varying, 'cancelada'::character varying])::text[])))
);


--
-- Name: COLUMN tarjeta_universitaria.uid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.tarjeta_universitaria.uid IS 'UID único del chip RFID/NFC';


--
-- Name: tarjeta_universitaria_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tarjeta_universitaria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tarjeta_universitaria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tarjeta_universitaria_id_seq OWNED BY public.tarjeta_universitaria.id;


--
-- Name: tickets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tickets (
    id_ticket integer NOT NULL,
    id_usuario_solicitante bigint NOT NULL,
    id_categoria integer NOT NULL,
    id_equipo integer,
    estado character varying(50) DEFAULT 'abierto'::character varying NOT NULL,
    prioridad character varying(30) DEFAULT 'media'::character varying NOT NULL,
    fecha_creacion timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: tickets_id_ticket_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tickets_id_ticket_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tickets_id_ticket_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tickets_id_ticket_seq OWNED BY public.tickets.id_ticket;


--
-- Name: tienda; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tienda (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    tipo character varying(30) DEFAULT 'otro'::character varying NOT NULL,
    descripcion text DEFAULT ''::text NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    logo_url character varying(255),
    color character varying(20) DEFAULT '#3b82f6'::character varying NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    deleted_at timestamp(0) with time zone,
    ubicacion character varying(255),
    vendedor_catalogo_id bigint,
    CONSTRAINT ck_tienda__tipo CHECK (((tipo)::text = ANY ((ARRAY['cafeteria'::character varying, 'papeleria'::character varying, 'kermesse'::character varying, 'mercadito'::character varying, 'estudiante'::character varying, 'otro'::character varying])::text[])))
);


--
-- Name: COLUMN tienda.vendedor_catalogo_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.tienda.vendedor_catalogo_id IS 'id_vendedor en el catálogo del módulo 4.3';


--
-- Name: tienda_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tienda_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tienda_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tienda_id_seq OWNED BY public.tienda.id;


--
-- Name: ubicaciones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.ubicaciones (
    id_ubicacion integer NOT NULL,
    edificio character varying(120) DEFAULT ''::character varying NOT NULL,
    aula_departamento character varying(120) DEFAULT ''::character varying NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: ubicaciones_id_ubicacion_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.ubicaciones_id_ubicacion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: ubicaciones_id_ubicacion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.ubicaciones_id_ubicacion_seq OWNED BY public.ubicaciones.id_ubicacion;


--
-- Name: usuario; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuario (
    id bigint NOT NULL,
    nombre character varying(120) DEFAULT ''::character varying NOT NULL,
    apellido character varying(120) DEFAULT ''::character varying NOT NULL,
    telefono character varying(30) DEFAULT ''::character varying NOT NULL,
    foto_url text DEFAULT ''::text NOT NULL,
    password_hash text NOT NULL,
    email_verificado boolean DEFAULT false NOT NULL,
    ultimo_login_at timestamp(0) with time zone,
    bloqueado boolean DEFAULT false NOT NULL,
    bloqueado_hasta timestamp(0) with time zone,
    seguridad_json jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone,
    email public.citext NOT NULL,
    remember_token character varying(100),
    modulo character varying(50),
    tienda_id bigint,
    sancion_efectivo_hasta timestamp(0) with time zone,
    tienda character varying(100),
    matricula character varying(50),
    CONSTRAINT ck_usuario__apellido_len CHECK ((length((apellido)::text) <= 120)),
    CONSTRAINT ck_usuario__email_no_vacio CHECK ((length(TRIM(BOTH FROM (email)::text)) > 3)),
    CONSTRAINT ck_usuario__nombre_len CHECK ((length((nombre)::text) <= 120))
);


--
-- Name: usuario_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuario_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuario_id_seq OWNED BY public.usuario.id;


--
-- Name: usuario_password_reset; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuario_password_reset (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    token_hash text NOT NULL,
    solicitado_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    expira_at timestamp(0) with time zone NOT NULL,
    usado_at timestamp(0) with time zone,
    ip inet,
    user_agent text DEFAULT ''::text NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone,
    CONSTRAINT ck_usuario_password_reset__expira_future CHECK ((expira_at > solicitado_at))
);


--
-- Name: usuario_password_reset_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuario_password_reset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuario_password_reset_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuario_password_reset_id_seq OWNED BY public.usuario_password_reset.id;


--
-- Name: usuario_perfil; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuario_perfil (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    fecha_nacimiento date,
    genero character varying(30) DEFAULT ''::character varying NOT NULL,
    direccion text DEFAULT ''::text NOT NULL,
    preferencias_json jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: usuario_perfil_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuario_perfil_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuario_perfil_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuario_perfil_id_seq OWNED BY public.usuario_perfil.id;


--
-- Name: usuario_rol; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuario_rol (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    rol_id bigint NOT NULL,
    asignado_por_usuario_id bigint,
    asignado_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: usuario_rol_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuario_rol_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuario_rol_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuario_rol_id_seq OWNED BY public.usuario_rol.id;


--
-- Name: usuario_sesion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuario_sesion (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    session_id character varying(255) NOT NULL,
    ip inet,
    user_agent text DEFAULT ''::text NOT NULL,
    inicia_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    expira_at timestamp(0) with time zone,
    termina_at timestamp(0) with time zone,
    activa boolean DEFAULT true NOT NULL,
    meta_json jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


--
-- Name: usuario_sesion_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuario_sesion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuario_sesion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuario_sesion_id_seq OWNED BY public.usuario_sesion.id;


--
-- Name: usuario_tienda; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuario_tienda (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    tienda_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: usuario_tienda_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuario_tienda_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuario_tienda_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuario_tienda_id_seq OWNED BY public.usuario_tienda.id;


--
-- Name: vendedores; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vendedores (
    id_vendedor integer NOT NULL,
    nombre character varying(150) NOT NULL,
    email character varying(255) NOT NULL,
    telefono character varying(20),
    descripcion text,
    activo boolean DEFAULT true NOT NULL,
    fecha_registro timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: vendedores_id_vendedor_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.vendedores_id_vendedor_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vendedores_id_vendedor_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.vendedores_id_vendedor_seq OWNED BY public.vendedores.id_vendedor;


--
-- Name: acceso_bitacora id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.acceso_bitacora ALTER COLUMN id SET DEFAULT nextval('public.acceso_bitacora_id_seq'::regclass);


--
-- Name: actividad_bitacora id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_bitacora ALTER COLUMN id SET DEFAULT nextval('public.actividad_bitacora_id_seq'::regclass);


--
-- Name: archivo id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archivo ALTER COLUMN id SET DEFAULT nextval('public.archivo_id_seq'::regclass);


--
-- Name: archivo_carpeta id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archivo_carpeta ALTER COLUMN id SET DEFAULT nextval('public.archivo_carpeta_id_seq'::regclass);


--
-- Name: area id_area; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.area ALTER COLUMN id_area SET DEFAULT nextval('public.area_id_area_seq'::regclass);


--
-- Name: areas id_area; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.areas ALTER COLUMN id_area SET DEFAULT nextval('public.areas_id_area_seq'::regclass);


--
-- Name: asignaciones_tecnicas id_asignacion; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaciones_tecnicas ALTER COLUMN id_asignacion SET DEFAULT nextval('public.asignaciones_tecnicas_id_asignacion_seq'::regclass);


--
-- Name: carrito_items id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.carrito_items ALTER COLUMN id SET DEFAULT nextval('public.carrito_items_id_seq'::regclass);


--
-- Name: cart_bitacora id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_bitacora ALTER COLUMN id SET DEFAULT nextval('public.cart_bitacora_id_seq'::regclass);


--
-- Name: cart_carritos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_carritos ALTER COLUMN id SET DEFAULT nextval('public.cart_carritos_id_seq'::regclass);


--
-- Name: cart_categorias id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_categorias ALTER COLUMN id SET DEFAULT nextval('public.cart_categorias_id_seq'::regclass);


--
-- Name: cart_conciliaciones_pendientes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_conciliaciones_pendientes ALTER COLUMN id SET DEFAULT nextval('public.cart_conciliaciones_pendientes_id_seq'::regclass);


--
-- Name: cart_items_carrito id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_items_carrito ALTER COLUMN id SET DEFAULT nextval('public.cart_items_carrito_id_seq'::regclass);


--
-- Name: cart_modulos_clientes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_modulos_clientes ALTER COLUMN id SET DEFAULT nextval('public.cart_modulos_clientes_id_seq'::regclass);


--
-- Name: cart_reglas_categoria id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_reglas_categoria ALTER COLUMN id SET DEFAULT nextval('public.cart_reglas_categoria_id_seq'::regclass);


--
-- Name: cart_solicitudes_modulo id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_solicitudes_modulo ALTER COLUMN id SET DEFAULT nextval('public.cart_solicitudes_modulo_id_seq'::regclass);


--
-- Name: cart_tokens_modulo id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_tokens_modulo ALTER COLUMN id SET DEFAULT nextval('public.cart_tokens_modulo_id_seq'::regclass);


--
-- Name: catalogo id_catalogo; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo ALTER COLUMN id_catalogo SET DEFAULT nextval('public.catalogo_id_catalogo_seq'::regclass);


--
-- Name: catalogo_vendedor id_cv; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo_vendedor ALTER COLUMN id_cv SET DEFAULT nextval('public.catalogo_vendedor_id_cv_seq'::regclass);


--
-- Name: categorias id_categoria; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias ALTER COLUMN id_categoria SET DEFAULT nextval('public.categorias_id_categoria_seq'::regclass);


--
-- Name: categorias_ticket id_categoria; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias_ticket ALTER COLUMN id_categoria SET DEFAULT nextval('public.categorias_ticket_id_categoria_seq'::regclass);


--
-- Name: comprobantes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.comprobantes ALTER COLUMN id SET DEFAULT nextval('public.comprobantes_id_seq'::regclass);


--
-- Name: disponibilidad id_disponibilidad; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.disponibilidad ALTER COLUMN id_disponibilidad SET DEFAULT nextval('public.disponibilidad_id_disponibilidad_seq'::regclass);


--
-- Name: disponibilidad_vendedor id_disp_v; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.disponibilidad_vendedor ALTER COLUMN id_disp_v SET DEFAULT nextval('public.disponibilidad_vendedor_id_disp_v_seq'::regclass);


--
-- Name: equipos_activos id_equipo; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.equipos_activos ALTER COLUMN id_equipo SET DEFAULT nextval('public.equipos_activos_id_equipo_seq'::regclass);


--
-- Name: gastos_ticket id_gasto; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gastos_ticket ALTER COLUMN id_gasto SET DEFAULT nextval('public.gastos_ticket_id_gasto_seq'::regclass);


--
-- Name: historial_tickets id_historial; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.historial_tickets ALTER COLUMN id_historial SET DEFAULT nextval('public.historial_tickets_id_historial_seq'::regclass);


--
-- Name: impuestos id_impuesto; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.impuestos ALTER COLUMN id_impuesto SET DEFAULT nextval('public.impuestos_id_impuesto_seq'::regclass);


--
-- Name: insumos id_insumo; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.insumos ALTER COLUMN id_insumo SET DEFAULT nextval('public.insumos_id_insumo_seq'::regclass);


--
-- Name: inventario id_inventario; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario ALTER COLUMN id_inventario SET DEFAULT nextval('public.inventario_id_inventario_seq'::regclass);


--
-- Name: mantenimientos_preventivos id_preventivo; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mantenimientos_preventivos ALTER COLUMN id_preventivo SET DEFAULT nextval('public.mantenimientos_preventivos_id_preventivo_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: movimientos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos ALTER COLUMN id SET DEFAULT nextval('public.movimientos_id_seq'::regclass);


--
-- Name: pagos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pagos ALTER COLUMN id SET DEFAULT nextval('public.pagos_id_seq'::regclass);


--
-- Name: pedido id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido ALTER COLUMN id SET DEFAULT nextval('public.pedido_id_seq'::regclass);


--
-- Name: pedido_detalles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_detalles ALTER COLUMN id SET DEFAULT nextval('public.pedido_detalles_id_seq'::regclass);


--
-- Name: pedido_historial id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_historial ALTER COLUMN id SET DEFAULT nextval('public.pedido_historial_id_seq'::regclass);


--
-- Name: pedido_item id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_item ALTER COLUMN id SET DEFAULT nextval('public.pedido_item_id_seq'::regclass);


--
-- Name: pedidos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedidos ALTER COLUMN id SET DEFAULT nextval('public.pedidos_id_seq'::regclass);


--
-- Name: permiso id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permiso ALTER COLUMN id SET DEFAULT nextval('public.permiso_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: precios id_precio; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.precios ALTER COLUMN id_precio SET DEFAULT nextval('public.precios_id_precio_seq'::regclass);


--
-- Name: precios_vendedor id_precio_v; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.precios_vendedor ALTER COLUMN id_precio_v SET DEFAULT nextval('public.precios_vendedor_id_precio_v_seq'::regclass);


--
-- Name: producto id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.producto ALTER COLUMN id SET DEFAULT nextval('public.producto_id_seq'::regclass);


--
-- Name: productos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.productos ALTER COLUMN id SET DEFAULT nextval('public.productos_id_seq'::regclass);


--
-- Name: promociones id_promocion; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promociones ALTER COLUMN id_promocion SET DEFAULT nextval('public.promociones_id_promocion_seq'::regclass);


--
-- Name: recarga id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recarga ALTER COLUMN id SET DEFAULT nextval('public.recarga_id_seq'::regclass);


--
-- Name: recargas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recargas ALTER COLUMN id SET DEFAULT nextval('public.recargas_id_seq'::regclass);


--
-- Name: reglas id_regla; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reglas ALTER COLUMN id_regla SET DEFAULT nextval('public.reglas_id_regla_seq'::regclass);


--
-- Name: reglas_vendedor id_regla_v; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reglas_vendedor ALTER COLUMN id_regla_v SET DEFAULT nextval('public.reglas_vendedor_id_regla_v_seq'::regclass);


--
-- Name: rol id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rol ALTER COLUMN id SET DEFAULT nextval('public.rol_id_seq'::regclass);


--
-- Name: rol_permiso id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rol_permiso ALTER COLUMN id SET DEFAULT nextval('public.rol_permiso_id_seq'::regclass);


--
-- Name: saldo_monedero id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_monedero ALTER COLUMN id SET DEFAULT nextval('public.saldo_monedero_id_seq'::regclass);


--
-- Name: saldo_movimiento id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_movimiento ALTER COLUMN id SET DEFAULT nextval('public.saldo_movimiento_id_seq'::regclass);


--
-- Name: saldo_reglas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_reglas ALTER COLUMN id SET DEFAULT nextval('public.saldo_reglas_id_seq'::regclass);


--
-- Name: saldo_reserva id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_reserva ALTER COLUMN id SET DEFAULT nextval('public.saldo_reserva_id_seq'::regclass);


--
-- Name: saldos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldos ALTER COLUMN id SET DEFAULT nextval('public.saldos_id_seq'::regclass);


--
-- Name: tarjeta_lectura id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_lectura ALTER COLUMN id SET DEFAULT nextval('public.tarjeta_lectura_id_seq'::regclass);


--
-- Name: tarjeta_universitaria id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_universitaria ALTER COLUMN id SET DEFAULT nextval('public.tarjeta_universitaria_id_seq'::regclass);


--
-- Name: tickets id_ticket; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tickets ALTER COLUMN id_ticket SET DEFAULT nextval('public.tickets_id_ticket_seq'::regclass);


--
-- Name: tienda id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tienda ALTER COLUMN id SET DEFAULT nextval('public.tienda_id_seq'::regclass);


--
-- Name: ubicaciones id_ubicacion; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ubicaciones ALTER COLUMN id_ubicacion SET DEFAULT nextval('public.ubicaciones_id_ubicacion_seq'::regclass);


--
-- Name: usuario id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario ALTER COLUMN id SET DEFAULT nextval('public.usuario_id_seq'::regclass);


--
-- Name: usuario_password_reset id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_password_reset ALTER COLUMN id SET DEFAULT nextval('public.usuario_password_reset_id_seq'::regclass);


--
-- Name: usuario_perfil id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_perfil ALTER COLUMN id SET DEFAULT nextval('public.usuario_perfil_id_seq'::regclass);


--
-- Name: usuario_rol id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_rol ALTER COLUMN id SET DEFAULT nextval('public.usuario_rol_id_seq'::regclass);


--
-- Name: usuario_sesion id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_sesion ALTER COLUMN id SET DEFAULT nextval('public.usuario_sesion_id_seq'::regclass);


--
-- Name: usuario_tienda id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_tienda ALTER COLUMN id SET DEFAULT nextval('public.usuario_tienda_id_seq'::regclass);


--
-- Name: vendedores id_vendedor; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendedores ALTER COLUMN id_vendedor SET DEFAULT nextval('public.vendedores_id_vendedor_seq'::regclass);


--
-- Name: acceso_bitacora acceso_bitacora_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.acceso_bitacora
    ADD CONSTRAINT acceso_bitacora_pkey PRIMARY KEY (id);


--
-- Name: actividad_bitacora actividad_bitacora_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_bitacora
    ADD CONSTRAINT actividad_bitacora_pkey PRIMARY KEY (id);


--
-- Name: archivo_carpeta archivo_carpeta_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archivo_carpeta
    ADD CONSTRAINT archivo_carpeta_pkey PRIMARY KEY (id);


--
-- Name: archivo archivo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archivo
    ADD CONSTRAINT archivo_pkey PRIMARY KEY (id);


--
-- Name: area area_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.area
    ADD CONSTRAINT area_pkey PRIMARY KEY (id_area);


--
-- Name: areas areas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.areas
    ADD CONSTRAINT areas_pkey PRIMARY KEY (id_area);


--
-- Name: asignaciones_tecnicas asignaciones_tecnicas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaciones_tecnicas
    ADD CONSTRAINT asignaciones_tecnicas_pkey PRIMARY KEY (id_asignacion);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: carrito_items carrito_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.carrito_items
    ADD CONSTRAINT carrito_items_pkey PRIMARY KEY (id);


--
-- Name: carrito_items carrito_items_regalo_hash_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.carrito_items
    ADD CONSTRAINT carrito_items_regalo_hash_unique UNIQUE (regalo_hash);


--
-- Name: cart_bitacora cart_bitacora_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_bitacora
    ADD CONSTRAINT cart_bitacora_pkey PRIMARY KEY (id);


--
-- Name: cart_carritos cart_carritos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_carritos
    ADD CONSTRAINT cart_carritos_pkey PRIMARY KEY (id);


--
-- Name: cart_carritos cart_carritos_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_carritos
    ADD CONSTRAINT cart_carritos_uuid_unique UNIQUE (uuid);


--
-- Name: cart_categorias cart_categorias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_categorias
    ADD CONSTRAINT cart_categorias_pkey PRIMARY KEY (id);


--
-- Name: cart_categorias cart_categorias_slug_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_categorias
    ADD CONSTRAINT cart_categorias_slug_unique UNIQUE (slug);


--
-- Name: cart_conciliaciones_pendientes cart_conciliaciones_pendientes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_conciliaciones_pendientes
    ADD CONSTRAINT cart_conciliaciones_pendientes_pkey PRIMARY KEY (id);


--
-- Name: cart_items_carrito cart_items_carrito_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_items_carrito
    ADD CONSTRAINT cart_items_carrito_pkey PRIMARY KEY (id);


--
-- Name: cart_modulos_clientes cart_modulos_clientes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_modulos_clientes
    ADD CONSTRAINT cart_modulos_clientes_pkey PRIMARY KEY (id);


--
-- Name: cart_modulos_clientes cart_modulos_clientes_slug_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_modulos_clientes
    ADD CONSTRAINT cart_modulos_clientes_slug_unique UNIQUE (slug);


--
-- Name: cart_reglas_categoria cart_reglas_categoria_categoria_id_clave_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_reglas_categoria
    ADD CONSTRAINT cart_reglas_categoria_categoria_id_clave_unique UNIQUE (categoria_id, clave);


--
-- Name: cart_reglas_categoria cart_reglas_categoria_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_reglas_categoria
    ADD CONSTRAINT cart_reglas_categoria_pkey PRIMARY KEY (id);


--
-- Name: cart_solicitudes_modulo cart_solicitudes_modulo_folio_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_solicitudes_modulo
    ADD CONSTRAINT cart_solicitudes_modulo_folio_unique UNIQUE (folio);


--
-- Name: cart_solicitudes_modulo cart_solicitudes_modulo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_solicitudes_modulo
    ADD CONSTRAINT cart_solicitudes_modulo_pkey PRIMARY KEY (id);


--
-- Name: cart_tokens_modulo cart_tokens_modulo_jti_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_tokens_modulo
    ADD CONSTRAINT cart_tokens_modulo_jti_unique UNIQUE (jti);


--
-- Name: cart_tokens_modulo cart_tokens_modulo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_tokens_modulo
    ADD CONSTRAINT cart_tokens_modulo_pkey PRIMARY KEY (id);


--
-- Name: catalogo_area catalogo_area_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo_area
    ADD CONSTRAINT catalogo_area_pkey PRIMARY KEY (id_catalogo, id_area);


--
-- Name: catalogo catalogo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo
    ADD CONSTRAINT catalogo_pkey PRIMARY KEY (id_catalogo);


--
-- Name: catalogo_vendedor catalogo_vendedor_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo_vendedor
    ADD CONSTRAINT catalogo_vendedor_pkey PRIMARY KEY (id_cv);


--
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id_categoria);


--
-- Name: categorias_ticket categorias_ticket_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias_ticket
    ADD CONSTRAINT categorias_ticket_pkey PRIMARY KEY (id_categoria);


--
-- Name: comprobantes comprobantes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.comprobantes
    ADD CONSTRAINT comprobantes_pkey PRIMARY KEY (id);


--
-- Name: disponibilidad disponibilidad_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.disponibilidad
    ADD CONSTRAINT disponibilidad_pkey PRIMARY KEY (id_disponibilidad);


--
-- Name: disponibilidad_vendedor disponibilidad_vendedor_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.disponibilidad_vendedor
    ADD CONSTRAINT disponibilidad_vendedor_pkey PRIMARY KEY (id_disp_v);


--
-- Name: equipos_activos equipos_activos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.equipos_activos
    ADD CONSTRAINT equipos_activos_pkey PRIMARY KEY (id_equipo);


--
-- Name: gastos_ticket gastos_ticket_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gastos_ticket
    ADD CONSTRAINT gastos_ticket_pkey PRIMARY KEY (id_gasto);


--
-- Name: historial_tickets historial_tickets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.historial_tickets
    ADD CONSTRAINT historial_tickets_pkey PRIMARY KEY (id_historial);


--
-- Name: impuestos impuestos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.impuestos
    ADD CONSTRAINT impuestos_pkey PRIMARY KEY (id_impuesto);


--
-- Name: insumos insumos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.insumos
    ADD CONSTRAINT insumos_pkey PRIMARY KEY (id_insumo);


--
-- Name: inventario inventario_id_catalogo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT inventario_id_catalogo_unique UNIQUE (id_catalogo);


--
-- Name: inventario inventario_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT inventario_pkey PRIMARY KEY (id_inventario);


--
-- Name: mantenimientos_preventivos mantenimientos_preventivos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mantenimientos_preventivos
    ADD CONSTRAINT mantenimientos_preventivos_pkey PRIMARY KEY (id_preventivo);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: movimientos movimientos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos
    ADD CONSTRAINT movimientos_pkey PRIMARY KEY (id);


--
-- Name: pagos pagos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pagos
    ADD CONSTRAINT pagos_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: pedido pedido_carrito_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_carrito_uuid_unique UNIQUE (carrito_uuid);


--
-- Name: pedido_detalles pedido_detalles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_detalles
    ADD CONSTRAINT pedido_detalles_pkey PRIMARY KEY (id);


--
-- Name: pedido_historial pedido_historial_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_historial
    ADD CONSTRAINT pedido_historial_pkey PRIMARY KEY (id);


--
-- Name: pedido_item pedido_item_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_item
    ADD CONSTRAINT pedido_item_pkey PRIMARY KEY (id);


--
-- Name: pedido pedido_numero_folio_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_numero_folio_unique UNIQUE (numero_folio);


--
-- Name: pedido pedido_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_pkey PRIMARY KEY (id);


--
-- Name: pedidos pedidos_pago_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_pago_token_unique UNIQUE (pago_token);


--
-- Name: pedidos pedidos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_pkey PRIMARY KEY (id);


--
-- Name: permiso permiso_clave_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permiso
    ADD CONSTRAINT permiso_clave_unique UNIQUE (clave);


--
-- Name: permiso permiso_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permiso
    ADD CONSTRAINT permiso_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: precios precios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.precios
    ADD CONSTRAINT precios_pkey PRIMARY KEY (id_precio);


--
-- Name: precios_vendedor precios_vendedor_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.precios_vendedor
    ADD CONSTRAINT precios_vendedor_pkey PRIMARY KEY (id_precio_v);


--
-- Name: producto producto_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_pkey PRIMARY KEY (id);


--
-- Name: productos productos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);


--
-- Name: promociones_catalogo promociones_catalogo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promociones_catalogo
    ADD CONSTRAINT promociones_catalogo_pkey PRIMARY KEY (id_promocion, id_catalogo);


--
-- Name: promociones promociones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promociones
    ADD CONSTRAINT promociones_pkey PRIMARY KEY (id_promocion);


--
-- Name: promociones_vendedor promociones_vendedor_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promociones_vendedor
    ADD CONSTRAINT promociones_vendedor_pkey PRIMARY KEY (id_promocion, id_cv);


--
-- Name: recarga recarga_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recarga
    ADD CONSTRAINT recarga_pkey PRIMARY KEY (id);


--
-- Name: recargas recargas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recargas
    ADD CONSTRAINT recargas_pkey PRIMARY KEY (id);


--
-- Name: reglas reglas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reglas
    ADD CONSTRAINT reglas_pkey PRIMARY KEY (id_regla);


--
-- Name: reglas_vendedor reglas_vendedor_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reglas_vendedor
    ADD CONSTRAINT reglas_vendedor_pkey PRIMARY KEY (id_regla_v);


--
-- Name: rol rol_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rol
    ADD CONSTRAINT rol_nombre_unique UNIQUE (nombre);


--
-- Name: rol_permiso rol_permiso_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_pkey PRIMARY KEY (id);


--
-- Name: rol rol_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rol
    ADD CONSTRAINT rol_pkey PRIMARY KEY (id);


--
-- Name: saldo_monedero saldo_monedero_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_monedero
    ADD CONSTRAINT saldo_monedero_pkey PRIMARY KEY (id);


--
-- Name: saldo_monedero saldo_monedero_usuario_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_monedero
    ADD CONSTRAINT saldo_monedero_usuario_id_unique UNIQUE (usuario_id);


--
-- Name: saldo_movimiento saldo_movimiento_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_movimiento
    ADD CONSTRAINT saldo_movimiento_pkey PRIMARY KEY (id);


--
-- Name: saldo_reglas saldo_reglas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_reglas
    ADD CONSTRAINT saldo_reglas_pkey PRIMARY KEY (id);


--
-- Name: saldo_reserva saldo_reserva_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_reserva
    ADD CONSTRAINT saldo_reserva_pkey PRIMARY KEY (id);


--
-- Name: saldo_reserva saldo_reserva_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_reserva
    ADD CONSTRAINT saldo_reserva_uuid_unique UNIQUE (uuid);


--
-- Name: saldos saldos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldos
    ADD CONSTRAINT saldos_pkey PRIMARY KEY (id);


--
-- Name: saldos saldos_usuario_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldos
    ADD CONSTRAINT saldos_usuario_id_unique UNIQUE (usuario_id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: tarjeta_lectura tarjeta_lectura_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_lectura
    ADD CONSTRAINT tarjeta_lectura_pkey PRIMARY KEY (id);


--
-- Name: tarjeta_universitaria tarjeta_universitaria_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_universitaria
    ADD CONSTRAINT tarjeta_universitaria_pkey PRIMARY KEY (id);


--
-- Name: tarjeta_universitaria tarjeta_universitaria_uid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_universitaria
    ADD CONSTRAINT tarjeta_universitaria_uid_unique UNIQUE (uid);


--
-- Name: tickets tickets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_pkey PRIMARY KEY (id_ticket);


--
-- Name: tienda tienda_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tienda
    ADD CONSTRAINT tienda_pkey PRIMARY KEY (id);


--
-- Name: ubicaciones ubicaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ubicaciones
    ADD CONSTRAINT ubicaciones_pkey PRIMARY KEY (id_ubicacion);


--
-- Name: rol_permiso uq_rol_permiso__rol_permiso; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT uq_rol_permiso__rol_permiso UNIQUE (rol_id, permiso_id);


--
-- Name: usuario uq_usuario__email; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT uq_usuario__email UNIQUE (email);


--
-- Name: usuario_password_reset uq_usuario_password_reset__token_hash; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_password_reset
    ADD CONSTRAINT uq_usuario_password_reset__token_hash UNIQUE (token_hash);


--
-- Name: usuario_perfil uq_usuario_perfil__usuario_id; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_perfil
    ADD CONSTRAINT uq_usuario_perfil__usuario_id UNIQUE (usuario_id);


--
-- Name: usuario_rol uq_usuario_rol__usuario_rol; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT uq_usuario_rol__usuario_rol UNIQUE (usuario_id, rol_id);


--
-- Name: usuario usuario_matricula_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_matricula_unique UNIQUE (matricula);


--
-- Name: usuario_password_reset usuario_password_reset_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_password_reset
    ADD CONSTRAINT usuario_password_reset_pkey PRIMARY KEY (id);


--
-- Name: usuario_perfil usuario_perfil_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_perfil
    ADD CONSTRAINT usuario_perfil_pkey PRIMARY KEY (id);


--
-- Name: usuario usuario_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_pkey PRIMARY KEY (id);


--
-- Name: usuario_rol usuario_rol_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_pkey PRIMARY KEY (id);


--
-- Name: usuario_sesion usuario_sesion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_sesion
    ADD CONSTRAINT usuario_sesion_pkey PRIMARY KEY (id);


--
-- Name: usuario_sesion usuario_sesion_session_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_sesion
    ADD CONSTRAINT usuario_sesion_session_id_unique UNIQUE (session_id);


--
-- Name: usuario_tienda usuario_tienda_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_tienda
    ADD CONSTRAINT usuario_tienda_pkey PRIMARY KEY (id);


--
-- Name: usuario_tienda usuario_tienda_usuario_id_tienda_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_tienda
    ADD CONSTRAINT usuario_tienda_usuario_id_tienda_id_unique UNIQUE (usuario_id, tienda_id);


--
-- Name: vendedores vendedores_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendedores
    ADD CONSTRAINT vendedores_email_unique UNIQUE (email);


--
-- Name: vendedores vendedores_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendedores
    ADD CONSTRAINT vendedores_pkey PRIMARY KEY (id_vendedor);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: cart_bitacora_accion_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_bitacora_accion_index ON public.cart_bitacora USING btree (accion);


--
-- Name: cart_bitacora_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_bitacora_created_at_index ON public.cart_bitacora USING btree (created_at);


--
-- Name: cart_bitacora_modulo_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_bitacora_modulo_id_created_at_index ON public.cart_bitacora USING btree (modulo_id, created_at);


--
-- Name: cart_carritos_expira_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_carritos_expira_at_index ON public.cart_carritos USING btree (expira_at);


--
-- Name: cart_carritos_modulo_id_estado_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_carritos_modulo_id_estado_index ON public.cart_carritos USING btree (modulo_id, estado);


--
-- Name: cart_carritos_usuario_ref_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_carritos_usuario_ref_index ON public.cart_carritos USING btree (usuario_ref);


--
-- Name: cart_conciliaciones_pendientes_estado_conciliacion_proximo_inte; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_conciliaciones_pendientes_estado_conciliacion_proximo_inte ON public.cart_conciliaciones_pendientes USING btree (estado_conciliacion, proximo_intento_at);


--
-- Name: cart_conciliaciones_pendientes_usuario_ref_estado_conciliacion_; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_conciliaciones_pendientes_usuario_ref_estado_conciliacion_ ON public.cart_conciliaciones_pendientes USING btree (usuario_ref, estado_conciliacion);


--
-- Name: cart_items_carrito_carrito_id_estado_item_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_items_carrito_carrito_id_estado_item_index ON public.cart_items_carrito USING btree (carrito_id, estado_item);


--
-- Name: cart_items_carrito_carrito_id_referencia_externa_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_items_carrito_carrito_id_referencia_externa_index ON public.cart_items_carrito USING btree (carrito_id, referencia_externa);


--
-- Name: cart_solicitudes_modulo_estado_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_solicitudes_modulo_estado_index ON public.cart_solicitudes_modulo USING btree (estado);


--
-- Name: cart_tokens_modulo_expira_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_tokens_modulo_expira_at_index ON public.cart_tokens_modulo USING btree (expira_at);


--
-- Name: cart_tokens_modulo_modulo_id_estado_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_tokens_modulo_modulo_id_estado_index ON public.cart_tokens_modulo USING btree (modulo_id, estado);


--
-- Name: cart_tokens_modulo_pair_jti_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_tokens_modulo_pair_jti_index ON public.cart_tokens_modulo USING btree (pair_jti);


--
-- Name: cart_tokens_modulo_replaces_jti_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cart_tokens_modulo_replaces_jti_index ON public.cart_tokens_modulo USING btree (replaces_jti);


--
-- Name: comprobantes_referencia_type_referencia_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX comprobantes_referencia_type_referencia_id_index ON public.comprobantes USING btree (referencia_type, referencia_id);


--
-- Name: idx_acceso_bitacora__created_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_acceso_bitacora__created_at ON public.acceso_bitacora USING btree (created_at);


--
-- Name: idx_acceso_bitacora__evento; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_acceso_bitacora__evento ON public.acceso_bitacora USING btree (evento);


--
-- Name: idx_acceso_bitacora__exito; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_acceso_bitacora__exito ON public.acceso_bitacora USING btree (exito);


--
-- Name: idx_acceso_bitacora__sesion_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_acceso_bitacora__sesion_id ON public.acceso_bitacora USING btree (sesion_id);


--
-- Name: idx_acceso_bitacora__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_acceso_bitacora__usuario_id ON public.acceso_bitacora USING btree (usuario_id);


--
-- Name: idx_actividad_bitacora__accion; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_actividad_bitacora__accion ON public.actividad_bitacora USING btree (accion);


--
-- Name: idx_actividad_bitacora__created_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_actividad_bitacora__created_at ON public.actividad_bitacora USING btree (created_at);


--
-- Name: idx_actividad_bitacora__modulo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_actividad_bitacora__modulo ON public.actividad_bitacora USING btree (modulo);


--
-- Name: idx_actividad_bitacora__sesion_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_actividad_bitacora__sesion_id ON public.actividad_bitacora USING btree (sesion_id);


--
-- Name: idx_actividad_bitacora__target_tabla; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_actividad_bitacora__target_tabla ON public.actividad_bitacora USING btree (target_tabla);


--
-- Name: idx_actividad_bitacora__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_actividad_bitacora__usuario_id ON public.actividad_bitacora USING btree (usuario_id);


--
-- Name: idx_archivo__carpeta_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_archivo__carpeta_id ON public.archivo USING btree (carpeta_id);


--
-- Name: idx_archivo__created_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_archivo__created_at ON public.archivo USING btree (created_at);


--
-- Name: idx_archivo__extension; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_archivo__extension ON public.archivo USING btree (extension);


--
-- Name: idx_archivo__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_archivo__usuario_id ON public.archivo USING btree (usuario_id);


--
-- Name: idx_archivo__visto_admin; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_archivo__visto_admin ON public.archivo USING btree (visto_admin);


--
-- Name: idx_archivo_carpeta__padre_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_archivo_carpeta__padre_id ON public.archivo_carpeta USING btree (padre_id);


--
-- Name: idx_archivo_carpeta__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_archivo_carpeta__usuario_id ON public.archivo_carpeta USING btree (usuario_id);


--
-- Name: idx_area__deleted_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_area__deleted_at ON public.area USING btree (deleted_at);


--
-- Name: idx_area__name_area; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_area__name_area ON public.area USING btree (name_area);


--
-- Name: idx_asignaciones_tecnicas__deleted_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_asignaciones_tecnicas__deleted_at ON public.asignaciones_tecnicas USING btree (deleted_at);


--
-- Name: idx_asignaciones_tecnicas__id_ticket; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_asignaciones_tecnicas__id_ticket ON public.asignaciones_tecnicas USING btree (id_ticket);


--
-- Name: idx_asignaciones_tecnicas__id_usuario_tecnico; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_asignaciones_tecnicas__id_usuario_tecnico ON public.asignaciones_tecnicas USING btree (id_usuario_tecnico);


--
-- Name: idx_categorias_ticket__deleted_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_categorias_ticket__deleted_at ON public.categorias_ticket USING btree (deleted_at);


--
-- Name: idx_categorias_ticket__id_area; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_categorias_ticket__id_area ON public.categorias_ticket USING btree (id_area);


--
-- Name: idx_categorias_ticket__nombre_categoria; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_categorias_ticket__nombre_categoria ON public.categorias_ticket USING btree (nombre_categoria);


--
-- Name: idx_equipos_activos__deleted_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_equipos_activos__deleted_at ON public.equipos_activos USING btree (deleted_at);


--
-- Name: idx_equipos_activos__estado_actual; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_equipos_activos__estado_actual ON public.equipos_activos USING btree (estado_actual);


--
-- Name: idx_equipos_activos__id_categoria; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_equipos_activos__id_categoria ON public.equipos_activos USING btree (id_categoria);


--
-- Name: idx_equipos_activos__id_ubicacion; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_equipos_activos__id_ubicacion ON public.equipos_activos USING btree (id_ubicacion);


--
-- Name: idx_gastos_ticket__deleted_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_gastos_ticket__deleted_at ON public.gastos_ticket USING btree (deleted_at);


--
-- Name: idx_gastos_ticket__id_insumo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_gastos_ticket__id_insumo ON public.gastos_ticket USING btree (id_insumo);


--
-- Name: idx_gastos_ticket__id_ticket; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_gastos_ticket__id_ticket ON public.gastos_ticket USING btree (id_ticket);


--
-- Name: idx_historial_tickets__deleted_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_historial_tickets__deleted_at ON public.historial_tickets USING btree (deleted_at);


--
-- Name: idx_historial_tickets__id_ticket; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_historial_tickets__id_ticket ON public.historial_tickets USING btree (id_ticket);


--
-- Name: idx_historial_tickets__id_usuario; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_historial_tickets__id_usuario ON public.historial_tickets USING btree (id_usuario);


--
-- Name: idx_insumos__deleted_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_insumos__deleted_at ON public.insumos USING btree (deleted_at);


--
-- Name: idx_mantenimientos_preventivos__deleted_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_mantenimientos_preventivos__deleted_at ON public.mantenimientos_preventivos USING btree (deleted_at);


--
-- Name: idx_mantenimientos_preventivos__id_equipo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_mantenimientos_preventivos__id_equipo ON public.mantenimientos_preventivos USING btree (id_equipo);


--
-- Name: idx_mantenimientos_preventivos__proxima_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_mantenimientos_preventivos__proxima_fecha ON public.mantenimientos_preventivos USING btree (proxima_fecha_programada);


--
-- Name: idx_pedido__created_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pedido__created_at ON public.pedido USING btree (created_at);


--
-- Name: idx_pedido__estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pedido__estado ON public.pedido USING btree (estado);


--
-- Name: idx_pedido__modulo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pedido__modulo ON public.pedido USING btree (modulo);


--
-- Name: idx_pedido__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pedido__usuario_id ON public.pedido USING btree (usuario_id);


--
-- Name: idx_pedido_historial__created_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pedido_historial__created_at ON public.pedido_historial USING btree (created_at);


--
-- Name: idx_pedido_historial__pedido_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pedido_historial__pedido_id ON public.pedido_historial USING btree (pedido_id);


--
-- Name: idx_permiso__activo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_permiso__activo ON public.permiso USING btree (activo);


--
-- Name: idx_producto__activo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_producto__activo ON public.producto USING btree (activo);


--
-- Name: idx_producto__modulo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_producto__modulo ON public.producto USING btree (modulo);


--
-- Name: idx_recarga__created_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_recarga__created_at ON public.recarga USING btree (created_at);


--
-- Name: idx_recarga__estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_recarga__estado ON public.recarga USING btree (estado);


--
-- Name: idx_recarga__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_recarga__usuario_id ON public.recarga USING btree (usuario_id);


--
-- Name: idx_rol__activo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_rol__activo ON public.rol USING btree (activo);


--
-- Name: idx_rol_permiso__permiso_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_rol_permiso__permiso_id ON public.rol_permiso USING btree (permiso_id);


--
-- Name: idx_rol_permiso__rol_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_rol_permiso__rol_id ON public.rol_permiso USING btree (rol_id);


--
-- Name: idx_saldo_movimiento__created_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_saldo_movimiento__created_at ON public.saldo_movimiento USING btree (created_at);


--
-- Name: idx_saldo_movimiento__modulo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_saldo_movimiento__modulo ON public.saldo_movimiento USING btree (modulo);


--
-- Name: idx_saldo_movimiento__tipo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_saldo_movimiento__tipo ON public.saldo_movimiento USING btree (tipo);


--
-- Name: idx_saldo_movimiento__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_saldo_movimiento__usuario_id ON public.saldo_movimiento USING btree (usuario_id);


--
-- Name: idx_saldo_reserva__carrito_uuid; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_saldo_reserva__carrito_uuid ON public.saldo_reserva USING btree (carrito_uuid);


--
-- Name: idx_saldo_reserva__estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_saldo_reserva__estado ON public.saldo_reserva USING btree (estado);


--
-- Name: idx_saldo_reserva__expira_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_saldo_reserva__expira_at ON public.saldo_reserva USING btree (expira_at);


--
-- Name: idx_saldo_reserva__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_saldo_reserva__usuario_id ON public.saldo_reserva USING btree (usuario_id);


--
-- Name: idx_saldo_reserva__uuid; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_saldo_reserva__uuid ON public.saldo_reserva USING btree (uuid);


--
-- Name: idx_tickets__deleted_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tickets__deleted_at ON public.tickets USING btree (deleted_at);


--
-- Name: idx_tickets__estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tickets__estado ON public.tickets USING btree (estado);


--
-- Name: idx_tickets__fecha_creacion; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tickets__fecha_creacion ON public.tickets USING btree (fecha_creacion);


--
-- Name: idx_tickets__id_categoria; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tickets__id_categoria ON public.tickets USING btree (id_categoria);


--
-- Name: idx_tickets__id_equipo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tickets__id_equipo ON public.tickets USING btree (id_equipo);


--
-- Name: idx_tickets__id_usuario_solicitante; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tickets__id_usuario_solicitante ON public.tickets USING btree (id_usuario_solicitante);


--
-- Name: idx_tickets__prioridad; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tickets__prioridad ON public.tickets USING btree (prioridad);


--
-- Name: idx_ubicaciones__deleted_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_ubicaciones__deleted_at ON public.ubicaciones USING btree (deleted_at);


--
-- Name: idx_ubicaciones__edificio; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_ubicaciones__edificio ON public.ubicaciones USING btree (edificio);


--
-- Name: idx_usuario__bloqueado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario__bloqueado ON public.usuario USING btree (bloqueado);


--
-- Name: idx_usuario__deleted_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario__deleted_at ON public.usuario USING btree (deleted_at);


--
-- Name: idx_usuario__email; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario__email ON public.usuario USING btree (email);


--
-- Name: idx_usuario__email_verificado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario__email_verificado ON public.usuario USING btree (email_verificado);


--
-- Name: idx_usuario__modulo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario__modulo ON public.usuario USING btree (modulo);


--
-- Name: idx_usuario_password_reset__expira_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_password_reset__expira_at ON public.usuario_password_reset USING btree (expira_at);


--
-- Name: idx_usuario_password_reset__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_password_reset__usuario_id ON public.usuario_password_reset USING btree (usuario_id);


--
-- Name: idx_usuario_perfil__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_perfil__usuario_id ON public.usuario_perfil USING btree (usuario_id);


--
-- Name: idx_usuario_rol__rol_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_rol__rol_id ON public.usuario_rol USING btree (rol_id);


--
-- Name: idx_usuario_rol__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_rol__usuario_id ON public.usuario_rol USING btree (usuario_id);


--
-- Name: idx_usuario_sesion__activa; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_sesion__activa ON public.usuario_sesion USING btree (activa);


--
-- Name: idx_usuario_sesion__session_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_sesion__session_id ON public.usuario_sesion USING btree (session_id);


--
-- Name: idx_usuario_sesion__usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_sesion__usuario_id ON public.usuario_sesion USING btree (usuario_id);


--
-- Name: movimientos_referencia_type_referencia_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX movimientos_referencia_type_referencia_id_index ON public.movimientos USING btree (referencia_type, referencia_id);


--
-- Name: pedido_item_pedido_id_producto_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pedido_item_pedido_id_producto_id_index ON public.pedido_item USING btree (pedido_id, producto_id);


--
-- Name: pedido_item_producto_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pedido_item_producto_id_index ON public.pedido_item USING btree (producto_id);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: saldo_reglas_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX saldo_reglas_created_at_index ON public.saldo_reglas USING btree (created_at);


--
-- Name: saldo_reglas_modulo_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX saldo_reglas_modulo_index ON public.saldo_reglas USING btree (modulo);


--
-- Name: saldo_reglas_tipo_limite_activo_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX saldo_reglas_tipo_limite_activo_index ON public.saldo_reglas USING btree (tipo_limite, activo);


--
-- Name: saldo_reglas_usuario_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX saldo_reglas_usuario_id_index ON public.saldo_reglas USING btree (usuario_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: acceso_bitacora trg_acceso_bitacora__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_acceso_bitacora__set_updated_at BEFORE UPDATE ON public.acceso_bitacora FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: actividad_bitacora trg_actividad_bitacora__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_actividad_bitacora__set_updated_at BEFORE UPDATE ON public.actividad_bitacora FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: archivo trg_archivo__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_archivo__set_updated_at BEFORE UPDATE ON public.archivo FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: archivo_carpeta trg_archivo_carpeta__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_archivo_carpeta__set_updated_at BEFORE UPDATE ON public.archivo_carpeta FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: area trg_area__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_area__set_updated_at BEFORE UPDATE ON public.area FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: asignaciones_tecnicas trg_asignaciones_tecnicas__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_asignaciones_tecnicas__set_updated_at BEFORE UPDATE ON public.asignaciones_tecnicas FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: categorias_ticket trg_categorias_ticket__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_categorias_ticket__set_updated_at BEFORE UPDATE ON public.categorias_ticket FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: equipos_activos trg_equipos_activos__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_equipos_activos__set_updated_at BEFORE UPDATE ON public.equipos_activos FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: gastos_ticket trg_gastos_ticket__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_gastos_ticket__set_updated_at BEFORE UPDATE ON public.gastos_ticket FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: historial_tickets trg_historial_tickets__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_historial_tickets__set_updated_at BEFORE UPDATE ON public.historial_tickets FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: insumos trg_insumos__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_insumos__set_updated_at BEFORE UPDATE ON public.insumos FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: mantenimientos_preventivos trg_mantenimientos_preventivos__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_mantenimientos_preventivos__set_updated_at BEFORE UPDATE ON public.mantenimientos_preventivos FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: pedido trg_pedido__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_pedido__set_updated_at BEFORE UPDATE ON public.pedido FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: permiso trg_permiso__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_permiso__set_updated_at BEFORE UPDATE ON public.permiso FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: producto trg_producto__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_producto__set_updated_at BEFORE UPDATE ON public.producto FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: recarga trg_recarga__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_recarga__set_updated_at BEFORE UPDATE ON public.recarga FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: rol trg_rol__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_rol__set_updated_at BEFORE UPDATE ON public.rol FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: rol_permiso trg_rol_permiso__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_rol_permiso__set_updated_at BEFORE UPDATE ON public.rol_permiso FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: saldo_monedero trg_saldo_monedero__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_saldo_monedero__set_updated_at BEFORE UPDATE ON public.saldo_monedero FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: saldo_movimiento trg_saldo_movimiento__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_saldo_movimiento__set_updated_at BEFORE UPDATE ON public.saldo_movimiento FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: tickets trg_tickets__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_tickets__set_updated_at BEFORE UPDATE ON public.tickets FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: ubicaciones trg_ubicaciones__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_ubicaciones__set_updated_at BEFORE UPDATE ON public.ubicaciones FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario trg_usuario__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_usuario__set_updated_at BEFORE UPDATE ON public.usuario FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_password_reset trg_usuario_password_reset__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_usuario_password_reset__set_updated_at BEFORE UPDATE ON public.usuario_password_reset FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_perfil trg_usuario_perfil__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_usuario_perfil__set_updated_at BEFORE UPDATE ON public.usuario_perfil FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_rol trg_usuario_rol__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_usuario_rol__set_updated_at BEFORE UPDATE ON public.usuario_rol FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_sesion trg_usuario_sesion__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_usuario_sesion__set_updated_at BEFORE UPDATE ON public.usuario_sesion FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: acceso_bitacora acceso_bitacora_sesion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.acceso_bitacora
    ADD CONSTRAINT acceso_bitacora_sesion_id_foreign FOREIGN KEY (sesion_id) REFERENCES public.usuario_sesion(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: acceso_bitacora acceso_bitacora_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.acceso_bitacora
    ADD CONSTRAINT acceso_bitacora_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: actividad_bitacora actividad_bitacora_sesion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_bitacora
    ADD CONSTRAINT actividad_bitacora_sesion_id_foreign FOREIGN KEY (sesion_id) REFERENCES public.usuario_sesion(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: actividad_bitacora actividad_bitacora_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_bitacora
    ADD CONSTRAINT actividad_bitacora_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: archivo archivo_carpeta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archivo
    ADD CONSTRAINT archivo_carpeta_id_foreign FOREIGN KEY (carpeta_id) REFERENCES public.archivo_carpeta(id) ON DELETE SET NULL;


--
-- Name: archivo_carpeta archivo_carpeta_padre_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archivo_carpeta
    ADD CONSTRAINT archivo_carpeta_padre_id_foreign FOREIGN KEY (padre_id) REFERENCES public.archivo_carpeta(id) ON DELETE SET NULL;


--
-- Name: archivo_carpeta archivo_carpeta_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archivo_carpeta
    ADD CONSTRAINT archivo_carpeta_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: archivo archivo_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archivo
    ADD CONSTRAINT archivo_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: archivo archivo_visto_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.archivo
    ADD CONSTRAINT archivo_visto_por_foreign FOREIGN KEY (visto_por) REFERENCES public.usuario(id) ON DELETE SET NULL;


--
-- Name: asignaciones_tecnicas asignaciones_tecnicas_id_ticket_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaciones_tecnicas
    ADD CONSTRAINT asignaciones_tecnicas_id_ticket_foreign FOREIGN KEY (id_ticket) REFERENCES public.tickets(id_ticket) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: asignaciones_tecnicas asignaciones_tecnicas_id_usuario_tecnico_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaciones_tecnicas
    ADD CONSTRAINT asignaciones_tecnicas_id_usuario_tecnico_foreign FOREIGN KEY (id_usuario_tecnico) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: carrito_items carrito_items_destinatario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.carrito_items
    ADD CONSTRAINT carrito_items_destinatario_id_foreign FOREIGN KEY (destinatario_id) REFERENCES public.usuario(id) ON DELETE SET NULL;


--
-- Name: carrito_items carrito_items_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.carrito_items
    ADD CONSTRAINT carrito_items_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.productos(id) ON DELETE CASCADE;


--
-- Name: carrito_items carrito_items_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.carrito_items
    ADD CONSTRAINT carrito_items_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON DELETE CASCADE;


--
-- Name: cart_bitacora cart_bitacora_modulo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_bitacora
    ADD CONSTRAINT cart_bitacora_modulo_id_foreign FOREIGN KEY (modulo_id) REFERENCES public.cart_modulos_clientes(id) ON DELETE SET NULL;


--
-- Name: cart_bitacora cart_bitacora_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_bitacora
    ADD CONSTRAINT cart_bitacora_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.usuario(id) ON DELETE SET NULL;


--
-- Name: cart_carritos cart_carritos_modulo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_carritos
    ADD CONSTRAINT cart_carritos_modulo_id_foreign FOREIGN KEY (modulo_id) REFERENCES public.cart_modulos_clientes(id);


--
-- Name: cart_conciliaciones_pendientes cart_conciliaciones_pendientes_carrito_uuid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_conciliaciones_pendientes
    ADD CONSTRAINT cart_conciliaciones_pendientes_carrito_uuid_foreign FOREIGN KEY (carrito_uuid) REFERENCES public.cart_carritos(uuid) ON DELETE CASCADE;


--
-- Name: cart_conciliaciones_pendientes cart_conciliaciones_pendientes_modulo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_conciliaciones_pendientes
    ADD CONSTRAINT cart_conciliaciones_pendientes_modulo_id_foreign FOREIGN KEY (modulo_id) REFERENCES public.cart_modulos_clientes(id);


--
-- Name: cart_items_carrito cart_items_carrito_carrito_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_items_carrito
    ADD CONSTRAINT cart_items_carrito_carrito_id_foreign FOREIGN KEY (carrito_id) REFERENCES public.cart_carritos(id) ON DELETE CASCADE;


--
-- Name: cart_items_carrito cart_items_carrito_categoria_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_items_carrito
    ADD CONSTRAINT cart_items_carrito_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES public.cart_categorias(id);


--
-- Name: cart_modulos_clientes cart_modulos_clientes_solicitud_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_modulos_clientes
    ADD CONSTRAINT cart_modulos_clientes_solicitud_id_foreign FOREIGN KEY (solicitud_id) REFERENCES public.cart_solicitudes_modulo(id);


--
-- Name: cart_reglas_categoria cart_reglas_categoria_categoria_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_reglas_categoria
    ADD CONSTRAINT cart_reglas_categoria_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES public.cart_categorias(id) ON DELETE CASCADE;


--
-- Name: cart_solicitudes_modulo cart_solicitudes_modulo_revisado_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_solicitudes_modulo
    ADD CONSTRAINT cart_solicitudes_modulo_revisado_por_foreign FOREIGN KEY (revisado_por) REFERENCES public.usuario(id) ON DELETE SET NULL;


--
-- Name: cart_tokens_modulo cart_tokens_modulo_modulo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_tokens_modulo
    ADD CONSTRAINT cart_tokens_modulo_modulo_id_foreign FOREIGN KEY (modulo_id) REFERENCES public.cart_modulos_clientes(id) ON DELETE CASCADE;


--
-- Name: cart_tokens_modulo cart_tokens_modulo_revocado_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cart_tokens_modulo
    ADD CONSTRAINT cart_tokens_modulo_revocado_por_foreign FOREIGN KEY (revocado_por) REFERENCES public.usuario(id) ON DELETE SET NULL;


--
-- Name: catalogo_area catalogo_area_id_area_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo_area
    ADD CONSTRAINT catalogo_area_id_area_foreign FOREIGN KEY (id_area) REFERENCES public.areas(id_area) ON DELETE CASCADE;


--
-- Name: catalogo_area catalogo_area_id_catalogo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo_area
    ADD CONSTRAINT catalogo_area_id_catalogo_foreign FOREIGN KEY (id_catalogo) REFERENCES public.catalogo(id_catalogo) ON DELETE CASCADE;


--
-- Name: catalogo catalogo_id_categoria_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo
    ADD CONSTRAINT catalogo_id_categoria_foreign FOREIGN KEY (id_categoria) REFERENCES public.categorias(id_categoria);


--
-- Name: catalogo catalogo_id_impuesto_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo
    ADD CONSTRAINT catalogo_id_impuesto_foreign FOREIGN KEY (id_impuesto) REFERENCES public.impuestos(id_impuesto);


--
-- Name: catalogo_vendedor catalogo_vendedor_id_catalogo_base_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo_vendedor
    ADD CONSTRAINT catalogo_vendedor_id_catalogo_base_foreign FOREIGN KEY (id_catalogo_base) REFERENCES public.catalogo(id_catalogo) ON DELETE SET NULL;


--
-- Name: catalogo_vendedor catalogo_vendedor_id_categoria_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo_vendedor
    ADD CONSTRAINT catalogo_vendedor_id_categoria_foreign FOREIGN KEY (id_categoria) REFERENCES public.categorias(id_categoria);


--
-- Name: catalogo_vendedor catalogo_vendedor_id_vendedor_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo_vendedor
    ADD CONSTRAINT catalogo_vendedor_id_vendedor_foreign FOREIGN KEY (id_vendedor) REFERENCES public.vendedores(id_vendedor) ON DELETE CASCADE;


--
-- Name: categorias_ticket categorias_ticket_id_area_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias_ticket
    ADD CONSTRAINT categorias_ticket_id_area_foreign FOREIGN KEY (id_area) REFERENCES public.area(id_area) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: comprobantes comprobantes_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.comprobantes
    ADD CONSTRAINT comprobantes_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON DELETE CASCADE;


--
-- Name: disponibilidad disponibilidad_id_catalogo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.disponibilidad
    ADD CONSTRAINT disponibilidad_id_catalogo_foreign FOREIGN KEY (id_catalogo) REFERENCES public.catalogo(id_catalogo) ON DELETE CASCADE;


--
-- Name: disponibilidad_vendedor disponibilidad_vendedor_id_cv_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.disponibilidad_vendedor
    ADD CONSTRAINT disponibilidad_vendedor_id_cv_foreign FOREIGN KEY (id_cv) REFERENCES public.catalogo_vendedor(id_cv) ON DELETE CASCADE;


--
-- Name: equipos_activos equipos_activos_id_categoria_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.equipos_activos
    ADD CONSTRAINT equipos_activos_id_categoria_foreign FOREIGN KEY (id_categoria) REFERENCES public.categorias_ticket(id_categoria) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: equipos_activos equipos_activos_id_ubicacion_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.equipos_activos
    ADD CONSTRAINT equipos_activos_id_ubicacion_foreign FOREIGN KEY (id_ubicacion) REFERENCES public.ubicaciones(id_ubicacion) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: gastos_ticket gastos_ticket_id_insumo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gastos_ticket
    ADD CONSTRAINT gastos_ticket_id_insumo_foreign FOREIGN KEY (id_insumo) REFERENCES public.insumos(id_insumo) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: gastos_ticket gastos_ticket_id_ticket_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gastos_ticket
    ADD CONSTRAINT gastos_ticket_id_ticket_foreign FOREIGN KEY (id_ticket) REFERENCES public.tickets(id_ticket) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: historial_tickets historial_tickets_id_ticket_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.historial_tickets
    ADD CONSTRAINT historial_tickets_id_ticket_foreign FOREIGN KEY (id_ticket) REFERENCES public.tickets(id_ticket) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: historial_tickets historial_tickets_id_usuario_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.historial_tickets
    ADD CONSTRAINT historial_tickets_id_usuario_foreign FOREIGN KEY (id_usuario) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: inventario inventario_id_catalogo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventario
    ADD CONSTRAINT inventario_id_catalogo_foreign FOREIGN KEY (id_catalogo) REFERENCES public.catalogo(id_catalogo) ON DELETE CASCADE;


--
-- Name: mantenimientos_preventivos mantenimientos_preventivos_id_equipo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mantenimientos_preventivos
    ADD CONSTRAINT mantenimientos_preventivos_id_equipo_foreign FOREIGN KEY (id_equipo) REFERENCES public.equipos_activos(id_equipo) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: movimientos movimientos_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos
    ADD CONSTRAINT movimientos_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON DELETE CASCADE;


--
-- Name: pagos pagos_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pagos
    ADD CONSTRAINT pagos_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON DELETE CASCADE;


--
-- Name: pedido_detalles pedido_detalles_pedido_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_detalles
    ADD CONSTRAINT pedido_detalles_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id) ON DELETE CASCADE;


--
-- Name: pedido_detalles pedido_detalles_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_detalles
    ADD CONSTRAINT pedido_detalles_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.productos(id) ON DELETE SET NULL;


--
-- Name: pedido_historial pedido_historial_pedido_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_historial
    ADD CONSTRAINT pedido_historial_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedido(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: pedido_historial pedido_historial_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_historial
    ADD CONSTRAINT pedido_historial_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: pedido_item pedido_item_pedido_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_item
    ADD CONSTRAINT pedido_item_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedido(id) ON DELETE CASCADE;


--
-- Name: pedido pedido_operador_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_operador_usuario_id_foreign FOREIGN KEY (operador_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: pedido pedido_repartidor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_repartidor_id_foreign FOREIGN KEY (repartidor_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: pedido pedido_saldo_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_saldo_movimiento_id_foreign FOREIGN KEY (saldo_movimiento_id) REFERENCES public.saldo_movimiento(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: pedido pedido_tarjeta_lectura_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_tarjeta_lectura_id_foreign FOREIGN KEY (tarjeta_lectura_id) REFERENCES public.tarjeta_lectura(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: pedido pedido_tienda_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_tienda_id_foreign FOREIGN KEY (tienda_id) REFERENCES public.tienda(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: pedido pedido_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: pedidos pedidos_destinatario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_destinatario_id_foreign FOREIGN KEY (destinatario_id) REFERENCES public.usuario(id) ON DELETE SET NULL;


--
-- Name: pedidos pedidos_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON DELETE CASCADE;


--
-- Name: precios precios_id_catalogo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.precios
    ADD CONSTRAINT precios_id_catalogo_foreign FOREIGN KEY (id_catalogo) REFERENCES public.catalogo(id_catalogo) ON DELETE CASCADE;


--
-- Name: precios_vendedor precios_vendedor_id_cv_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.precios_vendedor
    ADD CONSTRAINT precios_vendedor_id_cv_foreign FOREIGN KEY (id_cv) REFERENCES public.catalogo_vendedor(id_cv) ON DELETE CASCADE;


--
-- Name: producto producto_tienda_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_tienda_id_foreign FOREIGN KEY (tienda_id) REFERENCES public.tienda(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: promociones_catalogo promociones_catalogo_id_catalogo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promociones_catalogo
    ADD CONSTRAINT promociones_catalogo_id_catalogo_foreign FOREIGN KEY (id_catalogo) REFERENCES public.catalogo(id_catalogo) ON DELETE CASCADE;


--
-- Name: promociones_catalogo promociones_catalogo_id_promocion_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promociones_catalogo
    ADD CONSTRAINT promociones_catalogo_id_promocion_foreign FOREIGN KEY (id_promocion) REFERENCES public.promociones(id_promocion) ON DELETE CASCADE;


--
-- Name: promociones_vendedor promociones_vendedor_id_cv_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promociones_vendedor
    ADD CONSTRAINT promociones_vendedor_id_cv_foreign FOREIGN KEY (id_cv) REFERENCES public.catalogo_vendedor(id_cv) ON DELETE CASCADE;


--
-- Name: promociones_vendedor promociones_vendedor_id_promocion_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promociones_vendedor
    ADD CONSTRAINT promociones_vendedor_id_promocion_foreign FOREIGN KEY (id_promocion) REFERENCES public.promociones(id_promocion) ON DELETE CASCADE;


--
-- Name: recarga recarga_saldo_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recarga
    ADD CONSTRAINT recarga_saldo_movimiento_id_foreign FOREIGN KEY (saldo_movimiento_id) REFERENCES public.saldo_movimiento(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: recarga recarga_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recarga
    ADD CONSTRAINT recarga_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: recargas recargas_saldo_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recargas
    ADD CONSTRAINT recargas_saldo_movimiento_id_foreign FOREIGN KEY (saldo_movimiento_id) REFERENCES public.movimientos(id) ON DELETE SET NULL;


--
-- Name: recargas recargas_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recargas
    ADD CONSTRAINT recargas_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON DELETE CASCADE;


--
-- Name: reglas reglas_id_catalogo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reglas
    ADD CONSTRAINT reglas_id_catalogo_foreign FOREIGN KEY (id_catalogo) REFERENCES public.catalogo(id_catalogo) ON DELETE CASCADE;


--
-- Name: reglas_vendedor reglas_vendedor_id_cv_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reglas_vendedor
    ADD CONSTRAINT reglas_vendedor_id_cv_foreign FOREIGN KEY (id_cv) REFERENCES public.catalogo_vendedor(id_cv) ON DELETE CASCADE;


--
-- Name: rol_permiso rol_permiso_permiso_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_permiso_id_foreign FOREIGN KEY (permiso_id) REFERENCES public.permiso(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: rol_permiso rol_permiso_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.rol(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: saldo_monedero saldo_monedero_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_monedero
    ADD CONSTRAINT saldo_monedero_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: saldo_movimiento saldo_movimiento_operador_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_movimiento
    ADD CONSTRAINT saldo_movimiento_operador_usuario_id_foreign FOREIGN KEY (operador_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: saldo_movimiento saldo_movimiento_saldo_monedero_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_movimiento
    ADD CONSTRAINT saldo_movimiento_saldo_monedero_id_foreign FOREIGN KEY (saldo_monedero_id) REFERENCES public.saldo_monedero(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: saldo_movimiento saldo_movimiento_tarjeta_lectura_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_movimiento
    ADD CONSTRAINT saldo_movimiento_tarjeta_lectura_id_foreign FOREIGN KEY (tarjeta_lectura_id) REFERENCES public.tarjeta_lectura(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: saldo_movimiento saldo_movimiento_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_movimiento
    ADD CONSTRAINT saldo_movimiento_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: saldo_reglas saldo_reglas_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_reglas
    ADD CONSTRAINT saldo_reglas_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON DELETE SET NULL;


--
-- Name: saldo_reserva saldo_reserva_saldo_monedero_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_reserva
    ADD CONSTRAINT saldo_reserva_saldo_monedero_id_foreign FOREIGN KEY (saldo_monedero_id) REFERENCES public.saldo_monedero(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: saldo_reserva saldo_reserva_saldo_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_reserva
    ADD CONSTRAINT saldo_reserva_saldo_movimiento_id_foreign FOREIGN KEY (saldo_movimiento_id) REFERENCES public.saldo_movimiento(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: saldo_reserva saldo_reserva_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldo_reserva
    ADD CONSTRAINT saldo_reserva_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: saldos saldos_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.saldos
    ADD CONSTRAINT saldos_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON DELETE CASCADE;


--
-- Name: tarjeta_lectura tarjeta_lectura_operador_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_lectura
    ADD CONSTRAINT tarjeta_lectura_operador_usuario_id_foreign FOREIGN KEY (operador_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tarjeta_lectura tarjeta_lectura_pedido_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_lectura
    ADD CONSTRAINT tarjeta_lectura_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedido(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tarjeta_lectura tarjeta_lectura_tarjeta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_lectura
    ADD CONSTRAINT tarjeta_lectura_tarjeta_id_foreign FOREIGN KEY (tarjeta_id) REFERENCES public.tarjeta_universitaria(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tarjeta_universitaria tarjeta_universitaria_bloqueado_por_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_universitaria
    ADD CONSTRAINT tarjeta_universitaria_bloqueado_por_usuario_id_foreign FOREIGN KEY (bloqueado_por_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tarjeta_universitaria tarjeta_universitaria_registrado_por_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_universitaria
    ADD CONSTRAINT tarjeta_universitaria_registrado_por_usuario_id_foreign FOREIGN KEY (registrado_por_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tarjeta_universitaria tarjeta_universitaria_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_universitaria
    ADD CONSTRAINT tarjeta_universitaria_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tickets tickets_id_categoria_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_id_categoria_foreign FOREIGN KEY (id_categoria) REFERENCES public.categorias_ticket(id_categoria) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tickets tickets_id_equipo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_id_equipo_foreign FOREIGN KEY (id_equipo) REFERENCES public.equipos_activos(id_equipo) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tickets tickets_id_usuario_solicitante_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_id_usuario_solicitante_foreign FOREIGN KEY (id_usuario_solicitante) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_password_reset usuario_password_reset_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_password_reset
    ADD CONSTRAINT usuario_password_reset_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_perfil usuario_perfil_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_perfil
    ADD CONSTRAINT usuario_perfil_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_rol usuario_rol_asignado_por_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_asignado_por_usuario_id_foreign FOREIGN KEY (asignado_por_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: usuario_rol usuario_rol_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.rol(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_rol usuario_rol_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_sesion usuario_sesion_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_sesion
    ADD CONSTRAINT usuario_sesion_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario usuario_tienda_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_tienda_id_foreign FOREIGN KEY (tienda_id) REFERENCES public.tienda(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: usuario_tienda usuario_tienda_tienda_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_tienda
    ADD CONSTRAINT usuario_tienda_tienda_id_foreign FOREIGN KEY (tienda_id) REFERENCES public.tienda(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: usuario_tienda usuario_tienda_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario_tienda
    ADD CONSTRAINT usuario_tienda_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict 6TLOtapsDmIU4UIzeccuSj7AV18Y50AnMnz2JiM6NKtqJTntLS8FjqMqcefaHdq

