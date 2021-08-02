\connect postgres;

CREATE TABLE users (
	id serial NOT NULL,
	username varchar(25) NOT NULL,
	email varchar(255) NOT NULL UNIQUE,
	password varchar(255) NOT NULL,
	is_online BOOLEAN NOT NULL DEFAULT false,
	CONSTRAINT users_pk PRIMARY KEY (id)
) WITH (
  OIDS=FALSE
);



CREATE TABLE messages (
	id serial NOT NULL,
	user_id integer NOT NULL,
	content varchar(500) NOT NULL,
	chat_id integer NOT NULL,
	CONSTRAINT messages_pk PRIMARY KEY (id)
) WITH (
  OIDS=FALSE
);



CREATE TABLE chats (
	id serial NOT NULL,
	server_id integer,
	name varchar(25) NOT NULL,
	CONSTRAINT chats_pk PRIMARY KEY (id)
) WITH (
  OIDS=FALSE
);



CREATE TABLE messages_users (
	id serial NOT NULL,
	read BOOLEAN NOT NULL,
	user_id integer NOT NULL,
	message_id integer NOT NULL,
	CONSTRAINT messages_users_pk PRIMARY KEY (id)
) WITH (
  OIDS=FALSE
);



CREATE TABLE chats_users (
	id serial NOT NULL,
	chat_id integer NOT NULL,
	user_id integer NOT NULL,
	CONSTRAINT chats_users_pk PRIMARY KEY (id)
) WITH (
  OIDS=FALSE
);



CREATE TABLE servers (
	id serial NOT NULL,
	name varchar(25) NOT NULL,
	user_id integer NOT NULL,
	CONSTRAINT servers_pk PRIMARY KEY (id)
) WITH (
  OIDS=FALSE
);



CREATE TABLE servers_users (
	id serial NOT NULL,
	user_id integer NOT NULL,
	server_id integer NOT NULL,
	CONSTRAINT servers_users_pk PRIMARY KEY (id)
) WITH (
  OIDS=FALSE
);




ALTER TABLE messages ADD CONSTRAINT messages_fk0 FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE messages ADD CONSTRAINT messages_fk1 FOREIGN KEY (chat_id) REFERENCES chats(id);

ALTER TABLE chats ADD CONSTRAINT chats_fk0 FOREIGN KEY (server_id) REFERENCES servers(id);

ALTER TABLE messages_users ADD CONSTRAINT messages_users_fk0 FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE messages_users ADD CONSTRAINT messages_users_fk1 FOREIGN KEY (message_id) REFERENCES messages(id);

ALTER TABLE chats_users ADD CONSTRAINT chats_users_fk0 FOREIGN KEY (chat_id) REFERENCES chats(id);
ALTER TABLE chats_users ADD CONSTRAINT chats_users_fk1 FOREIGN KEY (user_id) REFERENCES users(id);

ALTER TABLE servers ADD CONSTRAINT servers_fk0 FOREIGN KEY (user_id) REFERENCES users(id);

ALTER TABLE servers_users ADD CONSTRAINT servers_users_fk0 FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE servers_users ADD CONSTRAINT servers_users_fk1 FOREIGN KEY (server_id) REFERENCES servers(id);








