INSERT INTO roles(id, name) VALUES
(1, 'Admin'),
(2, 'User');

INSERT INTO users(id, name, role_id, email, password) VALUES
(1, 'Gerhard', 1, 'fidel.kutch@example.com', 'asd'),
(2, 'Alien', 2, 'alien.west@example.com', 'asd');
