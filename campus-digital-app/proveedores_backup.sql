--
-- PostgreSQL database dump
--

\restrict SFcuVVJDAeMaTdVyTml8PWIsBgJHF1Y0mg01ovr1Oltc2RlVW6CvggUrxDX9lLd

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

SET default_tablespace = '';

SET default_table_access_method = heap;

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
    CONSTRAINT ck_pedido__estado CHECK (((estado)::text = ANY ((ARRAY['creado'::character varying, 'aceptado'::character varying, 'en_proceso'::character varying, 'listo'::character varying, 'entregado'::character varying, 'cancelado'::character varying])::text[])))
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
    deleted_at timestamp(0) without time zone
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
-- Name: pedido id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido ALTER COLUMN id SET DEFAULT nextval('public.pedido_id_seq'::regclass);


--
-- Name: producto id; Type: DEFAULT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.producto ALTER COLUMN id SET DEFAULT nextval('public.producto_id_seq'::regclass);


--
-- Data for Name: pedido; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.pedido (id, usuario_id, numero_folio, estado, modulo, total, descripcion, notas, operador_usuario_id, confirmado_con_tarjeta, confirmado_at, tarjeta_lectura_id, cobrado_de_saldo, saldo_movimiento_id, meta_json, created_at, updated_at, deleted_at) FROM stdin;
1	41	PED-OF7IMATF	creado	biblioteca	297.38	Pedido en biblioteca		8	f	\N	\N	f	\N	{}	2026-03-04 17:06:27-06	2026-03-04 17:06:27-06	\N
2	17	PED-MFCRCE8A	cancelado	biblioteca	246.24	Pedido en biblioteca		4	f	\N	\N	t	\N	{}	2026-03-15 23:06:27-06	2026-03-15 23:06:27-06	\N
3	12	PED-3IKHSIVZ	listo	biblioteca	201.70	Pedido en biblioteca		5	t	2026-03-05 21:15:27	\N	t	\N	{}	2026-03-05 21:06:27-06	2026-03-05 21:06:27-06	\N
4	7	PED-TU56HUVU	en_proceso	souvenirs	253.94	Pedido en souvenirs		4	f	\N	\N	f	\N	{}	2026-03-22 13:06:27-06	2026-03-22 13:06:27-06	\N
5	15	PED-6EAFPSBW	listo	copias	201.52	Pedido en copias		4	t	2026-03-08 18:23:27	\N	t	\N	{}	2026-03-08 18:06:27-06	2026-03-08 18:06:27-06	\N
6	33	PED-5BBJY1ZA	creado	biblioteca	202.54	Pedido en biblioteca		6	f	\N	\N	t	\N	{}	2026-03-04 20:06:27-06	2026-03-04 20:06:27-06	\N
7	20	PED-PUEX7AD4	aceptado	acceso	26.83	Pedido en acceso		5	f	\N	\N	f	\N	{}	2026-02-22 18:06:27-06	2026-02-22 18:06:27-06	\N
8	36	PED-BU2MJPRG	entregado	copias	142.17	Pedido en copias		8	t	2026-03-04 12:34:27	\N	f	\N	{}	2026-03-04 12:06:27-06	2026-03-04 12:06:27-06	\N
9	33	PED-1BU8XOVR	listo	souvenirs	44.08	Pedido en souvenirs		7	t	2026-03-20 17:28:27	\N	t	\N	{}	2026-03-20 17:06:27-06	2026-03-20 17:06:27-06	\N
10	39	PED-0JSWIY38	entregado	cafeteria	93.76	Pedido en cafeteria		8	t	2026-03-11 17:19:27	\N	f	\N	{}	2026-03-11 17:06:27-06	2026-03-11 17:06:27-06	\N
11	24	PED-QGJCUNOB	listo	copias	211.79	Pedido en copias		4	t	2026-03-03 22:12:27	\N	f	\N	{}	2026-03-03 22:06:27-06	2026-03-03 22:06:27-06	\N
12	15	PED-JPXJWWNN	creado	copias	219.20	Pedido en copias		6	f	\N	\N	t	\N	{}	2026-03-07 11:06:27-06	2026-03-07 11:06:27-06	\N
13	22	PED-LUU26NNJ	cancelado	biblioteca	38.02	Pedido en biblioteca		4	f	\N	\N	t	\N	{}	2026-03-21 18:06:27-06	2026-03-21 18:06:27-06	\N
14	32	PED-GXGF52Y7	creado	copias	112.64	Pedido en copias		7	f	\N	\N	f	\N	{}	2026-02-25 12:06:27-06	2026-02-25 12:06:27-06	\N
15	33	PED-YJBNDMW8	creado	biblioteca	278.80	Pedido en biblioteca		6	f	\N	\N	t	\N	{}	2026-02-24 11:06:27-06	2026-02-24 11:06:27-06	\N
16	25	PED-VIDTSJQH	creado	acceso	197.27	Pedido en acceso		5	f	\N	\N	t	\N	{}	2026-03-05 21:06:27-06	2026-03-05 21:06:27-06	\N
17	32	PED-AGCRVY2V	creado	biblioteca	22.27	Pedido en biblioteca		6	f	\N	\N	f	\N	{}	2026-03-05 19:06:27-06	2026-03-05 19:06:27-06	\N
18	12	PED-08FS1V26	entregado	biblioteca	245.36	Pedido en biblioteca		7	t	2026-03-22 16:13:27	\N	t	\N	{}	2026-03-22 16:06:27-06	2026-03-22 16:06:27-06	\N
19	8	PED-K0AII4RV	creado	copias	292.03	Pedido en copias		7	f	\N	\N	t	\N	{}	2026-03-23 14:06:27-06	2026-03-23 14:06:27-06	\N
20	19	PED-Q8VTNL7T	en_proceso	copias	41.64	Pedido en copias		5	f	\N	\N	f	\N	{}	2026-03-15 16:06:27-06	2026-03-15 16:06:27-06	\N
21	33	PED-YKVHWHIS	aceptado	souvenirs	43.38	Pedido en souvenirs		5	f	\N	\N	t	\N	{}	2026-03-04 17:06:27-06	2026-03-04 17:06:27-06	\N
22	6	PED-M5YJXJIX	entregado	copias	101.41	Pedido en copias		6	t	2026-03-14 16:24:27	\N	f	\N	{}	2026-03-14 16:06:27-06	2026-03-14 16:06:27-06	\N
23	15	PED-JJPYJS2S	entregado	acceso	94.93	Pedido en acceso		4	t	2026-03-02 17:20:27	\N	f	\N	{}	2026-03-02 17:06:27-06	2026-03-02 17:06:27-06	\N
24	30	PED-PSKS0RRP	entregado	biblioteca	55.45	Pedido en biblioteca		7	t	2026-02-25 21:31:27	\N	t	\N	{}	2026-02-25 21:06:27-06	2026-02-25 21:06:27-06	\N
26	16	PED-FF3K66P4	listo	acceso	290.94	Pedido en acceso		4	t	2026-03-14 12:21:27	\N	t	\N	{}	2026-03-14 12:06:27-06	2026-03-14 12:06:27-06	\N
27	13	PED-QJV7PCBD	entregado	souvenirs	138.25	Pedido en souvenirs		8	t	2026-03-04 21:16:27	\N	f	\N	{}	2026-03-04 21:06:27-06	2026-03-04 21:06:27-06	\N
28	34	PED-EFQN9HTB	cancelado	biblioteca	285.86	Pedido en biblioteca		4	f	\N	\N	f	\N	{}	2026-03-08 18:06:27-06	2026-03-08 18:06:27-06	\N
29	15	PED-N1OVFLFE	en_proceso	souvenirs	81.84	Pedido en souvenirs		5	f	\N	\N	f	\N	{}	2026-03-19 17:06:27-06	2026-03-19 17:06:27-06	\N
30	29	PED-G1FAQCZJ	aceptado	copias	201.81	Pedido en copias		4	f	\N	\N	t	\N	{}	2026-03-12 16:06:27-06	2026-03-12 16:06:27-06	\N
31	9	PED-LQNR6T1T	entregado	cafeteria	86.65	Pedido en cafeteria		7	t	2026-03-11 22:26:27	\N	t	\N	{}	2026-03-11 22:06:27-06	2026-03-11 22:06:27-06	\N
32	23	PED-5B6CWIDM	creado	copias	54.01	Pedido en copias		5	f	\N	\N	f	\N	{}	2026-03-12 18:06:27-06	2026-03-12 18:06:27-06	\N
33	6	PED-SSELJA0B	aceptado	cafeteria	286.99	Pedido en cafeteria		4	f	\N	\N	f	\N	{}	2026-03-09 11:06:27-06	2026-03-09 11:06:27-06	\N
34	10	PED-2BMQLNBM	entregado	cafeteria	259.45	Pedido en cafeteria		8	t	2026-03-04 20:34:27	\N	f	\N	{}	2026-03-04 20:06:27-06	2026-03-04 20:06:27-06	\N
35	31	PED-UMZPRYBF	listo	copias	177.64	Pedido en copias		6	t	2026-03-20 11:25:27	\N	f	\N	{}	2026-03-20 11:06:27-06	2026-03-20 11:06:27-06	\N
36	13	PED-WNVZFZOX	creado	copias	113.33	Pedido en copias		5	f	\N	\N	t	\N	{}	2026-02-21 19:06:27-06	2026-02-21 19:06:27-06	\N
38	8	PED-4UWT48HE	creado	acceso	183.65	Pedido en acceso		5	f	\N	\N	f	\N	{}	2026-02-21 21:06:27-06	2026-02-21 21:06:27-06	\N
39	19	PED-UDRC3NGY	entregado	copias	81.37	Pedido en copias		5	t	2026-03-23 12:28:27	\N	f	\N	{}	2026-03-23 12:06:27-06	2026-03-23 12:06:27-06	\N
40	37	PED-IF7WYH1G	entregado	cafeteria	259.83	Pedido en cafeteria		6	t	2026-03-22 20:33:27	\N	f	\N	{}	2026-03-22 20:06:27-06	2026-03-22 20:06:27-06	\N
42	31	PED-CFW2D736	listo	biblioteca	133.84	Pedido en biblioteca		5	t	2026-03-19 11:22:27	\N	f	\N	{}	2026-03-19 11:06:27-06	2026-03-19 11:06:27-06	\N
45	39	PED-AE9EBF04	en_proceso	souvenirs	283.54	Pedido en souvenirs		4	f	\N	\N	f	\N	{}	2026-03-06 21:06:27-06	2026-03-06 21:06:27-06	\N
46	41	PED-N6DNDRG8	cancelado	biblioteca	276.12	Pedido en biblioteca		5	f	\N	\N	f	\N	{}	2026-03-14 12:06:27-06	2026-03-14 12:06:27-06	\N
47	11	PED-OULYGU7H	aceptado	souvenirs	162.97	Pedido en souvenirs		5	f	\N	\N	t	\N	{}	2026-03-05 12:06:27-06	2026-03-05 12:06:27-06	\N
48	10	PED-UFDS0SSL	cancelado	biblioteca	112.71	Pedido en biblioteca		8	f	\N	\N	f	\N	{}	2026-03-21 21:06:27-06	2026-03-21 21:06:27-06	\N
50	26	PED-CFBDYPJ8	en_proceso	copias	111.42	Pedido en copias		4	f	\N	\N	f	\N	{}	2026-03-06 16:06:27-06	2026-03-06 16:06:27-06	\N
51	37	PED-8WSP8N58	aceptado	copias	86.22	Pedido en copias		4	f	\N	\N	t	\N	{}	2026-03-20 12:06:27-06	2026-03-20 12:06:27-06	\N
52	5	PED-EAOBTGCG	listo	copias	70.78	Pedido en copias		8	t	2026-03-07 18:18:27	\N	f	\N	{}	2026-03-07 18:06:27-06	2026-03-07 18:06:27-06	\N
53	10	PED-2LJ7EBID	cancelado	biblioteca	148.07	Pedido en biblioteca		5	f	\N	\N	f	\N	{}	2026-03-08 18:06:27-06	2026-03-08 18:06:27-06	\N
49	33	PED-ASLDXKKJ	aceptado	cafeteria	167.94	Pedido en cafeteria		5	f	\N	\N	t	\N	{}	2026-02-22 19:06:27-06	2026-03-24 17:20:06-06	\N
44	11	PED-XPFAOLCJ	entregado	cafeteria	28.20	Pedido en cafeteria		4	t	2026-03-01 22:13:27	\N	f	\N	{}	2026-03-01 22:06:27-06	2026-03-25 17:33:29-06	\N
37	29	PED-ND4ADVNV	entregado	cafeteria	35.97	Pedido en cafeteria		5	t	2026-03-03 17:26:27	\N	t	\N	{}	2026-03-03 17:06:27-06	2026-03-25 17:36:33-06	\N
41	18	PED-Z8MKIDRK	entregado	cafeteria	33.86	Pedido en cafeteria		8	f	\N	\N	t	\N	{}	2026-03-03 22:06:27-06	2026-03-25 17:51:54-06	\N
43	24	PED-0PMIWGDS	entregado	cafeteria	233.50	Pedido en cafeteria		6	f	\N	\N	f	\N	{}	2026-02-21 23:06:27-06	2026-03-25 17:52:46-06	\N
54	36	PED-UG7MYGLJ	entregado	cafeteria	42.90	Pedido en cafeteria		4	t	2026-03-02 23:31:27	\N	f	\N	{}	2026-03-02 23:06:27-06	2026-03-02 23:06:27-06	\N
56	20	PED-YTIUK4AU	aceptado	biblioteca	188.31	Pedido en biblioteca		4	f	\N	\N	t	\N	{}	2026-03-16 19:06:27-06	2026-03-16 19:06:27-06	\N
57	33	PED-EYQPNQTG	listo	souvenirs	238.23	Pedido en souvenirs		8	t	2026-03-09 15:36:27	\N	f	\N	{}	2026-03-09 15:06:27-06	2026-03-09 15:06:27-06	\N
59	20	PED-ANZBJ7RC	aceptado	copias	120.66	Pedido en copias		7	f	\N	\N	f	\N	{}	2026-02-28 11:06:27-06	2026-02-28 11:06:27-06	\N
60	40	PED-UYDOYYAA	creado	biblioteca	179.58	Pedido en biblioteca		6	f	\N	\N	t	\N	{}	2026-03-06 21:06:27-06	2026-03-06 21:06:27-06	\N
25	6	PED-6C5T3PRL	entregado	cafeteria	55.13	Pedido en cafeteria		8	t	2026-02-24 15:34:27	\N	t	\N	{}	2026-02-24 15:06:27-06	2026-03-24 17:14:58-06	\N
61	1	PED-20260325-0001	entregado	cafeteria	110.00	1 Café Americano, 1 Chilaquiles		\N	f	2026-03-24 09:31:16	\N	f	\N	{}	2026-03-24 08:45:16-06	2026-03-25 23:31:16-06	\N
63	1	DEMO-1774395098-1	entregado	cafeteria	110.00	1 Café Americano, 1 Chilaquiles	Sin cebolla	\N	f	2026-03-24 09:31:38	\N	f	\N	{"demo": true}	2026-03-24 08:45:38-06	\N	\N
64	1	DEMO-1774481498-2	entregado	cafeteria	35.00	1 Café Americano		\N	f	2026-03-25 21:31:38	\N	f	\N	{"demo": true}	2026-03-25 20:31:38-06	\N	\N
66	1	DEMO-1774481498-4	en_proceso	souvenirs	450.00	1 Sudadera Universitaria		\N	f	\N	\N	f	\N	{"demo": true}	2026-03-25 23:11:38-06	\N	\N
55	14	PED-BPZTBE5L	aceptado	cafeteria	128.50	Pedido en cafeteria		5	f	\N	\N	t	\N	{}	2026-03-06 14:06:27-06	2026-03-25 17:32:52-06	\N
67	16	PED-20260325-0004	creado	cafeteria	90.00	3 cafes	con azucar	\N	f	\N	\N	f	\N	{"manual": true, "created_by": 2}	2026-03-25 23:49:37-06	2026-03-25 23:49:37-06	\N
65	1	DEMO-1774481498-3	aceptado	cafeteria	45.00	1 Sándwich de Jamón		\N	f	\N	\N	f	\N	{"demo": true}	2026-03-25 23:21:38-06	2026-03-25 17:51:03-06	\N
58	40	PED-YAVDHYDZ	en_proceso	cafeteria	208.61	Pedido en cafeteria		6	f	\N	\N	f	\N	{}	2026-03-14 16:06:27-06	2026-03-25 17:51:45-06	\N
\.


--
-- Data for Name: producto; Type: TABLE DATA; Schema: public; Owner: campus_user
--

COPY public.producto (id, nombre, descripcion, precio, stock, modulo, activo, imagen_url, created_at, updated_at, deleted_at) FROM stdin;
1	Café Americano	Café de grano recién molido, 12oz.	35.00	50	cafeteria	t	\N	2026-03-25 23:31:16-06	2026-03-25 23:31:16-06	\N
2	Chilaquiles Verdes	Con pollo, crema, queso y cebolla.	75.00	20	cafeteria	t	\N	2026-03-25 23:31:16-06	2026-03-25 23:31:16-06	\N
4	Sudadera Universitaria	Sudadera azul marino con logo bordado. Talla M.	450.00	10	souvenirs	t	\N	2026-03-25 23:31:16-06	2026-03-25 23:31:16-06	\N
5	Termo Metálico	Acero inoxidable, mantiene calor por 12hrs.	280.00	30	souvenirs	t	\N	2026-03-25 23:31:16-06	2026-03-25 23:31:16-06	\N
6	cocacola	cocacola 6oo ml	25.00	90	cafeteria	t	\N	2026-03-25 23:47:07-06	2026-03-25 23:47:07-06	\N
3	Sándwich de Jamón	Pan integral, jamón de pavo, lechuga y tomate.	40.00	15	cafeteria	t	\N	2026-03-25 23:31:16-06	2026-03-25 17:47:22-06	\N
\.


--
-- Name: pedido_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.pedido_id_seq', 67, true);


--
-- Name: producto_id_seq; Type: SEQUENCE SET; Schema: public; Owner: campus_user
--

SELECT pg_catalog.setval('public.producto_id_seq', 6, true);


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
-- Name: producto producto_pkey; Type: CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_pkey PRIMARY KEY (id);


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
-- Name: idx_producto__activo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_producto__activo ON public.producto USING btree (activo);


--
-- Name: idx_producto__modulo; Type: INDEX; Schema: public; Owner: campus_user
--

CREATE INDEX idx_producto__modulo ON public.producto USING btree (modulo);


--
-- Name: pedido trg_pedido__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_pedido__set_updated_at BEFORE UPDATE ON public.pedido FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: producto trg_producto__set_updated_at; Type: TRIGGER; Schema: public; Owner: campus_user
--

CREATE TRIGGER trg_producto__set_updated_at BEFORE UPDATE ON public.producto FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: pedido pedido_operador_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_operador_usuario_id_foreign FOREIGN KEY (operador_usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE SET NULL;


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
-- Name: pedido pedido_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: campus_user
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuario(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

\unrestrict SFcuVVJDAeMaTdVyTml8PWIsBgJHF1Y0mg01ovr1Oltc2RlVW6CvggUrxDX9lLd

