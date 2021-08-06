\connect postgres;

INSERT INTO users (username, email, password) VALUES ('user1', 'user1_email', 'user1_password');
INSERT INTO users (username, email, password) VALUES ('user2', 'user2_email', 'user2_password');

INSERT INTO servers (name, owner_id) VALUES ('server_of_user1', 1);
INSERT INTO servers (name, owner_id) VALUES ('server_of_user2', 2);

INSERT INTO servers_users (user_id, server_id) VALUES (1, 1);
INSERT INTO servers_users (user_id, server_id) VALUES (1, 2);
INSERT INTO servers_users (user_id, server_id) VALUES (2, 2);

INSERT INTO chats (name) VALUES ('chat1');

INSERT INTO chats_users (chat_id, user_id) VALUES (1, 1);
INSERT INTO chats_users (chat_id, user_id) VALUES (1, 2);

INSERT INTO messages (sender_id, content, chat_id) VALUES (1, 'message1_content', 1);

INSERT INTO messages_users (message_id, user_id) VALUES (1, 1);
INSERT INTO messages_users (message_id, user_id, read) VALUES (1, 2, true);
