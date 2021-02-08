-- public.users definition

-- Drop table

-- DROP TABLE public.users;

CREATE TABLE public.users (
	id serial NOT NULL,
	username varchar(20) NULL,
	"password" varchar(100) NULL,
	"token" bpchar(32) NULL,
	url varchar(100) NULL,
	secret bpchar(32) NULL,
	CONSTRAINT users_pk PRIMARY KEY (id)
);


-- public.queue definition

-- Drop table

-- DROP TABLE public.queue;

CREATE TABLE public.queue (
	id serial NOT NULL,
	raw varchar(10000) NULL,
	html varchar(10000) NULL,
	subject varchar(100) NULL,
	id_sender serial NOT NULL,
	recepient varchar(100) NULL,
	CONSTRAINT queue_pk PRIMARY KEY (id),
	CONSTRAINT queue_fk FOREIGN KEY (id_sender) REFERENCES users(id)
);


-- public.sent_mail definition

-- Drop table

-- DROP TABLE public.sent_mail;

CREATE TABLE public.sent_mail (
	id serial NOT NULL,
	raw varchar(10000) NULL,
	html varchar(10000) NULL,
	subject varchar(100) NULL,
	id_sender serial NOT NULL,
	recepient varchar(100) NULL,
	CONSTRAINT sent_mail_pk PRIMARY KEY (id),
	CONSTRAINT sent_mail_fk FOREIGN KEY (id_sender) REFERENCES users(id)
);