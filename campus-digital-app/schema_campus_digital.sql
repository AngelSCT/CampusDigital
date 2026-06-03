--
-- PostgreSQL database dump
--

\restrict PnFPtgZnRwvfpCPvirQ2w20FMB9xnr4sHd11A4B0EkVGViRbjGlrbIuUQVUuJNW

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
    fecha_creacion timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT ck_catalogo__tipo CHECK (((tipo)::text = ANY ((ARRAY['servicio'::character varying, 'producto'::character varying])::text[])))
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
-- Name: impuestos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.impuestos (
    id_impuesto integer NOT NULL,
    nombre character varying(50) NOT NULL,
    porcentaje numeric(5,2) NOT NULL,
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
    carrito_uuid character varying(100),
    CONSTRAINT ck_pedido__estado CHECK (((estado)::text = ANY ((ARRAY['creado'::character varying, 'aceptado'::character varying, 'en_proceso'::character varying, 'listo'::character varying, 'entregado'::character varying, 'cancelado'::character varying])::text[])))
);


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
-- Name: precios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.precios (
    id_precio integer NOT NULL,
    id_catalogo integer NOT NULL,
    precio numeric(10,2) NOT NULL,
    fecha_inicio date NOT NULL,
    fecha_fin date,
    CONSTRAINT ck_precios__fechas_coherentes CHECK (((fecha_fin IS NULL) OR (fecha_inicio <= fecha_fin))),
    CONSTRAINT ck_precios__precio_positivo CHECK ((precio >= (0)::numeric))
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
    CONSTRAINT ck_saldo_monedero__saldo_no_negativo CHECK (((saldo_disponible >= (0)::numeric) AND (saldo_retenido >= (0)::numeric)))
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
-- Name: acceso_bitacora id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.acceso_bitacora ALTER COLUMN id SET DEFAULT nextval('public.acceso_bitacora_id_seq'::regclass);


--
-- Name: actividad_bitacora id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actividad_bitacora ALTER COLUMN id SET DEFAULT nextval('public.actividad_bitacora_id_seq'::regclass);


--
-- Name: catalogo id_catalogo; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo ALTER COLUMN id_catalogo SET DEFAULT nextval('public.catalogo_id_catalogo_seq'::regclass);


--
-- Name: categorias id_categoria; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias ALTER COLUMN id_categoria SET DEFAULT nextval('public.categorias_id_categoria_seq'::regclass);


--
-- Name: impuestos id_impuesto; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.impuestos ALTER COLUMN id_impuesto SET DEFAULT nextval('public.impuestos_id_impuesto_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: pedido id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido ALTER COLUMN id SET DEFAULT nextval('public.pedido_id_seq'::regclass);


--
-- Name: pedido_historial id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_historial ALTER COLUMN id SET DEFAULT nextval('public.pedido_historial_id_seq'::regclass);


--
-- Name: pedido_item id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido_item ALTER COLUMN id SET DEFAULT nextval('public.pedido_item_id_seq'::regclass);


--
-- Name: permiso id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permiso ALTER COLUMN id SET DEFAULT nextval('public.permiso_id_seq'::regclass);


--
-- Name: precios id_precio; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.precios ALTER COLUMN id_precio SET DEFAULT nextval('public.precios_id_precio_seq'::regclass);


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
-- Name: tarjeta_lectura id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_lectura ALTER COLUMN id SET DEFAULT nextval('public.tarjeta_lectura_id_seq'::regclass);


--
-- Name: tarjeta_universitaria id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tarjeta_universitaria ALTER COLUMN id SET DEFAULT nextval('public.tarjeta_universitaria_id_seq'::regclass);


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
-- Name: catalogo catalogo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo
    ADD CONSTRAINT catalogo_pkey PRIMARY KEY (id_catalogo);


--
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id_categoria);


--
-- Name: impuestos impuestos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.impuestos
    ADD CONSTRAINT impuestos_pkey PRIMARY KEY (id_impuesto);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


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
-- Name: precios precios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.precios
    ADD CONSTRAINT precios_pkey PRIMARY KEY (id_precio);


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
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


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
-- Name: idx_catalogo__activo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_catalogo__activo ON public.catalogo USING btree (activo);


--
-- Name: idx_catalogo__categoria; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_catalogo__categoria ON public.catalogo USING btree (id_categoria);


--
-- Name: idx_catalogo__tipo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_catalogo__tipo ON public.catalogo USING btree (tipo);


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
-- Name: idx_precios__fechas; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_precios__fechas ON public.precios USING btree (id_catalogo, fecha_inicio, fecha_fin);


--
-- Name: idx_precios__id_catalogo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_precios__id_catalogo ON public.precios USING btree (id_catalogo);


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
-- Name: pedido_item_pedido_id_producto_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pedido_item_pedido_id_producto_id_index ON public.pedido_item USING btree (pedido_id, producto_id);


--
-- Name: pedido_item_producto_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pedido_item_producto_id_index ON public.pedido_item USING btree (producto_id);


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
-- Name: pedido trg_pedido__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_pedido__set_updated_at BEFORE UPDATE ON public.pedido FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: permiso trg_permiso__set_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_permiso__set_updated_at BEFORE UPDATE ON public.permiso FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


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
-- Name: catalogo catalogo_id_categoria_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo
    ADD CONSTRAINT catalogo_id_categoria_foreign FOREIGN KEY (id_categoria) REFERENCES public.categorias(id_categoria) ON DELETE SET NULL;


--
-- Name: catalogo catalogo_id_impuesto_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.catalogo
    ADD CONSTRAINT catalogo_id_impuesto_foreign FOREIGN KEY (id_impuesto) REFERENCES public.impuestos(id_impuesto) ON DELETE SET NULL;


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
-- Name: pedido pedido_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: precios precios_id_catalogo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.precios
    ADD CONSTRAINT precios_id_catalogo_foreign FOREIGN KEY (id_catalogo) REFERENCES public.catalogo(id_catalogo) ON DELETE CASCADE;


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
-- PostgreSQL database dump complete
--

\unrestrict PnFPtgZnRwvfpCPvirQ2w20FMB9xnr4sHd11A4B0EkVGViRbjGlrbIuUQVUuJNW

