--
-- PostgreSQL database dump
--

\restrict nLXhesmxjjSr9PiPzzvkyufiUCXbalyGetSWuM6J8TE7zpC1b5fYpZhgqY3XW1k

-- Dumped from database version 17.5 (6bc9ef8)
-- Dumped by pg_dump version 18.0 (Ubuntu 18.0-1.pgdg24.04+3)

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
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


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
-- Name: orange_money; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.orange_money (
    id uuid NOT NULL,
    numero_telephone character varying(255) NOT NULL,
    nom character varying(255) NOT NULL,
    prenom character varying(255) NOT NULL,
    numero_cni character varying(255) NOT NULL,
    solde numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    id bigint NOT NULL,
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: password_reset_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.password_reset_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: password_reset_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.password_reset_tokens_id_seq OWNED BY public.password_reset_tokens.id;


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name character varying(255) NOT NULL,
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
-- Name: portefeuilles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.portefeuilles (
    id uuid NOT NULL,
    id_utilisateur uuid NOT NULL,
    solde numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    devise character varying(3) DEFAULT 'XOF'::character varying NOT NULL,
    derniere_mise_a_jour timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: qr_codes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.qr_codes (
    id uuid NOT NULL,
    id_marchand uuid,
    id_utilisateur uuid,
    donnees json NOT NULL,
    montant numeric(15,2),
    date_generation timestamp(0) without time zone NOT NULL,
    date_expiration timestamp(0) without time zone NOT NULL,
    utilise boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sessions_ompay; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions_ompay (
    id uuid NOT NULL,
    utilisateur_id uuid NOT NULL,
    token character varying(255) NOT NULL,
    last_activity timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: transactions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.transactions (
    id uuid NOT NULL,
    id_utilisateur uuid NOT NULL,
    type character varying(255) NOT NULL,
    montant numeric(15,2) NOT NULL,
    devise character varying(3) DEFAULT 'XOF'::character varying NOT NULL,
    statut character varying(255) DEFAULT 'en_attente'::character varying NOT NULL,
    frais numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    reference character varying(50) NOT NULL,
    date_transaction timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    numero_telephone_destinataire character varying(255),
    nom_destinataire character varying(255),
    nom_marchand character varying(255),
    categorie_marchand character varying(255),
    note text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT transactions_statut_check CHECK (((statut)::text = ANY ((ARRAY['en_attente'::character varying, 'en_cours'::character varying, 'termine'::character varying, 'echouee'::character varying, 'annulee'::character varying])::text[]))),
    CONSTRAINT transactions_type_check CHECK (((type)::text = ANY ((ARRAY['transfert'::character varying, 'paiement'::character varying])::text[])))
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: utilisateurs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.utilisateurs (
    id uuid NOT NULL,
    numero_telephone character varying(20) NOT NULL,
    prenom character varying(100) NOT NULL,
    nom character varying(100) NOT NULL,
    email character varying(255),
    code_pin character varying(255),
    numero_cni character varying(50),
    statut_kyc character varying(255) DEFAULT 'non_verifie'::character varying NOT NULL,
    biometrie_activee boolean DEFAULT false NOT NULL,
    otp character varying(10),
    otp_expires_at timestamp(0) without time zone,
    jeton_biometrique character varying(255),
    date_creation timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    derniere_connexion timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT utilisateurs_statut_kyc_check CHECK (((statut_kyc)::text = ANY (ARRAY[('non_verifie'::character varying)::text, ('en_cours'::character varying)::text, ('verifie'::character varying)::text, ('rejete'::character varying)::text, ('en_attente_verification'::character varying)::text])))
);


--
-- Name: verification_codes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.verification_codes (
    id uuid NOT NULL,
    numero_telephone character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    expire_at timestamp(0) without time zone NOT NULL,
    used boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: password_reset_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens ALTER COLUMN id SET DEFAULT nextval('public.password_reset_tokens_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: orange_money orange_money_numero_cni_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orange_money
    ADD CONSTRAINT orange_money_numero_cni_unique UNIQUE (numero_cni);


--
-- Name: orange_money orange_money_numero_telephone_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orange_money
    ADD CONSTRAINT orange_money_numero_telephone_unique UNIQUE (numero_telephone);


--
-- Name: orange_money orange_money_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orange_money
    ADD CONSTRAINT orange_money_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (id);


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
-- Name: portefeuilles portefeuilles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.portefeuilles
    ADD CONSTRAINT portefeuilles_pkey PRIMARY KEY (id);


--
-- Name: qr_codes qr_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.qr_codes
    ADD CONSTRAINT qr_codes_pkey PRIMARY KEY (id);


--
-- Name: sessions_ompay sessions_ompay_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions_ompay
    ADD CONSTRAINT sessions_ompay_pkey PRIMARY KEY (id);


--
-- Name: sessions_ompay sessions_ompay_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions_ompay
    ADD CONSTRAINT sessions_ompay_token_unique UNIQUE (token);


--
-- Name: transactions transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_pkey PRIMARY KEY (id);


--
-- Name: transactions transactions_reference_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_reference_unique UNIQUE (reference);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: utilisateurs utilisateurs_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_email_unique UNIQUE (email);


--
-- Name: utilisateurs utilisateurs_numero_cni_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_numero_cni_unique UNIQUE (numero_cni);


--
-- Name: utilisateurs utilisateurs_numero_telephone_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_numero_telephone_unique UNIQUE (numero_telephone);


--
-- Name: utilisateurs utilisateurs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_pkey PRIMARY KEY (id);


--
-- Name: verification_codes verification_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.verification_codes
    ADD CONSTRAINT verification_codes_pkey PRIMARY KEY (id);


--
-- Name: verification_codes verification_codes_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.verification_codes
    ADD CONSTRAINT verification_codes_token_unique UNIQUE (token);


--
-- Name: password_reset_tokens_email_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX password_reset_tokens_email_index ON public.password_reset_tokens USING btree (email);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: portefeuilles_utilisateur_solde_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX portefeuilles_utilisateur_solde_index ON public.portefeuilles USING btree (id_utilisateur, solde);


--
-- Name: qr_codes_date_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX qr_codes_date_expiration_index ON public.qr_codes USING btree (date_expiration);


--
-- Name: qr_codes_id_utilisateur_utilise_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX qr_codes_id_utilisateur_utilise_index ON public.qr_codes USING btree (id_utilisateur, utilise);


--
-- Name: transactions_reference_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX transactions_reference_index ON public.transactions USING btree (reference);


--
-- Name: transactions_type_statut_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX transactions_type_statut_index ON public.transactions USING btree (type, statut);


--
-- Name: transactions_utilisateur_statut_date_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX transactions_utilisateur_statut_date_index ON public.transactions USING btree (id_utilisateur, statut, date_transaction);


--
-- Name: utilisateurs_email_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX utilisateurs_email_index ON public.utilisateurs USING btree (email);


--
-- Name: utilisateurs_numero_statut_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX utilisateurs_numero_statut_index ON public.utilisateurs USING btree (numero_telephone, statut_kyc);


--
-- Name: portefeuilles portefeuilles_id_utilisateur_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.portefeuilles
    ADD CONSTRAINT portefeuilles_id_utilisateur_foreign FOREIGN KEY (id_utilisateur) REFERENCES public.utilisateurs(id) ON DELETE CASCADE;


--
-- Name: transactions transactions_id_utilisateur_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_id_utilisateur_foreign FOREIGN KEY (id_utilisateur) REFERENCES public.utilisateurs(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict nLXhesmxjjSr9PiPzzvkyufiUCXbalyGetSWuM6J8TE7zpC1b5fYpZhgqY3XW1k

--
-- PostgreSQL database dump
--

\restrict ffVd1RIbYziRBQgMgHtYWCBtJGqzkDUsMF9kYg94o6aUbWPHtKYPBJ4QT0piAaE

-- Dumped from database version 17.5 (6bc9ef8)
-- Dumped by pg_dump version 18.0 (Ubuntu 18.0-1.pgdg24.04+3)

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
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2014_10_12_000000_create_users_table	1
2	2014_10_12_100000_create_password_reset_tokens_table	1
3	2019_08_19_000000_create_failed_jobs_table	1
4	2019_12_14_000001_create_personal_access_tokens_table	1
5	2025_11_09_124906_create_utilisateurs_table	1
6	2025_11_09_124942_create_authentifications_table	1
7	2025_11_09_124958_create_parametres_securites_table	1
8	2025_11_09_125017_create_portefeuilles_table	1
9	2025_11_09_125038_create_transactions_table	1
10	2025_11_09_125059_create_destinataires_table	1
11	2025_11_09_125129_create_transferts_table	1
12	2025_11_09_125231_create_marchands_table	1
13	2025_11_09_125301_create_qr_codes_table	1
14	2025_11_09_125322_create_code_paiements_table	1
15	2025_11_09_125432_create_paiements_table	1
16	2025_11_09_125452_create_contacts_table	1
38	2025_11_10_000001_create_orange_money_table	2
39	2025_11_10_000002_drop_unused_tables	2
40	2025_11_10_000003_cleanup_database	2
41	2025_11_10_000004_cleanup_database_postgres	2
42	2025_11_10_000005_update_orange_money_table	2
43	2025_11_10_000006_create_auth_tables	2
44	2025_11_10_000007_recreate_orange_money_table	2
45	2025_11_10_131208_create_qr_codes_table	3
46	2025_11_10_142156_create_portefeuilles_table	4
47	2025_11_10_142307_create_transactions_table	5
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 47, true);


--
-- PostgreSQL database dump complete
--

\unrestrict ffVd1RIbYziRBQgMgHtYWCBtJGqzkDUsMF9kYg94o6aUbWPHtKYPBJ4QT0piAaE

