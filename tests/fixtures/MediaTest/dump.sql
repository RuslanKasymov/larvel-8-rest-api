INSERT INTO users(id, name, role_id, email, password) VALUES
(1, 'Gerhard', 1, 'fidel.kutch@example.com', 'asd'),
(2, 'Alien', 2, 'alien.west@example.com', 'asd');

INSERT INTO media(name, owner_id, is_public, link, filepath, created_at, updated_at) VALUES
  ('Product main photo', 1 , true, 'http://localhost/test.jpg', '/test.jpg', '2016-10-20 11:05:00', '2016-10-20 11:05:00'),
  ('Category Photo photo', 1, false, 'http://localhost/test1.jpg', '/test1.jpg', '2016-10-20 11:05:00', '2016-10-20 11:05:00'),
  ('Deleted photo', 2, true, 'http://localhost/test3.jpg', '/test.3jpg', '2016-10-20 11:05:00', '2016-10-20 11:05:00'),
  ('Photo', 2, true, 'http://localhost/test4.jpg', '/test4.jpg', '2016-10-20 11:05:00', '2016-10-20 11:05:00'),
  ('Photo', 2, true, 'https://s3.amazonaws.com/0_00733000_1593597980_f0c9631a33950198986a470fc3f252a0.jpg', '/0_00733000_1593597980_f0c9631a33950198986a470fc3f252a0.jpg', '2016-10-20 11:05:00', '2016-10-20 11:05:00');
