\connect postgres;

DROP SCHEMA public CASCADE;
CREATE SCHEMA public;

CREATE TABLE "users" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "username" varchar(25) NOT NULL,
  "email" varchar(255) UNIQUE NOT NULL,
  "password" varchar(255) NOT NULL,
  "is_online" BOOLEAN NOT NULL DEFAULT false
);

CREATE TABLE "messages" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "sender_id" integer NOT NULL,
  "content" varchar(500) NOT NULL,
  "chat_id" integer,
  "channel_id" integer
);

CREATE TABLE "chats" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "name" varchar(25)
);

CREATE TABLE "channels" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "server_id" integer NOT NULL,
  "name" varchar(25) NOT NULL
);

CREATE TABLE "messages_users" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "read" BOOLEAN NOT NULL DEFAULT false,
  "user_id" integer NOT NULL,
  "message_id" integer NOT NULL
);

CREATE TABLE "chats_users" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "chat_id" integer NOT NULL,
  "user_id" integer NOT NULL
);

CREATE TABLE "servers" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "name" varchar(25) NOT NULL,
  "owner_id" integer NOT NULL
);

CREATE TABLE "servers_users" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "user_id" integer NOT NULL,
  "server_id" integer NOT NULL
);

ALTER TABLE "messages" ADD FOREIGN KEY ("sender_id") REFERENCES "users" ("id") ON DELETE CASCADE;

ALTER TABLE "messages" ADD FOREIGN KEY ("chat_id") REFERENCES "chats" ("id") ON DELETE CASCADE;

ALTER TABLE "messages_users" ADD FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE;

ALTER TABLE "messages_users" ADD FOREIGN KEY ("message_id") REFERENCES "messages" ("id") ON DELETE CASCADE;

ALTER TABLE "chats_users" ADD FOREIGN KEY ("chat_id") REFERENCES "chats" ("id") ON DELETE CASCADE;

ALTER TABLE "messages" ADD FOREIGN KEY ("channel_id") REFERENCES "channels" ("id") ON DELETE CASCADE;

ALTER TABLE "channels" ADD FOREIGN KEY ("server_id") REFERENCES "servers" ("id") ON DELETE CASCADE;

ALTER TABLE "chats_users" ADD FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE;

ALTER TABLE "servers" ADD FOREIGN KEY ("owner_id") REFERENCES "users" ("id") ON DELETE CASCADE;

ALTER TABLE "servers_users" ADD FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE;

ALTER TABLE "servers_users" ADD FOREIGN KEY ("server_id") REFERENCES "servers" ("id") ON DELETE CASCADE;
