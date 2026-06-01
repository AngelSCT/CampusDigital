--
-- PostgreSQL database dump
--

\restrict NeUiPly262pgaOMk1ysFpqNkBCaTPKTIv75IlECNcz00jx4ulakT8KBHEQcrw4v

-- Dumped from database version 18.0
-- Dumped by pg_dump version 18.0

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
-- Name: EXTENSION citext; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION citext IS 'data type for case-insensitive character strings';


--
-- Name: set_updated_at(); Type: FUNCTION; Schema: public; Owner: campus_user
--

CREATE FUNCTION public.set_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
              NEW.updated_at = CURRENT_TIMESTAMP;
              RETURN NEW;
            END;
            $$;


ALTER FUNCTION public.set_updated_at() OWNER TO campus_user;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: acceso_bitacora; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.acceso_bitacora OWNER TO campus_user;

--
-- Name: acceso_bitacora_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.acceso_bitacora_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.acceso_bitacora_id_seq OWNER TO campus_user;

--
-- Name: acceso_bitacora_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.acceso_bitacora_id_seq OWNED BY public.acceso_bitacora.id;


--
-- Name: actividad_bitacora; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.actividad_bitacora OWNER TO campus_user;

--
-- Name: actividad_bitacora_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.actividad_bitacora_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.actividad_bitacora_id_seq OWNER TO campus_user;

--
-- Name: actividad_bitacora_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.actividad_bitacora_id_seq OWNED BY public.actividad_bitacora.id;


--
-- Name: archivo; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.archivo OWNER TO campus_user;

--
-- Name: archivo_carpeta; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.archivo_carpeta OWNER TO campus_user;

--
-- Name: archivo_carpeta_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.archivo_carpeta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.archivo_carpeta_id_seq OWNER TO campus_user;

--
-- Name: archivo_carpeta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.archivo_carpeta_id_seq OWNED BY public.archivo_carpeta.id;


--
-- Name: archivo_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.archivo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.archivo_id_seq OWNER TO campus_user;

--
-- Name: archivo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.archivo_id_seq OWNED BY public.archivo.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: campus_user
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO campus_user;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: campus_user
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO campus_user;

--
-- Name: migrations; Type: TABLE; Schema: public; Owner: campus_user
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO campus_user;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO campus_user;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: campus_user
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO campus_user;

--
-- Name: pedido; Type: TABLE; Schema: public; Owner: campus_user
--

CREATE TABLE public.pedido (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
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
    CONSTRAINT ck_pedido__estado CHECK (((estado)::text = ANY ((ARRAY['creado'::character varying, 'aceptado'::character varying, 'en_proceso'::character varying, 'listo'::character varying, 'entregado'::character varying, 'cancelado'::character varying])::text[]))),
    CONSTRAINT ck_pedido__tipo_entrega CHECK (((tipo_entrega)::text = ANY ((ARRAY['directo'::character varying, 'repartidor'::character varying])::text[])))
);


ALTER TABLE public.pedido OWNER TO campus_user;

--
-- Name: pedido_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.pedido_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pedido_id_seq OWNER TO campus_user;

--
-- Name: pedido_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.pedido_id_seq OWNED BY public.pedido.id;


--
-- Name: permiso; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.permiso OWNER TO campus_user;

--
-- Name: permiso_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.permiso_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permiso_id_seq OWNER TO campus_user;

--
-- Name: permiso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.permiso_id_seq OWNED BY public.permiso.id;


--
-- Name: producto; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.producto OWNER TO campus_user;

--
-- Name: producto_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.producto_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.producto_id_seq OWNER TO campus_user;

--
-- Name: producto_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.producto_id_seq OWNED BY public.producto.id;


--
-- Name: rol; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.rol OWNER TO campus_user;

--
-- Name: rol_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.rol_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rol_id_seq OWNER TO campus_user;

--
-- Name: rol_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.rol_id_seq OWNED BY public.rol.id;


--
-- Name: rol_permiso; Type: TABLE; Schema: public; Owner: campus_user
--

CREATE TABLE public.rol_permiso (
    id bigint NOT NULL,
    rol_id bigint NOT NULL,
    permiso_id bigint NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


ALTER TABLE public.rol_permiso OWNER TO campus_user;

--
-- Name: rol_permiso_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.rol_permiso_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rol_permiso_id_seq OWNER TO campus_user;

--
-- Name: rol_permiso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.rol_permiso_id_seq OWNED BY public.rol_permiso.id;


--
-- Name: saldo_monedero; Type: TABLE; Schema: public; Owner: campus_user
--

CREATE TABLE public.saldo_monedero (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    saldo_disponible numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    saldo_retenido numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) with time zone,
    updated_at timestamp(0) with time zone,
    deleted_at timestamp(0) with time zone,
    CONSTRAINT ck_saldo_monedero__saldo_no_negativo CHECK (((saldo_disponible >= (0)::numeric) AND (saldo_retenido >= (0)::numeric)))
);


ALTER TABLE public.saldo_monedero OWNER TO campus_user;

--
-- Name: saldo_monedero_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.saldo_monedero_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.saldo_monedero_id_seq OWNER TO campus_user;

--
-- Name: saldo_monedero_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.saldo_monedero_id_seq OWNED BY public.saldo_monedero.id;


--
-- Name: saldo_movimiento; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.saldo_movimiento OWNER TO campus_user;

--
-- Name: saldo_movimiento_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.saldo_movimiento_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.saldo_movimiento_id_seq OWNER TO campus_user;

--
-- Name: saldo_movimiento_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.saldo_movimiento_id_seq OWNED BY public.saldo_movimiento.id;


--
-- Name: tarjeta_lectura; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.tarjeta_lectura OWNER TO campus_user;

--
-- Name: COLUMN tarjeta_lectura.uid_leido; Type: COMMENT; Schema: public; Owner: campus_user
--

COMMENT ON COLUMN public.tarjeta_lectura.uid_leido IS 'UID que se intentó leer';


--
-- Name: COLUMN tarjeta_lectura.modulo; Type: COMMENT; Schema: public; Owner: campus_user
--

COMMENT ON COLUMN public.tarjeta_lectura.modulo IS 'cafeteria, copias, souvenirs, biblioteca, acceso, otro';


--
-- Name: COLUMN tarjeta_lectura.tipo_lectura; Type: COMMENT; Schema: public; Owner: campus_user
--

COMMENT ON COLUMN public.tarjeta_lectura.tipo_lectura IS 'acceso, consumo, consulta_saldo, confirmacion_entrega';


--
-- Name: tarjeta_lectura_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.tarjeta_lectura_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tarjeta_lectura_id_seq OWNER TO campus_user;

--
-- Name: tarjeta_lectura_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.tarjeta_lectura_id_seq OWNED BY public.tarjeta_lectura.id;


--
-- Name: tarjeta_universitaria; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.tarjeta_universitaria OWNER TO campus_user;

--
-- Name: COLUMN tarjeta_universitaria.uid; Type: COMMENT; Schema: public; Owner: campus_user
--

COMMENT ON COLUMN public.tarjeta_universitaria.uid IS 'UID único del chip RFID/NFC';


--
-- Name: tarjeta_universitaria_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.tarjeta_universitaria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tarjeta_universitaria_id_seq OWNER TO campus_user;

--
-- Name: tarjeta_universitaria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.tarjeta_universitaria_id_seq OWNED BY public.tarjeta_universitaria.id;


--
-- Name: tienda; Type: TABLE; Schema: public; Owner: campus_user
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
    CONSTRAINT ck_tienda__tipo CHECK (((tipo)::text = ANY ((ARRAY['cafeteria'::character varying, 'papeleria'::character varying, 'kermesse'::character varying, 'mercadito'::character varying, 'estudiante'::character varying, 'otro'::character varying])::text[])))
);


ALTER TABLE public.tienda OWNER TO campus_user;

--
-- Name: tienda_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.tienda_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tienda_id_seq OWNER TO campus_user;

--
-- Name: tienda_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.tienda_id_seq OWNED BY public.tienda.id;


--
-- Name: usuario; Type: TABLE; Schema: public; Owner: campus_user
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
    CONSTRAINT ck_usuario__apellido_len CHECK ((length((apellido)::text) <= 120)),
    CONSTRAINT ck_usuario__email_no_vacio CHECK ((length(TRIM(BOTH FROM (email)::text)) > 3)),
    CONSTRAINT ck_usuario__nombre_len CHECK ((length((nombre)::text) <= 120))
);


ALTER TABLE public.usuario OWNER TO campus_user;

--
-- Name: usuario_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.usuario_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_id_seq OWNER TO campus_user;

--
-- Name: usuario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.usuario_id_seq OWNED BY public.usuario.id;


--
-- Name: usuario_password_reset; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.usuario_password_reset OWNER TO campus_user;

--
-- Name: usuario_password_reset_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.usuario_password_reset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_password_reset_id_seq OWNER TO campus_user;

--
-- Name: usuario_password_reset_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.usuario_password_reset_id_seq OWNED BY public.usuario_password_reset.id;


--
-- Name: usuario_perfil; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.usuario_perfil OWNER TO campus_user;

--
-- Name: usuario_perfil_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.usuario_perfil_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_perfil_id_seq OWNER TO campus_user;

--
-- Name: usuario_perfil_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.usuario_perfil_id_seq OWNED BY public.usuario_perfil.id;


--
-- Name: usuario_rol; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.usuario_rol OWNER TO campus_user;

--
-- Name: usuario_rol_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.usuario_rol_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_rol_id_seq OWNER TO campus_user;

--
-- Name: usuario_rol_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.usuario_rol_id_seq OWNED BY public.usuario_rol.id;


--
-- Name: usuario_sesion; Type: TABLE; Schema: public; Owner: campus_user
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


ALTER TABLE public.usuario_sesion OWNER TO campus_user;

--
-- Name: usuario_sesion_id_seq; Type: SEQUENCE; Schema: public; Owner: campus_user
--

CREATE SEQUENCE public.usuario_sesion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_sesion_id_seq OWNER TO campus_user;

--
-- Name: usuario_sesion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: campus_user
--

ALTER SEQUENCE public.usuario_sesion_id_seq OWNED BY public.usuario_sesion.id;


--
-- Name: acceso_bitacora id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.acceso_bitacora ALTER COLUMN id SET DEFAULT nextval('public.acceso_bitacora_id_seq'::regclass);


--
-- Name: actividad_bitacora id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.actividad_bitacora ALTER COLUMN id SET DEFAULT nextval('public.actividad_bitacora_id_seq'::regclass);


--
-- Name: archivo id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.archivo ALTER COLUMN id SET DEFAULT nextval('public.archivo_id_seq'::regclass);


--
-- Name: archivo_carpeta id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.archivo_carpeta ALTER COLUMN id SET DEFAULT nextval('public.archivo_carpeta_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: pedido id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido ALTER COLUMN id SET DEFAULT nextval('public.pedido_id_seq'::regclass);


--
-- Name: permiso id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.permiso ALTER COLUMN id SET DEFAULT nextval('public.permiso_id_seq'::regclass);


--
-- Name: producto id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.producto ALTER COLUMN id SET DEFAULT nextval('public.producto_id_seq'::regclass);


--
-- Name: rol id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.rol ALTER COLUMN id SET DEFAULT nextval('public.rol_id_seq'::regclass);


--
-- Name: rol_permiso id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.rol_permiso ALTER COLUMN id SET DEFAULT nextval('public.rol_permiso_id_seq'::regclass);


--
-- Name: saldo_monedero id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.saldo_monedero ALTER COLUMN id SET DEFAULT nextval('public.saldo_monedero_id_seq'::regclass);


--
-- Name: saldo_movimiento id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.saldo_movimiento ALTER COLUMN id SET DEFAULT nextval('public.saldo_movimiento_id_seq'::regclass);


--
-- Name: tarjeta_lectura id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_lectura ALTER COLUMN id SET DEFAULT nextval('public.tarjeta_lectura_id_seq'::regclass);


--
-- Name: tarjeta_universitaria id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_universitaria ALTER COLUMN id SET DEFAULT nextval('public.tarjeta_universitaria_id_seq'::regclass);


--
-- Name: tienda id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tienda ALTER COLUMN id SET DEFAULT nextval('public.tienda_id_seq'::regclass);


--
-- Name: usuario id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario ALTER COLUMN id SET DEFAULT nextval('public.usuario_id_seq'::regclass);


--
-- Name: usuario_password_reset id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_password_reset ALTER COLUMN id SET DEFAULT nextval('public.usuario_password_reset_id_seq'::regclass);


--
-- Name: usuario_perfil id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_perfil ALTER COLUMN id SET DEFAULT nextval('public.usuario_perfil_id_seq'::regclass);


--
-- Name: usuario_rol id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_rol ALTER COLUMN id SET DEFAULT nextval('public.usuario_rol_id_seq'::regclass);


--
-- Name: usuario_sesion id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_sesion ALTER COLUMN id SET DEFAULT nextval('public.usuario_sesion_id_seq'::regclass);


--
-- Data for Name: acceso_bitacora; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.acceso_bitacora (id, usuario_id, sesion_id, email_intentado, evento, exito, detalle, ip, user_agent, meta_json, created_at, updated_at, deleted_at) FROM stdin;
1	\N	\N	ricardo.ramírez90@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	84.245.154.152	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/97.0.4149.91 Safari/532.0 EdgA/97.01035.40	{}	2026-03-23 04:20:25-06	2026-03-23 04:20:25-06	\N
2	37	36	valentina.torres90@campus.edu.mx	password_reset	t	Acceso registrado correctamente	128.41.68.12	Mozilla/5.0 (iPad; CPU OS 7_0_2 like Mac OS X; en-US) AppleWebKit/533.22.5 (KHTML, like Gecko) Version/3.0.5 Mobile/8B118 Safari/6533.22.5	{}	2026-03-20 15:33:25-06	2026-03-20 15:33:25-06	\N
3	34	104	carlos.gonzález25@campus.edu.mx	logout	t	Acceso registrado correctamente	211.229.77.194	Opera/9.77 (Windows NT 6.1; sl-SI) Presto/2.12.257 Version/12.00	{}	2026-03-22 08:31:25-06	2026-03-22 08:31:25-06	\N
4	4	40	admin@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	96.177.107.80	Mozilla/5.0 (Windows; U; Windows NT 6.2) AppleWebKit/532.27.2 (KHTML, like Gecko) Version/4.0.5 Safari/532.27.2	{}	2026-03-17 16:56:25-06	2026-03-17 16:56:25-06	\N
5	30	125	eduardo.gonzález63@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	40.237.42.60	Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5362 (KHTML, like Gecko) Chrome/36.0.877.0 Mobile Safari/5362	{}	2026-03-06 20:11:25-06	2026-03-06 20:11:25-06	\N
6	31	111	isabella.hernández16@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	55.97.116.198	Opera/9.50 (Windows 95; en-US) Presto/2.10.294 Version/10.00	{}	2026-02-26 01:16:25-06	2026-02-26 01:16:25-06	\N
7	26	46	ricardo.ramírez86@campus.edu.mx	login	t	Acceso registrado correctamente	163.181.136.213	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 5.0; Trident/3.0)	{}	2026-03-22 20:59:25-06	2026-03-22 20:59:25-06	\N
8	\N	\N	gabriela.pérez34@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	159.66.160.101	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_6 rv:2.0) Gecko/20171225 Firefox/35.0	{}	2026-03-17 06:26:25-06	2026-03-17 06:26:25-06	\N
9	32	105	andrés.pérez12@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	152.155.180.99	Opera/9.52 (Windows NT 6.0; sl-SI) Presto/2.11.193 Version/11.00	{}	2026-03-05 05:58:25-06	2026-03-05 05:58:25-06	\N
10	19	39	fernando.pérez16@campus.edu.mx	logout	t	Acceso registrado correctamente	208.211.243.20	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_6 rv:2.0) Gecko/20221019 Firefox/35.0	{}	2026-03-14 22:32:25-06	2026-03-14 22:32:25-06	\N
11	\N	\N	daniela.gonzález40@campus.edu.mx	login	f	Credenciales incorrectas o cuenta bloqueada	159.66.160.101	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 4.0; Trident/4.0)	{}	2026-03-09 06:27:25-06	2026-03-09 06:27:25-06	\N
12	19	18	fernando.pérez16@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	40.237.42.60	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_8_7) AppleWebKit/5331 (KHTML, like Gecko) Chrome/39.0.898.0 Mobile Safari/5331	{}	2026-03-23 07:36:25-06	2026-03-23 07:36:25-06	\N
13	36	11	gabriela.torres37@campus.edu.mx	login	t	Acceso registrado correctamente	153.30.103.29	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5341 (KHTML, like Gecko) Chrome/38.0.846.0 Mobile Safari/5341	{}	2026-02-22 05:20:25-06	2026-02-22 05:20:25-06	\N
14	9	46	antonio.flores35@campus.edu.mx	logout	t	Acceso registrado correctamente	159.66.160.101	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_8) AppleWebKit/536.2 (KHTML, like Gecko) Chrome/99.0.4684.75 Safari/536.2 Edg/99.01143.62	{}	2026-02-28 22:24:25-06	2026-02-28 22:24:25-06	\N
15	8	122	ricardo.hernández51@campus.edu.mx	login	t	Acceso registrado correctamente	21.83.32.249	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 4.0; Trident/3.0)	{}	2026-03-09 00:13:25-06	2026-03-09 00:13:25-06	\N
16	19	44	fernando.pérez16@campus.edu.mx	login	t	Acceso registrado correctamente	193.220.22.133	Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/3.1)	{}	2026-03-22 06:21:25-06	2026-03-22 06:21:25-06	\N
17	9	2	antonio.flores35@campus.edu.mx	password_reset	t	Acceso registrado correctamente	12.58.140.246	Mozilla/5.0 (Windows; U; Windows NT 6.1) AppleWebKit/535.20.7 (KHTML, like Gecko) Version/5.0.2 Safari/535.20.7	{}	2026-03-09 01:13:25-06	2026-03-09 01:13:25-06	\N
18	32	9	andrés.pérez12@campus.edu.mx	logout	t	Acceso registrado correctamente	21.83.32.249	Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/532.1 (KHTML, like Gecko) Version/15.0 EdgiOS/99.01095.69 Mobile/15E148 Safari/532.1	{}	2026-03-21 16:25:25-06	2026-03-21 16:25:25-06	\N
19	18	68	eduardo.hernández14@campus.edu.mx	password_reset	t	Acceso registrado correctamente	40.237.42.60	Mozilla/5.0 (iPhone; CPU iPhone OS 14_1 like Mac OS X) AppleWebKit/532.0 (KHTML, like Gecko) Version/15.0 EdgiOS/90.01136.46 Mobile/15E148 Safari/532.0	{}	2026-03-08 10:18:25-06	2026-03-08 10:18:25-06	\N
20	31	30	isabella.hernández16@campus.edu.mx	login	t	Acceso registrado correctamente	40.237.42.60	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_9) AppleWebKit/537.2 (KHTML, like Gecko) Chrome/92.0.4422.58 Safari/537.2 Edg/92.01002.42	{}	2026-03-16 05:34:25-06	2026-03-16 05:34:25-06	\N
21	\N	\N	carlos.garcía96@campus.edu.mx	token_refresh	f	Credenciales incorrectas o cuenta bloqueada	84.245.154.152	Mozilla/5.0 (Windows; U; Windows 95) AppleWebKit/533.43.5 (KHTML, like Gecko) Version/4.0.2 Safari/533.43.5	{}	2026-03-21 20:11:25-06	2026-03-21 20:11:25-06	\N
22	36	45	gabriela.torres37@campus.edu.mx	logout	t	Acceso registrado correctamente	187.71.223.183	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_1 rv:3.0; nl-NL) AppleWebKit/532.16.3 (KHTML, like Gecko) Version/4.0.4 Safari/532.16.3	{}	2026-03-21 14:15:25-06	2026-03-21 14:15:25-06	\N
23	26	115	ricardo.ramírez86@campus.edu.mx	logout	t	Acceso registrado correctamente	152.155.180.99	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.2; Trident/4.0)	{}	2026-03-23 03:08:25-06	2026-03-23 03:08:25-06	\N
24	19	118	fernando.pérez16@campus.edu.mx	password_reset	t	Acceso registrado correctamente	21.83.32.249	Mozilla/5.0 (compatible; MSIE 11.0; Windows 95; Trident/3.0)	{}	2026-03-10 23:31:25-06	2026-03-10 23:31:25-06	\N
25	\N	\N	fernando.torres78@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	193.220.22.133	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_2) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/99.0.4456.60 Safari/532.0 Edg/99.01134.45	{}	2026-03-11 11:09:25-06	2026-03-11 11:09:25-06	\N
26	9	110	antonio.flores35@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	8.53.35.195	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_1) AppleWebKit/5331 (KHTML, like Gecko) Chrome/39.0.891.0 Mobile Safari/5331	{}	2026-03-20 05:30:25-06	2026-03-20 05:30:25-06	\N
27	39	74	lucia.ramírez65@campus.edu.mx	login	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/531.2 (KHTML, like Gecko) Chrome/95.0.4483.43 Safari/531.2 EdgA/95.01031.5	{}	2026-03-20 10:23:25-06	2026-03-20 10:23:25-06	\N
28	\N	\N	ricardo.flores45@campus.edu.mx	logout	f	Credenciales incorrectas o cuenta bloqueada	107.92.187.137	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5350 (KHTML, like Gecko) Chrome/37.0.823.0 Mobile Safari/5350	{}	2026-03-23 22:05:25-06	2026-03-23 22:05:25-06	\N
29	41	114	jorge.garcía47@campus.edu.mx	password_reset	t	Acceso registrado correctamente	40.237.42.60	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_3) AppleWebKit/5331 (KHTML, like Gecko) Chrome/36.0.880.0 Mobile Safari/5331	{}	2026-03-21 06:57:25-06	2026-03-21 06:57:25-06	\N
30	18	23	eduardo.hernández14@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	12.58.140.246	Mozilla/5.0 (compatible; MSIE 11.0; Windows 95; Trident/5.1)	{}	2026-03-22 15:05:25-06	2026-03-22 15:05:25-06	\N
31	21	77	laura.garcía96@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	159.66.160.101	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/533.2 (KHTML, like Gecko) Chrome/81.0.4416.89 Safari/533.2 EdgA/81.01008.23	{}	2026-03-23 10:33:25-06	2026-03-23 10:33:25-06	\N
32	\N	\N	andrés.martínez25@campus.edu.mx	password_reset	f	Credenciales incorrectas o cuenta bloqueada	96.177.107.80	Opera/8.18 (X11; Linux i686; nl-NL) Presto/2.8.352 Version/12.00	{}	2026-03-20 22:47:25-06	2026-03-20 22:47:25-06	\N
33	12	118	andrés.flores53@campus.edu.mx	login	t	Acceso registrado correctamente	128.41.68.12	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_0 rv:4.0; en-US) AppleWebKit/533.35.7 (KHTML, like Gecko) Version/5.1 Safari/533.35.7	{}	2026-03-16 13:23:25-06	2026-03-16 13:23:25-06	\N
34	\N	\N	gabriela.flores57@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	159.66.160.101	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.2; Trident/4.1)	{}	2026-03-20 19:41:25-06	2026-03-20 19:41:25-06	\N
35	15	2	fernando.torres78@campus.edu.mx	password_reset	t	Acceso registrado correctamente	153.30.103.29	Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20260102 Firefox/37.0	{}	2026-03-04 17:11:25-06	2026-03-04 17:11:25-06	\N
36	21	76	laura.garcía96@campus.edu.mx	login	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (iPhone; CPU iPhone OS 7_2_2 like Mac OS X; sl-SI) AppleWebKit/532.48.5 (KHTML, like Gecko) Version/4.0.5 Mobile/8B116 Safari/6532.48.5	{}	2026-03-22 16:14:25-06	2026-03-22 16:14:25-06	\N
37	33	47	daniela.garcía26@campus.edu.mx	password_reset	t	Acceso registrado correctamente	128.41.68.12	Opera/9.69 (Windows 98; Win 9x 4.90; nl-NL) Presto/2.10.210 Version/12.00	{}	2026-03-17 09:28:25-06	2026-03-17 09:28:25-06	\N
38	40	26	andrés.gonzález79@campus.edu.mx	login	t	Acceso registrado correctamente	84.245.154.152	Mozilla/5.0 (compatible; MSIE 8.0; Windows 95; Trident/3.0)	{}	2026-02-22 12:06:25-06	2026-02-22 12:06:25-06	\N
39	34	134	carlos.gonzález25@campus.edu.mx	password_reset	t	Acceso registrado correctamente	208.211.243.20	Mozilla/5.0 (X11; Linux i686) AppleWebKit/533.1 (KHTML, like Gecko) Chrome/82.0.4227.53 Safari/533.1 EdgA/82.01045.13	{}	2026-03-17 06:51:25-06	2026-03-17 06:51:25-06	\N
40	33	105	daniela.garcía26@campus.edu.mx	login	t	Acceso registrado correctamente	12.58.140.246	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5361 (KHTML, like Gecko) Chrome/38.0.809.0 Mobile Safari/5361	{}	2026-03-17 10:34:25-06	2026-03-17 10:34:25-06	\N
41	8	56	ricardo.hernández51@campus.edu.mx	password_reset	t	Acceso registrado correctamente	50.6.183.32	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5322 (KHTML, like Gecko) Chrome/40.0.808.0 Mobile Safari/5322	{}	2026-03-16 01:31:25-06	2026-03-16 01:31:25-06	\N
42	31	67	isabella.hernández16@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	21.83.32.249	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_6 rv:2.0) Gecko/20201211 Firefox/37.0	{}	2026-02-27 08:56:25-06	2026-02-27 08:56:25-06	\N
43	14	90	daniela.hernández10@campus.edu.mx	login	t	Acceso registrado correctamente	84.245.154.152	Mozilla/5.0 (Windows 98) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/97.0.4470.92 Safari/535.2 Edg/97.01011.86	{}	2026-03-01 01:26:25-06	2026-03-01 01:26:25-06	\N
44	21	90	laura.garcía96@campus.edu.mx	login	t	Acceso registrado correctamente	12.58.140.246	Opera/9.49 (Windows NT 6.2; sl-SI) Presto/2.9.177 Version/12.00	{}	2026-03-21 02:47:25-06	2026-03-21 02:47:25-06	\N
45	\N	\N	luis.pérez69@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	8.53.35.195	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_8) AppleWebKit/5341 (KHTML, like Gecko) Chrome/38.0.853.0 Mobile Safari/5341	{}	2026-03-10 17:42:25-06	2026-03-10 17:42:25-06	\N
46	\N	\N	isabella.hernández16@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	193.220.22.133	Mozilla/5.0 (iPhone; CPU iPhone OS 15_2 like Mac OS X) AppleWebKit/531.1 (KHTML, like Gecko) Version/15.0 EdgiOS/99.01047.98 Mobile/15E148 Safari/531.1	{}	2026-03-16 19:27:25-06	2026-03-16 19:27:25-06	\N
47	\N	\N	daniela.hernández10@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	187.71.223.183	Opera/9.46 (Windows NT 5.01; en-US) Presto/2.10.224 Version/11.00	{}	2026-03-14 02:58:25-06	2026-03-14 02:58:25-06	\N
48	4	105	admin@campus.edu.mx	login	t	Acceso registrado correctamente	163.181.136.213	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_0 rv:4.0) Gecko/20181002 Firefox/35.0	{}	2026-03-20 21:25:25-06	2026-03-20 21:25:25-06	\N
49	7	84	sofia.ramírez21@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	50.6.183.32	Mozilla/5.0 (Windows; U; Windows NT 6.2) AppleWebKit/534.9.5 (KHTML, like Gecko) Version/5.0.4 Safari/534.9.5	{}	2026-03-22 21:58:25-06	2026-03-22 21:58:25-06	\N
50	27	112	ricardo.ramírez90@campus.edu.mx	password_reset	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5341 (KHTML, like Gecko) Chrome/38.0.892.0 Mobile Safari/5341	{}	2026-03-14 10:50:25-06	2026-03-14 10:50:25-06	\N
51	24	121	andrés.martínez25@campus.edu.mx	login	t	Acceso registrado correctamente	208.211.243.20	Mozilla/5.0 (X11; Linux x86_64; rv:7.0) Gecko/20100521 Firefox/35.0	{}	2026-03-05 02:44:25-06	2026-03-05 02:44:25-06	\N
52	\N	\N	gabriela.flores57@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	8.53.35.195	Mozilla/5.0 (compatible; MSIE 10.0; Windows CE; Trident/3.1)	{}	2026-03-18 21:10:25-06	2026-03-18 21:10:25-06	\N
53	\N	\N	fernando.torres78@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	152.155.180.99	Opera/8.11 (Windows NT 4.0; nl-NL) Presto/2.10.303 Version/12.00	{}	2026-03-16 12:33:25-06	2026-03-16 12:33:25-06	\N
54	18	99	eduardo.hernández14@campus.edu.mx	logout	t	Acceso registrado correctamente	159.66.160.101	Mozilla/5.0 (X11; Linux i686) AppleWebKit/531.0 (KHTML, like Gecko) Chrome/95.0.4349.96 Safari/531.0 EdgA/95.01014.60	{}	2026-03-16 00:14:25-06	2026-03-16 00:14:25-06	\N
55	19	91	fernando.pérez16@campus.edu.mx	password_reset	t	Acceso registrado correctamente	208.211.243.20	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5312 (KHTML, like Gecko) Chrome/38.0.884.0 Mobile Safari/5312	{}	2026-03-22 00:37:25-06	2026-03-22 00:37:25-06	\N
56	\N	\N	fernando.torres78@campus.edu.mx	logout	f	Credenciales incorrectas o cuenta bloqueada	8.53.35.195	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5362 (KHTML, like Gecko) Chrome/40.0.805.0 Mobile Safari/5362	{}	2026-03-16 14:38:25-06	2026-03-16 14:38:25-06	\N
57	\N	\N	gabriela.pérez34@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	96.177.107.80	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 6.0; Trident/4.1)	{}	2026-02-25 05:27:25-06	2026-02-25 05:27:25-06	\N
58	11	71	david.garcía63@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	208.211.243.20	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_2) AppleWebKit/5311 (KHTML, like Gecko) Chrome/39.0.835.0 Mobile Safari/5311	{}	2026-03-20 02:18:25-06	2026-03-20 02:18:25-06	\N
59	17	41	carlos.garcía96@campus.edu.mx	logout	t	Acceso registrado correctamente	187.71.223.183	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.2; Trident/4.1)	{}	2026-03-23 03:04:25-06	2026-03-23 03:04:25-06	\N
60	9	68	antonio.flores35@campus.edu.mx	password_reset	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/36.0.836.0 Mobile Safari/5352	{}	2026-02-22 04:50:25-06	2026-02-22 04:50:25-06	\N
61	\N	\N	david.garcía63@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	8.53.35.195	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/98.0.4072.92 Safari/537.1 EdgA/98.01115.84	{}	2026-03-16 07:52:25-06	2026-03-16 07:52:25-06	\N
62	\N	\N	eduardo.ramírez63@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	12.58.140.246	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3) AppleWebKit/533.0 (KHTML, like Gecko) Chrome/80.0.4127.86 Safari/533.0 Edg/80.01023.43	{}	2026-03-10 23:28:25-06	2026-03-10 23:28:25-06	\N
63	15	130	fernando.torres78@campus.edu.mx	logout	t	Acceso registrado correctamente	84.245.154.152	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8) AppleWebKit/534.1 (KHTML, like Gecko) Chrome/98.0.4072.38 Safari/534.1 Edg/98.01134.96	{}	2026-03-20 15:19:25-06	2026-03-20 15:19:25-06	\N
64	\N	\N	laura.cruz43@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	55.97.116.198	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/5.0)	{}	2026-03-22 11:50:25-06	2026-03-22 11:50:25-06	\N
65	\N	\N	andrés.gonzález79@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	208.211.243.20	Opera/9.48 (Windows 98; Win 9x 4.90; nl-NL) Presto/2.12.250 Version/12.00	{}	2026-03-20 12:50:25-06	2026-03-20 12:50:25-06	\N
66	\N	\N	ricardo.flores45@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	84.245.154.152	Mozilla/5.0 (Windows 98) AppleWebKit/5310 (KHTML, like Gecko) Chrome/36.0.803.0 Mobile Safari/5310	{}	2026-03-20 04:59:25-06	2026-03-20 04:59:25-06	\N
67	25	4	jorge.torres64@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	55.97.116.198	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_7) AppleWebKit/532.1 (KHTML, like Gecko) Chrome/88.0.4703.36 Safari/532.1 Edg/88.01105.58	{}	2026-03-13 10:20:25-06	2026-03-13 10:20:25-06	\N
68	\N	\N	eduardo.hernández14@campus.edu.mx	logout	f	Credenciales incorrectas o cuenta bloqueada	96.177.107.80	Opera/8.60 (Windows NT 5.1; sl-SI) Presto/2.10.305 Version/10.00	{}	2026-02-28 14:41:25-06	2026-02-28 14:41:25-06	\N
69	13	10	daniela.gonzález40@campus.edu.mx	logout	t	Acceso registrado correctamente	187.71.223.183	Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/531.39.5 (KHTML, like Gecko) Version/5.0.1 Safari/531.39.5	{}	2026-02-21 21:32:25-06	2026-02-21 21:32:25-06	\N
70	32	47	andrés.pérez12@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 4.0; Trident/4.0)	{}	2026-02-27 12:40:25-06	2026-02-27 12:40:25-06	\N
71	6	109	daniela.gonzález26@campus.edu.mx	logout	t	Acceso registrado correctamente	107.92.187.137	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_7 rv:4.0; sl-SI) AppleWebKit/532.47.6 (KHTML, like Gecko) Version/4.0.1 Safari/532.47.6	{}	2026-03-22 01:18:25-06	2026-03-22 01:18:25-06	\N
72	14	83	daniela.hernández10@campus.edu.mx	password_reset	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (Windows NT 5.01) AppleWebKit/534.1 (KHTML, like Gecko) Chrome/92.0.4237.29 Safari/534.1 Edg/92.01028.3	{}	2026-03-10 20:43:25-06	2026-03-10 20:43:25-06	\N
73	4	113	admin@campus.edu.mx	password_reset	t	Acceso registrado correctamente	107.92.187.137	Mozilla/5.0 (X11; Linux x86_64; rv:6.0) Gecko/20130819 Firefox/36.0	{}	2026-03-05 00:39:25-06	2026-03-05 00:39:25-06	\N
74	\N	\N	ricardo.ramírez86@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	12.58.140.246	Mozilla/5.0 (compatible; MSIE 9.0; Windows 95; Trident/4.1)	{}	2026-03-06 02:32:25-06	2026-03-06 02:32:25-06	\N
75	10	47	eduardo.ramírez63@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	163.181.136.213	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_6 rv:6.0; nl-NL) AppleWebKit/534.7.3 (KHTML, like Gecko) Version/4.0 Safari/534.7.3	{}	2026-03-22 15:57:25-06	2026-03-22 15:57:25-06	\N
76	\N	\N	daniela.hernández10@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	84.245.154.152	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 6.0; Trident/4.1)	{}	2026-03-20 16:37:25-06	2026-03-20 16:37:25-06	\N
77	26	23	ricardo.ramírez86@campus.edu.mx	logout	t	Acceso registrado correctamente	84.245.154.152	Opera/9.14 (Windows NT 6.1; sl-SI) Presto/2.8.355 Version/10.00	{}	2026-03-18 09:03:25-06	2026-03-18 09:03:25-06	\N
78	17	102	carlos.garcía96@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	163.181.136.213	Mozilla/5.0 (Windows NT 4.0) AppleWebKit/5352 (KHTML, like Gecko) Chrome/37.0.896.0 Mobile Safari/5352	{}	2026-03-18 03:43:25-06	2026-03-18 03:43:25-06	\N
79	27	115	ricardo.ramírez90@campus.edu.mx	logout	t	Acceso registrado correctamente	21.83.32.249	Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5321 (KHTML, like Gecko) Chrome/39.0.811.0 Mobile Safari/5321	{}	2026-03-20 01:44:25-06	2026-03-20 01:44:25-06	\N
80	16	99	laura.cruz43@campus.edu.mx	login	t	Acceso registrado correctamente	187.71.223.183	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5352 (KHTML, like Gecko) Chrome/40.0.835.0 Mobile Safari/5352	{}	2026-02-23 19:26:25-06	2026-02-23 19:26:25-06	\N
81	31	35	isabella.hernández16@campus.edu.mx	logout	t	Acceso registrado correctamente	21.83.32.249	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5342 (KHTML, like Gecko) Chrome/37.0.811.0 Mobile Safari/5342	{}	2026-03-23 15:24:25-06	2026-03-23 15:24:25-06	\N
82	25	55	jorge.torres64@campus.edu.mx	login	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (Windows; U; Windows NT 6.1) AppleWebKit/535.45.1 (KHTML, like Gecko) Version/4.0.2 Safari/535.45.1	{}	2026-03-22 00:12:25-06	2026-03-22 00:12:25-06	\N
83	20	120	jorge.flores83@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	128.41.68.12	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_4 rv:2.0; en-US) AppleWebKit/534.50.6 (KHTML, like Gecko) Version/5.0.2 Safari/534.50.6	{}	2026-03-19 02:07:25-06	2026-03-19 02:07:25-06	\N
84	15	73	fernando.torres78@campus.edu.mx	logout	t	Acceso registrado correctamente	107.92.187.137	Mozilla/5.0 (Windows; U; Windows 98) AppleWebKit/535.14.7 (KHTML, like Gecko) Version/5.0.5 Safari/535.14.7	{}	2026-03-16 07:02:25-06	2026-03-16 07:02:25-06	\N
85	\N	\N	antonio.flores35@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	153.30.103.29	Opera/8.84 (X11; Linux x86_64; nl-NL) Presto/2.8.292 Version/11.00	{}	2026-02-23 21:12:25-06	2026-02-23 21:12:25-06	\N
86	36	17	gabriela.torres37@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.0.20) Gecko/20100321 Firefox/36.0	{}	2026-02-21 11:35:25-06	2026-02-21 11:35:25-06	\N
87	17	52	carlos.garcía96@campus.edu.mx	login	t	Acceso registrado correctamente	159.66.160.101	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.1; Trident/4.1)	{}	2026-03-23 19:55:25-06	2026-03-23 19:55:25-06	\N
88	\N	\N	carlos.gonzález25@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	187.71.223.183	Mozilla/5.0 (compatible; MSIE 10.0; Windows 95; Trident/3.0)	{}	2026-03-22 03:24:25-06	2026-03-22 03:24:25-06	\N
89	25	3	jorge.torres64@campus.edu.mx	login	t	Acceso registrado correctamente	96.177.107.80	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_7 rv:2.0; en-US) AppleWebKit/535.27.7 (KHTML, like Gecko) Version/4.0.1 Safari/535.27.7	{}	2026-03-22 22:40:25-06	2026-03-22 22:40:25-06	\N
90	10	125	eduardo.ramírez63@campus.edu.mx	login	t	Acceso registrado correctamente	107.92.187.137	Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.1.20) Gecko/20140514 Firefox/36.0	{}	2026-03-22 00:15:25-06	2026-03-22 00:15:25-06	\N
91	\N	\N	david.flores22@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	55.97.116.198	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_7 rv:6.0) Gecko/20220506 Firefox/36.0	{}	2026-03-16 21:00:25-06	2026-03-16 21:00:25-06	\N
92	36	42	gabriela.torres37@campus.edu.mx	logout	t	Acceso registrado correctamente	50.6.183.32	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_4 rv:6.0) Gecko/20240728 Firefox/35.0	{}	2026-02-26 02:39:25-06	2026-02-26 02:39:25-06	\N
93	21	131	laura.garcía96@campus.edu.mx	logout	t	Acceso registrado correctamente	152.155.180.99	Mozilla/5.0 (Windows NT 6.2) AppleWebKit/533.1 (KHTML, like Gecko) Chrome/92.0.4098.22 Safari/533.1 Edg/92.01055.84	{}	2026-03-23 14:27:25-06	2026-03-23 14:27:25-06	\N
94	37	35	valentina.torres90@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	8.53.35.195	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4) AppleWebKit/5361 (KHTML, like Gecko) Chrome/36.0.855.0 Mobile Safari/5361	{}	2026-03-05 02:55:25-06	2026-03-05 02:55:25-06	\N
95	28	134	gabriela.flores57@campus.edu.mx	login	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (compatible; MSIE 6.0; Windows 98; Win 9x 4.90; Trident/4.0)	{}	2026-03-23 22:41:25-06	2026-03-23 22:41:25-06	\N
96	\N	\N	eduardo.ramírez63@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	159.66.160.101	Mozilla/5.0 (Windows; U; Windows NT 6.0) AppleWebKit/534.20.1 (KHTML, like Gecko) Version/5.0.3 Safari/534.20.1	{}	2026-02-22 13:23:25-06	2026-02-22 13:23:25-06	\N
97	15	104	fernando.torres78@campus.edu.mx	logout	t	Acceso registrado correctamente	84.245.154.152	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_9 rv:5.0) Gecko/20200130 Firefox/37.0	{}	2026-03-06 00:17:25-06	2026-03-06 00:17:25-06	\N
98	25	116	jorge.torres64@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	208.211.243.20	Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5321 (KHTML, like Gecko) Chrome/37.0.823.0 Mobile Safari/5321	{}	2026-03-21 13:26:25-06	2026-03-21 13:26:25-06	\N
99	40	45	andrés.gonzález79@campus.edu.mx	logout	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (iPhone; CPU iPhone OS 8_2_1 like Mac OS X; sl-SI) AppleWebKit/535.40.4 (KHTML, like Gecko) Version/4.0.5 Mobile/8B113 Safari/6535.40.4	{}	2026-03-04 01:06:25-06	2026-03-04 01:06:25-06	\N
100	14	87	daniela.hernández10@campus.edu.mx	logout	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (X11; Linux x86_64; rv:7.0) Gecko/20170509 Firefox/37.0	{}	2026-02-23 08:26:25-06	2026-02-23 08:26:25-06	\N
101	38	56	carlos.martínez99@campus.edu.mx	login	t	Acceso registrado correctamente	84.245.154.152	Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/3.0)	{}	2026-03-18 12:02:25-06	2026-03-18 12:02:25-06	\N
102	10	74	eduardo.ramírez63@campus.edu.mx	password_reset	t	Acceso registrado correctamente	112.89.71.69	Mozilla/5.0 (compatible; MSIE 8.0; Windows 95; Trident/4.1)	{}	2026-03-20 17:51:25-06	2026-03-20 17:51:25-06	\N
103	34	103	carlos.gonzález25@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	159.66.160.101	Mozilla/5.0 (X11; Linux i686) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/86.0.4112.36 Safari/532.0 EdgA/86.01068.49	{}	2026-03-19 05:26:25-06	2026-03-19 05:26:25-06	\N
104	8	124	ricardo.hernández51@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	159.66.160.101	Mozilla/5.0 (compatible; MSIE 10.0; Windows 95; Trident/3.0)	{}	2026-03-03 12:37:25-06	2026-03-03 12:37:25-06	\N
105	35	66	luis.pérez69@campus.edu.mx	login	t	Acceso registrado correctamente	193.220.22.133	Mozilla/5.0 (Windows NT 5.01) AppleWebKit/531.1 (KHTML, like Gecko) Chrome/96.0.4245.46 Safari/531.1 Edg/96.01066.18	{}	2026-03-07 15:37:25-06	2026-03-07 15:37:25-06	\N
106	39	27	lucia.ramírez65@campus.edu.mx	login	t	Acceso registrado correctamente	152.155.180.99	Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.1.20) Gecko/20100305 Firefox/35.0	{}	2026-03-20 02:03:25-06	2026-03-20 02:03:25-06	\N
107	\N	\N	daniela.garcía26@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	152.155.180.99	Mozilla/5.0 (iPad; CPU OS 7_2_2 like Mac OS X; nl-NL) AppleWebKit/531.24.3 (KHTML, like Gecko) Version/3.0.5 Mobile/8B115 Safari/6531.24.3	{}	2026-02-27 21:40:25-06	2026-02-27 21:40:25-06	\N
108	29	61	gabriela.pérez34@campus.edu.mx	logout	t	Acceso registrado correctamente	187.71.223.183	Mozilla/5.0 (Windows CE) AppleWebKit/533.1 (KHTML, like Gecko) Chrome/84.0.4077.40 Safari/533.1 Edg/84.01068.51	{}	2026-03-17 10:22:25-06	2026-03-17 10:22:25-06	\N
109	29	73	gabriela.pérez34@campus.edu.mx	password_reset	t	Acceso registrado correctamente	50.6.183.32	Opera/8.32 (Windows NT 6.1; sl-SI) Presto/2.11.345 Version/12.00	{}	2026-02-22 00:21:25-06	2026-02-22 00:21:25-06	\N
110	\N	\N	jorge.garcía47@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	187.71.223.183	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_5 rv:5.0; nl-NL) AppleWebKit/535.26.1 (KHTML, like Gecko) Version/4.0 Safari/535.26.1	{}	2026-03-07 15:59:25-06	2026-03-07 15:59:25-06	\N
111	\N	\N	laura.cruz43@campus.edu.mx	token_refresh	f	Credenciales incorrectas o cuenta bloqueada	187.71.223.183	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 4.0; Trident/4.1)	{}	2026-03-21 02:52:25-06	2026-03-21 02:52:25-06	\N
112	36	70	gabriela.torres37@campus.edu.mx	logout	t	Acceso registrado correctamente	8.53.35.195	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/80.0.4149.98 Safari/535.2 EdgA/80.01015.72	{}	2026-03-19 06:32:25-06	2026-03-19 06:32:25-06	\N
113	4	89	admin@campus.edu.mx	login	t	Acceso registrado correctamente	159.66.160.101	Mozilla/5.0 (Windows NT 5.1; nl-NL; rv:1.9.1.20) Gecko/20160810 Firefox/35.0	{}	2026-03-20 14:54:25-06	2026-03-20 14:54:25-06	\N
114	36	98	gabriela.torres37@campus.edu.mx	password_reset	t	Acceso registrado correctamente	152.155.180.99	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_2) AppleWebKit/5362 (KHTML, like Gecko) Chrome/36.0.804.0 Mobile Safari/5362	{}	2026-03-22 00:53:25-06	2026-03-22 00:53:25-06	\N
115	\N	\N	ricardo.ramírez90@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	55.97.116.198	Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20150206 Firefox/36.0	{}	2026-03-19 07:54:25-06	2026-03-19 07:54:25-06	\N
116	\N	\N	ricardo.ramírez90@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	193.220.22.133	Opera/8.91 (X11; Linux x86_64; en-US) Presto/2.9.290 Version/12.00	{}	2026-03-21 07:35:25-06	2026-03-21 07:35:25-06	\N
117	27	91	ricardo.ramírez90@campus.edu.mx	login	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (Windows NT 6.1; nl-NL; rv:1.9.1.20) Gecko/20151109 Firefox/35.0	{}	2026-03-16 05:17:25-06	2026-03-16 05:17:25-06	\N
118	18	135	eduardo.hernández14@campus.edu.mx	logout	t	Acceso registrado correctamente	159.66.160.101	Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.01; Trident/4.0)	{}	2026-03-16 16:38:25-06	2026-03-16 16:38:25-06	\N
119	7	76	sofia.ramírez21@campus.edu.mx	password_reset	t	Acceso registrado correctamente	40.237.42.60	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_2 rv:4.0; nl-NL) AppleWebKit/533.15.5 (KHTML, like Gecko) Version/5.1 Safari/533.15.5	{}	2026-02-23 01:03:25-06	2026-02-23 01:03:25-06	\N
120	10	17	eduardo.ramírez63@campus.edu.mx	password_reset	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (compatible; MSIE 8.0; Windows CE; Trident/4.1)	{}	2026-03-17 18:58:25-06	2026-03-17 18:58:25-06	\N
121	18	100	eduardo.hernández14@campus.edu.mx	login	t	Acceso registrado correctamente	159.66.160.101	Opera/9.42 (X11; Linux x86_64; en-US) Presto/2.8.260 Version/12.00	{}	2026-03-17 06:16:25-06	2026-03-17 06:16:25-06	\N
122	30	111	eduardo.gonzález63@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	128.41.68.12	Mozilla/5.0 (X11; Linux i686) AppleWebKit/531.2 (KHTML, like Gecko) Chrome/94.0.4318.26 Safari/531.2 EdgA/94.01014.23	{}	2026-03-17 20:06:25-06	2026-03-17 20:06:25-06	\N
123	37	93	valentina.torres90@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	8.53.35.195	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_4) AppleWebKit/533.1 (KHTML, like Gecko) Chrome/95.0.4107.86 Safari/533.1 Edg/95.01016.44	{}	2026-03-20 22:35:25-06	2026-03-20 22:35:25-06	\N
124	25	77	jorge.torres64@campus.edu.mx	logout	t	Acceso registrado correctamente	50.6.183.32	Mozilla/5.0 (Windows; U; Windows NT 4.0) AppleWebKit/533.1.2 (KHTML, like Gecko) Version/4.1 Safari/533.1.2	{}	2026-03-18 03:36:25-06	2026-03-18 03:36:25-06	\N
125	\N	\N	jorge.garcía47@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	128.41.68.12	Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_2 like Mac OS X; en-US) AppleWebKit/535.44.1 (KHTML, like Gecko) Version/3.0.5 Mobile/8B113 Safari/6535.44.1	{}	2026-02-23 07:03:25-06	2026-02-23 07:03:25-06	\N
126	18	103	eduardo.hernández14@campus.edu.mx	password_reset	t	Acceso registrado correctamente	40.237.42.60	Opera/9.93 (X11; Linux x86_64; sl-SI) Presto/2.10.346 Version/12.00	{}	2026-03-16 02:40:25-06	2026-03-16 02:40:25-06	\N
127	27	68	ricardo.ramírez90@campus.edu.mx	login	t	Acceso registrado correctamente	152.155.180.99	Mozilla/5.0 (Windows; U; Windows NT 5.01) AppleWebKit/533.31.1 (KHTML, like Gecko) Version/4.0 Safari/533.31.1	{}	2026-03-22 04:10:25-06	2026-03-22 04:10:25-06	\N
128	16	65	laura.cruz43@campus.edu.mx	password_reset	t	Acceso registrado correctamente	40.237.42.60	Mozilla/5.0 (X11; Linux i686; rv:7.0) Gecko/20190709 Firefox/36.0	{}	2026-03-23 09:37:25-06	2026-03-23 09:37:25-06	\N
129	4	31	admin@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	128.41.68.12	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 6.1; Trident/5.0)	{}	2026-03-22 17:03:25-06	2026-03-22 17:03:25-06	\N
130	17	120	carlos.garcía96@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	208.211.243.20	Opera/9.71 (Windows NT 5.0; en-US) Presto/2.11.244 Version/12.00	{}	2026-03-02 13:35:25-06	2026-03-02 13:35:25-06	\N
131	16	132	laura.cruz43@campus.edu.mx	logout	t	Acceso registrado correctamente	8.53.35.195	Mozilla/5.0 (Windows 98; nl-NL; rv:1.9.0.20) Gecko/20120622 Firefox/36.0	{}	2026-03-19 03:43:25-06	2026-03-19 03:43:25-06	\N
132	6	50	daniela.gonzález26@campus.edu.mx	login	t	Acceso registrado correctamente	163.181.136.213	Mozilla/5.0 (iPhone; CPU iPhone OS 14_1 like Mac OS X) AppleWebKit/535.1 (KHTML, like Gecko) Version/15.0 EdgiOS/90.01068.88 Mobile/15E148 Safari/535.1	{}	2026-03-19 21:51:25-06	2026-03-19 21:51:25-06	\N
133	7	83	sofia.ramírez21@campus.edu.mx	logout	t	Acceso registrado correctamente	50.6.183.32	Opera/8.35 (X11; Linux i686; en-US) Presto/2.8.306 Version/11.00	{}	2026-03-19 12:46:25-06	2026-03-19 12:46:25-06	\N
134	21	77	laura.garcía96@campus.edu.mx	login	t	Acceso registrado correctamente	208.211.243.20	Mozilla/5.0 (Windows; U; Windows NT 6.0) AppleWebKit/533.9.2 (KHTML, like Gecko) Version/4.0.3 Safari/533.9.2	{}	2026-02-21 10:18:25-06	2026-02-21 10:18:25-06	\N
135	20	81	jorge.flores83@campus.edu.mx	logout	t	Acceso registrado correctamente	163.181.136.213	Mozilla/5.0 (X11; Linux i686) AppleWebKit/536.1 (KHTML, like Gecko) Chrome/94.0.4457.74 Safari/536.1 EdgA/94.01111.68	{}	2026-02-25 08:49:25-06	2026-02-25 08:49:25-06	\N
136	29	107	gabriela.pérez34@campus.edu.mx	logout	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (iPhone; CPU iPhone OS 7_2_1 like Mac OS X; nl-NL) AppleWebKit/533.15.1 (KHTML, like Gecko) Version/3.0.5 Mobile/8B111 Safari/6533.15.1	{}	2026-03-19 15:12:25-06	2026-03-19 15:12:25-06	\N
137	\N	\N	daniela.garcía26@campus.edu.mx	password_reset	f	Credenciales incorrectas o cuenta bloqueada	40.237.42.60	Mozilla/5.0 (Windows NT 4.0) AppleWebKit/5331 (KHTML, like Gecko) Chrome/38.0.848.0 Mobile Safari/5331	{}	2026-02-25 10:09:25-06	2026-02-25 10:09:25-06	\N
138	\N	\N	daniela.hernández10@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	159.66.160.101	Mozilla/5.0 (iPhone; CPU iPhone OS 14_1 like Mac OS X) AppleWebKit/532.1 (KHTML, like Gecko) Version/15.0 EdgiOS/84.01142.36 Mobile/15E148 Safari/532.1	{}	2026-03-20 09:48:25-06	2026-03-20 09:48:25-06	\N
139	\N	\N	carlos.martínez99@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	112.89.71.69	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_7 rv:3.0; sl-SI) AppleWebKit/534.40.6 (KHTML, like Gecko) Version/5.0.5 Safari/534.40.6	{}	2026-03-09 00:03:25-06	2026-03-09 00:03:25-06	\N
140	4	119	admin@campus.edu.mx	login	t	Acceso registrado correctamente	153.30.103.29	Mozilla/5.0 (Windows 95; nl-NL; rv:1.9.1.20) Gecko/20151022 Firefox/37.0	{}	2026-03-21 16:26:25-06	2026-03-21 16:26:25-06	\N
141	23	71	david.flores22@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	50.6.183.32	Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20100423 Firefox/35.0	{}	2026-03-10 14:51:25-06	2026-03-10 14:51:25-06	\N
142	40	35	andrés.gonzález79@campus.edu.mx	login	t	Acceso registrado correctamente	152.155.180.99	Mozilla/5.0 (Windows NT 5.1; sl-SI; rv:1.9.0.20) Gecko/20181107 Firefox/37.0	{}	2026-03-18 10:28:25-06	2026-03-18 10:28:25-06	\N
143	28	107	gabriela.flores57@campus.edu.mx	password_reset	t	Acceso registrado correctamente	153.30.103.29	Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/537.1 (KHTML, like Gecko) Version/15.0 EdgiOS/83.01063.97 Mobile/15E148 Safari/537.1	{}	2026-03-20 01:16:25-06	2026-03-20 01:16:25-06	\N
144	20	27	jorge.flores83@campus.edu.mx	password_reset	t	Acceso registrado correctamente	152.155.180.99	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5340 (KHTML, like Gecko) Chrome/38.0.817.0 Mobile Safari/5340	{}	2026-03-21 01:45:25-06	2026-03-21 01:45:25-06	\N
145	23	114	david.flores22@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	74.89.81.140	Opera/9.54 (Windows NT 6.1; en-US) Presto/2.10.238 Version/12.00	{}	2026-02-28 06:22:25-06	2026-02-28 06:22:25-06	\N
146	7	74	sofia.ramírez21@campus.edu.mx	logout	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5321 (KHTML, like Gecko) Chrome/40.0.802.0 Mobile Safari/5321	{}	2026-03-22 15:20:25-06	2026-03-22 15:20:25-06	\N
147	31	45	isabella.hernández16@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	84.245.154.152	Mozilla/5.0 (Windows NT 5.01; en-US; rv:1.9.2.20) Gecko/20231204 Firefox/35.0	{}	2026-03-05 08:46:25-06	2026-03-05 08:46:25-06	\N
148	5	70	ricardo.flores45@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	40.237.42.60	Opera/9.75 (X11; Linux i686; sl-SI) Presto/2.8.214 Version/11.00	{}	2026-02-26 05:09:25-06	2026-02-26 05:09:25-06	\N
149	\N	\N	ricardo.ramírez86@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	163.181.136.213	Opera/9.47 (Windows NT 5.0; sl-SI) Presto/2.9.180 Version/12.00	{}	2026-03-02 20:17:25-06	2026-03-02 20:17:25-06	\N
150	\N	\N	daniela.garcía26@campus.edu.mx	password_reset	f	Credenciales incorrectas o cuenta bloqueada	159.66.160.101	Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X; sl-SI) AppleWebKit/535.10.7 (KHTML, like Gecko) Version/3.0.5 Mobile/8B118 Safari/6535.10.7	{}	2026-03-16 01:05:25-06	2026-03-16 01:05:25-06	\N
151	30	113	eduardo.gonzález63@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	12.58.140.246	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_7 rv:4.0) Gecko/20100622 Firefox/35.0	{}	2026-03-23 13:51:25-06	2026-03-23 13:51:25-06	\N
152	33	103	daniela.garcía26@campus.edu.mx	login	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_0 rv:4.0; en-US) AppleWebKit/535.23.6 (KHTML, like Gecko) Version/4.1 Safari/535.23.6	{}	2026-02-23 10:58:25-06	2026-02-23 10:58:25-06	\N
153	13	21	daniela.gonzález40@campus.edu.mx	password_reset	t	Acceso registrado correctamente	21.83.32.249	Mozilla/5.0 (iPhone; CPU iPhone OS 14_2 like Mac OS X) AppleWebKit/533.2 (KHTML, like Gecko) Version/15.0 EdgiOS/89.01034.88 Mobile/15E148 Safari/533.2	{}	2026-03-23 21:11:25-06	2026-03-23 21:11:25-06	\N
154	\N	\N	carlos.martínez99@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	74.89.81.140	Mozilla/5.0 (X11; Linux i686) AppleWebKit/535.0 (KHTML, like Gecko) Chrome/82.0.4064.64 Safari/535.0 EdgA/82.01114.3	{}	2026-03-18 17:30:25-06	2026-03-18 17:30:25-06	\N
155	30	41	eduardo.gonzález63@campus.edu.mx	login	t	Acceso registrado correctamente	187.71.223.183	Mozilla/5.0 (Windows 98; Win 9x 4.90) AppleWebKit/5360 (KHTML, like Gecko) Chrome/37.0.895.0 Mobile Safari/5360	{}	2026-03-12 01:32:25-06	2026-03-12 01:32:25-06	\N
156	9	74	antonio.flores35@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	55.97.116.198	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/5.0)	{}	2026-03-18 17:34:25-06	2026-03-18 17:34:25-06	\N
157	\N	\N	antonio.flores35@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	55.97.116.198	Mozilla/5.0 (compatible; MSIE 11.0; Windows CE; Trident/4.0)	{}	2026-03-22 22:24:25-06	2026-03-22 22:24:25-06	\N
158	41	130	jorge.garcía47@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	40.237.42.60	Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/532.27.5 (KHTML, like Gecko) Version/4.0 Safari/532.27.5	{}	2026-03-03 08:07:25-06	2026-03-03 08:07:25-06	\N
159	32	104	andrés.pérez12@campus.edu.mx	logout	t	Acceso registrado correctamente	128.41.68.12	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_8_2 rv:5.0) Gecko/20110828 Firefox/37.0	{}	2026-02-27 13:13:25-06	2026-02-27 13:13:25-06	\N
160	33	24	daniela.garcía26@campus.edu.mx	login	t	Acceso registrado correctamente	187.71.223.183	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_1) AppleWebKit/534.0 (KHTML, like Gecko) Chrome/90.0.4466.20 Safari/534.0 Edg/90.01046.79	{}	2026-03-19 21:08:25-06	2026-03-19 21:08:25-06	\N
161	34	24	carlos.gonzález25@campus.edu.mx	logout	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/536.0 (KHTML, like Gecko) Chrome/95.0.4523.90 Safari/536.0 EdgA/95.01071.15	{}	2026-02-26 09:40:25-06	2026-02-26 09:40:25-06	\N
162	\N	\N	lucia.ramírez65@campus.edu.mx	password_reset	f	Credenciales incorrectas o cuenta bloqueada	21.83.32.249	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/536.0 (KHTML, like Gecko) Chrome/87.0.4142.64 Safari/536.0 EdgA/87.01042.92	{}	2026-03-18 23:28:25-06	2026-03-18 23:28:25-06	\N
163	23	23	david.flores22@campus.edu.mx	logout	t	Acceso registrado correctamente	50.6.183.32	Mozilla/5.0 (Windows; U; Windows 98; Win 9x 4.90) AppleWebKit/532.35.4 (KHTML, like Gecko) Version/5.0.4 Safari/532.35.4	{}	2026-03-22 01:30:25-06	2026-03-22 01:30:25-06	\N
164	\N	\N	valentina.torres90@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	163.181.136.213	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 6.0; Trident/5.1)	{}	2026-03-21 21:18:25-06	2026-03-21 21:18:25-06	\N
165	26	74	ricardo.ramírez86@campus.edu.mx	password_reset	t	Acceso registrado correctamente	96.177.107.80	Mozilla/5.0 (Windows NT 5.01; nl-NL; rv:1.9.2.20) Gecko/20111210 Firefox/37.0	{}	2026-03-20 23:40:25-06	2026-03-20 23:40:25-06	\N
166	6	25	daniela.gonzález26@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_1 rv:5.0; en-US) AppleWebKit/531.9.2 (KHTML, like Gecko) Version/5.0.3 Safari/531.9.2	{}	2026-03-21 15:41:25-06	2026-03-21 15:41:25-06	\N
167	26	101	ricardo.ramírez86@campus.edu.mx	password_reset	t	Acceso registrado correctamente	12.58.140.246	Mozilla/5.0 (iPhone; CPU iPhone OS 15_2 like Mac OS X) AppleWebKit/533.0 (KHTML, like Gecko) Version/15.0 EdgiOS/86.01136.94 Mobile/15E148 Safari/533.0	{}	2026-03-20 06:35:25-06	2026-03-20 06:35:25-06	\N
168	11	77	david.garcía63@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	193.220.22.133	Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/531.11.1 (KHTML, like Gecko) Version/5.0 Safari/531.11.1	{}	2026-03-20 15:45:25-06	2026-03-20 15:45:25-06	\N
169	34	39	carlos.gonzález25@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	96.177.107.80	Mozilla/5.0 (iPhone; CPU iPhone OS 15_1 like Mac OS X) AppleWebKit/531.0 (KHTML, like Gecko) Version/15.0 EdgiOS/95.01043.51 Mobile/15E148 Safari/531.0	{}	2026-03-16 15:11:25-06	2026-03-16 15:11:25-06	\N
170	28	1	gabriela.flores57@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	152.155.180.99	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.1; Trident/4.1)	{}	2026-03-21 13:09:25-06	2026-03-21 13:09:25-06	\N
171	\N	\N	antonio.flores35@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	96.177.107.80	Opera/9.69 (Windows NT 5.1; sl-SI) Presto/2.11.173 Version/11.00	{}	2026-02-22 00:41:25-06	2026-02-22 00:41:25-06	\N
172	4	37	admin@campus.edu.mx	login	t	Acceso registrado correctamente	55.97.116.198	Opera/8.37 (X11; Linux x86_64; nl-NL) Presto/2.11.270 Version/10.00	{}	2026-03-19 22:34:25-06	2026-03-19 22:34:25-06	\N
173	22	84	ana.ramírez81@campus.edu.mx	password_reset	t	Acceso registrado correctamente	50.6.183.32	Mozilla/5.0 (Windows; U; Windows NT 6.0) AppleWebKit/534.23.4 (KHTML, like Gecko) Version/5.0 Safari/534.23.4	{}	2026-03-18 09:48:25-06	2026-03-18 09:48:25-06	\N
174	\N	\N	jorge.torres64@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	96.177.107.80	Mozilla/5.0 (X11; Linux x86_64; rv:7.0) Gecko/20130616 Firefox/35.0	{}	2026-03-11 10:28:25-06	2026-03-11 10:28:25-06	\N
175	\N	\N	admin@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	55.97.116.198	Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.1.20) Gecko/20111001 Firefox/37.0	{}	2026-02-26 00:38:25-06	2026-02-26 00:38:25-06	\N
176	13	92	daniela.gonzález40@campus.edu.mx	logout	t	Acceso registrado correctamente	84.245.154.152	Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.01; Trident/4.1)	{}	2026-03-22 12:10:25-06	2026-03-22 12:10:25-06	\N
177	25	42	jorge.torres64@campus.edu.mx	login	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_1) AppleWebKit/5322 (KHTML, like Gecko) Chrome/38.0.866.0 Mobile Safari/5322	{}	2026-02-28 05:49:25-06	2026-02-28 05:49:25-06	\N
178	13	81	daniela.gonzález40@campus.edu.mx	logout	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (iPhone; CPU iPhone OS 13_1 like Mac OS X) AppleWebKit/535.2 (KHTML, like Gecko) Version/15.0 EdgiOS/81.01031.8 Mobile/15E148 Safari/535.2	{}	2026-02-22 22:10:25-06	2026-02-22 22:10:25-06	\N
179	11	2	david.garcía63@campus.edu.mx	logout	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (compatible; MSIE 7.0; Windows 95; Trident/3.0)	{}	2026-03-06 21:41:25-06	2026-03-06 21:41:25-06	\N
180	\N	\N	daniela.garcía26@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	152.155.180.99	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5332 (KHTML, like Gecko) Chrome/40.0.856.0 Mobile Safari/5332	{}	2026-03-16 03:51:25-06	2026-03-16 03:51:25-06	\N
181	12	84	andrés.flores53@campus.edu.mx	password_reset	t	Acceso registrado correctamente	21.83.32.249	Opera/9.54 (Windows NT 6.1; nl-NL) Presto/2.12.207 Version/10.00	{}	2026-03-22 12:54:25-06	2026-03-22 12:54:25-06	\N
182	32	111	andrés.pérez12@campus.edu.mx	password_reset	t	Acceso registrado correctamente	84.245.154.152	Mozilla/5.0 (iPad; CPU OS 8_1_1 like Mac OS X; en-US) AppleWebKit/535.42.3 (KHTML, like Gecko) Version/3.0.5 Mobile/8B117 Safari/6535.42.3	{}	2026-03-19 19:28:25-06	2026-03-19 19:28:25-06	\N
183	\N	\N	ricardo.ramírez86@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	55.97.116.198	Opera/8.43 (Windows NT 6.2; nl-NL) Presto/2.11.266 Version/12.00	{}	2026-03-21 17:23:25-06	2026-03-21 17:23:25-06	\N
184	18	72	eduardo.hernández14@campus.edu.mx	password_reset	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (iPhone; CPU iPhone OS 8_0_1 like Mac OS X; nl-NL) AppleWebKit/531.39.1 (KHTML, like Gecko) Version/4.0.5 Mobile/8B111 Safari/6531.39.1	{}	2026-02-23 23:51:25-06	2026-02-23 23:51:25-06	\N
185	22	124	ana.ramírez81@campus.edu.mx	login	t	Acceso registrado correctamente	187.71.223.183	Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.0 (KHTML, like Gecko) Chrome/80.0.4361.54 Safari/537.0 EdgA/80.01122.2	{}	2026-03-08 11:00:25-06	2026-03-08 11:00:25-06	\N
186	24	60	andrés.martínez25@campus.edu.mx	login	t	Acceso registrado correctamente	12.58.140.246	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_7 rv:5.0; sl-SI) AppleWebKit/532.7.6 (KHTML, like Gecko) Version/5.0 Safari/532.7.6	{}	2026-03-16 02:10:25-06	2026-03-16 02:10:25-06	\N
187	24	115	andrés.martínez25@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	211.229.77.194	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5310 (KHTML, like Gecko) Chrome/39.0.860.0 Mobile Safari/5310	{}	2026-03-17 23:13:25-06	2026-03-17 23:13:25-06	\N
188	37	38	valentina.torres90@campus.edu.mx	password_reset	t	Acceso registrado correctamente	96.177.107.80	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5361 (KHTML, like Gecko) Chrome/39.0.890.0 Mobile Safari/5361	{}	2026-03-23 08:57:25-06	2026-03-23 08:57:25-06	\N
189	\N	\N	jorge.flores83@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	152.155.180.99	Mozilla/5.0 (iPad; CPU OS 7_2_1 like Mac OS X; nl-NL) AppleWebKit/532.27.6 (KHTML, like Gecko) Version/4.0.5 Mobile/8B112 Safari/6532.27.6	{}	2026-03-04 10:43:25-06	2026-03-04 10:43:25-06	\N
190	\N	\N	andrés.martínez25@campus.edu.mx	login	f	Credenciales incorrectas o cuenta bloqueada	12.58.140.246	Mozilla/5.0 (Windows 95) AppleWebKit/5350 (KHTML, like Gecko) Chrome/38.0.876.0 Mobile Safari/5350	{}	2026-03-20 21:27:25-06	2026-03-20 21:27:25-06	\N
191	39	14	lucia.ramírez65@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	112.89.71.69	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.01; Trident/4.0)	{}	2026-02-23 14:24:25-06	2026-02-23 14:24:25-06	\N
192	33	84	daniela.garcía26@campus.edu.mx	password_reset	t	Acceso registrado correctamente	187.71.223.183	Mozilla/5.0 (Windows; U; Windows NT 6.0) AppleWebKit/533.2.7 (KHTML, like Gecko) Version/5.0 Safari/533.2.7	{}	2026-03-11 14:21:25-06	2026-03-11 14:21:25-06	\N
193	\N	\N	jorge.flores83@campus.edu.mx	login	f	Credenciales incorrectas o cuenta bloqueada	163.181.136.213	Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5362 (KHTML, like Gecko) Chrome/36.0.849.0 Mobile Safari/5362	{}	2026-03-20 01:12:26-06	2026-03-20 01:12:26-06	\N
194	\N	\N	antonio.flores35@campus.edu.mx	token_refresh	f	Credenciales incorrectas o cuenta bloqueada	163.181.136.213	Opera/9.74 (X11; Linux i686; sl-SI) Presto/2.9.181 Version/10.00	{}	2026-03-22 10:34:26-06	2026-03-22 10:34:26-06	\N
195	\N	\N	andrés.gonzález79@campus.edu.mx	password_reset	f	Credenciales incorrectas o cuenta bloqueada	187.71.223.183	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/5.1)	{}	2026-03-13 02:36:26-06	2026-03-13 02:36:26-06	\N
196	17	7	carlos.garcía96@campus.edu.mx	login	t	Acceso registrado correctamente	159.66.160.101	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_5_7) AppleWebKit/536.1 (KHTML, like Gecko) Chrome/81.0.4812.33 Safari/536.1 Edg/81.01071.33	{}	2026-03-17 17:49:26-06	2026-03-17 17:49:26-06	\N
197	\N	\N	sofia.ramírez21@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	163.181.136.213	Mozilla/5.0 (compatible; MSIE 10.0; Windows 95; Trident/3.1)	{}	2026-03-21 05:45:26-06	2026-03-21 05:45:26-06	\N
198	36	54	gabriela.torres37@campus.edu.mx	login	t	Acceso registrado correctamente	96.177.107.80	Mozilla/5.0 (iPhone; CPU iPhone OS 13_0 like Mac OS X) AppleWebKit/535.1 (KHTML, like Gecko) Version/15.0 EdgiOS/87.01121.82 Mobile/15E148 Safari/535.1	{}	2026-03-20 20:35:26-06	2026-03-20 20:35:26-06	\N
199	13	120	daniela.gonzález40@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	187.71.223.183	Mozilla/5.0 (X11; Linux i686; rv:7.0) Gecko/20200903 Firefox/36.0	{}	2026-03-20 00:59:26-06	2026-03-20 00:59:26-06	\N
200	39	73	lucia.ramírez65@campus.edu.mx	logout	t	Acceso registrado correctamente	55.97.116.198	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.1; Trident/4.0)	{}	2026-03-22 08:54:26-06	2026-03-22 08:54:26-06	\N
201	27	96	ricardo.ramírez90@campus.edu.mx	logout	t	Acceso registrado correctamente	163.181.136.213	Mozilla/5.0 (Windows 98) AppleWebKit/5362 (KHTML, like Gecko) Chrome/40.0.868.0 Mobile Safari/5362	{}	2026-03-16 15:08:26-06	2026-03-16 15:08:26-06	\N
202	20	64	jorge.flores83@campus.edu.mx	logout	t	Acceso registrado correctamente	40.237.42.60	Mozilla/5.0 (Windows NT 6.0) AppleWebKit/536.2 (KHTML, like Gecko) Chrome/99.0.4032.29 Safari/536.2 Edg/99.01093.42	{}	2026-03-21 22:52:26-06	2026-03-21 22:52:26-06	\N
203	\N	\N	eduardo.ramírez63@campus.edu.mx	login	f	Credenciales incorrectas o cuenta bloqueada	55.97.116.198	Opera/8.81 (X11; Linux x86_64; sl-SI) Presto/2.11.256 Version/12.00	{}	2026-02-23 21:04:26-06	2026-02-23 21:04:26-06	\N
204	12	12	andrés.flores53@campus.edu.mx	logout	t	Acceso registrado correctamente	8.53.35.195	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_7) AppleWebKit/534.0 (KHTML, like Gecko) Chrome/98.0.4555.64 Safari/534.0 Edg/98.01044.30	{}	2026-03-16 08:05:26-06	2026-03-16 08:05:26-06	\N
205	\N	\N	isabella.hernández16@campus.edu.mx	password_reset	f	Credenciales incorrectas o cuenta bloqueada	107.92.187.137	Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5352 (KHTML, like Gecko) Chrome/38.0.898.0 Mobile Safari/5352	{}	2026-03-04 02:10:26-06	2026-03-04 02:10:26-06	\N
206	32	16	andrés.pérez12@campus.edu.mx	login	t	Acceso registrado correctamente	193.220.22.133	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_9 rv:5.0; sl-SI) AppleWebKit/533.20.6 (KHTML, like Gecko) Version/4.0 Safari/533.20.6	{}	2026-03-15 04:56:26-06	2026-03-15 04:56:26-06	\N
207	21	73	laura.garcía96@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (Windows NT 5.0; sl-SI; rv:1.9.1.20) Gecko/20180205 Firefox/37.0	{}	2026-03-21 02:01:26-06	2026-03-21 02:01:26-06	\N
208	12	71	andrés.flores53@campus.edu.mx	password_reset	t	Acceso registrado correctamente	74.89.81.140	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5362 (KHTML, like Gecko) Chrome/38.0.871.0 Mobile Safari/5362	{}	2026-02-25 20:02:26-06	2026-02-25 20:02:26-06	\N
209	42	107	eduardo.lópez43@campus.edu.mx	login	t	Acceso registrado correctamente	21.83.32.249	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/3.1)	{}	2026-03-12 10:31:26-06	2026-03-12 10:31:26-06	\N
210	\N	\N	carlos.gonzález25@campus.edu.mx	password_reset	f	Credenciales incorrectas o cuenta bloqueada	128.41.68.12	Mozilla/5.0 (Windows; U; Windows NT 5.1) AppleWebKit/535.20.2 (KHTML, like Gecko) Version/5.1 Safari/535.20.2	{}	2026-03-10 20:26:26-06	2026-03-10 20:26:26-06	\N
211	\N	\N	lucia.ramírez65@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	152.155.180.99	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4) AppleWebKit/5322 (KHTML, like Gecko) Chrome/37.0.833.0 Mobile Safari/5322	{}	2026-03-18 10:24:26-06	2026-03-18 10:24:26-06	\N
212	25	6	jorge.torres64@campus.edu.mx	password_reset	t	Acceso registrado correctamente	40.237.42.60	Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5322 (KHTML, like Gecko) Chrome/36.0.864.0 Mobile Safari/5322	{}	2026-03-08 09:11:26-06	2026-03-08 09:11:26-06	\N
213	\N	\N	gabriela.pérez34@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	55.97.116.198	Mozilla/5.0 (Windows; U; Windows NT 6.1) AppleWebKit/532.17.1 (KHTML, like Gecko) Version/4.0.4 Safari/532.17.1	{}	2026-03-19 06:10:26-06	2026-03-19 06:10:26-06	\N
214	\N	\N	valentina.torres90@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	21.83.32.249	Mozilla/5.0 (X11; Linux x86_64; rv:6.0) Gecko/20151130 Firefox/35.0	{}	2026-03-18 05:55:26-06	2026-03-18 05:55:26-06	\N
215	\N	\N	eduardo.lópez43@campus.edu.mx	login_failed	f	Credenciales incorrectas o cuenta bloqueada	40.237.42.60	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/533.1 (KHTML, like Gecko) Chrome/99.0.4634.82 Safari/533.1 EdgA/99.01095.24	{}	2026-03-22 22:30:26-06	2026-03-22 22:30:26-06	\N
216	33	64	daniela.garcía26@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	163.181.136.213	Mozilla/5.0 (Windows; U; Windows 98; Win 9x 4.90) AppleWebKit/533.30.2 (KHTML, like Gecko) Version/4.1 Safari/533.30.2	{}	2026-03-22 18:35:26-06	2026-03-22 18:35:26-06	\N
217	31	87	isabella.hernández16@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	187.71.223.183	Mozilla/5.0 (Windows NT 5.1; sl-SI; rv:1.9.2.20) Gecko/20241231 Firefox/35.0	{}	2026-03-10 08:12:26-06	2026-03-10 08:12:26-06	\N
218	32	4	andrés.pérez12@campus.edu.mx	login	t	Acceso registrado correctamente	96.177.107.80	Mozilla/5.0 (iPad; CPU OS 8_0_2 like Mac OS X; en-US) AppleWebKit/534.8.2 (KHTML, like Gecko) Version/4.0.5 Mobile/8B118 Safari/6534.8.2	{}	2026-03-13 01:40:26-06	2026-03-13 01:40:26-06	\N
219	7	92	sofia.ramírez21@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	8.53.35.195	Mozilla/5.0 (Windows CE; sl-SI; rv:1.9.0.20) Gecko/20210322 Firefox/36.0	{}	2026-03-20 18:10:26-06	2026-03-20 18:10:26-06	\N
220	21	24	laura.garcía96@campus.edu.mx	token_refresh	t	Acceso registrado correctamente	152.155.180.99	Mozilla/5.0 (iPhone; CPU iPhone OS 15_1 like Mac OS X) AppleWebKit/534.2 (KHTML, like Gecko) Version/15.0 EdgiOS/86.01095.68 Mobile/15E148 Safari/534.2	{}	2026-03-18 00:06:26-06	2026-03-18 00:06:26-06	\N
221	1	136	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-24 23:03:23-06	2026-03-24 23:03:23-06	\N
222	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-24 23:03:23-06	2026-03-24 23:03:23-06	\N
223	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-24 23:05:43-06	2026-03-24 23:05:43-06	\N
224	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-24 23:05:43-06	2026-03-24 23:05:43-06	\N
225	2	137	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-24 23:05:56-06	2026-03-24 23:05:56-06	\N
226	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-24 23:05:56-06	2026-03-24 23:05:56-06	\N
227	2	138	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-25 23:20:54-06	2026-03-25 23:20:54-06	\N
228	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-25 23:20:54-06	2026-03-25 23:20:54-06	\N
229	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-25 23:56:24-06	2026-03-25 23:56:24-06	\N
230	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-25 23:56:24-06	2026-03-25 23:56:24-06	\N
231	2	139	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-25 23:56:38-06	2026-03-25 23:56:38-06	\N
232	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-25 23:56:38-06	2026-03-25 23:56:38-06	\N
233	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-26 00:30:10-06	2026-03-26 00:30:10-06	\N
234	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-26 00:30:10-06	2026-03-26 00:30:10-06	\N
235	1	140	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-26 00:30:26-06	2026-03-26 00:30:26-06	\N
236	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-26 00:30:26-06	2026-03-26 00:30:26-06	\N
237	1	141	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 19:53:25-06	2026-03-27 19:53:25-06	\N
238	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 19:53:25-06	2026-03-27 19:53:25-06	\N
239	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 20:27:14-06	2026-03-27 20:27:14-06	\N
240	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 20:27:14-06	2026-03-27 20:27:14-06	\N
241	43	142	manager1@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 20:27:54-06	2026-03-27 20:27:54-06	\N
242	43	\N	manager1@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 20:27:54-06	2026-03-27 20:27:54-06	\N
243	43	\N	manager1@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 20:28:48-06	2026-03-27 20:28:48-06	\N
244	43	\N	manager1@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 20:28:48-06	2026-03-27 20:28:48-06	\N
245	1	143	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 20:28:53-06	2026-03-27 20:28:53-06	\N
246	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 20:28:53-06	2026-03-27 20:28:53-06	\N
247	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:04:12-06	2026-03-27 21:04:12-06	\N
248	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:04:12-06	2026-03-27 21:04:12-06	\N
249	43	144	manager1@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:04:24-06	2026-03-27 21:04:24-06	\N
250	43	\N	manager1@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:04:24-06	2026-03-27 21:04:24-06	\N
251	43	\N	manager1@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:04:49-06	2026-03-27 21:04:49-06	\N
252	43	\N	manager1@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:04:49-06	2026-03-27 21:04:49-06	\N
253	2	145	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:04:57-06	2026-03-27 21:04:57-06	\N
254	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:04:57-06	2026-03-27 21:04:57-06	\N
255	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:12:27-06	2026-03-27 21:12:27-06	\N
256	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:12:27-06	2026-03-27 21:12:27-06	\N
257	2	146	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:12:47-06	2026-03-27 21:12:47-06	\N
258	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 21:12:47-06	2026-03-27 21:12:47-06	\N
259	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:11:16-06	2026-03-27 22:11:16-06	\N
260	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:11:16-06	2026-03-27 22:11:16-06	\N
261	1	147	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:11:24-06	2026-03-27 22:11:24-06	\N
262	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:11:24-06	2026-03-27 22:11:24-06	\N
263	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:12:29-06	2026-03-27 22:12:29-06	\N
264	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:12:29-06	2026-03-27 22:12:29-06	\N
265	43	148	manager1@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:12:44-06	2026-03-27 22:12:44-06	\N
266	43	\N	manager1@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:12:44-06	2026-03-27 22:12:44-06	\N
267	43	\N	manager1@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:12:55-06	2026-03-27 22:12:55-06	\N
268	43	\N	manager1@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:12:55-06	2026-03-27 22:12:55-06	\N
269	2	149	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:13:08-06	2026-03-27 22:13:08-06	\N
270	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:13:08-06	2026-03-27 22:13:08-06	\N
271	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:19:20-06	2026-03-27 22:19:20-06	\N
272	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:19:20-06	2026-03-27 22:19:20-06	\N
273	1	150	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:19:30-06	2026-03-27 22:19:30-06	\N
274	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 22:19:30-06	2026-03-27 22:19:30-06	\N
275	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:24:37-06	2026-03-27 23:24:37-06	\N
276	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:24:37-06	2026-03-27 23:24:37-06	\N
277	2	151	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:24:53-06	2026-03-27 23:24:53-06	\N
278	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:24:53-06	2026-03-27 23:24:53-06	\N
279	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:25:13-06	2026-03-27 23:25:13-06	\N
280	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:25:13-06	2026-03-27 23:25:13-06	\N
281	43	152	manager1@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:25:23-06	2026-03-27 23:25:23-06	\N
282	43	\N	manager1@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:25:23-06	2026-03-27 23:25:23-06	\N
283	43	\N	manager1@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:25:44-06	2026-03-27 23:25:44-06	\N
284	43	\N	manager1@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:25:44-06	2026-03-27 23:25:44-06	\N
285	1	153	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:25:57-06	2026-03-27 23:25:57-06	\N
286	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:25:57-06	2026-03-27 23:25:57-06	\N
287	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:35:12-06	2026-03-27 23:35:12-06	\N
288	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:35:12-06	2026-03-27 23:35:12-06	\N
289	43	154	manager1@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:35:22-06	2026-03-27 23:35:22-06	\N
290	43	\N	manager1@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:35:22-06	2026-03-27 23:35:22-06	\N
291	43	\N	manager1@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:35:46-06	2026-03-27 23:35:46-06	\N
292	43	\N	manager1@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:35:46-06	2026-03-27 23:35:46-06	\N
293	2	155	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:35:56-06	2026-03-27 23:35:56-06	\N
294	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:35:56-06	2026-03-27 23:35:56-06	\N
295	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:36:08-06	2026-03-27 23:36:08-06	\N
296	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:36:08-06	2026-03-27 23:36:08-06	\N
297	1	156	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:36:19-06	2026-03-27 23:36:19-06	\N
298	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:36:19-06	2026-03-27 23:36:19-06	\N
299	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:46:52-06	2026-03-27 23:46:52-06	\N
300	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:46:52-06	2026-03-27 23:46:52-06	\N
301	44	157	manager2@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:47:02-06	2026-03-27 23:47:02-06	\N
302	44	\N	manager2@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:47:02-06	2026-03-27 23:47:02-06	\N
303	44	\N	manager2@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:47:33-06	2026-03-27 23:47:33-06	\N
304	44	\N	manager2@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:47:33-06	2026-03-27 23:47:33-06	\N
305	1	158	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:47:46-06	2026-03-27 23:47:46-06	\N
306	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	{}	2026-03-27 23:47:46-06	2026-03-27 23:47:46-06	\N
\.


--
-- Data for Name: actividad_bitacora; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.actividad_bitacora (id, usuario_id, sesion_id, accion, modulo, target_tabla, target_id, exito, detalle, ip, user_agent, meta_json, created_at, updated_at, deleted_at) FROM stdin;
1	12	98	crear	bitacora	rol	7	t	Crear en módulo bitacora	58.126.101.3	Opera/9.58 (Windows 98; Win 9x 4.90; sl-SI) Presto/2.11.209 Version/11.00	{}	2026-03-20 15:25:26-06	2026-03-20 15:25:26-06	\N
2	25	119	crear	perfil	rol	22	t	Crear en módulo perfil	119.36.92.155	Opera/8.22 (Windows NT 5.1; en-US) Presto/2.10.252 Version/11.00	{}	2026-02-28 12:20:26-06	2026-02-28 12:20:26-06	\N
3	31	112	bloquear	bitacora	rol	14	t	Bloquear en módulo bitacora	158.114.140.64	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0)	{}	2026-03-08 13:01:26-06	2026-03-08 13:01:26-06	\N
4	30	114	desbloquear	reportes	pedido	24	t	Desbloquear en módulo reportes	43.43.141.21	Mozilla/5.0 (Windows NT 5.0) AppleWebKit/532.1 (KHTML, like Gecko) Chrome/87.0.4472.88 Safari/532.1 Edg/87.01038.28	{}	2026-03-19 15:16:26-06	2026-03-19 15:16:26-06	\N
5	35	23	desbloquear	usuarios	permiso	37	f	Desbloquear en módulo usuarios	66.178.199.208	Mozilla/5.0 (X11; Linux x86_64; rv:7.0) Gecko/20110125 Firefox/37.0	{}	2026-03-20 17:10:26-06	2026-03-20 17:10:26-06	\N
6	4	13	exportar	permisos	tarjeta_universitaria	47	t	Exportar en módulo permisos	178.235.255.101	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5352 (KHTML, like Gecko) Chrome/39.0.817.0 Mobile Safari/5352	{}	2026-03-22 15:48:26-06	2026-03-22 15:48:26-06	\N
7	30	123	bloquear	bitacora	rol	20	f	Bloquear en módulo bitacora	123.218.182.151	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.01; Trident/4.1)	{}	2026-02-26 22:36:26-06	2026-02-26 22:36:26-06	\N
8	4	134	editar	bitacora	rol	15	t	Editar en módulo bitacora	41.184.164.122	Opera/8.13 (X11; Linux i686; sl-SI) Presto/2.10.258 Version/10.00	{}	2026-03-21 15:17:26-06	2026-03-21 15:17:26-06	\N
9	8	56	exportar	usuarios	tarjeta_universitaria	31	t	Exportar en módulo usuarios	78.61.225.205	Mozilla/5.0 (X11; Linux x86_64; rv:6.0) Gecko/20150210 Firefox/35.0	{}	2026-03-14 14:07:26-06	2026-03-14 14:07:26-06	\N
10	26	122	crear	permisos	permiso	13	t	Crear en módulo permisos	131.116.136.120	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_2 rv:3.0; nl-NL) AppleWebKit/535.46.1 (KHTML, like Gecko) Version/5.0.4 Safari/535.46.1	{}	2026-03-21 07:26:26-06	2026-03-21 07:26:26-06	\N
11	22	86	desbloquear	perfil	tarjeta_universitaria	29	t	Desbloquear en módulo perfil	144.138.102.7	Mozilla/5.0 (Windows NT 5.1) AppleWebKit/533.0 (KHTML, like Gecko) Chrome/87.0.4365.56 Safari/533.0 Edg/87.01089.51	{}	2026-03-18 12:48:26-06	2026-03-18 12:48:26-06	\N
12	40	18	ver	permisos	pedido	38	t	Ver en módulo permisos	27.33.204.179	Opera/9.41 (Windows CE; en-US) Presto/2.8.280 Version/12.00	{}	2026-02-23 10:25:26-06	2026-02-23 10:25:26-06	\N
13	11	94	exportar	bitacora	tarjeta_universitaria	32	t	Exportar en módulo bitacora	131.70.163.119	Mozilla/5.0 (Windows 95) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/98.0.4541.18 Safari/535.1 Edg/98.01019.15	{}	2026-03-09 07:54:26-06	2026-03-09 07:54:26-06	\N
14	18	107	eliminar	roles	tarjeta_universitaria	5	t	Eliminar en módulo roles	197.248.11.113	Opera/9.61 (Windows NT 5.0; nl-NL) Presto/2.11.330 Version/12.00	{}	2026-03-21 20:45:26-06	2026-03-21 20:45:26-06	\N
15	19	3	desbloquear	perfil	permiso	20	t	Desbloquear en módulo perfil	190.156.184.156	Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X; en-US) AppleWebKit/531.41.3 (KHTML, like Gecko) Version/3.0.5 Mobile/8B117 Safari/6531.41.3	{}	2026-03-13 22:08:26-06	2026-03-13 22:08:26-06	\N
16	9	10	ver	reportes	tarjeta_universitaria	37	t	Ver en módulo reportes	187.236.64.112	Opera/9.26 (X11; Linux x86_64; sl-SI) Presto/2.9.217 Version/12.00	{}	2026-03-20 08:54:26-06	2026-03-20 08:54:26-06	\N
17	6	13	bloquear	roles	usuario	40	t	Bloquear en módulo roles	44.94.142.221	Opera/9.98 (Windows NT 5.1; sl-SI) Presto/2.11.253 Version/11.00	{}	2026-03-23 15:34:26-06	2026-03-23 15:34:26-06	\N
18	13	118	exportar	reportes	tarjeta_universitaria	4	t	Exportar en módulo reportes	77.131.165.117	Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_1 like Mac OS X; sl-SI) AppleWebKit/534.31.7 (KHTML, like Gecko) Version/4.0.5 Mobile/8B115 Safari/6534.31.7	{}	2026-03-10 17:54:26-06	2026-03-10 17:54:26-06	\N
19	19	89	exportar	permisos	rol	41	t	Exportar en módulo permisos	174.175.131.182	Mozilla/5.0 (X11; Linux x86_64; rv:7.0) Gecko/20220417 Firefox/37.0	{}	2026-03-10 14:03:26-06	2026-03-10 14:03:26-06	\N
20	41	56	crear	reportes	permiso	46	t	Crear en módulo reportes	61.30.222.5	Mozilla/5.0 (Windows; U; Windows NT 5.01) AppleWebKit/531.42.3 (KHTML, like Gecko) Version/4.1 Safari/531.42.3	{}	2026-03-02 19:52:26-06	2026-03-02 19:52:26-06	\N
21	16	51	crear	tarjetas	rol	14	t	Crear en módulo tarjetas	240.149.179.211	Opera/8.32 (X11; Linux i686; sl-SI) Presto/2.11.207 Version/10.00	{}	2026-03-18 00:52:26-06	2026-03-18 00:52:26-06	\N
22	25	128	desbloquear	tarjetas	permiso	24	t	Desbloquear en módulo tarjetas	4.224.94.96	Mozilla/5.0 (Windows 98) AppleWebKit/532.2 (KHTML, like Gecko) Chrome/83.0.4274.14 Safari/532.2 Edg/83.01083.38	{}	2026-03-17 07:39:26-06	2026-03-17 07:39:26-06	\N
23	4	30	editar	bitacora	pedido	48	t	Editar en módulo bitacora	3.30.186.157	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_5_5) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/99.0.4394.17 Safari/537.1 Edg/99.01146.69	{}	2026-03-13 03:43:26-06	2026-03-13 03:43:26-06	\N
24	23	68	ver	reportes	pedido	10	t	Ver en módulo reportes	139.117.173.13	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_8_0 rv:5.0) Gecko/20160913 Firefox/37.0	{}	2026-03-17 14:46:26-06	2026-03-17 14:46:26-06	\N
25	5	135	ver	perfil	tarjeta_universitaria	42	t	Ver en módulo perfil	113.114.234.175	Mozilla/5.0 (Windows 98; Win 9x 4.90; sl-SI; rv:1.9.2.20) Gecko/20180205 Firefox/36.0	{}	2026-03-21 15:55:26-06	2026-03-21 15:55:26-06	\N
26	26	87	ver	roles	rol	10	t	Ver en módulo roles	3.76.109.231	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.2; Trident/4.1)	{}	2026-03-09 01:10:26-06	2026-03-09 01:10:26-06	\N
27	18	22	eliminar	bitacora	pedido	41	t	Eliminar en módulo bitacora	99.231.254.45	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_3) AppleWebKit/534.2 (KHTML, like Gecko) Chrome/94.0.4443.78 Safari/534.2 Edg/94.01003.17	{}	2026-03-18 05:36:26-06	2026-03-18 05:36:26-06	\N
28	6	56	eliminar	seguridad	tarjeta_universitaria	12	f	Eliminar en módulo seguridad	121.216.137.143	Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/537.2 (KHTML, like Gecko) Version/15.0 EdgiOS/81.01004.9 Mobile/15E148 Safari/537.2	{}	2026-03-23 07:23:26-06	2026-03-23 07:23:26-06	\N
29	17	18	desbloquear	reportes	tarjeta_universitaria	49	t	Desbloquear en módulo reportes	185.5.148.196	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5361 (KHTML, like Gecko) Chrome/37.0.886.0 Mobile Safari/5361	{}	2026-03-17 08:53:26-06	2026-03-17 08:53:26-06	\N
30	38	89	desbloquear	usuarios	usuario	36	t	Desbloquear en módulo usuarios	14.176.170.30	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/4.0)	{}	2026-03-15 21:02:26-06	2026-03-15 21:02:26-06	\N
31	35	18	bloquear	usuarios	permiso	6	t	Bloquear en módulo usuarios	157.5.121.56	Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5340 (KHTML, like Gecko) Chrome/36.0.822.0 Mobile Safari/5340	{}	2026-03-18 16:40:26-06	2026-03-18 16:40:26-06	\N
32	41	75	eliminar	perfil	usuario	8	t	Eliminar en módulo perfil	206.234.135.81	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5352 (KHTML, like Gecko) Chrome/36.0.863.0 Mobile Safari/5352	{}	2026-03-05 19:37:26-06	2026-03-05 19:37:26-06	\N
33	5	73	ver	seguridad	pedido	32	f	Ver en módulo seguridad	187.253.5.193	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/99.0.4683.92 Safari/537.1 EdgA/99.01081.88	{}	2026-02-24 11:24:26-06	2026-02-24 11:24:26-06	\N
34	24	131	crear	seguridad	rol	24	t	Crear en módulo seguridad	116.208.168.58	Mozilla/5.0 (Windows; U; Windows NT 6.0) AppleWebKit/533.21.3 (KHTML, like Gecko) Version/4.0 Safari/533.21.3	{}	2026-03-23 04:29:26-06	2026-03-23 04:29:26-06	\N
35	29	58	bloquear	usuarios	usuario	18	f	Bloquear en módulo usuarios	15.241.6.219	Mozilla/5.0 (compatible; MSIE 11.0; Windows 98; Win 9x 4.90; Trident/5.0)	{}	2026-03-17 17:37:26-06	2026-03-17 17:37:26-06	\N
36	6	1	ver	reportes	tarjeta_universitaria	1	t	Ver en módulo reportes	165.5.60.19	Mozilla/5.0 (compatible; MSIE 11.0; Windows 98; Trident/5.0)	{}	2026-02-24 19:58:26-06	2026-02-24 19:58:26-06	\N
37	41	133	bloquear	roles	permiso	36	t	Bloquear en módulo roles	226.187.113.57	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.1; Trident/3.0)	{}	2026-03-19 23:37:26-06	2026-03-19 23:37:26-06	\N
38	24	98	bloquear	permisos	rol	25	t	Bloquear en módulo permisos	88.249.53.57	Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5321 (KHTML, like Gecko) Chrome/39.0.819.0 Mobile Safari/5321	{}	2026-03-09 10:36:26-06	2026-03-09 10:36:26-06	\N
39	38	58	desbloquear	reportes	tarjeta_universitaria	46	t	Desbloquear en módulo reportes	154.40.59.70	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_5 rv:4.0; en-US) AppleWebKit/534.27.6 (KHTML, like Gecko) Version/5.0 Safari/534.27.6	{}	2026-03-21 07:49:26-06	2026-03-21 07:49:26-06	\N
40	9	24	editar	perfil	rol	31	t	Editar en módulo perfil	130.84.122.10	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_9) AppleWebKit/5330 (KHTML, like Gecko) Chrome/36.0.872.0 Mobile Safari/5330	{}	2026-03-19 15:39:26-06	2026-03-19 15:39:26-06	\N
41	11	78	eliminar	usuarios	pedido	23	f	Eliminar en módulo usuarios	143.76.18.74	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_5) AppleWebKit/5311 (KHTML, like Gecko) Chrome/38.0.889.0 Mobile Safari/5311	{}	2026-03-20 15:41:26-06	2026-03-20 15:41:26-06	\N
42	39	78	bloquear	roles	usuario	33	t	Bloquear en módulo roles	109.87.111.238	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_8 rv:2.0; nl-NL) AppleWebKit/532.36.1 (KHTML, like Gecko) Version/5.0.2 Safari/532.36.1	{}	2026-03-19 12:30:26-06	2026-03-19 12:30:26-06	\N
43	4	92	crear	reportes	pedido	40	t	Crear en módulo reportes	248.54.141.220	Mozilla/5.0 (X11; Linux i686) AppleWebKit/533.2 (KHTML, like Gecko) Chrome/92.0.4694.43 Safari/533.2 EdgA/92.01101.34	{}	2026-03-17 11:16:26-06	2026-03-17 11:16:26-06	\N
44	27	107	crear	usuarios	usuario	21	t	Crear en módulo usuarios	215.181.43.11	Mozilla/5.0 (X11; Linux x86_64; rv:7.0) Gecko/20220714 Firefox/36.0	{}	2026-03-08 05:02:26-06	2026-03-08 05:02:26-06	\N
45	9	133	desbloquear	usuarios	usuario	2	t	Desbloquear en módulo usuarios	56.32.139.163	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_2) AppleWebKit/5312 (KHTML, like Gecko) Chrome/38.0.870.0 Mobile Safari/5312	{}	2026-03-19 06:44:26-06	2026-03-19 06:44:26-06	\N
46	16	113	exportar	roles	pedido	50	t	Exportar en módulo roles	227.214.255.162	Mozilla/5.0 (Windows NT 5.0) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/83.0.4545.85 Safari/532.0 Edg/83.01060.38	{}	2026-03-05 22:53:26-06	2026-03-05 22:53:26-06	\N
47	16	36	ver	usuarios	usuario	9	t	Ver en módulo usuarios	165.48.19.190	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2 rv:5.0; nl-NL) AppleWebKit/533.48.1 (KHTML, like Gecko) Version/5.1 Safari/533.48.1	{}	2026-03-21 07:25:26-06	2026-03-21 07:25:26-06	\N
48	15	74	exportar	reportes	rol	39	t	Exportar en módulo reportes	112.163.172.27	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5310 (KHTML, like Gecko) Chrome/36.0.840.0 Mobile Safari/5310	{}	2026-03-21 16:06:26-06	2026-03-21 16:06:26-06	\N
49	27	2	editar	reportes	permiso	47	t	Editar en módulo reportes	19.29.7.142	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_7 rv:6.0) Gecko/20190915 Firefox/35.0	{}	2026-03-22 07:48:26-06	2026-03-22 07:48:26-06	\N
50	30	44	crear	bitacora	pedido	36	t	Crear en módulo bitacora	235.226.77.207	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/4.0)	{}	2026-03-19 05:39:26-06	2026-03-19 05:39:26-06	\N
51	10	44	bloquear	seguridad	usuario	14	t	Bloquear en módulo seguridad	141.42.99.85	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_0 rv:6.0) Gecko/20180509 Firefox/36.0	{}	2026-03-04 05:40:26-06	2026-03-04 05:40:26-06	\N
52	31	66	exportar	roles	tarjeta_universitaria	46	t	Exportar en módulo roles	126.181.141.61	Mozilla/5.0 (compatible; MSIE 9.0; Windows 98; Trident/5.0)	{}	2026-03-20 09:47:26-06	2026-03-20 09:47:26-06	\N
53	16	133	ver	roles	pedido	12	f	Ver en módulo roles	156.6.113.139	Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 4.0; Trident/3.0)	{}	2026-03-18 10:53:26-06	2026-03-18 10:53:26-06	\N
54	24	10	crear	seguridad	usuario	14	f	Crear en módulo seguridad	9.84.190.243	Opera/8.99 (Windows NT 5.0; sl-SI) Presto/2.8.190 Version/12.00	{}	2026-03-19 06:18:26-06	2026-03-19 06:18:26-06	\N
55	32	47	crear	roles	tarjeta_universitaria	49	t	Crear en módulo roles	123.240.18.105	Mozilla/5.0 (compatible; MSIE 7.0; Windows 95; Trident/4.0)	{}	2026-03-04 21:48:26-06	2026-03-04 21:48:26-06	\N
56	4	126	crear	bitacora	rol	36	t	Crear en módulo bitacora	218.216.236.111	Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5362 (KHTML, like Gecko) Chrome/38.0.828.0 Mobile Safari/5362	{}	2026-03-18 13:58:26-06	2026-03-18 13:58:26-06	\N
57	38	42	exportar	bitacora	permiso	31	t	Exportar en módulo bitacora	24.199.8.10	Mozilla/5.0 (Windows 98) AppleWebKit/5350 (KHTML, like Gecko) Chrome/38.0.867.0 Mobile Safari/5350	{}	2026-02-28 14:33:26-06	2026-02-28 14:33:26-06	\N
58	19	85	editar	perfil	tarjeta_universitaria	30	t	Editar en módulo perfil	158.230.207.251	Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20260107 Firefox/35.0	{}	2026-02-22 10:51:26-06	2026-02-22 10:51:26-06	\N
59	40	108	crear	perfil	rol	44	t	Crear en módulo perfil	75.120.217.216	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.01; Trident/3.0)	{}	2026-03-21 19:24:26-06	2026-03-21 19:24:26-06	\N
60	35	60	editar	roles	tarjeta_universitaria	6	t	Editar en módulo roles	17.30.30.12	Mozilla/5.0 (Windows NT 4.0) AppleWebKit/537.2 (KHTML, like Gecko) Chrome/86.0.4836.93 Safari/537.2 Edg/86.01036.73	{}	2026-03-21 16:41:26-06	2026-03-21 16:41:26-06	\N
61	21	3	crear	reportes	pedido	45	t	Crear en módulo reportes	116.37.21.221	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_5_3 rv:2.0; en-US) AppleWebKit/533.42.1 (KHTML, like Gecko) Version/5.1 Safari/533.42.1	{}	2026-03-22 03:06:26-06	2026-03-22 03:06:26-06	\N
62	39	94	ver	bitacora	tarjeta_universitaria	33	t	Ver en módulo bitacora	251.160.109.163	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_1 rv:6.0) Gecko/20160722 Firefox/36.0	{}	2026-03-16 22:43:26-06	2026-03-16 22:43:26-06	\N
63	12	43	editar	permisos	rol	46	t	Editar en módulo permisos	248.207.102.183	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/82.0.4453.84 Safari/535.1 EdgA/82.01026.99	{}	2026-03-22 12:06:26-06	2026-03-22 12:06:26-06	\N
64	10	103	desbloquear	usuarios	usuario	14	t	Desbloquear en módulo usuarios	203.176.141.205	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5342 (KHTML, like Gecko) Chrome/38.0.815.0 Mobile Safari/5342	{}	2026-03-19 14:48:26-06	2026-03-19 14:48:26-06	\N
65	35	70	crear	permisos	tarjeta_universitaria	45	t	Crear en módulo permisos	203.166.153.252	Mozilla/5.0 (iPad; CPU OS 8_2_2 like Mac OS X; en-US) AppleWebKit/534.31.1 (KHTML, like Gecko) Version/4.0.5 Mobile/8B118 Safari/6534.31.1	{}	2026-03-02 19:02:26-06	2026-03-02 19:02:26-06	\N
66	27	80	desbloquear	permisos	usuario	17	f	Desbloquear en módulo permisos	10.134.182.59	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_4) AppleWebKit/5322 (KHTML, like Gecko) Chrome/40.0.867.0 Mobile Safari/5322	{}	2026-02-23 05:19:26-06	2026-02-23 05:19:26-06	\N
67	20	44	eliminar	tarjetas	usuario	21	t	Eliminar en módulo tarjetas	163.60.228.103	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 6.0; Trident/4.0)	{}	2026-03-01 13:32:26-06	2026-03-01 13:32:26-06	\N
68	4	40	crear	seguridad	tarjeta_universitaria	10	t	Crear en módulo seguridad	23.82.8.232	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_7 rv:6.0; sl-SI) AppleWebKit/534.44.7 (KHTML, like Gecko) Version/4.0.1 Safari/534.44.7	{}	2026-03-17 18:53:26-06	2026-03-17 18:53:26-06	\N
69	17	135	crear	roles	permiso	44	t	Crear en módulo roles	113.38.104.104	Mozilla/5.0 (iPad; CPU OS 8_1_1 like Mac OS X; sl-SI) AppleWebKit/531.4.2 (KHTML, like Gecko) Version/4.0.5 Mobile/8B119 Safari/6531.4.2	{}	2026-03-18 06:43:26-06	2026-03-18 06:43:26-06	\N
70	18	81	exportar	roles	usuario	15	t	Exportar en módulo roles	110.178.65.132	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_0 rv:2.0; sl-SI) AppleWebKit/534.43.6 (KHTML, like Gecko) Version/4.0.1 Safari/534.43.6	{}	2026-03-19 04:13:26-06	2026-03-19 04:13:26-06	\N
71	38	80	bloquear	roles	tarjeta_universitaria	3	t	Bloquear en módulo roles	240.149.46.4	Mozilla/5.0 (iPhone; CPU iPhone OS 13_0 like Mac OS X) AppleWebKit/533.0 (KHTML, like Gecko) Version/15.0 EdgiOS/98.01053.12 Mobile/15E148 Safari/533.0	{}	2026-03-19 23:26:26-06	2026-03-19 23:26:26-06	\N
72	39	76	eliminar	perfil	tarjeta_universitaria	18	t	Eliminar en módulo perfil	145.8.104.183	Mozilla/5.0 (Windows NT 5.1) AppleWebKit/531.2 (KHTML, like Gecko) Chrome/95.0.4306.11 Safari/531.2 Edg/95.01113.14	{}	2026-03-20 06:28:26-06	2026-03-20 06:28:26-06	\N
73	5	68	bloquear	perfil	pedido	29	f	Bloquear en módulo perfil	102.92.79.61	Opera/8.84 (X11; Linux x86_64; nl-NL) Presto/2.11.219 Version/10.00	{}	2026-02-25 16:54:26-06	2026-02-25 16:54:26-06	\N
74	30	87	ver	permisos	tarjeta_universitaria	5	t	Ver en módulo permisos	80.30.82.96	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/4.0)	{}	2026-03-05 00:37:26-06	2026-03-05 00:37:26-06	\N
75	18	69	editar	reportes	rol	17	t	Editar en módulo reportes	39.2.214.154	Opera/8.89 (Windows NT 6.2; sl-SI) Presto/2.10.226 Version/11.00	{}	2026-03-21 20:46:26-06	2026-03-21 20:46:26-06	\N
76	26	130	exportar	perfil	rol	48	t	Exportar en módulo perfil	196.195.29.20	Mozilla/5.0 (X11; Linux i686) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/87.0.4191.96 Safari/532.0 EdgA/87.01039.51	{}	2026-03-16 17:28:26-06	2026-03-16 17:28:26-06	\N
77	8	33	editar	usuarios	pedido	23	t	Editar en módulo usuarios	242.108.159.61	Mozilla/5.0 (Windows NT 5.0) AppleWebKit/537.0 (KHTML, like Gecko) Chrome/92.0.4211.56 Safari/537.0 Edg/92.01059.49	{}	2026-03-23 11:23:26-06	2026-03-23 11:23:26-06	\N
78	28	108	ver	reportes	permiso	9	t	Ver en módulo reportes	13.122.154.190	Opera/9.66 (Windows NT 5.01; sl-SI) Presto/2.11.304 Version/11.00	{}	2026-03-01 15:07:26-06	2026-03-01 15:07:26-06	\N
79	5	121	eliminar	usuarios	permiso	11	t	Eliminar en módulo usuarios	70.29.219.107	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_6 rv:4.0; en-US) AppleWebKit/531.5.3 (KHTML, like Gecko) Version/4.0.5 Safari/531.5.3	{}	2026-03-16 15:42:26-06	2026-03-16 15:42:26-06	\N
80	6	55	desbloquear	usuarios	usuario	25	t	Desbloquear en módulo usuarios	20.192.132.16	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.1; Trident/3.1)	{}	2026-03-10 12:51:26-06	2026-03-10 12:51:26-06	\N
81	20	26	editar	perfil	usuario	5	t	Editar en módulo perfil	91.12.16.239	Mozilla/5.0 (iPad; CPU OS 8_1_2 like Mac OS X; nl-NL) AppleWebKit/534.10.6 (KHTML, like Gecko) Version/4.0.5 Mobile/8B115 Safari/6534.10.6	{}	2026-03-22 01:15:26-06	2026-03-22 01:15:26-06	\N
82	5	1	bloquear	usuarios	pedido	46	t	Bloquear en módulo usuarios	166.170.67.134	Mozilla/5.0 (X11; Linux i686; rv:7.0) Gecko/20180703 Firefox/37.0	{}	2026-03-09 00:32:26-06	2026-03-09 00:32:26-06	\N
83	26	27	editar	tarjetas	rol	46	t	Editar en módulo tarjetas	78.78.97.171	Opera/8.91 (Windows NT 5.1; sl-SI) Presto/2.10.207 Version/11.00	{}	2026-03-16 00:35:26-06	2026-03-16 00:35:26-06	\N
84	27	73	ver	perfil	tarjeta_universitaria	16	t	Ver en módulo perfil	54.122.227.98	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/90.0.4680.92 Safari/532.0 EdgA/90.01120.37	{}	2026-03-20 06:01:26-06	2026-03-20 06:01:26-06	\N
85	31	31	exportar	perfil	permiso	39	t	Exportar en módulo perfil	106.173.132.166	Mozilla/5.0 (Windows CE; sl-SI; rv:1.9.1.20) Gecko/20120302 Firefox/35.0	{}	2026-02-23 06:44:26-06	2026-02-23 06:44:26-06	\N
86	29	58	bloquear	usuarios	tarjeta_universitaria	40	t	Bloquear en módulo usuarios	177.72.133.51	Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5362 (KHTML, like Gecko) Chrome/38.0.897.0 Mobile Safari/5362	{}	2026-03-23 22:09:26-06	2026-03-23 22:09:26-06	\N
87	18	73	bloquear	seguridad	permiso	20	t	Bloquear en módulo seguridad	193.37.124.236	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 4.0; Trident/3.1)	{}	2026-03-20 13:00:26-06	2026-03-20 13:00:26-06	\N
88	6	110	ver	usuarios	tarjeta_universitaria	37	t	Ver en módulo usuarios	231.53.201.64	Opera/9.63 (X11; Linux x86_64; nl-NL) Presto/2.8.198 Version/10.00	{}	2026-03-18 19:42:26-06	2026-03-18 19:42:26-06	\N
89	34	53	editar	roles	tarjeta_universitaria	46	t	Editar en módulo roles	20.158.70.148	Mozilla/5.0 (Windows NT 5.0; sl-SI; rv:1.9.0.20) Gecko/20131116 Firefox/35.0	{}	2026-02-22 16:09:26-06	2026-02-22 16:09:26-06	\N
90	6	64	desbloquear	perfil	pedido	39	t	Desbloquear en módulo perfil	52.193.70.233	Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 4.0; Trident/4.1)	{}	2026-03-19 10:08:26-06	2026-03-19 10:08:26-06	\N
91	20	39	bloquear	perfil	usuario	17	t	Bloquear en módulo perfil	31.61.89.148	Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/531.1 (KHTML, like Gecko) Version/15.0 EdgiOS/91.01098.92 Mobile/15E148 Safari/531.1	{}	2026-03-16 17:24:26-06	2026-03-16 17:24:26-06	\N
92	9	93	crear	reportes	tarjeta_universitaria	18	t	Crear en módulo reportes	153.112.114.162	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_7) AppleWebKit/536.2 (KHTML, like Gecko) Chrome/87.0.4794.46 Safari/536.2 Edg/87.01095.5	{}	2026-03-21 09:52:26-06	2026-03-21 09:52:26-06	\N
93	20	96	crear	bitacora	tarjeta_universitaria	33	t	Crear en módulo bitacora	231.244.171.184	Opera/9.46 (Windows NT 6.1; en-US) Presto/2.11.209 Version/11.00	{}	2026-03-19 19:49:26-06	2026-03-19 19:49:26-06	\N
94	40	72	editar	perfil	permiso	8	f	Editar en módulo perfil	104.192.221.4	Mozilla/5.0 (Windows 98; Win 9x 4.90; nl-NL; rv:1.9.0.20) Gecko/20140313 Firefox/37.0	{}	2026-03-19 05:17:26-06	2026-03-19 05:17:26-06	\N
95	31	12	editar	seguridad	rol	19	t	Editar en módulo seguridad	130.65.26.152	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_6 rv:4.0) Gecko/20130714 Firefox/36.0	{}	2026-02-23 17:34:26-06	2026-02-23 17:34:26-06	\N
96	26	120	eliminar	seguridad	rol	24	t	Eliminar en módulo seguridad	213.48.46.184	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_7) AppleWebKit/533.1 (KHTML, like Gecko) Chrome/92.0.4329.40 Safari/533.1 Edg/92.01054.86	{}	2026-03-14 21:33:26-06	2026-03-14 21:33:26-06	\N
97	39	61	ver	usuarios	permiso	14	t	Ver en módulo usuarios	118.233.95.27	Mozilla/5.0 (iPhone; CPU iPhone OS 15_1 like Mac OS X) AppleWebKit/534.2 (KHTML, like Gecko) Version/15.0 EdgiOS/91.01072.76 Mobile/15E148 Safari/534.2	{}	2026-03-17 20:17:26-06	2026-03-17 20:17:26-06	\N
98	11	113	editar	seguridad	rol	47	t	Editar en módulo seguridad	96.68.4.44	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/533.2 (KHTML, like Gecko) Chrome/94.0.4705.99 Safari/533.2 EdgA/94.01139.45	{}	2026-03-17 17:18:26-06	2026-03-17 17:18:26-06	\N
99	14	46	crear	usuarios	tarjeta_universitaria	18	t	Crear en módulo usuarios	231.147.78.222	Mozilla/5.0 (Windows; U; Windows NT 6.2) AppleWebKit/532.13.3 (KHTML, like Gecko) Version/5.0.4 Safari/532.13.3	{}	2026-03-06 15:14:26-06	2026-03-06 15:14:26-06	\N
100	29	118	exportar	permisos	pedido	31	t	Exportar en módulo permisos	105.179.132.102	Mozilla/5.0 (X11; Linux x86_64; rv:7.0) Gecko/20150328 Firefox/37.0	{}	2026-02-21 09:49:26-06	2026-02-21 09:49:26-06	\N
101	5	110	crear	permisos	pedido	24	t	Crear en módulo permisos	243.97.92.177	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_2 rv:6.0) Gecko/20241023 Firefox/35.0	{}	2026-03-23 20:42:26-06	2026-03-23 20:42:26-06	\N
102	24	103	bloquear	roles	pedido	13	t	Bloquear en módulo roles	178.255.182.182	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5320 (KHTML, like Gecko) Chrome/37.0.846.0 Mobile Safari/5320	{}	2026-03-17 17:20:26-06	2026-03-17 17:20:26-06	\N
103	6	92	editar	seguridad	permiso	31	t	Editar en módulo seguridad	14.12.10.26	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5352 (KHTML, like Gecko) Chrome/36.0.828.0 Mobile Safari/5352	{}	2026-03-19 22:46:26-06	2026-03-19 22:46:26-06	\N
104	17	37	crear	perfil	usuario	15	t	Crear en módulo perfil	200.167.174.131	Mozilla/5.0 (Windows 98; Win 9x 4.90) AppleWebKit/5341 (KHTML, like Gecko) Chrome/38.0.864.0 Mobile Safari/5341	{}	2026-03-18 17:38:26-06	2026-03-18 17:38:26-06	\N
105	17	60	eliminar	bitacora	tarjeta_universitaria	21	t	Eliminar en módulo bitacora	231.133.142.100	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_9 rv:2.0) Gecko/20130117 Firefox/35.0	{}	2026-03-22 14:43:26-06	2026-03-22 14:43:26-06	\N
106	16	50	bloquear	bitacora	pedido	38	f	Bloquear en módulo bitacora	1.7.64.0	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5360 (KHTML, like Gecko) Chrome/36.0.822.0 Mobile Safari/5360	{}	2026-02-22 14:56:26-06	2026-02-22 14:56:26-06	\N
107	16	106	ver	bitacora	permiso	40	t	Ver en módulo bitacora	89.247.107.177	Opera/8.76 (Windows NT 5.2; sl-SI) Presto/2.11.209 Version/11.00	{}	2026-03-19 16:25:26-06	2026-03-19 16:25:26-06	\N
108	20	102	editar	tarjetas	tarjeta_universitaria	29	t	Editar en módulo tarjetas	209.104.174.21	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_0 rv:5.0; en-US) AppleWebKit/532.26.2 (KHTML, like Gecko) Version/4.0.2 Safari/532.26.2	{}	2026-03-18 21:25:26-06	2026-03-18 21:25:26-06	\N
109	9	11	bloquear	permisos	usuario	14	t	Bloquear en módulo permisos	202.238.214.55	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5362 (KHTML, like Gecko) Chrome/40.0.819.0 Mobile Safari/5362	{}	2026-03-20 04:59:26-06	2026-03-20 04:59:26-06	\N
110	29	32	bloquear	usuarios	tarjeta_universitaria	31	t	Bloquear en módulo usuarios	30.158.221.221	Opera/9.39 (X11; Linux i686; en-US) Presto/2.12.188 Version/11.00	{}	2026-03-06 22:22:26-06	2026-03-06 22:22:26-06	\N
111	10	109	eliminar	perfil	pedido	41	f	Eliminar en módulo perfil	55.28.237.126	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_5_9) AppleWebKit/5310 (KHTML, like Gecko) Chrome/40.0.863.0 Mobile Safari/5310	{}	2026-02-28 00:16:26-06	2026-02-28 00:16:26-06	\N
112	33	41	bloquear	tarjetas	pedido	3	t	Bloquear en módulo tarjetas	39.255.134.70	Opera/8.88 (Windows NT 5.0; nl-NL) Presto/2.9.204 Version/11.00	{}	2026-03-19 16:08:26-06	2026-03-19 16:08:26-06	\N
113	22	93	eliminar	roles	usuario	50	t	Eliminar en módulo roles	132.71.121.198	Mozilla/5.0 (compatible; MSIE 11.0; Windows 98; Win 9x 4.90; Trident/5.1)	{}	2026-03-18 14:24:26-06	2026-03-18 14:24:26-06	\N
114	41	42	desbloquear	roles	usuario	14	t	Desbloquear en módulo roles	6.19.85.233	Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5350 (KHTML, like Gecko) Chrome/36.0.890.0 Mobile Safari/5350	{}	2026-03-22 17:30:26-06	2026-03-22 17:30:26-06	\N
115	23	106	editar	bitacora	usuario	41	t	Editar en módulo bitacora	61.253.98.171	Mozilla/5.0 (Windows NT 6.0) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/82.0.4674.21 Safari/532.0 Edg/82.01019.28	{}	2026-02-23 19:17:26-06	2026-02-23 19:17:26-06	\N
116	5	111	eliminar	permisos	permiso	9	f	Eliminar en módulo permisos	48.229.255.64	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.0; Trident/4.1)	{}	2026-03-12 21:02:26-06	2026-03-12 21:02:26-06	\N
117	11	11	eliminar	tarjetas	rol	47	t	Eliminar en módulo tarjetas	52.141.173.46	Mozilla/5.0 (Windows; U; Windows NT 6.2) AppleWebKit/532.2.6 (KHTML, like Gecko) Version/4.0.2 Safari/532.2.6	{}	2026-03-18 10:54:26-06	2026-03-18 10:54:26-06	\N
118	40	90	exportar	permisos	tarjeta_universitaria	30	t	Exportar en módulo permisos	244.59.196.180	Opera/9.65 (Windows 98; sl-SI) Presto/2.8.237 Version/12.00	{}	2026-03-19 14:23:26-06	2026-03-19 14:23:26-06	\N
119	5	85	desbloquear	perfil	permiso	8	t	Desbloquear en módulo perfil	186.209.248.109	Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.1.20) Gecko/20251013 Firefox/35.0	{}	2026-02-28 08:45:26-06	2026-02-28 08:45:26-06	\N
120	20	72	ver	reportes	tarjeta_universitaria	43	t	Ver en módulo reportes	203.161.25.167	Opera/9.86 (X11; Linux x86_64; en-US) Presto/2.8.285 Version/12.00	{}	2026-03-21 14:52:26-06	2026-03-21 14:52:26-06	\N
121	28	69	ver	permisos	rol	23	t	Ver en módulo permisos	135.37.243.214	Mozilla/5.0 (Windows NT 5.2; nl-NL; rv:1.9.2.20) Gecko/20250610 Firefox/37.0	{}	2026-03-17 17:52:26-06	2026-03-17 17:52:26-06	\N
122	34	103	crear	tarjetas	pedido	11	t	Crear en módulo tarjetas	110.58.127.97	Opera/8.52 (X11; Linux x86_64; sl-SI) Presto/2.8.339 Version/12.00	{}	2026-03-18 12:01:26-06	2026-03-18 12:01:26-06	\N
123	13	77	bloquear	permisos	pedido	11	t	Bloquear en módulo permisos	144.101.110.66	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_8_0) AppleWebKit/536.1 (KHTML, like Gecko) Chrome/90.0.4397.75 Safari/536.1 Edg/90.01021.47	{}	2026-03-19 22:07:26-06	2026-03-19 22:07:26-06	\N
124	26	52	editar	usuarios	tarjeta_universitaria	15	t	Editar en módulo usuarios	13.99.43.22	Mozilla/5.0 (Windows; U; Windows NT 5.1) AppleWebKit/533.41.2 (KHTML, like Gecko) Version/4.0 Safari/533.41.2	{}	2026-03-09 03:12:26-06	2026-03-09 03:12:26-06	\N
125	10	23	crear	roles	pedido	10	t	Crear en módulo roles	201.106.224.12	Opera/9.69 (Windows NT 5.0; en-US) Presto/2.8.201 Version/11.00	{}	2026-03-18 17:16:26-06	2026-03-18 17:16:26-06	\N
126	42	77	exportar	roles	pedido	40	t	Exportar en módulo roles	196.207.169.160	Opera/9.65 (X11; Linux i686; nl-NL) Presto/2.8.336 Version/11.00	{}	2026-03-18 11:11:26-06	2026-03-18 11:11:26-06	\N
127	37	19	desbloquear	reportes	tarjeta_universitaria	7	t	Desbloquear en módulo reportes	91.23.68.34	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_4 rv:5.0) Gecko/20120329 Firefox/37.0	{}	2026-02-28 05:36:26-06	2026-02-28 05:36:26-06	\N
128	39	84	eliminar	perfil	permiso	26	t	Eliminar en módulo perfil	7.12.215.168	Mozilla/5.0 (iPad; CPU OS 7_1_2 like Mac OS X; nl-NL) AppleWebKit/535.40.2 (KHTML, like Gecko) Version/4.0.5 Mobile/8B117 Safari/6535.40.2	{}	2026-03-21 07:29:26-06	2026-03-21 07:29:26-06	\N
129	5	93	ver	roles	pedido	23	t	Ver en módulo roles	153.113.237.191	Mozilla/5.0 (compatible; MSIE 9.0; Windows CE; Trident/4.0)	{}	2026-03-23 11:33:26-06	2026-03-23 11:33:26-06	\N
130	33	9	exportar	reportes	usuario	28	t	Exportar en módulo reportes	109.178.217.90	Opera/8.18 (X11; Linux x86_64; en-US) Presto/2.12.272 Version/10.00	{}	2026-03-21 01:58:26-06	2026-03-21 01:58:26-06	\N
131	16	116	editar	tarjetas	permiso	17	t	Editar en módulo tarjetas	229.189.19.202	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3 rv:3.0) Gecko/20130428 Firefox/37.0	{}	2026-03-23 17:42:26-06	2026-03-23 17:42:26-06	\N
132	26	130	exportar	perfil	pedido	17	f	Exportar en módulo perfil	249.73.141.254	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/3.0)	{}	2026-03-19 10:45:26-06	2026-03-19 10:45:26-06	\N
133	9	44	bloquear	bitacora	rol	32	t	Bloquear en módulo bitacora	90.62.135.13	Mozilla/5.0 (iPad; CPU OS 7_0_1 like Mac OS X; nl-NL) AppleWebKit/535.2.1 (KHTML, like Gecko) Version/3.0.5 Mobile/8B114 Safari/6535.2.1	{}	2026-03-03 14:50:26-06	2026-03-03 14:50:26-06	\N
134	16	44	ver	roles	usuario	43	t	Ver en módulo roles	142.217.56.233	Opera/8.32 (X11; Linux i686; en-US) Presto/2.9.169 Version/10.00	{}	2026-03-19 22:02:26-06	2026-03-19 22:02:26-06	\N
135	10	53	exportar	perfil	permiso	21	t	Exportar en módulo perfil	129.48.205.189	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_4 rv:5.0) Gecko/20230318 Firefox/36.0	{}	2026-03-22 15:48:26-06	2026-03-22 15:48:26-06	\N
136	22	114	ver	perfil	permiso	16	t	Ver en módulo perfil	249.200.83.132	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8) AppleWebKit/5342 (KHTML, like Gecko) Chrome/40.0.819.0 Mobile Safari/5342	{}	2026-03-04 14:35:26-06	2026-03-04 14:35:26-06	\N
137	25	117	eliminar	tarjetas	rol	30	t	Eliminar en módulo tarjetas	237.12.53.33	Mozilla/5.0 (iPad; CPU OS 8_2_2 like Mac OS X; sl-SI) AppleWebKit/533.19.2 (KHTML, like Gecko) Version/4.0.5 Mobile/8B119 Safari/6533.19.2	{}	2026-03-23 16:15:26-06	2026-03-23 16:15:26-06	\N
138	21	127	eliminar	roles	permiso	6	f	Eliminar en módulo roles	74.59.113.96	Mozilla/5.0 (iPhone; CPU iPhone OS 8_0_2 like Mac OS X; sl-SI) AppleWebKit/533.18.2 (KHTML, like Gecko) Version/4.0.5 Mobile/8B114 Safari/6533.18.2	{}	2026-03-23 03:14:26-06	2026-03-23 03:14:26-06	\N
139	32	131	crear	bitacora	permiso	1	t	Crear en módulo bitacora	140.0.32.123	Opera/9.36 (Windows NT 5.0; en-US) Presto/2.11.195 Version/10.00	{}	2026-03-05 17:11:26-06	2026-03-05 17:11:26-06	\N
140	40	50	exportar	perfil	rol	22	t	Exportar en módulo perfil	247.57.117.4	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_9) AppleWebKit/534.1 (KHTML, like Gecko) Chrome/85.0.4005.94 Safari/534.1 Edg/85.01017.14	{}	2026-03-20 17:16:26-06	2026-03-20 17:16:26-06	\N
141	42	111	ver	reportes	usuario	27	t	Ver en módulo reportes	223.205.143.71	Mozilla/5.0 (X11; Linux x86_64; rv:6.0) Gecko/20101115 Firefox/35.0	{}	2026-02-23 08:53:26-06	2026-02-23 08:53:26-06	\N
142	15	68	exportar	seguridad	rol	32	t	Exportar en módulo seguridad	190.89.62.159	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_8 rv:3.0) Gecko/20250906 Firefox/37.0	{}	2026-03-23 06:16:26-06	2026-03-23 06:16:26-06	\N
143	41	32	eliminar	tarjetas	tarjeta_universitaria	22	t	Eliminar en módulo tarjetas	98.50.237.40	Opera/8.48 (Windows CE; en-US) Presto/2.10.212 Version/12.00	{}	2026-02-22 05:02:26-06	2026-02-22 05:02:26-06	\N
144	21	105	bloquear	perfil	tarjeta_universitaria	15	t	Bloquear en módulo perfil	110.155.89.77	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.2; Trident/3.0)	{}	2026-03-22 23:18:26-06	2026-03-22 23:18:26-06	\N
145	35	77	eliminar	usuarios	usuario	38	t	Eliminar en módulo usuarios	186.114.2.29	Mozilla/5.0 (Windows 98; Win 9x 4.90; nl-NL; rv:1.9.0.20) Gecko/20150316 Firefox/36.0	{}	2026-03-04 11:37:26-06	2026-03-04 11:37:26-06	\N
146	41	55	desbloquear	tarjetas	permiso	3	t	Desbloquear en módulo tarjetas	147.250.16.92	Mozilla/5.0 (iPhone; CPU iPhone OS 7_2_1 like Mac OS X; sl-SI) AppleWebKit/531.45.6 (KHTML, like Gecko) Version/4.0.5 Mobile/8B113 Safari/6531.45.6	{}	2026-03-20 22:46:26-06	2026-03-20 22:46:26-06	\N
147	8	52	eliminar	perfil	usuario	29	t	Eliminar en módulo perfil	217.220.137.251	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/5.0)	{}	2026-03-23 14:45:26-06	2026-03-23 14:45:26-06	\N
148	21	31	editar	seguridad	usuario	15	t	Editar en módulo seguridad	105.154.202.222	Opera/9.57 (X11; Linux x86_64; nl-NL) Presto/2.12.276 Version/10.00	{}	2026-03-21 11:14:26-06	2026-03-21 11:14:26-06	\N
149	9	108	crear	tarjetas	rol	50	f	Crear en módulo tarjetas	83.10.210.60	Mozilla/5.0 (Windows NT 6.2; nl-NL; rv:1.9.0.20) Gecko/20241123 Firefox/35.0	{}	2026-03-10 05:01:26-06	2026-03-10 05:01:26-06	\N
150	14	82	editar	bitacora	rol	44	t	Editar en módulo bitacora	237.58.39.181	Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.0 (KHTML, like Gecko) Chrome/79.0.4493.49 Safari/537.0 EdgA/79.01017.28	{}	2026-03-17 03:24:26-06	2026-03-17 03:24:26-06	\N
151	33	69	eliminar	roles	pedido	48	t	Eliminar en módulo roles	15.108.77.153	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8 rv:5.0) Gecko/20220408 Firefox/37.0	{}	2026-03-20 06:17:26-06	2026-03-20 06:17:26-06	\N
152	11	16	ver	perfil	tarjeta_universitaria	45	t	Ver en módulo perfil	144.39.105.191	Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20240520 Firefox/35.0	{}	2026-02-26 09:03:26-06	2026-02-26 09:03:26-06	\N
153	7	18	bloquear	usuarios	tarjeta_universitaria	22	t	Bloquear en módulo usuarios	211.255.206.203	Opera/9.25 (X11; Linux x86_64; nl-NL) Presto/2.10.314 Version/11.00	{}	2026-03-19 04:05:26-06	2026-03-19 04:05:26-06	\N
154	38	80	crear	seguridad	permiso	43	t	Crear en módulo seguridad	214.95.167.13	Mozilla/5.0 (Windows; U; Windows NT 5.0) AppleWebKit/535.13.2 (KHTML, like Gecko) Version/5.0.2 Safari/535.13.2	{}	2026-03-16 09:06:26-06	2026-03-16 09:06:26-06	\N
155	23	135	ver	reportes	rol	35	t	Ver en módulo reportes	138.75.175.146	Mozilla/5.0 (Windows 98; Win 9x 4.90; sl-SI; rv:1.9.0.20) Gecko/20100520 Firefox/37.0	{}	2026-02-24 14:35:26-06	2026-02-24 14:35:26-06	\N
156	26	70	bloquear	usuarios	pedido	26	t	Bloquear en módulo usuarios	105.208.42.32	Mozilla/5.0 (Windows 98; Win 9x 4.90) AppleWebKit/534.2 (KHTML, like Gecko) Chrome/88.0.4179.23 Safari/534.2 Edg/88.01104.38	{}	2026-03-15 13:01:26-06	2026-03-15 13:01:26-06	\N
157	12	86	bloquear	bitacora	tarjeta_universitaria	35	t	Bloquear en módulo bitacora	111.204.63.187	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_6 rv:3.0) Gecko/20100812 Firefox/35.0	{}	2026-03-23 07:58:26-06	2026-03-23 07:58:26-06	\N
158	37	109	editar	perfil	pedido	27	t	Editar en módulo perfil	165.255.179.210	Mozilla/5.0 (Windows CE) AppleWebKit/5341 (KHTML, like Gecko) Chrome/38.0.816.0 Mobile Safari/5341	{}	2026-03-21 03:09:26-06	2026-03-21 03:09:26-06	\N
159	26	5	editar	seguridad	pedido	22	t	Editar en módulo seguridad	69.124.71.63	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 5.01; Trident/4.1)	{}	2026-02-22 21:18:26-06	2026-02-22 21:18:26-06	\N
225	30	121	editar	seguridad	usuario	3	t	Editar en módulo seguridad	243.60.35.70	Opera/8.40 (X11; Linux i686; sl-SI) Presto/2.12.277 Version/11.00	{}	2026-03-17 06:16:26-06	2026-03-17 06:16:26-06	\N
160	35	95	desbloquear	seguridad	usuario	1	t	Desbloquear en módulo seguridad	43.15.98.234	Mozilla/5.0 (iPad; CPU OS 7_2_2 like Mac OS X; sl-SI) AppleWebKit/531.43.3 (KHTML, like Gecko) Version/4.0.5 Mobile/8B111 Safari/6531.43.3	{}	2026-02-24 01:32:26-06	2026-02-24 01:32:26-06	\N
161	41	11	ver	tarjetas	permiso	2	t	Ver en módulo tarjetas	62.132.150.189	Mozilla/5.0 (Windows NT 5.2; sl-SI; rv:1.9.2.20) Gecko/20230829 Firefox/35.0	{}	2026-03-18 01:34:26-06	2026-03-18 01:34:26-06	\N
162	14	119	bloquear	roles	rol	4	t	Bloquear en módulo roles	141.201.161.177	Mozilla/5.0 (Windows NT 4.0; en-US; rv:1.9.0.20) Gecko/20140609 Firefox/35.0	{}	2026-03-22 08:15:26-06	2026-03-22 08:15:26-06	\N
163	17	75	bloquear	permisos	permiso	6	t	Bloquear en módulo permisos	189.74.115.21	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_1 rv:5.0; sl-SI) AppleWebKit/531.19.2 (KHTML, like Gecko) Version/4.1 Safari/531.19.2	{}	2026-03-20 14:53:26-06	2026-03-20 14:53:26-06	\N
164	35	10	editar	reportes	rol	27	t	Editar en módulo reportes	55.128.252.147	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_3 rv:5.0) Gecko/20250406 Firefox/37.0	{}	2026-03-23 02:11:26-06	2026-03-23 02:11:26-06	\N
165	13	83	ver	permisos	permiso	5	t	Ver en módulo permisos	30.157.54.81	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5361 (KHTML, like Gecko) Chrome/37.0.869.0 Mobile Safari/5361	{}	2026-03-18 21:12:26-06	2026-03-18 21:12:26-06	\N
166	9	117	eliminar	roles	tarjeta_universitaria	40	t	Eliminar en módulo roles	210.63.98.64	Opera/9.68 (Windows NT 5.2; sl-SI) Presto/2.8.308 Version/12.00	{}	2026-03-22 15:41:26-06	2026-03-22 15:41:26-06	\N
167	21	40	crear	bitacora	usuario	24	t	Crear en módulo bitacora	167.244.245.226	Opera/9.88 (X11; Linux i686; nl-NL) Presto/2.9.279 Version/10.00	{}	2026-03-14 06:58:26-06	2026-03-14 06:58:26-06	\N
168	10	23	editar	roles	permiso	41	f	Editar en módulo roles	98.7.55.183	Mozilla/5.0 (compatible; MSIE 8.0; Windows 98; Win 9x 4.90; Trident/5.1)	{}	2026-03-21 19:47:26-06	2026-03-21 19:47:26-06	\N
169	22	20	crear	usuarios	rol	31	t	Crear en módulo usuarios	157.50.193.163	Mozilla/5.0 (Windows; U; Windows NT 6.1) AppleWebKit/532.1.2 (KHTML, like Gecko) Version/4.0.2 Safari/532.1.2	{}	2026-03-07 19:31:26-06	2026-03-07 19:31:26-06	\N
170	23	66	ver	reportes	usuario	43	t	Ver en módulo reportes	233.148.251.98	Mozilla/5.0 (X11; Linux i686) AppleWebKit/531.2 (KHTML, like Gecko) Chrome/92.0.4211.20 Safari/531.2 EdgA/92.01068.4	{}	2026-03-19 18:01:26-06	2026-03-19 18:01:26-06	\N
171	41	66	desbloquear	seguridad	usuario	16	t	Desbloquear en módulo seguridad	213.116.159.47	Mozilla/5.0 (Windows NT 5.2; sl-SI; rv:1.9.1.20) Gecko/20131005 Firefox/37.0	{}	2026-03-14 04:22:26-06	2026-03-14 04:22:26-06	\N
172	24	9	crear	reportes	usuario	14	t	Crear en módulo reportes	111.34.94.138	Opera/8.99 (Windows NT 5.1; sl-SI) Presto/2.9.223 Version/12.00	{}	2026-03-22 11:50:26-06	2026-03-22 11:50:26-06	\N
173	37	118	exportar	seguridad	permiso	36	f	Exportar en módulo seguridad	14.163.23.233	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_7) AppleWebKit/5331 (KHTML, like Gecko) Chrome/39.0.869.0 Mobile Safari/5331	{}	2026-02-28 04:23:26-06	2026-02-28 04:23:26-06	\N
174	27	82	crear	bitacora	tarjeta_universitaria	5	t	Crear en módulo bitacora	167.250.48.7	Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5352 (KHTML, like Gecko) Chrome/38.0.831.0 Mobile Safari/5352	{}	2026-03-23 04:25:26-06	2026-03-23 04:25:26-06	\N
175	40	18	desbloquear	reportes	pedido	31	t	Desbloquear en módulo reportes	249.193.174.61	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5362 (KHTML, like Gecko) Chrome/37.0.851.0 Mobile Safari/5362	{}	2026-03-23 17:08:26-06	2026-03-23 17:08:26-06	\N
176	24	44	desbloquear	reportes	pedido	22	t	Desbloquear en módulo reportes	60.85.146.218	Opera/9.17 (Windows 98; Win 9x 4.90; nl-NL) Presto/2.8.176 Version/11.00	{}	2026-03-05 15:50:26-06	2026-03-05 15:50:26-06	\N
177	32	50	crear	bitacora	tarjeta_universitaria	38	t	Crear en módulo bitacora	137.165.211.99	Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/39.0.875.0 Mobile Safari/5352	{}	2026-03-21 22:06:26-06	2026-03-21 22:06:26-06	\N
178	19	122	editar	permisos	rol	24	f	Editar en módulo permisos	6.200.172.49	Mozilla/5.0 (compatible; MSIE 5.0; Windows 98; Win 9x 4.90; Trident/3.1)	{}	2026-03-20 07:58:26-06	2026-03-20 07:58:26-06	\N
179	35	56	crear	bitacora	rol	40	t	Crear en módulo bitacora	23.49.230.145	Opera/8.12 (Windows NT 5.2; sl-SI) Presto/2.11.199 Version/12.00	{}	2026-03-14 15:26:26-06	2026-03-14 15:26:26-06	\N
180	13	111	ver	roles	permiso	5	t	Ver en módulo roles	185.179.112.2	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_1) AppleWebKit/536.1 (KHTML, like Gecko) Chrome/97.0.4799.28 Safari/536.1 Edg/97.01121.82	{}	2026-03-09 00:23:26-06	2026-03-09 00:23:26-06	\N
181	27	84	ver	bitacora	tarjeta_universitaria	36	t	Ver en módulo bitacora	70.96.228.75	Mozilla/5.0 (Windows NT 5.01; nl-NL; rv:1.9.2.20) Gecko/20190521 Firefox/35.0	{}	2026-03-10 15:23:26-06	2026-03-10 15:23:26-06	\N
182	37	18	crear	usuarios	tarjeta_universitaria	3	t	Crear en módulo usuarios	170.238.67.202	Mozilla/5.0 (X11; Linux x86_64; rv:7.0) Gecko/20120714 Firefox/35.0	{}	2026-03-22 08:25:26-06	2026-03-22 08:25:26-06	\N
183	4	72	exportar	perfil	rol	7	t	Exportar en módulo perfil	104.99.183.47	Mozilla/5.0 (compatible; MSIE 10.0; Windows 98; Trident/3.1)	{}	2026-02-20 23:26:26-06	2026-02-20 23:26:26-06	\N
184	19	64	eliminar	bitacora	usuario	12	t	Eliminar en módulo bitacora	244.198.202.203	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5362 (KHTML, like Gecko) Chrome/36.0.890.0 Mobile Safari/5362	{}	2026-03-20 23:03:26-06	2026-03-20 23:03:26-06	\N
185	14	51	editar	bitacora	pedido	4	t	Editar en módulo bitacora	95.187.159.217	Opera/9.48 (X11; Linux x86_64; sl-SI) Presto/2.9.285 Version/12.00	{}	2026-03-09 04:06:26-06	2026-03-09 04:06:26-06	\N
186	23	16	editar	roles	rol	3	t	Editar en módulo roles	16.159.216.240	Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/5.0)	{}	2026-03-21 02:11:26-06	2026-03-21 02:11:26-06	\N
187	36	72	ver	reportes	permiso	46	t	Ver en módulo reportes	20.166.221.210	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_4 rv:2.0; nl-NL) AppleWebKit/532.29.6 (KHTML, like Gecko) Version/4.1 Safari/532.29.6	{}	2026-03-21 00:43:26-06	2026-03-21 00:43:26-06	\N
188	25	102	ver	bitacora	usuario	12	t	Ver en módulo bitacora	11.28.175.68	Mozilla/5.0 (Windows; U; Windows NT 5.1) AppleWebKit/533.21.7 (KHTML, like Gecko) Version/5.0.4 Safari/533.21.7	{}	2026-03-22 22:21:26-06	2026-03-22 22:21:26-06	\N
189	19	108	editar	tarjetas	tarjeta_universitaria	25	t	Editar en módulo tarjetas	64.139.92.167	Mozilla/5.0 (iPad; CPU OS 8_2_2 like Mac OS X; sl-SI) AppleWebKit/535.46.3 (KHTML, like Gecko) Version/4.0.5 Mobile/8B112 Safari/6535.46.3	{}	2026-03-01 00:20:26-06	2026-03-01 00:20:26-06	\N
190	17	25	eliminar	reportes	pedido	29	t	Eliminar en módulo reportes	210.112.162.223	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/4.1)	{}	2026-03-10 21:33:26-06	2026-03-10 21:33:26-06	\N
191	42	93	ver	usuarios	rol	24	t	Ver en módulo usuarios	41.227.113.142	Opera/9.69 (X11; Linux x86_64; nl-NL) Presto/2.8.259 Version/11.00	{}	2026-03-19 04:28:26-06	2026-03-19 04:28:26-06	\N
192	9	79	editar	roles	tarjeta_universitaria	47	t	Editar en módulo roles	152.66.105.90	Opera/8.41 (X11; Linux x86_64; nl-NL) Presto/2.8.319 Version/10.00	{}	2026-03-04 11:19:26-06	2026-03-04 11:19:26-06	\N
193	42	24	crear	perfil	permiso	39	t	Crear en módulo perfil	115.32.179.219	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8 rv:3.0; sl-SI) AppleWebKit/534.47.2 (KHTML, like Gecko) Version/5.0.4 Safari/534.47.2	{}	2026-03-21 11:37:26-06	2026-03-21 11:37:26-06	\N
194	37	13	ver	usuarios	usuario	45	t	Ver en módulo usuarios	24.96.134.233	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_9) AppleWebKit/533.0 (KHTML, like Gecko) Chrome/81.0.4313.45 Safari/533.0 Edg/81.01137.38	{}	2026-03-18 14:03:26-06	2026-03-18 14:03:26-06	\N
195	37	91	bloquear	perfil	usuario	48	t	Bloquear en módulo perfil	172.14.37.227	Mozilla/5.0 (X11; Linux i686; rv:5.0) Gecko/20190331 Firefox/37.0	{}	2026-03-16 23:34:26-06	2026-03-16 23:34:26-06	\N
196	37	101	editar	perfil	rol	3	t	Editar en módulo perfil	73.170.45.126	Mozilla/5.0 (Windows NT 4.0; en-US; rv:1.9.0.20) Gecko/20210313 Firefox/36.0	{}	2026-03-05 07:33:26-06	2026-03-05 07:33:26-06	\N
197	37	109	crear	tarjetas	tarjeta_universitaria	33	t	Crear en módulo tarjetas	223.101.171.49	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/4.1)	{}	2026-03-19 19:16:26-06	2026-03-19 19:16:26-06	\N
198	39	46	crear	tarjetas	pedido	30	t	Crear en módulo tarjetas	225.44.252.188	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 4.0; Trident/4.1)	{}	2026-03-21 12:49:26-06	2026-03-21 12:49:26-06	\N
199	10	73	ver	tarjetas	rol	23	t	Ver en módulo tarjetas	150.238.47.58	Opera/9.48 (Windows NT 6.0; en-US) Presto/2.12.193 Version/11.00	{}	2026-03-18 07:43:26-06	2026-03-18 07:43:26-06	\N
200	29	96	exportar	reportes	pedido	27	t	Exportar en módulo reportes	101.104.189.21	Mozilla/5.0 (Windows CE; en-US; rv:1.9.0.20) Gecko/20141103 Firefox/37.0	{}	2026-03-17 18:55:26-06	2026-03-17 18:55:26-06	\N
201	24	132	desbloquear	tarjetas	pedido	9	t	Desbloquear en módulo tarjetas	205.45.198.161	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5362 (KHTML, like Gecko) Chrome/40.0.814.0 Mobile Safari/5362	{}	2026-03-23 11:05:26-06	2026-03-23 11:05:26-06	\N
202	18	128	crear	tarjetas	usuario	2	t	Crear en módulo tarjetas	135.158.75.128	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/5.1)	{}	2026-03-19 21:53:26-06	2026-03-19 21:53:26-06	\N
203	7	132	eliminar	tarjetas	usuario	10	t	Eliminar en módulo tarjetas	196.64.35.68	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.0 (KHTML, like Gecko) Chrome/93.0.4434.15 Safari/535.0 EdgA/93.01103.35	{}	2026-02-25 02:39:26-06	2026-02-25 02:39:26-06	\N
204	31	29	exportar	seguridad	rol	8	t	Exportar en módulo seguridad	210.124.38.33	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/536.2 (KHTML, like Gecko) Chrome/83.0.4394.28 Safari/536.2 EdgA/83.01014.79	{}	2026-03-20 04:45:26-06	2026-03-20 04:45:26-06	\N
205	23	107	desbloquear	reportes	pedido	27	t	Desbloquear en módulo reportes	164.17.108.61	Mozilla/5.0 (iPhone; CPU iPhone OS 8_1_2 like Mac OS X; sl-SI) AppleWebKit/533.38.1 (KHTML, like Gecko) Version/3.0.5 Mobile/8B112 Safari/6533.38.1	{}	2026-03-22 21:06:26-06	2026-03-22 21:06:26-06	\N
206	21	89	editar	roles	usuario	6	t	Editar en módulo roles	89.183.120.85	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4 rv:6.0) Gecko/20160630 Firefox/37.0	{}	2026-03-13 09:47:26-06	2026-03-13 09:47:26-06	\N
207	12	108	exportar	permisos	usuario	39	t	Exportar en módulo permisos	81.98.127.229	Mozilla/5.0 (iPad; CPU OS 8_0_1 like Mac OS X; en-US) AppleWebKit/533.34.5 (KHTML, like Gecko) Version/3.0.5 Mobile/8B112 Safari/6533.34.5	{}	2026-02-27 23:27:26-06	2026-02-27 23:27:26-06	\N
208	22	112	bloquear	roles	pedido	5	t	Bloquear en módulo roles	112.24.233.17	Opera/9.55 (Windows 98; en-US) Presto/2.8.273 Version/11.00	{}	2026-03-18 09:36:26-06	2026-03-18 09:36:26-06	\N
209	25	94	bloquear	tarjetas	usuario	41	t	Bloquear en módulo tarjetas	78.141.33.157	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 6.1; Trident/4.0)	{}	2026-03-14 10:56:26-06	2026-03-14 10:56:26-06	\N
210	31	107	ver	perfil	usuario	39	t	Ver en módulo perfil	181.200.92.147	Opera/8.62 (Windows NT 6.2; en-US) Presto/2.9.306 Version/12.00	{}	2026-03-22 00:51:26-06	2026-03-22 00:51:26-06	\N
211	8	76	eliminar	tarjetas	permiso	38	t	Eliminar en módulo tarjetas	160.131.188.150	Mozilla/5.0 (X11; Linux i686; rv:5.0) Gecko/20230201 Firefox/36.0	{}	2026-03-19 22:09:26-06	2026-03-19 22:09:26-06	\N
212	25	116	exportar	roles	tarjeta_universitaria	32	t	Exportar en módulo roles	82.74.254.117	Mozilla/5.0 (Windows NT 4.0) AppleWebKit/536.2 (KHTML, like Gecko) Chrome/84.0.4802.48 Safari/536.2 Edg/84.01129.95	{}	2026-02-25 20:59:26-06	2026-02-25 20:59:26-06	\N
213	18	130	desbloquear	roles	usuario	9	t	Desbloquear en módulo roles	144.43.94.129	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_9 rv:3.0) Gecko/20141014 Firefox/37.0	{}	2026-03-18 19:35:26-06	2026-03-18 19:35:26-06	\N
214	16	31	bloquear	roles	rol	18	t	Bloquear en módulo roles	79.43.172.29	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_5) AppleWebKit/534.1 (KHTML, like Gecko) Chrome/97.0.4415.86 Safari/534.1 Edg/97.01086.76	{}	2026-02-24 03:19:26-06	2026-02-24 03:19:26-06	\N
215	32	62	editar	seguridad	permiso	44	t	Editar en módulo seguridad	1.176.243.241	Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/532.2 (KHTML, like Gecko) Version/15.0 EdgiOS/93.01082.18 Mobile/15E148 Safari/532.2	{}	2026-03-21 06:09:26-06	2026-03-21 06:09:26-06	\N
216	33	8	ver	roles	usuario	24	t	Ver en módulo roles	115.123.112.173	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_0) AppleWebKit/531.0 (KHTML, like Gecko) Chrome/93.0.4766.63 Safari/531.0 Edg/93.01019.38	{}	2026-03-21 00:08:26-06	2026-03-21 00:08:26-06	\N
217	5	77	desbloquear	permisos	permiso	10	t	Desbloquear en módulo permisos	60.30.43.184	Mozilla/5.0 (Windows 98; Win 9x 4.90) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/88.0.4418.98 Safari/532.0 Edg/88.01024.28	{}	2026-03-07 14:11:26-06	2026-03-07 14:11:26-06	\N
218	28	82	bloquear	tarjetas	rol	27	t	Bloquear en módulo tarjetas	154.0.36.163	Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/5.1)	{}	2026-03-22 07:30:26-06	2026-03-22 07:30:26-06	\N
219	12	22	desbloquear	permisos	usuario	7	t	Desbloquear en módulo permisos	93.38.150.231	Mozilla/5.0 (X11; Linux i686; rv:7.0) Gecko/20251230 Firefox/35.0	{}	2026-03-23 19:48:26-06	2026-03-23 19:48:26-06	\N
220	38	11	editar	seguridad	permiso	5	t	Editar en módulo seguridad	202.172.190.228	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.0; Trident/3.0)	{}	2026-03-22 20:32:26-06	2026-03-22 20:32:26-06	\N
221	32	106	editar	seguridad	permiso	1	t	Editar en módulo seguridad	3.171.10.211	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/3.0)	{}	2026-03-10 12:19:26-06	2026-03-10 12:19:26-06	\N
222	30	30	eliminar	bitacora	pedido	29	t	Eliminar en módulo bitacora	186.254.251.64	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4 rv:4.0; sl-SI) AppleWebKit/531.2.1 (KHTML, like Gecko) Version/4.0.3 Safari/531.2.1	{}	2026-03-23 11:47:26-06	2026-03-23 11:47:26-06	\N
223	11	35	desbloquear	seguridad	usuario	22	t	Desbloquear en módulo seguridad	149.137.94.134	Mozilla/5.0 (Windows NT 5.0; sl-SI; rv:1.9.2.20) Gecko/20181123 Firefox/36.0	{}	2026-03-18 13:48:26-06	2026-03-18 13:48:26-06	\N
224	20	117	editar	roles	permiso	13	t	Editar en módulo roles	22.245.16.243	Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/85.0.4641.64 Safari/535.2 Edg/85.01105.22	{}	2026-03-17 08:08:26-06	2026-03-17 08:08:26-06	\N
226	15	91	desbloquear	bitacora	rol	20	t	Desbloquear en módulo bitacora	75.98.209.27	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5320 (KHTML, like Gecko) Chrome/39.0.857.0 Mobile Safari/5320	{}	2026-03-03 19:57:26-06	2026-03-03 19:57:26-06	\N
227	19	72	exportar	permisos	usuario	6	t	Exportar en módulo permisos	152.47.253.232	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_1 rv:4.0; en-US) AppleWebKit/533.15.7 (KHTML, like Gecko) Version/4.1 Safari/533.15.7	{}	2026-03-02 17:01:26-06	2026-03-02 17:01:26-06	\N
228	30	75	ver	reportes	usuario	36	t	Ver en módulo reportes	155.46.82.145	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8 rv:3.0) Gecko/20140507 Firefox/36.0	{}	2026-03-04 11:20:26-06	2026-03-04 11:20:26-06	\N
229	41	39	desbloquear	usuarios	pedido	21	t	Desbloquear en módulo usuarios	53.101.181.249	Mozilla/5.0 (X11; Linux x86_64; rv:6.0) Gecko/20220810 Firefox/35.0	{}	2026-03-22 10:27:26-06	2026-03-22 10:27:26-06	\N
230	21	18	bloquear	reportes	permiso	7	f	Bloquear en módulo reportes	107.237.57.174	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.1)	{}	2026-02-22 04:36:26-06	2026-02-22 04:36:26-06	\N
231	17	134	eliminar	bitacora	rol	25	t	Eliminar en módulo bitacora	40.201.14.67	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_4) AppleWebKit/5341 (KHTML, like Gecko) Chrome/39.0.886.0 Mobile Safari/5341	{}	2026-03-17 11:29:26-06	2026-03-17 11:29:26-06	\N
232	5	62	editar	usuarios	permiso	46	t	Editar en módulo usuarios	207.133.160.198	Mozilla/5.0 (Windows NT 5.2) AppleWebKit/533.0 (KHTML, like Gecko) Chrome/93.0.4591.18 Safari/533.0 Edg/93.01142.51	{}	2026-03-16 17:46:26-06	2026-03-16 17:46:26-06	\N
233	27	66	bloquear	seguridad	permiso	41	f	Bloquear en módulo seguridad	215.203.186.196	Mozilla/5.0 (iPhone; CPU iPhone OS 13_2 like Mac OS X) AppleWebKit/536.0 (KHTML, like Gecko) Version/15.0 EdgiOS/83.01122.75 Mobile/15E148 Safari/536.0	{}	2026-03-16 09:29:26-06	2026-03-16 09:29:26-06	\N
234	26	11	bloquear	seguridad	rol	17	t	Bloquear en módulo seguridad	133.176.198.243	Mozilla/5.0 (iPad; CPU OS 7_0_2 like Mac OS X; nl-NL) AppleWebKit/534.25.2 (KHTML, like Gecko) Version/3.0.5 Mobile/8B116 Safari/6534.25.2	{}	2026-03-04 02:55:26-06	2026-03-04 02:55:26-06	\N
235	17	18	ver	roles	rol	2	t	Ver en módulo roles	78.62.187.25	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.01; Trident/4.1)	{}	2026-03-21 01:16:26-06	2026-03-21 01:16:26-06	\N
236	29	71	eliminar	seguridad	permiso	32	t	Eliminar en módulo seguridad	4.191.54.241	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_6) AppleWebKit/531.2 (KHTML, like Gecko) Chrome/80.0.4487.90 Safari/531.2 Edg/80.01088.33	{}	2026-02-22 01:46:26-06	2026-02-22 01:46:26-06	\N
237	31	74	desbloquear	roles	usuario	35	t	Desbloquear en módulo roles	80.251.125.3	Mozilla/5.0 (iPad; CPU OS 8_1_1 like Mac OS X; nl-NL) AppleWebKit/531.23.3 (KHTML, like Gecko) Version/3.0.5 Mobile/8B115 Safari/6531.23.3	{}	2026-03-17 23:00:26-06	2026-03-17 23:00:26-06	\N
238	28	118	crear	reportes	usuario	37	t	Crear en módulo reportes	96.206.139.77	Mozilla/5.0 (iPad; CPU OS 8_1_1 like Mac OS X; en-US) AppleWebKit/531.3.3 (KHTML, like Gecko) Version/3.0.5 Mobile/8B116 Safari/6531.3.3	{}	2026-03-20 09:41:26-06	2026-03-20 09:41:26-06	\N
239	27	101	eliminar	usuarios	permiso	39	t	Eliminar en módulo usuarios	23.190.80.8	Opera/8.98 (Windows NT 5.01; en-US) Presto/2.8.297 Version/11.00	{}	2026-03-03 11:36:26-06	2026-03-03 11:36:26-06	\N
240	23	22	eliminar	usuarios	pedido	38	t	Eliminar en módulo usuarios	141.227.151.60	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 4.0; Trident/3.1)	{}	2026-02-25 21:03:26-06	2026-02-25 21:03:26-06	\N
241	11	92	crear	roles	rol	30	t	Crear en módulo roles	164.229.44.232	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_7 rv:6.0) Gecko/20120904 Firefox/37.0	{}	2026-03-11 03:04:26-06	2026-03-11 03:04:26-06	\N
242	5	129	crear	reportes	usuario	16	f	Crear en módulo reportes	91.64.38.45	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.01; Trident/4.0)	{}	2026-03-20 00:03:26-06	2026-03-20 00:03:26-06	\N
243	23	16	eliminar	perfil	tarjeta_universitaria	4	t	Eliminar en módulo perfil	233.113.79.177	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3 rv:2.0; en-US) AppleWebKit/532.37.3 (KHTML, like Gecko) Version/4.0.2 Safari/532.37.3	{}	2026-03-23 14:58:26-06	2026-03-23 14:58:26-06	\N
244	18	59	exportar	seguridad	usuario	22	t	Exportar en módulo seguridad	104.200.72.178	Mozilla/5.0 (X11; Linux i686; rv:5.0) Gecko/20170523 Firefox/37.0	{}	2026-03-16 05:59:26-06	2026-03-16 05:59:26-06	\N
245	39	59	bloquear	reportes	tarjeta_universitaria	8	t	Bloquear en módulo reportes	122.44.232.179	Opera/9.85 (X11; Linux i686; nl-NL) Presto/2.10.240 Version/11.00	{}	2026-03-16 09:54:26-06	2026-03-16 09:54:26-06	\N
246	10	23	eliminar	perfil	rol	13	t	Eliminar en módulo perfil	160.11.102.243	Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/533.2 (KHTML, like Gecko) Version/15.0 EdgiOS/92.01094.24 Mobile/15E148 Safari/533.2	{}	2026-03-10 04:52:26-06	2026-03-10 04:52:26-06	\N
247	25	127	exportar	reportes	rol	35	t	Exportar en módulo reportes	197.232.79.222	Opera/9.35 (Windows NT 5.1; nl-NL) Presto/2.11.205 Version/12.00	{}	2026-03-09 02:33:26-06	2026-03-09 02:33:26-06	\N
248	25	51	editar	usuarios	pedido	29	t	Editar en módulo usuarios	172.228.227.210	Opera/8.52 (Windows NT 6.0; nl-NL) Presto/2.12.178 Version/11.00	{}	2026-03-09 19:40:26-06	2026-03-09 19:40:26-06	\N
249	6	53	desbloquear	perfil	usuario	47	t	Desbloquear en módulo perfil	145.182.236.80	Opera/9.79 (Windows 98; Win 9x 4.90; sl-SI) Presto/2.9.336 Version/11.00	{}	2026-03-22 14:27:26-06	2026-03-22 14:27:26-06	\N
250	37	70	exportar	permisos	pedido	36	t	Exportar en módulo permisos	97.58.123.249	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 4.0; Trident/4.1)	{}	2026-03-22 02:37:26-06	2026-03-22 02:37:26-06	\N
251	11	68	ver	tarjetas	pedido	47	t	Ver en módulo tarjetas	224.59.64.94	Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.01; Trident/4.1)	{}	2026-03-20 11:01:26-06	2026-03-20 11:01:26-06	\N
252	17	54	bloquear	bitacora	permiso	40	t	Bloquear en módulo bitacora	172.106.142.169	Mozilla/5.0 (Windows NT 5.01) AppleWebKit/5350 (KHTML, like Gecko) Chrome/37.0.804.0 Mobile Safari/5350	{}	2026-03-16 22:35:26-06	2026-03-16 22:35:26-06	\N
253	36	28	bloquear	seguridad	tarjeta_universitaria	15	t	Bloquear en módulo seguridad	37.241.128.6	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 5.01; Trident/5.1)	{}	2026-03-04 05:00:26-06	2026-03-04 05:00:26-06	\N
254	31	89	desbloquear	tarjetas	usuario	2	t	Desbloquear en módulo tarjetas	197.124.244.175	Opera/8.31 (X11; Linux i686; nl-NL) Presto/2.8.300 Version/11.00	{}	2026-02-26 01:34:26-06	2026-02-26 01:34:26-06	\N
255	23	31	crear	usuarios	usuario	13	t	Crear en módulo usuarios	210.231.187.10	Mozilla/5.0 (iPhone; CPU iPhone OS 13_0 like Mac OS X) AppleWebKit/535.2 (KHTML, like Gecko) Version/15.0 EdgiOS/91.01020.85 Mobile/15E148 Safari/535.2	{}	2026-03-10 22:57:26-06	2026-03-10 22:57:26-06	\N
256	6	1	crear	permisos	tarjeta_universitaria	12	t	Crear en módulo permisos	1.25.5.215	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5342 (KHTML, like Gecko) Chrome/37.0.841.0 Mobile Safari/5342	{}	2026-03-16 06:29:26-06	2026-03-16 06:29:26-06	\N
257	5	31	eliminar	tarjetas	tarjeta_universitaria	36	f	Eliminar en módulo tarjetas	189.139.128.119	Mozilla/5.0 (Windows NT 6.2; sl-SI; rv:1.9.0.20) Gecko/20180828 Firefox/35.0	{}	2026-03-21 02:45:26-06	2026-03-21 02:45:26-06	\N
258	37	36	ver	permisos	tarjeta_universitaria	2	t	Ver en módulo permisos	231.14.60.19	Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.0 (KHTML, like Gecko) Chrome/86.0.4262.97 Safari/537.0 EdgA/86.01058.35	{}	2026-03-20 05:04:26-06	2026-03-20 05:04:26-06	\N
259	23	56	desbloquear	permisos	tarjeta_universitaria	43	t	Desbloquear en módulo permisos	208.73.64.164	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5) AppleWebKit/5321 (KHTML, like Gecko) Chrome/37.0.864.0 Mobile Safari/5321	{}	2026-03-22 18:27:26-06	2026-03-22 18:27:26-06	\N
260	33	101	editar	bitacora	tarjeta_universitaria	22	t	Editar en módulo bitacora	7.140.131.126	Mozilla/5.0 (iPad; CPU OS 7_1_1 like Mac OS X; nl-NL) AppleWebKit/534.9.6 (KHTML, like Gecko) Version/3.0.5 Mobile/8B119 Safari/6534.9.6	{}	2026-03-23 04:18:26-06	2026-03-23 04:18:26-06	\N
261	14	11	crear	usuarios	permiso	28	t	Crear en módulo usuarios	155.157.59.118	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5350 (KHTML, like Gecko) Chrome/37.0.873.0 Mobile Safari/5350	{}	2026-03-08 09:32:26-06	2026-03-08 09:32:26-06	\N
262	29	122	crear	permisos	permiso	10	t	Crear en módulo permisos	171.5.135.168	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/3.0)	{}	2026-03-01 16:25:26-06	2026-03-01 16:25:26-06	\N
263	42	52	eliminar	seguridad	rol	17	t	Eliminar en módulo seguridad	92.11.36.159	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/532.2 (KHTML, like Gecko) Chrome/92.0.4293.97 Safari/532.2 EdgA/92.01071.87	{}	2026-03-12 04:19:26-06	2026-03-12 04:19:26-06	\N
264	36	40	eliminar	usuarios	pedido	49	t	Eliminar en módulo usuarios	71.154.206.153	Opera/9.55 (X11; Linux i686; sl-SI) Presto/2.9.324 Version/12.00	{}	2026-03-21 10:05:26-06	2026-03-21 10:05:26-06	\N
265	25	15	desbloquear	roles	pedido	42	t	Desbloquear en módulo roles	103.176.131.96	Mozilla/5.0 (iPhone; CPU iPhone OS 13_1 like Mac OS X) AppleWebKit/536.1 (KHTML, like Gecko) Version/15.0 EdgiOS/94.01099.67 Mobile/15E148 Safari/536.1	{}	2026-03-23 20:53:26-06	2026-03-23 20:53:26-06	\N
266	5	78	crear	reportes	pedido	41	t	Crear en módulo reportes	88.30.84.7	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_3 rv:6.0) Gecko/20160215 Firefox/36.0	{}	2026-03-22 05:26:26-06	2026-03-22 05:26:26-06	\N
267	36	51	bloquear	bitacora	permiso	24	t	Bloquear en módulo bitacora	138.33.188.234	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 5.01; Trident/3.0)	{}	2026-03-22 00:53:26-06	2026-03-22 00:53:26-06	\N
268	41	36	eliminar	seguridad	usuario	17	t	Eliminar en módulo seguridad	212.27.25.240	Mozilla/5.0 (Windows; U; Windows NT 6.2) AppleWebKit/531.28.7 (KHTML, like Gecko) Version/4.1 Safari/531.28.7	{}	2026-03-20 21:22:26-06	2026-03-20 21:22:26-06	\N
269	35	67	ver	roles	usuario	27	t	Ver en módulo roles	153.53.47.249	Mozilla/5.0 (X11; Linux i686; rv:7.0) Gecko/20130629 Firefox/35.0	{}	2026-03-23 05:08:26-06	2026-03-23 05:08:26-06	\N
270	15	100	desbloquear	seguridad	rol	9	t	Desbloquear en módulo seguridad	75.234.194.232	Mozilla/5.0 (iPhone; CPU iPhone OS 8_1_2 like Mac OS X; en-US) AppleWebKit/534.16.1 (KHTML, like Gecko) Version/4.0.5 Mobile/8B114 Safari/6534.16.1	{}	2026-03-17 22:50:26-06	2026-03-17 22:50:26-06	\N
271	38	4	ver	bitacora	usuario	19	t	Ver en módulo bitacora	40.108.246.251	Opera/9.87 (Windows 98; Win 9x 4.90; nl-NL) Presto/2.8.270 Version/12.00	{}	2026-03-18 10:45:26-06	2026-03-18 10:45:26-06	\N
272	21	125	crear	perfil	pedido	20	t	Crear en módulo perfil	213.25.56.42	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_0) AppleWebKit/534.1 (KHTML, like Gecko) Chrome/94.0.4130.98 Safari/534.1 Edg/94.01013.0	{}	2026-03-09 10:16:26-06	2026-03-09 10:16:26-06	\N
273	24	79	desbloquear	seguridad	permiso	13	t	Desbloquear en módulo seguridad	200.104.143.97	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.01; Trident/3.0)	{}	2026-03-20 09:59:26-06	2026-03-20 09:59:26-06	\N
274	14	15	bloquear	bitacora	tarjeta_universitaria	34	t	Bloquear en módulo bitacora	124.119.70.149	Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20181104 Firefox/35.0	{}	2026-03-16 01:43:26-06	2026-03-16 01:43:26-06	\N
275	21	102	desbloquear	reportes	usuario	31	t	Desbloquear en módulo reportes	32.194.84.18	Mozilla/5.0 (Windows 95; sl-SI; rv:1.9.1.20) Gecko/20161027 Firefox/36.0	{}	2026-03-22 23:43:26-06	2026-03-22 23:43:26-06	\N
276	4	102	desbloquear	permisos	tarjeta_universitaria	26	t	Desbloquear en módulo permisos	206.148.103.246	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_4 rv:3.0; nl-NL) AppleWebKit/535.19.6 (KHTML, like Gecko) Version/4.1 Safari/535.19.6	{}	2026-03-23 06:36:26-06	2026-03-23 06:36:26-06	\N
277	19	8	editar	usuarios	tarjeta_universitaria	31	t	Editar en módulo usuarios	48.212.233.243	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5332 (KHTML, like Gecko) Chrome/40.0.838.0 Mobile Safari/5332	{}	2026-03-22 22:25:26-06	2026-03-22 22:25:26-06	\N
278	29	62	exportar	bitacora	usuario	37	t	Exportar en módulo bitacora	18.247.93.78	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_2) AppleWebKit/537.2 (KHTML, like Gecko) Chrome/82.0.4114.37 Safari/537.2 Edg/82.01077.11	{}	2026-03-18 12:40:26-06	2026-03-18 12:40:26-06	\N
279	27	46	eliminar	perfil	usuario	14	t	Eliminar en módulo perfil	249.176.218.66	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5330 (KHTML, like Gecko) Chrome/36.0.842.0 Mobile Safari/5330	{}	2026-03-11 14:53:26-06	2026-03-11 14:53:26-06	\N
280	13	23	ver	perfil	pedido	35	t	Ver en módulo perfil	49.68.186.12	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_4 rv:2.0) Gecko/20100426 Firefox/35.0	{}	2026-03-12 05:13:26-06	2026-03-12 05:13:26-06	\N
281	16	53	bloquear	seguridad	usuario	46	t	Bloquear en módulo seguridad	102.64.225.162	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_7 rv:6.0; nl-NL) AppleWebKit/532.28.2 (KHTML, like Gecko) Version/4.1 Safari/532.28.2	{}	2026-03-18 08:59:26-06	2026-03-18 08:59:26-06	\N
282	29	66	crear	bitacora	rol	14	t	Crear en módulo bitacora	111.6.253.65	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5360 (KHTML, like Gecko) Chrome/38.0.823.0 Mobile Safari/5360	{}	2026-03-20 12:05:26-06	2026-03-20 12:05:26-06	\N
283	13	53	desbloquear	permisos	tarjeta_universitaria	19	t	Desbloquear en módulo permisos	216.168.6.148	Mozilla/5.0 (iPad; CPU OS 7_1_2 like Mac OS X; en-US) AppleWebKit/534.8.6 (KHTML, like Gecko) Version/4.0.5 Mobile/8B119 Safari/6534.8.6	{}	2026-03-22 09:58:26-06	2026-03-22 09:58:26-06	\N
284	22	117	bloquear	reportes	usuario	31	t	Bloquear en módulo reportes	24.51.69.114	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/3.1)	{}	2026-02-27 20:50:26-06	2026-02-27 20:50:26-06	\N
285	28	72	desbloquear	permisos	rol	48	f	Desbloquear en módulo permisos	124.30.221.6	Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/5.0)	{}	2026-03-07 22:43:26-06	2026-03-07 22:43:26-06	\N
286	31	11	editar	bitacora	pedido	7	t	Editar en módulo bitacora	79.170.128.15	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_5 rv:3.0; sl-SI) AppleWebKit/534.10.7 (KHTML, like Gecko) Version/4.0.3 Safari/534.10.7	{}	2026-03-16 05:36:26-06	2026-03-16 05:36:26-06	\N
287	25	118	editar	reportes	pedido	24	t	Editar en módulo reportes	151.159.14.51	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/4.1)	{}	2026-03-17 03:29:26-06	2026-03-17 03:29:26-06	\N
288	28	15	editar	roles	usuario	32	t	Editar en módulo roles	227.172.58.206	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5 rv:5.0) Gecko/20201129 Firefox/37.0	{}	2026-03-23 19:47:26-06	2026-03-23 19:47:26-06	\N
289	6	81	crear	perfil	pedido	9	t	Crear en módulo perfil	229.215.135.74	Mozilla/5.0 (Windows NT 6.2; nl-NL; rv:1.9.1.20) Gecko/20151031 Firefox/35.0	{}	2026-03-23 07:46:26-06	2026-03-23 07:46:26-06	\N
290	19	80	crear	seguridad	rol	43	t	Crear en módulo seguridad	47.210.159.110	Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_1 like Mac OS X; sl-SI) AppleWebKit/531.43.5 (KHTML, like Gecko) Version/4.0.5 Mobile/8B112 Safari/6531.43.5	{}	2026-03-16 08:56:26-06	2026-03-16 08:56:26-06	\N
291	13	114	exportar	usuarios	pedido	49	t	Exportar en módulo usuarios	190.100.198.235	Mozilla/5.0 (Windows 95; en-US; rv:1.9.1.20) Gecko/20180625 Firefox/37.0	{}	2026-03-22 08:55:26-06	2026-03-22 08:55:26-06	\N
292	17	24	crear	usuarios	rol	46	t	Crear en módulo usuarios	235.9.75.168	Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5351 (KHTML, like Gecko) Chrome/37.0.828.0 Mobile Safari/5351	{}	2026-03-17 14:46:26-06	2026-03-17 14:46:26-06	\N
293	41	130	bloquear	tarjetas	usuario	11	t	Bloquear en módulo tarjetas	248.157.29.76	Mozilla/5.0 (Windows NT 5.01) AppleWebKit/5320 (KHTML, like Gecko) Chrome/37.0.893.0 Mobile Safari/5320	{}	2026-02-27 05:54:26-06	2026-02-27 05:54:26-06	\N
294	7	20	crear	seguridad	rol	15	t	Crear en módulo seguridad	203.255.229.123	Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.2.20) Gecko/20181220 Firefox/36.0	{}	2026-03-20 19:29:26-06	2026-03-20 19:29:26-06	\N
295	27	19	editar	roles	rol	18	t	Editar en módulo roles	225.231.248.139	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 5.1; Trident/4.0)	{}	2026-03-23 01:24:26-06	2026-03-23 01:24:26-06	\N
296	25	75	editar	reportes	permiso	40	t	Editar en módulo reportes	136.121.186.211	Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.0)	{}	2026-03-21 01:39:26-06	2026-03-21 01:39:26-06	\N
297	13	53	eliminar	permisos	tarjeta_universitaria	26	t	Eliminar en módulo permisos	195.229.154.8	Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.01; Trident/3.0)	{}	2026-03-20 20:04:26-06	2026-03-20 20:04:26-06	\N
298	39	107	crear	tarjetas	rol	46	t	Crear en módulo tarjetas	230.220.79.167	Mozilla/5.0 (Windows; U; Windows 98; Win 9x 4.90) AppleWebKit/534.31.1 (KHTML, like Gecko) Version/5.0.5 Safari/534.31.1	{}	2026-03-07 16:36:26-06	2026-03-07 16:36:26-06	\N
299	19	126	desbloquear	bitacora	permiso	39	t	Desbloquear en módulo bitacora	126.107.166.197	Mozilla/5.0 (iPad; CPU OS 7_1_1 like Mac OS X; sl-SI) AppleWebKit/533.10.6 (KHTML, like Gecko) Version/3.0.5 Mobile/8B115 Safari/6533.10.6	{}	2026-03-16 00:45:26-06	2026-03-16 00:45:26-06	\N
300	26	66	exportar	tarjetas	pedido	26	f	Exportar en módulo tarjetas	120.228.158.105	Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_2 like Mac OS X; en-US) AppleWebKit/532.6.4 (KHTML, like Gecko) Version/3.0.5 Mobile/8B114 Safari/6532.6.4	{}	2026-03-23 18:16:26-06	2026-03-23 18:16:26-06	\N
\.


--
-- Data for Name: archivo; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.archivo (id, usuario_id, carpeta_id, nombre_original, nombre_almacenado, ruta, mime_type, extension, tamanio, visto_admin, visto_admin_at, visto_por, notas_admin, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: archivo_carpeta; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.archivo_carpeta (id, usuario_id, nombre, padre_id, ruta, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2026_02_03_234134_create_citext_extension	1
2	2026_02_03_234237_create_updated_at_function	1
3	2026_02_03_234305_create_rol_table	1
4	2026_02_03_234329_create_permiso_table	1
5	2026_02_04_001151_create_rol_permiso_table	1
6	2026_02_04_001216_create_usuario_table	1
7	2026_02_04_001237_create_usuario_perfil_table	1
8	2026_02_04_001306_create_usuario_rol_table	1
9	2026_02_04_001330_create_usuario_sesion_table	1
10	2026_02_04_001352_create_usuario_password_reset_table	1
11	2026_02_04_001656_create_acceso_bitacora_table	1
12	2026_02_04_001715_create_actividad_bitacora_table	1
13	2026_02_04_010946_create_cache_table	1
14	2026_02_04_053416_create_password_reset_tokens_table	1
15	2026_02_04_055455_add_remember_token_to_usuario_table	1
16	2026_02_05_000001_create_tarjeta_universitaria_table	1
17	2026_02_05_000002_create_tarjeta_lectura_table	1
18	2026_03_05_000001_add_pin_hash_to_tarjeta_universitaria	1
19	2026_03_09_000001_create_saldo_monedero_table	1
20	2026_03_09_000002_create_saldo_movimiento_table	1
21	2026_03_09_000003_create_pedido_table	1
22	2026_03_09_000004_add_pedido_id_to_tarjeta_lectura	1
23	2026_03_10_000005_create_archivos_tables	1
24	2026_03_24_223638_add_modulo_to_usuario_table	2
25	2026_03_25_232649_create_producto_table	3
26	2026_03_25_230001_create_tienda_table	4
27	2026_03_25_230002_add_tienda_to_usuario_table	4
28	2026_03_25_230003_add_tienda_to_producto_table	4
29	2026_03_25_230004_add_tienda_to_pedido_table	4
30	2026_03_27_234826_add_ubicacion_to_tienda_table	5
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: pedido; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.pedido (id, usuario_id, numero_folio, estado, modulo, total, descripcion, notas, operador_usuario_id, confirmado_con_tarjeta, confirmado_at, tarjeta_lectura_id, cobrado_de_saldo, saldo_movimiento_id, meta_json, created_at, updated_at, deleted_at, tienda_id, tipo_entrega, repartidor_id) FROM stdin;
1	41	PED-OF7IMATF	creado	biblioteca	297.38	Pedido en biblioteca		8	f	\N	\N	f	\N	{}	2026-03-04 17:06:27-06	2026-03-04 17:06:27-06	\N	\N	directo	\N
2	17	PED-MFCRCE8A	cancelado	biblioteca	246.24	Pedido en biblioteca		4	f	\N	\N	t	\N	{}	2026-03-15 23:06:27-06	2026-03-15 23:06:27-06	\N	\N	directo	\N
3	12	PED-3IKHSIVZ	listo	biblioteca	201.70	Pedido en biblioteca		5	t	2026-03-05 21:15:27	\N	t	\N	{}	2026-03-05 21:06:27-06	2026-03-05 21:06:27-06	\N	\N	directo	\N
4	7	PED-TU56HUVU	en_proceso	souvenirs	253.94	Pedido en souvenirs		4	f	\N	\N	f	\N	{}	2026-03-22 13:06:27-06	2026-03-22 13:06:27-06	\N	\N	directo	\N
5	15	PED-6EAFPSBW	listo	copias	201.52	Pedido en copias		4	t	2026-03-08 18:23:27	\N	t	\N	{}	2026-03-08 18:06:27-06	2026-03-08 18:06:27-06	\N	\N	directo	\N
6	33	PED-5BBJY1ZA	creado	biblioteca	202.54	Pedido en biblioteca		6	f	\N	\N	t	\N	{}	2026-03-04 20:06:27-06	2026-03-04 20:06:27-06	\N	\N	directo	\N
7	20	PED-PUEX7AD4	aceptado	acceso	26.83	Pedido en acceso		5	f	\N	\N	f	\N	{}	2026-02-22 18:06:27-06	2026-02-22 18:06:27-06	\N	\N	directo	\N
8	36	PED-BU2MJPRG	entregado	copias	142.17	Pedido en copias		8	t	2026-03-04 12:34:27	\N	f	\N	{}	2026-03-04 12:06:27-06	2026-03-04 12:06:27-06	\N	\N	directo	\N
9	33	PED-1BU8XOVR	listo	souvenirs	44.08	Pedido en souvenirs		7	t	2026-03-20 17:28:27	\N	t	\N	{}	2026-03-20 17:06:27-06	2026-03-20 17:06:27-06	\N	\N	directo	\N
10	39	PED-0JSWIY38	entregado	cafeteria	93.76	Pedido en cafeteria		8	t	2026-03-11 17:19:27	\N	f	\N	{}	2026-03-11 17:06:27-06	2026-03-11 17:06:27-06	\N	\N	directo	\N
11	24	PED-QGJCUNOB	listo	copias	211.79	Pedido en copias		4	t	2026-03-03 22:12:27	\N	f	\N	{}	2026-03-03 22:06:27-06	2026-03-03 22:06:27-06	\N	\N	directo	\N
12	15	PED-JPXJWWNN	creado	copias	219.20	Pedido en copias		6	f	\N	\N	t	\N	{}	2026-03-07 11:06:27-06	2026-03-07 11:06:27-06	\N	\N	directo	\N
13	22	PED-LUU26NNJ	cancelado	biblioteca	38.02	Pedido en biblioteca		4	f	\N	\N	t	\N	{}	2026-03-21 18:06:27-06	2026-03-21 18:06:27-06	\N	\N	directo	\N
14	32	PED-GXGF52Y7	creado	copias	112.64	Pedido en copias		7	f	\N	\N	f	\N	{}	2026-02-25 12:06:27-06	2026-02-25 12:06:27-06	\N	\N	directo	\N
15	33	PED-YJBNDMW8	creado	biblioteca	278.80	Pedido en biblioteca		6	f	\N	\N	t	\N	{}	2026-02-24 11:06:27-06	2026-02-24 11:06:27-06	\N	\N	directo	\N
16	25	PED-VIDTSJQH	creado	acceso	197.27	Pedido en acceso		5	f	\N	\N	t	\N	{}	2026-03-05 21:06:27-06	2026-03-05 21:06:27-06	\N	\N	directo	\N
17	32	PED-AGCRVY2V	creado	biblioteca	22.27	Pedido en biblioteca		6	f	\N	\N	f	\N	{}	2026-03-05 19:06:27-06	2026-03-05 19:06:27-06	\N	\N	directo	\N
18	12	PED-08FS1V26	entregado	biblioteca	245.36	Pedido en biblioteca		7	t	2026-03-22 16:13:27	\N	t	\N	{}	2026-03-22 16:06:27-06	2026-03-22 16:06:27-06	\N	\N	directo	\N
19	8	PED-K0AII4RV	creado	copias	292.03	Pedido en copias		7	f	\N	\N	t	\N	{}	2026-03-23 14:06:27-06	2026-03-23 14:06:27-06	\N	\N	directo	\N
20	19	PED-Q8VTNL7T	en_proceso	copias	41.64	Pedido en copias		5	f	\N	\N	f	\N	{}	2026-03-15 16:06:27-06	2026-03-15 16:06:27-06	\N	\N	directo	\N
21	33	PED-YKVHWHIS	aceptado	souvenirs	43.38	Pedido en souvenirs		5	f	\N	\N	t	\N	{}	2026-03-04 17:06:27-06	2026-03-04 17:06:27-06	\N	\N	directo	\N
22	6	PED-M5YJXJIX	entregado	copias	101.41	Pedido en copias		6	t	2026-03-14 16:24:27	\N	f	\N	{}	2026-03-14 16:06:27-06	2026-03-14 16:06:27-06	\N	\N	directo	\N
23	15	PED-JJPYJS2S	entregado	acceso	94.93	Pedido en acceso		4	t	2026-03-02 17:20:27	\N	f	\N	{}	2026-03-02 17:06:27-06	2026-03-02 17:06:27-06	\N	\N	directo	\N
24	30	PED-PSKS0RRP	entregado	biblioteca	55.45	Pedido en biblioteca		7	t	2026-02-25 21:31:27	\N	t	\N	{}	2026-02-25 21:06:27-06	2026-02-25 21:06:27-06	\N	\N	directo	\N
26	16	PED-FF3K66P4	listo	acceso	290.94	Pedido en acceso		4	t	2026-03-14 12:21:27	\N	t	\N	{}	2026-03-14 12:06:27-06	2026-03-14 12:06:27-06	\N	\N	directo	\N
27	13	PED-QJV7PCBD	entregado	souvenirs	138.25	Pedido en souvenirs		8	t	2026-03-04 21:16:27	\N	f	\N	{}	2026-03-04 21:06:27-06	2026-03-04 21:06:27-06	\N	\N	directo	\N
28	34	PED-EFQN9HTB	cancelado	biblioteca	285.86	Pedido en biblioteca		4	f	\N	\N	f	\N	{}	2026-03-08 18:06:27-06	2026-03-08 18:06:27-06	\N	\N	directo	\N
29	15	PED-N1OVFLFE	en_proceso	souvenirs	81.84	Pedido en souvenirs		5	f	\N	\N	f	\N	{}	2026-03-19 17:06:27-06	2026-03-19 17:06:27-06	\N	\N	directo	\N
30	29	PED-G1FAQCZJ	aceptado	copias	201.81	Pedido en copias		4	f	\N	\N	t	\N	{}	2026-03-12 16:06:27-06	2026-03-12 16:06:27-06	\N	\N	directo	\N
31	9	PED-LQNR6T1T	entregado	cafeteria	86.65	Pedido en cafeteria		7	t	2026-03-11 22:26:27	\N	t	\N	{}	2026-03-11 22:06:27-06	2026-03-11 22:06:27-06	\N	\N	directo	\N
32	23	PED-5B6CWIDM	creado	copias	54.01	Pedido en copias		5	f	\N	\N	f	\N	{}	2026-03-12 18:06:27-06	2026-03-12 18:06:27-06	\N	\N	directo	\N
33	6	PED-SSELJA0B	aceptado	cafeteria	286.99	Pedido en cafeteria		4	f	\N	\N	f	\N	{}	2026-03-09 11:06:27-06	2026-03-09 11:06:27-06	\N	\N	directo	\N
34	10	PED-2BMQLNBM	entregado	cafeteria	259.45	Pedido en cafeteria		8	t	2026-03-04 20:34:27	\N	f	\N	{}	2026-03-04 20:06:27-06	2026-03-04 20:06:27-06	\N	\N	directo	\N
35	31	PED-UMZPRYBF	listo	copias	177.64	Pedido en copias		6	t	2026-03-20 11:25:27	\N	f	\N	{}	2026-03-20 11:06:27-06	2026-03-20 11:06:27-06	\N	\N	directo	\N
36	13	PED-WNVZFZOX	creado	copias	113.33	Pedido en copias		5	f	\N	\N	t	\N	{}	2026-02-21 19:06:27-06	2026-02-21 19:06:27-06	\N	\N	directo	\N
38	8	PED-4UWT48HE	creado	acceso	183.65	Pedido en acceso		5	f	\N	\N	f	\N	{}	2026-02-21 21:06:27-06	2026-02-21 21:06:27-06	\N	\N	directo	\N
39	19	PED-UDRC3NGY	entregado	copias	81.37	Pedido en copias		5	t	2026-03-23 12:28:27	\N	f	\N	{}	2026-03-23 12:06:27-06	2026-03-23 12:06:27-06	\N	\N	directo	\N
40	37	PED-IF7WYH1G	entregado	cafeteria	259.83	Pedido en cafeteria		6	t	2026-03-22 20:33:27	\N	f	\N	{}	2026-03-22 20:06:27-06	2026-03-22 20:06:27-06	\N	\N	directo	\N
42	31	PED-CFW2D736	listo	biblioteca	133.84	Pedido en biblioteca		5	t	2026-03-19 11:22:27	\N	f	\N	{}	2026-03-19 11:06:27-06	2026-03-19 11:06:27-06	\N	\N	directo	\N
45	39	PED-AE9EBF04	en_proceso	souvenirs	283.54	Pedido en souvenirs		4	f	\N	\N	f	\N	{}	2026-03-06 21:06:27-06	2026-03-06 21:06:27-06	\N	\N	directo	\N
46	41	PED-N6DNDRG8	cancelado	biblioteca	276.12	Pedido en biblioteca		5	f	\N	\N	f	\N	{}	2026-03-14 12:06:27-06	2026-03-14 12:06:27-06	\N	\N	directo	\N
47	11	PED-OULYGU7H	aceptado	souvenirs	162.97	Pedido en souvenirs		5	f	\N	\N	t	\N	{}	2026-03-05 12:06:27-06	2026-03-05 12:06:27-06	\N	\N	directo	\N
48	10	PED-UFDS0SSL	cancelado	biblioteca	112.71	Pedido en biblioteca		8	f	\N	\N	f	\N	{}	2026-03-21 21:06:27-06	2026-03-21 21:06:27-06	\N	\N	directo	\N
50	26	PED-CFBDYPJ8	en_proceso	copias	111.42	Pedido en copias		4	f	\N	\N	f	\N	{}	2026-03-06 16:06:27-06	2026-03-06 16:06:27-06	\N	\N	directo	\N
51	37	PED-8WSP8N58	aceptado	copias	86.22	Pedido en copias		4	f	\N	\N	t	\N	{}	2026-03-20 12:06:27-06	2026-03-20 12:06:27-06	\N	\N	directo	\N
52	5	PED-EAOBTGCG	listo	copias	70.78	Pedido en copias		8	t	2026-03-07 18:18:27	\N	f	\N	{}	2026-03-07 18:06:27-06	2026-03-07 18:06:27-06	\N	\N	directo	\N
53	10	PED-2LJ7EBID	cancelado	biblioteca	148.07	Pedido en biblioteca		5	f	\N	\N	f	\N	{}	2026-03-08 18:06:27-06	2026-03-08 18:06:27-06	\N	\N	directo	\N
49	33	PED-ASLDXKKJ	aceptado	cafeteria	167.94	Pedido en cafeteria		5	f	\N	\N	t	\N	{}	2026-02-22 19:06:27-06	2026-03-24 17:20:06-06	\N	\N	directo	\N
44	11	PED-XPFAOLCJ	entregado	cafeteria	28.20	Pedido en cafeteria		4	t	2026-03-01 22:13:27	\N	f	\N	{}	2026-03-01 22:06:27-06	2026-03-25 17:33:29-06	\N	\N	directo	\N
37	29	PED-ND4ADVNV	entregado	cafeteria	35.97	Pedido en cafeteria		5	t	2026-03-03 17:26:27	\N	t	\N	{}	2026-03-03 17:06:27-06	2026-03-25 17:36:33-06	\N	\N	directo	\N
41	18	PED-Z8MKIDRK	entregado	cafeteria	33.86	Pedido en cafeteria		8	f	\N	\N	t	\N	{}	2026-03-03 22:06:27-06	2026-03-25 17:51:54-06	\N	\N	directo	\N
43	24	PED-0PMIWGDS	entregado	cafeteria	233.50	Pedido en cafeteria		6	f	\N	\N	f	\N	{}	2026-02-21 23:06:27-06	2026-03-25 17:52:46-06	\N	\N	directo	\N
54	36	PED-UG7MYGLJ	entregado	cafeteria	42.90	Pedido en cafeteria		4	t	2026-03-02 23:31:27	\N	f	\N	{}	2026-03-02 23:06:27-06	2026-03-02 23:06:27-06	\N	\N	directo	\N
56	20	PED-YTIUK4AU	aceptado	biblioteca	188.31	Pedido en biblioteca		4	f	\N	\N	t	\N	{}	2026-03-16 19:06:27-06	2026-03-16 19:06:27-06	\N	\N	directo	\N
57	33	PED-EYQPNQTG	listo	souvenirs	238.23	Pedido en souvenirs		8	t	2026-03-09 15:36:27	\N	f	\N	{}	2026-03-09 15:06:27-06	2026-03-09 15:06:27-06	\N	\N	directo	\N
59	20	PED-ANZBJ7RC	aceptado	copias	120.66	Pedido en copias		7	f	\N	\N	f	\N	{}	2026-02-28 11:06:27-06	2026-02-28 11:06:27-06	\N	\N	directo	\N
60	40	PED-UYDOYYAA	creado	biblioteca	179.58	Pedido en biblioteca		6	f	\N	\N	t	\N	{}	2026-03-06 21:06:27-06	2026-03-06 21:06:27-06	\N	\N	directo	\N
25	6	PED-6C5T3PRL	entregado	cafeteria	55.13	Pedido en cafeteria		8	t	2026-02-24 15:34:27	\N	t	\N	{}	2026-02-24 15:06:27-06	2026-03-24 17:14:58-06	\N	\N	directo	\N
61	1	PED-20260325-0001	entregado	cafeteria	110.00	1 Café Americano, 1 Chilaquiles		\N	f	2026-03-24 09:31:16	\N	f	\N	{}	2026-03-24 08:45:16-06	2026-03-25 23:31:16-06	\N	\N	directo	\N
63	1	DEMO-1774395098-1	entregado	cafeteria	110.00	1 Café Americano, 1 Chilaquiles	Sin cebolla	\N	f	2026-03-24 09:31:38	\N	f	\N	{"demo": true}	2026-03-24 08:45:38-06	\N	\N	\N	directo	\N
64	1	DEMO-1774481498-2	entregado	cafeteria	35.00	1 Café Americano		\N	f	2026-03-25 21:31:38	\N	f	\N	{"demo": true}	2026-03-25 20:31:38-06	\N	\N	\N	directo	\N
66	1	DEMO-1774481498-4	en_proceso	souvenirs	450.00	1 Sudadera Universitaria		\N	f	\N	\N	f	\N	{"demo": true}	2026-03-25 23:11:38-06	\N	\N	\N	directo	\N
55	14	PED-BPZTBE5L	aceptado	cafeteria	128.50	Pedido en cafeteria		5	f	\N	\N	t	\N	{}	2026-03-06 14:06:27-06	2026-03-25 17:32:52-06	\N	\N	directo	\N
67	16	PED-20260325-0004	creado	cafeteria	90.00	3 cafes	con azucar	\N	f	\N	\N	f	\N	{"manual": true, "created_by": 2}	2026-03-25 23:49:37-06	2026-03-25 23:49:37-06	\N	\N	directo	\N
69	13	PED-20260326-0001	creado	cafeteria	45.00	1 sandwich	sin lechuga	\N	f	\N	\N	f	\N	{"manual": true, "created_by": 2}	2026-03-26 00:01:10-06	2026-03-26 00:01:10-06	\N	\N	directo	\N
68	14	PED-20260325-0005	aceptado	cafeteria	75.00	1 torta	sin chile	\N	f	\N	\N	f	\N	{"manual": true, "created_by": 2}	2026-03-25 23:58:24-06	2026-03-25 18:01:19-06	\N	\N	directo	\N
65	1	DEMO-1774481498-3	en_proceso	cafeteria	45.00	1 Sándwich de Jamón		\N	f	\N	\N	f	\N	{"demo": true}	2026-03-25 23:21:38-06	2026-03-25 18:02:00-06	\N	\N	directo	\N
58	40	PED-YAVDHYDZ	entregado	cafeteria	208.61	Pedido en cafeteria		6	f	\N	\N	f	\N	{}	2026-03-14 16:06:27-06	2026-03-25 18:02:15-06	\N	\N	directo	\N
\.


--
-- Data for Name: permiso; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.permiso (id, clave, descripcion, activo, created_at, updated_at, deleted_at) FROM stdin;
15	audit.write	Registrar eventos en bitácora	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
17	wallet.read	Consultar saldo propio	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
18	wallet.read.any	Consultar saldo de cualquier usuario	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
19	wallet.charge	Registrar cargos al monedero	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
20	wallet.credit	Registrar abonos al monedero	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
21	wallet.rules.write	Configurar reglas de límites de saldo	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
22	wallet.history.read	Consultar historial de movimientos propio	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
23	wallet.history.any	Consultar historial de movimientos de cualquier usuario	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
24	report.wallet	Ver reportes de saldo y movimientos	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
25	catalog.read	Consultar catálogo de servicios y productos	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
26	catalog.write	Crear/editar servicios y productos	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
27	catalog.delete	Eliminar/desactivar productos del catálogo	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
28	catalog.price.write	Modificar precios del catálogo	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
29	report.catalog	Ver reportes del catálogo	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
30	cart.read	Consultar carrito propio	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
31	cart.write	Agregar/quitar items del carrito	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
32	checkout.execute	Confirmar checkout/compra	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
33	checkout.read.any	Consultar checkouts de todos los usuarios	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
34	report.checkout	Ver reportes de consumos y checkouts	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
35	order.read	Consultar pedidos propios	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
36	order.read.any	Consultar pedidos de cualquier usuario o área	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
37	order.write	Crear pedidos	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
38	order.status.write	Actualizar estado de pedidos	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
39	order.cancel	Cancelar pedidos	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
40	order.evidence.write	Registrar evidencia de entrega (folio/QR)	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
41	report.orders	Ver reportes de pedidos	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
42	ticket.read	Consultar tickets propios	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
43	ticket.read.any	Consultar todos los tickets del sistema	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
44	ticket.write	Crear tickets de servicio interno	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
45	ticket.assign	Asignar tickets a áreas o usuarios	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
46	ticket.status.write	Actualizar estado y prioridad de tickets	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
47	ticket.close	Cerrar tickets con evidencia	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
48	report.tickets	Ver reportes de tickets y tiempos de atención	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
49	reservation.read	Consultar reservas propias	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
50	reservation.read.any	Consultar todas las reservas del sistema	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
51	reservation.write	Crear reservas de recursos o turnos	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
52	reservation.cancel	Cancelar reservas	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
53	resource.write	Administrar recursos reservables (salas, labs, equipos)	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
54	report.reservations	Ver reportes de reservas y ocupación	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
55	topup.execute	Realizar recarga de saldo	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
56	topup.read	Consultar historial de recargas propias	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
57	topup.read.any	Consultar historial de recargas de todos los usuarios	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
58	payment.read	Consultar pagos y su estado	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
59	voucher.read	Consultar y descargar comprobantes propios	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
60	voucher.read.any	Consultar comprobantes de cualquier usuario	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
61	conciliation.read	Consultar conciliación de recargas	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
62	report.payments	Ver reportes de recargas y pagos	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
63	provider.orders.read	Ver pedidos entrantes del área propia	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
64	provider.orders.manage	Aceptar, rechazar y actualizar pedidos del área	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
65	provider.delivery	Confirmar entrega/consumo de pedidos	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
66	provider.wallet.read	Consultar saldo del usuario (solo lectura, en contexto de entrega)	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
67	report.provider	Ver reportes operativos del área proveedora	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
73	card.import	Importar tarjetas de forma masiva por CSV	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
75	file.read	Ver y descargar archivos propios	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
76	file.write	Subir archivos y crear carpetas	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
77	file.delete	Eliminar archivos y carpetas propios	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
3	user.write	Permiso user.write	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
13	user.delete	Permiso user.delete	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
14	user.block	Permiso user.block	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
2	user.show	Permiso user.show	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
4	role.read	Permiso role.read	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
6	role.write	Permiso role.write	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
7	role.delete	Permiso role.delete	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
5	role.show	Permiso role.show	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
8	permission.read	Permiso permission.read	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
10	permission.write	Permiso permission.write	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
11	permission.delete	Permiso permission.delete	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
9	permission.show	Permiso permission.show	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
68	card.read	Permiso card.read	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
69	card.read.any	Permiso card.read.any	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
70	card.write	Permiso card.write	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
71	card.block	Permiso card.block	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
72	card.auth	Permiso card.auth	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
12	audit.read	Permiso audit.read	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
16	report.users	Permiso report.users	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
74	report.cards	Permiso report.cards	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
78	file.read.any	Ver archivos de cualquier usuario	t	2026-03-23 17:06:13-06	2026-03-23 17:06:13-06	\N
79	file.delete.any	Eliminar archivos de cualquier usuario	t	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
80	file.admin	Marcar como visto y agregar notas admin	t	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
1	user.read	Permiso user.read	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
\.


--
-- Data for Name: producto; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.producto (id, nombre, descripcion, precio, stock, modulo, activo, imagen_url, created_at, updated_at, deleted_at, tienda_id) FROM stdin;
1	Café Americano	Café de grano recién molido, 12oz.	35.00	50	cafeteria	t	\N	2026-03-25 23:31:16-06	2026-03-25 23:31:16-06	\N	\N
2	Chilaquiles Verdes	Con pollo, crema, queso y cebolla.	75.00	20	cafeteria	t	\N	2026-03-25 23:31:16-06	2026-03-25 23:31:16-06	\N	\N
4	Sudadera Universitaria	Sudadera azul marino con logo bordado. Talla M.	450.00	10	souvenirs	t	\N	2026-03-25 23:31:16-06	2026-03-25 23:31:16-06	\N	\N
5	Termo Metálico	Acero inoxidable, mantiene calor por 12hrs.	280.00	30	souvenirs	t	\N	2026-03-25 23:31:16-06	2026-03-25 23:31:16-06	\N	\N
6	cocacola	cocacola 6oo ml	25.00	90	cafeteria	t	\N	2026-03-25 23:47:07-06	2026-03-25 23:47:07-06	\N	\N
3	Sándwich de Jamón	Pan integral, jamón de pavo, lechuga y tomate.	40.00	15	cafeteria	t	\N	2026-03-25 23:31:16-06	2026-03-25 17:47:22-06	\N	\N
\.


--
-- Data for Name: rol; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.rol (id, nombre, descripcion, activo, created_at, updated_at, deleted_at) FROM stdin;
3	administrador	Acceso total al sistema	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
1	estudiante	Acceso básico de estudiante	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
2	proveedor_area	Proveedor de área de servicio	t	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
4	docente	Personal docente	t	2026-03-23 23:06:15-06	2026-03-23 23:06:15-06	\N
5	repartidor	Personal encargado de la entrega de pedidos a domicilio o puntos de entrega.	t	2026-03-27 19:58:59-06	2026-03-27 19:58:59-06	\N
6	encargado_tienda	Encargado responsable de la operación, pedidos e inventario de un negocio específico.	t	2026-03-27 20:22:39-06	2026-03-27 20:22:39-06	\N
\.


--
-- Data for Name: rol_permiso; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.rol_permiso (id, rol_id, permiso_id, created_at, updated_at, deleted_at) FROM stdin;
81	2	1	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
82	2	12	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
83	2	25	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
84	2	42	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
85	2	44	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
86	2	46	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
87	2	47	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
88	2	63	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
89	2	64	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
90	2	65	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
91	2	66	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
92	2	67	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
93	2	75	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
94	2	76	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
95	2	77	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
96	1	1	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
97	1	17	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
98	1	22	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
99	1	25	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
100	1	30	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
101	1	31	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
102	1	32	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
103	1	35	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
104	1	37	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
105	1	39	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
106	1	42	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
107	1	44	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
108	1	49	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
109	1	51	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
110	1	52	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
111	1	55	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
112	1	56	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
113	1	59	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
115	1	75	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
116	1	76	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
117	1	77	2026-03-23 17:06:14-06	2026-03-23 17:06:14-06	\N
15	3	15	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
17	3	17	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
18	3	18	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
19	3	19	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
20	3	20	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
21	3	21	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
22	3	22	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
23	3	23	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
24	3	24	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
25	3	25	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
26	3	26	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
27	3	27	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
28	3	28	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
29	3	29	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
30	3	30	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
31	3	31	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
32	3	32	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
33	3	33	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
34	3	34	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
35	3	35	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
36	3	36	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
37	3	37	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
38	3	38	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
39	3	39	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
40	3	40	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
41	3	41	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
42	3	42	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
43	3	43	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
44	3	44	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
45	3	45	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
46	3	46	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
47	3	47	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
48	3	48	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
49	3	49	2026-03-23 23:06:15-06	2026-03-23 17:06:16-06	\N
50	3	50	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
51	3	51	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
52	3	52	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
54	3	54	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
55	3	55	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
56	3	56	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
57	3	57	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
58	3	58	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
59	3	59	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
60	3	60	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
61	3	61	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
62	3	62	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
63	3	63	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
64	3	64	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
65	3	65	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
66	3	66	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
67	3	67	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
73	3	73	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
75	3	75	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
77	3	77	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
3	3	3	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
13	3	13	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
14	3	14	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
2	3	2	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
4	3	4	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
6	3	6	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
7	3	7	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
5	3	5	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
8	3	8	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
10	3	10	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
11	3	11	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
9	3	9	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
68	3	68	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
69	3	69	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
70	3	70	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
72	3	72	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
12	3	12	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
16	3	16	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
74	3	74	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
78	3	78	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
79	3	79	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
80	3	80	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
1	3	1	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
114	1	68	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
53	3	53	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
76	3	76	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
71	3	71	2026-03-23 23:06:16-06	2026-03-23 17:06:16-06	\N
118	1	72	2026-03-23 23:06:16-06	2026-03-23 23:06:16-06	\N
\.


--
-- Data for Name: saldo_monedero; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.saldo_monedero (id, usuario_id, saldo_disponible, saldo_retenido, created_at, updated_at, deleted_at) FROM stdin;
1	4	200.16	0.00	2026-02-08 23:06:27-06	2026-03-23 17:06:27-06	\N
2	5	57.21	0.00	2026-01-04 23:06:27-06	2026-03-23 17:06:27-06	\N
3	6	125.95	0.00	2026-03-09 23:06:27-06	2026-03-23 17:06:27-06	\N
4	7	22.87	0.00	2026-01-10 23:06:27-06	2026-03-23 17:06:27-06	\N
5	8	378.78	0.00	2026-03-07 23:06:27-06	2026-03-23 17:06:27-06	\N
6	9	40.46	0.00	2025-12-26 23:06:27-06	2026-03-23 17:06:27-06	\N
7	10	239.22	0.00	2025-12-28 23:06:27-06	2026-03-23 17:06:27-06	\N
8	11	191.98	0.00	2026-02-14 23:06:27-06	2026-03-23 17:06:27-06	\N
9	12	482.59	0.00	2026-01-08 23:06:27-06	2026-03-23 17:06:27-06	\N
10	13	333.91	0.00	2026-01-02 23:06:27-06	2026-03-23 17:06:27-06	\N
11	14	280.05	0.00	2026-02-08 23:06:27-06	2026-03-23 17:06:27-06	\N
12	15	378.32	0.00	2026-03-14 23:06:27-06	2026-03-23 17:06:27-06	\N
13	16	108.06	0.00	2026-01-19 23:06:27-06	2026-03-23 17:06:27-06	\N
14	17	476.41	0.00	2026-01-16 23:06:27-06	2026-03-23 17:06:27-06	\N
15	18	153.05	0.00	2026-01-10 23:06:27-06	2026-03-23 17:06:27-06	\N
16	19	26.32	0.00	2026-02-24 23:06:27-06	2026-03-23 17:06:27-06	\N
17	20	445.39	0.00	2026-01-26 23:06:27-06	2026-03-23 17:06:27-06	\N
18	21	269.69	0.00	2025-12-26 23:06:27-06	2026-03-23 17:06:27-06	\N
19	22	672.67	0.00	2026-01-31 23:06:27-06	2026-03-23 17:06:27-06	\N
20	23	307.29	0.00	2026-03-02 23:06:27-06	2026-03-23 17:06:27-06	\N
21	24	155.03	0.00	2026-02-28 23:06:27-06	2026-03-23 17:06:27-06	\N
22	25	365.70	0.00	2026-02-21 23:06:27-06	2026-03-23 17:06:27-06	\N
23	26	705.11	0.00	2026-01-03 23:06:27-06	2026-03-23 17:06:27-06	\N
24	27	195.52	0.00	2026-02-15 23:06:27-06	2026-03-23 17:06:27-06	\N
25	28	76.28	0.00	2026-01-12 23:06:27-06	2026-03-23 17:06:27-06	\N
26	29	179.70	0.00	2026-01-29 23:06:27-06	2026-03-23 17:06:27-06	\N
27	30	234.16	0.00	2025-12-31 23:06:27-06	2026-03-23 17:06:27-06	\N
28	31	98.27	0.00	2026-02-21 23:06:27-06	2026-03-23 17:06:27-06	\N
29	32	382.31	0.00	2026-01-01 23:06:27-06	2026-03-23 17:06:27-06	\N
30	33	127.94	0.00	2025-12-25 23:06:27-06	2026-03-23 17:06:27-06	\N
31	34	47.50	0.00	2026-02-06 23:06:27-06	2026-03-23 17:06:27-06	\N
32	35	61.42	0.00	2026-01-14 23:06:27-06	2026-03-23 17:06:27-06	\N
33	36	21.41	0.00	2025-12-23 23:06:27-06	2026-03-23 17:06:27-06	\N
34	37	348.83	0.00	2026-02-14 23:06:27-06	2026-03-23 17:06:27-06	\N
35	38	156.87	0.00	2026-01-18 23:06:27-06	2026-03-23 17:06:27-06	\N
36	39	18.59	0.00	2026-02-15 23:06:27-06	2026-03-23 17:06:27-06	\N
37	40	128.85	0.00	2026-01-27 23:06:27-06	2026-03-23 17:06:27-06	\N
38	41	247.68	0.00	2026-02-08 23:06:27-06	2026-03-23 17:06:27-06	\N
39	42	119.44	0.00	2026-02-02 23:06:27-06	2026-03-23 17:06:27-06	\N
\.


--
-- Data for Name: saldo_movimiento; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.saldo_movimiento (id, usuario_id, saldo_monedero_id, tipo, monto, saldo_anterior, saldo_nuevo, modulo, concepto, referencia_tabla, referencia_id, operador_usuario_id, tarjeta_lectura_id, meta_json, created_at, updated_at, deleted_at) FROM stdin;
1	4	1	abono	79.67	0.00	79.67	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-04 23:06:27-06	2026-03-04 23:06:27-06	\N
2	4	1	abono	180.96	79.67	260.63	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-17 23:06:27-06	2026-03-17 23:06:27-06	\N
3	4	1	cargo	90.12	260.63	170.51	acceso	Consumo en acceso	\N	\N	\N	\N	{}	2026-03-19 23:06:27-06	2026-03-19 23:06:27-06	\N
4	4	1	abono	29.65	170.51	200.16	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-24 23:06:27-06	2026-02-24 23:06:27-06	\N
5	5	2	abono	101.52	0.00	101.52	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-02 23:06:27-06	2026-03-02 23:06:27-06	\N
6	5	2	cargo	11.87	101.52	89.65	souvenirs	Consumo en acceso	\N	\N	\N	\N	{}	2026-03-10 23:06:27-06	2026-03-10 23:06:27-06	\N
7	5	2	abono	121.60	89.65	211.25	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
8	5	2	cargo	154.04	211.25	57.21	souvenirs	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-03-11 23:06:27-06	2026-03-11 23:06:27-06	\N
9	6	3	abono	20.31	0.00	20.31	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-07 23:06:27-06	2026-03-07 23:06:27-06	\N
10	6	3	abono	67.94	20.31	88.25	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-26 23:06:27-06	2026-02-26 23:06:27-06	\N
11	6	3	abono	72.82	88.25	161.07	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-25 23:06:27-06	2026-02-25 23:06:27-06	\N
12	6	3	cargo	35.12	161.07	125.95	acceso	Consumo en acceso	\N	\N	\N	\N	{}	2026-03-02 23:06:27-06	2026-03-02 23:06:27-06	\N
13	7	4	abono	189.82	0.00	189.82	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-20 23:06:27-06	2026-03-20 23:06:27-06	\N
14	7	4	cargo	136.64	189.82	53.18	souvenirs	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-22 23:06:27-06	2026-03-22 23:06:27-06	\N
15	7	4	cargo	43.77	53.18	9.41	souvenirs	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-02-25 23:06:27-06	2026-02-25 23:06:27-06	\N
16	7	4	abono	13.46	9.41	22.87	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-22 23:06:27-06	2026-02-22 23:06:27-06	\N
17	8	5	abono	183.94	0.00	183.94	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-26 23:06:27-06	2026-02-26 23:06:27-06	\N
18	8	5	cargo	182.85	183.94	1.09	biblioteca	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-13 23:06:27-06	2026-03-13 23:06:27-06	\N
19	8	5	abono	104.29	1.09	105.38	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-22 23:06:27-06	2026-02-22 23:06:27-06	\N
20	8	5	cargo	40.47	105.38	64.91	biblioteca	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-03 23:06:27-06	2026-03-03 23:06:27-06	\N
21	8	5	abono	130.68	64.91	195.59	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-02 23:06:27-06	2026-03-02 23:06:27-06	\N
22	8	5	abono	183.19	195.59	378.78	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
23	9	6	abono	145.47	0.00	145.47	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-07 23:06:27-06	2026-03-07 23:06:27-06	\N
24	9	6	cargo	13.62	145.47	131.85	copias	Consumo en copias	\N	\N	\N	\N	{}	2026-03-02 23:06:27-06	2026-03-02 23:06:27-06	\N
25	9	6	cargo	91.39	131.85	40.46	cafeteria	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-03-11 23:06:27-06	2026-03-11 23:06:27-06	\N
26	10	7	abono	158.15	0.00	158.15	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-08 23:06:27-06	2026-03-08 23:06:27-06	\N
27	10	7	abono	56.63	158.15	214.78	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-10 23:06:27-06	2026-03-10 23:06:27-06	\N
28	10	7	abono	61.91	214.78	276.69	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-18 23:06:27-06	2026-03-18 23:06:27-06	\N
29	10	7	abono	100.70	276.69	377.39	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-02 23:06:27-06	2026-03-02 23:06:27-06	\N
30	10	7	cargo	181.00	377.39	196.39	cafeteria	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-03-15 23:06:27-06	2026-03-15 23:06:27-06	\N
31	10	7	cargo	138.54	196.39	57.85	souvenirs	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-03-08 23:06:27-06	2026-03-08 23:06:27-06	\N
32	10	7	abono	181.37	57.85	239.22	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-22 23:06:27-06	2026-03-22 23:06:27-06	\N
33	11	8	abono	199.79	0.00	199.79	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-09 23:06:27-06	2026-03-09 23:06:27-06	\N
34	11	8	abono	76.45	199.79	276.24	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-21 23:06:27-06	2026-03-21 23:06:27-06	\N
35	11	8	abono	30.82	276.24	307.06	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-01 23:06:27-06	2026-03-01 23:06:27-06	\N
36	11	8	cargo	141.21	307.06	165.85	souvenirs	Consumo en copias	\N	\N	\N	\N	{}	2026-03-08 23:06:27-06	2026-03-08 23:06:27-06	\N
37	11	8	abono	12.17	165.85	178.02	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-07 23:06:27-06	2026-03-07 23:06:27-06	\N
38	11	8	abono	13.96	178.02	191.98	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-13 23:06:27-06	2026-03-13 23:06:27-06	\N
39	12	9	abono	197.96	0.00	197.96	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-15 23:06:27-06	2026-03-15 23:06:27-06	\N
40	12	9	abono	115.64	197.96	313.60	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
41	12	9	abono	32.83	313.60	346.43	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-21 23:06:27-06	2026-03-21 23:06:27-06	\N
42	12	9	cargo	198.48	346.43	147.95	souvenirs	Consumo en acceso	\N	\N	\N	\N	{}	2026-02-28 23:06:27-06	2026-02-28 23:06:27-06	\N
43	12	9	abono	59.25	147.95	207.20	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-21 23:06:27-06	2026-03-21 23:06:27-06	\N
44	12	9	abono	52.91	207.20	260.11	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-26 23:06:27-06	2026-02-26 23:06:27-06	\N
45	12	9	abono	170.50	260.11	430.61	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-21 23:06:27-06	2026-02-21 23:06:27-06	\N
46	12	9	abono	51.98	430.61	482.59	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-20 23:06:27-06	2026-03-20 23:06:27-06	\N
47	13	10	abono	70.13	0.00	70.13	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-15 23:06:27-06	2026-03-15 23:06:27-06	\N
48	13	10	abono	183.23	70.13	253.36	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-05 23:06:27-06	2026-03-05 23:06:27-06	\N
49	13	10	abono	46.30	253.36	299.66	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-16 23:06:27-06	2026-03-16 23:06:27-06	\N
50	13	10	abono	34.25	299.66	333.91	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-21 23:06:27-06	2026-03-21 23:06:27-06	\N
51	14	11	abono	20.34	0.00	20.34	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-02 23:06:27-06	2026-03-02 23:06:27-06	\N
52	14	11	abono	86.15	20.34	106.49	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-21 23:06:27-06	2026-03-21 23:06:27-06	\N
53	14	11	abono	50.95	106.49	157.44	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-20 23:06:27-06	2026-03-20 23:06:27-06	\N
54	14	11	cargo	76.58	157.44	80.86	copias	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-03-04 23:06:27-06	2026-03-04 23:06:27-06	\N
55	14	11	abono	199.19	80.86	280.05	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-06 23:06:27-06	2026-03-06 23:06:27-06	\N
56	15	12	abono	58.37	0.00	58.37	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-21 23:06:27-06	2026-02-21 23:06:27-06	\N
57	15	12	cargo	49.25	58.37	9.12	copias	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-02-27 23:06:27-06	2026-02-27 23:06:27-06	\N
58	15	12	abono	109.02	9.12	118.14	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-06 23:06:27-06	2026-03-06 23:06:27-06	\N
59	15	12	abono	128.90	118.14	247.04	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-21 23:06:27-06	2026-02-21 23:06:27-06	\N
60	15	12	abono	48.38	247.04	295.42	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-22 23:06:27-06	2026-03-22 23:06:27-06	\N
61	15	12	abono	82.90	295.42	378.32	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-20 23:06:27-06	2026-03-20 23:06:27-06	\N
62	16	13	abono	111.15	0.00	111.15	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-08 23:06:27-06	2026-03-08 23:06:27-06	\N
63	16	13	cargo	102.22	111.15	8.93	biblioteca	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-02-28 23:06:27-06	2026-02-28 23:06:27-06	\N
64	16	13	abono	196.64	8.93	205.57	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-23 23:06:27-06	2026-03-23 23:06:27-06	\N
65	16	13	cargo	159.37	205.57	46.20	copias	Consumo en acceso	\N	\N	\N	\N	{}	2026-03-03 23:06:27-06	2026-03-03 23:06:27-06	\N
66	16	13	abono	61.86	46.20	108.06	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-21 23:06:27-06	2026-02-21 23:06:27-06	\N
67	17	14	abono	183.35	0.00	183.35	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-19 23:06:27-06	2026-03-19 23:06:27-06	\N
68	17	14	cargo	89.64	183.35	93.71	souvenirs	Consumo en acceso	\N	\N	\N	\N	{}	2026-02-27 23:06:27-06	2026-02-27 23:06:27-06	\N
69	17	14	abono	12.19	93.71	105.90	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-08 23:06:27-06	2026-03-08 23:06:27-06	\N
70	17	14	abono	42.20	105.90	148.10	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-18 23:06:27-06	2026-03-18 23:06:27-06	\N
71	17	14	abono	170.51	148.10	318.61	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-01 23:06:27-06	2026-03-01 23:06:27-06	\N
72	17	14	abono	69.11	318.61	387.72	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-21 23:06:27-06	2026-03-21 23:06:27-06	\N
73	17	14	abono	63.47	387.72	451.19	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
74	17	14	abono	25.22	451.19	476.41	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-25 23:06:27-06	2026-02-25 23:06:27-06	\N
75	18	15	abono	117.10	0.00	117.10	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-21 23:06:27-06	2026-02-21 23:06:27-06	\N
76	18	15	abono	117.72	117.10	234.82	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-09 23:06:27-06	2026-03-09 23:06:27-06	\N
77	18	15	abono	83.58	234.82	318.40	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
78	18	15	cargo	83.89	318.40	234.51	biblioteca	Consumo en copias	\N	\N	\N	\N	{}	2026-03-03 23:06:27-06	2026-03-03 23:06:27-06	\N
79	18	15	cargo	28.53	234.51	205.98	cafeteria	Consumo en copias	\N	\N	\N	\N	{}	2026-03-14 23:06:27-06	2026-03-14 23:06:27-06	\N
80	18	15	cargo	193.78	205.98	12.20	biblioteca	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-03-13 23:06:27-06	2026-03-13 23:06:27-06	\N
81	18	15	abono	63.44	12.20	75.64	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-27 23:06:27-06	2026-02-27 23:06:27-06	\N
82	18	15	abono	77.41	75.64	153.05	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-28 23:06:27-06	2026-02-28 23:06:27-06	\N
83	19	16	abono	28.04	0.00	28.04	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-17 23:06:27-06	2026-03-17 23:06:27-06	\N
84	19	16	abono	161.76	28.04	189.80	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-05 23:06:27-06	2026-03-05 23:06:27-06	\N
85	19	16	abono	20.66	189.80	210.46	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-03 23:06:27-06	2026-03-03 23:06:27-06	\N
86	19	16	cargo	184.14	210.46	26.32	acceso	Consumo en acceso	\N	\N	\N	\N	{}	2026-03-01 23:06:27-06	2026-03-01 23:06:27-06	\N
87	20	17	abono	127.40	0.00	127.40	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-22 23:06:27-06	2026-02-22 23:06:27-06	\N
88	20	17	abono	61.43	127.40	188.83	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-16 23:06:27-06	2026-03-16 23:06:27-06	\N
89	20	17	abono	118.72	188.83	307.55	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-08 23:06:27-06	2026-03-08 23:06:27-06	\N
90	20	17	cargo	43.29	307.55	264.26	copias	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
91	20	17	abono	181.13	264.26	445.39	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-02 23:06:27-06	2026-03-02 23:06:27-06	\N
92	21	18	abono	65.31	0.00	65.31	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-23 23:06:27-06	2026-02-23 23:06:27-06	\N
93	21	18	abono	98.60	65.31	163.91	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-14 23:06:27-06	2026-03-14 23:06:27-06	\N
94	21	18	abono	105.78	163.91	269.69	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-23 23:06:27-06	2026-02-23 23:06:27-06	\N
95	22	19	abono	181.36	0.00	181.36	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-08 23:06:27-06	2026-03-08 23:06:27-06	\N
96	22	19	abono	156.89	181.36	338.25	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-21 23:06:27-06	2026-02-21 23:06:27-06	\N
97	22	19	abono	101.02	338.25	439.27	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-04 23:06:27-06	2026-03-04 23:06:27-06	\N
98	22	19	abono	37.24	439.27	476.51	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-11 23:06:27-06	2026-03-11 23:06:27-06	\N
99	22	19	abono	196.16	476.51	672.67	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-17 23:06:27-06	2026-03-17 23:06:27-06	\N
100	23	20	abono	101.00	0.00	101.00	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-15 23:06:27-06	2026-03-15 23:06:27-06	\N
101	23	20	abono	133.26	101.00	234.26	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-09 23:06:27-06	2026-03-09 23:06:27-06	\N
102	23	20	abono	165.23	234.26	399.49	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-23 23:06:27-06	2026-03-23 23:06:27-06	\N
103	23	20	cargo	143.30	399.49	256.19	souvenirs	Consumo en acceso	\N	\N	\N	\N	{}	2026-03-19 23:06:27-06	2026-03-19 23:06:27-06	\N
104	23	20	abono	193.24	256.19	449.43	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-22 23:06:27-06	2026-03-22 23:06:27-06	\N
105	23	20	cargo	142.14	449.43	307.29	acceso	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-03-04 23:06:27-06	2026-03-04 23:06:27-06	\N
106	24	21	abono	76.01	0.00	76.01	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-20 23:06:27-06	2026-03-20 23:06:27-06	\N
107	24	21	abono	133.94	76.01	209.95	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-07 23:06:27-06	2026-03-07 23:06:27-06	\N
108	24	21	cargo	114.88	209.95	95.07	acceso	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-21 23:06:27-06	2026-03-21 23:06:27-06	\N
109	24	21	abono	123.08	95.07	218.15	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-10 23:06:27-06	2026-03-10 23:06:27-06	\N
110	24	21	cargo	81.04	218.15	137.11	biblioteca	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-10 23:06:27-06	2026-03-10 23:06:27-06	\N
111	24	21	cargo	129.17	137.11	7.94	souvenirs	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-02-25 23:06:27-06	2026-02-25 23:06:27-06	\N
112	24	21	abono	21.19	7.94	29.13	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-14 23:06:27-06	2026-03-14 23:06:27-06	\N
113	24	21	abono	125.90	29.13	155.03	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-09 23:06:27-06	2026-03-09 23:06:27-06	\N
114	25	22	abono	148.35	0.00	148.35	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-25 23:06:27-06	2026-02-25 23:06:27-06	\N
115	25	22	cargo	36.51	148.35	111.84	biblioteca	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
116	25	22	abono	193.96	111.84	305.80	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-23 23:06:27-06	2026-03-23 23:06:27-06	\N
117	25	22	cargo	32.94	305.80	272.86	copias	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-15 23:06:27-06	2026-03-15 23:06:27-06	\N
118	25	22	abono	92.84	272.86	365.70	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-06 23:06:27-06	2026-03-06 23:06:27-06	\N
119	26	23	abono	188.86	0.00	188.86	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-19 23:06:27-06	2026-03-19 23:06:27-06	\N
120	26	23	cargo	151.65	188.86	37.21	copias	Consumo en copias	\N	\N	\N	\N	{}	2026-03-16 23:06:27-06	2026-03-16 23:06:27-06	\N
121	26	23	abono	193.53	37.21	230.74	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-23 23:06:27-06	2026-02-23 23:06:27-06	\N
122	26	23	abono	173.46	230.74	404.20	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-24 23:06:27-06	2026-02-24 23:06:27-06	\N
123	26	23	abono	22.64	404.20	426.84	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-17 23:06:27-06	2026-03-17 23:06:27-06	\N
124	26	23	abono	149.13	426.84	575.97	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-26 23:06:27-06	2026-02-26 23:06:27-06	\N
125	26	23	abono	129.14	575.97	705.11	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-21 23:06:27-06	2026-03-21 23:06:27-06	\N
126	27	24	abono	125.13	0.00	125.13	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-17 23:06:27-06	2026-03-17 23:06:27-06	\N
127	27	24	abono	38.84	125.13	163.97	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-04 23:06:27-06	2026-03-04 23:06:27-06	\N
128	27	24	cargo	136.14	163.97	27.83	biblioteca	Consumo en acceso	\N	\N	\N	\N	{}	2026-03-01 23:06:27-06	2026-03-01 23:06:27-06	\N
129	27	24	abono	167.69	27.83	195.52	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-05 23:06:27-06	2026-03-05 23:06:27-06	\N
130	28	25	abono	53.74	0.00	53.74	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-05 23:06:27-06	2026-03-05 23:06:27-06	\N
131	28	25	abono	112.40	53.74	166.14	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-16 23:06:27-06	2026-03-16 23:06:27-06	\N
132	28	25	abono	193.54	166.14	359.68	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-23 23:06:27-06	2026-02-23 23:06:27-06	\N
133	28	25	cargo	15.80	359.68	343.88	souvenirs	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-18 23:06:27-06	2026-03-18 23:06:27-06	\N
134	28	25	cargo	30.10	343.88	313.78	cafeteria	Consumo en acceso	\N	\N	\N	\N	{}	2026-02-26 23:06:27-06	2026-02-26 23:06:27-06	\N
135	28	25	cargo	171.54	313.78	142.24	copias	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-02-24 23:06:27-06	2026-02-24 23:06:27-06	\N
136	28	25	cargo	65.96	142.24	76.28	biblioteca	Consumo en acceso	\N	\N	\N	\N	{}	2026-02-28 23:06:27-06	2026-02-28 23:06:27-06	\N
137	29	26	abono	31.69	0.00	31.69	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-17 23:06:27-06	2026-03-17 23:06:27-06	\N
138	29	26	abono	81.86	31.69	113.55	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-26 23:06:27-06	2026-02-26 23:06:27-06	\N
139	29	26	abono	115.69	113.55	229.24	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-09 23:06:27-06	2026-03-09 23:06:27-06	\N
140	29	26	abono	143.23	229.24	372.47	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-28 23:06:27-06	2026-02-28 23:06:27-06	\N
141	29	26	cargo	179.43	372.47	193.04	souvenirs	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-03-22 23:06:27-06	2026-03-22 23:06:27-06	\N
142	29	26	abono	15.75	193.04	208.79	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-14 23:06:27-06	2026-03-14 23:06:27-06	\N
143	29	26	abono	21.77	208.79	230.56	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-19 23:06:27-06	2026-03-19 23:06:27-06	\N
144	29	26	cargo	50.86	230.56	179.70	biblioteca	Consumo en copias	\N	\N	\N	\N	{}	2026-02-23 23:06:27-06	2026-02-23 23:06:27-06	\N
145	30	27	abono	172.81	0.00	172.81	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-23 23:06:27-06	2026-02-23 23:06:27-06	\N
146	30	27	cargo	167.41	172.81	5.40	biblioteca	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-03-11 23:06:27-06	2026-03-11 23:06:27-06	\N
147	30	27	abono	168.57	5.40	173.97	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-19 23:06:27-06	2026-03-19 23:06:27-06	\N
148	30	27	cargo	95.92	173.97	78.05	souvenirs	Consumo en copias	\N	\N	\N	\N	{}	2026-03-10 23:06:27-06	2026-03-10 23:06:27-06	\N
149	30	27	abono	156.11	78.05	234.16	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-22 23:06:27-06	2026-03-22 23:06:27-06	\N
150	31	28	abono	123.99	0.00	123.99	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-22 23:06:27-06	2026-03-22 23:06:27-06	\N
151	31	28	abono	123.79	123.99	247.78	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-03 23:06:27-06	2026-03-03 23:06:27-06	\N
152	31	28	abono	78.05	247.78	325.83	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-23 23:06:27-06	2026-03-23 23:06:27-06	\N
153	31	28	cargo	135.84	325.83	189.99	biblioteca	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-02-26 23:06:27-06	2026-02-26 23:06:27-06	\N
154	31	28	abono	16.67	189.99	206.66	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-01 23:06:27-06	2026-03-01 23:06:27-06	\N
155	31	28	cargo	71.91	206.66	134.75	acceso	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-03-22 23:06:27-06	2026-03-22 23:06:27-06	\N
156	31	28	cargo	36.48	134.75	98.27	souvenirs	Consumo en copias	\N	\N	\N	\N	{}	2026-03-21 23:06:27-06	2026-03-21 23:06:27-06	\N
157	32	29	abono	154.23	0.00	154.23	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-27 23:06:27-06	2026-02-27 23:06:27-06	\N
158	32	29	abono	22.87	154.23	177.10	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-22 23:06:27-06	2026-03-22 23:06:27-06	\N
159	32	29	cargo	33.37	177.10	143.73	copias	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-03-13 23:06:27-06	2026-03-13 23:06:27-06	\N
160	32	29	cargo	108.31	143.73	35.42	acceso	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-04 23:06:27-06	2026-03-04 23:06:27-06	\N
161	32	29	abono	130.35	35.42	165.77	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-15 23:06:27-06	2026-03-15 23:06:27-06	\N
162	32	29	abono	193.49	165.77	359.26	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
163	32	29	abono	23.05	359.26	382.31	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-14 23:06:27-06	2026-03-14 23:06:27-06	\N
164	33	30	abono	73.82	0.00	73.82	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-10 23:06:27-06	2026-03-10 23:06:27-06	\N
165	33	30	abono	100.18	73.82	174.00	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-20 23:06:27-06	2026-03-20 23:06:27-06	\N
166	33	30	cargo	126.77	174.00	47.23	souvenirs	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-02-21 23:06:27-06	2026-02-21 23:06:27-06	\N
167	33	30	abono	15.08	47.23	62.31	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-21 23:06:27-06	2026-02-21 23:06:27-06	\N
168	33	30	abono	81.95	62.31	144.26	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-27 23:06:27-06	2026-02-27 23:06:27-06	\N
169	33	30	cargo	23.19	144.26	121.07	cafeteria	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
170	33	30	abono	199.41	121.07	320.48	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-03 23:06:27-06	2026-03-03 23:06:27-06	\N
171	33	30	cargo	192.54	320.48	127.94	souvenirs	Consumo en acceso	\N	\N	\N	\N	{}	2026-03-17 23:06:27-06	2026-03-17 23:06:27-06	\N
172	34	31	abono	178.90	0.00	178.90	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-16 23:06:27-06	2026-03-16 23:06:27-06	\N
173	34	31	cargo	111.86	178.90	67.04	cafeteria	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
174	34	31	abono	69.73	67.04	136.77	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-18 23:06:27-06	2026-03-18 23:06:27-06	\N
175	34	31	abono	72.77	136.77	209.54	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-20 23:06:27-06	2026-03-20 23:06:27-06	\N
176	34	31	cargo	36.61	209.54	172.93	acceso	Consumo en acceso	\N	\N	\N	\N	{}	2026-03-23 23:06:27-06	2026-03-23 23:06:27-06	\N
177	34	31	cargo	125.43	172.93	47.50	copias	Consumo en acceso	\N	\N	\N	\N	{}	2026-03-07 23:06:27-06	2026-03-07 23:06:27-06	\N
178	35	32	abono	112.00	0.00	112.00	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-28 23:06:27-06	2026-02-28 23:06:27-06	\N
179	35	32	abono	133.12	112.00	245.12	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-28 23:06:27-06	2026-02-28 23:06:27-06	\N
180	35	32	cargo	190.22	245.12	54.90	souvenirs	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-22 23:06:27-06	2026-03-22 23:06:27-06	\N
181	35	32	abono	188.22	54.90	243.12	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-11 23:06:27-06	2026-03-11 23:06:27-06	\N
182	35	32	abono	100.68	243.12	343.80	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-23 23:06:27-06	2026-02-23 23:06:27-06	\N
183	35	32	cargo	171.24	343.80	172.56	biblioteca	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-02-21 23:06:27-06	2026-02-21 23:06:27-06	\N
184	35	32	cargo	111.14	172.56	61.42	souvenirs	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-02-23 23:06:27-06	2026-02-23 23:06:27-06	\N
185	36	33	abono	184.68	0.00	184.68	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-05 23:06:27-06	2026-03-05 23:06:27-06	\N
186	36	33	cargo	57.69	184.68	126.99	cafeteria	Consumo en copias	\N	\N	\N	\N	{}	2026-02-27 23:06:27-06	2026-02-27 23:06:27-06	\N
187	36	33	abono	190.68	126.99	317.67	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-10 23:06:27-06	2026-03-10 23:06:27-06	\N
188	36	33	cargo	115.50	317.67	202.17	copias	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-02-25 23:06:27-06	2026-02-25 23:06:27-06	\N
189	36	33	abono	133.02	202.17	335.19	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-25 23:06:27-06	2026-02-25 23:06:27-06	\N
190	36	33	cargo	18.71	335.19	316.48	biblioteca	Consumo en acceso	\N	\N	\N	\N	{}	2026-03-23 23:06:27-06	2026-03-23 23:06:27-06	\N
191	36	33	cargo	151.35	316.48	165.13	acceso	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-03-20 23:06:27-06	2026-03-20 23:06:27-06	\N
192	36	33	cargo	143.72	165.13	21.41	biblioteca	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-03-09 23:06:27-06	2026-03-09 23:06:27-06	\N
193	37	34	abono	168.94	0.00	168.94	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-22 23:06:27-06	2026-02-22 23:06:27-06	\N
194	37	34	cargo	59.72	168.94	109.22	acceso	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-02-26 23:06:27-06	2026-02-26 23:06:27-06	\N
195	37	34	cargo	69.60	109.22	39.62	acceso	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-10 23:06:27-06	2026-03-10 23:06:27-06	\N
196	37	34	abono	105.66	39.62	145.28	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-06 23:06:27-06	2026-03-06 23:06:27-06	\N
197	37	34	abono	180.13	145.28	325.41	cafeteria	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-22 23:06:27-06	2026-03-22 23:06:27-06	\N
198	37	34	abono	23.42	325.41	348.83	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-22 23:06:27-06	2026-02-22 23:06:27-06	\N
199	38	35	abono	73.34	0.00	73.34	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-09 23:06:27-06	2026-03-09 23:06:27-06	\N
200	38	35	cargo	11.97	73.34	61.37	copias	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-03-11 23:06:27-06	2026-03-11 23:06:27-06	\N
201	38	35	abono	119.51	61.37	180.88	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-14 23:06:27-06	2026-03-14 23:06:27-06	\N
202	38	35	abono	108.57	180.88	289.45	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-28 23:06:27-06	2026-02-28 23:06:27-06	\N
203	38	35	abono	47.72	289.45	337.17	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-19 23:06:27-06	2026-03-19 23:06:27-06	\N
204	38	35	cargo	77.46	337.17	259.71	souvenirs	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-03 23:06:27-06	2026-03-03 23:06:27-06	\N
205	38	35	cargo	102.84	259.71	156.87	souvenirs	Consumo en copias	\N	\N	\N	\N	{}	2026-03-13 23:06:27-06	2026-03-13 23:06:27-06	\N
206	39	36	abono	121.33	0.00	121.33	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-10 23:06:27-06	2026-03-10 23:06:27-06	\N
207	39	36	abono	63.75	121.33	185.08	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-23 23:06:27-06	2026-02-23 23:06:27-06	\N
208	39	36	abono	112.50	185.08	297.58	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-21 23:06:27-06	2026-03-21 23:06:27-06	\N
209	39	36	abono	56.20	297.58	353.78	copias	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-09 23:06:27-06	2026-03-09 23:06:27-06	\N
210	39	36	cargo	120.51	353.78	233.27	biblioteca	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-03-21 23:06:27-06	2026-03-21 23:06:27-06	\N
211	39	36	cargo	98.27	233.27	135.00	biblioteca	Consumo en acceso	\N	\N	\N	\N	{}	2026-02-27 23:06:27-06	2026-02-27 23:06:27-06	\N
212	39	36	cargo	116.41	135.00	18.59	acceso	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-02-28 23:06:27-06	2026-02-28 23:06:27-06	\N
213	40	37	abono	189.91	0.00	189.91	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-11 23:06:27-06	2026-03-11 23:06:27-06	\N
214	40	37	cargo	100.83	189.91	89.08	cafeteria	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-05 23:06:27-06	2026-03-05 23:06:27-06	\N
215	40	37	abono	24.84	89.08	113.92	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-23 23:06:27-06	2026-03-23 23:06:27-06	\N
216	40	37	abono	171.80	113.92	285.72	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
217	40	37	cargo	156.87	285.72	128.85	acceso	Consumo en souvenirs	\N	\N	\N	\N	{}	2026-03-02 23:06:27-06	2026-03-02 23:06:27-06	\N
218	41	38	abono	97.44	0.00	97.44	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-23 23:06:27-06	2026-03-23 23:06:27-06	\N
219	41	38	cargo	60.71	97.44	36.73	souvenirs	Consumo en cafeteria	\N	\N	\N	\N	{}	2026-03-16 23:06:27-06	2026-03-16 23:06:27-06	\N
220	41	38	abono	52.17	36.73	88.90	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-26 23:06:27-06	2026-02-26 23:06:27-06	\N
221	41	38	cargo	78.98	88.90	9.92	copias	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-02-23 23:06:27-06	2026-02-23 23:06:27-06	\N
222	41	38	abono	80.34	9.92	90.26	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-18 23:06:27-06	2026-03-18 23:06:27-06	\N
223	41	38	abono	157.42	90.26	247.68	biblioteca	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-12 23:06:27-06	2026-03-12 23:06:27-06	\N
224	42	39	abono	162.94	0.00	162.94	acceso	Recarga de saldo	\N	\N	\N	\N	{}	2026-03-17 23:06:27-06	2026-03-17 23:06:27-06	\N
225	42	39	abono	19.70	162.94	182.64	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-26 23:06:27-06	2026-02-26 23:06:27-06	\N
226	42	39	abono	131.03	182.64	313.67	souvenirs	Recarga de saldo	\N	\N	\N	\N	{}	2026-02-25 23:06:27-06	2026-02-25 23:06:27-06	\N
227	42	39	cargo	194.23	313.67	119.44	biblioteca	Consumo en biblioteca	\N	\N	\N	\N	{}	2026-03-14 23:06:27-06	2026-03-14 23:06:27-06	\N
\.


--
-- Data for Name: tarjeta_lectura; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.tarjeta_lectura (id, tarjeta_id, uid_leido, modulo, tipo_lectura, exito, detalle, ip, user_agent, operador_usuario_id, meta_json, created_at, updated_at, deleted_at, pedido_id) FROM stdin;
1	1	FJ6IR46N	acceso	consumo	f	Tarjeta perdida	192.168.49.149		6	{}	2026-03-22 13:33:26	2026-03-22 13:33:26	\N	\N
2	1	FJ6IR46N	acceso	consulta_saldo	f	Tarjeta perdida	10.46.217.87		5	{}	2026-03-22 03:05:26	2026-03-22 03:05:26	\N	\N
3	1	FJ6IR46N	acceso	consulta_saldo	f	Tarjeta perdida	172.17.91.109		7	{}	2026-03-21 15:37:26	2026-03-21 15:37:26	\N	\N
4	1	FJ6IR46N	cafeteria	consulta_saldo	f	Tarjeta perdida	10.99.55.209		7	{}	2026-03-21 08:45:26	2026-03-21 08:45:26	\N	\N
5	1	FJ6IR46N	souvenirs	consulta_saldo	f	Tarjeta perdida	10.240.169.20		8	{}	2026-03-21 13:29:26	2026-03-21 13:29:26	\N	\N
6	1	FJ6IR46N	biblioteca	confirmacion_entrega	f	Tarjeta perdida	10.124.97.227		4	{}	2026-03-22 11:08:26	2026-03-22 11:08:26	\N	\N
7	1	FJ6IR46N	acceso	confirmacion_entrega	f	Tarjeta perdida	192.168.2.252		4	{}	2026-03-21 08:08:26	2026-03-21 08:08:26	\N	\N
8	1	FJ6IR46N	souvenirs	acceso	f	Tarjeta perdida	192.168.239.194		6	{}	2026-03-22 05:38:26	2026-03-22 05:38:26	\N	\N
9	1	FJ6IR46N	copias	confirmacion_entrega	f	Tarjeta perdida	192.168.202.102		6	{}	2026-03-21 05:47:26	2026-03-21 05:47:26	\N	\N
10	1	FJ6IR46N	cafeteria	acceso	f	Tarjeta perdida	192.168.137.222		5	{}	2026-03-22 02:34:26	2026-03-22 02:34:26	\N	\N
11	1	FJ6IR46N	souvenirs	consumo	f	Tarjeta perdida	172.30.130.15		8	{}	2026-03-22 14:12:26	2026-03-22 14:12:26	\N	\N
12	1	FJ6IR46N	acceso	confirmacion_entrega	f	Tarjeta perdida	10.132.69.44		7	{}	2026-03-21 04:44:26	2026-03-21 04:44:26	\N	\N
13	1	FJ6IR46N	souvenirs	consumo	f	Tarjeta perdida	10.59.134.203		6	{}	2026-03-21 04:12:26	2026-03-21 04:12:26	\N	\N
14	1	FJ6IR46N	copias	consumo	f	Tarjeta perdida	192.168.68.45		7	{}	2026-03-22 11:42:26	2026-03-22 11:42:26	\N	\N
15	1	FJ6IR46N	acceso	acceso	f	Tarjeta perdida	172.30.60.169		7	{}	2026-03-22 07:44:26	2026-03-22 07:44:26	\N	\N
16	1	FJ6IR46N	biblioteca	consulta_saldo	f	Tarjeta perdida	172.30.65.184		5	{}	2026-03-21 03:13:26	2026-03-21 03:13:26	\N	\N
17	1	FJ6IR46N	cafeteria	acceso	f	Tarjeta perdida	192.168.230.95		7	{}	2026-03-21 01:19:26	2026-03-21 01:19:26	\N	\N
18	1	FJ6IR46N	biblioteca	consulta_saldo	f	Tarjeta perdida	192.168.182.104		4	{}	2026-03-22 12:04:26	2026-03-22 12:04:26	\N	\N
19	1	FJ6IR46N	biblioteca	consumo	f	Tarjeta perdida	10.172.106.247		6	{}	2026-03-22 03:54:26	2026-03-22 03:54:26	\N	\N
20	1	FJ6IR46N	acceso	consulta_saldo	f	Tarjeta perdida	10.94.21.141		7	{}	2026-03-22 11:45:26	2026-03-22 11:45:26	\N	\N
21	2	OG09NWQJ	cafeteria	consumo	t	Lectura exitosa	192.168.139.38		8	{}	2026-03-21 08:20:26	2026-03-21 08:20:26	\N	\N
22	2	OG09NWQJ	cafeteria	consumo	t	Lectura exitosa	172.20.210.107		6	{}	2026-03-21 09:04:26	2026-03-21 09:04:26	\N	\N
23	2	OG09NWQJ	souvenirs	consulta_saldo	t	Lectura exitosa	10.137.149.5		5	{}	2026-03-22 02:31:26	2026-03-22 02:31:26	\N	\N
24	2	OG09NWQJ	acceso	consumo	t	Lectura exitosa	10.194.246.221		7	{}	2026-03-21 08:00:26	2026-03-21 08:00:26	\N	\N
25	2	OG09NWQJ	copias	consulta_saldo	t	Lectura exitosa	10.61.252.2		6	{}	2026-03-22 10:11:26	2026-03-22 10:11:26	\N	\N
26	2	OG09NWQJ	souvenirs	confirmacion_entrega	t	Lectura exitosa	172.20.248.211		7	{}	2026-03-22 00:25:26	2026-03-22 00:25:26	\N	\N
27	2	OG09NWQJ	acceso	acceso	t	Lectura exitosa	192.168.182.212		6	{}	2026-03-21 06:44:26	2026-03-21 06:44:26	\N	\N
28	2	OG09NWQJ	copias	consulta_saldo	t	Lectura exitosa	192.168.106.46		6	{}	2026-03-21 11:45:26	2026-03-21 11:45:26	\N	\N
29	2	OG09NWQJ	cafeteria	acceso	t	Lectura exitosa	10.57.7.59		8	{}	2026-03-22 12:17:26	2026-03-22 12:17:26	\N	\N
30	2	OG09NWQJ	cafeteria	consulta_saldo	t	Lectura exitosa	172.28.234.146		6	{}	2026-03-22 06:49:26	2026-03-22 06:49:26	\N	\N
31	2	OG09NWQJ	acceso	confirmacion_entrega	t	Lectura exitosa	10.199.228.28		5	{}	2026-03-21 13:59:26	2026-03-21 13:59:26	\N	\N
32	2	OG09NWQJ	cafeteria	acceso	t	Lectura exitosa	192.168.78.17		6	{}	2026-03-22 13:36:26	2026-03-22 13:36:26	\N	\N
33	2	OG09NWQJ	acceso	acceso	f	Tarjeta activa	192.168.28.20		6	{}	2026-03-22 03:30:26	2026-03-22 03:30:26	\N	\N
34	2	OG09NWQJ	acceso	consumo	t	Lectura exitosa	172.28.150.160		6	{}	2026-03-22 08:22:26	2026-03-22 08:22:26	\N	\N
35	3	0HC2Q03Y	copias	consulta_saldo	t	Lectura exitosa	172.22.242.240		8	{}	2026-03-21 14:54:26	2026-03-21 14:54:26	\N	\N
36	3	0HC2Q03Y	souvenirs	consulta_saldo	t	Lectura exitosa	192.168.113.205		5	{}	2026-03-22 11:39:26	2026-03-22 11:39:26	\N	\N
37	3	0HC2Q03Y	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.12.131		4	{}	2026-03-22 06:15:26	2026-03-22 06:15:26	\N	\N
38	3	0HC2Q03Y	souvenirs	confirmacion_entrega	t	Lectura exitosa	172.27.10.234		5	{}	2026-03-21 09:36:26	2026-03-21 09:36:26	\N	\N
39	3	0HC2Q03Y	copias	acceso	t	Lectura exitosa	10.181.120.103		5	{}	2026-03-21 11:00:26	2026-03-21 11:00:26	\N	\N
40	3	0HC2Q03Y	souvenirs	consulta_saldo	f	Tarjeta activa	192.168.144.121		7	{}	2026-03-21 07:31:26	2026-03-21 07:31:26	\N	\N
41	3	0HC2Q03Y	cafeteria	confirmacion_entrega	f	Tarjeta activa	172.29.22.56		4	{}	2026-03-22 09:25:26	2026-03-22 09:25:26	\N	\N
42	3	0HC2Q03Y	acceso	confirmacion_entrega	t	Lectura exitosa	10.42.240.166		7	{}	2026-03-22 17:02:26	2026-03-22 17:02:26	\N	\N
43	3	0HC2Q03Y	biblioteca	consulta_saldo	t	Lectura exitosa	192.168.69.33		5	{}	2026-03-22 00:19:26	2026-03-22 00:19:26	\N	\N
44	3	0HC2Q03Y	cafeteria	acceso	t	Lectura exitosa	192.168.177.92		4	{}	2026-03-21 04:33:26	2026-03-21 04:33:26	\N	\N
45	3	0HC2Q03Y	biblioteca	consumo	t	Lectura exitosa	10.185.153.229		5	{}	2026-03-22 01:58:26	2026-03-22 01:58:26	\N	\N
46	3	0HC2Q03Y	biblioteca	consulta_saldo	t	Lectura exitosa	10.22.181.39		6	{}	2026-03-21 13:03:26	2026-03-21 13:03:26	\N	\N
47	3	0HC2Q03Y	copias	confirmacion_entrega	f	Tarjeta activa	192.168.5.53		7	{}	2026-03-22 01:46:26	2026-03-22 01:46:26	\N	\N
48	3	0HC2Q03Y	copias	consulta_saldo	t	Lectura exitosa	192.168.1.69		5	{}	2026-03-22 08:43:26	2026-03-22 08:43:26	\N	\N
49	3	0HC2Q03Y	copias	acceso	t	Lectura exitosa	192.168.95.77		8	{}	2026-03-22 16:17:26	2026-03-22 16:17:26	\N	\N
50	3	0HC2Q03Y	biblioteca	consulta_saldo	t	Lectura exitosa	192.168.186.13		6	{}	2026-03-21 09:04:26	2026-03-21 09:04:26	\N	\N
51	3	0HC2Q03Y	souvenirs	consumo	t	Lectura exitosa	172.29.195.216		7	{}	2026-03-22 01:16:26	2026-03-22 01:16:26	\N	\N
52	3	0HC2Q03Y	biblioteca	acceso	t	Lectura exitosa	10.6.146.68		5	{}	2026-03-21 00:19:26	2026-03-21 00:19:26	\N	\N
53	3	0HC2Q03Y	copias	consulta_saldo	t	Lectura exitosa	10.166.68.156		4	{}	2026-03-21 12:16:26	2026-03-21 12:16:26	\N	\N
54	3	0HC2Q03Y	biblioteca	consumo	t	Lectura exitosa	10.122.139.119		4	{}	2026-03-22 14:04:26	2026-03-22 14:04:26	\N	\N
55	3	0HC2Q03Y	biblioteca	acceso	f	Tarjeta activa	172.21.31.145		4	{}	2026-03-21 08:51:26	2026-03-21 08:51:26	\N	\N
56	3	0HC2Q03Y	acceso	confirmacion_entrega	t	Lectura exitosa	10.85.43.209		4	{}	2026-03-22 08:17:26	2026-03-22 08:17:26	\N	\N
57	3	0HC2Q03Y	biblioteca	acceso	t	Lectura exitosa	172.21.92.146		6	{}	2026-03-21 01:24:26	2026-03-21 01:24:26	\N	\N
58	3	0HC2Q03Y	copias	acceso	t	Lectura exitosa	192.168.117.104		6	{}	2026-03-22 10:03:26	2026-03-22 10:03:26	\N	\N
59	3	0HC2Q03Y	souvenirs	confirmacion_entrega	t	Lectura exitosa	172.21.52.135		8	{}	2026-03-21 08:55:26	2026-03-21 08:55:26	\N	\N
60	3	0HC2Q03Y	souvenirs	confirmacion_entrega	t	Lectura exitosa	10.101.26.123		8	{}	2026-03-22 10:30:26	2026-03-22 10:30:26	\N	\N
61	4	CFN9DUFR	copias	acceso	f	Tarjeta activa	192.168.211.228		7	{}	2026-03-22 14:33:26	2026-03-22 14:33:26	\N	\N
62	4	CFN9DUFR	souvenirs	confirmacion_entrega	t	Lectura exitosa	192.168.65.46		4	{}	2026-03-21 01:53:26	2026-03-21 01:53:26	\N	\N
63	4	CFN9DUFR	souvenirs	consumo	t	Lectura exitosa	192.168.159.67		6	{}	2026-03-22 16:55:26	2026-03-22 16:55:26	\N	\N
64	4	CFN9DUFR	cafeteria	consumo	t	Lectura exitosa	10.84.65.57		8	{}	2026-03-22 15:25:26	2026-03-22 15:25:26	\N	\N
65	4	CFN9DUFR	copias	consulta_saldo	f	Tarjeta activa	10.0.57.75		6	{}	2026-03-21 11:52:26	2026-03-21 11:52:26	\N	\N
66	4	CFN9DUFR	biblioteca	consumo	t	Lectura exitosa	10.66.101.60		6	{}	2026-03-22 11:11:26	2026-03-22 11:11:26	\N	\N
67	4	CFN9DUFR	souvenirs	acceso	t	Lectura exitosa	10.79.90.148		8	{}	2026-03-21 15:11:26	2026-03-21 15:11:26	\N	\N
68	4	CFN9DUFR	copias	consulta_saldo	t	Lectura exitosa	10.17.203.176		5	{}	2026-03-22 09:29:26	2026-03-22 09:29:26	\N	\N
69	4	CFN9DUFR	souvenirs	confirmacion_entrega	t	Lectura exitosa	172.22.112.113		4	{}	2026-03-22 13:06:26	2026-03-22 13:06:26	\N	\N
70	4	CFN9DUFR	biblioteca	confirmacion_entrega	t	Lectura exitosa	192.168.135.48		6	{}	2026-03-21 09:18:26	2026-03-21 09:18:26	\N	\N
71	5	V2OG3C9Z	copias	confirmacion_entrega	f	Tarjeta activa	172.20.57.52		8	{}	2026-03-22 15:55:26	2026-03-22 15:55:26	\N	\N
72	5	V2OG3C9Z	souvenirs	consulta_saldo	t	Lectura exitosa	172.31.145.80		5	{}	2026-03-22 01:52:26	2026-03-22 01:52:26	\N	\N
73	5	V2OG3C9Z	acceso	confirmacion_entrega	t	Lectura exitosa	10.150.243.203		8	{}	2026-03-22 07:15:26	2026-03-22 07:15:26	\N	\N
74	5	V2OG3C9Z	souvenirs	acceso	t	Lectura exitosa	192.168.235.241		4	{}	2026-03-22 08:21:26	2026-03-22 08:21:26	\N	\N
75	5	V2OG3C9Z	acceso	confirmacion_entrega	t	Lectura exitosa	192.168.110.92		8	{}	2026-03-21 12:28:26	2026-03-21 12:28:26	\N	\N
76	5	V2OG3C9Z	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.70.117		6	{}	2026-03-21 05:55:26	2026-03-21 05:55:26	\N	\N
77	5	V2OG3C9Z	biblioteca	consulta_saldo	t	Lectura exitosa	192.168.53.90		6	{}	2026-03-22 09:04:26	2026-03-22 09:04:26	\N	\N
78	5	V2OG3C9Z	cafeteria	consulta_saldo	t	Lectura exitosa	10.132.248.255		6	{}	2026-03-21 11:22:26	2026-03-21 11:22:26	\N	\N
79	5	V2OG3C9Z	cafeteria	consumo	t	Lectura exitosa	192.168.205.228		6	{}	2026-03-21 08:16:26	2026-03-21 08:16:26	\N	\N
80	5	V2OG3C9Z	cafeteria	consulta_saldo	t	Lectura exitosa	192.168.239.138		7	{}	2026-03-22 04:23:26	2026-03-22 04:23:26	\N	\N
81	5	V2OG3C9Z	cafeteria	confirmacion_entrega	t	Lectura exitosa	10.186.128.178		7	{}	2026-03-21 15:49:26	2026-03-21 15:49:26	\N	\N
82	6	AHHM5YX0	acceso	acceso	t	Lectura exitosa	192.168.167.101		4	{}	2026-03-21 04:33:26	2026-03-21 04:33:26	\N	\N
83	6	AHHM5YX0	copias	confirmacion_entrega	t	Lectura exitosa	172.26.132.238		7	{}	2026-03-21 16:15:26	2026-03-21 16:15:26	\N	\N
84	6	AHHM5YX0	souvenirs	confirmacion_entrega	t	Lectura exitosa	172.19.255.214		7	{}	2026-03-22 05:41:26	2026-03-22 05:41:26	\N	\N
85	6	AHHM5YX0	souvenirs	acceso	t	Lectura exitosa	10.99.228.172		4	{}	2026-03-21 06:31:26	2026-03-21 06:31:26	\N	\N
86	6	AHHM5YX0	cafeteria	consumo	t	Lectura exitosa	172.17.34.31		6	{}	2026-03-21 12:19:26	2026-03-21 12:19:26	\N	\N
87	6	AHHM5YX0	cafeteria	consulta_saldo	t	Lectura exitosa	10.1.161.205		8	{}	2026-03-21 02:05:26	2026-03-21 02:05:26	\N	\N
88	6	AHHM5YX0	biblioteca	confirmacion_entrega	t	Lectura exitosa	10.148.153.121		4	{}	2026-03-22 02:13:26	2026-03-22 02:13:26	\N	\N
89	6	AHHM5YX0	souvenirs	acceso	t	Lectura exitosa	10.244.233.164		8	{}	2026-03-21 03:32:26	2026-03-21 03:32:26	\N	\N
90	6	AHHM5YX0	souvenirs	consumo	t	Lectura exitosa	192.168.242.139		6	{}	2026-03-21 13:13:26	2026-03-21 13:13:26	\N	\N
91	6	AHHM5YX0	copias	confirmacion_entrega	t	Lectura exitosa	10.200.51.146		6	{}	2026-03-22 09:19:26	2026-03-22 09:19:26	\N	\N
92	6	AHHM5YX0	copias	confirmacion_entrega	t	Lectura exitosa	10.87.111.2		7	{}	2026-03-21 10:51:26	2026-03-21 10:51:26	\N	\N
93	6	AHHM5YX0	cafeteria	confirmacion_entrega	t	Lectura exitosa	172.17.35.40		8	{}	2026-03-21 01:03:26	2026-03-21 01:03:26	\N	\N
94	6	AHHM5YX0	cafeteria	consulta_saldo	t	Lectura exitosa	10.85.143.220		7	{}	2026-03-21 04:37:26	2026-03-21 04:37:26	\N	\N
95	6	AHHM5YX0	biblioteca	confirmacion_entrega	t	Lectura exitosa	10.171.56.129		5	{}	2026-03-21 12:17:26	2026-03-21 12:17:26	\N	\N
96	6	AHHM5YX0	acceso	consumo	t	Lectura exitosa	192.168.153.230		8	{}	2026-03-22 11:38:26	2026-03-22 11:38:26	\N	\N
97	6	AHHM5YX0	acceso	consumo	t	Lectura exitosa	192.168.199.100		5	{}	2026-03-21 12:28:26	2026-03-21 12:28:26	\N	\N
98	6	AHHM5YX0	cafeteria	consulta_saldo	t	Lectura exitosa	172.26.233.75		8	{}	2026-03-22 15:16:26	2026-03-22 15:16:26	\N	\N
99	6	AHHM5YX0	acceso	acceso	t	Lectura exitosa	192.168.130.103		4	{}	2026-03-21 09:23:26	2026-03-21 09:23:26	\N	\N
100	6	AHHM5YX0	copias	consumo	t	Lectura exitosa	192.168.22.226		4	{}	2026-03-22 03:19:26	2026-03-22 03:19:26	\N	\N
101	6	AHHM5YX0	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.172.10		4	{}	2026-03-22 05:18:26	2026-03-22 05:18:26	\N	\N
102	6	AHHM5YX0	biblioteca	consulta_saldo	f	Tarjeta activa	10.122.233.210		6	{}	2026-03-22 08:56:26	2026-03-22 08:56:26	\N	\N
103	6	AHHM5YX0	copias	confirmacion_entrega	t	Lectura exitosa	172.31.93.55		4	{}	2026-03-22 04:49:26	2026-03-22 04:49:26	\N	\N
104	7	7RMTSLXA	biblioteca	acceso	t	Lectura exitosa	10.78.193.186		4	{}	2026-03-21 00:36:26	2026-03-21 00:36:26	\N	\N
105	7	7RMTSLXA	biblioteca	acceso	t	Lectura exitosa	192.168.104.111		8	{}	2026-03-21 14:22:26	2026-03-21 14:22:26	\N	\N
106	7	7RMTSLXA	copias	confirmacion_entrega	t	Lectura exitosa	192.168.134.16		6	{}	2026-03-21 13:34:26	2026-03-21 13:34:26	\N	\N
107	7	7RMTSLXA	copias	consumo	t	Lectura exitosa	172.18.11.78		6	{}	2026-03-22 00:57:26	2026-03-22 00:57:26	\N	\N
108	7	7RMTSLXA	acceso	consumo	t	Lectura exitosa	192.168.250.73		8	{}	2026-03-21 16:52:26	2026-03-21 16:52:26	\N	\N
109	7	7RMTSLXA	biblioteca	acceso	t	Lectura exitosa	192.168.64.119		4	{}	2026-03-21 01:09:26	2026-03-21 01:09:26	\N	\N
110	7	7RMTSLXA	cafeteria	consulta_saldo	t	Lectura exitosa	10.55.236.132		4	{}	2026-03-21 07:54:26	2026-03-21 07:54:26	\N	\N
111	7	7RMTSLXA	cafeteria	acceso	t	Lectura exitosa	10.205.240.59		4	{}	2026-03-22 06:31:26	2026-03-22 06:31:26	\N	\N
112	7	7RMTSLXA	souvenirs	consulta_saldo	t	Lectura exitosa	10.72.116.173		5	{}	2026-03-21 05:38:26	2026-03-21 05:38:26	\N	\N
113	7	7RMTSLXA	cafeteria	consulta_saldo	t	Lectura exitosa	10.106.193.208		7	{}	2026-03-21 09:23:26	2026-03-21 09:23:26	\N	\N
114	8	O0XBN2CK	cafeteria	consulta_saldo	t	Lectura exitosa	172.29.9.243		6	{}	2026-03-21 13:21:26	2026-03-21 13:21:26	\N	\N
115	8	O0XBN2CK	biblioteca	consumo	t	Lectura exitosa	192.168.174.28		6	{}	2026-03-22 08:32:26	2026-03-22 08:32:26	\N	\N
116	8	O0XBN2CK	biblioteca	consulta_saldo	t	Lectura exitosa	10.75.208.69		6	{}	2026-03-22 03:26:26	2026-03-22 03:26:26	\N	\N
117	8	O0XBN2CK	acceso	consulta_saldo	t	Lectura exitosa	10.69.159.123		5	{}	2026-03-21 07:13:26	2026-03-21 07:13:26	\N	\N
118	8	O0XBN2CK	souvenirs	consumo	t	Lectura exitosa	10.205.156.16		7	{}	2026-03-22 01:09:26	2026-03-22 01:09:26	\N	\N
119	8	O0XBN2CK	biblioteca	acceso	t	Lectura exitosa	10.50.65.103		4	{}	2026-03-21 05:38:26	2026-03-21 05:38:26	\N	\N
120	8	O0XBN2CK	acceso	confirmacion_entrega	f	Tarjeta activa	172.17.118.239		7	{}	2026-03-21 14:12:26	2026-03-21 14:12:26	\N	\N
121	8	O0XBN2CK	copias	consulta_saldo	t	Lectura exitosa	172.24.80.187		7	{}	2026-03-22 02:00:26	2026-03-22 02:00:26	\N	\N
122	8	O0XBN2CK	cafeteria	confirmacion_entrega	t	Lectura exitosa	10.197.227.208		7	{}	2026-03-21 00:25:26	2026-03-21 00:25:26	\N	\N
123	8	O0XBN2CK	copias	consulta_saldo	t	Lectura exitosa	192.168.133.192		7	{}	2026-03-21 13:57:26	2026-03-21 13:57:26	\N	\N
124	8	O0XBN2CK	copias	consulta_saldo	t	Lectura exitosa	10.119.232.79		7	{}	2026-03-22 01:00:26	2026-03-22 01:00:26	\N	\N
125	8	O0XBN2CK	biblioteca	consulta_saldo	t	Lectura exitosa	192.168.229.118		6	{}	2026-03-22 08:46:26	2026-03-22 08:46:26	\N	\N
126	8	O0XBN2CK	acceso	consulta_saldo	t	Lectura exitosa	10.204.170.176		8	{}	2026-03-21 08:33:26	2026-03-21 08:33:26	\N	\N
127	8	O0XBN2CK	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.52.10		8	{}	2026-03-22 08:42:26	2026-03-22 08:42:26	\N	\N
128	8	O0XBN2CK	acceso	confirmacion_entrega	t	Lectura exitosa	192.168.96.142		5	{}	2026-03-22 11:54:26	2026-03-22 11:54:26	\N	\N
129	8	O0XBN2CK	copias	consumo	t	Lectura exitosa	172.28.112.100		7	{}	2026-03-21 02:30:26	2026-03-21 02:30:26	\N	\N
130	8	O0XBN2CK	biblioteca	consumo	t	Lectura exitosa	172.23.215.33		7	{}	2026-03-21 16:13:26	2026-03-21 16:13:26	\N	\N
131	8	O0XBN2CK	cafeteria	acceso	t	Lectura exitosa	10.178.63.160		8	{}	2026-03-21 11:22:26	2026-03-21 11:22:26	\N	\N
132	8	O0XBN2CK	souvenirs	confirmacion_entrega	t	Lectura exitosa	10.201.38.246		6	{}	2026-03-21 05:19:26	2026-03-21 05:19:26	\N	\N
133	8	O0XBN2CK	cafeteria	acceso	t	Lectura exitosa	172.19.194.105		8	{}	2026-03-22 09:27:26	2026-03-22 09:27:26	\N	\N
134	8	O0XBN2CK	cafeteria	acceso	t	Lectura exitosa	192.168.2.20		7	{}	2026-03-21 06:48:26	2026-03-21 06:48:26	\N	\N
135	8	O0XBN2CK	cafeteria	acceso	t	Lectura exitosa	172.16.152.24		6	{}	2026-03-22 14:29:26	2026-03-22 14:29:26	\N	\N
136	8	O0XBN2CK	acceso	acceso	t	Lectura exitosa	172.31.196.48		7	{}	2026-03-22 06:04:26	2026-03-22 06:04:26	\N	\N
137	8	O0XBN2CK	copias	confirmacion_entrega	t	Lectura exitosa	10.121.23.86		6	{}	2026-03-21 05:45:26	2026-03-21 05:45:26	\N	\N
138	8	O0XBN2CK	copias	consumo	t	Lectura exitosa	172.23.89.196		6	{}	2026-03-21 06:37:26	2026-03-21 06:37:26	\N	\N
139	8	O0XBN2CK	souvenirs	consulta_saldo	t	Lectura exitosa	10.104.71.211		5	{}	2026-03-22 07:57:26	2026-03-22 07:57:26	\N	\N
140	8	O0XBN2CK	acceso	consulta_saldo	t	Lectura exitosa	192.168.231.164		8	{}	2026-03-22 07:03:26	2026-03-22 07:03:26	\N	\N
141	8	O0XBN2CK	biblioteca	confirmacion_entrega	t	Lectura exitosa	172.31.235.251		5	{}	2026-03-21 13:54:26	2026-03-21 13:54:26	\N	\N
142	8	O0XBN2CK	biblioteca	acceso	t	Lectura exitosa	172.27.134.119		6	{}	2026-03-21 12:52:26	2026-03-21 12:52:26	\N	\N
143	9	UJIPZQFX	acceso	acceso	t	Lectura exitosa	172.16.89.59		7	{}	2026-03-22 15:59:26	2026-03-22 15:59:26	\N	\N
144	9	UJIPZQFX	cafeteria	consulta_saldo	t	Lectura exitosa	10.5.106.131		4	{}	2026-03-21 07:27:26	2026-03-21 07:27:26	\N	\N
145	9	UJIPZQFX	acceso	confirmacion_entrega	t	Lectura exitosa	192.168.133.197		7	{}	2026-03-22 01:17:26	2026-03-22 01:17:26	\N	\N
146	9	UJIPZQFX	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.190.131		6	{}	2026-03-22 05:53:26	2026-03-22 05:53:26	\N	\N
147	9	UJIPZQFX	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.239.134		8	{}	2026-03-21 14:37:26	2026-03-21 14:37:26	\N	\N
148	9	UJIPZQFX	copias	consumo	t	Lectura exitosa	192.168.212.32		6	{}	2026-03-22 04:44:26	2026-03-22 04:44:26	\N	\N
149	9	UJIPZQFX	copias	consulta_saldo	t	Lectura exitosa	192.168.23.36		5	{}	2026-03-22 05:22:26	2026-03-22 05:22:26	\N	\N
150	9	UJIPZQFX	souvenirs	acceso	t	Lectura exitosa	172.23.134.125		7	{}	2026-03-21 02:07:26	2026-03-21 02:07:26	\N	\N
151	9	UJIPZQFX	biblioteca	acceso	t	Lectura exitosa	10.29.236.173		4	{}	2026-03-22 13:00:26	2026-03-22 13:00:26	\N	\N
152	9	UJIPZQFX	souvenirs	confirmacion_entrega	t	Lectura exitosa	192.168.217.118		6	{}	2026-03-22 09:32:26	2026-03-22 09:32:26	\N	\N
153	9	UJIPZQFX	copias	confirmacion_entrega	t	Lectura exitosa	10.235.218.191		5	{}	2026-03-21 08:31:26	2026-03-21 08:31:26	\N	\N
154	9	UJIPZQFX	biblioteca	acceso	t	Lectura exitosa	172.29.166.107		8	{}	2026-03-21 12:06:26	2026-03-21 12:06:26	\N	\N
155	9	UJIPZQFX	acceso	confirmacion_entrega	t	Lectura exitosa	172.26.25.94		5	{}	2026-03-22 15:29:26	2026-03-22 15:29:26	\N	\N
156	9	UJIPZQFX	cafeteria	consulta_saldo	t	Lectura exitosa	172.31.187.240		4	{}	2026-03-22 09:23:26	2026-03-22 09:23:26	\N	\N
157	9	UJIPZQFX	acceso	acceso	t	Lectura exitosa	172.19.170.27		7	{}	2026-03-22 00:56:26	2026-03-22 00:56:26	\N	\N
158	9	UJIPZQFX	acceso	consulta_saldo	t	Lectura exitosa	172.17.36.175		8	{}	2026-03-22 09:42:26	2026-03-22 09:42:26	\N	\N
159	9	UJIPZQFX	biblioteca	consulta_saldo	t	Lectura exitosa	172.27.114.113		5	{}	2026-03-21 12:30:26	2026-03-21 12:30:26	\N	\N
160	9	UJIPZQFX	acceso	confirmacion_entrega	t	Lectura exitosa	10.178.178.130		6	{}	2026-03-22 14:03:26	2026-03-22 14:03:26	\N	\N
161	9	UJIPZQFX	cafeteria	confirmacion_entrega	t	Lectura exitosa	172.24.199.185		4	{}	2026-03-22 01:49:26	2026-03-22 01:49:26	\N	\N
162	9	UJIPZQFX	biblioteca	confirmacion_entrega	t	Lectura exitosa	10.61.37.180		7	{}	2026-03-22 07:41:26	2026-03-22 07:41:26	\N	\N
163	9	UJIPZQFX	biblioteca	acceso	t	Lectura exitosa	172.17.94.127		7	{}	2026-03-21 01:17:26	2026-03-21 01:17:26	\N	\N
164	9	UJIPZQFX	biblioteca	acceso	t	Lectura exitosa	10.173.92.8		6	{}	2026-03-21 09:30:26	2026-03-21 09:30:26	\N	\N
165	9	UJIPZQFX	copias	acceso	f	Tarjeta activa	192.168.159.43		8	{}	2026-03-21 14:17:26	2026-03-21 14:17:26	\N	\N
166	10	Y3FW3M9T	souvenirs	consumo	f	Tarjeta bloqueada	192.168.0.204		6	{}	2026-03-21 12:34:26	2026-03-21 12:34:26	\N	\N
167	10	Y3FW3M9T	acceso	consulta_saldo	f	Tarjeta bloqueada	172.20.220.193		4	{}	2026-03-21 12:45:26	2026-03-21 12:45:26	\N	\N
168	10	Y3FW3M9T	copias	confirmacion_entrega	f	Tarjeta bloqueada	172.30.37.129		4	{}	2026-03-21 16:52:26	2026-03-21 16:52:26	\N	\N
169	10	Y3FW3M9T	souvenirs	confirmacion_entrega	f	Tarjeta bloqueada	192.168.158.118		8	{}	2026-03-22 06:59:26	2026-03-22 06:59:26	\N	\N
170	10	Y3FW3M9T	acceso	acceso	f	Tarjeta bloqueada	192.168.227.148		6	{}	2026-03-22 13:16:26	2026-03-22 13:16:26	\N	\N
171	10	Y3FW3M9T	biblioteca	consumo	f	Tarjeta bloqueada	10.87.96.37		8	{}	2026-03-22 16:46:26	2026-03-22 16:46:26	\N	\N
172	10	Y3FW3M9T	cafeteria	acceso	f	Tarjeta bloqueada	192.168.103.83		5	{}	2026-03-21 04:46:26	2026-03-21 04:46:26	\N	\N
173	10	Y3FW3M9T	acceso	confirmacion_entrega	f	Tarjeta bloqueada	172.27.195.99		4	{}	2026-03-22 14:02:26	2026-03-22 14:02:26	\N	\N
174	10	Y3FW3M9T	cafeteria	acceso	f	Tarjeta bloqueada	192.168.131.105		8	{}	2026-03-22 10:07:26	2026-03-22 10:07:26	\N	\N
175	10	Y3FW3M9T	copias	confirmacion_entrega	f	Tarjeta bloqueada	10.163.120.27		5	{}	2026-03-22 10:58:26	2026-03-22 10:58:26	\N	\N
176	10	Y3FW3M9T	biblioteca	consumo	f	Tarjeta bloqueada	172.31.16.236		8	{}	2026-03-22 14:01:26	2026-03-22 14:01:26	\N	\N
177	10	Y3FW3M9T	copias	consumo	f	Tarjeta bloqueada	10.178.126.101		7	{}	2026-03-21 10:37:26	2026-03-21 10:37:26	\N	\N
178	10	Y3FW3M9T	acceso	consumo	f	Tarjeta bloqueada	192.168.6.164		7	{}	2026-03-22 07:43:26	2026-03-22 07:43:26	\N	\N
179	10	Y3FW3M9T	cafeteria	consumo	f	Tarjeta bloqueada	10.15.92.230		8	{}	2026-03-21 07:43:26	2026-03-21 07:43:26	\N	\N
180	10	Y3FW3M9T	acceso	confirmacion_entrega	f	Tarjeta bloqueada	192.168.108.96		8	{}	2026-03-21 00:27:26	2026-03-21 00:27:26	\N	\N
181	10	Y3FW3M9T	souvenirs	acceso	f	Tarjeta bloqueada	10.248.67.237		8	{}	2026-03-22 05:22:26	2026-03-22 05:22:26	\N	\N
182	10	Y3FW3M9T	copias	confirmacion_entrega	f	Tarjeta bloqueada	10.50.153.164		5	{}	2026-03-21 09:56:26	2026-03-21 09:56:26	\N	\N
183	10	Y3FW3M9T	copias	consumo	f	Tarjeta bloqueada	10.127.32.141		7	{}	2026-03-21 08:10:26	2026-03-21 08:10:26	\N	\N
184	10	Y3FW3M9T	copias	consumo	f	Tarjeta bloqueada	10.12.240.12		4	{}	2026-03-21 06:54:26	2026-03-21 06:54:26	\N	\N
185	10	Y3FW3M9T	cafeteria	acceso	f	Tarjeta bloqueada	192.168.228.163		7	{}	2026-03-22 01:18:26	2026-03-22 01:18:26	\N	\N
186	10	Y3FW3M9T	souvenirs	consulta_saldo	f	Tarjeta bloqueada	192.168.51.144		7	{}	2026-03-22 15:37:26	2026-03-22 15:37:26	\N	\N
187	10	Y3FW3M9T	copias	consumo	f	Tarjeta bloqueada	172.21.106.226		7	{}	2026-03-22 09:25:26	2026-03-22 09:25:26	\N	\N
188	10	Y3FW3M9T	acceso	consumo	f	Tarjeta bloqueada	10.75.101.208		8	{}	2026-03-21 07:35:26	2026-03-21 07:35:26	\N	\N
189	10	Y3FW3M9T	cafeteria	confirmacion_entrega	f	Tarjeta bloqueada	10.124.10.34		8	{}	2026-03-22 09:14:26	2026-03-22 09:14:26	\N	\N
190	10	Y3FW3M9T	acceso	consulta_saldo	f	Tarjeta bloqueada	172.25.166.25		7	{}	2026-03-22 16:11:26	2026-03-22 16:11:26	\N	\N
191	10	Y3FW3M9T	souvenirs	acceso	f	Tarjeta bloqueada	10.48.10.154		5	{}	2026-03-21 05:51:26	2026-03-21 05:51:26	\N	\N
192	11	EO8ZHR9H	cafeteria	consumo	t	Lectura exitosa	172.30.253.136		4	{}	2026-03-21 04:24:26	2026-03-21 04:24:26	\N	\N
193	11	EO8ZHR9H	souvenirs	confirmacion_entrega	t	Lectura exitosa	10.81.208.200		5	{}	2026-03-21 15:38:26	2026-03-21 15:38:26	\N	\N
194	11	EO8ZHR9H	acceso	acceso	t	Lectura exitosa	10.130.16.242		8	{}	2026-03-22 07:02:26	2026-03-22 07:02:26	\N	\N
195	11	EO8ZHR9H	souvenirs	consulta_saldo	t	Lectura exitosa	192.168.228.248		7	{}	2026-03-22 12:43:26	2026-03-22 12:43:26	\N	\N
196	11	EO8ZHR9H	souvenirs	acceso	t	Lectura exitosa	172.29.71.91		6	{}	2026-03-22 05:55:26	2026-03-22 05:55:26	\N	\N
197	11	EO8ZHR9H	biblioteca	confirmacion_entrega	t	Lectura exitosa	192.168.39.154		6	{}	2026-03-21 14:42:26	2026-03-21 14:42:26	\N	\N
198	11	EO8ZHR9H	biblioteca	consumo	t	Lectura exitosa	172.30.63.133		7	{}	2026-03-21 06:09:26	2026-03-21 06:09:26	\N	\N
199	11	EO8ZHR9H	copias	confirmacion_entrega	t	Lectura exitosa	172.30.182.112		8	{}	2026-03-21 00:22:26	2026-03-21 00:22:26	\N	\N
200	11	EO8ZHR9H	cafeteria	consumo	t	Lectura exitosa	10.179.14.134		8	{}	2026-03-21 07:24:26	2026-03-21 07:24:26	\N	\N
201	11	EO8ZHR9H	copias	consumo	t	Lectura exitosa	10.176.58.50		5	{}	2026-03-21 13:13:26	2026-03-21 13:13:26	\N	\N
202	11	EO8ZHR9H	souvenirs	confirmacion_entrega	t	Lectura exitosa	192.168.27.223		8	{}	2026-03-21 08:07:26	2026-03-21 08:07:26	\N	\N
203	11	EO8ZHR9H	acceso	consulta_saldo	f	Tarjeta activa	10.12.228.135		4	{}	2026-03-22 06:24:26	2026-03-22 06:24:26	\N	\N
204	11	EO8ZHR9H	biblioteca	confirmacion_entrega	t	Lectura exitosa	172.29.179.208		6	{}	2026-03-21 11:17:26	2026-03-21 11:17:26	\N	\N
205	11	EO8ZHR9H	cafeteria	acceso	t	Lectura exitosa	192.168.83.199		8	{}	2026-03-22 14:04:26	2026-03-22 14:04:26	\N	\N
206	11	EO8ZHR9H	biblioteca	confirmacion_entrega	t	Lectura exitosa	172.18.92.91		8	{}	2026-03-21 13:42:26	2026-03-21 13:42:26	\N	\N
207	11	EO8ZHR9H	biblioteca	acceso	t	Lectura exitosa	192.168.96.106		7	{}	2026-03-21 07:41:26	2026-03-21 07:41:26	\N	\N
208	12	HOIVYRXH	biblioteca	acceso	f	Tarjeta activa	10.151.128.102		6	{}	2026-03-21 03:01:26	2026-03-21 03:01:26	\N	\N
209	12	HOIVYRXH	copias	consulta_saldo	f	Tarjeta activa	192.168.54.184		5	{}	2026-03-21 08:05:26	2026-03-21 08:05:26	\N	\N
210	12	HOIVYRXH	cafeteria	consulta_saldo	t	Lectura exitosa	172.19.141.48		5	{}	2026-03-21 03:26:26	2026-03-21 03:26:26	\N	\N
211	12	HOIVYRXH	cafeteria	consulta_saldo	f	Tarjeta activa	192.168.45.212		7	{}	2026-03-21 15:41:26	2026-03-21 15:41:26	\N	\N
212	12	HOIVYRXH	acceso	confirmacion_entrega	t	Lectura exitosa	10.247.122.9		5	{}	2026-03-22 10:46:26	2026-03-22 10:46:26	\N	\N
213	12	HOIVYRXH	copias	consulta_saldo	t	Lectura exitosa	192.168.78.183		5	{}	2026-03-22 01:55:26	2026-03-22 01:55:26	\N	\N
214	12	HOIVYRXH	souvenirs	confirmacion_entrega	t	Lectura exitosa	192.168.194.241		8	{}	2026-03-21 14:55:26	2026-03-21 14:55:26	\N	\N
215	12	HOIVYRXH	copias	acceso	t	Lectura exitosa	172.18.183.11		4	{}	2026-03-21 09:03:26	2026-03-21 09:03:26	\N	\N
216	12	HOIVYRXH	biblioteca	acceso	t	Lectura exitosa	10.213.73.155		8	{}	2026-03-21 05:30:26	2026-03-21 05:30:26	\N	\N
217	12	HOIVYRXH	acceso	confirmacion_entrega	t	Lectura exitosa	172.29.115.98		6	{}	2026-03-22 13:06:26	2026-03-22 13:06:26	\N	\N
218	12	HOIVYRXH	biblioteca	consumo	t	Lectura exitosa	10.161.238.107		4	{}	2026-03-22 08:18:26	2026-03-22 08:18:26	\N	\N
219	12	HOIVYRXH	souvenirs	confirmacion_entrega	t	Lectura exitosa	10.56.166.68		8	{}	2026-03-22 02:39:26	2026-03-22 02:39:26	\N	\N
220	12	HOIVYRXH	biblioteca	acceso	t	Lectura exitosa	172.24.250.62		6	{}	2026-03-22 10:32:26	2026-03-22 10:32:26	\N	\N
221	12	HOIVYRXH	acceso	acceso	t	Lectura exitosa	192.168.167.31		5	{}	2026-03-22 04:48:26	2026-03-22 04:48:26	\N	\N
222	12	HOIVYRXH	biblioteca	confirmacion_entrega	t	Lectura exitosa	10.154.233.68		7	{}	2026-03-22 12:56:26	2026-03-22 12:56:26	\N	\N
223	13	APDINIZM	copias	acceso	t	Lectura exitosa	172.26.46.145		8	{}	2026-03-22 04:57:26	2026-03-22 04:57:26	\N	\N
224	13	APDINIZM	souvenirs	confirmacion_entrega	t	Lectura exitosa	10.221.0.152		4	{}	2026-03-21 01:36:26	2026-03-21 01:36:26	\N	\N
225	13	APDINIZM	cafeteria	acceso	t	Lectura exitosa	172.16.250.63		8	{}	2026-03-21 09:39:26	2026-03-21 09:39:26	\N	\N
226	13	APDINIZM	biblioteca	consulta_saldo	t	Lectura exitosa	172.22.230.111		4	{}	2026-03-21 09:05:26	2026-03-21 09:05:26	\N	\N
227	13	APDINIZM	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.195.95		6	{}	2026-03-21 09:04:26	2026-03-21 09:04:26	\N	\N
228	13	APDINIZM	cafeteria	consulta_saldo	t	Lectura exitosa	172.25.226.121		8	{}	2026-03-21 11:51:26	2026-03-21 11:51:26	\N	\N
229	13	APDINIZM	souvenirs	acceso	t	Lectura exitosa	172.23.140.44		5	{}	2026-03-22 01:28:26	2026-03-22 01:28:26	\N	\N
230	13	APDINIZM	acceso	consumo	t	Lectura exitosa	10.90.14.163		8	{}	2026-03-22 06:19:26	2026-03-22 06:19:26	\N	\N
231	13	APDINIZM	souvenirs	consumo	t	Lectura exitosa	172.22.128.17		5	{}	2026-03-21 15:52:26	2026-03-21 15:52:26	\N	\N
232	13	APDINIZM	copias	consulta_saldo	t	Lectura exitosa	192.168.165.19		6	{}	2026-03-21 10:44:26	2026-03-21 10:44:26	\N	\N
233	13	APDINIZM	cafeteria	acceso	t	Lectura exitosa	10.88.196.7		4	{}	2026-03-22 00:18:26	2026-03-22 00:18:26	\N	\N
234	13	APDINIZM	acceso	consulta_saldo	t	Lectura exitosa	192.168.16.48		8	{}	2026-03-22 10:06:26	2026-03-22 10:06:26	\N	\N
235	14	YSOV28TP	biblioteca	confirmacion_entrega	t	Lectura exitosa	10.131.102.25		5	{}	2026-03-22 11:13:26	2026-03-22 11:13:26	\N	\N
236	14	YSOV28TP	acceso	acceso	t	Lectura exitosa	172.29.2.205		4	{}	2026-03-21 03:19:26	2026-03-21 03:19:26	\N	\N
237	14	YSOV28TP	acceso	consulta_saldo	t	Lectura exitosa	10.30.60.161		8	{}	2026-03-21 12:45:26	2026-03-21 12:45:26	\N	\N
238	14	YSOV28TP	biblioteca	acceso	t	Lectura exitosa	172.16.36.52		7	{}	2026-03-22 11:22:26	2026-03-22 11:22:26	\N	\N
239	14	YSOV28TP	biblioteca	acceso	t	Lectura exitosa	192.168.75.185		8	{}	2026-03-22 07:41:26	2026-03-22 07:41:26	\N	\N
240	14	YSOV28TP	souvenirs	consulta_saldo	t	Lectura exitosa	192.168.105.225		7	{}	2026-03-22 16:26:26	2026-03-22 16:26:26	\N	\N
241	14	YSOV28TP	cafeteria	acceso	t	Lectura exitosa	172.24.205.226		5	{}	2026-03-22 01:36:26	2026-03-22 01:36:26	\N	\N
242	14	YSOV28TP	cafeteria	consulta_saldo	t	Lectura exitosa	172.21.5.121		5	{}	2026-03-22 08:42:26	2026-03-22 08:42:26	\N	\N
243	14	YSOV28TP	cafeteria	consumo	t	Lectura exitosa	10.93.77.193		7	{}	2026-03-22 10:35:26	2026-03-22 10:35:26	\N	\N
244	14	YSOV28TP	acceso	confirmacion_entrega	t	Lectura exitosa	192.168.25.155		5	{}	2026-03-22 06:34:26	2026-03-22 06:34:26	\N	\N
245	14	YSOV28TP	cafeteria	confirmacion_entrega	f	Tarjeta activa	172.16.43.18		7	{}	2026-03-22 10:35:26	2026-03-22 10:35:26	\N	\N
246	14	YSOV28TP	acceso	consulta_saldo	f	Tarjeta activa	172.16.88.94		8	{}	2026-03-22 15:25:26	2026-03-22 15:25:26	\N	\N
247	14	YSOV28TP	cafeteria	consumo	t	Lectura exitosa	10.174.186.187		4	{}	2026-03-22 09:03:26	2026-03-22 09:03:26	\N	\N
248	14	YSOV28TP	acceso	confirmacion_entrega	t	Lectura exitosa	192.168.140.178		4	{}	2026-03-21 02:44:26	2026-03-21 02:44:26	\N	\N
249	14	YSOV28TP	acceso	confirmacion_entrega	t	Lectura exitosa	10.195.174.125		4	{}	2026-03-21 15:07:26	2026-03-21 15:07:26	\N	\N
250	14	YSOV28TP	biblioteca	consumo	t	Lectura exitosa	192.168.74.253		4	{}	2026-03-21 12:46:26	2026-03-21 12:46:26	\N	\N
251	14	YSOV28TP	biblioteca	consumo	t	Lectura exitosa	192.168.16.83		5	{}	2026-03-21 08:08:26	2026-03-21 08:08:26	\N	\N
252	15	ANSOK1GE	biblioteca	consumo	t	Lectura exitosa	172.17.8.42		4	{}	2026-03-22 00:46:26	2026-03-22 00:46:26	\N	\N
253	15	ANSOK1GE	copias	consumo	t	Lectura exitosa	192.168.62.120		4	{}	2026-03-22 05:19:26	2026-03-22 05:19:26	\N	\N
254	15	ANSOK1GE	copias	confirmacion_entrega	t	Lectura exitosa	192.168.122.171		4	{}	2026-03-22 10:17:26	2026-03-22 10:17:26	\N	\N
255	15	ANSOK1GE	acceso	confirmacion_entrega	t	Lectura exitosa	10.204.48.113		5	{}	2026-03-22 14:39:26	2026-03-22 14:39:26	\N	\N
256	15	ANSOK1GE	cafeteria	acceso	t	Lectura exitosa	192.168.119.47		4	{}	2026-03-22 07:07:26	2026-03-22 07:07:26	\N	\N
257	15	ANSOK1GE	acceso	consumo	t	Lectura exitosa	10.147.252.33		8	{}	2026-03-21 00:28:26	2026-03-21 00:28:26	\N	\N
258	15	ANSOK1GE	acceso	acceso	t	Lectura exitosa	192.168.50.17		7	{}	2026-03-22 14:12:26	2026-03-22 14:12:26	\N	\N
259	15	ANSOK1GE	copias	acceso	t	Lectura exitosa	192.168.99.86		8	{}	2026-03-22 10:15:26	2026-03-22 10:15:26	\N	\N
260	15	ANSOK1GE	acceso	consumo	t	Lectura exitosa	10.117.145.200		4	{}	2026-03-22 14:24:26	2026-03-22 14:24:26	\N	\N
261	15	ANSOK1GE	biblioteca	acceso	t	Lectura exitosa	192.168.176.102		5	{}	2026-03-21 13:06:26	2026-03-21 13:06:26	\N	\N
262	15	ANSOK1GE	souvenirs	consulta_saldo	f	Tarjeta activa	10.88.128.231		6	{}	2026-03-21 08:38:26	2026-03-21 08:38:26	\N	\N
263	16	B88GRDTT	acceso	confirmacion_entrega	t	Lectura exitosa	192.168.22.109		8	{}	2026-03-21 06:58:26	2026-03-21 06:58:26	\N	\N
264	16	B88GRDTT	cafeteria	acceso	t	Lectura exitosa	172.29.229.121		5	{}	2026-03-22 00:56:26	2026-03-22 00:56:26	\N	\N
265	16	B88GRDTT	acceso	confirmacion_entrega	t	Lectura exitosa	172.17.227.112		7	{}	2026-03-21 02:52:26	2026-03-21 02:52:26	\N	\N
266	16	B88GRDTT	acceso	consumo	t	Lectura exitosa	192.168.239.192		8	{}	2026-03-21 15:43:26	2026-03-21 15:43:26	\N	\N
267	16	B88GRDTT	cafeteria	confirmacion_entrega	t	Lectura exitosa	172.19.254.19		4	{}	2026-03-21 12:01:26	2026-03-21 12:01:26	\N	\N
268	16	B88GRDTT	cafeteria	consumo	t	Lectura exitosa	10.172.223.223		6	{}	2026-03-22 12:44:26	2026-03-22 12:44:26	\N	\N
269	16	B88GRDTT	copias	consulta_saldo	f	Tarjeta activa	10.54.53.232		5	{}	2026-03-22 13:24:26	2026-03-22 13:24:26	\N	\N
270	16	B88GRDTT	cafeteria	consulta_saldo	t	Lectura exitosa	172.18.32.154		8	{}	2026-03-21 02:41:26	2026-03-21 02:41:26	\N	\N
271	16	B88GRDTT	biblioteca	consulta_saldo	t	Lectura exitosa	10.96.40.239		8	{}	2026-03-22 11:03:26	2026-03-22 11:03:26	\N	\N
272	16	B88GRDTT	copias	confirmacion_entrega	t	Lectura exitosa	10.2.69.2		7	{}	2026-03-21 16:41:26	2026-03-21 16:41:26	\N	\N
273	16	B88GRDTT	copias	consumo	t	Lectura exitosa	192.168.0.246		8	{}	2026-03-21 10:06:26	2026-03-21 10:06:26	\N	\N
274	16	B88GRDTT	biblioteca	consumo	f	Tarjeta activa	172.17.135.123		6	{}	2026-03-21 03:34:26	2026-03-21 03:34:26	\N	\N
275	16	B88GRDTT	copias	acceso	t	Lectura exitosa	172.27.212.92		6	{}	2026-03-22 15:20:26	2026-03-22 15:20:26	\N	\N
276	16	B88GRDTT	acceso	consumo	t	Lectura exitosa	172.28.154.186		8	{}	2026-03-22 15:19:26	2026-03-22 15:19:26	\N	\N
277	16	B88GRDTT	cafeteria	confirmacion_entrega	t	Lectura exitosa	10.29.51.149		6	{}	2026-03-21 10:28:26	2026-03-21 10:28:26	\N	\N
278	16	B88GRDTT	souvenirs	consumo	f	Tarjeta activa	192.168.47.71		4	{}	2026-03-21 15:35:26	2026-03-21 15:35:26	\N	\N
279	17	N79AGVXB	souvenirs	consumo	t	Lectura exitosa	10.99.192.11		5	{}	2026-03-22 05:07:26	2026-03-22 05:07:26	\N	\N
280	17	N79AGVXB	souvenirs	confirmacion_entrega	t	Lectura exitosa	10.169.158.219		5	{}	2026-03-21 15:56:26	2026-03-21 15:56:26	\N	\N
281	17	N79AGVXB	souvenirs	acceso	t	Lectura exitosa	10.172.20.253		5	{}	2026-03-22 13:14:26	2026-03-22 13:14:26	\N	\N
282	17	N79AGVXB	copias	acceso	t	Lectura exitosa	192.168.224.179		7	{}	2026-03-21 03:42:26	2026-03-21 03:42:26	\N	\N
283	17	N79AGVXB	biblioteca	confirmacion_entrega	t	Lectura exitosa	192.168.166.106		6	{}	2026-03-21 04:46:26	2026-03-21 04:46:26	\N	\N
284	17	N79AGVXB	biblioteca	confirmacion_entrega	t	Lectura exitosa	10.199.16.38		7	{}	2026-03-22 06:47:26	2026-03-22 06:47:26	\N	\N
285	17	N79AGVXB	copias	consulta_saldo	t	Lectura exitosa	172.17.70.111		7	{}	2026-03-22 01:08:26	2026-03-22 01:08:26	\N	\N
286	17	N79AGVXB	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.102.48		5	{}	2026-03-21 11:51:26	2026-03-21 11:51:26	\N	\N
287	17	N79AGVXB	copias	consumo	t	Lectura exitosa	172.25.248.180		5	{}	2026-03-22 16:39:26	2026-03-22 16:39:26	\N	\N
288	17	N79AGVXB	copias	confirmacion_entrega	t	Lectura exitosa	192.168.199.42		7	{}	2026-03-21 06:04:26	2026-03-21 06:04:26	\N	\N
289	17	N79AGVXB	copias	confirmacion_entrega	t	Lectura exitosa	10.205.117.80		8	{}	2026-03-22 13:03:26	2026-03-22 13:03:26	\N	\N
290	17	N79AGVXB	biblioteca	consumo	t	Lectura exitosa	192.168.197.116		8	{}	2026-03-21 01:21:26	2026-03-21 01:21:26	\N	\N
291	17	N79AGVXB	copias	consulta_saldo	t	Lectura exitosa	192.168.32.159		4	{}	2026-03-22 16:46:26	2026-03-22 16:46:26	\N	\N
292	17	N79AGVXB	biblioteca	consumo	t	Lectura exitosa	172.19.23.220		4	{}	2026-03-22 02:43:26	2026-03-22 02:43:26	\N	\N
293	17	N79AGVXB	souvenirs	acceso	t	Lectura exitosa	192.168.88.40		6	{}	2026-03-22 07:32:26	2026-03-22 07:32:26	\N	\N
294	17	N79AGVXB	acceso	consumo	t	Lectura exitosa	172.21.11.93		5	{}	2026-03-21 11:53:26	2026-03-21 11:53:26	\N	\N
295	18	MRM8VEMI	biblioteca	confirmacion_entrega	f	Tarjeta bloqueada	172.30.48.247		6	{}	2026-03-21 16:42:26	2026-03-21 16:42:26	\N	\N
296	18	MRM8VEMI	cafeteria	consumo	f	Tarjeta bloqueada	192.168.183.96		4	{}	2026-03-21 02:59:26	2026-03-21 02:59:26	\N	\N
297	18	MRM8VEMI	souvenirs	confirmacion_entrega	f	Tarjeta bloqueada	172.30.212.176		8	{}	2026-03-21 12:39:26	2026-03-21 12:39:26	\N	\N
298	18	MRM8VEMI	copias	confirmacion_entrega	f	Tarjeta bloqueada	10.82.25.207		8	{}	2026-03-21 06:04:26	2026-03-21 06:04:26	\N	\N
299	18	MRM8VEMI	cafeteria	acceso	f	Tarjeta bloqueada	10.189.134.78		4	{}	2026-03-22 05:29:26	2026-03-22 05:29:26	\N	\N
300	18	MRM8VEMI	biblioteca	consulta_saldo	f	Tarjeta bloqueada	172.27.249.189		5	{}	2026-03-21 01:49:26	2026-03-21 01:49:26	\N	\N
301	18	MRM8VEMI	souvenirs	acceso	f	Tarjeta bloqueada	192.168.103.98		4	{}	2026-03-22 07:48:26	2026-03-22 07:48:26	\N	\N
302	18	MRM8VEMI	acceso	confirmacion_entrega	f	Tarjeta bloqueada	10.84.190.29		4	{}	2026-03-21 14:08:26	2026-03-21 14:08:26	\N	\N
303	18	MRM8VEMI	copias	acceso	f	Tarjeta bloqueada	10.203.63.14		4	{}	2026-03-22 12:12:26	2026-03-22 12:12:26	\N	\N
304	18	MRM8VEMI	souvenirs	consumo	f	Tarjeta bloqueada	172.25.235.253		7	{}	2026-03-22 08:50:26	2026-03-22 08:50:26	\N	\N
305	18	MRM8VEMI	biblioteca	consulta_saldo	f	Tarjeta bloqueada	192.168.130.90		8	{}	2026-03-22 05:10:26	2026-03-22 05:10:26	\N	\N
306	18	MRM8VEMI	souvenirs	acceso	f	Tarjeta bloqueada	172.20.18.34		4	{}	2026-03-21 09:22:26	2026-03-21 09:22:26	\N	\N
307	18	MRM8VEMI	cafeteria	confirmacion_entrega	f	Tarjeta bloqueada	10.100.65.228		5	{}	2026-03-22 13:14:26	2026-03-22 13:14:26	\N	\N
308	18	MRM8VEMI	biblioteca	consulta_saldo	f	Tarjeta bloqueada	172.17.184.163		5	{}	2026-03-22 03:08:26	2026-03-22 03:08:26	\N	\N
309	18	MRM8VEMI	acceso	acceso	f	Tarjeta bloqueada	172.20.90.111		5	{}	2026-03-21 09:29:26	2026-03-21 09:29:26	\N	\N
310	18	MRM8VEMI	biblioteca	consulta_saldo	f	Tarjeta bloqueada	172.18.6.167		5	{}	2026-03-21 12:55:26	2026-03-21 12:55:26	\N	\N
311	18	MRM8VEMI	cafeteria	acceso	f	Tarjeta bloqueada	10.24.206.91		4	{}	2026-03-21 09:15:26	2026-03-21 09:15:26	\N	\N
312	18	MRM8VEMI	souvenirs	consulta_saldo	f	Tarjeta bloqueada	10.142.69.4		8	{}	2026-03-21 08:37:26	2026-03-21 08:37:26	\N	\N
313	18	MRM8VEMI	souvenirs	consulta_saldo	f	Tarjeta bloqueada	10.1.165.191		4	{}	2026-03-22 04:34:26	2026-03-22 04:34:26	\N	\N
314	18	MRM8VEMI	acceso	acceso	f	Tarjeta bloqueada	10.251.3.82		7	{}	2026-03-21 05:51:26	2026-03-21 05:51:26	\N	\N
315	18	MRM8VEMI	cafeteria	acceso	f	Tarjeta bloqueada	172.24.118.111		5	{}	2026-03-22 06:27:26	2026-03-22 06:27:26	\N	\N
316	18	MRM8VEMI	souvenirs	consulta_saldo	f	Tarjeta bloqueada	192.168.83.48		8	{}	2026-03-21 12:26:26	2026-03-21 12:26:26	\N	\N
317	18	MRM8VEMI	biblioteca	consumo	f	Tarjeta bloqueada	10.178.154.207		8	{}	2026-03-21 03:51:26	2026-03-21 03:51:26	\N	\N
318	18	MRM8VEMI	acceso	consulta_saldo	f	Tarjeta bloqueada	10.209.105.179		7	{}	2026-03-21 05:16:26	2026-03-21 05:16:26	\N	\N
319	18	MRM8VEMI	copias	consulta_saldo	f	Tarjeta bloqueada	172.21.59.115		5	{}	2026-03-21 04:01:26	2026-03-21 04:01:26	\N	\N
320	18	MRM8VEMI	copias	consumo	f	Tarjeta bloqueada	10.151.232.60		6	{}	2026-03-21 09:38:26	2026-03-21 09:38:26	\N	\N
321	18	MRM8VEMI	copias	confirmacion_entrega	f	Tarjeta bloqueada	172.16.67.197		7	{}	2026-03-22 04:29:26	2026-03-22 04:29:26	\N	\N
322	18	MRM8VEMI	acceso	acceso	f	Tarjeta bloqueada	172.22.15.55		7	{}	2026-03-22 02:29:26	2026-03-22 02:29:26	\N	\N
323	18	MRM8VEMI	souvenirs	acceso	f	Tarjeta bloqueada	172.22.29.33		4	{}	2026-03-22 08:08:26	2026-03-22 08:08:26	\N	\N
324	19	AZPQASEJ	copias	consulta_saldo	f	Tarjeta bloqueada	192.168.200.126		7	{}	2026-03-21 08:33:26	2026-03-21 08:33:26	\N	\N
325	19	AZPQASEJ	cafeteria	confirmacion_entrega	f	Tarjeta bloqueada	10.10.51.186		8	{}	2026-03-21 13:03:26	2026-03-21 13:03:26	\N	\N
326	19	AZPQASEJ	souvenirs	confirmacion_entrega	f	Tarjeta bloqueada	10.32.113.13		4	{}	2026-03-21 05:51:26	2026-03-21 05:51:26	\N	\N
327	19	AZPQASEJ	copias	consulta_saldo	f	Tarjeta bloqueada	172.31.145.61		5	{}	2026-03-22 14:11:26	2026-03-22 14:11:26	\N	\N
328	19	AZPQASEJ	cafeteria	confirmacion_entrega	f	Tarjeta bloqueada	192.168.177.242		6	{}	2026-03-22 13:21:26	2026-03-22 13:21:26	\N	\N
329	19	AZPQASEJ	biblioteca	consumo	f	Tarjeta bloqueada	10.227.144.248		6	{}	2026-03-21 16:41:26	2026-03-21 16:41:26	\N	\N
330	19	AZPQASEJ	souvenirs	consulta_saldo	f	Tarjeta bloqueada	10.136.201.147		7	{}	2026-03-21 06:37:26	2026-03-21 06:37:26	\N	\N
331	19	AZPQASEJ	souvenirs	consumo	f	Tarjeta bloqueada	192.168.169.240		6	{}	2026-03-21 16:15:26	2026-03-21 16:15:26	\N	\N
332	19	AZPQASEJ	biblioteca	acceso	f	Tarjeta bloqueada	192.168.132.200		8	{}	2026-03-21 01:15:26	2026-03-21 01:15:26	\N	\N
333	19	AZPQASEJ	souvenirs	consumo	f	Tarjeta bloqueada	172.31.173.154		8	{}	2026-03-22 15:44:26	2026-03-22 15:44:26	\N	\N
334	19	AZPQASEJ	souvenirs	confirmacion_entrega	f	Tarjeta bloqueada	10.22.183.132		7	{}	2026-03-21 04:49:26	2026-03-21 04:49:26	\N	\N
335	19	AZPQASEJ	copias	consumo	f	Tarjeta bloqueada	172.17.82.218		8	{}	2026-03-21 04:53:26	2026-03-21 04:53:26	\N	\N
336	19	AZPQASEJ	cafeteria	consulta_saldo	f	Tarjeta bloqueada	10.197.119.251		8	{}	2026-03-22 03:30:26	2026-03-22 03:30:26	\N	\N
337	20	8WD11TRP	copias	consumo	t	Lectura exitosa	192.168.218.84		5	{}	2026-03-22 11:18:26	2026-03-22 11:18:26	\N	\N
338	20	8WD11TRP	acceso	acceso	t	Lectura exitosa	192.168.92.170		8	{}	2026-03-21 00:40:26	2026-03-21 00:40:26	\N	\N
339	20	8WD11TRP	biblioteca	consumo	t	Lectura exitosa	172.29.36.26		6	{}	2026-03-22 01:39:26	2026-03-22 01:39:26	\N	\N
340	20	8WD11TRP	souvenirs	confirmacion_entrega	f	Tarjeta activa	192.168.163.195		5	{}	2026-03-22 02:03:26	2026-03-22 02:03:26	\N	\N
341	20	8WD11TRP	acceso	confirmacion_entrega	t	Lectura exitosa	10.245.250.32		4	{}	2026-03-22 07:14:26	2026-03-22 07:14:26	\N	\N
342	20	8WD11TRP	acceso	consumo	t	Lectura exitosa	192.168.160.218		4	{}	2026-03-21 00:58:26	2026-03-21 00:58:26	\N	\N
343	20	8WD11TRP	souvenirs	confirmacion_entrega	t	Lectura exitosa	192.168.71.231		7	{}	2026-03-22 01:08:26	2026-03-22 01:08:26	\N	\N
344	20	8WD11TRP	souvenirs	consumo	t	Lectura exitosa	172.18.186.93		6	{}	2026-03-21 05:46:26	2026-03-21 05:46:26	\N	\N
345	20	8WD11TRP	cafeteria	consulta_saldo	t	Lectura exitosa	10.11.167.213		5	{}	2026-03-22 11:40:26	2026-03-22 11:40:26	\N	\N
346	20	8WD11TRP	souvenirs	consumo	t	Lectura exitosa	172.29.217.202		7	{}	2026-03-22 13:41:26	2026-03-22 13:41:26	\N	\N
347	20	8WD11TRP	biblioteca	consumo	t	Lectura exitosa	192.168.1.202		4	{}	2026-03-22 08:32:26	2026-03-22 08:32:26	\N	\N
348	20	8WD11TRP	copias	consumo	t	Lectura exitosa	10.139.94.131		7	{}	2026-03-22 15:52:26	2026-03-22 15:52:26	\N	\N
349	20	8WD11TRP	copias	consumo	t	Lectura exitosa	172.20.26.105		6	{}	2026-03-21 15:34:26	2026-03-21 15:34:26	\N	\N
350	20	8WD11TRP	biblioteca	consulta_saldo	f	Tarjeta activa	192.168.208.174		5	{}	2026-03-21 06:14:26	2026-03-21 06:14:26	\N	\N
351	20	8WD11TRP	biblioteca	acceso	t	Lectura exitosa	192.168.45.216		7	{}	2026-03-21 17:00:26	2026-03-21 17:00:26	\N	\N
352	20	8WD11TRP	copias	acceso	t	Lectura exitosa	172.23.51.7		8	{}	2026-03-22 01:16:26	2026-03-22 01:16:26	\N	\N
353	20	8WD11TRP	souvenirs	consulta_saldo	t	Lectura exitosa	10.128.119.99		5	{}	2026-03-22 08:42:26	2026-03-22 08:42:26	\N	\N
354	20	8WD11TRP	cafeteria	consumo	t	Lectura exitosa	10.64.241.60		7	{}	2026-03-22 05:55:26	2026-03-22 05:55:26	\N	\N
355	20	8WD11TRP	copias	consumo	t	Lectura exitosa	192.168.188.224		4	{}	2026-03-22 02:51:26	2026-03-22 02:51:26	\N	\N
356	20	8WD11TRP	cafeteria	confirmacion_entrega	t	Lectura exitosa	172.31.169.246		6	{}	2026-03-22 11:38:26	2026-03-22 11:38:26	\N	\N
357	20	8WD11TRP	acceso	acceso	f	Tarjeta activa	172.16.93.6		8	{}	2026-03-21 16:40:26	2026-03-21 16:40:26	\N	\N
358	20	8WD11TRP	acceso	confirmacion_entrega	t	Lectura exitosa	172.28.239.206		8	{}	2026-03-22 12:54:26	2026-03-22 12:54:26	\N	\N
359	20	8WD11TRP	copias	acceso	t	Lectura exitosa	10.206.52.210		5	{}	2026-03-21 11:29:26	2026-03-21 11:29:26	\N	\N
360	20	8WD11TRP	biblioteca	confirmacion_entrega	t	Lectura exitosa	172.22.157.225		8	{}	2026-03-22 01:34:26	2026-03-22 01:34:26	\N	\N
361	20	8WD11TRP	copias	consumo	t	Lectura exitosa	172.22.163.56		6	{}	2026-03-21 13:19:26	2026-03-21 13:19:26	\N	\N
362	20	8WD11TRP	souvenirs	acceso	t	Lectura exitosa	192.168.38.143		4	{}	2026-03-21 10:32:26	2026-03-21 10:32:26	\N	\N
363	21	ZOU22ZE0	cafeteria	consumo	f	Tarjeta bloqueada	10.38.196.223		5	{}	2026-03-22 10:03:26	2026-03-22 10:03:26	\N	\N
364	21	ZOU22ZE0	copias	consulta_saldo	f	Tarjeta bloqueada	172.16.31.126		7	{}	2026-03-22 16:15:26	2026-03-22 16:15:26	\N	\N
365	21	ZOU22ZE0	cafeteria	consulta_saldo	f	Tarjeta bloqueada	192.168.17.238		8	{}	2026-03-22 09:52:26	2026-03-22 09:52:26	\N	\N
366	21	ZOU22ZE0	acceso	acceso	f	Tarjeta bloqueada	192.168.117.102		7	{}	2026-03-22 03:51:26	2026-03-22 03:51:26	\N	\N
367	21	ZOU22ZE0	acceso	confirmacion_entrega	f	Tarjeta bloqueada	192.168.102.182		7	{}	2026-03-22 09:07:26	2026-03-22 09:07:26	\N	\N
368	21	ZOU22ZE0	biblioteca	consumo	f	Tarjeta bloqueada	172.16.79.196		7	{}	2026-03-21 06:53:26	2026-03-21 06:53:26	\N	\N
369	21	ZOU22ZE0	copias	consulta_saldo	f	Tarjeta bloqueada	192.168.50.4		4	{}	2026-03-21 15:51:26	2026-03-21 15:51:26	\N	\N
370	21	ZOU22ZE0	acceso	acceso	f	Tarjeta bloqueada	172.16.84.50		5	{}	2026-03-21 13:19:26	2026-03-21 13:19:26	\N	\N
371	21	ZOU22ZE0	souvenirs	acceso	f	Tarjeta bloqueada	192.168.102.48		8	{}	2026-03-21 05:01:26	2026-03-21 05:01:26	\N	\N
372	21	ZOU22ZE0	cafeteria	confirmacion_entrega	f	Tarjeta bloqueada	10.76.150.246		5	{}	2026-03-22 16:28:26	2026-03-22 16:28:26	\N	\N
373	21	ZOU22ZE0	acceso	confirmacion_entrega	f	Tarjeta bloqueada	10.68.3.251		6	{}	2026-03-22 06:10:26	2026-03-22 06:10:26	\N	\N
374	21	ZOU22ZE0	copias	acceso	f	Tarjeta bloqueada	172.27.22.37		6	{}	2026-03-21 17:06:26	2026-03-21 17:06:26	\N	\N
375	21	ZOU22ZE0	cafeteria	confirmacion_entrega	f	Tarjeta bloqueada	172.21.215.200		7	{}	2026-03-22 05:23:26	2026-03-22 05:23:26	\N	\N
376	21	ZOU22ZE0	cafeteria	consulta_saldo	f	Tarjeta bloqueada	10.125.172.100		4	{}	2026-03-21 08:45:26	2026-03-21 08:45:26	\N	\N
377	21	ZOU22ZE0	souvenirs	acceso	f	Tarjeta bloqueada	10.152.179.45		7	{}	2026-03-21 15:57:26	2026-03-21 15:57:26	\N	\N
378	21	ZOU22ZE0	copias	acceso	f	Tarjeta bloqueada	10.25.131.136		5	{}	2026-03-21 06:44:26	2026-03-21 06:44:26	\N	\N
379	21	ZOU22ZE0	acceso	consumo	f	Tarjeta bloqueada	192.168.185.178		8	{}	2026-03-21 06:56:26	2026-03-21 06:56:26	\N	\N
380	21	ZOU22ZE0	cafeteria	acceso	f	Tarjeta bloqueada	172.16.137.193		8	{}	2026-03-22 15:40:26	2026-03-22 15:40:26	\N	\N
381	21	ZOU22ZE0	acceso	confirmacion_entrega	f	Tarjeta bloqueada	192.168.241.100		8	{}	2026-03-22 16:27:26	2026-03-22 16:27:26	\N	\N
382	21	ZOU22ZE0	acceso	confirmacion_entrega	f	Tarjeta bloqueada	192.168.46.34		5	{}	2026-03-21 12:11:26	2026-03-21 12:11:26	\N	\N
383	21	ZOU22ZE0	souvenirs	consumo	f	Tarjeta bloqueada	172.23.165.69		4	{}	2026-03-21 06:14:26	2026-03-21 06:14:26	\N	\N
384	21	ZOU22ZE0	biblioteca	confirmacion_entrega	f	Tarjeta bloqueada	192.168.32.159		6	{}	2026-03-22 00:12:26	2026-03-22 00:12:26	\N	\N
385	21	ZOU22ZE0	acceso	acceso	f	Tarjeta bloqueada	10.254.115.153		6	{}	2026-03-22 02:59:26	2026-03-22 02:59:26	\N	\N
386	21	ZOU22ZE0	cafeteria	consulta_saldo	f	Tarjeta bloqueada	10.233.245.111		6	{}	2026-03-21 12:48:26	2026-03-21 12:48:26	\N	\N
387	22	SOGHMV1W	biblioteca	confirmacion_entrega	t	Lectura exitosa	172.31.195.53		6	{}	2026-03-22 11:39:26	2026-03-22 11:39:26	\N	\N
388	22	SOGHMV1W	souvenirs	confirmacion_entrega	t	Lectura exitosa	10.241.205.105		4	{}	2026-03-21 04:40:26	2026-03-21 04:40:26	\N	\N
389	22	SOGHMV1W	copias	acceso	t	Lectura exitosa	10.97.95.106		8	{}	2026-03-21 09:19:26	2026-03-21 09:19:26	\N	\N
390	22	SOGHMV1W	acceso	consumo	t	Lectura exitosa	172.17.30.85		8	{}	2026-03-22 13:31:26	2026-03-22 13:31:26	\N	\N
391	22	SOGHMV1W	biblioteca	consulta_saldo	t	Lectura exitosa	192.168.27.18		6	{}	2026-03-22 16:00:26	2026-03-22 16:00:26	\N	\N
392	22	SOGHMV1W	cafeteria	consulta_saldo	t	Lectura exitosa	10.188.210.44		4	{}	2026-03-22 09:29:26	2026-03-22 09:29:26	\N	\N
393	22	SOGHMV1W	copias	consulta_saldo	t	Lectura exitosa	192.168.122.92		4	{}	2026-03-21 10:43:26	2026-03-21 10:43:26	\N	\N
394	22	SOGHMV1W	souvenirs	acceso	t	Lectura exitosa	172.26.131.133		8	{}	2026-03-21 12:09:26	2026-03-21 12:09:26	\N	\N
395	22	SOGHMV1W	souvenirs	acceso	t	Lectura exitosa	172.30.84.153		6	{}	2026-03-21 02:01:26	2026-03-21 02:01:26	\N	\N
396	22	SOGHMV1W	biblioteca	confirmacion_entrega	t	Lectura exitosa	192.168.118.50		5	{}	2026-03-22 09:05:26	2026-03-22 09:05:26	\N	\N
397	22	SOGHMV1W	souvenirs	confirmacion_entrega	t	Lectura exitosa	172.20.134.38		4	{}	2026-03-22 07:48:26	2026-03-22 07:48:26	\N	\N
398	23	VKPIPYGN	acceso	consulta_saldo	t	Lectura exitosa	192.168.247.11		6	{}	2026-03-21 15:14:26	2026-03-21 15:14:26	\N	\N
399	23	VKPIPYGN	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.246.55		4	{}	2026-03-22 10:47:26	2026-03-22 10:47:26	\N	\N
400	23	VKPIPYGN	copias	consumo	t	Lectura exitosa	10.193.70.128		8	{}	2026-03-22 09:18:26	2026-03-22 09:18:26	\N	\N
401	23	VKPIPYGN	copias	consulta_saldo	t	Lectura exitosa	10.214.202.173		6	{}	2026-03-21 02:16:26	2026-03-21 02:16:26	\N	\N
402	23	VKPIPYGN	souvenirs	consulta_saldo	t	Lectura exitosa	192.168.252.250		6	{}	2026-03-22 15:25:26	2026-03-22 15:25:26	\N	\N
403	23	VKPIPYGN	acceso	consumo	t	Lectura exitosa	172.18.15.252		6	{}	2026-03-21 15:11:26	2026-03-21 15:11:26	\N	\N
404	23	VKPIPYGN	copias	consumo	t	Lectura exitosa	172.29.156.2		4	{}	2026-03-22 15:54:26	2026-03-22 15:54:26	\N	\N
405	23	VKPIPYGN	biblioteca	confirmacion_entrega	t	Lectura exitosa	192.168.116.147		5	{}	2026-03-21 15:18:26	2026-03-21 15:18:26	\N	\N
406	23	VKPIPYGN	acceso	consulta_saldo	t	Lectura exitosa	10.44.100.24		5	{}	2026-03-21 00:57:26	2026-03-21 00:57:26	\N	\N
407	23	VKPIPYGN	souvenirs	consumo	t	Lectura exitosa	192.168.154.98		7	{}	2026-03-22 16:56:26	2026-03-22 16:56:26	\N	\N
408	23	VKPIPYGN	souvenirs	confirmacion_entrega	t	Lectura exitosa	172.26.34.179		8	{}	2026-03-22 02:16:26	2026-03-22 02:16:26	\N	\N
409	23	VKPIPYGN	copias	consulta_saldo	t	Lectura exitosa	192.168.51.227		5	{}	2026-03-22 16:48:26	2026-03-22 16:48:26	\N	\N
410	23	VKPIPYGN	cafeteria	confirmacion_entrega	t	Lectura exitosa	10.109.16.52		7	{}	2026-03-21 12:25:26	2026-03-21 12:25:26	\N	\N
411	23	VKPIPYGN	cafeteria	consumo	f	Tarjeta activa	10.200.239.184		7	{}	2026-03-22 03:07:26	2026-03-22 03:07:26	\N	\N
412	23	VKPIPYGN	biblioteca	acceso	t	Lectura exitosa	10.38.209.112		8	{}	2026-03-21 15:45:26	2026-03-21 15:45:26	\N	\N
413	23	VKPIPYGN	cafeteria	acceso	f	Tarjeta activa	10.145.94.189		4	{}	2026-03-22 14:20:26	2026-03-22 14:20:26	\N	\N
414	23	VKPIPYGN	souvenirs	acceso	t	Lectura exitosa	172.17.29.58		7	{}	2026-03-21 11:18:26	2026-03-21 11:18:26	\N	\N
415	23	VKPIPYGN	copias	consulta_saldo	t	Lectura exitosa	192.168.97.183		6	{}	2026-03-22 03:11:26	2026-03-22 03:11:26	\N	\N
416	23	VKPIPYGN	copias	consulta_saldo	t	Lectura exitosa	192.168.233.100		7	{}	2026-03-21 15:34:26	2026-03-21 15:34:26	\N	\N
417	23	VKPIPYGN	biblioteca	confirmacion_entrega	t	Lectura exitosa	172.27.140.52		4	{}	2026-03-21 03:32:26	2026-03-21 03:32:26	\N	\N
418	24	5F7QGPZX	souvenirs	acceso	t	Lectura exitosa	172.27.144.151		8	{}	2026-03-21 12:28:26	2026-03-21 12:28:26	\N	\N
419	24	5F7QGPZX	copias	acceso	t	Lectura exitosa	10.169.221.131		8	{}	2026-03-21 15:30:26	2026-03-21 15:30:26	\N	\N
420	24	5F7QGPZX	copias	acceso	t	Lectura exitosa	192.168.19.190		5	{}	2026-03-22 02:20:26	2026-03-22 02:20:26	\N	\N
421	24	5F7QGPZX	biblioteca	consumo	t	Lectura exitosa	10.227.139.70		8	{}	2026-03-22 08:43:26	2026-03-22 08:43:26	\N	\N
422	24	5F7QGPZX	biblioteca	acceso	t	Lectura exitosa	192.168.3.242		5	{}	2026-03-21 11:25:26	2026-03-21 11:25:26	\N	\N
423	24	5F7QGPZX	cafeteria	consumo	t	Lectura exitosa	10.162.106.157		5	{}	2026-03-22 08:44:26	2026-03-22 08:44:26	\N	\N
424	24	5F7QGPZX	souvenirs	consulta_saldo	t	Lectura exitosa	172.17.85.226		7	{}	2026-03-21 10:55:26	2026-03-21 10:55:26	\N	\N
425	24	5F7QGPZX	biblioteca	consumo	f	Tarjeta activa	10.64.84.208		7	{}	2026-03-21 11:19:26	2026-03-21 11:19:26	\N	\N
426	24	5F7QGPZX	souvenirs	consumo	t	Lectura exitosa	10.158.119.61		7	{}	2026-03-21 04:48:26	2026-03-21 04:48:26	\N	\N
427	24	5F7QGPZX	copias	consumo	t	Lectura exitosa	192.168.153.87		5	{}	2026-03-21 15:08:26	2026-03-21 15:08:26	\N	\N
428	24	5F7QGPZX	acceso	acceso	t	Lectura exitosa	192.168.69.198		6	{}	2026-03-21 13:54:26	2026-03-21 13:54:26	\N	\N
429	24	5F7QGPZX	acceso	acceso	t	Lectura exitosa	172.24.231.225		8	{}	2026-03-21 06:12:26	2026-03-21 06:12:26	\N	\N
430	24	5F7QGPZX	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.252.8		6	{}	2026-03-22 01:29:26	2026-03-22 01:29:26	\N	\N
431	24	5F7QGPZX	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.183.143		7	{}	2026-03-21 11:37:26	2026-03-21 11:37:26	\N	\N
432	24	5F7QGPZX	acceso	confirmacion_entrega	f	Tarjeta activa	172.30.19.17		8	{}	2026-03-22 01:10:26	2026-03-22 01:10:26	\N	\N
433	24	5F7QGPZX	biblioteca	consulta_saldo	t	Lectura exitosa	10.247.190.235		6	{}	2026-03-21 16:19:26	2026-03-21 16:19:26	\N	\N
434	24	5F7QGPZX	acceso	consulta_saldo	t	Lectura exitosa	10.149.65.200		8	{}	2026-03-21 00:45:26	2026-03-21 00:45:26	\N	\N
435	24	5F7QGPZX	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.228.143		4	{}	2026-03-21 00:42:26	2026-03-21 00:42:26	\N	\N
436	24	5F7QGPZX	acceso	acceso	t	Lectura exitosa	192.168.143.9		7	{}	2026-03-22 16:33:26	2026-03-22 16:33:26	\N	\N
437	24	5F7QGPZX	acceso	consumo	t	Lectura exitosa	172.31.37.116		8	{}	2026-03-21 03:31:26	2026-03-21 03:31:26	\N	\N
438	25	JRF24FNX	souvenirs	consulta_saldo	t	Lectura exitosa	192.168.88.102		7	{}	2026-03-21 16:12:26	2026-03-21 16:12:26	\N	\N
439	25	JRF24FNX	cafeteria	acceso	t	Lectura exitosa	172.22.12.175		4	{}	2026-03-22 09:52:26	2026-03-22 09:52:26	\N	\N
440	25	JRF24FNX	biblioteca	confirmacion_entrega	t	Lectura exitosa	10.241.43.136		4	{}	2026-03-22 08:22:26	2026-03-22 08:22:26	\N	\N
441	25	JRF24FNX	copias	consulta_saldo	t	Lectura exitosa	172.21.237.190		4	{}	2026-03-21 08:55:26	2026-03-21 08:55:26	\N	\N
442	25	JRF24FNX	copias	acceso	t	Lectura exitosa	192.168.24.255		6	{}	2026-03-21 05:17:26	2026-03-21 05:17:26	\N	\N
443	25	JRF24FNX	cafeteria	consumo	f	Tarjeta activa	10.174.233.209		4	{}	2026-03-22 05:16:26	2026-03-22 05:16:26	\N	\N
444	25	JRF24FNX	souvenirs	acceso	f	Tarjeta activa	172.22.14.233		5	{}	2026-03-21 13:50:26	2026-03-21 13:50:26	\N	\N
445	25	JRF24FNX	acceso	acceso	t	Lectura exitosa	10.72.139.230		4	{}	2026-03-21 14:56:26	2026-03-21 14:56:26	\N	\N
446	25	JRF24FNX	acceso	consumo	t	Lectura exitosa	192.168.42.129		7	{}	2026-03-22 16:09:26	2026-03-22 16:09:26	\N	\N
447	25	JRF24FNX	souvenirs	consulta_saldo	t	Lectura exitosa	10.138.125.136		7	{}	2026-03-22 05:03:26	2026-03-22 05:03:26	\N	\N
448	26	HKE181BN	cafeteria	consulta_saldo	t	Lectura exitosa	172.17.156.194		5	{}	2026-03-22 03:42:26	2026-03-22 03:42:26	\N	\N
449	26	HKE181BN	copias	acceso	t	Lectura exitosa	172.19.243.188		8	{}	2026-03-21 04:34:26	2026-03-21 04:34:26	\N	\N
450	26	HKE181BN	souvenirs	consumo	t	Lectura exitosa	192.168.171.120		8	{}	2026-03-21 02:41:26	2026-03-21 02:41:26	\N	\N
451	26	HKE181BN	biblioteca	confirmacion_entrega	f	Tarjeta activa	172.20.130.208		8	{}	2026-03-22 13:17:26	2026-03-22 13:17:26	\N	\N
452	26	HKE181BN	souvenirs	acceso	t	Lectura exitosa	192.168.127.208		6	{}	2026-03-21 09:59:26	2026-03-21 09:59:26	\N	\N
453	26	HKE181BN	souvenirs	confirmacion_entrega	t	Lectura exitosa	192.168.117.7		7	{}	2026-03-22 07:55:26	2026-03-22 07:55:26	\N	\N
454	26	HKE181BN	souvenirs	consulta_saldo	t	Lectura exitosa	192.168.195.86		6	{}	2026-03-21 05:22:26	2026-03-21 05:22:26	\N	\N
455	26	HKE181BN	copias	consulta_saldo	f	Tarjeta activa	192.168.96.79		6	{}	2026-03-22 01:40:26	2026-03-22 01:40:26	\N	\N
456	26	HKE181BN	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.88.45		8	{}	2026-03-21 12:21:26	2026-03-21 12:21:26	\N	\N
457	26	HKE181BN	acceso	consulta_saldo	t	Lectura exitosa	192.168.252.106		6	{}	2026-03-22 13:31:26	2026-03-22 13:31:26	\N	\N
458	26	HKE181BN	souvenirs	acceso	t	Lectura exitosa	192.168.22.125		5	{}	2026-03-21 00:35:26	2026-03-21 00:35:26	\N	\N
459	26	HKE181BN	souvenirs	consumo	t	Lectura exitosa	192.168.35.102		4	{}	2026-03-21 09:02:26	2026-03-21 09:02:26	\N	\N
460	26	HKE181BN	acceso	confirmacion_entrega	t	Lectura exitosa	10.215.116.89		4	{}	2026-03-22 11:04:26	2026-03-22 11:04:26	\N	\N
461	26	HKE181BN	souvenirs	acceso	t	Lectura exitosa	10.135.29.119		6	{}	2026-03-21 10:15:26	2026-03-21 10:15:26	\N	\N
462	26	HKE181BN	acceso	acceso	t	Lectura exitosa	192.168.41.191		4	{}	2026-03-22 06:35:26	2026-03-22 06:35:26	\N	\N
463	26	HKE181BN	cafeteria	consulta_saldo	t	Lectura exitosa	10.131.105.192		7	{}	2026-03-21 11:56:26	2026-03-21 11:56:26	\N	\N
464	26	HKE181BN	acceso	consumo	t	Lectura exitosa	10.102.2.62		4	{}	2026-03-21 04:27:26	2026-03-21 04:27:26	\N	\N
465	26	HKE181BN	souvenirs	acceso	t	Lectura exitosa	172.23.231.195		5	{}	2026-03-22 02:15:26	2026-03-22 02:15:26	\N	\N
466	26	HKE181BN	copias	confirmacion_entrega	t	Lectura exitosa	172.20.253.104		8	{}	2026-03-22 04:55:26	2026-03-22 04:55:26	\N	\N
467	26	HKE181BN	copias	acceso	t	Lectura exitosa	172.16.81.113		8	{}	2026-03-21 05:33:26	2026-03-21 05:33:26	\N	\N
468	26	HKE181BN	souvenirs	consumo	t	Lectura exitosa	192.168.218.49		8	{}	2026-03-21 13:28:26	2026-03-21 13:28:26	\N	\N
469	26	HKE181BN	copias	consulta_saldo	t	Lectura exitosa	172.30.131.62		4	{}	2026-03-22 04:21:26	2026-03-22 04:21:26	\N	\N
470	26	HKE181BN	cafeteria	consumo	t	Lectura exitosa	10.126.161.144		6	{}	2026-03-22 02:53:26	2026-03-22 02:53:26	\N	\N
471	26	HKE181BN	cafeteria	confirmacion_entrega	t	Lectura exitosa	10.42.193.51		8	{}	2026-03-22 10:02:26	2026-03-22 10:02:26	\N	\N
472	26	HKE181BN	acceso	consumo	t	Lectura exitosa	172.23.27.135		7	{}	2026-03-22 00:36:26	2026-03-22 00:36:26	\N	\N
473	26	HKE181BN	souvenirs	acceso	t	Lectura exitosa	192.168.35.16		5	{}	2026-03-22 03:32:26	2026-03-22 03:32:26	\N	\N
474	27	V2YM0HHG	copias	acceso	t	Lectura exitosa	10.201.241.22		7	{}	2026-03-21 08:13:26	2026-03-21 08:13:26	\N	\N
475	27	V2YM0HHG	copias	confirmacion_entrega	t	Lectura exitosa	172.31.15.122		5	{}	2026-03-22 09:56:26	2026-03-22 09:56:26	\N	\N
476	27	V2YM0HHG	souvenirs	acceso	t	Lectura exitosa	10.62.44.25		5	{}	2026-03-22 07:48:26	2026-03-22 07:48:26	\N	\N
477	27	V2YM0HHG	biblioteca	consumo	f	Tarjeta activa	192.168.28.116		4	{}	2026-03-21 15:35:26	2026-03-21 15:35:26	\N	\N
478	27	V2YM0HHG	cafeteria	confirmacion_entrega	t	Lectura exitosa	172.21.174.247		7	{}	2026-03-21 09:43:26	2026-03-21 09:43:26	\N	\N
479	27	V2YM0HHG	biblioteca	consumo	t	Lectura exitosa	192.168.108.75		7	{}	2026-03-22 17:05:26	2026-03-22 17:05:26	\N	\N
480	27	V2YM0HHG	copias	consumo	t	Lectura exitosa	10.26.23.122		6	{}	2026-03-22 04:04:26	2026-03-22 04:04:26	\N	\N
481	27	V2YM0HHG	cafeteria	consumo	t	Lectura exitosa	192.168.44.29		5	{}	2026-03-21 10:38:26	2026-03-21 10:38:26	\N	\N
482	27	V2YM0HHG	biblioteca	consumo	t	Lectura exitosa	172.22.160.167		4	{}	2026-03-21 08:46:26	2026-03-21 08:46:26	\N	\N
483	27	V2YM0HHG	copias	acceso	t	Lectura exitosa	192.168.135.225		7	{}	2026-03-22 10:03:26	2026-03-22 10:03:26	\N	\N
484	27	V2YM0HHG	cafeteria	consumo	f	Tarjeta activa	10.27.255.177		7	{}	2026-03-21 04:08:26	2026-03-21 04:08:26	\N	\N
485	27	V2YM0HHG	copias	consulta_saldo	t	Lectura exitosa	192.168.99.62		4	{}	2026-03-22 06:55:26	2026-03-22 06:55:26	\N	\N
486	27	V2YM0HHG	copias	consumo	t	Lectura exitosa	10.110.149.221		6	{}	2026-03-21 17:06:26	2026-03-21 17:06:26	\N	\N
487	27	V2YM0HHG	copias	confirmacion_entrega	t	Lectura exitosa	192.168.116.112		4	{}	2026-03-22 13:26:27	2026-03-22 13:26:27	\N	\N
488	27	V2YM0HHG	cafeteria	acceso	t	Lectura exitosa	192.168.183.242		4	{}	2026-03-21 14:14:27	2026-03-21 14:14:27	\N	\N
489	27	V2YM0HHG	acceso	acceso	t	Lectura exitosa	10.111.149.184		5	{}	2026-03-21 16:04:27	2026-03-21 16:04:27	\N	\N
490	27	V2YM0HHG	biblioteca	consumo	t	Lectura exitosa	10.116.29.42		6	{}	2026-03-21 10:57:27	2026-03-21 10:57:27	\N	\N
491	27	V2YM0HHG	cafeteria	consulta_saldo	t	Lectura exitosa	172.27.27.35		5	{}	2026-03-22 13:15:27	2026-03-22 13:15:27	\N	\N
492	27	V2YM0HHG	souvenirs	acceso	t	Lectura exitosa	172.27.141.254		5	{}	2026-03-22 05:25:27	2026-03-22 05:25:27	\N	\N
493	27	V2YM0HHG	biblioteca	consumo	t	Lectura exitosa	10.39.157.7		5	{}	2026-03-21 00:46:27	2026-03-21 00:46:27	\N	\N
494	27	V2YM0HHG	copias	consumo	f	Tarjeta activa	192.168.174.92		7	{}	2026-03-22 07:50:27	2026-03-22 07:50:27	\N	\N
495	27	V2YM0HHG	copias	consulta_saldo	t	Lectura exitosa	192.168.24.97		7	{}	2026-03-21 06:50:27	2026-03-21 06:50:27	\N	\N
496	27	V2YM0HHG	copias	consulta_saldo	t	Lectura exitosa	172.23.51.91		6	{}	2026-03-21 10:12:27	2026-03-21 10:12:27	\N	\N
497	27	V2YM0HHG	cafeteria	acceso	t	Lectura exitosa	192.168.161.174		8	{}	2026-03-21 02:52:27	2026-03-21 02:52:27	\N	\N
498	28	BIBPJ4HX	copias	consulta_saldo	f	Tarjeta perdida	192.168.118.186		7	{}	2026-03-22 12:43:27	2026-03-22 12:43:27	\N	\N
499	28	BIBPJ4HX	cafeteria	acceso	f	Tarjeta perdida	172.28.119.71		8	{}	2026-03-21 12:43:27	2026-03-21 12:43:27	\N	\N
500	28	BIBPJ4HX	souvenirs	consumo	f	Tarjeta perdida	10.11.206.129		7	{}	2026-03-22 01:37:27	2026-03-22 01:37:27	\N	\N
501	28	BIBPJ4HX	copias	confirmacion_entrega	f	Tarjeta perdida	172.31.11.161		7	{}	2026-03-22 04:44:27	2026-03-22 04:44:27	\N	\N
502	28	BIBPJ4HX	biblioteca	consumo	f	Tarjeta perdida	172.30.233.47		8	{}	2026-03-22 00:41:27	2026-03-22 00:41:27	\N	\N
503	28	BIBPJ4HX	copias	consulta_saldo	f	Tarjeta perdida	172.19.129.90		8	{}	2026-03-22 04:13:27	2026-03-22 04:13:27	\N	\N
504	28	BIBPJ4HX	copias	consulta_saldo	f	Tarjeta perdida	172.28.194.153		8	{}	2026-03-21 03:06:27	2026-03-21 03:06:27	\N	\N
505	28	BIBPJ4HX	souvenirs	acceso	f	Tarjeta perdida	192.168.125.103		7	{}	2026-03-22 09:47:27	2026-03-22 09:47:27	\N	\N
506	28	BIBPJ4HX	souvenirs	consumo	f	Tarjeta perdida	192.168.218.30		8	{}	2026-03-21 09:06:27	2026-03-21 09:06:27	\N	\N
507	28	BIBPJ4HX	souvenirs	confirmacion_entrega	f	Tarjeta perdida	10.249.222.31		7	{}	2026-03-21 07:25:27	2026-03-21 07:25:27	\N	\N
508	28	BIBPJ4HX	souvenirs	confirmacion_entrega	f	Tarjeta perdida	192.168.2.61		6	{}	2026-03-22 16:02:27	2026-03-22 16:02:27	\N	\N
509	28	BIBPJ4HX	biblioteca	confirmacion_entrega	f	Tarjeta perdida	172.29.40.166		8	{}	2026-03-21 06:41:27	2026-03-21 06:41:27	\N	\N
510	28	BIBPJ4HX	copias	acceso	f	Tarjeta perdida	10.204.118.199		8	{}	2026-03-21 15:04:27	2026-03-21 15:04:27	\N	\N
511	28	BIBPJ4HX	souvenirs	confirmacion_entrega	f	Tarjeta perdida	192.168.62.189		6	{}	2026-03-22 08:31:27	2026-03-22 08:31:27	\N	\N
512	28	BIBPJ4HX	souvenirs	consulta_saldo	f	Tarjeta perdida	172.27.48.251		4	{}	2026-03-21 07:15:27	2026-03-21 07:15:27	\N	\N
513	28	BIBPJ4HX	cafeteria	acceso	f	Tarjeta perdida	192.168.38.99		4	{}	2026-03-21 14:09:27	2026-03-21 14:09:27	\N	\N
514	28	BIBPJ4HX	copias	confirmacion_entrega	f	Tarjeta perdida	10.140.78.211		5	{}	2026-03-22 16:29:27	2026-03-22 16:29:27	\N	\N
515	29	ZFZE7TRP	copias	acceso	f	Tarjeta perdida	172.25.224.206		6	{}	2026-03-21 16:57:27	2026-03-21 16:57:27	\N	\N
516	29	ZFZE7TRP	souvenirs	consumo	f	Tarjeta perdida	172.31.168.107		4	{}	2026-03-22 01:44:27	2026-03-22 01:44:27	\N	\N
517	29	ZFZE7TRP	souvenirs	acceso	f	Tarjeta perdida	172.20.71.233		7	{}	2026-03-21 05:19:27	2026-03-21 05:19:27	\N	\N
518	29	ZFZE7TRP	copias	confirmacion_entrega	f	Tarjeta perdida	172.23.236.157		8	{}	2026-03-22 12:27:27	2026-03-22 12:27:27	\N	\N
519	29	ZFZE7TRP	biblioteca	consulta_saldo	f	Tarjeta perdida	172.23.101.171		6	{}	2026-03-22 07:48:27	2026-03-22 07:48:27	\N	\N
520	29	ZFZE7TRP	souvenirs	consulta_saldo	f	Tarjeta perdida	172.29.103.34		7	{}	2026-03-22 08:26:27	2026-03-22 08:26:27	\N	\N
521	29	ZFZE7TRP	copias	consumo	f	Tarjeta perdida	172.29.43.196		6	{}	2026-03-21 00:48:27	2026-03-21 00:48:27	\N	\N
522	29	ZFZE7TRP	acceso	confirmacion_entrega	f	Tarjeta perdida	10.104.75.169		5	{}	2026-03-22 13:43:27	2026-03-22 13:43:27	\N	\N
523	29	ZFZE7TRP	copias	consulta_saldo	f	Tarjeta perdida	172.24.217.236		4	{}	2026-03-21 06:57:27	2026-03-21 06:57:27	\N	\N
524	29	ZFZE7TRP	copias	consumo	f	Tarjeta perdida	172.19.247.211		6	{}	2026-03-21 00:27:27	2026-03-21 00:27:27	\N	\N
525	29	ZFZE7TRP	acceso	consulta_saldo	f	Tarjeta perdida	192.168.97.72		8	{}	2026-03-21 06:00:27	2026-03-21 06:00:27	\N	\N
526	29	ZFZE7TRP	copias	consulta_saldo	f	Tarjeta perdida	172.25.243.64		5	{}	2026-03-21 10:51:27	2026-03-21 10:51:27	\N	\N
527	29	ZFZE7TRP	copias	acceso	f	Tarjeta perdida	172.29.194.253		4	{}	2026-03-22 12:09:27	2026-03-22 12:09:27	\N	\N
528	29	ZFZE7TRP	souvenirs	consulta_saldo	f	Tarjeta perdida	192.168.156.121		5	{}	2026-03-22 03:21:27	2026-03-22 03:21:27	\N	\N
529	29	ZFZE7TRP	biblioteca	acceso	f	Tarjeta perdida	172.16.36.152		4	{}	2026-03-21 02:10:27	2026-03-21 02:10:27	\N	\N
530	29	ZFZE7TRP	souvenirs	consumo	f	Tarjeta perdida	10.224.98.210		6	{}	2026-03-22 02:30:27	2026-03-22 02:30:27	\N	\N
531	30	XA4B586L	cafeteria	confirmacion_entrega	t	Lectura exitosa	172.24.107.5		7	{}	2026-03-22 16:56:27	2026-03-22 16:56:27	\N	\N
532	30	XA4B586L	copias	consumo	t	Lectura exitosa	192.168.230.196		4	{}	2026-03-22 10:30:27	2026-03-22 10:30:27	\N	\N
533	30	XA4B586L	acceso	confirmacion_entrega	t	Lectura exitosa	192.168.245.182		5	{}	2026-03-22 03:54:27	2026-03-22 03:54:27	\N	\N
534	30	XA4B586L	biblioteca	acceso	t	Lectura exitosa	172.26.104.131		8	{}	2026-03-21 05:46:27	2026-03-21 05:46:27	\N	\N
535	30	XA4B586L	biblioteca	acceso	t	Lectura exitosa	192.168.230.31		6	{}	2026-03-21 03:17:27	2026-03-21 03:17:27	\N	\N
536	30	XA4B586L	copias	consulta_saldo	t	Lectura exitosa	10.39.153.62		4	{}	2026-03-21 15:02:27	2026-03-21 15:02:27	\N	\N
537	30	XA4B586L	biblioteca	acceso	t	Lectura exitosa	192.168.36.150		8	{}	2026-03-21 07:54:27	2026-03-21 07:54:27	\N	\N
538	30	XA4B586L	acceso	acceso	t	Lectura exitosa	192.168.54.41		6	{}	2026-03-22 12:21:27	2026-03-22 12:21:27	\N	\N
539	30	XA4B586L	biblioteca	consulta_saldo	t	Lectura exitosa	172.19.79.41		4	{}	2026-03-22 12:03:27	2026-03-22 12:03:27	\N	\N
540	30	XA4B586L	biblioteca	confirmacion_entrega	t	Lectura exitosa	192.168.111.230		8	{}	2026-03-22 04:39:27	2026-03-22 04:39:27	\N	\N
541	30	XA4B586L	cafeteria	consumo	t	Lectura exitosa	192.168.149.245		7	{}	2026-03-22 13:45:27	2026-03-22 13:45:27	\N	\N
542	30	XA4B586L	copias	consumo	t	Lectura exitosa	192.168.64.144		5	{}	2026-03-21 15:18:27	2026-03-21 15:18:27	\N	\N
543	31	ZBVQLKBQ	biblioteca	acceso	t	Lectura exitosa	172.24.191.52		5	{}	2026-03-22 00:31:27	2026-03-22 00:31:27	\N	\N
544	31	ZBVQLKBQ	acceso	consulta_saldo	t	Lectura exitosa	172.24.61.236		5	{}	2026-03-22 17:03:27	2026-03-22 17:03:27	\N	\N
545	31	ZBVQLKBQ	souvenirs	consulta_saldo	t	Lectura exitosa	10.64.223.211		5	{}	2026-03-22 15:20:27	2026-03-22 15:20:27	\N	\N
546	31	ZBVQLKBQ	souvenirs	confirmacion_entrega	t	Lectura exitosa	10.127.105.178		7	{}	2026-03-21 09:16:27	2026-03-21 09:16:27	\N	\N
547	31	ZBVQLKBQ	acceso	consulta_saldo	t	Lectura exitosa	192.168.244.108		7	{}	2026-03-22 07:08:27	2026-03-22 07:08:27	\N	\N
548	31	ZBVQLKBQ	souvenirs	consumo	t	Lectura exitosa	10.231.105.141		7	{}	2026-03-21 07:07:27	2026-03-21 07:07:27	\N	\N
549	31	ZBVQLKBQ	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.183.183		6	{}	2026-03-22 05:26:27	2026-03-22 05:26:27	\N	\N
550	31	ZBVQLKBQ	acceso	consumo	t	Lectura exitosa	10.75.192.156		8	{}	2026-03-22 03:04:27	2026-03-22 03:04:27	\N	\N
551	31	ZBVQLKBQ	copias	confirmacion_entrega	t	Lectura exitosa	172.27.85.42		8	{}	2026-03-22 11:37:27	2026-03-22 11:37:27	\N	\N
552	31	ZBVQLKBQ	cafeteria	acceso	f	Tarjeta activa	172.18.242.229		5	{}	2026-03-21 01:22:27	2026-03-21 01:22:27	\N	\N
553	31	ZBVQLKBQ	cafeteria	confirmacion_entrega	t	Lectura exitosa	192.168.152.144		5	{}	2026-03-21 02:45:27	2026-03-21 02:45:27	\N	\N
554	31	ZBVQLKBQ	cafeteria	consumo	t	Lectura exitosa	172.28.109.112		4	{}	2026-03-22 07:27:27	2026-03-22 07:27:27	\N	\N
555	31	ZBVQLKBQ	copias	confirmacion_entrega	t	Lectura exitosa	172.31.98.139		5	{}	2026-03-22 14:04:27	2026-03-22 14:04:27	\N	\N
556	32	A7YPLFA4	souvenirs	confirmacion_entrega	t	Lectura exitosa	172.17.86.159		6	{}	2026-03-22 08:58:27	2026-03-22 08:58:27	\N	\N
557	32	A7YPLFA4	copias	consulta_saldo	t	Lectura exitosa	172.25.132.198		4	{}	2026-03-22 10:23:27	2026-03-22 10:23:27	\N	\N
558	32	A7YPLFA4	biblioteca	confirmacion_entrega	t	Lectura exitosa	10.163.11.140		7	{}	2026-03-21 14:05:27	2026-03-21 14:05:27	\N	\N
559	32	A7YPLFA4	cafeteria	acceso	t	Lectura exitosa	10.196.194.109		6	{}	2026-03-21 10:14:27	2026-03-21 10:14:27	\N	\N
560	32	A7YPLFA4	acceso	consulta_saldo	t	Lectura exitosa	10.219.249.244		7	{}	2026-03-22 14:36:27	2026-03-22 14:36:27	\N	\N
561	32	A7YPLFA4	copias	consumo	t	Lectura exitosa	10.112.144.53		8	{}	2026-03-22 02:27:27	2026-03-22 02:27:27	\N	\N
562	32	A7YPLFA4	souvenirs	acceso	t	Lectura exitosa	192.168.22.112		8	{}	2026-03-21 07:36:27	2026-03-21 07:36:27	\N	\N
563	32	A7YPLFA4	souvenirs	acceso	t	Lectura exitosa	10.255.125.126		7	{}	2026-03-21 16:53:27	2026-03-21 16:53:27	\N	\N
564	32	A7YPLFA4	copias	acceso	t	Lectura exitosa	10.163.249.78		4	{}	2026-03-21 14:24:27	2026-03-21 14:24:27	\N	\N
565	32	A7YPLFA4	cafeteria	consulta_saldo	f	Tarjeta activa	172.31.147.172		5	{}	2026-03-21 07:10:27	2026-03-21 07:10:27	\N	\N
566	32	A7YPLFA4	copias	consulta_saldo	t	Lectura exitosa	192.168.101.59		7	{}	2026-03-22 01:12:27	2026-03-22 01:12:27	\N	\N
567	32	A7YPLFA4	copias	consumo	t	Lectura exitosa	172.25.175.54		5	{}	2026-03-21 03:02:27	2026-03-21 03:02:27	\N	\N
568	32	A7YPLFA4	biblioteca	consumo	t	Lectura exitosa	172.19.76.223		4	{}	2026-03-22 14:21:27	2026-03-22 14:21:27	\N	\N
569	32	A7YPLFA4	biblioteca	acceso	t	Lectura exitosa	192.168.65.218		6	{}	2026-03-22 09:47:27	2026-03-22 09:47:27	\N	\N
570	32	A7YPLFA4	cafeteria	acceso	t	Lectura exitosa	172.18.239.73		5	{}	2026-03-21 08:51:27	2026-03-21 08:51:27	\N	\N
571	32	A7YPLFA4	copias	confirmacion_entrega	t	Lectura exitosa	172.23.19.127		4	{}	2026-03-22 15:47:27	2026-03-22 15:47:27	\N	\N
572	32	A7YPLFA4	biblioteca	consumo	t	Lectura exitosa	10.206.58.39		5	{}	2026-03-21 07:53:27	2026-03-21 07:53:27	\N	\N
573	32	A7YPLFA4	souvenirs	consumo	t	Lectura exitosa	172.18.165.82		7	{}	2026-03-22 02:04:27	2026-03-22 02:04:27	\N	\N
574	33	6YAN5YLX	biblioteca	acceso	f	Tarjeta perdida	10.39.218.123		4	{}	2026-03-21 05:23:27	2026-03-21 05:23:27	\N	\N
575	33	6YAN5YLX	copias	consumo	f	Tarjeta perdida	172.21.248.76		5	{}	2026-03-22 15:17:27	2026-03-22 15:17:27	\N	\N
576	33	6YAN5YLX	acceso	consumo	f	Tarjeta perdida	192.168.32.24		5	{}	2026-03-22 08:16:27	2026-03-22 08:16:27	\N	\N
577	33	6YAN5YLX	acceso	consulta_saldo	f	Tarjeta perdida	192.168.22.56		5	{}	2026-03-22 13:24:27	2026-03-22 13:24:27	\N	\N
578	33	6YAN5YLX	cafeteria	consumo	f	Tarjeta perdida	192.168.176.181		8	{}	2026-03-21 11:27:27	2026-03-21 11:27:27	\N	\N
579	33	6YAN5YLX	souvenirs	consulta_saldo	f	Tarjeta perdida	172.23.34.230		4	{}	2026-03-21 16:26:27	2026-03-21 16:26:27	\N	\N
580	33	6YAN5YLX	copias	acceso	f	Tarjeta perdida	192.168.3.221		5	{}	2026-03-21 12:13:27	2026-03-21 12:13:27	\N	\N
581	33	6YAN5YLX	cafeteria	consulta_saldo	f	Tarjeta perdida	10.101.116.155		7	{}	2026-03-21 01:43:27	2026-03-21 01:43:27	\N	\N
582	33	6YAN5YLX	biblioteca	confirmacion_entrega	f	Tarjeta perdida	172.24.114.107		4	{}	2026-03-21 12:07:27	2026-03-21 12:07:27	\N	\N
583	33	6YAN5YLX	cafeteria	consumo	f	Tarjeta perdida	192.168.57.158		4	{}	2026-03-22 13:50:27	2026-03-22 13:50:27	\N	\N
584	33	6YAN5YLX	cafeteria	confirmacion_entrega	f	Tarjeta perdida	10.18.204.24		5	{}	2026-03-22 03:26:27	2026-03-22 03:26:27	\N	\N
585	33	6YAN5YLX	cafeteria	acceso	f	Tarjeta perdida	172.27.80.46		6	{}	2026-03-21 13:58:27	2026-03-21 13:58:27	\N	\N
586	33	6YAN5YLX	acceso	confirmacion_entrega	f	Tarjeta perdida	172.27.228.131		6	{}	2026-03-21 07:48:27	2026-03-21 07:48:27	\N	\N
587	33	6YAN5YLX	biblioteca	consumo	f	Tarjeta perdida	192.168.71.49		4	{}	2026-03-21 06:29:27	2026-03-21 06:29:27	\N	\N
588	33	6YAN5YLX	biblioteca	confirmacion_entrega	f	Tarjeta perdida	10.148.160.186		7	{}	2026-03-22 04:09:27	2026-03-22 04:09:27	\N	\N
589	33	6YAN5YLX	copias	acceso	f	Tarjeta perdida	192.168.79.52		4	{}	2026-03-21 12:12:27	2026-03-21 12:12:27	\N	\N
590	33	6YAN5YLX	copias	confirmacion_entrega	f	Tarjeta perdida	192.168.240.157		6	{}	2026-03-22 11:07:27	2026-03-22 11:07:27	\N	\N
591	33	6YAN5YLX	acceso	confirmacion_entrega	f	Tarjeta perdida	10.200.177.191		8	{}	2026-03-21 13:00:27	2026-03-21 13:00:27	\N	\N
592	33	6YAN5YLX	souvenirs	acceso	f	Tarjeta perdida	192.168.1.153		6	{}	2026-03-22 14:03:27	2026-03-22 14:03:27	\N	\N
593	33	6YAN5YLX	souvenirs	confirmacion_entrega	f	Tarjeta perdida	192.168.160.145		5	{}	2026-03-21 01:42:27	2026-03-21 01:42:27	\N	\N
594	34	S5GZOYID	cafeteria	consumo	t	Lectura exitosa	172.29.186.203		8	{}	2026-03-22 09:40:27	2026-03-22 09:40:27	\N	\N
595	34	S5GZOYID	cafeteria	acceso	f	Tarjeta activa	192.168.124.19		5	{}	2026-03-21 04:16:27	2026-03-21 04:16:27	\N	\N
596	34	S5GZOYID	biblioteca	acceso	t	Lectura exitosa	192.168.210.243		5	{}	2026-03-21 01:58:27	2026-03-21 01:58:27	\N	\N
597	34	S5GZOYID	biblioteca	consulta_saldo	t	Lectura exitosa	10.86.82.215		4	{}	2026-03-22 12:53:27	2026-03-22 12:53:27	\N	\N
598	34	S5GZOYID	acceso	consumo	t	Lectura exitosa	172.30.33.221		7	{}	2026-03-22 07:33:27	2026-03-22 07:33:27	\N	\N
599	34	S5GZOYID	souvenirs	consumo	t	Lectura exitosa	192.168.97.86		7	{}	2026-03-21 05:00:27	2026-03-21 05:00:27	\N	\N
600	34	S5GZOYID	biblioteca	consumo	t	Lectura exitosa	192.168.103.207		8	{}	2026-03-22 11:51:27	2026-03-22 11:51:27	\N	\N
601	34	S5GZOYID	acceso	consulta_saldo	t	Lectura exitosa	10.169.243.34		4	{}	2026-03-22 14:53:27	2026-03-22 14:53:27	\N	\N
602	34	S5GZOYID	cafeteria	consumo	t	Lectura exitosa	172.18.183.128		4	{}	2026-03-22 06:25:27	2026-03-22 06:25:27	\N	\N
603	34	S5GZOYID	acceso	acceso	t	Lectura exitosa	172.22.30.218		6	{}	2026-03-21 14:54:27	2026-03-21 14:54:27	\N	\N
604	34	S5GZOYID	biblioteca	confirmacion_entrega	t	Lectura exitosa	192.168.120.218		8	{}	2026-03-22 04:33:27	2026-03-22 04:33:27	\N	\N
605	34	S5GZOYID	souvenirs	confirmacion_entrega	t	Lectura exitosa	172.29.200.99		6	{}	2026-03-21 08:38:27	2026-03-21 08:38:27	\N	\N
606	34	S5GZOYID	souvenirs	acceso	t	Lectura exitosa	192.168.212.30		4	{}	2026-03-21 04:35:27	2026-03-21 04:35:27	\N	\N
607	34	S5GZOYID	cafeteria	consulta_saldo	t	Lectura exitosa	192.168.249.1		7	{}	2026-03-22 14:10:27	2026-03-22 14:10:27	\N	\N
608	34	S5GZOYID	cafeteria	consumo	t	Lectura exitosa	172.26.162.123		8	{}	2026-03-22 15:19:27	2026-03-22 15:19:27	\N	\N
609	34	S5GZOYID	biblioteca	consumo	t	Lectura exitosa	192.168.26.102		4	{}	2026-03-21 14:14:27	2026-03-21 14:14:27	\N	\N
610	34	S5GZOYID	cafeteria	consumo	t	Lectura exitosa	10.68.165.205		7	{}	2026-03-22 12:51:27	2026-03-22 12:51:27	\N	\N
611	34	S5GZOYID	acceso	acceso	t	Lectura exitosa	172.31.27.71		5	{}	2026-03-22 07:04:27	2026-03-22 07:04:27	\N	\N
612	34	S5GZOYID	cafeteria	consumo	t	Lectura exitosa	172.21.165.42		8	{}	2026-03-22 14:34:27	2026-03-22 14:34:27	\N	\N
613	34	S5GZOYID	acceso	confirmacion_entrega	t	Lectura exitosa	192.168.143.123		8	{}	2026-03-22 07:17:27	2026-03-22 07:17:27	\N	\N
614	34	S5GZOYID	biblioteca	consulta_saldo	f	Tarjeta activa	10.250.241.234		6	{}	2026-03-21 16:35:27	2026-03-21 16:35:27	\N	\N
615	35	GQ6TU3WI	cafeteria	consulta_saldo	f	Tarjeta bloqueada	10.236.131.214		8	{}	2026-03-21 16:54:27	2026-03-21 16:54:27	\N	\N
616	35	GQ6TU3WI	cafeteria	consumo	f	Tarjeta bloqueada	172.20.217.152		7	{}	2026-03-21 00:56:27	2026-03-21 00:56:27	\N	\N
617	35	GQ6TU3WI	biblioteca	consumo	f	Tarjeta bloqueada	192.168.77.36		5	{}	2026-03-21 00:28:27	2026-03-21 00:28:27	\N	\N
618	35	GQ6TU3WI	copias	acceso	f	Tarjeta bloqueada	192.168.146.128		6	{}	2026-03-21 12:26:27	2026-03-21 12:26:27	\N	\N
619	35	GQ6TU3WI	cafeteria	acceso	f	Tarjeta bloqueada	10.22.184.84		8	{}	2026-03-21 16:17:27	2026-03-21 16:17:27	\N	\N
620	35	GQ6TU3WI	biblioteca	consumo	f	Tarjeta bloqueada	192.168.158.248		7	{}	2026-03-22 14:10:27	2026-03-22 14:10:27	\N	\N
621	35	GQ6TU3WI	souvenirs	acceso	f	Tarjeta bloqueada	10.94.208.239		8	{}	2026-03-21 13:24:27	2026-03-21 13:24:27	\N	\N
622	35	GQ6TU3WI	cafeteria	consulta_saldo	f	Tarjeta bloqueada	172.24.180.28		6	{}	2026-03-21 05:43:27	2026-03-21 05:43:27	\N	\N
623	35	GQ6TU3WI	acceso	consulta_saldo	f	Tarjeta bloqueada	172.28.89.239		4	{}	2026-03-21 03:45:27	2026-03-21 03:45:27	\N	\N
624	35	GQ6TU3WI	biblioteca	acceso	f	Tarjeta bloqueada	10.168.138.131		5	{}	2026-03-21 05:26:27	2026-03-21 05:26:27	\N	\N
625	35	GQ6TU3WI	copias	confirmacion_entrega	f	Tarjeta bloqueada	192.168.50.145		4	{}	2026-03-21 04:48:27	2026-03-21 04:48:27	\N	\N
626	35	GQ6TU3WI	biblioteca	consumo	f	Tarjeta bloqueada	10.42.86.45		6	{}	2026-03-22 06:28:27	2026-03-22 06:28:27	\N	\N
627	35	GQ6TU3WI	copias	confirmacion_entrega	f	Tarjeta bloqueada	192.168.228.159		7	{}	2026-03-21 04:12:27	2026-03-21 04:12:27	\N	\N
628	35	GQ6TU3WI	copias	consulta_saldo	f	Tarjeta bloqueada	192.168.5.0		6	{}	2026-03-21 09:31:27	2026-03-21 09:31:27	\N	\N
629	35	GQ6TU3WI	copias	confirmacion_entrega	f	Tarjeta bloqueada	192.168.30.6		8	{}	2026-03-22 09:19:27	2026-03-22 09:19:27	\N	\N
630	35	GQ6TU3WI	cafeteria	consulta_saldo	f	Tarjeta bloqueada	10.227.218.146		7	{}	2026-03-22 08:04:27	2026-03-22 08:04:27	\N	\N
631	35	GQ6TU3WI	biblioteca	confirmacion_entrega	f	Tarjeta bloqueada	192.168.30.144		6	{}	2026-03-22 00:43:27	2026-03-22 00:43:27	\N	\N
632	35	GQ6TU3WI	cafeteria	confirmacion_entrega	f	Tarjeta bloqueada	172.25.87.33		4	{}	2026-03-21 10:39:27	2026-03-21 10:39:27	\N	\N
633	35	GQ6TU3WI	acceso	consumo	f	Tarjeta bloqueada	192.168.113.174		6	{}	2026-03-22 15:33:27	2026-03-22 15:33:27	\N	\N
634	35	GQ6TU3WI	souvenirs	consulta_saldo	f	Tarjeta bloqueada	172.21.228.23		4	{}	2026-03-22 03:03:27	2026-03-22 03:03:27	\N	\N
635	35	GQ6TU3WI	souvenirs	consumo	f	Tarjeta bloqueada	10.228.36.34		5	{}	2026-03-22 10:27:27	2026-03-22 10:27:27	\N	\N
636	35	GQ6TU3WI	copias	acceso	f	Tarjeta bloqueada	172.31.53.122		6	{}	2026-03-22 09:10:27	2026-03-22 09:10:27	\N	\N
\.


--
-- Data for Name: tarjeta_universitaria; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.tarjeta_universitaria (id, usuario_id, uid, estado, motivo_bloqueo, registrado_por_usuario_id, bloqueado_por_usuario_id, bloqueado_at, meta_json, created_at, updated_at, deleted_at, pin_hash) FROM stdin;
1	4	FJ6IR46N	perdida	\N	4	\N	\N	{}	2026-02-24 23:06:25	2026-03-23 23:06:25	\N	\N
2	5	OG09NWQJ	activa	\N	4	\N	\N	{}	2026-01-03 23:06:25	2026-03-23 23:06:25	\N	\N
3	6	0HC2Q03Y	activa	\N	4	\N	\N	{}	2026-01-20 23:06:25	2026-03-23 23:06:25	\N	\N
4	7	CFN9DUFR	activa	\N	4	\N	\N	{}	2026-03-04 23:06:25	2026-03-23 23:06:25	\N	\N
5	8	V2OG3C9Z	activa	\N	4	\N	\N	{}	2026-01-09 23:06:25	2026-03-23 23:06:25	\N	\N
6	9	AHHM5YX0	activa	\N	4	\N	\N	{}	2026-01-02 23:06:25	2026-03-23 23:06:25	\N	\N
7	10	7RMTSLXA	activa	\N	4	\N	\N	{}	2026-01-24 23:06:25	2026-03-23 23:06:25	\N	\N
8	11	O0XBN2CK	activa	\N	4	\N	\N	{}	2026-01-07 23:06:25	2026-03-23 23:06:25	\N	\N
9	12	UJIPZQFX	activa	\N	4	\N	\N	{}	2026-01-04 23:06:25	2026-03-23 23:06:25	\N	\N
10	14	Y3FW3M9T	bloqueada	Bloqueo preventivo por seguridad	4	4	2026-02-28 23:06:25	{}	2026-02-10 23:06:25	2026-03-23 23:06:25	\N	\N
11	15	EO8ZHR9H	activa	\N	4	\N	\N	{}	2026-03-15 23:06:25	2026-03-23 23:06:25	\N	\N
12	16	HOIVYRXH	activa	\N	4	\N	\N	{}	2026-01-14 23:06:25	2026-03-23 23:06:25	\N	\N
13	17	APDINIZM	activa	\N	4	\N	\N	{}	2026-02-26 23:06:25	2026-03-23 23:06:25	\N	\N
14	18	YSOV28TP	activa	\N	4	\N	\N	{}	2026-01-04 23:06:25	2026-03-23 23:06:25	\N	\N
15	19	ANSOK1GE	activa	\N	4	\N	\N	{}	2026-03-18 23:06:25	2026-03-23 23:06:25	\N	\N
16	20	B88GRDTT	activa	\N	4	\N	\N	{}	2026-02-07 23:06:25	2026-03-23 23:06:25	\N	\N
17	21	N79AGVXB	activa	\N	4	\N	\N	{}	2026-02-17 23:06:25	2026-03-23 23:06:25	\N	\N
18	23	MRM8VEMI	bloqueada	Bloqueo preventivo por seguridad	4	4	2026-03-15 23:06:25	{}	2026-02-10 23:06:25	2026-03-23 23:06:25	\N	\N
19	24	AZPQASEJ	bloqueada	Bloqueo preventivo por seguridad	4	4	2026-02-27 23:06:25	{}	2026-02-26 23:06:25	2026-03-23 23:06:25	\N	\N
20	26	8WD11TRP	activa	\N	4	\N	\N	{}	2026-02-11 23:06:25	2026-03-23 23:06:25	\N	\N
21	27	ZOU22ZE0	bloqueada	Bloqueo preventivo por seguridad	4	4	2026-03-19 23:06:25	{}	2026-01-18 23:06:25	2026-03-23 23:06:25	\N	\N
22	28	SOGHMV1W	activa	\N	4	\N	\N	{}	2026-03-05 23:06:25	2026-03-23 23:06:25	\N	\N
23	29	VKPIPYGN	activa	\N	4	\N	\N	{}	2026-01-26 23:06:25	2026-03-23 23:06:25	\N	\N
24	30	5F7QGPZX	activa	\N	4	\N	\N	{}	2026-02-09 23:06:25	2026-03-23 23:06:25	\N	\N
25	31	JRF24FNX	activa	\N	4	\N	\N	{}	2026-03-17 23:06:25	2026-03-23 23:06:25	\N	\N
26	32	HKE181BN	activa	\N	4	\N	\N	{}	2026-01-03 23:06:25	2026-03-23 23:06:25	\N	\N
27	34	V2YM0HHG	activa	\N	4	\N	\N	{}	2026-03-09 23:06:25	2026-03-23 23:06:25	\N	\N
28	35	BIBPJ4HX	perdida	\N	4	\N	\N	{}	2026-01-17 23:06:25	2026-03-23 23:06:25	\N	\N
29	36	ZFZE7TRP	perdida	\N	4	\N	\N	{}	2026-03-17 23:06:25	2026-03-23 23:06:25	\N	\N
30	37	XA4B586L	activa	\N	4	\N	\N	{}	2026-02-05 23:06:25	2026-03-23 23:06:25	\N	\N
31	38	ZBVQLKBQ	activa	\N	4	\N	\N	{}	2026-02-09 23:06:25	2026-03-23 23:06:25	\N	\N
32	39	A7YPLFA4	activa	\N	4	\N	\N	{}	2026-03-07 23:06:25	2026-03-23 23:06:25	\N	\N
33	40	6YAN5YLX	perdida	\N	4	\N	\N	{}	2026-03-03 23:06:25	2026-03-23 23:06:25	\N	\N
34	41	S5GZOYID	activa	\N	4	\N	\N	{}	2026-03-16 23:06:25	2026-03-23 23:06:25	\N	\N
35	42	GQ6TU3WI	bloqueada	Bloqueo preventivo por seguridad	4	4	2026-03-12 23:06:25	{}	2026-01-04 23:06:25	2026-03-23 23:06:25	\N	\N
\.


--
-- Data for Name: tienda; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.tienda (id, nombre, tipo, descripcion, activo, logo_url, color, created_at, updated_at, deleted_at, ubicacion) FROM stdin;
1	Cafetería Central	cafeteria	Desayunos, comidas y bebidas para toda la comunidad universitaria.	t	\N	#f59e0b	2026-03-26 00:28:48-06	2026-03-26 00:28:48-06	\N	\N
2	Papelería & Copias	papeleria	Impresiones, copias, encuadernados y artículos de papelería.	t	\N	#3b82f6	2026-03-26 00:28:48-06	2026-03-26 00:28:48-06	\N	\N
3	Mercadito Universitario	mercadito	Espacio para emprendedores y pequeños negocios del campus.	t	\N	#10b981	2026-03-26 00:28:48-06	2026-03-26 00:28:48-06	\N	\N
4	Kermesse Navideña 2025	kermesse	Evento especial con puestos de comida, artesanías y juegos.	t	\N	#ec4899	2026-03-26 00:28:48-06	2026-03-26 00:28:48-06	\N	\N
5	Tienda del Estudiante	estudiante	Vendedores alumnos que ofrecen productos y servicios en el campus.	t	\N	#8b5cf6	2026-03-26 00:28:48-06	2026-03-26 00:28:48-06	\N	\N
7	melai	cafeteria	cafeteria	t	\N	#3bf783	2026-03-27 19:55:00-06	2026-03-27 20:29:07-06	2026-03-27 20:29:07-06	\N
\.


--
-- Data for Name: usuario; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.usuario (id, nombre, apellido, telefono, foto_url, password_hash, email_verificado, ultimo_login_at, bloqueado, bloqueado_hasta, seguridad_json, created_at, updated_at, deleted_at, email, remember_token, modulo, tienda_id) FROM stdin;
1	Admin	Sistema	1234567890		$2y$12$NpUk9qqTAKCSjuTKIdE8Fu1J4SdwCJUNFklumuySFE7YMGxlHT.9a	t	\N	f	\N	{}	2026-03-23 23:06:13-06	2026-03-23 23:06:13-06	\N	admin@campusdigital.com	\N	\N	\N
3	Juan	Pérez	5555555555		$2y$12$tYwN7fO5SsqU3x7j6AA6IOtd0nmSbjw6/AfqICpps.dDNLSL97P62	t	\N	f	\N	{}	2026-03-23 23:06:14-06	2026-03-23 23:06:14-06	\N	estudiante@campusdigital.com	\N	\N	\N
4	Admin	Sistema			$2y$12$MigdxYn.n966dA.7pJ0tT.9T8xsS6ByrguRud6OiR8c.qiY65hX3y	t	2026-03-23 23:06:16-06	f	\N	{}	2026-01-22 23:06:16-06	2026-03-23 23:06:16-06	\N	admin@campus.edu.mx	\N	\N	\N
5	Ricardo	Flores			$2y$12$Uum2kXcJkdThP3UDgVd7f.slaYVGmIJhlTS3hLUhb41Ur822twMXq	t	\N	t	\N	{}	2026-03-18 23:06:16-06	2026-03-23 23:06:16-06	\N	ricardo.flores45@campus.edu.mx	\N	\N	\N
6	Daniela	González			$2y$12$vdsiUyCYpB6dwqYczIFpM.9BTWK.LWjeCTJ9Btr4RQ0QfnOuDXn4m	f	2026-03-23 19:51:16-06	f	\N	{}	2026-02-14 23:06:16-06	2026-03-23 23:06:16-06	\N	daniela.gonzález26@campus.edu.mx	\N	\N	\N
7	Sofia	Ramírez			$2y$12$x6WOG.wvx3KVrq6t7vges.DHfZ8xV/S1CWNnjRZpcpVinXI3JvtLy	t	2026-03-23 15:34:16-06	f	\N	{}	2026-02-22 23:06:16-06	2026-03-23 23:06:17-06	\N	sofia.ramírez21@campus.edu.mx	\N	\N	\N
8	Ricardo	Hernández			$2y$12$CF8zeVwqKSqHtZiUkiGby.Tak3YmQcU/C8GI6l.ZsE70S5WiFdV.W	t	2026-03-21 05:12:17-06	f	\N	{}	2026-02-12 23:06:17-06	2026-03-23 23:06:17-06	\N	ricardo.hernández51@campus.edu.mx	\N	\N	\N
9	Antonio	Flores			$2y$12$abws0pElxsRZ5OzeKGwJ9.u9NefzhejaHOneKcmKI/hI2MleUfjbS	t	2026-03-19 21:57:17-06	f	\N	{}	2026-02-21 23:06:17-06	2026-03-23 23:06:17-06	\N	antonio.flores35@campus.edu.mx	\N	\N	\N
10	Eduardo	Ramírez			$2y$12$DhQ2nlfVYDL3WtpSFc4H0uOmp5Jju6A4ABMZ54yNugnoGH2bZpMW.	t	2026-03-21 19:51:17-06	f	\N	{}	2026-01-29 23:06:17-06	2026-03-23 23:06:17-06	\N	eduardo.ramírez63@campus.edu.mx	\N	\N	\N
11	David	García			$2y$12$GM/mSOMEzNnZpawcvVuQD.WrE19g6IvUDtgk/tUdrAo3bQAqrTkGu	t	\N	t	\N	{}	2026-01-25 23:06:17-06	2026-03-23 23:06:18-06	\N	david.garcía63@campus.edu.mx	\N	\N	\N
12	Andrés	Flores			$2y$12$64TNqej9W1/z8ZdT9eFBNO5YbB/n6g0BNdqm2VRSMbYzoc7gd1MWy	t	2026-03-16 23:16:18-06	f	\N	{}	2026-03-13 23:06:18-06	2026-03-23 23:06:18-06	\N	andrés.flores53@campus.edu.mx	\N	\N	\N
13	Daniela	González			$2y$12$DpIsU/7FZ.oLJjZ4VP9bIOHuVwgQwO15vUzzPFN5E5UKcoMCYLfLS	t	2026-03-19 20:04:18-06	f	\N	{}	2026-03-13 23:06:18-06	2026-03-23 23:06:18-06	\N	daniela.gonzález40@campus.edu.mx	\N	\N	\N
14	Daniela	Hernández			$2y$12$kXHkmUsSMFqNP1PmO6nnfeSR.txKNxw.M8Bqtw5Wyc9KbG.7Y.Jdy	t	2026-03-18 17:43:18-06	f	\N	{}	2026-01-16 23:06:18-06	2026-03-23 23:06:18-06	\N	daniela.hernández10@campus.edu.mx	\N	\N	\N
15	Fernando	Torres			$2y$12$h7e80mH4pQ8I/pkOs6y2/.MDoya8ZeXra9BsAf7dDVrTNUxe50xSC	t	2026-03-18 05:24:18-06	f	\N	{}	2025-12-25 23:06:18-06	2026-03-23 23:06:18-06	\N	fernando.torres78@campus.edu.mx	\N	\N	\N
16	Laura	Cruz			$2y$12$euj966ANBlhXST0my5vZLO7aAT/ssbNQTB09.WZRYp80j1OQF0M52	t	\N	t	\N	{}	2025-12-31 23:06:18-06	2026-03-23 23:06:19-06	\N	laura.cruz43@campus.edu.mx	\N	\N	\N
17	Carlos	García			$2y$12$.kaBwjvYKtPpizJxMYVFGeojgBkk1tuuxxMIQuiCHRCTutQL51g9W	t	2026-03-23 08:29:19-06	f	\N	{}	2026-01-26 23:06:19-06	2026-03-23 23:06:19-06	\N	carlos.garcía96@campus.edu.mx	\N	\N	\N
18	Eduardo	Hernández			$2y$12$3WitNY4aCeqYL7kQN..FbOkFIXAdTz5bMrmcEAlGj6ghp4tbybcu.	t	2026-03-18 21:29:19-06	f	\N	{}	2026-03-11 23:06:19-06	2026-03-23 23:06:19-06	\N	eduardo.hernández14@campus.edu.mx	\N	\N	\N
19	Fernando	Pérez			$2y$12$XijBxpJPvkkx4zh5pvSABO6sVs/JfYLa4gJAPmVNpFWlXBC40CajO	f	2026-03-22 19:42:19-06	f	\N	{}	2026-03-17 23:06:19-06	2026-03-23 23:06:19-06	\N	fernando.pérez16@campus.edu.mx	\N	\N	\N
20	Jorge	Flores			$2y$12$EODhPvXEUEz.ec/tnzfdQuqEWWlGHfuu5pN/eK5DU0xjVzEDeCnxy	f	2026-03-19 09:22:19-06	f	\N	{}	2026-03-22 23:06:19-06	2026-03-23 23:06:20-06	\N	jorge.flores83@campus.edu.mx	\N	\N	\N
21	Laura	García			$2y$12$7PITpgIMXQby1H3mVWd7i.4jnOUGk.LequowY0.mh8df3J.NC0E7O	f	2026-03-23 22:45:20-06	f	\N	{}	2026-01-16 23:06:20-06	2026-03-23 23:06:20-06	\N	laura.garcía96@campus.edu.mx	\N	\N	\N
22	Ana	Ramírez			$2y$12$VUuSGSpM4iUmzFtJSzZcNeq4Qx6ZJebbp1Lr0BhDiAPXc2p8Frba6	t	2026-03-20 20:13:20-06	f	\N	{}	2026-01-31 23:06:20-06	2026-03-23 23:06:20-06	\N	ana.ramírez81@campus.edu.mx	\N	\N	\N
23	David	Flores			$2y$12$odcxRWbI1yYAfmWhFqNGweZy0NdEIAvFoMqvFBLwHszaCKBS6falu	t	2026-03-20 02:07:20-06	f	\N	{}	2025-12-30 23:06:20-06	2026-03-23 23:06:20-06	\N	david.flores22@campus.edu.mx	\N	\N	\N
24	Andrés	Martínez			$2y$12$afa7ZUufqlkQnDpPuDK6puotbksaYghmgu/tl71M7.KbHolXbslzC	t	2026-03-21 18:16:20-06	f	\N	{}	2025-12-25 23:06:20-06	2026-03-23 23:06:21-06	\N	andrés.martínez25@campus.edu.mx	\N	\N	\N
25	Jorge	Torres			$2y$12$BPa7zZ1tCqVpHeYI.efn6.9pvjU7D.jlD7rEuCGzNciRReqIEAOM2	t	2026-03-19 08:05:21-06	f	\N	{}	2026-01-30 23:06:21-06	2026-03-23 23:06:21-06	\N	jorge.torres64@campus.edu.mx	\N	\N	\N
26	Ricardo	Ramírez			$2y$12$8Q2aPrcGNJ8YEYtpHlOrnuGuvnJp0UpsRIByVr7S3r4jhqtBhSTfu	t	2026-03-21 19:47:21-06	f	\N	{}	2026-01-06 23:06:21-06	2026-03-23 23:06:21-06	\N	ricardo.ramírez86@campus.edu.mx	\N	\N	\N
27	Ricardo	Ramírez			$2y$12$HlqOYQ7d0j0RJ1BwQ2N3Ge1hI1HpAvdhAZ1NPPhWL.30TjG9vQwdu	t	\N	t	\N	{}	2026-01-31 23:06:21-06	2026-03-23 23:06:21-06	\N	ricardo.ramírez90@campus.edu.mx	\N	\N	\N
28	Gabriela	Flores			$2y$12$SVZ5cNsnVCVQku6dS52wDu1wtQqF.9sMlu1gEbZzoqjnjIHmGdDw2	t	2026-03-19 14:36:21-06	f	\N	{}	2026-03-21 23:06:21-06	2026-03-23 23:06:21-06	\N	gabriela.flores57@campus.edu.mx	\N	\N	\N
29	Gabriela	Pérez			$2y$12$bBjNDv4EJI0Z1eeWorOqdenn4I.JUkcai1i1vQc0txP06VC6c4D4m	t	\N	t	\N	{}	2026-01-03 23:06:21-06	2026-03-23 23:06:22-06	\N	gabriela.pérez34@campus.edu.mx	\N	\N	\N
30	Eduardo	González			$2y$12$Bfd55w4VfgikbcyDiciIGeU9d6Ao3QzQa058Zk3pyn1GtLJFi4tLy	t	2026-03-23 17:34:22-06	f	\N	{}	2026-02-16 23:06:22-06	2026-03-23 23:06:22-06	\N	eduardo.gonzález63@campus.edu.mx	\N	\N	\N
31	Isabella	Hernández			$2y$12$ky085aDU54KWOgVk/j4vSuGn46KaO4qHqIp0PUGVO5cEB8evIXEkq	t	2026-03-21 09:53:22-06	f	\N	{}	2025-12-24 23:06:22-06	2026-03-23 23:06:22-06	\N	isabella.hernández16@campus.edu.mx	\N	\N	\N
32	Andrés	Pérez			$2y$12$e.o66T0IYd5e8Z.4KdZeLerkn6BIbh4iEpvX6wLFAnEQapINwVFCe	t	2026-03-20 10:42:22-06	f	\N	{}	2026-01-30 23:06:22-06	2026-03-23 23:06:22-06	\N	andrés.pérez12@campus.edu.mx	\N	\N	\N
33	Daniela	García			$2y$12$2nbuttKyWeYeX237357Hhe9alN6AxyauqC7O4tlNeZIYLqNIrPuhm	t	\N	t	\N	{}	2026-02-10 23:06:22-06	2026-03-23 23:06:23-06	\N	daniela.garcía26@campus.edu.mx	\N	\N	\N
34	Carlos	González			$2y$12$uWXbUvp5nVjwu53XYOtb7eqU7amu7j9N9eSqbwlZcNL728p1mC2c6	t	2026-03-23 10:49:23-06	f	\N	{}	2026-02-16 23:06:23-06	2026-03-23 23:06:23-06	\N	carlos.gonzález25@campus.edu.mx	\N	\N	\N
35	Luis	Pérez			$2y$12$svjJ/nN0w5ZtPeQRM7vFNOH/3nsIoAZSyTk7Z5W1i2iC9d5grWVU2	f	2026-03-21 07:52:23-06	f	\N	{}	2026-02-14 23:06:23-06	2026-03-23 23:06:23-06	\N	luis.pérez69@campus.edu.mx	\N	\N	\N
36	Gabriela	Torres			$2y$12$7fSW8MXYrtajwr5.ZXzw7upfsrlrtmCPtj2XsUfKnsaKF2ZEmw7mi	t	2026-03-23 10:10:23-06	f	\N	{}	2026-01-20 23:06:23-06	2026-03-23 23:06:23-06	\N	gabriela.torres37@campus.edu.mx	\N	\N	\N
37	Valentina	Torres			$2y$12$qp/.y9iWHN.FAXQaqx/vjOh3zLrm/cTGmRhb4WlGGUWu2BBakYEta	f	2026-03-21 21:33:23-06	f	\N	{}	2026-02-24 23:06:23-06	2026-03-23 23:06:24-06	\N	valentina.torres90@campus.edu.mx	\N	\N	\N
38	Carlos	Martínez			$2y$12$cEeNvLzv3UUbeV2oqECowOVcNNyp0o1PBJGQHv.MyKe3ZeCp8XmoK	t	\N	t	\N	{}	2026-03-07 23:06:24-06	2026-03-23 23:06:24-06	\N	carlos.martínez99@campus.edu.mx	\N	\N	\N
39	Lucia	Ramírez			$2y$12$wa8AD0CbSXN04sbEXifN5e5S7b.8YnE1DdhKWoSW4x8MNJdbXelvC	t	2026-03-22 19:13:24-06	f	\N	{}	2026-02-13 23:06:24-06	2026-03-23 23:06:24-06	\N	lucia.ramírez65@campus.edu.mx	\N	\N	\N
40	Andrés	González			$2y$12$qhraQypWw.JoUgHWzqL3C.fCt6sruoUzatyFliq4km9OTD7eQLTiK	t	2026-03-23 01:07:24-06	f	\N	{}	2026-01-31 23:06:24-06	2026-03-23 23:06:24-06	\N	andrés.gonzález79@campus.edu.mx	\N	\N	\N
41	Jorge	García			$2y$12$6u276IyHc9NvZHB3nhyVtu7YGotPUfwbBCl83Hsof8Fln3jz8NPsO	f	2026-03-20 08:24:24-06	f	\N	{}	2026-03-10 23:06:24-06	2026-03-23 23:06:25-06	\N	jorge.garcía47@campus.edu.mx	\N	\N	\N
42	Eduardo	López			$2y$12$fygLnXwVdSxPcFLcDQSkF.lf5bPT5DpvI5b5KqZFAAmJnsxV3yxm2	t	2026-03-18 00:09:25-06	f	\N	{}	2026-01-09 23:06:25-06	2026-03-23 23:06:25-06	\N	eduardo.lópez43@campus.edu.mx	\N	\N	\N
43	Encargado	Cafetería Central	1234567890		$2y$12$1dFGBZC5bzDuR2C8FHmBa.ddBAwLtGNByb7JIhxyTveHQ/XxYysRm	t	\N	f	\N	{}	2026-03-27 20:22:39-06	2026-03-27 20:22:39-06	\N	manager1@campusdigital.com	\N	\N	1
44	Encargado	Papelería & Copias	1234567890		$2y$12$pQd9fzy3hQpNYb3w3aJvSuJQcsYBP39syv.rrNny7R5nxa7fMl1ie	t	\N	f	\N	{}	2026-03-27 20:22:39-06	2026-03-27 20:22:39-06	\N	manager2@campusdigital.com	\N	\N	2
2	Proveedor	Cafetería	0987654321		$2y$12$SGq.s1G9pmpsWft5OZLrK.TmVxxECip.b7Tky0wPiiXbF2n68dF7i	t	\N	f	\N	{}	2026-03-23 23:06:14-06	2026-03-27 17:36:08-06	\N	proveedor@campusdigital.com	iR7Z17pOVDxAaBynRVn98sFYWNCdMjay6BCgnEEOc0T5CfavBGGS3HW72LmN	cafeteria	\N
\.


--
-- Data for Name: usuario_password_reset; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.usuario_password_reset (id, usuario_id, token_hash, solicitado_at, expira_at, usado_at, ip, user_agent, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: usuario_perfil; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.usuario_perfil (id, usuario_id, fecha_nacimiento, genero, direccion, preferencias_json, created_at, updated_at, deleted_at) FROM stdin;
1	1	\N			{}	2026-03-23 23:06:13-06	2026-03-23 23:06:13-06	\N
2	2	\N			{}	2026-03-23 23:06:14-06	2026-03-23 23:06:14-06	\N
3	3	2000-01-15	masculino		{}	2026-03-23 23:06:14-06	2026-03-23 23:06:14-06	\N
\.


--
-- Data for Name: usuario_rol; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.usuario_rol (id, usuario_id, rol_id, asignado_por_usuario_id, asignado_at, created_at, updated_at, deleted_at) FROM stdin;
1	1	3	\N	2026-03-23 17:06:14-06	2026-03-23 23:06:13-06	2026-03-23 23:06:13-06	\N
2	2	2	\N	2026-03-23 17:06:14-06	2026-03-23 23:06:14-06	2026-03-23 23:06:14-06	\N
3	3	1	\N	2026-03-23 17:06:14-06	2026-03-23 23:06:14-06	2026-03-23 23:06:14-06	\N
4	4	3	\N	2026-03-23 23:06:16-06	2026-03-23 23:06:16-06	2026-03-23 23:06:16-06	\N
5	5	1	4	2026-03-18 23:06:16-06	2026-03-18 23:06:16-06	2026-03-18 23:06:16-06	\N
6	6	1	4	2026-02-14 23:06:16-06	2026-02-14 23:06:16-06	2026-02-14 23:06:16-06	\N
7	7	1	4	2026-02-22 23:06:16-06	2026-02-22 23:06:16-06	2026-02-22 23:06:16-06	\N
8	8	1	4	2026-02-12 23:06:17-06	2026-02-12 23:06:17-06	2026-02-12 23:06:17-06	\N
9	9	1	4	2026-02-21 23:06:17-06	2026-02-21 23:06:17-06	2026-02-21 23:06:17-06	\N
10	10	1	4	2026-01-29 23:06:17-06	2026-01-29 23:06:17-06	2026-01-29 23:06:17-06	\N
11	11	1	4	2026-01-25 23:06:17-06	2026-01-25 23:06:17-06	2026-01-25 23:06:17-06	\N
12	12	1	4	2026-03-13 23:06:18-06	2026-03-13 23:06:18-06	2026-03-13 23:06:18-06	\N
13	13	1	4	2026-03-13 23:06:18-06	2026-03-13 23:06:18-06	2026-03-13 23:06:18-06	\N
14	14	1	4	2026-01-16 23:06:18-06	2026-01-16 23:06:18-06	2026-01-16 23:06:18-06	\N
15	15	1	4	2025-12-25 23:06:18-06	2025-12-25 23:06:18-06	2025-12-25 23:06:18-06	\N
16	16	1	4	2025-12-31 23:06:18-06	2025-12-31 23:06:18-06	2025-12-31 23:06:18-06	\N
17	17	1	4	2026-01-26 23:06:19-06	2026-01-26 23:06:19-06	2026-01-26 23:06:19-06	\N
18	18	1	4	2026-03-11 23:06:19-06	2026-03-11 23:06:19-06	2026-03-11 23:06:19-06	\N
19	19	1	4	2026-03-17 23:06:19-06	2026-03-17 23:06:19-06	2026-03-17 23:06:19-06	\N
20	20	1	4	2026-03-22 23:06:19-06	2026-03-22 23:06:19-06	2026-03-22 23:06:19-06	\N
21	21	1	4	2026-01-16 23:06:20-06	2026-01-16 23:06:20-06	2026-01-16 23:06:20-06	\N
22	22	1	4	2026-01-31 23:06:20-06	2026-01-31 23:06:20-06	2026-01-31 23:06:20-06	\N
23	23	1	4	2025-12-30 23:06:20-06	2025-12-30 23:06:20-06	2025-12-30 23:06:20-06	\N
24	24	1	4	2025-12-25 23:06:20-06	2025-12-25 23:06:20-06	2025-12-25 23:06:20-06	\N
25	25	1	4	2026-01-30 23:06:21-06	2026-01-30 23:06:21-06	2026-01-30 23:06:21-06	\N
26	26	1	4	2026-01-06 23:06:21-06	2026-01-06 23:06:21-06	2026-01-06 23:06:21-06	\N
27	27	1	4	2026-01-31 23:06:21-06	2026-01-31 23:06:21-06	2026-01-31 23:06:21-06	\N
28	28	1	4	2026-03-21 23:06:21-06	2026-03-21 23:06:21-06	2026-03-21 23:06:21-06	\N
29	29	1	4	2026-01-03 23:06:21-06	2026-01-03 23:06:21-06	2026-01-03 23:06:21-06	\N
30	30	1	4	2026-02-16 23:06:22-06	2026-02-16 23:06:22-06	2026-02-16 23:06:22-06	\N
31	31	1	4	2025-12-24 23:06:22-06	2025-12-24 23:06:22-06	2025-12-24 23:06:22-06	\N
32	32	1	4	2026-01-30 23:06:22-06	2026-01-30 23:06:22-06	2026-01-30 23:06:22-06	\N
33	33	1	4	2026-02-10 23:06:22-06	2026-02-10 23:06:22-06	2026-02-10 23:06:22-06	\N
34	34	1	4	2026-02-16 23:06:23-06	2026-02-16 23:06:23-06	2026-02-16 23:06:23-06	\N
35	35	2	4	2026-02-14 23:06:23-06	2026-02-14 23:06:23-06	2026-02-14 23:06:23-06	\N
36	36	2	4	2026-01-20 23:06:23-06	2026-01-20 23:06:23-06	2026-01-20 23:06:23-06	\N
37	37	2	4	2026-02-24 23:06:23-06	2026-02-24 23:06:23-06	2026-02-24 23:06:23-06	\N
38	38	2	4	2026-03-07 23:06:24-06	2026-03-07 23:06:24-06	2026-03-07 23:06:24-06	\N
39	39	2	4	2026-02-13 23:06:24-06	2026-02-13 23:06:24-06	2026-02-13 23:06:24-06	\N
40	40	4	4	2026-01-31 23:06:24-06	2026-01-31 23:06:24-06	2026-01-31 23:06:24-06	\N
41	41	4	4	2026-03-10 23:06:24-06	2026-03-10 23:06:24-06	2026-03-10 23:06:24-06	\N
42	42	4	4	2026-01-09 23:06:25-06	2026-01-09 23:06:25-06	2026-01-09 23:06:25-06	\N
43	3	5	\N	2026-03-27 13:59:00-06	2026-03-27 19:58:59-06	2026-03-27 19:58:59-06	\N
44	43	6	\N	2026-03-27 14:22:40-06	2026-03-27 20:22:39-06	2026-03-27 20:22:39-06	\N
45	44	6	\N	2026-03-27 14:22:40-06	2026-03-27 20:22:39-06	2026-03-27 20:22:39-06	\N
\.


--
-- Data for Name: usuario_sesion; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.usuario_sesion (id, usuario_id, session_id, ip, user_agent, inicia_at, expira_at, termina_at, activa, meta_json, created_at, updated_at, deleted_at) FROM stdin;
1	4	iWZLQTkg3Jrrd31nBm2eH1p4dFGDCVvHOsVLGyQY	141.158.56.108	Mozilla/5.0 (Windows NT 5.1) AppleWebKit/531.0 (KHTML, like Gecko) Chrome/80.0.4586.71 Safari/531.0 Edg/80.01063.83	2026-02-23 07:06:25-06	2026-02-23 15:06:25-06	\N	t	{}	2026-02-23 07:06:25-06	2026-02-23 07:06:25-06	\N
2	4	Asxqe7DgGXaN423kwZrgn4SpGuxHFIk0nNWkJdTG	250.92.43.22	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_8_5 rv:3.0) Gecko/20220508 Firefox/35.0	2026-03-03 02:06:25-06	2026-03-03 10:06:25-06	2026-03-03 06:06:25-06	f	{}	2026-03-03 02:06:25-06	2026-03-03 02:06:25-06	\N
3	4	4DgjyAfnMrtdjRmg0dBo64R2qVTJ1HDWo9HHCQcE	61.221.57.42	Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.2.20) Gecko/20170622 Firefox/37.0	2026-03-12 02:06:25-06	2026-03-12 10:06:25-06	2026-03-12 03:06:25-06	f	{}	2026-03-12 02:06:25-06	2026-03-12 02:06:25-06	\N
4	4	BfsDWJN44DHct2sHboiFJ4yeZDEiRoaEV3twG815	33.249.115.74	Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/533.30.7 (KHTML, like Gecko) Version/4.0.3 Safari/533.30.7	2026-03-15 20:06:25-06	2026-03-16 04:06:25-06	2026-03-16 00:06:25-06	f	{}	2026-03-15 20:06:25-06	2026-03-15 20:06:25-06	\N
5	4	m25IFnxGHqM8pn9LhBrHbAGEJGpa0tXiXMhybSx2	230.103.203.212	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_0 rv:6.0) Gecko/20150303 Firefox/35.0	2026-03-16 04:06:25-06	2026-03-16 12:06:25-06	2026-03-16 06:06:25-06	f	{}	2026-03-16 04:06:25-06	2026-03-16 04:06:25-06	\N
6	5	YwEYgB68jqofGwzIh2BzK85kK6fIRJXem16MSQkl	36.77.4.71	Mozilla/5.0 (iPhone; CPU iPhone OS 14_1 like Mac OS X) AppleWebKit/537.1 (KHTML, like Gecko) Version/15.0 EdgiOS/82.01124.34 Mobile/15E148 Safari/537.1	2026-03-20 16:06:25-06	2026-03-21 00:06:25-06	\N	t	{}	2026-03-20 16:06:25-06	2026-03-20 16:06:25-06	\N
7	5	qG7eqjsLDIlmHYP88PmSJO6LhAtTHiwZDuv00xoZ	54.244.141.243	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5311 (KHTML, like Gecko) Chrome/40.0.860.0 Mobile Safari/5311	2026-02-24 01:06:25-06	2026-02-24 09:06:25-06	2026-02-24 02:06:25-06	f	{}	2026-02-24 01:06:25-06	2026-02-24 01:06:25-06	\N
8	6	gvlzvzNkjEt4phHnmGjGmpR1rQNLlHGxizrcUhYe	197.3.160.83	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_1) AppleWebKit/5342 (KHTML, like Gecko) Chrome/37.0.845.0 Mobile Safari/5342	2026-03-07 20:06:25-06	2026-03-08 04:06:25-06	2026-03-07 23:06:25-06	f	{}	2026-03-07 20:06:25-06	2026-03-07 20:06:25-06	\N
9	6	NZOr6dbRi5y6qDMdGZZxAJBKdZrV24lZ4TZPdSM8	76.71.15.222	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_6 rv:4.0) Gecko/20240411 Firefox/37.0	2026-03-07 01:06:25-06	2026-03-07 09:06:25-06	2026-03-07 03:06:25-06	f	{}	2026-03-07 01:06:25-06	2026-03-07 01:06:25-06	\N
10	6	jA6ZF8gbbGHYiSMER3HF1ssMARApBX0l7tJcx1Am	31.221.2.44	Mozilla/5.0 (Windows NT 5.01) AppleWebKit/531.2 (KHTML, like Gecko) Chrome/82.0.4179.77 Safari/531.2 Edg/82.01145.74	2026-03-01 15:06:25-06	2026-03-01 23:06:25-06	2026-03-01 17:06:25-06	f	{}	2026-03-01 15:06:25-06	2026-03-01 15:06:25-06	\N
11	6	GaQlc9zjA5YudM0cBNJc0yGaZbCnom6KX3v6VZW7	6.252.99.142	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_9) AppleWebKit/533.0 (KHTML, like Gecko) Chrome/88.0.4788.92 Safari/533.0 Edg/88.01143.58	2026-03-11 13:06:25-06	2026-03-11 21:06:25-06	2026-03-11 16:06:25-06	f	{}	2026-03-11 13:06:25-06	2026-03-11 13:06:25-06	\N
12	6	7eqUstFZ1ed2KMqHs1j5ICPttYNBkCvJmz9YVYd7	105.231.131.219	Mozilla/5.0 (iPhone; CPU iPhone OS 8_1_2 like Mac OS X; en-US) AppleWebKit/532.4.5 (KHTML, like Gecko) Version/3.0.5 Mobile/8B113 Safari/6532.4.5	2026-03-16 19:06:25-06	2026-03-17 03:06:25-06	2026-03-16 23:06:25-06	f	{}	2026-03-16 19:06:25-06	2026-03-16 19:06:25-06	\N
13	7	aswsDq1uPovjDRhICEXSJF8M3ljHcg3846NloU7i	38.142.218.181	Mozilla/5.0 (Windows NT 4.0) AppleWebKit/533.0 (KHTML, like Gecko) Chrome/86.0.4649.51 Safari/533.0 Edg/86.01005.70	2026-03-14 13:06:25-06	2026-03-14 21:06:25-06	2026-03-14 17:06:25-06	f	{}	2026-03-14 13:06:25-06	2026-03-14 13:06:25-06	\N
14	7	HDrzr6UCJ1gEnEa9VNwkN3l0oRi6MQ4IOZxUrlEC	50.10.194.160	Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/82.0.4688.84 Safari/537.1 Edg/82.01106.86	2026-03-14 01:06:25-06	2026-03-14 09:06:25-06	2026-03-14 03:06:25-06	f	{}	2026-03-14 01:06:25-06	2026-03-14 01:06:25-06	\N
15	7	QdAt5EK1NCbL63kvfIM0UEnSZzW5ENMffHksbR5d	86.30.210.108	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/532.1 (KHTML, like Gecko) Chrome/97.0.4802.38 Safari/532.1 EdgA/97.01030.34	2026-03-13 21:06:25-06	2026-03-14 05:06:25-06	2026-03-13 23:06:25-06	f	{}	2026-03-13 21:06:25-06	2026-03-13 21:06:25-06	\N
16	7	wIei9nXSAxjfUfQwXBOoyGpo7qkgDGjqQDfJTV6Y	245.242.57.146	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_9 rv:4.0; en-US) AppleWebKit/535.50.5 (KHTML, like Gecko) Version/5.1 Safari/535.50.5	2026-03-21 15:06:25-06	2026-03-21 23:06:25-06	2026-03-21 16:06:25-06	f	{}	2026-03-21 15:06:25-06	2026-03-21 15:06:25-06	\N
17	8	foZispRKVaY8hp5W9Sx3cMjtSNcD04WD4Yf9ZUBg	232.158.148.28	Mozilla/5.0 (Windows; U; Windows CE) AppleWebKit/533.48.6 (KHTML, like Gecko) Version/5.0.3 Safari/533.48.6	2026-03-23 07:06:25-06	2026-03-23 15:06:25-06	2026-03-23 10:06:25-06	f	{}	2026-03-23 07:06:25-06	2026-03-23 07:06:25-06	\N
18	8	gXimyuVA670ocCIb5d0SGeyJWQqGOBwTJmpU1DX6	225.10.246.196	Mozilla/5.0 (compatible; MSIE 9.0; Windows 98; Win 9x 4.90; Trident/4.0)	2026-02-21 12:06:25-06	2026-02-21 20:06:25-06	2026-02-21 14:06:25-06	f	{}	2026-02-21 12:06:25-06	2026-02-21 12:06:25-06	\N
19	9	FxDn2xUeEzSA4rnXwVzraoSo5jEb9H0S9WOc21Ii	80.57.38.167	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_0 rv:3.0) Gecko/20180630 Firefox/37.0	2026-03-14 20:06:25-06	2026-03-15 04:06:25-06	\N	t	{}	2026-03-14 20:06:25-06	2026-03-14 20:06:25-06	\N
20	9	gElPJOneVzHwvEYAqtaEtOvSS3S1KvVEFDHX0pox	114.11.141.184	Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/3.0)	2026-03-21 14:06:25-06	2026-03-21 22:06:25-06	2026-03-21 15:06:25-06	f	{}	2026-03-21 14:06:25-06	2026-03-21 14:06:25-06	\N
21	9	FT4PY3O4hUo5iMyZuetcwmPVnJTk1igj6n9iTXDn	166.6.218.122	Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20120607 Firefox/35.0	2026-03-03 08:06:25-06	2026-03-03 16:06:25-06	2026-03-03 10:06:25-06	f	{}	2026-03-03 08:06:25-06	2026-03-03 08:06:25-06	\N
22	9	u11DHFcXRZifJVxnBLcb0MFZoPFuD1jJ0md2kdzX	245.119.141.30	Mozilla/5.0 (iPhone; CPU iPhone OS 14_1 like Mac OS X) AppleWebKit/535.2 (KHTML, like Gecko) Version/15.0 EdgiOS/83.01102.88 Mobile/15E148 Safari/535.2	2026-03-23 23:06:25-06	2026-03-24 07:06:25-06	2026-03-24 01:06:25-06	f	{}	2026-03-23 23:06:25-06	2026-03-23 23:06:25-06	\N
23	10	WObSOGHihCObzUC2xuR8oEowuc5lGgN76uKQ4aFr	175.202.13.222	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5352 (KHTML, like Gecko) Chrome/38.0.866.0 Mobile Safari/5352	2026-03-23 18:06:25-06	2026-03-24 02:06:25-06	2026-03-23 19:06:25-06	f	{}	2026-03-23 18:06:25-06	2026-03-23 18:06:25-06	\N
24	10	zknTHOvEaqgUsNOp6eQEpbo1THQXf8T5QwrkcBsJ	55.206.69.155	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_0 rv:4.0) Gecko/20131229 Firefox/36.0	2026-03-18 09:06:25-06	2026-03-18 17:06:25-06	2026-03-18 13:06:25-06	f	{}	2026-03-18 09:06:25-06	2026-03-18 09:06:25-06	\N
25	10	4rt9MQSBEDOSCaGNBSNDNlqS7PmchUYW8MlZghSu	203.46.39.183	Opera/9.52 (X11; Linux x86_64; nl-NL) Presto/2.10.172 Version/12.00	2026-02-21 08:06:25-06	2026-02-21 16:06:25-06	2026-02-21 12:06:25-06	f	{}	2026-02-21 08:06:25-06	2026-02-21 08:06:25-06	\N
26	11	1kHe6UmSBjfbnALlqoYkVKSVJJCT3YvfHhB7FjKZ	175.216.216.244	Opera/9.17 (X11; Linux x86_64; en-US) Presto/2.9.333 Version/11.00	2026-02-25 01:06:25-06	2026-02-25 09:06:25-06	2026-02-25 04:06:25-06	f	{}	2026-02-25 01:06:25-06	2026-02-25 01:06:25-06	\N
27	11	8YoIYz1A9TuGN9IWQYZwZK4UjCjNhmJlwVEz510X	65.16.37.143	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 6.1; Trident/4.0)	2026-03-17 07:06:25-06	2026-03-17 15:06:25-06	2026-03-17 08:06:25-06	f	{}	2026-03-17 07:06:25-06	2026-03-17 07:06:25-06	\N
28	11	o3eRy6jDmcEKTzK2n0DXc2YvBMx2xn70sgcJLY4I	127.205.80.18	Mozilla/5.0 (Windows NT 5.0; nl-NL; rv:1.9.0.20) Gecko/20180622 Firefox/37.0	2026-03-11 17:06:25-06	2026-03-12 01:06:25-06	2026-03-11 19:06:25-06	f	{}	2026-03-11 17:06:25-06	2026-03-11 17:06:25-06	\N
29	12	KCYaHOSG2fBakD8jGLV7xHwATrHGy53H97LgHwls	40.222.130.105	Mozilla/5.0 (Windows NT 5.0; sl-SI; rv:1.9.1.20) Gecko/20160108 Firefox/36.0	2026-03-17 18:06:25-06	2026-03-18 02:06:25-06	\N	t	{}	2026-03-17 18:06:25-06	2026-03-17 18:06:25-06	\N
30	12	Cf0PCgzp2NGDoA13Ak3M8Ie6T7QtW6jWANWNTkES	170.157.251.97	Mozilla/5.0 (Windows; U; Windows 95) AppleWebKit/533.28.6 (KHTML, like Gecko) Version/4.0.2 Safari/533.28.6	2026-03-02 02:06:25-06	2026-03-02 10:06:25-06	2026-03-02 04:06:25-06	f	{}	2026-03-02 02:06:25-06	2026-03-02 02:06:25-06	\N
31	13	JYpwny8tGF8xqnpTXGt2dtZSzfbuaPQpzfiFVxM0	163.44.249.25	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_3) AppleWebKit/534.1 (KHTML, like Gecko) Chrome/89.0.4779.42 Safari/534.1 Edg/89.01044.26	2026-03-21 14:06:25-06	2026-03-21 22:06:25-06	2026-03-21 16:06:25-06	f	{}	2026-03-21 14:06:25-06	2026-03-21 14:06:25-06	\N
32	13	85R2imWNyIJIoM04a4r6WFpayb0xKE5tvThVfRgK	209.144.69.69	Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/5.1)	2026-03-09 08:06:25-06	2026-03-09 16:06:25-06	2026-03-09 11:06:25-06	f	{}	2026-03-09 08:06:25-06	2026-03-09 08:06:25-06	\N
33	14	KsKU4f2dmTj5iDC9RuoHPInOaDsvVpBzjbzhfCD1	124.44.87.125	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/531.2 (KHTML, like Gecko) Chrome/92.0.4133.62 Safari/531.2 Edg/92.01110.64	2026-03-14 10:06:25-06	2026-03-14 18:06:25-06	2026-03-14 14:06:25-06	f	{}	2026-03-14 10:06:25-06	2026-03-14 10:06:25-06	\N
34	14	bR5U6ltybcPbHBkl96mRYRgOrRBQTCBoDDUvuMiQ	95.74.204.25	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_2 rv:5.0; sl-SI) AppleWebKit/534.24.7 (KHTML, like Gecko) Version/4.0.1 Safari/534.24.7	2026-02-25 21:06:25-06	2026-02-26 05:06:25-06	2026-02-26 00:06:25-06	f	{}	2026-02-25 21:06:25-06	2026-02-25 21:06:25-06	\N
35	15	X2Y7RL6s2EejIYzpiXU68xhUODcom1df0eUeYtMP	89.33.29.80	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_4 rv:4.0; nl-NL) AppleWebKit/532.23.7 (KHTML, like Gecko) Version/4.0.4 Safari/532.23.7	2026-03-23 16:06:25-06	2026-03-24 00:06:25-06	2026-03-23 20:06:25-06	f	{}	2026-03-23 16:06:25-06	2026-03-23 16:06:25-06	\N
36	15	991FGpA9iPOCVZixxFDSv6ayFBC7Naxz5jPLy2r5	70.165.7.24	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5322 (KHTML, like Gecko) Chrome/38.0.826.0 Mobile Safari/5322	2026-02-27 08:06:25-06	2026-02-27 16:06:25-06	2026-02-27 11:06:25-06	f	{}	2026-02-27 08:06:25-06	2026-02-27 08:06:25-06	\N
37	16	pjfNoVjnFQQEOWOTE86xDI8634CqiPkBbveJbaqu	243.157.43.176	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 6.2; Trident/4.1)	2026-03-18 05:06:25-06	2026-03-18 13:06:25-06	\N	t	{}	2026-03-18 05:06:25-06	2026-03-18 05:06:25-06	\N
38	16	gAGUw9Ptuc6sU6nqLdEjQHbidJQ8zPGLrLwqwmeO	193.222.73.75	Mozilla/5.0 (compatible; MSIE 9.0; Windows 95; Trident/3.1)	2026-03-09 11:06:25-06	2026-03-09 19:06:25-06	2026-03-09 14:06:25-06	f	{}	2026-03-09 11:06:25-06	2026-03-09 11:06:25-06	\N
39	17	eY3Z0ivKsSE86HGHIEumGcA5uCiZT2Raug6u6ltb	244.30.182.184	Mozilla/5.0 (Windows 95; nl-NL; rv:1.9.2.20) Gecko/20110506 Firefox/35.0	2026-02-28 20:06:25-06	2026-03-01 04:06:25-06	2026-02-28 23:06:25-06	f	{}	2026-02-28 20:06:25-06	2026-02-28 20:06:25-06	\N
40	17	NyfLVviOAgIs705D0sF17aKkj1WnMe7U4u5IIVgA	203.238.198.206	Mozilla/5.0 (compatible; MSIE 7.0; Windows 98; Win 9x 4.90; Trident/5.1)	2026-03-18 16:06:25-06	2026-03-19 00:06:25-06	2026-03-18 20:06:25-06	f	{}	2026-03-18 16:06:25-06	2026-03-18 16:06:25-06	\N
41	17	bz29lAZacBCtabUWI1PfLqUieLTGw8EwBllVsQIp	181.40.254.12	Mozilla/5.0 (Windows NT 5.01; en-US; rv:1.9.0.20) Gecko/20260103 Firefox/35.0	2026-03-20 16:06:25-06	2026-03-21 00:06:25-06	2026-03-20 18:06:25-06	f	{}	2026-03-20 16:06:25-06	2026-03-20 16:06:25-06	\N
42	17	SZXwqzCVYImzmz01DpAVAO93dxIM5XJ947XM98VU	132.204.177.138	Mozilla/5.0 (iPhone; CPU iPhone OS 14_2 like Mac OS X) AppleWebKit/537.2 (KHTML, like Gecko) Version/15.0 EdgiOS/83.01090.27 Mobile/15E148 Safari/537.2	2026-03-15 23:06:25-06	2026-03-16 07:06:25-06	2026-03-16 01:06:25-06	f	{}	2026-03-15 23:06:25-06	2026-03-15 23:06:25-06	\N
43	18	yDS1R1z5Nku4vknwvv4EAqU1Oms9Q1UNs4eFl3tN	219.150.11.240	Mozilla/5.0 (iPhone; CPU iPhone OS 8_0_1 like Mac OS X; nl-NL) AppleWebKit/535.11.5 (KHTML, like Gecko) Version/4.0.5 Mobile/8B117 Safari/6535.11.5	2026-03-02 23:06:25-06	2026-03-03 07:06:25-06	2026-03-03 00:06:25-06	f	{}	2026-03-02 23:06:25-06	2026-03-02 23:06:25-06	\N
44	18	NtHmKosif41mIrCtVWRLD7OuTAQYek5AcKImib7I	75.158.155.198	Mozilla/5.0 (Windows; U; Windows NT 5.01) AppleWebKit/532.32.3 (KHTML, like Gecko) Version/5.0.4 Safari/532.32.3	2026-03-19 20:06:25-06	2026-03-20 04:06:25-06	2026-03-20 00:06:25-06	f	{}	2026-03-19 20:06:25-06	2026-03-19 20:06:25-06	\N
45	19	ZomsZB9mBsrHCNz0j1dStGu2bmC4DuV8d0hz8B6y	119.209.104.153	Mozilla/5.0 (Windows NT 5.1) AppleWebKit/536.0 (KHTML, like Gecko) Chrome/85.0.4498.17 Safari/536.0 Edg/85.01125.17	2026-03-20 02:06:25-06	2026-03-20 10:06:25-06	2026-03-20 06:06:25-06	f	{}	2026-03-20 02:06:25-06	2026-03-20 02:06:25-06	\N
46	19	fC9YLSHOm1i1UxDCzc5vYSu782w7YRdScHwufKII	10.3.153.110	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_8) AppleWebKit/531.1 (KHTML, like Gecko) Chrome/83.0.4094.86 Safari/531.1 Edg/83.01013.90	2026-03-01 22:06:25-06	2026-03-02 06:06:25-06	2026-03-02 00:06:25-06	f	{}	2026-03-01 22:06:25-06	2026-03-01 22:06:25-06	\N
47	19	EmeVJDzoF1071paVBZWdlVO2SlBbmHlrL34u3vqN	60.198.100.14	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_7) AppleWebKit/537.0 (KHTML, like Gecko) Chrome/97.0.4524.41 Safari/537.0 Edg/97.01146.13	2026-03-21 04:06:25-06	2026-03-21 12:06:25-06	2026-03-21 05:06:25-06	f	{}	2026-03-21 04:06:25-06	2026-03-21 04:06:25-06	\N
48	19	sfFZ2O0yNOBX9OSxYbJkS1kcfzM56oIRPiptRoP4	73.179.41.176	Opera/8.41 (Windows 98; en-US) Presto/2.8.240 Version/11.00	2026-02-25 11:06:25-06	2026-02-25 19:06:25-06	2026-02-25 14:06:25-06	f	{}	2026-02-25 11:06:25-06	2026-02-25 11:06:25-06	\N
49	19	xDUXA2AvgCjVsFcOHY4DuJq9WwIytO8ELWE8Awxt	28.67.22.114	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_7 rv:2.0) Gecko/20230217 Firefox/37.0	2026-02-28 04:06:25-06	2026-02-28 12:06:25-06	2026-02-28 05:06:25-06	f	{}	2026-02-28 04:06:25-06	2026-02-28 04:06:25-06	\N
50	20	NWPpZ4GYyw5ifLrNe70mIKEmnLXtyQPELEI9P4id	140.169.16.183	Opera/8.85 (Windows NT 5.2; sl-SI) Presto/2.11.169 Version/10.00	2026-03-16 11:06:25-06	2026-03-16 19:06:25-06	\N	t	{}	2026-03-16 11:06:25-06	2026-03-16 11:06:25-06	\N
51	20	NPlZukfg0JD9nCq882Yxzullk11576Ptd7J8lrj0	107.193.29.193	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 4.0; Trident/5.0)	2026-02-21 08:06:25-06	2026-02-21 16:06:25-06	2026-02-21 11:06:25-06	f	{}	2026-02-21 08:06:25-06	2026-02-21 08:06:25-06	\N
52	21	3gKr4qd3OUD3V8AsnDRJFOfG8s3nzlKfHuG3HgPx	102.28.160.235	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_6) AppleWebKit/5342 (KHTML, like Gecko) Chrome/39.0.857.0 Mobile Safari/5342	2026-02-24 19:06:25-06	2026-02-25 03:06:25-06	\N	t	{}	2026-02-24 19:06:25-06	2026-02-24 19:06:25-06	\N
53	21	Mk0AknBq378N3HnnSKmqjFIJfiFO6GYt0q5mdbhA	229.132.6.25	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_0) AppleWebKit/5311 (KHTML, like Gecko) Chrome/39.0.848.0 Mobile Safari/5311	2026-03-15 07:06:25-06	2026-03-15 15:06:25-06	2026-03-15 08:06:25-06	f	{}	2026-03-15 07:06:25-06	2026-03-15 07:06:25-06	\N
54	21	L48uM33MYsGMG1fud1fGCFw5dEx6QcysA7DwFZOZ	37.175.132.50	Opera/9.77 (Windows CE; sl-SI) Presto/2.8.181 Version/12.00	2026-03-23 21:06:25-06	2026-03-24 05:06:25-06	2026-03-24 01:06:25-06	f	{}	2026-03-23 21:06:25-06	2026-03-23 21:06:25-06	\N
55	21	hcsG7Impp4KhdWLXYerQvo7Y1NqJPZx0IbFggsXZ	46.25.13.180	Opera/8.61 (X11; Linux i686; sl-SI) Presto/2.11.206 Version/10.00	2026-02-23 16:06:25-06	2026-02-24 00:06:25-06	2026-02-23 18:06:25-06	f	{}	2026-02-23 16:06:25-06	2026-02-23 16:06:25-06	\N
56	21	ON4HOtt6lbQLghtf9cR1p3syNGZ3KhUuS4fZwsU0	100.204.32.48	Mozilla/5.0 (Windows NT 5.01; nl-NL; rv:1.9.1.20) Gecko/20170129 Firefox/37.0	2026-03-16 23:06:25-06	2026-03-17 07:06:25-06	2026-03-17 01:06:25-06	f	{}	2026-03-16 23:06:25-06	2026-03-16 23:06:25-06	\N
57	22	8LB2PwNSguTWl22WsHmUMlxejF7vlSBaKBFX2vw1	239.148.182.255	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/3.1)	2026-03-05 05:06:25-06	2026-03-05 13:06:25-06	2026-03-05 06:06:25-06	f	{}	2026-03-05 05:06:25-06	2026-03-05 05:06:25-06	\N
58	22	N2GmuXEhqa1UTPyAFar9y7R1o0mFQ1OkX6AfPH3o	125.164.251.101	Mozilla/5.0 (X11; Linux i686; rv:7.0) Gecko/20150911 Firefox/37.0	2026-03-14 00:06:25-06	2026-03-14 08:06:25-06	2026-03-14 01:06:25-06	f	{}	2026-03-14 00:06:25-06	2026-03-14 00:06:25-06	\N
59	22	g8MR0BC3AwIAkXA0N84joP8N9JlQQp7FZtmjaKr9	157.53.139.1	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_4) AppleWebKit/535.0 (KHTML, like Gecko) Chrome/81.0.4324.58 Safari/535.0 Edg/81.01103.22	2026-02-27 23:06:25-06	2026-02-28 07:06:25-06	2026-02-28 02:06:25-06	f	{}	2026-02-27 23:06:25-06	2026-02-27 23:06:25-06	\N
60	22	J1Uuh3c9f1NPdCrLeP7pT27q1LZTl34dK3m2t20c	235.248.121.236	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.0; Trident/4.1)	2026-03-04 11:06:25-06	2026-03-04 19:06:25-06	2026-03-04 13:06:25-06	f	{}	2026-03-04 11:06:25-06	2026-03-04 11:06:25-06	\N
61	23	S6UfGCwitw0oI4KmyclNPH69T8tqXX9b3X06xiJn	93.45.115.58	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0)	2026-03-18 19:06:25-06	2026-03-19 03:06:25-06	2026-03-18 23:06:25-06	f	{}	2026-03-18 19:06:25-06	2026-03-18 19:06:25-06	\N
62	23	Hydw1K65NiIeZZrwrzRsY1Tzd8no4WPDMaWusooU	2.86.103.0	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_6) AppleWebKit/5361 (KHTML, like Gecko) Chrome/39.0.881.0 Mobile Safari/5361	2026-03-11 06:06:25-06	2026-03-11 14:06:25-06	2026-03-11 08:06:25-06	f	{}	2026-03-11 06:06:25-06	2026-03-11 06:06:25-06	\N
63	24	r5CXDQjD7BgHGoYX5SNCmYvR4SYVdrTwzwl9Zows	212.243.194.65	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5342 (KHTML, like Gecko) Chrome/36.0.837.0 Mobile Safari/5342	2026-02-23 08:06:25-06	2026-02-23 16:06:25-06	2026-02-23 12:06:25-06	f	{}	2026-02-23 08:06:25-06	2026-02-23 08:06:25-06	\N
64	24	BtoyRWE8dbdCXEIE1YIOAHxVNU2pJbopVtpUxZr2	43.226.75.29	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5341 (KHTML, like Gecko) Chrome/37.0.845.0 Mobile Safari/5341	2026-03-07 20:06:25-06	2026-03-08 04:06:25-06	2026-03-07 23:06:25-06	f	{}	2026-03-07 20:06:25-06	2026-03-07 20:06:25-06	\N
65	25	84u9dVtKjwnvuhXQ8SdPJ9ob1BbFboEWLSMFHkrI	192.42.94.145	Mozilla/5.0 (Windows; U; Windows 95) AppleWebKit/533.39.1 (KHTML, like Gecko) Version/5.1 Safari/533.39.1	2026-02-26 03:06:25-06	2026-02-26 11:06:25-06	\N	t	{}	2026-02-26 03:06:25-06	2026-02-26 03:06:25-06	\N
66	25	x8WEBBvXccg2IS8BR92i7tsUCmdzu5JJ8dBhfvrH	156.49.22.16	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_6 rv:6.0) Gecko/20250330 Firefox/36.0	2026-03-06 13:06:25-06	2026-03-06 21:06:25-06	2026-03-06 14:06:25-06	f	{}	2026-03-06 13:06:25-06	2026-03-06 13:06:25-06	\N
67	25	WbDdLXXjINWJm8vncCxjCXa7K7Mg6LVe3e1IKqqn	127.192.123.83	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5320 (KHTML, like Gecko) Chrome/40.0.895.0 Mobile Safari/5320	2026-03-05 09:06:25-06	2026-03-05 17:06:25-06	2026-03-05 10:06:25-06	f	{}	2026-03-05 09:06:25-06	2026-03-05 09:06:25-06	\N
68	25	PdNJZTElgty58xVd9AJ8D3DHLrS8BOXK7tzVMyz2	28.155.185.72	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 6.1; Trident/5.1)	2026-03-02 16:06:25-06	2026-03-03 00:06:25-06	2026-03-02 18:06:25-06	f	{}	2026-03-02 16:06:25-06	2026-03-02 16:06:25-06	\N
69	26	XiRXTT3wdP4uCgrFTDzqW8xYgyTPWVbZpOu2Tj3y	63.107.101.27	Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.0; Trident/3.0)	2026-03-14 10:06:25-06	2026-03-14 18:06:25-06	2026-03-14 11:06:25-06	f	{}	2026-03-14 10:06:25-06	2026-03-14 10:06:25-06	\N
70	26	yRPWAbybey0WJ3rzgXnXmpm5nZtfUoNg7jpJuwpl	7.34.66.222	Mozilla/5.0 (compatible; MSIE 5.0; Windows NT 5.01; Trident/4.1)	2026-03-08 21:06:25-06	2026-03-09 05:06:25-06	2026-03-09 01:06:25-06	f	{}	2026-03-08 21:06:25-06	2026-03-08 21:06:25-06	\N
71	26	jpFnCL6uKszOVrFyg0ucNPFVeAqXFaheghSi6P0q	17.79.175.38	Mozilla/5.0 (Windows NT 6.1; nl-NL; rv:1.9.1.20) Gecko/20150212 Firefox/35.0	2026-03-21 04:06:25-06	2026-03-21 12:06:25-06	2026-03-21 07:06:25-06	f	{}	2026-03-21 04:06:25-06	2026-03-21 04:06:25-06	\N
72	26	wW5Woqowap872GhaZIwBkZF2QDJ3MfYs8m2m1XzS	141.230.151.139	Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 6.1; Trident/4.0)	2026-02-27 06:06:25-06	2026-02-27 14:06:25-06	2026-02-27 07:06:25-06	f	{}	2026-02-27 06:06:25-06	2026-02-27 06:06:25-06	\N
73	26	t5TR8BY32Qtyd8VbBfcZSkMQgeYjZSwQkiAA1Evs	243.17.175.19	Mozilla/5.0 (X11; Linux i686) AppleWebKit/534.1 (KHTML, like Gecko) Chrome/84.0.4779.70 Safari/534.1 EdgA/84.01085.71	2026-03-17 04:06:25-06	2026-03-17 12:06:25-06	2026-03-17 05:06:25-06	f	{}	2026-03-17 04:06:25-06	2026-03-17 04:06:25-06	\N
74	27	oAhW2zY7yc2fr5crnPsMceeiTrSkPuDm0yoNWQbX	46.233.106.235	Mozilla/5.0 (X11; Linux x86_64; rv:7.0) Gecko/20210508 Firefox/35.0	2026-02-24 23:06:25-06	2026-02-25 07:06:25-06	\N	t	{}	2026-02-24 23:06:25-06	2026-02-24 23:06:25-06	\N
75	27	HvhFjTQ9jhzsW5vmPHz7nks1xiYcJLUfkwpYZcnx	165.167.178.190	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_4 rv:5.0; nl-NL) AppleWebKit/532.34.5 (KHTML, like Gecko) Version/5.0.4 Safari/532.34.5	2026-03-04 13:06:25-06	2026-03-04 21:06:25-06	2026-03-04 14:06:25-06	f	{}	2026-03-04 13:06:25-06	2026-03-04 13:06:25-06	\N
76	27	cZA5F4h04wdMigt3LK79N1BGDWfdx3O1tNk0TQ2k	188.39.41.219	Opera/9.42 (X11; Linux i686; en-US) Presto/2.9.350 Version/11.00	2026-03-14 13:06:25-06	2026-03-14 21:06:25-06	2026-03-14 17:06:25-06	f	{}	2026-03-14 13:06:25-06	2026-03-14 13:06:25-06	\N
77	27	9jEOnW6DSHlbZ2ZFqEYrVrvFwledqhLYBX1TxDz2	151.42.172.203	Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/535.24.3 (KHTML, like Gecko) Version/5.1 Safari/535.24.3	2026-03-04 16:06:25-06	2026-03-05 00:06:25-06	2026-03-04 17:06:25-06	f	{}	2026-03-04 16:06:25-06	2026-03-04 16:06:25-06	\N
78	27	3ywHFFWqct4vK24VWe3NEsA5LXmQ8LhcZHcZ4TU0	13.49.146.252	Mozilla/5.0 (Windows CE) AppleWebKit/536.1 (KHTML, like Gecko) Chrome/94.0.4500.17 Safari/536.1 Edg/94.01066.75	2026-02-28 12:06:25-06	2026-02-28 20:06:25-06	2026-02-28 13:06:25-06	f	{}	2026-02-28 12:06:25-06	2026-02-28 12:06:25-06	\N
79	28	3rXqO5SQgzjUAulOVjDeCXDlWnLknt1t01r1BO63	59.110.77.38	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/533.1 (KHTML, like Gecko) Chrome/81.0.4410.14 Safari/533.1 EdgA/81.01128.96	2026-03-18 20:06:25-06	2026-03-19 04:06:25-06	2026-03-18 22:06:25-06	f	{}	2026-03-18 20:06:25-06	2026-03-18 20:06:25-06	\N
80	28	RMeQvHWLC5cxohUqt3sBWbHTqhGQHTQesWE2qo4S	206.211.68.174	Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 4.0; Trident/3.0)	2026-03-11 14:06:25-06	2026-03-11 22:06:25-06	2026-03-11 15:06:25-06	f	{}	2026-03-11 14:06:25-06	2026-03-11 14:06:25-06	\N
81	29	SQCzbymjztKZW879iBwYHEs7moz8PsH9QkXM0fnc	101.30.131.109	Mozilla/5.0 (Windows; U; Windows CE) AppleWebKit/534.39.7 (KHTML, like Gecko) Version/4.0.2 Safari/534.39.7	2026-02-22 14:06:25-06	2026-02-22 22:06:25-06	2026-02-22 17:06:25-06	f	{}	2026-02-22 14:06:25-06	2026-02-22 14:06:25-06	\N
82	29	tDMatsw9XWJp8YQxtVegCjnUROM5DNV278hJlm3K	11.94.111.234	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_7) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/84.0.4447.48 Safari/535.2 Edg/84.01103.46	2026-03-01 10:06:25-06	2026-03-01 18:06:25-06	2026-03-01 11:06:25-06	f	{}	2026-03-01 10:06:25-06	2026-03-01 10:06:25-06	\N
83	29	r33IBZYypbKYaDeQq6b5yz33XqUQoUH1WkwRB6CA	189.105.78.103	Opera/9.36 (Windows NT 5.01; sl-SI) Presto/2.12.275 Version/10.00	2026-03-14 17:06:25-06	2026-03-15 01:06:25-06	2026-03-14 20:06:25-06	f	{}	2026-03-14 17:06:25-06	2026-03-14 17:06:25-06	\N
84	29	gaYrJf7dPIBIkqng4rbF2BUQ3rit0kEvhQ6SdErI	61.152.43.112	Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/4.0)	2026-03-23 23:06:25-06	2026-03-24 07:06:25-06	2026-03-24 03:06:25-06	f	{}	2026-03-23 23:06:25-06	2026-03-23 23:06:25-06	\N
85	29	4kK3PTsdExAC58J3oamK0lVK8p1aLK3ibVuD3gqS	183.72.37.136	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/93.0.4019.20 Safari/535.1 EdgA/93.01114.10	2026-03-15 09:06:25-06	2026-03-15 17:06:25-06	2026-03-15 10:06:25-06	f	{}	2026-03-15 09:06:25-06	2026-03-15 09:06:25-06	\N
86	30	824qwlu3fgWvySGMXMZeInlfhxNzmsYpceQep3Hn	11.32.251.177	Opera/9.51 (X11; Linux x86_64; sl-SI) Presto/2.11.297 Version/10.00	2026-02-23 12:06:25-06	2026-02-23 20:06:25-06	\N	t	{}	2026-02-23 12:06:25-06	2026-02-23 12:06:25-06	\N
87	30	8bgCM8xKgcZgwrFnaNl9Tm227344CD6w8KPiVzAS	109.33.43.27	Mozilla/5.0 (Windows NT 4.0) AppleWebKit/5322 (KHTML, like Gecko) Chrome/37.0.864.0 Mobile Safari/5322	2026-03-18 08:06:25-06	2026-03-18 16:06:25-06	2026-03-18 10:06:25-06	f	{}	2026-03-18 08:06:25-06	2026-03-18 08:06:25-06	\N
88	30	ie0aNDnYtq00aJsweo2v2KLkUIgtus9wnM1iT3LX	75.193.155.40	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_6) AppleWebKit/533.2 (KHTML, like Gecko) Chrome/92.0.4228.49 Safari/533.2 Edg/92.01106.64	2026-02-26 06:06:25-06	2026-02-26 14:06:25-06	2026-02-26 07:06:25-06	f	{}	2026-02-26 06:06:25-06	2026-02-26 06:06:25-06	\N
89	31	g1gSezGE0oA5Zrx4wHrmEmYIGcV8hn3frcfanKsx	68.247.159.166	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_5_3 rv:6.0) Gecko/20160614 Firefox/35.0	2026-02-28 06:06:25-06	2026-02-28 14:06:25-06	\N	t	{}	2026-02-28 06:06:25-06	2026-02-28 06:06:25-06	\N
90	31	TRsDve92gqsSfNF5fh3wXHqvQWQuvwhN0sWKJ6a5	61.216.183.254	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_6) AppleWebKit/533.0 (KHTML, like Gecko) Chrome/99.0.4768.93 Safari/533.0 Edg/99.01025.52	2026-02-25 07:06:25-06	2026-02-25 15:06:25-06	2026-02-25 09:06:25-06	f	{}	2026-02-25 07:06:25-06	2026-02-25 07:06:25-06	\N
91	31	kebok5DuVy7Hw6foyUhRWO0SkIZaMYjBHGBZJeIP	107.150.148.86	Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.1)	2026-02-23 12:06:25-06	2026-02-23 20:06:25-06	2026-02-23 14:06:25-06	f	{}	2026-02-23 12:06:25-06	2026-02-23 12:06:25-06	\N
92	31	3SWQxmY6q5JT5SjRSVMJdUNqzzfkRpEu0IvzFyyt	230.139.165.102	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_8 rv:4.0) Gecko/20150221 Firefox/36.0	2026-03-05 16:06:25-06	2026-03-06 00:06:25-06	2026-03-05 17:06:25-06	f	{}	2026-03-05 16:06:25-06	2026-03-05 16:06:25-06	\N
93	32	K5zFF3I5mlQ0MfMJ5K8IHUcs4gTV7vKGJKXTss4q	77.152.172.165	Opera/8.19 (Windows NT 5.01; sl-SI) Presto/2.10.300 Version/12.00	2026-03-07 13:06:25-06	2026-03-07 21:06:25-06	2026-03-07 16:06:25-06	f	{}	2026-03-07 13:06:25-06	2026-03-07 13:06:25-06	\N
94	32	86AtesltNBN2cYpeYixvYR5gnILuvuSk0oxh917B	188.56.115.242	Mozilla/5.0 (X11; Linux i686) AppleWebKit/5342 (KHTML, like Gecko) Chrome/36.0.877.0 Mobile Safari/5342	2026-02-24 08:06:25-06	2026-02-24 16:06:25-06	2026-02-24 12:06:25-06	f	{}	2026-02-24 08:06:25-06	2026-02-24 08:06:25-06	\N
95	32	OzFcW50b5g6GkN59TyhZMTo2GmPKjnTZIxU1aUr1	121.56.114.217	Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20240713 Firefox/36.0	2026-03-08 10:06:25-06	2026-03-08 18:06:25-06	2026-03-08 14:06:25-06	f	{}	2026-03-08 10:06:25-06	2026-03-08 10:06:25-06	\N
96	32	vfCLI6CNsIjKVaidvndYeXQuEK4Jasnr2cHg3AHf	206.218.229.181	Opera/9.54 (Windows 98; nl-NL) Presto/2.12.288 Version/12.00	2026-03-11 15:06:25-06	2026-03-11 23:06:25-06	2026-03-11 19:06:25-06	f	{}	2026-03-11 15:06:25-06	2026-03-11 15:06:25-06	\N
97	32	d1U7oF2LwnSzUjW7Iiwan9EqjUqRlyhewaG1Yk25	183.73.174.58	Mozilla/5.0 (iPad; CPU OS 7_0_1 like Mac OS X; sl-SI) AppleWebKit/531.22.2 (KHTML, like Gecko) Version/4.0.5 Mobile/8B114 Safari/6531.22.2	2026-03-15 19:06:25-06	2026-03-16 03:06:25-06	2026-03-15 21:06:25-06	f	{}	2026-03-15 19:06:25-06	2026-03-15 19:06:25-06	\N
98	33	VsMVPedR7vwlAgZ80lhq6pcxdYS9cM4iv4lC2ArT	151.102.5.9	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_8 rv:3.0) Gecko/20150109 Firefox/37.0	2026-03-09 09:06:25-06	2026-03-09 17:06:25-06	\N	t	{}	2026-03-09 09:06:25-06	2026-03-09 09:06:25-06	\N
99	33	zDFDe7tmX3lgaY5qRHQsD1YykyButlQVXu86xDSs	227.16.35.150	Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X; en-US) AppleWebKit/531.9.3 (KHTML, like Gecko) Version/4.0.5 Mobile/8B119 Safari/6531.9.3	2026-02-27 21:06:25-06	2026-02-28 05:06:25-06	2026-02-28 01:06:25-06	f	{}	2026-02-27 21:06:25-06	2026-02-27 21:06:25-06	\N
100	33	xqTGHtJSrgPD4b7LSHiSBDwNkjpbJw0VrXCMApOU	210.158.171.163	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_5_7 rv:6.0; sl-SI) AppleWebKit/533.7.5 (KHTML, like Gecko) Version/4.0 Safari/533.7.5	2026-03-08 05:06:25-06	2026-03-08 13:06:25-06	2026-03-08 07:06:25-06	f	{}	2026-03-08 05:06:25-06	2026-03-08 05:06:25-06	\N
101	33	4T1ATicnHgmFRboBByPSQn5b9ZycTD3NYxUTa78q	19.130.3.225	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_0) AppleWebKit/533.1 (KHTML, like Gecko) Chrome/93.0.4615.25 Safari/533.1 Edg/93.01016.63	2026-03-22 21:06:25-06	2026-03-23 05:06:25-06	2026-03-22 23:06:25-06	f	{}	2026-03-22 21:06:25-06	2026-03-22 21:06:25-06	\N
102	33	vdu6guCFYvAUSvMILqxNK34XBMeH1y0oPvb9eDNV	50.50.68.100	Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_2 rv:5.0; en-US) AppleWebKit/534.7.3 (KHTML, like Gecko) Version/5.0.2 Safari/534.7.3	2026-02-24 22:06:25-06	2026-02-25 06:06:25-06	2026-02-24 23:06:25-06	f	{}	2026-02-24 22:06:25-06	2026-02-24 22:06:25-06	\N
103	34	ve5xHQTP0tvYLC5zQXwgQrH3aKhXgtB81MwWD8rN	248.240.111.65	Opera/8.71 (X11; Linux x86_64; nl-NL) Presto/2.10.246 Version/12.00	2026-02-24 15:06:25-06	2026-02-24 23:06:25-06	2026-02-24 18:06:25-06	f	{}	2026-02-24 15:06:25-06	2026-02-24 15:06:25-06	\N
104	34	gYUG81v3NSanyC2nhb2mkxdKMz2NhUJhJgLFSuk7	176.206.198.192	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_7 rv:6.0) Gecko/20160816 Firefox/36.0	2026-03-12 05:06:25-06	2026-03-12 13:06:25-06	2026-03-12 06:06:25-06	f	{}	2026-03-12 05:06:25-06	2026-03-12 05:06:25-06	\N
105	34	PpIuertBfh2I4JykE6ZrmMVo9YYmGxdjocjt9s9k	221.214.105.242	Mozilla/5.0 (Windows NT 5.01; nl-NL; rv:1.9.2.20) Gecko/20100903 Firefox/37.0	2026-03-14 13:06:25-06	2026-03-14 21:06:25-06	2026-03-14 15:06:25-06	f	{}	2026-03-14 13:06:25-06	2026-03-14 13:06:25-06	\N
106	35	ne2piz3ssFjXUw8dN70VonYXuclXAk1VtoXmTwDu	216.192.177.73	Mozilla/5.0 (compatible; MSIE 9.0; Windows 98; Win 9x 4.90; Trident/5.0)	2026-03-05 01:06:25-06	2026-03-05 09:06:25-06	2026-03-05 03:06:25-06	f	{}	2026-03-05 01:06:25-06	2026-03-05 01:06:25-06	\N
107	35	zDjXlr2dVYM6uZDVGuOEA0lKVuThs2BcS4vPaUKS	55.105.19.44	Mozilla/5.0 (Windows NT 6.2; nl-NL; rv:1.9.2.20) Gecko/20241013 Firefox/35.0	2026-03-01 19:06:25-06	2026-03-02 03:06:25-06	2026-03-01 22:06:25-06	f	{}	2026-03-01 19:06:25-06	2026-03-01 19:06:25-06	\N
108	35	TQZfZrePXkoHD06CbqCXzV36cA5SAs1YLfVw7paG	149.208.247.111	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5320 (KHTML, like Gecko) Chrome/40.0.866.0 Mobile Safari/5320	2026-02-23 04:06:25-06	2026-02-23 12:06:25-06	2026-02-23 08:06:25-06	f	{}	2026-02-23 04:06:25-06	2026-02-23 04:06:25-06	\N
109	35	QVzcQakxwAn7NvWovS40VKPeuIbKKSwiaRrmLqjv	94.16.181.123	Mozilla/5.0 (Windows CE) AppleWebKit/532.2 (KHTML, like Gecko) Chrome/94.0.4412.37 Safari/532.2 Edg/94.01077.99	2026-02-24 12:06:25-06	2026-02-24 20:06:25-06	2026-02-24 14:06:25-06	f	{}	2026-02-24 12:06:25-06	2026-02-24 12:06:25-06	\N
110	36	VT7QViBKD4AZIZfYmo0vwnNiCVxSVtIcrr3q18AR	168.25.235.211	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_1) AppleWebKit/534.2 (KHTML, like Gecko) Chrome/80.0.4463.16 Safari/534.2 Edg/80.01109.45	2026-02-23 15:06:25-06	2026-02-23 23:06:25-06	\N	t	{}	2026-02-23 15:06:25-06	2026-02-23 15:06:25-06	\N
111	36	dnO1zQRWd5wzntG9D7li4lFFa954W7i0Sos7jurd	58.218.135.79	Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/96.0.4416.41 Safari/537.1 Edg/96.01144.8	2026-03-08 18:06:25-06	2026-03-09 02:06:25-06	2026-03-08 21:06:25-06	f	{}	2026-03-08 18:06:25-06	2026-03-08 18:06:25-06	\N
112	36	5oXFdanXoM6U1oaX7MuegWRluBPk1RccYojx4uX2	35.28.242.173	Mozilla/5.0 (Windows NT 4.0) AppleWebKit/532.1 (KHTML, like Gecko) Chrome/97.0.4223.99 Safari/532.1 Edg/97.01066.34	2026-03-06 23:06:25-06	2026-03-07 07:06:25-06	2026-03-07 00:06:25-06	f	{}	2026-03-06 23:06:25-06	2026-03-06 23:06:25-06	\N
113	37	tkXzsxg1mmFgmuma64sj7xIowTwClEs2wJT4atVy	25.162.176.235	Mozilla/5.0 (Windows; U; Windows NT 6.1) AppleWebKit/534.48.4 (KHTML, like Gecko) Version/4.0.4 Safari/534.48.4	2026-02-28 04:06:25-06	2026-02-28 12:06:25-06	2026-02-28 05:06:25-06	f	{}	2026-02-28 04:06:25-06	2026-02-28 04:06:25-06	\N
114	37	jRDo4EpSYHo6HnMTvHnE5wMJFFdAGVxVpUNUExno	166.11.135.68	Mozilla/5.0 (Windows; U; Windows NT 6.2) AppleWebKit/535.3.4 (KHTML, like Gecko) Version/4.1 Safari/535.3.4	2026-03-04 15:06:25-06	2026-03-04 23:06:25-06	2026-03-04 18:06:25-06	f	{}	2026-03-04 15:06:25-06	2026-03-04 15:06:25-06	\N
115	37	IRovQNYrGtyvC8qPIGXNkwI7BZ0B4O1EYnLJ8Ww7	129.188.240.231	Mozilla/5.0 (iPhone; CPU iPhone OS 15_1 like Mac OS X) AppleWebKit/535.1 (KHTML, like Gecko) Version/15.0 EdgiOS/87.01061.80 Mobile/15E148 Safari/535.1	2026-03-05 19:06:25-06	2026-03-06 03:06:25-06	2026-03-05 20:06:25-06	f	{}	2026-03-05 19:06:25-06	2026-03-05 19:06:25-06	\N
116	37	apWNk5q7i3euGYq6BkQJ6ieu94R1OhlvYUIpe2Nf	210.255.163.115	Opera/8.79 (X11; Linux x86_64; en-US) Presto/2.10.219 Version/10.00	2026-03-08 04:06:25-06	2026-03-08 12:06:25-06	2026-03-08 07:06:25-06	f	{}	2026-03-08 04:06:25-06	2026-03-08 04:06:25-06	\N
117	37	ikDf6EBeOUWrCtOSxPA8OuIwM7qefBzO7Zg5Xpp6	10.65.67.66	Mozilla/5.0 (iPhone; CPU iPhone OS 13_2 like Mac OS X) AppleWebKit/532.2 (KHTML, like Gecko) Version/15.0 EdgiOS/80.01031.35 Mobile/15E148 Safari/532.2	2026-03-02 08:06:25-06	2026-03-02 16:06:25-06	2026-03-02 11:06:25-06	f	{}	2026-03-02 08:06:25-06	2026-03-02 08:06:25-06	\N
118	38	oNoWUbTHX5W4BbKP9XMnzD349fATpKb7BRhkuAeZ	123.35.163.42	Mozilla/5.0 (iPad; CPU OS 8_1_1 like Mac OS X; nl-NL) AppleWebKit/534.44.3 (KHTML, like Gecko) Version/3.0.5 Mobile/8B116 Safari/6534.44.3	2026-02-26 19:06:25-06	2026-02-27 03:06:25-06	2026-02-26 21:06:25-06	f	{}	2026-02-26 19:06:25-06	2026-02-26 19:06:25-06	\N
119	38	Xpgh4SbgfKRUrOfvsjt7WeIjCriKQ2mh5zKu5IAO	159.108.102.150	Opera/9.35 (Windows NT 5.01; nl-NL) Presto/2.12.216 Version/12.00	2026-03-01 04:06:25-06	2026-03-01 12:06:25-06	2026-03-01 08:06:25-06	f	{}	2026-03-01 04:06:25-06	2026-03-01 04:06:25-06	\N
120	38	ne22SPUhJMth5wozxfWecFyMQc4x7vKj4Isw5zWi	204.68.178.234	Mozilla/5.0 (Windows; U; Windows NT 6.0) AppleWebKit/532.15.2 (KHTML, like Gecko) Version/5.0 Safari/532.15.2	2026-03-20 08:06:25-06	2026-03-20 16:06:25-06	2026-03-20 12:06:25-06	f	{}	2026-03-20 08:06:25-06	2026-03-20 08:06:25-06	\N
121	39	sZHGyI6oZOX83dqAknyRLZttFyFCHePYnR4MOvES	100.234.146.78	Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/84.0.4121.89 Safari/537.1 Edg/84.01034.33	2026-02-25 20:06:25-06	2026-02-26 04:06:25-06	2026-02-25 23:06:25-06	f	{}	2026-02-25 20:06:25-06	2026-02-25 20:06:25-06	\N
122	39	vvJWbk4lkqQQNSidB4IWnp1EyqZzVDrrdLr6iRhw	39.157.183.171	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/88.0.4596.25 Safari/532.0 EdgA/88.01103.78	2026-03-16 23:06:25-06	2026-03-17 07:06:25-06	2026-03-17 02:06:25-06	f	{}	2026-03-16 23:06:25-06	2026-03-16 23:06:25-06	\N
123	39	nwZiFfztmM7gTQg0as0yoMLW4Wm8NgYddIqKxXTN	130.191.235.44	Mozilla/5.0 (iPad; CPU OS 7_1_1 like Mac OS X; nl-NL) AppleWebKit/535.37.1 (KHTML, like Gecko) Version/3.0.5 Mobile/8B114 Safari/6535.37.1	2026-02-24 00:06:25-06	2026-02-24 08:06:25-06	2026-02-24 02:06:25-06	f	{}	2026-02-24 00:06:25-06	2026-02-24 00:06:25-06	\N
124	39	DIB8aXLAxCDd1ZOHotuRcrVfDlQSlLhrH4Uh1ADL	145.174.51.164	Mozilla/5.0 (iPhone; CPU iPhone OS 15_1 like Mac OS X) AppleWebKit/535.0 (KHTML, like Gecko) Version/15.0 EdgiOS/86.01044.89 Mobile/15E148 Safari/535.0	2026-03-10 11:06:25-06	2026-03-10 19:06:25-06	2026-03-10 12:06:25-06	f	{}	2026-03-10 11:06:25-06	2026-03-10 11:06:25-06	\N
125	39	E9oV0wFVf5NtFcQh1smZ6u6i2N7Mam8qInwUkY4y	236.93.219.151	Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.0.20) Gecko/20190915 Firefox/37.0	2026-03-23 12:06:25-06	2026-03-23 20:06:25-06	2026-03-23 14:06:25-06	f	{}	2026-03-23 12:06:25-06	2026-03-23 12:06:25-06	\N
126	40	tfzJuQci8OJhOEoptGWdUU4qtkZsBeBhiFZ4Jh7K	251.192.216.117	Opera/8.21 (Windows NT 5.2; nl-NL) Presto/2.9.193 Version/10.00	2026-02-26 16:06:25-06	2026-02-27 00:06:25-06	\N	t	{}	2026-02-26 16:06:25-06	2026-02-26 16:06:25-06	\N
127	40	wstHYicotmOvSWtGJWFuE86tRW87QBUihaKkfs4G	105.101.135.194	Mozilla/5.0 (Windows NT 5.1; nl-NL; rv:1.9.0.20) Gecko/20140214 Firefox/35.0	2026-03-18 19:06:25-06	2026-03-19 03:06:25-06	2026-03-18 21:06:25-06	f	{}	2026-03-18 19:06:25-06	2026-03-18 19:06:25-06	\N
128	41	qWh0EOGFPamV5oFVkl2YalPFilmVqOINPVDimgVx	92.72.87.130	Mozilla/5.0 (Windows 98; sl-SI; rv:1.9.1.20) Gecko/20130513 Firefox/36.0	2026-03-04 03:06:25-06	2026-03-04 11:06:25-06	2026-03-04 07:06:25-06	f	{}	2026-03-04 03:06:25-06	2026-03-04 03:06:25-06	\N
129	41	n5xlLr5AeYF0G1UjPMMo8V54ZK91xiO5rSUeKoYW	19.17.226.242	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/4.1)	2026-03-05 09:06:25-06	2026-03-05 17:06:25-06	2026-03-05 10:06:25-06	f	{}	2026-03-05 09:06:25-06	2026-03-05 09:06:25-06	\N
130	41	d5qt1Qcu12tJeFy1pvtvUef99VbbODQsGwNEqaiv	154.223.160.66	Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/3.0)	2026-03-02 17:06:25-06	2026-03-03 01:06:25-06	2026-03-02 20:06:25-06	f	{}	2026-03-02 17:06:25-06	2026-03-02 17:06:25-06	\N
131	41	gz96JIRbjefM6IW5X14j3r7MohbcvUWlgWXU9EFk	97.138.126.100	Opera/8.28 (Windows NT 6.1; sl-SI) Presto/2.10.281 Version/11.00	2026-03-09 14:06:25-06	2026-03-09 22:06:25-06	2026-03-09 16:06:25-06	f	{}	2026-03-09 14:06:25-06	2026-03-09 14:06:25-06	\N
132	41	S8qLi3zDEwn9d9gIAtRVvyB5zT19JF5IKvgpslUm	114.4.159.92	Opera/8.44 (X11; Linux x86_64; sl-SI) Presto/2.11.308 Version/11.00	2026-03-10 01:06:25-06	2026-03-10 09:06:25-06	2026-03-10 03:06:25-06	f	{}	2026-03-10 01:06:25-06	2026-03-10 01:06:25-06	\N
133	42	vwVD3mrPJPJTe8Q5FSLlXB2x88FildG8acaLlbNE	10.183.168.244	Mozilla/5.0 (compatible; MSIE 5.0; Windows 98; Trident/5.1)	2026-02-23 04:06:25-06	2026-02-23 12:06:25-06	\N	t	{}	2026-02-23 04:06:25-06	2026-02-23 04:06:25-06	\N
134	42	puKr2McusS9lUTF6nIAvDHoY5bmq55rpf4MIRxEu	147.205.158.21	Mozilla/5.0 (iPhone; CPU iPhone OS 13_0 like Mac OS X) AppleWebKit/537.2 (KHTML, like Gecko) Version/15.0 EdgiOS/85.01128.60 Mobile/15E148 Safari/537.2	2026-02-26 14:06:25-06	2026-02-26 22:06:25-06	2026-02-26 15:06:25-06	f	{}	2026-02-26 14:06:25-06	2026-02-26 14:06:25-06	\N
135	42	TBiLL38gUzpLggucKIvET0PURlu0kkHjlDPAbJaT	140.87.225.71	Mozilla/5.0 (Macintosh; PPC Mac OS X 10_5_4) AppleWebKit/533.0 (KHTML, like Gecko) Chrome/98.0.4326.14 Safari/533.0 Edg/98.01081.93	2026-03-10 20:06:25-06	2026-03-11 04:06:25-06	2026-03-10 23:06:25-06	f	{}	2026-03-10 20:06:25-06	2026-03-10 20:06:25-06	\N
136	1	2AxGUWIyh50eAGznrjlDzynzwTjPhKFn60a5fkeM	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-24 23:03:23-06	2026-03-25 01:03:23-06	\N	t	{}	2026-03-24 23:03:23-06	2026-03-24 23:03:23-06	\N
137	2	UEth0yn8CnVCEVGMsRO3YplTL4O31CSGTXSozAWs	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-24 23:05:56-06	2026-03-25 01:05:56-06	\N	t	{}	2026-03-24 23:05:56-06	2026-03-24 23:05:56-06	\N
138	2	TGdSOMq7CbMpuUvJgqysjTxPLkJ8tnS1MwwgausP	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-25 23:20:54-06	2026-03-26 01:20:54-06	\N	t	{}	2026-03-25 23:20:54-06	2026-03-25 23:20:54-06	\N
139	2	pc66rPbpzRihUhwgFDSnUk24Znr0ZrH8CnEX6fZM	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-25 23:56:37-06	2026-03-26 01:56:37-06	\N	t	{}	2026-03-25 23:56:37-06	2026-03-25 23:56:37-06	\N
140	1	tzXp9WPfV8Ofmjcn7pjiGUnMoSOIcwQGofdgVHbA	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-26 00:30:26-06	2026-03-26 02:30:26-06	\N	t	{}	2026-03-26 00:30:26-06	2026-03-26 00:30:26-06	\N
141	1	FPe71rIiz4RyKl6AIEPWD2mNTJWlwCpDSBe9ThWI	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 19:53:25-06	2026-03-27 21:53:25-06	\N	t	{}	2026-03-27 19:53:25-06	2026-03-27 19:53:25-06	\N
142	43	HSrRCEMkj64XwPhV9vg8UdQ8CF5AGqNdcsWKvkPD	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 20:27:53-06	2026-03-27 22:27:53-06	\N	t	{}	2026-03-27 20:27:53-06	2026-03-27 20:27:53-06	\N
143	1	U1DbVgh3zxAlM4m0gAxECN4ViXCybrYkQvW6FQBe	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 20:28:53-06	2026-03-27 22:28:53-06	\N	t	{}	2026-03-27 20:28:53-06	2026-03-27 20:28:53-06	\N
144	43	V1CkeSLKqQoYPhvJh2V3l4SCd1gJlnhXjEKewaKv	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 21:04:24-06	2026-03-27 23:04:24-06	\N	t	{}	2026-03-27 21:04:24-06	2026-03-27 21:04:24-06	\N
145	2	0RkVebdlGppHCEbMvbjOPWEDLRletNDZvPcchd1N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 21:04:57-06	2026-03-27 23:04:57-06	\N	t	{}	2026-03-27 21:04:57-06	2026-03-27 21:04:57-06	\N
146	2	hCJAgRT3aMIoWusSNt0e0XsDYiNju0M4JO6uNykZ	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 21:12:47-06	2026-03-27 23:12:47-06	\N	t	{}	2026-03-27 21:12:47-06	2026-03-27 21:12:47-06	\N
147	1	eVZfnzPtDBqDo5gMJ53q7bmOnmTS3by2L4vOnYUh	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 22:11:24-06	2026-03-28 00:11:24-06	\N	t	{}	2026-03-27 22:11:24-06	2026-03-27 22:11:24-06	\N
148	43	yfrW0ougOfChLkijYpgWCYifL5NUXhdQDSZhGNcS	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 22:12:44-06	2026-03-28 00:12:44-06	\N	t	{}	2026-03-27 22:12:44-06	2026-03-27 22:12:44-06	\N
149	2	Ucr575xaCWlc4LWYeDc1vmtZi97dDD5L6bIbSpI5	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 22:13:08-06	2026-03-28 00:13:08-06	\N	t	{}	2026-03-27 22:13:08-06	2026-03-27 22:13:08-06	\N
150	1	FolGcYhwiZzxioBSiKRnviltyyl9WIRSD2c4B3VU	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 22:19:30-06	2026-03-28 00:19:30-06	\N	t	{}	2026-03-27 22:19:30-06	2026-03-27 22:19:30-06	\N
151	2	bRrPMnFUvcZby7Tc3jaKq2XukcCEEnR12Sh5GJex	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 23:24:53-06	2026-03-28 01:24:53-06	\N	t	{}	2026-03-27 23:24:53-06	2026-03-27 23:24:53-06	\N
152	43	0JAgDES3D8SiTcRT3HLQNvUR72OGo8K4Ui7wfb61	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 23:25:23-06	2026-03-28 01:25:23-06	\N	t	{}	2026-03-27 23:25:23-06	2026-03-27 23:25:23-06	\N
153	1	CxAXcGGVlfvSRzsusiSXpGxNF3DJiuXTvIvJo3vd	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 23:25:57-06	2026-03-28 01:25:57-06	\N	t	{}	2026-03-27 23:25:57-06	2026-03-27 23:25:57-06	\N
154	43	swdRRDSOF169FnLtla1wLoP4Yw8tQiTDBQ5J4s9M	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 23:35:22-06	2026-03-28 01:35:22-06	\N	t	{}	2026-03-27 23:35:22-06	2026-03-27 23:35:22-06	\N
155	2	D2P7SYKJ6m7nAmCRGhU59CMLxM7w7cbevMAmXWyL	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 23:35:56-06	2026-03-28 01:35:56-06	\N	t	{}	2026-03-27 23:35:56-06	2026-03-27 23:35:56-06	\N
156	1	xpWiDsW5rkkFjWy6IHZwG199WNqgDJ8DRof0w7Hf	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 23:36:19-06	2026-03-28 01:36:19-06	\N	t	{}	2026-03-27 23:36:19-06	2026-03-27 23:36:19-06	\N
157	44	mwb4D2GbUZT90GNvQ0WGlDCG5xheA10jT9QYyDQr	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 23:47:02-06	2026-03-28 01:47:02-06	\N	t	{}	2026-03-27 23:47:02-06	2026-03-27 23:47:02-06	\N
158	1	sVppC4f4gfYMlFdZO55iL3ZU2KUKjohV6zevHbEL	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0	2026-03-27 23:47:46-06	2026-03-28 01:47:46-06	\N	t	{}	2026-03-27 23:47:46-06	2026-03-27 23:47:46-06	\N
\.


--
-- Name: acceso_bitacora_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.acceso_bitacora_id_seq', 306, true);


--
-- Name: actividad_bitacora_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.actividad_bitacora_id_seq', 300, true);


--
-- Name: archivo_carpeta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.archivo_carpeta_id_seq', 1, false);


--
-- Name: archivo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.archivo_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.migrations_id_seq', 30, true);


--
-- Name: pedido_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.pedido_id_seq', 69, true);


--
-- Name: permiso_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.permiso_id_seq', 80, true);


--
-- Name: producto_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.producto_id_seq', 6, true);


--
-- Name: rol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.rol_id_seq', 6, true);


--
-- Name: rol_permiso_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.rol_permiso_id_seq', 118, true);


--
-- Name: saldo_monedero_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.saldo_monedero_id_seq', 39, true);


--
-- Name: saldo_movimiento_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.saldo_movimiento_id_seq', 227, true);


--
-- Name: tarjeta_lectura_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.tarjeta_lectura_id_seq', 636, true);


--
-- Name: tarjeta_universitaria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.tarjeta_universitaria_id_seq', 35, true);


--
-- Name: tienda_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.tienda_id_seq', 7, true);


--
-- Name: usuario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.usuario_id_seq', 44, true);


--
-- Name: usuario_password_reset_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.usuario_password_reset_id_seq', 1, false);


--
-- Name: usuario_perfil_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.usuario_perfil_id_seq', 3, true);


--
-- Name: usuario_rol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.usuario_rol_id_seq', 45, true);


--
-- Name: usuario_sesion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.usuario_sesion_id_seq', 158, true);


--
-- Name: acceso_bitacora acceso_bitacora_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.acceso_bitacora
    ADD CONSTRAINT acceso_bitacora_pkey PRIMARY KEY (id);


--
-- Name: actividad_bitacora actividad_bitacora_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.actividad_bitacora
    ADD CONSTRAINT actividad_bitacora_pkey PRIMARY KEY (id);


--
-- Name: archivo_carpeta archivo_carpeta_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.archivo_carpeta
    ADD CONSTRAINT archivo_carpeta_pkey PRIMARY KEY (id);


--
-- Name: archivo archivo_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.archivo
    ADD CONSTRAINT archivo_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: pedido pedido_numero_folio_unique; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_numero_folio_unique UNIQUE (numero_folio);


--
-- Name: pedido pedido_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_pkey PRIMARY KEY (id);


--
-- Name: permiso permiso_clave_unique; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.permiso
    ADD CONSTRAINT permiso_clave_unique UNIQUE (clave);


--
-- Name: permiso permiso_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.permiso
    ADD CONSTRAINT permiso_pkey PRIMARY KEY (id);


--
-- Name: producto producto_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_pkey PRIMARY KEY (id);


--
-- Name: rol rol_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.rol
    ADD CONSTRAINT rol_nombre_unique UNIQUE (nombre);


--
-- Name: rol_permiso rol_permiso_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_pkey PRIMARY KEY (id);


--
-- Name: rol rol_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.rol
    ADD CONSTRAINT rol_pkey PRIMARY KEY (id);


--
-- Name: saldo_monedero saldo_monedero_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.saldo_monedero
    ADD CONSTRAINT saldo_monedero_pkey PRIMARY KEY (id);


--
-- Name: saldo_monedero saldo_monedero_usuario_id_unique; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.saldo_monedero
    ADD CONSTRAINT saldo_monedero_usuario_id_unique UNIQUE (usuario_id);


--
-- Name: saldo_movimiento saldo_movimiento_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.saldo_movimiento
    ADD CONSTRAINT saldo_movimiento_pkey PRIMARY KEY (id);


--
-- Name: tarjeta_lectura tarjeta_lectura_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_lectura
    ADD CONSTRAINT tarjeta_lectura_pkey PRIMARY KEY (id);


--
-- Name: tarjeta_universitaria tarjeta_universitaria_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_universitaria
    ADD CONSTRAINT tarjeta_universitaria_pkey PRIMARY KEY (id);


--
-- Name: tarjeta_universitaria tarjeta_universitaria_uid_unique; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_universitaria
    ADD CONSTRAINT tarjeta_universitaria_uid_unique UNIQUE (uid);


--
-- Name: tienda tienda_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tienda
    ADD CONSTRAINT tienda_pkey PRIMARY KEY (id);


--
-- Name: rol_permiso uq_rol_permiso__rol_permiso; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT uq_rol_permiso__rol_permiso UNIQUE (rol_id, permiso_id);


--
-- Name: usuario uq_usuario__email; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT uq_usuario__email UNIQUE (email);


--
-- Name: usuario_password_reset uq_usuario_password_reset__token_hash; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_password_reset
    ADD CONSTRAINT uq_usuario_password_reset__token_hash UNIQUE (token_hash);


--
-- Name: usuario_perfil uq_usuario_perfil__usuario_id; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_perfil
    ADD CONSTRAINT uq_usuario_perfil__usuario_id UNIQUE (usuario_id);


--
-- Name: usuario_rol uq_usuario_rol__usuario_rol; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT uq_usuario_rol__usuario_rol UNIQUE (usuario_id, rol_id);


--
-- Name: usuario_password_reset usuario_password_reset_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_password_reset
    ADD CONSTRAINT usuario_password_reset_pkey PRIMARY KEY (id);


--
-- Name: usuario_perfil usuario_perfil_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_perfil
    ADD CONSTRAINT usuario_perfil_pkey PRIMARY KEY (id);


--
-- Name: usuario usuario_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_pkey PRIMARY KEY (id);


--
-- Name: usuario_rol usuario_rol_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_pkey PRIMARY KEY (id);


--
-- Name: usuario_sesion usuario_sesion_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_sesion
    ADD CONSTRAINT usuario_sesion_pkey PRIMARY KEY (id);


--
-- Name: usuario_sesion usuario_sesion_session_id_unique; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_sesion
    ADD CONSTRAINT usuario_sesion_session_id_unique UNIQUE (session_id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: idx_acceso_bitacora__created_at; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_acceso_bitacora__created_at ON public.acceso_bitacora USING btree (created_at);


--
-- Name: idx_acceso_bitacora__evento; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_acceso_bitacora__evento ON public.acceso_bitacora USING btree (evento);


--
-- Name: idx_acceso_bitacora__exito; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_acceso_bitacora__exito ON public.acceso_bitacora USING btree (exito);


--
-- Name: idx_acceso_bitacora__sesion_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_acceso_bitacora__sesion_id ON public.acceso_bitacora USING btree (sesion_id);


--
-- Name: idx_acceso_bitacora__usuario_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_acceso_bitacora__usuario_id ON public.acceso_bitacora USING btree (usuario_id);


--
-- Name: idx_actividad_bitacora__accion; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_actividad_bitacora__accion ON public.actividad_bitacora USING btree (accion);


--
-- Name: idx_actividad_bitacora__created_at; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_actividad_bitacora__created_at ON public.actividad_bitacora USING btree (created_at);


--
-- Name: idx_actividad_bitacora__modulo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_actividad_bitacora__modulo ON public.actividad_bitacora USING btree (modulo);


--
-- Name: idx_actividad_bitacora__sesion_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_actividad_bitacora__sesion_id ON public.actividad_bitacora USING btree (sesion_id);


--
-- Name: idx_actividad_bitacora__target_tabla; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_actividad_bitacora__target_tabla ON public.actividad_bitacora USING btree (target_tabla);


--
-- Name: idx_actividad_bitacora__usuario_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_actividad_bitacora__usuario_id ON public.actividad_bitacora USING btree (usuario_id);


--
-- Name: idx_archivo__carpeta_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_archivo__carpeta_id ON public.archivo USING btree (carpeta_id);


--
-- Name: idx_archivo__created_at; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_archivo__created_at ON public.archivo USING btree (created_at);


--
-- Name: idx_archivo__extension; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_archivo__extension ON public.archivo USING btree (extension);


--
-- Name: idx_archivo__usuario_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_archivo__usuario_id ON public.archivo USING btree (usuario_id);


--
-- Name: idx_archivo__visto_admin; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_archivo__visto_admin ON public.archivo USING btree (visto_admin);


--
-- Name: idx_archivo_carpeta__padre_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_archivo_carpeta__padre_id ON public.archivo_carpeta USING btree (padre_id);


--
-- Name: idx_archivo_carpeta__usuario_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_archivo_carpeta__usuario_id ON public.archivo_carpeta USING btree (usuario_id);


--
-- Name: idx_pedido__created_at; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_pedido__created_at ON public.pedido USING btree (created_at);


--
-- Name: idx_pedido__estado; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_pedido__estado ON public.pedido USING btree (estado);


--
-- Name: idx_pedido__modulo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_pedido__modulo ON public.pedido USING btree (modulo);


--
-- Name: idx_pedido__usuario_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_pedido__usuario_id ON public.pedido USING btree (usuario_id);


--
-- Name: idx_permiso__activo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_permiso__activo ON public.permiso USING btree (activo);


--
-- Name: idx_producto__activo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_producto__activo ON public.producto USING btree (activo);


--
-- Name: idx_producto__modulo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_producto__modulo ON public.producto USING btree (modulo);


--
-- Name: idx_rol__activo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_rol__activo ON public.rol USING btree (activo);


--
-- Name: idx_rol_permiso__permiso_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_rol_permiso__permiso_id ON public.rol_permiso USING btree (permiso_id);


--
-- Name: idx_rol_permiso__rol_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_rol_permiso__rol_id ON public.rol_permiso USING btree (rol_id);


--
-- Name: idx_saldo_movimiento__created_at; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_saldo_movimiento__created_at ON public.saldo_movimiento USING btree (created_at);


--
-- Name: idx_saldo_movimiento__modulo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_saldo_movimiento__modulo ON public.saldo_movimiento USING btree (modulo);


--
-- Name: idx_saldo_movimiento__tipo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_saldo_movimiento__tipo ON public.saldo_movimiento USING btree (tipo);


--
-- Name: idx_saldo_movimiento__usuario_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_saldo_movimiento__usuario_id ON public.saldo_movimiento USING btree (usuario_id);


--
-- Name: idx_usuario__bloqueado; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario__bloqueado ON public.usuario USING btree (bloqueado);


--
-- Name: idx_usuario__deleted_at; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario__deleted_at ON public.usuario USING btree (deleted_at);


--
-- Name: idx_usuario__email; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario__email ON public.usuario USING btree (email);


--
-- Name: idx_usuario__email_verificado; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario__email_verificado ON public.usuario USING btree (email_verificado);


--
-- Name: idx_usuario__modulo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario__modulo ON public.usuario USING btree (modulo);


--
-- Name: idx_usuario_password_reset__expira_at; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario_password_reset__expira_at ON public.usuario_password_reset USING btree (expira_at);


--
-- Name: idx_usuario_password_reset__usuario_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario_password_reset__usuario_id ON public.usuario_password_reset USING btree (usuario_id);


--
-- Name: idx_usuario_perfil__usuario_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario_perfil__usuario_id ON public.usuario_perfil USING btree (usuario_id);


--
-- Name: idx_usuario_rol__rol_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario_rol__rol_id ON public.usuario_rol USING btree (rol_id);


--
-- Name: idx_usuario_rol__usuario_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario_rol__usuario_id ON public.usuario_rol USING btree (usuario_id);


--
-- Name: idx_usuario_sesion__activa; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario_sesion__activa ON public.usuario_sesion USING btree (activa);


--
-- Name: idx_usuario_sesion__session_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario_sesion__session_id ON public.usuario_sesion USING btree (session_id);


--
-- Name: idx_usuario_sesion__usuario_id; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_usuario_sesion__usuario_id ON public.usuario_sesion USING btree (usuario_id);


--
-- Name: acceso_bitacora trg_acceso_bitacora__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_acceso_bitacora__set_updated_at BEFORE UPDATE ON public.acceso_bitacora FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: actividad_bitacora trg_actividad_bitacora__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_actividad_bitacora__set_updated_at BEFORE UPDATE ON public.actividad_bitacora FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: archivo trg_archivo__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_archivo__set_updated_at BEFORE UPDATE ON public.archivo FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: archivo_carpeta trg_archivo_carpeta__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_archivo_carpeta__set_updated_at BEFORE UPDATE ON public.archivo_carpeta FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: pedido trg_pedido__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_pedido__set_updated_at BEFORE UPDATE ON public.pedido FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: permiso trg_permiso__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_permiso__set_updated_at BEFORE UPDATE ON public.permiso FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: producto trg_producto__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_producto__set_updated_at BEFORE UPDATE ON public.producto FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: rol trg_rol__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_rol__set_updated_at BEFORE UPDATE ON public.rol FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: rol_permiso trg_rol_permiso__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_rol_permiso__set_updated_at BEFORE UPDATE ON public.rol_permiso FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: saldo_monedero trg_saldo_monedero__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_saldo_monedero__set_updated_at BEFORE UPDATE ON public.saldo_monedero FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: saldo_movimiento trg_saldo_movimiento__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_saldo_movimiento__set_updated_at BEFORE UPDATE ON public.saldo_movimiento FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario trg_usuario__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_usuario__set_updated_at BEFORE UPDATE ON public.usuario FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_password_reset trg_usuario_password_reset__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_usuario_password_reset__set_updated_at BEFORE UPDATE ON public.usuario_password_reset FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_perfil trg_usuario_perfil__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_usuario_perfil__set_updated_at BEFORE UPDATE ON public.usuario_perfil FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_rol trg_usuario_rol__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_usuario_rol__set_updated_at BEFORE UPDATE ON public.usuario_rol FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_sesion trg_usuario_sesion__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_usuario_sesion__set_updated_at BEFORE UPDATE ON public.usuario_sesion FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: acceso_bitacora acceso_bitacora_sesion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.acceso_bitacora
    ADD CONSTRAINT acceso_bitacora_sesion_id_foreign FOREIGN KEY (sesion_id) REFERENCES public.usuario_sesion(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: acceso_bitacora acceso_bitacora_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.acceso_bitacora
    ADD CONSTRAINT acceso_bitacora_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: actividad_bitacora actividad_bitacora_sesion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.actividad_bitacora
    ADD CONSTRAINT actividad_bitacora_sesion_id_foreign FOREIGN KEY (sesion_id) REFERENCES public.usuario_sesion(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: actividad_bitacora actividad_bitacora_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.actividad_bitacora
    ADD CONSTRAINT actividad_bitacora_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: archivo archivo_carpeta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.archivo
    ADD CONSTRAINT archivo_carpeta_id_foreign FOREIGN KEY (carpeta_id) REFERENCES public.archivo_carpeta(id) ON DELETE SET NULL;


--
-- Name: archivo_carpeta archivo_carpeta_padre_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.archivo_carpeta
    ADD CONSTRAINT archivo_carpeta_padre_id_foreign FOREIGN KEY (padre_id) REFERENCES public.archivo_carpeta(id) ON DELETE SET NULL;


--
-- Name: archivo_carpeta archivo_carpeta_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.archivo_carpeta
    ADD CONSTRAINT archivo_carpeta_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: archivo archivo_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.archivo
    ADD CONSTRAINT archivo_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: archivo archivo_visto_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.archivo
    ADD CONSTRAINT archivo_visto_por_foreign FOREIGN KEY (visto_por) REFERENCES public.usuario(id) ON DELETE SET NULL;


--
-- Name: pedido pedido_operador_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_operador_usuario_id_foreign FOREIGN KEY (operador_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: pedido pedido_repartidor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_repartidor_id_foreign FOREIGN KEY (repartidor_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: pedido pedido_saldo_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_saldo_movimiento_id_foreign FOREIGN KEY (saldo_movimiento_id) REFERENCES public.saldo_movimiento(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: pedido pedido_tarjeta_lectura_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_tarjeta_lectura_id_foreign FOREIGN KEY (tarjeta_lectura_id) REFERENCES public.tarjeta_lectura(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: pedido pedido_tienda_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_tienda_id_foreign FOREIGN KEY (tienda_id) REFERENCES public.tienda(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: pedido pedido_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: producto producto_tienda_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_tienda_id_foreign FOREIGN KEY (tienda_id) REFERENCES public.tienda(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: rol_permiso rol_permiso_permiso_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_permiso_id_foreign FOREIGN KEY (permiso_id) REFERENCES public.permiso(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: rol_permiso rol_permiso_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.rol(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: saldo_monedero saldo_monedero_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.saldo_monedero
    ADD CONSTRAINT saldo_monedero_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: saldo_movimiento saldo_movimiento_operador_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.saldo_movimiento
    ADD CONSTRAINT saldo_movimiento_operador_usuario_id_foreign FOREIGN KEY (operador_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: saldo_movimiento saldo_movimiento_saldo_monedero_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.saldo_movimiento
    ADD CONSTRAINT saldo_movimiento_saldo_monedero_id_foreign FOREIGN KEY (saldo_monedero_id) REFERENCES public.saldo_monedero(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: saldo_movimiento saldo_movimiento_tarjeta_lectura_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.saldo_movimiento
    ADD CONSTRAINT saldo_movimiento_tarjeta_lectura_id_foreign FOREIGN KEY (tarjeta_lectura_id) REFERENCES public.tarjeta_lectura(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: saldo_movimiento saldo_movimiento_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.saldo_movimiento
    ADD CONSTRAINT saldo_movimiento_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tarjeta_lectura tarjeta_lectura_operador_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_lectura
    ADD CONSTRAINT tarjeta_lectura_operador_usuario_id_foreign FOREIGN KEY (operador_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tarjeta_lectura tarjeta_lectura_pedido_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_lectura
    ADD CONSTRAINT tarjeta_lectura_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedido(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tarjeta_lectura tarjeta_lectura_tarjeta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_lectura
    ADD CONSTRAINT tarjeta_lectura_tarjeta_id_foreign FOREIGN KEY (tarjeta_id) REFERENCES public.tarjeta_universitaria(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tarjeta_universitaria tarjeta_universitaria_bloqueado_por_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_universitaria
    ADD CONSTRAINT tarjeta_universitaria_bloqueado_por_usuario_id_foreign FOREIGN KEY (bloqueado_por_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tarjeta_universitaria tarjeta_universitaria_registrado_por_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_universitaria
    ADD CONSTRAINT tarjeta_universitaria_registrado_por_usuario_id_foreign FOREIGN KEY (registrado_por_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tarjeta_universitaria tarjeta_universitaria_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_universitaria
    ADD CONSTRAINT tarjeta_universitaria_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_password_reset usuario_password_reset_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_password_reset
    ADD CONSTRAINT usuario_password_reset_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_perfil usuario_perfil_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_perfil
    ADD CONSTRAINT usuario_perfil_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_rol usuario_rol_asignado_por_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_asignado_por_usuario_id_foreign FOREIGN KEY (asignado_por_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: usuario_rol usuario_rol_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.rol(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_rol usuario_rol_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_sesion usuario_sesion_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario_sesion
    ADD CONSTRAINT usuario_sesion_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario usuario_tienda_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_tienda_id_foreign FOREIGN KEY (tienda_id) REFERENCES public.tienda(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict NeUiPly262pgaOMk1ysFpqNkBCaTPKTIv75IlECNcz00jx4ulakT8KBHEQcrw4v

