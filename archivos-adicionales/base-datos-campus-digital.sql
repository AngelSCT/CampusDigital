--
-- PostgreSQL database dump
--

\restrict K5DZ6EKztpghK93u3LjcAhWjWLV2YF7WlJ6Hbbcox5DgdmUZ26th7LgUqKG8WMK

-- Dumped from database version 16.11
-- Dumped by pg_dump version 16.11

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
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
    deleted_at timestamp(0) without time zone
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
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: permiso id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.permiso ALTER COLUMN id SET DEFAULT nextval('public.permiso_id_seq'::regclass);


--
-- Name: rol id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.rol ALTER COLUMN id SET DEFAULT nextval('public.rol_id_seq'::regclass);


--
-- Name: rol_permiso id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.rol_permiso ALTER COLUMN id SET DEFAULT nextval('public.rol_permiso_id_seq'::regclass);


--
-- Name: tarjeta_lectura id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_lectura ALTER COLUMN id SET DEFAULT nextval('public.tarjeta_lectura_id_seq'::regclass);


--
-- Name: tarjeta_universitaria id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_universitaria ALTER COLUMN id SET DEFAULT nextval('public.tarjeta_universitaria_id_seq'::regclass);


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
1	\N	\N	admin@campusdigital.com	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:05:13+00	2026-03-04 20:05:13+00	\N
2	\N	\N	admin@campusdigital.com	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:05:13+00	2026-03-04 20:05:13+00	\N
3	\N	\N	admin@campusdigital.com	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:05:17+00	2026-03-04 20:05:17+00	\N
4	\N	\N	admin@campusdigital.com	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:05:17+00	2026-03-04 20:05:17+00	\N
5	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:17:20+00	2026-03-04 20:17:20+00	\N
6	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:17:20+00	2026-03-04 20:17:20+00	\N
7	1	1	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:17:21+00	2026-03-04 20:17:21+00	\N
8	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:17:21+00	2026-03-04 20:17:21+00	\N
9	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:38:38+00	2026-03-04 20:38:38+00	\N
10	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:38:38+00	2026-03-04 20:38:38+00	\N
11	3	2	estudiante@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:38:45+00	2026-03-04 20:38:45+00	\N
12	3	\N	estudiante@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:38:45+00	2026-03-04 20:38:45+00	\N
13	3	\N	estudiante@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:39:14+00	2026-03-04 20:39:14+00	\N
14	3	\N	estudiante@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:39:14+00	2026-03-04 20:39:14+00	\N
15	1	3	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:39:18+00	2026-03-04 20:39:18+00	\N
16	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:39:18+00	2026-03-04 20:39:18+00	\N
17	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:42:32+00	2026-03-04 20:42:32+00	\N
18	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:42:32+00	2026-03-04 20:42:32+00	\N
19	1	4	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:44:55+00	2026-03-04 20:44:55+00	\N
20	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 20:44:55+00	2026-03-04 20:44:55+00	\N
21	1	5	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 23:55:46+00	2026-03-04 23:55:46+00	\N
22	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-04 23:55:46+00	2026-03-04 23:55:46+00	\N
23	1	6	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 20:38:20+00	2026-03-06 20:38:20+00	\N
24	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 20:38:20+00	2026-03-06 20:38:20+00	\N
25	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 20:38:23+00	2026-03-06 20:38:23+00	\N
26	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 20:38:23+00	2026-03-06 20:38:23+00	\N
27	1	7	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 20:59:00+00	2026-03-06 20:59:00+00	\N
28	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 20:59:00+00	2026-03-06 20:59:00+00	\N
29	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 20:59:03+00	2026-03-06 20:59:03+00	\N
30	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 20:59:03+00	2026-03-06 20:59:03+00	\N
31	\N	\N		rfid_login_failed	f	UID no registrado: ADMIN@CAMPUSDIGITAL.COM	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:03:17+00	2026-03-06 21:03:17+00	\N
32	1	8	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:03:30+00	2026-03-06 21:03:30+00	\N
33	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:03:30+00	2026-03-06 21:03:30+00	\N
34	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:03:39+00	2026-03-06 21:03:39+00	\N
35	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:03:39+00	2026-03-06 21:03:39+00	\N
36	\N	\N		rfid_login_failed	f	UID no registrado: DCA0 2642	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:03:45+00	2026-03-06 21:03:45+00	\N
37	1	\N	admin@campusdigital.com	rfid_login_failed	f	Tarjeta inactiva	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:03:52+00	2026-03-06 21:03:52+00	\N
38	1	9	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:03:58+00	2026-03-06 21:03:58+00	\N
39	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:03:58+00	2026-03-06 21:03:58+00	\N
40	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:04:27+00	2026-03-06 21:04:27+00	\N
41	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:04:27+00	2026-03-06 21:04:27+00	\N
42	1	\N	admin@campusdigital.com	rfid_login_failed	f	Tarjeta inactiva	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:04:44+00	2026-03-06 21:04:44+00	\N
43	\N	\N		rfid_login_failed	f	UID no registrado: DDE	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:08:00+00	2026-03-06 21:08:00+00	\N
44	1	10	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:08:05+00	2026-03-06 21:08:05+00	\N
45	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:08:05+00	2026-03-06 21:08:05+00	\N
46	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:08:11+00	2026-03-06 21:08:11+00	\N
47	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:08:11+00	2026-03-06 21:08:11+00	\N
48	\N	\N		rfid_login_failed	f	UID no registrado: DCA0 2642	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:08:16+00	2026-03-06 21:08:16+00	\N
49	1	\N	admin@campusdigital.com	rfid_login_failed	f	Tarjeta inactiva	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:08:21+00	2026-03-06 21:08:21+00	\N
50	1	11	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:10:41+00	2026-03-06 21:10:41+00	\N
51	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:10:41+00	2026-03-06 21:10:41+00	\N
52	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:13:15+00	2026-03-06 21:13:15+00	\N
53	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:13:15+00	2026-03-06 21:13:15+00	\N
54	1	\N	admin@campusdigital.com	rfid_login_failed	f	Tarjeta inactiva	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:13:19+00	2026-03-06 21:13:19+00	\N
55	1	\N	admin@campusdigital.com	rfid_login_failed	f	Tarjeta inactiva	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:13:19+00	2026-03-06 21:13:19+00	\N
56	\N	\N		rfid_login_failed	f	UID no registrado: ADMIN@CAMPUSDIGITAL.COM	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:13+00	2026-03-06 21:16:13+00	\N
57	1	12	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:26+00	2026-03-06 21:16:26+00	\N
58	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:26+00	2026-03-06 21:16:26+00	\N
59	1	\N	admin@campusdigital.com	rfid_login_success	t	Login exitoso por tarjeta RFID/NFC. UID: DCA02642	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:26+00	2026-03-06 21:16:26+00	\N
60	1	13	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:36+00	2026-03-06 21:16:36+00	\N
61	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:36+00	2026-03-06 21:16:36+00	\N
62	1	\N	admin@campusdigital.com	rfid_login_success	t	Login exitoso por tarjeta RFID/NFC. UID: DCA02642	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:36+00	2026-03-06 21:16:36+00	\N
63	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:42+00	2026-03-06 21:16:42+00	\N
64	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:42+00	2026-03-06 21:16:42+00	\N
65	1	14	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:46+00	2026-03-06 21:16:46+00	\N
66	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:46+00	2026-03-06 21:16:46+00	\N
67	1	\N	admin@campusdigital.com	rfid_login_success	t	Login exitoso por tarjeta RFID/NFC. UID: DCA02642	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:46+00	2026-03-06 21:16:46+00	\N
68	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:50+00	2026-03-06 21:16:50+00	\N
69	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:50+00	2026-03-06 21:16:50+00	\N
70	\N	\N		rfid_login_failed	f	UID no registrado: DCA02642F	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:16:55+00	2026-03-06 21:16:55+00	\N
71	1	15	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:17:00+00	2026-03-06 21:17:00+00	\N
72	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:17:00+00	2026-03-06 21:17:00+00	\N
73	1	\N	admin@campusdigital.com	rfid_login_success	t	Login exitoso por tarjeta RFID/NFC. UID: DCA02642	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:17:00+00	2026-03-06 21:17:00+00	\N
74	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:17:02+00	2026-03-06 21:17:02+00	\N
75	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:17:02+00	2026-03-06 21:17:02+00	\N
76	1	16	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:33:50+00	2026-03-06 21:33:50+00	\N
77	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:33:50+00	2026-03-06 21:33:50+00	\N
78	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:33:58+00	2026-03-06 21:33:58+00	\N
79	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:33:58+00	2026-03-06 21:33:58+00	\N
80	\N	\N		rfid_login_failed	f	UID no registrado: 33335	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:42:09+00	2026-03-06 21:42:09+00	\N
81	1	17	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:42:25+00	2026-03-06 21:42:25+00	\N
82	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 21:42:25+00	2026-03-06 21:42:25+00	\N
83	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:09:08+00	2026-03-06 22:09:08+00	\N
84	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:09:08+00	2026-03-06 22:09:08+00	\N
85	1	18	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:09:21+00	2026-03-06 22:09:21+00	\N
86	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:09:21+00	2026-03-06 22:09:21+00	\N
87	1	\N	admin@campusdigital.com	rfid_login_success	t	Login exitoso por tarjeta RFID/NFC con PIN. UID: DCA02642	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:09:21+00	2026-03-06 22:09:21+00	\N
88	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:09:26+00	2026-03-06 22:09:26+00	\N
89	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:09:26+00	2026-03-06 22:09:26+00	\N
90	1	19	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:09:32+00	2026-03-06 22:09:32+00	\N
91	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:09:32+00	2026-03-06 22:09:32+00	\N
92	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:14:33+00	2026-03-06 22:14:33+00	\N
93	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:14:33+00	2026-03-06 22:14:33+00	\N
94	3	20	estudiante@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:14:45+00	2026-03-06 22:14:45+00	\N
95	3	\N	estudiante@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:14:45+00	2026-03-06 22:14:45+00	\N
96	3	\N	estudiante@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:15:00+00	2026-03-06 22:15:00+00	\N
97	3	\N	estudiante@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:15:00+00	2026-03-06 22:15:00+00	\N
98	1	21	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:15:04+00	2026-03-06 22:15:04+00	\N
99	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-06 22:15:04+00	2026-03-06 22:15:04+00	\N
100	1	22	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:22:16+00	2026-03-09 18:22:16+00	\N
101	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:22:16+00	2026-03-09 18:22:16+00	\N
102	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:23:08+00	2026-03-09 18:23:08+00	\N
103	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:23:08+00	2026-03-09 18:23:08+00	\N
104	2	23	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:23:12+00	2026-03-09 18:23:12+00	\N
105	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:23:12+00	2026-03-09 18:23:12+00	\N
106	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:23:32+00	2026-03-09 18:23:32+00	\N
107	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:23:32+00	2026-03-09 18:23:32+00	\N
108	2	24	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:23:43+00	2026-03-09 18:23:43+00	\N
109	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:23:43+00	2026-03-09 18:23:43+00	\N
110	2	\N	proveedor@campusdigital.com	rfid_login_success	t	Login exitoso por tarjeta RFID/NFC con PIN. UID: D1B0FB13	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:23:43+00	2026-03-09 18:23:43+00	\N
111	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:24:01+00	2026-03-09 18:24:01+00	\N
112	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:24:01+00	2026-03-09 18:24:01+00	\N
113	\N	\N	admin@campusdigital.com	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:24:03+00	2026-03-09 18:24:03+00	\N
114	\N	\N	admin@campusdigital.com	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:24:03+00	2026-03-09 18:24:03+00	\N
115	1	25	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:24:12+00	2026-03-09 18:24:12+00	\N
116	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	{}	2026-03-09 18:24:12+00	2026-03-09 18:24:12+00	\N
\.


--
-- Data for Name: actividad_bitacora; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.actividad_bitacora (id, usuario_id, sesion_id, accion, modulo, target_tabla, target_id, exito, detalle, ip, user_agent, meta_json, created_at, updated_at, deleted_at) FROM stdin;
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
18	2026_03_05_000001_add_pin_hash_to_tarjeta_universitaria	2
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: permiso; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.permiso (id, clave, descripcion, activo, created_at, updated_at, deleted_at) FROM stdin;
1	user.read	Consultar usuarios	t	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
2	user.write	Crear/editar usuarios	t	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
3	role.read	Consultar roles	t	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
4	role.write	Administrar roles	t	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
5	permission.read	Consultar permisos	t	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
6	permission.write	Administrar permisos	t	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
7	audit.read	Consultar bitácoras	t	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
\.


--
-- Data for Name: rol; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.rol (id, nombre, descripcion, activo, created_at, updated_at, deleted_at) FROM stdin;
1	estudiante	Usuario final que consume servicios digitales	t	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
2	proveedor_area	Proveedor o área interna que atiende solicitudes	t	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
3	administrador	Admin con acceso total	t	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
4	test	Rol de moderacion	t	2026-03-04 23:50:32+00	2026-03-04 23:51:33+00	\N
\.


--
-- Data for Name: rol_permiso; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.rol_permiso (id, rol_id, permiso_id, created_at, updated_at, deleted_at) FROM stdin;
1	3	1	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
2	3	2	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
3	3	3	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
4	3	4	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
5	3	5	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
6	3	6	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
7	3	7	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
8	2	1	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
9	2	7	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
10	1	1	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
\.


--
-- Data for Name: tarjeta_lectura; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.tarjeta_lectura (id, tarjeta_id, uid_leido, modulo, tipo_lectura, exito, detalle, ip, user_agent, operador_usuario_id, meta_json, created_at, updated_at, deleted_at) FROM stdin;
1	1	DCA02642	copias	acceso	t	Lectura exitosa en módulo: copias. Tipo: acceso.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-04 20:24:29	2026-03-04 20:24:29	\N
2	\N	DCA02645	cafeteria	acceso	f	Tarjeta no registrada en el sistema.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-04 20:25:24	2026-03-04 20:25:24	\N
3	\N	F9BC5D26	cafeteria	acceso	f	Tarjeta no registrada en el sistema.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-04 20:29:02	2026-03-04 20:29:02	\N
4	\N	0FADA898	copias	acceso	f	Tarjeta no registrada en el sistema.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-04 20:29:14	2026-03-04 20:29:14	\N
5	\N	B57277F6	copias	acceso	f	Tarjeta no registrada en el sistema.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-04 23:55:53	2026-03-04 23:55:53	\N
6	\N	19041887	copias	acceso	f	Tarjeta no registrada en el sistema.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-04 23:57:28	2026-03-04 23:57:28	\N
7	\N	DCA0 2642	copias	acceso	f	Tarjeta no registrada en el sistema.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-06 21:43:16	2026-03-06 21:43:16	\N
8	1	DCA02642	copias	acceso	t	Lectura exitosa en módulo: copias. Tipo: acceso.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-06 21:43:23	2026-03-06 21:43:23	\N
9	1	DCA02642	cafeteria	acceso	t	Lectura exitosa en módulo: cafeteria. Tipo: acceso.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-06 22:09:42	2026-03-06 22:09:42	\N
10	\N	34005A10	cafeteria	acceso	f	Tarjeta no registrada en el sistema.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-06 22:15:30	2026-03-06 22:15:30	\N
11	3	D1B0FB13	copias	acceso	t	Lectura exitosa en módulo: copias. Tipo: acceso.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2	[]	2026-03-09 18:23:28	2026-03-09 18:23:28	\N
12	3	D1B0FB13	otro	acceso	f	Tarjeta bloqueada. Motivo: a	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-09 18:26:27	2026-03-09 18:26:27	\N
13	3	D1B0FB13	otro	acceso	t	Lectura exitosa en módulo: otro. Tipo: acceso.	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	1	[]	2026-03-09 18:26:39	2026-03-09 18:26:39	\N
\.


--
-- Data for Name: tarjeta_universitaria; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.tarjeta_universitaria (id, usuario_id, uid, estado, motivo_bloqueo, registrado_por_usuario_id, bloqueado_por_usuario_id, bloqueado_at, meta_json, created_at, updated_at, deleted_at, pin_hash) FROM stdin;
2	3	E61B92B0	activa	\N	1	\N	\N	{}	2026-03-06 22:01:39	2026-03-06 22:01:39	\N	$2y$12$IFe0Vo8QaOsjS7MFgxSqgOO0BBm9J5kfNtH0uSgroe0fYubCq8Ot.
1	1	DCA02642	activa	\N	1	\N	\N	{}	2026-03-04 20:23:50	2026-03-06 22:09:01	\N	$2y$12$R.ZZpoxpoIYqdXGukcBNYeuf/P4DLmqnNnLmfhlnHHdnpZPnzmZQ6
3	2	D1B0FB13	activa	\N	1	\N	\N	{}	2026-03-09 18:22:49	2026-03-09 18:26:33	\N	$2y$12$qXJcZyGz9P9gHR0mAoIJLedO2.pSNy9248sR4PWYWS6KmUQLuOQtS
\.


--
-- Data for Name: usuario; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.usuario (id, nombre, apellido, telefono, foto_url, password_hash, email_verificado, ultimo_login_at, bloqueado, bloqueado_hasta, seguridad_json, created_at, updated_at, deleted_at, email, remember_token) FROM stdin;
1	Admin	Sistema	1234567890		$2y$12$yobK1xchCGy6twBN4VFN5us/LNFB6bTkAEaoPf1ianP2Hf0wIdTti	t	\N	f	\N	{}	2026-03-04 20:05:41+00	2026-03-04 20:05:41+00	\N	admin@campusdigital.com	\N
2	Proveedor	Cafetería	0987654321		$2y$12$uDuBf8Iyr.IJ1APUUaHDWuGpyIsBh50JSDa6RPMALXrFuR4Fxrbbm	t	\N	f	\N	{}	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N	proveedor@campusdigital.com	\N
3	Juan	Pérez	5555555555		$2y$12$1Tj8nt2Qs6pbyY8HRAZjreiXryHEK8EBn.FjurhfH2/3RMoSoEjt6	t	\N	f	\N	{}	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N	estudiante@campusdigital.com	\N
4	MARTIN	Lopez	5551234567		$2y$12$Y51nU2hbmMgIg3LydHVBauVIj9/HyjNLPxq6ZHEczIKwjETsGvsye	t	\N	f	\N	{}	2026-03-04 23:46:50+00	2026-03-04 23:47:55+00	\N	carlos@test.com	\N
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
1	1	\N			{}	2026-03-04 20:05:41+00	2026-03-04 20:05:41+00	\N
2	2	\N			{}	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
3	3	2000-01-15	masculino		{}	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
4	4	\N			{}	2026-03-04 23:46:50+00	2026-03-04 23:46:50+00	\N
\.


--
-- Data for Name: usuario_rol; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.usuario_rol (id, usuario_id, rol_id, asignado_por_usuario_id, asignado_at, created_at, updated_at, deleted_at) FROM stdin;
1	1	3	\N	2026-03-04 20:05:42+00	2026-03-04 20:05:41+00	2026-03-04 20:05:41+00	\N
2	2	2	\N	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
3	3	1	\N	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	2026-03-04 20:05:42+00	\N
\.


--
-- Data for Name: usuario_sesion; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.usuario_sesion (id, usuario_id, session_id, ip, user_agent, inicia_at, expira_at, termina_at, activa, meta_json, created_at, updated_at, deleted_at) FROM stdin;
1	1	Mpax1vug9Y4A3trqlK9T8s5mZ9wBPsUdqzrwUMZw	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-04 20:17:21+00	2026-03-04 22:17:21+00	\N	t	{}	2026-03-04 20:17:21+00	2026-03-04 20:17:21+00	\N
2	3	lIKbLCbyTHjRWIYpCMTTQtpB6mjprSs1YE58dNFs	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-04 20:38:45+00	2026-03-04 22:38:45+00	\N	t	{}	2026-03-04 20:38:45+00	2026-03-04 20:38:45+00	\N
3	1	VQ87kAH6JsE7WT0xEJuhSopEihiooL0aGiviv6Xx	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-04 20:39:18+00	2026-03-04 22:39:18+00	\N	t	{}	2026-03-04 20:39:18+00	2026-03-04 20:39:18+00	\N
4	1	XA9oTadNeZbkbAN2KeKNdmTeFHYHnThgfSF04k65	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-04 20:44:55+00	2026-03-04 22:44:55+00	\N	t	{}	2026-03-04 20:44:55+00	2026-03-04 20:44:55+00	\N
5	1	g5rmHapLo6weFGQjsbt6VwjPU2tk8PRtu750vkqA	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-04 23:55:46+00	2026-03-05 01:55:46+00	\N	t	{}	2026-03-04 23:55:46+00	2026-03-04 23:55:46+00	\N
6	1	2x7z1QQ0x5bZq0FCdeCExozFPRiJ8pMgV7lMb6fJ	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 20:38:20+00	2026-03-06 22:38:20+00	\N	t	{}	2026-03-06 20:38:20+00	2026-03-06 20:38:20+00	\N
7	1	ATFwMP1NsAsTa6jgAr7595o2bRRenguWKAWINcrB	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 20:59:00+00	2026-03-06 22:59:00+00	\N	t	{}	2026-03-06 20:59:00+00	2026-03-06 20:59:00+00	\N
8	1	IZSsplQREeAMpSnV6jt7RRTVhcob6deEDeqTEzS2	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 21:03:30+00	2026-03-06 23:03:30+00	\N	t	{}	2026-03-06 21:03:30+00	2026-03-06 21:03:30+00	\N
9	1	UcxVvaHoSLxdFqiSVbyclpqJ2yk3VN6CzWa1kP1H	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 21:03:58+00	2026-03-06 23:03:58+00	\N	t	{}	2026-03-06 21:03:58+00	2026-03-06 21:03:58+00	\N
10	1	pmOkuqhCkVtvUZdtkhRQPaFkTtb90UmNWN71j9c9	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 21:08:05+00	2026-03-06 23:08:05+00	\N	t	{}	2026-03-06 21:08:05+00	2026-03-06 21:08:05+00	\N
11	1	yveqCSEeTu5HqEXTH0UvqDivMwLOodbpM5o4UD4n	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 21:10:41+00	2026-03-06 23:10:41+00	\N	t	{}	2026-03-06 21:10:41+00	2026-03-06 21:10:41+00	\N
12	1	lykGOWRY7KWBMfdiXJFknXrQAhvxu4TQfHhuqHHv	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 21:16:26+00	2026-03-06 23:16:26+00	\N	t	{}	2026-03-06 21:16:26+00	2026-03-06 21:16:26+00	\N
13	1	jkx6qKIvgQarvkgY7dUCTiKcFVtK2LaAy2i9CWQu	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 21:16:36+00	2026-03-06 23:16:36+00	\N	t	{}	2026-03-06 21:16:36+00	2026-03-06 21:16:36+00	\N
14	1	s2zWQG4pJFkqw5Wh2mg6vZxPAanAlOEUakJmdpRO	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 21:16:46+00	2026-03-06 23:16:46+00	\N	t	{}	2026-03-06 21:16:46+00	2026-03-06 21:16:46+00	\N
15	1	T29fLixD19EXvyE1mQqCmmXFi9f369J1c2aOak05	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 21:17:00+00	2026-03-06 23:17:00+00	\N	t	{}	2026-03-06 21:17:00+00	2026-03-06 21:17:00+00	\N
16	1	jc1KKuAaNMy8Iy3QT5KcqTlRmC8ELXboeTINXsRD	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 21:33:50+00	2026-03-06 23:33:50+00	\N	t	{}	2026-03-06 21:33:50+00	2026-03-06 21:33:50+00	\N
17	1	irlZafSIRGOON4b5Bavi6JZs6uke6RnKIDfjPAlp	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 21:42:25+00	2026-03-06 23:42:25+00	\N	t	{}	2026-03-06 21:42:25+00	2026-03-06 21:42:25+00	\N
18	1	9aLk7Il6Htl3gwd5fssTp6xAqqFk8GpbCji4j2ox	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 22:09:21+00	2026-03-07 00:09:21+00	\N	t	{}	2026-03-06 22:09:21+00	2026-03-06 22:09:21+00	\N
19	1	Axbc58Lh4QB2CSoklXoML5VyYbpUJOZhFHihXk1J	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 22:09:32+00	2026-03-07 00:09:32+00	\N	t	{}	2026-03-06 22:09:32+00	2026-03-06 22:09:32+00	\N
20	3	71O5Z56rgJ7cCsy7bjagZqV0mG4jvR1Xt4APn0FW	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 22:14:45+00	2026-03-07 00:14:45+00	\N	t	{}	2026-03-06 22:14:45+00	2026-03-06 22:14:45+00	\N
21	1	gnfNBKq2WCWW6z5SNRHbvJYHlhNKDrkGI1frAnR4	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-06 22:15:04+00	2026-03-07 00:15:04+00	\N	t	{}	2026-03-06 22:15:04+00	2026-03-06 22:15:04+00	\N
22	1	wk2oXt9PBtwwB2tn5tty7HMZQuaMcbuLjkxsOFpu	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-09 18:22:16+00	2026-03-09 20:22:16+00	\N	t	{}	2026-03-09 18:22:16+00	2026-03-09 18:22:16+00	\N
23	2	5pnZDcHsGrbH3K3PoLoC4mcQ1b3dU95PiGMqpqQZ	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-09 18:23:12+00	2026-03-09 20:23:12+00	\N	t	{}	2026-03-09 18:23:12+00	2026-03-09 18:23:12+00	\N
24	2	ucKdsCcxPe8gNTrUZ05WWeDDVovk6jFPVyuUB1hE	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-09 18:23:43+00	2026-03-09 20:23:43+00	\N	t	{}	2026-03-09 18:23:43+00	2026-03-09 18:23:43+00	\N
25	1	7CtqWIwvgjuZHevqXmtZa6oJiLxIpQU0jf8Ba7au	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0	2026-03-09 18:24:12+00	2026-03-09 20:24:12+00	\N	t	{}	2026-03-09 18:24:12+00	2026-03-09 18:24:12+00	\N
\.


--
-- Name: acceso_bitacora_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.acceso_bitacora_id_seq', 116, true);


--
-- Name: actividad_bitacora_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.actividad_bitacora_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.migrations_id_seq', 18, true);


--
-- Name: permiso_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.permiso_id_seq', 7, true);


--
-- Name: rol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.rol_id_seq', 4, true);


--
-- Name: rol_permiso_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.rol_permiso_id_seq', 10, true);


--
-- Name: tarjeta_lectura_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.tarjeta_lectura_id_seq', 13, true);


--
-- Name: tarjeta_universitaria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.tarjeta_universitaria_id_seq', 3, true);


--
-- Name: usuario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.usuario_id_seq', 4, true);


--
-- Name: usuario_password_reset_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.usuario_password_reset_id_seq', 1, false);


--
-- Name: usuario_perfil_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.usuario_perfil_id_seq', 4, true);


--
-- Name: usuario_rol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.usuario_rol_id_seq', 3, true);


--
-- Name: usuario_sesion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.usuario_sesion_id_seq', 25, true);


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
-- Name: idx_permiso__activo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_permiso__activo ON public.permiso USING btree (activo);


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
-- Name: permiso trg_permiso__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_permiso__set_updated_at BEFORE UPDATE ON public.permiso FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: rol trg_rol__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_rol__set_updated_at BEFORE UPDATE ON public.rol FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: rol_permiso trg_rol_permiso__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_rol_permiso__set_updated_at BEFORE UPDATE ON public.rol_permiso FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


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
-- Name: tarjeta_lectura tarjeta_lectura_operador_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.tarjeta_lectura
    ADD CONSTRAINT tarjeta_lectura_operador_usuario_id_foreign FOREIGN KEY (operador_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


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
-- PostgreSQL database dump complete
--

\unrestrict K5DZ6EKztpghK93u3LjcAhWjWLV2YF7WlJ6Hbbcox5DgdmUZ26th7LgUqKG8WMK

