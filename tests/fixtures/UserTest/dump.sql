INSERT INTO roles(id, name, created_at, updated_at) VALUES
(1, 'Admin'),
(2, 'User');

INSERT INTO users(id, name, role_id, email) VALUES
(1, 'Gerhard', 1, 'fidel.kutch@example.com'),
(2, 'Alien', 2, 'alien.west@example.com');
