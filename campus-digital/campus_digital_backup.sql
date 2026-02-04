--
-- PostgreSQL database dump
--

\restrict A8DYFVLP5fjZETZwXCEZo50vVcpfyKhk0kaRe3OZOdNk3dQ9vSYPeMVCxraiYTU

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

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
-- Name: set_updated_at(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.set_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
              NEW.updated_at = CURRENT_TIMESTAMP;
              RETURN NEW;
            END;
            $$;


ALTER FUNCTION public.set_updated_at() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: acceso_bitacora; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.acceso_bitacora OWNER TO postgres;

--
-- Name: acceso_bitacora_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.acceso_bitacora_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.acceso_bitacora_id_seq OWNER TO postgres;

--
-- Name: acceso_bitacora_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.acceso_bitacora_id_seq OWNED BY public.acceso_bitacora.id;


--
-- Name: actividad_bitacora; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.actividad_bitacora OWNER TO postgres;

--
-- Name: actividad_bitacora_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.actividad_bitacora_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.actividad_bitacora_id_seq OWNER TO postgres;

--
-- Name: actividad_bitacora_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.actividad_bitacora_id_seq OWNED BY public.actividad_bitacora.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: permiso; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.permiso OWNER TO postgres;

--
-- Name: permiso_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permiso_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permiso_id_seq OWNER TO postgres;

--
-- Name: permiso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permiso_id_seq OWNED BY public.permiso.id;


--
-- Name: rol; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.rol OWNER TO postgres;

--
-- Name: rol_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rol_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rol_id_seq OWNER TO postgres;

--
-- Name: rol_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rol_id_seq OWNED BY public.rol.id;


--
-- Name: rol_permiso; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rol_permiso (
    id bigint NOT NULL,
    rol_id bigint NOT NULL,
    permiso_id bigint NOT NULL,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) with time zone
);


ALTER TABLE public.rol_permiso OWNER TO postgres;

--
-- Name: rol_permiso_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rol_permiso_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rol_permiso_id_seq OWNER TO postgres;

--
-- Name: rol_permiso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rol_permiso_id_seq OWNED BY public.rol_permiso.id;


--
-- Name: usuario; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.usuario OWNER TO postgres;

--
-- Name: usuario_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuario_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_id_seq OWNER TO postgres;

--
-- Name: usuario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuario_id_seq OWNED BY public.usuario.id;


--
-- Name: usuario_password_reset; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.usuario_password_reset OWNER TO postgres;

--
-- Name: usuario_password_reset_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuario_password_reset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_password_reset_id_seq OWNER TO postgres;

--
-- Name: usuario_password_reset_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuario_password_reset_id_seq OWNED BY public.usuario_password_reset.id;


--
-- Name: usuario_perfil; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.usuario_perfil OWNER TO postgres;

--
-- Name: usuario_perfil_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuario_perfil_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_perfil_id_seq OWNER TO postgres;

--
-- Name: usuario_perfil_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuario_perfil_id_seq OWNED BY public.usuario_perfil.id;


--
-- Name: usuario_rol; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.usuario_rol OWNER TO postgres;

--
-- Name: usuario_rol_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuario_rol_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_rol_id_seq OWNER TO postgres;

--
-- Name: usuario_rol_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuario_rol_id_seq OWNED BY public.usuario_rol.id;


--
-- Name: usuario_sesion; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.usuario_sesion OWNER TO postgres;

--
-- Name: usuario_sesion_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuario_sesion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_sesion_id_seq OWNER TO postgres;

--
-- Name: usuario_sesion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuario_sesion_id_seq OWNED BY public.usuario_sesion.id;


--
-- Name: acceso_bitacora id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.acceso_bitacora ALTER COLUMN id SET DEFAULT nextval('public.acceso_bitacora_id_seq'::regclass);


--
-- Name: actividad_bitacora id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_bitacora ALTER COLUMN id SET DEFAULT nextval('public.actividad_bitacora_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: permiso id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permiso ALTER COLUMN id SET DEFAULT nextval('public.permiso_id_seq'::regclass);


--
-- Name: rol id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol ALTER COLUMN id SET DEFAULT nextval('public.rol_id_seq'::regclass);


--
-- Name: rol_permiso id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso ALTER COLUMN id SET DEFAULT nextval('public.rol_permiso_id_seq'::regclass);


--
-- Name: usuario id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario ALTER COLUMN id SET DEFAULT nextval('public.usuario_id_seq'::regclass);


--
-- Name: usuario_password_reset id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_password_reset ALTER COLUMN id SET DEFAULT nextval('public.usuario_password_reset_id_seq'::regclass);


--
-- Name: usuario_perfil id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_perfil ALTER COLUMN id SET DEFAULT nextval('public.usuario_perfil_id_seq'::regclass);


--
-- Name: usuario_rol id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_rol ALTER COLUMN id SET DEFAULT nextval('public.usuario_rol_id_seq'::regclass);


--
-- Name: usuario_sesion id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_sesion ALTER COLUMN id SET DEFAULT nextval('public.usuario_sesion_id_seq'::regclass);


--
-- Data for Name: acceso_bitacora; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.acceso_bitacora (id, usuario_id, sesion_id, email_intentado, evento, exito, detalle, ip, user_agent, meta_json, created_at, updated_at, deleted_at) FROM stdin;
1	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:17:02-06	2026-02-04 03:17:02-06	\N
2	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:17:02-06	2026-02-04 03:17:02-06	\N
3	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:17:09-06	2026-02-04 03:17:09-06	\N
4	1	1	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:17:09-06	2026-02-04 03:17:09-06	\N
5	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:18:00-06	2026-02-04 03:18:00-06	\N
6	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:18:00-06	2026-02-04 03:18:00-06	\N
7	\N	\N	admin@rewards.com	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:18:07-06	2026-02-04 03:18:07-06	\N
8	\N	\N	admin@rewards.com	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:18:07-06	2026-02-04 03:18:07-06	\N
9	\N	\N	admin@rewards.com	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:18:11-06	2026-02-04 03:18:11-06	\N
10	\N	\N	admin@rewards.com	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:18:11-06	2026-02-04 03:18:11-06	\N
11	3	\N	estudiante@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:18:29-06	2026-02-04 03:18:29-06	\N
12	3	2	estudiante@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:18:29-06	2026-02-04 03:18:29-06	\N
13	3	\N	estudiante@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:20:37-06	2026-02-04 03:20:37-06	\N
14	3	\N	estudiante@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:20:37-06	2026-02-04 03:20:37-06	\N
15	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:20:41-06	2026-02-04 03:20:41-06	\N
16	1	3	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:20:41-06	2026-02-04 03:20:41-06	\N
17	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:23:46-06	2026-02-04 03:23:46-06	\N
18	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:23:46-06	2026-02-04 03:23:46-06	\N
19	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:24:27-06	2026-02-04 03:24:27-06	\N
20	2	4	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:24:27-06	2026-02-04 03:24:27-06	\N
21	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:24:57-06	2026-02-04 03:24:57-06	\N
22	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:24:57-06	2026-02-04 03:24:57-06	\N
23	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:37:01-06	2026-02-04 03:37:01-06	\N
24	1	5	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:37:01-06	2026-02-04 03:37:01-06	\N
25	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:37:28-06	2026-02-04 03:37:28-06	\N
26	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:37:28-06	2026-02-04 03:37:28-06	\N
27	4	\N	javiirving915@gmail.com	registro	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:40:50-06	2026-02-04 03:40:50-06	\N
28	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:40:51-06	2026-02-04 03:40:51-06	\N
29	4	6	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:40:51-06	2026-02-04 03:40:51-06	\N
30	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:41:56-06	2026-02-04 03:41:56-06	\N
31	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:41:56-06	2026-02-04 03:41:56-06	\N
32	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:42:02-06	2026-02-04 03:42:02-06	\N
33	4	7	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:42:02-06	2026-02-04 03:42:02-06	\N
34	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:43:30-06	2026-02-04 03:43:30-06	\N
35	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:43:30-06	2026-02-04 03:43:30-06	\N
36	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:59:08-06	2026-02-04 03:59:08-06	\N
37	4	8	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:59:08-06	2026-02-04 03:59:08-06	\N
38	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:59:57-06	2026-02-04 03:59:57-06	\N
39	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 03:59:57-06	2026-02-04 03:59:57-06	\N
40	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:01:33-06	2026-02-04 04:01:33-06	\N
41	4	9	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:01:33-06	2026-02-04 04:01:33-06	\N
42	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:04:47-06	2026-02-04 04:04:47-06	\N
43	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:04:47-06	2026-02-04 04:04:47-06	\N
44	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:05:47-06	2026-02-04 04:05:47-06	\N
45	4	10	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:05:47-06	2026-02-04 04:05:47-06	\N
46	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:02-06	2026-02-04 04:06:02-06	\N
47	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:02-06	2026-02-04 04:06:02-06	\N
48	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:06-06	2026-02-04 04:06:06-06	\N
49	4	11	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:06-06	2026-02-04 04:06:06-06	\N
50	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:17-06	2026-02-04 04:06:17-06	\N
51	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:17-06	2026-02-04 04:06:17-06	\N
52	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:22-06	2026-02-04 04:06:22-06	\N
53	4	12	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:22-06	2026-02-04 04:06:22-06	\N
54	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:34-06	2026-02-04 04:06:34-06	\N
55	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:34-06	2026-02-04 04:06:34-06	\N
56	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:40-06	2026-02-04 04:06:40-06	\N
57	4	13	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:40-06	2026-02-04 04:06:40-06	\N
58	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:54-06	2026-02-04 04:06:54-06	\N
59	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:06:54-06	2026-02-04 04:06:54-06	\N
60	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:07:00-06	2026-02-04 04:07:00-06	\N
61	4	14	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:07:00-06	2026-02-04 04:07:00-06	\N
62	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:07:13-06	2026-02-04 04:07:13-06	\N
63	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:07:13-06	2026-02-04 04:07:13-06	\N
64	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:30:06-06	2026-02-04 04:30:06-06	\N
65	1	15	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:30:06-06	2026-02-04 04:30:06-06	\N
66	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:58:26-06	2026-02-04 04:58:26-06	\N
67	1	16	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 04:58:26-06	2026-02-04 04:58:26-06	\N
68	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36	{}	2026-02-04 04:59:48-06	2026-02-04 04:59:48-06	\N
69	1	17	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36	{}	2026-02-04 04:59:48-06	2026-02-04 04:59:48-06	\N
70	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:03:43-06	2026-02-04 05:03:43-06	\N
71	1	18	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:03:43-06	2026-02-04 05:03:43-06	\N
72	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:04:13-06	2026-02-04 05:04:13-06	\N
73	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:04:13-06	2026-02-04 05:04:13-06	\N
74	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:04:18-06	2026-02-04 05:04:18-06	\N
75	4	19	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:04:18-06	2026-02-04 05:04:18-06	\N
76	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36	{}	2026-02-04 05:06:10-06	2026-02-04 05:06:10-06	\N
77	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36	{}	2026-02-04 05:06:10-06	2026-02-04 05:06:10-06	\N
78	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36	{}	2026-02-04 05:06:40-06	2026-02-04 05:06:40-06	\N
79	2	20	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36	{}	2026-02-04 05:06:40-06	2026-02-04 05:06:40-06	\N
80	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:06:59-06	2026-02-04 05:06:59-06	\N
81	2	21	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:06:59-06	2026-02-04 05:06:59-06	\N
82	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:07:47-06	2026-02-04 05:07:47-06	\N
83	1	22	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:07:47-06	2026-02-04 05:07:47-06	\N
84	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:07:50-06	2026-02-04 05:07:50-06	\N
85	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:07:50-06	2026-02-04 05:07:50-06	\N
86	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:08:12-06	2026-02-04 05:08:12-06	\N
87	1	23	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:08:12-06	2026-02-04 05:08:12-06	\N
88	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:08:18-06	2026-02-04 05:08:18-06	\N
89	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:08:18-06	2026-02-04 05:08:18-06	\N
90	\N	\N	admin@rewards.com	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:09:35-06	2026-02-04 05:09:35-06	\N
91	\N	\N	admin@rewards.com	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:09:35-06	2026-02-04 05:09:35-06	\N
92	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:09:42-06	2026-02-04 05:09:42-06	\N
93	1	24	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:09:42-06	2026-02-04 05:09:42-06	\N
94	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:11:27-06	2026-02-04 05:11:27-06	\N
95	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:11:27-06	2026-02-04 05:11:27-06	\N
96	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:13:24-06	2026-02-04 05:13:24-06	\N
97	1	25	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:13:24-06	2026-02-04 05:13:24-06	\N
98	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:13:27-06	2026-02-04 05:13:27-06	\N
99	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:13:27-06	2026-02-04 05:13:27-06	\N
100	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:13:33-06	2026-02-04 05:13:33-06	\N
101	2	26	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:13:33-06	2026-02-04 05:13:33-06	\N
102	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:15:49-06	2026-02-04 05:15:49-06	\N
103	1	27	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:15:49-06	2026-02-04 05:15:49-06	\N
104	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:15:53-06	2026-02-04 05:15:53-06	\N
105	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:15:53-06	2026-02-04 05:15:53-06	\N
106	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:16:00-06	2026-02-04 05:16:00-06	\N
107	2	28	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:16:00-06	2026-02-04 05:16:00-06	\N
108	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:16:36-06	2026-02-04 05:16:36-06	\N
109	1	29	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:16:36-06	2026-02-04 05:16:36-06	\N
110	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:12-06	2026-02-04 05:25:12-06	\N
111	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:12-06	2026-02-04 05:25:12-06	\N
112	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:16-06	2026-02-04 05:25:16-06	\N
113	1	30	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:16-06	2026-02-04 05:25:16-06	\N
114	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:20-06	2026-02-04 05:25:20-06	\N
115	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:20-06	2026-02-04 05:25:20-06	\N
116	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:29-06	2026-02-04 05:25:29-06	\N
117	2	31	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:29-06	2026-02-04 05:25:29-06	\N
118	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:41-06	2026-02-04 05:25:41-06	\N
119	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:41-06	2026-02-04 05:25:41-06	\N
120	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:46-06	2026-02-04 05:25:46-06	\N
121	4	32	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:25:46-06	2026-02-04 05:25:46-06	\N
163	1	42	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:39:25-06	2026-02-04 05:39:25-06	\N
122	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:26:00-06	2026-02-04 05:26:00-06	\N
123	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:26:00-06	2026-02-04 05:26:00-06	\N
124	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:26:06-06	2026-02-04 05:26:06-06	\N
125	2	33	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:26:06-06	2026-02-04 05:26:06-06	\N
126	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:26:22-06	2026-02-04 05:26:22-06	\N
127	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:26:22-06	2026-02-04 05:26:22-06	\N
128	2	\N	proveedor@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:27:08-06	2026-02-04 05:27:08-06	\N
129	2	34	proveedor@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:27:08-06	2026-02-04 05:27:08-06	\N
130	2	\N	proveedor@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:27:18-06	2026-02-04 05:27:18-06	\N
131	2	\N	proveedor@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:27:18-06	2026-02-04 05:27:18-06	\N
132	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:27:25-06	2026-02-04 05:27:25-06	\N
133	1	35	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:27:25-06	2026-02-04 05:27:25-06	\N
134	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:29:58-06	2026-02-04 05:29:58-06	\N
135	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:29:58-06	2026-02-04 05:29:58-06	\N
136	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:30:01-06	2026-02-04 05:30:01-06	\N
137	4	36	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:30:01-06	2026-02-04 05:30:01-06	\N
138	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:30:13-06	2026-02-04 05:30:13-06	\N
139	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:30:13-06	2026-02-04 05:30:13-06	\N
140	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:30:17-06	2026-02-04 05:30:17-06	\N
141	1	37	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:30:17-06	2026-02-04 05:30:17-06	\N
142	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:31:08-06	2026-02-04 05:31:08-06	\N
143	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:31:08-06	2026-02-04 05:31:08-06	\N
144	\N	\N	javiirving915@gmail.com	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:31:12-06	2026-02-04 05:31:12-06	\N
145	\N	\N	javiirving915@gmail.com	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:31:12-06	2026-02-04 05:31:12-06	\N
146	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:31:18-06	2026-02-04 05:31:18-06	\N
147	4	38	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:31:18-06	2026-02-04 05:31:18-06	\N
148	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:31:53-06	2026-02-04 05:31:53-06	\N
149	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:31:53-06	2026-02-04 05:31:53-06	\N
150	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:31:58-06	2026-02-04 05:31:58-06	\N
151	1	39	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:31:58-06	2026-02-04 05:31:58-06	\N
152	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:32:26-06	2026-02-04 05:32:26-06	\N
153	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:32:26-06	2026-02-04 05:32:26-06	\N
154	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:32:30-06	2026-02-04 05:32:30-06	\N
155	4	40	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:32:30-06	2026-02-04 05:32:30-06	\N
156	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:38:01-06	2026-02-04 05:38:01-06	\N
157	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:38:01-06	2026-02-04 05:38:01-06	\N
158	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:38:07-06	2026-02-04 05:38:07-06	\N
159	1	41	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:38:07-06	2026-02-04 05:38:07-06	\N
160	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:39:11-06	2026-02-04 05:39:11-06	\N
161	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:39:11-06	2026-02-04 05:39:11-06	\N
162	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:39:25-06	2026-02-04 05:39:25-06	\N
164	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:39:53-06	2026-02-04 05:39:53-06	\N
165	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:39:53-06	2026-02-04 05:39:53-06	\N
166	5	\N	javiirving915itc@gmail.com	registro	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:43:03-06	2026-02-04 05:43:03-06	\N
167	5	\N	javiirving915itc@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:44:42-06	2026-02-04 05:44:42-06	\N
168	5	43	javiirving915itc@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:44:42-06	2026-02-04 05:44:42-06	\N
169	5	\N	javiirving915itc@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:46:40-06	2026-02-04 05:46:40-06	\N
170	5	\N	javiirving915itc@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:46:40-06	2026-02-04 05:46:40-06	\N
171	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:51:26-06	2026-02-04 05:51:26-06	\N
172	1	44	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:51:26-06	2026-02-04 05:51:26-06	\N
173	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:51:29-06	2026-02-04 05:51:29-06	\N
174	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:51:29-06	2026-02-04 05:51:29-06	\N
175	\N	\N	javiirving915@gmail.com	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:06-06	2026-02-04 05:57:06-06	\N
176	\N	\N	javiirving915@gmail.com	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:06-06	2026-02-04 05:57:06-06	\N
177	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:11-06	2026-02-04 05:57:11-06	\N
178	4	45	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:11-06	2026-02-04 05:57:11-06	\N
179	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:32-06	2026-02-04 05:57:32-06	\N
180	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:32-06	2026-02-04 05:57:32-06	\N
181	\N	\N	javiirving915@gmail.com	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:39-06	2026-02-04 05:57:39-06	\N
182	\N	\N	javiirving915@gmail.com	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:39-06	2026-02-04 05:57:39-06	\N
183	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:46-06	2026-02-04 05:57:46-06	\N
184	4	46	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:46-06	2026-02-04 05:57:46-06	\N
185	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:49-06	2026-02-04 05:57:49-06	\N
186	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:49-06	2026-02-04 05:57:49-06	\N
187	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:53-06	2026-02-04 05:57:53-06	\N
188	1	47	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:57:53-06	2026-02-04 05:57:53-06	\N
189	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:58:08-06	2026-02-04 05:58:08-06	\N
190	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:58:08-06	2026-02-04 05:58:08-06	\N
191	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:58:14-06	2026-02-04 05:58:14-06	\N
192	1	48	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:58:14-06	2026-02-04 05:58:14-06	\N
193	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:05:44-06	2026-02-04 06:05:44-06	\N
194	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:05:44-06	2026-02-04 06:05:44-06	\N
195	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:05:49-06	2026-02-04 06:05:49-06	\N
196	4	49	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:05:49-06	2026-02-04 06:05:49-06	\N
197	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:06:00-06	2026-02-04 06:06:00-06	\N
198	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:06:00-06	2026-02-04 06:06:00-06	\N
199	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:06:12-06	2026-02-04 06:06:12-06	\N
200	1	50	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:06:12-06	2026-02-04 06:06:12-06	\N
201	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:48:28-06	2026-02-04 06:48:28-06	\N
202	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:48:28-06	2026-02-04 06:48:28-06	\N
203	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:48:34-06	2026-02-04 06:48:34-06	\N
204	4	51	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:48:34-06	2026-02-04 06:48:34-06	\N
205	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:48:49-06	2026-02-04 06:48:49-06	\N
206	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:48:49-06	2026-02-04 06:48:49-06	\N
207	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:11:45-06	2026-02-04 07:11:45-06	\N
208	1	52	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:11:45-06	2026-02-04 07:11:45-06	\N
209	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:11:49-06	2026-02-04 07:11:49-06	\N
210	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:11:49-06	2026-02-04 07:11:49-06	\N
211	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:16:36-06	2026-02-04 07:16:36-06	\N
212	1	53	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:16:36-06	2026-02-04 07:16:36-06	\N
213	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:16:39-06	2026-02-04 07:16:39-06	\N
214	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:16:39-06	2026-02-04 07:16:39-06	\N
215	\N	\N	aaaa@ncom.vv	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:16:41-06	2026-02-04 07:16:41-06	\N
216	\N	\N	aaaa@ncom.vv	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:16:42-06	2026-02-04 07:16:42-06	\N
217	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:31:33-06	2026-02-04 07:31:33-06	\N
218	1	54	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:31:33-06	2026-02-04 07:31:33-06	\N
219	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:31:35-06	2026-02-04 07:31:35-06	\N
220	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:31:35-06	2026-02-04 07:31:35-06	\N
221	6	\N	javiirving716@gmail.com	registro	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:32:18-06	2026-02-04 07:32:18-06	\N
222	6	\N	javiirving716@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:32:22-06	2026-02-04 07:32:22-06	\N
223	6	55	javiirving716@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:32:22-06	2026-02-04 07:32:22-06	\N
224	6	\N	javiirving716@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:34:58-06	2026-02-04 07:34:58-06	\N
225	6	\N	javiirving716@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:34:58-06	2026-02-04 07:34:58-06	\N
226	\N	\N	javiirvin716@gmail.com	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:35:12-06	2026-02-04 07:35:12-06	\N
227	\N	\N	javiirvin716@gmail.com	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:35:12-06	2026-02-04 07:35:12-06	\N
228	7	\N	javiirvin716@gmail.com	registro	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:35:25-06	2026-02-04 07:35:25-06	\N
229	7	\N	javiirvin716@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:35:29-06	2026-02-04 07:35:29-06	\N
230	7	56	javiirvin716@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:35:29-06	2026-02-04 07:35:29-06	\N
231	7	\N	javiirvin716@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:36:07-06	2026-02-04 07:36:07-06	\N
232	7	\N	javiirvin716@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:36:07-06	2026-02-04 07:36:07-06	\N
233	7	\N	javiirvin716@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:36:14-06	2026-02-04 07:36:14-06	\N
234	7	57	javiirvin716@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:36:14-06	2026-02-04 07:36:14-06	\N
235	7	\N	javiirvin716@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:36:17-06	2026-02-04 07:36:17-06	\N
236	7	\N	javiirvin716@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:36:17-06	2026-02-04 07:36:17-06	\N
237	7	\N	javiirvin716@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:23-06	2026-02-04 07:37:23-06	\N
238	7	58	javiirvin716@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:23-06	2026-02-04 07:37:23-06	\N
239	7	\N	javiirvin716@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:30-06	2026-02-04 07:37:30-06	\N
240	7	\N	javiirvin716@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:30-06	2026-02-04 07:37:30-06	\N
241	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:40-06	2026-02-04 07:37:40-06	\N
242	1	59	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:40-06	2026-02-04 07:37:40-06	\N
243	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:43-06	2026-02-04 07:37:43-06	\N
244	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:43-06	2026-02-04 07:37:43-06	\N
245	4	\N	javiirving915@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:51-06	2026-02-04 07:37:51-06	\N
246	4	60	javiirving915@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:51-06	2026-02-04 07:37:51-06	\N
247	4	\N	javiirving915@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:54-06	2026-02-04 07:37:54-06	\N
248	4	\N	javiirving915@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:37:54-06	2026-02-04 07:37:54-06	\N
249	7	\N	javiirvin716@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:38:01-06	2026-02-04 07:38:01-06	\N
250	7	61	javiirvin716@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:38:01-06	2026-02-04 07:38:01-06	\N
251	7	\N	javiirvin716@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:38:04-06	2026-02-04 07:38:04-06	\N
252	7	\N	javiirvin716@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:38:04-06	2026-02-04 07:38:04-06	\N
253	\N	\N	javiirvin716@gmail.com	login_failed	f	Credenciales inválidas	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:38:16-06	2026-02-04 07:38:16-06	\N
254	\N	\N	javiirvin716@gmail.com	login_failed	f	Intento de login fallido	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:38:16-06	2026-02-04 07:38:16-06	\N
255	7	\N	javiirvin716@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:38:21-06	2026-02-04 07:38:21-06	\N
256	7	62	javiirvin716@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:38:21-06	2026-02-04 07:38:21-06	\N
257	7	\N	javiirvin716@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:38:52-06	2026-02-04 07:38:52-06	\N
258	7	\N	javiirvin716@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:38:52-06	2026-02-04 07:38:52-06	\N
259	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:39:15-06	2026-02-04 07:39:15-06	\N
260	1	63	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:39:15-06	2026-02-04 07:39:15-06	\N
261	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:39:42-06	2026-02-04 07:39:42-06	\N
262	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:39:42-06	2026-02-04 07:39:42-06	\N
263	7	\N	javiirvin716@gmail.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:39:48-06	2026-02-04 07:39:48-06	\N
264	7	64	javiirvin716@gmail.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:39:48-06	2026-02-04 07:39:48-06	\N
265	7	\N	javiirvin716@gmail.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:40:23-06	2026-02-04 07:40:23-06	\N
266	7	\N	javiirvin716@gmail.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:40:23-06	2026-02-04 07:40:23-06	\N
267	1	\N	admin@campusdigital.com	login_success	t	Login exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:40:27-06	2026-02-04 07:40:27-06	\N
268	1	65	admin@campusdigital.com	login	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:40:27-06	2026-02-04 07:40:27-06	\N
269	1	\N	admin@campusdigital.com	logout	t	Logout exitoso	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:41:04-06	2026-02-04 07:41:04-06	\N
270	1	\N	admin@campusdigital.com	logout	t		127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:41:04-06	2026-02-04 07:41:04-06	\N
\.


--
-- Data for Name: actividad_bitacora; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.actividad_bitacora (id, usuario_id, sesion_id, accion, modulo, target_tabla, target_id, exito, detalle, ip, user_agent, meta_json, created_at, updated_at, deleted_at) FROM stdin;
1	1	\N	actualizar_usuario	seguridad	usuario	4	t	Usuario actualizado: javiirving915@gmail.com	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:29:40-06	2026-02-04 05:29:40-06	\N
2	1	\N	actualizar_usuario	seguridad	usuario	4	t	Usuario actualizado: javiirving915@gmail.com	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:29:51-06	2026-02-04 05:29:51-06	\N
3	1	\N	actualizar_usuario	seguridad	usuario	4	t	Usuario actualizado: javiirving915@gmail.com	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:31:04-06	2026-02-04 05:31:04-06	\N
4	1	\N	actualizar_usuario	seguridad	usuario	4	t	Usuario actualizado: javiirving915@gmail.com	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 05:32:22-06	2026-02-04 05:32:22-06	\N
5	1	\N	eliminar_usuario	seguridad	usuario	5	t	Usuario eliminado: javiirving915itc@gmail.com	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:05:09-06	2026-02-04 06:05:09-06	\N
6	1	\N	cambiar_estado_usuario	seguridad	usuario	4	t	Usuario bloqueado: javiirving915@gmail.com	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:05:41-06	2026-02-04 06:05:41-06	\N
7	1	\N	cambiar_estado_usuario	seguridad	usuario	4	t	Usuario desbloqueado: javiirving915@gmail.com	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 06:06:21-06	2026-02-04 06:06:21-06	\N
8	1	\N	eliminar_usuario	seguridad	usuario	6	t	Usuario eliminado: javiirving716@gmail.com	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:39:29-06	2026-02-04 07:39:29-06	\N
9	1	\N	actualizar_usuario	seguridad	usuario	7	t	Usuario actualizado: javiirvin716@gmail.com	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:39:38-06	2026-02-04 07:39:38-06	\N
10	7	\N	actualizar_usuario	seguridad	usuario	7	t	Usuario actualizado: javiirvin716@gmail.com	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	{}	2026-02-04 07:40:11-06	2026-02-04 07:40:11-06	\N
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
campus-digital-cache-09ec809bdc0d52bff6e1fef7f7e37720:timer	i:1770190847;	1770190847
campus-digital-cache-09ec809bdc0d52bff6e1fef7f7e37720	i:1;	1770190847
campus-digital-cache-ab55a59befc701b7394580c6a0729840:timer	i:1770190887;	1770190887
campus-digital-cache-ab55a59befc701b7394580c6a0729840	i:1;	1770190887
campus-digital-cache-801665fde8b097b28a195d1648f9c024:timer	i:1770189461;	1770189461
campus-digital-cache-801665fde8b097b28a195d1648f9c024	i:1;	1770189461
campus-digital-cache-aaaa@ncom.vv|127.0.0.1:timer	i:1770189462;	1770189462
campus-digital-cache-aaaa@ncom.vv|127.0.0.1	i:1;	1770189462
campus-digital-cache-c1dfd96eea8cc2b62785275bca38ac261256e278:timer	i:1770190528;	1770190528
campus-digital-cache-c1dfd96eea8cc2b62785275bca38ac261256e278	i:1;	1770190528
campus-digital-cache-902ba3cda1883801594b6e1b452790cc53948fda:timer	i:1770190622;	1770190622
campus-digital-cache-902ba3cda1883801594b6e1b452790cc53948fda	i:1;	1770190622
campus-digital-cache-619a789551ea6f3074403aa7c23c594d:timer	i:1770190730;	1770190730
campus-digital-cache-619a789551ea6f3074403aa7c23c594d	i:1;	1770190730
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
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
15	2026_02_04_053416_create_password_reset_tokens_table	2
16	2026_02_04_055455_add_remember_token_to_usuario_table	3
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: permiso; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permiso (id, clave, descripcion, activo, created_at, updated_at, deleted_at) FROM stdin;
1	user.read	Consultar usuarios	t	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
2	user.write	Crear/editar usuarios	t	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
3	role.read	Consultar roles	t	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
4	role.write	Administrar roles	t	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
5	permission.read	Consultar permisos	t	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
6	permission.write	Administrar permisos	t	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
7	audit.read	Consultar bitácoras	t	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
8	iijiij	ffbfbfb	t	2026-02-04 06:25:08-06	2026-02-04 00:25:15-06	2026-02-04 06:25:14-06
\.


--
-- Data for Name: rol; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.rol (id, nombre, descripcion, activo, created_at, updated_at, deleted_at) FROM stdin;
1	estudiante	Usuario final que consume servicios digitales	t	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
2	proveedor_area	Proveedor o área interna que atiende solicitudes	t	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
3	administrador	Admin con acceso total	t	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
4	tester	rfrfff	t	2026-02-04 06:21:32-06	2026-02-04 00:21:44-06	2026-02-04 06:21:44-06
\.


--
-- Data for Name: rol_permiso; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.rol_permiso (id, rol_id, permiso_id, created_at, updated_at, deleted_at) FROM stdin;
1	3	1	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
2	3	2	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
3	3	3	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
4	3	4	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
5	3	5	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
6	3	6	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
7	3	7	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
8	2	1	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
9	2	7	2026-02-03 21:16:33-06	2026-02-03 21:16:33-06	\N
11	4	1	2026-02-04 06:21:32-06	2026-02-04 06:21:32-06	\N
12	4	3	2026-02-04 06:21:32-06	2026-02-04 06:21:32-06	\N
13	4	5	2026-02-04 06:21:32-06	2026-02-04 06:21:32-06	\N
14	1	1	2026-02-04 06:21:55-06	2026-02-04 06:21:55-06	\N
\.


--
-- Data for Name: usuario; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuario (id, nombre, apellido, telefono, foto_url, password_hash, email_verificado, ultimo_login_at, bloqueado, bloqueado_hasta, seguridad_json, created_at, updated_at, deleted_at, email, remember_token) FROM stdin;
2	Proveedor	Cafetería	0987654321		$2y$12$gl0tbWxabHhcAFXEsAB1u.fiRlcfQd4rZQo1SqCvh/8.Kn9yPpzJW	t	\N	f	\N	{}	2026-02-04 03:16:33-06	2026-02-04 03:16:33-06	\N	proveedor@campusdigital.com	\N
3	Juan	Pérez	5555555555		$2y$12$jF6D0xVShzeuulEGCaIUk.A34pT2CvR4So8OjPPpCygStXZoy8Fmy	t	\N	f	\N	{}	2026-02-04 03:16:34-06	2026-02-04 03:16:34-06	\N	estudiante@campusdigital.com	\N
4	javiirving915@gmail.com	javiirving915@gmail.com	7777		$2y$12$RHbP8I5mztTq7.RHvtNADuDlU4q6Y6F5yEsWyBxlydmReOlUv0dJG	t	\N	f	\N	{}	2026-02-04 03:40:50-06	2026-02-04 01:37:54-06	\N	javiirving915@gmail.com	fmJzMQcPAmVleKYr9WzmyYwnGyQlhaRD9LxG2VQFbm3nrO7bLMLOY7WK7fMg
6	ggggtg	tgtgg			$2y$12$HgdvZ3GFeRuqk5MTvvLNouDqqLojY8cIOyfoTsw403nBap.4/lPlO	f	\N	f	\N	{}	2026-02-04 07:32:18-06	2026-02-04 01:39:29-06	2026-02-04 07:39:29-06	javiirving716@gmail.com	\N
7	gthtth	thht			$2y$12$8E3cc.cpv/d6S4MsGJdt.OHWBDxXib0496dT0IqUDykFSxdIHtRp.	t	\N	f	\N	{}	2026-02-04 07:35:25-06	2026-02-04 01:40:23-06	\N	javiirvin716@gmail.com	BXRUA1kZX7ByLOzSlF2g2zRoQV42cI0uxKbVK9MvRdlwAAhu2nUmYM54HpRA
1	Admin	Sistema	1234567890711		$2y$12$ZlF4qrt3J80oVlVU/A2F9.KwcK/2QS/12o4R0SEZSatuBuxxPXrA6	t	\N	f	\N	{}	2026-02-04 03:16:33-06	2026-02-03 23:38:20-06	\N	admin@campusdigital.com	\N
5	javiirving915itc@gmail.com	javiirving915itc@gmail.com			$2y$12$0/Jz5UW71T36uTPPgNYn0uHjwM5jt4R3KWDhulGivVE/bv7Aof0/i	t	\N	f	\N	{}	2026-02-04 05:43:03-06	2026-02-04 00:05:09-06	2026-02-04 06:05:09-06	javiirving915itc@gmail.com	\N
\.


--
-- Data for Name: usuario_password_reset; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuario_password_reset (id, usuario_id, token_hash, solicitado_at, expira_at, usado_at, ip, user_agent, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: usuario_perfil; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuario_perfil (id, usuario_id, fecha_nacimiento, genero, direccion, preferencias_json, created_at, updated_at, deleted_at) FROM stdin;
2	2	\N			{}	2026-02-04 03:16:33-06	2026-02-04 03:16:33-06	\N
3	3	2000-01-15	masculino		{}	2026-02-04 03:16:34-06	2026-02-04 03:16:34-06	\N
4	4	2026-01-28	masculino	mjmjmjmjmjmj	{}	2026-02-04 03:40:50-06	2026-02-03 22:06:31-06	\N
5	5	\N			{}	2026-02-04 05:43:03-06	2026-02-04 05:43:03-06	\N
1	1	\N	masculino		{}	2026-02-04 03:16:33-06	2026-02-03 23:58:01-06	\N
6	6	\N			{}	2026-02-04 07:32:18-06	2026-02-04 07:32:18-06	\N
7	7	\N			{}	2026-02-04 07:35:25-06	2026-02-04 07:35:25-06	\N
\.


--
-- Data for Name: usuario_rol; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuario_rol (id, usuario_id, rol_id, asignado_por_usuario_id, asignado_at, created_at, updated_at, deleted_at) FROM stdin;
1	1	3	\N	2026-02-03 21:16:33-06	2026-02-04 03:16:33-06	2026-02-04 03:16:33-06	\N
2	2	2	\N	2026-02-03 21:16:34-06	2026-02-04 03:16:33-06	2026-02-04 03:16:33-06	\N
3	3	1	\N	2026-02-03 21:16:34-06	2026-02-04 03:16:34-06	2026-02-04 03:16:34-06	\N
6	4	1	\N	2026-02-03 23:32:23-06	2026-02-04 05:32:22-06	2026-02-04 05:32:22-06	\N
7	5	1	\N	2026-02-03 23:43:04-06	2026-02-04 05:43:03-06	2026-02-04 05:43:03-06	\N
8	6	1	\N	2026-02-04 01:32:18-06	2026-02-04 07:32:18-06	2026-02-04 07:32:18-06	\N
10	7	3	\N	2026-02-04 01:39:39-06	2026-02-04 07:39:38-06	2026-02-04 07:39:38-06	\N
11	7	1	\N	2026-02-04 01:40:12-06	2026-02-04 07:40:11-06	2026-02-04 07:40:11-06	\N
\.


--
-- Data for Name: usuario_sesion; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuario_sesion (id, usuario_id, session_id, ip, user_agent, inicia_at, expira_at, termina_at, activa, meta_json, created_at, updated_at, deleted_at) FROM stdin;
1	1	DFhpP0SvCVph5NGuN8tu3axDkIVvgI7C92uKGw0s	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 03:17:09-06	2026-02-04 05:17:09-06	\N	t	{}	2026-02-04 03:17:09-06	2026-02-04 03:17:09-06	\N
2	3	rxARpKyrdoTAZ4LsUXPR1YcM67V7s76eMvt2RWI6	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 03:18:29-06	2026-02-04 05:18:29-06	\N	t	{}	2026-02-04 03:18:29-06	2026-02-04 03:18:29-06	\N
3	1	7OsoNropX8zMlRbKim2xmHJh50DxJ9iZTb3bOpqB	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 03:20:41-06	2026-02-04 05:20:41-06	\N	t	{}	2026-02-04 03:20:41-06	2026-02-04 03:20:41-06	\N
4	2	rPPRCIPGBcv7P3DvUtje4t0sz7erJmRUhKDOKAYi	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 03:24:27-06	2026-02-04 05:24:27-06	\N	t	{}	2026-02-04 03:24:27-06	2026-02-04 03:24:27-06	\N
5	1	HUU0X0Fvy7b4KFRkweKZYxZmNxVPdbdK2aVzjGwR	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 03:37:01-06	2026-02-04 05:37:01-06	\N	t	{}	2026-02-04 03:37:01-06	2026-02-04 03:37:01-06	\N
6	4	uhI45naCAoP7rjIC7HIAJx4EZc6K01qaZvgTDyJj	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 03:40:51-06	2026-02-04 05:40:51-06	\N	t	{}	2026-02-04 03:40:51-06	2026-02-04 03:40:51-06	\N
7	4	eC7tO72rR56yf9Y3m0tlACQTtcKHo1dxyxL3TeeS	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 03:42:02-06	2026-02-04 05:42:02-06	\N	t	{}	2026-02-04 03:42:02-06	2026-02-04 03:42:02-06	\N
8	4	EkBI05RUyCfXcxy7hUaOHlII8RXOXHCydSLpK1L7	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 03:59:08-06	2026-02-04 05:59:08-06	\N	t	{}	2026-02-04 03:59:08-06	2026-02-04 03:59:08-06	\N
9	4	STyzGncSBMU9se6pmiTacZyDcgRGfBrCYZ0iegfP	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 04:01:33-06	2026-02-04 06:01:33-06	\N	t	{}	2026-02-04 04:01:33-06	2026-02-04 04:01:33-06	\N
10	4	qe0axQP9PNmRPWKKTlU6OlGuSOcvsKflElzHegGD	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 04:05:47-06	2026-02-04 06:05:47-06	\N	t	{}	2026-02-04 04:05:47-06	2026-02-04 04:05:47-06	\N
11	4	96i9Z2G1RgFHICD07Y4ecHw8JQIMqgYe9GYQqiMr	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 04:06:06-06	2026-02-04 06:06:06-06	\N	t	{}	2026-02-04 04:06:06-06	2026-02-04 04:06:06-06	\N
12	4	VLaBMbECbzXeMePhynyxiP3RCarQM4MABZFiYCvT	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 04:06:22-06	2026-02-04 06:06:22-06	\N	t	{}	2026-02-04 04:06:22-06	2026-02-04 04:06:22-06	\N
13	4	ljAjUvrgk9xD7CKtGOd7A0I7wsHiqTV8f640lceJ	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 04:06:40-06	2026-02-04 06:06:40-06	\N	t	{}	2026-02-04 04:06:40-06	2026-02-04 04:06:40-06	\N
14	4	4XoqgGmcqBwlWRu7FWNhC98m78Q2kOurEuWwTGbw	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 04:07:00-06	2026-02-04 06:07:00-06	\N	t	{}	2026-02-04 04:07:00-06	2026-02-04 04:07:00-06	\N
15	1	mC9UNXpUhsjhbPrxEX6fyBJp2Qcno3hbOMM0180R	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 04:30:06-06	2026-02-04 06:30:06-06	\N	t	{}	2026-02-04 04:30:06-06	2026-02-04 04:30:06-06	\N
16	1	4PQKTvKZOzmpSrNShPH3IHAD0YAk9SF9ddG2QVWd	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 04:58:26-06	2026-02-04 06:58:26-06	\N	t	{}	2026-02-04 04:58:26-06	2026-02-04 04:58:26-06	\N
17	1	o99niZSII8WAcpnvy2HfSuFglQe5QZ1XQtGihjXb	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36	2026-02-04 04:59:48-06	2026-02-04 06:59:48-06	\N	t	{}	2026-02-04 04:59:48-06	2026-02-04 04:59:48-06	\N
18	1	OZIfR5AQcaUqRWtSL3KOdpGBmqKyM8M9PAaCQCUJ	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:03:43-06	2026-02-04 07:03:43-06	\N	t	{}	2026-02-04 05:03:43-06	2026-02-04 05:03:43-06	\N
19	4	G1OaZuRt0U3VFahMsyAEMAjnS305kPGRkpoJ2FwW	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:04:18-06	2026-02-04 07:04:18-06	\N	t	{}	2026-02-04 05:04:18-06	2026-02-04 05:04:18-06	\N
20	2	hC4mvZxLjBXLv6VwSNVLnoCMMIrgFJH3oNkGXdVv	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36	2026-02-04 05:06:40-06	2026-02-04 07:06:40-06	\N	t	{}	2026-02-04 05:06:40-06	2026-02-04 05:06:40-06	\N
21	2	9IHD4GxV4pAWH9NnkkfwWlGpGqiztWS1VVB3Gm48	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:06:59-06	2026-02-04 07:06:59-06	\N	t	{}	2026-02-04 05:06:59-06	2026-02-04 05:06:59-06	\N
22	1	oiFDVUQBJDO7aqQJYa9onwnzq56DgJFRjy78Ax7q	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:07:47-06	2026-02-04 07:07:47-06	\N	t	{}	2026-02-04 05:07:47-06	2026-02-04 05:07:47-06	\N
23	1	50ay1GoqOTa8Q0aCYNHjAHLTbGW3qdIyEplblHz3	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:08:12-06	2026-02-04 07:08:12-06	\N	t	{}	2026-02-04 05:08:12-06	2026-02-04 05:08:12-06	\N
24	1	dQBonL8TJrO2cfRJB2xQTvRC74PWwLxlyHFKIbey	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:09:42-06	2026-02-04 07:09:42-06	\N	t	{}	2026-02-04 05:09:42-06	2026-02-04 05:09:42-06	\N
25	1	2q64GuNtWQyH3c9Ehf9AuJdEhTcI3AtxLa2kkJWp	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:13:24-06	2026-02-04 07:13:24-06	\N	t	{}	2026-02-04 05:13:24-06	2026-02-04 05:13:24-06	\N
26	2	Z0SeVC1ulRUqeofYUfC9pMtBB0QDxXynEEW95oPc	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:13:33-06	2026-02-04 07:13:33-06	\N	t	{}	2026-02-04 05:13:33-06	2026-02-04 05:13:33-06	\N
27	1	Rt9PTOaopaTbJkckNm611lxvTiNqduIOuoPlcWsX	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:15:49-06	2026-02-04 07:15:49-06	\N	t	{}	2026-02-04 05:15:49-06	2026-02-04 05:15:49-06	\N
28	2	wNKb24C4q7ye0tSsMX57kN92hUhygMULVxiJ8MVN	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:16:00-06	2026-02-04 07:16:00-06	\N	t	{}	2026-02-04 05:16:00-06	2026-02-04 05:16:00-06	\N
29	1	wMVVLIFaDfbbsjsv2fSGJQe11S4Uv2f2zNlMMBqO	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:16:36-06	2026-02-04 07:16:36-06	\N	t	{}	2026-02-04 05:16:36-06	2026-02-04 05:16:36-06	\N
30	1	4MsosIXZKpu0WGjHgyyWkmrBfkTct8TM6OrsdkA5	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:25:16-06	2026-02-04 07:25:16-06	\N	t	{}	2026-02-04 05:25:16-06	2026-02-04 05:25:16-06	\N
31	2	LAvQXpqQeB1uS3rheUjlAM5uJBNGw6z28cGn4Yb2	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:25:29-06	2026-02-04 07:25:29-06	\N	t	{}	2026-02-04 05:25:29-06	2026-02-04 05:25:29-06	\N
32	4	2zZZbMNbuZWTgZorjN0iNsjLZz5lJlcc2PNUaTyY	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:25:46-06	2026-02-04 07:25:46-06	\N	t	{}	2026-02-04 05:25:46-06	2026-02-04 05:25:46-06	\N
33	2	XnMa893TqstC3LL0xbbgG8C5gadSWbscnqPlZ2fg	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:26:06-06	2026-02-04 07:26:06-06	\N	t	{}	2026-02-04 05:26:06-06	2026-02-04 05:26:06-06	\N
34	2	byBpF6oZ5Kq3CjCRq7xqTo38qghkmeiYV9299nsN	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:27:08-06	2026-02-04 07:27:08-06	\N	t	{}	2026-02-04 05:27:08-06	2026-02-04 05:27:08-06	\N
35	1	2LaIyBcAR7sE7hx6AmeHfUuurBkn3supXFEqd8cP	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:27:25-06	2026-02-04 07:27:25-06	\N	t	{}	2026-02-04 05:27:25-06	2026-02-04 05:27:25-06	\N
36	4	KBaAJB1vGDe32gmep1eUZ1zGeI0pNKgOUq5YDUBF	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:30:01-06	2026-02-04 07:30:01-06	\N	t	{}	2026-02-04 05:30:01-06	2026-02-04 05:30:01-06	\N
37	1	Cm9FN4BSspiciBUMOD5vSimHvWqZ6ewPrr9fBv7A	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:30:17-06	2026-02-04 07:30:17-06	\N	t	{}	2026-02-04 05:30:17-06	2026-02-04 05:30:17-06	\N
38	4	m4aKwwDPR0Q9Mg9PXwKlYrnLj3RzR5XIQflqwF6m	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:31:18-06	2026-02-04 07:31:18-06	\N	t	{}	2026-02-04 05:31:18-06	2026-02-04 05:31:18-06	\N
39	1	6Lmh9gR8SDG1qV6GvHO1v5CW4SAD34KLV36GJpYw	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:31:58-06	2026-02-04 07:31:58-06	\N	t	{}	2026-02-04 05:31:58-06	2026-02-04 05:31:58-06	\N
40	4	n8ZQbaioVcpxVKIjLXDLsYyXKtImIK0lMNIh5q6S	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:32:30-06	2026-02-04 07:32:30-06	\N	t	{}	2026-02-04 05:32:30-06	2026-02-04 05:32:30-06	\N
41	1	ZbmuYVyyGBizwJWaKpCZazZ78Us3eynFpTlOkkDM	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:38:07-06	2026-02-04 07:38:07-06	\N	t	{}	2026-02-04 05:38:07-06	2026-02-04 05:38:07-06	\N
42	1	ihgWbl45IEeHJlCJd5mdO97TobwOOdEBvmioWh7R	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:39:25-06	2026-02-04 07:39:25-06	\N	t	{}	2026-02-04 05:39:25-06	2026-02-04 05:39:25-06	\N
43	5	w7NqZJhYyEfQjhQYgeLf7CyaZWR4CaKEus6dE1KQ	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:44:42-06	2026-02-04 07:44:42-06	\N	t	{}	2026-02-04 05:44:42-06	2026-02-04 05:44:42-06	\N
44	1	QgDRsRI3FKF0VcX0ovip6ZtWJhRxfOvXUtcj7njS	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:51:26-06	2026-02-04 07:51:26-06	\N	t	{}	2026-02-04 05:51:26-06	2026-02-04 05:51:26-06	\N
45	4	6zepAKLPlTBOY4llxqk60CFpfCh0rh8V8h711hvq	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:57:11-06	2026-02-04 07:57:11-06	\N	t	{}	2026-02-04 05:57:11-06	2026-02-04 05:57:11-06	\N
46	4	SUz8NalKwz1NshpvEEOcDpF2PCbEb2mlE2R24MTr	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:57:46-06	2026-02-04 07:57:46-06	\N	t	{}	2026-02-04 05:57:46-06	2026-02-04 05:57:46-06	\N
47	1	8knxa4GNhsJvqeb1rlxOeUTNgOZlgavfx2bwlqTQ	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:57:53-06	2026-02-04 07:57:53-06	\N	t	{}	2026-02-04 05:57:53-06	2026-02-04 05:57:53-06	\N
48	1	hbWf2hBBXFELsTObpGmpLa45djmh1yIAEOPH6kYD	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 05:58:14-06	2026-02-04 07:58:14-06	\N	t	{}	2026-02-04 05:58:14-06	2026-02-04 05:58:14-06	\N
49	4	vAeaATlSmBkQYPJHFBZacpBMaDPuwn0R63jIvzKG	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 06:05:49-06	2026-02-04 08:05:49-06	\N	t	{}	2026-02-04 06:05:49-06	2026-02-04 06:05:49-06	\N
50	1	yFv93W1ZJYzBMJiWxljKxLLSbOcyZVNVG6JQxPWI	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 06:06:12-06	2026-02-04 08:06:12-06	\N	t	{}	2026-02-04 06:06:12-06	2026-02-04 06:06:12-06	\N
51	4	67G3HysUgfXLjHsIyNwxy0crKOEPaK0Pfj93tPbE	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 06:48:34-06	2026-02-04 08:48:34-06	\N	t	{}	2026-02-04 06:48:34-06	2026-02-04 06:48:34-06	\N
52	1	amiL1oCCQO9F9d0VDULT5KVltTvzGzGs1By2Wi4R	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:11:45-06	2026-02-04 09:11:45-06	\N	t	{}	2026-02-04 07:11:45-06	2026-02-04 07:11:45-06	\N
53	1	Ic3KtFcmxI4egH3SK73CZoWg6nySQWyUV34e7uHO	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:16:36-06	2026-02-04 09:16:36-06	\N	t	{}	2026-02-04 07:16:36-06	2026-02-04 07:16:36-06	\N
54	1	EkN1FjtN3twmgOn1CBy4gVX4ZuLM80dCGaPbIFnh	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:31:33-06	2026-02-04 09:31:33-06	\N	t	{}	2026-02-04 07:31:33-06	2026-02-04 07:31:33-06	\N
55	6	Ke50Tl7bcBVEoNXo4GiaTAZWo9qRPV2QGUpEu5r8	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:32:22-06	2026-02-04 09:32:22-06	\N	t	{}	2026-02-04 07:32:22-06	2026-02-04 07:32:22-06	\N
56	7	Y1sfhcFFlLKdCyjfGPYs3RGpVgSjnWRWZHIAeGxU	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:35:29-06	2026-02-04 09:35:29-06	\N	t	{}	2026-02-04 07:35:29-06	2026-02-04 07:35:29-06	\N
57	7	cNhcgMM9JQuPsp7WeTBDh08aCPNzETZczgVZAk36	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:36:14-06	2026-02-04 09:36:14-06	\N	t	{}	2026-02-04 07:36:14-06	2026-02-04 07:36:14-06	\N
58	7	nnR1M15twGRMG5HNClbAbJsZL11RzPVnvjBHSMyP	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:37:23-06	2026-02-04 09:37:23-06	\N	t	{}	2026-02-04 07:37:23-06	2026-02-04 07:37:23-06	\N
59	1	oDZ2wv4V2lDKU40Vw0fvbA820TvHEG45tPiodwTF	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:37:40-06	2026-02-04 09:37:40-06	\N	t	{}	2026-02-04 07:37:40-06	2026-02-04 07:37:40-06	\N
60	4	lPUnCrAPPHM3y8NEXY1iM1paz8xnh1fzorRwQ0pU	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:37:51-06	2026-02-04 09:37:51-06	\N	t	{}	2026-02-04 07:37:51-06	2026-02-04 07:37:51-06	\N
61	7	FwbM522kUHvHGpSESkmw9RydAXtPVF0myUaFrz8v	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:38:01-06	2026-02-04 09:38:01-06	\N	t	{}	2026-02-04 07:38:01-06	2026-02-04 07:38:01-06	\N
62	7	b4o9o4GK6Qyx10Q5ZgGyMijhtdy59s9YMacCWLlq	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:38:21-06	2026-02-04 09:38:21-06	\N	t	{}	2026-02-04 07:38:21-06	2026-02-04 07:38:21-06	\N
63	1	W4aHjmWmQohLpCT5LaIzmJXyLIuKHbHoA6payDpZ	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:39:15-06	2026-02-04 09:39:15-06	\N	t	{}	2026-02-04 07:39:15-06	2026-02-04 07:39:15-06	\N
64	7	lfVEOU67sfsIQRQNtnbQ2Jdn5HAdw3fBIUEDggvT	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:39:48-06	2026-02-04 09:39:48-06	\N	t	{}	2026-02-04 07:39:48-06	2026-02-04 07:39:48-06	\N
65	1	9oOEM5cELwnZJdd1McvcDzFs4UaW5z8L4NKyL3i4	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0	2026-02-04 07:40:27-06	2026-02-04 09:40:27-06	\N	t	{}	2026-02-04 07:40:27-06	2026-02-04 07:40:27-06	\N
\.


--
-- Name: acceso_bitacora_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.acceso_bitacora_id_seq', 270, true);


--
-- Name: actividad_bitacora_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.actividad_bitacora_id_seq', 10, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 16, true);


--
-- Name: permiso_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permiso_id_seq', 8, true);


--
-- Name: rol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.rol_id_seq', 4, true);


--
-- Name: rol_permiso_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.rol_permiso_id_seq', 14, true);


--
-- Name: usuario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuario_id_seq', 7, true);


--
-- Name: usuario_password_reset_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuario_password_reset_id_seq', 1, false);


--
-- Name: usuario_perfil_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuario_perfil_id_seq', 7, true);


--
-- Name: usuario_rol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuario_rol_id_seq', 11, true);


--
-- Name: usuario_sesion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuario_sesion_id_seq', 65, true);


--
-- Name: acceso_bitacora acceso_bitacora_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.acceso_bitacora
    ADD CONSTRAINT acceso_bitacora_pkey PRIMARY KEY (id);


--
-- Name: actividad_bitacora actividad_bitacora_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_bitacora
    ADD CONSTRAINT actividad_bitacora_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: permiso permiso_clave_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permiso
    ADD CONSTRAINT permiso_clave_unique UNIQUE (clave);


--
-- Name: permiso permiso_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permiso
    ADD CONSTRAINT permiso_pkey PRIMARY KEY (id);


--
-- Name: rol rol_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol
    ADD CONSTRAINT rol_nombre_unique UNIQUE (nombre);


--
-- Name: rol_permiso rol_permiso_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_pkey PRIMARY KEY (id);


--
-- Name: rol rol_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol
    ADD CONSTRAINT rol_pkey PRIMARY KEY (id);


--
-- Name: rol_permiso uq_rol_permiso__rol_permiso; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT uq_rol_permiso__rol_permiso UNIQUE (rol_id, permiso_id);


--
-- Name: usuario uq_usuario__email; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT uq_usuario__email UNIQUE (email);


--
-- Name: usuario_password_reset uq_usuario_password_reset__token_hash; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_password_reset
    ADD CONSTRAINT uq_usuario_password_reset__token_hash UNIQUE (token_hash);


--
-- Name: usuario_perfil uq_usuario_perfil__usuario_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_perfil
    ADD CONSTRAINT uq_usuario_perfil__usuario_id UNIQUE (usuario_id);


--
-- Name: usuario_rol uq_usuario_rol__usuario_rol; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT uq_usuario_rol__usuario_rol UNIQUE (usuario_id, rol_id);


--
-- Name: usuario_password_reset usuario_password_reset_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_password_reset
    ADD CONSTRAINT usuario_password_reset_pkey PRIMARY KEY (id);


--
-- Name: usuario_perfil usuario_perfil_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_perfil
    ADD CONSTRAINT usuario_perfil_pkey PRIMARY KEY (id);


--
-- Name: usuario usuario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_pkey PRIMARY KEY (id);


--
-- Name: usuario_rol usuario_rol_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_pkey PRIMARY KEY (id);


--
-- Name: usuario_sesion usuario_sesion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_sesion
    ADD CONSTRAINT usuario_sesion_pkey PRIMARY KEY (id);


--
-- Name: usuario_sesion usuario_sesion_session_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_sesion
    ADD CONSTRAINT usuario_sesion_session_id_unique UNIQUE (session_id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: idx_acceso_bitacora__created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_acceso_bitacora__created_at ON public.acceso_bitacora USING btree (created_at);


--
-- Name: idx_acceso_bitacora__evento; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_acceso_bitacora__evento ON public.acceso_bitacora USING btree (evento);


--
-- Name: idx_acceso_bitacora__exito; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_acceso_bitacora__exito ON public.acceso_bitacora USING btree (exito);


--
-- Name: idx_acceso_bitacora__sesion_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_acceso_bitacora__sesion_id ON public.acceso_bitacora USING btree (sesion_id);


--
-- Name: idx_acceso_bitacora__usuario_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_acceso_bitacora__usuario_id ON public.acceso_bitacora USING btree (usuario_id);


--
-- Name: idx_actividad_bitacora__accion; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_actividad_bitacora__accion ON public.actividad_bitacora USING btree (accion);


--
-- Name: idx_actividad_bitacora__created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_actividad_bitacora__created_at ON public.actividad_bitacora USING btree (created_at);


--
-- Name: idx_actividad_bitacora__modulo; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_actividad_bitacora__modulo ON public.actividad_bitacora USING btree (modulo);


--
-- Name: idx_actividad_bitacora__sesion_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_actividad_bitacora__sesion_id ON public.actividad_bitacora USING btree (sesion_id);


--
-- Name: idx_actividad_bitacora__target_tabla; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_actividad_bitacora__target_tabla ON public.actividad_bitacora USING btree (target_tabla);


--
-- Name: idx_actividad_bitacora__usuario_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_actividad_bitacora__usuario_id ON public.actividad_bitacora USING btree (usuario_id);


--
-- Name: idx_permiso__activo; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_permiso__activo ON public.permiso USING btree (activo);


--
-- Name: idx_rol__activo; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_rol__activo ON public.rol USING btree (activo);


--
-- Name: idx_rol_permiso__permiso_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_rol_permiso__permiso_id ON public.rol_permiso USING btree (permiso_id);


--
-- Name: idx_rol_permiso__rol_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_rol_permiso__rol_id ON public.rol_permiso USING btree (rol_id);


--
-- Name: idx_usuario__bloqueado; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario__bloqueado ON public.usuario USING btree (bloqueado);


--
-- Name: idx_usuario__deleted_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario__deleted_at ON public.usuario USING btree (deleted_at);


--
-- Name: idx_usuario__email; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario__email ON public.usuario USING btree (email);


--
-- Name: idx_usuario__email_verificado; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario__email_verificado ON public.usuario USING btree (email_verificado);


--
-- Name: idx_usuario_password_reset__expira_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario_password_reset__expira_at ON public.usuario_password_reset USING btree (expira_at);


--
-- Name: idx_usuario_password_reset__usuario_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario_password_reset__usuario_id ON public.usuario_password_reset USING btree (usuario_id);


--
-- Name: idx_usuario_perfil__usuario_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario_perfil__usuario_id ON public.usuario_perfil USING btree (usuario_id);


--
-- Name: idx_usuario_rol__rol_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario_rol__rol_id ON public.usuario_rol USING btree (rol_id);


--
-- Name: idx_usuario_rol__usuario_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario_rol__usuario_id ON public.usuario_rol USING btree (usuario_id);


--
-- Name: idx_usuario_sesion__activa; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario_sesion__activa ON public.usuario_sesion USING btree (activa);


--
-- Name: idx_usuario_sesion__session_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario_sesion__session_id ON public.usuario_sesion USING btree (session_id);


--
-- Name: idx_usuario_sesion__usuario_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_usuario_sesion__usuario_id ON public.usuario_sesion USING btree (usuario_id);


--
-- Name: acceso_bitacora trg_acceso_bitacora__set_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_acceso_bitacora__set_updated_at BEFORE UPDATE ON public.acceso_bitacora FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: actividad_bitacora trg_actividad_bitacora__set_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_actividad_bitacora__set_updated_at BEFORE UPDATE ON public.actividad_bitacora FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: permiso trg_permiso__set_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_permiso__set_updated_at BEFORE UPDATE ON public.permiso FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: rol trg_rol__set_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_rol__set_updated_at BEFORE UPDATE ON public.rol FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: rol_permiso trg_rol_permiso__set_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_rol_permiso__set_updated_at BEFORE UPDATE ON public.rol_permiso FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario trg_usuario__set_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_usuario__set_updated_at BEFORE UPDATE ON public.usuario FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_password_reset trg_usuario_password_reset__set_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_usuario_password_reset__set_updated_at BEFORE UPDATE ON public.usuario_password_reset FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_perfil trg_usuario_perfil__set_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_usuario_perfil__set_updated_at BEFORE UPDATE ON public.usuario_perfil FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_rol trg_usuario_rol__set_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_usuario_rol__set_updated_at BEFORE UPDATE ON public.usuario_rol FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuario_sesion trg_usuario_sesion__set_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_usuario_sesion__set_updated_at BEFORE UPDATE ON public.usuario_sesion FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: acceso_bitacora acceso_bitacora_sesion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.acceso_bitacora
    ADD CONSTRAINT acceso_bitacora_sesion_id_foreign FOREIGN KEY (sesion_id) REFERENCES public.usuario_sesion(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: acceso_bitacora acceso_bitacora_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.acceso_bitacora
    ADD CONSTRAINT acceso_bitacora_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: actividad_bitacora actividad_bitacora_sesion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_bitacora
    ADD CONSTRAINT actividad_bitacora_sesion_id_foreign FOREIGN KEY (sesion_id) REFERENCES public.usuario_sesion(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: actividad_bitacora actividad_bitacora_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_bitacora
    ADD CONSTRAINT actividad_bitacora_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: rol_permiso rol_permiso_permiso_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_permiso_id_foreign FOREIGN KEY (permiso_id) REFERENCES public.permiso(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: rol_permiso rol_permiso_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.rol(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_password_reset usuario_password_reset_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_password_reset
    ADD CONSTRAINT usuario_password_reset_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_perfil usuario_perfil_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_perfil
    ADD CONSTRAINT usuario_perfil_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_rol usuario_rol_asignado_por_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_asignado_por_usuario_id_foreign FOREIGN KEY (asignado_por_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: usuario_rol usuario_rol_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.rol(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_rol usuario_rol_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_rol
    ADD CONSTRAINT usuario_rol_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: usuario_sesion usuario_sesion_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_sesion
    ADD CONSTRAINT usuario_sesion_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

\unrestrict A8DYFVLP5fjZETZwXCEZo50vVcpfyKhk0kaRe3OZOdNk3dQ9vSYPeMVCxraiYTU

