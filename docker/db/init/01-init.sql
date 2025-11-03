--
-- PostgreSQL database dump
--

\restrict FGD71a1An9aNolxyfzkHkX1Hr5wgckpEjqMaEwOTNPfGAb2ftbsEu5T8VL4nsrI

-- Dumped from database version 16.10
-- Dumped by pg_dump version 16.10

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

ALTER TABLE IF EXISTS ONLY public.card_booster DROP CONSTRAINT IF EXISTS fk_b86c15dbe7085f09;
ALTER TABLE IF EXISTS ONLY public.card_booster DROP CONSTRAINT IF EXISTS fk_b86c15db4acc9a20;
ALTER TABLE IF EXISTS ONLY public.reset_password_request DROP CONSTRAINT IF EXISTS fk_7ce748aa76ed395;
ALTER TABLE IF EXISTS ONLY public.cards DROP CONSTRAINT IF EXISTS fk_4c258fd10fb0d18;
DROP INDEX IF EXISTS public.uniq_identifier_email;
DROP INDEX IF EXISTS public.idx_b86c15dbe7085f09;
DROP INDEX IF EXISTS public.idx_b86c15db4acc9a20;
DROP INDEX IF EXISTS public.idx_7ce748aa76ed395;
DROP INDEX IF EXISTS public.idx_4c258fd10fb0d18;
ALTER TABLE IF EXISTS ONLY public."user" DROP CONSTRAINT IF EXISTS user_pkey;
ALTER TABLE IF EXISTS ONLY public.user_card DROP CONSTRAINT IF EXISTS user_card_pkey;
ALTER TABLE IF EXISTS ONLY public.sets DROP CONSTRAINT IF EXISTS sets_pkey;
ALTER TABLE IF EXISTS ONLY public.reset_password_request DROP CONSTRAINT IF EXISTS reset_password_request_pkey;
ALTER TABLE IF EXISTS ONLY public.doctrine_migration_versions DROP CONSTRAINT IF EXISTS doctrine_migration_versions_pkey;
ALTER TABLE IF EXISTS ONLY public.cards DROP CONSTRAINT IF EXISTS cards_pkey;
ALTER TABLE IF EXISTS ONLY public.card_booster DROP CONSTRAINT IF EXISTS card_booster_pkey;
ALTER TABLE IF EXISTS ONLY public.boosters DROP CONSTRAINT IF EXISTS boosters_pkey;
DROP SEQUENCE IF EXISTS public.user_id_seq;
DROP TABLE IF EXISTS public.user_card;
DROP TABLE IF EXISTS public."user";
DROP TABLE IF EXISTS public.sets;
DROP SEQUENCE IF EXISTS public.reset_password_request_id_seq;
DROP TABLE IF EXISTS public.reset_password_request;
DROP TABLE IF EXISTS public.doctrine_migration_versions;
DROP SEQUENCE IF EXISTS public.cards_id_seq;
DROP TABLE IF EXISTS public.cards;
DROP TABLE IF EXISTS public.card_booster;
DROP TABLE IF EXISTS public.boosters;
SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: boosters; Type: TABLE; Schema: public; Owner: tcgcard
--

CREATE TABLE public.boosters (
    name character varying(50) NOT NULL,
    logo character varying(255) DEFAULT NULL::character varying,
    artwork_front character varying(255) DEFAULT NULL::character varying,
    artwork_back character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE public.boosters OWNER TO tcgcard;

--
-- Name: card_booster; Type: TABLE; Schema: public; Owner: tcgcard
--

CREATE TABLE public.card_booster (
    card_id character varying(50) NOT NULL,
    booster_name character varying(50) NOT NULL
);


ALTER TABLE public.card_booster OWNER TO tcgcard;

--
-- Name: cards; Type: TABLE; Schema: public; Owner: tcgcard
--

CREATE TABLE public.cards (
    id character varying(50) NOT NULL,
    set_id character varying(20) NOT NULL,
    name character varying(255) NOT NULL,
    supertype character varying(50) DEFAULT NULL::character varying,
    subtypes json,
    hp character varying(10) DEFAULT NULL::character varying,
    types json,
    evolves_from character varying(50) DEFAULT NULL::character varying,
    evolves_to json,
    rules json,
    ancient_trait json,
    abilities json,
    attacks json,
    weaknesses json,
    resistances json,
    retreat_cost json,
    converted_retreat_cost integer,
    number integer,
    artist character varying(255) DEFAULT NULL::character varying,
    rarity character varying(100) DEFAULT NULL::character varying,
    flavor_text text,
    national_pokedex_numbers json,
    legalities json,
    regulation_mark character varying(5) DEFAULT NULL::character varying,
    images json,
    tcgplayer json,
    cardmarket json,
    name_fr character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE public.cards OWNER TO tcgcard;

--
-- Name: cards_id_seq; Type: SEQUENCE; Schema: public; Owner: tcgcard
--

CREATE SEQUENCE public.cards_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cards_id_seq OWNER TO tcgcard;

--
-- Name: doctrine_migration_versions; Type: TABLE; Schema: public; Owner: tcgcard
--

CREATE TABLE public.doctrine_migration_versions (
    version character varying(191) NOT NULL,
    executed_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    execution_time integer
);


ALTER TABLE public.doctrine_migration_versions OWNER TO tcgcard;

--
-- Name: reset_password_request; Type: TABLE; Schema: public; Owner: tcgcard
--

CREATE TABLE public.reset_password_request (
    id integer NOT NULL,
    user_id integer NOT NULL,
    selector character varying(20) NOT NULL,
    hashed_token character varying(100) NOT NULL,
    requested_at timestamp(6) without time zone NOT NULL,
    expires_at timestamp(6) without time zone NOT NULL
);


ALTER TABLE public.reset_password_request OWNER TO tcgcard;

--
-- Name: COLUMN reset_password_request.requested_at; Type: COMMENT; Schema: public; Owner: tcgcard
--

COMMENT ON COLUMN public.reset_password_request.requested_at IS '(DC2Type:datetime_immutable)';


--
-- Name: COLUMN reset_password_request.expires_at; Type: COMMENT; Schema: public; Owner: tcgcard
--

COMMENT ON COLUMN public.reset_password_request.expires_at IS '(DC2Type:datetime_immutable)';


--
-- Name: reset_password_request_id_seq; Type: SEQUENCE; Schema: public; Owner: tcgcard
--

CREATE SEQUENCE public.reset_password_request_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.reset_password_request_id_seq OWNER TO tcgcard;

--
-- Name: sets; Type: TABLE; Schema: public; Owner: tcgcard
--

CREATE TABLE public.sets (
    id character varying(20) NOT NULL,
    name character varying(255) NOT NULL,
    series character varying(100) DEFAULT NULL::character varying,
    printed_total integer,
    total integer,
    legalities json,
    ptcgo_code character varying(20) DEFAULT NULL::character varying,
    release_date date,
    updated_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    images json
);


ALTER TABLE public.sets OWNER TO tcgcard;

--
-- Name: user; Type: TABLE; Schema: public; Owner: tcgcard
--

CREATE TABLE public."user" (
    id integer NOT NULL,
    email character varying(180) NOT NULL,
    roles json NOT NULL,
    password character varying(255) NOT NULL,
    api_token character varying(255) NOT NULL,
    first_name character varying(255) DEFAULT NULL::character varying,
    last_name character varying(255) DEFAULT NULL::character varying,
    updated_at timestamp(6) without time zone NOT NULL,
    created_at timestamp(6) without time zone NOT NULL
);


ALTER TABLE public."user" OWNER TO tcgcard;

--
-- Name: COLUMN "user".updated_at; Type: COMMENT; Schema: public; Owner: tcgcard
--

COMMENT ON COLUMN public."user".updated_at IS '(DC2Type:datetime_immutable)';


--
-- Name: COLUMN "user".created_at; Type: COMMENT; Schema: public; Owner: tcgcard
--

COMMENT ON COLUMN public."user".created_at IS '(DC2Type:datetime_immutable)';


--
-- Name: user_card; Type: TABLE; Schema: public; Owner: tcgcard
--

CREATE TABLE public.user_card (
    user_id integer NOT NULL,
    card_id integer NOT NULL,
    languages json
);


ALTER TABLE public.user_card OWNER TO tcgcard;

--
-- Name: user_id_seq; Type: SEQUENCE; Schema: public; Owner: tcgcard
--

CREATE SEQUENCE public.user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_id_seq OWNER TO tcgcard;

--
-- Data for Name: boosters; Type: TABLE DATA; Schema: public; Owner: tcgcard
--

COPY public.boosters (name, logo, artwork_front, artwork_back) FROM stdin;
\.


--
-- Data for Name: card_booster; Type: TABLE DATA; Schema: public; Owner: tcgcard
--

COPY public.card_booster (card_id, booster_name) FROM stdin;
\.


--
-- Data for Name: cards; Type: TABLE DATA; Schema: public; Owner: tcgcard
--

COPY public.cards (id, set_id, name, supertype, subtypes, hp, types, evolves_from, evolves_to, rules, ancient_trait, abilities, attacks, weaknesses, resistances, retreat_cost, converted_retreat_cost, number, artist, rarity, flavor_text, national_pokedex_numbers, legalities, regulation_mark, images, tcgplayer, cardmarket, name_fr) FROM stdin;
\.


--
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: tcgcard
--

COPY public.doctrine_migration_versions (version, executed_at, execution_time) FROM stdin;
DoctrineMigrations\\Version20250702223256	2025-07-03 00:33:06	42
DoctrineMigrations\\Version20250702224812	2025-07-03 00:48:23	5
\.


--
-- Data for Name: reset_password_request; Type: TABLE DATA; Schema: public; Owner: tcgcard
--

COPY public.reset_password_request (id, user_id, selector, hashed_token, requested_at, expires_at) FROM stdin;
\.


--
-- Data for Name: sets; Type: TABLE DATA; Schema: public; Owner: tcgcard
--

COPY public.sets (id, name, series, printed_total, total, legalities, ptcgo_code, release_date, updated_at, images) FROM stdin;
\.


--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: tcgcard
--

COPY public."user" (id, email, roles, password, api_token, first_name, last_name, updated_at, created_at) FROM stdin;
69	nmenard@yahoo.fr	[]	HYSW3uCOfI]f	cf4c970c3cc95c1917b94d0a2431364738dab89fa6b51b3111a0bb94130d0d39917f9d4c0d79f6a0e57822ecdeb93e38975258f3621779d93334d1ad	Cécile	Henry	2025-11-03 12:56:26.878104	2025-11-03 12:56:26.878103
53	thierry12@perrier.com	[]	/;>YQ.E!-?i0!}5M*~]	f2cf2cb34084c3ca6299fbfe27f51e3c8f3b6e098d4589f235ebba853b219e6be97ed38ab1cf034c6090e54ccbd1d952ff1cc76014782eac3c6359a2	Laurent	Fouquet	2025-11-03 12:56:26.848541	2025-11-03 12:56:26.848538
54	celine.regnier@jacquet.com	[]	rD>pkb8J>	c70869667b31ff86e1f0876315629cc8902ef5514c01385c9cef23e168dcde20b940fd71ce99942607f1dc1263a889a99a8f1d42d9de6bce2bfc1411	Alix	Maillet	2025-11-03 12:56:26.866226	2025-11-03 12:56:26.866223
55	hamel.marguerite@delannoy.fr	[]	7WYE?^.;w59yuI,E'ZM	5a8a4da35a0445ca01d6fd3c9cf4aadb932c6d80e6ef806d981fe5b15c36d1001e4066566e691c8231b022c5333ac0b1adb4c61a6ebef748e281c191	Arthur	Marion	2025-11-03 12:56:26.86743	2025-11-03 12:56:26.867429
56	jules.ferrand@live.com	[]	5%/8Xk>aPu	784ff52d911b387625a2df7e006d89a2376fc1737d3309b3c18e16515e3900bfee02b3d4f8b0b1f8ad6a036f6264473f5e17c30e6afd3a302b686753	Susan	Marin	2025-11-03 12:56:26.868263	2025-11-03 12:56:26.868262
57	theophile34@dbmail.com	[]	I-gJFR`1(r	80b52ad5a1f91f5d82fc7ef1c0aeb3e9b22b0f28c01053b9f4602a8c6d1f13a54a8f124ef7bcf121591741972bb36f767dccc547f9be6f7154f6aa2f	Madeleine	Martins	2025-11-03 12:56:26.869466	2025-11-03 12:56:26.869465
58	maubert@live.com	[]	mQg65wCA%Om9	8f68dae9078c70e10acf57fd0e6ff707268347eb3bee232c04e4fc93d639b20cb6e5fb01ea92be106a804ba9feb71b6e89a08dca5332b9544d456d6f	Arthur	Mallet	2025-11-03 12:56:26.870213	2025-11-03 12:56:26.870212
59	diallo.philippe@schneider.net	[]	|Y|8Q79;#JE;tJ4s	bb86120e21e1b827967e1f6efa32245ce96fa24847770f6fb51c302647c06fd244f4b04e359ee9392b3c6e8f78550853093bf7c8118c46e87db19e70	William	Berger	2025-11-03 12:56:26.870905	2025-11-03 12:56:26.870904
60	fernandes.leon@dumont.com	[]	yb5bdv@fW7u6[wuIm	8e10b60871eeb6e38e2c85b983eb51ce478fc431f1a63cc99731214af2c5565824f7e5bea6aa226acb50b273e0d25e88558f477197aec2eecb72d8be	Joseph	Martins	2025-11-03 12:56:26.871942	2025-11-03 12:56:26.871941
61	cbouvier@gomez.fr	[]	,KE5,z{5aO+^b6O;	f67c741b3c50a672405a8a0012a40fe3257eabf6011772210ee6216eef4ab3983452e6418d0d8d9c287d23f248ff01d7e83552d019cfef993c0558f9	Daniel	Bourgeois	2025-11-03 12:56:26.872669	2025-11-03 12:56:26.872668
62	jbrun@pires.org	[]	0_>wn3e!pTiu}]	e5c6d887109c9f9e1db6d33ba365793a62faff772d66e51b51f1acde82fe1b228cd5c9f70e31ee9db85a2c3579a272d1a3c1678796228ad1514e7934	Jacqueline	Moreno	2025-11-03 12:56:26.873334	2025-11-03 12:56:26.873333
63	martine.garnier@bernier.fr	[]	\\J!0zc6qrLICf=	d322a1b3b32ba28af49a4df2fe3659e26d68e90f5276ee9a5c60f2e3c638272a33e1e4aadd2a378bc8b2265bab6ef728682b5e55ad969f9d07481489	Vincent	Dufour	2025-11-03 12:56:26.873991	2025-11-03 12:56:26.87399
64	gpinto@pinto.fr	[]	rN";dc\\cvIQaA"v	e248681f94439b7f8b5898edce181f79f4a059cc31b1d0e8e885c5fc755aa4c1457ed29dca5172f23292a3abbb4a98c47d2570bdeafa61f5d8f9634e	Daniel	Monnier	2025-11-03 12:56:26.874694	2025-11-03 12:56:26.874693
65	abreton@guyon.com	[]	S62<?&5QX0<NX	a2a1b0b5063d20cb9f081cf91f02383fa9a97967a5ad61df13de52cb8a1ec6dab21fb3ec799b3f313be3cba9e4def7f2e1fc0f8bceecee83766a54b6	Danielle	Guyot	2025-11-03 12:56:26.875395	2025-11-03 12:56:26.875394
66	lorraine84@hotmail.fr	[]	Ay)z:8O.uf'xE	4f657a022bc41d9cbf82a124178b7d8655bcfc31eec113324bffcfde852d7ad48b7cd04fc0c338c38edffdb0063eb4db7952f2ebf00ed4219b8e8a38	Émile	Rolland	2025-11-03 12:56:26.876054	2025-11-03 12:56:26.876053
67	zribeiro@tele2.fr	[]	z#sV)'%	2c8880f527a6be2e887155c56f322b6548358b7a8651a00c2dfba5a677b49c89ef60c1129be1878979a4b328507f1fa26e158f1274be9fa1d3ac121b	Hélène	Dufour	2025-11-03 12:56:26.876764	2025-11-03 12:56:26.876763
68	gguichard@blot.com	[]	OAT$R.A^T_qz	ac794e6e5397102e9293c1b98836a8d4981cbf8e49009accb9c87a5fc2d14b314a9648010e93889e2a19a76eb97a8a617498e7055c2642149bfe77de	Luce	Regnier	2025-11-03 12:56:26.877419	2025-11-03 12:56:26.877418
70	qcarpentier@maurice.fr	[]	`OhRRb_c']$t',1F,"'	fe2b348d047c65661e81a268da51578cb735d2201349c3bb3999c065bde6e5e6f3562aece8a3c03cf9dfbacd778ac92cc4df30785298fd48bef93070	Bertrand	Maury	2025-11-03 12:56:26.879042	2025-11-03 12:56:26.879039
71	nicole.leleu@sfr.fr	[]	O}~PFWqCG2^]'9P]ZR/	74a95aeb54dd8bb82664d8378fb749ae9c2f2a1a5fa4448cef0a73c922a875fe53fdc3b9a035e87c27294f46dc8a75b32ef2496e22566cf2c4fee8cb	Margaret	Pires	2025-11-03 12:56:26.881016	2025-11-03 12:56:26.881015
72	xperez@club-internet.fr	[]	Ko#-Ul]7R'	50ccdd7877d6a081855c5a822a2e82e7edbda0007ed7fd8620c15615d411e152976c0655ebb6e99b9dc1b7e26f67acb46a88634aa5790898a6bce19e	Lorraine	Marty	2025-11-03 12:56:26.881748	2025-11-03 12:56:26.881747
73	dleclerc@dijoux.org	[]	}U)h5q1pMw)>8MfUn%^n	fdee579dc6849898d493ce9db5221b48eb1241559ad19c87acb6b48532ec2a80105e01657bee86768f89dbfc2134ce9f1e1ae28a3086bee3503f10ec	Odette	Colas	2025-11-03 12:56:26.882431	2025-11-03 12:56:26.88243
74	voisin.michelle@live.com	[]	MN{\\R3	132d5a9874aa8e29299a4eab5f2818381306be1c131003e3103c0268630e9bc6f361c82e34f9066dae3b7a70722e7763d5622e5349a9f898839b18a2	Gabriel	Jourdan	2025-11-03 12:56:26.8831	2025-11-03 12:56:26.8831
75	hmeyer@bodin.com	[]	[1<q#7liz&!DT	3fcd349e361487a9c4b6fd700129c43d7476841bf391a9447506c96ecef50caa26061eb0e0e8007803f89e4876284e40ff16489ada3906134cd1fab4	Dorothée	Boulanger	2025-11-03 12:56:26.883761	2025-11-03 12:56:26.88376
76	marianne70@leger.org	[]	KX2IECu[-p;	0e07af64690f48660cbf16dd5d3d215df2eb959a1d82ac8e47ca7823e8161a1c49419267ca6a7caaaf873277a84a048fa773da0a6516169ab92d4f4b	Éric	Faivre	2025-11-03 12:56:26.884438	2025-11-03 12:56:26.884438
77	charlotte85@lelievre.net	[]	k}W,O:Cd$!t(COf?=	e1f1a9312c13a42abb3d7d7ffbbd10e7eadfbe5097d08715453161f772dd69200aa8d41a067e5d76971c8bd30b02848942dd09042d6c1168d185befd	Suzanne	Georges	2025-11-03 12:56:26.885129	2025-11-03 12:56:26.885129
78	clemence66@dossantos.org	[]	JKJLh;zEX~qjy!~pV5&	4c944b629e533756f5e8bb56af1d8abb6ffc390e9a5eed80496425fb6ae456498efd38b8645011b047286a142cd2b9b02811c013b6499ca0a99705c7	Yves	Lemonnier	2025-11-03 12:56:26.8865	2025-11-03 12:56:26.886499
79	proche@tele2.fr	[]	DrH/`|S5JQfa,2ur	af90a5974ddd737468bcb81f40b84e3371086159d2486e899ae7353e6f9f40bb808bb80223913b77b35ba9807466f9d0b78509d83c76875bd8d65541	Guy	Dias	2025-11-03 12:56:26.887211	2025-11-03 12:56:26.88721
80	pguillon@noos.fr	[]	8IpJ5;z5YiNV{_'J\\8=	4775dbf72081daefdd5623e7aabf1e21232a37ec22c55725c72befc454a6fe05b9ecec419210ea1295141dd915e095d6f2a874f236af1f3c48aad255	Laetitia	Renaud	2025-11-03 12:56:26.887932	2025-11-03 12:56:26.887931
81	michel.guillou@lefevre.com	[]	]3Of|J#l{E	bf27e4b99b154fd07e60aa3d43313c5fe8b52a078e8f0a85702c1a280ec9837389684c12fea83fcf84d476cfc5f0db4b15cb20fa4f71499dca0402be	Léon	Prevost	2025-11-03 12:56:26.888634	2025-11-03 12:56:26.888633
82	dufour.nicolas@wanadoo.fr	[]	F&>(Nw	13ba8f1d75ba557dc5145c400fd8f9e93c416d32680d8df10061fb1334e86485febce8c60e06f89725f42f4edfa91eebf41cbc19b1fc92914599614d	Hugues	Bouvet	2025-11-03 12:56:26.88931	2025-11-03 12:56:26.889309
83	blanc.andree@navarro.com	[]	G?yS)Pg	62b1e0edf8e6aa88cc6325eef2ae30341e689c31895609708a4659c071dd502120afa2f89be5e7c86dd34acf11e59a8021f82b388443c5a94c3496fc	Jean	Pasquier	2025-11-03 12:56:26.890348	2025-11-03 12:56:26.890347
84	josephine.buisson@rossi.com	[]	h;3A10F|tMZ	59e402569e7a805ad7b6bb9ad735af36c3bb77da0b915f0445af147c4dd747da534e37925a6a945f8d5110a6eb413eaced31fdf65ae3d2fefc849992	Émile	Guyot	2025-11-03 12:56:26.891375	2025-11-03 12:56:26.891374
85	pottier.therese@leroy.fr	[]	0YXu(M[	2ae58aea191ad8bca176c5b762da589d7a6435c9dbd12a441915600234c6781a8b6a713464cda3b0abc5bef53c90531b69c9e57a3e8c9d0f1efafc25	Thérèse	Joseph	2025-11-03 12:56:26.892383	2025-11-03 12:56:26.892382
86	thierry.lefebvre@dbmail.com	[]	;$(C7-+cB,mM>GR?_pb	72fc3bb69a3a0ad7479a3a09961efc3e403423c1732542bbce76078e3667b59d4c91f75a13733699122465896e2cbd49474116fd834d8d13d803678a	Camille	Huet	2025-11-03 12:56:26.893063	2025-11-03 12:56:26.893062
87	boutin.auguste@bazin.fr	[]	eJJ_M]b7lQ;`'C9	b830c61b01aaef08ebffccde13aea7ed9044c5fe3e46109b5bfc8ef5cc2d0f8b116f6ec86cf6d926422f31b42b526b892f87d1fe8f1a483130a3253d	Hélène	Guyot	2025-11-03 12:56:26.893811	2025-11-03 12:56:26.89381
88	josette.guillon@gmail.com	[]	hz[$BdS;3	8114f75999673d58e7b89f5269c7e5a8c95280adec7ac4989182d629ff0730523148710667c4048c464eb7ec667d0600813f23ffcb7fdf717ca5c280	André	Guichard	2025-11-03 12:56:26.894514	2025-11-03 12:56:26.894513
89	aleclercq@bonnin.fr	[]	%?jL/dXf	7459624892ea9eb6d9f28cd6d6a4bfcf6a7035183d0dad9620861f0d3d358ad4fe3624b8987cbe68d5f225e23e1a086bb49c221e04aad5c030f9f3c3	Maurice	Jourdan	2025-11-03 12:56:26.895164	2025-11-03 12:56:26.895164
90	dpoirier@club-internet.fr	[]	bDT-]@RMM,	9de72728d48cab28fd5035f3b4818a0bf6a71437a073e68494be1a3a107597c2b7e96f0c5355c7000ad8d9bada87468f6238a73c18f097848a8418bb	Frédérique	Etienne	2025-11-03 12:56:26.895813	2025-11-03 12:56:26.895812
91	kdacosta@hotmail.fr	[]	;wm"9o@sw!w_Q4}i?PM3	e5b304c302f9c6d7aaefef42573a7c3103b7ac4917880ec636b2a551ca4ce872eadbb6601f7becfe5de4e5a805387489fff69da28c42b50b1d2d31c0	Laure	Martins	2025-11-03 12:56:26.896762	2025-11-03 12:56:26.896761
92	mary.margot@costa.org	[]	$pY`ZYY	d9e63e4df0ef6f7ef92289501805639f83274f8cacdf1449e6a94d5cf8b6c0379e3cd16f70c6e2c1b0f73913fe6d2fe0206430846b3dd62d89661553	Anouk	Mahe	2025-11-03 12:56:26.897453	2025-11-03 12:56:26.897453
93	oceane26@hotmail.fr	[]	^r{p;lUrv84W%"]dO	79f56fdab8b231aec0b4698102d72f999a0463a6fc92b48cede526fb2284cba2fc92f8f88cad2b2a249157b439c6876d4458267f395e4c934ef76c83	Alix	Noel	2025-11-03 12:56:26.89853	2025-11-03 12:56:26.898529
94	amoulin@paul.fr	[]	t-4MV[CE"EcyQIe	c4f72dcab436aa2b4d36dffac168537e58a0c87558710f38d7d3e3cf1bb9509567e9547c2d5ac936c69b020e319c1644261c408370d27634f3bd2f1f	Roland	Marion	2025-11-03 12:56:26.899232	2025-11-03 12:56:26.899231
95	fguillon@jacquot.org	[]	;"_5:Q{b`R6jBO"V*A	527962b0e4a3d85819ed07dc2ac7e13e0f816478ed95a62f85053c87bfbb1d687618b831e7b271c449b86e4b11f59363487cd028e3f0b060649e7815	Guillaume	Duval	2025-11-03 12:56:26.899924	2025-11-03 12:56:26.899923
96	iperrin@noos.fr	[]	&|?X"Wk/R0[O1	5cffa40734b8ec49f46948f5e133fd67786378056b26119294a17c03264bbd3213d7f7643ad12a85d89ac9ae3a057183ff4a95c66ef235167ffab61c	Thibaut	Aubert	2025-11-03 12:56:26.900589	2025-11-03 12:56:26.900588
97	therve@rocher.com	[]	pVM[c7uV	933810fb7e8f51a307fc3590a56cccbbe3e3fdf29ff3ab8e442370788ce09d38eeb17ff702ebeedfd5ca0c46f5fc8b1928abde80c0d121795a4794e8	Andrée	Perez	2025-11-03 12:56:26.901266	2025-11-03 12:56:26.901265
98	eric90@delahaye.com	[]	8B[~Suh#l	d019c31046062d67110c9cd7cdda0cd6c2f21934933fbff00ab48887739ddcd607a9001cea174070c4b416078a909a9b4ffd6c625059d39398b29ca9	Margaux	Julien	2025-11-03 12:56:26.902262	2025-11-03 12:56:26.902261
99	tanguy.luce@sfr.fr	[]	}we-O[a744	b7df4f39363e2ac28737f8ace38f5b2ed91ad90312804bf2b9ba5b899780de216b837fdb3f2eaddce6a9e13a41b4821a598e9ad9d81cac1d8e3b5b66	Suzanne	Poirier	2025-11-03 12:56:26.902903	2025-11-03 12:56:26.902902
100	alice.ruiz@ramos.net	[]	m_P`TZY	128ff2f886a9f9edf0ffe1b425dc612e026b54539e73446092788d5b2826b24cd85c5f9a679ea31f27a698db57017bc01338588283bdf5e60e887d75	Alex	Julien	2025-11-03 12:56:26.903551	2025-11-03 12:56:26.90355
101	tmarchand@live.com	[]	Tb?0!"Vh&^Z}A7?e6%U$	accca04a1ba9c33144d76ca9efa53834685390951fbd2a0ecf61eae96a9690e487f7018a3e13dcb6514ac51560b14c6d4953d340761ef83365ae6992	Astrid	Bailly	2025-11-03 12:56:26.904199	2025-11-03 12:56:26.904198
102	moreno.patrick@roche.com	[]	/!q}">/[-+	373d2ef7d90e0788005a60273d85e2ad33f01bd9f4512a6c9e1066e2c66b5af545414be78125b09b44e251a5b554e706d555e91ff412ad8a81061572	Gilles	Petit	2025-11-03 12:56:26.90486	2025-11-03 12:56:26.904859
103	valentin.bissay@gmail.com	["ROLE_ADMIN"]	$2y$13$aam4PY7ZHW9d6IB.4lUtQON1jD/dGHCt7Znv5N7m1WH//Gk4HtGNm	53b87f2798996bac9307220357259fccdb5cde7ab96eb3b83c11adeae3575a111f38332a6c3a9fdf5925d8ba96d34a4bc0a1ddd80ac08f173cd728f9	Valentin	Bissay	2025-11-03 12:56:27.479236	2025-11-03 12:56:27.479233
104	test@test.com	[]	$2y$13$NK.csrRAY33CiFyjmFRc0eH8EUQc6ijqlmIrx.OmubyRbKJri50MC	509222090c50c9f6dee53b9abcf78a50f1257d0869b8bcd75722dfee9154a03dab50020bd06304a0021711c7d691d511c9a4d0b6671a8d6bc9891165	Test	Test	2025-11-03 12:56:27.938437	2025-11-03 12:56:27.938433
\.


--
-- Data for Name: user_card; Type: TABLE DATA; Schema: public; Owner: tcgcard
--

COPY public.user_card (user_id, card_id, languages) FROM stdin;
\.


--
-- Name: cards_id_seq; Type: SEQUENCE SET; Schema: public; Owner: tcgcard
--

SELECT pg_catalog.setval('public.cards_id_seq', 250, true);


--
-- Name: reset_password_request_id_seq; Type: SEQUENCE SET; Schema: public; Owner: tcgcard
--

SELECT pg_catalog.setval('public.reset_password_request_id_seq', 1, false);


--
-- Name: user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: tcgcard
--

SELECT pg_catalog.setval('public.user_id_seq', 104, true);


--
-- Name: boosters boosters_pkey; Type: CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public.boosters
    ADD CONSTRAINT boosters_pkey PRIMARY KEY (name);


--
-- Name: card_booster card_booster_pkey; Type: CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public.card_booster
    ADD CONSTRAINT card_booster_pkey PRIMARY KEY (card_id, booster_name);


--
-- Name: cards cards_pkey; Type: CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public.cards
    ADD CONSTRAINT cards_pkey PRIMARY KEY (id);


--
-- Name: doctrine_migration_versions doctrine_migration_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public.doctrine_migration_versions
    ADD CONSTRAINT doctrine_migration_versions_pkey PRIMARY KEY (version);


--
-- Name: reset_password_request reset_password_request_pkey; Type: CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public.reset_password_request
    ADD CONSTRAINT reset_password_request_pkey PRIMARY KEY (id);


--
-- Name: sets sets_pkey; Type: CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public.sets
    ADD CONSTRAINT sets_pkey PRIMARY KEY (id);


--
-- Name: user_card user_card_pkey; Type: CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public.user_card
    ADD CONSTRAINT user_card_pkey PRIMARY KEY (user_id, card_id);


--
-- Name: user user_pkey; Type: CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- Name: idx_4c258fd10fb0d18; Type: INDEX; Schema: public; Owner: tcgcard
--

CREATE INDEX idx_4c258fd10fb0d18 ON public.cards USING btree (set_id);


--
-- Name: idx_7ce748aa76ed395; Type: INDEX; Schema: public; Owner: tcgcard
--

CREATE INDEX idx_7ce748aa76ed395 ON public.reset_password_request USING btree (user_id);


--
-- Name: idx_b86c15db4acc9a20; Type: INDEX; Schema: public; Owner: tcgcard
--

CREATE INDEX idx_b86c15db4acc9a20 ON public.card_booster USING btree (card_id);


--
-- Name: idx_b86c15dbe7085f09; Type: INDEX; Schema: public; Owner: tcgcard
--

CREATE INDEX idx_b86c15dbe7085f09 ON public.card_booster USING btree (booster_name);


--
-- Name: uniq_identifier_email; Type: INDEX; Schema: public; Owner: tcgcard
--

CREATE UNIQUE INDEX uniq_identifier_email ON public."user" USING btree (email);


--
-- Name: cards fk_4c258fd10fb0d18; Type: FK CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public.cards
    ADD CONSTRAINT fk_4c258fd10fb0d18 FOREIGN KEY (set_id) REFERENCES public.sets(id);


--
-- Name: reset_password_request fk_7ce748aa76ed395; Type: FK CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public.reset_password_request
    ADD CONSTRAINT fk_7ce748aa76ed395 FOREIGN KEY (user_id) REFERENCES public."user"(id);


--
-- Name: card_booster fk_b86c15db4acc9a20; Type: FK CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public.card_booster
    ADD CONSTRAINT fk_b86c15db4acc9a20 FOREIGN KEY (card_id) REFERENCES public.cards(id);


--
-- Name: card_booster fk_b86c15dbe7085f09; Type: FK CONSTRAINT; Schema: public; Owner: tcgcard
--

ALTER TABLE ONLY public.card_booster
    ADD CONSTRAINT fk_b86c15dbe7085f09 FOREIGN KEY (booster_name) REFERENCES public.boosters(name);


--
-- PostgreSQL database dump complete
--

\unrestrict FGD71a1An9aNolxyfzkHkX1Hr5wgckpEjqMaEwOTNPfGAb2ftbsEu5T8VL4nsrI

